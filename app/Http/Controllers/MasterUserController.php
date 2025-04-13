<?php

namespace App\Http\Controllers;

use App\Helpers\CatalystHelper;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MasterUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $masterUsers = User::with('roles')
            ->leftJoin('users as creator_user', 'users.created_by', '=', 'creator_user.id')
            ->leftJoin('users as editor_user', 'users.edited_by', '=', 'editor_user.id')
            ->select(
                'users.*',
                'creator_user.name as creator_name',
                'editor_user.name as editor_name'
            )
            ->get();

        // Debug to see what's happening
        // dd($masterUsers->first());

        return view('admin.master-user.index', compact('masterUsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.master-user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|unique:users,nik',
            'roles' => 'required|array|min:1',
            'status' => 'required|in:Active,Not Active',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Fetch user details from API
            $employeeDetails = getDetailNaker($request->nik);

            if (!$employeeDetails) {
                return redirect()->back()
                    ->withErrors(['nik' => 'Cannot find user data with the provided NIK'])
                    ->withInput();
            }

            // Create the user
            $user = User::create([
                'nik' => $request->nik,
                'object_id' => $request->object_id,
                'name' => $employeeDetails['name'] ?? '',
                'email' => $request->nik . '@example.com', // Create a placeholder email
                'password' => bcrypt('password'), // Set a default password
                'unit_kerja' => $employeeDetails['unit'] ?? '',
                'jabatan' => $employeeDetails['nama_posisi'] ?? '',
                'status' => $request->status,
                'created_by' => Auth::id(),
            ]);

            // Add roles with budget limits
            if (isset($request->roles) && is_array($request->roles)) {
                foreach ($request->roles as $roleKey => $roleData) {
                    // Handle both formats: array of strings or array of arrays
                    if (is_array($roleData)) {
                        $role = $roleData['role'] ?? null;
                        $minBudget = isset($roleData['min_budget']) ? (float)$roleData['min_budget'] : null;
                        $maxBudget = isset($roleData['max_budget']) && $roleData['max_budget'] !== '' ?
                            (float)$roleData['max_budget'] : null;
                    } else {
                        $role = $roleData;
                        $minBudget = null;
                        $maxBudget = null;
                    }

                    if ($role) {
                        UserRole::create([
                            'user_id' => $user->id,
                            'role' => $role,
                            'min_budget' => $minBudget,
                            'max_budget' => $maxBudget
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.master-user.index')
                ->with('success', 'Master User created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Error creating master user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $masterUser)
    {
        $masterUser->load('roles');
        return view('admin.master-user.show', compact('masterUser'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $masterUser)
    {
        $masterUser->load('roles');
        return view('admin.master-user.edit', compact('masterUser'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $masterUser)
    {
        // Remove this debug line once fixed
        // return $request->all();

        $validator = Validator::make($request->all(), [
            'roles' => 'required|array|min:1',
            'status' => 'required|in:Active,Not Active',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update user details
            $masterUser->status = $request->status;
            $masterUser->edited_by = Auth::id();
            $masterUser->object_id = $request->object_id;
            $masterUser->save();

            // Remove existing roles
            $masterUser->roles()->delete();

            // Add new roles with budget limits
            if (isset($request->roles) && is_array($request->roles)) {
                foreach ($request->roles as $roleKey => $roleData) {
                    $role = null;
                    $minBudget = null;
                    $maxBudget = null;

                    // Handle different possible structures of incoming data
                    if (is_array($roleData)) {
                        if (isset($roleData['role'])) {
                            $role = $roleData['role'];
                        } else {
                            $role = $roleKey; // If the role name is the array key
                        }

                        $minBudget = isset($roleData['min_budget']) ? (float)$roleData['min_budget'] : null;
                        $maxBudget = isset($roleData['max_budget']) && $roleData['max_budget'] !== ''
                            ? (float)$roleData['max_budget'] : null;
                    } else {
                        $role = $roleData;
                    }

                    if ($role) {
                        UserRole::create([
                            'user_id' => $masterUser->id,
                            'role' => $role,
                            'min_budget' => $minBudget,
                            'max_budget' => $maxBudget
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.master-user.index')
                ->with('success', 'Master User updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Error updating master user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $masterUser)
    {
        try {
            $masterUser->delete();
            return redirect()->route('admin.master-user.index')
                ->with('success', 'Master User deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Error deleting master user: ' . $e->getMessage()]);
        }
    }

    /**
     * Get user details by NIK via AJAX
     */
    public function getUserDetailsByNik(Request $request)
    {
        $nik = $request->input('nik');
        $userDetails = getDetailNaker($nik);

        if (!$userDetails) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $userDetails['name'] ?? '',
                'unit_kerja' => $userDetails['unit'] ?? '',
                'jabatan' => $userDetails['nama_posisi'] ?? '',
                'object_id' => $userDetails['object_id'] ?? null,
            ]
        ]);
    }


    /**
     * Search employees by name or part of name
     */
    public function searchEmployees(Request $request)
    {
        $searchParam = $request->input('param');

        if (empty($searchParam)) {
            return response()->json(['error' => 'Search parameter is required'], 400);
        }

        try {
            // Get employees from Catalyst API
            $clientIdPersonal = "9964612a-f658-4d31-8f28-f146dd8c8eb3";
            $clientSecretPersonal = "LZFCNQ0QrMcvMxUjJqkolRkPaySQh0hJVwTRGusb";
            $endpointPersonal = "https://catalyst.telkomakses.co.id:2101/api/v1/employee/personal";

            $clientIdDetail = "9ae6194e-ab57-4936-ab34-08877c9b2382";
            $clientSecretDetail = "BvmTwAFR8Ku112gPvuULOoIZHQr1VWZrjtuKGc99";
            $endpointDetail = "https://catalyst.telkomakses.co.id:2101/api/v1/employee/detail";

            // Get access token for personal data
            $accessTokenPersonal = CatalystHelper::getCatalystAccessToken($clientIdPersonal, $clientSecretPersonal);
            if (!$accessTokenPersonal) {
                return response()->json(['error' => 'Failed to retrieve access token for personal data'], 500);
            }

            // Fetch list of employees
            $personalResponse = Http::withToken($accessTokenPersonal)->post($endpointPersonal, [
                'param' => $searchParam,
            ]);

            if ($personalResponse->failed()) {
                return response()->json(['error' => 'Failed to retrieve employee personal data'], $personalResponse->status());
            }

            $employees = $personalResponse->json();
            if (!isset($employees['payload']) || empty($employees['payload'])) {
                return response()->json([
                    'state' => 'success',
                    'code' => 200,
                    'data' => []
                ]);
            }

            // Get access token for detail data
            $accessTokenDetail = CatalystHelper::getCatalystAccessToken($clientIdDetail, $clientSecretDetail);
            if (!$accessTokenDetail) {
                return response()->json(['error' => 'Failed to retrieve access token for employee details'], 500);
            }

            // Fetch details for each employee using NIK
            $detailedEmployees = [];
            foreach ($employees['payload'] as $employee) {
                $nik = $employee['nik'] ?? null;
                if (!$nik) continue;

                $detailResponse = Http::withToken($accessTokenDetail)->post($endpointDetail, [
                    'nik' => $nik,
                ]);

                $detailData = $detailResponse->json();

                // Process detail data to match expected format
                $detailedEmployees[] = [
                    'personal' => $employee,
                    'detail' => $detailData
                ];
            }

            return response()->json([
                'state' => 'success',
                'code' => 200,
                'data' => $detailedEmployees,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error searching employees: ' . $e->getMessage());
            return response()->json([
                'state' => 'error',
                'code' => 500,
                'message' => 'An error occurred while searching for employees: ' . $e->getMessage()
            ], 500);
        }
    }
}
