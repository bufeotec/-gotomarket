<div>
    @php
        $me = new \App\Models\General();
    @endphp
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
                                    <p>S/ {{ $me->formatoDecimal($detalle_tarifario->tarifa_monto) }}</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    @php
                                        $modalCapMin = "0";
                                        if ($detalle_tarifario->tarifa_cap_min){
                                            $modalCapMin = $me->formatoDecimal($detalle_tarifario->tarifa_cap_min);
                                        }
                                    @endphp
                                    <strong style="color: #8c1017">Capacidad en minima:</strong>
                                    <p>{{ $modalCapMin }} {{$detalle_tarifario->id_medida == 9 ? 'cm³' : 'kg' }}</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    @php
                                        $modalCapMax = "0";
                                        if ($detalle_tarifario->tarifa_cap_max){
                                            $modalCapMax = $me->formatoDecimal($detalle_tarifario->tarifa_cap_max);
                                        }
                                    @endphp
                                    <strong style="color: #8c1017">Capacidad maxima:</strong>
                                    <p>{{ $modalCapMax }} {{$detalle_tarifario->id_medida == 9 ? 'cm³' : 'kg' }}</p>
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

    {{--    MODAL AGREGAR OTROS GASTOS --}}
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
{{--                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">--}}
{{--                    <label for="despacho_ayudante" class="form-label">Mano de obra S/</label>--}}
{{--                    <input type="text" class="form-control" id="despacho_ayudante" name="despacho_ayudante" wire:input="calcularCostoTotal" wire:model="despacho_ayudante" onkeyup="validar_numeros(this.id)" />--}}
{{--                </div>--}}
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
                        @if($select_nombre_cliente)
                            <div class="row">
                                <div class="col-lg-6 col-md-2 col-sm-12 mb-2">
                                    <input type="date" name="fecha_desde" id="fecha_desde" wire:model.live="desde" min="2025-01-01"  class="form-control">
                                </div>
                                <div class="col-lg-6 col-md-2 col-sm-12 mb-2">
                                    <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model.live="hasta"  min="2025-01-01"   class="form-control">
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-lg-12">
                                {{-- Buscador --}}
                                <div class="row align-items-center">
                                    <div class="col-lg-9 col-md-9 col-sm-12 mb-2">
                                        <div class="position-relative ">
                                            <input
                                                type="text"
                                                class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                                                placeholder="{{ $selectedCliente ? 'Buscar comprobante' : 'Buscar cliente' }}"
                                                wire:model="{{ $selectedCliente ? 'searchComprobante' : 'searchCliente' }}"
{{--                                                wire:change="{{ $selectedCliente ? 'buscar_comprobante' : 'buscar_cliente' }}"--}}
                                                value="{{ $selectedCliente ? $searchComprobante : $searchCliente }}"
                                                style="border: none; outline: none;"
                                            />
                                            <i class="fas fa-search position-absolute"
                                               style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                                        @if($selectedCliente)
                                            <button class="btn btn-sm bg-primary text-white w-100" wire:click="buscar_comprobante" >
                                                <i class="fa fa-search"></i> BUSCAR
                                            </button>
                                        @else
                                            <button class="btn btn-sm bg-primary text-white w-100" wire:click="buscar_cliente" >
                                                <i class="fa fa-search"></i> BUSCAR
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="loader mt-2" wire:loading wire:target="buscar_comprobante"></div>
                                    <div class="loader mt-2" wire:loading wire:target="buscar_cliente"></div>
                                </div>



                                @if(!$selectedCliente)
                                    {{-- Mostrar clientes cuando no hay cliente seleccionado --}}
                                    @if(!empty($searchCliente))
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="contenedor-comprobante" style="max-height: 600px; overflow: auto">
                                                    <x-table-general>
                                                        <x-slot name="thead">
                                                            <tr>
                                                                <th style="font-size: 12px">Nombre del Cliente</th>
                                                                <th style="font-size: 12px">RUC O DNI</th>
                                                                <th style="font-size: 12px">Dirección Fiscal</th>
                                                            </tr>
                                                        </x-slot>
                                                        <x-slot name="tbody">
                                                            @if(count($filteredClientes) == 0 )
                                                                <p>No se encontró el cliente.</p>
                                                            @else
                                                                @foreach($filteredClientes as $factura)
                                                                    <tr style="cursor: pointer" wire:click="seleccionar_cliente('{{$factura->CCODCLI}}')">
                                                                        <td style="width: 39.6%">
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                {{ $factura->CNOMCLI }}
                                                                            </span>
                                                                            </td>
                                                                            <td style="width: 32.2%">
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                <b class="colorBlackComprobantes">{{ $factura->CCODCLI }}</b>
                                                                            </span>
                                                                            </td>
                                                                            <td>
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                {{ $factura->CDIRCLI }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </x-slot>
                                                    </x-table-general>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    {{-- Mostrar comprobantes cuando un cliente está seleccionado --}}
                                    <div class="row mt-3">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="contenedor-comprobante" style="max-height: 600px; overflow: auto">
                                                <x-table-general>
                                                    <x-slot name="thead">
                                                        <tr>
                                                            <th style="font-size: 12px">Serie y Correlativo / Guía</th>
                                                            <th style="font-size: 12px">Peso y Volumen</th>
                                                            <th style="font-size: 12px">Dirección</th>
                                                        </tr>
                                                    </x-slot>
                                                    <x-slot name="tbody">
                                                        @if(!empty($filteredComprobantes))
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
                                                                                {{ $guia}}
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
                                                                        <td>
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                {{ $factura->LLEGADADIRECCION }} <br> UBIGEO: <b style="color: black">{{ $factura->DEPARTAMENTO }} - {{ $factura->PROVINCIA }} - {{ $factura->DISTRITO }}</b>
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="3">
                                                                    <p class="text-center mb-0" style="font-size: 12px">No se encontró comprobantes.</p>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </x-slot>
                                                </x-table-general>
                                            </div>
                                        </div>
                                    </div>
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

        <div class="col-lg-7">
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
                                <select class="form-select" name="id_transportistas" id="id_transportistas" wire:model="id_transportistas" wire:change="listar_tarifarios_su">
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
                                        <p class="text-end mb-0">Monto de la tarifa seleccionado:
                                            <span class="font-bold badge bg-label-success curso-pointer" data-bs-toggle="modal" data-bs-target="#modalMontoModificado">
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
                                        @foreach($tarifariosSugeridos as $index => $tari)
                                            <div class="position-relative mx-2">
                                                @if($tari->tarifa_estado_aprobacion == 1)
                                                    <input type="radio" name="vehiculo" id="id_check_vehiculo_{{ $tari->id_tarifario}}_{{$conteoGen}}" class="inputCheckRadio" value="{{ $tari->id_tarifario }}" wire:model="selectedTarifario"  wire:click="seleccionarTarifario({{ $tari->id_tarifario }})" />
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
                                                        @php
                                                            $colorCapacidad = $me->obtenerColorPorPorcentaje($tari->capacidad_usada);
                                                        @endphp
                                                        <svg class="progreso-circular" viewBox="0 0 36 36">
                                                            <path class="progreso-circular-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                            <path class="progreso-circular-fg"
                                                                  stroke-dasharray="{{ $tari->capacidad_usada }}, 100"
                                                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                                  style="stroke: {{ $colorCapacidad }};" />
                                                        </svg>
                                                        <div class="circulo-vehiculo">
                                                            <span class="tarifa-monto d-block" style="margin-top: 20px;">
                                                                @php
                                                                    $tarifa = "0";
                                                                    if ($tari->tarifa_monto){
                                                                        $tarifa = $me->formatoDecimal($tari->tarifa_monto);
                                                                    }
                                                                @endphp
                                                                S/ {{ $tarifa }}
                                                            </span>
                                                            <span class="capacidad-peso d-block">
                                                                @php
                                                                    $pesovehiculoMin = "0";
                                                                    if ($tari->tarifa_cap_min){
                                                                        $pesovehiculoMin = $me->formatoDecimal($tari->tarifa_cap_min);
                                                                    }
                                                                @endphp
                                                                @php
                                                                    $pesovehiculoMax = "0";
                                                                    if ($tari->tarifa_cap_max){
                                                                        $pesovehiculoMax = $me->formatoDecimal($tari->tarifa_cap_max);
                                                                    }
                                                                @endphp
                                                                {{$pesovehiculoMin}} {{$tari->id_medida == 9 ? 'cm³' : 'kg' }} - {{ $pesovehiculoMax }} {{$tari->id_medida == 9 ? 'cm³' : 'kg' }}
                                                            </span>

                                                            <div class="boton-container">
                                                                <a href="#" class="btn-ver" data-bs-toggle="modal" data-bs-target="#modalVehiculo" wire:click="modal_detalle_tarifario({{ $tari->id_tarifario }})">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </label>
                                                    @php
                                                        $capacidadPorcentaje = "0";
                                                        if ($tari->capacidad_usada){
                                                            $capacidadPorcentaje = $me->formatoDecimal($tari->capacidad_usada);
                                                        }
                                                        $colorPorcentaje = $me->obtenerColorPorPorcentaje($capacidadPorcentaje);
                                                    @endphp
                                                    <div class="row">
                                                        <div class="col-lg-12 text-center">
                                                            <span class="d-block text-black tamanhoTablaComprobantes"><b>Peso:</b></span>
                                                            <div class="tamanhoTablaComprobantes" style="color: {{ $colorPorcentaje }};">
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
                                                    $divisor2 = $importeTotalVenta != 0 ? $importeTotalVenta : 1;
                                                    $to = ($costoTotal / $divisor2) * 100;
                                                @endphp
                                                F / V: <b class="colorBlackComprobantes">{{$me->formatoDecimal($costoTotal)}}</b> / <b class="colorBlackComprobantes">{{$me->formatoDecimal($importeTotalVenta)}}</b> =  <span>{{ $me->formatoDecimal($to) }} %</span>
                                            </small>
                                        @endif
                                        @if($costoTotal && $pesoTotal)
                                            <small class="textTotalComprobantesSeleccionados">
                                                @php
                                                    $to2 = $costoTotal / $pesoTotal;
                                                @endphp
                                                F / P: <b class="colorBlackComprobantes">{{$me->formatoDecimal($costoTotal)}}</b> / <b class="colorBlackComprobantes">{{$me->formatoDecimal($pesoTotal)}}</b> =  <span>{{ $me->formatoDecimal($to2) }}</span>
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
{{--                                                <th class="">Nombre Cliente</th>--}}
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
                                                            $guiaTable = $me->formatearCodigo($factura['guia'])
                                                        @endphp
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $guiaTable }}
                                                        </span>
                                                    </td>
                                                    @php
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
                                                            <b class="colorBlackComprobantes">{{ $importe }} </b>
                                                        </span>
                                                    </td>
{{--                                                    <td>--}}
{{--                                                        <span class="d-block tamanhoTablaComprobantes">--}}
{{--                                                            {{ $factura['CNOMCLI'] }}--}}
{{--                                                        </span>--}}
{{--                                                    </td>--}}
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
                                                            <b class="colorBlackComprobantes">{{ $pesoTabla }} kg</b>
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
