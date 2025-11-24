# Stock Display & Service Type Updates

## ✅ Completed Features

### 1. Stock Quantity Display
Stock quantities are now displayed throughout the application:

#### **Product Listing Page** (`all_product.php`)
- Shows "In Stock (X available)" for items with stock > 10
- Shows "⚠️ Only X left!" for low stock (≤ 10 items)
- Shows "❌ Out of Stock" for items with 0 stock
- Disables "Add to Cart" button for out-of-stock items

#### **Product Detail Page** (`single_product.php`)
- Large prominent stock availability box
- Green box for in-stock items
- Red box for out-of-stock items
- Yellow/orange warning for low stock
- Shows exact quantity available
- Disables "Add to Cart" button when out of stock

#### **Seller Profile Page** (`seller_profile.php`)
- Same stock display on seller's product listings
- Matches the styling from all_product.php

### 2. Service Type Display

#### **Seller Profile** (`seller_profile.php`)
- Service type badge displayed next to seller name
- Shows icon and text: ✂️ Tailor, 🪡 Seamstress, 👔 General
- Purple badge styling to distinguish from "Verified" badge
- Only shown if service_type is set and not 'none'

#### **User Profile** (`profile.php`)
- Sellers can see their service type in their profile settings
- Displayed in a styled box with icon
- Non-editable (read-only display)
- Note indicating it's shown on their seller profile

## Database Requirements

Make sure you've run this SQL:

```sql
-- Add stock column
ALTER TABLE products ADD COLUMN product_stock INT DEFAULT 0;

-- Add service type column (if not already added)
ALTER TABLE customer ADD COLUMN service_type ENUM('tailor', 'seamstress', 'general', 'none') DEFAULT 'none';
```

## Setting Initial Stock

Update product stock values:

```sql
-- Set stock for all products
UPDATE products SET product_stock = 100;

-- Or set individual products
UPDATE products SET product_stock = 50 WHERE product_id = 1;
UPDATE products SET product_stock = 75 WHERE product_id = 2;
```

## Setting Service Types

Update seller service types:

```sql
-- Set a seller as a tailor
UPDATE customer SET service_type = 'tailor' WHERE customer_id = 123 AND user_role = 3;

-- Set a seller as a seamstress
UPDATE customer SET service_type = 'seamstress' WHERE customer_id = 124 AND user_role = 3;

-- Set a seller as general service provider
UPDATE customer SET service_type = 'general' WHERE customer_id = 125 AND user_role = 3;
```

## How It Works

### Stock Management Flow:
1. **Initial Setup**: Set stock quantities in database
2. **Display**: Stock shown on all product pages
3. **Order Placement**: When payment is verified, stock is automatically reduced
4. **Prevention**: Out-of-stock items cannot be added to cart (button disabled)

### Service Type Flow:
1. **Database**: Service type stored in `customer` table
2. **Seller Profile**: Badge displayed showing service type
3. **User Profile**: Sellers can view their service type
4. **Public View**: Customers see service type when viewing seller profiles

## Visual Indicators

### Stock Levels:
- **High Stock** (> 10): Green "✓ In Stock" with quantity
- **Low Stock** (1-10): Red "⚠️ Only X left!"
- **Out of Stock** (0): Red "❌ Out of Stock" + disabled button

### Service Types:
- **Tailor**: ✂️ Purple badge
- **Seamstress**: 🪡 Purple badge
- **General**: 👔 Purple badge

## Files Modified

1. `view/all_product.php` - Added stock display to product cards
2. `view/single_product.php` - Added prominent stock availability section
3. `view/seller_profile.php` - Added service type badge and stock display
4. `view/profile.php` - Added service type display for sellers
5. `controllers/product_controller.php` - Added stock management functions
6. `classes/product_class.php` - Added reduceStock() and checkStock() methods
7. `actions/paystack_verify_payment.php` - Integrated stock reduction on payment

## Testing

1. **Test Stock Display**:
   - Browse products with different stock levels
   - Try adding out-of-stock items to cart (should be disabled)
   - View single product page with different stock levels

2. **Test Service Type**:
   - Set a seller's service type in database
   - Visit their seller profile (seller_profile.php?id=X)
   - Check badge displays correctly
   - Login as seller and view profile.php

3. **Test Stock Reduction**:
   - Place an order through PayStack
   - After successful payment, check product_stock in database
   - Should be reduced by ordered quantity
