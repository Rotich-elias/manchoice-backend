# Quick Deployment Commands

## One-Command Deployment

```bash
# Connect to production and run everything in one go:
ssh your-user@your-server-ip "cd /var/www/manchoice-backend && git pull origin main && bash fix-storage.sh && php artisan config:clear && php artisan cache:clear && sudo systemctl restart apache2"
```

---

## Step-by-Step Commands

### 1. Connect to Server
```bash
ssh your-user@your-server-ip
```

### 2. Navigate to Backend
```bash
cd /var/www/manchoice-backend
# OR wherever your backend is located
```

### 3. Pull Latest Code
```bash
git pull origin main
```

### 4. Update .env (if needed)
```bash
nano .env
# Add or update: FILESYSTEM_DISK=public
```

### 5. Run Storage Fix
```bash
chmod +x fix-storage.sh
bash fix-storage.sh
```

### 6. Fix Permissions (if needed)
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 7. Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 8. Optimize (optional)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9. Restart Web Server
```bash
# Apache:
sudo systemctl restart apache2

# Nginx:
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
```

### 10. Test Images
```bash
curl -I https://manschoice.co.ke/storage/products/n5Qtyfur1ECk6HrELgbYefW96B1WONj7XZHHvyXm.jpg
# Should return: HTTP/2 200 OK
```

---

## Common Server Locations

Your backend might be in one of these locations:

```bash
/var/www/manchoice-backend
/var/www/html/manchoice-backend
/home/your-user/manchoice-backend
/opt/manchoice-backend
~/manchoice-backend
```

To find it:
```bash
find /var/www -name "artisan" -type f 2>/dev/null
find /home -name "artisan" -type f 2>/dev/null
```

---

## Troubleshooting Commands

### Check if storage symlink exists:
```bash
ls -la public/storage
# Should show: public/storage -> ../../storage/app/public
```

### Recreate symlink if broken:
```bash
rm public/storage
php artisan storage:link
```

### Check permissions:
```bash
ls -la storage/app/public/
```

### Check web server user:
```bash
ps aux | grep -E 'apache|nginx|php'
```

### View error logs:
```bash
# Laravel logs:
tail -f storage/logs/laravel.log

# Apache logs:
sudo tail -f /var/log/apache2/error.log

# Nginx logs:
sudo tail -f /var/log/nginx/error.log
```

### Test if files are readable:
```bash
ls -la storage/app/public/products/
ls -la storage/app/public/loan-documents/
```

### Check disk space:
```bash
df -h
```

---

## What to Check After Deployment

1. **Admin Panel**: https://manschoice.co.ke/admin/products
2. **Loan Images**: https://manschoice.co.ke/admin/loans
3. **API Response**:
   ```bash
   curl https://manschoice.co.ke/api/products | grep image_url
   ```
4. **Direct Image**: https://manschoice.co.ke/storage/products/[filename].jpg

---

## Emergency Rollback

If something goes wrong:

```bash
# Restore from backup:
cd /var/www
mv manchoice-backend manchoice-backend-broken
mv manchoice-backend-backup-YYYYMMDD manchoice-backend

# Restart web server:
sudo systemctl restart apache2
```

---

## Server Information You'll Need

Fill this out before deploying:

- **Server IP**: ___________________________
- **SSH Username**: ________________________
- **Backend Location**: _____________________
- **Web Server**: Apache / Nginx (circle one)
- **PHP Version**: __________________________

---

**Total Time**: 5-10 minutes
**Difficulty**: Easy
