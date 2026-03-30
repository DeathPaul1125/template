<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permisos ────────────────────────────────────────────────
        $permissions = [
            // Sistema
            'manage-users',
            'manage-roles',
            'manage-settings',
            'view-dashboard',
            'view-reports',
            // Products
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            'restore-products',
            'force-delete-products',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Roles ────────────────────────────────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        $editor     = Role::firstOrCreate(['name' => 'editor',      'guard_name' => 'web']);
        $viewer     = Role::firstOrCreate(['name' => 'viewer',      'guard_name' => 'web']);

        // super-admin → todos los permisos
        $superAdmin->syncPermissions(Permission::all());

        // admin → gestión de usuarios y dashboard
        $admin->syncPermissions(['manage-users', 'view-dashboard', 'view-reports']);

        // editor → solo dashboard
        $editor->syncPermissions(['view-dashboard']);

        // viewer → solo dashboard
        $viewer->syncPermissions(['view-dashboard']);

        // ── Usuario Super Admin inicial ──────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole('super-admin');

        // ── Usuario Admin de prueba ──────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'administrador@admin.com'],
            [
                'name'     => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->assignRole('admin');

        // ── Usuario Editor de prueba ─────────────────────────────────
        $editorUser = User::firstOrCreate(
            ['email' => 'editor@admin.com'],
            [
                'name'     => 'Editor',
                'password' => Hash::make('password'),
            ]
        );
        $editorUser->assignRole('editor');

        $this->command->info('✅ Roles, permisos y usuarios creados correctamente.');
        $this->command->table(
            ['Email', 'Rol', 'Contraseña'],
            [
                ['admin@admin.com',          'super-admin', 'password'],
                ['administrador@admin.com',  'admin',       'password'],
                ['editor@admin.com',         'editor',      'password'],
            ]
        );
    }
}
