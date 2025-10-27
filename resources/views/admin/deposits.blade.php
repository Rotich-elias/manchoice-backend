@extends('admin.layout')

@section('title', 'Verify Deposit Payments')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Verify Deposit Payments</h1>
        <p class="text-sm text-gray-600 mt-1">Review and verify customer deposit payment submissions</p>
    </div>

    <div class="flex items-center space-x-2">
        @if($pendingCount > 0)
        <span class="px-4 py-2 bg-orange-100 text-orange-800 rounded-full font-semibold">
            {{ $pendingCount }} Pending Verification
        </span>
        @endif
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-6 flex space-x-4 border-b">
    <a href="{{ url('/admin/deposits') }}"
       class="pb-2 px-4 {{ $currentStatus === 'all' ? 'border-b-2 border-blue-500 text-blue-600 font-semibold' : 'text-gray-600' }}">
        All Submissions
    </a>
    <a href="{{ url('/admin/deposits?status=pending') }}"
       class="pb-2 px-4 {{ $currentStatus === 'pending' ? 'border-b-2 border-orange-500 text-orange-600 font-semibold' : 'text-gray-600' }}">
        Pending Verification ({{ $pendingCount }})
    </a>
    <a href="{{ url('/admin/deposits?status=completed') }}"
       class="pb-2 px-4 {{ $currentStatus === 'completed' ? 'border-b-2 border-green-500 text-green-600 font-semibold' : 'text-gray-600' }}">
        Verified
    </a>
    <a href="{{ url('/admin/deposits?status=failed') }}"
       class="pb-2 px-4 {{ $currentStatus === 'failed' ? 'border-b-2 border-red-500 text-red-600 font-semibold' : 'text-gray-600' }}">
        Rejected
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
    {{ session('error') }}
</div>
@endif

<!-- Deposits Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">M-PESA Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($deposits as $deposit)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    @if($deposit->loan)
                        <div class="font-medium text-gray-900">{{ $deposit->loan->loan_number }}</div>
                        <div class="text-xs text-gray-500">Total: KES {{ number_format($deposit->loan->deposit_amount, 2) }}</div>
                        <div class="text-xs text-gray-500">Paid: KES {{ number_format($deposit->loan->deposit_paid, 2) }}</div>
                    @else
                        <div class="font-medium text-gray-500">N/A</div>
                        <div class="text-xs text-gray-400">Loan not found</div>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($deposit->customer)
                        <div class="font-medium text-gray-900">{{ $deposit->customer->name }}</div>
                        <div class="text-sm text-gray-500">{{ $deposit->customer->email ?? 'N/A' }}</div>
                    @else
                        <div class="font-medium text-gray-500">N/A</div>
                        <div class="text-sm text-gray-400">Customer not found</div>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm">
                    {{ $deposit->phone_number }}
                </td>
                <td class="px-6 py-4">
                    @if($deposit->mpesa_receipt_number)
                        <span class="font-mono text-sm font-bold text-blue-600">{{ $deposit->mpesa_receipt_number }}</span>
                        <button onclick="copyToClipboard('{{ $deposit->mpesa_receipt_number }}')" class="ml-2 text-gray-400 hover:text-gray-600" title="Copy M-PESA code">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </button>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-lg font-bold text-green-600">
                        KES {{ number_format($deposit->amount, 2) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ $deposit->payment_method === 'mpesa' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $deposit->payment_method === 'cash' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $deposit->payment_method === 'bank_transfer' ? 'bg-purple-100 text-purple-800' : '' }}">
                        {{ strtoupper($deposit->payment_method) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $deposit->created_at->format('M d, Y') }}
                    <div class="text-xs text-gray-400">{{ $deposit->created_at->format('h:i A') }}</div>
                </td>
                <td class="px-6 py-4">
                    @if($deposit->status === 'pending')
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-800">
                            PENDING
                        </span>
                    @elseif($deposit->status === 'completed')
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                            ✓ VERIFIED
                        </span>
                        @if($deposit->paid_at)
                            <div class="text-xs text-gray-500 mt-1">{{ $deposit->paid_at->format('M d, Y') }}</div>
                        @endif
                    @elseif($deposit->status === 'failed')
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                            FAILED
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($deposit->status === 'pending')
                        <div class="flex space-x-2">
                            <button onclick="showVerifyModal({{ $deposit->id }}, '{{ $deposit->customer ? $deposit->customer->name : 'N/A' }}', '{{ $deposit->loan ? $deposit->loan->loan_number : 'N/A' }}', '{{ $deposit->mpesa_receipt_number }}', {{ $deposit->amount }})"
                                    class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded">
                                ✓ Verify
                            </button>
                            <button onclick="showRejectModal({{ $deposit->id }}, '{{ $deposit->customer ? $deposit->customer->name : 'N/A' }}', '{{ $deposit->loan ? $deposit->loan->loan_number : 'N/A' }}')"
                                    class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded">
                                ✗ Reject
                            </button>
                        </div>
                    @else
                        @if($deposit->recorder)
                            <div class="text-xs text-gray-500">
                                By: {{ $deposit->recorder->name }}
                            </div>
                        @endif
                        @if($deposit->notes)
                            <div class="text-xs text-gray-600 mt-1" title="{{ $deposit->notes }}">
                                <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                Notes
                            </div>
                        @endif
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                    <div class="text-6xl mb-4">💰</div>
                    <div class="text-lg font-medium">No deposit payments found</div>
                    <div class="text-sm">{{ $currentStatus !== 'all' ? 'Try viewing all deposits' : 'Deposits will appear here once customers submit payments' }}</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($deposits->hasPages())
<div class="mt-6">
    {{ $deposits->links() }}
</div>
@endif

<!-- Verify Modal -->
<div id="verifyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold mb-4 text-green-600">Verify Deposit Payment</h2>
        <form id="verifyForm" method="POST">
            @csrf
            <input type="hidden" name="status" value="completed">

            <div class="mb-4">
                <p class="text-gray-700 mb-2">Customer: <span id="verifyCustomerName" class="font-bold"></span></p>
                <p class="text-gray-700 mb-2">Loan #: <span id="verifyLoanNumber" class="font-bold text-blue-600"></span></p>
                <p class="text-gray-700 mb-2">M-PESA Code: <span id="verifyMpesaCode" class="font-mono font-bold text-blue-600"></span></p>
                <p class="text-gray-700 mb-4">Amount: <span id="verifyAmount" class="font-bold text-green-600"></span></p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Verification Notes (Optional)
                </label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Add any notes about the verification..."></textarea>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-4">
                <p class="text-sm text-yellow-800">
                    <strong>⚠️ Important:</strong> Please verify the M-PESA code before confirming. This will update the loan deposit balance.
                </p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeVerifyModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-bold">
                    ✓ Confirm Verification
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold mb-4 text-red-600">Reject Deposit Payment</h2>
        <form id="rejectForm" method="POST">
            @csrf
            <input type="hidden" name="status" value="failed">

            <div class="mb-4">
                <p class="text-gray-700 mb-2">Customer: <span id="rejectCustomerName" class="font-bold"></span></p>
                <p class="text-gray-700 mb-4">Loan #: <span id="rejectLoanNumber" class="font-bold text-blue-600"></span></p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Reason for Rejection <span class="text-red-500">*</span>
                </label>
                <textarea name="notes" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Explain why this deposit payment is being rejected..."></textarea>
            </div>

            <div class="bg-red-50 border border-red-200 rounded p-3 mb-4">
                <p class="text-sm text-red-800">
                    <strong>⚠️ Warning:</strong> This will mark the payment as failed. The customer will be able to submit a new payment.
                </p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-bold">
                    ✗ Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showVerifyModal(depositId, customerName, loanNumber, mpesaCode, amount) {
    document.getElementById('verifyCustomerName').textContent = customerName;
    document.getElementById('verifyLoanNumber').textContent = loanNumber;
    document.getElementById('verifyMpesaCode').textContent = mpesaCode || 'N/A';
    document.getElementById('verifyAmount').textContent = 'KES ' + parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('verifyForm').action = '/admin/deposits/' + depositId + '/verify';
    document.getElementById('verifyModal').classList.remove('hidden');
}

function closeVerifyModal() {
    document.getElementById('verifyModal').classList.add('hidden');
}

function showRejectModal(depositId, customerName, loanNumber) {
    document.getElementById('rejectCustomerName').textContent = customerName;
    document.getElementById('rejectLoanNumber').textContent = loanNumber;
    document.getElementById('rejectForm').action = '/admin/deposits/' + depositId + '/verify';
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('M-PESA code copied: ' + text);
    });
}

// Close modals when clicking outside
document.getElementById('verifyModal').addEventListener('click', function(e) {
    if (e.target === this) closeVerifyModal();
});

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});
</script>

@endsection
