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
    WorkflowController
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


Route::get('hehe',[MasterKaryawanController::class, 'getAllKaryawan'])->middleware(SsoGate::class);
Route::get('hihi',[MasterKaryawanController::class, 'detailKaryawan'])->middleware(SsoGate::class);


Route::prefix('workflows')->name('workflows.')->group(function () {
    Route::get('/', [WorkflowController::class, 'index'])->name('index'); // List workflows
    Route::get('/create', [WorkflowController::class, 'create'])->name('create'); // Create form
    Route::post('/store', [WorkflowController::class, 'store'])->name('store'); // Store new workflow
    Route::get('/{workflow}', [WorkflowController::class, 'show'])->name('show'); // Show details
    Route::get('/{workflow}/edit', [WorkflowController::class, 'edit'])->name('edit'); // Edit form
    Route::put('/{workflow}', [WorkflowController::class, 'update'])->name('update'); // Update workflow
    Route::delete('/{workflow}', [WorkflowController::class, 'destroy'])->name('destroy'); // Delete workflow
});

Route::get('meta/find-users', [WorkflowController::class, 'findUsers']);
Route::get('meta/fetch-jabatan',[WorkflowController::class, 'fetchJabatan']);

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {



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

    // Process Tickets (Duplicate Route Removed)
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
