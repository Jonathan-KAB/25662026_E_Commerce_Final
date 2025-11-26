<?php
require_once __DIR__ . '/../settings/core.php';

// Get order details from URL
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$invoiceNo = isset($_GET['invoice']) ? htmlspecialchars($_GET['invoice']) : '';
$amount = isset($_GET['amount']) ? htmlspecialchars($_GET['amount']) : '0.00';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .success-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .success-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .success-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .order-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }
        
        .order-details p {
            margin: 10px 0;
            font-size: 16px;
        }
        
        .order-details strong {
            color: #333;
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
    <div class="success-container">
        <div class="success-icon"><i class="fas fa-check-circle" aria-hidden="true"></i></div>
        <h1 class="success-title">Payment Successful!</h1>
        <p class="success-message">Your order has been placed successfully.</p>
        
        <div class="order-details">
            <p><strong>Order ID:</strong> #<?= $orderId ?></p>
            <p><strong>Invoice Number:</strong> <?= $invoiceNo ?></p>
            <p><strong>Amount Paid:</strong> GHS <?= $amount ?></p>
            <p><strong>Payment Status:</strong> <span style="color: #28a745;">Completed</span></p>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            A confirmation email has been sent to your registered email address.
        </p>
        
        <div class="action-buttons">
            <a href="orders.php" class="btn btn-primary">View My Orders</a>
            <a href="all_product.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
        <!-- Footer Spacing -->
        <div style="height: 60px;"></div>
    </body>
    </html>
