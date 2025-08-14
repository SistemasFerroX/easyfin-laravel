<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\SolicitudAdminController;

/*
|--------------------------------------------------------------------------
| Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('welcome'))->name('welcome');

// Auth scaffolding (login/registro/etc.)
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Autenticadas (todos los roles)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard (todos)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
     | Mis solicitudes (USUARIO)
     | - /solicitudes           => SOLO pendientes del usuario
     | - /solicitudes/historial => Aprobadas + Rechazadas del usuario
     */
    Route::get('solicitudes',               [SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('solicitudes/historial',     [SolicitudController::class, 'history'])->name('solicitudes.history');

    // Crear/guardar solicitud (sólo usuario final)
    Route::middleware('role:user')->group(function () {
        Route::get('solicitudes/create', [SolicitudController::class, 'create'])->name('solicitudes.create');
        Route::post('solicitudes',        [SolicitudController::class, 'store'])->name('solicitudes.store');

        // Respuesta del usuario a la contraoferta del admin
        Route::post('solicitudes/{solicitud}/propuesta/aceptar',  [SolicitudController::class, 'acceptProposal'])
            ->name('solicitudes.propuesta.aceptar');
        Route::post('solicitudes/{solicitud}/propuesta/rechazar', [SolicitudController::class, 'rejectProposal'])
            ->name('solicitudes.propuesta.rechazar');
    });

    // Informes (sólo admin / super-admin)
    Route::middleware('role:admin|super-admin')->group(function () {
        Route::get('informes', [SolicitudController::class, 'informes'])->name('informes');
    });

    // Perfil (propio)
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Panel de Administración
|--------------------------------------------------------------------------
| - /admin/solicitudes           => SOLO pendientes (todas)
| - /admin/solicitudes/historial => Aprobadas + Rechazadas (todas)
*/
Route::middleware(['auth', 'role:admin|super-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard Admin
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        // Solicitudes (Admin)
        Route::get('solicitudes',                 [SolicitudAdminController::class, 'index'])->name('solicitudes.index');
        Route::get('solicitudes/historial',       [SolicitudAdminController::class, 'history'])->name('solicitudes.history');

        Route::post('solicitudes/{solicitud}/approve', [SolicitudAdminController::class, 'approve'])->name('solicitudes.approve');
        Route::post('solicitudes/{solicitud}/reject',  [SolicitudAdminController::class, 'reject'])->name('solicitudes.reject');
        Route::post('solicitudes/{solicitud}/counter', [SolicitudAdminController::class, 'counter'])->name('solicitudes.counter');

        // Gestión de usuarios (sólo super-admin)
        Route::middleware('role:super-admin')->group(function () {
            Route::get('users',             [AdminController::class, 'users'])->name('users.index');
            Route::get('users/create',      [AdminController::class, 'createUser'])->name('users.create');
            Route::post('users',            [AdminController::class, 'storeUser'])->name('users.store');
            Route::get('users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
            Route::put('users/{user}',      [AdminController::class, 'updateUser'])->name('users.update');
            Route::delete('users/{user}',   [AdminController::class, 'destroyUser'])->name('users.destroy');
        });
    });
