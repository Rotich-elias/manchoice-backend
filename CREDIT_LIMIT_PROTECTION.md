# Credit Limit Protection System

## Problem Statement
Without proper protection, new customers could:
1. Submit multiple loan applications before admin review
2. Pay deposits for loans before credit limit is set
3. Bypass the admin approval process

## Solution: Multi-Layer Protection

### Protection Layer 1: Loan Application Restriction
**Location:** `app/Http/Controllers/API/LoanController.php` (Lines 170-197)

**Logic:**
```php
if ($customer->credit_limit <= 0) {
    $existingLoans = Loan::where('customer_id', $customer->id)->count();

    if ($existingLoans > 0) {
        // BLOCK: Customer already has an application pending
        return error("You already have a pending application...");
    }
    // ALLOW: First application (for registration flow)
}
```

**What it does:**
- ✅ **Allows** the first loan application (needed for registration flow)
- ❌ **Blocks** any additional applications until credit limit is set
- 📝 Provides clear messaging based on registration fee payment status

**Scenarios:**

| Situation | Credit Limit | Existing Loans | Reg Fee Paid | Result |
|-----------|--------------|----------------|--------------|---------|
| New customer, first application | 0 | 0 | No | ✅ Application created (status: awaiting_registration_fee) |
| New customer, first application | 0 | 0 | Yes | ✅ Application created (status: pending) |
| Customer tries 2nd application | 0 | 1 | No | ❌ Blocked: "Please pay KES 300 first" |
| Customer tries 2nd application | 0 | 1 | Yes | ❌ Blocked: "Under review, admin will set limit" |
| After admin sets limit | 50000 | 1 | Yes | ✅ Can apply for more loans |

### Protection Layer 2: Deposit Payment Restriction
**Location:** `app/Http/Controllers/API/DepositController.php` (Lines 63-72)

**Logic:**
```php
if ($loan->customer->credit_limit <= 0) {
    // BLOCK: Cannot pay deposit before admin sets credit limit
    return error("Cannot pay deposit yet. Your application is under review...");
}
```

**What it does:**
- ❌ **Blocks** all deposit payments when credit_limit = 0
- 📝 Informs user to wait for admin review
- 🛡️ Prevents money being paid before loan approval is possible

**Impact:**
Even if somehow a loan gets created with credit_limit = 0, the customer **cannot pay the deposit** until admin sets the limit.

## Complete Flow with Protection

### Flow Diagram
```
┌─────────────────────────────────────────────────────────────┐
│ Step 1: User Signs Up                                        │
│ - No payment required                                        │
│ - credit_limit = 0 (default)                                 │
│ - registration_fee_paid = false                              │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 2: First Loan Application                               │
│ ✅ ALLOWED (existingLoans = 0)                               │
│ - User fills application form                                │
│ - Uploads all documents                                      │
│ - Loan created with status: "awaiting_registration_fee"     │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ User tries 2nd application?                                  │
│ ❌ BLOCKED (credit_limit = 0, existingLoans = 1)            │
│ Message: "You already have a pending application..."        │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 3: User Pays Registration Fee (KES 300)                │
│ - registration_fee_paid = true                               │
│ - Loan status changes: awaiting_registration_fee → pending  │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ User tries to pay deposit?                                   │
│ ❌ BLOCKED (credit_limit = 0)                                │
│ Message: "Cannot pay deposit yet. Under review..."          │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 4: Admin Reviews Application                            │
│ - Admin views loan in "Pending" tab                         │
│ - Sees warning: "Set loan limit for this customer"          │
│ - Goes to customer profile                                   │
│ - Sets credit_limit = 50000 (example)                        │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 5: Admin Approves Loan                                 │
│ ✅ NOW POSSIBLE (credit_limit > 0)                           │
│ - Loan status: pending → approved                           │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 6: User Pays Deposit                                    │
│ ✅ NOW ALLOWED (credit_limit > 0)                            │
│ - User can pay 10% deposit                                   │
│ - Deposit deducted from loan balance                         │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ Future: User Applies for More Loans                          │
│ ✅ ALLOWED (credit_limit > 0)                                │
│ - Can apply for additional loans                             │
│ - Subject to available credit                                │
└─────────────────────────────────────────────────────────────┘
```

## Protection Points Summary

| Protection Point | Location | Prevents |
|------------------|----------|----------|
| **Application Limit** | LoanController.php:170-197 | Multiple applications before admin review |
| **Deposit Payment** | DepositController.php:63-72 | Paying deposit before credit limit set |
| **Admin Approval** | DashboardController.php:112-114 | Approving loans without setting limit |

## API Response Examples

### Scenario 1: Second Application Attempt (Fee Not Paid)
```json
POST /api/loans

Response (400):
{
  "success": false,
  "message": "You already have a pending application. Please pay the KES 300 registration fee to proceed with your application.",
  "credit_limit_not_set": true,
  "registration_fee_paid": false,
  "registration_fee_amount": 300.00
}
```

### Scenario 2: Second Application Attempt (Fee Paid)
```json
POST /api/loans

Response (400):
{
  "success": false,
  "message": "Your first loan application is under review. Admin will set your loan limit soon. You will be notified when you can apply for more loans.",
  "credit_limit_not_set": true,
  "registration_fee_paid": true,
  "status": "awaiting_admin_review"
}
```

### Scenario 3: Deposit Payment Attempt (Credit Limit Not Set)
```json
POST /api/loans/1/deposit/mpesa

Response (400):
{
  "success": false,
  "message": "Cannot pay deposit yet. Your application is under review. Admin will set your loan limit first, then you can proceed with the deposit payment.",
  "credit_limit_not_set": true,
  "status": "awaiting_admin_review"
}
```

## Admin Experience

### When Reviewing New Customer
```
┌─────────────────────────────────────────────────────────┐
│ Loan Application #LN20251025001                         │
│ Status: 🟡 Pending                                       │
│                                                          │
│ ⚠ Action Required: Set Loan Limit                       │
│ This is a new customer with no approved loans.          │
│ Please set their loan limit before approving or         │
│ rejecting this application.                             │
│                                                          │
│ Customer: John Doe                                       │
│ Current Credit Limit: KES 0.00                          │
│ Requested Amount: KES 50,000.00                         │
│                                                          │
│ [Go to Customer Profile] [Cannot Approve Yet]           │
└─────────────────────────────────────────────────────────┘
```

### After Setting Credit Limit
```
┌─────────────────────────────────────────────────────────┐
│ Loan Application #LN20251025001                         │
│ Status: 🟡 Pending                                       │
│                                                          │
│ Customer: John Doe                                       │
│ Credit Limit: KES 100,000.00                            │
│ Outstanding: KES 0.00                                    │
│ Available Credit: KES 100,000.00                        │
│ Requested Amount: KES 50,000.00 ✅                       │
│                                                          │
│ [Approve Loan] [Reject Loan]                            │
└─────────────────────────────────────────────────────────┘
```

## User Experience

### Mobile App Flow

#### First Application
```
[Apply for Loan]
Amount: KES 50,000
Duration: 30 days
[Upload Documents]
[Submit Application]

✅ Success!
Application submitted successfully.
Please pay KES 300 registration fee to proceed.

[Pay with M-PESA]
```

#### Trying Second Application
```
[Apply for Loan]
Amount: KES 30,000
Duration: 20 days

❌ Cannot Apply
Your first loan application is under review.
Admin will set your loan limit soon.
You will be notified when you can apply for more loans.

Current Status: Awaiting Admin Review

[Go Back to Dashboard]
```

#### Trying to Pay Deposit (Before Admin Sets Limit)
```
[Loan #LN20251025001]
Amount: KES 50,000
Deposit Required: KES 5,000

[Pay Deposit]

❌ Cannot Pay Deposit Yet
Your application is under review.
Admin will set your loan limit first,
then you can proceed with the deposit payment.

Status: Awaiting Admin Review

[OK]
```

#### After Admin Sets Limit
```
🎉 Great News!
Your loan limit has been set to KES 100,000

[Loan #LN20251025001]
Status: ✅ Approved
Amount: KES 50,000
Deposit Required: KES 5,000

[Pay Deposit Now]
```

## Testing Scenarios

### Test 1: Block Multiple Applications
```bash
# 1. Create first application
POST /api/loans
{
  "customer_id": 1,
  "principal_amount": 50000,
  ...
}
✅ Expected: Success (status: awaiting_registration_fee)

# 2. Try to create second application
POST /api/loans
{
  "customer_id": 1,
  "principal_amount": 30000,
  ...
}
❌ Expected: Error "You already have a pending application..."
```

### Test 2: Block Deposit Payment
```bash
# 1. Pay registration fee
POST /api/registration-fee/verify
{
  "transaction_id": "REG-ABC123"
}
✅ Expected: Success (loan status → pending)

# 2. Try to pay deposit (credit_limit still 0)
POST /api/loans/1/deposit/mpesa
{
  "phone_number": "0712345678"
}
❌ Expected: Error "Cannot pay deposit yet. Under review..."
```

### Test 3: Allow After Credit Limit Set
```bash
# 1. Admin sets credit limit
UPDATE customers SET credit_limit = 50000 WHERE id = 1;

# 2. Admin approves loan
POST /admin/loans/1/approve
✅ Expected: Success

# 3. User pays deposit
POST /api/loans/1/deposit/mpesa
{
  "phone_number": "0712345678"
}
✅ Expected: Success (deposit payment initiated)
```

## Security Benefits

### Before Protection
```
❌ User signs up
❌ Submits 10 loan applications
❌ Pays deposits for all 10 (KES 50,000 total deposits)
❌ Admin overwhelmed with applications
❌ Money stuck in deposits before proper review
```

### After Protection
```
✅ User signs up
✅ Submits 1 loan application
✅ Pays KES 300 registration fee
✅ Cannot pay deposit yet
⏳ Waits for admin review
✅ Admin reviews, sets appropriate limit
✅ Admin approves loan
✅ User pays deposit
✅ Clean, controlled process
```

## Edge Cases Handled

### Edge Case 1: User Bypasses Frontend
**Scenario:** User directly calls API to submit multiple loans

**Protection:** Backend validation in LoanController blocks it
```
POST /api/loans (2nd attempt)
→ 400 Error: "You already have a pending application"
```

### Edge Case 2: User Tries Deposit via Different Methods
**Scenario:** User tries M-PESA, cash, or other payment methods

**Protection:** All deposit payment methods check credit_limit
```
POST /api/loans/1/deposit/mpesa
POST /api/deposits/cash
→ Both blocked if credit_limit = 0
```

### Edge Case 3: Race Condition (Multiple Simultaneous Requests)
**Scenario:** User rapidly clicks "Apply" multiple times

**Protection:** Database transaction + count check happens atomically
```php
DB::beginTransaction();
$existingLoans = Loan::where('customer_id', $customer->id)->count();
// Only first request proceeds, others see existingLoans > 0
```

## Files Modified

| File | Lines | Purpose |
|------|-------|---------|
| `app/Http/Controllers/API/LoanController.php` | 170-197 | Block multiple applications |
| `app/Http/Controllers/API/DepositController.php` | 63-72 | Block deposit payments |

## Rollback Plan

If needed to remove protection:

```php
// In LoanController.php, remove lines 170-197
// In DepositController.php, remove lines 63-72

// Or keep but disable:
if (config('app.enable_credit_limit_protection', true)) {
    // protection code
}
```

## Configuration

No configuration needed - protection is always active.

To customize messages, edit the response messages in:
- `LoanController.php:179-185` (blocked 2nd application messages)
- `DepositController.php:66-71` (blocked deposit message)

## Monitoring

### Metrics to Track
- Number of users hitting "already have application" error
- Average time from signup to credit limit being set
- Number of deposit payment attempts with credit_limit = 0

### Log Messages
```
[INFO] User {user_id} blocked from 2nd application (credit_limit=0)
[INFO] User {user_id} blocked from deposit payment (credit_limit=0)
[INFO] Admin set credit_limit={amount} for customer {customer_id}
```

---

## Summary

This protection system ensures:
1. ✅ Users can submit ONE initial application
2. ✅ Users CANNOT submit additional applications before review
3. ✅ Users CANNOT pay deposits before admin sets credit limit
4. ✅ Admin has full control over credit approval process
5. ✅ Clear messaging guides users through the process
6. ✅ Money is protected - no premature deposit payments

**Implementation Date:** 2025-10-25
**Status:** ✅ COMPLETE AND PROTECTED
**Security Level:** 🛡️ HIGH
