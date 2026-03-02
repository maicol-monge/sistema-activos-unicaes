@extends('layouts.app')

@section('title', 'Reportes de Estado - Administración - UNICAES')

@section('content')

<style>
    .btn-nuevo {
        background-color: var(--dorado);
        color: var(--rojo-oscuro);
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-nuevo:hover {
        background-color: #dca72c;
        color: var(--rojo-oscuro);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(237, 189, 63, 0.4);
    }

    .table-custom th {
        background-color: var(--rojo-principal);
        color: var(--dorado);
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
    }

    .table-custom td {
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .table-custom tbody tr:hover {
        background-color: #fdfaf3;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1" style="color: var(--rojo-principal); font-weight: 700;">
            <i class="fa-solid fa-clipboard-check me-2"></i> Reportes de Estado de Activos
        </h2>
        <p class="mb-0 text-muted">Vista consolidada de los reportes registrados por los responsables de activos.</p>
    </div>
    <a href="{{ route('reportes-activos.pdf', request()->query()) }}" class="btn btn-nuevo shadow-sm">
        <i class="fa-solid fa-file-pdf me-1"></i> Descargar PDF
    </a>
</div>

@if(!empty($charts ?? []))
<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <h5 class="fw-bold mb-3" style="color: var(--rojo-oscuro);">
            <i class="fa-solid fa-chart-pie me-2"></i> Estadísticas globales del sistema
        </h5>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Activos por categoría</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartActivosPorCategoria" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartActivosPorCategoria" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartActivosPorCategoria"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Activos por estado</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartActivosPorEstado" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartActivosPorEstado" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartActivosPorEstado"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Activos por condición</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartActivosPorCondicion" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartActivosPorCondicion" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartActivosPorCondicion"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Reportes por estado</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartReportesPorEstado" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartReportesPorEstado" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartReportesPorEstado"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Reportes por mes</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartReportesPorMes" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartReportesPorMes" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartReportesPorMes"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Bajas por estado</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartBajasPorEstado" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartBajasPorEstado" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartBajasPorEstado"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Asignaciones por estado</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartAsignacionesPorEstado" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartAsignacionesPorEstado" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartAsignacionesPorEstado"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Valor total por categoría</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartValorPorCategoria" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartValorPorCategoria" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartValorPorCategoria"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Distribución de valor de activos</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartHistogramaValorActivos" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartHistogramaValorActivos" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartHistogramaValorActivos"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Valor del activo vs reportes</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartScatterValorVsReportes" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartScatterValorVsReportes" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartScatterValorVsReportes"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('reportes-activos.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Búsqueda</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="q" class="form-control" value="{{ $filtros['q'] ?? '' }}" placeholder="Activo o responsable...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted fw-bold mb-1">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ $filtros['fecha_desde'] ?? '' }}" min="1982-04-13" max="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted fw-bold mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ $filtros['fecha_hasta'] ?? '' }}" min="1982-04-13" max="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted fw-bold mb-1">Estado</label>
                <select name="estado_reporte" class="form-select">
                    <option value="">Todos</option>
                    <option value="BUENO" @selected(($filtros['estado_reporte'] ?? '') === 'BUENO')>BUENO</option>
                    <option value="DANIADO" @selected(($filtros['estado_reporte'] ?? '') === 'DANIADO')>DAÑADO</option>
                    <option value="PERDIDO" @selected(($filtros['estado_reporte'] ?? '') === 'PERDIDO')>PERDIDO</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <a href="{{ route('reportes-activos.index') }}" class="btn btn-light border">
                    <i class="fa-solid fa-broom me-1"></i> Limpiar
                </a>
                <button type="submit" class="btn btn-nuevo">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom table-hover mb-0">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Activo</th>
                <th>Código</th>
                <th>Estado reportado</th>
                <th>Responsable</th>
                <th>Comentario</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportes as $reporte)
            <tr>
                <td>{{ \Carbon\Carbon::parse($reporte->fecha)->format('d/m/Y') }}</td>
                <td>{{ $reporte->activo?->nombre ?? 'N/A' }}</td>
                <td>{{ $reporte->activo?->codigo ?? 'N/A' }}</td>
                <td>
                    @if($reporte->estado_reporte === 'BUENO')
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">BUENO</span>
                    @elseif($reporte->estado_reporte === 'DANIADO')
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">DAÑADO</span>
                    @else
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">PERDIDO</span>
                    @endif
                </td>
                <td>{{ $reporte->usuario?->nombre ?? 'N/A' }}</td>
                <td>{{ $reporte->comentario }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No hay reportes que coincidan con los filtros seleccionados.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $reportes->links() }}
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
    (function () {
        const charts = @json($charts ?? []);
        if (!charts || Object.keys(charts).length === 0) return;

        function createChart(id, type, data, title, extraOptions = {}) {
            const el = document.getElementById(id);
            if (!el) return;
            const baseOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: !!title, text: title }
                }
            };
            const options = Object.assign(baseOptions, extraOptions || {});
            new Chart(el.getContext('2d'), {
                type,
                data,
                options
            });
        }

        const palette = [
            '#8B0000', '#C9A84C', '#198754', '#0d6efd', '#fd7e14',
            '#20c997', '#6f42c1', '#6610f2', '#e83e8c', '#ffc107'
        ];

        createChart('chartActivosPorCategoria', 'bar', {
            labels: charts.activosPorCategoria.labels,
            datasets: [{
                label: 'Activos',
                data: charts.activosPorCategoria.data,
                backgroundColor: palette,
            }]
        }, 'Activos por categoría');

        createChart('chartActivosPorEstado', 'pie', {
            labels: charts.activosPorEstado.labels,
            datasets: [{
                data: charts.activosPorEstado.data,
                backgroundColor: palette,
            }]
        }, 'Activos por estado');

        createChart('chartActivosPorCondicion', 'doughnut', {
            labels: charts.activosPorCondicion.labels,
            datasets: [{
                data: charts.activosPorCondicion.data,
                backgroundColor: palette,
            }]
        }, 'Activos por condición');

        createChart('chartReportesPorEstado', 'bar', {
            labels: charts.reportesPorEstado.labels,
            datasets: [{
                label: 'Reportes',
                data: charts.reportesPorEstado.data,
                backgroundColor: palette,
            }]
        }, 'Reportes por estado');

        createChart('chartReportesPorMes', 'line', {
            labels: charts.reportesPorMes.labels,
            datasets: [{
                label: 'Reportes por mes',
                data: charts.reportesPorMes.data,
                fill: false,
                borderColor: '#8B0000',
                tension: 0.2,
            }]
        }, 'Reportes por mes', { scales: { y: { beginAtZero: true } } });

        createChart('chartBajasPorEstado', 'bar', {
            labels: charts.bajasPorEstado.labels,
            datasets: [{
                label: 'Bajas',
                data: charts.bajasPorEstado.data,
                backgroundColor: palette,
            }]
        }, 'Bajas por estado');

        createChart('chartAsignacionesPorEstado', 'bar', {
            labels: charts.asignacionesPorEstado.labels,
            datasets: [{
                label: 'Asignaciones',
                data: charts.asignacionesPorEstado.data,
                backgroundColor: palette,
            }]
        }, 'Asignaciones por estado', { indexAxis: 'y' });

        createChart('chartValorPorCategoria', 'bar', {
            labels: charts.valorPorCategoria.labels,
            datasets: [{
                label: 'Valor total (USD)',
                data: charts.valorPorCategoria.data,
                backgroundColor: palette,
            }]
        }, 'Valor total por categoría', { scales: { y: { beginAtZero: true } } });

        createChart('chartHistogramaValorActivos', 'bar', {
            labels: charts.histogramaValorActivos.labels,
            datasets: [{
                label: 'Número de activos',
                data: charts.histogramaValorActivos.data,
                backgroundColor: '#8B0000',
            }]
        }, 'Distribución de valor de activos', { scales: { y: { beginAtZero: true } } });

        createChart('chartScatterValorVsReportes', 'scatter', {
            datasets: [{
                label: 'Activos',
                data: charts.scatterValorVsReportes.data,
                backgroundColor: '#C9A84C',
            }]
        }, 'Valor del activo vs reportes', {
            scales: {
                x: { title: { display: true, text: 'Valor de compra (USD)' } },
                y: { title: { display: true, text: 'Cantidad de reportes' }, beginAtZero: true }
            }
        });

        function downloadPng(canvasId, filename) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png', 1.0);
            link.download = filename || (canvasId + '.png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function downloadPdf(canvasId, filename) {
            const canvas = document.getElementById(canvasId);
            if (!canvas || !window.jspdf) return;
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('landscape', 'pt', 'a4');
            const imgData = canvas.toDataURL('image/png', 1.0);
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 20;
            const availableWidth = pageWidth - margin * 2;
            const availableHeight = pageHeight - margin * 2;
            const imgWidth = availableWidth;
            const imgHeight = canvas.height * (imgWidth / canvas.width);
            const finalHeight = imgHeight > availableHeight ? availableHeight : imgHeight;
            pdf.addImage(imgData, 'PNG', margin, margin, imgWidth, finalHeight);
            pdf.save(filename || (canvasId + '.pdf'));
        }

        document.querySelectorAll('.btn-download-png').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.target;
                downloadPng(id, id + '.png');
            });
        });

        document.querySelectorAll('.btn-download-pdf').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.target;
                downloadPdf(id, id + '.pdf');
            });
        });
    })();
</script>
@endpush
