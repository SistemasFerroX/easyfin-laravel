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

// Bienvenida pública
Route::get('/', fn() => view('welcome'))->name('welcome');

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    // Solicitudes (index, create, store)
    Route::get('solicitudes',          [SolicitudController::class, 'index'])
         ->name('solicitudes.index');
    Route::get('solicitudes/create',   [SolicitudController::class, 'create'])
         ->name('solicitudes.create');
    Route::post('solicitudes',         [SolicitudController::class, 'store'])
         ->name('solicitudes.store');

    // Perfil de usuario
    Route::get('/profile',   [ProfileController::class, 'edit'])
         ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
         ->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])
         ->name('profile.destroy');
});

// Rutas de administrador (requieren permiso isAdmin además de auth)
Route::middleware(['auth','can:isAdmin'])->group(function() {
    Route::get('admin',            [AdminController::class, 'index'])
         ->name('admin.dashboard');
    Route::get('admin/users',      [AdminController::class, 'users'])
         ->name('admin.users');
    Route::get('admin/users/{user}/edit',   [AdminController::class,'editUser'])
         ->name('admin.users.edit');
    Route::put('admin/users/{user}',         [AdminController::class,'updateUser'])
         ->name('admin.users.update');
});

// Incluye las rutas de login, registro, etc.
require __DIR__.'/auth.php';
