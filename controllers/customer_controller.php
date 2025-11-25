<?php

require_once '../classes/user_class.php';

// added country and city parameters
function register_user_ctr($name, $email, $password, $phone_number, $country, $city, $role, $service_type = 'none')
{
    $user = new User();
    $user_id = $user->createUser($name, $email, $password, $phone_number, $country, $city, $role, $service_type);
    if ($user_id) {
        return $user_id;
    }
    return false;
}

function get_user_by_email_ctr($email)
{
    $user = new User();
    return $user->getUserByEmail($email);
}

function email_exists_ctr($email)
{
    $user = new User();
    $existing_user = $user->getUserByEmail($email);
    return ($existing_user !== false && !empty($existing_user));
}

// for login authentication
function authenticate_user_ctr($email, $password)
{
    $user = new User();
    return $user->authenticateUser($email, $password);
}

// get customer by ID
function get_customer_by_id_ctr($customerId)
{
    $user = new User();
    return $user->getCustomerById($customerId);
}

// update customer profile
function update_customer_ctr($customerId, $name, $contact, $country, $city, $service_type = null, $image = null)
{
    $user = new User();
    return $user->updateCustomer($customerId, $name, $contact, $country, $city, $service_type, $image);
}

?>