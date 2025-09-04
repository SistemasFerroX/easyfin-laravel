<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\SolicitudAdminController;
use App\Http\Controllers\PdfSolicitudController;

/*
|--------------------------------------------------------------------------
| Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('welcome'))->name('welcome');
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Autenticadas (todos los roles)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard (todos)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Mis solicitudes (USUARIO)
    Route::get('solicitudes',           [SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('solicitudes/historial', [SolicitudController::class, 'history'])->name('solicitudes.history');

    // PDFs del USUARIO: SOLO amortización de su propia solicitud
    Route::get('solicitudes/{solicitud}/pdf/amortizacion',
        [PdfSolicitudController::class, 'amortizacion']
    )->name('solicitudes.pdf.amortizacion');

    // Crear / guardar / responder propuesta (solo role:user)
    Route::middleware('role:user')->group(function () {
        Route::get('solicitudes/create', [SolicitudController::class, 'create'])->name('solicitudes.create');
        Route::post('solicitudes',       [SolicitudController::class, 'store'])->name('solicitudes.store');

        Route::post('solicitudes/{solicitud}/propuesta/aceptar',
            [SolicitudController::class, 'acceptProposal']
        )->name('solicitudes.propuesta.aceptar');

        Route::post('solicitudes/{solicitud}/propuesta/rechazar',
            [SolicitudController::class, 'rejectProposal']
        )->name('solicitudes.propuesta.rechazar');
    });

    // Informes (solo admin / super-admin)
    Route::middleware('role:admin|super-admin')->group(function () {
        Route::get('informes', [SolicitudController::class, 'informes'])->name('informes');
    });

    // Perfil
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Panel de Administración (admin / super-admin)
|--------------------------------------------------------------------------
| - /admin/solicitudes           => pendientes
| - /admin/solicitudes/historial => aprobadas + rechazadas
*/
Route::middleware(['auth', 'role:admin|super-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        // Solicitudes (Admin)
        Route::get('solicitudes',           [SolicitudAdminController::class, 'index'])->name('solicitudes.index');
        Route::get('solicitudes/historial', [SolicitudAdminController::class, 'history'])->name('solicitudes.history');

        Route::post('solicitudes/{solicitud}/approve', [SolicitudAdminController::class, 'approve'])->name('solicitudes.approve');
        Route::post('solicitudes/{solicitud}/reject',  [SolicitudAdminController::class, 'reject'])->name('solicitudes.reject');
        Route::post('solicitudes/{solicitud}/counter', [SolicitudAdminController::class, 'counter'])->name('solicitudes.counter');

        // Descargar PDFs generados (Admin/Super): amortización + certificado
        Route::get('solicitudes/{solicitud}/pdf/amortizacion',
            [PdfSolicitudController::class, 'amortizacion']
        )->name('solicitudes.pdf.amortizacion');

        Route::get('solicitudes/{solicitud}/pdf/certificado',
            [PdfSolicitudController::class, 'certificado']
        )->name('solicitudes.pdf.certificado');

        // === NUEVO: Adjuntos y documentos ===

        // Subir/actualizar PDF del admin (visible para todos los admins)
        Route::post('solicitudes/{solicitud}/admin-pdf',
            [SolicitudAdminController::class, 'uploadAdminPdf']
        )->name('solicitudes.adminpdf.upload');

        // Ver/descargar el PDF del admin
        Route::get('solicitudes/{solicitud}/admin-pdf',
            [SolicitudAdminController::class, 'viewAdminPdf']
        )->name('solicitudes.adminpdf.view');

        // Ver cédula y certificado bancario subidos por el usuario (solo admin/super)
        Route::get('solicitudes/{solicitud}/doc/cedula',
            [SolicitudAdminController::class, 'viewUserDoc']
        )->defaults('tipo','cedula')->name('solicitudes.doc.cedula');

        Route::get('solicitudes/{solicitud}/doc/banco',
            [SolicitudAdminController::class, 'viewUserDoc']
        )->defaults('tipo','banco')->name('solicitudes.doc.banco');

        // Gestión de usuarios (solo super-admin)
        Route::middleware('role:super-admin')->group(function () {
            Route::get('users',             [AdminController::class, 'users'])->name('users.index');
            Route::get('users/create',      [AdminController::class, 'createUser'])->name('users.create');
            Route::post('users',            [AdminController::class, 'storeUser'])->name('users.store');
            Route::get('users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
            Route::put('users/{user}',      [AdminController::class, 'updateUser'])->name('users.update');
            Route::delete('users/{user}',   [AdminController::class, 'destroyUser'])->name('users.destroy');
        });
    });
