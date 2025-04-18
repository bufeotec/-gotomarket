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
    {{--    CUANDO BUSQUO--}}
    <div class="row align-items-center mt-2">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="tipo_reporte" class="form-label">Tipo de reporte</label>
            <select name="tipo_reporte" id="tipo_reporte" wire:model="tipo_reporte" class="form-select">
                <option value="">Seleccionar...</option>
                <option value="1">F. Emisión</option>
                <option value="2">F. Programación</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_desde" class="form-label">Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_hasta" class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control">
        </div>
        <div class="col-lg-2 col-md-3 col-sm-12 mt-4">
            <button class="btn btn-sm bg-primary text-white" wire:click="buscar_entrega_pedido">
                <i class="fa fa-search"></i> BUSCAR
            </button>
        </div>
        @if($searchExecuted)
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <button class="btn btn-success btn-sm text-white mt-4" wire:click="generar_excel_entrega_pedidos">
                    <i class="fa-solid fa-file-excel"></i> Exportar
                </button>
            </div>
        @endif
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_entrega_pedido"></div>
        </div>
    </div>

    {{-- RESULTADO --}}
    @if($searchExecuted)
        <div class="row mt-3">
            <div class="col-lg-12 col-md-12">
                <h6>Reporte resumen</h6>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                            <tr style="background: #FFE699; color: black; font-weight: bold">
                                <th>Efectividad de Entrega - N° Despachos</th>
                                <th class="text-end">Valores</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Total de Pedidos Despachados</td>
                                <td class="text-end">{{ $general->formatoDecimal($reporteData['total_despachados'] ?? 0)}}</td>
                            </tr>
                            <tr>
                                <td>Despacho con Devoluciones</td>
                                <td class="text-end">{{ $general->formatoDecimal($reporteData['despachos_con_devolucion'] ?? 0)}}</td>
                            </tr>
                            <tr>
                                <td>Envíos sin devoluciones</td>
                                <td class="text-end">{{ $general->formatoDecimal($reporteData['envios_sin_devolucion'] ?? 0)}}</td>
                            </tr>
                            <tr style="background: #E2EFDA; color: black; font-weight: bold">
                                <td>Indicador de Efectividad - N° Despachos</td>
                                <td class="text-end">{{ isset($reporteData['indicador_efectividad']) ? $general->formatoDecimal($reporteData['indicador_efectividad']) . '%' : '0%' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                            <tr style="background: #FFE699; color: black; font-weight: bold">
                                <th>Efectividad de Entrega - Valor Despachado</th>
                                <th class="text-end">Valores</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Monto Total de Pedidos Despachados</td>
                                <td class="text-end">{{ isset($reporteValoresData['monto_total_despachados']) ? $general->formatoDecimal($reporteValoresData['monto_total_despachados']) : '0' }}</td>
                            </tr>
                            <tr>
                                <td>Despacho con Devoluciones</td>
                                <td class="text-end">{{ isset($reporteValoresData['monto_con_devolucion']) ? $general->formatoDecimal($reporteValoresData['monto_con_devolucion']) : '0' }}</td>
                            </tr>
                            <tr>
                                <td>Valor de Envíos sin devoluciones</td>
                                <td class="text-end">{{ isset($reporteValoresData['monto_sin_devolucion']) ? $general->formatoDecimal($reporteValoresData['monto_sin_devolucion']) : '0' }}</td>
                            </tr>
                            <tr style="background: #E2EFDA; color: black; font-weight: bold">
                                <td>Indicador de Efectividad - Valor Despachado</td>
                                <td class="text-end">{{ isset($reporteValoresData['indicador_efectividad_valor']) ? $general->formatoDecimal($reporteValoresData['indicador_efectividad_valor']) . '%' : '0%' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- GRAFICO DE Efectividad de Entrega - N° Despachos -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header ">
                        <h5 class="card-title mb-0">EFECTIVIDAD DE ENTREGA - POR N° DE DESPACHOS</h5>
                    </div>
                    <div class="card-body" wire:ignore>
                        <canvas id="graficoEfectividadDespachos" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRAFICO DE Efectividad de Entrega - Valor Despachado -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">EFECTIVIDAD DE ENTREGA - POR VALOR DESPACHADO</h5>
                    </div>
                    <div class="card-body" wire:ignore>
                        <canvas id="graficoEfectividadValor" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        // Variables globales para los gráficos
        let graficoEfectividadDespachos = null;
        let graficoEfectividadValor = null;

        // Función para inicializar o actualizar el gráfico de despachos
        function actualizarGraficoDespachos(data) {
            const canvas = document.getElementById('graficoEfectividadDespachos');
            if (!canvas) return;

            canvas.style.height = '300px';
            canvas.style.width = '100%';

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoEfectividadDespachos) {
                graficoEfectividadDespachos.destroy();
            }

            // Extraer los datos correctamente
            const datosGrafico = Array.isArray(data) ? data[0] : data;

            // Procesar datos para el gráfico
            const meses = datosGrafico.meses || [];
            const totalDespachados = datosGrafico.total_despachados || [];
            const enviosSinDevolucion = datosGrafico.envios_sin_devolucion || [];
            const porcentajeEfectividad = datosGrafico.porcentaje_efectividad || [];

            // Crear nuevo gráfico
            graficoEfectividadDespachos = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: meses,
                    datasets: [
                        {
                            label: 'Pedidos Entregados',
                            data: totalDespachados,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Entregado sin Devolución',
                            data: enviosSinDevolucion,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: '% Entregas sin Devolución',
                            data: porcentajeEfectividad,
                            type: 'line',
                            borderColor: 'rgba(169, 169, 169, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(169, 169, 169, 1)',
                            pointRadius: 4,
                            yAxisID: 'y1'
                        },
                        {
                            label: '% Obj Entrega sin Dev.',
                            data: Array(meses.length).fill(95),
                            type: 'line',
                            borderColor: 'rgba(255, 204, 0, 1)',
                            borderWidth: 1,
                            borderDash: [5, 5],
                            pointRadius: 0,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: getCommonChartOptions('Cantidad de Despachos')
            });
        }

        // Función para inicializar o actualizar el gráfico de valor despachado
        function actualizarGraficoValor(data) {
            const canvas = document.getElementById('graficoEfectividadValor');
            if (!canvas) return;

            canvas.style.height = '300px';
            canvas.style.width = '100%';

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoEfectividadValor) {
                graficoEfectividadValor.destroy();
            }

            // Extraer los datos correctamente
            const datosGrafico = Array.isArray(data) ? data[0] : data;

            // Procesar datos para el gráfico
            const meses = datosGrafico.meses || [];
            const montoTotal = datosGrafico.monto_total_despachados || [];
            const montoSinDevolucion = datosGrafico.monto_sin_devolucion || [];
            const porcentajeEfectividad = datosGrafico.porcentaje_efectividad_valor || [];

            // Crear nuevo gráfico
            graficoEfectividadValor = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: meses,
                    datasets: [
                        {
                            label: 'Monto Total Despachado',
                            data: montoTotal,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Valor sin Devolución',
                            data: montoSinDevolucion,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: '% Efectividad Valor',
                            data: porcentajeEfectividad,
                            type: 'line',
                            borderColor: 'rgba(169, 169, 169, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(169, 169, 169, 1)',
                            pointRadius: 4,
                            yAxisID: 'y1'
                        },
                        {
                            label: '% Obj Entrega sin Dev.',
                            data: Array(meses.length).fill(95),
                            type: 'line',
                            borderColor: 'rgba(255, 204, 0, 1)',
                            borderWidth: 1,
                            borderDash: [5, 5],
                            pointRadius: 0,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: getCommonChartOptions('Valor (Soles)')
            });
        }

        // Función para opciones comunes de los gráficos
        function getCommonChartOptions(yAxisTitle) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: true,
                        ticks: {
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: yAxisTitle
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Porcentaje (%)'
                        },
                        min: 0,
                        max: 100,
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.datasetIndex >= 2) {
                                    return label + context.raw + '%';
                                }
                                return label + Number(context.raw).toLocaleString('es-PE');
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            };
        }

        // Inicialización cuando el DOM está listo
        document.addEventListener('livewire:init', () => {
            // Configurar eventos Livewire
            if (typeof Livewire !== 'undefined') {
                Livewire.on('actualizarGraficoDespachos', function(data) {
                    setTimeout(() => actualizarGraficoDespachos(data), 200);
                });

                Livewire.on('actualizarGraficoValor', function(data) {
                    setTimeout(() => actualizarGraficoValor(data), 200);
                });
            }
        });
    </script>

</div>
