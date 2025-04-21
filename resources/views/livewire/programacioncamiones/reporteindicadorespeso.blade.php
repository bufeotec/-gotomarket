<div>
    @php
        $general = new \App\Models\General();
    @endphp
    <div class="row align-items-center justify-content-between">
        <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
            <label for="tipo_reporte" class="form-label">Tipo de reporte</label>
            <select name="tipo_reporte" id="tipo_reporte" wire:model="tipo_reporte" class="form-select">
                <option value="">Seleccionar...</option>
                <option value="1">F. Emisión</option>
                <option value="2">F. Despacho</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-12 mb-2">
            <label class="form-label">F. Desde</label>
            <input type="date" wire:model="ydesde" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-4 col-sm-12 mb-2">
            <label class="form-label">F. Hasta</label>
            <input type="date" wire:model="yhasta" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-4 col-sm-12 mt-4 mb-2">
            <button class="btn btn-sm bg-primary text-white" wire:click="buscar_reporte_peso">
                <i class="fa fa-search"></i> BUSCAR
            </button>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 text-end">
            @if(count($filtrarData) > 0)
                <button class="btn btn-sm bg-success text-white mt-4" wire:click="exportarReportePesoExcel">
                    <i class="fa-solid fa-file-excel"></i> EXPORTAR
                </button>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="loader mt-2" wire:loading wire:target="buscar_reporte_peso"></div>
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
                                @php
                                    $totalGeneralFlete = 0;
                                    foreach($filtrarData as  $zon1){
                                        $totalGeneralFlete+= $zon1->costoTotal;
                                    }
                                    $por = $totalPesoTrans != 0 ? round($totalGeneralFlete / $totalPesoTrans, 3) : 0
                                @endphp
                                <tr>
                                    <td>TOTAL</td>
                                    <td class="text-center">S/ {{ $general->formatoDecimal($totalGeneralFlete ?? 0)}}</td>
                                    <td class="text-center">{{ $general->formatoDecimal($totalPesoTrans ?? 0)}}</td>
                                    <td class="text-center">{{ $general->formatoDecimal($por ?? 0) }}</td>
                                    <td class="text-center">0.35</td>
                                </tr>
                                @foreach($filtrarData as $indexZona => $zon)
                                    @php
                                        $zonaText = "";
                                        $zonaOb = "";
                                        if ($indexZona == 0){
                                            $zonaText = 'LOCAL';
                                            $zonaOb = 0.15;

                                        }elseif ($indexZona == 1){
                                            $zonaText = 'PROVINCIA 1';
                                            $zonaOb = 0.55;

                                        }elseif ($indexZona == 2){
                                            $zonaText = 'PROVINCIA 2';
                                            $zonaOb = 0.85;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $zonaText }}</td>
                                        <td class="text-center"> S/ {{ $general->formatoDecimal($zon->costoTotal ?? 0)}}</td>
                                        <td class="text-center"> {{ $general->formatoDecimal($zon->pesoKilos ?? 0)}}</td>
                                        <td class="text-center">{{ $general->formatoDecimal($zon->porcentaje ?? 0) }}</td>
                                        <td class="text-center">{{ $zonaOb ?? 0 }}</td>
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
        function actualizarGraficoPeso(meses,Lima,ProvinciaOne,provinciaTwoe) {
            const canvas = document.getElementById('graficoPesoTonelada');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoPesoTonelada) {
                graficoPesoTonelada.destroy();
            }

            // Crear nuevo gráfico
            graficoPesoTonelada = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: meses,
                    datasets: [
                        {
                            label: 'Peso Lima (Ton)',
                            data: Lima,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Peso Provincia 1 (Ton)',
                            data: ProvinciaOne,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Peso Provincia 2 (Ton)',
                            data: provinciaTwoe,
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
        function actualizarGraficoFlete(meses,Lima,ProvinciaOne,provinciaTwoe) {
            const canvas = document.getElementById('graficoFleteSolesKilo');
            if (!canvas) return;
            console.log(meses);
            console.log(Lima);
            console.log(ProvinciaOne);

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoFleteSolesKilo) {
                graficoFleteSolesKilo.destroy();
            }

            // Crear nuevo gráfico
            graficoFleteSolesKilo = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: meses,
                    datasets: [
                        {
                            label: 'Soles x Kilo Lima',
                            data: Lima,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: true
                        },
                        {
                            label: 'Soles x Kilo Prov.',
                            data: ProvinciaOne,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: true
                        },
                        {
                            label: 'Obj. Lima',
                            data: Array(meses.length).fill(0.12),
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            borderDash: [5, 5],
                            pointRadius: 0
                        },
                        {
                            label: 'Obj. Prov.',
                            data: Array(meses.length).fill(0.55),
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
                Livewire.on('generarGraficoPesoToneladas', function(data) {
                    setTimeout(() => {
                        try {
                            actualizarGraficoPeso(data[0][0], data[0][1], data[0][2],data[0][3]);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de peso:', error);
                        }
                    }, 100);
                });

                Livewire.on('generarGraficoPesoKilos', function(data) {
                    setTimeout(() => {
                        try {
                            actualizarGraficoFlete(data[0][0], data[0][1], data[0][2],data[0][3]);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de flete:', error);
                        }
                    }, 100);
                });
            }
        });
    </script>

</div>
