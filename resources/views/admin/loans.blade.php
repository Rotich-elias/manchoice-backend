@extends('admin.layout')

@section('title', 'Loans')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-800">Loans</h1>
    <div class="flex space-x-2">
        <a href="/admin/reports/loans?format=pdf{{ isset($currentStatus) && $currentStatus !== 'all' ? '&status=' . $currentStatus : '' }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            Export PDF
        </a>
        <a href="/admin/reports/loans?format=excel{{ isset($currentStatus) && $currentStatus !== 'all' ? '&status=' . $currentStatus : '' }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Excel
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-4 flex flex-wrap gap-2">
    <a href="/admin/loans" class="px-4 py-2 rounded {{ (!isset($currentStatus) || $currentStatus === 'all') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        All Loans
    </a>
    <a href="/admin/loans?status=pending" class="px-4 py-2 rounded {{ (isset($currentStatus) && $currentStatus === 'pending') ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Pending
    </a>
    <a href="/admin/loans?status=approved" class="px-4 py-2 rounded {{ (isset($currentStatus) && $currentStatus === 'approved') ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Approved
    </a>
    <a href="/admin/loans?status=active" class="px-4 py-2 rounded {{ (isset($currentStatus) && $currentStatus === 'active') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Active
    </a>
    <a href="/admin/loans?status=completed" class="px-4 py-2 rounded {{ (isset($currentStatus) && $currentStatus === 'completed') ? 'bg-teal-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Completed
    </a>
    <a href="/admin/loans?status=rejected" class="px-4 py-2 rounded {{ (isset($currentStatus) && $currentStatus === 'rejected') ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Rejected
    </a>
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
                        {{ $loan->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
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
                        <div class="flex space-x-2">
                            <a href="/admin/loans/{{ $loan->id }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                View
                            </a>
                            <form action="/admin/loans/{{ $loan->id }}/approve" method="POST" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Approve this loan?')" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                    Approve
                                </button>
                            </form>
                            <button onclick="showRejectModal({{ $loan->id }})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                Reject
                            </button>
                        </div>
                    @else
                        <div class="flex space-x-2">
                            <a href="/admin/loans/{{ $loan->id }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                View Details
                            </a>
                        </div>
                        @if($loan->approver)
                            <div class="text-sm text-gray-500 mt-2">
                                {{ $loan->status === 'rejected' ? 'Rejected' : 'Approved' }} by: {{ $loan->approver->name }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $loan->approved_at->format('M d, Y') }}
                            </div>
                        @endif
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

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Reject Loan Application</h3>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="rejection_reason">
                        Reason for Rejection *
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        rows="4" placeholder="Enter the reason for rejecting this loan..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                        Reject Loan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal(loanId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/admin/loans/${loanId}/reject`;
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
    document.getElementById('rejection_reason').value = '';
}

// Close modal when clicking outside
document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endsection
