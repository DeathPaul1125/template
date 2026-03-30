<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Rutas del instalador (Wizard)
Route::middleware(['redirect.installed'])->prefix('install')->name('install.')->group(function () {
    Route::get('/welcome', [InstallerController::class, 'welcome'])->name('welcome');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'saveDatabase'])->name('saveDatabase');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallerController::class, 'saveAdmin'])->name('saveAdmin');
    Route::get('/finalized', [InstallerController::class, 'finalized'])->name('finalized');
});

// Ruta raíz → redirige a login o dashboard
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->middleware('check.installed');

// Rutas protegidas
Route::middleware([
    'check.installed',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/save-layout', [\App\Http\Controllers\DashboardController::class, 'saveLayout'])->name('dashboard.save-layout');
    Route::post('/dashboard/widget-data', [\App\Http\Controllers\DashboardController::class, 'widgetData'])->name('dashboard.widget-data');

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

    // Generador de CRUD
    Route::get('/crud-generator', [\App\Http\Controllers\CrudGeneratorController::class, 'index'])
        ->name('crud-generator.index')
        ->middleware('role:super-admin');
    Route::post('/crud-generator/generate', [\App\Http\Controllers\CrudGeneratorController::class, 'generate'])
        ->name('crud-generator.generate')
        ->middleware('role:super-admin');
    Route::get('/crud-generator/{model}/edit', [\App\Http\Controllers\CrudGeneratorController::class, 'edit'])
        ->name('crud-generator.edit')
        ->middleware('role:super-admin');
    Route::post('/crud-generator/{model}/rebuild', [\App\Http\Controllers\CrudGeneratorController::class, 'rebuild'])
        ->name('crud-generator.rebuild')
        ->middleware('role:super-admin');
    Route::delete('/crud-generator/{model}/meta', [\App\Http\Controllers\CrudGeneratorController::class, 'destroyMeta'])
        ->name('crud-generator.destroy-meta')
        ->middleware('role:super-admin');
    Route::delete('/crud-generator/{model}/module', [\App\Http\Controllers\CrudGeneratorController::class, 'destroyModule'])
        ->name('crud-generator.destroy-module')
        ->middleware('role:super-admin');
    Route::get('/crud-generator/icons', [\App\Http\Controllers\CrudGeneratorController::class, 'icons'])
        ->name('crud-generator.icons')
        ->middleware('role:super-admin');



    // Product
    Route::get('/products/data', [ProductController::class, 'data'])->name('products.data');
    Route::resource('products', ProductController::class)->except(['show']);
    // Product: restaurar y eliminar definitivo (solo super-admin)
    Route::post('/products/{id}/restore', [ProductController::class, 'restore'])
        ->name('products.restore')
        ->middleware('role:super-admin');
    Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])
        ->name('products.force-delete')
        ->middleware('role:super-admin');
    // @crud-routes
});
