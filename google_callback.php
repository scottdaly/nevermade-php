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

$token_url = 'https://oauth2.googleapis.com/token';
$userinfo_url = 'https://www.googleapis.com/oauth2/v3/userinfo';

if (isset($_GET['code'])) {
    $token_params = array(
        'code' => $_GET['code'],
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code',
    );

    // Log the token request parameters
    error_log("Token request params: " . print_r($token_params, true));

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_params));
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    $response = curl_exec($ch);
    
    // Log the curl response
    error_log("Curl response: " . $response);
    
    if ($response === false) {
        error_log("Curl error: " . curl_error($ch));
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        error_log("Verbose information:\n" . $verboseLog);
    }
    
    curl_close($ch);

    $token_data = json_decode($response, true);

    if (isset($token_data['access_token'])) {
        $ch = curl_init($userinfo_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token_data['access_token']));
        $userinfo_response = curl_exec($ch);
        curl_close($ch);

        $userinfo = json_decode($userinfo_response, true);

        $_SESSION['google_id'] = $userinfo['sub'];
        $_SESSION['name'] = $userinfo['name'];
        $_SESSION['email'] = $userinfo['email'];
        $_SESSION['profile_picture'] = $userinfo['picture'];

        header('Location: /dashboard.php');
        exit;
    } else {
        error_log("Token data error: " . print_r($token_data, true));
        echo "Failed to obtain access token. Check error logs for details.";
    }
} else {
    error_log("No code received in callback");
    echo "No authorization code received.";
}