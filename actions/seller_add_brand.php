<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../settings/db_class.php';

if (!isLoggedIn() || ($_SESSION['user_role'] != 3 && $_SESSION['user_role'] != 4)) {
    header("Location: ../login/login.php");
    exit();
}

$ipAddress = $_SERVER['REMOTE_ADDR'];
$cartCount = get_cart_count_ctr($ipAddress, $_SESSION['customer_id']);

// Get categories for dropdown
$db = new db_connection();
$db->db_connect();
$categories = $db->db_fetch_all("SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Business Name - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../view/includes/menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-store"></i> Add Business Name</h1>
            <p>Create a brand identity for your products or services</p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px; max-width: 600px;">
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;"><i class="fas fa-copyright"></i> Business Information</h3>
            </div>
            <div class="card-body">
                <div id="message-container"></div>
                
                <form id="brand-form" enctype="multipart/form-data">
                    <div style="display: grid; gap: 20px;">
                        <div>
                            <label for="brand_name" class="form-label">
                                <i class="fas fa-tag"></i> Business/Brand Name
                            </label>
                            <input type="text" class="form-input" id="brand_name" name="brand_name" 
                                   placeholder="e.g., Kojo's Tailoring Services, Adwoa's Fabrics" required>
                            <small style="color: var(--gray-600); font-size: 0.875rem;">
                                This will appear as your vendor name on listings
                            </small>
                        </div>

                        <div>
                            <label for="brand_cat" class="form-label">
                                <i class="fas fa-layer-group"></i> Primary Category
                            </label>
                            <select class="form-input" id="brand_cat" name="brand_cat" required>
                                <option value="">Select your main category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['cat_id'] ?>"><?= htmlspecialchars($cat['cat_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color: var(--gray-600); font-size: 0.875rem;">
                                Choose the category that best represents your business
                            </small>
                        </div>

                        <div>
                            <label for="brand_image" class="form-label">
                                <i class="fas fa-image"></i> Business Logo (Optional)
                            </label>
                            <input type="file" class="form-input" id="brand_image" name="brand_image" accept="image/*">
                            <small style="color: var(--gray-600); font-size: 0.875rem;">
                                Upload a logo to make your brand more recognizable
                            </small>
                            <div id="image-preview" style="margin-top: 12px;"></div>
                        </div>

                        <div style="background: var(--gray-50); padding: 16px; border-radius: 8px; border-left: 4px solid var(--primary);">
                            <p style="margin: 0; font-size: 0.875rem; color: var(--gray-700);">
                                <i class="fas fa-info-circle"></i> <strong>Note:</strong> You can create multiple brands if you run different businesses or want to organize your listings by category.
                            </p>
                        </div>

                        <div style="display: flex; gap: 12px; margin-top: 8px;">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fas fa-plus-circle"></i> Create Brand
                            </button>
                            <a href="seller_add_product.php" class="btn btn-outline-secondary">Back to Add Product</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Image preview
        $('#brand_image').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').html('<img src="' + e.target.result + '" style="max-width: 150px; max-height: 150px; border-radius: 8px; border: 2px solid var(--gray-200);">');
                };
                reader.readAsDataURL(file);
            }
        });

        // Form submission
        $('#brand-form').on('submit', function(e) {
            e.preventDefault();
            
            const brandName = $('#brand_name').val().trim();
            const brandCat = $('#brand_cat').val();
            const submitBtn = $('#submit-btn');
            const originalText = submitBtn.html();
            
            if (!brandName || !brandCat) {
                showMessage('Please fill in all required fields', 'error');
                return;
            }
            
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');

            // First create the brand
            $.ajax({
                url: 'add_brand_action.php',
                method: 'POST',
                data: {
                    brand_name: brandName,
                    brand_cat: brandCat
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const brandId = response.brand_id;
                        const imageFile = $('#brand_image')[0].files[0];
                        
                        // If there's an image, upload it
                        if (imageFile) {
                            const formData = new FormData();
                            formData.append('image', imageFile);
                            formData.append('brand_id', brandId);
                            
                            $.ajax({
                                url: 'upload_brand_image_action.php',
                                method: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                dataType: 'json',
                                success: function(imgResponse) {
                                    if (imgResponse.status === 'success') {
                                        showMessage('Brand created successfully with logo!', 'success');
                                    } else {
                                        showMessage('Brand created but logo upload failed: ' + (imgResponse.message || 'Unknown error'), 'warning');
                                    }
                                    redirectToAddProduct();
                                },
                                error: function() {
                                    showMessage('Brand created but logo upload failed', 'warning');
                                    redirectToAddProduct();
                                }
                            });
                        } else {
                            showMessage('Brand created successfully!', 'success');
                            redirectToAddProduct();
                        }
                    } else {
                        showMessage(response.message || 'Failed to create brand', 'error');
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function() {
                    showMessage('Error creating brand. Please try again.', 'error');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        function showMessage(message, type) {
            const alertClass = type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger');
            const icon = type === 'success' ? 'check-circle' : (type === 'warning' ? 'exclamation-triangle' : 'times-circle');
            
            $('#message-container').html(
                '<div style="padding: 12px 16px; margin-bottom: 20px; border-radius: 8px; background: var(--' + alertClass + '-light, #f0f0f0); color: var(--' + alertClass + ', #333); border-left: 4px solid var(--' + alertClass + ');">' +
                '<i class="fas fa-' + icon + '"></i> ' + message +
                '</div>'
            );
        }

        function redirectToAddProduct() {
            setTimeout(function() {
                window.location.href = 'seller_add_product.php';
            }, 2000);
        }
    </script>
</body>
</html>
