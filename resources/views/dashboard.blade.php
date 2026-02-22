@extends('layouts.app')

@section('title', 'Dashboard - UNICAES')

@section('content')

<style>
    .card-module {
        transition: all 0.3s ease;
        border: none;
        border-bottom: 4px solid transparent;
        border-radius: 10px;
        background-color: white;
    }

    .card-module:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
        border-bottom: 4px solid var(--rojo-principal);
    }

    .icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(237, 189, 63, 0.15);
        color: var(--rojo-oscuro);
    }
</style>

@php $rol = auth()->user()->rol; @endphp

<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, var(--rojo-principal) 0%, var(--rojo-oscuro) 100%); border-radius: 12px;">
    <div class="card-body p-4 p-md-5 d-flex align-items-center flex-wrap">
        <div class="me-4 d-none d-md-block">
            <i class="fa-solid fa-building-columns fa-4x" style="color: var(--dorado);"></i>
        </div>
        <div class="text-white">
            <h2 class="fw-bold mb-1">¡Bienvenido, {{ auth()->user()->nombre }}!</h2>
            <p class="mb-0" style="color: rgba(255,255,255,0.8);">Sistema de Control de Activos UNICAES</p>
        </div>
        <div class="ms-auto mt-3 mt-md-0">
            <span class="badge fs-6 py-2 px-3 shadow-sm" style="background-color: var(--dorado); color: var(--rojo-oscuro); border: 1px solid #dca72c;">
                <i class="fa-solid fa-id-badge me-1"></i> Rol: {{ $rol }}
            </span>
        </div>
    </div>
</div>

<h5 class="fw-bold mb-3" style="color: var(--rojo-oscuro);">
    <i class="fa-solid fa-layer-group me-2"></i> Módulos Disponibles
</h5>

<div class="row g-4">

    @if($rol === 'ADMIN')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('users.index') }}" class="text-decoration-none text-dark">
            <div class="card card-module shadow-sm h-100 p-3">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="fa-solid fa-users-gear fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Gestión de Usuarios</h5>
                        <p class="text-muted mb-0" style="font-size: 0.85em;">Administrar accesos, roles y cuentas del sistema.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('categorias-activos.index') }}" class="text-decoration-none text-dark">
            <div class="card card-module shadow-sm h-100 p-3">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="fa-solid fa-tags fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Categorías</h5>
                        <p class="text-muted mb-0" style="font-size: 0.85em;">Administrar categorías de activos institucionales.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('activos.aprobaciones') }}" class="text-decoration-none text-dark">
            <div class="card card-module shadow-sm h-100 p-3">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="fa-solid fa-circle-check fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Aprobaciones</h5>
                        <p class="text-muted mb-0" style="font-size: 0.85em;">Revisar y aprobar activos pendientes.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    @elseif($rol === 'INVENTARIADOR')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('activos.index') }}" class="text-decoration-none text-dark">
            <div class="card card-module shadow-sm h-100 p-3">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="fa-solid fa-boxes-stacked fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Inventario General</h5>
                        <p class="text-muted mb-0" style="font-size: 0.85em;">Registrar, clasificar y gestionar todos los activos.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('asignaciones.index') }}" class="text-decoration-none text-dark">
            <div class="card card-module shadow-sm h-100 p-3">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="fa-solid fa-clipboard-list fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Asignaciones</h5>
                        <p class="text-muted mb-0" style="font-size: 0.85em;">Gestionar asignaciones de activos a encargados.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    @elseif($rol === 'ENCARGADO')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('activos.mis') }}" class="text-decoration-none text-dark">
            <div class="card card-module shadow-sm h-100 p-3">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="fa-solid fa-laptop-file fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Mis Activos</h5>
                        <p class="text-muted mb-0" style="font-size: 0.85em;">Consultar el estado de los bienes bajo tu responsabilidad.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('encargado.reportes.index') }}" class="text-decoration-none text-dark">
            <div class="card card-module shadow-sm h-100 p-3">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="fa-solid fa-clipboard-check fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Reportar Estado</h5>
                        <p class="text-muted mb-0" style="font-size: 0.85em;">Informar estado y consultar historial por activo.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    @elseif($rol === 'DECANO')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reportes.index') }}" class="text-decoration-none text-dark">
            <div class="card card-module shadow-sm h-100 p-3">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="fa-solid fa-chart-line fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Reportes y Consultas</h5>
                        <p class="text-muted mb-0" style="font-size: 0.85em;">Generar estadísticas y reportes ejecutivos del inventario.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    @else
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-center shadow-sm" role="alert" style="border-left: 4px solid var(--dorado);">
            <i class="fa-solid fa-triangle-exclamation fa-2x me-3" style="color: var(--rojo-principal);"></i>
            <div>
                <strong>Rol no reconocido.</strong><br>
                No tienes módulos asignados. Por favor, contacta al administrador del sistema.
            </div>
        </div>
    </div>
    @endif

</div>

@endsection