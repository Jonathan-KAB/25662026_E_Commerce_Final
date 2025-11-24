<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

$ipAddress = $_SERVER['REMOTE_ADDR'];
$cartCount = get_cart_count_ctr($ipAddress, $_SESSION['customer_id']);
require_once __DIR__ . '/../controllers/customer_controller.php';

$customer = get_customer_by_id_ctr($_SESSION['customer_id']);
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? $customer['customer_name'];
    $contact = $_POST['contact'] ?? $customer['customer_contact'];
    $country = $_POST['country'] ?? $customer['customer_country'];
    $city = $_POST['city'] ?? $customer['customer_city'];
    $service_type = $_POST['service_type'] ?? $customer['service_type'];
    
    $updated = update_customer_ctr($_SESSION['customer_id'], $name, $contact, $country, $city, $service_type);
    
    if ($updated) {
        $customer = get_customer_by_id_ctr($_SESSION['customer_id']);
        $_SESSION['customer_name'] = $customer['customer_name'];
        $message = 'Profile updated successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to update profile. Please try again.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Edit Profile</h1>
            <p>Update your account information</p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px; max-width: 600px;">
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 24px; padding: 16px; border-radius: var(--radius-lg); background: <?= $messageType === 'success' ? 'var(--success-light)' : 'var(--danger-light)' ?>; color: <?= $messageType === 'success' ? 'var(--success)' : 'var(--danger)' ?>;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">Profile Information</h3>
            </div>
            <div class="card-body">
                <form method="post">
                    <div style="display: grid; gap: 24px;">
                        <div>
                            <label for="name" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600;">Full Name</label>
                            <input type="text" class="form-input" id="name" name="name" 
                                   value="<?= htmlspecialchars($customer['customer_name']) ?>" 
                                   style="width: 100%; padding: 12px 16px; border: 1px solid var(--gray-300); border-radius: 8px; font-size: 15px;" required>
                        </div>

                        <div>
                            <label for="email" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600;">Email Address</label>
                            <input type="email" class="form-input" id="email" 
                                   value="<?= htmlspecialchars($customer['customer_email']) ?>" 
                                   disabled style="width: 100%; padding: 12px 16px; border: 1px solid var(--gray-300); border-radius: 8px; font-size: 15px; background: var(--gray-100); cursor: not-allowed;">
                            <small style="color: var(--gray-600); font-size: 0.875rem; display: block; margin-top: 6px;">Email cannot be changed</small>
                        </div>

                        <!-- Service Type Display (for sellers) -->
                        <?php if (isset($customer['user_role']) && $customer['user_role'] == 3): ?>
                        <div>
                            <label for="service_type" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <i class="fas fa-scissors"></i> Service Type
                            </label>
                            <select class="form-select" id="service_type" name="service_type" 
                                style="width: 100%; padding: 12px 16px; border: 2px solid var(--gray-300); border-radius: 8px; font-size: 15px; background: white; cursor: pointer;">
                                <option value="none" <?= (!isset($customer['service_type']) || $customer['service_type'] === 'none') ? 'selected' : '' ?>>General Vendor</option>
                                <option value="tailor" <?= (isset($customer['service_type']) && $customer['service_type'] === 'tailor') ? 'selected' : '' ?>>✂️ Tailor</option>
                                <option value="seamstress" <?= (isset($customer['service_type']) && $customer['service_type'] === 'seamstress') ? 'selected' : '' ?>>🪡 Seamstress</option>
                                <option value="general" <?= (isset($customer['service_type']) && $customer['service_type'] === 'general') ? 'selected' : '' ?>>👔 General Service Provider</option>
                            </select>
                            <small style="color: var(--gray-600); font-size: 0.875rem; display: block; margin-top: 6px;">This is displayed on your seller profile</small>
                        </div>
                        <?php endif; ?>

                        <div>
                            <label for="contact" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600;">Phone Number</label>
                            <input type="tel" class="form-input" id="contact" name="contact" 
                                   value="<?= htmlspecialchars($customer['customer_contact'] ?? '') ?>" 
                                   style="width: 100%; padding: 12px 16px; border: 1px solid var(--gray-300); border-radius: 8px; font-size: 15px;" required>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label for="city" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600;">City</label>
                                <input type="text" class="form-input" id="city" name="city" 
                                       value="<?= htmlspecialchars($customer['customer_city'] ?? '') ?>" 
                                       style="width: 100%; padding: 12px 16px; border: 1px solid var(--gray-300); border-radius: 8px; font-size: 15px;" required>
                            </div>

                            <div>
                                <label for="country" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600;">Country</label>
                                <input type="text" class="form-input" id="country" name="country" 
                                       value="<?= htmlspecialchars($customer['customer_country'] ?? '') ?>" 
                                       style="width: 100%; padding: 12px 16px; border: 1px solid var(--gray-300); border-radius: 8px; font-size: 15px;" required>
                            </div>
                        </div>

                        <div style="display: flex; gap: 12px; margin-top: 8px;">
                            <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">Save Changes</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary" style="padding: 12px 24px;">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
