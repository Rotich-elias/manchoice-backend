# MAN'S CHOICE ENTERPRISE - Credit Management System Backend

A comprehensive Laravel backend API for managing credit/loan operations with integrated M-PESA payment processing.

## Features

- **Authentication System**: Secure JWT authentication using Laravel Sanctum
- **Customer Management**: Full CRUD operations for customer records
- **Loan Management**: Create, approve, and track loans
- **Payment Processing**: Record payments with multiple methods
- **M-PESA Integration**: Automated STK Push payment processing
- **Real-time Tracking**: Track loan balances, payments, and overdue loans

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or SQLite
- Laravel 12.x

## Installation

### 1. Clone and Install Dependencies

```bash
cd /home/smith/Desktop/myproject/manchoice-backend
composer install
```

### 2. Configure Environment

The `.env` file is already configured to use MySQL:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mans_choice_db
DB_USERNAME=manchoice_user
DB_PASSWORD=StrongPass123!
```

**Note**: The database and user have already been created. For production, update the password to a more secure value.

### 3. Configure M-PESA Credentials

Update the following in your `.env` file with your Safaricom M-PESA credentials:

```bash
MPESA_ENV=sandbox  # or 'production' for live
MPESA_CONSUMER_KEY=your_consumer_key_here
MPESA_CONSUMER_SECRET=your_consumer_secret_here
MPESA_SHORTCODE=your_paybill_number
MPESA_PASSKEY=your_passkey_here
```

To get M-PESA credentials:
1. Visit https://developer.safaricom.co.ke/
2. Create an account and a new app
3. Copy your Consumer Key and Consumer Secret
4. Get your Paybill/Till number and Passkey

### 4. Run Migrations

Migrations have already been run. If you need to run them again:

```bash
./artisan.sh migrate
```

**Important**: Use `./artisan.sh` instead of `php artisan` to ensure the correct database connection is used.

Alternatively, you can use the `art` alias in your terminal:
```bash
art migrate  # After opening a new terminal
```

### 5. Start Development Server

```bash
./artisan.sh serve
# Or using composer:
composer dev
```

The API will be available at `http://localhost:8000`

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | Login user |
| POST | `/api/logout` | Logout user (Auth) |
| GET | `/api/user` | Get current user (Auth) |

### Customers

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/customers` | List all customers (Auth) |
| POST | `/api/customers` | Create customer (Auth) |
| GET | `/api/customers/{id}` | Get customer details (Auth) |
| PUT | `/api/customers/{id}` | Update customer (Auth) |
| DELETE | `/api/customers/{id}` | Delete customer (Auth) |
| GET | `/api/customers/{id}/stats` | Get customer statistics (Auth) |

### Loans

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/loans` | List all loans (Auth) |
| POST | `/api/loans` | Create loan (Auth) |
| GET | `/api/loans/{id}` | Get loan details (Auth) |
| PUT | `/api/loans/{id}` | Update loan (Auth) |
| DELETE | `/api/loans/{id}` | Delete loan (Auth) |
| POST | `/api/loans/{id}/approve` | Approve loan (Auth) |

### Payments

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/payments` | List all payments (Auth) |
| POST | `/api/payments` | Record payment (Auth) |
| GET | `/api/payments/{id}` | Get payment details (Auth) |
| PUT | `/api/payments/{id}` | Update payment (Auth) |
| DELETE | `/api/payments/{id}` | Delete payment (Auth) |
| POST | `/api/payments/{id}/reverse` | Reverse payment (Auth) |

### M-PESA Integration

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/mpesa/stk-push` | Initiate STK Push (Auth) |
| POST | `/api/mpesa/check-status` | Check payment status (Auth) |
| POST | `/api/mpesa/callback` | M-PESA callback (Public) |
| POST | `/api/mpesa/timeout` | M-PESA timeout (Public) |
| POST | `/api/mpesa/result` | M-PESA result (Public) |

## Example API Usage

### 1. Register/Login

```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

### 2. Create Customer

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

### 3. Create Loan

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

### 4. Initiate M-PESA Payment

```bash
curl -X POST http://localhost:8000/api/mpesa/stk-push \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "loan_id": 1,
    "phone_number": "254712345678",
    "amount": 5000
  }'
```

## Database Schema

### Customers Table
- id, name, email, phone, id_number
- address, business_name, status
- credit_limit, total_borrowed, total_paid, loan_count
- notes, timestamps, deleted_at

### Loans Table
- id, customer_id, loan_number
- principal_amount, interest_rate, total_amount
- amount_paid, balance, status
- disbursement_date, due_date, duration_days
- purpose, notes, approved_by, approved_at
- timestamps, deleted_at

### Payments Table
- id, loan_id, customer_id
- transaction_id, mpesa_receipt_number
- amount, payment_method, status
- payment_date, phone_number, notes
- recorded_by, timestamps, deleted_at

## Security

- All API endpoints (except auth and M-PESA callbacks) require authentication
- Passwords are hashed using bcrypt
- API tokens are managed by Laravel Sanctum
- Input validation on all endpoints
- CORS configured for Flutter app integration

## Testing

```bash
# Run tests
./artisan.sh test
# Or using composer:
composer test

# Check routes
./artisan.sh route:list
```

## Troubleshooting

### Database Connection Issues

If you see SQLite being used instead of MySQL when running `php artisan db:show`:

1. **Use the wrapper script**: Always use `./artisan.sh` instead of `php artisan`
2. **Use the composer scripts**: Run `composer dev` instead of manual commands
3. **Use the alias**: Open a new terminal and use `art` command (e.g., `art db:show`)
4. **Manual override**: Prefix commands with `unset DB_CONNECTION &&`
   ```bash
   unset DB_CONNECTION && php artisan db:show
   ```

The issue occurs because there's a system environment variable `DB_CONNECTION=sqlite` that overrides the `.env` file. The solutions above ensure the `.env` file takes precedence.

## Production Deployment

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Generate production key: `php artisan key:generate`
4. Switch to MySQL database
5. Configure proper M-PESA credentials (production environment)
6. Set up SSL certificate
7. Configure web server (Nginx/Apache)

## Support

For issues or questions, contact: support@manschoice.co.ke

## License

Proprietary - MAN'S CHOICE ENTERPRISE
