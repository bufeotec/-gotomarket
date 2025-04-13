<div>
    @php
        $general = new \App\Models\General();
    @endphp

    <div class="row align-items-center justify-content-between">
        <div class="col-lg-8 col-md-8 col-sm-12">
            <div class="row align-items-center7">
                <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                    <label for="tipo_reporte" class="form-label">Tipo de reporte</label>
                    <select name="tipo_reporte" id="tipo_reporte" wire:model="tipo_reporte" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="1">F. Emisión</option>
                        <option value="2">F. Despacho</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                    <label class="form-label">Desde</label>
                    <input type="date" wire:model="xdesde" class="form-control" min="2025-01-01">
                      @error('xdesde')
                    <span class="message-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" wire:model="xhasta" class="form-control" min="2025-01-01">
                      @error('xhasta')
                    <span class="message-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-lg-3 col-md-3 col-sm-12 mt-4 mb-2">
                    <button class="btn btn-sm bg-primary text-white mt-2" wire:click="buscar_reporte_valor">
                        <i class="fa fa-search"></i> BUSCAR
                    </button>
                </div>
            </div>
        </div>
        @if(count($filteredData) > 0)
            <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2">
                <button class="btn btn-sm bg-success text-white" wire:click.prevent="exportarReporteValorExcel">
                    <i class="fa-solid fa-file-excel"></i> EXPORTAR
                </button>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="loader mt-2" wire:loading wire:target="buscar_reporte_valor"></div>
    </div>

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

    @if($searchdatos && count($filteredData) > 0)
        <div class="row mt-5">
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
                                @php
                                    $totalGeneralFlete = 0;
                                    $totalGeneralDetalle = 0;
                                    foreach($valoresPorzona as  $zon1){
                                        $totalGeneralFlete+= $zon1->total_despacho;
                                        $totalGeneralDetalle+= $zon1->total_detalles;
                                    }
                                    $por = $totalGeneralDetalle != 0 ? ($totalGeneralFlete / $totalGeneralDetalle) * 100 : 0
                                @endphp
                                <tr>
                                    <td>TOTAL</td>
                                    <td class="text-center">S/ {{ $general->formatoDecimal($totalGeneralFlete ?? 0)}}</td>
                                    <td class="text-center">S/ {{ $general->formatoDecimal($totalGeneralDetalle ?? 0)}}</td>
                                    <td class="text-center">{{ $general->formatoDecimal($por ?? 0) }}%</td>
                                    <td class="text-center">3.9%</td>
                                </tr>
                                @foreach($valoresPorzona as $indexZona => $zon)
                                    @php
                                        $zonaText = "";
                                        $zonaOb = "";
                                        if ($indexZona == 0){
                                            $zonaText = 'LOCAL';
                                            $zonaOb = 1.9;

                                        }elseif ($indexZona == 1){
                                            $zonaText = 'PROVINCIA 1';
                                            $zonaOb = 5.5;

                                        }elseif ($indexZona == 2){
                                            $zonaText = 'PROVINCIA 2';
                                            $zonaOb = 9.5;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $zonaText }}</td>
                                        <td class="text-center">S/ {{ $general->formatoDecimal($zon->total_despacho ?? 0)}}</td>
                                        <td class="text-center">S/ {{ $general->formatoDecimal($zon->total_detalles ?? 0)}}</td>
                                        <td class="text-center">{{ $general->formatoDecimal($zon->porcentaje ?? 0) }}%</td>
                                        <td class="text-center">{{ $zonaOb ?? 0 }}%</td>
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

    <script>
        // Variables globales para los gráficos
        let graficoFleteTotal = null;
        let graficoFleteLimaProvincia = null;

        // Función para inicializar o actualizar el gráfico de Flete Total
        function actualizarGraficoFleteTotal(meses,valores) {
            const canvas = document.getElementById('graficoFleteTotal');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoFleteTotal) {
                graficoFleteTotal.destroy();
            }

            // Crear nuevo gráfico
            graficoFleteTotal = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: meses,
                    datasets: [
                        {
                            label: 'Flete Total',
                            data: valores,
                            backgroundColor: 'rgba(169, 169, 169, 0.7)', // Gris
                            borderColor: 'rgba(169, 169, 169, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Obj. Total 2025',
                            data: Array(meses.length).fill(3.9), // Objetivo del 3.9%
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
        function actualizarGraficoFleteLimaProvincia(meses,lima,provincia) {
            const canvas = document.getElementById('graficoFleteLimaProvincia');
            if (!canvas) return;


            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoFleteLimaProvincia) {
                graficoFleteLimaProvincia.destroy();
            }

            // Crear nuevo gráfico
            graficoFleteLimaProvincia = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: meses,
                    datasets: [
                        {
                            label: 'Flete Lima',
                            data: lima,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)', // Azul
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Flete Provincia',
                            data: provincia,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)', // Naranja
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Obj. Lima',
                            data: Array(meses.length).fill(1.9), // Objetivo del 1.9%
                            borderColor: 'rgba(54, 162, 235, 1)', // Azul
                            borderWidth: 2,
                            borderDash: [5, 5],
                            type: 'line',
                            pointRadius: 0
                        },
                        {
                            label: 'Obj. Provincia',
                            data: Array(meses.length).fill(5.5), // Objetivo del 5.5%
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
                Livewire.on('actualizarGraficoTotal', function(data) {
                    setTimeout(() => {
                        try {
                            actualizarGraficoFleteTotal(data[0][0], data[0][1]);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de flete total:', error);
                        }
                    }, 100);
                });
                Livewire.on('actualizarGraficoFleteMes', function(data) {
                    setTimeout(() => {
                        try {
                            actualizarGraficoFleteLimaProvincia(data[0][0], data[0][1], data[0][2]);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de flete lima/provincia:', error);
                        }
                    }, 100);
                });
            }
        });
    </script>

</div>
