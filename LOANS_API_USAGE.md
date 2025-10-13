# Loans API Documentation

## Overview
The Loans API now supports full CRUD operations with sorting and filtering capabilities.

## Authentication
All endpoints require Bearer token authentication:
```bash
Authorization: Bearer YOUR_TOKEN_HERE
```

## Endpoints

### 1. List All Loans with Filtering & Sorting
**GET** `/api/loans`

#### Query Parameters:

**Filtering:**
- `customer_id` - Filter by specific customer ID
- `customer_name` - Search by customer name (partial match)
- `status` - Filter by loan status (pending, approved, active, completed, defaulted, cancelled)
- `overdue` - Set to "true" to show only overdue loans

**Sorting:**
- `sort_by` - Column to sort by:
  - `customer` or `customer_name` - Sort by customer name
  - `amount` or `total_amount` - Sort by loan amount
  - `due_date` - Sort by due date
  - `balance` - Sort by remaining balance
  - `principal_amount` - Sort by principal amount
  - `created_at` - Sort by creation date (default)
- `sort_order` - Sort direction: `asc` or `desc` (default: `desc`)

#### Examples:

```bash
# Get all loans
curl -X GET "http://localhost:8000/api/loans" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by customer name
curl -X GET "http://localhost:8000/api/loans?customer_name=John" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Sort by due date (ascending)
curl -X GET "http://localhost:8000/api/loans?sort_by=due_date&sort_order=asc" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Sort by customer name
curl -X GET "http://localhost:8000/api/loans?sort_by=customer&sort_order=asc" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Sort by amount (descending)
curl -X GET "http://localhost:8000/api/loans?sort_by=amount&sort_order=desc" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by customer name AND sort by due date
curl -X GET "http://localhost:8000/api/loans?customer_name=John&sort_by=due_date&sort_order=asc" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get overdue loans sorted by due date
curl -X GET "http://localhost:8000/api/loans?overdue=true&sort_by=due_date&sort_order=asc" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 2. Get Single Loan
**GET** `/api/loans/{loan_id}`

```bash
curl -X GET "http://localhost:8000/api/loans/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 3. Edit/Update Loan
**PUT/PATCH** `/api/loans/{loan_id}`

#### Updatable Fields:
- `principal_amount` - Loan principal amount
- `interest_rate` - Interest rate (0-100)
- `duration_days` - Loan duration in days
- `due_date` - Due date
- `purpose` - Loan purpose
- `notes` - Additional notes
- `status` - Loan status (pending, approved, active, completed, defaulted, cancelled)

**Note:** When updating `principal_amount` or `interest_rate`, the total amount and balance are automatically recalculated.

#### Example:

```bash
# Update loan amount and due date
curl -X PUT "http://localhost:8000/api/loans/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "principal_amount": 150000,
    "interest_rate": 15,
    "due_date": "2025-12-31"
  }'

# Update loan status
curl -X PATCH "http://localhost:8000/api/loans/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "active",
    "notes": "Loan activated after verification"
  }'
```

---

### 4. Delete Loan
**DELETE** `/api/loans/{loan_id}`

**Important:** Only loans with status `pending`, `rejected`, or `cancelled` can be deleted. Active or approved loans cannot be deleted for data integrity.

```bash
curl -X DELETE "http://localhost:8000/api/loans/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Loan deleted successfully"
}
```

**Response (Error - Cannot Delete):**
```json
{
  "success": false,
  "message": "Cannot delete approved or active loans"
}
```

---

### 5. Approve Loan
**POST** `/api/loans/{loan_id}/approve`

```bash
curl -X POST "http://localhost:8000/api/loans/1/approve" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 6. Reject Loan
**POST** `/api/loans/{loan_id}/reject`

```bash
curl -X POST "http://localhost:8000/api/loans/1/reject" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "rejection_reason": "Insufficient credit history"
  }'
```

---

## Response Format

All endpoints return JSON responses in this format:

**Success:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error message"
}
```

---

## Loan Status Values

- `pending` - Loan application submitted, awaiting approval
- `approved` - Loan approved by admin
- `active` - Loan is active and being repaid
- `completed` - Loan fully repaid
- `defaulted` - Loan defaulted
- `cancelled` - Loan cancelled
- `rejected` - Loan application rejected

---

## Common Use Cases

### Admin Dashboard - View All Loans Sorted by Due Date
```bash
curl -X GET "http://localhost:8000/api/loans?sort_by=due_date&sort_order=asc" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Find Customer's Loans
```bash
curl -X GET "http://localhost:8000/api/loans?customer_name=Mary" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### View Highest Loans First
```bash
curl -X GET "http://localhost:8000/api/loans?sort_by=amount&sort_order=desc" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Update Loan Terms
```bash
curl -X PUT "http://localhost:8000/api/loans/5" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "principal_amount": 200000,
    "interest_rate": 12,
    "due_date": "2025-11-30",
    "notes": "Extended payment period as per agreement"
  }'
```

### Delete Pending Loan Application
```bash
curl -X DELETE "http://localhost:8000/api/loans/10" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
