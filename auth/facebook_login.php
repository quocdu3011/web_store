<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Facebook OAuth Configuration
$fb_app_id = 'YOUR_FACEBOOK_APP_ID';
$fb_app_secret = 'YOUR_FACEBOOK_APP_SECRET';
$fb_redirect_uri = 'http://your-domain.com/auth/facebook_login.php';

// Initialize Facebook SDK
require_once '../vendor/autoload.php';
$fb = new Facebook\Facebook([
    'app_id' => $fb_app_id,
    'app_secret' => $fb_app_secret,
    'default_graph_version' => 'v12.0'
]);

$helper = $fb->getRedirectLoginHelper();

try {
    if (isset($_GET['code'])) {
        // Get access token
        $accessToken = $helper->getAccessToken();
        
        if (!isset($accessToken)) {
            throw new Exception('Failed to get access token');
        }
        
        // Get user data
        $response = $fb->get('/me?fields=id,name,email', $accessToken);
        $fb_user = $response->getGraphUser();
        
        $email = $fb_user->getEmail();
        $name = $fb_user->getName();
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND social_type = 'facebook'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            // User exists, log them in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
        } else {
            // Create new user
            $username = strtolower(str_replace(' ', '', $name)) . rand(100, 999);
            $social_id = $fb_user->getId();
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, social_id, social_type) VALUES (?, ?, ?, ?, 'facebook')");
            $stmt->bind_param("ssss", $username, $email, $name, $social_id);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
            }
        }
        
        // Redirect to home page
        header('Location: ../index.php');
        exit;
    }
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // Graph API returned an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // SDK returned an error
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}

// Generate login URL
$permissions = ['email'];
$loginUrl = $helper->getLoginUrl($fb_redirect_uri, $permissions);
header('Location: ' . $loginUrl);
exit; 