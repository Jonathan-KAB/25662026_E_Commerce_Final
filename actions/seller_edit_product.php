<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../controllers/customer_controller.php';
require_once __DIR__ . '/../settings/db_class.php';

if (!isLoggedIn() || ($_SESSION['user_role'] != 3 && $_SESSION['user_role'] != 4)) {
    header("Location: ../login/login.php");
    exit();
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$product_id) {
    header("Location: ../view/seller_dashboard.php");
    exit();
}

// Fetch product details
$product = get_product_ctr($product_id);
if (!$product) {
    header("Location: ../view/seller_dashboard.php");
    exit();
}

// Verify seller owns this product
if ($product['seller_id'] != $_SESSION['customer_id']) {
    header("Location: ../view/seller_dashboard.php");
    exit();
}

$ipAddress = $_SERVER['REMOTE_ADDR'];
$cartCount = get_cart_count_ctr($ipAddress, $_SESSION['customer_id']);

$customer = get_customer_by_id_ctr($_SESSION['customer_id']);
$customer_name = $customer['customer_name'] ?? 'Seller';
$is_service_provider = ($_SESSION['user_role'] == 4);

// Get categories and brands for dropdowns
$db = new db_connection();
$db->db_connect();
$categories = $db->db_fetch_all("SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC");
$brands = $db->db_fetch_all("SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_service_provider ? 'Edit Service Listing' : 'Edit Product' ?> - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .container {
                padding: 0 16px !important;
            }
            
            .card {
                border-radius: 0 !important;
                margin: 0 -16px !important;
            }
            
            .card-header {
                border-radius: 0 !important;
                padding: 20px 16px !important;
            }
            
            .card-header h3 {
                font-size: 1.25rem !important;
            }
            
            .card-body {
                padding: 20px 16px !important;
            }
            
            /* Stack two-column grids on mobile */
            div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
            
            /* Bigger touch targets for mobile */
            input.form-input, 
            select.form-input, 
            textarea.form-input {
                padding: 12px 14px !important;
                font-size: 16px !important; /* Stops iOS from zooming in on focus */
            }
            
            /* Stack buttons vertically on mobile */
            div[style*="display: flex; gap: 16px"] {
                flex-direction: column !important;
            }
            
            /* Full width buttons are easier to tap */
            button.btn, 
            a.btn {
                width: 100% !important;
                justify-content: center !important;
            }
            
            /* Tighten up spacing a bit */
            div[style*="gap: 28px"] {
                gap: 20px !important;
            }
            
            div[style*="gap: 20px"] {
                gap: 16px !important;
            }
            
            /* Less padding on small screens */
            div[style*="padding: 20px"] {
                padding: 16px !important;
            }
            
            /* Adjust image preview on mobile */
            img[style*="max-width: 300px"] {
                max-width: 100% !important;
            }
        }
        
        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.5rem !important;
            }
            
            .page-header p {
                font-size: 0.875rem !important;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../view/includes/menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1><?= $is_service_provider ? 'Edit Service Listing' : 'Edit Product' ?></h1>
            <p><?= $is_service_provider ? 'Update your service offering details' : 'Modify your product information' ?></p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px; max-width: 800px;">
        <div class="card" style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border-radius: 12px; overflow: hidden; border: none;">
            <div class="card-header" style="background: linear-gradient(135deg, <?= $is_service_provider ? '#8b5cf6 0%, #6d28d9 100%' : '#10b981 0%, #059669 100%' ?>); color: white; border-radius: 12px 12px 0 0; padding: 24px; border: none;">
                <h3 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: white;">
                    <?= $is_service_provider ? '<i class="fas fa-scissors"></i> Service Information' : '<i class="fas fa-box"></i> Product Information' ?>
                </h3>
                <p style="margin: 8px 0 0 0; opacity: 0.95; font-size: 0.95rem;">
                    <?= $is_service_provider ? 'Update your service offering details' : 'Modify your product information' ?>
                </p>
            </div>
            <div class="card-body">
                <form id="product-form" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                    
                    <div style="display: grid; gap: 28px;">
                        <div style="background: <?= $is_service_provider ? '#f5f3ff' : '#f0fdf4' ?>; padding: 20px; border-radius: 12px; border-left: 4px solid <?= $is_service_provider ? '#8b5cf6' : '#10b981' ?>;">
                            <label for="product_title" class="form-label" style="font-size: 0.95rem; font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">
                                <?= $is_service_provider ? '<i class="fas fa-cut" style="color: #8b5cf6;"></i> Service Title' : '<i class="fas fa-tag" style="color: #10b981;"></i> Product Name' ?>
                            </label>
                            <input type="text" class="form-input" id="product_title" name="product_title" 
                                   value="<?= htmlspecialchars($product['product_title']) ?>" 
                                   placeholder="<?= $is_service_provider ? 'e.g., Custom Suit Tailoring' : 'e.g., Kente Fabric' ?>" required
                                   style="width: 100%; padding: 14px 16px; border: 2px solid <?= $is_service_provider ? '#c4b5fd' : '#86efac' ?>; border-radius: 8px; font-size: 15px; transition: all 0.2s;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div style="background: white; padding: 20px; border-radius: 12px; border: 2px solid #e5e7eb;">
                                <label for="product_cat" class="form-label" style="font-size: 0.95rem; font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">
                                    <?= $is_service_provider ? '<i class="fas fa-tags" style="color: #8b5cf6;"></i> Garment Type' : '<i class="fas fa-list" style="color: #10b981;"></i> Category' ?>
                                </label>
                                <select class="form-input" id="product_cat" name="product_cat" required
                                        style="width: 100%; padding: 12px 16px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 15px; transition: all 0.2s;">
                                    <option value=""><?= $is_service_provider ? 'What can you make?' : 'Select Category' ?></option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['cat_id'] ?>" <?= $cat['cat_id'] == $product['product_cat'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['cat_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div style="background: white; padding: 20px; border-radius: 12px; border: 2px solid #e5e7eb;">
                                <label for="product_brand" class="form-label" style="font-size: 0.95rem; font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">
                                    <i class="fas fa-store" style="color: <?= $is_service_provider ? '#8b5cf6' : '#10b981' ?>;"></i> Business Name
                                </label>
                                <select class="form-input" id="product_brand" name="product_brand" required
                                        style="width: 100%; padding: 12px 16px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 15px; transition: all 0.2s;">
                                    <option value="">Select Business Name</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?= $brand['brand_id'] ?>" <?= $brand['brand_id'] == $product['product_brand'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($brand['brand_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div style="background: white; padding: 20px; border-radius: 12px; border: 2px solid #e5e7eb;">
                                <label for="product_price" class="form-label" style="font-size: 0.95rem; font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">
                                    <i class="fas fa-money-bill-wave" style="color: <?= $is_service_provider ? '#8b5cf6' : '#10b981' ?>;"></i> <?= $is_service_provider ? 'Starting Price (GH₵)' : 'Price (GH₵)' ?>
                                </label>
                                <input type="number" class="form-input" id="product_price" name="product_price" 
                                       value="<?= $product['product_price'] ?>" step="0.01" min="0" required
                                       style="width: 100%; padding: 12px 16px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 15px; transition: all 0.2s;">
                            </div>

                            <?php if (!$is_service_provider): ?>
                            <div style="background: white; padding: 20px; border-radius: 12px; border: 2px solid #e5e7eb;">
                                <label for="product_stock" class="form-label" style="font-size: 0.95rem; font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">
                                    <i class="fas fa-boxes" style="color: #10b981;"></i> Stock Quantity
                                </label>
                                <input type="number" class="form-input" id="product_stock" name="product_stock" 
                                       value="<?= $product['product_stock'] ?? 0 ?>" min="0" required
                                       style="width: 100%; padding: 12px 16px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 15px; transition: all 0.2s;">
                                <small style="color: #6b7280; font-size: 0.875rem; display: block; margin-top: 6px;">Number of items available</small>
                            </div>
                            <?php else: ?>
                            <div style="background: white; padding: 20px; border-radius: 12px; border: 2px solid #e5e7eb;">
                                <label class="form-label" style="font-size: 0.95rem; font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">
                                    <i class="fas fa-clock" style="color: #8b5cf6;"></i> Turnaround Time
                                </label>
                                <input type="text" class="form-input" id="turnaround_time" name="product_keywords" 
                                       value="<?= htmlspecialchars($product['product_keywords'] ?? '') ?>"
                                       placeholder="e.g., 3-5 days, 1 week"
                                       style="width: 100%; padding: 12px 16px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 15px; transition: all 0.2s;">
                                <small style="color: #6b7280; font-size: 0.875rem; display: block; margin-top: 6px;">How long does it typically take?</small>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div style="background: white; padding: 20px; border-radius: 12px; border: 2px solid #e5e7eb;">
                            <label for="product_desc" class="form-label" style="font-size: 0.95rem; font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">
                                <i class="fas fa-align-left" style="color: <?= $is_service_provider ? '#8b5cf6' : '#10b981' ?>;"></i> <?= $is_service_provider ? 'Service Description' : 'Description' ?>
                            </label>
                            <textarea class="form-input" id="product_desc" name="product_desc" rows="5" required
                                      style="width: 100%; padding: 14px 16px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 15px; transition: all 0.2s; resize: vertical; min-height: 140px; font-family: inherit;"><?= htmlspecialchars($product['product_desc']) ?></textarea>
                        </div>

                        <?php if (!$is_service_provider): ?>
                        <div style="background: white; padding: 20px; border-radius: 12px; border: 2px solid #e5e7eb;">
                            <label for="product_keywords" class="form-label" style="font-size: 0.95rem; font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">
                                <i class="fas fa-tags" style="color: #10b981;"></i> Keywords (comma separated)
                            </label>
                            <input type="text" class="form-input" id="product_keywords" name="product_keywords" 
                                   value="<?= htmlspecialchars($product['product_keywords'] ?? '') ?>"
                                   placeholder="e.g., shirt, cotton, casual"
                                   style="width: 100%; padding: 12px 16px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 15px; transition: all 0.2s;">
                            <small style="color: #6b7280; font-size: 0.875rem; display: block; margin-top: 6px;">Help customers find your product with relevant keywords</small>
                        </div>
                        <?php endif; ?>

                        <div style="background: white; padding: 20px; border-radius: 12px; border: 2px solid #e5e7eb;">
                            <label for="product_image" class="form-label" style="font-size: 0.95rem; font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">
                                <i class="fas fa-image" style="color: <?= $is_service_provider ? '#8b5cf6' : '#10b981' ?>;"></i> <?= $is_service_provider ? 'Portfolio Image' : 'Product Image' ?>
                            </label>
                            <?php if (!empty($product['product_image'])): ?>
                                <div style="margin-bottom: 16px; text-align: center;">
                                    <div style="position: relative; display: inline-block;">
                                        <img src="../<?= htmlspecialchars($product['product_image']) ?>" 
                                             alt="Current image" 
                                             style="max-width: 300px; border-radius: 12px; border: 3px solid <?= $is_service_provider ? '#8b5cf6' : '#10b981' ?>; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                        <div style="position: absolute; top: -8px; right: -8px; background: <?= $is_service_provider ? '#8b5cf6' : '#10b981' ?>; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600;">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    <p style="font-size: 0.875rem; color: #6b7280; margin-top: 8px; font-weight: 500;">Current image</p>
                                </div>
                            <?php endif; ?>
                            <div style="position: relative;">
                                <input type="file" class="form-input" id="product_image" name="product_image" accept="image/*"
                                       style="width: 100%; padding: 12px 16px; border: 2px dashed #d1d5db; border-radius: 8px; font-size: 15px; transition: all 0.2s; background: #f9fafb; cursor: pointer;">
                            </div>
                            <small style="color: #6b7280; font-size: 0.875rem; display: block; margin-top: 6px;">
                                Leave blank to keep current image
                            </small>
                        </div>

                        <div style="display: flex; gap: 16px; margin-top: 8px; padding-top: 8px;">
                            <button type="submit" class="btn btn-primary" id="submit-btn"
                                    style="background: linear-gradient(135deg, <?= $is_service_provider ? '#8b5cf6 0%, #6d28d9 100%' : '#10b981 0%, #059669 100%' ?>); 
                                           color: white; border: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; 
                                           font-size: 16px; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                                           display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-save"></i> Update <?= $is_service_provider ? 'Service' : 'Product' ?>
                            </button>
                            <a href="../view/seller_dashboard.php" class="btn btn-outline-secondary"
                               style="padding: 14px 32px; border-radius: 8px; border: 2px solid #e5e7eb; color: #6b7280; 
                                      text-decoration: none; font-weight: 600; font-size: 16px; transition: all 0.2s; 
                                      display: inline-flex; align-items: center; background: white;">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#product-form').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#submit-btn');
            const formData = new FormData(this);
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            
            $.ajax({
                url: 'update_product_action.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        alert('<?= $is_service_provider ? 'Service' : 'Product' ?> updated successfully!');
                        window.location.href = '../view/seller_dashboard.php';
                    } else {
                        alert('Error: ' + (response.message || 'Failed to update'));
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update <?= $is_service_provider ? 'Service' : 'Product' ?>');
                    }
                },
                error: function() {
                    alert('Server error. Please try again.');
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update <?= $is_service_provider ? 'Service' : 'Product' ?>');
                }
            });
        });
    </script>
</body>
</html>
