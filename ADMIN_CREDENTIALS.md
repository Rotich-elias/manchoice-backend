# Admin User Credentials

## Created: 2025-10-25 10:58:30

---

# ⚠️ ⚠️ ⚠️ CRITICAL PRODUCTION WARNING ⚠️ ⚠️ ⚠️

## 🔴 DO NOT USE THESE CREDENTIALS IN PRODUCTION!

**THIS FILE CONTAINS DEFAULT DEVELOPMENT CREDENTIALS ONLY!**

### BEFORE DEPLOYING TO PRODUCTION:
1. ✅ **CHANGE THE ADMIN PASSWORD IMMEDIATELY**
2. ✅ **DELETE THIS FILE FROM PRODUCTION SERVERS**
3. ✅ **DO NOT COMMIT THIS FILE TO GIT** (already in .gitignore)
4. ✅ **Store real credentials in a secure password manager**
5. ✅ **Use strong, unique passwords (min 16 characters)**

### IF THIS IS PRODUCTION:
- **STOP!** Change all passwords NOW
- Run: `php artisan tinker` and execute:
  ```
  $admin = \App\Models\User::find(1);
  $admin->password = bcrypt('YOUR_STRONG_PASSWORD_HERE');
  $admin->save();
  ```
- Delete this file immediately
- Rotate all API keys and secrets

---

## 🔐 Admin Login Credentials

### Web Admin Panel
```
URL:      http://YOUR_SERVER_IP/admin/login
Email:    admin@manchoice.com
Phone:    0700000000
Password: admin123
```

### API Login
```bash
POST /api/login

Body:
{
  "phone": "0700000000",
  "password": "admin123"
}
```

### Database Details
```
User ID:  1
Name:     Admin
Email:    admin@manchoice.com
Phone:    0700000000
Role:     admin
Status:   Active
Reg Fee:  Paid (bypassed for admin)
```

---

## ⚠️ SECURITY WARNINGS

### 🔴 CRITICAL - Change Password Immediately!

**Default password:** `admin123`

This is a **TEMPORARY** password for initial setup only.

**MUST DO AFTER FIRST LOGIN:**
1. Login to admin panel
2. Go to Profile/Settings
3. Change password to a strong password
4. Use at least 12 characters with uppercase, lowercase, numbers, and symbols

### Recommended Strong Password Format
```
Example: M@nCh0ice#2025!Secure
- Minimum 12 characters
- Mix of uppercase and lowercase
- Include numbers
- Include special characters
- Avoid common words
```

---

## 📱 Login Methods

### Method 1: Web Admin Panel (Recommended)
```
1. Open browser
2. Go to: http://YOUR_SERVER_IP/admin/login
3. Enter email or phone: 0700000000
4. Enter password: admin123
5. Click "Login"
```

### Method 2: API Login (For Development/Testing)
```bash
curl -X POST http://YOUR_SERVER_IP/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "0700000000",
    "password": "admin123"
  }'
```

Response:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@manchoice.com",
      "phone": "0700000000",
      "role": "admin"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

---

## 🎯 Admin Capabilities

As an admin, you can:

### User Management
- ✅ View all users and customers
- ✅ Update customer credit limits
- ✅ Blacklist/activate customers
- ✅ View customer loan history

### Loan Management
- ✅ View all loan applications
- ✅ Filter by status (pending, approved, active, etc.)
- ✅ Approve or reject loans
- ✅ Set credit limits before approval
- ✅ View loan details and documents
- ✅ Track deposit payments

### Payment Management
- ✅ View all payments
- ✅ Record cash payments
- ✅ Approve pending payments
- ✅ Reject invalid payments
- ✅ Track payment history

### Deposit Management
- ✅ View all deposits
- ✅ Record cash deposits
- ✅ Filter by type (registration, loan_deposit)
- ✅ Track deposit status

### Product Management
- ✅ Add new products
- ✅ Update product details
- ✅ Manage stock levels
- ✅ Set product prices and discounts

### Reports & Analytics
- ✅ Generate loan reports (PDF/Excel)
- ✅ View dashboard statistics
- ✅ Track revenue and collections
- ✅ Monitor defaulted loans

---

## 🚀 First Login Checklist

After logging in for the first time:

### Immediate Actions (Security)
- [ ] Change default password
- [ ] Update email to your work email
- [ ] Update phone number if needed
- [ ] Verify admin panel access

### System Setup
- [ ] Review dashboard statistics
- [ ] Check product inventory (8 products)
- [ ] Verify M-PESA configuration
- [ ] Test loan application flow
- [ ] Test registration fee payment

### Configuration
- [ ] Set up notification preferences
- [ ] Configure business settings
- [ ] Set default interest rates
- [ ] Configure deposit requirements

---

## 🔄 Password Reset (If Needed)

### Option 1: Via Tinker (Direct Database)
```bash
php artisan tinker

$admin = \App\Models\User::find(1);
$admin->password = bcrypt('new_password_here');
$admin->save();
echo "Password updated!";
```

### Option 2: Create New Admin (If Locked Out)
```bash
php artisan tinker

\App\Models\User::create([
    'name' => 'Admin Backup',
    'email' => 'admin2@manchoice.com',
    'phone' => '0700000001',
    'password' => bcrypt('new_secure_password'),
    'role' => 'admin',
    'registration_fee_paid' => true,
]);
```

---

## 📊 Admin Dashboard Access

### Dashboard URL
```
http://YOUR_SERVER_IP/admin/dashboard
```

### Main Sections
1. **Dashboard** - Overview statistics
2. **Customers** - Customer management
3. **Loans** - Loan applications
4. **Payments** - Payment tracking
5. **Deposits** - Deposit management
6. **Products** - Inventory management
7. **Reports** - Analytics & exports
8. **Support** - Customer support tickets

---

## 🔍 Testing Admin Login

### Test 1: Web Login
```
1. Open: http://localhost/admin/login
2. Phone: 0700000000
3. Password: admin123
4. Should redirect to admin dashboard
```

### Test 2: API Login
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"phone":"0700000000","password":"admin123"}'

# Use token to access protected routes
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Test 3: Database Verification
```bash
php artisan tinker --execute="
echo 'Admin: ' . \App\Models\User::where('role', 'admin')->count() . PHP_EOL;
echo 'User #1: ' . \App\Models\User::find(1)->name . PHP_EOL;
"
```

Expected output:
```
Admin: 1
User #1: Admin
```

---

## 🛡️ Security Best Practices

### Password Management
- ✅ Use a password manager
- ✅ Never share password via email/SMS
- ✅ Change password every 90 days
- ✅ Use unique password (not used elsewhere)
- ❌ Don't use: admin123, password, 123456

### Access Control
- ✅ Only login from trusted devices
- ✅ Logout when done
- ✅ Use HTTPS in production
- ✅ Enable 2FA if available
- ❌ Don't share admin credentials

### Session Management
- ✅ Clear browser cache regularly
- ✅ Don't stay logged in on public computers
- ✅ Monitor active sessions
- ✅ Logout after inactivity

---

## 📝 Admin User Structure

```json
{
  "id": 1,
  "name": "Admin",
  "email": "admin@manchoice.com",
  "phone": "0700000000",
  "role": "admin",
  "registration_fee_paid": true,
  "registration_fee_amount": 0,
  "registration_fee_paid_at": "2025-10-25 10:58:30",
  "created_at": "2025-10-25 10:58:30",
  "updated_at": "2025-10-25 10:58:30"
}
```

---

## 🔧 Troubleshooting

### Issue: Cannot Login
**Solutions:**
1. Check if server is running: `php artisan serve`
2. Clear cache: `php artisan cache:clear`
3. Verify user exists: `php artisan tinker` → `User::find(1)`
4. Reset password using tinker (see above)

### Issue: "Invalid Credentials"
**Solutions:**
1. Double-check phone number: `0700000000`
2. Double-check password: `admin123`
3. Verify caps lock is OFF
4. Try email instead: `admin@manchoice.com`

### Issue: "Role Not Authorized"
**Solutions:**
1. Verify role is 'admin': `User::find(1)->role`
2. Update role if needed: `User::find(1)->update(['role' => 'admin'])`

### Issue: Session Expired
**Solutions:**
1. Simply login again
2. Clear browser cookies
3. Use incognito/private mode for testing

---

## 📞 Support Information

### For Technical Issues
- Check logs: `storage/logs/laravel.log`
- Run migrations: `php artisan migrate:status`
- Clear config: `php artisan config:clear`
- Restart server: `php artisan serve`

### For Password Issues
- Reset via tinker (see Password Reset section)
- Create backup admin user
- Contact system administrator

---

## ✅ Verification Checklist

- [x] Admin user created (ID: 1)
- [x] Role set to 'admin'
- [x] Registration fee marked as paid
- [x] Email configured: admin@manchoice.com
- [x] Phone configured: 0700000000
- [x] Password set: admin123
- [ ] **CHANGE PASSWORD AFTER FIRST LOGIN**
- [ ] Update email to work email
- [ ] Test admin panel access
- [ ] Configure system settings

---

## 🎯 Next Steps

1. **Login to admin panel**
   - Use credentials above
   - Verify dashboard loads

2. **Change password immediately**
   - Go to profile settings
   - Set strong password
   - Save and re-login

3. **Configure system**
   - Set up M-PESA credentials
   - Configure business settings
   - Set default loan parameters

4. **Test the flow**
   - Create test customer
   - Submit test loan application
   - Process registration fee
   - Set credit limit
   - Approve loan

5. **Go live**
   - Once testing is complete
   - Start accepting real customers

---

**Created:** 2025-10-25
**Status:** ✅ ACTIVE
**User ID:** 1
**Default Password:** admin123 (⚠️ CHANGE THIS!)

**IMPORTANT:** Keep this document secure. Delete or restrict access after setting up your permanent admin credentials.
