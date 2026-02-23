@extends('layouts.app')

@section('title', 'Página No Encontrada')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-7 text-center">
            <div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div style="height: 8px; background-color: var(--rojo-principal);"></div>
                
                <div class="card-body p-5">
                    <div class="error-code-container mb-4">
                        <h1 class="display-1 fw-black floating-text" style="color: var(--rojo-principal); font-size: 8rem;">404</h1>
                        <div class="error-shadow"></div>
                    </div>

                    <h2 class="fw-bold mb-3" style="color: var(--rojo-oscuro);">¡Vaya! Parece que te has perdido</h2>
                    
                    <p class="text-muted mb-5 px-md-5">
                        La página que buscas no está disponible.
                    </p>

                    <div class="d-grid d-sm-flex justify-content-sm-center gap-3">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                            <i class="fa-solid fa-arrow-left me-2"></i>
                            Regresar
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm" 
                           style="background-color: var(--rojo-principal); border-color: var(--rojo-principal);">
                            <i class="fa-solid fa-house me-2"></i>
                            Ir al Panel Principal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Animación de flotado para el texto 404 */
    .floating-text {
        animation: float 4s ease-in-out infinite;
        text-shadow: 10px 10px 20px rgba(0,0,0,0.05);
        font-weight: 900;
        letter-spacing: -5px;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }

    /* Sombra dinámica debajo del número */
    .error-code-container {
        position: relative;
        display: inline-block;
    }

    .error-shadow {
        width: 100%;
        height: 15px;
        background: rgba(0,0,0,0.1);
        border-radius: 50%;
        margin-top: -10px;
        filter: blur(5px);
        animation: shadow-scale 4s ease-in-out infinite;
    }

    @keyframes shadow-scale {
        0%, 100% { transform: scale(0.8); opacity: 0.3; }
        50% { transform: scale(1.2); opacity: 0.1; }
    }

    /* Hover de botones */
    .btn-primary:hover {
        background-color: var(--rojo-oscuro) !important;
        border-color: var(--rojo-oscuro) !important;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    /* Estilo extra para el contenedor */
    .min-vh-100 {
        min-height: 100vh !important;
    }
</style>
@endpush