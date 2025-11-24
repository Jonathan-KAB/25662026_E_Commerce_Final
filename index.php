<?php
// ALL PHP CODE MUST BE AT THE TOP - BEFORE ANY HTML OUTPUT
require_once 'settings/core.php';
require_once 'settings/db_class.php';
require_once 'controllers/cart_controller.php';

// Get cart count
$ipAddress = $_SERVER['REMOTE_ADDR'];
$customerId = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : null;
$cartCount = get_cart_count_ctr($ipAddress, $customerId);

// Fetch featured products
$db = new db_connection();
$db->db_connect();
$featured_products = $db->db_fetch_all("
	SELECT p.*, c.cat_name, b.brand_name,
	COALESCE(p.rating_average, 0) as rating_average,
	COALESCE(p.rating_count, 0) as rating_count
	FROM products p
	LEFT JOIN categories c ON p.product_cat = c.cat_id
	LEFT JOIN brands b ON p.product_brand = b.brand_id
	WHERE p.rating_count >= 3
	ORDER BY p.rating_average DESC, p.rating_count DESC
	LIMIT 3
");
if (!$featured_products) {
	$featured_products = $db->db_fetch_all("
		SELECT p.*, c.cat_name, b.brand_name,
		COALESCE(p.rating_average, 0) as rating_average,
		COALESCE(p.rating_count, 0) as rating_count
		FROM products p
		LEFT JOIN categories c ON p.product_cat = c.cat_id
		LEFT JOIN brands b ON p.product_brand = b.brand_id
		ORDER BY p.product_id DESC
		LIMIT 3
	");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>SeamLink - Connecting Ghana's Finest Sellers</title>
	<link href="css/app.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<style>
		body {
			background: #fafafa;
		}
		/* Hero Section */
		.hero {
			background: linear-gradient(135deg, rgba(25, 135, 84, 0.95) 0%, rgba(21, 115, 71, 0.95) 100%),
			            url('uploads/hero-kente.jpeg') center/cover;
			color: white;
			padding: 120px 0 100px;
			margin-bottom: 0;
			position: relative;
			overflow: hidden;
		}
		.hero::before {
			content: '';
			position: absolute;
			top: 20px;
			right: 20px;
			font-size: 3rem;
			opacity: 0;
		}
		.hero-content {
			max-width: 1200px;
			margin: 0 auto;
			padding: 0 20px;
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 60px;
			align-items: center;
		}
		.hero-text h1 {
			font-size: 3.5rem;
			font-weight: 700;
			margin-bottom: 20px;
			line-height: 1.1;
			color: white;
		}
		.hero-text p {
			font-size: 1.25rem;
			margin-bottom: 32px;
			opacity: 0.95;
			line-height: 1.6;
			color: white;
		}
		.hero-subtitle {
			display: inline-block;
			background: rgba(255, 255, 255, 0.2);
			padding: 8px 16px;
			border-radius: 20px;
			font-size: 0.9rem;
			margin-bottom: 16px;
			font-weight: 600;
		}
		.hero-buttons {
			display: flex;
			gap: 16px;
		}
		.hero-buttons .btn {
			padding: 14px 32px;
			font-size: 1.0625rem;
			font-weight: 600;
			border-radius: var(--radius-lg);
			text-decoration: none;
			display: inline-block;
			transition: all 0.3s;
		}
		.hero-buttons .btn i {
			margin-right: 8px;
		}
		.btn-primary-hero {
			background: white;
			color: var(--primary);
		}
		.btn-primary-hero:hover {
			transform: translateY(-2px);
			box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
		}
		.btn-secondary-hero {
			background: transparent;
			color: white;
			border: 2px solid white;
		}
		.btn-secondary-hero:hover {
			background: white;
			color: var(--primary);
		}
		.hero-image {
			position: relative;
		}
		.hero-image img {
			width: 100%;
			border-radius: var(--radius-xl);
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
		}
		.hero-stats {
			display: flex;
			gap: 40px;
			margin-top: 40px;
		}
		.hero-stat {
			text-align: center;
		}
		.hero-stat-icon {
			font-size: 2rem;
			margin-bottom: 8px;
			color: white;
		}
		.hero-stat-value {
			font-size: 1.5rem;
			font-weight: 700;
			color: white;
		}
		.hero-stat-label {
			font-size: 0.875rem;
			opacity: 0.9;
			color: white;
		}

		/* How It Works Section */
		.section {
			padding: 80px 0;
			background: white;
		}
		.section-alt {
			background: #fafafa;
		}
		.section-title {
			text-align: center;
			font-size: 2.5rem;
			font-weight: 700;
			margin-bottom: 16px;
			color: var(--gray-900);
		}
		.section-subtitle {
			text-align: center;
			font-size: 1.125rem;
			color: var(--gray-600);
			margin-bottom: 60px;
			max-width: 700px;
			margin-left: auto;
			margin-right: auto;
		}
		.steps-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 40px;
			max-width: 1200px;
			margin: 0 auto;
		}
		.step-card {
			text-align: center;
			position: relative;
		}
		.step-number {
			width: 60px;
			height: 60px;
			background: linear-gradient(135deg, var(--primary), #157347);
			color: white;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.5rem;
			font-weight: 700;
			margin: 0 auto 24px;
			box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
		}
		.step-icon {
			font-size: 3rem;
			margin-bottom: 20px;
			color: var(--primary);
		}
		.step-card h3 {
			font-size: 1.25rem;
			font-weight: 600;
			margin-bottom: 12px;
			color: var(--gray-900);
		}
		.step-card p {
			color: var(--gray-600);
			line-height: 1.6;
		}

		/* Featured Products/Sellers */
		.featured-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
			gap: 24px;
			margin-top: 40px;
		}
		.featured-card {
			background: white;
			border-radius: var(--radius-lg);
			overflow: hidden;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
			transition: all 0.3s;
		}
		.featured-card:hover {
			transform: translateY(-4px);
			box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
		}
		.featured-card img {
			width: 100%;
			height: 200px;
			object-fit: cover;
			display: block;
			background: #f5f5f5;
		}
		.featured-card-body {
			padding: 20px;
		}
		.featured-badge {
			display: inline-block;
			background: var(--success);
			color: white;
			padding: 4px 10px;
			border-radius: 12px;
			font-size: 0.75rem;
			font-weight: 600;
			margin-bottom: 12px;
		}
		.featured-card h3 {
			font-size: 1.125rem;
			font-weight: 600;
			margin-bottom: 8px;
			color: var(--gray-900);
		}
		.featured-meta {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 12px;
			font-size: 0.875rem;
			color: var(--gray-600);
		}
		.rating {
			color: #ffc107;
		}

		/* Mission Section */
		.mission-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 60px;
			align-items: center;
			max-width: 1200px;
			margin: 0 auto;
		}
		.mission-icons {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 24px;
			margin-bottom: 32px;
		}
		.mission-icon-card {
			background: white;
			padding: 24px;
			border-radius: var(--radius-lg);
			text-align: center;
			border: 1px solid var(--gray-200);
		}
		.mission-icon-card-icon {
			font-size: 2.5rem;
			margin-bottom: 12px;
			color: var(--primary);
		}
		.mission-icon-card h4 {
			font-size: 1rem;
			font-weight: 600;
			margin-bottom: 4px;
			color: var(--gray-900);
		}
		.mission-icon-card p {
			font-size: 0.875rem;
			color: var(--gray-600);
		}

		/* CTA Section */
		.cta-section {
			background: linear-gradient(135deg, var(--primary), #157347);
			color: white;
			padding: 80px 0;
			text-align: center;
		}
		.cta-section h2 {
			font-size: 2.5rem;
			font-weight: 700;
			margin-bottom: 16px;
			color: white;
		}
		.cta-section p {
			font-size: 1.25rem;
			margin-bottom: 32px;
			opacity: 0.95;
			color: white;
		}
		.cta-buttons {
			display: flex;
			gap: 16px;
			justify-content: center;
		}

		@media (max-width: 968px) {
			.hero-content {
				grid-template-columns: 1fr;
				text-align: center;
			}
			.hero-text h1 {
				font-size: 2.5rem;
			}
			.hero-buttons {
				justify-content: center;
			}
			.hero-stats {
				justify-content: center;
			}
			.mission-grid {
				grid-template-columns: 1fr;
			}
		}
		@media (max-width: 640px) {
			.hero-text h1 {
				font-size: 2rem;
			}
			.hero-buttons {
				flex-direction: column;
			}
			.section-title {
				font-size: 2rem;
			}
		}
	</style>
</head>
<body>
	<?php
	// Include menu
	include __DIR__ . '/view/includes/menu.php';
	?>

	<!-- Hero Section -->
	<div class="hero">
		<div class="hero-content">
			<div class="hero-text">
				<div class="hero-subtitle"><i class="fas fa-map-marker-alt"></i> Empowering Ghana's Fashion Heritage</div>
				<h1>Connect with Master Tailors & Authentic Fabrics</h1>
				<p>Ghana's premier digital fashion marketplace. Find verified tailors, discover authentic fabrics, and bring your custom fashion vision to life—all in one seamless platform.</p>
				<div class="hero-buttons">
					<a href="view/all_product.php" class="btn btn-primary-hero"><i class="fas fa-cut"></i> Find Tailors</a>
					<a href="#how-it-works" class="btn btn-secondary-hero"><i class="fas fa-info-circle"></i> How It Works</a>
				</div>
				<div class="hero-stats">
					<div class="hero-stat">
						<div class="hero-stat-icon"><i class="fas fa-users-cog"></i></div>
						<div class="hero-stat-value">
							<?php 
							$seller_count = $db->db_fetch_one("SELECT COUNT(*) as total FROM customer WHERE user_role = 3");
							echo ($seller_count['total'] ?? '100') . '+';
							?>
						</div>
						<div class="hero-stat-label">Verified Tailors</div>
					</div>
					<div class="hero-stat">
						<div class="hero-stat-icon"><i class="fas fa-tshirt"></i></div>
						<div class="hero-stat-value">
							<?php 
							$count_result = $db->db_fetch_one("SELECT COUNT(*) as total FROM products");
							echo $count_result['total'] ?? '500+';
							?>
						</div>
						<div class="hero-stat-label">Fabric Options</div>
					</div>
					<div class="hero-stat">
						<div class="hero-stat-icon"><i class="fas fa-check-circle"></i></div>
						<div class="hero-stat-value">100%</div>
						<div class="hero-stat-label">Authentic & Trusted</div>
					</div>
				</div>
			</div>
			<div class="hero-image">
				<img src="uploads/hero-kente.jpeg" alt="Ghanaian tailors and fabrics" onerror="this.style.display='none'">
			</div>
		</div>
	</div>

	<!-- How It Works Section -->
	<div class="section" id="how-it-works">
		<div class="container">
			<h2 class="section-title">How SeamLink Works</h2>
			<p class="section-subtitle">From discovery to delivery, we make custom tailoring simple and transparent</p>
			
			<div class="steps-grid">
				<div class="step-card">
					<div class="step-number">1</div>
					<div class="step-icon"><i class="fas fa-search"></i></div>
					<h3>Browse & Discover</h3>
					<p>Explore verified tailors and authentic fabrics from trusted vendors across Ghana</p>
				</div>
				
				<div class="step-card">
					<div class="step-number">2</div>
					<div class="step-icon"><i class="fas fa-comments"></i></div>
					<h3>Connect & Discuss</h3>
					<p>Chat with tailors about your vision, share designs, and get accurate quotes</p>
				</div>
				
				<div class="step-card">
					<div class="step-number">3</div>
					<div class="step-icon"><i class="fas fa-shield-alt"></i></div>
					<h3>Secure Payment</h3>
					<p>Pay safely through our platform with buyer protection and milestone payments</p>
				</div>
				
				<div class="step-card">
					<div class="step-number">4</div>
					<div class="step-icon"><i class="fas fa-star"></i></div>
					<h3>Rate & Review</h3>
					<p>Share your experience and help build trust in our artisan community</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Featured Products Section -->
	<div class="section section-alt">
		<div class="container">
			<h2 class="section-title">Discover Authentic Fabrics</h2>
			<p class="section-subtitle">Premium quality textiles from trusted vendors across Ghana</p>
			
			<div class="featured-grid">
				<?php if ($featured_products && count($featured_products) > 0): ?>
					<?php foreach ($featured_products as $product): ?>
						<div class="featured-card">
							<?php if (!empty($product['product_image'])): ?>
								<?php
								// Handle image paths more robustly
								$imagePath = $product['product_image'];
								
								// Check if it's an absolute path (starts with /)
								if (strpos($imagePath, '/') === 0) {
									$imageUrl = $imagePath;
								} 
								// Check if it already contains 'uploads/'
								elseif (strpos($imagePath, 'uploads/') === 0) {
									$imageUrl = $imagePath;
								}
								// Otherwise prepend uploads/
								else {
									$imageUrl = 'uploads/' . $imagePath;
								}
								?>
								<img src="<?= htmlspecialchars($imageUrl) ?>" 
								     alt="<?= htmlspecialchars($product['product_title']) ?>"
								     style="object-fit: cover;"
								     onerror="console.log('Failed to load: <?= htmlspecialchars($imageUrl) ?>'); this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22200%22%3E%3Crect fill=%22%23e0e0e0%22 width=%22300%22 height=%22200%22/%3E%3Ctext fill=%22%23999%22 font-family=%22Arial%22 font-size=%2218%22 x=%2250%25%22 y=%2245%25%22 text-anchor=%22middle%22%3E%F0%9F%93%A6 No Image%3C/text%3E%3Ctext fill=%22%23999%22 font-family=%22Arial%22 font-size=%2214%22 x=%2250%25%22 y=%2260%25%22 text-anchor=%22middle%22%3E<?= htmlspecialchars(substr($product['product_title'], 0, 20)) ?>%3C/text%3E%3C/svg%3E';">
							<?php else: ?>
								<img src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22200%22%3E%3Crect fill=%22%23e0e0e0%22 width=%22300%22 height=%22200%22/%3E%3Ctext fill=%22%23999%22 font-family=%22Arial%22 font-size=%2224%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3E%F0%9F%93%A6 No Image%3C/text%3E%3C/svg%3E" 
								     alt="No image available"
								     style="object-fit: cover;">
							<?php endif; ?>
							
							<div class="featured-card-body">
								<?php if ($product['rating_count'] >= 5): ?>
									<span class="featured-badge">Top Rated</span>
								<?php endif; ?>
								
								<h3><?= htmlspecialchars($product['product_title']) ?></h3>
								
								<div class="featured-meta">
									<span><?= htmlspecialchars($product['cat_name'] ?? 'Uncategorized') ?></span>
									<?php if ($product['rating_count'] > 0): ?>
										<span class="rating">
											★ <?= number_format($product['rating_average'], 1) ?> (<?= $product['rating_count'] ?>)
										</span>
									<?php endif; ?>
								</div>
								
								<div style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-bottom: 12px;">
									GH₵ <?= number_format($product['product_price'], 2) ?>
								</div>
								
								<a href="view/single_product.php?id=<?= $product['product_id'] ?>" class="btn btn-primary" style="width: 100%;">View Details</a>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--gray-600);">
						<p>No featured products available yet. Check back soon!</p>
					</div>
				<?php endif; ?>
			</div>
			
			<div style="text-align: center; margin-top: 40px;">
				<a href="view/all_product.php" class="btn btn-outline-primary" style="padding: 12px 32px;"><i class="fas fa-th"></i> View Full Catalog</a>
			</div>
		</div>
	</div>

	<!-- Mission Section -->
	<div class="section">
		<div class="container">
			<div class="mission-grid">
				<div>
					<div class="mission-icons">
						<div class="mission-icon-card">
							<div class="mission-icon-card-icon"><i class="fas fa-bullseye"></i></div>
							<h4>Our Vision</h4>
							<p>Ghana's leading digital marketplace</p>
						</div>
						<div class="mission-icon-card">
							<div class="mission-icon-card-icon"><i class="fas fa-gem"></i></div>
							<h4>Our Values</h4>
							<p>Trust, quality, and transparency</p>
						</div>
						<div class="mission-icon-card">
							<div class="mission-icon-card-icon"><i class="fas fa-rocket"></i></div>
							<h4>Our Impact</h4>
							<p>Empowering local businesses</p>
						</div>
						<div class="mission-icon-card">
							<div class="mission-icon-card-icon"><i class="fas fa-heart"></i></div>
							<h4>Our Pride</h4>
							<p>Made in Ghana, for Ghana</p>
						</div>
					</div>
				</div>
				
				<div>
					<h2 class="section-title" style="text-align: left; margin-bottom: 20px;">Transforming Ghana's Fashion Industry</h2>
					<p style="color: var(--gray-700); line-height: 1.8; margin-bottom: 16px;">
						SeamLink's mission is to transform Ghana's fragmented tailoring industry into a 
						seamless, transparent, and trusted marketplace. We connect customers with verified, 
						rated tailors and seamstresses while providing integrated access to authentic local 
						and international fabrics.
					</p>
					<p style="color: var(--gray-700); line-height: 1.8; margin-bottom: 24px;">
						Through secure payment systems, buyer protection, and digital visibility tools, 
						we're empowering local artisans to thrive while preserving Ghana's rich fashion 
						heritage and custom tailoring culture.
					</p>
					<a href="view/all_product.php" class="btn btn-primary" style="padding: 12px 32px;"><i class="fas fa-shopping-bag"></i> Start Shopping</a>
				</div>
			</div>
		</div>
	</div>

	<!-- CTA Section -->
	<div class="cta-section">
		<div class="container">
			<?php if (!isLoggedIn()): ?>
				<h2>Ready to Bring Your Fashion Vision to Life?</h2>
				<p>Join thousands of satisfied customers connecting with master tailors and discovering authentic fabrics</p>
				<div class="cta-buttons">
					<a href="login/register.php" class="btn btn-primary-hero"><i class="fas fa-user-plus"></i> Get Started Today</a>
					<a href="view/all_product.php" class="btn btn-secondary-hero"><i class="fas fa-cut"></i> Browse Fabrics</a>
				</div>
			<?php else: ?>
				<h2>Welcome Back, <?= htmlspecialchars($_SESSION['customer_name'] ?? 'Fashion Enthusiast') ?>!</h2>
				<p>Ready to discover amazing fabrics and connect with talented tailors?</p>
				<div class="cta-buttons">
					<a href="view/all_product.php" class="btn btn-primary-hero"><i class="fas fa-tshirt"></i> Shop Fabrics</a>
					<a href="view/dashboard.php" class="btn btn-secondary-hero"><i class="fas fa-user"></i> My Dashboard</a>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
