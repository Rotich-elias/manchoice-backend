<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartRequest;
use App\Models\PartRequestStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PartRequest::with(['customer', 'user']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by urgency
        if ($request->has('urgency') && $request->urgency) {
            $query->where('urgency', $request->urgency);
        }

        // Search by part name or customer
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('motorcycle_model', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Sort by
        if ($request->has('sort') && $request->sort) {
            switch ($request->sort) {
                case 'oldest':
                    $query->oldest();
                    break;
                case 'urgency':
                    $query->orderByRaw("FIELD(urgency, 'high', 'medium', 'low')");
                    break;
                case 'newest':
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        $partRequests = $query->paginate(20);

        return view('admin.part-requests', compact('partRequests'));
    }

    public function show($id)
    {
        $partRequest = PartRequest::with(['customer', 'user', 'statusHistories.user'])->findOrFail($id);
        return view('admin.part-request-detail', compact('partRequest'));
    }

    public function updateStatus(Request $request, $id)
    {
        $partRequest = PartRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,available,fulfilled,cancelled',
            'admin_notes' => 'nullable|string',
        ]);

        // Check if status changed
        $statusChanged = $partRequest->status !== $validated['status'];

        // Update the part request
        $partRequest->update($validated);

        // Create status history entry if status changed
        if ($statusChanged) {
            PartRequestStatusHistory::create([
                'part_request_id' => $partRequest->id,
                'status' => $validated['status'],
                'notes' => $validated['admin_notes'] ?? null,
                'user_id' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Part request status updated successfully');
    }
}
