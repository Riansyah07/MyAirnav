<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Superadmin\UserManagementController;
use App\Http\Controllers\Superadmin\DocumentController as SuperadminDocumentController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\User\DocumentController as UserDocumentController;
use App\Http\Controllers\Superadmin\CertificateController;
use App\Models\Certificate;

// Redirect root ke login
Route::get('/', fn () => redirect('/login'));

// ===================== LOGIN / LOGOUT ===================== //
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// ===================== DASHBOARD BERDASARKAN ROLE ===================== //
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return match (Auth::user()->role) {
            'superadmin' => redirect()->route('superadmin.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('user.dashboard'),
        };
    })->name('dashboard');
});

Route::post('/notifications/read/{id}', function ($id) {
    $notification = Auth::user()->notifications->findOrFail($id);
    $notification->markAsRead();
    return back();
})->name('notifications.markAsRead');


Route::prefix('superadmin')->middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Superadmin\DashboardController::class, 'index'])->name('superadmin.dashboard');
});
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
});
Route::prefix('user')->middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\User\DashboardController::class, 'index'])->name('user.dashboard');
});


// ===================== RUTE SUPERADMIN ===================== //
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Superadmin\DashboardController::class, 'index'])->name('dashboard');

    // Notifikasi Superadmin
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Manajemen Users
    Route::resource('users', UserManagementController::class);

    // Dokumen
    Route::post('/documents/bulk-download', [SuperadminDocumentController::class, 'bulkDownload'])->name('documents.bulkDownload');
    Route::post('/documents/bulk-delete', [SuperadminDocumentController::class, 'bulkDelete'])->name('documents.bulkDelete');
    Route::get('/documents/{category}', [SuperadminDocumentController::class, 'showCategory'])->whereIn('category', ['teknik', 'operasi', 'k3'])->name('documents.category');
    Route::resource('documents', SuperadminDocumentController::class);

    // Sertifikat
    Route::prefix('sertifikat')->name('sertifikat.')->group(function () {
        Route::get('/', [CertificateController::class, 'index'])->name('index');
        Route::get('/create', [CertificateController::class, 'create'])->name('create');
        Route::post('/', [CertificateController::class, 'store'])->name('store');
        Route::get('/{id}', [CertificateController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CertificateController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CertificateController::class, 'update'])->name('update');
        Route::delete('/superadmin/sertifikat/{id}', [CertificateController::class, 'destroy'])->name('superadmin.sertifikat.destroy');
        Route::post('/bulk-download', [CertificateController::class, 'bulkDownload'])->name('bulkDownload');
        Route::post('/bulk-delete', [CertificateController::class, 'bulkDelete'])->name('bulkDelete');
    });
});


// ===================== RUTE ADMIN ===================== //
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');

    // Notifikasi Admin
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Dokumen
    Route::get('/documents/{category}', [AdminDocumentController::class, 'showCategory'])->whereIn('category', ['teknik', 'operasi', 'k3'])->name('documents.category');
    Route::resource('documents', AdminDocumentController::class);
    Route::post('/documents/bulk-download', [AdminDocumentController::class, 'bulkDownload'])->name('documents.bulkDownload');
    Route::post('/documents/bulk-delete', [AdminDocumentController::class, 'bulkDelete'])->name('documents.bulkDelete');
});

// ===================== RUTE USER ===================== //
Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    // Route::get('/dashboard', fn () => view('user.dashboard'))->name('dashboard');

    // Notifikasi User
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Dokumen
    Route::get('/documents/{category}', [UserDocumentController::class, 'showCategory'])->whereIn('category', ['teknik', 'operasi', 'k3'])->name('documents.category');
    Route::resource('documents', UserDocumentController::class);
});




require __DIR__ . '/auth.php';
