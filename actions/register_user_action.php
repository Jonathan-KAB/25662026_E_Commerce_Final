<?php

header('Content-Type: application/json');

session_start();

$response = array();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

// Validate that all required fields are present
$required_fields = ['name', 'email', 'password', 'phone_number', 'country', 'city', 'role'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $response['status'] = 'error';
        $response['message'] = 'Please fill in all required fields';
        $response['missing_field'] = $field;
        echo json_encode($response);
        exit();
    }
}

require_once '../controllers/customer_controller.php';

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$phone_number = trim($_POST['phone_number']);
$country = trim($_POST['country']);
$city = trim($_POST['city']);
$role = intval($_POST['role']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid email format';
    echo json_encode($response);
    exit();
}

// Check if email already exists
if (email_exists_ctr($email)) {
    $response['status'] = 'error';
    $response['message'] = 'Email already exists';
    echo json_encode($response);
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    $response['status'] = 'error';
    $response['message'] = 'Password must be at least 6 characters long';
    echo json_encode($response);
    exit();
}

// Attempt to register the user
$user_id = register_user_ctr($name, $email, $password, $phone_number, $country, $city, $role);

if ($user_id) {
    $response['status'] = 'success';
    $response['message'] = 'Registered successfully';
    $response['user_id'] = $user_id;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to register. Please try again.';
}

echo json_encode($response);

?>