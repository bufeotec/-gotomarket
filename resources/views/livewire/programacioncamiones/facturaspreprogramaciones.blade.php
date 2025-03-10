<div>
    @php
        $me = new \App\Models\General();
    @endphp
    {{--    MODAL DETALLE --}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleFactura</x-slot>
        <x-slot name="titleModal">Detalles del Documeto Seleccionado</x-slot>
        <x-slot name="modalContent">
            <div class="modal-body">
                <h6>Detalles del Documento</h6>
                <hr>
                @if(count($selectedGuias) > 0)
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>Serie / Guía</th>
                                <th>F. Emisión</th>
                                <th>Importe sin IGV</th>
                                <th>Nombre Cliente</th>
                                <th>Dirección</th>
                            </tr>
                        </x-slot>
                        <x-slot name="tbody">
                            @foreach($selectedGuias as $factura)
                                <tr>
                                    <td>
                                    <span class="d-block tamanhoTablaComprobantes">
                                        {{ $factura['CFNUMSER'] }} - {{ $factura['CFNUMDOC'] }}
                                    </span>
                                    </td>
                                    <td>
                                        @php
                                            $feFor = $me->obtenerNombreFecha($factura['GREFECEMISION'],'DateTime','Date');
                                        @endphp
                                        <span class="d-block tamanhoTablaComprobantes">{{ $feFor }}</span>
                                    </td>
                                    <td>
                                    <span class="d-block tamanhoTablaComprobantes">
                                        <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura['CFIMPORTE']) }}</b>
                                    </span>
                                    </td>
                                    <td>
                                        <span class="d-block tamanhoTablaComprobantes">{{ $factura['CNOMCLI'] }}</span>
                                    </td>
                                    <td>
                                        <span class="d-block tamanhoTablaComprobantes">{{ $factura['LLEGADADIRECCION'] }}</span>
                                        <br>
                                        <span class="d-block tamanhoTablaComprobantes" style="color: black;font-weight: bold">
                                        {{ $factura['DEPARTAMENTO'] }} - {{ $factura['PROVINCIA'] }} - {{ $factura['DISTRITO'] }}
                                    </span>
                                    </td>
                                </tr>
                            @endforeach
                        </x-slot>
                    </x-table-general>
                @else
                    <p>No hay comprobantes seleccionadas.</p>
                @endif
            </div>
        </x-slot>
    </x-modal-general>
    {{--    FIN MODAL DETALLE --}}

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
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <h6>DOCUMENTOS</h6>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                            <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control" min="2025-01-01">
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                            <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control" min="2025-01-01">
                        </div>
                        <div class="col-lg-9 col-md-9 col-sm-12 mb-2">
                            <div class="position-relative">
                                <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder" placeholder="Buscar documento" wire:model="searchFactura" style="border: none; outline: none;" />
                                <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                            <button class="btn btn-sm bg-primary text-white w-100" wire:click="buscar_comprobantes" >
                                <i class="fa fa-search"></i> BUSCAR
                            </button>
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
                                        </tr>
                                    </x-slot>

                                    <x-slot name="tbody">
                                        @if(!empty($filteredGuias))
                                            @foreach($filteredGuias as $guia)
                                                @php
                                                    $SERIE = $guia->SERIE;
                                                    $NUMERO = $guia->NÚMERO;
                                                    $comprobanteExiste = collect($this->selectedGuias)->first(function ($facturaVa) use ($SERIE, $NUMERO) {
                                                        return $facturaVa['SERIE'] === $SERIE && $facturaVa['NUMERO'] === $NUMERO;
                                                    });
                                                @endphp
                                                @if(!$comprobanteExiste)
                                                    <tr style="cursor: pointer"
                                                        wire:click="seleccionarFactura('{{ $guia->SERIE }}', '{{ $guia->NÚMERO }}')">
                                                        <td colspan="3" style="padding: 0px">
                                                            <table class="table">
                                                                <tbody>
                                                                <tr>
                                                                    <td style="width: 39.6%">
                                                                        <span class="tamanhoTablaComprobantes">
                                                                            <b class="colorBlackComprobantes">
                                                                                {{ isset($guia->{'FECHA EMISIÓN'}) ? date('d/m/Y', strtotime($guia->{'FECHA EMISIÓN'})) : 'Sin fecha' }}
                                                                            </b>
                                                                        </span>
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $guia->SERIE }} - {{ $guia->NÚMERO }}
                                                                        </span>
                                                                    </td>
                                                                    <td style="width: 32.2%">
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ ($guia->{'NOMBRE CLIENTE'}) ?? 'Desconocido' }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            <b class="colorBlackComprobantes">{{ number_format($guia->PESO ?? 0, 2) }} kg</b>
                                                                        </span>
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            <b class="colorBlackComprobantes">{{ number_format($guia->VOLUMEN ?? 0, 2) }} cm³</b>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr style="border-top: 2px solid transparent;">
                                                                    <td colspan="3" style="padding-top: 0">
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ ($guia->{'DIRECCIÓN LLEGADA'}) ?? 'Sin dirección' }} <br>
                                                                            UBIGEO: <b class="colorBlackComprobantes">
                                                                                {{ ($guia->{'DEPARTAMENTO LLEGADA'}) ?? 'N/A' }} -
                                                                                {{ ($guia->{'PROVINCIA LLEGADA'} )?? 'N/A' }} -
                                                                                {{ ($guia->{'DISTRITO LLEGADA'}) ?? 'N/A' }}
                                                                            </b>
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
                                                    <p class="mb-0" style="font-size: 12px">No se encontraron documentos.</p>
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

        <div class="col-lg-6">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="row align-items-center">
                                        <div class="col-lg-5 col-md-12 col-sm-12 mb-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Documentos Seleccionadas</h6>
                                            </div>
                                        </div>
                                        @if(count($selectedGuias) > 0)
                                            <div class="col-lg-5 col-md-12 col-sm-12 mb-2">
                                                <h6 class="mb-1">Estado de la guía</h6>
                                                <select class="form-select" id="estado_envio" name="estado_envio" wire:model="estado_envio">
                                                    <option value="">Seleccionar...</option>
                                                    <option value="1">Creditos</option>
                                                    <option value="2">Despacho</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-2">
                                                <button href="#" class="btn bg-info text-white d-flex align-items-center" wire:click.prevent="guardarFacturas">
                                                    Enviar
                                                    <i class="fa-solid fa-right-to-bracket ms-1"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    @if(count($selectedGuias) > 0)
                                        <x-table-general id="ederTable">
                                            <x-slot name="thead">
                                                <tr>
                                                    <th class="">Serie / Guía</th>
                                                    <th class="">F. Emisión</th>
                                                    <th class="">Importe sin IGV</th>
                                                    <th class="">Nombre Cliente</th>
{{--                                                    <th class="">Peso y Volumen</th>--}}
                                                    <th class="">Dirección</th>
                                                    <th class="">Acciones</th>
                                                </tr>
                                            </x-slot>
                                            <x-slot name="tbody">
                                                @foreach($selectedGuias as $factura)
                                                    <tr>
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura['SERIE'] }} - {{ $factura['NÚMERO'] }}
                                                        </span>
                                                            @php
                                                                $guia2 = $me->formatearCodigo($factura['guia'])
                                                            @endphp
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $guia2 }}
                                                        </span>
                                                        </td>
                                                        @php
                                                            $me = new \App\Models\General();
                                                            $importe = "0";
                                                            if ($factura['CFIMPORTE']){
                                                                $importe = $me->formatoDecimal($factura['CFIMPORTE']);
                                                            }
                                                        @endphp
                                                        @php
                                                            $feFor = "";
                                                            if ($factura['FECHA EMISIÓN']){
                                                                $feFor = $me->obtenerNombreFecha($factura['FECHA EMISIÓN'],'DateTime','Date');
                                                            }
                                                        @endphp
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $feFor }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            <b class="colorBlackComprobantes">{{ $importe }}</b>
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura['NOMBRE CLIENTE'] }}
                                                        </span>
                                                        </td>
{{--                                                        @php--}}
{{--                                                            $pesoTabla = "0";--}}
{{--                                                            if ($factura['total_kg']){--}}
{{--                                                                $pesoTabla = $me->formatoDecimal($factura['total_kg']);--}}
{{--                                                            }--}}
{{--                                                        @endphp--}}
{{--                                                        @php--}}
{{--                                                            $volumenTabla = "0";--}}
{{--                                                            if ($factura['total_volumen']){--}}
{{--                                                                $volumenTabla = $me->formatoDecimal($factura['total_volumen']);--}}
{{--                                                            }--}}
{{--                                                        @endphp--}}
{{--                                                        <td>--}}
{{--                                                        <span class="d-block tamanhoTablaComprobantes">--}}
{{--                                                           <b class="colorBlackComprobantes">{{ $pesoTabla }}  kg</b>--}}
{{--                                                        </span>--}}
{{--                                                            <span class="d-block tamanhoTablaComprobantes">--}}
{{--                                                            <b class="colorBlackComprobantes">{{ $volumenTabla }} cm³</b>--}}
{{--                                                        </span>--}}
{{--                                                        </td>--}}
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                             {{ $factura['DIRECCIÓN LLEGADA'] }}
                                                        </span>
                                                            <br>
                                                            <span class="d-block tamanhoTablaComprobantes" style="color: black;font-weight: bold">
                                                             {{ $factura['DEPARTAMENTO LLEGADA'] }} - {{ $factura['PROVINCIA LLEGADA'] }}- {{ $factura['DISTRITO LLEGADA'] }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                            <a href="#" wire:click.prevent="eliminarFacturaSeleccionada('{{ $factura['SERIE'] }}','{{ $factura['NÚMERO'] }}')" class="btn btn-danger btn-sm text-white m-1">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>

                                                            <a href="#" wire:click="listar_detallesf({{$factura['CFTD']}}','{{ $factura['CFNUMSER'] }}','{{ $factura['CFNUMDOC'] }})" class="btn btn-sm btn-primary text-white m-1" data-bs-toggle="modal" data-bs-target="#modalDetalleFactura">
                                                                <i class="fas fa-eye"></i>
                                                            </a>

{{--                                                            <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($lv->id_vehiculo) }}')" data-bs-toggle="modal" data-bs-target="#modalVehiculos">--}}
{{--                                                                <x-slot name="message">--}}
{{--                                                                    <i class="fa-solid fa-pen-to-square"></i>--}}
{{--                                                                </x-slot>--}}
{{--                                                            </x-btn-accion>--}}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </x-slot>
                                        </x-table-general>
                                    @else
                                        <p>No hay documentos seleccionados.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div wire:loading wire:target="eliminarFacturaSeleccionada" class="overlay__eliminar">
                        <div class="spinner__container__eliminar">
                            <div class="spinner__eliminar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
