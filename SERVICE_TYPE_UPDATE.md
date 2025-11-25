# Service Type Selection Feature

## ✅ Implemented Features

### 1. Registration Page
**File**: `login/register.php`

- Added **Service Type dropdown** that appears when user selects "Vendor" role
- Options available:
  - General Vendor (default)
  - ✂️ Tailor
  - 🪡 Seamstress
  - 👔 General Service Provider
- Field automatically shows/hides based on role selection
- Smooth slide animation using jQuery

### 2. Profile Edit Page
**File**: `view/profile.php`

- Vendors can now **update their service type** from their profile
- Dropdown selector with all service type options
- Only visible to users with vendor role (user_role = 3)
- Saves along with other profile information

### 3. Backend Updates

**Files Modified:**
- `actions/register_user_action.php` - Captures service_type from registration
- `controllers/customer_controller.php` - Added service_type parameter to functions
- `classes/user_class.php` - Updated createUser() and updateCustomer() methods

## How It Works

### During Registration:
1. User picks "Vendor" role
2. Service Type dropdown pops up automatically
3. User picks their service type (or keeps it as "General Vendor")
4. Service type gets saved to the database

### After Registration (Profile Update):
1. Vendor logs in and goes to their Profile page
2. Service Type dropdown is there waiting
3. Can change it whenever they want
4. Hit "Save Changes" to update

### Display on Public Profile:
1. Service type badge shows up on `seller_profile.php?id=X`
2. Has an icon and label (like "✂️ Tailor")
3. Purple badge next to seller name
4. Only shows if service_type isn't 'none'

## Database Structure

The `service_type` column should already exist from your previous SQL:

```sql
ALTER TABLE customer 
ADD COLUMN service_type ENUM('tailor', 'seamstress', 'general', 'none') 
DEFAULT 'none';
```

## Testing Steps

### Test Registration:
1. Go to `/login/register.php`
2. Fill in all fields
3. Select "Vendor" role
4. Notice Service Type dropdown appears
5. Select a service type (e.g., "Tailor")
6. Register and check database

### Test Profile Update:
1. Login as a vendor
2. Go to Profile page
3. You should see "Service Type" dropdown
4. Change to a different service type
5. Click "Save Changes"
6. Verify in database

### Test Public Display:
1. Find a vendor's customer_id in database
2. Visit `view/seller_profile.php?id=X` (replace X with vendor's customer_id)
3. Badge should show next to vendor name

## Service Type Options

| Value | Display | Icon |
|-------|---------|------|
| `none` | General Vendor | (no badge) |
| `tailor` | Tailor | ✂️ |
| `seamstress` | Seamstress | 🪡 |
| `general` | General Service Provider | 👔 |

## Files Modified

1. **Frontend**:
   - `login/register.php` - Added service type selector
   - `view/profile.php` - Added editable service type field
   - `view/seller_profile.php` - Already displays badge (from previous update)

2. **Backend**:
   - `actions/register_user_action.php` - Handles service_type on registration
   - `controllers/customer_controller.php` - Updated function signatures
   - `classes/user_class.php` - Updated database operations

## User Flow

```
NEW USER REGISTRATION
├─ Select "Customer" → No service type field
└─ Select "Vendor" → Service type dropdown appears
   ├─ Choose type or leave as "General Vendor"
   └─ Complete registration → Saved to database

EXISTING VENDOR UPDATE
├─ Login to account
├─ Go to Profile
├─ See Service Type dropdown
├─ Change selection
└─ Save Changes → Updated in database

PUBLIC VIEW
├─ Customer visits seller profile
└─ Sees service type badge (if set)
```

## Notes

- Service type is **optional** - it defaults to 'none' (General Vendor)
- Only **vendors** (user_role = 3) can set or see this
- **Customers** (user_role = 1) never see this field
- Badge only shows up if service_type isn't 'none'
- You can change it anytime from your profile
