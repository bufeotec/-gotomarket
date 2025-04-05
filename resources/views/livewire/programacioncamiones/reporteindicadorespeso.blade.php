<div>
    @php
        $general = new \App\Models\General();
    @endphp
    <div class="row">
        <div class="col-lg-12 col-md-6 col-sm-12 mb-2">
            <div>
                <button class="btn btn-info" wire:click="setFilterByEmision(true)">F. Despacho de OS</button>
                <button class="btn btn-info" wire:click="setFilterByEmision(false)">F. Emisión Guía</button>
                <button class="btn bg-primary text-white" wire:click="buscar_reporte_peso">
                    <i class="fa fa-search"></i> BUSCAR
                </button>
                @if(count($filtrarData) > 0)
                    <button class="btn bg-success text-white" wire:click.prevent="exportarReportePesoExcel">
                        <i class="fa-solid fa-file-excel"></i> EXPORTAR
                    </button>
                @endif
            </div>
        </div>

        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <label class="form-label">F. Desde</label>
            <input type="date" wire:model.live="ydesde" class="form-control" min="2025-01-01">
        </div>

        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <label class="form-label">F. Hasta</label>
            <input type="date" wire:model.live="yhasta" class="form-control" min="2025-01-01">
        </div>

        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <label class="form-label">M. Desde</label>
            <input type="month" wire:model.live="mydesde" class="form-control">
        </div>

        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
            <label class="form-label">M. Hasta</label>
            <input type="month" wire:model.live="myhasta" class="form-control">
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_reporte_peso"></div>
        </div>
    </div>
    @if($searchdatos && count($filtrarData) > 0)
        <div class="row">
            <div class="col-lg-5 col-md-12 col-sm-12 mb-4">
                <x-card-general-view>
                    <x-slot name="content">
                        <h6 class="text-center my-3">OBJETIVO DE INDICADORES</h6>
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>Zona de Despacho</th>
                                    <th class="text-center">Flete / Peso</th>
                                </tr>
                            </x-slot>
                            <x-slot name="tbody">
                                @foreach(['Lima', 'Provincia 1', 'Provincia 2', 'Total'] as $zona)
                                    <tr>
                                        <td>{{ $zona }}</td>
                                        <td class="text-center">S/{{ $summary[$zona]['indicador'] }}</td>
                                    </tr>
                                @endforeach
                            </x-slot>
                        </x-table-general>
                    </x-slot>
                </x-card-general-view>
            </div>

            <div class="col-lg-7 col-md-12 col-sm-12 mb-4">
                <x-card-general-view>
                    <x-slot name="content">
                        <h6 class="text-center my-3">REPORTE RESUMEN</h6>
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>Zona de Despacho</th>
                                    <th class="text-center">Flete (S/)</th>
                                    <th class="text-center">Peso (Kg)</th>
                                    <th class="text-center">Indicador (S/Kg)</th>
                                </tr>
                            </x-slot>
                            <x-slot name="tbody">
                                @foreach(['Lima', 'Provincia 1', 'Provincia 2', 'Total'] as $zona)
                                    <tr>
                                        <td>{{ $zona }}</td>
                                        <td class="text-center">S/{{ number_format($summary[$zona]['flete'], 2) }}</td>
                                        <td class="text-center">{{ number_format($summary[$zona]['peso'], 2) }} kg</td>
                                        <td class="text-center">S/{{ $summary[$zona]['indicador'] }}</td>
                                    </tr>
                                @endforeach
                            </x-slot>
                        </x-table-general>
                    </x-slot>
                </x-card-general-view>
            </div>
        </div>
    @elseif($searchdatos)
        <div class="alert alert-info text-center">
            No se encontraron resultados
        </div>
    @endif
</div>
