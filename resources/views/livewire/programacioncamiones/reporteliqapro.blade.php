<div>
    @php
        $general = new \App\Models\General();
    @endphp
    {{--BUSCAR--}}
    <div class="row">
        <div class="col-lg-12 mb-2">
            <button class="btn btn-info" wire:click="setFilter(true)">Filtrar por Programación</button>
            <button class="btn btn-info" wire:click="setFilter(false)">Filtrar por Despacho</button>

            <button class="btn bg-primary text-white" wire:click="buscar_datos">
                <i class="fa fa-search"></i> BUSCAR
            </button>

            @if(count($filteredData) > 0)
                <button class="btn btn-success text-white" wire:click.prevent="exportarDespachosExcel">
                    <i class="fa-solid fa-file-excel"></i> Exportar
                </button>
            @endif
        </div>

        <div class="col-lg-2 mb-2">
            <label class="form-label">Desde</label>
            <input type="date" wire:model.defer="desde" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 mb-2">
            <label class="form-label">Hasta</label>
            <input type="date" wire:model.defer="hasta" class="form-control" min="2025-01-01">
        </div>
    </div>

    @if($searchdatos)
        <div class="row">
            <!-- TABLA LOCAL (tipo_servicio = 1) -->
            @if(count($localData) > 0)
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <x-card-general-view>
                        <x-slot name="content">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 p-3">
                                    <div class="row text-center">
                                        <h6>DESPACHO LOCAL</h6>
                                    </div>

                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th style="font-size: 12px">FEC. DESPACHO</th>
                                                <th style="font-size: 12px">FEC. APROB</th>
                                                <th style="font-size: 12px">LOCAL</th>
                                                <th style="font-size: 12px">PROVEEDOR</th>
                                                <th style="font-size: 12px">FACT</th>
                                                <th style="font-size: 12px">SIN IGV</th>
                                                <th style="font-size: 12px">CON IGV</th>
                                                <th style="font-size: 12px">TOTAL PROVEEDOR</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @php
                                                $groupedLocalData = $localData->groupBy('proveedor');
                                                $totalLocalConIgv = $localData->sum('con_igv');
                                                $totalLocalSinIgv = $localData->sum('sin_igv');
                                            @endphp

                                            @foreach($groupedLocalData as $proveedor => $guias)
                                                @php
                                                    $totalProveedorConIgv = $guias->sum('con_igv');
                                                    $totalProveedorSinIgv = $guias->sum('sin_igv');
                                                    $firstRow = true;
                                                @endphp

                                                @foreach($guias as $guia)
                                                    <tr style="cursor: pointer">
                                                        <td style="font-size: 12px">{{ date('d/m/Y', strtotime($guia->fec_despacho)) }}</td>
                                                        <td style="font-size: 12px">{{ date('d/m/Y', strtotime($guia->fec_aprob)) }}</td>
                                                        <td style="font-size: 12px">
                                                            @if($guia->distrito)
                                                                {{ $guia->distrito }}
                                                            @elseif($guia->departamento)
                                                                {{ $guia->departamento }}
                                                            @else
                                                                S/N
                                                            @endif
                                                        </td>
                                                        <td style="font-size: 12px">{{ $proveedor }}</td>
                                                        <td style="font-size: 12px" class="text-center">{{ $guia->fact ?? '---'}}</td>
                                                        <td style="font-size: 12px">S/ {{ $general->formatoDecimal($guia->sin_igv) }}</td>
                                                        <td style="font-size: 12px">S/ {{ $general->formatoDecimal($guia->con_igv) }}</td>
                                                        <td style="font-size: 12px">
                                                            @if($firstRow)
                                                                S/ {{ $general->formatoDecimal($totalProveedorConIgv) }}
                                                                @php $firstRow = false; @endphp
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach

                                            <tr class="fw-bold text-primary">
                                                <td colspan="2"></td>
                                                <td colspan="3"><strong>Total General</strong></td>
                                                <td>S/ {{ $general->formatoDecimal($totalLocalSinIgv) }}</td>
                                                <td>S/ {{ $general->formatoDecimal($totalLocalConIgv) }}</td>
                                                <td>S/ {{ $general->formatoDecimal($totalLocalConIgv) }}</td>
                                            </tr>
                                        </x-slot>
                                    </x-table-general>
                                </div>
                            </div>
                        </x-slot>
                    </x-card-general-view>
                </div>
            @elseif(count($provincialData) > 0)
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <x-card-general-view>
                        <x-slot name="content">
                            <div class="alert m-3">
                                No hay registros de despachos locales para los filtros seleccionados.
                            </div>
                        </x-slot>
                    </x-card-general-view>
                </div>
            @endif

            @if(count($provincialData) > 0)
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <x-card-general-view>
                        <x-slot name="content">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 p-3">
                                    <div class="row text-center">
                                        <h6>DESPACHO A PROVINCIA</h6>
                                    </div>

                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th style="font-size: 12px">FEC. DESPACHO</th>
                                                <th style="font-size: 12px">FEC. APROB</th>
                                                <th style="font-size: 12px">DEPARTAMENTO - PROVINCIA</th> <!-- Cambiado de DEPARTAMENTO-PROVINCIA a DESTINO -->
                                                <th style="font-size: 12px">PROVEEDOR</th>
                                                <th style="font-size: 12px">FACT</th>
                                                <th style="font-size: 12px">SIN IGV</th>
                                                <th style="font-size: 12px">CON IGV</th>
                                                <th style="font-size: 12px">TOTAL PROVEEDOR</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @php
                                                $groupedProvincialData = $provincialData->groupBy('proveedor');
                                                $totalProvincialConIgv = $provincialData->sum('con_igv');
                                                $totalProvincialSinIgv = $provincialData->sum('sin_igv');
                                            @endphp

                                            @foreach($groupedProvincialData as $proveedor => $guias)
                                                @php
                                                    $totalProveedorConIgv = $guias->sum('con_igv');
                                                    $totalProveedorSinIgv = $guias->sum('sin_igv');
                                                    $firstRow = true;
                                                @endphp

                                                @foreach($guias as $guia)
                                                    <tr style="cursor: pointer">
                                                        <td style="font-size: 12px">{{ date('d/m/Y', strtotime($guia->fec_despacho)) }}</td>
                                                        <td style="font-size: 12px">{{ date('d/m/Y', strtotime($guia->fec_aprob)) }}</td>
                                                        <td style="font-size: 12px">
                                                            @if($guia->departamento && $guia->provincia)
                                                                {{ $guia->departamento }} - {{ $guia->provincia }}
                                                            @elseif($guia->departamento)
                                                                {{ $guia->departamento }}
                                                            @else
                                                                S/N
                                                            @endif
                                                        </td>
                                                        <td style="font-size: 12px">{{ $proveedor }}</td>
                                                        <td style="font-size: 12px" class="text-center">{{ $guia->fact ?? '---'}}</td>
                                                        <td style="font-size: 12px">S/ {{ $general->formatoDecimal($guia->sin_igv) }}</td>
                                                        <td style="font-size: 12px">S/ {{ $general->formatoDecimal($guia->con_igv) }}</td>
                                                        <td style="font-size: 12px">
                                                            @if($firstRow)
                                                                S/ {{ $general->formatoDecimal($totalProveedorConIgv) }}
                                                                @php $firstRow = false; @endphp
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach

                                            <tr class="fw-bold text-primary">
                                                <td colspan="2"></td>
                                                <td colspan="3"><strong>Total General</strong></td>
                                                <td>S/ {{ $general->formatoDecimal($totalProvincialSinIgv) }}</td>
                                                <td>S/ {{ $general->formatoDecimal($totalProvincialConIgv) }}</td>
                                                <td>S/ {{ $general->formatoDecimal($totalProvincialConIgv) }}</td>
                                            </tr>
                                        </x-slot>
                                    </x-table-general>
                                </div>
                            </div>
                        </x-slot>
                    </x-card-general-view>
                </div>
            @elseif(count($localData) > 0)
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <x-card-general-view>
                        <x-slot name="content">
                            <div class="alert m-3">
                                No hay registros de despachos provinciales para los filtros seleccionados.
                            </div>
                        </x-slot>
                    </x-card-general-view>
                </div>
            @endif
        </div>
        @if(count($localData) == 0 && count($provincialData) == 0)
            <div class="alert alert-info">
                No se encontraron datos para los filtros seleccionados.
            </div>
        @endif
    @endif
</div>
