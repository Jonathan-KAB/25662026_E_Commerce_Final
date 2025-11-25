<?php
/**
 * Image Upload Helper Class
 * Handles image uploads for products, brands, and users
 */

require_once __DIR__ . '/../settings/upload_config.php';

class ImageUploadHelper
{
    private $uploadsBaseDir;
    private $uploadsWebPath;
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5242880; // 5MB in bytes
    
    public function __construct($baseDir = null)
    {
        // Use configured upload path or provided path
        $this->uploadsBaseDir = $baseDir ?? UPLOAD_BASE_PATH;
        $this->uploadsWebPath = UPLOAD_WEB_PATH;
        
        // Create uploads folder if it doesn't exist yet
        if (!is_dir($this->uploadsBaseDir)) {
            mkdir($this->uploadsBaseDir, 0755, true);
        }
    }
    
    /**
     * Upload product image
     * @param array $file - $_FILES['image']
     * @param int $productId
     * @param int $userId
     * @return array - ['success' => bool, 'path' => string, 'message' => string]
     */
    public function uploadProductImage($file, $productId, $userId)
    {
        return $this->uploadImage($file, 'product', $productId, $userId);
    }
    
    /**
     * Upload brand image
     * @param array $file - $_FILES['image']
     * @param int $brandId
     * @param int $userId
     * @return array - ['success' => bool, 'path' => string, 'message' => string]
     */
    public function uploadBrandImage($file, $brandId, $userId)
    {
        return $this->uploadImage($file, 'brand', $brandId, $userId);
    }
    
    /**
     * Upload customer/user image
     * @param array $file - $_FILES['image']
     * @param int $userId
     * @return array - ['success' => bool, 'path' => string, 'message' => string]
     */
    public function uploadCustomerImage($file, $userId)
    {
        return $this->uploadImage($file, 'customer', $userId, $userId);
    }
    
    /**
     * Generic image upload handler
     * @param array $file
     * @param string $type - 'product', 'brand', or 'customer'
     * @param int $entityId
     * @param int $userId
     * @return array
     */
    private function uploadImage($file, $type, $entityId, $userId)
    {
        // Validate upload
        $validation = $this->validateUpload($file);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'path' => '',
                'message' => $validation['message']
            ];
        }
        
        // Create directory structure
        $prefix = $type === 'product' ? 'p' : ($type === 'brand' ? 'b' : 'c');
        $targetDir = $this->createDirectoryStructure($userId, $prefix, $entityId);
        
        if (!$targetDir) {
            return [
                'success' => false,
                'path' => '',
                'message' => 'Failed to create upload directory. Check permissions on uploads folder.'
            ];
        }
        
        // Generate filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $targetDir . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            error_log("Failed to move file from " . $file['tmp_name'] . " to " . $targetPath);
            error_log("Target dir writable: " . (is_writable($targetDir) ? 'yes' : 'no'));
            return [
                'success' => false,
                'path' => '',
                'message' => 'Failed to move uploaded file. Check directory permissions: ' . $targetDir
            ];
        }
        
        // Set file permissions
        chmod($targetPath, 0644);
        
        // Optional: Resize image if needed
        $this->resizeImage($targetPath, 1200, 1200);
        
        // Return web-accessible path for database storage
        $relativePath = $this->uploadsWebPath . '/u' . $userId . '/' . $prefix . $entityId . '/' . $filename;
        
        return [
            'success' => true,
            'path' => $relativePath,
            'message' => 'Image uploaded successfully'
        ];
    }
    
    /**
     * Validate uploaded file
     * @param array $file
     * @return array - ['valid' => bool, 'message' => string]
     */
    private function validateUpload($file)
    {
        // Check if file was uploaded
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'message' => 'No file uploaded or upload error occurred'
            ];
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return [
                'valid' => false,
                'message' => 'File size exceeds maximum allowed size (5MB)'
            ];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return [
                'valid' => false,
                'message' => 'Invalid file type. Allowed types: ' . implode(', ', $this->allowedExtensions)
            ];
        }
        
        // Check MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return [
                'valid' => false,
                'message' => 'Invalid MIME type'
            ];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Create directory structure for uploads
     * @param int $userId
     * @param string $prefix - 'p' for product, 'b' for brand, 'c' for customer
     * @param int $entityId
     * @return string|false - Directory path or false on failure
     */
    private function createDirectoryStructure($userId, $prefix, $entityId)
    {
        $userDir = $this->uploadsBaseDir . '/u' . $userId;
        $entityDir = $userDir . '/' . $prefix . $entityId;
        
        if (!is_dir($entityDir)) {
            // Try creating with 0777 permissions
            $oldmask = umask(0);
            $result = mkdir($entityDir, 0777, true);
            umask($oldmask);
            
            if (!$result) {
                error_log("Failed to create directory: " . $entityDir);
                error_log("Base dir exists: " . (is_dir($this->uploadsBaseDir) ? 'yes' : 'no'));
                error_log("Base dir writable: " . (is_writable($this->uploadsBaseDir) ? 'yes' : 'no'));
                return false;
            }
            
            // Ensure the directory has proper permissions
            chmod($entityDir, 0777);
            if (is_dir($userDir)) {
                chmod($userDir, 0777);
            }
        }
        
        return $entityDir;
    }
    
    /**
     * Resize image to fit within max dimensions while maintaining aspect ratio
     * @param string $imagePath
     * @param int $maxWidth
     * @param int $maxHeight
     * @return bool
     */
    private function resizeImage($imagePath, $maxWidth, $maxHeight)
    {
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Check if resize is needed
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Create image resource based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($imagePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($imagePath);
                break;
            default:
                return false;
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Keep transparency for PNGs and GIFs
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize
        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $imagePath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $imagePath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $imagePath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($newImage, $imagePath, 90);
                break;
        }
        
        // Clean up
        imagedestroy($source);
        imagedestroy($newImage);
        
        return true;
    }
    
    /**
     * Delete an image file
     * @param string $imagePath - Relative path from project root
     * @return bool
     */
    public function deleteImage($imagePath)
    {
        $fullPath = __DIR__ . '/../' . $imagePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
}
