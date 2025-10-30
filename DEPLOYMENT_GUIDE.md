# Man's Choice Enterprise - Deployment Guide
## Domain: manschoice.co.ke

## Pre-Deployment Checklist

### 1. Server Requirements
- PHP >= 8.2
- MySQL >= 8.0 or MariaDB >= 10.3
- Redis Server (for caching and sessions)
- Composer
- Node.js & NPM (for asset compilation)
- SSL Certificate (Let's Encrypt recommended)

### 2. Required PHP Extensions
```bash
php -m | grep -E 'PDO|mbstring|tokenizer|xml|ctype|json|bcmath|openssl|fileinfo|redis'
```

Ensure these extensions are installed:
- PDO
- mbstring
- tokenizer
- xml
- ctype
- json
- bcmath
- openssl
- fileinfo
- redis (phpredis extension)

## Environment Configuration

### Critical Environment Variables to Update

#### 1. Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mans_choice_db
DB_USERNAME=manchoice_user
DB_PASSWORD=StrongPass123!
```
**Action Required:** Update DB_PASSWORD with a strong production password.

#### 2. Redis Configuration
```env
REDIS_PASSWORD=your-redis-password-here
```
**Action Required:** Set a strong Redis password in your Redis server config and update this value.

#### 3. Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@manschoice.co.ke"
```
**Action Required:**
- Update MAIL_USERNAME with your actual email
- Update MAIL_PASSWORD with Gmail App Password (not regular password)
- Or use alternative SMTP service (e.g., SendGrid, Mailgun, AWS SES)

#### 4. M-PESA Configuration (CRITICAL)
```env
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=your_consumer_key_here
MPESA_CONSUMER_SECRET=your_consumer_secret_here
MPESA_SHORTCODE=your_paybill_number
MPESA_PASSKEY=your_passkey_here
```
**Action Required:**
1. Obtain production M-PESA credentials from Safaricom
2. Update all MPESA_* variables with production values
3. Change MPESA_ENV=production when ready to go live
4. Test in sandbox environment first!

## Deployment Steps

### Step 1: Server Setup

#### Install Dependencies
```bash
# Install PHP and extensions
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
  php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-redis

# Install Redis
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### Configure Redis
```bash
sudo nano /etc/redis/redis.conf
```
Add/Update:
```
requirepass your-redis-password-here
bind 127.0.0.1
```
Restart Redis:
```bash
sudo systemctl restart redis-server
```

### Step 2: Deploy Application Files

```bash
# Upload files to server (or use git clone)
cd /var/www/manschoice.co.ke

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Set correct permissions
sudo chown -R www-data:www-data /var/www/manschoice.co.ke
sudo chmod -R 755 /var/www/manschoice.co.ke
sudo chmod -R 775 /var/www/manschoice.co.ke/storage
sudo chmod -R 775 /var/www/manschoice.co.ke/bootstrap/cache
```

### Step 3: Configure Web Server

#### Nginx Configuration (Recommended)
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name manschoice.co.ke www.manschoice.co.ke;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name manschoice.co.ke www.manschoice.co.ke;

    root /var/www/manschoice.co.ke/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/manschoice.co.ke/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/manschoice.co.ke/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';" always;

    # Logs
    access_log /var/log/nginx/manschoice.access.log;
    error_log /var/log/nginx/manschoice.error.log;

    # Client body size (for file uploads)
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Save to: `/etc/nginx/sites-available/manschoice.co.ke`

```bash
sudo ln -s /etc/nginx/sites-available/manschoice.co.ke /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 4: SSL Certificate

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d manschoice.co.ke -d www.manschoice.co.ke

# Auto-renewal is configured automatically
```

### Step 5: Database Setup

```bash
# Create database and user
mysql -u root -p

CREATE DATABASE mans_choice_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'manchoice_user'@'localhost' IDENTIFIED BY 'StrongPass123!';
GRANT ALL PRIVILEGES ON mans_choice_db.* TO 'manchoice_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
cd /var/www/manschoice.co.ke
php artisan migrate --force

# Optional: Seed initial data (if needed)
# php artisan db:seed --force
```

### Step 6: Optimize Application

```bash
# Clear and cache configuration
php artisan config:clear
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload -o
```

### Step 7: Setup Queue Worker (Background Jobs)

```bash
# Create systemd service
sudo nano /etc/systemd/system/manschoice-worker.service
```

Add:
```ini
[Unit]
Description=Man's Choice Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/manschoice.co.ke/artisan queue:work database --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable manschoice-worker
sudo systemctl start manschoice-worker
sudo systemctl status manschoice-worker
```

### Step 8: Setup Scheduled Tasks (Cron)

```bash
sudo crontab -e -u www-data
```

Add:
```cron
* * * * * cd /var/www/manschoice.co.ke && php artisan schedule:run >> /dev/null 2>&1
```

## Post-Deployment Verification

### 1. Test API Endpoints
```bash
# Health check
curl https://manschoice.co.ke/up

# Test CORS
curl -H "Origin: https://manschoice.co.ke" \
     -H "Access-Control-Request-Method: POST" \
     -H "Access-Control-Request-Headers: X-Requested-With" \
     -X OPTIONS https://manschoice.co.ke/api/customers \
     --verbose
```

### 2. Verify Configuration
```bash
php artisan config:show

# Check specific configs
php artisan config:show app.url
php artisan config:show app.env
php artisan config:show app.debug
```

### 3. Test M-PESA Integration
- Start with sandbox environment
- Test STK Push functionality
- Verify callback URLs are accessible
- Test payment validation

### 4. Monitor Logs
```bash
# Laravel logs
tail -f /var/www/manschoice.co.ke/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/manschoice.error.log

# Queue worker logs
sudo journalctl -u manschoice-worker -f
```

## Security Checklist

- [ ] APP_DEBUG=false in production
- [ ] Strong APP_KEY is set
- [ ] Strong database password
- [ ] Redis password configured
- [ ] SSL certificate installed and auto-renewal working
- [ ] File permissions set correctly (755 for directories, 644 for files)
- [ ] Storage and bootstrap/cache writable by web server
- [ ] .env file not accessible from web (Laravel handles this)
- [ ] Security headers configured in Nginx
- [ ] Firewall configured (allow 80, 443, 22 only)
- [ ] Regular backups configured
- [ ] M-PESA credentials secured

## Firewall Configuration

```bash
# UFW setup
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

## Backup Strategy

### Database Backup
```bash
# Create backup script
sudo nano /usr/local/bin/backup-manschoice-db.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/manschoice"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

mysqldump -u manchoice_user -p'StrongPass123!' mans_choice_db | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +7 -delete
```

```bash
chmod +x /usr/local/bin/backup-manschoice-db.sh

# Add to crontab
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-manschoice-db.sh
```

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check storage permissions: `sudo chmod -R 775 storage bootstrap/cache`
   - Check logs: `tail -f storage/logs/laravel.log`

2. **CORS Errors**
   - Verify CORS_ALLOWED_ORIGINS in .env
   - Clear config: `php artisan config:clear`
   - Check Nginx headers

3. **Session Issues**
   - Verify SESSION_DOMAIN=.manschoice.co.ke
   - Verify Redis is running: `redis-cli ping`
   - Check Redis password

4. **M-PESA Callbacks Failing**
   - Ensure firewall allows incoming connections
   - Verify callback URLs are publicly accessible
   - Check M-PESA IP whitelist

## Monitoring

### Setup Log Monitoring
Consider installing:
- Sentry for error tracking
- Laravel Telescope for debugging (disable in production)
- New Relic or similar APM tool

### Performance Monitoring
```bash
# Enable OPcache
sudo nano /etc/php/8.2/fpm/php.ini

# Add/Update:
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2

sudo systemctl restart php8.2-fpm
```

## Maintenance Mode

```bash
# Enable maintenance mode
php artisan down --secret="your-secret-token"

# Access site during maintenance
# Visit: https://manschoice.co.ke/your-secret-token

# Disable maintenance mode
php artisan up
```

## Rollback Plan

1. Keep previous version backup
2. Database rollback:
   ```bash
   php artisan migrate:rollback
   ```
3. Restore from backup if needed
4. Clear all caches after rollback

## Support Contacts

- Domain Registrar: [Your domain provider]
- Hosting Provider: [Your hosting provider]
- M-PESA Support: Safaricom Business Support

## Updates and Maintenance

### Regular Tasks
- Weekly: Review error logs
- Monthly: Update dependencies (`composer update`)
- Monthly: Review and rotate logs
- Quarterly: Security audit
- As needed: Laravel framework updates

---

**Document Version:** 1.0
**Last Updated:** 2025-10-31
**Domain:** manschoice.co.ke
