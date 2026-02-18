<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login</title>
</head>

<body style="font-family: Arial; background:#111; color:#fff; display:flex; justify-content:center; align-items:center; height:100vh;">

    <form method="POST" action="{{ route('login.post') }}" style="width:360px; background:#1c1c1c; padding:24px; border-radius:12px;">
        @csrf

        <h2 style="margin:0 0 16px;">Iniciar sesión</h2>

        @if ($errors->any())
        <div style="background:#3b0a0a; padding:10px; border-radius:8px; margin-bottom:12px;">
            {{ $errors->first() }}
        </div>
        @endif

        <div style="margin-bottom:12px;">
            <label>Correo</label>
            <input type="email" name="correo" value="{{ old('correo') }}" required
                style="width:100%; padding:10px; border-radius:8px; border:1px solid #333; background:#0f0f0f; color:#fff;">
        </div>

        <div style="margin-bottom:12px;">
            <label>Contraseña</label>
            <input type="password" name="contrasena" required
                style="width:100%; padding:10px; border-radius:8px; border:1px solid #333; background:#0f0f0f; color:#fff;">
        </div>

        <div style="margin-bottom:12px;">
            <label>
                <input type="checkbox" name="remember">
                Recordarme
            </label>
        </div>

        <button type="submit" style="width:100%; padding:10px; border-radius:8px; border:none; cursor:pointer;">
            Entrar
        </button>
    </form>

</body>

</html>