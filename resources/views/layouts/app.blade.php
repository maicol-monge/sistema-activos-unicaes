<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Sistema Activos')</title>
</head>

<body>

    @if(auth()->check())
    <nav style="padding:12px; border-bottom:1px solid #ddd;">
        <b>UNICAES</b> |
        Hola, {{ auth()->user()->nombre }} |
        Rol: {{ auth()->user()->rol }}

        <span style="margin-left:20px;">
            <a href="{{ route('dashboard') }}">Dashboard</a>

            @if(auth()->user()->rol === 'ADMIN')
            | <a href="{{ route('users.index') }}">Usuarios</a>
            @endif

            @if(auth()->user()->rol === 'INVENTARIADOR')
            | <a href="{{ route('inventario.index') }}">Inventario</a>
            @endif

            @if(auth()->user()->rol === 'ENCARGADO')
            | <a href="{{ route('activos.mis') }}">Mis Activos</a>
            @endif

            @if(auth()->user()->rol === 'DECANO')
            | <a href="{{ route('reportes.index') }}">Reportes</a>
            @endif
        </span>

        <form method="POST" action="{{ route('logout') }}" style="display:inline; margin-left:20px;">
            @csrf
            <button type="submit">Salir</button>
        </form>
    </nav>
    @endif

    <main style="padding:16px;">
        @yield('content')
    </main>

</body>

</html>