@extends('admin.layout')

@section('title', 'Payments')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-3xl font-bold text-gray-800">Payments</h1>

    <div class="flex items-center space-x-4">
        @if($pendingCount > 0)
        <span class="px-4 py-2 bg-orange-100 text-orange-800 rounded-full font-semibold">
            {{ $pendingCount }} Pending Approval
        </span>
        @endif
        <button onclick="showCreatePaymentModal()"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Record Payment
        </button>
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-6 flex space-x-4 border-b">
    <a href="{{ url('/admin/payments') }}"
       class="pb-2 px-4 {{ $currentStatus === 'all' ? 'border-b-2 border-blue-500 text-blue-600 font-semibold' : 'text-gray-600' }}">
        All Payments
    </a>
    <a href="{{ url('/admin/payments?status=pending') }}"
       class="pb-2 px-4 {{ $currentStatus === 'pending' ? 'border-b-2 border-orange-500 text-orange-600 font-semibold' : 'text-gray-600' }}">
        Pending
    </a>
    <a href="{{ url('/admin/payments?status=completed') }}"
       class="pb-2 px-4 {{ $currentStatus === 'completed' ? 'border-b-2 border-green-500 text-green-600 font-semibold' : 'text-gray-600' }}">
        Completed
    </a>
    <a href="{{ url('/admin/payments?status=failed') }}"
       class="pb-2 px-4 {{ $currentStatus === 'failed' ? 'border-b-2 border-red-500 text-red-600 font-semibold' : 'text-gray-600' }}">
        Failed/Rejected
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

<!-- Payments Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt/Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($payments as $payment)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-mono">{{ $payment->transaction_id }}</td>
                <td class="px-6 py-4">
                    <div class="font-medium text-gray-900">{{ $payment->customer->name }}</div>
                    <div class="text-sm text-gray-500">{{ $payment->customer->phone }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm">
                        <div class="font-medium">{{ $payment->loan->loan_number }}</div>
                        <div class="text-gray-500">Balance: KES {{ number_format($payment->loan->balance, 2) }}</div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-lg font-bold text-green-600">
                        KES {{ number_format($payment->amount, 2) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                        {{ strtoupper($payment->payment_method) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($payment->mpesa_receipt_number)
                        <div class="text-sm font-mono">{{ $payment->mpesa_receipt_number }}</div>
                    @else
                        <span class="text-gray-400">N/A</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    {{ $payment->payment_date->format('M d, Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full
                        {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $payment->status === 'pending' ? 'bg-orange-100 text-orange-800' : '' }}
                        {{ $payment->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($payment->status === 'pending')
                    <div class="flex space-x-2">
                        <form action="/admin/payments/{{ $payment->id }}/approve" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Approve this payment of KES {{ number_format($payment->amount, 2) }}?')"
                                    class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white text-xs rounded">
                                ✓ Approve
                            </button>
                        </form>
                        <button onclick="showRejectModal({{ $payment->id }})"
                                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs rounded">
                            ✗ Reject
                        </button>
                    </div>
                    @else
                    <span class="text-gray-400 text-sm">No actions</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-6 py-4 text-center text-gray-500">No payments found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $payments->links() }}
</div>

<!-- Create Payment Modal -->
<div id="createPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl font-bold mb-4">Record Manual Payment</h3>
        <form method="POST" action="/admin/payments/create">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Loan Selection -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Loan *</label>
                    <select name="loan_id" id="loan_id" required
                            onchange="updateLoanInfo(this)"
                            class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">-- Select a loan --</option>
                        @foreach($activeLoans as $loan)
                        <option value="{{ $loan->id }}"
                                data-balance="{{ $loan->balance }}"
                                data-customer="{{ $loan->customer->name }}"
                                data-loan-number="{{ $loan->loan_number }}">
                            {{ $loan->loan_number }} - {{ $loan->customer->name }} (Balance: KES {{ number_format($loan->balance, 2) }})
                        </option>
                        @endforeach
                    </select>
                    <div id="loanInfo" class="mt-2 p-3 bg-blue-50 rounded text-sm hidden">
                        <p><strong>Customer:</strong> <span id="customerName"></span></p>
                        <p><strong>Loan Number:</strong> <span id="loanNumber"></span></p>
                        <p><strong>Outstanding Balance:</strong> <span id="loanBalance" class="text-red-600 font-bold"></span></p>
                    </div>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount (KES) *</label>
                    <input type="number"
                           name="amount"
                           id="payment_amount"
                           step="0.01"
                           min="0.01"
                           required
                           class="w-full border border-gray-300 rounded px-3 py-2"
                           placeholder="Enter amount">
                </div>

                <!-- Payment Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date *</label>
                    <input type="date"
                           name="payment_date"
                           required
                           value="{{ date('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2">
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                    <select name="payment_method" id="payment_method" required
                            onchange="toggleMpesaFields(this)"
                            class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">-- Select method --</option>
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-PESA</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Phone Number (for M-PESA) -->
                <div id="phone_field" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="text"
                           name="phone_number"
                           placeholder="254712345678"
                           class="w-full border border-gray-300 rounded px-3 py-2">
                </div>

                <!-- M-PESA Receipt Number -->
                <div id="mpesa_receipt_field" class="hidden col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">M-PESA Receipt Number</label>
                    <input type="text"
                           name="mpesa_receipt_number"
                           placeholder="Enter M-PESA receipt code"
                           class="w-full border border-gray-300 rounded px-3 py-2">
                </div>

                <!-- Notes -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes"
                              rows="3"
                              class="w-full border border-gray-300 rounded px-3 py-2"
                              placeholder="Add any additional notes..."></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button"
                        onclick="hideCreatePaymentModal()"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-xl font-bold mb-4">Reject Payment</h3>
        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                <textarea name="rejection_reason"
                          rows="4"
                          class="w-full border border-gray-300 rounded px-3 py-2"
                          required></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button"
                        onclick="hideRejectModal()"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">
                    Reject Payment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreatePaymentModal() {
    document.getElementById('createPaymentModal').classList.remove('hidden');
    document.getElementById('createPaymentModal').classList.add('flex');
}

function hideCreatePaymentModal() {
    document.getElementById('createPaymentModal').classList.add('hidden');
    document.getElementById('createPaymentModal').classList.remove('flex');
    // Reset form
    document.querySelector('#createPaymentModal form').reset();
    document.getElementById('loanInfo').classList.add('hidden');
    document.getElementById('phone_field').classList.add('hidden');
    document.getElementById('mpesa_receipt_field').classList.add('hidden');
}

function updateLoanInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption.value) {
        const balance = selectedOption.dataset.balance;
        const customer = selectedOption.dataset.customer;
        const loanNumber = selectedOption.dataset.loanNumber;

        document.getElementById('customerName').textContent = customer;
        document.getElementById('loanNumber').textContent = loanNumber;
        document.getElementById('loanBalance').textContent = 'KES ' + parseFloat(balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('loanInfo').classList.remove('hidden');

        // Set max amount
        document.getElementById('payment_amount').max = balance;
    } else {
        document.getElementById('loanInfo').classList.add('hidden');
    }
}

function toggleMpesaFields(select) {
    const phoneField = document.getElementById('phone_field');
    const mpesaReceiptField = document.getElementById('mpesa_receipt_field');

    if (select.value === 'mpesa') {
        phoneField.classList.remove('hidden');
        mpesaReceiptField.classList.remove('hidden');
    } else {
        phoneField.classList.add('hidden');
        mpesaReceiptField.classList.add('hidden');
    }
}

function showRejectModal(paymentId) {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
    document.getElementById('rejectForm').action = '/admin/payments/' + paymentId + '/reject';
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
}
</script>
@endsection
