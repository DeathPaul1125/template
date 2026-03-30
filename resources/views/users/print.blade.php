<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lista de Usuarios</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid {{ \App\Models\Setting::get('brand_color', '#6366f1') }}; pb: 10px; }
        .logo { height: 50px; margin-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #eee; padding: 10px; text-align: left; }
        th { background-color: {{ \App\Models\Setting::get('brand_color', '#6366f1') }}; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        @if($logo = \App\Models\Setting::get('site_logo'))
            {{-- Para DOMPDF, rutas absolutas en el disco son mejores pero probaremos con la URL --}}
            <img src="{{ public_path(str_replace('/storage', 'storage', parse_url($logo, PHP_URL_PATH))) }}" class="logo">
        @endif
        <div class="title">{{ \App\Models\Setting::get('site_name', config('app.name')) }}</div>
        <div class="subtitle">Reporte General de Usuarios — {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Fecha Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->getRoleNames()->first() ?? 'Sin rol' }}</td>
                <td>{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado automáticamente por {{ \App\Models\Setting::get('site_name', config('app.name')) }}
    </div>
</body>
</html>
