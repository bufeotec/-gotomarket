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
            @error('tipo_reporte')
            <span class="message-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_desde" class="form-label">Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control">
            @error('desde')
            <span class="message-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_hasta" class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control">
            @error('hasta')
            <span class="message-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-lg-2 col-md-3 col-sm-12 mt-4">
            <button class="btn btn-sm bg-primary text-white" wire:click="buscar_entrega_pedido">
                <i class="fa fa-search"></i> BUSCAR
            </button>
        </div>
        @if($reporteData)
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2 mt-4">
                <button class="btn btn-success btn-sm text-white" wire:click="generar_excel_entrega_pedidos">
                    <i class="fa-solid fa-file-excel"></i> Exportar
                </button>
            </div>
        @endif
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_entrega_pedido"></div>
        </div>
    </div>

    @if(!empty($reporteData))
        <div class="row mt-4">
            <div class="col-lg-12 col-md-12">
                <h5>Reporte resumen</h5>
            </div>

            <!-- Primera tabla (Cantidad de despachos) -->
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-primary text-white">
                                <tr style="background: #FFE699; color: black; font-weight: bold">
                                    <th>Efectividad de Entrega - Nº Despachos</th>
                                    <th>Valores</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Total de Pedidos Despachados</td>
                                    <td>{{ $reporteData['total_pedidos_despachados'] }}</td>
                                </tr>
                                <tr>
                                    <td>Despacho con Devoluciones</td>
                                    <td>{{ $reporteData['despacho_con_devoluciones'] }}</td>
                                </tr>
                                <tr>
                                    <td>Envíos sin devoluciones</td>
                                    <td>{{ $reporteData['envios_sin_devoluciones'] }}</td>
                                </tr>
                                <tr style="background: #E2EFDA; color: black; font-weight: bold">
                                    <td>Indicador de Efectividad - Nº Despachos</td>
                                    <td>{{ $reporteData['indicador_efectividad'] }}%</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda tabla (Valor despachado) -->
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-primary text-white">
                                <tr style="background: #FFE699; color: black; font-weight: bold">
                                    <th>Efectividad de Entrega - Valor Despachado</th>
                                    <th>Valores</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Monto Total de Pedidos Despachados</td>
                                    <td>{{ number_format($reporteData['monto_total_despachado'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Despacho con Devoluciones</td>
                                    <td>{{ number_format($reporteData['monto_con_devolucion'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Valor de Envíos sin devoluciones</td>
                                    <td>{{ number_format($reporteData['monto_sin_devolucion'], 2) }}</td>
                                </tr>
                                <tr style="background: #E2EFDA; color: black; font-weight: bold">
                                    <td>Indicador de Efectividad - Valor Despachado</td>
                                    <td>{{ $reporteData['efectividad_valor'] }}%</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- GRAFICO DE Efectividad de Entrega - N° Despachos -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">EFECTIVIDAD DE ENTREGA - POR N° DE DESPACHOS</h5>
                    </div>
                    <div class="card-body" wire:ignore>
                        <canvas id="grafico_efectividad_despachos" height="300"></canvas>
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
                        <canvas id="grafico_efectividad_valor" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        // Variables globales para los gráficos
        let graficoEfectividadDespachos = null;
        let graficoEfectividadValor = null;

        // Función para verificar y limpiar datos
        function prepararDatos(data) {
            // Verificar si data es null, undefined o no es un array con elementos
            if (!data || !Array.isArray(data) || data.length === 0) {
                console.warn('Datos no definidos o incorrectos, usando valores por defecto');
                return {
                    meses: [],
                    pedidosEntregados: [],
                    entregadosSinDevolucion: [],
                    efectividad: [],
                    solesEntregados: [],
                    solesSinDevolucion: [],
                    efectividadValor: []
                };
            }

            // Tomar el primer elemento del array (asumiendo que es ahí donde están los datos)
            const primerElemento = data[0];

            return {
                meses: Array.isArray(primerElemento.meses) ? primerElemento.meses : [],
                pedidosEntregados: Array.isArray(primerElemento.pedidosEntregados) ? primerElemento.pedidosEntregados : [],
                entregadosSinDevolucion: Array.isArray(primerElemento.entregadosSinDevolucion) ? primerElemento.entregadosSinDevolucion : [],
                efectividad: Array.isArray(primerElemento.efectividad) ? primerElemento.efectividad : [],
                solesEntregados: Array.isArray(primerElemento.solesEntregados) ? primerElemento.solesEntregados : [],
                solesSinDevolucion: Array.isArray(primerElemento.solesSinDevolucion) ? primerElemento.solesSinDevolucion : [],
                efectividadValor: Array.isArray(primerElemento.efectividadValor) ? primerElemento.efectividadValor : []
            };
        }

        // Función para mostrar gráfico vacío o con datos
        function actualizarGraficoEfectividadDespachos(data) {
            const canvas = document.getElementById('grafico_efectividad_despachos');
            if (!canvas) {
                console.error('Canvas no encontrado');
                return;
            }

            try {
                const ctx = canvas.getContext('2d');

                // Destruir gráfico anterior si existe
                if (graficoEfectividadDespachos) {
                    graficoEfectividadDespachos.destroy();
                }

                // Si no hay meses, mostramos gráfico vacío
                if (data.meses.length === 0) {
                    graficoEfectividadDespachos = new Chart(ctx, {
                        type: 'bar',
                        data: { labels: ['No hay datos'], datasets: [] },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true } },
                            plugins: { legend: { display: false } }
                        }
                    });
                    return;
                }

                // Crear nuevo gráfico con datos y configurar para usar dos ejes Y
                graficoEfectividadDespachos = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.meses,
                        datasets: [
                            {
                                label: 'PEDIDOS ENTREGADOS',
                                data: data.pedidosEntregados,
                                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1,
                                yAxisID: 'y' // Eje izquierdo para barras
                            },
                            {
                                label: 'ENTREGADO SIN DEVOLUCION',
                                data: data.entregadosSinDevolucion,
                                backgroundColor: 'rgba(255, 159, 64, 0.7)',
                                borderColor: 'rgba(255, 159, 64, 1)',
                                borderWidth: 1,
                                yAxisID: 'y' // Eje izquierdo para barras
                            },
                            {
                                label: 'ENTREGAS SIN DEVOLUCION',
                                data: data.efectividad,
                                borderColor: 'rgba(96, 96, 96, 1)',
                                borderWidth: 2,
                                type: 'line',
                                pointRadius: 4,
                                fill: false,
                                yAxisID: 'y1' // Eje derecho para líneas de porcentaje
                            },
                            {
                                label: 'OBJ ENTREGA SIN DEV (95%)',
                                data: Array(data.meses.length).fill(95),
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                type: 'line',
                                pointRadius: 0,
                                fill: false,
                                yAxisID: 'y1' // Eje derecho para líneas de porcentaje
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Cantidad despachos'
                                },
                                grid: {
                                    drawOnChartArea: true
                                }
                            },
                            y1: {
                                beginAtZero: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Porcentaje %'
                                },
                                max: 100,
                                grid: {
                                    drawOnChartArea: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
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
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.datasetIndex <= 1) {
                                            // Formato para barras (cantidades)
                                            return label + context.parsed.y.toLocaleString('es-PE');
                                        } else {
                                            // Formato para líneas (porcentajes)
                                            return label + context.parsed.y.toFixed(2) + '%';
                                        }
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error al crear gráfico de despachos:', error);
            }
        }

        // Función para el gráfico de valores con ejes Y duales
        function actualizarGraficoEfectividadValor(data) {
            const canvas = document.getElementById('grafico_efectividad_valor');
            if (!canvas) {
                console.error('Canvas no encontrado');
                return;
            }

            try {
                const ctx = canvas.getContext('2d');

                // Destruir gráfico anterior si existe
                if (graficoEfectividadValor) {
                    graficoEfectividadValor.destroy();
                }

                // Si no hay meses, mostramos gráfico vacío
                if (data.meses.length === 0) {
                    graficoEfectividadValor = new Chart(ctx, {
                        type: 'bar',
                        data: { labels: ['No hay datos'], datasets: [] },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true } },
                            plugins: { legend: { display: false } }
                        }
                    });
                    return;
                }

                // Crear nuevo gráfico con datos y configurar para usar dos ejes Y
                graficoEfectividadValor = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.meses,
                        datasets: [
                            {
                                label: 'SOLES ENTREGADOS',
                                data: data.solesEntregados,
                                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1,
                                yAxisID: 'y' // Eje izquierdo para barras
                            },
                            {
                                label: 'SOLES ENTREGADOS SIN DEVOLUCION',
                                data: data.solesSinDevolucion,
                                backgroundColor: 'rgba(255, 159, 64, 0.7)',
                                borderColor: 'rgba(255, 159, 64, 1)',
                                borderWidth: 1,
                                yAxisID: 'y' // Eje izquierdo para barras
                            },
                            {
                                label: 'ENTREGAS SIN DEVOLUCION',
                                data: data.efectividadValor,
                                borderColor: 'rgba(96, 96, 96, 1)',
                                borderWidth: 2,
                                type: 'line',
                                pointRadius: 4,
                                fill: false,
                                yAxisID: 'y1' // Eje derecho para líneas de porcentaje
                            },
                            {
                                label: 'OBJ ENTREGA SIN DEV (99%)',
                                data: Array(data.meses.length).fill(99),
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                type: 'line',
                                pointRadius: 0,
                                fill: false,
                                yAxisID: 'y1' // Eje derecho para líneas de porcentaje
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Valor soles'
                                },
                                ticks: {
                                    callback: v => 'S/ ' + v.toLocaleString('es-PE')
                                },
                                grid: {
                                    drawOnChartArea: true
                                }
                            },
                            y1: {
                                beginAtZero: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Porcentaje %'
                                },
                                max: 100,
                                grid: {
                                    drawOnChartArea: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
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
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.datasetIndex <= 1) {
                                            // Formato para barras (soles)
                                            return label + 'S/ ' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2});
                                        } else {
                                            // Formato para líneas (porcentajes)
                                            return label + context.parsed.y.toFixed(2) + '%';
                                        }
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error al crear gráfico de valores:', error);
            }
        }

        // Inicialización cuando el DOM está listo
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Livewire !== 'undefined') {
                Livewire.on('actualizarGraficosEfectividad', function(data) {
                    setTimeout(() => {
                        try {
                            console.log('Datos recibidos:', data); // Para depuración
                            const datosPreparados = prepararDatos(data);
                            console.log('Datos preparados:', datosPreparados); // Para depuración
                            actualizarGraficoEfectividadDespachos(datosPreparados);
                            actualizarGraficoEfectividadValor(datosPreparados);
                        } catch (error) {
                            console.error('Error general al actualizar gráficos:', error);
                        }
                    }, 100);
                });
            }
        });
    </script>

</div>
