@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-800">Customers</h1>
    <div class="flex space-x-2">
        @if(auth()->user()->role === 'super_admin')
        <button onclick="openCreateCustomerModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Customer
        </button>
        @endif
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

<!-- Filters & Search -->
<div class="bg-white p-6 rounded-lg shadow mb-6">
    <form method="GET" action="/admin/customers" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2 border rounded-lg">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="blacklisted" {{ request('status') == 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Credit Status</label>
            <select name="credit_status" class="w-full px-4 py-2 border rounded-lg">
                <option value="">All</option>
                <option value="available" {{ request('credit_status') == 'available' ? 'selected' : '' }}>Has Available Credit</option>
                <option value="maxed" {{ request('credit_status') == 'maxed' ? 'selected' : '' }}>Maxed Out</option>
                <option value="overlimit" {{ request('credit_status') == 'overlimit' ? 'selected' : '' }}>Over Limit</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <div class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Phone, ID#" class="flex-1 px-4 py-2 border rounded-lg">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">Filter</button>
            </div>
        </div>
    </form>
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

<!-- Create Customer Modal -->
<div id="createCustomerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-gray-900">Add New Customer</h3>
            <button onclick="closeCreateCustomerModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="/admin/customers/store" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="John Doe">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                    <input type="text" name="phone" required pattern="^0[0-9]{9}$"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="0712345678">
                    <p class="text-xs text-gray-500 mt-1">Format: 0712345678</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="john@example.com">
                </div>

                <!-- ID Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Number</label>
                    <input type="text" name="id_number"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="12345678">
                </div>

                <!-- Business Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                    <input type="text" name="business_name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Business name">
                </div>

                <!-- Credit Limit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Credit Limit (KSh)</label>
                    <input type="number" name="credit_limit" step="0.01" min="0" value="1000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="1000">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="blacklisted">Blacklisted</option>
                    </select>
                </div>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input type="text" name="address"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Nairobi, Kenya">
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Additional notes about this customer"></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeCreateCustomerModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Create Customer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateCustomerModal() {
    document.getElementById('createCustomerModal').classList.remove('hidden');
}

function closeCreateCustomerModal() {
    document.getElementById('createCustomerModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('createCustomerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateCustomerModal();
    }
});
</script>
@endsection
