@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Customers</h1>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credit Limit</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Borrowed</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Paid</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loans</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
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
                <td class="px-6 py-4 whitespace-nowrap">{{ $customer->phone }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $customer->email ?? 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-semibold">
                    KES {{ number_format($customer->credit_limit, 2) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    KES {{ number_format($customer->total_borrowed, 2) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-green-600">
                    KES {{ number_format($customer->total_paid, 2) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                        {{ $customer->loan_count }} loans
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($customer->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No customers found</td>
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
