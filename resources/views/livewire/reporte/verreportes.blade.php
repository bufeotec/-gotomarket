<div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <div class="row align-items-center mt-2">
                <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
                    <label for="fecha_desde" class="form-label">Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control">
                </div>
                <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
                    <label for="fecha_hasta" class="form-label">Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control">
                </div>
                @if(count($list_nc_dv) > 0)
                    <div class="col-lg-4 col-md-2 col-sm-12">
                        <button class="btn bg-success text-white mt-4">
                            <i class="fa-solid fa-file-excel"></i> Generar Excel
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="chart-container" style="background-color: rgba(255, 255, 255, 1); margin-bottom: 20px; padding: 15px; border-radius: 8px;">
                <canvas id="costoFleteChart" style="height: 230px;"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-container" style="background-color: rgba(255, 255, 255, 1); margin-bottom: 20px; padding: 15px; border-radius: 8px;">
                <canvas id="kilosDespachadosChart" style="height: 230px;"></canvas>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="chart-container" style="background-color: rgba(255, 255, 255, 1); margin-bottom: 20px; padding: 15px; border-radius: 8px;">
                <canvas id="pedidosEntregadosChart" style="height: 230px;"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-container" style="background-color: rgba(255, 255, 255, 1); margin-bottom: 20px; padding: 15px; border-radius: 8px;">
                <canvas id="tiemposEntregaChart" style="height: 230px;"></canvas>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="chart-container" style="background-color: rgba(255, 255, 255, 1); margin-bottom: 20px; padding: 15px; border-radius: 8px;">
                <canvas id="incidentesChart" style="height: 230px;"></canvas>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const costoFleteData = @json($costoFleteData);
            const kilosData = @json($kilosData);
            const pedidosData = @json($pedidosData);
            const incidentesData = @json($incidentesData);

            function calculatePercentage(data) {
                const total = data.reduce((sum, value) => sum + value, 0);
                return data.map(value => ((value / total) * 100).toFixed(2));
            }

            function configureChart(ctx, type, labels, datasets, title) {
                return new Chart(ctx, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0)',
                                },
                                ticks: {
                                    color: '#000',
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(0, 0, 0, 0)',
                                },
                                ticks: {
                                    color: '#000',
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom', // Cambiar posición de leyendas
                            },
                            title: {
                                display: true,
                                text: title // Título específico para cada gráfico
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                    }
                });
            }

            const ctxCostoFlete = document.getElementById('costoFleteChart').getContext('2d');
            const costoFletePercentages = calculatePercentage(costoFleteData);
            configureChart(ctxCostoFlete, 'bar',
                ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'],
                [
                    {
                        label: 'Costo Flete',
                        data: costoFleteData,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        type: 'bar',
                    },
                    {
                        label: 'Porcentaje',
                        data: costoFletePercentages,
                        backgroundColor: 'rgba(0, 0, 0, 0)',
                        borderColor: 'rgba(0, 0, 0, 1)',
                        borderWidth: 2,
                        fill: false,
                        type: 'line',
                    }
                ], 'Costo de Flete'
            );

            // Gráfico de Kilos Despachados
            const ctxKilos = document.getElementById('kilosDespachadosChart').getContext('2d');
            const kilosPercentages = calculatePercentage(kilosData);
            configureChart(ctxKilos, 'bar',
                ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'],
                [
                    {
                        label: 'Kilos Despachados',
                        data: kilosData,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        type: 'bar',
                    },
                    {
                        label: 'Porcentaje',
                        data: kilosPercentages,
                        backgroundColor: 'rgba(0, 0, 0, 0)',
                        borderColor: 'rgba(0, 0, 0, 1)',
                        borderWidth: 2,
                        fill: false,
                        type: 'line',
                    }
                ], 'Kilos Despachados'
            );

            // Gráfico de Pedidos Entregados
            const ctxPedidos = document.getElementById('pedidosEntregadosChart').getContext('2d');
            const pedidosPercentages = calculatePercentage(pedidosData);
            configureChart(ctxPedidos, 'bar',
                ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'],
                [
                    {
                        label: 'Total Pedidos Entregados',
                        data: pedidosData,
                        backgroundColor: 'rgba(255, 206, 86, 0.6)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1,
                        type: 'bar',
                    },
                    {
                        label: 'Porcentaje',
                        data: pedidosPercentages,
                        backgroundColor: 'rgba(0, 0, 0, 0)',
                        borderColor: 'rgba(0, 0, 0, 1)',
                        borderWidth: 2,
                        fill: false,
                        type: 'line',
                    }
                ], 'Total Pedidos Entregados'
            );

            // Gráfico de Tiempos de Entrega
            const ctxTiempos = document.getElementById('tiemposEntregaChart').getContext('2d');
            const tiemposData = [5, 6, 0, 5, 6, 7, 0, 6, 5, 5, 4, 5];
            const tiemposPercentages = calculatePercentage(tiemposData);
            configureChart(ctxTiempos, 'bar',
                ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'],
                [
                    {
                        label: 'Tiempo de Entrega (días) Lima',
                        data: tiemposData,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        type: 'bar',
                    },
                    {
                        label: 'Porcentaje',
                        data: tiemposPercentages,
                        backgroundColor: 'rgba(0, 0, 0, 0)',
                        borderColor: 'rgba(0, 0, 0, 1)',
                        borderWidth: 2,
                        fill: false,
                        type: 'line',
                    }
                ], 'Tiempos de Entrega'
            );

            const ctxIncidentes = document.getElementById('incidentesChart').getContext('2d');
            configureChart(ctxIncidentes, 'line',
                ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'],
                [{
                    label: 'Incidentes Registrados',
                    data: incidentesData,
                    borderColor: 'rgba(255, 20, 147, 1)',
                    backgroundColor: 'rgba(255, 20, 147, 0.2)',
                    fill: true,
                    tension: 0.1
                }], 'Incidentes Registrados'
            );
        });
    </script>
</div>
