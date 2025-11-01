# Quick Fix for Broken Images - Production Server

## The Problem
Images showing as broken/404 errors in the admin panel for:
- Loan application documents (bike photos, logbooks, IDs, etc.)
- Product images
- Part request images
- Customer photos

## The Solution (3 Minutes)

### Step 1: SSH into Production Server
```bash
ssh your-user@your-server-ip
cd /var/www/manchoice-backend  # or wherever your Laravel app is
```

### Step 2: Run These Commands
```bash
# Create the storage symlink
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Clear caches
php artisan cache:clear
php artisan config:clear
```

### Step 3: Update .env
Edit your `.env` file and make sure it has:
```env
FILESYSTEM_DISK=public
```

### Step 4: Test
1. Go to `https://manschoice.co.ke/admin/loans`
2. Click on a loan with photos
3. Images should now display correctly

---

## Alternative: Use the Automated Script

I've created a script that does all of this automatically:

```bash
# Upload fix-storage.sh to your server, then:
chmod +x fix-storage.sh
bash fix-storage.sh
```

---

## Still Not Working?

### Check if symlink exists:
```bash
ls -la public/storage
# Should show: public/storage -> ../storage/app/public
```

### Check file permissions:
```bash
ls -la storage/app/public/loan-documents/
# Files should be readable by www-data
```

### Check a specific image URL:
```bash
# Replace with actual filename from database
curl -I https://manschoice.co.ke/storage/loan-documents/LN-2025-001_bike_photo.jpg
# Should return 200 OK, not 404
```

### Check web server error logs:
```bash
# Apache
sudo tail -f /var/log/apache2/error.log

# Nginx
sudo tail -f /var/log/nginx/error.log
```

---

## What This Does

Laravel stores uploaded files in `storage/app/public/` but serves them through `public/storage/`. The symlink connects these two directories so files can be accessed via the web.

Before fix:
- File location: `/var/www/manchoice-backend/storage/app/public/loan-documents/photo.jpg`
- Web access: ❌ 404 Not Found

After fix:
- File location: `/var/www/manchoice-backend/storage/app/public/loan-documents/photo.jpg`
- Symlink: `/var/www/manchoice-backend/public/storage` → `../storage/app/public`
- Web access: ✅ `https://manschoice.co.ke/storage/loan-documents/photo.jpg`

---

## Files Updated Locally

✅ `.env` - Changed `FILESYSTEM_DISK=public`
✅ `fix-storage.sh` - Automated fix script
✅ `STORAGE_FIX_GUIDE.md` - Detailed troubleshooting guide

**Deploy these changes to production and run the script!**
