# Flutter Integration Guide

This guide will help you connect your Flutter app to the Man's Choice Enterprise backend API.

## Backend Setup (Already Completed)

- Server is running on: `http://0.0.0.0:8000` (accessible from network)
- CORS is configured to accept requests from any origin
- API routes are under `/api/` prefix
- Authentication uses Laravel Sanctum (Bearer token)

## Network Connection Details

### Local Development

**Your Local Network IP:** `192.168.100.65`

- **From Physical Device:** `http://192.168.100.65:8000`
- **From Android Emulator:** `http://10.0.2.2:8000`
- **From iOS Simulator:** `http://localhost:8000` or `http://127.0.0.1:8000`
- **From Web Browser:** `http://localhost:8000`

Make sure your phone/device and computer are on the same WiFi network when testing with physical devices.

---

## Flutter Implementation

### 1. Add Dependencies

Add these packages to your `pubspec.yaml`:

```yaml
dependencies:
  http: ^1.1.0
  shared_preferences: ^2.2.2
  provider: ^6.1.1  # For state management (optional)
```

Run:
```bash
flutter pub get
```

### 2. Create API Service Class

Create `lib/services/api_service.dart`:

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Change this based on your environment
  static const String baseUrl = 'http://192.168.100.65:8000/api';

  // For Android Emulator, use:
  // static const String baseUrl = 'http://10.0.2.2:8000/api';

  // For iOS Simulator, use:
  // static const String baseUrl = 'http://localhost:8000/api';

  String? _token;

  // Initialize token from storage
  Future<void> initToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
  }

  // Save token to storage
  Future<void> saveToken(String token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  // Remove token from storage
  Future<void> clearToken() async {
    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }

  // Get headers with authentication
  Map<String, String> _getHeaders({bool includeAuth = true}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (includeAuth && _token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }

    return headers;
  }

  // Handle API response
  dynamic _handleResponse(http.Response response) {
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return json.decode(response.body);
    } else if (response.statusCode == 401) {
      throw Exception('Unauthorized. Please login again.');
    } else {
      final body = json.decode(response.body);
      throw Exception(body['message'] ?? 'Request failed');
    }
  }

  // === AUTHENTICATION ===

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/register'),
      headers: _getHeaders(includeAuth: false),
      body: json.encode({
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
      }),
    );

    final data = _handleResponse(response);
    await saveToken(data['data']['access_token']);
    return data;
  }

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: _getHeaders(includeAuth: false),
      body: json.encode({
        'email': email,
        'password': password,
      }),
    );

    final data = _handleResponse(response);
    await saveToken(data['data']['access_token']);
    return data;
  }

  Future<Map<String, dynamic>> logout() async {
    final response = await http.post(
      Uri.parse('$baseUrl/logout'),
      headers: _getHeaders(),
    );

    final data = _handleResponse(response);
    await clearToken();
    return data;
  }

  Future<Map<String, dynamic>> getUser() async {
    final response = await http.get(
      Uri.parse('$baseUrl/user'),
      headers: _getHeaders(),
    );

    return _handleResponse(response);
  }

  // === CUSTOMERS ===

  Future<Map<String, dynamic>> getCustomers() async {
    final response = await http.get(
      Uri.parse('$baseUrl/customers'),
      headers: _getHeaders(),
    );

    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> createCustomer({
    required String name,
    required String phone,
    String? email,
    String? idNumber,
    String? address,
    double? creditLimit,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/customers'),
      headers: _getHeaders(),
      body: json.encode({
        'name': name,
        'phone': phone,
        'email': email,
        'id_number': idNumber,
        'address': address,
        'credit_limit': creditLimit,
      }),
    );

    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> getCustomer(int id) async {
    final response = await http.get(
      Uri.parse('$baseUrl/customers/$id'),
      headers: _getHeaders(),
    );

    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> updateCustomer(int id, Map<String, dynamic> data) async {
    final response = await http.put(
      Uri.parse('$baseUrl/customers/$id'),
      headers: _getHeaders(),
      body: json.encode(data),
    );

    return _handleResponse(response);
  }

  Future<void> deleteCustomer(int id) async {
    final response = await http.delete(
      Uri.parse('$baseUrl/customers/$id'),
      headers: _getHeaders(),
    );

    _handleResponse(response);
  }

  Future<Map<String, dynamic>> getCustomerStats(int id) async {
    final response = await http.get(
      Uri.parse('$baseUrl/customers/$id/stats'),
      headers: _getHeaders(),
    );

    return _handleResponse(response);
  }

  // === LOANS ===

  Future<Map<String, dynamic>> getLoans() async {
    final response = await http.get(
      Uri.parse('$baseUrl/loans'),
      headers: _getHeaders(),
    );

    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> createLoan({
    required int customerId,
    required double principalAmount,
    required double interestRate,
    required int durationDays,
    String? purpose,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/loans'),
      headers: _getHeaders(),
      body: json.encode({
        'customer_id': customerId,
        'principal_amount': principalAmount,
        'interest_rate': interestRate,
        'duration_days': durationDays,
        'purpose': purpose,
      }),
    );

    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> getLoan(int id) async {
    final response = await http.get(
      Uri.parse('$baseUrl/loans/$id'),
      headers: _getHeaders(),
    );

    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> approveLoan(int id) async {
    final response = await http.post(
      Uri.parse('$baseUrl/loans/$id/approve'),
      headers: _getHeaders(),
    );

    return _handleResponse(response);
  }

  // === PAYMENTS ===

  Future<Map<String, dynamic>> getPayments() async {
    final response = await http.get(
      Uri.parse('$baseUrl/payments'),
      headers: _getHeaders(),
    );

    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> createPayment({
    required int loanId,
    required double amount,
    required String paymentMethod,
    String? phoneNumber,
    String? notes,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/payments'),
      headers: _getHeaders(),
      body: json.encode({
        'loan_id': loanId,
        'amount': amount,
        'payment_method': paymentMethod,
        'phone_number': phoneNumber,
        'notes': notes,
      }),
    );

    return _handleResponse(response);
  }

  // === M-PESA ===

  Future<Map<String, dynamic>> initiateMpesaPayment({
    required int loanId,
    required String phoneNumber,
    required double amount,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/mpesa/stk-push'),
      headers: _getHeaders(),
      body: json.encode({
        'loan_id': loanId,
        'phone_number': phoneNumber,
        'amount': amount,
      }),
    );

    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> checkMpesaStatus(String checkoutRequestId) async {
    final response = await http.post(
      Uri.parse('$baseUrl/mpesa/check-status'),
      headers: _getHeaders(),
      body: json.encode({
        'checkout_request_id': checkoutRequestId,
      }),
    );

    return _handleResponse(response);
  }

  // === PRODUCTS ===

  Future<Map<String, dynamic>> getProducts() async {
    final response = await http.get(
      Uri.parse('$baseUrl/products'),
      headers: _getHeaders(),
    );

    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> createProduct({
    required String name,
    String? description,
    required double price,
    int? stockQuantity,
    String? category,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/products'),
      headers: _getHeaders(),
      body: json.encode({
        'name': name,
        'description': description,
        'price': price,
        'stock_quantity': stockQuantity,
        'category': category,
      }),
    );

    return _handleResponse(response);
  }
}
```

### 3. Create Authentication Provider (Optional)

Create `lib/providers/auth_provider.dart`:

```dart
import 'package:flutter/foundation.dart';
import '../services/api_service.dart';

class AuthProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();
  Map<String, dynamic>? _user;
  bool _isLoading = false;
  String? _error;

  Map<String, dynamic>? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _user != null;

  AuthProvider() {
    _init();
  }

  Future<void> _init() async {
    await _apiService.initToken();
    await checkAuth();
  }

  Future<void> checkAuth() async {
    try {
      final response = await _apiService.getUser();
      _user = response['data'];
      notifyListeners();
    } catch (e) {
      _user = null;
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.login(email: email, password: password);
      _user = response['data']['user'];
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> register(String name, String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.register(
        name: name,
        email: email,
        password: password,
      );
      _user = response['data']['user'];
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    try {
      await _apiService.logout();
    } catch (e) {
      // Ignore errors on logout
    }
    _user = null;
    notifyListeners();
  }
}
```

### 4. Example Login Screen

Create `lib/screens/login_screen.dart`:

```dart
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _apiService = ApiService();
  bool _isLoading = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      await _apiService.initToken();
      final response = await _apiService.login(
        email: _emailController.text.trim(),
        password: _passwordController.text,
      );

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(response['message'] ?? 'Login successful'),
          backgroundColor: Colors.green,
        ),
      );

      // Navigate to home screen
      // Navigator.pushReplacement(context, ...);
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString()),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Login'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              TextFormField(
                controller: _emailController,
                decoration: const InputDecoration(
                  labelText: 'Email',
                  border: OutlineInputBorder(),
                ),
                keyboardType: TextInputType.emailAddress,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter your email';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _passwordController,
                decoration: const InputDecoration(
                  labelText: 'Password',
                  border: OutlineInputBorder(),
                ),
                obscureText: true,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter your password';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _login,
                  child: _isLoading
                      ? const CircularProgressIndicator()
                      : const Text('Login'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

### 5. Test the Connection

Create a simple test to verify connectivity:

```dart
// Add this to your main.dart or create a test screen
Future<void> testConnection() async {
  final apiService = ApiService();

  try {
    // Test 1: Register a user
    print('Testing registration...');
    final registerResponse = await apiService.register(
      name: 'Test User',
      email: 'test@example.com',
      password: 'password123',
    );
    print('Registration successful: ${registerResponse['message']}');

    // Test 2: Get user info
    print('\nTesting get user...');
    final userResponse = await apiService.getUser();
    print('User: ${userResponse['data']}');

    // Test 3: Get customers
    print('\nTesting get customers...');
    final customersResponse = await apiService.getCustomers();
    print('Customers: ${customersResponse['data']}');

    print('\n✅ All tests passed!');
  } catch (e) {
    print('❌ Error: $e');
  }
}
```

---

## Testing Steps

### 1. Ensure Backend is Running

On your computer:
```bash
cd /home/smith/Desktop/myproject/manchoice-backend
./artisan.sh serve --host=0.0.0.0 --port=8000
```

### 2. Test from Browser

Open: `http://192.168.100.65:8000/up`

If you see a page, the backend is accessible!

### 3. Configure Flutter App

Update the `baseUrl` in `ApiService` based on where you're testing:

- **Physical Device (same WiFi):** `http://192.168.100.65:8000/api`
- **Android Emulator:** `http://10.0.2.2:8000/api`
- **iOS Simulator:** `http://localhost:8000/api`

### 4. Android Network Permissions

Add to `android/app/src/main/AndroidManifest.xml`:

```xml
<uses-permission android:name="android.permission.INTERNET" />
```

For development with HTTP (not HTTPS), also add:

```xml
<application
    android:usesCleartextTraffic="true"
    ... >
```

### 5. iOS Configuration

Add to `ios/Runner/Info.plist`:

```xml
<key>NSAppTransportSecurity</key>
<dict>
    <key>NSAllowsArbitraryLoads</key>
    <true/>
</dict>
```

---

## API Response Format

All API responses follow this structure:

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message here",
  "errors": { ... }
}
```

---

## Troubleshooting

### Connection Refused
- Ensure backend server is running
- Check firewall settings
- Verify you're using the correct IP address
- Ensure phone and computer are on same WiFi

### 401 Unauthorized
- Token might be expired or invalid
- Call login again to get a new token

### CORS Errors
- CORS is already configured on the backend
- If issues persist, check browser console for details

### SSL Certificate Errors
- Use HTTP (not HTTPS) for local development
- Configure `usesCleartextTraffic` as shown above

---

## Production Deployment

When deploying to production:

1. Update `baseUrl` to your production API URL (with HTTPS)
2. Remove `usesCleartextTraffic` from Android manifest
3. Remove NSAllowsArbitraryLoads from iOS Info.plist
4. Set backend `APP_ENV=production` and `APP_DEBUG=false`
5. Configure proper CORS origins in backend
6. Use SSL certificate for HTTPS

---

## Available Endpoints

See the main README.md for complete API documentation with all endpoints:

- Authentication (register, login, logout)
- Customers (CRUD + statistics)
- Loans (CRUD + approval)
- Payments (CRUD + reversal)
- M-PESA Integration (STK Push, status check)
- Products (CRUD + inventory management)

---

## Need Help?

- Check the main `README.md` for complete API documentation
- Review `START_SERVER.md` for server setup
- Test API endpoints using the `test-api.sh` script
- Check Laravel logs: `storage/logs/laravel.log`

