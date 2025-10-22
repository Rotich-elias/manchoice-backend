@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-800">Customers</h1>
    <div class="flex space-x-2">
        <a href="/admin/reports/customers?format=pdf" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            Export PDF
        </a>
        <a href="/admin/reports/customers?format=excel" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Excel
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credit Info</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loans</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($customers as $customer)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="font-medium text-gray-900">{{ $customer->name }}</div>
                    @if($customer->id_number)
                    <div class="text-sm text-gray-500">ID: {{ $customer->id_number }}</div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div>{{ $customer->phone }}</div>
                    @if($customer->email)
                    <div class="text-xs text-gray-500">{{ $customer->email }}</div>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @php
                        $outstanding = $customer->total_borrowed - $customer->total_paid;
                        $available = $customer->credit_limit - $outstanding;
                        $percentage = $customer->credit_limit > 0 ? ($outstanding / $customer->credit_limit) * 100 : 0;
                        $barColor = $percentage >= 80 ? 'bg-red-600' : ($percentage >= 50 ? 'bg-yellow-500' : 'bg-green-600');
                    @endphp
                    <div class="min-w-[200px]">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">Limit:</span>
                            <span class="font-semibold">KSh {{ number_format($customer->credit_limit) }}</span>
                        </div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">Outstanding:</span>
                            <span class="font-semibold text-red-600">KSh {{ number_format($outstanding) }}</span>
                        </div>
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-gray-600">Available:</span>
                            <span class="font-semibold text-green-600">KSh {{ number_format($available) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="{{ $barColor }} h-2 rounded-full transition-all duration-300"
                                 style="width: {{ min($percentage, 100) }}%"
                                 title="{{ number_format($percentage, 1) }}% used"></div>
                        </div>
                        <div class="text-xs text-gray-500 text-center mt-1">{{ number_format($percentage, 1) }}% used</div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                        {{ $customer->loan_count }} loans
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : ($customer->status === 'blacklisted' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst($customer->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <a href="/admin/customers/{{ $customer->id }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                        View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No customers found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $customers->links() }}
</div>

<!-- Outstanding Balance Summary -->
<div class="mt-8 bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Summary</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <div class="text-gray-500 text-sm">Total Customers</div>
            <div class="text-2xl font-bold text-blue-600">{{ $customers->total() }}</div>
        </div>
        <div>
            <div class="text-gray-500 text-sm">Total Credit Limit</div>
            <div class="text-2xl font-bold text-green-600">
                KES {{ number_format($customers->sum('credit_limit'), 2) }}
            </div>
        </div>
        <div>
            <div class="text-gray-500 text-sm">Total Outstanding</div>
            <div class="text-2xl font-bold text-orange-600">
                KES {{ number_format($customers->sum('total_borrowed') - $customers->sum('total_paid'), 2) }}
            </div>
        </div>
    </div>
</div>
@endsection
