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
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^0[0-9]{9}$/|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'pin_confirmation' => 'required|same:pin',
            'password' => 'nullable|string|min:8',
            'accepted_terms' => 'required|boolean|accepted',
        ]);

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
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
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

        // Check registration fee status
        if (!$user->registration_fee_paid) {
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
