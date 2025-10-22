@extends('reports.layout')

@section('title', 'Product Inventory Report')

@section('content')
<h2 class="report-title">PRODUCT INVENTORY REPORT</h2>

<div class="report-meta">
    <strong>Report Generated:</strong> {{ date('F d, Y \a\t h:i A') }}<br>
    <strong>Total Products:</strong> {{ $products->count() }}
</div>

<div class="summary-box">
    <h3>Inventory Summary</h3>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-label">Total Products</div>
            <div class="summary-value">{{ $products->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Stock Units</div>
            <div class="summary-value text-blue">{{ number_format($products->sum('stock_quantity')) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Stock Value</div>
            <div class="summary-value text-green">KES {{ number_format($products->sum(function($p) { return $p->price * $p->stock_quantity; }), 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Low Stock Items</div>
            <div class="summary-value text-red">{{ $products->where('stock_quantity', '<', 10)->count() }}</div>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Category</th>
            <th class="text-right">Price</th>
            <th>Discount</th>
            <th class="text-center">Stock</th>
            <th class="text-right">Stock Value</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td class="font-bold">{{ $product->name }}</td>
            <td style="font-size: 8pt;">{{ $product->category }}</td>
            <td class="text-right">{{ number_format($product->price, 2) }}</td>
            <td class="text-center">
                @if($product->discount_percentage > 0)
                    <span class="text-green">{{ $product->discount_percentage }}%</span>
                @else
                    -
                @endif
            </td>
            <td class="text-center {{ $product->stock_quantity < 10 ? 'text-red font-bold' : '' }}">
                {{ $product->stock_quantity }}
                @if($product->stock_quantity < 10)
                    <br><span style="font-size: 7pt;">(LOW)</span>
                @endif
            </td>
            <td class="text-right">{{ number_format($product->price * $product->stock_quantity, 2) }}</td>
            <td>
                @if($product->is_available)
                    <span class="status-badge status-active">Available</span>
                @else
                    <span class="status-badge status-rejected">Unavailable</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f3f4f6; font-weight: bold;">
            <td colspan="5" class="text-right" style="padding: 10px;">TOTAL STOCK VALUE:</td>
            <td class="text-center">{{ number_format($products->sum('stock_quantity')) }}</td>
            <td class="text-right text-green" style="font-size: 12pt;">KES {{ number_format($products->sum(function($p) { return $p->price * $p->stock_quantity; }), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

@if($products->isEmpty())
<div style="text-align: center; padding: 40px; color: #9ca3af;">
    <p>No products found.</p>
</div>
@endif

@if($products->where('stock_quantity', '<', 10)->count() > 0)
<div style="margin-top: 30px; padding: 15px; background-color: #fef3c7; border-left: 4px solid #f59e0b;">
    <strong style="color: #92400e;">⚠ LOW STOCK ALERT:</strong>
    <span style="color: #92400e;">{{ $products->where('stock_quantity', '<', 10)->count() }} product(s) have low stock (less than 10 units). Consider restocking.</span>
</div>
@endif

</body>
</html>
@endsection
