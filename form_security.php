<?php
/**
 * Biblioteca de segurança e tratamento de formulários de contato.
 *
 * Regras implementadas:
 * - Honeypot invisível (campo "website")
 * - Time trap (start_time mínimo de 3 segundos)
 * - Token anti-CSRF (double submit cookie + campo oculto)
 * - Validação de User-Agent (bloqueia vazio/suspeitos)
 * - Validação de formato de e-mail, telefone, nome.
 * - Rejeição de payload com HTML, tags, conteúdo malicioso.
 * - Rate-limit simples por IP (arquivo JSON).
 * - Integração com Cloudflare Turnstile (token cf-turnstile-response).
 * - Envio de e-mail com mail().
 */

// CONFIGURAÇÃO GLOBAL
define('FORM_SECURITY_MIN_SECONDS', 3);
define('FORM_SECURITY_RATE_MAX', 5);
define('FORM_SECURITY_RATE_WINDOW', 300);
define('FORM_SECURITY_HONEYPOT_FIELD', 'website');

if (!defined('FORM_SECURITY_TURNSTILE_SECRET')) {
    define('FORM_SECURITY_TURNSTILE_SECRET', getenv('CF_TURNSTILE_SECRET') ?: '');
}

function form_security_process(array $config): array
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return [
            'success' => false,
            'message' => 'Método de requisição inválido.'
        ];
    }

    // 1) User-Agent obrigatório e não suspeito
    $userAgent = trim($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($userAgent === '' || form_security_is_suspicious_user_agent($userAgent)) {
        return [
            'success' => false,
            'message' => 'User-Agent inválido ou suspeito.'
        ];
    }

    // 2) Rate limit por IP
    $ip = form_security_get_ip();
    if (!form_security_check_rate_limit($ip)) {
        return [
            'success' => false,
            'message' => 'Muitas tentativas em um curto período. Tente novamente em alguns minutos.'
        ];
    }

    // 3) Honeypot
    $honeypotField = FORM_SECURITY_HONEYPOT_FIELD;
    if (!empty($_POST[$honeypotField] ?? '')) {
        return [
            'success' => false,
            'message' => 'Submissão inválida.'
        ];
    }

    // 4) Time trap
    $startTime = isset($_POST['start_time']) ? (int)$_POST['start_time'] : 0;
    if ($startTime > 0) {
        $nowMs = (int)(microtime(true) * 1000);
        $diff  = $nowMs - $startTime;
        if ($diff < (FORM_SECURITY_MIN_SECONDS * 1000)) {
            return [
                'success' => false,
                'message' => 'Formulário enviado rápido demais. Por favor, tente novamente.'
            ];
        }
    } else {
        return [
            'success' => false,
            'message' => 'Dados de tempo inválidos.'
        ];
    }

    // 5) CSRF
    $csrfTokenPost   = trim($_POST['csrf_token'] ?? '');
    $csrfTokenCookie = trim($_COOKIE['csrf_token'] ?? '');
    if ($csrfTokenPost === '' || $csrfTokenCookie === '' || !hash_equals($csrfTokenCookie, $csrfTokenPost)) {
        return [
            'success' => false,
            'message' => 'Falha de segurança na validação do token.'
        ];
    }

    // 6) Turnstile
    if (FORM_SECURITY_TURNSTILE_SECRET !== '') {
        $turnstileToken = $_POST['cf-turnstile-response'] ?? '';
        if (!form_security_validate_turnstile($turnstileToken, $ip)) {
            return [
                'success' => false,
                'message' => 'Validação de segurança falhou. Recarregue a página e tente novamente.'
            ];
        }
    }

    // 7) Normalização
    $fields   = $config['fields'] ?? [];
    $required = $config['required'] ?? [];
    $emailKey = $config['email_field'] ?? null;
    $phoneKey = $config['phone_field'] ?? null;
    $nameKey  = $config['name_field'] ?? null;

    $clean = [];

    foreach ($fields as $field) {
        $rawValue = $_POST[$field] ?? '';
        if (is_array($rawValue)) {
            return [
                'success' => false,
                'message' => 'Formato de dados inválido.'
            ];
        }
        $value = trim((string)$rawValue);

        if (in_array($field, $required, true) && $value === '') {
            return [
                'success' => false,
                'message' => 'Preencha todos os campos obrigatórios.'
            ];
        }

        if ($value !== '' && form_security_has_malicious_content($value)) {
            return [
                'success' => false,
                'message' => 'Conteúdo inválido detectado no formulário.'
            ];
        }

        $clean[$field] = $value;
    }

    // 8) Validações específicas
    if ($emailKey && !empty($clean[$emailKey])) {
        if (!filter_var($clean[$emailKey], FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'E-mail inválido.'
            ];
        }
    }

    if ($phoneKey && !empty($clean[$phoneKey])) {
        if (!form_security_validate_phone($clean[$phoneKey])) {
            return [
                'success' => false,
                'message' => 'Telefone inválido.'
            ];
        }
    }

    if ($nameKey && !empty($clean[$nameKey])) {
        if (!form_security_validate_name($clean[$nameKey])) {
            return [
                'success' => false,
                'message' => 'Nome inválido.'
            ];
        }
    }

    // 9) Montagem do e-mail
    $recipient = $config['recipient']      ?? '';
    $siteName  = $config['site_name']      ?? 'Formulário';
    $prefix    = $config['subject_prefix'] ?? '[' . $siteName . ']';

    if ($recipient === '') {
        return [
            'success' => false,
            'message' => 'Configuração de destino de e-mail ausente.'
        ];
    }

    $subjectBase = $clean[$config['subject_field'] ?? 'subject'] ?? 'Novo contato';
    $subject     = trim($prefix . ' ' . $subjectBase);

    $bodyLines   = [];
    $bodyLines[] = "Novo contato recebido através do site {$siteName}:";
    $bodyLines[] = '----------------------------------------';
    foreach ($clean as $key => $value) {
        if ($value === '') continue;
        $label       = ucwords(str_replace(['_', '-'], ' ', $key));
        $bodyLines[] = "{$label}: {$value}";
    }
    $bodyLines[] = '----------------------------------------';
    $bodyLines[] = 'IP: ' . $ip;
    $bodyLines[] = 'User-Agent: ' . $userAgent;
    $body        = implode("\n", $bodyLines);

    $fromEmail = $emailKey && !empty($clean[$emailKey]) ? $clean[$emailKey] : $recipient;
    $headers   = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'From: ' . form_security_sanitize_header($siteName) . " <{$fromEmail}>";
    if ($emailKey && !empty($clean[$emailKey])) {
        $headers[] = 'Reply-To: ' . $clean[$emailKey];
    }

    $mailSuccess = @mail(
        $recipient,
        form_security_sanitize_header($subject),
        $body,
        implode("\r\n", $headers)
    );

    if (!$mailSuccess) {
        return [
            'success' => false,
            'message' => 'Não foi possível enviar sua mensagem no momento. Tente novamente mais tarde.'
        ];
    }

    return [
        'success' => true,
        'message' => 'Mensagem enviada com sucesso!'
    ];
}

function form_security_is_suspicious_user_agent(string $ua): bool
{
    $uaLower   = strtolower($ua);
    $blacklist = [
        'curl',
        'wget',
        'httpclient',
        'python-requests',
        'python-urllib',
        'scrapy',
        'bot',
        'spider',
        'crawler',
        'scanner',
        'libwww-perl',
        'java',
    ];

    foreach ($blacklist as $term) {
        if (strpos($uaLower, $term) !== false) {
            return true;
        }
    }

    return false;
}

function form_security_get_ip(): string
{
    $keys = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR',
    ];

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ipList = explode(',', (string)$_SERVER[$key]);
            return trim($ipList[0]);
        }
    }

    return '0.0.0.0';
}

function form_security_check_rate_limit(string $ip): bool
{
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'form_rate_limits';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $safeIp = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $ip);
    $file   = $dir . DIRECTORY_SEPARATOR . $safeIp . '.json';

    $now     = time();
    $window  = FORM_SECURITY_RATE_WINDOW;
    $maxHits = FORM_SECURITY_RATE_MAX;
    $entries = [];

    if (is_file($file)) {
        $content = file_get_contents($file);
        if ($content !== false) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $entries = $decoded;
            }
        }
    }

    $entries = array_values(array_filter($entries, function ($ts) use ($now, $window) {
        return is_int($ts) && ($now - $ts) < $window;
    }));

    if (count($entries) >= $maxHits) {
        @file_put_contents($file, json_encode($entries));
        return false;
    }

    $entries[] = $now;
    @file_put_contents($file, json_encode($entries));

    return true;
}

function form_security_has_malicious_content(string $value): bool
{
    if ($value !== strip_tags($value)) {
        return true;
    }

    $patterns = [
        '/<\s*script/i',
        '/on\w+\s*=/i',
        '/javascript:/i',
        '/<\s*iframe/i',
        '/<\s*img/i',
        '/document\.cookie/i',
        '/<\s*form/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $value)) {
            return true;
        }
    }

    return false;
}

function form_security_validate_phone(string $phone): bool
{
    $phone = preg_replace('/\s+/', '', $phone);
    if (!preg_match('/^\+?[0-9().\-]{8,20}$/', $phone)) {
        return false;
    }
    $digits = preg_replace('/\D+/', '', $phone);
    return strlen($digits) >= 8;
}

function form_security_validate_name(string $name): bool
{
    $name = trim($name);
    if ($name === '') {
        return false;
    }
    if (!preg_match('/^[\p{L}\s\'\-]{2,}$/u', $name)) {
        return false;
    }
    return true;
}

function form_security_validate_turnstile(string $token, string $ip): bool
{
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

    $url  = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret'   => $secret,
        'response' => $token,
        'remoteip' => $ip,
    ];

    if (!function_exists('curl_init')) {
        if (function_exists('error_log')) {
            error_log('cURL não disponível para validar Turnstile.');
        }
        return false;
    }

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

function form_security_sanitize_header(string $value): string
{
    return trim(preg_replace('/[\r\n]+/', ' ', $value));
}
