@extends('admin.layout')

@section('title', 'Products')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-800">Products Management</h1>
    <button onclick="showAddProductModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
        + Add New Product
    </button>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($products as $product)
            <tr>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-12 h-12 rounded object-cover mr-3">
                        @else
                        <div class="w-12 h-12 bg-gray-200 rounded mr-3 flex items-center justify-center">
                            <span class="text-gray-400">📦</span>
                        </div>
                        @endif
                        <div>
                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                            @if($product->description)
                            <div class="text-sm text-gray-500">{{ Str::limit($product->description, 40) }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs bg-gray-100 rounded">{{ $product->category ?? 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($product->discount_percentage > 0 && $product->original_price)
                    <div class="text-sm text-gray-500 line-through">KSh {{ number_format($product->original_price) }}</div>
                    @endif
                    <div class="font-semibold text-gray-900">KSh {{ number_format($product->price) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($product->discount_percentage > 0)
                    <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded font-medium">-{{ $product->discount_percentage }}%</span>
                    @else
                    <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <form action="/admin/products/{{ $product->id }}/update-stock" method="POST" class="flex items-center space-x-2">
                        @csrf
                        <input type="number" name="stock_quantity" value="{{ $product->stock_quantity }}" class="w-20 px-2 py-1 border rounded" min="0">
                        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                            Update
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full {{ $product->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $product->is_available ? 'Available' : 'Unavailable' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap space-x-2">
                    <button onclick='editProduct(@json($product))' class="text-blue-600 hover:text-blue-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteProduct({{ $product->id }})" class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <div class="text-4xl mb-2">📦</div>
                    <div>No products found</div>
                    <button onclick="showAddProductModal()" class="mt-4 text-blue-600 hover:text-blue-800">Add your first product</button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $products->links() }}
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-bold">Add New Product</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="productForm" method="POST" action="/admin/products/store">
            @csrf
            <input type="hidden" id="productId" name="product_id">
            <input type="hidden" id="formMethod" name="_method" value="POST">

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Product Name *</label>
                    <input type="text" name="name" id="productName" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="productDescription" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Category *</label>
                    <select name="category" id="productCategory" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">Select Category</option>
                        <option value="Fashion">Fashion</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Small Appliances">Small Appliances</option>
                        <option value="Phone Accessories">Phone Accessories</option>
                        <option value="Home">Home</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" id="productStock" required min="0" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Price (KSh) *</label>
                    <input type="number" name="price" id="productPrice" required min="0" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Original Price (KSh)</label>
                    <input type="number" name="original_price" id="productOriginalPrice" min="0" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Leave empty if no discount</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Discount Percentage (%)</label>
                    <input type="number" name="discount_percentage" id="productDiscount" min="0" max="100" value="0" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="is_available" id="productStatus" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="1">Available</option>
                        <option value="0">Unavailable</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Image URL</label>
                    <input type="url" name="image_url" id="productImageUrl" placeholder="https://example.com/image.jpg" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddProductModal() {
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('productForm').action = '/admin/products/store';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('productModal').classList.remove('hidden');
}

function editProduct(product) {
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('productForm').action = '/admin/products/' + product.id + '/update';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productDescription').value = product.description || '';
    document.getElementById('productCategory').value = product.category || '';
    document.getElementById('productStock').value = product.stock_quantity;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productOriginalPrice').value = product.original_price || '';
    document.getElementById('productDiscount').value = product.discount_percentage || 0;
    document.getElementById('productStatus').value = product.is_available ? '1' : '0';
    document.getElementById('productImageUrl').value = product.image_url || '';
    document.getElementById('productModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('productModal').classList.add('hidden');
}

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch('/admin/products/' + id + '/delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to delete product');
            }
        });
    }
}

// Close modal when clicking outside
document.getElementById('productModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
