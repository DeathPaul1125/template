<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Ruta raíz → redirige a login o dashboard
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Rutas protegidas
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Gestión de usuarios
    Route::resource('users', UserController::class)
        ->except(['show'])
        ->middleware('permission:manage-users');

    // Gestión de roles
    Route::resource('roles', RoleController::class)
        ->except(['show'])
        ->middleware('permission:manage-roles');

    // Configuración del sistema
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index')->middleware('permission:manage-settings');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update')->middleware('permission:manage-settings');

    // Imprimir PDF
    Route::get('/users/print', [\App\Http\Controllers\UserController::class, 'downloadPdf'])->name('users.print');
});
