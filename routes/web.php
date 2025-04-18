<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ApprovalWorkflowController,
    HomeController,
    Auth\LoginController,
    RegistrasiController,
    DiscussionController,
    KaryawanController,
    EmailController,
    CategoryController,
    JenisAnggaranController,
    MasterKaryawanController,
    MasterUserController,
    WorkflowController,
    Admin\ApprovalMatrixController
};
use App\Http\Controllers\User\TicketController;
use App\Http\Middleware\SsoGate;

Route::view('/', 'welcome');
Route::redirect('/', '/login');

// Authentication Routes
Route::view('registrasi', 'auth.registrasi');
Route::view('login', 'auth.login')->name('login');
Route::post('/login/proc', [LoginController::class, 'checkLogin']);
Route::any('/logout', [LoginController::class, 'logout'])->name('logout');

// Home Routes
Route::get('/home', [HomeController::class, 'index'])->name('home');

// User Ticket Routes
Route::prefix('user/ticket')->controller(TicketController::class)->group(function () {
    Route::get('create', 'viewCreate');
    Route::post('create', 'store');
    Route::get('pending', 'viewUserPending');
    Route::get('progress', 'viewUserProgress');
    Route::get('complete', 'viewUserComplete');
    Route::get('{id}/edit', 'viewDetail');
    Route::get('{id}/delete', 'destroy');
});

Route::get('/user/home', [HomeController::class, 'homeUser']);

// User Registration
Route::post('/user/regis', [RegistrasiController::class, 'store']);

Route::get('hehe', [MasterKaryawanController::class, 'getAllKaryawan']);
Route::get('hihi', [MasterKaryawanController::class, 'detailKaryawan']);
Route::get('wkwk', [MasterKaryawanController::class, 'getAllUsers']);

Route::get('/api/coa/cost-center-account-list',[WorkflowController::class, 'getCostCenterAccountList']);

Route::get('get-approval-matrix', [WorkflowController::class, 'getApprovalMatrix'])
    ->name('get.approval.matrix');

// Define your workflow action routes
Route::get('workflow-actions/find-users', [WorkflowController::class, 'findUsers'])
    ->name('workflows.find-users');

    // Add this to your routes/web.php file in the appropriate section
Route::post('workflows-actions/get-cost-center-accounts', [WorkflowController::class, 'getCostCenterAccounts'])->name('workflows.get-cost-center-accounts');

Route::get('workflow-actions/fetch-jabatan', [WorkflowController::class, 'fetchJabatan'])
    ->name('workflows.fetch-jabatan');

Route::get('workflow-actions/get-unit-kerja', [WorkflowController::class, 'getUnitKerja'])
    ->name('workflows.get-unit-kerja');

Route::get('workflow-actions/get-employees', [WorkflowController::class, 'getEmployees'])
    ->name('workflows.get-employees');

Route::get('workflow-actions/get-user-roles', [WorkflowController::class, 'getUserRoles'])
    ->name('workflows.get-user-roles');

// NEW: Add the getAvailableRoles endpoint for dynamic role selection
Route::get('workflow-actions/getAvailableRoles', [WorkflowController::class, 'getAvailableRoles'])
    ->name('workflows.getAvailableRoles');

// Keep the approval and rejection routes with the workflows prefix
Route::post('workflows/{workflow}/approve', [WorkflowController::class, 'approve'])
    ->name('workflows.approve');

Route::post('workflows/{workflow}/reject', [WorkflowController::class, 'reject'])
    ->name('workflows.reject');

Route::post('workflows/{workflow}/draft', [WorkflowController::class, 'draft'])
    ->name('workflows.draft');

//add middleware auth group with laravel default
Route::middleware(['auth'])->group(function () {
    Route::resource('workflows', WorkflowController::class);
});



Route::get('meta/find-users', [WorkflowController::class, 'findUsers']);
Route::get('meta/fetch-jabatan', [WorkflowController::class, 'fetchJabatan']);

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('master-user', MasterUserController::class);
    Route::post('get-user-details', [MasterUserController::class, 'getUserDetailsByNik'])->name('get-user-details');
    Route::post('search-employees', [MasterUserController::class, 'searchEmployees'])->name('search-employees');

    // Add the new resource route for approval matrix
    Route::resource('approval-matrix', ApprovalMatrixController::class)->except(['show']);

    Route::resource('jenis-anggaran', JenisAnggaranController::class);
    Route::get('home', [HomeController::class, 'index']);
    Route::get('karyawan/tambah', [HomeController::class, 'homeAdmin']);

    // Ticket Management
    Route::prefix('ticket')->controller(AdminTicketController::class)->group(function () {
        Route::get('{status}', 'viewManage');
        Route::get('{id}/delete', 'destroy');
        Route::post('{id}/update_status', 'update_status');
        Route::get('{id}/edit', 'viewDetail');
    });

    // Process Tickets
    Route::get('/process/ticket/{status}', [AdminTicketController::class, 'viewManage']);
});

// Operator Routes
Route::get('/operator/home', [HomeController::class, 'homeAdmin']);

// Discussion Routes
Route::post('ticket/discussion/{id}/post', [DiscussionController::class, 'store']);
Route::post('ticket/delegate', [AdminTicketController::class, 'delegate']);

// Karyawan Management Routes
Route::prefix('karyawan')->controller(KaryawanController::class)->group(function () {
    Route::get('tambah', 'viewAddKaryawan');
    Route::post('tambah', 'store');
    Route::get('manage', 'viewManage');
    Route::get('{id}/delete', 'destroy');
    Route::get('{id}/edit', 'viewEdit');
    Route::post('{id}/edit', 'edit');
});

// Email Routes
Route::get('/kirim-email', [EmailController::class, 'index']);

// Category Management Routes
Route::prefix('kategori')->controller(CategoryController::class)->group(function () {
    Route::get('tambah', 'viewCreate');
    Route::post('tambah', 'store');
    Route::get('manage', 'viewManage');
    Route::get('{id}/delete', 'destroy');
    Route::get('{id}/edit', 'viewEdit');
    Route::post('{id}/edit', 'update');
});
