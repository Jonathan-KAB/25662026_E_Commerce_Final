<?php
/**
 * PayStack Payment Callback Page
 * User is redirected here after completing payment on PayStack gateway
 */
require_once '../settings/core.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Get reference from URL parameter (PayStack sends this as trxref or reference)
$reference = null;
if (isset($_GET['reference'])) {
    $reference = trim($_GET['reference']);
} elseif (isset($_GET['trxref'])) {
    $reference = trim($_GET['trxref']);
}

if (!$reference) {
    error_log("PayStack callback - No reference found in URL. GET params: " . print_r($_GET, true));
    header('Location: payment_failed.php?error=no_reference');
    exit();
}

error_log("PayStack callback received - Reference: $reference");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - Aya Crafts</title>
    <link rel="stylesheet" href="../css/app.css">
    <style>
        .loader-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loader-text {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .loader-subtext {
            font-size: 1rem;
            opacity: 0.9;
            max-width: 400px;
        }
        
        .error-message {
            background: #ef4444;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
        }
        
        .retry-button {
            margin-top: 20px;
            padding: 12px 30px;
            background: white;
            color: #667eea;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="spinner"></div>
        <h1 class="loader-text">Processing Your Payment...</h1>
        <p class="loader-subtext">Please wait while we verify your payment with PayStack. Do not close this window.</p>
        <p class="loader-subtext" style="font-size: 0.875rem; margin-top: 16px; opacity: 0.8;">
            If you just completed payment, please wait a moment for verification.
        </p>
        <div id="errorMessage" class="error-message"></div>
        <a href="../view/cart.php" class="retry-button" id="retryButton" style="display: none;">Return to Cart</a>
    </div>

    <script>
        // Get reference from PHP
        const reference = "<?php echo htmlspecialchars($reference); ?>";
        
        if (!reference) {
            showError('No payment reference found');
        } else {
            verifyPayment(reference);
        }
        
        async function verifyPayment(reference) {
            try {
                // Get cart data from session storage (if saved by checkout page)
                const cartData = sessionStorage.getItem('checkout_cart');
                let requestData = { reference: reference };
                
                if (cartData) {
                    const parsedCart = JSON.parse(cartData);
                    requestData.cart_items = parsedCart.items || [];
                    requestData.total_amount = parsedCart.total || 0;
                }
                
                console.log('Verifying payment with reference:', reference);
                
                const response = await fetch('../actions/paystack_verify_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });
                
                const result = await response.json();
                console.log('Verification result:', result);
                
                if (result.status === 'success' && result.verified) {
                    // Clear cart data from session storage
                    sessionStorage.removeItem('checkout_cart');
                    
                    // Show success message briefly before redirect
                    document.querySelector('.loader-text').textContent = 'Payment Successful!';
                    document.querySelector('.loader-subtext').textContent = 'Redirecting to your order confirmation...';
                    
                    // Redirect to all_product.php with success message
                    setTimeout(function() {
                        window.location.href = 'all_product.php?payment_success=1&order_id=' + result.order_id;
                    }, 2000);
                } else {
                    // Payment verification failed
                    const errorMsg = result.message || 'Payment verification failed';
                    showError(errorMsg);
                }
                
            } catch (error) {
                console.error('Error verifying payment:', error);
                showError('An error occurred while verifying your payment. Please contact support.');
            }
        }
        
        function showError(message) {
            const spinner = document.querySelector('.spinner');
            const loaderText = document.querySelector('.loader-text');
            const loaderSubtext = document.querySelector('.loader-subtext');
            const errorDiv = document.getElementById('errorMessage');
            const retryButton = document.getElementById('retryButton');
            
            if (spinner) spinner.style.display = 'none';
            if (loaderText) loaderText.textContent = 'Payment Verification Failed';
            if (loaderSubtext) loaderSubtext.textContent = '';
            
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            retryButton.style.display = 'inline-block';
        }
    </script>
</body>
</html>
