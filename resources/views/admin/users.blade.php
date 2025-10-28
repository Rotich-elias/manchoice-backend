@extends('admin.layout')

@section('title', 'Staff Management')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-800">Staff Management</h1>
    <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add New User
    </button>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm mb-1">Total Staff</div>
        <div class="text-3xl font-bold text-gray-800">{{ $stats['total_staff'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm mb-1">Active</div>
        <div class="text-3xl font-bold text-green-600">{{ $stats['by_status']['active'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm mb-1">Inactive</div>
        <div class="text-3xl font-bold text-gray-600">{{ $stats['by_status']['inactive'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="text-gray-500 text-sm mb-1">Suspended</div>
        <div class="text-3xl font-bold text-red-600">{{ $stats['by_status']['suspended'] }}</div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-lg shadow mb-4">
    <form method="GET" action="/admin/users" class="flex gap-4">
        <select name="role" class="border rounded px-4 py-2">
            <option value="">All Roles</option>
            <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
            <option value="clerk" {{ request('role') == 'clerk' ? 'selected' : '' }}>Clerk</option>
            <option value="collector" {{ request('role') == 'collector' ? 'selected' : '' }}>Collector</option>
        </select>
        <select name="status" class="border rounded px-4 py-2">
            <option value="">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
        <input type="text" name="search" placeholder="Search by name, email, phone..." value="{{ request('search') }}" class="border rounded px-4 py-2 flex-1">
        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded">Filter</button>
        <a href="/admin/users" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded">Clear</a>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approval Limit</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created By</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                    <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if($user->role === 'super_admin') bg-purple-100 text-purple-800
                        @elseif($user->role === 'admin') bg-blue-100 text-blue-800
                        @elseif($user->role === 'manager') bg-green-100 text-green-800
                        @elseif($user->role === 'clerk') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucwords(str_replace('_', ' ', $user->role)) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if($user->status === 'active') bg-green-100 text-green-800
                        @elseif($user->status === 'inactive') bg-gray-100 text-gray-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($user->approval_limit)
                        <span class="text-sm text-gray-900">KSh {{ number_format($user->approval_limit) }}</span>
                    @else
                        <span class="text-sm text-gray-400">No limit</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $user->creator ? $user->creator->name : 'System' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex space-x-2">
                        <button onclick="openEditModal({{ json_encode($user) }})" class="text-blue-600 hover:text-blue-800" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>

                        @if($user->status === 'active')
                            <form method="POST" action="/admin/users/{{ $user->id }}/update-status" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="suspended">
                                <button type="submit" class="text-yellow-600 hover:text-yellow-800" title="Suspend" onclick="return confirm('Suspend this user?')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                </button>
                            </form>
                        @else
                            <form method="POST" action="/admin/users/{{ $user->id }}/update-status" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="text-green-600 hover:text-green-800" title="Activate" onclick="return confirm('Activate this user?')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            </form>
                        @endif

                        <button onclick="openPasswordModal({{ $user->id }}, '{{ $user->name }}')" class="text-purple-600 hover:text-purple-800" title="Reset Password">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                        </button>

                        @if(auth()->user()->id !== $user->id)
                            <form method="POST" action="/admin/users/{{ $user->id }}/delete" class="inline">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    No users found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($users->hasPages())
<div class="mt-4">
    {{ $users->links() }}
</div>
@endif

<!-- Create/Edit User Modal -->
<div id="userModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-2xl font-bold text-gray-900">Add New User</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="userForm" method="POST" action="/admin/users/store">
            @csrf

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded px-3 py-2">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded px-3 py-2">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required pattern="0[0-9]{9}" placeholder="0712345678" class="w-full border rounded px-3 py-2">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1">Format: 0712345678</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                    <select name="role" required class="w-full border rounded px-3 py-2">
                        <option value="">Select Role</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="clerk" {{ old('role') == 'clerk' ? 'selected' : '' }}>Clerk</option>
                        <option value="collector" {{ old('role') == 'collector' ? 'selected' : '' }}>Collector</option>
                    </select>
                    @error('role')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" required class="w-full border rounded px-3 py-2">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Approval Limit (KSh)</label>
                    <input type="number" name="approval_limit" value="{{ old('approval_limit') }}" step="0.01" min="0" placeholder="Optional" class="w-full border rounded px-3 py-2">
                    @error('approval_limit')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="passwordFields" class="col-span-2 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                        <input type="password" name="password" id="password" required minlength="8" class="w-full border rounded px-3 py-2">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-xs mt-1">Minimum 8 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" class="w-full border rounded px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Save User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="passwordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Reset Password</h3>
            <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="passwordForm" method="POST">
            @csrf
            <p class="text-sm text-gray-600 mb-4">Reset password for: <strong id="resetUserName"></strong></p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">New Password *</label>
                <input type="password" name="password" required minlength="8" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                <input type="password" name="password_confirmation" required minlength="8" class="w-full border rounded px-3 py-2">
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closePasswordModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Check if there are validation errors and reopen the modal
@if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        openCreateModal();
    });
@endif

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('userForm').action = '/admin/users/store';
    // Don't reset if there are errors (to preserve field values)
    @if(!$errors->any())
        document.getElementById('userForm').reset();
    @endif
    document.getElementById('passwordFields').classList.remove('hidden');
    document.getElementById('password').required = true;
    document.getElementById('password_confirmation').required = true;
    document.getElementById('userModal').classList.remove('hidden');
}

function openEditModal(user) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('userForm').action = '/admin/users/' + user.id + '/update';

    // Populate form
    document.querySelector('[name="name"]').value = user.name;
    document.querySelector('[name="email"]').value = user.email;
    document.querySelector('[name="phone"]').value = user.phone;
    document.querySelector('[name="role"]').value = user.role;
    document.querySelector('[name="status"]').value = user.status;
    document.querySelector('[name="approval_limit"]').value = user.approval_limit || '';

    // Hide password fields for editing
    document.getElementById('passwordFields').classList.add('hidden');
    document.getElementById('password').required = false;
    document.getElementById('password_confirmation').required = false;

    document.getElementById('userModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
    document.getElementById('userForm').reset();
}

function openPasswordModal(userId, userName) {
    document.getElementById('resetUserName').textContent = userName;
    document.getElementById('passwordForm').action = '/admin/users/' + userId + '/reset-password';
    document.getElementById('passwordModal').classList.remove('hidden');
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
    document.getElementById('passwordForm').reset();
}

// Close modals on outside click
window.onclick = function(event) {
    const userModal = document.getElementById('userModal');
    const passwordModal = document.getElementById('passwordModal');
    if (event.target === userModal) {
        closeModal();
    }
    if (event.target === passwordModal) {
        closePasswordModal();
    }
}
</script>
@endsection
