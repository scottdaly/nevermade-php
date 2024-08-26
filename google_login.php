<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$client_id = '1075189821345-qhi68a4qhsd1208vpjctjfjptq70tmbb.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri = 'http://localhost:5173/google_callback.php';

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth';
$token_url = 'https://oauth2.googleapis.com/token';
$userinfo_url = 'https://www.googleapis.com/oauth2/v3/userinfo';

$params = array(
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
    'access_type' => 'online',
);

$auth_url = $auth_url . '?' . http_build_query($params);

// Log the auth URL
error_log("Auth URL: " . $auth_url);

// Redirect with error handling
if (!headers_sent()) {
    header('Location: ' . $auth_url);
    exit;
} else {
    echo "Headers already sent. Cannot redirect. Auth URL: " . htmlspecialchars($auth_url);
}