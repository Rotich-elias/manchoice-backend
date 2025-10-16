# Loan-Products Integration Guide

## Overview

The loan system now supports linking products to loan applications. When a loan is approved, the product stock is automatically deducted. This creates a complete inventory management system tied to your loan/credit system.

---

## Database Structure

### New Table: `loan_items`

Links loans with products and tracks quantities.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| loan_id | bigint | Foreign key to loans table |
| product_id | bigint | Foreign key to products table |
| quantity | integer | Number of units |
| unit_price | decimal(15,2) | Price at time of loan |
| subtotal | decimal(15,2) | quantity × unit_price |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## How It Works

### 1. Create Loan Application with Products

When creating a loan application, you can now include products:

```json
POST /api/loans

{
  "customer_id": 1,
  "principal_amount": 50000,
  "interest_rate": 10,
  "duration_days": 30,
  "purpose": "Purchase motorcycle parts",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ]
}
```

### 2. Loan Approval with Stock Validation

When admin approves the loan:
- System checks if all products are in stock
- If insufficient stock, approval is blocked
- If stock available, loan is approved AND stock is deducted

```json
POST /api/loans/{loan_id}/approve

Response (Success):
{
  "success": true,
  "message": "Loan approved successfully and stock deducted",
  "data": {
    "id": 1,
    "loan_number": "LN20251016001",
    "status": "approved",
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
          "stock_quantity": 11  // Reduced from 13
        }
      }
    ]
  }
}

Response (Insufficient Stock):
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

### 3. View Loan with Products

```json
GET /api/loans/{loan_id}

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "loan_number": "LN20251016001",
    "customer": { ... },
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
          "stock_quantity": 11,
          "category": "Engine Parts",
          "image_url": "http://..."
        }
      }
    ],
    "total_products_value": 5000.00
  }
}
```

---

## Flutter Integration

### Models

```dart
class LoanItem {
  final int id;
  final int loanId;
  final int productId;
  final int quantity;
  final double unitPrice;
  final double subtotal;
  final Product? product;

  LoanItem({
    required this.id,
    required this.loanId,
    required this.productId,
    required this.quantity,
    required this.unitPrice,
    required this.subtotal,
    this.product,
  });

  factory LoanItem.fromJson(Map<String, dynamic> json) {
    return LoanItem(
      id: json['id'],
      loanId: json['loan_id'],
      productId: json['product_id'],
      quantity: json['quantity'],
      unitPrice: double.parse(json['unit_price'].toString()),
      subtotal: double.parse(json['subtotal'].toString()),
      product: json['product'] != null
          ? Product.fromJson(json['product'])
          : null,
    );
  }
}

class Loan {
  final int id;
  final String loanNumber;
  final double principalAmount;
  final double totalAmount;
  final double balance;
  final String status;
  final List<LoanItem>? items;
  // ... other fields

  double get totalProductsValue {
    if (items == null) return 0.0;
    return items!.fold(0.0, (sum, item) => sum + item.subtotal);
  }

  factory Loan.fromJson(Map<String, dynamic> json) {
    return Loan(
      id: json['id'],
      loanNumber: json['loan_number'],
      principalAmount: double.parse(json['principal_amount'].toString()),
      totalAmount: double.parse(json['total_amount'].toString()),
      balance: double.parse(json['balance'].toString()),
      status: json['status'],
      items: json['items'] != null
          ? (json['items'] as List)
              .map((item) => LoanItem.fromJson(item))
              .toList()
          : null,
      // ... other fields
    );
  }
}
```

### Service Methods

```dart
class LoanService {
  static const String baseUrl = 'http://192.168.100.65:8000/api';

  // Create loan with products
  Future<Loan> createLoanWithProducts({
    required int customerId,
    required double principalAmount,
    required double interestRate,
    required int durationDays,
    required String purpose,
    required List<LoanItemRequest> items,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/loans'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'customer_id': customerId,
        'principal_amount': principalAmount,
        'interest_rate': interestRate,
        'duration_days': durationDays,
        'purpose': purpose,
        'items': items.map((item) => {
          'product_id': item.productId,
          'quantity': item.quantity,
        }).toList(),
      }),
    );

    if (response.statusCode == 201) {
      final jsonData = json.decode(response.body);
      return Loan.fromJson(jsonData['data']);
    } else {
      throw Exception('Failed to create loan');
    }
  }

  // Approve loan
  Future<Loan> approveLoan(int loanId) async {
    final response = await http.post(
      Uri.parse('$baseUrl/loans/$loanId/approve'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      return Loan.fromJson(jsonData['data']);
    } else if (response.statusCode == 400) {
      final jsonData = json.decode(response.body);
      // Check for insufficient stock error
      if (jsonData['insufficient_stock'] != null) {
        throw InsufficientStockException(
          jsonData['message'],
          jsonData['insufficient_stock'],
        );
      }
      throw Exception(jsonData['message']);
    } else {
      throw Exception('Failed to approve loan');
    }
  }
}

class LoanItemRequest {
  final int productId;
  final int quantity;

  LoanItemRequest({required this.productId, required this.quantity});
}

class InsufficientStockException implements Exception {
  final String message;
  final List<dynamic> insufficientItems;

  InsufficientStockException(this.message, this.insufficientItems);
}
```

### UI Example: Product Selection for Loan

```dart
class LoanProductSelection extends StatefulWidget {
  @override
  _LoanProductSelectionState createState() => _LoanProductSelectionState();
}

class _LoanProductSelectionState extends State<LoanProductSelection> {
  List<Product> _availableProducts = [];
  List<LoanItemRequest> _selectedItems = [];
  double _totalAmount = 0.0;

  @override
  void initState() {
    super.initState();
    _loadProducts();
  }

  Future<void> _loadProducts() async {
    // Load products with in_stock filter
    final products = await ProductService().getProducts(inStock: true);
    setState(() {
      _availableProducts = products;
    });
  }

  void _addProduct(Product product, int quantity) {
    setState(() {
      _selectedItems.add(
        LoanItemRequest(productId: product.id, quantity: quantity)
      );
      _totalAmount += product.price * quantity;
    });
  }

  void _removeProduct(int index) {
    final item = _selectedItems[index];
    final product = _availableProducts.firstWhere(
      (p) => p.id == item.productId
    );
    setState(() {
      _totalAmount -= product.price * item.quantity;
      _selectedItems.removeAt(index);
    });
  }

  Future<void> _createLoan() async {
    try {
      final loan = await LoanService().createLoanWithProducts(
        customerId: widget.customerId,
        principalAmount: _totalAmount,
        interestRate: 10.0,
        durationDays: 30,
        purpose: 'Purchase of motorcycle parts',
        items: _selectedItems,
      );

      Navigator.pop(context, loan);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Loan application created successfully')),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Select Products')),
      body: Column(
        children: [
          // Product selection list
          Expanded(
            child: ListView.builder(
              itemCount: _availableProducts.length,
              itemBuilder: (context, index) {
                final product = _availableProducts[index];
                return ProductTile(
                  product: product,
                  onAdd: (quantity) => _addProduct(product, quantity),
                );
              },
            ),
          ),
          // Selected items summary
          Container(
            padding: EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.grey[200],
              border: Border(top: BorderSide(color: Colors.grey)),
            ),
            child: Column(
              children: [
                Text(
                  'Total: KSh ${_totalAmount.toStringAsFixed(2)}',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SizedBox(height: 8),
                Text(
                  '${_selectedItems.length} items selected',
                  style: TextStyle(color: Colors.grey[600]),
                ),
                SizedBox(height: 16),
                ElevatedButton(
                  onPressed: _selectedItems.isEmpty ? null : _createLoan,
                  child: Text('Create Loan Application'),
                  style: ElevatedButton.styleFrom(
                    minimumSize: Size(double.infinity, 50),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
```

---

## Stock Management Flow

### Approval Flow

```
1. Admin reviews loan application
2. Admin clicks "Approve"
3. Backend validates stock for all items
4. If stock sufficient:
   - Deduct stock from each product
   - Update product availability if stock reaches 0
   - Approve loan
   - Return success
5. If stock insufficient:
   - Block approval
   - Return list of products with insufficient stock
   - Admin can reduce quantities or wait for restock
```

### Stock Restoration (Future Enhancement)

If you want to restore stock when loans are cancelled/rejected:

```php
// In LoanController::reject() method
foreach ($loan->items as $item) {
    $product = $item->product;
    $product->addStock($item->quantity);
}
```

---

## API Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/loans | Create loan (with optional items) |
| GET | /api/loans | List loans (includes items) |
| GET | /api/loans/{id} | Get loan details (includes items) |
| POST | /api/loans/{id}/approve | Approve loan (validates & deducts stock) |
| POST | /api/loans/{id}/reject | Reject loan |

---

## Testing

### Test Stock Deduction

```bash
# 1. Check initial stock
curl http://localhost:8000/api/products/1

# 2. Create loan with products
curl -X POST http://localhost:8000/api/loans \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "principal_amount": 5000,
    "interest_rate": 10,
    "duration_days": 30,
    "items": [
      {"product_id": 1, "quantity": 2}
    ]
  }'

# 3. Approve loan
curl -X POST http://localhost:8000/api/loans/1/approve \
  -H "Authorization: Bearer YOUR_TOKEN"

# 4. Check stock after approval
curl http://localhost:8000/api/products/1
# Stock should be reduced by 2
```

---

## Benefits

1. **Inventory Control**: Automatic stock management tied to loan approvals
2. **Accurate Records**: Track which products were part of each loan
3. **Historical Pricing**: Unit price stored at time of loan (price changes don't affect past loans)
4. **Stock Validation**: Prevents approval of loans for out-of-stock items
5. **Business Insights**: Analyze which products are frequently included in loans

---

## Important Notes

1. **Stock is only deducted on approval**, not when loan is created
2. **Pending loans don't affect available stock** (allows overselling prevention)
3. **Product deletion is blocked** if it's referenced in any loan (onDelete: restrict)
4. **Subtotals are calculated automatically** in the LoanItem model
5. **Loan items are deleted** when parent loan is deleted (cascade delete)

---

## Future Enhancements

1. Stock restoration on loan rejection/cancellation
2. Stock reservation on loan creation (pending status)
3. Partial deliveries (multiple stock deductions)
4. Product bundles/packages
5. Automatic reorder alerts when stock is low
