<div>
    @php
        $me = new \App\Models\General();
    @endphp
    {{--    MODAL MOTIVO CREDITO --}}
    <x-modal-delete wire:ignore.self style="z-index: 1056;">
        <x-slot name="id_modal">modalMotCre</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="aceptar_fac_credito">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{ $messageMotCre }}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">SI</button>
                        <button type="button" data-bs-dismiss="modal" class="btn btn-danger btnDelete">No</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
    {{--    FIN MODAL MOTIVO CREDITO --}}
    <div class="row">
        @if (session()->has('success'))
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="alert alert-success alert-dismissible show fade mt-2">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="alert alert-danger alert-dismissible show fade mt-2">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-2">
                            <h6>FACTURAS POR APROBRAR</h6>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control" min="2025-01-01">
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control" min="2025-01-01">
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="loader mt-2" wire:loading wire:target="buscar_comprobantes"></div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="contenedor-comprobante" style="max-height: 600px; overflow: auto">
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th style="font-size: 12px">Serie y Correlativo / Guía</th>
                                                <th style="font-size: 12px">Nombre del Cliente</th>
                                                <th style="font-size: 12px">Peso y Volumen</th>
                                                <th style="font-size: 12px">Check</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if(!empty($filteredFacturas))
                                                @foreach($filteredFacturas as $factura)
                                                    @php
                                                        $CFTD = $factura->fac_pre_prog_cftd; // Cambiado
                                                        $CFNUMSER = $factura->fac_pre_prog_cfnumser; // Cambiado
                                                        $CFNUMDOC = $factura->fac_pre_prog_cfnumdoc; // Cambiado
                                                        $comprobanteExiste = collect($this->selectedFacturas)->first(function ($facturaVa) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
                                                            return $facturaVa['CFTD'] === $CFTD
                                                                && $facturaVa['CFNUMSER'] === $CFNUMSER
                                                                && $facturaVa['CFNUMDOC'] === $CFNUMDOC;
                                                        });
                                                    @endphp
                                                    @if(!$comprobanteExiste)
                                                        <tr style="cursor: pointer">
                                                            <td colspan="4" style="padding: 0px">
                                                                <table class="table">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="width: 39.6%">
                                                                            <span class="tamanhoTablaComprobantes">
                                                                                <b class="colorBlackComprobantes">{{ date('d/m/Y', strtotime($factura->fac_pre_prog_grefecemision)) }}</b>
                                                                            </span>
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                {{ $factura->fac_pre_prog_cfnumser }} - {{ $factura->fac_pre_prog_cfnumdoc }}
                                                                            </span>
                                                                            @php
                                                                                $guia = $me->formatearCodigo($factura->fac_pre_prog_guia) // Cambiado
                                                                            @endphp
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                {{ $guia }}
                                                                            </span>
                                                                        </td>
                                                                        <td style="width: 32.2%">
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                {{ $factura->fac_pre_prog_cnomcli }} <!-- Cambiado -->
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            @php
                                                                                $tablaPeso = "0";
                                                                                if ($factura->fac_pre_prog_total_kg){ // Cambiado
                                                                                    $tablaPeso = $me->formatoDecimal($factura->fac_pre_prog_total_kg);
                                                                                }
                                                                            @endphp
                                                                            @php
                                                                                $tablaVolumen = "0";
                                                                                if ($factura->fac_pre_prog_total_volumen){ // Cambiado
                                                                                    $tablaVolumen = $me->formatoDecimal($factura->fac_pre_prog_total_volumen);
                                                                                }
                                                                            @endphp
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                <b class="colorBlackComprobantes">{{ $tablaPeso }} kg</b>
                                                                            </span>
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                <b class="colorBlackComprobantes">{{ $tablaVolumen }} cm³</b>
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <x-btn-accion class="btn btn-success btn-sm text-white" wire:click="pre_mot_cre('{{ base64_encode($factura->id_fac_pre_prog) }}')" data-bs-toggle="modal" data-bs-target="#modalMotCre">
                                                                                <x-slot name="message">
                                                                                    <i class="fa-regular fa-circle-check"></i>
                                                                                </x-slot>
                                                                            </x-btn-accion>
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="border-top: 2px solid transparent;">
                                                                        <td colspan="4" style="padding-top: 0">
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                {{ $factura->fac_pre_prog_direccion_llegada }} <br> UBIGEO: <b class="colorBlackComprobantes">{{ $factura->fac_pre_prog_departamento }} - {{ $factura->fac_pre_prog_provincia }} - {{ $factura->fac_pre_prog_distrito }}</b>
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3" class="text-center">
                                                        <p class="mb-0" style="font-size: 12px">No se encontraron comprobantes.</p>
                                                    </td>
                                                </tr>
                                            @endif
                                        </x-slot>
                                    </x-table-general>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div wire:loading wire:target="seleccionarFactura" class="overlay__eliminar">
                    <div class="spinner__container__eliminar">
                        <div class="spinner__eliminar"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body table-responsive">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row align-items-center">
                                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">Facturas Seleccionadas</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        @if(count($facturasCreditoAprobadas) > 0)
                                            <x-table-general id="ederTable">
                                                <x-slot name="thead">
                                                    <tr>
                                                        <th class="">Serie / Guía</th>
                                                        <th class="">F. Emisión</th>
                                                        <th class="">Importe sin IGV</th>
                                                        <th class="">Nombre Cliente</th>
                                                        <th class="">Peso y Volumen</th>
                                                        <th class="">Dirección</th>
                                                    </tr>
                                                </x-slot>
                                                <x-slot name="tbody">
                                                    @foreach($facturasCreditoAprobadas as $factura)
                                                        <tr>
                                                            <td>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    {{ $factura->fac_pre_prog_cfnumser }} - {{ $factura->fac_pre_prog_cfnumdoc }}
                                                                </span>
                                                                @php
                                                                    $guia2 = $me->formatearCodigo($factura->fac_pre_prog_guia)
                                                                @endphp
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    {{ $guia2 }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    {{ date('d/m/Y', strtotime($factura->fac_pre_prog_grefecemision)) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura->fac_pre_prog_cfimporte ?? 0) }}</b>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    {{ $factura->fac_pre_prog_cnomcli }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura->fac_pre_prog_total_kg ?? 0) }} kg</b>
                                                                </span>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura->fac_pre_prog_total_volumen ?? 0) }} cm³</b>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    {{ $factura->fac_pre_prog_direccion_llegada }}
                                                                </span>
                                                                <br>
                                                                <span class="d-block tamanhoTablaComprobantes" style="color: black;font-weight: bold">
                                                                    {{ $factura->fac_pre_prog_departamento }} - {{ $factura->fac_pre_prog_provincia }} - {{ $factura->fac_pre_prog_distrito }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </x-slot>
                                            </x-table-general>
                                        @else
                                            <p>No hay facturas aprobadas para crédito.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>

@script
<script>
    $wire.on('hidemodalMotCre', () => {
        $('#modalMotCre').modal('hide');
    });
</script>
@endscript
