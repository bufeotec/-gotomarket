<div>
    @php
        $general = new \App\Models\General();
    @endphp

    <div class="row align-items-center justify-content-between">
        <div class="col-lg-7 col-md-7 col-sm-12">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                    <label for="tipo_reporte" class="form-label">Tipo de reporte</label>
                    <select name="tipo_reporte" id="tipo_reporte" wire:model="tipo_reporte" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="1">F. Emisión</option>
                        <option value="2">F. Programación</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                    <label class="form-label">F. Desde</label>
                    <input type="date" wire:model="desde" class="form-control" min="2025-01-01">
                    @error('desde')
                    <span class="message-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                    <label class="form-label">F. Hasta</label>
                    <input type="date" wire:model="hasta" class="form-control" min="2025-01-01">
                    @error('hasta')
                    <span class="message-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 mt-4">
                    <button class="btn btn-sm bg-primary text-white" wire:click="buscar_reporte_tiempo">
                        <i class="fa fa-search"></i> BUSCAR
                    </button>
                </div>
            </div>
        </div>
        @if($filteredData)
            <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2">
                <button class="btn btn-sm bg-success text-white" wire:click="exportarTiemposExcel">
                    <i class="fa-solid fa-file-excel"></i> EXPORTAR
                </button>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="loader mt-2" wire:loading wire:target="buscar_reporte_tiempo"></div>
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

    @if($filteredData)
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
                                    <th class="text-center">Objetivo (días)</th>
                                </tr>
                            </x-slot>
                            <x-slot name="tbody">
                                    <tr>
                                        <td class="text-center">Local</td>
                                        <td class="text-center">{{ $filteredData[0] }}</td>
                                        <td class="text-center">3</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">Provincia 1</td>4
                                        <td class="text-center">{{ $filteredData[1] }}</td>
                                        <td class="text-center">6</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">Provincia 2</td>
                                        <td class="text-center">{{ $filteredData[2]}}</td>
                                        <td class="text-center">8</td>
                                    </tr>
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
    @endif

</div>




<script>
    let miGrafico = null;
    function generarG(meses,lima,provincia) {
        const canvas = document.getElementById('graficoTiempoEntrega');
        if (!canvas) {
            console.warn("Canvas 'graficoTiempoEntrega' no encontrado.");
            return;
        }
        const ctx = canvas.getContext('2d');

        // Destruye gráfico anterior si ya existe
        if (miGrafico) {
            miGrafico.destroy();
        }

        miGrafico = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [
                    {
                        label: 'Tiempo Lima (Local)',
                        data: lima,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)', // Azul
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Tiempo Provincia',
                        data: provincia,
                        backgroundColor: 'rgba(255, 159, 64, 0.7)', // Naranja
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Obj. Lima',
                        data: Array(meses.length).fill(3), // Objetivo de 3 días
                        borderColor: 'rgba(255, 206, 86, 1)', // Amarillo
                        borderWidth: 2,
                        borderDash: [5, 5],
                        type: 'line',
                        pointRadius: 0
                    },
                    {
                        label: 'Obj. Provincia',
                        data: Array(meses.length).fill(7), // Objetivo promedio de 7 días (entre Prov 1 y 2)
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
    document.addEventListener('livewire:init', () => {
        Livewire.on('actualizarGraficoTiempoEntrega', (meses) => {
            setTimeout(() => {
                generarG(meses[0][0], meses[0][1], meses[0][2]);
            }, 200); // Espera 200ms, puedes ajustar si hace falta
        });
    });

</script>

