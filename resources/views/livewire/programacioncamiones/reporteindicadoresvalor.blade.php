<div>
    @php
        $general = new \App\Models\General();
    @endphp
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible show fade mt-2">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row align-items-center">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label class="form-label">F. Desde (Aprobación)</label>
            <input type="date" wire:model.live="xdesde" class="form-control" min="2025-01-01">
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label class="form-label">F. Hasta (Aprobación)</label>
            <input type="date" wire:model.live="xhasta" class="form-control" min="2025-01-01">
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2">
            <button class="btn btn-sm bg-primary text-white" wire:click="buscar_reporte_valor">
                <i class="fa fa-search"></i> BUSCAR
            </button>
        </div>

        @if(count($filteredData) > 0)
            <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2">
                <button class="btn btn-sm bg-success text-white" wire:click.prevent="exportarReporteValorExcel">
                    <i class="fa-solid fa-file-excel"></i> EXPORTAR
                </button>
            </div>
        @endif

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_reporte_valor"></div>
        </div>
    </div>

    @if($searchdatos && count($filteredData) > 0)
        <div class="row mt-3">
            <div class="col-lg-12">
                <h6>REPORTE RESUMEN DE DESPACHOS</h6>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>Zona de Despacho</th>
                                    <th class="text-center">Flete (S/)</th>
                                    <th class="text-center">Valor Transportado (S/)</th>
                                    <th class="text-center">Indicador (%)</th>
                                    <th class="text-center">Objetivo (%)</th>
                                </tr>
                            </x-slot>
                            <x-slot name="tbody">
                                @foreach(['Total', 'Local', 'Provincia 1', 'Provincia 2'] as $zona)
                                    <tr>
                                        <td>{{ $zona }}</td>
                                        <td class="text-center">S/ {{ $general->formatoDecimal($summary[$zona]['flete'] ?? 0)}}</td>
                                        <td class="text-center">S/ {{ $general->formatoDecimal($summary[$zona]['valor'] ?? 0)}}</td>
                                        <td class="text-center">{{ $general->formatoDecimal($summary[$zona]['porcentaje'] ?? 0) }}%</td>
                                        <td class="text-center">{{ $objetivos[$zona] ?? 0 }}%</td>
                                    </tr>
                                @endforeach
                            </x-slot>
                        </x-table-general>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- GRAFICO FLETE TOTAL -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">FLETE TOTAL</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoFleteTotal" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRAFICO DE FLETE LIMA Y PROVINCIA -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">FLETE LIMA Y PROVINCIA</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoFleteLimaProvincia" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

    @elseif($searchdatos)
        <div class="alert alert-info text-center">
            No se encontraron resultados con los filtros aplicados
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        // Variables globales para los gráficos
        let graficoFleteTotal = null;
        let graficoFleteLimaProvincia = null;

        // Función para inicializar o actualizar el gráfico de Flete Total
        function actualizarGraficoFleteTotal(data) {
            const canvas = document.getElementById('graficoFleteTotal');
            if (!canvas) return;

            // Extraer el primer elemento si data es un array
            const chartData = Array.isArray(data) ? data[0] : data;

            // Verificar estructura de datos
            if (!chartData || !chartData.meses || !chartData.flete_total) {
                console.error('Datos incorrectos para gráfico de flete total:', chartData);
                return;
            }

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoFleteTotal) {
                graficoFleteTotal.destroy();
            }

            // Crear nuevo gráfico
            graficoFleteTotal = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.meses,
                    datasets: [
                        {
                            label: 'Flete Total',
                            data: chartData.flete_total,
                            backgroundColor: 'rgba(169, 169, 169, 0.7)', // Gris
                            borderColor: 'rgba(169, 169, 169, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Obj. Total 2025',
                            data: Array(chartData.meses.length).fill(3.9), // Objetivo del 3.9%
                            borderColor: 'rgba(255, 206, 86, 1)', // Amarillo
                            borderWidth: 2,
                            borderDash: [5, 5],
                            type: 'line',
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Soles'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('es-PE');
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Meses'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'S/ ' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Función para inicializar o actualizar el gráfico de Flete Lima y Provincia
        function actualizarGraficoFleteLimaProvincia(data) {
            const canvas = document.getElementById('graficoFleteLimaProvincia');
            if (!canvas) return;

            // Extraer el primer elemento si data es un array
            const chartData = Array.isArray(data) ? data[0] : data;

            // Verificar estructura de datos
            if (!chartData || !chartData.meses || !chartData.flete_lima || !chartData.flete_provincia) {
                console.error('Datos incorrectos para gráfico de flete lima/provincia:', chartData);
                return;
            }

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoFleteLimaProvincia) {
                graficoFleteLimaProvincia.destroy();
            }

            // Crear nuevo gráfico
            graficoFleteLimaProvincia = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.meses,
                    datasets: [
                        {
                            label: 'Flete Lima',
                            data: chartData.flete_lima,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)', // Azul
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Flete Provincia',
                            data: chartData.flete_provincia,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)', // Naranja
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Obj. Lima',
                            data: Array(chartData.meses.length).fill(1.9), // Objetivo del 1.9%
                            borderColor: 'rgba(54, 162, 235, 1)', // Azul
                            borderWidth: 2,
                            borderDash: [5, 5],
                            type: 'line',
                            pointRadius: 0
                        },
                        {
                            label: 'Obj. Provincia',
                            data: Array(chartData.meses.length).fill(5.5), // Objetivo del 5.5%
                            borderColor: 'rgba(255, 159, 64, 1)', // Naranja
                            borderWidth: 2,
                            borderDash: [5, 5],
                            type: 'line',
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Soles'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('es-PE');
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Meses'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'S/ ' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Inicialización cuando el DOM está listo
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar eventos Livewire
            if (typeof Livewire !== 'undefined') {
                Livewire.on('actualizarGraficoFleteTotal', function(data) {
                    setTimeout(() => {
                        try {
                            actualizarGraficoFleteTotal(data);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de flete total:', error);
                        }
                    }, 100);
                });

                Livewire.on('actualizarGraficoFleteLimaProvincia', function(data) {
                    setTimeout(() => {
                        try {
                            actualizarGraficoFleteLimaProvincia(data);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de flete lima/provincia:', error);
                        }
                    }, 100);
                });
            }
        });
    </script>

</div>
