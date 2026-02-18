@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1>Dashboard</h1>

@php $rol = auth()->user()->rol; @endphp

@if($rol === 'ADMIN')
<p>Panel de administración: gestión de usuarios, encargados, etc.</p>

@elseif($rol === 'INVENTARIADOR')
<p>Panel de inventariador: registro y gestión de activos.</p>

@elseif($rol === 'ENCARGADO')
<p>Panel de encargado: ver activos asignados y su estado.</p>

@elseif($rol === 'DECANO')
<p>Panel de decano: reportes y consultas.</p>

@else
<p>Rol no reconocido.</p>
@endif
@endsection