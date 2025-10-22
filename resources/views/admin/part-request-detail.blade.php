@extends('admin.layout')

@section('title', 'Part Request Details')

@section('content')
<div class="mb-6">
    <a href="/admin/part-requests" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
        &larr; Back to Requests
    </a>
    <h1 class="text-3xl font-bold text-gray-800">Part Request Details</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Request Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Part Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Part Information</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Part Name</label>
                    <p class="text-gray-900 font-semibold">{{ $partRequest->part_name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Quantity</label>
                    <p class="text-gray-900">{{ $partRequest->quantity }}</p>
                </div>
                @if($partRequest->motorcycle_model)
                <div>
                    <label class="text-sm font-medium text-gray-500">Motorcycle Model</label>
                    <p class="text-gray-900">{{ $partRequest->motorcycle_model }}</p>
                </div>
                @endif
                @if($partRequest->year)
                <div>
                    <label class="text-sm font-medium text-gray-500">Year</label>
                    <p class="text-gray-900">{{ $partRequest->year }}</p>
                </div>
                @endif
                @if($partRequest->budget)
                <div>
                    <label class="text-sm font-medium text-gray-500">Budget</label>
                    <p class="text-gray-900">KSh {{ number_format($partRequest->budget) }}</p>
                </div>
                @endif
                <div>
                    <label class="text-sm font-medium text-gray-500">Urgency</label>
                    <p>
                        <span class="px-2 py-1 text-xs rounded {{ $partRequest->urgency === 'high' ? 'bg-red-100 text-red-800' : ($partRequest->urgency === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                            {{ strtoupper($partRequest->urgency) }}
                        </span>
                    </p>
                </div>
            </div>
            @if($partRequest->additional_notes)
            <div class="mt-4">
                <label class="text-sm font-medium text-gray-500">Additional Notes</label>
                <p class="text-gray-900 mt-1">{{ $partRequest->additional_notes }}</p>
            </div>
            @endif
        </div>

        <!-- Images -->
        @if($partRequest->image_paths && count(json_decode($partRequest->image_paths)) > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Images</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach(json_decode($partRequest->image_paths) as $imagePath)
                <div class="border rounded-lg overflow-hidden">
                    <img src="{{ asset('storage/' . $imagePath) }}" alt="Part image" class="w-full h-48 object-cover cursor-pointer" onclick="showImageModal('{{ asset('storage/' . $imagePath) }}')">
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Admin Notes -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Admin Notes</h2>
            <form method="POST" action="/admin/part-requests/{{ $partRequest->id }}/update-status">
                @csrf
                <textarea name="admin_notes" rows="4" class="block w-full border border-gray-300 rounded-md px-3 py-2 mb-3" placeholder="Add notes about this request...">{{ $partRequest->admin_notes }}</textarea>
                <input type="hidden" name="status" value="{{ $partRequest->status }}">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save Notes
                </button>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Status & Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Status</h2>
            <div class="mb-4">
                <span class="px-3 py-2 text-sm rounded-full {{ $partRequest->status === 'pending' ? 'bg-orange-100 text-orange-800' : ($partRequest->status === 'available' ? 'bg-green-100 text-green-800' : ($partRequest->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) }}">
                    {{ ucfirst(str_replace('_', ' ', $partRequest->status)) }}
                </span>
            </div>
            <form method="POST" action="/admin/part-requests/{{ $partRequest->id }}/update-status">
                @csrf
                <label class="block text-sm font-medium text-gray-700 mb-2">Update Status</label>
                <select name="status" class="block w-full border border-gray-300 rounded-md px-3 py-2 mb-3">
                    <option value="pending" {{ $partRequest->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ $partRequest->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="available" {{ $partRequest->status === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="fulfilled" {{ $partRequest->status === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                    <option value="cancelled" {{ $partRequest->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <input type="hidden" name="admin_notes" value="{{ $partRequest->admin_notes }}">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update Status
                </button>
            </form>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Customer</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Name</label>
                    <p class="text-gray-900">{{ $partRequest->customer->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Phone</label>
                    <p class="text-gray-900">{{ $partRequest->customer->phone }}</p>
                </div>
                @if($partRequest->customer->email)
                <div>
                    <label class="text-sm font-medium text-gray-500">Email</label>
                    <p class="text-gray-900">{{ $partRequest->customer->email }}</p>
                </div>
                @endif
                <div class="pt-3 border-t">
                    <a href="/admin/customers/{{ $partRequest->customer->id }}" class="text-blue-600 hover:text-blue-800">
                        View Customer Profile &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Status Timeline</h2>
            <div class="space-y-4">
                <!-- Created Event -->
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">Request Created</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $partRequest->created_at->format('M d, Y h:i A') }}</p>
                        @if($partRequest->user)
                        <p class="text-xs text-gray-400">by {{ $partRequest->user->name }}</p>
                        @endif
                    </div>
                </div>

                <!-- Status History -->
                @forelse($partRequest->statusHistories as $history)
                <div class="flex items-start border-l-2 border-gray-200 pl-2 ml-4">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            {{ $history->status === 'pending' ? 'bg-orange-100' : '' }}
                            {{ $history->status === 'in_progress' ? 'bg-blue-100' : '' }}
                            {{ $history->status === 'available' ? 'bg-green-100' : '' }}
                            {{ $history->status === 'fulfilled' ? 'bg-purple-100' : '' }}
                            {{ $history->status === 'cancelled' ? 'bg-red-100' : '' }}">
                            <div class="w-3 h-3 rounded-full
                                {{ $history->status === 'pending' ? 'bg-orange-500' : '' }}
                                {{ $history->status === 'in_progress' ? 'bg-blue-500' : '' }}
                                {{ $history->status === 'available' ? 'bg-green-500' : '' }}
                                {{ $history->status === 'fulfilled' ? 'bg-purple-500' : '' }}
                                {{ $history->status === 'cancelled' ? 'bg-red-500' : '' }}"></div>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            Status changed to
                            <span class="px-2 py-0.5 text-xs rounded-full
                                {{ $history->status === 'pending' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $history->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $history->status === 'available' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $history->status === 'fulfilled' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $history->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                            </span>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ $history->created_at->format('M d, Y h:i A') }}</p>
                        @if($history->user)
                        <p class="text-xs text-gray-400">by {{ $history->user->name }}</p>
                        @endif
                        @if($history->notes)
                        <p class="text-xs text-gray-600 mt-1 italic">"{{ $history->notes }}"</p>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-sm text-gray-400 italic pl-11">No status changes yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center" onclick="closeImageModal()">
    <div class="max-w-4xl max-h-screen p-4">
        <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-full rounded-lg">
    </div>
</div>

<script>
function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}
</script>

@if(session('success'))
<script>
    setTimeout(() => {
        alert('{{ session('success') }}');
    }, 100);
</script>
@endif
@endsection
