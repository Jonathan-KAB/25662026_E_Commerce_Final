<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

if (!isLoggedIn() || $_SESSION['user_role'] != 3) {
    header("Location: ../login/login.php");
    exit();
}

$ipAddress = $_SERVER['REMOTE_ADDR'];
$cartCount = get_cart_count_ctr($ipAddress, $_SESSION['customer_id']);
require_once __DIR__ . '/../controllers/customer_controller.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../settings/db_class.php';

$customer = get_customer_by_id_ctr($_SESSION['customer_id']);
$customer_name = $customer['customer_name'] ?? 'Seller';

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
    <title>Add Product - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Add New Product</h1>
            <p>List your product for sale</p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px; max-width: 800px;">
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">Product Information</h3>
            </div>
            <div class="card-body">
                <form id="product-form" enctype="multipart/form-data">
                    <div style="display: grid; gap: 20px;">
                        <div>
                            <label for="product_title" class="form-label">Product Name</label>
                            <input type="text" class="form-input" id="product_title" name="product_title" required>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label for="product_cat" class="form-label">Category</label>
                                <select class="form-input" id="product_cat" name="product_cat" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['cat_id'] ?>"><?= htmlspecialchars($cat['cat_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="product_brand" class="form-label">Brand</label>
                                <select class="form-input" id="product_brand" name="product_brand" required>
                                    <option value="">Select Brand</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?= $brand['brand_id'] ?>"><?= htmlspecialchars($brand['brand_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label for="product_price" class="form-label">Price (GH₵)</label>
                                <input type="number" class="form-input" id="product_price" name="product_price" step="0.01" min="0" required>
                            </div>

                            <div>
                                <label for="product_stock" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-input" id="product_stock" name="product_stock" min="0" value="0" required>
                                <small style="color: var(--gray-600); font-size: 0.875rem;">Number of items available</small>
                            </div>
                        </div>

                        <div>
                            <label for="product_desc" class="form-label">Description</label>
                            <textarea class="form-input" id="product_desc" name="product_desc" rows="5" required></textarea>
                        </div>

                        <div>
                            <label for="product_keywords" class="form-label">Keywords (comma separated)</label>
                            <input type="text" class="form-input" id="product_keywords" name="product_keywords" placeholder="e.g., shirt, cotton, casual">
                        </div>

                        <div>
                            <label for="product_image" class="form-label">Product Image</label>
                            <input type="file" class="form-input" id="product_image" name="product_image" accept="image/*">
                            <small style="color: var(--gray-600); font-size: 0.875rem;">Optional. Supported formats: JPG, PNG, GIF</small>
                            <div id="image-preview" style="margin-top: 12px;"></div>
                        </div>

                        <div style="display: flex; gap: 12px; margin-top: 8px;">
                            <button type="submit" class="btn btn-primary" id="submit-btn">Add Product</button>
                            <a href="seller_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Image preview
        $('#product_image').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').html('<img src="' + e.target.result + '" style="max-width: 200px; max-height: 200px; border-radius: 8px;">');
                };
                reader.readAsDataURL(file);
            }
        });

        // Form submission
        $('#product-form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = $('#submit-btn');
            const originalText = submitBtn.text();
            
            submitBtn.prop('disabled', true).text('Adding...');

            $.ajax({
                url: '../actions/add_product_action.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Product added successfully!');
                        window.location.href = 'seller_dashboard.php';
                    } else {
                        alert(response.message || 'Failed to add product');
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('Error adding product. Please try again.');
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
    </script>
</body>
</html>
