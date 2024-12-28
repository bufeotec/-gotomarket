<div>
    @php
        $me = new \App\Models\General();
    @endphp
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
                                    <p>{{ $me->formatoDecimal($detalle_vehiculo->vehiculo_placa) }}</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    @php
                                        $modalPeso = "0";
                                        if ($detalle_vehiculo->vehiculo_capacidad_peso){
                                            $modalPeso = $me->formatoDecimal($detalle_vehiculo->vehiculo_capacidad_peso);
                                        }
                                    @endphp
                                    <strong style="color: #8c1017">Capacidad en peso:</strong>
                                    <p>{{ $modalPeso }} kg</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    @php
                                        $modalAncho = "0";
                                        if ($detalle_vehiculo->vehiculo_ancho){
                                            $modalAncho = $me->formatoDecimal($detalle_vehiculo->vehiculo_ancho);
                                        }
                                    @endphp
                                    <strong style="color: #8c1017">Ancho:</strong>
                                    <p>{{ $modalAncho }} cm</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    @php
                                        $modalLargo = "0";
                                        if ($detalle_vehiculo->vehiculo_largo){
                                            $modalLargo = $me->formatoDecimal($detalle_vehiculo->vehiculo_largo);
                                        }
                                    @endphp
                                    <strong style="color: #8c1017">Largo:</strong>
                                    <p>{{ $modalLargo }} cm</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    @php
                                        $modalAlto = "0";
                                        if ($detalle_vehiculo->vehiculo_alto){
                                            $modalAlto = $me->formatoDecimal($detalle_vehiculo->vehiculo_alto);
                                        }
                                    @endphp
                                    <strong style="color: #8c1017">Alto:</strong>
                                    <p>{{ $modalAlto }} cm</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    @php
                                        $modalVolumen = "0";
                                        if ($detalle_vehiculo->vehiculo_capacidad_volumen){
                                            $modalVolumen = $me->formatoDecimal($detalle_vehiculo->vehiculo_capacidad_volumen);
                                        }
                                    @endphp
                                    <strong style="color: #8c1017">Volumen:</strong>
                                    <p>{{ $modalVolumen }} cm³</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @endif
        </x-slot>
    </x-modal-general>

{{--    MODAL AGREGAR OTROS GASTOS--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalRegistrarGastos</x-slot>
        <x-slot name="titleModal">Registrar Gastos Operativos</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                    <label for="despacho_gasto_otros" class="form-label">Otros S/</label>
                    <input type="text" class="form-control" id="despacho_gasto_otros" name="despacho_gasto_otros" wire:input="calcularCostoTotal" wire:model="despacho_gasto_otros" onkeyup="validar_numeros(this.id)" />
                </div>
                @if($despacho_gasto_otros > 0)
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                        <label for="despacho_descripcion_otros" class="form-label">Descripción otros</label>
                        <textarea class="form-control" id="despacho_descripcion_otros" name="despacho_descripcion_otros" wire:model="despacho_descripcion_otros"></textarea>
                        @error('despacho_descripcion_otros')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                    <label for="despacho_ayudante" class="form-label">Mano de obra S/</label>
                    <input type="text" class="form-control" id="despacho_ayudante" name="despacho_ayudante" wire:input="calcularCostoTotal" wire:model="despacho_ayudante" onkeyup="validar_numeros(this.id)" />
                </div>
            </div>
        </x-slot>
    </x-modal-general>

    <!-- MODAL MONTO MODIFICADO -->
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalMontoModificado</x-slot>
        <x-slot name="titleModal">Modificar monto</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                    <label for="despacho_monto_modificado" class="form-label">Nuevo monto</label>
                    <input type="text" class="form-control" id="despacho_monto_modificado" name="despacho_monto_modificado" wire:input="calcularCostoTotal" wire:model.live="tarifaMontoSeleccionado">
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                    <label for="despacho_descripcion_modificado" class="form-label">Descripción</label>
                    <textarea id="despacho_descripcion_modificado" class="form-control" name="despacho_descripcion_modificado" wire:model.live="despacho_descripcion_modificado"></textarea>
                    @error('despacho_descripcion_modificado')
                    <span class="message-error">{{ $message }}</span>
                    @enderror
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
                                        @if(!empty($filteredFacturas))
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
                                                    <tr style="cursor: pointer"
                                                        wire:click="seleccionarFactura('{{ $factura->CFTD }}', '{{ $factura->CFNUMSER }}', '{{ $factura->CFNUMDOC }}')">
                                                        <td colspan="3" style="padding: 0px">
                                                            <table class="table">
                                                                <tbody>
                                                                <tr>
                                                                    <td style="width: 39.6%">
                                                                        <span class="tamanhoTablaComprobantes">
                                                                            <b class="colorBlackComprobantes">{{ date('d/m/Y',strtotime($factura->GREFECEMISION)) }}</b>
                                                                        </span>
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->CFNUMSER }} - {{ $factura->CFNUMDOC }}
                                                                        </span>
                                                                        @php
                                                                            $guia = $me->formatearCodigo($factura->CFTEXGUIA)
                                                                        @endphp
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $guia }}
                                                                        </span>
                                                                    </td>
                                                                    <td style="width: 32.2%">
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->CNOMCLI }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        @php
                                                                            $tablaPeso = "0";
                                                                            if ($factura->total_kg){
                                                                                $tablaPeso = $me->formatoDecimal($factura->total_kg);
                                                                            }
                                                                        @endphp
                                                                        @php
                                                                            $tablaVolumen = "0";
                                                                            if ($factura->total_volumen){
                                                                                $tablaVolumen = $me->formatoDecimal($factura->total_volumen);
                                                                            }
                                                                        @endphp
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            <b class="colorBlackComprobantes">{{ $tablaPeso }} kg</b>
                                                                        </span>
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            <b class="colorBlackComprobantes">{{ $tablaVolumen }} cm³</b>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr style="border-top: 2px solid transparent;">
                                                                    <td colspan="3" style="padding-top: 0">
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->LLEGADADIRECCION }} <br> UBIGEO: <b class="colorBlackComprobantes">{{ $factura->DEPARTAMENTO }} - {{ $factura->PROVINCIA }} - {{ $factura->DISTRITO }}</b>
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
                {{--    TRANSPORTISTA   --}}
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Lista de transportistas</h6>
                            <select class="form-select" name="id_transportistas" id="id_transportistas" wire:model="id_transportistas" wire:change="listar_vehiculos_lo">
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
                                        <span class="font-bold badge bg-label-success curso-pointer" data-bs-toggle="modal" data-bs-target="#modalMontoModificado" >
                                            S/ {{ $me->formatoDecimal($tarifaMontoSeleccionado) }}
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
                                                <input type="radio"  name="vehiculo" id="id_check_vehiculo_{{ $vehiculo->id_vehiculo }}_{{ $vehiculo->id_tarifario}}_{{$conteoGen}}" class="inputCheckRadio" wire:model="checkInput" value="{{ $vehiculo->id_vehiculo }}-{{ $vehiculo->id_tarifario }}" wire:click="seleccionarVehiculo({{ $vehiculo->id_vehiculo }},{{ $vehiculo->id_tarifario }})" />
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
                                                @php
                                                    $colorCapacidad = $me->obtenerColorPorPorcentaje($vehiculo->vehiculo_capacidad_usada);
                                                @endphp
                                                <svg class="progreso-circular" viewBox="0 0 36 36">
                                                    <path class="progreso-circular-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                    <path class="progreso-circular-fg"
                                                          stroke-dasharray="{{ $vehiculo->vehiculo_capacidad_usada }}, 100"
                                                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                          style="stroke: {{ $colorCapacidad }};" />
                                                </svg>
                                                <div class="circulo-vehiculo">
                                                    <span class="vehiculo-placa d-block">{{ $vehiculo->vehiculo_placa }}</span>
                                                    <span class="tarifa-monto d-block">
                                                        @php
                                                            $tarifa = "0";
                                                            if ($vehiculo->tarifa_monto){
                                                                $tarifa = $me->formatoDecimal($vehiculo->tarifa_monto);
                                                            }
                                                        @endphp
                                                            S/ {{ $tarifa }}
                                                    </span>
                                                    <span class="capacidad-peso d-block">
                                                        @php
                                                            $pesovehiculo = "0";
                                                            if ($vehiculo->vehiculo_capacidad_peso){
                                                                $pesovehiculo = $me->formatoDecimal($vehiculo->vehiculo_capacidad_peso);
                                                            }
                                                        @endphp
                                                    {{ $pesovehiculo }} kg
                                                    </span>
                                                    <span class="capacidad-peso d-block">
                                                        @php
                                                            $pesovolumen = "0";
                                                            if ($vehiculo->vehiculo_capacidad_volumen){
                                                                $pesovolumen = $me->formatoDecimal($vehiculo->vehiculo_capacidad_volumen);
                                                            }
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
                                                    $pesoPorcentaje = "0";
                                                    if ($vehiculo->vehiculo_capacidad_usada){
                                                        $pesoPorcentaje = $me->formatoDecimal($vehiculo->vehiculo_capacidad_usada);
                                                    }
                                                    $colorPorcentajePeso = $me->obtenerColorPorPorcentaje($pesoPorcentaje);
                                                @endphp
                                                @php
                                                    $volumenPorcentaje = "0";
                                                    if ($vehiculo->vehiculo_volumen_usado){
                                                        $volumenPorcentaje = $me->formatoDecimal($vehiculo->vehiculo_volumen_usado);
                                                    }
                                                    $colorPorcentajeVolumen = $me->obtenerColorPorPorcentaje($volumenPorcentaje);
                                                @endphp
                                                <div class="row">
                                                    <div class="col-lg-6 text-center">
                                                        <span class="d-block text-black tamanhoTablaComprobantes"><b>Peso:</b></span>
                                                        <div class="tamanhoTablaComprobantes" style="color: {{ $colorPorcentajePeso }};font-weight: bold">
                                                            <span>{{ $pesoPorcentaje }}%</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 text-center">
                                                        <span class="d-block text-black tamanhoTablaComprobantes"><b>Volumen:</b></span>
                                                        <div class="tamanhoTablaComprobantes" style="color: {{ $colorPorcentajeVolumen }};font-weight: bold">
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
                                    <div class="col-lg-12 col-md-12 col-sm-12 text-start">
                                        @php
                                            $peso = "0";
                                            if ($pesoTotal){
                                                $peso = $me->formatoDecimal($pesoTotal);
                                            }
                                        @endphp
                                        @php
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
                                    <div class="col-lg-12 col-md-12 col-sm-12 text-end">
                                        @if($costoTotal && $importeTotalVenta)
                                            <small class="textTotalComprobantesSeleccionados me-2">
                                                @php
                                                    $to = $costoTotal / $importeTotalVenta;
                                                @endphp
                                                F / V: <b class="colorBlackComprobantes">{{$me->formatoDecimal($costoTotal)}}</b> / <b class="colorBlackComprobantes">{{$me->formatoDecimal($importeTotalVenta)}}</b> =  <span>{{ $me->formatoDecimal($to) }}</span>
                                            </small>
                                        @endif
                                        @if($costoTotal && $pesoTotal)
                                            <small class="textTotalComprobantesSeleccionados">
                                                @php
                                                    $toPeso = $costoTotal / $pesoTotal;
                                                @endphp
                                                F / P: <b class="colorBlackComprobantes">{{$me->formatoDecimal($costoTotal)}}</b> / <b class="colorBlackComprobantes">{{$me->formatoDecimal($pesoTotal)}}</b> =  <span>{{ $me->formatoDecimal($toPeso) }}</span>
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                @if(count($selectedFacturas) > 0)
                                    <x-table-general id="ederTable">
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
                                                        if ($factura['GREFECEMISION']){
                                                            $feFor = $me->obtenerNombreFecha($factura['GREFECEMISION'],'DateTime','Date');
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
                                                            {{ $factura['CNOMCLI'] }}
                                                        </span>
                                                    </td>
                                                    @php
                                                        $pesoTabla = "0";
                                                        if ($factura['total_kg']){
                                                            $pesoTabla = $me->formatoDecimal($factura['total_kg']);
                                                        }
                                                    @endphp
                                                    @php
                                                        $volumenTabla = "0";
                                                        if ($factura['total_volumen']){
                                                            $volumenTabla = $me->formatoDecimal($factura['total_volumen']);
                                                        }
                                                    @endphp
                                                    <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                           <b class="colorBlackComprobantes">{{ $pesoTabla }}  kg</b>
                                                        </span>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            <b class="colorBlackComprobantes">{{ $volumenTabla }} cm³</b>
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
@script
<script>
    document.addEventListener('livewire:navigated', () => {
        setTimeout(() => {
            $wire.buscar_comprobantes(); // Llama directamente al método Livewire
        }, 2000); // Esperar 2 segundos
    })
</script>
@endscript
