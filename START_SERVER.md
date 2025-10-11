# How to Run MAN'S CHOICE Backend

## Quick Start

### Option 1: Simple Server (Recommended for Testing)
```bash
./artisan.sh serve
```
Server will run at: **http://localhost:8000**

### Option 2: Full Development Stack (All Services)
```bash
composer dev
```
This starts:
- Laravel server (http://localhost:8000)
- Queue worker (for background jobs)
- Log viewer (Pail)
- Vite dev server (for assets)

### Option 3: Background Server
```bash
./artisan.sh serve --host=0.0.0.0 --port=8000 &
```

## Testing the API

### 1. Register a User
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@manschoice.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Save the token from the response!**

### 2. Login (if needed)
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@manschoice.com",
    "password": "password123"
  }'
```

### 3. Create a Customer
```bash
curl -X POST http://localhost:8000/api/customers \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "phone": "254712345678",
    "email": "john@example.com",
    "id_number": "12345678",
    "address": "Nairobi, Kenya",
    "credit_limit": 50000
  }'
```

### 4. Create a Loan
```bash
curl -X POST http://localhost:8000/api/loans \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "principal_amount": 10000,
    "interest_rate": 10,
    "duration_days": 30,
    "purpose": "Business expansion"
  }'
```

### 5. Approve the Loan
```bash
curl -X POST http://localhost:8000/api/loans/1/approve \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### 6. List All Loans
```bash
curl -X GET http://localhost:8000/api/loans \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 7. Record a Payment
```bash
curl -X POST http://localhost:8000/api/payments \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "loan_id": 1,
    "amount": 5000,
    "payment_method": "cash",
    "payment_date": "2025-10-10"
  }'
```

## Useful Commands

### Check Database Connection
```bash
./artisan.sh db:show
```

### View All Customers
```bash
./artisan.sh tinker --execute="App\Models\Customer::all();"
```

### View All Loans
```bash
./artisan.sh tinker --execute="App\Models\Loan::with('customer')->get();"
```

### Clear Cache
```bash
./artisan.sh cache:clear
./artisan.sh config:clear
```

### Stop the Server
Press `Ctrl + C` in the terminal where the server is running

## Troubleshooting

### Server won't start?
```bash
# Check if port 8000 is already in use
lsof -i :8000

# Kill the process if needed
kill -9 <PID>

# Or use a different port
./artisan.sh serve --port=8001
```

### Database connection error?
```bash
# Always use ./artisan.sh instead of php artisan
./artisan.sh db:show

# Or manually unset the environment variable
unset DB_CONNECTION && php artisan serve
```

### Need to reset the database?
```bash
./artisan.sh migrate:fresh
```

## API Documentation

All endpoints are prefixed with `/api`

**Public Endpoints:**
- POST `/api/register` - Register new user
- POST `/api/login` - Login user

**Protected Endpoints (require Bearer token):**
- Customers: `/api/customers`
- Loans: `/api/loans`
- Payments: `/api/payments`
- M-PESA: `/api/mpesa/*`

See README.md for complete endpoint documentation.
