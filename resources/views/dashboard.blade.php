<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
</head>

<body>
    <h1>Dashboard</h1>

    <p>Bienvenido: {{ auth()->user()->name ?? auth()->user()->email }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Cerrar sesión</button>
    </form>
</body>

</html>