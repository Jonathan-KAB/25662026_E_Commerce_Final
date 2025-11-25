<?php
require_once '../settings/core.php';
require_once '../controllers/customer_controller.php';

header('Content-Type: application/json');

$response = array();

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

$email = $_POST['email'];
$password = $_POST['password'];

// Debug: Check if user exists
$existing_user = get_user_by_email_ctr($email);
if (!$existing_user) {
    $response['status'] = 'error';
    $response['message'] = 'No account found with this email address';
    echo json_encode($response);
    exit();
}

// Try to authenticate
$user = authenticate_user_ctr($email, $password);


if ($user) {
    $_SESSION['user_id'] = $user['customer_id'];
    $_SESSION['customer_id'] = $user['customer_id'];
    $_SESSION['user_name'] = $user['customer_name'];
    $_SESSION['customer_name'] = $user['customer_name'];
    $_SESSION['user_email'] = $user['customer_email'];
    $_SESSION['user_role'] = $user['user_role'];
    
    // Force session write
    session_write_close();
    session_start();

    $response['status'] = 'success';
    $response['message'] = 'Login successful';
    
    // Role-based redirects
    if ($user['user_role'] == 2) {
        // Admin -> Admin panel
        $response['redirect'] = '../admin/category.php';
    } elseif ($user['user_role'] == 3 || $user['user_role'] == 4) {
        // Fabric Seller (3) or Service Provider (4) -> Seller dashboard
        $response['redirect'] = '../view/seller_dashboard.php';
    } else {
        // Buyer -> User dashboard
        $response['redirect'] = '../view/dashboard.php';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid password';
}

echo json_encode($response);

?>