<div>
    <!-- MODAL DETALLE DE VEHÍCULO -->
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="id_modal">modalVehiculo</x-slot>
        <x-slot name="titleModal">Detalles del Vehículo</x-slot>
        <x-slot name="modalContent">
            @if($detalle_vehiculo)
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
                                    <p>{{ $detalle_vehiculo->transportista_nom_comercial }}</p>
                                </div>
                                <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">RUC:</strong>
                                    <p>{{ $detalle_vehiculo->transportista_ruc }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <h6>Información del vehículo</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Placa del vehículo:</strong>
                                    <p>{{ $detalle_vehiculo->vehiculo_placa }}</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Capacidad en peso:</strong>
                                    <p>{{ (substr(number_format($detalle_vehiculo->vehiculo_capacidad_peso, 2, '.', ','), -3) == '.00') ? number_format($detalle_vehiculo->vehiculo_capacidad_peso, 0, '.', ',') : number_format($detalle_vehiculo->vehiculo_capacidad_peso, 2, '.', ',') }} kg</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Ancho:</strong>
                                    <p>{{ (substr(number_format($detalle_vehiculo->vehiculo_ancho, 2, '.', ','), -3) == '.00') ? number_format($detalle_vehiculo->vehiculo_ancho, 0, '.', ',') : number_format($detalle_vehiculo->vehiculo_ancho, 2, '.', ',') }} cm</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Largo:</strong>
                                    <p>{{ (substr(number_format($detalle_vehiculo->vehiculo_largo, 2, '.', ','), -3) == '.00') ? number_format($detalle_vehiculo->vehiculo_largo, 0, '.', ',') : number_format($detalle_vehiculo->vehiculo_largo, 2, '.', ',') }} cm</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Alto:</strong>
                                    <p>{{ (substr(number_format($detalle_vehiculo->vehiculo_alto, 2, '.', ','), -3) == '.00') ? number_format($detalle_vehiculo->vehiculo_alto, 0, '.', ',') : number_format($detalle_vehiculo->vehiculo_alto, 2, '.', ',') }} cm</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Volumen:</strong>
                                    <p>{{ (substr(number_format($detalle_vehiculo->vehiculo_capacidad_volumen, 2, '.', ','), -3) == '.00') ? number_format($detalle_vehiculo->vehiculo_capacidad_volumen, 0, '.', ',') : number_format($detalle_vehiculo->vehiculo_capacidad_volumen, 2, '.', ',') }} cm³</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @endif
        </x-slot>
    </x-modal-general>
    <x-modal-general  wire:ignore.self >
{{--        <x-slot name="tama">modal-lg</x-slot>--}}
        <x-slot name="id_modal">modalRegistrarGastos</x-slot>
        <x-slot name="titleModal">Registrar Gastos Operativos</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                    <label for="despacho_gasto_otros" class="form-label">Otros S/</label>
                    <input type="text" class="form-control" id="despacho_gasto_otros" name="despacho_gasto_otros" wire:input="calcularCostoTotal" wire:model="despacho_gasto_otros" onkeyup="validar_numeros(this.id)" />
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                    <label for="despacho_ayudante" class="form-label">Mano de obra S/</label>
                    <input type="text" class="form-control" id="despacho_ayudante" name="despacho_ayudante" wire:input="calcularCostoTotal" wire:model="despacho_ayudante" onkeyup="validar_numeros(this.id)" />
                </div>
            </div>
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
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <h6>COMPROBANTES</h6>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                            <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" wire:change="buscar_comprobantes" class="form-control">
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                            <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" wire:change="buscar_comprobantes" class="form-control">
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="position-relative">
                                <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder" placeholder="Buscar comprobante" wire:model="searchFactura" wire:change="buscar_comprobantes" style="border: none; outline: none;" />
                                <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                            </div>
                        </div>
                    </div>
                    @if($searchFactura !== '')
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
                                    @if(count($filteredFacturas) > 0 )
                                        @foreach($filteredFacturas as $factura)
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
                                                <tr style="cursor: pointer" wire:click="seleccionarFactura('{{$factura->CFTD}}','{{ $factura->CFNUMSER }}','{{ $factura->CFNUMDOC }}')">
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
                                                                    <td style="width: 32.2%">
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->CNOMCLI }}
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
                    @endif
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
                {{--    TRANSPORTISTA   --}}
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Lista de transportistas</h6>
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
                {{--    FECHA DE ENTREGA    --}}
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="card ">
                        <div class="card-body">
                            <h6>Fecha de despacho</h6>
                            <input type="date" class="form-control" id="programacion_fecha" name="programacion_fecha" wire:model="programacion_fecha" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- VEHICULOS --}}
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                <h6>Vehículos Sugeridos</h6>
                            </div>
                            @if($tarifaMontoSeleccionado > 0)
                                <div class="col-lg-8 col-md-8 col-sm-12 mb-2">
                                    <p class="text-end mb-0">Monto de la tarifa del vehículo seleccionado:
                                        <span class="font-bold badge bg-label-success curso-pointer" data-bs-toggle="modal" data-bs-target="#modalRegistrarGastos" >
                                            S/ {{ $tarifaMontoSeleccionado }}
                                        </span>
                                    </p>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="vehiculos-scroll-container-horizontal">
                                    @php $conteoGen = 1; @endphp
                                    @foreach($vehiculosSugeridos as $index => $vehiculo)
                                        <div class="position-relative mx-2">
                                            @if($vehiculo->tarifa_estado_aprobacion == 1)
                                                <input type="radio"  name="vehiculo" id="id_check_vehiculo_{{ $vehiculo->id_vehiculo }}_{{ $vehiculo->id_tarifario}}_{{$conteoGen}}" class="inputCheckRadio" value="{{ $vehiculo->id_vehiculo }}-{{ $vehiculo->id_tarifario }}" wire:click="seleccionarVehiculo({{ $vehiculo->id_vehiculo }},{{ $vehiculo->id_tarifario }})" />
                                                <label for="id_check_vehiculo_{{ $vehiculo->id_vehiculo }}_{{ $vehiculo->id_tarifario}}_{{$conteoGen}}" class="labelCheckRadios">
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

                                            <label class="circulo-vehiculo-container m-2 {{ $vehiculo->tarifa_estado_aprobacion == 0 ? 'no-aprobado' : '' }}" for="id_check_vehiculo_{{ $vehiculo->id_vehiculo }}_{{ $vehiculo->id_tarifario}}_{{$conteoGen}}">
                                                <svg class="progreso-circular" viewBox="0 0 36 36">
                                                    <path class="progreso-circular-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                    <path class="progreso-circular-fg"
                                                          stroke-dasharray="{{ $vehiculo->vehiculo_capacidad_usada }}, 100"
                                                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                          style="stroke: {{$vehiculo->vehiculo_capacidad_usada <= 25 ? 'red' :
                                                                    ($vehiculo->vehiculo_capacidad_usada <= 50 ? 'orange' :
                                                                    ($vehiculo->vehiculo_capacidad_usada <= 75 ? 'yellow' : 'green'))
                                                                }};" />
                                                </svg>
                                                <div class="circulo-vehiculo">
                                                    <span class="vehiculo-placa d-block">{{ $vehiculo->vehiculo_placa }}</span>
                                                    <span class="tarifa-monto d-block">
                                                            @php
                                                                $tarifa = number_format($vehiculo->tarifa_monto, 2, '.', ',');
                                                                $tarifa = strpos($tarifa, '.00') !== false ? number_format($vehiculo->tarifa_monto, 0, '.', ',') : $tarifa;
                                                            @endphp
                                                            S/ {{ $tarifa }}
                                                    </span>
                                                    <span class="capacidad-peso d-block">
                                                        @php
                                                            $pesovehiculo = number_format($vehiculo->vehiculo_capacidad_peso, 2, '.', ',');
                                                            $pesovehiculo = strpos($pesovehiculo, '.00') !== false ? number_format($vehiculo->vehiculo_capacidad_peso, 0, '.', ',') : $pesovehiculo;
                                                        @endphp
                                                    {{ $pesovehiculo }} kg
                                                    </span>
                                                    <span class="capacidad-peso d-block">
                                                            @php
                                                                $pesovolumen = number_format($vehiculo->vehiculo_capacidad_volumen, 2, '.', ',');
                                                                $pesovolumen = strpos($pesovolumen, '.00') !== false ? number_format($vehiculo->vehiculo_capacidad_volumen, 0, '.', ',') : $pesovolumen;
                                                            @endphp
                                                        {{ $pesovolumen }} cm³
                                                    </span>
                                                    <div class="boton-container">
                                                        <a href="#" class="btn-ver" data-bs-toggle="modal" data-bs-target="#modalVehiculo" wire:click="modal_por_vehiculo({{ $vehiculo->id_vehiculo }})">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </label>
                                                @php
                                                    $me = new \App\Models\General();
                                                    $pesoPorcentaje = "0";
                                                    if ($vehiculo->vehiculo_capacidad_usada){
                                                        $pesoPorcentaje = $me->formatoDecimal($vehiculo->vehiculo_capacidad_usada);
                                                    }
                                                @endphp
                                                @php
                                                    $me = new \App\Models\General();
                                                    $volumenPorcentaje = "0";
                                                    if ($vehiculo->vehiculo_volumen_usado){
                                                        $volumenPorcentaje = $me->formatoDecimal($vehiculo->vehiculo_volumen_usado);
                                                    }
                                                @endphp
                                                <div class="row">
                                                    <div class="col-lg-6 text-center">
                                                        <span class="d-block text-black tamanhoTablaComprobantes"><b>Peso:</b></span>
                                                        <div class="tamanhoTablaComprobantes" style="color: {{ $pesoPorcentaje <= 25 ? 'red' : ($pesoPorcentaje <= 50 ? 'orange' : ($pesoPorcentaje <= 75 ? 'yellow' : 'green')) }};font-weight: bold">
                                                            <span>{{ $pesoPorcentaje }}%</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 text-center">
                                                        <span class="d-block text-black tamanhoTablaComprobantes"><b>Volumen:</b></span>
                                                        <div class="tamanhoTablaComprobantes" style="color: {{ $volumenPorcentaje <= 25 ? 'red' : ($volumenPorcentaje <= 50 ? 'orange' : ($volumenPorcentaje <= 75 ? 'yellow' : 'green')) }};font-weight: bold">
                                                            <span>{{ $volumenPorcentaje }}%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                        @php $conteoGen++; @endphp
                                    @endforeach
                                </div>
                                @error('selectedVehiculo')
                                    <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            {{--    TABLA DE COMPROBANTES SELECCIONADOS --}}
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body table-responsive">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Comprobantes Seleccionadas</h6>
                                    <div class="">
                                        <span class="font-bold badge bg-label-success curso-pointer" data-bs-toggle="modal" data-bs-target="#modalRegistrarGastos" >
                                            Registrar Gastos Operativo
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <div class="row">
                                    <div class="col-lg-5 col-md-5 col-sm-12 text-start">
                                        @php
                                            $me = new \App\Models\General();
                                            $peso = "0";
                                            if ($pesoTotal){
                                                $peso = $me->formatoDecimal($pesoTotal);
                                            }
                                        @endphp
                                        @php
                                            $me = new \App\Models\General();
                                            $volumen = "0";
                                            if ($volumenTotal){
                                                $volumen = $me->formatoDecimal($volumenTotal);
                                            }
                                        @endphp
                                        <small class="textTotalComprobantesSeleccionados me-2">
                                            Kg: <span>{{ $peso }}</span>
                                        </small>
                                        <small class="textTotalComprobantesSeleccionados">
                                            Cm³: <span>{{ $volumen }}</span>
                                        </small>
                                    </div>
                                    <div class="col-lg-7 col-md-7 col-sm-12 text-end">
                                        @if($costoTotal && $importeTotalVenta)
                                            <small class="textTotalComprobantesSeleccionados me-2">
                                                @php
                                                    $me = new \App\Models\General();
                                                    $ra1 = 0;
                                                    if ($volumenTotal){
                                                        $to = $costoTotal / $importeTotalVenta;
                                                        $ra1 = $me->formatoDecimal($to);
                                                    }
                                                @endphp

                                                F.V: {{$costoTotal}} / {{$importeTotalVenta}} =  <span>{{ $ra1 }}</span>
                                            </small>
                                        @endif
                                        @if($costoTotal && $peso)
                                            <small class="textTotalComprobantesSeleccionados">
                                                @php
                                                    $me = new \App\Models\General();
                                                    $ra2 = 0;
                                                    if ($volumenTotal){
                                                        $to = $costoTotal / $peso;
                                                        $ra2 = $me->formatoDecimal($to);
                                                    }
                                                @endphp

                                                F.P: {{$costoTotal}} / {{$peso}} =  <span>{{ $ra2 }}</span>
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                @if(count($selectedFacturas) > 0)
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th class="">Serie / Guía</th>
                                                <th class="">F. Emisión</th>
                                                <th class="">Importe sin IGV</th>
                                                <th class="">Nombre Cliente</th>
                                                <th class="">Peso y Volumen</th>
                                                <th class="">Dirección</th>
                                                <th class="">Acciones</th>
                                            </tr>
                                        </x-slot>
                                        <x-slot name="tbody">
                                            @foreach($selectedFacturas as $factura)
                                                <tr>
                                                    <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura['CFNUMSER'] }} - {{ $factura['CFNUMDOC'] }}
                                                        </span>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura['guia'] }}
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
                                                        $fe = new \App\Models\General();
                                                        $feFor = "";
                                                        if ($factura['GREFECEMISION']){
                                                            $feFor = $fe->obtenerNombreFecha($factura['GREFECEMISION'],'DateTime','Date');
                                                        }
                                                    @endphp
                                                    <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $feFor }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $importe }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura['CNOMCLI'] }}
                                                        </span>
                                                    </td>
                                                    @php
                                                        $me = new \App\Models\General();
                                                        $pesoTabla = "0";
                                                        if ($factura['total_kg']){
                                                            $pesoTabla = $me->formatoDecimal($factura['total_kg']);
                                                        }
                                                    @endphp
                                                    @php
                                                        $me = new \App\Models\General();
                                                        $volumenTabla = "0";
                                                        if ($factura['total_volumen']){
                                                            $volumenTabla = $me->formatoDecimal($factura['total_volumen']);
                                                        }
                                                    @endphp
                                                    <td>
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                               {{ $pesoTabla }} kg
                                                            </span>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                 {{ $volumenTabla }} cm³
                                                            </span>
                                                    </td>
                                                    <td>
                                                             <span class="d-block tamanhoTablaComprobantes">
                                                                 {{ $factura['LLEGADADIRECCION'] }}
                                                             </span>
                                                        <br>
                                                        <span class="d-block tamanhoTablaComprobantes" style="color: black;font-weight: bold">
                                                                 {{ $factura['DEPARTAMENTO'] }} - {{ $factura['PROVINCIA'] }}- {{ $factura['DISTRITO'] }}
                                                             </span>
                                                    </td>
                                                    <td>
                                                        <a href="#" wire:click.prevent="eliminarFacturaSeleccionada('{{$factura['CFTD']}}','{{ $factura['CFNUMSER'] }}','{{ $factura['CFNUMDOC'] }}')" class="btn btn-danger btn-sm text-white">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </x-slot>
                                    </x-table-general>
                                @else
                                    <p>No hay comprobantes seleccionadas.</p>
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
