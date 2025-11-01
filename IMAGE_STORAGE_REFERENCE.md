# Image Storage Reference - Man's Choice Backend

## Overview
All uploaded images in the Man's Choice system are stored in `storage/app/public/` and accessed via a symlink at `public/storage/`. This document provides a complete reference of all image types and their storage locations.

## Storage Directories

### 1. Loan Documents (`storage/app/public/loan-documents/`)

Stores all documentation for loan applications:

| Field Name | Example Filename | Description |
|-----------|------------------|-------------|
| `bike_photo_path` | `LN-2025-001_bike_photo_1234567.jpg` | Customer's motorcycle photo |
| `logbook_photo_path` | `LN-2025-001_logbook_photo_1234567.jpg` | Motorcycle logbook/registration |
| `passport_photo_path` | `LN-2025-001_passport_photo_1234567.jpg` | Customer passport photo |
| `id_photo_front_path` | `LN-2025-001_id_photo_front_1234567.jpg` | Customer ID front |
| `id_photo_back_path` | `LN-2025-001_id_photo_back_1234567.jpg` | Customer ID back |
| `next_of_kin_id_front_path` | `LN-2025-001_next_of_kin_id_front_1234567.jpg` | Next of kin ID front |
| `next_of_kin_id_back_path` | `LN-2025-001_next_of_kin_id_back_1234567.jpg` | Next of kin ID back |
| `next_of_kin_passport_photo_path` | `LN-2025-001_next_of_kin_passport_photo_1234567.jpg` | Next of kin passport photo |
| `guarantor_id_front_path` | `LN-2025-001_guarantor_id_front_1234567.jpg` | Guarantor ID front |
| `guarantor_id_back_path` | `LN-2025-001_guarantor_id_back_1234567.jpg` | Guarantor ID back |
| `guarantor_passport_photo_path` | `LN-2025-001_guarantor_passport_photo_1234567.jpg` | Guarantor passport photo |
| `guarantor_bike_photo_path` | `LN-2025-001_guarantor_bike_photo_1234567.jpg` | Guarantor's motorcycle |
| `guarantor_logbook_photo_path` | `LN-2025-001_guarantor_logbook_photo_1234567.jpg` | Guarantor's logbook |

**Controller**: `app/Http/Controllers/API/LoanController.php`
**Upload Method**: `$file->storeAs('loan-documents', $filename, 'public')`
**Access URL**: `https://manschoice.co.ke/storage/loan-documents/filename.jpg`

### 2. Product Images (`storage/app/public/products/`)

Stores product catalog images for the shop:

| Field Name | Example Filename | Description |
|-----------|------------------|-------------|
| `image_path` | `products/x8Y3kL9mN2pQrTvWz4Ab.jpg` | Product photo for catalog |

**Controller**: `app/Http/Controllers/API/ProductController.php`
**Model**: `app/Models/Product.php`
**Upload Method**: `$image->store('products', 'public')`
**Access URL**: `https://manschoice.co.ke/storage/products/filename.jpg`
**Accessor**: `getImageUrlAttribute()` returns `asset('storage/' . $image_path)`

**Used In**:
- Admin panel: `/admin/products`
- Flutter app: Shop screen, Products screen, Cart screen
- API endpoint: `/api/products`

### 3. Part Request Images (`storage/app/public/part-requests/`)

Stores images uploaded with spare part requests:

| Field Name | Example Filename | Description |
|-----------|------------------|-------------|
| `image_path` | `part_request_1234567890.jpg` | Photo of requested spare part |

**Controller**: `app/Http/Controllers/API/PartRequestController.php`
**Model**: `app/Models/PartRequest.php`
**Upload Method**: `$image->storeAs('part-requests', $filename, 'public')`
**Access URL**: `https://manschoice.co.ke/storage/part-requests/filename.jpg`

**Used In**:
- Admin panel: `/admin/part-requests`
- Flutter app: Part requests screen
- API endpoint: `/api/part-requests`

### 4. Customer Photos (`storage/app/public/customer-photos/`)

Reserved for future use (customer profile photos, etc.)

**Status**: Directory created but not currently used in production code

## How It Works

### 1. File Upload Flow

```
User uploads file
    ↓
Laravel receives upload
    ↓
File saved to storage/app/public/{directory}/
    ↓
Path saved to database (e.g., "products/abc123.jpg")
    ↓
Accessor generates full URL using asset('storage/' . path)
    ↓
URL returned in API response
    ↓
Flutter app displays image using Image.network(url)
```

### 2. Symlink Requirement

Laravel stores files in `storage/app/public/` but serves them through `public/storage/`:

```
Storage Location:
  /var/www/manchoice-backend/storage/app/public/products/abc123.jpg

Symlink:
  /var/www/manchoice-backend/public/storage → ../storage/app/public

Web Access:
  https://manschoice.co.ke/storage/products/abc123.jpg
```

**Without the symlink, all image URLs return 404 errors.**

### 3. URL Generation

#### Backend (Laravel)
```php
// In Model accessor (e.g., Product.php)
public function getImageUrlAttribute(): ?string
{
    if ($this->image_path) {
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path; // External URL
        }
        return asset('storage/' . $this->image_path); // Local file
    }
    return null;
}
```

#### Frontend (Flutter)
```dart
Image.network(
  product.imageUrl!,
  fit: BoxFit.cover,
  errorBuilder: (context, error, stackTrace) {
    return Icon(Icons.broken_image); // Show if 404
  },
)
```

## Configuration

### Required .env Settings

```env
# Use 'public' disk for file storage
FILESYSTEM_DISK=public

# Ensure correct APP_URL for production
APP_URL=https://manschoice.co.ke
```

### Filesystem Configuration

File: `config/filesystems.php`

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],

'links' => [
    public_path('storage') => storage_path('app/public'),
],
```

## Common Issues

### Issue 1: 404 Errors on All Images

**Cause**: Symlink missing or broken
**Solution**: Run `php artisan storage:link` or use `fix-storage.sh`

### Issue 2: Some Images Work, Others Don't

**Cause**: Specific storage directory doesn't exist
**Solution**: Create missing directory:
```bash
mkdir -p storage/app/public/{directory-name}
chmod 775 storage/app/public/{directory-name}
```

### Issue 3: Permission Denied Errors

**Cause**: Incorrect file/directory permissions
**Solution**:
```bash
chmod -R 775 storage
sudo chown -R www-data:www-data storage
```

### Issue 4: Images Work Locally But Not in Production

**Cause**: Symlink not created on production server
**Solution**: SSH to production and run:
```bash
cd /path/to/manchoice-backend
php artisan storage:link
```

## Testing Image Access

### 1. Check Symlink
```bash
ls -la public/storage
# Should show: public/storage -> ../storage/app/public
```

### 2. Check Directories Exist
```bash
ls -la storage/app/public/
# Should show:
#   drwxrwxr-x loan-documents
#   drwxrwxr-x products
#   drwxrwxr-x part-requests
#   drwxrwxr-x customer-photos
```

### 3. Check File Permissions
```bash
ls -la storage/app/public/products/
# Files should be readable (e.g., -rw-r--r--)
```

### 4. Test Direct URL Access
```bash
# Test a product image
curl -I https://manschoice.co.ke/storage/products/actual-filename.jpg
# Should return: HTTP/1.1 200 OK

# Not: HTTP/1.1 404 Not Found
```

### 5. Check in Admin Panel
1. Log into admin panel: `https://manschoice.co.ke/admin`
2. Go to Products: `https://manschoice.co.ke/admin/products`
3. Images should display correctly

### 6. Check in Flutter App
1. Open app and go to Shop screen
2. Product images should display
3. No broken image icons

## Database Schema

### Products Table
```sql
CREATE TABLE products (
    id bigint PRIMARY KEY,
    name varchar(255) NOT NULL,
    description text,
    category varchar(255),
    price decimal(10,2) NOT NULL,
    original_price decimal(10,2),
    discount_percentage int,
    image_path varchar(255),  -- Stores: "products/abc123.jpg"
    stock_quantity int DEFAULT 0,
    is_available tinyint(1) DEFAULT 1,
    created_at timestamp,
    updated_at timestamp
);
```

### Loans Table
```sql
CREATE TABLE loans (
    id bigint PRIMARY KEY,
    -- ... other fields ...
    bike_photo_path varchar(255),      -- "loan-documents/LN-2025-001_bike_photo.jpg"
    logbook_photo_path varchar(255),
    passport_photo_path varchar(255),
    id_photo_front_path varchar(255),
    id_photo_back_path varchar(255),
    next_of_kin_id_front_path varchar(255),
    next_of_kin_id_back_path varchar(255),
    next_of_kin_passport_photo_path varchar(255),
    guarantor_id_front_path varchar(255),
    guarantor_id_back_path varchar(255),
    guarantor_passport_photo_path varchar(255),
    guarantor_bike_photo_path varchar(255),
    guarantor_logbook_photo_path varchar(255),
    -- ... other fields ...
);
```

### Part Requests Table
```sql
CREATE TABLE part_requests (
    id bigint PRIMARY KEY,
    -- ... other fields ...
    image_path varchar(255),  -- "part-requests/part_request_1234567.jpg"
    -- ... other fields ...
);
```

## File Naming Conventions

### Loan Documents
Format: `{loan_number}_{field_name}_{timestamp}.{extension}`
Example: `LN-2025-001_bike_photo_1704067200.jpg`

### Product Images
Format: Random hash generated by Laravel
Example: `x8Y3kL9mN2pQrTvWz4Ab.jpg`

### Part Request Images
Format: `part_request_{timestamp}.{extension}`
Example: `part_request_1704067200.jpg`

## Security Considerations

1. **File Validation**: All uploads are validated for:
   - File type (image/jpeg, image/png, etc.)
   - File size (max 5MB for most uploads)
   - MIME type checking

2. **Public Access**: Files in `storage/app/public/` are publicly accessible via web URLs
   - Do NOT store sensitive documents here
   - Use `storage/app/private/` for confidential files

3. **Filename Sanitization**: Filenames include loan numbers and timestamps to prevent collisions

4. **Storage Limits**: Monitor disk space on production server

## Maintenance

### Regular Tasks

1. **Monitor Storage Usage**:
   ```bash
   du -sh storage/app/public/*
   ```

2. **Backup Images** (recommended weekly):
   ```bash
   tar -czf images-backup-$(date +%Y%m%d).tar.gz storage/app/public/
   ```

3. **Clean Up Old Test Files**:
   ```bash
   # Be careful with this command!
   find storage/app/public/ -name "*test*" -type f
   ```

4. **Verify Symlink After Deployments**:
   ```bash
   ls -la public/storage || php artisan storage:link
   ```

## Quick Reference Commands

```bash
# Create symlink
php artisan storage:link

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check permissions
ls -la storage/app/public/
ls -la public/storage

# Fix permissions
chmod -R 775 storage
sudo chown -R www-data:www-data storage

# Test file access
echo "test" > storage/app/public/test.txt
curl https://manschoice.co.ke/storage/test.txt
rm storage/app/public/test.txt
```

## Summary

All image uploads require:
1. ✅ Storage directory exists in `storage/app/public/{directory}/`
2. ✅ Symlink exists: `public/storage` → `storage/app/public`
3. ✅ Correct permissions (775 on directories, www-data ownership)
4. ✅ `.env` has `FILESYSTEM_DISK=public`
5. ✅ Correct `APP_URL` in `.env`

**Use the `fix-storage.sh` script to automatically set up all requirements!**
