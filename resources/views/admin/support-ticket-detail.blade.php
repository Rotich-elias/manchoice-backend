@extends('admin.layout')

@section('title', 'Ticket Details')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Ticket: {{ $ticket->ticket_number }}</h1>
            <p class="text-gray-600 mt-1">View and respond to support ticket</p>
        </div>
        <a href="/admin/support-tickets" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            ← Back to Tickets
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Ticket Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $ticket->subject }}</h2>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="px-3 py-1 text-xs rounded-full font-semibold
                            {{ $ticket->type === 'bug' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $ticket->type === 'feature_request' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $ticket->type === 'help' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $ticket->type === 'complaint' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $ticket->type === 'feedback' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $ticket->type === 'other' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucwords(str_replace('_', ' ', $ticket->type)) }}
                        </span>
                        <span class="px-3 py-1 text-xs rounded-full font-semibold
                            {{ $ticket->priority === 'urgent' ? 'bg-red-500 text-white' : '' }}
                            {{ $ticket->priority === 'high' ? 'bg-orange-500 text-white' : '' }}
                            {{ $ticket->priority === 'medium' ? 'bg-yellow-500 text-white' : '' }}
                            {{ $ticket->priority === 'low' ? 'bg-green-500 text-white' : '' }}">
                            {{ strtoupper($ticket->priority) }} PRIORITY
                        </span>
                        <span class="px-3 py-1 text-xs rounded-full
                            {{ $ticket->status === 'open' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $ticket->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $ticket->status === 'resolved' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $ticket->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="border-t pt-4 mt-4">
                <h3 class="font-semibold text-gray-700 mb-2">Customer Message:</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $ticket->message }}</p>
                </div>
            </div>

            <div class="border-t pt-4 mt-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Submitted:</span>
                        <span class="font-medium">{{ $ticket->created_at->format('F d, Y \a\t h:i A') }}</span>
                    </div>
                    @if($ticket->resolved_at)
                    <div>
                        <span class="text-gray-600">Resolved:</span>
                        <span class="font-medium">{{ $ticket->resolved_at->format('F d, Y \a\t h:i A') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Admin Response History -->
        @if($ticket->admin_response)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Response History</h3>
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $ticket->admin_response }}</p>
            </div>
        </div>
        @endif

        <!-- Add Response Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                {{ $ticket->admin_response ? 'Add Additional Response' : 'Add Response' }}
            </h3>
            <form action="/admin/support-tickets/{{ $ticket->id }}/update" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Response</label>
                    <textarea name="admin_response" rows="6" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Type your response to the customer..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" required class="w-full px-4 py-2 border rounded-lg">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select name="priority" class="w-full px-4 py-2 border rounded-lg">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                        <select name="assigned_to" class="w-full px-4 py-2 border rounded-lg">
                            <option value="">Unassigned</option>
                            @foreach(\App\Models\User::where('role', 'admin')->get() as $admin)
                                <option value="{{ $admin->id }}" {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">
                    Update Ticket
                </button>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Customer Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-900 mb-4">Customer Information</h3>

            @if($ticket->customer)
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Name:</span>
                    <p class="font-medium">
                        <a href="/admin/customers/{{ $ticket->customer->id }}" class="text-blue-600 hover:underline">
                            {{ $ticket->customer->name }}
                        </a>
                    </p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Phone:</span>
                    <p class="font-medium">{{ $ticket->customer->phone }}</p>
                </div>
                @if($ticket->customer->email)
                <div>
                    <span class="text-sm text-gray-600">Email:</span>
                    <p class="font-medium">{{ $ticket->customer->email }}</p>
                </div>
                @endif
            </div>
            @elseif($ticket->user)
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">User:</span>
                    <p class="font-medium">{{ $ticket->user->name }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Phone:</span>
                    <p class="font-medium">{{ $ticket->user->phone ?? 'N/A' }}</p>
                </div>
            </div>
            @else
            <div class="space-y-3">
                @if($ticket->contact_email)
                <div>
                    <span class="text-sm text-gray-600">Email:</span>
                    <p class="font-medium">{{ $ticket->contact_email }}</p>
                </div>
                @endif
                @if($ticket->contact_phone)
                <div>
                    <span class="text-sm text-gray-600">Phone:</span>
                    <p class="font-medium">{{ $ticket->contact_phone }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Ticket Details -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-900 mb-4">Ticket Details</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-gray-600">Ticket Number:</span>
                    <p class="font-semibold text-blue-600">{{ $ticket->ticket_number }}</p>
                </div>
                <div>
                    <span class="text-gray-600">Created:</span>
                    <p class="font-medium">{{ $ticket->created_at->diffForHumans() }}</p>
                </div>
                @if($ticket->assignedAdmin)
                <div>
                    <span class="text-gray-600">Assigned To:</span>
                    <p class="font-medium">{{ $ticket->assignedAdmin->name }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                @if($ticket->status !== 'closed')
                <form action="/admin/support-tickets/{{ $ticket->id }}/update" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="closed">
                    <button type="submit" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm">
                        Close Ticket
                    </button>
                </form>
                @endif
                @if($ticket->status !== 'resolved')
                <form action="/admin/support-tickets/{{ $ticket->id }}/update" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="resolved">
                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                        Mark as Resolved
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
