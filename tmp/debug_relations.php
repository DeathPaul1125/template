<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- DEPURACIÓN DE RELACIONES ---\n";

function test($label, $callback) {
    try {
        echo "Testing $label... ";
        $result = $callback();
        echo "OK (Resultado: " . (is_scalar($result) ? $result : gettype($result)) . ")\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        // echo "Trace: " . $e->getTraceAsString() . "\n";
    }
}

test("User Count", fn() => \App\Models\User::count());
test("Role Count", fn() => \Spatie\Permission\Models\Role::count());
test("Permission Count", fn() => \Spatie\Permission\Models\Permission::count());

test("User-Roles Relationship", function() {
    $user = \App\Models\User::first();
    if (!$user) return "No user found";
    return $user->roles()->count();
});

test("Role-Permissions Relationship", function() {
    $role = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();
    if (!$role) return "No role found";
    return $role->permissions()->count();
});

test("Role-Users (morphedByMany) Relationship", function() {
    $role = \Spatie\Permission\Models\Role::first();
    if (!$role) return "No role found";
    // Forzamos la resolución de la relación users
    return $role->users()->count();
});
