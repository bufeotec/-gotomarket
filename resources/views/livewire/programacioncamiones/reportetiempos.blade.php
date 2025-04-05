<div>
    @php
        $general = new \App\Models\General();
    @endphp
    <div class="row">
        <div class="col-lg-12 col-md-6 col-sm-12 mb-2">
            <div>
                <button class="btn btn-info" wire:click="setFilterByEmision(true)">F. Emisión</button>
                <button class="btn btn-info" wire:click="setFilterByEmision(false)">F. Programación</button>
                <button class="btn bg-primary text-white" wire:click="buscar_reporte_tiempo">
                    <i class="fa fa-search"></i> BUSCAR
                </button>
                @if(count($filteredData) > 0)
                    <button class="btn bg-success text-white" wire:click="exportarTiemposExcel">
                        <i class="fa-solid fa-file-excel"></i> EXPORTAR
                    </button>
                @endif
            </div>
        </div>

        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <label class="form-label">F. Desde</label>
            <input type="date" wire:model.live="desde" class="form-control" min="2025-01-01">
        </div>

        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <label class="form-label">F. Hasta</label>
            <input type="date" wire:model.live="hasta" class="form-control" min="2025-01-01">
        </div>

        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <label class="form-label">M. Desde</label>
            <input type="month" wire:model.live="mdesde" class="form-control">
        </div>

        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <label class="form-label">M. Hasta</label>
            <input type="month" wire:model.live="mhasta" class="form-control">
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_reporte_tiempo"></div>
        </div>
    </div>

    @if(count($filteredData) > 0)
        <div class="row">
            <div class="col-lg-4 col-md-12 col-sm-12">
                <x-card-general-view>
                    <x-slot name="content">
                        <div class="row">
                            <div class="row text-center mt-2">
                                <h6>TIEMPO DE ATENCIÓN</h6>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <x-table-general>
                                    <x-slot name="thead">
                                        <tr>
                                            <th>Zona de Despacho</th>
                                            <th>Días</th>
                                        </tr>
                                    </x-slot>

                                    <x-slot name="tbody">
                                        @php
                                            $zonas = [
                                                'Lima' => 0,
                                                'Provincia 1' => 0,
                                                'Provincia 2' => 0,
                                            ];

                                            foreach ($filteredData as $resultado) {
                                                $zona = ucwords(strtolower($resultado->departamento));
                                                if (array_key_exists($zona, $zonas)) {
                                                    $zonas[$zona] = round($resultado->dias_entrega, 2);
                                                }
                                            }
                                        @endphp

                                        @foreach ($zonas as $zona => $dias)
                                            <tr>
                                                <td>{{ $zona }}</td>
                                                <td>{{ $dias }}</td>
                                            </tr>
                                        @endforeach
                                    </x-slot>
                                </x-table-general>
                            </div>
                        </div>
                    </x-slot>
                </x-card-general-view>
            </div>

            <div class="col-lg-5 col-md-12 col-sm-12">
                <x-card-general-view>
                    <x-slot name="content">
                        <div class="row">
                            <div class="row text-center mt-2">
                                <h6>REPORTE RESÚMEN</h6>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <x-table-general>
                                    <x-slot name="thead">
                                        <tr>
                                            <th>Zona</th>
                                            <th>Promedio Tiempo de Entrega</th>
                                            <th>Objetivo</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @php
                                            $promedios = [
                                                'Local' => [],
                                                'Provincia 1' => [],
                                                'Provincia 2' => [],
                                            ];

                                            foreach ($filteredData as $resultado) {
                                                $zona = ucwords(strtolower($resultado->departamento));
                                                if ($zona === 'Lima' || $zona === 'Callao') {
                                                    $promedios['Local'][] = $resultado->dias_entrega;
                                                } elseif (array_key_exists($zona, $promedios)) {
                                                    $promedios[$zona][] = $resultado->dias_entrega;
                                                }
                                            }

                                            $resultados = [];
                                            foreach ($promedios as $zona => $dias) {
                                                if (!empty($dias)) {
                                                    $promedio = array_sum($dias) / count($dias);
                                                    $resultados[$zona] = [
                                                        'promedio' => round($promedio, 2),
                                                        'objetivo' => round($promedio),
                                                    ];
                                                } else {
                                                    $resultados[$zona] = [
                                                        'promedio' => 0,
                                                        'objetivo' => 0,
                                                    ];
                                                }
                                            }
                                        @endphp

                                        @foreach ($resultados as $zona => $data)
                                            <tr>
                                                <td>{{ $zona }}</td>
                                                <td class="text-center">{{ $data['promedio'] }}</td>
                                                <td>{{ $data['objetivo'] }}</td>
                                            </tr>
                                        @endforeach
                                    </x-slot>
                                </x-table-general>
                            </div>
                        </div>
                    </x-slot>
                </x-card-general-view>
            </div>
        </div>
    @elseif($searchExecuted && empty($desde) && empty($hasta) && empty($mdesde) && empty($mhasta))
        <div class="alert alert-info">
            Debes seleccionar un rango de fecha válido.
        </div>
    @elseif($searchExecuted)
        <div class="alert alert-info">
            No hay datos disponibles para mostrar con los filtros seleccionados.
        </div>
    @endif


</div>
