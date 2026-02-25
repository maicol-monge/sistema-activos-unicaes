@extends('layouts.app')


@section('title', 'Acceso Restringido')

@section('content')
<style>
    /* Ocultar elementos del layout que no queremos ver en esta pantalla */
    .navbar, .navbar-custom, .navbar-toggler, .sticky-top { display: none !important; }
    .content-card { border: none !important; box-shadow: none !important; background: transparent !important; }
    .card-body.p-4 { padding: 0 !important; }
    main.container.my-4 { margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
    body { background-color: #f4f6f9 !important; }

    /* Contenedor del Icono */
    .icon-box {
        width: 120px;
        height: 120px;
        background: #fff5f5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin-bottom: 20px;
    }

    /* Efecto del candado sobre el escudo */
    .lock-overlay {
        position: absolute;
        bottom: 20px;
        right: 20px;
        font-size: 1.5rem;
        background: white;
        padding: 5px;
        border-radius: 50%;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Hover effects */
    .btn-primary:hover {
        background-color: var(--rojo-oscuro) !important;
        border-color: var(--rojo-oscuro) !important;
        transform: scale(1.02);
        transition: all 0.2s ease-in-out;
    }

    .btn-outline-dark:hover {
        background-color: #343a40;
        color: white;
        transform: scale(1.02);
        transition: all 0.2s ease-in-out;
    }

    .min-vh-100 {
        min-height: 100vh !important;
    }
</style>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 text-center">
            <div class="card border-0 shadow-lg" style="border-radius: 15px; border-top: 5px solid var(--rojo-oscuro) !important;">
                <div class="card-body p-5">
                    
                    <div class="mb-4">
                        <div class="icon-box mx-auto shadow-sm">
                            <i class="fa-solid fa-shield-halved fa-4x" style="color: var(--rojo-principal);"></i>
                            <i class="fa-solid fa-lock lock-overlay" style="color: var(--rojo-oscuro);"></i>
                        </div>
                    </div>

                    <h1 class="display-5 fw-bold mb-2" style="color: var(--rojo-oscuro);">Error 403</h1>
                    <h2 class="h4 fw-semibold text-uppercase mb-3" style="letter-spacing: 1px;">Acceso No Autorizado</h2>
                    
                    <div class="alert alert-light border-0 py-3 mb-4" style="background-color: #f8f9fa; border-left: 4px solid var(--rojo-principal) !important;">
                        <p class="mb-0 text-muted">
                            Lo sentimos, no tienes los permisos necesarios para acceder a este recurso.
                        </p>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ url('/') }}" class="btn btn-primary px-4 shadow-sm" 
                           style="background-color: var(--rojo-principal); border-color: var(--rojo-principal);">
                            <i class="fa-solid fa-house-user me-2"></i>Ir al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
