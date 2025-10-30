# Quick Deployment Checklist for manschoice.co.ke

## CRITICAL - Update Before Deployment

### 1. Database Credentials
- [ ] Update `DB_PASSWORD` with strong production password
- [ ] Create database: `mans_choice_db`
- [ ] Create user: `manchoice_user` with proper password

### 2. Redis Configuration
- [ ] Install Redis server
- [ ] Set Redis password in `/etc/redis/redis.conf`
- [ ] Update `REDIS_PASSWORD` in .env file

### 3. Email Configuration
- [ ] Update `MAIL_USERNAME` with actual email
- [ ] Update `MAIL_PASSWORD` with SMTP password/app password
- [ ] Verify sender address: `noreply@manschoice.co.ke`

### 4. M-PESA Configuration (MOST CRITICAL)
- [ ] Obtain production M-PESA credentials from Safaricom
- [ ] Update `MPESA_CONSUMER_KEY`
- [ ] Update `MPESA_CONSUMER_SECRET`
- [ ] Update `MPESA_SHORTCODE` (Paybill number)
- [ ] Update `MPESA_PASSKEY`
- [ ] Test in sandbox first (MPESA_ENV=sandbox)
- [ ] Switch to production: `MPESA_ENV=production`
- [ ] Verify callback URLs are publicly accessible

### 5. SSL Certificate
- [ ] Install SSL certificate for manschoice.co.ke
- [ ] Configure auto-renewal
- [ ] Test HTTPS access

### 6. Application Security
- [ ] Verify `APP_DEBUG=false`
- [ ] Verify `APP_ENV=production`
- [ ] Generate new `APP_KEY` if needed: `php artisan key:generate`
- [ ] Review all .env passwords are strong

### 7. Server Configuration
- [ ] Set correct file permissions (755/644)
- [ ] Make storage writable: `chmod -R 775 storage bootstrap/cache`
- [ ] Configure Nginx/Apache with provided config
- [ ] Setup firewall (allow 80, 443, 22)

### 8. Database
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed initial data if needed
- [ ] Setup automated backups

### 9. Performance Optimization
- [ ] Run: `php artisan config:cache`
- [ ] Run: `php artisan route:cache`
- [ ] Run: `php artisan view:cache`
- [ ] Run: `composer install --optimize-autoloader --no-dev`

### 10. Background Services
- [ ] Setup queue worker service
- [ ] Setup cron for scheduled tasks
- [ ] Verify queue worker is running

## Testing After Deployment

- [ ] Test API health endpoint: `https://manschoice.co.ke/up`
- [ ] Test CORS from frontend domain
- [ ] Test user authentication/registration
- [ ] Test M-PESA integration (sandbox first)
- [ ] Verify email sending works
- [ ] Check error logs for issues
- [ ] Test all critical API endpoints

## Configuration Files Updated

✅ `.env` - Domain and production settings configured
✅ `config/cors.php` - CORS settings for manschoice.co.ke
✅ `config/app.php` - Timezone set to Africa/Nairobi

## Critical URLs

- Frontend: https://manschoice.co.ke
- API Base: https://manschoice.co.ke/api
- Health Check: https://manschoice.co.ke/up
- M-PESA Callback: https://manschoice.co.ke/api/mpesa/callback
- M-PESA Timeout: https://manschoice.co.ke/api/mpesa/timeout
- M-PESA Result: https://manschoice.co.ke/api/mpesa/result

## Need Help?

Refer to `DEPLOYMENT_GUIDE.md` for detailed instructions.

---
**Domain:** manschoice.co.ke
**Last Updated:** 2025-10-31
