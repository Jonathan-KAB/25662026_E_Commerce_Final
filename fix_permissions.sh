#!/bin/bash
# Fix permissions for uploads directory on Ashesi server

# Navigate to the public_html directory
cd /home/jonathan.boateng/public_html

# Create uploads directory if it doesn't exist
mkdir -p uploads

# Set permissions to allow web server to write
chmod 777 uploads

# Also fix any existing subdirectories
chmod -R 777 uploads

echo "Permissions fixed for uploads directory"
echo "Directory permissions:"
ls -la | grep uploads
