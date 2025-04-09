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
            <select name="tipo_reporte" id="tipo_reporte" wire:model.live="tipo_reporte" class="form-select">
                <option value="">Seleccionar...</option>
                <option value="emision">F. Emisión</option>
                <option value="programacion">F. Programación</option>
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
        @if($mostrarResultados)
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <button class="btn btn-success btn-sm text-white mt-4" wire:click="generar_excel_entrega_pedidos" wire:loading.attr="disabled"><i class="fa-solid fa-file-excel"></i> Exportar</button>
            </div>
        @endif
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_entrega_pedido"></div>
        </div>
    </div>

    {{--    RESULTADO--}}
    @if($mostrarResultados)
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
                                <td class="text-end">{{ $general->formatoDecimal($reporte['total_despachados'] ?? 0)}}</td>
                            </tr>
                            <tr>
                                <td>Despacho con Devoluciones</td>
                                <td class="text-end">{{ $general->formatoDecimal($reporte['despachos_con_devolucion'] ?? 0)}}</td>
                            </tr>
                            <tr>
                                <td>Envíos sin devoluciones</td>
                                <td class="text-end">{{ $general->formatoDecimal($reporte['envios_sin_devolucion'] ?? 0)}}</td>
                            </tr>
                            <tr style="background: #E2EFDA; color: black; font-weight: bold">
                                <td>Indicador de Efectividad - N° Despachos</td>
                                <td class="text-end">{{ isset($reporte['indicador_efectividad']) ? $general->formatoDecimal($reporte['indicador_efectividad']) . '%' : '0%' }}</td>
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
                                <td class="text-end">{{ isset($reporteValores['monto_total_despachados']) ? $general->formatoDecimal($reporteValores['monto_total_despachados']) : '0' }}</td>
                            </tr>
                            <tr>
                                <td>Despacho con Devoluciones</td>
                                <td class="text-end">{{ isset($reporteValores['monto_con_devolucion']) ? $general->formatoDecimal($reporteValores['monto_con_devolucion']) : '0' }}</td>
                            </tr>
                            <tr>
                                <td>Valor de Envíos sin devoluciones</td>
                                <td class="text-end">{{ isset($reporteValores['monto_sin_devolucion']) ? $general->formatoDecimal($reporteValores['monto_sin_devolucion']) : '0' }}</td>
                            </tr>
                            <tr style="background: #E2EFDA; color: black; font-weight: bold">
                                <td>Indicador de Efectividad - Valor Despachado</td>
                                <td class="text-end">{{ isset($reporteValores['indicador_efectividad_valor']) ? $general->formatoDecimal($reporteValores['indicador_efectividad_valor']) . '%' : '0%' }}</td>
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
                    <div class="card-body">
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
                    <div class="card-body">
                        <canvas id="graficoEfectividadValor" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif

{{--    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>--}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        // Variables globales para los gráficos
        let graficoEfectividadDespachos = null;
        let graficoEfectividadValor = null;

        // Función para inicializar o actualizar el gráfico de despachos
        function actualizarGraficoDespachos(data) {
            const canvas = document.getElementById('graficoEfectividadDespachos');
            if (!canvas) {
                console.error('No se encontró el canvas para gráfico de despachos');
                return;
            }

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoEfectividadDespachos) {
                graficoEfectividadDespachos.destroy();
            }

            console.log('Datos recibidos para gráfico de despachos:', data);

            // Verificar la estructura de datos y adaptarse
            let datosGrafico = data;

            // Si tenemos un array anidado, tomamos el primer elemento
            if (Array.isArray(data) && data.length > 0 && data[0] && typeof data[0] === 'object') {
                datosGrafico = data[0];
                console.log('Usando datos anidados:', datosGrafico);
            }

            // Si tenemos datos en formato alternativo (con propiedades data.0.meses)
            if (data && data[0] && data[0].meses) {
                datosGrafico = data[0];
                console.log('Usando datos del formato alternativo:', datosGrafico);
            }

            // Verificar y preparar datos
            const mesesDatos = Array.isArray(datosGrafico.meses) ? datosGrafico.meses : [];
            const totalDespachados = Array.isArray(datosGrafico.total_despachados) ? datosGrafico.total_despachados : [];
            const enviosSinDevolucion = Array.isArray(datosGrafico.envios_sin_devolucion) ? datosGrafico.envios_sin_devolucion : [];
            const porcentajeEfectividad = Array.isArray(datosGrafico.porcentaje_efectividad) ? datosGrafico.porcentaje_efectividad : [];

            // Generar todos los meses del año hasta el mes actual
            const mesesEspanol = [
                'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN',
                'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'
            ];

            const añoActual = new Date().getFullYear();
            const mesActual = new Date().getMonth(); // 0-11

            let mesesCompletos = [];
            let dataTotalDespachados = [];
            let dataEnviosSinDevolucion = [];
            let dataPorcentajeEfectividad = [];

            // Generar array de todos los meses desde enero hasta el mes actual
            for (let i = 0; i <= mesActual; i++) {
                const etiquetaMes = mesesEspanol[i] + ' ' + añoActual;
                mesesCompletos.push(etiquetaMes);

                // Buscar si hay datos para este mes
                const indice = mesesDatos.findIndex(m => m === etiquetaMes);

                if (indice !== -1) {
                    // Hay datos para este mes
                    dataTotalDespachados.push(totalDespachados[indice]);
                    dataEnviosSinDevolucion.push(enviosSinDevolucion[indice]);
                    dataPorcentajeEfectividad.push(porcentajeEfectividad[indice]);
                } else {
                    // No hay datos para este mes
                    dataTotalDespachados.push(0);
                    dataEnviosSinDevolucion.push(0);
                    dataPorcentajeEfectividad.push(0);
                }
            }

            console.log('Datos procesados para gráfico de despachos:', {
                mesesCompletos,
                dataTotalDespachados,
                dataEnviosSinDevolucion,
                dataPorcentajeEfectividad
            });

            // Crear nuevo gráfico
            graficoEfectividadDespachos = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: mesesCompletos,
                    datasets: [
                        {
                            label: 'Pedidos Entregados',
                            data: dataTotalDespachados,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Entregado sin Devolución',
                            data: dataEnviosSinDevolucion,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: '% Entregas sin Devolución',
                            data: dataPorcentajeEfectividad,
                            type: 'line',
                            borderColor: 'rgba(169, 169, 169, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(169, 169, 169, 1)',
                            pointRadius: 4,
                            yAxisID: 'y1'
                        },
                        {
                            label: '% Obj Entrega sin Dev.',
                            data: Array(mesesCompletos.length).fill(95),
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
            if (!canvas) {
                console.error('No se encontró el canvas para gráfico de valor');
                return;
            }

            const ctx = canvas.getContext('2d');

            // Destruir gráfico anterior si existe
            if (graficoEfectividadValor) {
                graficoEfectividadValor.destroy();
            }

            console.log('Datos recibidos para gráfico de valor:', data);

            // Verificar la estructura de datos y adaptarse
            let datosGrafico = data;

            // Si tenemos un array anidado, tomamos el primer elemento
            if (Array.isArray(data) && data.length > 0 && data[0] && typeof data[0] === 'object') {
                datosGrafico = data[0];
                console.log('Usando datos anidados para valor:', datosGrafico);
            }

            // Si tenemos datos en formato alternativo (con propiedades data.0.meses)
            if (data && data[0] && data[0].meses) {
                datosGrafico = data[0];
                console.log('Usando datos del formato alternativo para valor:', datosGrafico);
            }

            // Verificar y preparar datos
            const mesesDatos = Array.isArray(datosGrafico.meses) ? datosGrafico.meses : [];
            const montoTotalDespachados = Array.isArray(datosGrafico.monto_total_despachados) ? datosGrafico.monto_total_despachados : [];
            const montoSinDevolucion = Array.isArray(datosGrafico.monto_sin_devolucion) ? datosGrafico.monto_sin_devolucion : [];
            const porcentajeEfectividadValor = Array.isArray(datosGrafico.porcentaje_efectividad_valor) ? datosGrafico.porcentaje_efectividad_valor : [];

            // Generar todos los meses del año hasta el mes actual
            const mesesEspanol = [
                'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN',
                'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'
            ];

            const añoActual = new Date().getFullYear();
            const mesActual = new Date().getMonth(); // 0-11

            let mesesCompletos = [];
            let dataMontoTotalDespachados = [];
            let dataMontoSinDevolucion = [];
            let dataPorcentajeEfectividadValor = [];

            // Generar array de todos los meses desde enero hasta el mes actual
            for (let i = 0; i <= mesActual; i++) {
                const etiquetaMes = mesesEspanol[i] + ' ' + añoActual;
                mesesCompletos.push(etiquetaMes);

                // Buscar si hay datos para este mes
                const indice = mesesDatos.findIndex(m => m === etiquetaMes);

                if (indice !== -1) {
                    // Hay datos para este mes
                    dataMontoTotalDespachados.push(montoTotalDespachados[indice]);
                    dataMontoSinDevolucion.push(montoSinDevolucion[indice]);
                    dataPorcentajeEfectividadValor.push(porcentajeEfectividadValor[indice]);
                } else {
                    // No hay datos para este mes
                    dataMontoTotalDespachados.push(0);
                    dataMontoSinDevolucion.push(0);
                    dataPorcentajeEfectividadValor.push(0);
                }
            }

            console.log('Datos procesados para gráfico de valor:', {
                mesesCompletos,
                dataMontoTotalDespachados,
                dataMontoSinDevolucion,
                dataPorcentajeEfectividadValor
            });

            // Crear nuevo gráfico
            graficoEfectividadValor = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: mesesCompletos,
                    datasets: [
                        {
                            label: 'Monto Total Despachado',
                            data: dataMontoTotalDespachados,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Valor sin Devolución',
                            data: dataMontoSinDevolucion,
                            backgroundColor: 'rgba(255, 159, 64, 0.7)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: '% Efectividad Valor',
                            data: dataPorcentajeEfectividadValor,
                            type: 'line',
                            borderColor: 'rgba(169, 169, 169, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(169, 169, 169, 1)',
                            pointRadius: 4,
                            yAxisID: 'y1'
                        },
                        {
                            label: '% Obj Entrega sin Dev.',
                            data: Array(mesesCompletos.length).fill(95),
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
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar eventos Livewire
            if (typeof Livewire !== 'undefined') {
                Livewire.on('actualizarGraficoDespachos', function(data) {
                    console.log('Evento actualizarGraficoDespachos recibido:', data);
                    setTimeout(() => {
                        try {
                            actualizarGraficoDespachos(data);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de despachos:', error);
                        }
                    }, 100);
                });

                Livewire.on('actualizarGraficoValor', function(data) {
                    console.log('Evento actualizarGraficoValor recibido:', data);
                    setTimeout(() => {
                        try {
                            actualizarGraficoValor(data);
                        } catch (error) {
                            console.error('Error al actualizar gráfico de valor:', error);
                        }
                    }, 100);
                });
            }
        });
    </script>
</div>
