<?php
// public product listing view
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Products</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/app.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
	<style>
		/* Local note: product image heights are controlled by css/app.css via variables --product-image-height-desktop and --product-image-height-mobile */
		.product-image-placeholder i{font-size:28px}
	</style>
</head>
<body>

	<?php
	// Menu tray: view is in `view/` so settings are one level up
	require_once __DIR__ . '/../settings/core.php';
	?>

	<div class="menu-tray">
		<span class="me-2">Menu:</span>
		<?php if (!isLoggedIn()): ?>
			<a href="../login/register.php" class="btn btn-sm btn-outline-primary">Register</a>
			<a href="../login/login.php" class="btn btn-sm btn-outline-secondary">Login</a>
		<?php else: ?>
			<?php if (isAdmin()): ?>
				<a href="../admin/category.php" class="btn btn-sm btn-outline-secondary">Category</a>
				<a href="../admin/brand.php" class="btn btn-sm btn-outline-secondary">Brand</a>
				<a href="../admin/product.php" class="btn btn-sm btn-outline-secondary">Products</a>
			<?php endif; ?>
			<a href="../login/logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
		<?php endif; ?>
	</div>

	<div class="page-header">
		<div class="container">
			<h1>Products</h1>
			<p>Browse products by category and brand</p>
		</div>
	</div>

	<div class="container" style="margin-top: 40px; margin-bottom: 60px; max-width:1100px;">
		<div id="products" class="row g-3"></div>
	</div>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script>
	$(function(){
		$.getJSON('../actions/fetch_product_action.php', function(data){
			var html = '';
			if (Array.isArray(data) && data.length) {
				data.forEach(function(p){
					// Use Font Awesome placeholder when no image is provided
					var imgHtml = p.product_image ? ('<img src="' + ('../' + p.product_image) + '" class="card-img-top product-image" alt="' + (p.product_title || '') + '">') : '<div class="product-image-placeholder" role="img" aria-label="No image available"><i class="fa fa-image" aria-hidden="true"></i></div>';
					// Safely build a short description (escape HTML and truncate)
					var rawDesc = p.product_desc || p.description || '';
					var escapedDesc = $('<div>').text(rawDesc).html();
					var shortDesc = escapedDesc.length > 140 ? escapedDesc.substr(0,137) + '...' : escapedDesc;
					const supName = p.seller_name || p.brand_name || 'Unknown';
					const supHtml = p.seller_id ? `<a href="seller_profile.php?id=${p.seller_id}">${supName}</a>` : supName;
					const supAvatar = p.seller_image ? `<img class="seller-logo" src="${(p.seller_image && p.seller_image.startsWith('/uploads')) ? p.seller_image : ('../' + p.seller_image)}" alt="${supName}">` : `<div class="seller-avatar">${(supName || 'U').charAt(0).toUpperCase()}</div>`;
					let stockHtml = '';
					if (typeof p.product_stock !== 'undefined') {
						if (p.product_stock >= 999) {
							stockHtml = `<div class="stock-bubble production"><i class="fas fa-check-circle"></i> Available for Production</div>`;
						} else if (p.product_stock > 0 && p.product_stock <= 10) {
							stockHtml = `<div class="stock-bubble warn"><i class="fas fa-exclamation-triangle"></i> Only ${p.product_stock} left!</div>`;
						} else if (p.product_stock > 10) {
							stockHtml = `<div class="stock-bubble in-stock"><i class="fas fa-check-circle"></i> In Stock (${p.product_stock} available)</div>`;
						} else {
							stockHtml = `<div class="stock-bubble out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</div>`;
						}
					}
					html += `
								<div class="col-md-4">
									<div class="card mb-3 product-card">
										<div class="position-relative">
											<span class="product-badge">${p.brand_name || ''}</span>
											<span class="product-like">❤</span>
											${imgHtml}
										</div>
										<div class="card-body">
											<div class="product-meta mb-1">${p.cat_name || ''}</div>
											<div class="product-seller--compact">${supAvatar}<div class="seller-info"><div class="seller-label">Supplied by</div><div class="seller-details">${supHtml}</div></div></div>
											<h5 class="product-title">${p.product_title || ''}</h5>
											<p class="card-text">${shortDesc}</p>
											<div class="d-flex justify-content-between align-items-center mt-2">
												<div class="product-meta">Views: ${p.views || 0}</div>
												<div class="price ${(p.product_type === 'service') ? 'service' : ''}">GHC ${(parseFloat(p.product_price) || 0).toFixed(2)}</div>
											</div>
											${stockHtml}
										</div>
									</div>
								</div>
								`;
				});
			} else {
				html = '<div class="col-12 text-muted">No products found.</div>';
			}
			$('#products').html(html);
		}).fail(function(){
			$('#products').html('<div class="col-12 text-danger">Failed to load products.</div>');
		});
	});
	</script>
	<!-- Footer Spacing -->
	<div style="height: 60px;"></div>
	</body>
	</html>
