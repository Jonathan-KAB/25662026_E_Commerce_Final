<?php
// Configure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_lifetime', 0);
session_name('SEAMLINK_SESSION');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//for header redirection
ob_start();

//funtion to check for login
function isLoggedIn(){
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    else{
        return true;
    }
}

function isAdmin(){
    if (isLoggedIn()){
        return $_SESSION['user_role'] == 2;
    }
}

//function to get user ID
function get_user_id(){
    if (isLoggedIn()) {
        return $_SESSION['user_id'];
    }
    return null;
}

//function to get user name
function get_user_name(){
    if (isLoggedIn()) {
        return $_SESSION['user_name'] ?? $_SESSION['customer_name'] ?? 'User';
    }
    return null;
}

//function to check for role (admin, customer, etc)




?>