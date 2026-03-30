<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class InstallerController extends Controller
{
    public function welcome()
    {
        $requirements = [
            'PHP >= 8.3' => PHP_VERSION_ID >= 80300,
            'BCMath Extension' => extension_loaded('bcmath'),
            'Ctype Extension' => extension_loaded('ctype'),
            'JSON Extension' => extension_loaded('json'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'PDO Extension' => extension_loaded('pdo'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
            'Storage Writable' => is_writable(storage_path()),
            '.env Writable' => is_writable(base_path('.env')),
        ];

        return view('installer.welcome', compact('requirements'));
    }

    public function database()
    {
        return view('installer.database');
    }

    public function saveDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
        ]);

        $this->updateEnv([
            'DB_HOST' => $request->db_host,
            'DB_PORT' => $request->db_port,
            'DB_DATABASE' => $request->db_database,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password ?? '',
        ]);

        // Intentar conectar
        try {
            DB::purge('mysql');
            config(['database.connections.mysql.database' => $request->db_database]);
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            // Error 1049: Unknown database
            if ($e->getCode() == 1049 || str_contains($e->getMessage(), 'Unknown database')) {
                try {
                    $pdo = new \PDO(
                        sprintf("mysql:host=%s;port=%s", $request->db_host, $request->db_port),
                        $request->db_username,
                        $request->db_password ?? ''
                    );
                    $pdo->exec(sprintf("CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;", $request->db_database));
                    
                    // Re-intentar conexión de Laravel
                    DB::purge('mysql');
                    DB::connection()->getPdo();
                } catch (\Exception $inner) {
                    return back()->withInput()->withErrors(['connection' => 'No se pudo crear la base de datos: ' . $inner->getMessage()]);
                }
            } else {
                return back()->withInput()->withErrors(['connection' => 'No se pudo conectar al servidor: ' . $e->getMessage()]);
            }
        }

        return redirect()->route('install.admin');
    }

    public function admin()
    {
        return view('installer.admin');
    }

    public function saveAdmin(Request $request)
    {
        $request->validate([
            'site_name' => 'required',
            'admin_name' => 'required',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:8|confirmed',
        ]);

        try {
            // 1. Migraciones
            Artisan::call('migrate:fresh', ['--force' => true]);

            // 2. Roles (Spatie) - asumiendo que los seeders existen o creándolos aquí
            $adminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
            
            // 3. Crear Usuario
            $user = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
            ]);
            $user->assignRole($adminRole);

            // 4. Guardar Configuración inicial
            Setting::set('site_name', $request->site_name);
            $this->updateEnv(['APP_NAME' => '"' . $request->site_name . '"']);

            // 5. Marcar como instalado
            file_put_contents(storage_path('installed'), date('Y-m-d H:i:s'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ocurrió un error: ' . $e->getMessage()]);
        }

        return redirect()->route('install.finalized');
    }

    public function finalized()
    {
        return view('installer.finalized');
    }

    private function updateEnv(array $data)
    {
        $envFile = base_path('.env');
        $lines = file($envFile);
        $newLines = [];

        foreach ($lines as $line) {
            $matched = false;
            foreach ($data as $key => $value) {
                if (strpos($line, $key . '=') === 0) {
                    $newLines[] = "{$key}={$value}\n";
                    $matched = true;
                    unset($data[$key]);
                    break;
                }
            }
            if (!$matched) {
                $newLines[] = $line;
            }
        }

        foreach ($data as $key => $value) {
            $newLines[] = "{$key}={$value}\n";
        }

        file_put_contents($envFile, implode('', $newLines));
    }
}
