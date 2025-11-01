# Production Deployment Guide - Image Storage Fix

## Overview
This guide will walk you through deploying the image storage fixes to your production server at **manschoice.co.ke**.

---

## Prerequisites

Before you begin, make sure you have:
- [ ] SSH access to your production server
- [ ] Git installed on production server
- [ ] Server credentials ready
- [ ] Backup of current production code (optional but recommended)

---

## Step-by-Step Deployment

### Step 1: Connect to Production Server

```bash
# Replace with your actual server details
ssh your-username@your-server-ip

# Example:
# ssh ubuntu@manschoice.co.ke
# OR
# ssh root@123.456.789.10
```

---

### Step 2: Navigate to Backend Directory

```bash
# Common locations:
cd /var/www/manchoice-backend
# OR
cd /var/www/html/manchoice-backend
# OR
cd ~/manchoice-backend

# Verify you're in the right place
pwd
ls -la
# You should see: artisan, composer.json, app/, etc.
```

---

### Step 3: Backup Current Code (Optional)

```bash
# Create a backup of current version
cp -r ../manchoice-backend ../manchoice-backend-backup-$(date +%Y%m%d)

# OR just backup .env file
cp .env .env.backup
```

---

### Step 4: Pull Latest Changes from Git

```bash
# Check current branch
git branch

# Fetch latest changes
git fetch origin

# Pull the updates
git pull origin main

# You should see files being updated:
# - fix-storage.sh
# - QUICK_FIX_IMAGES.md
# - IMAGE_STORAGE_REFERENCE.md
# - STORAGE_FIX_GUIDE.md
# - ENV_SETUP.md
# - .gitignore updates
```

---

### Step 5: Update Environment Configuration

```bash
# Edit .env file
nano .env
# OR
vi .env

# Find or add this line:
FILESYSTEM_DISK=public

# Save and exit:
# For nano: Ctrl+X, then Y, then Enter
# For vi: Press ESC, type :wq, press Enter
```

---

### Step 6: Run the Storage Fix Script

```bash
# Make script executable
chmod +x fix-storage.sh

# Run the fix script
bash fix-storage.sh

# If you get permission errors, try with sudo:
sudo bash fix-storage.sh
```

**What the script does:**
- Creates storage directories
- Creates the storage symlink
- Sets correct permissions (775)
- Assigns correct ownership (www-data for Apache/Nginx)
- Clears Laravel caches

---

### Step 7: Set Correct Ownership

```bash
# For Apache (most common):
sudo chown -R www-data:www-data storage bootstrap/cache

# For Nginx with php-fpm:
sudo chown -R www-data:www-data storage bootstrap/cache

# If using a different user (check with: ps aux | grep php):
# sudo chown -R nginx:nginx storage bootstrap/cache
```

---

### Step 8: Verify Storage Symlink

```bash
# Check if symlink exists
ls -la public/storage

# Expected output:
# lrwxrwxrwx 1 www-data www-data 25 Nov  1 16:00 public/storage -> ../../storage/app/public

# If symlink doesn't exist or is broken, create it:
php artisan storage:link
```

---

### Step 9: Clear All Caches

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### Step 10: Check Permissions

```bash
# Verify storage permissions
ls -la storage/app/public/

# You should see:
drwxrwxr-x (775) for directories
-rw-rw-r-- (664) for files

# Owner should be www-data:www-data

# If permissions are wrong, fix them:
sudo chmod -R 775 storage bootstrap/cache
sudo find storage -type f -exec chmod 664 {} \;
sudo find storage -type d -exec chmod 775 {} \;
```

---

### Step 11: Restart Web Server (Optional)

```bash
# For Apache:
sudo systemctl restart apache2
# OR
sudo service apache2 restart

# For Nginx:
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm  # Adjust PHP version as needed
# OR
sudo service nginx restart
sudo service php8.1-fpm restart
```

---

### Step 12: Test Image Access

```bash
# Test a product image
curl -I https://manschoice.co.ke/storage/products/n5Qtyfur1ECk6HrELgbYefW96B1WONj7XZHHvyXm.jpg

# Expected response:
# HTTP/2 200 OK
# Content-Type: image/jpeg

# If you get 404, check:
# 1. Is the symlink correct? ls -la public/storage
# 2. Are permissions correct? ls -la storage/app/public/
# 3. Does the file exist? ls storage/app/public/products/
```

---

## Step 13: Verify in Browser

Open your browser and test:

### Test 1: Admin Panel
1. Go to: `https://manschoice.co.ke/admin/products`
2. Check if product images are displaying
3. Go to: `https://manschoice.co.ke/admin/loans`
4. Click on a loan and verify all photos display

### Test 2: Direct Image URLs
1. Right-click an image and "Copy image address"
2. Paste URL in browser
3. Image should load successfully

### Test 3: API Endpoint
```bash
# From your local machine:
curl https://manschoice.co.ke/api/products | python3 -m json.tool | grep image_url
```

---

## Troubleshooting

### Problem: Images still showing 404

**Solution 1: Check symlink**
```bash
cd /var/www/manchoice-backend
ls -la public/storage
# If not pointing to ../../storage/app/public, recreate it:
rm -f public/storage
php artisan storage:link
```

**Solution 2: Check file permissions**
```bash
# Files must be readable by web server
ls -la storage/app/public/products/
sudo chmod -R 775 storage
sudo chown -R www-data:www-data storage
```

**Solution 3: Check .htaccess (Apache only)**
```bash
# Make sure public/.htaccess has FollowSymLinks enabled
cat public/.htaccess | grep FollowSymLinks
```

---

### Problem: Permission Denied errors

```bash
# Check who owns the files
ls -la storage/app/public/

# Check what user PHP runs as
ps aux | grep php

# Set correct ownership
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

### Problem: "Link already exists" error

```bash
# Remove existing symlink and recreate
rm public/storage
php artisan storage:link
```

---

### Problem: Web server can't access files

```bash
# Check SELinux status (CentOS/RHEL only)
getenforce

# If SELinux is enabled:
sudo chcon -R -t httpd_sys_rw_content_t storage
sudo setsebool -P httpd_can_network_connect 1
```

---

## Quick Reference Commands

```bash
# One-liner to fix everything:
cd /var/www/manchoice-backend && \
git pull origin main && \
bash fix-storage.sh && \
php artisan config:clear && \
php artisan cache:clear && \
sudo systemctl restart apache2

# Check if images are accessible:
curl -I https://manschoice.co.ke/storage/products/n5Qtyfur1ECk6HrELgbYefW96B1WONj7XZHHvyXm.jpg

# View server error logs:
sudo tail -f /var/log/apache2/error.log
# OR for Nginx:
sudo tail -f /var/log/nginx/error.log
```

---

## After Deployment Checklist

- [ ] Git pulled successfully
- [ ] .env has `FILESYSTEM_DISK=public`
- [ ] Storage symlink exists: `public/storage -> ../../storage/app/public`
- [ ] Permissions set: `storage/` is 775
- [ ] Ownership correct: files owned by `www-data`
- [ ] Caches cleared
- [ ] Web server restarted
- [ ] Product images loading in admin panel
- [ ] Loan document images loading
- [ ] API returning correct image URLs
- [ ] Mobile app displaying images correctly

---

## Important Notes

1. **Don't commit storage files**: The `.gitignore` already excludes actual uploaded files, only directory structure is in git.

2. **Backup before deployment**: Always backup your production database and files before major changes.

3. **Check disk space**: Make sure you have enough disk space for image uploads:
   ```bash
   df -h
   ```

4. **Monitor logs**: After deployment, monitor error logs for any issues:
   ```bash
   sudo tail -f /var/log/apache2/error.log
   sudo tail -f storage/logs/laravel.log
   ```

5. **SSL Certificate**: Ensure your SSL certificate is valid for HTTPS image URLs.

---

## Support Files Included

- `fix-storage.sh` - Automated fix script
- `QUICK_FIX_IMAGES.md` - Quick 3-minute fix guide
- `STORAGE_FIX_GUIDE.md` - Detailed troubleshooting
- `IMAGE_STORAGE_REFERENCE.md` - Complete image storage reference
- `ENV_SETUP.md` - Environment configuration guide

---

## Need Help?

If you encounter issues:

1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Check web server logs: `tail -f /var/log/apache2/error.log`
3. Run: `php artisan config:clear && php artisan cache:clear`
4. Verify symlink: `ls -la public/storage`
5. Test direct file access: `ls storage/app/public/products/`

---

**Deployment Time**: ~10-15 minutes
**Difficulty**: Beginner-Intermediate
**Risk Level**: Low (only affects image display, no database changes)
