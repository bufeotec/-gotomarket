<div>
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="id_modal">modalVehiculo</x-slot>
        <x-slot name="titleModal">Detalles de la Tarifa</x-slot>
        <x-slot name="modalContent">
            @if($detalle_tarifario)
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <h6>Información del transportista</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Nombre comercial:</strong>
                                    <p>{{ $detalle_tarifario->transportista_nom_comercial }}</p>
                                </div>
                                <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">RUC:</strong>
                                    <p>{{ $detalle_tarifario->transportista_ruc }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <h6>Información de la tarifa</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Precio:</strong>
                                    <p>S/ {{ $detalle_tarifario->tarifa_monto }}</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Capacidad en minima:</strong>
                                    <p>{{ (substr(number_format($detalle_tarifario->tarifa_cap_min, 2, '.', ','), -3) == '.00') ? number_format($detalle_tarifario->tarifa_cap_min, 0, '.', ',') : number_format($detalle_tarifario->tarifa_cap_min, 2, '.', ',') }} {{$detalle_tarifario->id_medida == 9 ? 'cm³' : 'kg' }}</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Capacidad maxima:</strong>
                                    <p>{{ (substr(number_format($detalle_tarifario->tarifa_cap_max, 2, '.', ','), -3) == '.00') ? number_format($detalle_tarifario->tarifa_cap_max, 0, '.', ',') : number_format($detalle_tarifario->tarifa_cap_max, 2, '.', ',') }} {{$detalle_tarifario->id_medida == 9 ? 'cm³' : 'kg' }}</p>
                                </div>

                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Departamento de llegada:</strong>
                                    @php
                                        $depar = "";
                                        if ($detalle_tarifario->id_departamento){
                                            $depar = \Illuminate\Support\Facades\DB::table('departamentos')->where('id_departamento','=',$detalle_tarifario->id_departamento)->first();
                                        }
                                    @endphp
                                    <p>{{ $depar ? $depar->departamento_nombre : '-' }} </p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Provincia de llegada:</strong>
                                    @php
                                        $provi = "";
                                        if ($detalle_tarifario->id_provincia){
                                            $provi = \Illuminate\Support\Facades\DB::table('provincias')->where('id_provincia','=',$detalle_tarifario->id_provincia)->first();
                                        }
                                    @endphp
                                    <p>{{ $provi ? $provi->provincia_nombre : '-' }} </p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Distrito de llegada:</strong>
                                    @php
                                        $distri = "";
                                        if ($detalle_tarifario->id_distrito){
                                            $distri = \Illuminate\Support\Facades\DB::table('distritos')->where('id_distrito','=',$detalle_tarifario->id_distrito)->first();
                                        }
                                    @endphp
                                    <p>{{ $distri ? $distri->distrito_nombre : 'TODOS LOS DISTRITOS' }} </p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </x-slot>
    </x-modal-general>


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
        <div class="col-lg-4">
            <div class="col-lg-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2 d-flex align-items-center justify-content-between">
                                <h6 class="mb-0">Clientes</h6>
                                @if($select_nombre_cliente)
                                    <button class="btn btn-sm" wire:click="limpiar_cliente({{ $selectedCliente }})">
                                        <i class="fa-solid fa-rotate text-warning"></i>
                                    </button>
                                @endif
                            </div>
                            @if($select_nombre_cliente)
                                <div class="col-lg-12 col-md-8 col-sm-12 mb-3">
                                    <p class="text-end mb-0"><b>{{ $select_nombre_cliente }}</b></p>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-2 col-sm-12 mb-2">
                                <input type="date" name="fecha_desde" id="fecha_desde" wire:model.live="desde" wire:change="buscar_comprobante" class="form-control">
                            </div>
                            <div class="col-lg-6 col-md-2 col-sm-12 mb-2">
                                <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model.live="hasta" wire:change="buscar_comprobante" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                {{-- Buscador --}}
                                <div class="position-relative mb-3">
                                    <input
                                        type="text"
                                        class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                                        placeholder="{{ $selectedCliente ? 'Buscar comprobante' : 'Buscar cliente' }}"
                                        wire:model="{{ $selectedCliente ? 'searchComprobante' : 'searchCliente' }}"
                                        wire:change="{{ $selectedCliente ? 'buscar_comprobante' : 'buscar_cliente' }}"
                                        value="{{ $selectedCliente ? $searchComprobante : $searchCliente }}"
                                        style="border: none; outline: none;"
                                    />
                                    <i class="fas fa-search position-absolute"
                                       style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                                </div>

                                @if(!$selectedCliente)
                                    {{-- Mostrar clientes cuando no hay cliente seleccionado --}}
                                    @if(!empty($searchCliente))

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <x-table-general>
                                                    <x-slot name="thead">
                                                        <tr>
                                                            <th style="font-size: 12px">Nombre del Cliente</th>
                                                            <th style="font-size: 12px">RUC O DNI</th>
                                                            <th style="font-size: 12px">Dirección Fiscal</th>
                                                        </tr>
                                                    </x-slot>
                                                    <x-slot name="tbody">
                                                        {{--                        <ul class="factura-list list-group list-group-flush containerSearchComprobantes">--}}
                                                        @if(count($filteredClientes) == 0 )
                                                            @foreach($filteredClientes as $factura)
                                                                <tr style="cursor: pointer" wire:click="seleccionar_cliente('{{$factura->CFTD}}','{{ $factura->CFNUMSER }}','{{ $factura->CFNUMDOC }}')">
                                                                    <td colspan="3" style="padding: 0px">
                                                                        <table class="table">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td style="width: 39.6%">
                                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                                            {{ $factura->CNOMCLI }}
                                                                                        </span>
                                                                                </td>
                                                                                <td style="width: 32.2%">
                                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                                            {{ $factura->CCODCLI }}
                                                                                        </span>
                                                                                </td>
                                                                                <td>
                                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                                            {{ $factura->CDIRCLI }} kg
                                                                                        </span>
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                            @endforeach
                                                        @else
                                                            <p>No se encontró el comprobante.</p>
                                                        @endif
                                                        {{--                        </ul>--}}
                                                    </x-slot>
                                                </x-table-general>
                                            </div>
                                        </div>







{{--                                        <div class="cliente-lista">--}}
{{--                                            @if(count($filteredClientes) == 0)--}}
{{--                                                <p>No se encontró el cliente.</p>--}}
{{--                                            @else--}}
{{--                                                @foreach($filteredClientes as $cl)--}}
{{--                                                    <div class="row factura-item align-items-center mb-2" wire:click="seleccionar_cliente({{ $cl->CCODCLI }})">--}}
{{--                                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">--}}
{{--                                                            <p class="nombre-cliente ms-0">Razón social:</p>--}}
{{--                                                            <b style="font-size: 16px;color: black">{{ $cl->CNOMCLI }}</b>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">--}}
{{--                                                            <p class="peso ms-0">RUC O DNI:</p>--}}
{{--                                                            <b style="font-size: 16px;color: black">{{ $cl->CCODCLI }}</b>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">--}}
{{--                                                            <p class="peso ms-0">Dirección Fiscal:</p>--}}
{{--                                                            <b style="font-size: 16px;color: black">{{ $cl->CDIRCLI }}</b>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                @endforeach--}}
{{--                                            @endif--}}
{{--                                        </div>--}}
                                    @endif
                                @else
                                    {{-- Mostrar comprobantes cuando un cliente está seleccionado --}}
                                    @if(!empty($searchComprobante))

                                        <div class="row mt-3">
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <x-table-general>
                                                    <x-slot name="thead">
                                                        <tr>
                                                            <th style="font-size: 12px">Serie y Correlativo / Guía</th>
                                                            <th style="font-size: 12px">Nombre del Cliente</th>
                                                            <th style="font-size: 12px">Peso y Volumen</th>
                                                        </tr>
                                                    </x-slot>
                                                    <x-slot name="tbody">
                                                        {{--                        <ul class="factura-list list-group list-group-flush containerSearchComprobantes">--}}
                                                        @if(count($filteredComprobantes) > 0 )
                                                            @foreach($filteredComprobantes as $factura)
                                                                @php
                                                                    $CFTD = $factura->CFTD;
                                                                    $CFNUMSER = $factura->CFNUMSER;
                                                                    $CFNUMDOC = $factura->CFNUMDOC;
                                                                    $comprobanteExiste = collect($this->selectedFacturas)->first(function ($facturaVa) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
                                                                        return $facturaVa['CFTD'] === $CFTD
                                                                            && $facturaVa['CFNUMSER'] === $CFNUMSER
                                                                            && $facturaVa['CFNUMDOC'] === $CFNUMDOC;
                                                                    });
                                                                @endphp
                                                                @if(!$comprobanteExiste)
                                                                    <tr style="cursor: pointer" wire:click="seleccionar_factura_cliente('{{$factura->CFTD}}','{{ $factura->CFNUMSER }}','{{ $factura->CFNUMDOC }}')">
                                                                        <td colspan="3" style="padding: 0px">
                                                                            <table class="table">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td style="width: 39.6%">
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->CFNUMSER }} - {{ $factura->CFNUMDOC }}
                                                                        </span>
                                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->CFTEXGUIA }}
                                                                        </span>
                                                                                    </td>
                                                                                    <td>
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->total_kg }} kg
                                                                        </span>
                                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->total_volumen }} cm³
                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr style="border-top: 2px solid transparent;">
                                                                                    <td colspan="3">
                                                                         <span class="d-block tamanhoTablaComprobantes">
                                                                                {{ $factura->LLEGADADIRECCION }} <br> UBIGEO: <b style="color: black">{{ $factura->DEPARTAMENTO }} - {{ $factura->PROVINCIA }} - {{ $factura->DISTRITO }}</b>
                                                                         </span>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    {{--                                        <li class="list-group-item list-group-item-action factura-item" wire:click="seleccionarFactura('{{$factura->CFTD}}','{{ $factura->CFNUMSER }}','{{ $factura->CFNUMDOC }}')">--}}
                                                                    {{--                                            <div class="row">--}}
                                                                    {{--                                                <div class="col-lg-4 col-md-4 col-sm-12">--}}
                                                                    {{--                                                    <small class="textListarComprobantes me-2">Serie y Correlativo: <span>{{ $factura->CFNUMSER }} - {{ $factura->CFNUMDOC }}</span></small>--}}
                                                                    {{--                                                    <small class="textListarComprobantes">N° Guía: <span>{{ $factura->CFTEXGUIA }}</span></small>--}}
                                                                    {{--                                                </div>--}}
                                                                    {{--                                                <div class="col-lg-3 col-md-3 col-sm-12">--}}
                                                                    {{--                                                    <small class="textListarComprobantes">Cliente: <span>{{ $factura->CNOMCLI }}</span></small>--}}
                                                                    {{--                                                </div>--}}
                                                                    {{--                                                <div class="col-lg-4 col-md-4 col-sm-12">--}}
                                                                    {{--                                                    <small class="textListarComprobantes me-2">Peso: <span>{{ $factura->total_kg }} kg</span></small>--}}
                                                                    {{--                                                    <small class="textListarComprobantes">Volumen: <span>{{ $factura->total_volumen }} cm³</span></small>--}}
                                                                    {{--                                                </div>--}}
                                                                    {{--                                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
                                                                    {{--                                                    <small class="textListarComprobantes">Dirección: <span>{{ $factura->LLEGADADIRECCION }}</span></small>--}}
                                                                    {{--                                                </div>--}}
                                                                    {{--                                            </div>--}}

                                                                    {{--                                            --}}{{--                                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-2">--}}
                                                                    {{--                                            --}}{{--                                                        <p class="peso ms-0">Importe: </p>--}}
                                                                    {{--                                            --}}{{--                                                        <b style="font-size: 16px;color: black">{{ $factura->CFIMPORTE }}</b>--}}
                                                                    {{--                                            --}}{{--                                                    </div>--}}
                                                                    {{--                                            --}}{{--                                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-2">--}}
                                                                    {{--                                            --}}{{--                                                        <p class="peso ms-0">Fecha de Emisión: </p>--}}
                                                                    {{--                                            --}}{{--                                                        <b style="font-size: 16px;color: black">{{ $factura->GREFECEMISION ? date('d-m-Y',strtotime($factura->GREFECEMISION))  : '-' }}</b>--}}
                                                                    {{--                                            --}}{{--                                                    </div>--}}
                                                                    {{--                                        </li>--}}
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="3">
                                                                    <p class="text-center mb-0" style="font-size: 12px">No se encontró comprobantes.</p>
                                                                </td>
                                                            </tr>
                                                            {{--                                <p>No se encontró el comprobante.</p>--}}
                                                        @endif
                                                        {{--                        </ul>--}}
                                                    </x-slot>
                                                </x-table-general>
                                            </div>
                                        </div>

{{--                                        <div class="comprobante-lista">--}}
{{--                                            @if(count($filteredComprobantes) == 0)--}}
{{--                                                <p>No se encontraron comprobantes.</p>--}}
{{--                                            @else--}}
{{--                                                @foreach($filteredComprobantes as $comprobante)--}}
{{--                                                    <div class="row factura-item align-items-center mb-2" wire:click="seleccionar_factura_cliente('{{ $comprobante->CFTD }}', '{{ $comprobante->CFNUMSER }}', '{{ $comprobante->CFNUMDOC }}')">--}}
{{--                                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">--}}
{{--                                                            <p class="serie-correlativa ms-0">Serie y Correlativo:</p>--}}
{{--                                                            <b style="font-size: 16px;color: black">{{ $comprobante->CFNUMSER }} - {{ $comprobante->CFNUMDOC }}</b>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">--}}
{{--                                                            <p class="serie-correlativa ms-0">N° de Guía:</p>--}}
{{--                                                            <b style="font-size: 16px;color: black">{{ $comprobante->CFTEXGUIA }}</b>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">--}}
{{--                                                            <p class="peso ms-0">Importe: </p>--}}
{{--                                                            <b style="font-size: 16px;color: black">{{ $comprobante->CFIMPORTE }}</b>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">--}}
{{--                                                            <p class="peso ms-0">Fecha de Emisión: </p>--}}
{{--                                                            <b style="font-size: 16px;color: black">{{ $comprobante->guia ? date('d-m-Y',strtotime($comprobante->guia->GREFECEMISION))  : '-' }}</b>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">--}}
{{--                                                            <p class="peso ms-0">Peso: </p>--}}
{{--                                                            <b style="font-size: 16px;color: black">{{ $comprobante->total_kg }} kg</b>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">--}}
{{--                                                            <p class="peso ms-0">Volumen: </p>--}}
{{--                                                            <b style="font-size: 16px;color: black">{{ $comprobante->total_volumen }} cm³</b>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">--}}
{{--                                                            <p class="peso ms-0">Dirección: </p>--}}
{{--                                                            <b style="font-size: 16px;color: black">{{$comprobante->guia ? $comprobante->guia->LLEGADADIRECCION : '-' }}</b>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                @endforeach--}}
{{--                                            @endif--}}
{{--                                        </div>--}}
                                    @endif

                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div wire:loading wire:target="seleccionar_factura_cliente" class="overlay__eliminar">
            <div class="spinner__container__eliminar">
                <div class="spinner__eliminar"></div>
            </div>
        </div>
        <div wire:loading wire:target="seleccionar_cliente" class="overlay__eliminar">
            <div class="spinner__container__eliminar">
                <div class="spinner__eliminar"></div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row">
                {{--    TRANSPORTISTA   --}}
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Lista de transportistas</h6>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <select class="form-select" name="id_transportistas" id="id_transportistas" wire:model="id_transportistas" wire:change="actualizarVehiculosSugeridos">
                                    <option value="">Seleccionar...</option>
                                    @foreach($listar_transportistas as $lt)
                                        <option value="{{ $lt->id_transportistas }}">{{ $lt->transportista_nom_comercial }}</option>
                                    @endforeach
                                </select>
                                @error('id_transportistas')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{--    FECHA DE ENTREGA    --}}
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Fecha de despacho</h6>
                                </div>
                                <div class="col-lg-12">
                                    <input type="date" class="form-control" id="programacion_fecha" name="programacion_fecha" wire:model="programacion_fecha" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- VEHICULOS --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                    <h6>Tarifarios Sugeridos</h6>
                                </div>
                                @if($tarifaMontoSeleccionado > 0)
                                    <div class="col-lg-8 col-md-8 col-sm-12 mb-2">
                                        <p class="text-end mb-0">Monto de la tarifa seleccionado: S/ <strong>{{ $tarifaMontoSeleccionado }}</strong></p>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="vehiculos-scroll-container-horizontal">
                                        @php $conteoGen = 1; @endphp
                                        @foreach($tarifariosSugeridos as $index => $tari)
                                            <div class="position-relative mx-2">
                                                @if($tari->tarifa_estado_aprobacion == 1)
                                                    <input type="radio" name="vehiculo" id="id_check_vehiculo_{{ $tari->id_tarifario}}_{{$conteoGen}}" class="inputCheckRadio" value="{{ $tari->id_tarifario }}"  wire:click="seleccionarTarifario({{ $tari->id_tarifario }})" />
                                                    <label for="id_check_vehiculo_{{ $tari->id_tarifario}}_{{$conteoGen}}" class="labelCheckRadios">
                                                        <div class="container_check_radios" >
                                                            <div class="cRadioBtn">
                                                                <div class="overlay"></div>
                                                                <div class="drops xsDrop"></div>
                                                                <div class="drops mdDrop"></div>
                                                                <div class="drops lgDrop"></div>
                                                            </div>
                                                        </div>
                                                    </label>
                                                @else
                                                    <label class="labelCheckRadios">
                                                        <div class="container_check_radios" >
                                                            <div class="cRadioBtnNo">
                                                                <i class="fa-solid fa-exclamation"></i>
                                                            </div>
                                                        </div>
                                                    </label>
                                                @endif

                                                    <label class="circulo-vehiculo-container m-2 {{ $tari->tarifa_estado_aprobacion == 0 ? 'no-aprobado' : '' }}" for="id_check_vehiculo_{{ $tari->id_tarifario}}_{{$conteoGen}}">
                                                        <!-- Progreso Circular usando SVG -->
                                                        <svg class="progreso-circular" viewBox="0 0 36 36">
                                                            <path class="progreso-circular-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                            <path class="progreso-circular-fg"
                                                                  stroke-dasharray="{{ $tari->capacidad_usada }}, 100"
                                                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                                  style="stroke: {{$tari->capacidad_usada <= 25 ? 'red' :
                                                                    ($tari->capacidad_usada <= 50 ? 'orange' :
                                                                    ($tari->capacidad_usada <= 75 ? 'yellow' : 'green'))
                                                                }};" />
                                                        </svg>
                                                        <div class="circulo-vehiculo">
                                                            <div class="tarifa-container" style="margin-top: 20%;">
                                                        <span class="tarifa-monto">
                                                            @php
                                                                $tarifa = number_format($tari->tarifa_monto, 2, '.', ',');
                                                                $tarifa = strpos($tarifa, '.00') !== false ? number_format($tari->tarifa_monto, 0, '.', ',') : $tarifa;
                                                            @endphp
                                                            S/ {{ $tarifa }}
                                                        </span>
                                                            </div>
                                                            <div class="peso-container">
                                                        <span class="capacidad-peso">
                                                            @php
                                                                $pesovehiculoMin = number_format($tari->tarifa_cap_min, 2, '.', ',');
                                                                $pesovehiculoMin = strpos($pesovehiculoMin, '.00') !== false ? number_format($tari->tarifa_cap_min, 0, '.', ',') : $pesovehiculoMin;
                                                                $pesovehiculo = number_format($tari->tarifa_cap_max, 2, '.', ',');
                                                                $pesovehiculo = strpos($pesovehiculo, '.00') !== false ? number_format($tari->tarifa_cap_max, 0, '.', ',') : $pesovehiculo;
                                                            @endphp
                                                            {{$pesovehiculoMin}} {{$tari->id_medida == 9 ? 'cm³' : 'kg' }} - {{ $pesovehiculo }} {{$tari->id_medida == 9 ? 'cm³' : 'kg' }}
                                                        </span>
                                                            </div>
                                                            <div class="boton-container">
                                                                <a href="#" class="btn-ver" data-bs-toggle="modal" data-bs-target="#modalVehiculo" wire:click="modal_detalle_tarifario({{ $tari->id_tarifario }})">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </label>
                                                    @php
                                                        $me = new \App\Models\General();
                                                        $capacidadPorcentaje = "0";
                                                        if ($tari->capacidad_usada){
                                                            $capacidadPorcentaje = $me->formatoDecimal($tari->capacidad_usada);
                                                        }
                                                    @endphp
                                                    <div class="row">
                                                        <div class="col-lg-12 text-center">
                                                            <span class="d-block text-black"><b>Peso:</b></span>
                                                            <div style="color: {{ $capacidadPorcentaje <= 25 ? 'red' : ($capacidadPorcentaje <= 50 ? 'orange' : ($capacidadPorcentaje <= 75 ? 'yellow' : 'green')) }};">
                                                                <span>{{ $capacidadPorcentaje }}%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                            @php $conteoGen++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                                @error('selectedTarifario')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- OTROS - MANO DE OBRA --}}
            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Otros S/</h6>
                                </div>
                                <div class="col-lg-12">
                                    <input type="text" class="form-control" id="despacho_gasto_otros" name="despacho_gasto_otros" wire:input="calcularCostoTotal" wire:model="despacho_gasto_otros" onkeyup="validar_numeros(this.id)" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($despacho_gasto_otros > 0)
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                        <h6>Descripción otros</h6>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control" id="despacho_descripcion_otros" name="despacho_descripcion_otros" wire:model="despacho_descripcion_otros"></textarea>
                                        @error('despacho_descripcion_otros')
                                        <span class="message-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Costo total</h6>
                                </div>
                                <div class="col-lg-12">
                                    <h5 class="text-end mb-0">s/ {{ number_format($costoTotal, 2, '.', ',') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{--    DEPARTAMENTO - PROVINCIA - DISTRITO --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                            <h6>Departamento (*)</h6>
                                        </div>
                                        <div class="col-lg-12">
                                            <select class="form-select" name="id_departamento" id="id_departamento" wire:change="deparTari" wire:model="id_departamento">
                                                <option value="">Seleccionar...</option>
                                                @foreach($listar_departamento as $de)
                                                    <option value="{{ $de->id_departamento }}">{{ $de->departamento_nombre }}</option>
                                                @endforeach
                                            </select>
                                            @error('id_departamento')
                                            <span class="message-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                            <h6>Provincia (*)</h6>
                                        </div>
                                        <div class="col-lg-12">
                                            <select class="form-select" name="id_provincia" id="id_provincia" wire:model="id_provincia" wire:change="proviTari" {{ empty($provincias) ? 'disabled' : '' }}>
                                                <option value="">Seleccionar...</option>
                                                @foreach($provincias as $pr)
                                                    <option value="{{ $pr->id_provincia }}" {{ $pr->id_provincia == $id_provincia ? 'selected' : '' }}>{{ $pr->provincia_nombre }}</option>
                                                @endforeach
                                            </select>
                                            @error('id_provincia')
                                            <span class="message-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                            <h6>Distrito</h6>
                                        </div>
                                        <div class="col-lg-12">
                                            <select class="form-select" name="id_distrito" id="id_distrito"  wire:change="distriTari" wire:model="id_distrito" {{ empty($distritos) ? 'disabled' : '' }}>
                                                <option value="">Todos los distritos</option>
                                                @foreach($distritos as $di)
                                                    <option value="{{ $di->id_distrito }}" {{ $di->id_distrito == $id_distrito ? 'selected' : '' }}>{{ $di->distrito_nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{--    TABLA DE COMPROBANTES SELECCIONADOS --}}
            <div class="col-lg-12 ">
                <div class="card">
                    <div class="card-body table-responsive">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">COMPROBANTES SELECCIONADAS</h6>
                                    <div class="d-flex flex-column align-items-end ml-auto">
                                        <div class="d-flex justify-content-center text-center py-1">
                                            @php
                                                $me = new \App\Models\General();
                                                $peso = "0";
                                                if ($pesoTotal){
                                                    $peso = $me->formatoDecimal($pesoTotal);
                                                }
                                            @endphp
                                            <p class="mb-0 me-2">Peso total: </p>
                                            <h4 class="mb-0 text-dark">{{ $peso }} kg</h4>
                                        </div>
                                        <div class="d-flex justify-content-center text-center py-1">
                                            @php
                                                $me = new \App\Models\General();
                                                $volumen = "0";
                                                if ($volumenTotal){
                                                    $volumen = $me->formatoDecimal($volumenTotal);
                                                }
                                            @endphp
                                            <p class="mb-0 me-2">Volumen total: </p>
                                            <h4 class="mb-0 text-dark">{{ $volumen }} cm³</h4>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    @if(count($selectedFacturas) > 0)
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Serie</th>
                                                <th>Guía</th>
                                                <th>Peso</th>
                                                <th>Volumen</th>
                                                <th>Acciones</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($selectedFacturas as $factura)
                                                <tr>
                                                    <td>{{ $factura['CFNUMSER'] }} - {{ $factura['CFNUMDOC'] }}</td>
                                                    <td>{{ $factura['guia'] }}</td>
                                                    @php
                                                        $me = new \App\Models\General();
                                                        $pesoTabla = "0";
                                                        if ($factura['total_kg']){
                                                            $pesoTabla = $me->formatoDecimal($factura['total_kg']);
                                                        }
                                                    @endphp
                                                    <td>{{ $pesoTabla }} kg</td>
                                                    @php
                                                        $me = new \App\Models\General();
                                                        $volumenTabla = "0";
                                                        if ($factura['total_volumen']){
                                                            $volumenTabla = $me->formatoDecimal($factura['total_volumen']);
                                                        }
                                                    @endphp
                                                    <td>{{ $volumenTabla }} cm³</td>
                                                    <td>
                                                        <a href="#" wire:click.prevent="eliminarFacturaSeleccionada('{{$factura['CFTD']}}','{{ $factura['CFNUMSER'] }}','{{ $factura['CFNUMDOC'] }}')" class="btn btn-danger btn-sm text-white">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p>No hay comprobantes seleccionadas.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div wire:loading wire:target="eliminarFacturaSeleccionada" class="overlay__eliminar">
                    <div class="spinner__container__eliminar">
                        <div class="spinner__eliminar"></div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="row">
                        @if(count($selectedFacturas) > 0)
                            <div class="text-center d-flex justify-content-end">
                                <a href="#" wire:click.prevent="guardarDespachos" class="btn text-white" style="background: #e51821">
                                    Guardar Despacho
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        .card {
            margin-bottom: 1rem;
            border: none;
        }
    </style>
</div>
