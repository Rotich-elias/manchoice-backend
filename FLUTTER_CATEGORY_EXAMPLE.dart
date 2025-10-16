// Complete Flutter Example - Product Categories Integration
// Place this in your Flutter app's lib/services/ directory

import 'dart:convert';
import 'package:http/http.dart' as http;

// ============================================================================
// EXAMPLE 1: Fetching Categories from Backend
// ============================================================================

class ProductService {
  static const String baseUrl = 'http://192.168.100.65:8000/api';

  /// Fetch available product categories from backend
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

  /// Get products filtered by category
  Future<List<dynamic>> getProductsByCategory(String category) async {
    final response = await http.get(
      Uri.parse('$baseUrl/products/category/$category'),
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      return jsonData['data']['data'];
    } else {
      throw Exception('Failed to load products');
    }
  }
}

// ============================================================================
// EXAMPLE 2: Category Dropdown Widget
// ============================================================================

import 'package:flutter/material.dart';

class CategoryDropdown extends StatefulWidget {
  final Function(String?) onCategorySelected;

  const CategoryDropdown({Key? key, required this.onCategorySelected})
      : super(key: key);

  @override
  State<CategoryDropdown> createState() => _CategoryDropdownState();
}

class _CategoryDropdownState extends State<CategoryDropdown> {
  final ProductService _productService = ProductService();
  List<String> _categories = [];
  String? _selectedCategory;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    try {
      final categories = await _productService.getCategories();
      setState(() {
        _categories = categories;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      print('Error loading categories: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const CircularProgressIndicator();
    }

    return DropdownButton<String>(
      value: _selectedCategory,
      hint: const Text('Select Category'),
      isExpanded: true,
      items: [
        const DropdownMenuItem<String>(
          value: null,
          child: Text('All Categories'),
        ),
        ..._categories.map((String category) {
          return DropdownMenuItem<String>(
            value: category,
            child: Text(category),
          );
        }).toList(),
      ],
      onChanged: (String? newValue) {
        setState(() {
          _selectedCategory = newValue;
        });
        widget.onCategorySelected(newValue);
      },
    );
  }
}

// ============================================================================
// EXAMPLE 3: Category Filter Chips
// ============================================================================

class CategoryFilterChips extends StatefulWidget {
  final Function(String?) onCategorySelected;

  const CategoryFilterChips({Key? key, required this.onCategorySelected})
      : super(key: key);

  @override
  State<CategoryFilterChips> createState() => _CategoryFilterChipsState();
}

class _CategoryFilterChipsState extends State<CategoryFilterChips> {
  final ProductService _productService = ProductService();
  List<String> _categories = [];
  String? _selectedCategory;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    try {
      final categories = await _productService.getCategories();
      setState(() {
        _categories = categories;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      print('Error loading categories: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          // All categories chip
          Padding(
            padding: const EdgeInsets.only(right: 8.0),
            child: FilterChip(
              label: const Text('All'),
              selected: _selectedCategory == null,
              onSelected: (bool selected) {
                setState(() {
                  _selectedCategory = null;
                });
                widget.onCategorySelected(null);
              },
            ),
          ),
          // Category chips
          ..._categories.map((category) {
            return Padding(
              padding: const EdgeInsets.only(right: 8.0),
              child: FilterChip(
                label: Text(category),
                selected: _selectedCategory == category,
                onSelected: (bool selected) {
                  setState(() {
                    _selectedCategory = selected ? category : null;
                  });
                  widget.onCategorySelected(selected ? category : null);
                },
              ),
            );
          }).toList(),
        ],
      ),
    );
  }
}

// ============================================================================
// EXAMPLE 4: Complete Products Page with Category Filter
// ============================================================================

class ProductsPage extends StatefulWidget {
  const ProductsPage({Key? key}) : super(key: key);

  @override
  State<ProductsPage> createState() => _ProductsPageState();
}

class _ProductsPageState extends State<ProductsPage> {
  final ProductService _productService = ProductService();
  List<dynamic> _products = [];
  String? _selectedCategory;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadProducts();
  }

  Future<void> _loadProducts({String? category}) async {
    setState(() {
      _isLoading = true;
    });

    try {
      List<dynamic> products;
      if (category != null) {
        products = await _productService.getProductsByCategory(category);
      } else {
        // Load all products using your existing method
        products = []; // Replace with your getProducts() call
      }

      setState(() {
        _products = products;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  void _onCategorySelected(String? category) {
    setState(() {
      _selectedCategory = category;
    });
    _loadProducts(category: category);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Products'),
      ),
      body: Column(
        children: [
          // Category filter
          Container(
            padding: const EdgeInsets.all(16.0),
            child: CategoryFilterChips(
              onCategorySelected: _onCategorySelected,
            ),
          ),
          // Products list
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _products.isEmpty
                    ? const Center(child: Text('No products found'))
                    : ListView.builder(
                        itemCount: _products.length,
                        itemBuilder: (context, index) {
                          final product = _products[index];
                          return ListTile(
                            leading: product['image_url'] != null
                                ? Image.network(
                                    product['image_url'],
                                    width: 50,
                                    height: 50,
                                    fit: BoxFit.cover,
                                  )
                                : const Icon(Icons.image_not_supported),
                            title: Text(product['name']),
                            subtitle: Text(
                              'KSh ${product['price']} • ${product['category']}',
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }
}

// ============================================================================
// USAGE EXAMPLE
// ============================================================================

void main() {
  runApp(MaterialApp(
    home: ProductsPage(),
  ));
}

/*
TESTING THE API:

1. Test categories endpoint:
   curl http://192.168.100.65:8000/api/products/categories

2. Test products by category:
   curl "http://192.168.100.65:8000/api/products/category/Engine Parts"

3. Test in Flutter:
   final categories = await ProductService().getCategories();
   print(categories); // Prints list of category names
*/
