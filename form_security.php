<?php
/**
 * Biblioteca de segurança e tratamento de formulários de contato.
 * Versão compatível com PHP 5+ (sem tipagem escalar / retorno).
 */

// CONFIGURAÇÃO GLOBAL
if (!defined('FORM_SECURITY_MIN_SECONDS')) {
    define('FORM_SECURITY_MIN_SECONDS', 3);
}
if (!defined('FORM_SECURITY_RATE_MAX')) {
    define('FORM_SECURITY_RATE_MAX', 5);
}
if (!defined('FORM_SECURITY_RATE_WINDOW')) {
    define('FORM_SECURITY_RATE_WINDOW', 300);
}
if (!defined('FORM_SECURITY_HONEYPOT_FIELD')) {
    define('FORM_SECURITY_HONEYPOT_FIELD', 'website');
}
if (!defined('FORM_SECURITY_TURNSTILE_SECRET')) {
    define('FORM_SECURITY_TURNSTILE_SECRET', getenv('CF_TURNSTILE_SECRET') ? getenv('CF_TURNSTILE_SECRET') : '');
}

// Polyfill para hash_equals em ambientes PHP antigos
if (!function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string)
    {
        if (!is_string($known_string) || !is_string($user_string)) {
            return false;
        }
        if (strlen($known_string) !== strlen($user_string)) {
            return false;
        }
        $res = $known_string ^ $user_string;
        $ret = 0;
        $len = strlen($res);
        for ($i = 0; $i < $len; $i++) {
            $ret |= ord($res[$i]);
        }
        return $ret === 0;
    }
}

// #region agent log helper
function form_security_debug_log($hypothesisId, $location, $message, $data = array())
{
    $entry = array(
        'sessionId'   => 'debug-session',
        'runId'       => 'run1',
        'hypothesisId'=> $hypothesisId,
        'location'    => $location,
        'message'     => $message,
        'data'        => $data,
        'timestamp'   => round(microtime(true) * 1000)
    );
    $path = __DIR__ . '/../.cursor/debug.log';
    @file_put_contents($path, json_encode($entry) . "\n", FILE_APPEND);
}
// #endregion agent log helper

function form_security_process($config)
{
    if (!is_array($config)) {
        $config = array();
    }

    // #region agent log
    form_security_debug_log('H1', 'form_security.php:process:entry', 'form_security_process entry', array('config_keys' => array_keys($config)));
    // #endregion agent log

    $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
    if ($method !== 'POST') {
        return array('success' => false, 'message' => 'Método de requisição inválido.');
    }

    // 1) User-Agent obrigatório e não suspeito
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
    if ($userAgent === '' || form_security_is_suspicious_user_agent($userAgent)) {
        return array('success' => false, 'message' => 'User-Agent inválido ou suspeito.');
    }

    // 2) Rate limit
    $ip = form_security_get_ip();
    if (!form_security_check_rate_limit($ip)) {
        return array('success' => false, 'message' => 'Muitas tentativas em um curto período. Tente novamente em alguns minutos.');
    }

    // 3) Honeypot
    $honeypotField = FORM_SECURITY_HONEYPOT_FIELD;
    $honeypotValue = isset($_POST[$honeypotField]) ? $_POST[$honeypotField] : '';
    if (!empty($honeypotValue)) {
        return array('success' => false, 'message' => 'Submissão inválida.');
    }

    // 4) Time trap
    $startTime = isset($_POST['start_time']) ? (int)$_POST['start_time'] : 0;
    if ($startTime > 0) {
        $nowMs = (int)(microtime(true) * 1000);
        $diff  = $nowMs - $startTime;
        if ($diff < FORM_SECURITY_MIN_SECONDS * 1000) {
            return array('success' => false, 'message' => 'Formulário enviado rápido demais. Por favor, tente novamente.');
        }
    } else {
        return array('success' => false, 'message' => 'Dados de tempo inválidos.');
    }

    // 5) CSRF
    $csrfPost   = isset($_POST['csrf_token']) ? trim($_POST['csrf_token']) : '';
    $csrfCookie = isset($_COOKIE['csrf_token']) ? trim($_COOKIE['csrf_token']) : '';
    if ($csrfPost === '' || $csrfCookie === '' || !hash_equals($csrfCookie, $csrfPost)) {
        // #region agent log
        form_security_debug_log('H2', 'form_security.php:process:csrf', 'CSRF inválido', array('csrf_post_empty' => $csrfPost === '', 'csrf_cookie_empty' => $csrfCookie === ''));
        // #endregion agent log
        return array('success' => false, 'message' => 'Falha de segurança na validação do token.');
    }

    // 6) Turnstile
    if (FORM_SECURITY_TURNSTILE_SECRET !== '') {
        $turnstileToken = isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '';
        if (!form_security_validate_turnstile($turnstileToken, $ip)) {
            return array('success' => false, 'message' => 'Validação de segurança falhou. Recarregue a página e tente novamente.');
        }
    }

    // 7) Normalização
    $fields   = isset($config['fields']) ? $config['fields'] : array();
    $required = isset($config['required']) ? $config['required'] : array();
    $emailKey = isset($config['email_field']) ? $config['email_field'] : null;
    $phoneKey = isset($config['phone_field']) ? $config['phone_field'] : null;
    $nameKey  = isset($config['name_field']) ? $config['name_field'] : null;

    $clean = array();

    foreach ($fields as $field) {
        $rawValue = isset($_POST[$field]) ? $_POST[$field] : '';
        if (is_array($rawValue)) {
            return array('success' => false, 'message' => 'Formato de dados inválido.');
        }
        $value = trim((string)$rawValue);

        if (in_array($field, $required, true) && $value === '') {
            return array('success' => false, 'message' => 'Preencha todos os campos obrigatórios.');
        }

        if ($value !== '' && form_security_has_malicious_content($value)) {
            return array('success' => false, 'message' => 'Conteúdo inválido detectado no formulário.');
        }

        $clean[$field] = $value;
    }

    // 8) Validações específicas
    if ($emailKey && !empty($clean[$emailKey])) {
        if (!filter_var($clean[$emailKey], FILTER_VALIDATE_EMAIL)) {
            return array('success' => false, 'message' => 'E-mail inválido.');
        }
    }

    if ($phoneKey && !empty($clean[$phoneKey])) {
        if (!form_security_validate_phone($clean[$phoneKey])) {
            return array('success' => false, 'message' => 'Telefone inválido.');
        }
    }

    if ($nameKey && !empty($clean[$nameKey])) {
        if (!form_security_validate_name($clean[$nameKey])) {
            return array('success' => false, 'message' => 'Nome inválido.');
        }
    }

    // 9) Montagem do e-mail
    $recipient = isset($config['recipient']) ? $config['recipient'] : '';
    $siteName  = isset($config['site_name']) ? $config['site_name'] : 'Formulário';
    $prefix    = isset($config['subject_prefix']) ? $config['subject_prefix'] : '[' . $siteName . ']';

    if ($recipient === '') {
        return array('success' => false, 'message' => 'Configuração de destino de e-mail ausente.');
    }

    $subjectField = isset($config['subject_field']) ? $config['subject_field'] : 'subject';
    $subjectBase  = isset($clean[$subjectField]) ? $clean[$subjectField] : 'Novo contato';
    $subject      = trim($prefix . ' ' . $subjectBase);

    $bodyLines   = array();
    $bodyLines[] = 'Novo contato recebido através do site ' . $siteName . ':';
    $bodyLines[] = '----------------------------------------';
    foreach ($clean as $key => $value) {
        if ($value === '') continue;
        $label       = ucwords(str_replace(array('_', '-'), ' ', $key));
        $bodyLines[] = $label . ': ' . $value;
    }
    $bodyLines[] = '----------------------------------------';
    $bodyLines[] = 'IP: ' . $ip;
    $bodyLines[] = 'User-Agent: ' . $userAgent;
    $body        = implode("\n", $bodyLines);

    $fromEmail = ($emailKey && !empty($clean[$emailKey])) ? $clean[$emailKey] : $recipient;
    $headers   = array();
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'From: ' . form_security_sanitize_header($siteName) . ' <' . $fromEmail . '>';
    if ($emailKey && !empty($clean[$emailKey])) {
        $headers[] = 'Reply-To: ' . $clean[$emailKey];
    }

    // #region agent log
    form_security_debug_log('H3', 'form_security.php:process:before_mail', 'Enviando e-mail', array('to' => $recipient, 'subject' => $subject));
    // #endregion agent log

    $mailSuccess = @mail(
        $recipient,
        form_security_sanitize_header($subject),
        $body,
        implode("\r\n", $headers)
    );

    if (!$mailSuccess) {
        return array('success' => false, 'message' => 'Não foi possível enviar sua mensagem no momento. Tente novamente mais tarde.');
    }

    return array('success' => true, 'message' => 'Mensagem enviada com sucesso!');
}

function form_security_is_suspicious_user_agent($ua)
{
    $uaLower   = strtolower((string)$ua);
    $blacklist = array(
        'curl','wget','httpclient','python-requests','python-urllib',
        'scrapy','bot','spider','crawler','scanner','libwww-perl','java',
    );
    foreach ($blacklist as $term) {
        if (strpos($uaLower, $term) !== false) {
            return true;
        }
    }
    return false;
}

function form_security_get_ip()
{
    $keys = array('HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR');
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ipList = explode(',', (string)$_SERVER[$key]);
            return trim($ipList[0]);
        }
    }
    return '0.0.0.0';
}

function form_security_check_rate_limit($ip)
{
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'form_rate_limits';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $safeIp = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', (string)$ip);
    $file   = $dir . DIRECTORY_SEPARATOR . $safeIp . '.json';

    $now     = time();
    $window  = FORM_SECURITY_RATE_WINDOW;
    $maxHits = FORM_SECURITY_RATE_MAX;
    $entries = array();

    if (is_file($file)) {
        $content = file_get_contents($file);
        if ($content !== false) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $entries = $decoded;
            }
        }
    }

    $filtered = array();
    foreach ($entries as $ts) {
        if (is_int($ts) && ($now - $ts) < $window) {
            $filtered[] = $ts;
        }
    }
    $entries = $filtered;

    if (count($entries) >= $maxHits) {
        @file_put_contents($file, json_encode($entries));
        return false;
    }

    $entries[] = $now;
    @file_put_contents($file, json_encode($entries));

    return true;
}

function form_security_has_malicious_content($value)
{
    $value = (string)$value;
    if ($value !== strip_tags($value)) {
        return true;
    }
    $patterns = array(
        '/<\s*script/i',
        '/on\w+\s*=/i',
        '/javascript:/i',
        '/<\s*iframe/i',
        '/<\s*img/i',
        '/document\.cookie/i',
        '/<\s*form/i',
    );
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $value)) {
            return true;
        }
    }
    return false;
}

function form_security_validate_phone($phone)
{
    $phone = preg_replace('/\s+/', '', (string)$phone);
    if (!preg_match('/^\+?[0-9().\-]{8,20}$/', $phone)) {
        return false;
    }
    $digits = preg_replace('/\D+/', '', $phone);
    return strlen($digits) >= 8;
}

function form_security_validate_name($name)
{
    $name = trim((string)$name);
    if ($name === '') {
        return false;
    }
    if (!preg_match('/^[\p{L}\s\'\-]{2,}$/u', $name)) {
        return false;
    }
    return true;
}

function form_security_validate_turnstile($token, $ip)
{
    $token = (string)$token;
    if ($token === '') {
        return false;
    }

    $secret = FORM_SECURITY_TURNSTILE_SECRET;
    if ($secret === '') {
        if (function_exists('error_log')) {
            error_log('FORM_SECURITY_TURNSTILE_SECRET não configurado. Token não será validado.');
        }
        return true;
    }

    if (!function_exists('curl_init')) {
        if (function_exists('error_log')) {
            error_log('cURL não disponível para validar Turnstile.');
        }
        return false;
    }

    $url  = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = array('secret' => $secret, 'response' => $token, 'remoteip' => $ip);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    if ($response === false) {
        if (function_exists('error_log')) {
            error_log('Falha ao conectar na API Turnstile: ' . curl_error($ch));
        }
        curl_close($ch);
        return false;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        if (function_exists('error_log')) {
            error_log('Resposta inválida da API Turnstile: HTTP ' . $httpCode);
        }
        return false;
    }

    $result = json_decode($response, true);
    if (!is_array($result) || empty($result['success'])) {
        if (function_exists('error_log')) {
            error_log('Token Turnstile reprovado ou resposta inválida: ' . $response);
        }
        return false;
    }

    return true;
}

function form_security_sanitize_header($value)
{
    return trim(preg_replace('/[\r\n]+/', ' ', (string)$value));
}
