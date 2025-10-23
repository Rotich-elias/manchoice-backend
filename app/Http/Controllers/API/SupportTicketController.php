<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    /**
     * Submit a new support ticket
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:bug,feature_request,help,complaint,feedback,other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Get customer if user is logged in
        $customer = null;
        if ($user) {
            $customer = Customer::where('user_id', $user->id)->first();
        }

        $ticket = SupportTicket::create([
            'user_id' => $user?->id,
            'customer_id' => $customer?->id,
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
            'contact_email' => $validated['contact_email'] ?? $user?->email ?? $customer?->email,
            'contact_phone' => $validated['contact_phone'] ?? $user?->phone ?? $customer?->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket submitted successfully. Ticket number: ' . $ticket->ticket_number,
            'data' => [
                'ticket_number' => $ticket->ticket_number,
                'id' => $ticket->id,
                'status' => $ticket->status,
                'created_at' => $ticket->created_at->toDateTimeString(),
            ]
        ], 201);
    }

    /**
     * Get user's support tickets
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $customer = Customer::where('user_id', $user->id)->first();

        $query = SupportTicket::query();

        // Get tickets for this user or their customer profile
        $query->where(function ($q) use ($user, $customer) {
            $q->where('user_id', $user->id);
            if ($customer) {
                $q->orWhere('customer_id', $customer->id);
            }
        });

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->get()->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'type' => $ticket->type,
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'admin_response' => $ticket->admin_response,
                'created_at' => $ticket->created_at->toDateTimeString(),
                'resolved_at' => $ticket->resolved_at?->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tickets,
            'count' => $tickets->count(),
        ]);
    }

    /**
     * Get single ticket details
     */
    public function show($id): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $customer = Customer::where('user_id', $user->id)->first();

        $ticket = SupportTicket::where('id', $id)
            ->where(function ($q) use ($user, $customer) {
                $q->where('user_id', $user->id);
                if ($customer) {
                    $q->orWhere('customer_id', $customer->id);
                }
            })
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'type' => $ticket->type,
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'admin_response' => $ticket->admin_response,
                'contact_email' => $ticket->contact_email,
                'contact_phone' => $ticket->contact_phone,
                'created_at' => $ticket->created_at->toDateTimeString(),
                'resolved_at' => $ticket->resolved_at?->toDateTimeString(),
            ]
        ]);
    }
}
