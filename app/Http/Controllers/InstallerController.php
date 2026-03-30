<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class InstallerController extends Controller
{
    public function welcome()
    {
        // El .env ya fue creado en public/index.php si no existía.
        // Solo verificamos que la clave no esté vacía (caso edge donde .env existía sin clave).
        $envFile = base_path('.env');
        if (file_exists($envFile) && empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        $requirements = [
            'PHP >= 8.3'          => PHP_VERSION_ID >= 80300,
            'BCMath Extension'    => extension_loaded('bcmath'),
            'Ctype Extension'     => extension_loaded('ctype'),
            'JSON Extension'      => extension_loaded('json'),
            'Mbstring Extension'  => extension_loaded('mbstring'),
            'OpenSSL Extension'   => extension_loaded('openssl'),
            'PDO Extension'       => extension_loaded('pdo'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension'       => extension_loaded('xml'),
            'Storage Writable'    => is_writable(storage_path()),
            '.env Writable'       => file_exists($envFile)
                                         ? is_writable($envFile)
                                         : is_writable(base_path()),
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

        // Actualizar configuración en memoria (el .env cambió pero este request aún tiene los valores viejos)
        DB::purge('mysql');
        config([
            'database.connections.mysql.host'     => $request->db_host,
            'database.connections.mysql.port'     => $request->db_port,
            'database.connections.mysql.database' => $request->db_database,
            'database.connections.mysql.username' => $request->db_username,
            'database.connections.mysql.password' => $request->db_password ?? '',
        ]);

        // Intentar conectar
        try {
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

                    // Re-intentar conexión de Laravel con la base de datos recién creada
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
            'site_name'    => 'required',
            'app_url'      => 'required|url',
            'admin_name'   => 'required',
            'admin_email'  => 'required|email',
            'admin_password' => 'required|min:8|confirmed',
        ]);

        $log = [];

        try {
            // 0. Limpiar config cache para asegurar que se lean las nuevas credenciales del .env
            Artisan::call('config:clear');

            // 1. Migraciones
            Artisan::call('migrate:fresh', ['--force' => true]);
            $migrateOutput = trim(Artisan::output());
            $log['migrations'] = [
                'success' => true,
                'lines'   => array_values(array_filter(explode("\n", $migrateOutput))),
            ];

            // 2. Roles y permisos completos via seeder
            Artisan::call('db:seed', ['--class' => 'RolesPermissionsSeeder', '--force' => true]);
            $log['roles'] = ['success' => true];

            // 3. Crear usuario administrador del wizard (eliminar el demo si coincide el email)
            User::where('email', $request->admin_email)->delete();
            $user = User::create([
                'name'     => $request->admin_name,
                'email'    => $request->admin_email,
                'password' => Hash::make($request->admin_password),
            ]);
            $user->assignRole('super-admin');
            $log['admin'] = ['success' => true, 'email' => $request->admin_email];

            // 4. Guardar Configuración inicial
            Setting::set('site_name', $request->site_name);
            $this->updateEnv([
                'APP_NAME'            => '"' . $request->site_name . '"',
                'APP_URL'             => $request->app_url,
                'APP_LOCALE'          => 'es',
                'APP_FALLBACK_LOCALE' => 'es',
                'APP_FAKER_LOCALE'    => 'es_ES',
                'SESSION_DRIVER'      => 'database',
            ]);
            $log['settings'] = ['success' => true, 'site_name' => $request->site_name, 'app_url' => $request->app_url];

            // 5. Marcar como instalado
            $installedAt = date('Y-m-d H:i:s');
            file_put_contents(storage_path('installed'), $installedAt);
            $log['installed_at'] = $installedAt;

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ocurrió un error: ' . $e->getMessage()]);
        }

        return redirect()->route('install.finalized')->with('install_log', $log);
    }

    public function finalized()
    {
        $log = session('install_log', []);
        return view('installer.finished', compact('log'));
    }

    private function updateEnv(array $data)
    {
        $envFile = base_path('.env');

        // Si por alguna razón no existe, partir de .env.example
        if (!file_exists($envFile)) {
            copy(base_path('.env.example'), $envFile);
        }

        $lines    = file($envFile);
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
