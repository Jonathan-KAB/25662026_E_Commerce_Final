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
    
    // Handle service type - only for role 4 (Service Provider)
    if (isset($customer['user_role']) && $customer['user_role'] == 4) {
        $service_type = $_POST['service_type'] ?? 'general';
    } else {
        $service_type = 'none';
    }
    
    // Handle profile picture upload
    $imagePath = $customer['customer_image'] ?? '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        require_once __DIR__ . '/../classes/image_helper.php';
        $imageHelper = new ImageUploadHelper();
        $uploadResult = $imageHelper->uploadCustomerImage($_FILES['profile_picture'], $_SESSION['customer_id']);
        
        if ($uploadResult['success']) {
            $imagePath = $uploadResult['path'];
        }
    }
    
    $updated = update_customer_ctr($_SESSION['customer_id'], $name, $contact, $country, $city, $service_type, $imagePath);
    
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
                <form method="post" enctype="multipart/form-data" id="profile-form">
                    <div style="display: grid; gap: 24px;">
                        <!-- Profile Picture Section -->
                        <div style="text-align: center; padding: 24px; background: var(--gray-50); border-radius: 12px;">
                            <label for="profile_picture" style="cursor: pointer;">
                                <div style="width: 120px; height: 120px; margin: 0 auto 12px; border-radius: 50%; overflow: hidden; border: 4px solid var(--primary); position: relative;">
                                    <?php if (!empty($customer['customer_image'])): ?>
                                        <img src="../<?= htmlspecialchars($customer['customer_image']) ?>" 
                                             alt="Profile" id="preview-image"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <div id="preview-placeholder" style="width: 100%; height: 100%; background: var(--gray-200); display: flex; align-items: center; justify-content: center; font-size: 48px; color: var(--gray-400);">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div style="position: absolute; bottom: 0; right: 0; background: var(--primary); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white;">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>
                            </label>
                            <input type="file" class="form-input" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;">
                            <p style="font-size: 0.875rem; color: var(--gray-600); margin: 0;">Click to upload profile picture</p>
                        </div>
                        
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

                        <!-- Service Provider Settings (role 4 only) -->
                        <?php if (isset($customer['user_role']) && $customer['user_role'] == 4): ?>
                        <div style="padding: 20px; background: linear-gradient(135deg, #f8f4ff 0%, #fff 100%); border-radius: var(--radius-lg); border: 2px solid #9b87f5;">
                            <h4 style="margin-bottom: 16px; color: #7c3aed; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-scissors"></i> Service Provider Settings
                            </h4>
                            
                            <div>
                                <label for="service_type" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                    <i class="fas fa-user-tag"></i> Service Specialty
                                </label>
                                <select class="form-select" id="service_type" name="service_type" 
                                    style="width: 100%; padding: 12px 16px; border: 2px solid #9b87f5; border-radius: 8px; font-size: 15px; background: white; cursor: pointer;">
                                    <option value="general" <?= (isset($customer['service_type']) && $customer['service_type'] === 'general') ? 'selected' : '' ?>>
                                        <i class="fas fa-star"></i> General Service Provider
                                    </option>
                                    <option value="tailor" <?= (isset($customer['service_type']) && $customer['service_type'] === 'tailor') ? 'selected' : '' ?>>
                                        <i class="fas fa-user-tie"></i> Tailor
                                    </option>
                                    <option value="seamstress" <?= (isset($customer['service_type']) && $customer['service_type'] === 'seamstress') ? 'selected' : '' ?>>
                                        <i class="fas fa-cut"></i> Seamstress
                                    </option>
                                </select>
                                <small style="color: var(--gray-600); font-size: 0.875rem; display: block; margin-top: 6px;">Choose your area of expertise</small>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Fabric Seller Badge (role 3 only) -->
                        <?php if (isset($customer['user_role']) && $customer['user_role'] == 3): ?>
                        <div style="padding: 20px; background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%); border-radius: var(--radius-lg); border: 2px solid var(--primary-light);">
                            <h4 style="margin-bottom: 8px; color: var(--primary); display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-store"></i> Fabric Seller Account
                            </h4>
                            <p style="color: var(--gray-600); margin: 0; font-size: 0.875rem;">
                                You can list physical products like fabrics, materials, and sewing supplies.
                            </p>
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

    <!-- Footer Spacing -->
    <div style="height: 60px;"></div>

    <script>
        // Image preview
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImage = document.getElementById('preview-image');
                    const previewPlaceholder = document.getElementById('preview-placeholder');
                    
                    if (previewImage) {
                        previewImage.src = e.target.result;
                    } else if (previewPlaceholder) {
                        previewPlaceholder.outerHTML = '<img src="' + e.target.result + '" alt="Profile" id="preview-image" style="width: 100%; height: 100%; object-fit: cover;">';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
