<?php

namespace App\Http\Controllers;

use App\Helpers\CatalystHelper;
use App\Models\JenisAnggaran;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Workflow;
use App\Models\WorkflowApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        $workflows = Workflow::with(['approvals', 'jenisAnggaran'])->get();

        foreach ($workflows as $workflow) {
            foreach ($workflow->approvals as $approval) {
                $userDetail = getDetailNaker($approval->user_id);
                $approval->user_detail = $userDetail['name'] ?? '-'; // Store only the name, not the whole array
            }
        }

        $user = Auth::user();
        $userDetail = getDetailNaker($user->user_id ?? null);

        $compact = compact('workflows', 'user', 'userDetail');

        if ($request->dump == true) {
            return $compact;
        }

        return view('workflows.index', $compact);
    }

    public function create(Request $request)
    {
        $user = Auth::user(); // Get the logged-in user

        // Select where is not deleted (soft) and is_show = 1
        $jenisAnggaran = JenisAnggaran::whereNull('deleted_at')
            ->where('is_show', 1)
            ->get();

        return view('workflows.create', compact('jenisAnggaran', 'user'));
    }

    public function fetchJabatan(Request $request)
    {
        $nik = $request->nik;

        if (!$nik) {
            return response()->json(['success' => false, 'message' => 'NIK is required']);
        }

        $detail = getDetailNaker($nik);

        if ($detail && isset($detail['nama_posisi'])) {
            return response()->json(['success' => true, 'nama_posisi' => $detail['nama_posisi']]);
        }

        return response()->json(['success' => false, 'message' => 'Position not found']);
    }

    public function findUsers(Request $request)
    {
        $search = $request->input('search');

        if (!$search) {
            return response()->json(['error' => 'Search parameter is required'], 400);
        }

        // ✅ Step 1: Get Access Token
        $clientId = env('SEC_FIND_USER_CLIENT_ID');
        $clientSecret = env('SEC_FIND_USER_CLIENT_SECRET');
        $accessToken = CatalystHelper::getCatalystAccessToken($clientId, $clientSecret);

        if (!$accessToken) {
            return response()->json(['error' => 'Failed to retrieve access token'], 500);
        }

        // ✅ Step 2: Call the Correct API Endpoint
        $detailEndpoint = env("URL_CATALYST_API") . "employee/personal?param=" . urlencode($search);
        $response = Http::withToken($accessToken)->post($detailEndpoint);

        // ✅ Step 3: Check Response
        if ($response->failed() || empty($response->json())) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Extract the payload from the API response
        $data = $response->json();
        if (!isset($data['payload']) || !is_array($data['payload'])) {
            return response()->json(['error' => 'Invalid API response'], 500);
        }

        // ✅ Step 4: Map the Payload to the Desired Format
        $users = collect($data['payload'])->map(function ($user) {
            // Ensure each user has the required keys
            if (!isset($user['nik']) || !isset($user['name'])) {
                return null; // Skip invalid entries
            }

            return [
                'id'   => $user['nik'],   // NIK as unique identifier
                'name' => $user['name'], // Employee Name
            ];
        })->filter(); // Remove any null values (invalid entries)

        // ✅ Step 5: Return JSON Response
        return response()->json($users);
    }


    public function store(Request $request)
    {

        $validStatusCodes = collect(\App\Models\Workflow::getStatuses())->pluck('code')->toArray();

        $validated = $request->validate([
            'nomor_pengajuan'    => 'required|string|unique:workflows,nomor_pengajuan',
            'unit_kerja'         => 'required|string',
            'cost_center'         => 'required|string',
            'nama_kegiatan'      => 'required|string',
            'jenis_anggaran'     => 'required|string',
            'total_nilai'        => 'required|numeric|min:0',
            'waktu_penggunaan'   => 'required|date',
            'account'            => 'required|string',
            'justification_form' => 'nullable|string',
            'doc' => 'nullable|file|mimes:pdf|max:2048',
            'pics'               => 'required|array',
            'pics.*.user_id'     => 'required',
            'pics.*.notes'     => 'nullable|string',
            'pics.*.digital_signature'     => 'nullable|string',
            // 'pics.*.role'        => ['required', Rule::in($validStatusCodes)],
            'pics.*.role'        => ['required'],
        ]);

        DB::beginTransaction();
        try {
            $workflow = new Workflow();
            $workflow->fill($validated);
            $workflow->save();

            // Assign approvals (if user selects approvers)
            if ($request->has('pics')) {
                $index = 0; // Add index counter

                foreach ($request->pics as $pic) {
                    // First, create the WorkflowApproval record
                    $approval = WorkflowApproval::create([
                        'workflow_id'        => $workflow->id,
                        'user_id'            => $pic['user_id'] ?? null,
                        'role'               => $pic['role'] ?? null,
                        'digital_signature'  => $pic['digital_signature'] ?? null,
                        'notes'              => $pic['notes'] ?? null,
                    ]);


                    // Handle file upload for the first approval only
                    if ($index === 0 && $request->hasFile('doc')) {
                        $file = $request->file('doc');

                        // Generate unique filename
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $uniqueName = $originalName . '_' . time() . '.' . $extension;

                        // Create directory path with workflow ID
                        $directory = public_path("documents/{$workflow->id}");

                        // Ensure the directory exists
                        if (!file_exists($directory)) {
                            mkdir($directory, 0777, true);
                        }

                        // Move file to public directory
                        $file->move($directory, $uniqueName);

                        // Store the relative path
                        $relativePath = "documents/{$workflow->id}/{$uniqueName}";

                        // Update the first approval with file path
                        $approval->update(['attachment' => $relativePath]);
                    }

                    $index++; // Increment index
                }
            }

            DB::commit();
            return redirect()->route('workflows.index')->with('success', 'Workflow created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // ValidationException is thrown by Laravel's validate() method
            DB::rollBack();
            return back()
                ->withErrors($e->errors()) // Pass validation errors
                ->withInput(); // Retain all form data, including files and dynamic PICs
        } catch (\Exception $e) {
            // Catch any other exceptions
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()])
                ->withInput(); // Retain all form data, including files and dynamic PICs
        }
    }



    public function show(Request $request, Workflow $workflow)
    {

        $workflowApproval = WorkflowApproval::where('workflow_id', $workflow->id)
            ->orderBy('sequence', 'asc')
            ->get();


        // Select where is not deleted (soft) and is_show = 1
        $jenisAnggaran = JenisAnggaran::whereNull('deleted_at')
            ->where('is_show', 1)
            ->get();


        $compact = compact('workflow', 'jenisAnggaran', 'workflowApproval');

        // only on .env development
        if ($request->dump == true) {
            return $compact;
        }

        return view('workflows.show', $compact);
    }

    public function edit(Workflow $workflow)
    {

        return view('workflows.edit', compact('workflow'));
    }

    public function update(Request $request, Workflow $workflow)
    {
        $validated = $request->validate([
            'nomor_pengajuan'  => 'required|string|unique:workflows,nomor_pengajuan,' . $workflow->id,
            'unit_kerja'       => 'required|string',
            'nama_kegiatan'    => 'required|string',
            'jenis_anggaran'   => 'required|string',
            'total_nilai'      => 'required|numeric',
            'waktu_penggunaan' => 'required|date',
            'account'          => 'required|string',
            'justification_form' => 'nullable|string',
        ]);

        $workflow->update($validated);

        return redirect()->route('workflows.index')->with('success', 'Workflow updated successfully.');
    }

    public function destroy(Workflow $workflow)
    {
        $workflow->delete();
        return redirect()->route('workflows.index')->with('success', 'Workflow deleted successfully.');
    }
}
