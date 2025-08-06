<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta pública de bienvenida
Route::get('/', fn() => view('welcome'))->name('welcome');

// Rutas de autenticación (login, registro, etc.)
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Rutas protegidas (cualquier usuario autenticado)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard: todos los roles
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    // Ver el listado de solicitudes (user, admin y super-admin)
    Route::get('solicitudes', [SolicitudController::class, 'index'])
         ->name('solicitudes.index');

    // CREAR y GUARDAR solicitud: solo role = user
    Route::middleware('role:user')->group(function () {
        Route::get('solicitudes/create', [SolicitudController::class, 'create'])
             ->name('solicitudes.create');
        Route::post('solicitudes', [SolicitudController::class, 'store'])
             ->name('solicitudes.store');
    });

    // Aprobar, rechazar e informes: solo admin o super-admin
    Route::middleware('role:admin|super-admin')->group(function () {
        Route::post('solicitudes/{solicitud}/approve', [SolicitudController::class, 'approve'])
             ->name('solicitudes.approve');
        Route::post('solicitudes/{solicitud}/reject', [SolicitudController::class, 'reject'])
             ->name('solicitudes.reject');
        Route::get('informes', [SolicitudController::class, 'informes'])
             ->name('informes');
    });

    // Perfil del propio usuario
    Route::get('/profile',    [ProfileController::class, 'edit'])
         ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])
         ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
         ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Panel de Administración
|--------------------------------------------------------------------------
| - Dashboard de admin: roles admin y super-admin
| - Gestión completa de usuarios: solo super-admin
*/
// Dashboard de admin/super-admin
Route::middleware(['auth', 'role:admin|super-admin'])->group(function () {
    Route::get('admin', [AdminController::class, 'index'])
         ->name('admin.dashboard');
});

// Gestión de usuarios (solo super-admin)
Route::middleware(['auth', 'role:super-admin'])
     ->prefix('admin')
     ->name('admin.')
     ->group(function () {
         Route::get('users',            [AdminController::class, 'users'])
              ->name('users.index');
         Route::get('users/create',     [AdminController::class, 'createUser'])
              ->name('users.create');
         Route::post('users',           [AdminController::class, 'storeUser'])
              ->name('users.store');
         Route::get('users/{user}/edit',[AdminController::class, 'editUser'])
              ->name('users.edit');
         Route::put('users/{user}',     [AdminController::class, 'updateUser'])
              ->name('users.update');
         Route::delete('users/{user}',  [AdminController::class, 'destroyUser'])
              ->name('users.destroy');
     });
