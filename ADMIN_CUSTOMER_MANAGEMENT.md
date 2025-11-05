# Admin Customer & Loan Management Guide

## 📍 Where to Add Customers in the Admin Dashboard

### Web Dashboard (Browser Interface)

#### **Location:** `/admin/customers`

**Steps to Add a Customer:**

1. **Navigate to Customers Page**
   - Log in to the admin dashboard at `/admin/login`
   - Click on "Customers" in the navigation menu
   - URL: `http://localhost:8000/admin/customers`

2. **Click "Add Customer" Button**
   - At the top-right of the page, you'll see a blue **"Add Customer"** button
   - Click it to open the customer creation modal

3. **Fill in Customer Details**

   **Required Fields:**
   - **Full Name** - Customer's full name
   - **Phone Number** - Format: `0712345678` (10 digits starting with 0)

   **Optional Fields:**
   - **Email** - Customer's email address
   - **ID Number** - National ID number
   - **Business Name** - If customer has a business
   - **Credit Limit** - Default is KES 1,000 (you can change this)
   - **Status** - Active, Inactive, or Blacklisted (default: Active)
   - **Address** - Customer's physical address
   - **Notes** - Any additional information

4. **Submit the Form**
   - Click **"Create Customer"** button
   - Customer will be created and you'll be redirected back to the customers list
   - A success message will appear

---

## 🔄 How to Update Existing Customers

### Method 1: From Customer Detail Page

1. Go to `/admin/customers`
2. Click **"View"** button on any customer
3. On the customer detail page, you can:
   - **Edit Credit Limit** - Click the "Edit" button next to Credit Limit
   - **Update Customer Info** - Use the API endpoint (see below)

### Method 2: Via API (Programmatic)

**Endpoint:** `PUT /api/admin/customers/{id}`

**Headers:**
```
Authorization: Bearer {your_admin_token}
Content-Type: application/json
```

**Example Request:**
```bash
curl -X PUT http://localhost:8000/api/admin/customers/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe Updated",
    "phone": "0712345678",
    "email": "john.updated@example.com",
    "credit_limit": 50000,
    "status": "active",
    "notes": "VIP customer - increased credit limit"
  }'
```

---

## 💰 How to Create Loans for Customers (Admin)

### Method 1: Via API (Recommended)

**Endpoint:** `POST /api/admin/loans`

**Headers:**
```
Authorization: Bearer {your_admin_token}
Content-Type: application/json
```

**Basic Loan Creation:**
```bash
curl -X POST http://localhost:8000/api/admin/loans \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "principal_amount": 10000,
    "interest_rate": 10,
    "duration_days": 30,
    "purpose": "Business expansion",
    "status": "pending"
  }'
```

**Advanced Loan Creation (With Overrides):**
```bash
curl -X POST http://localhost:8000/api/admin/loans \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "principal_amount": 50000,
    "interest_rate": 12,
    "duration_days": 60,
    "purpose": "Emergency loan - management approved",
    "status": "approved",
    "skip_credit_limit_check": true,
    "force_create": true,
    "deposit_required": false,
    "notes": "Approved by CEO for VIP customer"
  }'
```

**Loan with Products:**
```bash
curl -X POST http://localhost:8000/api/admin/loans \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "principal_amount": 15000,
    "interest_rate": 10,
    "duration_days": 45,
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 3, "quantity": 1}
    ]
  }'
```

---

## 🎯 Admin Privileges & Overrides

As an admin, you have special powers when creating loans:

### 1. **Skip Credit Limit Check**
```json
{
  "skip_credit_limit_check": true
}
```
- Allows creating loans that exceed customer's credit limit
- Useful for special cases or VIP customers

### 2. **Force Create (Bypass Customer Status)**
```json
{
  "force_create": true
}
```
- Create loans for blacklisted or inactive customers
- Requires explicit confirmation

### 3. **Custom Loan Status**
```json
{
  "status": "approved"
}
```
- Set initial status: `pending`, `approved`, `active`, `awaiting_deposit`
- Regular users can only create `pending` loans

### 4. **Disable Deposit Requirement**
```json
{
  "deposit_required": false
}
```
- Skip the 10% deposit requirement
- Loan goes straight to approval process

---

## 📊 Available Endpoints

### **Customer Management**

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/api/admin/customers` | List all customers | Admin, Manager |
| GET | `/api/admin/customers/{id}` | View customer details | Admin, Manager |
| PUT | `/api/admin/customers/{id}` | Update customer | Admin, Manager |
| POST | `/admin/customers/store` | Create customer (web) | Admin |

### **Loan Management**

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/api/admin/loans` | List all loans | Admin, Manager |
| POST | `/api/admin/loans` | Create new loan | Admin, Manager |
| GET | `/api/admin/loans/{id}` | View loan details | Admin, Manager |
| PUT | `/api/admin/loans/{id}` | Update loan | Admin, Manager |

---

## 🔒 Access Control

**Roles with access to these features:**
- ✅ **Super Admin** - Full access
- ✅ **Admin** - Full access
- ✅ **Manager** - Full access
- ❌ **Clerk** - No access (read-only)
- ❌ **Collector** - No access (read-only)
- ❌ **Customer** - No access

---

## 📝 Customer Creation Fields Reference

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `name` | String | ✅ Yes | Customer full name | "John Doe" |
| `phone` | String | ✅ Yes | Phone number (10 digits) | "0712345678" |
| `email` | String | No | Email address | "john@example.com" |
| `id_number` | String | No | National ID number | "12345678" |
| `business_name` | String | No | Business name | "John's Boda Boda" |
| `credit_limit` | Number | No | Credit limit in KES | 10000 |
| `status` | String | No | active/inactive/blacklisted | "active" |
| `address` | String | No | Physical address | "Nairobi, Kenya" |
| `notes` | Text | No | Additional notes | "VIP customer" |
| `user_id` | Number | No | Link to user account | 1 |

---

## 💡 Best Practices

### When Creating Customers:
1. ✅ Always verify phone number format (0712345678)
2. ✅ Set appropriate credit limit based on customer history
3. ✅ Add notes explaining admin creation reason
4. ✅ Double-check for duplicate phone numbers

### When Creating Loans:
1. ✅ Verify customer status before creating
2. ✅ Add clear notes explaining any overrides used
3. ✅ Only use `skip_credit_limit_check` when authorized
4. ✅ Set appropriate interest rate and duration
5. ✅ Document the reason in the notes field

### When Updating Customers:
1. ✅ Document reason for changes in notes
2. ✅ Be careful when changing status to "blacklisted"
3. ✅ Notify other staff of major credit limit changes
4. ✅ Keep customer informed of any changes

---

## 🚨 Common Issues & Solutions

### Issue: "Customer already exists with this phone number"
**Solution:** Check if customer already in system. Update existing customer instead.

### Issue: "Loan amount exceeds credit limit"
**Solution:** Either increase credit limit or use `"skip_credit_limit_check": true`

### Issue: "Customer account is blacklisted"
**Solution:** Use `"force_create": true` to override (requires authorization)

### Issue: Modal not opening
**Solution:** Check browser console for JavaScript errors. Refresh page.

---

## 📞 Support

For additional help or custom implementations:
1. Check Laravel logs: `/storage/logs/laravel.log`
2. Review admin actions log for audit trail
3. Contact system administrator

---

**Last Updated:** 2025-01-04
**Version:** 1.0
