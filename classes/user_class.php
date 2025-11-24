<?php

require_once '../settings/db_class.php';

/**
 * 
 */
class User extends db_connection
{
    private $user_id;
    private $name;
    private $email;
    private $password;
    private $role;
    private $date_created;
    private $phone_number;

    public function __construct($user_id = null)
    {
        parent::db_connect();
        if ($user_id) {
            $this->user_id = $user_id;
            $this->loadUser();
        }
    }

    private function loadUser($user_id = null)
    {
        if ($user_id) {
            $this->user_id = $user_id;
        }
        if (!$this->user_id) {
            return false;
        }
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result) {
            $this->name = $result['customer_name'];
            $this->email = $result['customer_email'];
            $this->role = $result['user_role'];
            $this->date_created = isset($result['date_created']) ? $result['date_created'] : null;
            $this->phone_number = $result['customer_contact'];
        }
    }

    public function createUser($name, $email, $password, $phone_number, $country, $city, $role, $service_type = 'none')
    {
        // Check if email already exists
        $existing = $this->getUserByEmail($email);
        if ($existing && !empty($existing)) {
            return false;
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO customer (customer_name, customer_email, customer_pass, customer_contact, customer_country, customer_city, user_role, service_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $name, $email, $hashed_password, $phone_number, $country, $city, $role, $service_type);
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result : false;
    }

    public function authenticateUser($email, $password)
    {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['customer_pass'])) {
            return $user;
        }
        return false;
    }

    public function getCustomerById($customerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateCustomer($customerId, $name, $contact, $country, $city, $service_type = null)
    {
        if ($service_type !== null) {
            $stmt = $this->db->prepare("UPDATE customer SET customer_name = ?, customer_contact = ?, customer_country = ?, customer_city = ?, service_type = ? WHERE customer_id = ?");
            $stmt->bind_param("sssssi", $name, $contact, $country, $city, $service_type, $customerId);
        } else {
            $stmt = $this->db->prepare("UPDATE customer SET customer_name = ?, customer_contact = ?, customer_country = ?, customer_city = ? WHERE customer_id = ?");
            $stmt->bind_param("ssssi", $name, $contact, $country, $city, $customerId);
        }
        return $stmt->execute();
    }

}
