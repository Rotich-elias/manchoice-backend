@extends('admin.layout')

@section('title', 'Part Requests')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-800">Part Requests</h1>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Part Details</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Urgency</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($partRequests as $request)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="font-medium text-gray-900">{{ $request->customer->name }}</div>
                    <div class="text-sm text-gray-500">{{ $request->customer->phone }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-semibold text-gray-900">{{ $request->part_name }}</div>
                    @if($request->motorcycle_model)
                    <div class="text-sm text-gray-600">{{ $request->motorcycle_model }}{{ $request->year ? ' ('.$request->year.')' : '' }}</div>
                    @endif
                    <div class="text-sm text-gray-500">Qty: {{ $request->quantity }}{{ $request->budget ? ' | Budget: KSh '.number_format($request->budget) : '' }}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded {{ $request->urgency === 'high' ? 'bg-red-100 text-red-800' : ($request->urgency === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                        {{ strtoupper($request->urgency) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full {{ $request->status === 'pending' ? 'bg-orange-100 text-orange-800' : ($request->status === 'available' ? 'bg-green-100 text-green-800' : ($request->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) }}">
                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->created_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <button onclick="showStatusModal({{ $request->id }}, '{{ $request->status }}', '{{ addslashes($request->admin_notes ?? '') }}')" class="text-blue-600 hover:text-blue-800 mr-2">
                        Update
                    </button>
                    <a href="/admin/part-requests/{{ $request->id }}" class="text-green-600 hover:text-green-800">
                        View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    No part requests found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $partRequests->links() }}
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold mb-4">Update Request Status</h3>
        <form id="statusForm" method="POST">
            @csrf
            <input type="hidden" id="requestId" name="request_id">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="statusSelect" class="block w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="available">Available</option>
                    <option value="fulfilled">Fulfilled</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                <textarea name="admin_notes" id="adminNotes" rows="3" class="block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showStatusModal(id, status, notes) {
    document.getElementById('requestId').value = id;
    document.getElementById('statusSelect').value = status;
    document.getElementById('adminNotes').value = notes;
    document.getElementById('statusForm').action = '/admin/part-requests/' + id + '/update-status';
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endsection
