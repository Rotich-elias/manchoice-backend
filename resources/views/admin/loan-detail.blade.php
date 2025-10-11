@extends('admin.layout')

@section('title', 'Loan Application Details')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Loan Application #{{ $loan->loan_number }}</h1>
            <p class="text-gray-600 mt-1">Submitted {{ $loan->created_at->format('F d, Y \a\t h:i A') }}</p>
        </div>
        <a href="/admin/loans" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Back to Loans
        </a>
    </div>
</div>

<!-- Status and Actions -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold mb-2">Application Status</h2>
            <span class="px-3 py-1 text-sm rounded-full
                {{ $loan->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                {{ $loan->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $loan->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                {{ $loan->status === 'active' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $loan->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
            ">
                {{ ucfirst($loan->status) }}
            </span>
        </div>

        @if($loan->status === 'pending')
        <div class="flex space-x-3">
            <form action="/admin/loans/{{ $loan->id }}/approve" method="POST" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('Are you sure you want to approve this loan?')"
                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded font-semibold">
                    Approve Loan
                </button>
            </form>
            <form action="/admin/loans/{{ $loan->id }}/reject" method="POST" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('Are you sure you want to reject this loan?')"
                        class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded font-semibold">
                    Reject Loan
                </button>
            </form>
        </div>
        @elseif($loan->approved_by && $loan->approver)
        <div class="text-sm text-gray-600">
            <p>Approved by: <span class="font-semibold">{{ $loan->approver->name }}</span></p>
            <p>Approved on: {{ $loan->approved_at->format('F d, Y \a\t h:i A') }}</p>
        </div>
        @endif
    </div>
</div>

<!-- Customer Information -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4 border-b pb-2">Customer Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="font-semibold text-gray-700 mb-3">Personal Details</h3>
            <div class="space-y-2">
                <div class="flex">
                    <span class="text-gray-600 w-40">Full Name:</span>
                    <span class="font-medium">{{ $loan->customer->name }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-600 w-40">Phone:</span>
                    <span class="font-medium">{{ $loan->customer->phone }}</span>
                </div>
                @if($loan->customer->email)
                <div class="flex">
                    <span class="text-gray-600 w-40">Email:</span>
                    <span class="font-medium">{{ $loan->customer->email }}</span>
                </div>
                @endif
                @if($loan->customer->id_number)
                <div class="flex">
                    <span class="text-gray-600 w-40">National ID:</span>
                    <span class="font-medium">{{ $loan->customer->id_number }}</span>
                </div>
                @endif
                @if($loan->customer->address)
                <div class="flex">
                    <span class="text-gray-600 w-40">Working Station:</span>
                    <span class="font-medium">{{ $loan->customer->address }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Motorcycle Details -->
        @if($loan->customer->motorcycle_number_plate)
        <div>
            <h3 class="font-semibold text-gray-700 mb-3">Motorcycle Details</h3>
            <div class="space-y-2">
                @if($loan->customer->motorcycle_number_plate)
                <div class="flex">
                    <span class="text-gray-600 w-40">Number Plate:</span>
                    <span class="font-medium">{{ $loan->customer->motorcycle_number_plate }}</span>
                </div>
                @endif
                @if($loan->customer->motorcycle_chassis_number)
                <div class="flex">
                    <span class="text-gray-600 w-40">Chassis Number:</span>
                    <span class="font-medium">{{ $loan->customer->motorcycle_chassis_number }}</span>
                </div>
                @endif
                @if($loan->customer->motorcycle_model)
                <div class="flex">
                    <span class="text-gray-600 w-40">Model:</span>
                    <span class="font-medium">{{ $loan->customer->motorcycle_model }}</span>
                </div>
                @endif
                @if($loan->customer->motorcycle_type)
                <div class="flex">
                    <span class="text-gray-600 w-40">Type:</span>
                    <span class="font-medium">{{ $loan->customer->motorcycle_type }}</span>
                </div>
                @endif
                @if($loan->customer->motorcycle_engine_cc)
                <div class="flex">
                    <span class="text-gray-600 w-40">Engine CC:</span>
                    <span class="font-medium">{{ $loan->customer->motorcycle_engine_cc }}</span>
                </div>
                @endif
                @if($loan->customer->motorcycle_colour)
                <div class="flex">
                    <span class="text-gray-600 w-40">Colour:</span>
                    <span class="font-medium">{{ $loan->customer->motorcycle_colour }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Next of Kin and Guarantor -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4 border-b pb-2">Next of Kin & Guarantor Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if($loan->customer->next_of_kin_name)
        <div>
            <h3 class="font-semibold text-gray-700 mb-3">Next of Kin</h3>
            <div class="space-y-2">
                <div class="flex">
                    <span class="text-gray-600 w-40">Name:</span>
                    <span class="font-medium">{{ $loan->customer->next_of_kin_name }}</span>
                </div>
                @if($loan->customer->next_of_kin_phone)
                <div class="flex">
                    <span class="text-gray-600 w-40">Phone:</span>
                    <span class="font-medium">{{ $loan->customer->next_of_kin_phone }}</span>
                </div>
                @endif
                @if($loan->customer->next_of_kin_relationship)
                <div class="flex">
                    <span class="text-gray-600 w-40">Relationship:</span>
                    <span class="font-medium">{{ $loan->customer->next_of_kin_relationship }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($loan->customer->guarantor_name)
        <div>
            <h3 class="font-semibold text-gray-700 mb-3">Guarantor</h3>
            <div class="space-y-2">
                <div class="flex">
                    <span class="text-gray-600 w-40">Name:</span>
                    <span class="font-medium">{{ $loan->customer->guarantor_name }}</span>
                </div>
                @if($loan->customer->guarantor_phone)
                <div class="flex">
                    <span class="text-gray-600 w-40">Phone:</span>
                    <span class="font-medium">{{ $loan->customer->guarantor_phone }}</span>
                </div>
                @endif
                @if($loan->customer->guarantor_relationship)
                <div class="flex">
                    <span class="text-gray-600 w-40">Relationship:</span>
                    <span class="font-medium">{{ $loan->customer->guarantor_relationship }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Loan Details -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4 border-b pb-2">Loan Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <span class="text-gray-600">Principal Amount</span>
            <div class="text-2xl font-bold text-blue-600">KES {{ number_format($loan->principal_amount, 2) }}</div>
        </div>
        <div>
            <span class="text-gray-600">Interest Rate</span>
            <div class="text-2xl font-bold text-orange-600">{{ $loan->interest_rate }}%</div>
        </div>
        <div>
            <span class="text-gray-600">Total Amount</span>
            <div class="text-2xl font-bold text-green-600">KES {{ number_format($loan->total_amount, 2) }}</div>
        </div>
    </div>

    @if($loan->purpose || $loan->notes)
    <div class="mt-6 pt-6 border-t">
        @if($loan->purpose)
        <div class="mb-4">
            <span class="text-gray-600 font-semibold">Purpose:</span>
            <p class="mt-1">{{ $loan->purpose }}</p>
        </div>
        @endif
        @if($loan->notes)
        <div>
            <span class="text-gray-600 font-semibold">Products Selected:</span>
            <p class="mt-1 text-sm">{{ $loan->notes }}</p>
        </div>
        @endif
    </div>
    @endif
</div>

<!-- Application Documents -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4 border-b pb-2">Application Documents</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Bike Photo -->
        @if($loan->bike_photo_path)
        <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">Motorcycle Photo</h3>
            <img src="file://{{ $loan->bike_photo_path }}" alt="Motorcycle" class="w-full h-48 object-cover rounded mb-2">
            <a href="file://{{ $loan->bike_photo_path }}" target="_blank" class="text-blue-600 hover:underline text-sm">View Full Size</a>
        </div>
        @endif

        <!-- Logbook Photo -->
        @if($loan->logbook_photo_path)
        <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">Logbook Photo</h3>
            <img src="file://{{ $loan->logbook_photo_path }}" alt="Logbook" class="w-full h-48 object-cover rounded mb-2">
            <a href="file://{{ $loan->logbook_photo_path }}" target="_blank" class="text-blue-600 hover:underline text-sm">View Full Size</a>
        </div>
        @endif

        <!-- Passport Photo -->
        @if($loan->passport_photo_path)
        <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">Passport Photo</h3>
            <img src="file://{{ $loan->passport_photo_path }}" alt="Passport" class="w-full h-48 object-cover rounded mb-2">
            <a href="file://{{ $loan->passport_photo_path }}" target="_blank" class="text-blue-600 hover:underline text-sm">View Full Size</a>
        </div>
        @endif

        <!-- ID Photo -->
        @if($loan->id_photo_path)
        <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">National ID Photo</h3>
            <img src="file://{{ $loan->id_photo_path }}" alt="National ID" class="w-full h-48 object-cover rounded mb-2">
            <a href="file://{{ $loan->id_photo_path }}" target="_blank" class="text-blue-600 hover:underline text-sm">View Full Size</a>
        </div>
        @endif

        <!-- Next of Kin ID Photo -->
        @if($loan->next_of_kin_id_photo_path)
        <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">Next of Kin ID Photo</h3>
            <img src="file://{{ $loan->next_of_kin_id_photo_path }}" alt="Next of Kin ID" class="w-full h-48 object-cover rounded mb-2">
            <a href="file://{{ $loan->next_of_kin_id_photo_path }}" target="_blank" class="text-blue-600 hover:underline text-sm">View Full Size</a>
        </div>
        @endif

        <!-- Guarantor ID Photo -->
        @if($loan->guarantor_id_photo_path)
        <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">Guarantor ID Photo</h3>
            <img src="file://{{ $loan->guarantor_id_photo_path }}" alt="Guarantor ID" class="w-full h-48 object-cover rounded mb-2">
            <a href="file://{{ $loan->guarantor_id_photo_path }}" target="_blank" class="text-blue-600 hover:underline text-sm">View Full Size</a>
        </div>
        @endif
    </div>

    @if(!$loan->bike_photo_path && !$loan->logbook_photo_path && !$loan->passport_photo_path && !$loan->id_photo_path && !$loan->next_of_kin_id_photo_path && !$loan->guarantor_id_photo_path)
    <p class="text-gray-500 text-center py-8">No documents uploaded for this application</p>
    @endif
</div>

<!-- Action Buttons (Bottom) -->
@if($loan->status === 'pending')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-center space-x-4">
        <form action="/admin/loans/{{ $loan->id }}/approve" method="POST" class="inline">
            @csrf
            <button type="submit" onclick="return confirm('Are you sure you want to approve this loan?')"
                    class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-lg font-semibold text-lg">
                ✓ Approve Loan
            </button>
        </form>
        <form action="/admin/loans/{{ $loan->id }}/reject" method="POST" class="inline">
            @csrf
            <button type="submit" onclick="return confirm('Are you sure you want to reject this loan?')"
                    class="bg-red-500 hover:bg-red-600 text-white px-8 py-3 rounded-lg font-semibold text-lg">
                ✗ Reject Loan
            </button>
        </form>
    </div>
</div>
@endif
@endsection
