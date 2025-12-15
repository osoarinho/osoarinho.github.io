<?php
require_once __DIR__ . '/../form_security.php';

// Handler de formulário - Hub Principal (soarinho.com)
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

$result  = form_security_process($config);
$status  = $result['success'] ? 'ok' : 'error';
$message = urlencode($result['message']);

$redirectUrl = 'index.html?status=' . $status . '&msg=' . $message . '#contact';
header('Location: ' . $redirectUrl);
exit;

