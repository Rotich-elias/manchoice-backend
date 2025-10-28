<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of staff users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['creator'])
            ->where('role', '!=', User::ROLE_CUSTOMER) // Exclude customers
            ->latest();

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, email, or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created staff user
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|regex:/^0[0-9]{9}$/|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in([
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN,
                User::ROLE_MANAGER,
                User::ROLE_CLERK,
                User::ROLE_COLLECTOR,
            ])],
            'status' => ['nullable', Rule::in([
                User::STATUS_ACTIVE,
                User::STATUS_INACTIVE,
                User::STATUS_SUSPENDED,
            ])],
            'approval_limit' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if current user has permission to create this role
        $currentUser = $request->user();
        if (!$this->canManageRole($currentUser, $request->role)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create users with this role'
            ], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'pin' => Hash::make('1234'), // Default PIN
            'role' => $request->role,
            'status' => $request->status ?? User::STATUS_ACTIVE,
            'approval_limit' => $request->approval_limit,
            'created_by' => $currentUser->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load('creator')
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = User::with(['creator', 'createdUsers'])
            ->where('role', '!=', User::ROLE_CUSTOMER)
            ->findOrFail($id);

        // Super admin can view all
        // Admin can view all except super admin
        // Others cannot view user details
        $currentUser = $request->user();
        if (!$currentUser->isSuperAdmin() && $user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::where('role', '!=', User::ROLE_CUSTOMER)->findOrFail($id);

        // Check permissions
        $currentUser = $request->user();
        if (!$this->canManageUser($currentUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this user'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'phone' => 'sometimes|required|string|regex:/^0[0-9]{9}$/|unique:users,phone,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['sometimes', 'required', Rule::in([
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN,
                User::ROLE_MANAGER,
                User::ROLE_CLERK,
                User::ROLE_COLLECTOR,
            ])],
            'status' => ['sometimes', Rule::in([
                User::STATUS_ACTIVE,
                User::STATUS_INACTIVE,
                User::STATUS_SUSPENDED,
            ])],
            'approval_limit' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if trying to change role
        if ($request->has('role') && $request->role !== $user->role) {
            if (!$this->canManageRole($currentUser, $request->role)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to assign this role'
                ], 403);
            }
        }

        $data = $request->only(['name', 'email', 'phone', 'role', 'status', 'approval_limit']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->fresh(['creator'])
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = User::where('role', '!=', User::ROLE_CUSTOMER)->findOrFail($id);

        // Check permissions
        $currentUser = $request->user();
        if (!$this->canManageUser($currentUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this user'
            ], 403);
        }

        // Prevent deleting self
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        // Check if user has created other users
        if ($user->createdUsers()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete user who has created other users. Please reassign or delete those users first.'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Suspend or activate user
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = User::where('role', '!=', User::ROLE_CUSTOMER)->findOrFail($id);

        // Check permissions
        $currentUser = $request->user();
        if (!$this->canManageUser($currentUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to modify this user'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in([
                User::STATUS_ACTIVE,
                User::STATUS_INACTIVE,
                User::STATUS_SUSPENDED,
            ])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent suspending self
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own status'
            ], 422);
        }

        $user->update(['status' => $request->status]);

        $statusText = [
            User::STATUS_ACTIVE => 'activated',
            User::STATUS_INACTIVE => 'deactivated',
            User::STATUS_SUSPENDED => 'suspended',
        ];

        return response()->json([
            'success' => true,
            'message' => "User {$statusText[$request->status]} successfully",
            'data' => $user
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $user = User::where('role', '!=', User::ROLE_CUSTOMER)->findOrFail($id);

        // Check permissions
        $currentUser = $request->user();
        if (!$this->canManageUser($currentUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reset password for this user'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Get user statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_staff' => User::where('role', '!=', User::ROLE_CUSTOMER)->count(),
            'by_role' => [
                'super_admin' => User::where('role', User::ROLE_SUPER_ADMIN)->count(),
                'admin' => User::where('role', User::ROLE_ADMIN)->count(),
                'manager' => User::where('role', User::ROLE_MANAGER)->count(),
                'clerk' => User::where('role', User::ROLE_CLERK)->count(),
                'collector' => User::where('role', User::ROLE_COLLECTOR)->count(),
            ],
            'by_status' => [
                'active' => User::where('role', '!=', User::ROLE_CUSTOMER)
                    ->where('status', User::STATUS_ACTIVE)->count(),
                'inactive' => User::where('role', '!=', User::ROLE_CUSTOMER)
                    ->where('status', User::STATUS_INACTIVE)->count(),
                'suspended' => User::where('role', '!=', User::ROLE_CUSTOMER)
                    ->where('status', User::STATUS_SUSPENDED)->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Check if current user can manage target user
     */
    private function canManageUser(User $currentUser, User $targetUser): bool
    {
        // Super admin can manage all
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        // Admin can manage all except super admin
        if ($currentUser->isAdmin() && !$targetUser->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Check if current user can create/assign a role
     */
    private function canManageRole(User $currentUser, string $role): bool
    {
        // Super admin can assign all roles
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        // Admin can assign all roles except super_admin
        if ($currentUser->isAdmin() && $role !== User::ROLE_SUPER_ADMIN) {
            return true;
        }

        return false;
    }
}
