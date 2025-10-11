@extends('admin.layout')

@section('title', 'Loans')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Loans</h1>
</div>

<!-- Filter Tabs -->
<div class="mb-4 flex space-x-2">
    <a href="/admin/loans" class="px-4 py-2 bg-blue-600 text-white rounded">All Loans</a>
    <a href="/admin/loans?status=pending" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Pending</a>
    <a href="/admin/loans?status=approved" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Approved</a>
    <a href="/admin/loans?status=active" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Active</a>
    <a href="/admin/loans?status=completed" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Completed</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($loans as $loan)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-medium text-gray-900">{{ $loan->loan_number }}</div>
                    <div class="text-sm text-gray-500">{{ $loan->created_at->format('M d, Y') }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">{{ $loan->customer->name }}</div>
                    <div class="text-sm text-gray-500">{{ $loan->customer->phone }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-semibold">KES {{ number_format($loan->total_amount, 2) }}</div>
                    <div class="text-sm text-gray-500">
                        Principal: KES {{ number_format($loan->principal_amount, 2) }}
                    </div>
                    <div class="text-sm text-gray-500">
                        Interest: {{ $loan->interest_rate }}%
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-semibold {{ $loan->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                        KES {{ number_format($loan->balance, 2) }}
                    </div>
                    <div class="text-sm text-gray-500">
                        Paid: KES {{ number_format($loan->amount_paid, 2) }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full
                        {{ $loan->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $loan->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $loan->status === 'active' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $loan->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $loan->status === 'defaulted' ? 'bg-red-100 text-red-800' : '' }}
                    ">
                        {{ ucfirst($loan->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($loan->due_date)
                        <div class="{{ $loan->due_date->isPast() && $loan->balance > 0 ? 'text-red-600 font-semibold' : '' }}">
                            {{ $loan->due_date->format('M d, Y') }}
                        </div>
                        @if($loan->due_date->isPast() && $loan->balance > 0)
                            <div class="text-xs text-red-600">
                                Overdue by {{ $loan->due_date->diffInDays(now()) }} days
                            </div>
                        @endif
                    @else
                        <span class="text-gray-400">N/A</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($loan->status === 'pending')
                        <form action="/admin/loans/{{ $loan->id }}/approve" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                                Approve
                            </button>
                        </form>
                    @elseif($loan->approver)
                        <div class="text-sm text-gray-500">
                            Approved by: {{ $loan->approver->name }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $loan->approved_at->format('M d, Y') }}
                        </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No loans found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $loans->links() }}
</div>

<!-- Summary -->
<div class="mt-8 bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Loan Summary</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <div class="text-gray-500 text-sm">Total Loans</div>
            <div class="text-2xl font-bold text-blue-600">{{ $loans->total() }}</div>
        </div>
        <div>
            <div class="text-gray-500 text-sm">Total Amount</div>
            <div class="text-2xl font-bold text-green-600">
                KES {{ number_format($loans->sum('total_amount'), 2) }}
            </div>
        </div>
        <div>
            <div class="text-gray-500 text-sm">Total Paid</div>
            <div class="text-2xl font-bold text-blue-600">
                KES {{ number_format($loans->sum('amount_paid'), 2) }}
            </div>
        </div>
        <div>
            <div class="text-gray-500 text-sm">Outstanding</div>
            <div class="text-2xl font-bold text-red-600">
                KES {{ number_format($loans->sum('balance'), 2) }}
            </div>
        </div>
    </div>
</div>
@endsection
