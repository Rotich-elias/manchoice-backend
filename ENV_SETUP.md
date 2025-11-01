# Environment Configuration Guide

This guide explains how to switch between local development and production environments.

## The Problem

The 419 "Page Expired" error on localhost was caused by the `SESSION_DOMAIN` being set to `.manschoice.co.ke` in the `.env` file. Browsers reject cookies for localhost when they're configured for a different domain, preventing CSRF token validation.

## Solution

We've created separate environment configuration files:

- **`.env`** - Currently active environment (auto-switched by script)
- **`.env.local`** - Local development settings
- **`.env.production.backup`** - Production settings backup

### Key Differences Between Environments

| Setting | Local | Production |
|---------|-------|------------|
| `APP_ENV` | local | production |
| `APP_DEBUG` | true | false |
| `APP_URL` | http://localhost:8000 | https://manschoice.co.ke |
| `SESSION_DOMAIN` | null | .manschoice.co.ke |
| `FRONTEND_URL` | http://localhost:3000 | https://manschoice.co.ke |
| `SANCTUM_STATEFUL_DOMAINS` | localhost,127.0.0.1 | manschoice.co.ke |
| `CORS_ALLOWED_ORIGINS` | http://localhost:* | https://manschoice.co.ke |
| `LOG_LEVEL` | debug | error |

## How to Switch Environments

### Switch to Local Development
```bash
./switch-env.sh local
```

### Switch to Production
```bash
./switch-env.sh production
```

The script will automatically:
1. Copy the appropriate environment file to `.env`
2. Create a backup of the previous `.env`
3. Clear all Laravel caches
4. Show the current environment settings

## After Switching

1. **Restart your development server** (if running)
2. **Clear your browser cookies** for localhost
3. Try logging in again

## Manual Configuration

If you prefer to manually edit the `.env` file, the critical setting for localhost is:

```env
SESSION_DOMAIN=null
```

After any manual `.env` changes, always run:
```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

## Troubleshooting

### Still getting 419 errors?
1. Clear Laravel cache: `php artisan optimize:clear`
2. Clear browser cookies for localhost
3. Restart your development server
4. Check that `SESSION_DOMAIN=null` in your `.env`

### Session table issues?
```bash
php artisan migrate
```

### Can't access sessions table?
```bash
php artisan tinker
# Then run: \Schema::hasTable('sessions')
```

## Important Notes

- **Never commit `.env` files** to version control
- The `.env.production.backup` is automatically created when switching to local
- Always switch back to production before deploying
- Keep your `.env.local` updated with any new settings added to `.env`
