# User Role System Migration

## Overview
Switched from using service_type to a proper user role system. Now we have separate roles for Fabric Sellers and Service Providers.

## New User Roles
- **Role 1**: Customer (Buyer) - Can browse and purchase products/services
- **Role 2**: Admin - Full system access
- **Role 3**: Fabric Seller - Sells physical products (fabrics, materials, supplies)
- **Role 4**: Service Provider - Offers tailoring/sewing services

## Key Changes

### Database Migration
SQL script that:
1. Creates a `user_roles` reference table
2. Splits existing role 3 vendors into roles 3 and 4 based on their `service_type`
3. Updates all users with `service_type IN ('tailor', 'seamstress', 'general')` to role 4
4. Keeps users with `service_type = 'none'` as role 3

### Files Updated

#### 1. Registration System
**File**: `login/register.php`
- Three role options: Customer (1), Fabric Seller (3), Service Provider (4)
- Service specialty dropdown only appears when role 4 selected
- Specialty options: General, Tailor, Seamstress
- jQuery validates and requires specialty for role 4

#### 2. Seller Pages
**File**: `view/seller_add_product.php`
- Auth check: `$_SESSION['user_role'] != 3 && $_SESSION['user_role'] != 4`
- Detection: `$is_service_provider = ($_SESSION['user_role'] == 4)`
- Purple theme and service-specific labels for role 4
- Green theme and product labels for role 3

**File**: `view/seller_dashboard.php`
- Auth check: `$_SESSION['user_role'] != 3 && $_SESSION['user_role'] != 4`
- Detection: `$is_service_provider = ($_SESSION['user_role'] == 4)`
- Dynamic terminology: "Service Listings", "Bookings", "Revenue" for role 4
- Standard terminology: "Products", "Orders", "Sales" for role 3

**File**: `view/seller_add_brand.php`
- Auth check: `$_SESSION['user_role'] != 3 && $_SESSION['user_role'] != 4`
- Accessible to both fabric sellers and service providers

#### 3. Profile Management
**File**: `view/profile.php`
- Role 4: Shows "Service Provider Settings" with purple theme and service specialty dropdown
- Role 3: Shows "Fabric Seller Account" badge with green theme
- Removed business type toggle (no longer needed - role is permanent)
- Only role 4 can edit service specialty

#### 4. Public Seller Profiles
**File**: `view/seller_profile.php`
- SQL query allows role 3 OR role 4
- Role 4: Purple gradient "Service Provider" badge with specialty icon
- Role 3: Green gradient "Fabric Seller" badge
- Dynamic stats labels: "Service Listings"/"Bookings" vs "Products"/"Sales"
- Dynamic page heading based on seller role

#### 5. Navigation
**File**: `view/includes/menu.php`
- Shows "Seller Dashboard" link for role 3 OR role 4
- Both seller types get same navigation access

**File**: `view/dashboard.php`
- Redirects role 3 OR role 4 to seller_dashboard.php
- Regular customers stay on customer dashboard

#### 6. Authentication
**File**: `actions/login_user_action.php`
- Role 2: Redirect to `admin/category.php`
- Role 3 OR 4: Redirect to `view/seller_dashboard.php`
- Role 1: Redirect to `view/dashboard.php`

#### 7. Product Actions
**File**: `actions/add_product_action.php`
- Auth: Allow admin OR role 3 OR role 4
- Sets seller_id for both role 3 and role 4

## Detection Pattern

### OLD (service_type-based):
```php
$is_service_provider = in_array($service_type, ['tailor', 'seamstress', 'general']);
```

### NEW (role-based):
```php
$is_service_provider = ($_SESSION['user_role'] == 4);
```

## Access Control Pattern

### OLD:
```php
if (!isLoggedIn() || $_SESSION['user_role'] != 3) {
    // Deny access
}
```

### NEW:
```php
if (!isLoggedIn() || ($_SESSION['user_role'] != 3 && $_SESSION['user_role'] != 4)) {
    // Deny access
}
```

## service_type Field Usage

- **Role 3 (Fabric Seller)**: `service_type = 'none'` (we don't use it)
- **Role 4 (Service Provider)**: `service_type IN ('general', 'tailor', 'seamstress')`
  - This tells us what kind of service they offer
  - Shows up on their public profile and service listings
  - Can be changed in profile settings

## UI Theme Differentiation

### Fabric Sellers (Role 3)
- **Color**: Green (`var(--primary)`, `#1db954`)
- **Gradient**: `linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%)`
- **Icon**: `<i class="fas fa-store"></i>`
- **Labels**: Products, Orders, Sales, Stock Quantity

### Service Providers (Role 4)
- **Color**: Purple (`#7c3aed`, `#9b87f5`)
- **Gradient**: `linear-gradient(135deg, #9b87f5 0%, #7c3aed 100%)`
- **Icons**: `<i class="fas fa-scissors"></i>`, `<i class="fas fa-user-tie"></i>`, `<i class="fas fa-cut"></i>`
- **Labels**: Services, Bookings, Revenue, Turnaround Time

## Benefits of New System

1. **Clear Separation**: Roles tell you exactly what someone can do
2. **Simpler Logic**: Just check `user_role == 4` instead of `in_array(service_type, [...])`
3. **Better Access Control**: Role-based permissions are way cleaner
4. **Scalability**: Easy to add new roles later (like role 5 for wholesalers)
5. **Correct Terminology**: Service providers never see "products", fabric sellers never see "services"
6. **Permanent Roles**: Users can't accidentally switch between being a seller and service provider

## Testing Checklist

- [ ] New user registration with role 3 (Fabric Seller)
- [ ] New user registration with role 4 (Service Provider)
- [ ] Login redirect for role 3
- [ ] Login redirect for role 4
- [ ] Seller dashboard display for role 3
- [ ] Seller dashboard display for role 4
- [ ] Add product form for role 3 (product terminology)
- [ ] Add product form for role 4 (service terminology)
- [ ] Profile editing for role 3 (no service type)
- [ ] Profile editing for role 4 (with service specialty)
- [ ] Public seller profile for role 3 (green theme)
- [ ] Public seller profile for role 4 (purple theme)
- [ ] Menu navigation for both seller types
- [ ] Product listing badges (service vs product)

## Migration SQL

Run the SQL script to:
1. Create the user_roles reference table
2. Update existing vendors based on their service_type
3. Double check all role assignments are right

---

**Migration Completed**: All service_type checks swapped out for user_role checks
**Date**: 2024
**Status**: ✅ Done
