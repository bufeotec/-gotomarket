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
            <label for="tipo_reporte" class="form-label">Tipo de reporte</label>
            <select name="tipo_reporte" id="tipo_reporte" wire:model.live="tipo_reporte" class="form-select">
                <option value="">Seleccionar...</option>
                <option value="emision">F. Emisión</option>
                <option value="programacion">F. Programación</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label class="form-label">F. Desde</label>
            <input type="date" wire:model.live="desde" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label class="form-label">F. Hasta</label>
            <input type="date" wire:model.live="hasta" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mt-4">
            <button class="btn btn-sm bg-primary text-white" wire:click="buscar_reporte_tiempo">
                <i class="fa fa-search"></i> BUSCAR
            </button>
        </div>
        @if(count($filteredData) > 0)
            <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2">
                <button class="btn btn-sm bg-success text-white" wire:click="exportarTiemposExcel">
                    <i class="fa-solid fa-file-excel"></i> EXPORTAR
                </button>
            </div>
        @endif
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_reporte_tiempo"></div>
        </div>
    </div>

    @if(count($filteredData) > 0)
        <div class="row mt-4">
            <div class="col-lg-12">
                <h6>REPORTE RESUMEN</h6>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th class="text-center">Zona</th>
                                    <th class="text-center">Promedio Tiempo de entrega</th>
                                    <th class="text-center">Guias que cumplen</th>
                                </tr>
                            </x-slot>
                            <x-slot name="tbody">
                                @php
                                    // Inicializamos acumuladores
                                    $datos = [
                                        'LOCAL' => ['suma' => 0, 'count' => 0, 'cumplen' => 0],
                                        'PROVINCIA 1' => ['suma' => 0, 'count' => 0, 'cumplen' => 0],
                                        'PROVINCIA 2' => ['suma' => 0, 'count' => 0, 'cumplen' => 0],
                                    ];

                                    foreach ($filteredData as $resultado) {
                                        $zona = $resultado->zona;
                                        if ($resultado->cumple_objetivo) {
                                            $datos[$zona]['suma'] += $resultado->dias_entrega;
                                            $datos[$zona]['count']++;
                                            $datos[$zona]['cumplen']++;
                                        }
                                    }

                                    // Calculamos resultados
                                    $resultados = [];
                                    foreach ($datos as $zona => $data) {
                                        $promedio = $data['count'] > 0 ? round($data['suma'] / $data['count'], 2) : 0;

                                        $resultados[$zona] = [
                                            'promedio' => $promedio,
                                            'cumplen' => $data['cumplen']
                                        ];
                                    }
                                @endphp

                                @foreach ($resultados as $zona => $data)
                                    <tr>
                                        <td class="text-center">{{ $zona }}</td>
                                        <td class="text-center">{{ $data['promedio'] }}</td>
                                        <td class="text-center">{{ $data['cumplen'] }}</td>
                                    </tr>
                                @endforeach
                            </x-slot>
                        </x-table-general>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- GRAFICO TIEMPO DE ENTREGA -->
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">TIEMPO DE ENTREGA</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoTiempoEntrega" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @elseif($searchExecuted && (empty($desde) || empty($hasta)))
        <div class="alert alert-info alert-dismissible show fade mt-2">
            Debes seleccionar un rango de fecha válido.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif($searchExecuted)
        <div class="alert alert-danger alert-dismissible show fade mt-2">
            No hay datos disponibles para mostrar con los filtros seleccionados.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Variable global para el gráfico
        let graficoTiempoEntrega = null;

        // Función para inicializar o actualizar el gráfico de Tiempos de Entrega
        function actualizarGraficoTiempoEntrega(data) {
            const canvas = document.getElementById('graficoTiempoEntrega');
            if (!canvas) return;

            // Extraer el primer elemento si data es un array
            const chartData = Array.isArray(data) ? data[0] : data;

            // Verificar estructura de datos
            if (!chartData || !chartData.meses || !chartData.tiempo_lima || !chartData.tiempo_provincia) {
                console.error('Datos incorrectos para gráfico de tiempos de entrega:', chartData);
                return;
            }

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoTiempoEntrega) {
                graficoTiempoEntrega.destroy();
            }

            // Crear nuevo gráfico
            graficoTiempoEntrega = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.meses,
                    datasets: [
                        {
                            label: 'Tiempo Lima (Local)',
                            data: chartData.tiempo_lima,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)', // Azul
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Tiempo Provincia',
                            data: chartData.tiempo_provincia,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)', // Naranja
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Obj. Lima',
                            data: Array(chartData.meses.length).fill(3), // Objetivo de 3 días
                            borderColor: 'rgba(255, 206, 86, 1)', // Amarillo
                            borderWidth: 2,
                            borderDash: [5, 5],
                            type: 'line',
                            pointRadius: 0
                        },
                        {
                            label: 'Obj. Provincia',
                            data: Array(chartData.meses.length).fill(7), // Objetivo promedio de 7 días (entre Prov 1 y 2)
                            borderColor: 'rgba(255, 99, 132, 1)', // Rojo
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
                                text: 'Días'
                            },
                            ticks: {
                                precision: 0
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
                                        label += context.parsed.y + ' días';
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
                Livewire.on('actualizarGraficoTiempoEntrega', function(data) {
                    setTimeout(() => {
                        try {
                            actualizarGraficoTiempoEntrega(data);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de tiempos de entrega:', error);
                        }
                    }, 100);
                });
            }
        });
    </script>

</div>
