<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema Activos - UNICAES')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --rojo-principal: #7e0001;
            /* rgb(126, 0, 1) */
            --rojo-oscuro: #5c0001;
            /* rgb(92, 0, 1) */
            --dorado: #edbd3f;
            /* rgb(237, 189, 63) */
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f4f6f9;
        }

        .navbar-custom {
            background-color: var(--rojo-principal);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar-custom .navbar-brand {
            color: var(--dorado);
            font-weight: 700;
            letter-spacing: 1px;
        }

        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 5px;
            border-radius: 5px;
        }

        .navbar-custom .nav-link:hover {
            color: var(--dorado);
            background-color: var(--rojo-oscuro);
        }

        .badge-rol {
            background-color: var(--dorado);
            color: var(--rojo-oscuro);
            font-weight: bold;
            font-size: 0.8em;
        }

        .btn-salir {
            background-color: transparent;
            color: white;
            border: 1px solid var(--dorado);
            transition: all 0.3s ease;
        }

        .btn-salir:hover {
            background-color: var(--dorado);
            color: var(--rojo-oscuro);
        }

        .content-card {
            border: none;
            border-top: 4px solid var(--rojo-principal);
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    @auth
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fa-solid fa-building-columns me-2"></i>UNICAES
            </a>

            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border-color: var(--dorado);">
                <i class="fa-solid fa-bars" style="color: var(--dorado);"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    @php $rol = auth()->user()->rol ?? null; @endphp

                    @if($rol === 'ADMIN')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarAdminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-gear me-1"></i> Administración
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarAdminDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('activos.index') }}">
                                    <i class="fa-solid fa-boxes-stacked me-1"></i> Inventario de Activos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('activos.aprobaciones') }}">
                                    <i class="fa-solid fa-check-double me-1"></i> Aprobaciones de Activos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('asignaciones.index') }}">
                                    <i class="fa-solid fa-clipboard-list me-1"></i> Asignaciones
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('bajas-activos.index') }}">
                                    <i class="fa-solid fa-circle-down me-1"></i> Solicitudes de Baja
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('users.index') }}">
                                    <i class="fa-solid fa-users me-1"></i> Usuarios
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('categorias-activos.index') }}">
                                    <i class="fa-solid fa-tags me-1"></i> Categorías
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @if($rol === 'INVENTARIADOR')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('activos.index') }}">
                            <i class="fa-solid fa-boxes-stacked me-1"></i> Activos
                        </a>
                    </li>
                    @endif

                    @if($rol === 'INVENTARIADOR')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('asignaciones.create') }}">
                            <i class="fa-solid fa-share-nodes me-1"></i> Asignar Activos
                        </a>
                    </li>
                    @endif

                    @if($rol === 'ADMIN')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarMisAdminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user-gear me-1"></i> Mis activos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarMisAdminDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('activos.mis') }}">
                                    <i class="fa-solid fa-laptop-file me-1"></i> Mis Activos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('asignaciones.mis') }}">
                                    <i class="fa-solid fa-clipboard-list me-1"></i> Mis Asignaciones
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('encargado.reportes.index') }}">
                                    <i class="fa-solid fa-clipboard-check me-1"></i> Reportar Estado
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('bajas-activos.create') }}">
                                    <i class="fa-solid fa-minus-circle me-1"></i> Solicitar Baja
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @if(in_array($rol, ['ENCARGADO', 'INVENTARIADOR', 'DECANO']))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('activos.mis') }}">
                            <i class="fa-solid fa-laptop-file me-1"></i> Mis Activos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('asignaciones.mis') }}">
                            <i class="fa-solid fa-clipboard-list me-1"></i> Mis Asignaciones
                        </a>
                    </li>
                    @endif

                    @if(in_array($rol, ['ENCARGADO', 'DECANO']))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('encargado.reportes.index') }}">
                            <i class="fa-solid fa-clipboard-check me-1"></i> Reportar Estado
                        </a>
                    </li>
                    @endif

                    @if(in_array($rol, ['ENCARGADO', 'INVENTARIADOR', 'DECANO']))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('bajas-activos.create') }}">
                            <i class="fa-solid fa-minus-circle me-1"></i> Solicitar Baja
                        </a>
                    </li>
                    @endif

                    @if($rol === 'DECANO')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reportes.index') }}">
                            <i class="fa-solid fa-chart-line me-1"></i> Reportes y Consultas
                        </a>
                    </li>
                    @endif
                </ul>

                <div class="d-flex align-items-center text-white">
                    <div class="me-3 text-end">
                        <span class="d-block" style="font-size: 0.9em;">
                            Hola, <strong>{{ auth()->user()->nombre }}</strong>
                        </span>
                        <span class="badge badge-rol">
                            {{ auth()->user()->rol }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="m-0 logout-form">
                        @csrf
                        <button type="button" class="btn btn-sm btn-salir btn-logout">
                            <i class="fa-solid fa-right-from-bracket"></i> Salir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <main class="container my-4">
        <div class="card content-card">
            <div class="card-body p-4">
                @yield('content')
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @php
    $firstError = $errors->first();
    @endphp


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ok = @json(session('ok'));
            const err = @json(session('err'));
            const firstError = @json($firstError);

            if (ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: ok,
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            if (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err,
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            if (firstError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: firstError,
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            document.querySelectorAll('.btn-logout').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Cerrar sesión?',
                        text: '¿Estás seguro que deseas cerrar la sesión?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, cerrar sesión',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = btn.closest('form');
                            if (form) form.submit();
                        }
                    });
                });
            });
        });
    </script>

    @stack('scripts')

</body>

</html>