# Products API - Flutter Integration Guide

This guide explains how to integrate the products API with your Flutter application.

## Base URLs

### Local Development (Testing on same machine)
```
http://localhost:8000/api
```

### Local Network Testing (Flutter on phone/emulator)
```
http://192.168.100.65:8000/api
```

### Production (Update when deployed)
```
https://your-domain.com/api
```

## Authentication

The product browsing endpoints are **PUBLIC** - no authentication required!

Protected endpoints (create, update, delete) require Bearer token authentication:
```dart
headers: {
  'Authorization': 'Bearer YOUR_TOKEN',
  'Accept': 'application/json',
}
```

---

## API Endpoints

### 1. Get Available Categories

**Endpoint:** `GET /api/products/categories`

**Description:** Returns a list of all available product categories. Use this to populate category filters in your Flutter app.

**Example Request:**
```dart
final response = await http.get(
  Uri.parse('http://192.168.100.65:8000/api/products/categories'),
  headers: {'Accept': 'application/json'},
);
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    "Engine Parts",
    "Electrical",
    "Tires",
    "Body Parts",
    "Transmission",
    "Accessories",
    "Brakes",
    "Suspension",
    "Exhaust",
    "Filters",
    "Belts & Hoses",
    "Lighting",
    "Interior",
    "Exterior",
    "Tools",
    "Fluids & Chemicals",
    "Other"
  ]
}
```

---

### 2. Get All Products (Paginated)

**Endpoint:** `GET /api/products`

**Query Parameters:**
- `category` (optional) - Filter by category
- `available` (optional) - Filter by availability (true/false)
- `in_stock` (optional) - Filter by stock availability (true/false)
- `search` (optional) - Search by product name
- `page` (optional) - Page number (default: 1)

**Example Request:**
```dart
final response = await http.get(
  Uri.parse('http://192.168.100.65:8000/api/products?category=Engine Parts&page=1'),
  headers: {'Accept': 'application/json'},
);
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 16,
        "name": "SHARON",
        "description": null,
        "category": "Engine Parts",
        "price": "300.00",
        "original_price": "3000.00",
        "discount_percentage": 0,
        "image_path": null,
        "stock_quantity": 17,
        "is_available": true,
        "created_at": "2025-10-16T16:20:15.000000Z",
        "updated_at": "2025-10-16T16:20:31.000000Z",
        "deleted_at": null,
        "image_url": null
      },
      {
        "id": 8,
        "name": "side mirror",
        "description": "High quality side mirror",
        "category": "Body Parts",
        "price": "2999.00",
        "original_price": "3999.00",
        "discount_percentage": 25,
        "image_path": "https://ke.jumia.is/unsafe/fit-in/300x300/filters:fill(white)/product/89/9362/1.jpg",
        "stock_quantity": 20,
        "is_available": true,
        "created_at": "2025-10-16T11:41:13.000000Z",
        "updated_at": "2025-10-16T11:53:34.000000Z",
        "deleted_at": null,
        "image_url": "https://ke.jumia.is/unsafe/fit-in/300x300/filters:fill(white)/product/89/9362/1.jpg"
      }
    ],
    "first_page_url": "http://localhost:8000/api/products?page=1",
    "from": 1,
    "last_page": 2,
    "last_page_url": "http://localhost:8000/api/products?page=2",
    "links": [...],
    "next_page_url": "http://localhost:8000/api/products?page=2",
    "path": "http://localhost:8000/api/products",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 30
  }
}
```

---

### 3. Get Single Product

**Endpoint:** `GET /api/products/{id}`

**Example Request:**
```dart
final response = await http.get(
  Uri.parse('http://192.168.100.65:8000/api/products/8'),
  headers: {'Accept': 'application/json'},
);
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 8,
    "name": "side mirror",
    "description": "High quality side mirror",
    "category": "Body Parts",
    "price": "2999.00",
    "original_price": "3999.00",
    "discount_percentage": 25,
    "image_path": "https://ke.jumia.is/unsafe/fit-in/300x300/filters:fill(white)/product/89/9362/1.jpg",
    "stock_quantity": 20,
    "is_available": true,
    "created_at": "2025-10-16T11:41:13.000000Z",
    "updated_at": "2025-10-16T11:53:34.000000Z",
    "deleted_at": null,
    "image_url": "https://ke.jumia.is/unsafe/fit-in/300x300/filters:fill(white)/product/89/9362/1.jpg"
  }
}
```

---

### 4. Get Products by Category

**Endpoint:** `GET /api/products/category/{category}`

**Note:** To get the list of available categories, use the `/api/products/categories` endpoint (see endpoint #1).

**Example Request:**
```dart
final response = await http.get(
  Uri.parse('http://192.168.100.65:8000/api/products/category/Engine Parts'),
  headers: {'Accept': 'application/json'},
);
```

---

## Flutter Model Class

```dart
class Product {
  final int id;
  final String name;
  final String? description;
  final String? category;
  final double price;
  final double? originalPrice;
  final int discountPercentage;
  final String? imagePath;
  final int stockQuantity;
  final bool isAvailable;
  final DateTime createdAt;
  final DateTime updatedAt;
  final String? imageUrl; // Use this for displaying images

  Product({
    required this.id,
    required this.name,
    this.description,
    this.category,
    required this.price,
    this.originalPrice,
    required this.discountPercentage,
    this.imagePath,
    required this.stockQuantity,
    required this.isAvailable,
    required this.createdAt,
    required this.updatedAt,
    this.imageUrl,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      name: json['name'],
      description: json['description'],
      category: json['category'],
      price: double.parse(json['price'].toString()),
      originalPrice: json['original_price'] != null
          ? double.parse(json['original_price'].toString())
          : null,
      discountPercentage: json['discount_percentage'] ?? 0,
      imagePath: json['image_path'],
      stockQuantity: json['stock_quantity'],
      isAvailable: json['is_available'] == true || json['is_available'] == 1,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      imageUrl: json['image_url'],
    );
  }

  // Helper method to check if product has discount
  bool get hasDiscount => discountPercentage > 0 && originalPrice != null;

  // Helper method to check if in stock
  bool get inStock => stockQuantity > 0 && isAvailable;
}
```

---

## Flutter Service Class Example

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ProductService {
  static const String baseUrl = 'http://192.168.100.65:8000/api';

  // Get all products
  Future<List<Product>> getProducts({
    String? category,
    bool? available,
    bool? inStock,
    String? search,
    int page = 1,
  }) async {
    var uri = Uri.parse('$baseUrl/products');

    Map<String, String> queryParams = {'page': page.toString()};
    if (category != null) queryParams['category'] = category;
    if (available != null) queryParams['available'] = available.toString();
    if (inStock != null) queryParams['in_stock'] = inStock.toString();
    if (search != null) queryParams['search'] = search;

    uri = uri.replace(queryParameters: queryParams);

    final response = await http.get(
      uri,
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      final List productsJson = jsonData['data']['data'];
      return productsJson.map((json) => Product.fromJson(json)).toList();
    } else {
      throw Exception('Failed to load products');
    }
  }

  // Get single product
  Future<Product> getProduct(int id) async {
    final response = await http.get(
      Uri.parse('$baseUrl/products/$id'),
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      return Product.fromJson(jsonData['data']);
    } else {
      throw Exception('Failed to load product');
    }
  }

  // Get products by category
  Future<List<Product>> getProductsByCategory(String category) async {
    final response = await http.get(
      Uri.parse('$baseUrl/products/category/$category'),
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      final List productsJson = jsonData['data']['data'];
      return productsJson.map((json) => Product.fromJson(json)).toList();
    } else {
      throw Exception('Failed to load products');
    }
  }

  // Get available categories
  Future<List<String>> getCategories() async {
    final response = await http.get(
      Uri.parse('$baseUrl/products/categories'),
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      final List categoriesJson = jsonData['data'];
      return categoriesJson.map((cat) => cat.toString()).toList();
    } else {
      throw Exception('Failed to load categories');
    }
  }
}
```

---

## Displaying Images in Flutter

Use the `image_url` field from the API response:

```dart
// For network images
Image.network(
  product.imageUrl ?? 'https://via.placeholder.com/150',
  fit: BoxFit.cover,
  errorBuilder: (context, error, stackTrace) {
    return Icon(Icons.image_not_supported);
  },
  loadingBuilder: (context, child, loadingProgress) {
    if (loadingProgress == null) return child;
    return Center(
      child: CircularProgressIndicator(
        value: loadingProgress.expectedTotalBytes != null
            ? loadingProgress.cumulativeBytesLoaded /
                loadingProgress.expectedTotalBytes!
            : null,
      ),
    );
  },
)

// Or use cached_network_image package for better performance
CachedNetworkImage(
  imageUrl: product.imageUrl ?? '',
  placeholder: (context, url) => CircularProgressIndicator(),
  errorWidget: (context, url, error) => Icon(Icons.error),
  fit: BoxFit.cover,
)
```

---

## Important Notes

### 1. Image URLs
- The API automatically returns the full image URL in the `image_url` field
- For uploaded images: `http://192.168.100.65:8000/storage/products/filename.jpg`
- For external images: Returns the original URL as-is

### 2. Network Permissions

**Android** - Add to `android/app/src/main/AndroidManifest.xml`:
```xml
<uses-permission android:name="android.permission.INTERNET"/>
```

**iOS** - Add to `ios/Runner/Info.plist`:
```xml
<key>NSAppTransportSecurity</key>
<dict>
    <key>NSAllowsArbitraryLoads</key>
    <true/>
</dict>
```

### 3. Base URL Configuration
For different environments, use a config class:
```dart
class ApiConfig {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://192.168.100.65:8000/api',
  );
}
```

### 4. Error Handling
Always handle HTTP errors and network issues:
```dart
try {
  final products = await productService.getProducts();
  // Use products
} on SocketException {
  // No internet connection
  print('No internet connection');
} on HttpException {
  // Server error
  print('Server error');
} on FormatException {
  // Invalid response format
  print('Invalid response');
} catch (e) {
  // Other errors
  print('Error: $e');
}
```

### 5. Testing
You can test the API directly from your browser or Postman:
- List all products: `http://192.168.100.65:8000/api/products`
- Single product: `http://192.168.100.65:8000/api/products/8`
- By category: `http://192.168.100.65:8000/api/products/category/Engine%20Parts`

---

## CORS Configuration

CORS is already configured to allow requests from any origin. If you encounter CORS issues, verify:
1. You're using the correct IP address (not localhost when testing on device)
2. The Laravel server is running
3. Your Flutter app has internet permissions

---

## Troubleshooting

### Issue: Cannot connect to API from Flutter app
**Solution:** Make sure both your computer and phone/emulator are on the same network, and use the IP address (192.168.100.65:8000) instead of localhost.

### Issue: Images not loading
**Solution:** Check that:
- The `image_url` field is not null
- Your app has internet permissions
- The storage symlink exists: `php artisan storage:link`

### Issue: Empty response or 404
**Solution:** Verify the API is accessible:
```bash
curl http://192.168.100.65:8000/api/products
```

---

## Contact & Support

For backend issues, check:
- Laravel logs: `storage/logs/laravel.log`
- Server status: `php artisan serve`
- Database connection: `php artisan tinker`
