<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Google OAuth Configuration
$google_client_id = '943827709693-5qk22d00l28lpqfqgjcshjeqjclbughn.apps.googleusercontent.com';
$google_client_secret = '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';
$google_redirect_uri = 'http://localhost/auth/google_login.php';

// Initialize Google Client
require_once '../vendor/autoload.php';
$client = new Google_Client();
$client->setClientId($google_client_id);
$client->setClientSecret($google_client_secret);
$client->setRedirectUri($google_redirect_uri);
$client->addScope("email");
$client->addScope("profile");
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $info = $google_oauth->userinfo->get();

        $email = $info->email;
        $name = $info->name;
        $social_id = $info->id;

        // Kiểm tra email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND social_type = 'google'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Đã tồn tại → đăng nhập
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $full_name = $user['full_name'] ?: $name; // Sử dụng tên từ Google nếu không có
            $_SESSION['success_message'] = "Đăng nhập thành công! Chào mừng bạn, $full_name";
            header('Location: ../index.php');
            exit;
        } else {
            // Chưa có → lưu tạm vào session, chuyển sang bước nhập username
            $_SESSION['google_register'] = [
                'email' => $email,
                'full_name' => $name,
                'social_id' => $social_id,
                'social_type' => 'google',
                'avatar' => $info->picture
            ];
            header('Location: complete_google_register.php');
            exit;
        }
    }
}

// Nếu chưa có code → redirect Google login
$auth_url = $client->createAuthUrl();
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit;
