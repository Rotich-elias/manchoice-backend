@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Total Customers</div>
        <div class="text-3xl font-bold text-blue-600">{{ $stats['customers'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Total Loans</div>
        <div class="text-3xl font-bold text-green-600">{{ $stats['loans'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Pending Loans</div>
        <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_loans'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Active Loans</div>
        <div class="text-3xl font-bold text-purple-600">{{ $stats['active_loans'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Defaulted Loans</div>
        <div class="text-3xl font-bold text-red-600">{{ $stats['defaulted_loans'] }}</div>
    </div>
</div>

<!-- Financial Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Total Borrowed</div>
        <div class="text-2xl font-bold text-green-600">KES {{ number_format($stats['total_borrowed'], 2) }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Total Paid</div>
        <div class="text-2xl font-bold text-blue-600">KES {{ number_format($stats['total_paid'], 2) }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Outstanding</div>
        <div class="text-2xl font-bold text-red-600">KES {{ number_format($stats['total_borrowed'] - $stats['total_paid'], 2) }}</div>
    </div>
</div>

<!-- Product Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Total Products</div>
        <div class="text-2xl font-bold text-indigo-600">{{ $stats['products'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Low Stock Products</div>
        <div class="text-2xl font-bold text-orange-600">{{ $stats['low_stock_products'] }}</div>
    </div>
</div>

<!-- Pending Loans -->
@if($pendingLoans->count() > 0)
<div class="bg-white p-6 rounded-lg shadow mb-8">
    <h2 class="text-xl font-bold mb-4">Pending Loan Approvals</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($pendingLoans as $loan)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $loan->loan_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $loan->customer->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">KES {{ number_format($loan->total_amount, 2) }}</td>
                    <td class="px-6 py-4">{{ $loan->purpose }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="/admin/loans/{{ $loan->id }}/approve" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                Approve
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Defaulted Loans -->
@if($defaultedLoans->count() > 0)
<div class="bg-white p-6 rounded-lg shadow mb-8 border-l-4 border-red-500">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-red-700">⚠ Defaulted Loans (Missed Payments)</h2>
        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">
            {{ $defaultedLoans->count() }} Loan(s)
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-red-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Loan Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Total Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($defaultedLoans as $loan)
                <tr class="hover:bg-red-50">
                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-red-700">{{ $loan->loan_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="/admin/customers/{{ $loan->customer->id }}" class="text-blue-600 hover:underline">
                            {{ $loan->customer->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">KES {{ number_format($loan->total_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-red-600 font-bold">KES {{ number_format($loan->balance, 2) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($loan->due_date)
                            <span class="text-red-600">{{ $loan->due_date->format('M d, Y') }}</span>
                            <br>
                            <span class="text-xs text-gray-500">({{ $loan->due_date->diffForHumans() }})</span>
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="/admin/loans/{{ $loan->id }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm">
                            View Details
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Recent Loans -->
<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Recent Loans</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($recentLoans as $loan)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $loan->loan_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $loan->customer->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">KES {{ number_format($loan->total_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full {{ $loan->status === 'approved' ? 'bg-green-100 text-green-800' : ($loan->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($loan->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $loan->created_at->format('M d, Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
