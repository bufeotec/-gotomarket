<div>
    @php
        $general = new \App\Models\General();
    @endphp
    <div class="row align-items-center">
        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <label class="form-label">F. Desde</label>
            <input type="date" wire:model.live="ydesde" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <label class="form-label">F. Hasta</label>
            <input type="date" wire:model.live="yhasta" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2">
            <button class="btn btn-sm bg-primary text-white" wire:click="buscar_reporte_peso">
                <i class="fa fa-search"></i> BUSCAR
            </button>
        </div>
        @if(count($filtrarData) > 0)
            <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2">
                <button class="btn btn-sm bg-success text-white" wire:click.prevent="exportarReportePesoExcel">
                    <i class="fa-solid fa-file-excel"></i> EXPORTAR
                </button>
            </div>
        @endif
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_reporte_peso"></div>
        </div>
    </div>

    @if($searchdatos && count($filtrarData) > 0)
        <div class="row mt-3">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <h6>REPORTE RESUMEN</h6>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>Zona</th>
                                    <th class="text-center">Flete (S/)</th>
                                    <th class="text-center">Peso Transportado (KG)</th>
                                    <th class="text-center">Indicador (S/Kg)</th>
                                    <th class="text-center">Objetivo</th>
                                </tr>
                            </x-slot>
                            <x-slot name="tbody">
                                @foreach(['Total', 'Local', 'Provincia 1', 'Provincia 2'] as $zona)
                                    <tr>
                                        <td>{{ $zona }}</td>
                                        <td class="text-center">{{ $general->formatoDecimal($summary[$zona]['flete'] ?? 0)}}</td>
                                        <td class="text-center">{{ $general->formatoDecimal($summary[$zona]['peso'] ?? 0)}}</td>
                                        <td class="text-center">{{ $general->formatoDecimal($summary[$zona]['indicador'] ?? 0) }}%</td>

                                        <td class="text-center">{{ number_format($summary[$zona]['objetivo'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </x-slot>
                        </x-table-general>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- GRAFICO PESO TOTAL DESPACHADO - TONELADAS -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">PESO TOTAL DESPACHADO - TONELADAS</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoPesoTonelada" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRAFICO DE FLETE SOLES X KILOS -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">FLETE SOLES X KILOS</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoFleteSolesKilo" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

    @elseif($searchdatos)
        <div class="alert alert-info text-center">
            No se encontraron resultados
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        // Variables globales para los gráficos
        let graficoPesoTonelada = null;
        let graficoFleteSolesKilo = null;

        // Función para inicializar o actualizar el gráfico de peso
        function actualizarGraficoPeso(data) {
            const canvas = document.getElementById('graficoPesoTonelada');
            if (!canvas) return;

            // Extraer el primer elemento si data es un array
            const chartData = Array.isArray(data) ? data[0] : data;

            // Verificar estructura de datos
            if (!chartData || !chartData.meses || !chartData.peso_local || !chartData.peso_provincia1 || !chartData.peso_provincia2) {
                console.error('Datos incorrectos para gráfico de peso:', chartData);
                return;
            }

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoPesoTonelada) {
                graficoPesoTonelada.destroy();
            }

            // Crear nuevo gráfico
            graficoPesoTonelada = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.meses,
                    datasets: [
                        {
                            label: 'Peso Lima (Ton)',
                            data: chartData.peso_local,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Peso Provincia 1 (Ton)',
                            data: chartData.peso_provincia1,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Peso Provincia 2 (Ton)',
                            data: chartData.peso_provincia2,
                            backgroundColor: 'rgba(169,169,169,0.27)',
                            borderColor: 'rgba(169, 169, 169, 1)',
                            borderWidth: 1
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
                                text: 'Toneladas'
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
                                    return context.dataset.label + ': ' + context.raw.toFixed(2) + ' Ton';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Función para inicializar o actualizar el gráfico de flete
        function actualizarGraficoFlete(data) {
            const canvas = document.getElementById('graficoFleteSolesKilo');
            if (!canvas) return;

            // Extraer el primer elemento si data es un array
            const chartData = Array.isArray(data) ? data[0] : data;

            // Verificar estructura de datos
            if (!chartData || !chartData.meses || !chartData.flete_local || !chartData.flete_provincia) {
                console.error('Datos incorrectos para gráfico de flete:', chartData);
                return;
            }

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoFleteSolesKilo) {
                graficoFleteSolesKilo.destroy();
            }

            // Crear nuevo gráfico
            graficoFleteSolesKilo = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.meses,
                    datasets: [
                        {
                            label: 'Soles x Kilo Lima',
                            data: chartData.flete_local,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: true
                        },
                        {
                            label: 'Soles x Kilo Prov.',
                            data: chartData.flete_provincia,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: true
                        },
                        {
                            label: 'Obj. Lima',
                            data: Array(chartData.meses.length).fill(0.12),
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            borderDash: [5, 5],
                            pointRadius: 0
                        },
                        {
                            label: 'Obj. Prov.',
                            data: Array(chartData.meses.length).fill(0.55),
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1,
                            borderDash: [5, 5],
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
                                text: 'Soles por Kilo'
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
                                    return context.dataset.label + ': ' + context.raw.toFixed(3);
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
                Livewire.on('actualizarGraficoPeso', function(data) {
                    setTimeout(() => {
                        try {
                            actualizarGraficoPeso(data);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de peso:', error);
                        }
                    }, 100);
                });

                Livewire.on('actualizarGraficoFlete', function(data) {
                    setTimeout(() => {
                        try {
                            actualizarGraficoFlete(data);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de flete:', error);
                        }
                    }, 100);
                });
            }
        });
    </script>

</div>
