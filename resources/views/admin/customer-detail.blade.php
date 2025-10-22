@extends('admin.layout')

@section('title', 'Customer Details')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Customer Details</h1>
            <p class="text-gray-600 mt-1">Complete profile and loan history</p>
        </div>
        <a href="/admin/customers" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            ← Back to Customers
        </a>
    </div>
</div>

<!-- Customer Status & Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500 mb-1">Status</div>
        <div class="text-2xl font-bold">
            <span class="px-3 py-1 text-sm rounded-full {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ ucfirst($customer->status) }}
            </span>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-1">
            <div class="text-sm text-gray-500">Credit Limit</div>
            <button onclick="openCreditLimitModal()" class="text-blue-600 hover:text-blue-800 text-sm">
                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </button>
        </div>
        <div class="text-2xl font-bold text-blue-600">KES {{ number_format($customer->credit_limit, 2) }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500 mb-1">Total Borrowed</div>
        <div class="text-2xl font-bold text-purple-600">KES {{ number_format($customer->total_borrowed, 2) }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500 mb-1">Outstanding Balance</div>
        <div class="text-2xl font-bold {{ $customer->outstandingBalance() > 0 ? 'text-red-600' : 'text-green-600' }}">
            KES {{ number_format($customer->outstandingBalance(), 2) }}
        </div>
    </div>
</div>

<!-- Personal Information -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Personal Information</h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-sm font-medium text-gray-500">Full Name</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->name }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">ID Number</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->id_number ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Phone Number</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->phone }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Email</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->email ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-500">Address</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->address ?? 'N/A' }}</p>
            </div>
            @if($customer->business_name)
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-500">Business Name</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->business_name }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Motorcycle Details -->
@if($customer->motorcycle_number_plate)
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Motorcycle Details</h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-sm font-medium text-gray-500">Number Plate</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->motorcycle_number_plate }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Chassis Number</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->motorcycle_chassis_number ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Model</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->motorcycle_model ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Type</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->motorcycle_type ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Engine CC</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->motorcycle_engine_cc ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Colour</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->motorcycle_colour ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Next of Kin Details -->
@if($customer->next_of_kin_name)
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Next of Kin Details</h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-sm font-medium text-gray-500">Name</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->next_of_kin_name }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Phone Number</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->next_of_kin_phone ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Relationship</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->next_of_kin_relationship ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Email</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->next_of_kin_email ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Guarantor Details -->
@if($customer->guarantor_name)
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Guarantor Details</h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-sm font-medium text-gray-500">Name</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->guarantor_name }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Phone Number</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->guarantor_phone ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Relationship</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->guarantor_relationship ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Email</label>
                <p class="text-lg font-semibold text-gray-900">{{ $customer->guarantor_email ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Document Uploads -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Uploaded Documents</h2>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Bike Photo -->
        @if($customer->bike_photo_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Motorcycle Photo
            </h3>
            <img src="{{ str_starts_with($customer->bike_photo_path, 'http') ? $customer->bike_photo_path : asset('storage/' . $customer->bike_photo_path) }}"
                 alt="Motorcycle" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->bike_photo_path, 'http') ? $customer->bike_photo_path : asset('storage/' . $customer->bike_photo_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif

        <!-- Logbook Photo -->
        @if($customer->logbook_photo_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Logbook
            </h3>
            <img src="{{ str_starts_with($customer->logbook_photo_path, 'http') ? $customer->logbook_photo_path : asset('storage/' . $customer->logbook_photo_path) }}"
                 alt="Logbook" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->logbook_photo_path, 'http') ? $customer->logbook_photo_path : asset('storage/' . $customer->logbook_photo_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif

        <!-- Passport Photo -->
        @if($customer->passport_photo_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Passport Photo
            </h3>
            <img src="{{ str_starts_with($customer->passport_photo_path, 'http') ? $customer->passport_photo_path : asset('storage/' . $customer->passport_photo_path) }}"
                 alt="Passport Photo" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->passport_photo_path, 'http') ? $customer->passport_photo_path : asset('storage/' . $customer->passport_photo_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif

        <!-- ID Photo Front -->
        @if($customer->id_photo_front_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
                National ID (Front)
            </h3>
            <img src="{{ str_starts_with($customer->id_photo_front_path, 'http') ? $customer->id_photo_front_path : asset('storage/' . $customer->id_photo_front_path) }}"
                 alt="National ID Front" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->id_photo_front_path, 'http') ? $customer->id_photo_front_path : asset('storage/' . $customer->id_photo_front_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif

        <!-- ID Photo Back -->
        @if($customer->id_photo_back_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
                National ID (Back)
            </h3>
            <img src="{{ str_starts_with($customer->id_photo_back_path, 'http') ? $customer->id_photo_back_path : asset('storage/' . $customer->id_photo_back_path) }}"
                 alt="National ID Back" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->id_photo_back_path, 'http') ? $customer->id_photo_back_path : asset('storage/' . $customer->id_photo_back_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif

        <!-- Next of Kin ID Front -->
        @if($customer->next_of_kin_id_front_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Next of Kin ID (Front)
            </h3>
            <img src="{{ str_starts_with($customer->next_of_kin_id_front_path, 'http') ? $customer->next_of_kin_id_front_path : asset('storage/' . $customer->next_of_kin_id_front_path) }}"
                 alt="Next of Kin ID Front" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->next_of_kin_id_front_path, 'http') ? $customer->next_of_kin_id_front_path : asset('storage/' . $customer->next_of_kin_id_front_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif

        <!-- Next of Kin ID Back -->
        @if($customer->next_of_kin_id_back_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Next of Kin ID (Back)
            </h3>
            <img src="{{ str_starts_with($customer->next_of_kin_id_back_path, 'http') ? $customer->next_of_kin_id_back_path : asset('storage/' . $customer->next_of_kin_id_back_path) }}"
                 alt="Next of Kin ID Back" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->next_of_kin_id_back_path, 'http') ? $customer->next_of_kin_id_back_path : asset('storage/' . $customer->next_of_kin_id_back_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif

        <!-- Next of Kin Passport Photo -->
        @if($customer->next_of_kin_passport_photo_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Next of Kin Passport Photo
            </h3>
            <img src="{{ str_starts_with($customer->next_of_kin_passport_photo_path, 'http') ? $customer->next_of_kin_passport_photo_path : asset('storage/' . $customer->next_of_kin_passport_photo_path) }}"
                 alt="Next of Kin Passport Photo" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->next_of_kin_passport_photo_path, 'http') ? $customer->next_of_kin_passport_photo_path : asset('storage/' . $customer->next_of_kin_passport_photo_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif

        <!-- Guarantor ID Front -->
        @if($customer->guarantor_id_front_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Guarantor ID (Front)
            </h3>
            <img src="{{ str_starts_with($customer->guarantor_id_front_path, 'http') ? $customer->guarantor_id_front_path : asset('storage/' . $customer->guarantor_id_front_path) }}"
                 alt="Guarantor ID Front" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->guarantor_id_front_path, 'http') ? $customer->guarantor_id_front_path : asset('storage/' . $customer->guarantor_id_front_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif

        <!-- Guarantor ID Back -->
        @if($customer->guarantor_id_back_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Guarantor ID (Back)
            </h3>
            <img src="{{ str_starts_with($customer->guarantor_id_back_path, 'http') ? $customer->guarantor_id_back_path : asset('storage/' . $customer->guarantor_id_back_path) }}"
                 alt="Guarantor ID Back" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->guarantor_id_back_path, 'http') ? $customer->guarantor_id_back_path : asset('storage/' . $customer->guarantor_id_back_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif

        <!-- Guarantor Passport Photo -->
        @if($customer->guarantor_passport_photo_path)
        <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-lg transition-shadow">
            <h3 class="font-semibold mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Guarantor Passport Photo
            </h3>
            <img src="{{ str_starts_with($customer->guarantor_passport_photo_path, 'http') ? $customer->guarantor_passport_photo_path : asset('storage/' . $customer->guarantor_passport_photo_path) }}"
                 alt="Guarantor Passport Photo" class="w-full h-48 object-cover rounded mb-2 border">
            <a href="{{ str_starts_with($customer->guarantor_passport_photo_path, 'http') ? $customer->guarantor_passport_photo_path : asset('storage/' . $customer->guarantor_passport_photo_path) }}"
               target="_blank" class="text-blue-600 hover:underline text-sm font-medium">📸 View Full Size</a>
        </div>
        @endif
    </div>

    @if(!$customer->bike_photo_path && !$customer->logbook_photo_path && !$customer->passport_photo_path && !$customer->id_photo_front_path && !$customer->id_photo_back_path && !$customer->next_of_kin_id_front_path && !$customer->next_of_kin_id_back_path && !$customer->next_of_kin_passport_photo_path && !$customer->guarantor_id_front_path && !$customer->guarantor_id_back_path && !$customer->guarantor_passport_photo_path)
    <p class="text-gray-500 text-center py-8">No documents uploaded for this customer</p>
    @endif
</div>

<!-- Loan History -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Loan History</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($customer->loans as $loan)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium text-gray-900">{{ $loan->loan_number }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-semibold">KES {{ number_format($loan->total_amount, 2) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-semibold {{ $loan->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                            KES {{ number_format($loan->balance, 2) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $loan->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $loan->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $loan->status === 'active' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $loan->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $loan->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                        ">
                            {{ ucfirst($loan->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $loan->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="/admin/loans/{{ $loan->id }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                            View Loan
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No loans found for this customer</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($customer->notes)
<!-- Notes -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Notes</h2>
    </div>
    <div class="p-6">
        <p class="text-gray-700 whitespace-pre-line">{{ $customer->notes }}</p>
    </div>
</div>
@endif

<!-- Credit Limit Update Modal -->
<div id="creditLimitModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Update Credit Limit</h3>
            <form method="POST" action="/admin/customers/{{ $customer->id }}/update-credit-limit">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="credit_limit">
                        Credit Limit (KES)
                    </label>
                    <input type="number" step="0.01" min="0" name="credit_limit" id="credit_limit"
                           value="{{ $customer->credit_limit }}"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           required>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="closeCreditLimitModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Update Credit Limit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreditLimitModal() {
    document.getElementById('creditLimitModal').classList.remove('hidden');
}

function closeCreditLimitModal() {
    document.getElementById('creditLimitModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('creditLimitModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreditLimitModal();
    }
});
</script>

@if(session('success'))
<script>
    setTimeout(() => {
        alert('{{ session('success') }}');
    }, 100);
</script>
@endif

@endsection
