# Loan Deposit Admin Guide

## Overview
This guide explains how the admin can view and manage 10% loan deposits that customers pay when applying for loans.

## Database Changes

### Deposits Table
Two new/updated columns:
- `loan_id` (bigint, nullable) - Links the deposit to a specific loan
- `type` (enum) - Categorizes deposits: 'registration', 'loan_deposit', 'savings'

## API Endpoints for Admin

### 1. Get All Deposits (with filtering)
```http
GET /api/deposits
```

**Query Parameters:**
- `status` - Filter by status (pending, completed, failed)
- `loan_id` - Filter deposits for a specific loan
- `type` - Filter by type (registration, loan_deposit, savings)
- `per_page` - Results per page (default: 15)

**Examples:**
```bash
# Get all loan deposits
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://192.168.100.20:8000/api/deposits?type=loan_deposit"

# Get deposits for a specific loan
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://192.168.100.20:8000/api/deposits?loan_id=123"

# Get all registration deposits
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://192.168.100.20:8000/api/deposits?type=registration"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "loan_id": 45,
        "customer_id": 12,
        "amount": "5000.00",
        "type": "loan_deposit",
        "transaction_id": "DEP-ABC123",
        "mpesa_receipt_number": "MPE12345678",
        "payment_method": "mpesa",
        "status": "completed",
        "paid_at": "2025-10-25 10:30:00",
        "loan": {
          "id": 45,
          "loan_number": "LN20251025001",
          "total_amount": "50000.00"
        },
        "customer": {
          "id": 12,
          "name": "John Doe"
        }
      }
    ],
    "total": 1
  }
}
```

### 2. Get Deposits for a Specific Loan
```http
GET /api/loans/{loan_id}/deposits
```

**Example:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://192.168.100.20:8000/api/loans/45/deposits"
```

### 3. Get Deposit Status for a Loan
```http
GET /api/loans/{loan_id}/deposit/status
```

**Response:**
```json
{
  "success": true,
  "data": {
    "deposit_required": true,
    "deposit_amount": "5000.00",
    "deposit_paid": "5000.00",
    "remaining_deposit": "0.00",
    "is_deposit_paid": true,
    "deposit_paid_at": "2025-10-25 10:30:00",
    "deposits": [
      {
        "id": 1,
        "amount": "5000.00",
        "type": "loan_deposit",
        "status": "completed"
      }
    ]
  }
}
```

### 4. Record Cash Deposit Payment (Admin Only)
```http
POST /api/deposits/cash
```

**Request Body:**
```json
{
  "loan_id": 45,
  "amount": 5000,
  "phone_number": "0712345678",
  "notes": "Cash deposit received at office"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Deposit recorded successfully",
  "data": {
    "deposit": {
      "id": 1,
      "loan_id": 45,
      "customer_id": 12,
      "amount": "5000.00",
      "type": "loan_deposit",
      "payment_method": "cash",
      "status": "completed"
    },
    "loan": {
      "id": 45,
      "deposit_paid": "5000.00",
      "deposit_amount": "5000.00"
    }
  }
}
```

## Admin Dashboard Integration

### Viewing Loan Details with Deposits
When viewing a loan in the admin panel, the loan object now includes deposits:

```php
// In your admin controller
$loan = Loan::with(['customer', 'approver', 'payments', 'deposits'])->find($id);

// Access deposits
foreach ($loan->deposits as $deposit) {
    echo $deposit->amount;
    echo $deposit->type; // 'loan_deposit' or 'registration'
    echo $deposit->status; // 'completed', 'pending', etc.
    echo $deposit->paid_at;
}
```

### Blade View Example
```blade
<h3>Loan Deposits</h3>
@if($loan->deposits->count() > 0)
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Amount</th>
        <th>Type</th>
        <th>Method</th>
        <th>Status</th>
        <th>Receipt</th>
      </tr>
    </thead>
    <tbody>
      @foreach($loan->deposits as $deposit)
        <tr>
          <td>{{ $deposit->paid_at?->format('Y-m-d H:i') ?? 'Pending' }}</td>
          <td>KES {{ number_format($deposit->amount, 2) }}</td>
          <td>
            <span class="badge badge-{{ $deposit->type == 'loan_deposit' ? 'primary' : 'secondary' }}">
              {{ ucfirst(str_replace('_', ' ', $deposit->type)) }}
            </span>
          </td>
          <td>{{ ucfirst($deposit->payment_method) }}</td>
          <td>
            <span class="badge badge-{{ $deposit->status == 'completed' ? 'success' : 'warning' }}">
              {{ ucfirst($deposit->status) }}
            </span>
          </td>
          <td>{{ $deposit->mpesa_receipt_number ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@else
  <p>No deposits recorded for this loan.</p>
@endif

<div class="deposit-summary">
  <p><strong>Required Deposit:</strong> KES {{ number_format($loan->deposit_amount, 2) }}</p>
  <p><strong>Paid:</strong> KES {{ number_format($loan->deposit_paid, 2) }}</p>
  <p><strong>Remaining:</strong> KES {{ number_format($loan->getRemainingDepositAmount(), 2) }}</p>
  <p><strong>Status:</strong>
    @if($loan->isDepositPaid())
      <span class="badge badge-success">Fully Paid</span>
    @else
      <span class="badge badge-warning">Pending</span>
    @endif
  </p>
</div>
```

## How It Works

### Loan Application Flow
1. Customer applies for a loan via mobile app
2. System calculates 10% deposit amount
3. Loan is created with status 'pending'
4. Customer pays deposit via M-PESA or cash
5. Deposit record is created with:
   - `type = 'loan_deposit'`
   - `loan_id` linked to the loan
   - `status = 'completed'` (when payment succeeds)
6. Loan's `deposit_paid` field is updated
7. Admin can approve loan after deposit is paid

### Admin Visibility
- **Loan Detail Page**: Shows all deposits related to the loan
- **Deposits List**: Can filter by type to see only loan deposits
- **Reports**: Can track total loan deposits vs registration deposits

## Deposit Types

| Type | Description | Use Case |
|------|-------------|----------|
| `registration` | Customer registration fee | When new customer signs up |
| `loan_deposit` | 10% loan application deposit | When customer applies for loan |
| `savings` | Customer savings deposit | Future feature for savings accounts |

## Useful Helper Methods

### On Loan Model
```php
$loan->isDepositPaid(); // Returns true/false
$loan->getRemainingDepositAmount(); // Returns remaining amount
$loan->calculateDepositAmount(); // Returns required deposit (10%)
$loan->deposits; // Get all deposits for this loan
```

### On Deposit Model
```php
$deposit->loan; // Get the related loan
$deposit->customer; // Get the customer
$deposit->recorder; // Get the admin who recorded it
```

## Migration Details
Migration file: `2025_10_25_094116_add_loan_id_and_type_to_deposits_table.php`

The migration:
- Adds `type` enum column with default 'registration'
- The `loan_id` column already existed from previous migrations
- Safe to rollback - only removes `type` column, keeps `loan_id`

## Testing the Implementation

```bash
# 1. Check deposits table structure
php artisan db:show deposits

# 2. Create a test loan deposit
curl -X POST http://192.168.100.20:8000/api/deposits/cash \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "loan_id": 1,
    "amount": 5000,
    "phone_number": "0712345678",
    "notes": "Test deposit"
  }'

# 3. View all loan deposits
curl http://192.168.100.20:8000/api/deposits?type=loan_deposit \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Next Steps

1. Update your admin panel views to display loan deposits
2. Add filtering options in the deposits list page
3. Create reports showing deposit collection statistics
4. Add notifications for when deposits are paid
5. Consider adding a "pending deposits" dashboard widget

## Support

If you encounter any issues:
1. Check the migration ran successfully: `php artisan migrate:status`
2. Verify the deposits table has the `type` column
3. Check API responses include the new fields
4. Review logs at `storage/logs/laravel.log`
