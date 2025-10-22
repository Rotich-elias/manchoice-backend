@extends('reports.layout')

@section('title', 'Customer Report')

@section('content')
<h2 class="report-title">CUSTOMER DIRECTORY REPORT</h2>

<div class="report-meta">
    <strong>Report Generated:</strong> {{ date('F d, Y \a\t h:i A') }}<br>
    <strong>Total Customers:</strong> {{ $customers->count() }}
</div>

<div class="summary-box">
    <h3>Financial Summary</h3>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-label">Total Credit Limit</div>
            <div class="summary-value">KES {{ number_format($customers->sum('credit_limit'), 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Borrowed</div>
            <div class="summary-value text-blue">KES {{ number_format($customers->sum('total_borrowed'), 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Paid</div>
            <div class="summary-value text-green">KES {{ number_format($customers->sum('total_paid'), 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Outstanding Balance</div>
            <div class="summary-value text-red">KES {{ number_format($customers->sum('total_borrowed') - $customers->sum('total_paid'), 2) }}</div>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Credit Limit</th>
            <th class="text-right">Borrowed</th>
            <th class="text-right">Paid</th>
            <th class="text-right">Balance</th>
            <th>Loans</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $customer)
        <tr>
            <td>{{ $customer->id }}</td>
            <td class="font-bold">{{ $customer->name }}</td>
            <td>{{ $customer->phone }}</td>
            <td class="text-right">{{ number_format($customer->credit_limit, 0) }}</td>
            <td class="text-right">{{ number_format($customer->total_borrowed, 2) }}</td>
            <td class="text-right text-green">{{ number_format($customer->total_paid, 2) }}</td>
            <td class="text-right text-red">{{ number_format($customer->outstandingBalance(), 2) }}</td>
            <td class="text-center">{{ $customer->loan_count }}</td>
            <td>
                <span class="status-badge status-{{ $customer->status }}">
                    {{ ucfirst($customer->status) }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($customers->isEmpty())
<div style="text-align: center; padding: 40px; color: #9ca3af;">
    <p>No customers found.</p>
</div>
@endif

</body>
</html>
@endsection
