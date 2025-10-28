# User Management System Proposal
## Man's Choice Enterprise - Admin Panel

---

## Overview
A comprehensive user management system to allow administrators to manage staff members with different roles and permissions.

---

## Proposed User Roles

### 1. **Super Admin** 👑
**Description:** Complete system access - owner/founder level
- Full access to everything
- Can create/delete other admins
- Can modify system settings
- View all reports and analytics
- Manage all users and permissions

**Use Case:** Business owner, IT administrator

---

### 2. **Admin** 🔧
**Description:** High-level management with most permissions
- Manage customers, loans, products
- Approve/reject loans
- Manage staff (except Super Admin)
- View financial reports
- Cannot delete Super Admin
- Cannot access system-critical settings

**Use Case:** Branch manager, senior manager

---

### 3. **Manager** 📊
**Description:** Day-to-day operations management
- View and manage customers
- Process loan applications
- View loan status and payments
- Generate reports
- Cannot approve loans over certain amount
- Cannot manage users

**Use Case:** Branch supervisor, operations manager

---

### 4. **Clerk/Staff** 📝
**Description:** Front desk operations
- Create customer profiles
- Submit loan applications (requires approval)
- Record customer information
- View assigned customers only
- Cannot approve loans
- Cannot delete records

**Use Case:** Front office staff, customer service

---

### 5. **Collector** 💰
**Description:** Field staff for payment collection
- View assigned customers and loans
- Record payments
- Update payment status
- View customer contact info
- View loan balances
- Cannot modify loan terms
- Cannot access financial reports

**Use Case:** Field collectors, recovery agents

---

### 6. **Accountant** 📈
**Description:** Financial management and reporting
- View all financial reports
- Verify payments and deposits
- Reconcile accounts
- View registration fees
- Export financial data
- Cannot create/modify loans
- Cannot manage customers

**Use Case:** Accountant, financial officer

---

### 7. **Auditor** 🔍
**Description:** Read-only access for auditing
- View-only access to all records
- Generate reports
- Export data
- Cannot modify anything
- Cannot approve/reject anything

**Use Case:** Internal auditor, compliance officer

---

## Detailed Permissions Matrix

### Customer Management
| Permission | Super Admin | Admin | Manager | Clerk | Collector | Accountant | Auditor |
|-----------|-------------|-------|---------|-------|-----------|------------|---------|
| View All Customers | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ |
| View Assigned Customers | - | - | - | ✅ | ✅ | - | - |
| Create Customer | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Edit Customer | ✅ | ✅ | ✅ | ✅* | ❌ | ❌ | ❌ |
| Delete Customer | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Blacklist Customer | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

*Clerk can only edit their own created customers

---

### Loan Management
| Permission | Super Admin | Admin | Manager | Clerk | Collector | Accountant | Auditor |
|-----------|-------------|-------|---------|-------|-----------|------------|---------|
| View All Loans | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ |
| View Assigned Loans | - | - | - | ✅ | ✅ | - | - |
| Create Loan Application | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Approve Loan (Any Amount) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Approve Loan (< 50,000) | - | - | ✅ | ❌ | ❌ | ❌ | ❌ |
| Reject Loan | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Edit Loan Terms | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Delete Loan | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Assign Collector | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

---

### Payment Management
| Permission | Super Admin | Admin | Manager | Clerk | Collector | Accountant | Auditor |
|-----------|-------------|-------|---------|-------|-----------|------------|---------|
| View All Payments | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ |
| View Assigned Payments | - | - | - | ✅ | ✅ | - | - |
| Record Payment | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Verify Payment | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Edit Payment | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Delete Payment | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Reverse Payment | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |

---

### Deposit Management
| Permission | Super Admin | Admin | Manager | Clerk | Collector | Accountant | Auditor |
|-----------|-------------|-------|---------|-------|-----------|------------|---------|
| View Deposits | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| Verify Deposit | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Reject Deposit | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| View Rejection History | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |

---

### Product Management
| Permission | Super Admin | Admin | Manager | Clerk | Collector | Accountant | Auditor |
|-----------|-------------|-------|---------|-------|-----------|------------|---------|
| View Products | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| Create Product | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Edit Product | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Delete Product | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Manage Stock | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |

---

### Registration Fee Management
| Permission | Super Admin | Admin | Manager | Clerk | Collector | Accountant | Auditor |
|-----------|-------------|-------|---------|-------|-----------|------------|---------|
| View Registration Fees | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| Verify Registration Fee | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Reject Registration Fee | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |

---

### Reports & Analytics
| Permission | Super Admin | Admin | Manager | Clerk | Collector | Accountant | Auditor |
|-----------|-------------|-------|---------|-------|-----------|------------|---------|
| View Dashboard | ✅ | ✅ | ✅ | ✅ | ✅* | ✅ | ✅ |
| View Financial Reports | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ |
| View Customer Reports | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| View Loan Reports | ✅ | ✅ | ✅ | ✅ | ✅* | ✅ | ✅ |
| Export Reports | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ |
| View Profit/Loss | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ |

*Collector sees limited dashboard with assigned loans only

---

### User Management
| Permission | Super Admin | Admin | Manager | Clerk | Collector | Accountant | Auditor |
|-----------|-------------|-------|---------|-------|-----------|------------|---------|
| View Users | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| Create User | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Edit User | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Delete User | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Assign Roles | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Reset Password | ✅ | ✅ | ✅* | ❌ | ❌ | ❌ | ❌ |

*Manager can only reset password for Clerk/Collector

---

### System Settings
| Permission | Super Admin | Admin | Manager | Clerk | Collector | Accountant | Auditor |
|-----------|-------------|-------|---------|-------|-----------|------------|---------|
| View Settings | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Edit Settings | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Backup Database | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| View Audit Logs | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## Additional Features

### 1. Assignment System
**For Clerk & Collector roles:**
- Assign specific customers to clerks
- Assign specific loans to collectors
- Track who is responsible for what
- View "My Customers" or "My Loans" section

### 2. Approval Limits
**For Manager role:**
- Set maximum loan amount they can approve
- Loans above limit require Admin/Super Admin approval
- Example: Manager can approve up to KES 50,000

### 3. Activity Tracking
**For all roles:**
- Track all user actions (audit log)
- Who created/modified what
- Login/logout history
- Failed login attempts

### 4. Multi-branch Support (Future)
**If you expand:**
- Assign users to specific branches
- Users can only see their branch data
- Branch managers manage their branch only
- Super Admin sees all branches

---

## User Management Interface Features

### User List Page
- Table showing all users
- Filter by role
- Search by name/email/phone
- Status indicator (Active/Inactive/Suspended)
- Last login timestamp
- Actions: Edit, Suspend, Delete, Reset Password

### Create/Edit User Form
- Basic Info:
  - Full Name
  - Email
  - Phone Number
  - National ID
  - Role (Dropdown)
  - Status (Active/Inactive)
- Password:
  - Set initial password
  - Send password via email/SMS
  - Force password change on first login
- Permissions:
  - Role-based (automatic)
  - Custom permissions (override specific rights)
- Assignment (if applicable):
  - Assign branch
  - Assign customers (for Clerk)
  - Assign loans (for Collector)
  - Set approval limit (for Manager)

### User Profile Page
- View user details
- Activity history
- Assigned customers/loans
- Performance metrics (for Collector)
- Edit profile
- Change password
- Suspend/Activate account

---

## Security Features

### 1. Password Requirements
- Minimum 8 characters
- Must contain uppercase, lowercase, number
- Cannot reuse last 3 passwords
- Expires every 90 days (optional)

### 2. Two-Factor Authentication (Optional)
- SMS verification
- Email verification
- Google Authenticator

### 3. Session Management
- Auto-logout after inactivity (30 minutes)
- Single session per user
- Or allow multiple sessions with tracking

### 4. IP Whitelisting (Optional)
- Restrict admin access to office IP only
- Allow mobile access for collectors

---

## Database Structure Proposal

### Users Table (Existing - needs modification)
```
- id
- name
- email
- phone
- password
- pin (for customer role)
- role (enum: super_admin, admin, manager, clerk, collector, accountant, auditor, customer)
- status (enum: active, inactive, suspended)
- branch_id (nullable, for future)
- approval_limit (decimal, for managers)
- last_login_at
- profile_completed
- customer_id (for customer role)
- accepted_terms
- accepted_terms_at
- created_by (user_id who created this user)
- created_at
- updated_at
- deleted_at (soft delete)
```

### Permissions Table (New)
```
- id
- name (e.g., 'view_customers', 'create_loan')
- display_name (e.g., 'View Customers', 'Create Loan')
- category (e.g., 'customer_management', 'loan_management')
- description
- created_at
- updated_at
```

### Role_Permissions Table (New)
```
- id
- role (enum: same as users table)
- permission_id
- created_at
- updated_at
```

### User_Assignments Table (New - for Clerk/Collector)
```
- id
- user_id
- assignable_type (Customer or Loan)
- assignable_id
- assigned_by (user_id who made assignment)
- assigned_at
- created_at
- updated_at
```

### Activity_Logs Table (New)
```
- id
- user_id
- action (e.g., 'created', 'updated', 'deleted', 'viewed')
- model_type (e.g., 'Customer', 'Loan', 'Payment')
- model_id
- description
- ip_address
- user_agent
- created_at
```

---

## Implementation Priority

### Phase 1: Basic User Management
1. Update users table with role field
2. Create user CRUD in admin panel
3. Basic role-based access control
4. User list, create, edit, delete pages

### Phase 2: Permissions System
1. Create permissions and role_permissions tables
2. Implement permission checking middleware
3. Seed default permissions for each role
4. Test all role permissions

### Phase 3: Advanced Features
1. Assignment system for Clerk/Collector
2. Approval limits for Manager
3. Activity logging
4. User profile and history

### Phase 4: Security Enhancements
1. Password policies
2. Session management
3. Two-factor authentication
4. IP whitelisting

---

## UI Wireframe Suggestions

### User Management Menu
```
Admin Panel
├── Dashboard
├── Customers
├── Loans
├── Products
├── Payments
├── Reports
└── Settings
    ├── User Management  ← NEW
    │   ├── All Users
    │   ├── Add New User
    │   ├── Roles & Permissions
    │   └── Activity Log
    ├── System Settings
    └── Backup & Restore
```

---

## Questions for You

1. **Which roles do you want to start with?**
   - All 7 roles, or start with Super Admin, Admin, Manager, Clerk, Collector?

2. **Do you need branch support now or later?**
   - Single location or multiple branches?

3. **Assignment system priority?**
   - Should collectors be assigned specific loans immediately?

4. **Custom permissions?**
   - Should admins be able to override role permissions for specific users?

5. **Activity logging?**
   - Track all user actions for audit trail?

6. **Approval workflow?**
   - Should loans require multi-level approval (Clerk creates → Manager approves → Admin final approval)?

---

## Recommended Starting Point

I suggest we start with:

1. ✅ **5 Core Roles:**
   - Super Admin (you)
   - Admin (senior staff)
   - Manager (branch manager)
   - Clerk (front desk)
   - Collector (field staff)

2. ✅ **Basic Permissions:**
   - Focus on the permission matrix above
   - Hardcode role permissions initially (can make flexible later)

3. ✅ **User Management UI:**
   - User list page
   - Create/edit user form
   - Role assignment

4. ✅ **Middleware Protection:**
   - Protect all admin routes by role
   - Check permissions before actions

5. ⏰ **Future Enhancement:**
   - Custom permissions system
   - Assignment system
   - Activity logging
   - Multi-branch support

---

## Let me know:
1. Which roles you want
2. Any modifications to permissions
3. Priority features
4. Then I'll implement the user management system!
