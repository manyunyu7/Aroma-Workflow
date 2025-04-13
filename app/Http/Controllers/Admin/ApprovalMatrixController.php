<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalMatrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalMatrixController extends Controller
{
    /**
     * Display a listing of the matrices.
     */
    public function index()
    {
        $matrices = ApprovalMatrix::with(['creator', 'editor'])->orderBy('min_budget')->get();
        return view('admin.approval-matrix.index', compact('matrices'));
    }

    /**
     * Show the form for creating a new matrix.
     */
    public function create()
    {
        return view('admin.approval-matrix.create');
    }

    /**
     * Store a newly created matrix in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_budget' => 'required|numeric|min:0',
            'max_budget' => 'nullable|numeric|gt:min_budget',
            // 'approvers' => 'required|array',
            'status' => 'required|in:Active,Not Active',
        ]);

        // Set max_budget to null if it's empty (for unlimited)
        $maxBudget = $request->filled('max_budget') ? $request->max_budget : null;

        ApprovalMatrix::create([
            'name' => $request->name,
            'min_budget' => $request->min_budget,
            'max_budget' => $maxBudget,
            // 'approvers' => $request->approvers,
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => Auth::id(), // Using ID instead of name
        ]);

        return redirect()->route('admin.approval-matrix.index')
            ->with('success', 'Approval matrix created successfully.');
    }

    /**
     * Show the form for editing the specified matrix.
     */
    public function edit(ApprovalMatrix $approvalMatrix)
    {
        $approvalMatrix->load(['creator', 'editor']);
        return view('admin.approval-matrix.edit', compact('approvalMatrix'));
    }

    /**
     * Update the specified matrix in storage.
     */
    public function update(Request $request, ApprovalMatrix $approvalMatrix)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_budget' => 'required|numeric|min:0',
            'max_budget' => 'nullable|numeric|gt:min_budget',
            // 'approvers' => 'required|array',
            'status' => 'required|in:Active,Not Active',
        ]);

        // Set max_budget to null if it's empty (for unlimited)
        $maxBudget = $request->filled('max_budget') ? $request->max_budget : null;

        $approvalMatrix->update([
            'name' => $request->name,
            'min_budget' => $request->min_budget,
            'max_budget' => $maxBudget,
            // 'approvers' => $request->approvers,
            'description' => $request->description,
            'status' => $request->status,
            'edited_by' => Auth::id(), // Using ID instead of name
        ]);

        return redirect()->route('admin.approval-matrix.index')
            ->with('success', 'Approval matrix updated successfully.');
    }

    /**
     * Remove the specified matrix from storage.
     */
    public function destroy(ApprovalMatrix $approvalMatrix)
    {
        $approvalMatrix->delete();

        return redirect()->route('admin.approval-matrix.index')
            ->with('success', 'Approval matrix deleted successfully.');
    }
}
