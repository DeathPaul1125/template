<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- DEPURACIÓN DE SPATIE GUARD MAPPING ---\n";

use Spatie\Permission\Guard;

try {
    $guard = config('auth.defaults.guard');
    echo "Default Guard: $guard\n";
    
    $model = \Spatie\Permission\PermissionRegistrar::getModels()->getRoleClass()->where('name', 'super-admin')->first();
    if ($model) {
        echo "Role find OK. Guard Name on Role: " . ($model->guard_name ?? 'NULL') . "\n";
        
        // Test internal Spatie helper if possible, or just the config
        $provider = config("auth.guards.{$guard}.provider");
        echo "Provider for guard $guard: " . ($provider ?? 'NULL') . "\n";
        
        $userModel = config("auth.providers.{$provider}.model");
        echo "User Model for provider $provider: " . ($userModel ?? 'NULL') . "\n";
        
        if (!$userModel) {
            echo "CRITICAL: User model is NULL for guard $guard\n";
        }
    } else {
        echo "No roles found in DB.\n";
    }

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
