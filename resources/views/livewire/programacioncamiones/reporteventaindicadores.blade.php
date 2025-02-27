<div>
    @php
        $general = new \App\Models\General();
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="row">
        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <input type="date" wire:model.live="desde" class="form-control">
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <input type="date" wire:model.live="hasta" class="form-control">
        </div>
        <div class="col-lg-8 col-md-6 col-sm-12 text-end">
            <a class="btn bg-white text-dark create-new ms-3" onclick="history.back()">
                <span>
                    <i class="fa-solid fa-arrow-left me-sm-1"></i>
                    <span class="d-none d-sm-inline-block">Regresar</span>
                </span>
            </a>
        </div>
    </div>

    @if($total_ped_des['total_despachos'] > 0 || $listarEfectividad['ventas_despachadas'] > 0)
        <div class="row mt-4">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Resumen de Pedidos</h5>
                        @if($total_ped_des['total_despachos'] > 0)
                            <div class="mb-3">
                                <strong>Total de pedidos despachados:</strong>
                                <span class="text-primary">{{ $total_ped_des['total_despachos'] }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Despacho sin errores:</strong>
                                <span class="text-primary">{{ $total_ped_des['total_despachos_sin_nota_credito'] }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Total de envíos sin devoluciones:</strong>
                                <span class="text-primary">
                                    {{ number_format(($total_ped_des['total_despachos_sin_nota_credito'] / $total_ped_des['total_despachos']) * 100, 2) }}%
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Efectividad de Entrega</h5>
                        @if($listarEfectividad['ventas_despachadas'] > 0)
                            <div class="mb-3">
                                <strong>Ventas despachadas en soles:</strong>
                                <span class="text-primary">S/ {{$general->formatoDecimal($listarEfectividad['ventas_despachadas'] ?? 0) }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Nota de crédito por despacho:</strong>
                                <span class="text-primary">S/ {{$general->formatoDecimal($listarEfectividad['notas_credito'] ?? 0) }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Efectividad de entrega por monto:</strong>
                                @php
                                    $efectividad = $listarEfectividad['ventas_despachadas'] > 0
                                        ? 100 - (($listarEfectividad['notas_credito'] * 100) / $listarEfectividad['ventas_despachadas'])
                                        : 0;
                                @endphp
                                <span class="text-primary">S/ {{ $general->formatoDecimal($efectividad) }}</span>
                            </div>
                        @else
                            <div class="text-center">
                                No se han encontrado resultados.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card text-center">
                    <div class="card-body">
                        <h6>FLETE TOTAL</h6>
                        <div id="chart-flete"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="text-center card">
                    <div class="card-body">
                        <h6>FLETE LIMA Y PROVINCIA</h6>
                        <div id="chart-costo-total"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="text-center card">
                    <div class="card-body">
                        <h6>VOLUMEN TN DESPACHADOS</h6>
                        <div id="chart-volumen"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="text-center card">
                    <div class="card-body">
                        <h6>FLETE EN SOLES POR KILO</h6>
                        <div id="chart-flete-kg"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const data = {
                flete: [{{ implode(',', $list_data->pluck('despacho_flete')->toArray()) }}],
                costo: [{{ implode(',', $list_data->pluck('despacho_costo_total')->toArray()) }}],
                volumen: [{{ implode(',', $list_data->pluck('despacho_venta_total_volumen')->toArray()) }}],
                fleteKg: [{{ implode(',', $list_data->pluck('flete_soles_kg')->toArray()) }}]
            };

            const months = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

            function formatCurrency(value) {
                return new Intl.NumberFormat('es-PE', { minimumFractionDigits: 2 }).format(value);
            }

            function formatNumber(value) {
                return new Intl.NumberFormat('es-PE', { minimumFractionDigits: 2 }).format(value);
            }

            function formatPercentage(value) {
                return value.toFixed(2) + "%";
            }

            function createChart(selector, series, colors, stacked = false, showLabelsOnSeries = [], isPercentage = false, showMarkers = false) {
                const options = {
                    series: series,
                    chart: {
                        height: 350,
                        type: stacked ? 'bar' : 'line',
                        stacked: stacked
                    },
                    colors: colors,
                    stroke: {
                        width: [0, 2, 2],
                        curve: 'smooth'
                    },
                    markers: {
                        size: showMarkers ? 3 : 0,
                        shape: "circle",
                        strokeWidth: 0,
                        hover: { size: showMarkers ? 7 : 0 }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: (value, { seriesIndex }) =>
                            showLabelsOnSeries.includes(seriesIndex)
                                ? (isPercentage ? formatPercentage(value) : formatNumber(value))
                                : "",
                        style: {
                            fontSize: '9px',
                            fontWeight: '',
                            colors: ['#000']
                        },
                        background: {
                            enabled: false
                        },
                        offsetY: -3
                    },
                    xaxis: { categories: months },
                    yaxis: {
                        labels: { formatter: formatNumber }
                    },
                    legend: {
                        position: 'bottom'
                    }
                };
                new ApexCharts(document.querySelector(selector), options).render();
            }

            //-----------Gráfico 1
            const totalFlete = data.flete.reduce((acc, val) => acc + val, 0);
            const fleteEnPorcentaje = data.flete.map(v => (v / totalFlete) * 100);

            const objTotalConstante = fleteEnPorcentaje.reduce((acc, val) => acc + val, 0) / data.flete.length;

            const objTotalLineaRecta = new Array(data.flete.length - 1).fill(null).concat(objTotalConstante);
            createChart("#chart-flete", [
                { name: 'Flete Total', type: 'column', data: data.flete },
                { name: '%F. Total', type: 'line', data: data.flete.map(v => v * 1.1) },
                { name: '%Obj.Total', type: 'line', data: objTotalLineaRecta }
            ], ["#98a1a6", "#00bcd4", "#ff9800"], false, [1], true, true);

            //----------Gráfico 2
            const totalCosto = data.costo.reduce((acc, val) => acc + val, 0);
            const costoEnPorcentaje = data.costo.map(v => (v / totalCosto) * 100);

            createChart("#chart-costo-total", [
                { name: 'Flete Lima', type: 'column', data: data.costo.map(v => v * 0.6) },
                { name: 'Flete Provincia', type: 'column', data: data.costo.map(v => v * 0.4) },
                { name: '%F. Lima', type: 'line', data: costoEnPorcentaje },
                { name: '%F. Provincia', type: 'line', data: costoEnPorcentaje }
            ], ["#658dff", "#ff9800", "#040491","#b3b3bd"], false, [2], true, false);

            //----------Gráfico 3
            const totalVolumen = data.volumen.reduce((acc, val) => acc + val, 0);
            const volumenEnPorcentaje = data.volumen.map(v => (v / totalVolumen) * 100);

            createChart("#chart-volumen", [
                { name: 'Total volumen Despachados', type: 'column', data: data.volumen },
                { name: '%V. Total', type: 'line', data: volumenEnPorcentaje }
            ], ["#4691dc", "#30ad30"], false, [1], true, false);

            //----------Gráfico 4
            const totalFleteKg = data.fleteKg.reduce((acc, val) => acc + val, 0);
            const fleteKgEnPorcentaje = data.fleteKg.map(v => (v / totalFleteKg) * 100);

            createChart("#chart-flete-kg", [
                { name: 'Flete por Kg', type: 'column', data: data.fleteKg },
                { name: 'Tendencia Flete por Kg', type: 'line', data: fleteKgEnPorcentaje }
            ], ["#658dff", "#f83f14"], false, [1], true, false);
        });
    </script>
</div>
{{--hola--}}
