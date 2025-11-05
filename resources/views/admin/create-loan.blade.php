@extends('admin.layout')

@section('title', 'Create Loan')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800">Create New Loan</h1>
        <a href="/admin/loans" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Loans
        </a>
    </div>
</div>

<!-- Success/Error Messages -->
@if ($errors->any())
<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
    <p class="font-bold">Error!</p>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if (session('success'))
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
    <p>{{ session('success') }}</p>
</div>
@endif

<form action="/admin/loans/store" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <!-- Section 1: Customer Selection -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Customer Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Customer *</label>
                <select id="customer_id" name="customer_id" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Customer --</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}"
                            data-name="{{ $customer->name }}"
                            data-phone="{{ $customer->phone }}"
                            data-credit-limit="{{ $customer->credit_limit }}"
                            data-total-borrowed="{{ $customer->total_borrowed }}"
                            data-total-paid="{{ $customer->total_paid }}"
                            data-status="{{ $customer->status }}">
                        {{ $customer->name }} - {{ $customer->phone }} (ID: {{ $customer->id_number ?? 'N/A' }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div id="customer-info" class="hidden">
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <h3 class="text-sm font-semibold mb-2">Customer Details</h3>
                    <div class="text-xs space-y-1">
                        <p><span class="font-medium">Credit Limit:</span> <span id="info-credit-limit">-</span></p>
                        <p><span class="font-medium">Outstanding:</span> <span id="info-outstanding" class="text-red-600">-</span></p>
                        <p><span class="font-medium">Available Credit:</span> <span id="info-available" class="text-green-600">-</span></p>
                        <p><span class="font-medium">Status:</span> <span id="info-status">-</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Loan Details -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Loan Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Principal Amount (KSh) *</label>
                <input type="number" id="principal_amount" name="principal_amount" step="0.01" min="0" required
                       value="{{ old('principal_amount') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="50000.00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (%)</label>
                <input type="number" id="interest_rate" name="interest_rate" step="0.01" min="0" max="100"
                       value="{{ old('interest_rate', '0') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="10.00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Days)</label>
                <input type="number" id="duration_days" name="duration_days" min="1"
                       value="{{ old('duration_days', '30') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="30">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Disbursement Date</label>
                <input type="date" id="disbursement_date" name="disbursement_date"
                       value="{{ old('disbursement_date', date('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Purpose</label>
                <input type="text" name="purpose"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g., Business inventory, Emergency, etc.">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="3"
                          class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Additional notes or comments"></textarea>
            </div>
        </div>

        <!-- Auto-calculated fields display -->
        <div class="mt-4 bg-blue-50 p-4 rounded border border-blue-200">
            <h3 class="text-sm font-semibold mb-2 text-blue-900">Calculated Loan Summary</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div>
                    <span class="text-gray-600">Total Amount:</span>
                    <p class="font-bold text-blue-900" id="calc-total">KSh 0.00</p>
                </div>
                <div>
                    <span class="text-gray-600">Deposit (10%):</span>
                    <p class="font-bold text-blue-900" id="calc-deposit">KSh 0.00</p>
                </div>
                <div>
                    <span class="text-gray-600">Daily Payment:</span>
                    <p class="font-bold text-blue-900" id="calc-daily">KSh 0.00</p>
                </div>
                <div>
                    <span class="text-gray-600">Balance:</span>
                    <p class="font-bold text-green-600" id="calc-balance">KSh 0.00</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Payment Tracking (NEW) -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">
            <svg class="w-5 h-5 inline-block mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Payment Tracking
        </h2>
        <p class="text-sm text-gray-600 mb-4">If the customer has already made payments, record them here.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Amount Already Paid (KSh)</label>
                <input type="number" id="amount_already_paid" name="amount_already_paid" step="0.01" min="0"
                       value="{{ old('amount_already_paid', '0') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="0.00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <select name="payment_method" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Cash">Cash</option>
                    <option value="M-Pesa">M-Pesa</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cheque">Cheque</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Reference/Transaction ID</label>
                <input type="text" name="payment_reference"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g., M-Pesa code, Receipt number">
            </div>
        </div>
    </div>

    <!-- Section 4: Next of Kin & Guarantor Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Next of Kin & Guarantor Information</h2>

        <!-- Next of Kin Information -->
        <h3 class="font-semibold text-gray-700 mb-3 mt-4">Next of Kin Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Next of Kin Name</label>
                <input type="text" name="next_of_kin_name" value="{{ old('next_of_kin_name') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Full name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Next of Kin Phone</label>
                <input type="text" name="next_of_kin_phone" value="{{ old('next_of_kin_phone') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="254712345678">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Next of Kin ID Number</label>
                <input type="text" name="next_of_kin_id_number" value="{{ old('next_of_kin_id_number') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="12345678">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                <input type="text" name="next_of_kin_relationship" value="{{ old('next_of_kin_relationship') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g., Spouse, Parent, Sibling">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Next of Kin Address</label>
                <input type="text" name="next_of_kin_address" value="{{ old('next_of_kin_address') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Full address">
            </div>
        </div>

        <!-- Guarantor Information -->
        <h3 class="font-semibold text-gray-700 mb-3 mt-6">Guarantor Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Guarantor Name</label>
                <input type="text" name="guarantor_name" value="{{ old('guarantor_name') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Full name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Guarantor Phone</label>
                <input type="text" name="guarantor_phone" value="{{ old('guarantor_phone') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="254712345678">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Guarantor ID Number</label>
                <input type="text" name="guarantor_id_number" value="{{ old('guarantor_id_number') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="12345678">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Relationship to Applicant</label>
                <input type="text" name="guarantor_relationship" value="{{ old('guarantor_relationship') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g., Friend, Colleague, Relative">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Guarantor Address</label>
                <input type="text" name="guarantor_address" value="{{ old('guarantor_address') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Full address">
            </div>
        </div>

        <!-- Guarantor Bike Details -->
        <h3 class="font-semibold text-gray-700 mb-3 mt-6">Guarantor's Bike Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                <input type="text" name="guarantor_bike_registration" value="{{ old('guarantor_bike_registration') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g., KXX 123Y">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Make/Brand</label>
                <input type="text" name="guarantor_bike_make" value="{{ old('guarantor_bike_make') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g., Honda, Yamaha, Bajaj">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                <input type="text" name="guarantor_bike_model" value="{{ old('guarantor_bike_model') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g., CB 125, YBR 125">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Year of Manufacture</label>
                <input type="number" name="guarantor_bike_year" value="{{ old('guarantor_bike_year') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="2020" min="1900" max="2099">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                <input type="text" name="guarantor_bike_color" value="{{ old('guarantor_bike_color') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g., Red, Black, Blue">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Engine Number</label>
                <input type="text" name="guarantor_bike_engine_number" value="{{ old('guarantor_bike_engine_number') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Engine number">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Chassis Number</label>
                <input type="text" name="guarantor_bike_chassis_number" value="{{ old('guarantor_bike_chassis_number') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Chassis/Frame number">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Logbook Number</label>
                <input type="text" name="guarantor_bike_logbook_number" value="{{ old('guarantor_bike_logbook_number') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Logbook number">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                <textarea name="guarantor_bike_notes" rows="2" value="{{ old('guarantor_bike_notes') }}"
                          class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Any additional information about the guarantor's bike"></textarea>
            </div>
        </div>
    </div>

    <!-- Section 5: Products/Items -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Products/Items (Optional)</h2>
        <div id="items-container">
            <!-- Items will be added dynamically -->
        </div>
        <button type="button" onclick="addProductItem()" class="mt-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
            + Add Product
        </button>
    </div>

    <!-- Section 6: Document Uploads -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Document Uploads</h2>

        <!-- Applicant Documents -->
        <h3 class="font-semibold text-gray-700 mb-3">Applicant Documents</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ID Front</label>
                <input type="file" name="id_photo_front" accept="image/*" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ID Back</label>
                <input type="file" name="id_photo_back" accept="image/*" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Passport Photo</label>
                <input type="file" name="passport_photo" accept="image/*" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bike Photo</label>
                <input type="file" name="bike_photo" accept="image/*" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Logbook Photo</label>
                <input type="file" name="logbook_photo" accept="image/*" class="w-full text-sm">
            </div>
        </div>

        <!-- Next of Kin Documents -->
        <h3 class="font-semibold text-gray-700 mb-3">Next of Kin Documents</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Next of Kin ID Front</label>
                <input type="file" name="next_of_kin_id_front" accept="image/*" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Next of Kin ID Back</label>
                <input type="file" name="next_of_kin_id_back" accept="image/*" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Next of Kin Passport</label>
                <input type="file" name="next_of_kin_passport_photo" accept="image/*" class="w-full text-sm">
            </div>
        </div>

        <!-- Guarantor Documents -->
        <h3 class="font-semibold text-gray-700 mb-3">Guarantor Documents</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Guarantor ID Front</label>
                <input type="file" name="guarantor_id_front" accept="image/*" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Guarantor ID Back</label>
                <input type="file" name="guarantor_id_back" accept="image/*" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Guarantor Passport</label>
                <input type="file" name="guarantor_passport_photo" accept="image/*" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Guarantor Bike Photo</label>
                <input type="file" name="guarantor_bike_photo" accept="image/*" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Guarantor Logbook</label>
                <input type="file" name="guarantor_logbook_photo" accept="image/*" class="w-full text-sm">
            </div>
        </div>
    </div>

    <!-- Section 7: Admin Options -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Admin Options</h2>
        <div class="space-y-3">
            <label class="flex items-center">
                <input type="checkbox" name="skip_credit_limit_check" value="1" class="mr-2" {{ old('skip_credit_limit_check') ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Skip credit limit check</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" name="deposit_required" value="1" class="mr-2" {{ old('deposit_required') ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Skip deposit requirement</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" name="force_create" value="1" class="mr-2" {{ old('force_create') ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Force create (override customer status restrictions)</span>
            </label>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="flex justify-end space-x-3">
        <a href="/admin/loans" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded">
            Cancel
        </a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded">
            Create Loan
        </button>
    </div>
</form>

@endsection

@section('scripts')
<script>
// Products list for dropdown
const products = @json($products);
let itemCount = 0;

// Customer info display
document.getElementById('customer_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (this.value) {
        const creditLimit = parseFloat(selectedOption.dataset.creditLimit) || 0;
        const totalBorrowed = parseFloat(selectedOption.dataset.totalBorrowed) || 0;
        const totalPaid = parseFloat(selectedOption.dataset.totalPaid) || 0;
        const status = selectedOption.dataset.status;

        const outstanding = totalBorrowed - totalPaid;
        const available = creditLimit - outstanding;

        document.getElementById('info-credit-limit').textContent = 'KSh ' + creditLimit.toLocaleString();
        document.getElementById('info-outstanding').textContent = 'KSh ' + outstanding.toLocaleString();
        document.getElementById('info-available').textContent = 'KSh ' + available.toLocaleString();
        document.getElementById('info-status').textContent = status.charAt(0).toUpperCase() + status.slice(1);
        document.getElementById('customer-info').classList.remove('hidden');
    } else {
        document.getElementById('customer-info').classList.add('hidden');
    }
});

// Auto-calculate loan summary
function calculateLoanSummary() {
    const principal = parseFloat(document.getElementById('principal_amount').value) || 0;
    const interestRate = parseFloat(document.getElementById('interest_rate').value) || 0;
    const duration = parseInt(document.getElementById('duration_days').value) || 30;
    const amountPaid = parseFloat(document.getElementById('amount_already_paid').value) || 0;

    const totalAmount = principal * (1 + (interestRate / 100));
    const deposit = totalAmount * 0.10;
    const balance = totalAmount - amountPaid;
    const dailyPayment = Math.max(200, totalAmount / duration);

    document.getElementById('calc-total').textContent = 'KSh ' + totalAmount.toFixed(2).toLocaleString();
    document.getElementById('calc-deposit').textContent = 'KSh ' + deposit.toFixed(2).toLocaleString();
    document.getElementById('calc-daily').textContent = 'KSh ' + dailyPayment.toFixed(2).toLocaleString();
    document.getElementById('calc-balance').textContent = 'KSh ' + balance.toFixed(2).toLocaleString();
}

document.getElementById('principal_amount').addEventListener('input', calculateLoanSummary);
document.getElementById('interest_rate').addEventListener('input', calculateLoanSummary);
document.getElementById('duration_days').addEventListener('input', calculateLoanSummary);
document.getElementById('amount_already_paid').addEventListener('input', calculateLoanSummary);

// Add product item
function addProductItem() {
    itemCount++;
    const container = document.getElementById('items-container');
    const itemDiv = document.createElement('div');
    itemDiv.className = 'flex gap-3 mb-3 items-start';
    itemDiv.id = 'item-' + itemCount;
    itemDiv.innerHTML = `
        <div class="flex-1">
            <select name="items[${itemCount}][product_id]" required class="w-full border border-gray-300 rounded px-3 py-2">
                <option value="">-- Select Product --</option>
                ${products.map(p => `<option value="${p.id}">${p.name} - KSh ${p.price}</option>`).join('')}
            </select>
        </div>
        <div class="w-24">
            <input type="number" name="items[${itemCount}][quantity]" min="1" value="1" required
                   class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Qty">
        </div>
        <button type="button" onclick="removeItem(${itemCount})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded">
            Remove
        </button>
    `;
    container.appendChild(itemDiv);
}

function removeItem(id) {
    document.getElementById('item-' + id).remove();
}
</script>
@endsection
