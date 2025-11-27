<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/db_class.php';
require_once __DIR__ . '/../settings/paystack_config.php';

$db = new db_connection();
$db->db_connect();
$plans = $db->db_fetch_all("SELECT * FROM plans WHERE is_public = 1 ORDER BY price ASC");

// Gate the pricing page to only allow sellers/service providers (role 3 and 4)
if (!isLoggedIn()) {
    // Redirect guests to login page with redirect param
    header('Location: ../login/login.php?redirect=pricing.php');
    exit();
}

if (!isSeller()) {
    // Show a friendly message for users who aren't sellers
    include __DIR__ . '/includes/menu.php';
    ?>
    <div class="container" style="padding: 60px 20px;">
        <div class="no-products">
            <h3>SeamLink Pricing is for Sellers & Service Providers</h3>
            <p>This page is only available to seller accounts (Fabric Sellers and Service Providers). If you'd like to sell on SeamLink, please register as a vendor or contact support to request an account upgrade.</p>
            <p><a class="btn btn-primary" href="../login/register.php">Register as a Seller</a> <a class="btn btn-outline-secondary" href="../view/all_product.php">Browse Products</a></p>
        </div>
    </div>
    <?php
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .pricing-hero { text-align: center; margin: 36px 0; }
        .pricing-hero p { color: var(--gray-600); }
        /* Minor adjustments for the demo */
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <div class="container">
        <div class="page-title pricing-hero">
            <h1>Choose Your Plan</h1>
            <p>SeamLink subscription tiers for sellers and vendors — upgrade to unlock advanced features.</p>
        </div>
        <div class="pricing-hero-card">
            <div class="hero-left">
                <h2>Power up your storefront</h2>
                <p>Upgrade to reach more customers, promote your work, and get priority support from SeamLink.</p>
            </div>
            <div class="hero-right">
                <div class="featured-note">
                    <i class="fas fa-bolt"></i>
                    Best for sellers who want to scale their visibility
                </div>
            </div>
        </div>

        <div class="pricing-grid" role="list">
            <?php if ($plans && count($plans) > 0): ?>
                <?php foreach ($plans as $plan): ?>
                    <?php $pid = $plan['id'] ?? $plan['plan_id'] ?? $plan['planId'] ?? $plan['planID'] ?? null; ?>
                    <div class="pricing-card card <?= (strtolower($plan['name']) === 'pro' || floatval($plan['price']) === 150) ? 'popular' : '' ?>">
                        <div class="ribbon">Most popular</div>
                        <div class="card-body">
                            <div class="pricing-header">
                                <div style="display:flex;align-items:center;justify-content:space-between;">
                                    <div style="font-weight:800;font-size:1.125rem;color:var(--gray-900);"><?= htmlspecialchars($plan['name']) ?></div>
                                    <?php if ((float)$plan['price'] <= 0): ?>
                                        <div class="badge" style="background: var(--gray-100); color: var(--gray-700); padding: 6px 10px; border-radius: 8px; font-weight:700;">Free</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="price" style="margin: 10px 0; font-size: 1.75rem; color: var(--primary); display:flex; align-items:baseline; gap: 8px;">
                                <?php if ((float)$plan['price'] <= 0): ?>
                                    <div class="price-free">Free</div>
                                <?php else: ?>
                                    <span class="currency"><?= get_currency_symbol($plan['currency'] ?? 'GHS') ?></span>
                                    <span class="amount"><?= number_format($plan['price'], 2) ?></span>
                                    <span class="interval">/ month</span>
                                <?php endif; ?>
                            </div>
                            <ul class="features">
                                <?php $features = json_decode($plan['features'], true) ?: [];
                                    foreach ($features as $f): ?>
                                    <li><i class="fas fa-check" style="color:var(--primary);"></i> <?= htmlspecialchars($f) ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <div style="margin-top: 16px; display:flex; gap:10px;">
                                    <?php if ((float)$plan['price'] <= 0): ?>
                                    <a href="javascript:void(0)" class="btn btn-block btn-sm btn-outline-secondary" onclick="chooseFree(<?= json_encode($pid) ?>)">Get Started</a>
                                <?php else: ?>
                                    <button class="btn btn-block btn-sm btn-primary" onclick="subscribe(<?= json_encode($pid) ?>, <?= json_encode($plan['name']) ?>)">Subscribe</button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-primary learn-more-btn" onclick="openPlanModal(<?= json_encode($pid) ?>)">Learn More</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <h3>No plans available at the moment</h3>
                    <p>Please check again later or contact support for more information.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <div style="height: 60px;"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Client-side plan data for the modal
        const PRICING_PLANS = <?= json_encode($plans ?? []) ?>;

        function findPlanById(id) {
            if (!id && id !== 0) return null;
            return PRICING_PLANS.find(p => (p.id && p.id == id) || (p.plan_id && p.plan_id == id) || (p.planId && p.planId == id));
        }

        function openPlanModal(id) {
            const plan = findPlanById(id);
            const modal = document.getElementById('planDetailsModal');
            if (!plan) {
                alert('Plan not found');
                return;
            }
            modal.querySelector('.plan-title').textContent = plan.name;
            if (parseFloat(plan.price) > 0) {
                modal.querySelector('.plan-price').innerHTML = `${get_currency_symbol(plan.currency || 'GHS')} ${parseFloat(plan.price).toFixed(2)} <span class="plan-interval">/ month</span>`;
            } else {
                modal.querySelector('.plan-price').textContent = 'Free';
            }
            const list = modal.querySelector('.plan-features');
            list.innerHTML = '';
            const features = JSON.parse(plan.features || '[]');
            features.forEach(f => {
                const li = document.createElement('li');
                li.innerHTML = `<i class="fas fa-check" style="color:var(--primary)"></i> ${f}`;
                list.appendChild(li);
            });
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        }
        function closePlanModal() {
            const modal = document.getElementById('planDetailsModal');
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }
    </script>
    <script>
        function subscribe(planId, planName) {
            if (!confirm('Proceed to subscribe to ' + planName + '?')) return;
            // If not logged in, redirect to login
            var loggedIn = <?= isLoggedIn() ? 'true' : 'false'; ?>;
            if (!loggedIn) {
                window.location.href = '../login/login.php';
                return;
            }
            showSubscriptionModal(planId, planName);
            // TODO: integrate with backend create_subscription_action.php
        }

        function chooseFree(planId) {
            if (!confirm('Select free Basic tier?')) return;
            var loggedIn = <?= isLoggedIn() ? 'true' : 'false'; ?>;
            if (!loggedIn) {
                window.location.href = '../login/login.php';
                return;
            }
            showSubscriptionModal(planId, 'Basic');
            // TODO: call an action to set the plan for the user
        }
        function showSubscriptionModal(planId, planName) {
            const modal = document.getElementById('subscriptionModal');
            modal.querySelector('.modal-title').textContent = planName + ' Plan';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        }
        function closeSubscriptionModal() {
            const modal = document.getElementById('subscriptionModal');
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }
        // Close modal on clicking backdrop
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('subscriptionModal');
            if (modal && modal.classList.contains('show') && e.target === modal) {
                closeSubscriptionModal();
            }
        });
        // Close on ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('subscriptionModal');
                if (modal && modal.classList.contains('show')) {
                    closeSubscriptionModal();
                }
            }
        });
    </script>
    
    <!-- Subscription Modal (simple) -->
    <div id="subscriptionModal" class="modal" role="dialog" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Subscribe</h3>
                <button onclick="closeSubscriptionModal()" class="btn btn-sm btn-outline-secondary">Close</button>
            </div>
            <div class="modal-body">
                <p>Thanks for choosing a SeamLink plan. We'll be in touch shortly to finalize your subscription.</p>
                <p>If you'd like immediate access, please contact support or use the checkout flow.</p>
            </div>
        </div>
    </div>
    
    <!-- Plan Details Modal -->
    <div id="planDetailsModal" class="modal" role="dialog" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="plan-title">Plan Name</h3>
                <button onclick="closePlanModal()" class="btn btn-sm btn-outline-secondary">Close</button>
            </div>
            <div class="modal-body">
                <div class="plan-price" style="font-weight:900; font-size:1.25rem; margin-bottom:10px">GHS 0.00</div>
                <ul class="plan-features" style="list-style:none; padding-left:0; margin-bottom: 12px;"></ul>
                <div style="display:flex; gap:8px;">
                    <button class="btn btn-primary" onclick="closePlanModal()">Subscribe</button>
                    <button class="btn btn-outline-secondary" onclick="closePlanModal()">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

