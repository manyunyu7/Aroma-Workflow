<?php

namespace App\Http\Controllers;

use App\Helpers\WorkflowLogger;
use App\Models\ApprovalMatrix;
use App\Models\JenisAnggaran;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Workflow;
use App\Models\WorkflowApproval;
use App\Models\WorkflowDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{


    public function getApprovalMatrix(Request $request)
    {
        $budget = $request->budget;

        // Find the appropriate approval matrix based on budget amount
        $matrix = ApprovalMatrix::where('status', 'Active')
            ->where('min_budget', '<=', $budget)
            ->where(function ($query) use ($budget) {
                $query->where('max_budget', '>=', $budget)
                    ->orWhereNull('max_budget');
            })
            ->first();

        if ($matrix) {
            return response()->json([
                'success' => true,
                'matrix' => $matrix
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No approval matrix found for this budget amount.'
        ]);
    }

    /**
     * Get accounts for a specific cost center
     */
    public function getCostCenterAccountList(Request $request)
    {
        $unitCcId = $request->input('unit_cc_id');

        if (!$unitCcId) {
            return response()->json(['error' => 'Unit cost center ID is required'], 400);
        }

        // Use the service to get accounts
        $accounts = getCostCenterAccounts($unitCcId);

        return response()->json($accounts);
    }

    /**
     * Get all unique unit kerja with employee count
     */
    public function getUnitKerja(Request $request)
    {
        $search = $request->input('search');

        if (!$search) {
            return response()->json(['error' => 'Search parameter is required'], 400);
        }

        // Get unit kerja with employee count, filtered by search term
        $unitKerja = DB::table('users')
            ->select('unit_kerja', DB::raw('COUNT(*) as employee_count'))
            ->where('status', 'active')
            ->where('unit_kerja', 'like', "%{$search}%")
            ->groupBy('unit_kerja')
            ->havingRaw('COUNT(*) > 0')
            ->get();

        return response()->json($unitKerja);
    }

    /**
     * Get employees by unit kerja and role with budget filtering
     */
    public function getEmployees(Request $request)
    {
        $unitKerja = $request->input('unit_kerja');
        $role = $request->input('role');
        $budget = $request->input('budget', 0);
        $userMin = $request->input('user_min'); // IMPORTANT: Added userMin parameter
        $previousRole = $request->input('previous_role');

        if (!$unitKerja) {
            return response()->json(['error' => 'Unit kerja parameter is required'], 400);
        }

        // Start query to get employees
        $query = User::where('status', 'active')
            ->where('unit_kerja', $unitKerja);

        // Add role filter if provided
        if ($role) {
            $query->whereHas('userRoles', function ($q) use ($role, $budget) {
                // Filter by role
                $q->where('role', $role);

                // Budget range filtering
                $q->where(function ($budgetQuery) use ($budget) {
                    $budgetQuery
                        // Scenario 1: Both min and max are set
                        ->where(function ($innerQuery) use ($budget) {
                            $innerQuery->where('min_budget', '<=', $budget)
                                ->where('max_budget', '>=', $budget)
                                ->whereNotNull('min_budget')
                                ->whereNotNull('max_budget');
                        })
                        // Scenario 2: Only min is set (and less than or equal to budget)
                        ->orWhere(function ($innerQuery) use ($budget) {
                            $innerQuery->where('min_budget', '<=', $budget)
                                ->whereNull('max_budget');
                        })
                        // Scenario 3: Only max is set (and greater than or equal to budget)
                        ->orWhere(function ($innerQuery) use ($budget) {
                            $innerQuery->where('max_budget', '>=', $budget)
                                ->whereNull('min_budget');
                        })
                        // Scenario 4: No budget constraints set (NULL on both)
                        ->orWhereNull('min_budget')
                        ->orWhereNull('max_budget');
                });
            });
        }

        // Special handling for reviewers based on previous role
        if ($previousRole && ($previousRole == 'Acknowledger' || $previousRole == 'Unit Head - Approver')) {
            // Only show Reviewer-Maker role users
            if ($role == 'Reviewer-Maker') {
                $query->whereHas('userRoles', function ($q) {
                    $q->where('role', 'Reviewer-Maker');
                });
            }
        }

        // Select employee details and add budget information
        $employees = $query->select('id', 'name', 'nik', 'unit_kerja')
            ->with(['userRoles' => function ($q) {
                $q->select('user_id', 'min_budget', 'max_budget');
            }])
            ->get();

        // Add budget information to each employee
        $employees->transform(function ($employee) {
            // Retrieve the user's budget info (assuming the first role is the relevant one)
            $userRole = $employee->userRoles->first();
            if ($userRole) {
                $employee->min_budget = $userRole->min_budget ?? 0; // NULL treated as 0
                $employee->max_budget = $userRole->max_budget ?? null; // NULL stays as null (infinity)
            } else {
                $employee->min_budget = 0; // Default to 0 if no userRoles
                $employee->max_budget = null; // Default to null if no userRoles
            }
            return $employee;
        });

        return response()->json($employees);
    }

    /**
     * Get available roles based on the current workflow state
     */
    public function getAvailableRoles(Request $request)
    {
        // Logging initialization
        Log::info('Fetching available roles...');
        $currentRoles = $request->input('current_roles', []);

        $budget = $request->input('budget', 0);
        $lastRole = end($currentRoles) ?: null;

        // Default available roles
        $availableRoles = [];

        // If no roles selected yet, can only choose between acknowledger and unit head
        if (empty($currentRoles)) {
            $availableRoles = ['Acknowledger', 'Unit Head - Approver'];
            Log::info('No roles selected yet. Available roles: ' . implode(', ', $availableRoles));
        }

        // If roles is Creator or can add Unit Head - Approver
        else if ($lastRole == 'Creator') {
            $availableRoles = ['Acknowledger', 'Unit Head - Approver'];
            Log::info('No roles selected yet. Available roles: ' . implode(', ', $availableRoles));
        }

        // If roles isAcknowledger, can add Unit Head - Approver
        else if ($lastRole == 'Acknowledger') {
            $availableRoles = ['Unit Head - Approver'];
            Log::info('No roles selected yet. Available roles: ' . implode(', ', $availableRoles));
        }

        // If last role is acknowledger or unit head, next must be reviewer-maker
        elseif ($lastRole == 'Unit Head - Approver') {
            $availableRoles = ['Reviewer-Maker'];
            Log::info('Last role was "' . $lastRole . '". Available roles: ' . implode(', ', $availableRoles));
        }
        // If last role is reviewer-maker, next must be reviewer-approver
        elseif ($lastRole == 'Reviewer-Maker') {
            $availableRoles = ['Reviewer-Approver'];
            Log::info('Last role was "' . $lastRole . '". Available roles: ' . implode(', ', $availableRoles));
        }
        // If last role is reviewer-approver, can add another reviewer-maker
        elseif ($lastRole == 'Reviewer-Approver') {
            $availableRoles = ['Reviewer-Maker'];
            Log::info('Last role was "' . $lastRole . '". Available roles: ' . implode(', ', $availableRoles));
        }

        // Get roles that match the budget requirement
        if ($budget > 0) {
            // For simplicity, assuming we're not filtering the roles based on budget here,
            // just returning the available roles based on workflow logic
            Log::info('Budget is greater than zero. No budget filtering implemented in this version.');
        }

        Log::info('Returning available roles: ' . implode(', ', $availableRoles));

        return response()->json($availableRoles);
    }

    /**
     * Get roles for a specific user
     */
    /**
     * Get roles for a specific user
     */
    public function getUserRoles(Request $request)
    {
        $userId = $request->input('user_id');
        $budget = $request->input('budget', 0);

        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }

        // Get user roles
        $query = UserRole::where('user_id', $userId);

        // Filter by budget if provided
        if ($budget > 0) {
            $query->where('max_budget', '>=', $budget);
        }

        $roles = $query->get(['role', 'max_budget']);

        // If no roles found, return empty array
        if ($roles->isEmpty()) {
            return response()->json([]);
        }

        // Map roles to include role name if available
        $mappedRoles = $roles->map(function ($roleObj) {
            return [
                'role' => $roleObj->role,
                'role_name' => $roleObj->role,
                'max_budget' => $roleObj->max_budget
            ];
        });

        return response()->json($mappedRoles);
    }

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

        $userRoles = UserRole::where('user_id', $user->id)->get();

        $compact = compact('jenisAnggaran', 'user', 'users', 'userRoles');

        if ($request->dump == true) {
            return $compact;
        }

        return view('workflows.create.create', $compact);
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
        $role = $request->input('role');
        $budget = $request->input('budget', 0);
        $unit_kerja = $request->input('unit_kerja');

        if (!$search) {
            return response()->json(['error' => 'Search parameter is required'], 400);
        }

        // Start query for users
        $query = User::where('status', 'active')
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });

        // Filter by role if provided
        if ($role) {
            $query->whereHas('userRoles', function ($q) use ($role, $budget) {
                $q->where('role', $role);

                // Filter by budget if provided
                if ($budget > 0) {
                    $q->where('max_budget', '>=', $budget);
                }
            });
        }

        // Filter by unit_kerja if provided
        if ($unit_kerja) {
            $query->where('unit_kerja', $unit_kerja);
        }

        // For budget under 500,000,000, enforce unit_kerja constraint for Acknowledger and Unit Head
        if ($budget < 500000000 && ($role == 'Acknowledger' || $role == 'Unit Head - Approver') && $unit_kerja) {
            $query->where('unit_kerja', $unit_kerja);
        }

        $users = $query->get(['id', 'name', 'unit_kerja']);

        return response()->json($users);
    }


    /**
     * Generate a unique nomor pengajuan with format: YearMonth-UnitNumber-RunningNumber
     *
     * @param string $unitKerja The unit kerja code or name
     * @return string The generated nomor pengajuan
     */
    private function generateNomorPengajuan($unitKerja)
    {
        // Get current year and month
        $yearMonth = date('Ym');

        // Generate unit number (you can modify this logic as needed)
        // For example, using first 3 characters of unit kerja or a predefined mapping
        $unitNumber = substr(preg_replace('/[^a-zA-Z0-9]/', '', $unitKerja), 0, 3);
        $unitNumber = strtoupper($unitNumber);

        // Use a database transaction with locking to prevent race conditions
        return DB::transaction(function () use ($yearMonth, $unitNumber) {
            // Lock the workflows table to prevent concurrent access
            // Using FOR UPDATE causes the query to wait until any locks are released
            $lastWorkflow = DB::table('workflows')
                ->where('nomor_pengajuan', 'like', $yearMonth . '-' . $unitNumber . '-%')
                ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_pengajuan, "-", -1) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->first();

            $runningNumber = 1;

            if ($lastWorkflow) {
                // Extract the running number from the last nomor pengajuan
                $parts = explode('-', $lastWorkflow->nomor_pengajuan);
                $lastRunningNumber = intval(end($parts));
                $runningNumber = $lastRunningNumber + 1;
            }

            // Format the running number with leading zeros (e.g., 001, 023, 158)
            $formattedRunningNumber = str_pad($runningNumber, 3, '0', STR_PAD_LEFT);

            // Construct the nomor pengajuan
            $nomorPengajuan = $yearMonth . '-' . $unitNumber . '-' . $formattedRunningNumber;

            return $nomorPengajuan;
        }, 5); // Retry up to 5 times if a deadlock occurs
    }

    public function store(Request $request)
    {
        // Get valid status codes from your model
        $validStatusCodes = collect(Workflow::getStatuses())->pluck('code')->toArray();

        try {
            $validated = $request->validate([
                'unit_kerja'           => 'required|string',
                'cost_center'          => 'nullable|string', //tempcoa
                'unit_cc_id'          => 'nullable|string', //tempcoa
                'nama_kegiatan'        => 'required|string',
                'deskripsi_kegiatan'   => 'nullable|string',
                'jenis_anggaran'       => 'required|string',
                'creation_date'        => 'required|string',
                'total_nilai'          => 'required|numeric|min:0',
                'waktu_penggunaan'     => 'required|date',
                'account'              => 'nullable|string',
                'pics'                 => 'required|array',
                'pics.*.user_id'       => 'required',
                'pics.*.notes'         => 'nullable|string',
                'pics.*.digital_signature' => 'nullable|string',
                'pics.*.role'          => 'required|string',
                'documents'            => 'nullable|array',
                'documents.*'          => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
                // 'document_categories.*' => ['required', 'string', Rule::in(['MAIN', 'SUPPORTING'])],
                // 'document_types.*'     => ['required', 'string', Rule::in(['JUSTIFICATION_DOC', 'REVIEW_DOC', 'OTHER'])],
                'document_sequence.*'  => 'nullable|integer',
                'document_notes.*'     => 'nullable|string',
                'is_draft'             => 'nullable|boolean',
            ]);

            // Additional validation for budget rules
            $budget = $validated['total_nilai'];
            $picRoles = array_column($request->pics, 'role');

            // Validate workflow has proper sequence of roles
            // First must be creator, then either acknowledger or head, then reviewer-maker and reviewer-approver
            if (!in_array('Creator', $picRoles)) {
                return back()->withErrors(['workflow_sequence' => 'Workflow must include a Creator role.'])->withInput();
            }

            // Check that either acknowledger or head is included
            if (!in_array('Acknowledger', $picRoles) && !in_array('Unit Head - Approver', $picRoles)) {
                return back()->withErrors(['workflow_sequence' => 'Workflow must include either an Acknowledger or a Unit Head - Approver.'])->withInput();
            }

            // Check that reviewer-maker and reviewer-approver are paired correctly
            $reviewerMakerPositions = array_keys($picRoles, 'Reviewer-Maker');
            $reviewerApproverPositions = array_keys($picRoles, 'Reviewer-Approver');

            if (count($reviewerMakerPositions) != count($reviewerApproverPositions)) {
                return back()->withErrors(['workflow_sequence' => 'Each Reviewer-Maker must be paired with a Reviewer-Approver.'])->withInput();
            }

            // Check each reviewer-maker is immediately followed by a reviewer-approver
            foreach ($reviewerMakerPositions as $position) {
                if (!isset($picRoles[$position + 1]) || $picRoles[$position + 1] != 'Reviewer-Approver') {
                    return back()->withErrors(['workflow_sequence' => 'Each Reviewer-Maker must be immediately followed by a Reviewer-Approver.'])->withInput();
                }
            }
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
            // Generate unique nomor pengajuan
            $nomorPengajuan = $this->generateNomorPengajuan($validated['unit_kerja']);
            $workflow = new Workflow();
            $workflow->fill([
                'nomor_pengajuan' => $nomorPengajuan,
                'unit_kerja' => $validated['unit_kerja'],
                'cost_center' => $validated['cost_center'],
                'cost_center_account' => $validated['unit_cc_id'],
                'creation_date' => $validated['creation_date'],
                'nama_kegiatan' => $validated['nama_kegiatan'],
                'deskripsi_kegiatan' => $validated['deskripsi_kegiatan'] ?? null,
                'jenis_anggaran' => $validated['jenis_anggaran'],
                'total_nilai' => $validated['total_nilai'],
                'waktu_penggunaan' => $validated['waktu_penggunaan'],
                'account' => $validated['account'],
                'created_by' => Auth::id(),
                'status' => $request->input('is_draft', false) ? 'DRAFT_CREATOR' : 'WAITING_APPROVAL',
            ]);


            // ADD THIS:
            // Log the workflow creation
            $isDraft = $request->input('is_draft', false);
            WorkflowLogger::logCreate($workflow, $isDraft);

            $workflow->save();

            // Process approvals/PICs
            if ($request->has('pics')) {
                // Use PICs in the exact order they came in the request
                $pics = $request->pics;

                // Find the creator index for reference
                $creatorIndex = null;
                foreach ($pics as $index => $pic) {
                    if ($pic['role'] === 'Creator') {
                        $creatorIndex = $index;
                        break;
                    }
                }

                // Determine next active person
                $nextActiveIndex = null;
                if (!$request->input('is_draft', false) && $creatorIndex !== null) {
                    // Find the next person after creator in sequence
                    // We're keeping the original request order
                    $nextActiveIndex = $creatorIndex + 1;
                    if ($nextActiveIndex >= count($pics)) {
                        $nextActiveIndex = null; // No next person if creator is last
                    }
                }

                foreach ($pics as $index => $pic) {
                    $isCurrentUser = ($pic['user_id'] == Auth::id());
                    $role = $pic['role'];
                    $isCreator = ($role === 'Creator');

                    // Map role names to role codes for DB storage
                    $roleCodeMap = [
                        'Creator' => 'CREATOR',
                        'Acknowledger' => 'ACKNOWLEDGED_BY_SPV',
                        'Unit Head - Approver' => 'APPROVED_BY_HEAD_UNIT',
                        'Reviewer-Maker' => 'REVIEWED_BY_MAKER',
                        'Reviewer-Approver' => 'REVIEWED_BY_APPROVER'
                    ];

                    $roleCode = $roleCodeMap[$role] ?? $role;

                    // Determine approval status
                    $isApproved = false;
                    if (!$request->input('is_draft', false)) {
                        if ($isCreator) {
                            $isApproved = true; // Creator is auto-approved on submission
                        } else if ($isCurrentUser) {
                            $isApproved = true; // Current user is auto-approved
                        }
                    }

                    // Determine active status
                    $isActive = false;
                    if ($request->input('is_draft', false)) {
                        // In draft mode, only creator is active
                        $isActive = $isCreator;
                    } else {
                        // In submit mode, next person after creator is active
                        $isActive = ($index === $nextActiveIndex);
                    }

                    // Create the WorkflowApproval record
                    $approval = WorkflowApproval::create([
                        'workflow_id' => $workflow->id,
                        'user_id' => $pic['user_id'],
                        'role' => $roleCode,
                        'digital_signature' => $pic['digital_signature'] ?? 0,
                        'notes' => $pic['notes'] ?? null,
                        'sequence' => $index + 1, // Keep original sequence
                        'is_active' => $isActive ? 1 : 0,
                        'status' => $isApproved ? 'APPROVED' : 'PENDING',
                        'approved_at' => $isApproved ? now() : null,
                    ]);
                }
            }

            // Handle document uploads
            if ($request->hasFile('documents')) {
                $files = $request->file('documents');

                // Create directory for this workflow's documents
                $directory = public_path("documents/{$workflow->id}");
                if (!file_exists($directory)) {
                    mkdir($directory, 0777, true);
                }

                foreach ($files as $index => $file) {
                    if ($file && $file->isValid()) {
                        // Get document metadata for this file
                        $documentCategory = $request->input("document_categories.$index") ?? 'SUPPORTING';
                        $documentType = $request->input("document_types.$index") ?? 'OTHER';
                        $sequence = $request->input("document_sequence.$index") ?? $index;
                        $notes = $request->input("document_notes.$index") ?? null;

                        // Generate unique filename
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $uniqueName = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

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
                            'document_category' => $documentCategory,
                            'document_type' => $documentType,
                            'sequence' => $sequence,
                            'notes' => $notes,
                            'uploaded_by' => Auth::id(),
                        ]);
                    }
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
            Log::error('Workflow creation error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()
                ->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Request $request, Workflow $workflow)
    {
        // ADD THIS:
        // Log the workflow view action
        WorkflowLogger::logView($workflow);

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

    /**
     * Show the form for editing the specified workflow.
     *
     * @param  \App\Models\Workflow  $workflow
     * @return \Illuminate\Http\Response
     */
    public function edit(Workflow $workflow,Request $request)
    {
        // Check if user can edit this workflow
        if ($workflow->status !== 'DRAFT_CREATOR' || $workflow->created_by !== Auth::id()) {
            return redirect()->route('workflows.index')
                ->with('error', 'You can only edit workflows in draft status that you created.');
        }

        $user = Auth::user();

        // Get jenis anggaran
        $jenisAnggaran = JenisAnggaran::whereNull('deleted_at')
            ->where('is_show', 1)
            ->get();

        // Get all available users
        $users = User::where('status', 'active')
            ->where('id', '!=', $user->id)
            ->get();

        // Get workflow approvals in sequence order
        $workflowApprovals = WorkflowApproval::where('workflow_id', $workflow->id)
            ->orderBy('sequence', 'asc')
            ->get();

        // Get workflow documents
        $workflowDocuments = WorkflowDocument::where('workflow_id', $workflow->id)
            ->orderBy('sequence', 'asc')
            ->get();

        // Get user roles for the current user
        $userRoles = UserRole::where('user_id', $user->id)->get();

        // Fetch employee details for cost center data
        $nik = getAuthNik() ?? null;
        $employeeDetails = getDetailNaker($nik);

        $compact = compact(
            'workflow',
            'jenisAnggaran',
            'user',
            'users',
            'workflowApprovals',
            'workflowDocuments',
            'userRoles'
        );

        if ($request->dump == true) {
            return $compact;
        }

        return view('workflows.edit.edit', $compact);
    }

    /**
     * Update the specified workflow in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Workflow  $workflow
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Workflow $workflow)
    {
        // Check if user can update this workflow
        if ($workflow->status !== 'DRAFT_CREATOR' || $workflow->created_by !== Auth::id()) {
            return redirect()->route('workflows.index')
                ->with('error', 'You can only update workflows in draft status that you created.');
        }

        $validStatusCodes = collect(Workflow::getStatuses())->pluck('code')->toArray();

        try {
            $validated = $request->validate([
                'unit_kerja'           => 'required|string',
                'cost_center'          => 'nullable|string',
                'cost_center_id'       => 'nullable|string',
                'unit_cc_id'           => 'nullable|string',
                'nama_kegiatan'        => 'required|string',
                'deskripsi_kegiatan'   => 'nullable|string',
                'jenis_anggaran'       => 'required|string',
                'creation_date'        => 'required|string',
                'total_nilai'          => 'required|numeric|min:0',
                'waktu_penggunaan'     => 'required|date',
                'account'              => 'nullable|string',
                'pics'                 => 'required|array',
                'pics.*.user_id'       => 'required',
                'pics.*.notes'         => 'nullable|string',
                'pics.*.digital_signature' => 'nullable|string',
                'pics.*.role'          => 'required|string',
                'documents'            => 'nullable|array',
                'documents.*'          => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
                'document_categories.*' => 'nullable|string',
                'document_types.*'     => 'nullable|string',
                'document_sequence.*'  => 'nullable|integer',
                'document_notes.*'     => 'nullable|string',
                'is_draft'             => 'nullable|boolean',
                'remove_documents'     => 'nullable|array',
                'remove_documents.*'   => 'nullable|integer|exists:workflow_documents,id',
                'existing_document_ids' => 'nullable|array',
                'existing_document_categories' => 'nullable|array',
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
            // Update workflow basic info
            $workflow->update([
                'unit_kerja' => $validated['unit_kerja'],
                'cost_center' => $validated['cost_center'] ?? null,
                'cost_center_id' => $validated['cost_center_id'] ?? null,
                'cost_center_account' => $validated['unit_cc_id'] ?? null,
                'nama_kegiatan' => $validated['nama_kegiatan'],
                'deskripsi_kegiatan' => $validated['deskripsi_kegiatan'] ?? null,
                'jenis_anggaran' => $validated['jenis_anggaran'],
                'total_nilai' => $validated['total_nilai'],
                'waktu_penggunaan' => $validated['waktu_penggunaan'],
                'account' => $validated['account'] ?? null,
                'status' => $request->input('is_draft', false) ? 'DRAFT_CREATOR' : 'WAITING_APPROVAL',
            ]);

            // Log the update action
            WorkflowLogger::logUpdate($workflow, array_keys($validated));

            // Process approvals/PICs
            if ($request->has('pics')) {
                // Delete existing approvals
                WorkflowApproval::where('workflow_id', $workflow->id)->delete();

                // Use PICs in the exact order they came in the request
                $pics = $request->pics;

                // Find the creator index for reference
                $creatorIndex = null;
                foreach ($pics as $index => $pic) {
                    if ($pic['role'] === 'Creator') {
                        $creatorIndex = $index;
                        break;
                    }
                }

                // Determine next active person
                $nextActiveIndex = null;
                if (!$request->input('is_draft', false) && $creatorIndex !== null) {
                    // Find the next person after creator in sequence
                    $nextActiveIndex = $creatorIndex + 1;
                    if ($nextActiveIndex >= count($pics)) {
                        $nextActiveIndex = null; // No next person if creator is last
                    }
                }

                foreach ($pics as $index => $pic) {
                    $isCurrentUser = ($pic['user_id'] == Auth::id());
                    $role = $pic['role'];
                    $isCreator = ($role === 'Creator');

                    // Map role names to role codes for DB storage
                    $roleCodeMap = [
                        'Creator' => 'CREATOR',
                        'Acknowledger' => 'ACKNOWLEDGED_BY_SPV',
                        'Unit Head - Approver' => 'APPROVED_BY_HEAD_UNIT',
                        'Reviewer-Maker' => 'REVIEWED_BY_MAKER',
                        'Reviewer-Approver' => 'REVIEWED_BY_APPROVER'
                    ];

                    $roleCode = $roleCodeMap[$role] ?? $role;

                    // Determine approval status
                    $isApproved = false;
                    if (!$request->input('is_draft', false)) {
                        if ($isCreator) {
                            $isApproved = true; // Creator is auto-approved on submission
                        } else if ($isCurrentUser) {
                            $isApproved = true; // Current user is auto-approved
                        }
                    }

                    // Determine active status
                    $isActive = false;
                    if ($request->input('is_draft', false)) {
                        // In draft mode, only creator is active
                        $isActive = $isCreator;
                    } else {
                        // In submit mode, next person after creator is active
                        $isActive = ($index === $nextActiveIndex);
                    }

                    // Create the WorkflowApproval record
                    $approval = WorkflowApproval::create([
                        'workflow_id' => $workflow->id,
                        'user_id' => $pic['user_id'],
                        'role' => $roleCode,
                        'digital_signature' => $pic['digital_signature'] ?? 0,
                        'notes' => $pic['notes'] ?? null,
                        'sequence' => $index + 1, // Keep original sequence
                        'is_active' => $isActive ? 1 : 0,
                        'status' => $isApproved ? 'APPROVED' : 'PENDING',
                        'approved_at' => $isApproved ? now() : null,
                    ]);
                }
            }

            // Handle document removal if requested
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

            // Update existing document categories if any
            if ($request->has('existing_document_ids') && $request->has('existing_document_categories')) {
                foreach ($request->input('existing_document_ids') as $documentId) {
                    if (isset($request->input('existing_document_categories')[$documentId])) {
                        $document = WorkflowDocument::find($documentId);
                        if ($document) {
                            $document->update([
                                'document_category' => $request->input('existing_document_categories')[$documentId],
                                'sequence' => $request->input('document_sequence')[$documentId] ?? $document->sequence,
                            ]);
                        }
                    }
                }
            }

            // Handle new document uploads
            if ($request->hasFile('documents')) {
                $files = $request->file('documents');

                // Create directory for this workflow's documents
                $directory = public_path("documents/{$workflow->id}");
                if (!file_exists($directory)) {
                    mkdir($directory, 0777, true);
                }

                foreach ($files as $index => $file) {
                    if ($file && $file->isValid()) {
                        // Get file ID from the input name (dynamically generated in the view)
                        $fileId = null;
                        foreach ($request->input('document_types', []) as $key => $value) {
                            if (strpos($key, 'new_') === 0) {
                                $fileId = $key;
                                break;
                            }
                        }

                        // Get document metadata for this file
                        $documentCategory = $request->input("document_categories.$fileId") ?? 'SUPPORTING';
                        $documentType = $request->input("document_types.$fileId") ?? 'OTHER';
                        $sequence = $request->input("document_sequence.$fileId") ?? $index;
                        $notes = $request->input("document_notes.$fileId") ?? null;

                        // Generate unique filename
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $uniqueName = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

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
                            'document_category' => $documentCategory,
                            'document_type' => $documentType,
                            'sequence' => $sequence,
                            'notes' => $notes,
                            'uploaded_by' => Auth::id(),
                        ]);
                    }
                }
            }

            DB::commit();

            $message = $request->input('is_draft', false)
                ? 'Workflow saved as draft successfully.'
                : 'Workflow updated and submitted successfully.';

            return redirect()->route('workflows.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            Log::error('Workflow update error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()
                ->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Updated approve method to work with document upload component
     */
    public function approve(Request $request, Workflow $workflow)
    {
        // Validate request
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'digital_signature' => 'nullable|boolean',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
            'document_categories.*' => 'nullable|string',
            'document_types.*' => 'nullable|string',
            'document_sequence.*' => 'nullable|integer',
            'document_notes.*' => 'nullable|string',
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

            // Log the approval action
            WorkflowLogger::logApprove($workflow, $approval->role, $validated['notes'] ?? null);

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
                $files = $request->file('documents');

                // Create directory path with workflow ID
                $directory = public_path("documents/{$workflow->id}");

                // Ensure the directory exists
                if (!file_exists($directory)) {
                    mkdir($directory, 0777, true);
                }

                foreach ($files as $index => $file) {
                    if ($file && $file->isValid()) {
                        // Get document metadata for this file
                        $documentCategory = $request->input("document_categories.$index", 'SUPPORTING');
                        $documentType = $request->input("document_types.$index", 'OTHER');
                        $sequence = $request->input("document_sequence.$index", $index);
                        $notes = $request->input("document_notes.$index");

                        // Generate unique filename
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $uniqueName = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

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
                            'document_category' => $documentCategory,
                            'document_type' => $documentType,
                            'sequence' => $sequence,
                            'notes' => $notes,
                            'uploaded_by' => Auth::id(),
                        ]);
                    }
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

    /**
     * Updated reject method to work with document upload component
     */
    public function reject(Request $request, Workflow $workflow)
    {
        // Validate request
        $validated = $request->validate([
            'notes' => 'required|string',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
            'document_categories.*' => 'nullable|string',
            'document_types.*' => 'nullable|string',
            'document_sequence.*' => 'nullable|integer',
            'document_notes.*' => 'nullable|string',
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

            // Log the rejection action
            WorkflowLogger::logReject($workflow, $approval->role, $validated['notes']);

            // Update approval status
            $approval->update([
                'status' => 'REJECTED',
                'notes' => $validated['notes'],
                'rejected_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            // Handle document uploads if any
            if ($request->hasFile('documents')) {
                $files = $request->file('documents');

                // Create directory path with workflow ID
                $directory = public_path("documents/{$workflow->id}");

                // Ensure the directory exists
                if (!file_exists($directory)) {
                    mkdir($directory, 0777, true);
                }

                foreach ($files as $index => $file) {
                    if ($file && $file->isValid()) {
                        // Get document metadata for this file
                        $documentCategory = $request->input("document_categories.$index", 'SUPPORTING');
                        $documentType = $request->input("document_types.$index", 'OTHER');
                        $sequence = $request->input("document_sequence.$index", $index);
                        $notes = $request->input("document_notes.$index");

                        // Generate unique filename
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $uniqueName = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

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
                            'document_category' => $documentCategory,
                            'document_type' => $documentType,
                            'sequence' => $sequence,
                            'notes' => $notes,
                            'uploaded_by' => Auth::id(),
                        ]);
                    }
                }
            }

            // Log the workflow status change
            WorkflowLogger::log($workflow, 'SAVE_DRAFT', $workflow->getOriginal('status'), 'DRAFT_CREATOR', 'CREATOR', 'Workflow rejected and sent back to creator');

            // Update workflow status to DRAFT_CREATOR (sent back to creator)
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
