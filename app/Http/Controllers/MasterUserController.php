<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MasterUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $masterUsers = User::with('roles')->get();
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
            'roles.*' => 'required|string',
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
                'created_by' => getAuthName(),
            ]);

            // Add roles
            foreach ($request->roles as $role) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role' => $role
                ]);
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
        $validator = Validator::make($request->all(), [
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|string',
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
            $masterUser->edited_by = getAuthName();
            $masterUser->object_id = $request->object_id;
            $masterUser->save();

            // Remove existing roles
            $masterUser->roles()->delete();

            // Add new roles
            foreach ($request->roles as $role) {
                UserRole::create([
                    'user_id' => $masterUser->id,
                    'role' => $role
                ]);
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
}
