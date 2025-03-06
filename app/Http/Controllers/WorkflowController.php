<?php

namespace App\Http\Controllers;

use App\Models\JenisAnggaran;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Workflow;
use App\Models\WorkflowApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        $workflows = Workflow::with(['approvals', 'jenisAnggaran'])->get();
        $user = Auth::user(); // Get the logged-in user

        $compact = compact('workflows', 'user');

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

    public function findUsers(Request $request)
    {
        $search = $request->input('search');

        $users = User::where('name', 'like', "%$search%")
            ->orderBy('name')
            ->limit(20) // Limit results to improve performance
            ->get(['id', 'name']);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validStatusCodes = collect(\App\Models\Workflow::getStatuses())->pluck('code')->toArray();

        $validated = $request->validate([
            'nomor_pengajuan'    => 'required|string|unique:workflows,nomor_pengajuan',
            'unit_kerja'         => 'required|string',
            'nama_kegiatan'      => 'required|string',
            'jenis_anggaran'     => 'required|string',
            'total_nilai'        => 'required|numeric|min:0',
            'waktu_penggunaan'   => 'required|date',
            'account'            => 'required|string',
            'justification_form' => 'nullable|string',
            'doc' => 'nullable|file|mimes:pdf|max:2048',
            'pics'               => 'required|array',
            'pics.*.user_id'     => 'required|exists:users,id',
            'pics.*.role'        => ['required', Rule::in($validStatusCodes)],
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
                        'workflow_id' => $workflow->id,
                        'user_id'     => $pic['user_id'],
                        'role'        => $pic['role'],
                    ]);


                    // Handle file upload for the first approval only
                    if ($index === 0 && $request->hasFile('doc')) {
                        $file = $request->file('doc');

                        // Generate unique filename
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $uniqueName = $originalName . '_' . time() . '.' . $extension;

                        // Create directory path with workflow ID
                        $directory = "documents/{$workflow->id}";

                        // Store file
                        $path = $file->storeAs($directory, $uniqueName, 'public');

                        // Update the first approval with file path
                        $approval->update(['attachment' => $path]);
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



    public function show(Workflow $workflow)
    {
        return view('workflows.show', compact('workflow'));
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
