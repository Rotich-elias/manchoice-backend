# Storage and Image Upload Fix Guide

## Problem
Uploaded images (loan documents, customer photos, etc.) are showing as broken images with 404 errors in the backend admin panel.

## Root Cause
The issue occurs because Laravel stores uploaded files in `storage/app/public/` but serves them through `public/storage/`. This requires a symbolic link that may be missing or broken on your production server.

## How Laravel File Storage Works

1. **Files are uploaded to**: `storage/app/public/loan-documents/filename.jpg`
2. **Symlink required**: `public/storage` → `storage/app/public`
3. **Files are accessed via**: `https://manschoice.co.ke/storage/loan-documents/filename.jpg`

## Solution

### Option 1: Automated Fix Script (Recommended)

I've created a script that automatically fixes all storage issues.

#### On Production Server:

```bash
# 1. SSH into your production server
ssh your-user@manschoice.co.ke

# 2. Navigate to your Laravel directory
cd /path/to/manchoice-backend

# 3. Upload the fix-storage.sh script (if not already there)
# You can use scp, ftp, or your hosting control panel

# 4. Make the script executable
chmod +x fix-storage.sh

# 5. Run the script
bash fix-storage.sh

# If you get permission errors, try with sudo:
sudo bash fix-storage.sh
```

### Option 2: Manual Fix

If you prefer to fix it manually, follow these steps:

#### Step 1: Create Storage Directories

```bash
cd /path/to/manchoice-backend
mkdir -p storage/app/public/loan-documents
mkdir -p storage/app/public/customer-photos
mkdir -p storage/app/public/product-images
```

#### Step 2: Create Symbolic Link

```bash
# Remove old symlink if it exists
rm -f public/storage

# Create new symlink using Laravel
php artisan storage:link

# Verify the symlink was created
ls -la public/storage
```

#### Step 3: Set Proper Permissions

```bash
# Set permissions for web server access
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Set ownership (adjust www-data based on your server configuration)
# For Apache/Nginx on Ubuntu/Debian:
sudo chown -R www-data:www-data storage bootstrap/cache

# For other setups, replace www-data with your web server user:
# sudo chown -R nginx:nginx storage bootstrap/cache
# OR
# sudo chown -R apache:apache storage bootstrap/cache
```

#### Step 4: Update .env File

Make sure your `.env` file on production has these settings:

```env
APP_URL=https://manschoice.co.ke
FILESYSTEM_DISK=public
```

#### Step 5: Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Verification Steps

After applying the fix:

1. **Check Symlink**:
   ```bash
   ls -la public/storage
   # Should show: public/storage -> ../storage/app/public
   ```

2. **Test File Access**:
   ```bash
   echo "test" > storage/app/public/test.txt
   cat public/storage/test.txt
   # Should display: test
   rm storage/app/public/test.txt
   ```

3. **Check in Browser**:
   - Log into admin panel at `https://manschoice.co.ke/admin`
   - View a loan application with photos
   - Images should now display correctly

4. **Test Upload**:
   - Try uploading a new loan application with photos
   - Verify the photos display correctly after upload

## Common Issues and Solutions

### Issue 1: Permission Denied

**Error**: `Permission denied` when creating symlink

**Solution**:
```bash
sudo bash fix-storage.sh
# OR
sudo php artisan storage:link
```

### Issue 2: Symlink Already Exists

**Error**: `symlink(): File exists`

**Solution**:
```bash
rm public/storage
php artisan storage:link
```

### Issue 3: Wrong Ownership

**Error**: Images still not loading after symlink creation

**Solution**:
```bash
# Find your web server user
ps aux | grep -E 'apache|nginx'

# Set correct ownership (replace www-data with your web server user)
sudo chown -R www-data:www-data storage public/storage
sudo chmod -R 775 storage public/storage
```

### Issue 4: SELinux Issues (CentOS/RHEL)

If you're on CentOS/RHEL with SELinux enabled:

```bash
# Set correct SELinux context
sudo chcon -R -t httpd_sys_rw_content_t storage/
sudo chcon -R -t httpd_sys_rw_content_t public/storage

# OR disable SELinux (not recommended for production)
sudo setenforce 0
```

### Issue 5: Cloudflare or CDN Caching

If you use Cloudflare or another CDN:

1. Purge your CDN cache
2. Create a page rule to bypass cache for `/storage/*` paths

## File Structure Reference

After the fix, your file structure should look like this:

```
manchoice-backend/
├── public/
│   ├── storage/          → (symlink to ../storage/app/public)
│   ├── index.php
│   └── ...
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   ├── loan-documents/
│   │   │   │   ├── LN-2025-001_bike_photo_12345.jpg
│   │   │   │   └── ...
│   │   │   ├── customer-photos/
│   │   │   └── product-images/
│   │   └── private/
│   ├── logs/
│   └── framework/
└── ...
```

## How Files Are Accessed

| Database Path | Storage Location | Public URL |
|--------------|------------------|------------|
| `loan-documents/bike.jpg` | `storage/app/public/loan-documents/bike.jpg` | `https://manschoice.co.ke/storage/loan-documents/bike.jpg` |
| `customer-photos/photo.jpg` | `storage/app/public/customer-photos/photo.jpg` | `https://manschoice.co.ke/storage/customer-photos/photo.jpg` |

## Preventing Future Issues

### On Local Development
```bash
# Always run after cloning the repository
php artisan storage:link
```

### On Production Deployment
Add to your deployment script:
```bash
php artisan storage:link
php artisan cache:clear
php artisan config:clear
```

### Using Deployment Tools

#### Laravel Forge
Add to your deployment script:
```bash
php artisan storage:link --force
```

#### Laravel Envoyer
Add as a deployment hook:
```bash
php artisan storage:link
```

## Need Help?

If images are still not displaying after following this guide:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server error logs:
   - Apache: `/var/log/apache2/error.log`
   - Nginx: `/var/log/nginx/error.log`
3. Verify file permissions: `ls -la storage/app/public/loan-documents/`
4. Test direct URL access: `https://manschoice.co.ke/storage/loan-documents/filename.jpg`

## Summary

The fix involves:
1. ✅ Updated `.env` to use `FILESYSTEM_DISK=public`
2. ✅ Created `fix-storage.sh` script
3. ✅ Created this comprehensive guide

**Next Step**: Run the `fix-storage.sh` script on your production server.
