<?php

namespace App\Http\Controllers;

use App\Models\JenisAnggaran;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Workflow;
use App\Models\WorkflowApproval;
use App\Models\WorkflowDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        // Get current user
        $user = Auth::user();

        // Get workflows where the user is involved (as creator or approver)
        $workflows = Workflow::with(['approvals', 'jenisAnggaran'])
            ->whereHas('approvals', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orWhere('created_by', $user->id)
            ->latest()
            ->get();

        foreach ($workflows as $workflow) {
            foreach ($workflow->approvals as $approval) {
                $userDetail = User::find($approval->user_id);
                $approval->user_detail = $userDetail ? $userDetail->name : '-';
            }
        }

        return view('workflows.index', compact('workflows', 'user'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        // Get all jenis anggaran
        $jenisAnggaran = JenisAnggaran::whereNull('deleted_at')
            ->where('is_show', 1)
            ->get();

        // Get all available users for approval selection
        $users = User::where('status', 'active')
            ->where('id', '!=', $user->id)
            ->get();

        return view('workflows.create', compact('jenisAnggaran', 'user', 'users'));
    }

    public function fetchJabatan(Request $request)
    {
        $user_id = $request->user_id;

        if (!$user_id) {
            return response()->json(['success' => false, 'message' => 'User ID is required']);
        }

        $user = User::find($user_id);

        if ($user && $user->jabatan) {
            return response()->json(['success' => true, 'nama_posisi' => $user->jabatan]);
        }

        return response()->json(['success' => false, 'message' => 'Position not found']);
    }

    public function findUsers(Request $request)
    {
        $search = $request->input('search');

        if (!$search) {
            return response()->json(['error' => 'Search parameter is required'], 400);
        }

        // Search users from our own table
        $users = User::where('status', 'active')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->get(['id', 'name']);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        // Get valid status codes from your model
        $validStatusCodes = collect(Workflow::getStatuses())->pluck('code')->toArray();

        try {
            $validated = $request->validate([
                'nomor_pengajuan'    => 'required|string|unique:workflows,nomor_pengajuan',
                'unit_kerja'         => 'required|string',
                'cost_center'        => 'required|string',
                'nama_kegiatan'      => 'required|string',
                'deskripsi_kegiatan' => 'nullable|string', // New field
                'jenis_anggaran'     => 'required|string',
                'total_nilai'        => 'required|numeric|min:0',
                'waktu_penggunaan'   => 'required|date',
                'account'            => 'required|string',
                'pics'               => 'required|array',
                'pics.*.user_id'     => 'required',
                'pics.*.notes'       => 'nullable|string',
                'pics.*.digital_signature' => 'nullable|string',
                'pics.*.role'        => ['required', Rule::in($validStatusCodes)],
                'documents'          => 'nullable|array',
                'documents.*'        => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
                'is_draft'           => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Flash uploaded files to session to maintain them between requests
            if ($request->hasFile('documents')) {
                $request->flash();  // This will flash all input including files
            }

            return back()
                ->withErrors($e->validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $workflow = new Workflow();
            $workflow->fill([
                'nomor_pengajuan' => $validated['nomor_pengajuan'],
                'unit_kerja' => $validated['unit_kerja'],
                'cost_center' => $validated['cost_center'],
                'nama_kegiatan' => $validated['nama_kegiatan'],
                'deskripsi_kegiatan' => $validated['deskripsi_kegiatan'] ?? null, // New field
                'jenis_anggaran' => $validated['jenis_anggaran'],
                'total_nilai' => $validated['total_nilai'],
                'waktu_penggunaan' => $validated['waktu_penggunaan'],
                'account' => $validated['account'],
                'created_by' => Auth::id(),
                'status' => $request->input('is_draft', false) ? 'DRAFT_CREATOR' : 'WAITING_APPROVAL',
            ]);

            $workflow->save();

            // Process approvals/PICs
            if ($request->has('pics')) {
                // Sort PICs by sequence
                $pics = collect($request->pics)->sortBy(function ($pic) {
                    // Define sequence based on role
                    $roleSequence = [
                        'CREATOR' => 1,
                        'ACKNOWLEDGED_BY_SPV' => 2,
                        'APPROVED_BY_HEAD_UNIT' => 3,
                        'REVIEWED_BY_MAKER' => 4,
                        'REVIEWED_BY_APPROVER' => 5,
                    ];

                    return $roleSequence[$pic['role']] ?? 999;
                })->values()->all();

                foreach ($pics as $index => $pic) {
                    $isCurrentUser = ($pic['user_id'] == Auth::id());

                    // Create the WorkflowApproval record
                    $approval = WorkflowApproval::create([
                        'workflow_id' => $workflow->id,
                        'user_id' => $pic['user_id'],
                        'role' => $pic['role'],
                        'digital_signature' => $pic['digital_signature'] ?? 0,
                        'notes' => $pic['notes'] ?? null,
                        'sequence' => $index + 1,
                        'is_active' => ($index === 0 || $isCurrentUser) ? 1 : 0,
                        'status' => $isCurrentUser && !$request->input('is_draft', false) ? 'PENDING' : 'DRAFT',
                        'approved_at' => $isCurrentUser && !$request->input('is_draft', false) ? now() : null,
                    ]);
                }
            }

            // Handle multiple document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    // Generate unique filename
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $uniqueName = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

                    // Create directory path with workflow ID
                    $directory = public_path("documents/{$workflow->id}");

                    // Ensure the directory exists
                    if (!file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    // Move file to the directory
                    $file->move($directory, $uniqueName);

                    // Store the relative path
                    $relativePath = "documents/{$workflow->id}/{$uniqueName}";

                    // Create document record
                    WorkflowDocument::create([
                        'workflow_id' => $workflow->id,
                        'file_path' => $relativePath,
                        'file_name' => $originalName,
                        'file_type' => $extension,
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            $message = $request->input('is_draft', false)
                ? 'Workflow saved as draft successfully.'
                : 'Workflow created and submitted successfully.';

            return redirect()->route('workflows.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            \Log::error('Workflow creation error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return back()
                ->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Request $request, Workflow $workflow)
    {
        // Check if the user is authorized to view this workflow
        $this->authorizeWorkflow($workflow);

        // Get workflow approvals in sequence order
        $workflowApprovals = WorkflowApproval::where('workflow_id', $workflow->id)
            ->orderBy('sequence', 'asc')
            ->get();

        // Get workflow documents
        $workflowDocuments = WorkflowDocument::where('workflow_id', $workflow->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get jenis anggaran
        $jenisAnggaran = JenisAnggaran::whereNull('deleted_at')
            ->where('is_show', 1)
            ->get();

        // Check if current user can approve this workflow
        $canApprove = $this->canApproveWorkflow($workflow);

        return view('workflows.show', compact(
            'workflow',
            'jenisAnggaran',
            'workflowApprovals',
            'workflowDocuments',
            'canApprove'
        ));
    }

    public function edit(Workflow $workflow)
    {
        // Check if user can edit this workflow
        if ($workflow->status !== 'DRAFT_CREATOR' || $workflow->created_by !== Auth::id()) {
            return redirect()->route('workflows.index')
                ->with('error', 'You can only edit workflows in draft status that you created.');
        }

        // Get jenis anggaran
        $jenisAnggaran = JenisAnggaran::whereNull('deleted_at')
            ->where('is_show', 1)
            ->get();

        // Get all users
        $users = User::where('status', 'active')->get();

        // Get workflow approvals
        $workflowApprovals = WorkflowApproval::where('workflow_id', $workflow->id)
            ->orderBy('sequence', 'asc')
            ->get();

        // Get workflow documents
        $workflowDocuments = WorkflowDocument::where('workflow_id', $workflow->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('workflows.edit', compact(
            'workflow',
            'jenisAnggaran',
            'users',
            'workflowApprovals',
            'workflowDocuments'
        ));
    }

    public function update(Request $request, Workflow $workflow)
    {
        // Check if user can update this workflow
        if ($workflow->status !== 'DRAFT_CREATOR' || $workflow->created_by !== Auth::id()) {
            return redirect()->route('workflows.index')
                ->with('error', 'You can only update workflows in draft status that you created.');
        }

        $validStatusCodes = collect(Workflow::getStatuses())->pluck('code')->toArray();

        $validated = $request->validate([
            'nomor_pengajuan'    => 'required|string|unique:workflows,nomor_pengajuan,' . $workflow->id,
            'unit_kerja'         => 'required|string',
            'cost_center'        => 'required|string',
            'nama_kegiatan'      => 'required|string',
            'jenis_anggaran'     => 'required|string',
            'total_nilai'        => 'required|numeric|min:0',
            'waktu_penggunaan'   => 'required|date',
            'account'            => 'required|string',
            'pics'               => 'required|array',
            'pics.*.user_id'     => 'required',
            'pics.*.notes'       => 'nullable|string',
            'pics.*.digital_signature' => 'nullable|string',
            'pics.*.role'        => ['required', Rule::in($validStatusCodes)],
            'new_documents'      => 'nullable|array',
            'new_documents.*'    => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
            'is_draft'           => 'nullable|boolean',
            'remove_documents'   => 'nullable|array',
            'remove_documents.*' => 'nullable|integer|exists:workflow_documents,id',
        ]);

        DB::beginTransaction();
        try {
            // Update workflow
            $workflow->update([
                'nomor_pengajuan' => $validated['nomor_pengajuan'],
                'unit_kerja' => $validated['unit_kerja'],
                'cost_center' => $validated['cost_center'],
                'nama_kegiatan' => $validated['nama_kegiatan'],
                'jenis_anggaran' => $validated['jenis_anggaran'],
                'total_nilai' => $validated['total_nilai'],
                'waktu_penggunaan' => $validated['waktu_penggunaan'],
                'account' => $validated['account'],
                'status' => $request->input('is_draft', false) ? 'DRAFT_CREATOR' : 'WAITING_APPROVAL',
            ]);

            // Delete existing approvals and recreate them
            WorkflowApproval::where('workflow_id', $workflow->id)->delete();

            // Process approvals/PICs
            if ($request->has('pics')) {
                // Sort PICs by sequence
                $pics = collect($request->pics)->sortBy(function ($pic) {
                    // Define sequence based on role
                    $roleSequence = [
                        'CREATOR' => 1,
                        'ACKNOWLEDGED_BY_SPV' => 2,
                        'APPROVED_BY_HEAD_UNIT' => 3,
                        'REVIEWED_BY_MAKER' => 4,
                        'REVIEWED_BY_APPROVER' => 5,
                    ];

                    return $roleSequence[$pic['role']] ?? 999;
                })->values()->all();

                foreach ($pics as $index => $pic) {
                    $isCurrentUser = ($pic['user_id'] == Auth::id());

                    // Create the WorkflowApproval record
                    $approval = WorkflowApproval::create([
                        'workflow_id' => $workflow->id,
                        'user_id' => $pic['user_id'],
                        'role' => $pic['role'],
                        'digital_signature' => $pic['digital_signature'] ?? 0,
                        'notes' => $pic['notes'] ?? null,
                        'sequence' => $index + 1,
                        'is_active' => ($index === 0 || $isCurrentUser) ? 1 : 0,
                        'status' => $isCurrentUser && !$request->input('is_draft', false) ? 'APPROVED' : 'PENDING',
                        'approved_at' => $isCurrentUser && !$request->input('is_draft', false) ? now() : null,
                    ]);
                }
            }

            // Handle document removal
            if ($request->has('remove_documents')) {
                foreach ($request->input('remove_documents') as $documentId) {
                    $document = WorkflowDocument::find($documentId);
                    if ($document) {
                        // Delete the file
                        $filePath = public_path($document->file_path);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        // Delete the record
                        $document->delete();
                    }
                }
            }

            // Handle new document uploads
            if ($request->hasFile('new_documents')) {
                foreach ($request->file('new_documents') as $file) {
                    // Generate unique filename
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $uniqueName = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

                    // Create directory path with workflow ID
                    $directory = public_path("documents/{$workflow->id}");

                    // Ensure the directory exists
                    if (!file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    // Move file to the directory
                    $file->move($directory, $uniqueName);

                    // Store the relative path
                    $relativePath = "documents/{$workflow->id}/{$uniqueName}";

                    // Create document record
                    WorkflowDocument::create([
                        'workflow_id' => $workflow->id,
                        'file_path' => $relativePath,
                        'file_name' => $originalName,
                        'file_type' => $extension,
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            $message = $request->input('is_draft', false)
                ? 'Workflow saved as draft successfully.'
                : 'Workflow updated and submitted successfully.';

            return redirect()->route('workflows.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function approve(Request $request, Workflow $workflow)
    {
        // Validate request
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'digital_signature' => 'nullable|boolean',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
        ]);

        // Check if user can approve this workflow
        if (!$this->canApproveWorkflow($workflow)) {
            return redirect()->route('workflows.index')
                ->with('error', 'You are not authorized to approve this workflow at this stage.');
        }

        DB::beginTransaction();
        try {
            // Get current user's approval record
            $approval = WorkflowApproval::where('workflow_id', $workflow->id)
                ->where('user_id', Auth::id())
                ->where('is_active', 1)
                ->first();

            if (!$approval) {
                throw new \Exception('No active approval record found for this user.');
            }

            // Update approval status
            $approval->update([
                'status' => 'APPROVED',
                'notes' => $validated['notes'] ?? $approval->notes,
                'digital_signature' => $validated['digital_signature'] ?? $approval->digital_signature,
                'approved_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            // Handle document uploads if any
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    // Generate unique filename
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $uniqueName = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

                    // Create directory path with workflow ID
                    $directory = public_path("documents/{$workflow->id}");

                    // Ensure the directory exists
                    if (!file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    // Move file to the directory
                    $file->move($directory, $uniqueName);

                    // Store the relative path
                    $relativePath = "documents/{$workflow->id}/{$uniqueName}";

                    // Create document record
                    WorkflowDocument::create([
                        'workflow_id' => $workflow->id,
                        'file_path' => $relativePath,
                        'file_name' => $originalName,
                        'file_type' => $extension,
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            // Activate next approval if this is not the last one
            $nextApproval = WorkflowApproval::where('workflow_id', $workflow->id)
                ->where('sequence', '>', $approval->sequence)
                ->orderBy('sequence', 'asc')
                ->first();

            if ($nextApproval) {
                $nextApproval->update(['is_active' => 1]);
            } else {
                // This was the last approval, so update workflow status to COMPLETED
                $workflow->update(['status' => 'COMPLETED']);
            }

            DB::commit();
            return redirect()->route('workflows.index')
                ->with('success', 'Workflow approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to approve workflow: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, Workflow $workflow)
    {
        // Validate request
        $validated = $request->validate([
            'notes' => 'required|string',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
        ]);

        // Check if user can reject this workflow
        if (!$this->canApproveWorkflow($workflow)) {
            return redirect()->route('workflows.index')
                ->with('error', 'You are not authorized to reject this workflow at this stage.');
        }

        DB::beginTransaction();
        try {
            // Get current user's approval record
            $approval = WorkflowApproval::where('workflow_id', $workflow->id)
                ->where('user_id', Auth::id())
                ->where('is_active', 1)
                ->first();

            if (!$approval) {
                throw new \Exception('No active approval record found for this user.');
            }

            // Update approval status
            $approval->update([
                'status' => 'REJECTED',
                'notes' => $validated['notes'],
                'rejected_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            // Handle document uploads if any
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    // Generate unique filename
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $uniqueName = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

                    // Create directory path with workflow ID
                    $directory = public_path("documents/{$workflow->id}");

                    // Ensure the directory exists
                    if (!file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    // Move file to the directory
                    $file->move($directory, $uniqueName);

                    // Store the relative path
                    $relativePath = "documents/{$workflow->id}/{$uniqueName}";

                    // Create document record
                    WorkflowDocument::create([
                        'workflow_id' => $workflow->id,
                        'file_path' => $relativePath,
                        'file_name' => $originalName,
                        'file_type' => $extension,
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            // Update workflow status to REVISED (sent back to creator)
            $workflow->update(['status' => 'DRAFT_CREATOR']);

            // Reset all approvals to inactive except the creator
            WorkflowApproval::where('workflow_id', $workflow->id)
                ->where('role', '!=', 'CREATOR')
                ->update([
                    'is_active' => 0,
                    'status' => 'PENDING',
                    'approved_at' => null,
                    'rejected_at' => null
                ]);

            // Set creator approval to active
            $creatorApproval = WorkflowApproval::where('workflow_id', $workflow->id)
                ->where('role', 'CREATOR')
                ->first();

            if ($creatorApproval) {
                $creatorApproval->update(['is_active' => 1]);
            }

            DB::commit();
            return redirect()->route('workflows.index')
                ->with('success', 'Workflow rejected and sent back to creator.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to reject workflow: ' . $e->getMessage()]);
        }
    }

    public function draft(Request $request, Workflow $workflow)
    {
        // Check if this is a new workflow or an existing one
        if ($workflow->exists) {
            // For existing workflow, just save it as draft
            $workflow->update(['status' => 'DRAFT_CREATOR']);
            return redirect()->route('workflows.index')
                ->with('success', 'Workflow saved as draft.');
        } else {
            // For new workflow, add is_draft parameter and call store
            $request->merge(['is_draft' => true]);
            return $this->store($request);
        }
    }

    public function destroy(Workflow $workflow)
    {
        // Check if user can delete this workflow
        if ($workflow->created_by !== Auth::id() || $workflow->status !== 'DRAFT_CREATOR') {
            return redirect()->route('workflows.index')
                ->with('error', 'You can only delete your own draft workflows.');
        }

        DB::beginTransaction();
        try {
            // Delete all related documents first
            $documents = WorkflowDocument::where('workflow_id', $workflow->id)->get();

            foreach ($documents as $document) {
                // Delete the file
                $filePath = public_path($document->file_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                // Delete the record
                $document->delete();
            }

            // Delete related approvals
            WorkflowApproval::where('workflow_id', $workflow->id)->delete();

            // Delete the workflow
            $workflow->delete();

            DB::commit();
            return redirect()->route('workflows.index')
                ->with('success', 'Workflow deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('workflows.index')
                ->with('error', 'Failed to delete workflow: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to check if current user can approve a workflow
     */
    private function canApproveWorkflow(Workflow $workflow)
    {
        // Check if workflow is in waiting approval status
        if ($workflow->status !== 'WAITING_APPROVAL') {
            return false;
        }

        // Check if current user has an active approval record
        $hasActiveApproval = WorkflowApproval::where('workflow_id', $workflow->id)
            ->where('user_id', Auth::id())
            ->where('is_active', 1)
            ->where('status', 'PENDING')
            ->exists();

        return $hasActiveApproval;
    }

    /**
     * Helper method to authorize a user to view a workflow
     */
    private function authorizeWorkflow(Workflow $workflow)
    {
        $user = Auth::user();

        // Check if user created this workflow
        if ($workflow->created_by === $user->id) {
            return true;
        }

        // Check if user is an approver for this workflow
        $isApprover = WorkflowApproval::where('workflow_id', $workflow->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isApprover) {
            abort(403, 'You are not authorized to view this workflow.');
        }

        return true;
    }
}
