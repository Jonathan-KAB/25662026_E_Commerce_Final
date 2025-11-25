# Image Storage System Documentation

## Overview
This e-commerce platform has a centralized image storage system for products, brands, and users. Everything goes through the `ImageUploadHelper` class to keep things consistent and secure.

## Database Setup

### Running the Migration
Before you can use brand images, run this SQL:

```bash
# Open MySQL/phpMyAdmin and run this:
```

```sql
USE shoppn;

-- Add brand_image column
ALTER TABLE brands 
ADD COLUMN brand_image VARCHAR(100) DEFAULT NULL AFTER brand_name;
```

Or import the file:
```bash
mysql -u root -p shoppn < db/add_brand_image.sql
```

## Directory Structure

Images are organized in a hierarchical structure:

```
uploads/
├── u{user_id}/              # User-specific directory
│   ├── p{product_id}/       # Product images
│   │   └── p123_1234567890_5678.jpg
│   └── b{brand_id}/         # Brand images
│       └── b45_1234567890_9012.png
└── users/                   # Customer profile images
    └── u{customer_id}/
        └── c67_1234567890_3456.jpg
```

## Features

### Image Upload Helper (`classes/image_helper.php`)

One class that handles all image uploads with:

- ✅ **File validation** - Checks type, size, and makes sure it's actually an image
- ✅ **Secure uploads** - Only allows jpg, jpeg, png, gif, webp
- ✅ **Size limits** - Max 5MB per file
- ✅ **Organized storage** - Files are organized by user and item
- ✅ **Unique filenames** - No conflicts or accidental overwrites
- ✅ **Image verification** - Actually checks the file content
- ✅ **Resize capability** - Can resize images if needed

### Supported Image Types

1. **Product Images**
   - Location: `uploads/u{user_id}/p{product_id}/`
   - Filename format: `p{product_id}_{timestamp}_{random}.ext`
   - Database field: `products.product_image`

2. **Brand Images** (NEW!)
   - Location: `uploads/u{user_id}/b{brand_id}/`
   - Filename format: `b{brand_id}_{timestamp}_{random}.ext`
   - Database field: `brands.brand_image`

3. **Customer Images**
   - Location: `uploads/users/u{customer_id}/`
   - Filename format: `c{customer_id}_{timestamp}_{random}.ext`
   - Database field: `customer.customer_image`

## API Endpoints

### Upload Product Image
```php
POST /actions/upload_product_image_action.php
Content-Type: multipart/form-data

Parameters:
- product_id: int (required)
- image: file (required)

Response:
{
  "status": "success",
  "message": "Product image uploaded successfully",
  "path": "uploads/u1/p123/p123_1234567890_5678.jpg"
}
```

### Upload Brand Image (NEW!)
```php
POST /actions/upload_brand_image_action.php
Content-Type: multipart/form-data

Parameters:
- brand_id: int (required)
- image: file (required)

Response:
{
  "status": "success",
  "message": "Brand image uploaded successfully",
  "path": "uploads/u1/b45/b45_1234567890_9012.png"
}
```

## Usage Examples

### PHP - Upload Product Image

```php
require_once 'classes/image_helper.php';

$imageHelper = new ImageUploadHelper();
$result = $imageHelper->uploadProductImage($_FILES['image'], $productId, $userId);

if ($result['success']) {
    // Update database with $result['path']
    update_product_ctr($productId, ['product_image' => $result['path']]);
}
```

### PHP - Upload Brand Image

```php
require_once 'classes/image_helper.php';

$imageHelper = new ImageUploadHelper();
$result = $imageHelper->uploadBrandImage($_FILES['image'], $brandId, $userId);

if ($result['success']) {
    // Update database with $result['path']
    update_brand_image_ctr($brandId, $result['path']);
}
```

### JavaScript - Upload via AJAX

```javascript
// Product image upload
const formData = new FormData();
formData.append('product_id', productId);
formData.append('image', fileInput.files[0]);

fetch('../actions/upload_product_image_action.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.status === 'success') {
        console.log('Uploaded:', data.path);
    }
});

// Brand image upload
const formData = new FormData();
formData.append('brand_id', brandId);
formData.append('image', fileInput.files[0]);

fetch('../actions/upload_brand_image_action.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.status === 'success') {
        console.log('Uploaded:', data.path);
    }
});
```

## Display Images in Views

### Product Image Display

```php
<?php if (!empty($product['product_image'])): ?>
    <img src="../<?= htmlspecialchars($product['product_image']) ?>" 
         alt="<?= htmlspecialchars($product['product_title']) ?>">
<?php else: ?>
    <img src="../uploads/placeholder.jpg" alt="No image">
<?php endif; ?>
```

### Brand Image Display

```php
<?php if (!empty($brand['brand_image'])): ?>
    <img src="../<?= htmlspecialchars($brand['brand_image']) ?>" 
         alt="<?= htmlspecialchars($brand['brand_name']) ?>">
<?php else: ?>
    <div class="brand-placeholder">No logo</div>
<?php endif; ?>
```

## Security Features

1. **Authentication Required** - Only logged-in admins can upload stuff
2. **File Type Validation** - Only images allowed
3. **Size Limits** - 5MB max
4. **MIME Type Checking** - Uses `getimagesize()` to make sure it's really an image
5. **Path Sanitization** - Stops directory traversal attacks
6. **Unique Filenames** - Prevents overwrite attacks

## Error Handling

Common error responses:

```json
{
  "status": "error",
  "message": "Not authorized"
}

{
  "status": "error",
  "message": "Invalid file type. Allowed types: jpg, jpeg, png, gif, webp"
}

{
  "status": "error",
  "message": "File size exceeds maximum allowed size of 5MB"
}

{
  "status": "error",
  "message": "File is not a valid image"
}
```

## Advanced Features

### Image Resizing (Optional)

```php
$imageHelper = new ImageUploadHelper();

// Resize to max 800x800 but keep the aspect ratio
$imageHelper->resizeImage(
    $sourcePath, 
    $destPath, 
    800,  // max width
    800   // max height
);
```

### Delete Image

```php
$imageHelper = new ImageUploadHelper();
$imageHelper->deleteImage($relativePath);
```

## Permissions

Ensure the uploads directory has proper permissions:

```bash
chmod -R 755 uploads/
chown -R www-data:www-data uploads/  # Linux/Apache
# or
chown -R _www:_www uploads/  # macOS/Apache
```

## Troubleshooting

### "Failed to create upload directory"
- Check folder permissions
- Make sure the parent folder is writable
- Check if PHP has `open_basedir` restrictions

### "Failed to move uploaded file"
- Check `upload_tmp_dir` in php.ini
- Check destination folder permissions
- Make sure you have enough disk space

### "File size exceeds maximum"
- Check `upload_max_filesize` in php.ini
- Check `post_max_size` in php.ini
- Or change `$maxFileSize` in the ImageUploadHelper class

## Configuration

Edit `classes/image_helper.php` to customize:

```php
private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
private $maxFileSize = 5242880; // 5MB in bytes
```

## Future Enhancements

- [ ] Auto-generate thumbnails
- [ ] Support multiple images per product
- [ ] Compress/optimize images automatically
- [ ] Cloud storage (S3, Cloudinary, etc.)
- [ ] Image cropping tool
- [ ] Watermarks
- [ ] Convert everything to WebP for better performance

## Support

For issues or questions about the image storage system, refer to:
- `classes/image_helper.php` - Core upload logic
- `actions/upload_product_image_action.php` - Product upload endpoint
- `actions/upload_brand_image_action.php` - Brand upload endpoint
- `db/add_brand_image.sql` - Database migration

---

**Version:** 1.0  
**Last Updated:** November 5, 2025
