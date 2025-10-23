@extends('admin.layout')

@section('title', 'Support Tickets')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Support & Feedback Tickets</h1>
    <p class="text-gray-600 mt-1">View and manage customer support requests and feedback</p>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Total Tickets</div>
        <div class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Open</div>
        <div class="text-3xl font-bold text-yellow-600">{{ $stats['open'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">In Progress</div>
        <div class="text-3xl font-bold text-purple-600">{{ $stats['in_progress'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm">Resolved</div>
        <div class="text-3xl font-bold text-green-600">{{ $stats['resolved'] }}</div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-6 rounded-lg shadow mb-6">
    <form method="GET" action="/admin/support-tickets" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2 border rounded-lg">
                <option value="">All Statuses</option>
                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select name="type" class="w-full px-4 py-2 border rounded-lg">
                <option value="">All Types</option>
                <option value="bug" {{ request('type') == 'bug' ? 'selected' : '' }}>Bug Report</option>
                <option value="feature_request" {{ request('type') == 'feature_request' ? 'selected' : '' }}>Feature Request</option>
                <option value="help" {{ request('type') == 'help' ? 'selected' : '' }}>Help/Support</option>
                <option value="complaint" {{ request('type') == 'complaint' ? 'selected' : '' }}>Complaint</option>
                <option value="feedback" {{ request('type') == 'feedback' ? 'selected' : '' }}>Feedback</option>
                <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
            <select name="priority" class="w-full px-4 py-2 border rounded-lg">
                <option value="">All Priorities</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <div class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket # or subject" class="flex-1 px-4 py-2 border rounded-lg">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">Filter</button>
            </div>
        </div>
    </form>
</div>

<!-- Tickets List -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tickets as $ticket)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-semibold text-blue-600">{{ $ticket->ticket_number }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $ticket->type === 'bug' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $ticket->type === 'feature_request' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $ticket->type === 'help' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $ticket->type === 'complaint' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $ticket->type === 'feedback' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $ticket->type === 'other' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucwords(str_replace('_', ' ', $ticket->type)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="max-w-xs">
                            <div class="font-medium text-gray-900">{{ $ticket->subject }}</div>
                            <div class="text-sm text-gray-500 truncate">{{ Str::limit($ticket->message, 60) }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($ticket->customer)
                            <a href="/admin/customers/{{ $ticket->customer->id }}" class="text-blue-600 hover:underline">
                                {{ $ticket->customer->name }}
                            </a>
                        @elseif($ticket->user)
                            {{ $ticket->user->name }}
                        @else
                            <span class="text-gray-400">Guest</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full font-semibold
                            {{ $ticket->priority === 'urgent' ? 'bg-red-500 text-white' : '' }}
                            {{ $ticket->priority === 'high' ? 'bg-orange-500 text-white' : '' }}
                            {{ $ticket->priority === 'medium' ? 'bg-yellow-500 text-white' : '' }}
                            {{ $ticket->priority === 'low' ? 'bg-green-500 text-white' : '' }}">
                            {{ strtoupper($ticket->priority) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $ticket->status === 'open' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $ticket->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $ticket->status === 'resolved' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $ticket->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $ticket->created_at->format('M d, Y') }}
                        <br>
                        <span class="text-xs">{{ $ticket->created_at->diffForHumans() }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="/admin/support-tickets/{{ $ticket->id }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        No support tickets found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t">
        {{ $tickets->links() }}
    </div>
</div>
@endsection
