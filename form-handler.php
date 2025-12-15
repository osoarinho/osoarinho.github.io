<?php
require_once __DIR__ . '/form_security.php';

/**
 * Endpoint único de formulário em https://soarinho.com/form-handler.php
 *
 * Cada landing page envia:
 * - site: identificador da origem (hub, voz, musica, edicao/editor, web/dev, suporte)
 * - redirect: URL absoluta para redirecionar após o processamento
 */

$site     = isset($_POST['site']) ? $_POST['site'] : 'hub';
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'https://soarinho.com/';

switch ($site) {
    case 'voz':
        $config = [
            'site_name'      => 'Locução e Dublagem',
            'recipient'      => 'contato@soarinho.com',
            'subject_prefix' => '[Voz]',
            'fields'         => ['name', 'email', 'subject', 'message'],
            'required'       => ['name', 'email', 'message'],
            'email_field'    => 'email',
            'phone_field'    => null,
            'name_field'     => 'name',
            'subject_field'  => 'subject',
        ];
        break;

    case 'musica':
        $config = [
            'site_name'      => 'Música',
            'recipient'      => 'contato@soarinho.com',
            'subject_prefix' => '[Música]',
            'fields'         => ['name', 'email', 'subject', 'message'],
            'required'       => ['name', 'email', 'message'],
            'email_field'    => 'email',
            'phone_field'    => null,
            'name_field'     => 'name',
            'subject_field'  => 'subject',
        ];
        break;

    case 'edicao':
    case 'editor':
        $config = [
            'site_name'      => 'Edição Audiovisual',
            'recipient'      => 'contato@soarinho.com',
            'subject_prefix' => '[Edição]',
            'fields'         => ['name', 'email', 'service_type', 'message'],
            'required'       => ['name', 'email', 'message'],
            'email_field'    => 'email',
            'phone_field'    => null,
            'name_field'     => 'name',
            'subject_field'  => 'service_type',
        ];
        break;

    case 'web':
    case 'dev':
        $config = [
            'site_name'      => 'Desenvolvimento Web',
            'recipient'      => 'contato@soarinho.com',
            'subject_prefix' => '[Web]',
            'fields'         => ['name', 'email', 'message'],
            'required'       => ['name', 'email', 'message'],
            'email_field'    => 'email',
            'phone_field'    => null,
            'name_field'     => 'name',
            'subject_field'  => 'message',
        ];
        break;

    case 'suporte':
        $config = [
            'site_name'      => 'Suporte Técnico Remoto',
            'recipient'      => 'contato@soarinho.com',
            'subject_prefix' => '[Suporte]',
            'fields'         => ['name', 'email', 'phone', 'problem'],
            'required'       => ['name', 'email', 'phone', 'problem'],
            'email_field'    => 'email',
            'phone_field'    => 'phone',
            'name_field'     => 'name',
            'subject_field'  => 'problem',
        ];
        break;

    case 'hub':
    default:
        $config = [
            'site_name'      => 'Soarinho - Artista Tecnológico',
            'recipient'      => 'contato@soarinho.com',
            'subject_prefix' => '[Soarinho]',
            'fields'         => ['name', 'email', 'subject', 'message'],
            'required'       => ['name', 'email', 'message'],
            'email_field'    => 'email',
            'phone_field'    => null,
            'name_field'     => 'name',
            'subject_field'  => 'subject',
        ];
        break;
}

$result  = form_security_process($config);
$status  = $result['success'] ? 'ok' : 'error';
$message = urlencode($result['message']);

// Anexa status e msg à URL de redirect, respeitando fragmento (#)
$url  = $redirect;
$hash = '';
$pos  = strpos($url, '#');
if ($pos !== false) {
    $hash = substr($url, $pos);
    $url  = substr($url, 0, $pos);
}

$sep = (strpos($url, '?') === false) ? '?' : '&';
$url .= $sep . 'status=' . $status . '&msg=' . $message;

$redirectUrl = $url . $hash;

header('Location: ' . $redirectUrl);
exit;
