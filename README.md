# SeamLink - Ghanaian Fashion & Tailoring Marketplace

**SeamLink** is a comprehensive digital marketplace connecting customers with master tailors, seamstresses, and authentic fabric vendors across Ghana. Built with PHP/MySQL, it features custom tailoring services, fabric shopping, reviews, seller profiles, and order management.

## Features

### For Customers
- **Find Tailors & Seamstresses** - Browse verified master tailors with ratings and portfolios
- **Shop Authentic Fabrics** - Discover local and international fabrics from trusted vendors
- **Custom Orders** - Commission custom clothing with detailed specifications
- **Review System** - Read and write 5-star reviews for tailors and fabrics
- **Tailor Profiles** - Check out artisan storefronts with ratings and completed work
- **Order Tracking** - Keep tabs on your orders from start to finish
- **Shopping Cart** - Purchase fabrics and materials
- **Wishlist** - Save your favorite fabrics and tailors for later

### For Tailors & Seamstresses (User Role 3)
- **Professional Storefront** - Show off your skills with a custom profile and portfolio
- **Manage Orders** - Accept and track custom tailoring jobs
- **Fabric Listings** - Sell fabrics on top of your tailoring services
- **Client Communication** - Talk directly with customers about what they want
- **Rating & Reviews** - Build your reputation through customer feedback
- **Business Analytics** - Track your orders, ratings, and how happy customers are

### For Fabric Vendors (User Role 3)
- **Product Catalog** - List authentic fabrics with descriptions and prices
- **Inventory Management** - Track fabric stock levels and variants
- **Multiple Images** - Showcase fabric patterns and textures
- **Vendor Profile** - Build trust with store branding and verification
- **Sales Dashboard** - Monitor fabric sales and popular items

### For Admins (User Role 1)
- **Vendor Management** - Verify and manage tailors and fabric sellers
- **Product Management** - Moderate fabric and material listings
- **Category Management** - Organize fabric types and tailoring services
- **Order Oversight** - Monitor all transactions and resolve disputes
- **Review Moderation** - Approve or reject reviews for quality control
- **Brand Management** - Manage fabric brands and suppliers

### Review System
- 5-star rating system for tailors and fabrics
- Verified purchase badges so you know the review is real
- Review titles and detailed customer experiences
- Rating breakdown and stats
- One review per customer per product/tailor
- Ratings calculated automatically
- Vote on helpful reviews

### Tailor/Vendor Features
- Public storefronts
- Custom branding with your own logo, banner, and bio
- Ratings and verification badges
- Portfolio to show off your work
- Link to your social media
- Client testimonials and reviews
- Stats on orders and sales

## Installation

### Prerequisites
- XAMPP (Apache + MySQL + PHP 7.4+)
- Modern web browser
- Git (optional)

### Setup Steps

1. **Clone or download** this repo to your XAMPP htdocs folder:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/
   git clone [repository-url] 25662026_Lab_02_Register_Login
   ```

2. **Start XAMPP** - Fire up Apache and MySQL

3. **Create Database**:
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `shoppn`

4. **Import Database**:
   - Import the base schema: `db/dbforlab.sql`
   - Import reviews and seller stuff: `db/add_reviews_and_seller_features.sql`
   - (Optional) Import some sample products: `db/seed_products.sql`

5. **Configure Database Connection**:
   - Edit `settings/db_cred.php` with your database credentials:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $database = "shoppn";
   ```

6. **Create Uploads Directory**:
   ```bash
   mkdir -p uploads
   chmod 755 uploads
   ```

7. **Access the Application**:
   - Homepage: `http://localhost/25662026_Lab_02_Register_Login/`
   - Login: `http://localhost/25662026_Lab_02_Register_Login/login/login.php`
   - Admin Panel: Login with admin credentials

## Test Accounts

### Buyer Account
- **Email**: testuser@test.com
- **Password**: testpass2A,
- **Role**: Customer (Buyer)

### Admin Account
- **Email**: testadmin@test.com
- **Password**: testpass1A,
- **Role**: Administrator

### Seller Account
- **Email**: testseller@test.com
- **Password**: testpass3A,
- **Role**: Fabric Vendor

### Tailor Account
- **Email**: testtailor@test.com
- **Password**: testtailor1A
- **Role**: Tailor

## Project Structure

```
├── actions/              # Backend API endpoints
│   ├── add_review_action.php
│   ├── get_review_action.php
│   ├── get_seller_products_action.php
│   ├── add_to_cart_action.php
│   ├── login_user_action.php
│   └── ...
├── admin/               # Admin panel pages
│   ├── product.php
│   ├── category.php
│   ├── brand.php
│   └── orders.php
├── classes/             # Database classes
│   ├── product_class.php
│   ├── cart_class.php
│   ├── user_class.php
│   └── ...
├── controllers/         # Business logic controllers
│   ├── product_controller.php
│   ├── cart_controller.php
│   └── ...
├── css/                 # Stylesheets
│   └── app.css
├── db/                  # Database files
│   ├── dbforlab.sql
│   └── add_reviews_and_seller_features.sql
├── js/                  # JavaScript files
│   ├── cart.js
│   ├── product.js
│   └── ...
├── login/               # Authentication pages
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── settings/            # Configuration files
│   ├── db_class.php
│   ├── db_cred.php
│   └── core.php
├── uploads/             # Product images
├── view/                # Frontend pages
│   ├── all_product.php
│   ├── single_product.php
│   ├── seller_profile.php
│   ├── cart.php
│   ├── checkout.php
│   └── ...
└── index.php           # Homepage
```

## Design Features

- **SeamLink Branding** - Custom green theme (#198754) inspired by Ghana's colors
- **Momo Trust Display Font** - Clean, professional typography
- **Kente Pattern Hero** - Celebrating Ghanaian textile heritage
- **Responsive Design** - Works great on mobile for tailors and customers on the go
- **Modern UI** - Clean cards with smooth transitions
- **Star Ratings** - Interactive 5-star reviews for artisans
- **Fashion-Focused Icons** - Tailoring and fabric-specific imagery

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Libraries**: jQuery 3.6.0
- **Server**: Apache (XAMPP)
- **Architecture**: MVC Pattern

## Database Schema

### Main Tables
- `customer` - User accounts (buyers, sellers, admins)
- `products` - Product catalog with ratings and stock
- `categories` - Product categories
- `brands` - Product brands
- `cart` - Shopping cart items
- `orders` - Order records
- `orderdetails` - Order line items

### New Tables (Review System)
- `product_reviews` - Customer product reviews
- `seller_profiles` - Seller store information
- `product_images` - Multiple product images
- `wishlist` - Customer saved items
- `review_votes` - Review helpfulness votes

## Configuration

### Upload Settings
Edit `settings/upload_config.php` to configure image uploads:
- Max file size: 5MB
- Allowed formats: JPG, PNG, GIF, WebP
- Upload directory: `uploads/`

### User Roles
- **1** = Administrator
- **2** = Customer (Buyer)
- **3** = Tailor/Seamstress/Fabric Vendor

## Known Issues

- ~~Fix logout functionality when going back to register page~~ Fixed
- Email notifications aren't hooked up yet
- ~~Multiple product images work on the backend but need frontend work~~

## Recent Updates

### November 10, 2025
- Added complete review system with 5-star ratings
- Implemented seller profile pages
- Added verified purchase detection
- Created automatic rating calculation triggers
- Added stock management columns
- Updated all product queries to include ratings
- Created database views for top products and sellers

## Roadmap

- [ ] Direct messaging between customers and tailors
- [ ] Custom order forms with measurements
- [ ] Image galleries for fabrics
- [ ] Portfolio section for tailors
- [ ] Better search (filter by fabric type, tailor specialty, price)
- [ ] Appointment booking for fittings
- [ ] Payment integration (Mobile Money, cards)
- [ ] Email notifications
- [ ] Mobile apps

---

**Built with love in Ghana** | **SeamLink - Connecting Fashion & Heritage**


