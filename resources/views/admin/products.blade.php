@extends('admin.layout')

@section('title', 'Products')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-800">Products</h1>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($products as $product)
            <tr>
                <td class="px-6 py-4">
                    <div class="font-medium text-gray-900">{{ $product->name }}</div>
                    @if($product->description)
                    <div class="text-sm text-gray-500">{{ Str::limit($product->description, 50) }}</div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $product->category ?? 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-semibold">KES {{ number_format($product->price, 2) }}</td>
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
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $product->created_at->format('M d, Y') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No products found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $products->links() }}
</div>
@endsection
