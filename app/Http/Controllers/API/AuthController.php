<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Lookup customer by phone or ID number (public endpoint for registration)
     */
    public function lookupCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'nullable|string|regex:/^0[0-9]{9}$/',
            'id_number' => 'nullable|string',
        ]);

        // Require at least one search parameter
        if (!$request->phone && !$request->id_number) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide either phone number or ID number'
            ], 400);
        }

        $query = \App\Models\Customer::query();

        // Search by phone or ID number
        if ($request->phone) {
            $query->where('phone', $request->phone);
        }
        if ($request->id_number) {
            $query->orWhere('id_number', $request->id_number);
        }

        $customer = $query->first();

        if ($customer) {
            // Check if customer is already linked to a user
            if ($customer->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This customer account is already registered. Please login instead of signing up.',
                    'data' => null,
                    'already_registered' => true
                ], 409); // 409 Conflict
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer found',
                'data' => $customer
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No matching customer record found',
            'data' => null
        ], 404);
    }

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        // First, check if linking to existing customer
        $linkingCustomer = null;
        if ($request->customer_id) {
            $linkingCustomer = \App\Models\Customer::find($request->customer_id);

            // Verify customer exists and is not already linked
            if (!$linkingCustomer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer record not found'
                ], 404);
            }

            if ($linkingCustomer->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This customer account is already linked to a user'
                ], 400);
            }
        }

        // Build validation rules
        $validationRules = [
            'name' => 'required|string|max:255',
            'pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'pin_confirmation' => 'required|same:pin',
            'password' => 'nullable|string|min:8',
            'accepted_terms' => 'required|boolean|accepted',
            'customer_id' => 'nullable|exists:customers,id',
            'claim_existing' => 'nullable|boolean',
        ];

        // For phone and email, check if they match the linking customer's info
        if ($linkingCustomer) {
            // If linking customer, verify phone matches
            if ($request->phone !== $linkingCustomer->phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number does not match customer record'
                ], 400);
            }

            // Check if phone already exists in users table
            $existingUserWithPhone = User::where('phone', $request->phone)->first();
            if ($existingUserWithPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'This phone number is already registered. If you already have an account, please login instead.',
                ], 400);
            }

            // Email can be optional for existing customer or must be unique
            $validationRules['email'] = 'nullable|string|email|max:255|unique:users';
            $validationRules['phone'] = 'required|string|regex:/^0[0-9]{9}$/';
        } else {
            // For new customers, both phone and email must be unique
            $validationRules['phone'] = 'required|string|regex:/^0[0-9]{9}$/|unique:users';
            $validationRules['email'] = 'required|string|email|max:255|unique:users';
        }

        $request->validate($validationRules);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'pin' => Hash::make($request->pin),
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : Hash::make($request->pin),
            'accepted_terms' => true,
            'accepted_terms_at' => now(),
            'accepted_terms_version' => '1.0',
            'accepted_terms_ip' => $request->ip(),
            'customer_id' => $request->customer_id, // Link to existing customer if provided
            // If linking to existing customer created by admin, skip registration fee
            'registration_fee_paid' => $request->customer_id ? true : false,
        ]);

        // If linking to existing customer, update the customer record
        if ($request->customer_id) {
            $customer = \App\Models\Customer::find($request->customer_id);
            $customer->update([
                'user_id' => $user->id,
                // Update customer details if provided in registration
                'email' => $request->email,
            ]);

            // Create a registration fee record marked as verified for admin-created customers
            \App\Models\RegistrationFee::create([
                'user_id' => $user->id,
                'amount' => 300.00,
                'mpesa_receipt_number' => 'ADMIN_CREATED',
                'phone_number' => $user->phone,
                'status' => 'verified',
                'verified_at' => now(),
                'verified_by' => null, // Can be set to admin user if available
                'notes' => 'Auto-verified for admin-created customer',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user->fresh()->load('customer'),
                'access_token' => $token,
                'token_type' => 'Bearer',
                'customer_linked' => $request->customer_id ? true : false,
            ]
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|regex:/^0[0-9]{9}$/',
            'pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->pin, $user->pin)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check registration fee status (only for customers, not staff)
        if ($user->role === User::ROLE_CUSTOMER && !$user->registration_fee_paid) {
            $registrationFee = $user->registrationFee;

            // If no registration fee record exists, user needs to pay
            if (!$registrationFee) {
                return response()->json([
                    'success' => false,
                    'requires_registration_fee' => true,
                    'registration_fee_status' => 'not_submitted',
                    'message' => 'Please pay the registration fee to proceed',
                    'data' => [
                        'user' => $user,
                        'payment_status' => [
                            'status' => 'not_submitted',
                            'message' => 'You need to pay the KES 300 registration fee to activate your account.',
                            'fee_amount' => 300.00,
                        ]
                    ]
                ], 402); // 402 Payment Required
            }

            // If registration fee is pending verification
            if ($registrationFee->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'requires_registration_fee' => true,
                    'registration_fee_status' => 'pending_verification',
                    'message' => 'Your registration fee payment is awaiting admin verification',
                    'data' => [
                        'user' => $user,
                        'payment_status' => [
                            'status' => 'pending_verification',
                            'message' => 'Your payment has been submitted and is awaiting admin verification. This usually takes a few hours.',
                            'fee_amount' => 300.00,
                            'submitted_at' => $registrationFee->created_at,
                            'mpesa_code' => $registrationFee->mpesa_receipt_number,
                        ]
                    ]
                ], 402); // 402 Payment Required
            }

            // If registration fee was rejected
            if ($registrationFee->status === 'failed') {
                return response()->json([
                    'success' => false,
                    'requires_registration_fee' => true,
                    'registration_fee_status' => 'rejected',
                    'message' => 'Your registration fee payment was rejected. Please submit a new payment.',
                    'data' => [
                        'user' => $user,
                        'payment_status' => [
                            'status' => 'rejected',
                            'message' => 'Your previous payment was rejected. Please make a new payment with a valid M-PESA transaction code.',
                            'fee_amount' => 300.00,
                            'rejection_reason' => $registrationFee->notes,
                        ]
                    ]
                ], 402); // 402 Payment Required
            }
        }

        // Registration fee is paid, allow login
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Logout user (Revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    /**
     * Mark user profile as completed
     */
    public function completeProfile(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $user = $request->user();
        $user->update([
            'profile_completed' => true,
            'customer_id' => $request->customer_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile completed successfully',
            'data' => $user->fresh()
        ]);
    }
}
