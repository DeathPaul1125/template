<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Mostrar el formulario de configuración.
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Guardar las configuraciones generales.
     */
    public function update(Request $request)
    {
        $request->validate([
            'site_name'    => 'nullable|string|max:50',
            'site_logo'    => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'login_bg'     => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'site_favicon' => 'nullable|image|mimes:png,ico,svg|max:512',
            'brand_color'  => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            // Nota: se usa array para evitar que el | del regex se interprete como separador de reglas
        ]);

        // Guardar valores de texto
        if ($request->has('site_name')) {
            Setting::set('site_name', $request->site_name);
        }

        if ($request->has('brand_color')) {
            Setting::set('brand_color', $request->brand_color);
        }

        // Manejar subida de Logo
        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('settings', 'public');
            Setting::set('site_logo', Storage::url($path));
        }

        // Manejar subida de imagen de Login
        if ($request->hasFile('login_bg')) {
            $path = $request->file('login_bg')->store('settings', 'public');
            Setting::set('login_bg', Storage::url($path));
        }

        // Manejar subida de Favicon
        if ($request->hasFile('site_favicon')) {
            $path = $request->file('site_favicon')->store('settings', 'public');
            Setting::set('site_favicon', Storage::url($path));
        }

        return redirect()->back()->with('success', 'Configuración actualizada correctamente.');
    }
}
