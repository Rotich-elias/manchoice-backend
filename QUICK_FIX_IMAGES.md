# Quick Fixes - Admin Panel Issues

## Table of Contents
1. [Fix: Mobile Responsive Buttons (Admin Panel)](#fix-mobile-responsive-buttons)
2. [Fix: Broken Images (Production Server)](#fix-broken-images-production-server)

---

## Fix: Mobile Responsive Buttons

### The Problem
When admins logged into the admin panel on mobile web browsers, the Approve/Reject buttons for payments, loans, and deposits were not visible or not clickable.

### Files Changed
- `resources/views/admin/payments.blade.php` (lines 129-142)
- `resources/views/admin/loans.blade.php` (lines 132-145)
- `resources/views/admin/loan-detail.blade.php` (lines 57-72, 786-801)
- `resources/views/admin/deposits.blade.php` (lines 143-152)

### The Solution
Updated button layouts to be mobile-responsive using Tailwind CSS:

**Before:**
```html
<div class="flex gap-2">
    <button class="bg-green-500">Approve</button>
    <button class="bg-red-500">Reject</button>
</div>
```

**After:**
```html
<div class="flex flex-col sm:flex-row gap-2">
    <form class="w-full sm:w-auto">
        <button class="w-full bg-green-500">Approve</button>
    </form>
    <button class="w-full sm:w-auto bg-red-500">Reject</button>
</div>
```

### What Changed
- Buttons stack vertically on mobile (below 640px width)
- Buttons take full width on mobile for easier tapping
- Buttons display side-by-side on desktop/tablet
- Applied to: Payment approvals, Loan approvals, Deposit verifications

### Status
✅ **COMPLETED** - All admin action buttons are now mobile-friendly

---

## Fix: Broken Images (Production Server)

### The Problem
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

### Step 2: Update .env First
Edit your `.env` file and make sure it has:
```env
FILESYSTEM_DISK=public
APP_URL=https://manschoice.co.ke
```

**Important:** The `APP_URL` must match your actual domain for images to work correctly!

### Step 3: Run These Commands (No Sudo Required)
```bash
# Remove old symlink if it exists
rm -f public/storage

# Create the storage symlink
php artisan storage:link

# Set permissions (only if you own the files)
chmod -R 755 storage bootstrap/cache

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan config:cache
```

### Step 4: Test
1. Go to `https://manschoice.co.ke/admin/loans`
2. Click on a loan with photos
3. Images should now display correctly

---

## Why This Approach is Safer

✅ **No sudo commands** - Avoids security risks of running commands as root
✅ **Uses symlinks** - Laravel's recommended approach for serving storage files
✅ **APP_URL config** - Ensures URLs are generated correctly
✅ **Permission 755** - Safe permissions that work with most hosting setups

If your web server runs as a different user (like www-data) and you get permission errors, contact your hosting provider to fix permissions - don't use sudo yourself.

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

### Verify APP_URL is correct:
```bash
# Check your .env file
grep APP_URL .env
# Should show: APP_URL=https://manschoice.co.ke
```

### Check web server error logs (if you have access):
```bash
# Apache (without sudo - if you have read access)
tail -f /var/log/apache2/error.log

# Nginx (without sudo - if you have read access)
tail -f /var/log/nginx/error.log

# If you don't have access, ask your hosting provider
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

## Quick Summary

**Safe Approach (No Sudo Required):**
1. Set `APP_URL=https://manschoice.co.ke` in .env
2. Set `FILESYSTEM_DISK=public` in .env
3. Run `php artisan storage:link`
4. Run `chmod -R 755 storage bootstrap/cache`
5. Clear caches

**Or use the automated script:** `bash fix-storage.sh`

The script now:
- Uses NO sudo commands
- Uses safe 755 permissions
- Verifies APP_URL and FILESYSTEM_DISK settings
- Works with most hosting environments
