/**
 * Checkout JavaScript
 * Handles PayStack payment integration
 */

$(document).ready(function() {
    console.log('Checkout.js loaded - PayStack integration active');
    console.log('Cart data:', window.cartData);
    
    // Handle "Pay with Paystack" button click
    $('#proceedToCheckout').on('click', function(e) {
        e.preventDefault();
        console.log('Pay with Paystack button clicked');
        initiatePaystackPayment();
    });
    
    // Handle manual verification button
    $('#verifyPayment').on('click', function(e) {
        e.preventDefault();
        console.log('Manual verification triggered');
        manuallyVerifyPayment();
    });
    
    // Check if we just returned from PayStack (check for stored reference)
    const pendingReference = sessionStorage.getItem('paystack_pending_ref');
    if (pendingReference) {
        console.log('Pending PayStack transaction detected:', pendingReference);
        console.log('Auto-verifying payment...');
        
        // Hide pay button, show verify button
        $('#proceedToCheckout').hide();
        $('#verifyPayment').show().text('Verifying Payment...').prop('disabled', true);
        $('#paymentInstructions').html('<strong>🔄 Verifying your payment...</strong><br>Please wait while we confirm your transaction.')
            .css('background', '#d1ecf1').css('border-color', '#bee5eb').css('color', '#0c5460').show();
        
        // Auto-verify immediately (reduced from 1 second to 500ms)
        setTimeout(function() {
            manuallyVerifyPayment();
        }, 500);
    }
});

/**
 * Initiate PayStack payment flow
 */
function initiatePaystackPayment() {
    console.log('Initiating PayStack payment...');
    
    // Disable button to prevent double clicks
    $('#proceedToCheckout').prop('disabled', true).text('Initializing payment...');
    
    // Get cart data from window object (passed from PHP)
    const cartData = window.cartData || {};
    const amount = cartData.total || 0;
    const email = cartData.customerEmail || '';
    
    console.log('Payment details:', { amount, email, currency: 'GHS' });
    
    if (!amount || amount <= 0) {
        console.error('Invalid cart total:', amount);
        alert('Invalid cart total');
        $('#proceedToCheckout').prop('disabled', false).text('Pay with Paystack');
        return;
    }
    
    if (!email) {
        console.error('Customer email is required');
        alert('Customer email is required');
        $('#proceedToCheckout').prop('disabled', false).text('Pay with Paystack');
        return;
    }
    
    // Store cart data in sessionStorage for verification later
    sessionStorage.setItem('checkout_cart', JSON.stringify({
        items: cartData.items || [],
        total: amount
    }));
    
    console.log('Calling PayStack initialization API...');
    
    // Call PayStack initialization endpoint
    $.ajax({
        url: '../actions/paystack_init_transaction.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            amount: amount,
            email: email
        }),
        dataType: 'json',
        success: function(response) {
            console.log('PayStack init response:', response);
            
            if (response.status === 'success' && response.authorization_url) {
                console.log('Redirecting to PayStack:', response.authorization_url);
                
                // Store reference and cart data for verification later
                sessionStorage.setItem('paystack_pending_ref', response.reference);
                sessionStorage.setItem('checkout_cart', JSON.stringify({
                    items: cartData.items || [],
                    total: amount
                }));
                
                // Redirect to PayStack (full page redirect)
                window.location.href = response.authorization_url;
            } else {
                // Show error message
                console.error('PayStack initialization failed:', response);
                alert('Failed to initialize payment: ' + (response.message || 'Unknown error'));
                $('#proceedToCheckout').prop('disabled', false).text('Pay with Paystack');
            }
        },
        error: function(xhr, status, error) {
            console.error('PayStack initialization error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            console.error('Status code:', xhr.status);
            
            let errorMessage = 'An error occurred while initializing payment. Please try again.';
            
            // Try to parse error response
            try {
                const errorResponse = JSON.parse(xhr.responseText);
                if (errorResponse.message) {
                    errorMessage = errorResponse.message;
                }
            } catch (e) {
                console.error('Could not parse error response:', e);
            }
            
            alert(errorMessage);
            $('#proceedToCheckout').prop('disabled', false).text('Pay with Paystack');
        }
    });
}

/**
 * Manually verify payment after user returns from PayStack
 */
function manuallyVerifyPayment() {
    const reference = sessionStorage.getItem('paystack_pending_ref');
    const cartData = sessionStorage.getItem('checkout_cart');
    
    if (!reference) {
        alert('No pending payment found. Please try again.');
        return;
    }
    
    console.log('Verifying payment for reference:', reference);
    
    // Disable verify button
    $('#verifyPayment').prop('disabled', true).text('Verifying...');
    
    let requestData = { reference: reference };
    
    if (cartData) {
        try {
            const parsedCart = JSON.parse(cartData);
            requestData.cart_items = parsedCart.items || [];
            requestData.total_amount = parsedCart.total || 0;
        } catch (e) {
            console.error('Error parsing cart data:', e);
        }
    }
    
    // Call verification endpoint
    $.ajax({
        url: '../actions/paystack_verify_payment.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(requestData),
        dataType: 'json',
        success: function(result) {
            console.log('Verification result:', result);
            
            if (result.status === 'success' && result.verified) {
                // Clear stored reference
                sessionStorage.removeItem('paystack_pending_ref');
                sessionStorage.removeItem('checkout_cart');
                
                // Show success and redirect
                $('#verifyPayment').removeClass('btn-success').addClass('btn-primary')
                    .text('✓ Payment Verified! Redirecting...').prop('disabled', true);
                $('#paymentInstructions').html('<strong>✓ Payment Successful!</strong> Redirecting you now...')
                    .css('background', '#d4edda').css('border-color', '#c3e6cb').css('color', '#155724');
                
                setTimeout(function() {
                    window.location.href = 'all_product.php?payment_success=1&order_id=' + result.order_id;
                }, 2000);
            } else {
                // Verification failed
                const errorMsg = result.message || 'Payment verification failed';
                console.error('Verification failed:', errorMsg);
                
                // Clear stored reference
                sessionStorage.removeItem('paystack_pending_ref');
                sessionStorage.removeItem('checkout_cart');
                
                $('#paymentInstructions').html('<strong>❌ Payment Verification Failed</strong><br>' + errorMsg)
                    .css('background', '#f8d7da').css('border-color', '#f5c6cb').css('color', '#721c24').show();
                
                // Reset buttons
                $('#verifyPayment').hide();
                $('#proceedToCheckout').show().prop('disabled', false).text('Pay with Paystack');
                
                // Show alert
                alert('Payment verification failed: ' + errorMsg + '\n\nPlease try again or contact support if payment was deducted.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Verification error:', error);
            console.error('Response:', xhr.responseText);
            
            // Clear stored reference
            sessionStorage.removeItem('paystack_pending_ref');
            sessionStorage.removeItem('checkout_cart');
            
            $('#paymentInstructions').html('<strong>❌ Verification Error</strong><br>Could not verify payment. Please contact support.')
                .css('background', '#f8d7da').css('border-color', '#f5c6cb').css('color', '#721c24').show();
            
            // Reset buttons
            $('#verifyPayment').hide();
            $('#proceedToCheckout').show().prop('disabled', false).text('Pay with Paystack');
            
            alert('An error occurred while verifying payment. Please contact support if payment was deducted.\n\nError: ' + error);
        }
    });
}
