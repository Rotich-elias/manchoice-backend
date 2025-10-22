<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartRequest;
use Illuminate\Http\Request;

class PartRequestController extends Controller
{
    public function index()
    {
        $partRequests = PartRequest::with(['customer', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.part-requests', compact('partRequests'));
    }

    public function show($id)
    {
        $partRequest = PartRequest::with(['customer', 'user'])->findOrFail($id);
        return view('admin.part-request-detail', compact('partRequest'));
    }

    public function updateStatus(Request $request, $id)
    {
        $partRequest = PartRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,available,fulfilled,cancelled',
            'admin_notes' => 'nullable|string',
        ]);

        $partRequest->update($validated);

        return back()->with('success', 'Part request status updated successfully');
    }
}
