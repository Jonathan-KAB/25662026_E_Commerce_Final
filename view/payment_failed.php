<?php
require_once __DIR__ . '/../settings/core.php';

// Get error message from URL
$errorMessage = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'An error occurred during payment processing.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <style>
        .error-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .error-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .error-details {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            color: #856404;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">✗</div>
        <h1 class="error-title">Payment Failed</h1>
        <p class="error-message">Unfortunately, your payment could not be processed.</p>
        
        <div class="error-details">
            <p><strong>Error:</strong> <?= $errorMessage ?></p>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            Your cart items have been preserved. Please try again or contact support if the problem persists.
        </p>
        
        <div class="action-buttons">
            <a href="checkout.php" class="btn btn-primary">Try Again</a>
            <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
        </div>
    </div>
    <!-- Footer Spacing -->
    <div style="height: 60px;"></div>
</body>
</html>
