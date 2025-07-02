<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $confirm_delete = $_POST['confirm_delete'] ?? '';
    
    if ($confirm_delete !== 'DELETE') {
        $_SESSION['error'] = 'Please type "DELETE" to confirm account deletion';
        header('Location: account.php');
        exit;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete user's orders first (if orders table exists)
        $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
        }
        
        // Delete user's cart items (if they exist in session)
        unset($_SESSION['cart']);
        
        // Delete the user account
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Commit transaction
            $conn->commit();
            
            // Clear all session data
            session_destroy();
            
            // Redirect to home page with success message
            session_start();
            $_SESSION['message'] = 'Your account has been successfully deleted';
            header('Location: index.php');
            exit;
        } else {
            throw new Exception('Failed to delete account');
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $_SESSION['error'] = 'Failed to delete account: ' . $e->getMessage();
        header('Location: account.php');
        exit;
    }
}

// If not POST request, redirect to account page
header('Location: account.php');
exit; 