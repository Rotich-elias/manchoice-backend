# Quick Start: Loan-Product Integration

## For Flutter Developers

### What Changed?

Loans can now include products! When a loan is approved, product stock is automatically deducted.

---

## Creating a Loan with Products

### API Request

```dart
// 1. Let user select products
List<Map<String, int>> selectedProducts = [
  {'product_id': 1, 'quantity': 2},
  {'product_id': 3, 'quantity': 1},
];

// 2. Create loan with products
final response = await http.post(
  Uri.parse('http://192.168.100.65:8000/api/loans'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: json.encode({
    'customer_id': customerId,
    'principal_amount': 5000,
    'interest_rate': 10,
    'duration_days': 30,
    'purpose': 'Purchase motorcycle parts',
    'items': selectedProducts,  // <-- NEW!
  }),
);
```

### Response

```json
{
  "success": true,
  "message": "Loan created successfully",
  "data": {
    "id": 1,
    "loan_number": "LN20251016001",
    "customer_id": 1,
    "principal_amount": "5000.00",
    "total_amount": "5500.00",
    "status": "pending",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "quantity": 2,
        "unit_price": "2500.00",
        "subtotal": "5000.00",
        "product": {
          "id": 1,
          "name": "Motorcycle Chain",
          "price": "2500.00",
          "stock_quantity": 13,
          "image_url": "http://..."
        }
      }
    ],
    "total_products_value": 5000.00
  }
}
```

---

## When Loan is Approved

### Admin Side
1. Admin reviews loan application
2. Admin clicks "Approve"
3. System checks if products are in stock
4. If yes: Approves loan + Deducts stock
5. If no: Blocks approval + Shows which products are out of stock

### Your App Side
When fetching approved loans, you'll see updated stock levels in the product data.

---

## UI Flow Suggestion

### 1. Product Selection Screen
```
┌─────────────────────────────┐
│ Select Products for Loan   │
├─────────────────────────────┤
│ □ Motorcycle Chain          │
│   KSh 2,500 • 13 in stock  │
│   [  -  ]  2  [  +  ]      │
│                             │
│ □ Side Mirror              │
│   KSh 2,999 • 20 in stock  │
│   [  -  ]  1  [  +  ]      │
│                             │
├─────────────────────────────┤
│ Total: KSh 8,499           │
│ 3 items selected           │
│                             │
│ [   Create Loan Request  ] │
└─────────────────────────────┘
```

### 2. Loan Details Screen
```
┌─────────────────────────────┐
│ Loan #LN20251016001        │
├─────────────────────────────┤
│ Status: Pending Approval    │
│ Amount: KSh 5,500          │
│                             │
│ Products:                   │
│ • Motorcycle Chain (2x)    │
│   KSh 5,000               │
│ • Side Mirror (1x)         │
│   KSh 2,999               │
│                             │
│ Total Products: KSh 8,499  │
└─────────────────────────────┘
```

---

## Stock Validation

If admin tries to approve but stock is insufficient:

### Error Response
```json
{
  "success": false,
  "message": "Insufficient stock for some products",
  "insufficient_stock": [
    {
      "product": "Motorcycle Chain",
      "required": 20,
      "available": 13
    }
  ]
}
```

### Your App Should:
1. Show error to admin
2. Display which products don't have enough stock
3. Allow admin to:
   - Wait for restock
   - Reduce quantity in loan
   - Reject the loan

---

## Key Points

1. **Products are OPTIONAL** in loans
   - Old loans without products still work
   - Can create loans without products

2. **Stock deducted ONLY on approval**
   - Creating loan = no stock change
   - Pending loan = no stock reserved
   - Approved loan = stock deducted

3. **Historical pricing**
   - Product price locked at loan creation
   - Future price changes don't affect existing loans

4. **Can't delete products in active loans**
   - Database constraint prevents deletion
   - Archive/disable instead

---

## Testing

### Test Data
```bash
# Get products with stock
GET http://192.168.100.65:8000/api/products?in_stock=true

# Get product categories
GET http://192.168.100.65:8000/api/products/categories

# Create loan with products
POST http://192.168.100.65:8000/api/loans
{
  "customer_id": 1,
  "principal_amount": 5000,
  "interest_rate": 10,
  "duration_days": 30,
  "items": [
    {"product_id": 1, "quantity": 2}
  ]
}
```

---

## Need Help?

- Full documentation: `LOAN_PRODUCTS_INTEGRATION.md`
- Product API docs: `PRODUCTS_API_FLUTTER.md`
- Category setup: `FLUTTER_CATEGORY_EXAMPLE.dart`

---

## Migration Status

✅ Database migrated
✅ Models updated
✅ API endpoints ready
✅ Stock management working
✅ Ready for Flutter integration
