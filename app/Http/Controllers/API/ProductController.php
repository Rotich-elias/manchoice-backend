<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by availability
        if ($request->has('available') && $request->available === 'true') {
            $query->where('is_available', true);
        }

        // Filter by in stock
        if ($request->has('in_stock') && $request->in_stock === 'true') {
            $query->where('stock_quantity', '>', 0)
                  ->where('is_available', true);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|url|max:500',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'image_url' => 'nullable|url|max:500',
            'stock_quantity' => 'sometimes|integer|min:0',
            'is_available' => 'sometimes|boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Update stock quantity
     */
    public function updateStock(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'action' => 'required|in:add,reduce,set',
        ]);

        switch ($validated['action']) {
            case 'add':
                $product->addStock($validated['quantity']);
                break;
            case 'reduce':
                $product->reduceStock($validated['quantity']);
                break;
            case 'set':
                $product->stock_quantity = $validated['quantity'];
                $product->is_available = $validated['quantity'] > 0;
                $product->save();
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => $product
        ]);
    }

    /**
     * Toggle product availability
     */
    public function toggleAvailability(Product $product): JsonResponse
    {
        $product->is_available = !$product->is_available;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product availability updated',
            'data' => $product
        ]);
    }

    /**
     * Get products by category
     */
    public function byCategory(string $category): JsonResponse
    {
        $products = Product::where('category', $category)
                           ->where('is_available', true)
                           ->latest()
                           ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
