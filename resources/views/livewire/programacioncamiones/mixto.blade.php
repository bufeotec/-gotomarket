<div>
    {{----}}
{{--    <x-modal-general  wire:ignore.self >--}}
{{--        <x-slot name="tama">modal-lg</x-slot>--}}
{{--        <x-slot name="id_modal">modalDetalleTarifa</x-slot>--}}
{{--        <x-slot name="titleModal">Detalles de la Tarifa</x-slot>--}}
{{--        <x-slot name="modalContent">--}}
{{--            @if($detalle_tarifario)--}}
{{--                <div class="modal-body">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                    <h6>Información del transportista</h6>--}}
{{--                                    <hr>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-6 col-md-4 col-sm-12 mb-3">--}}
{{--                                    <strong style="color: #8c1017">Nombre comercial:</strong>--}}
{{--                                    <p>{{ $detalle_tarifario->transportista_nom_comercial }}</p>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-6 col-md-4 col-sm-12 mb-3">--}}
{{--                                    <strong style="color: #8c1017">RUC:</strong>--}}
{{--                                    <p>{{ $detalle_tarifario->transportista_ruc }}</p>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                    <h6>Información de la tarifa</h6>--}}
{{--                                    <hr>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">--}}
{{--                                    <strong style="color: #8c1017">Precio:</strong>--}}
{{--                                    <p>S/ {{ $detalle_tarifario->tarifa_monto }}</p>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">--}}
{{--                                    <strong style="color: #8c1017">Capacidad en minima:</strong>--}}
{{--                                    <p>{{ (substr(number_format($detalle_tarifario->tarifa_cap_min, 2, '.', ','), -3) == '.00') ? number_format($detalle_tarifario->tarifa_cap_min, 0, '.', ',') : number_format($detalle_tarifario->tarifa_cap_min, 2, '.', ',') }} {{$detalle_tarifario->id_medida == 9 ? 'cm³' : 'kg' }}</p>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">--}}
{{--                                    <strong style="color: #8c1017">Capacidad maxima:</strong>--}}
{{--                                    <p>{{ (substr(number_format($detalle_tarifario->tarifa_cap_max, 2, '.', ','), -3) == '.00') ? number_format($detalle_tarifario->tarifa_cap_max, 0, '.', ',') : number_format($detalle_tarifario->tarifa_cap_max, 2, '.', ',') }} {{$detalle_tarifario->id_medida == 9 ? 'cm³' : 'kg' }}</p>--}}
{{--                                </div>--}}

{{--                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">--}}
{{--                                    <strong style="color: #8c1017">Departamento de llegada:</strong>--}}
{{--                                    @php--}}
{{--                                        $depar = "";--}}
{{--                                        if ($detalle_tarifario->id_departamento){--}}
{{--                                            $depar = \Illuminate\Support\Facades\DB::table('departamentos')->where('id_departamento','=',$detalle_tarifario->id_departamento)->first();--}}
{{--                                        }--}}
{{--                                    @endphp--}}
{{--                                    <p>{{ $depar ? $depar->departamento_nombre : '-' }} </p>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">--}}
{{--                                    <strong style="color: #8c1017">Provincia de llegada:</strong>--}}
{{--                                    @php--}}
{{--                                        $provi = "";--}}
{{--                                        if ($detalle_tarifario->id_provincia){--}}
{{--                                            $provi = \Illuminate\Support\Facades\DB::table('provincias')->where('id_provincia','=',$detalle_tarifario->id_provincia)->first();--}}
{{--                                        }--}}
{{--                                    @endphp--}}
{{--                                    <p>{{ $provi ? $provi->provincia_nombre : '-' }} </p>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">--}}
{{--                                    <strong style="color: #8c1017">Distrito de llegada:</strong>--}}
{{--                                    @php--}}
{{--                                        $distri = "";--}}
{{--                                        if ($detalle_tarifario->id_distrito){--}}
{{--                                            $distri = \Illuminate\Support\Facades\DB::table('distritos')->where('id_distrito','=',$detalle_tarifario->id_distrito)->first();--}}
{{--                                        }--}}
{{--                                    @endphp--}}
{{--                                    <p>{{ $distri ? $distri->distrito_nombre : 'TODOS LOS DISTRITOS' }} </p>--}}
{{--                                </div>--}}

{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}
{{--        </x-slot>--}}
{{--    </x-modal-general>--}}
    {{-- MODAL DETALLE VEHICULO--}}
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

    {{--    MODAL DE PROGRAMACION PROVINCIAL PROVINCIALES  --}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalComprobantes</x-slot>
        <x-slot name="titleModal">Comprobantes Seleccionados</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                <!-- Lista de comprobantes -->
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <h6>Comprobantes del Cliente</h6>
                            <hr>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            @if(count($comprobantesSeleccionados) > 0)
                                @foreach($comprobantesSeleccionados as $com)
                                    <div class="row">
                                        <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                            <small>Serie y Correlativo</small>
                                            <p class="mb-0 textBlack">{{ $com['CFNUMSER'] }} - {{ $com['CFNUMDOC'] }}</p>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                            <small>Guía</small>
                                            <p class="mb-0 textBlack">{{ $com['guia'] }}</p>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                            @php
                                                $me2Pronvicial = new \App\Models\General();
                                                $fechaFormateAprobacion2 = "-";
                                                if ($com['fecha_guia']){
                                                    $fechaFormateAprobacion2 = $me2Pronvicial->obtenerNombreFecha($com['fecha_guia'],'DateTime', 'Date');
                                                }
                                            @endphp
                                            <small>Fecha de Emisión</small>
                                            <p class="mb-0 textBlack">{{ $fechaFormateAprobacion2 }}</p>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                            <small>Dirección</small>
                                            <p class="mb-0 textBlack">{{ $com['direccion_guia'] }}</p>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                            <small>Peso</small>
                                            <p class="mb-0 textBlack">{{ $com['total_kg'] }} kg</p>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                            <small>Volumen</small>
                                            <p class="mb-0 textBlack">{{ $com['total_volumen'] }} cm³</p>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                            <hr>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                        </div>
                    </div>
                </div>

                <div class="col-lg-7 col-md-7 col-sm-12">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0">Contenido adicional</h6>
                        <span class="font-bold badge {{$showBotonListo ? 'bg-label-success ' : 'bg-label-danger'}}">
                            {{$showBotonListo ? 'PROGRAMACIÓN COMPLETA ' : 'PROGRAMACIÓN INCOMPLETA'}}
                        </span>
                    </div>
                    <hr>
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Lista de transportistas</h6>
                                </div>
                                <div class="col-lg-12">
                                    <select class="form-select" name="id_trans" id="id_trans" wire:model="id_trans" wire:change="save_cliente_data({{$clienteindex}})">
                                        <option value="">Seleccionar...</option>
                                        @foreach($listar_transportistas as $lt)
                                            <option value="{{ $lt->id_transportistas }}" @selected($lt->id_transportistas == $id_trans) >
                                                {{ $lt->transportista_nom_comercial }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                            <div class="d-flex justify-content-center text-center py-1">
                                <p class="mb-0 me-2">Peso total: </p>
                                <h4 class="mb-0 text-dark">{{ $toKg }} kg</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 mb-3">
                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                    <h6>Tarifarios Sugeridos</h6>
                                </div>
{{--                                @if($tarifaMontoSeleccionado > 0)--}}
{{--                                    <div class="col-lg-8 col-md-8 col-sm-12 mb-2">--}}
{{--                                        <p class="text-end mb-0">Monto de la tarifa seleccionado: S/ <strong>{{ $tarifaMontoSeleccionado }}</strong></p>--}}
{{--                                    </div>--}}
{{--                                @endif--}}
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="vehiculos-scroll-container-horizontal">
                                        @php $conteoGen = 1; @endphp
                                        @foreach($tarifariosSugeridos as $index => $tari)
                                            <div class="position-relative mx-2">
                                                @if($tari->tarifa_estado_aprobacion == 1)
                                                    <input type="radio" name="vehiculo" id="id_check_vehiculo_{{ $tari->id_tarifario}}_{{$conteoGen}}" class="inputCheckRadio" value="{{ $tari->id_tarifario }}"  wire:model="id_tari" wire:change="save_cliente_data({{$clienteindex}})" />
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

                                                <label class="circulo-vehiculo-container m-2 {{ $tari->tarifa_estado_aprobacion == 0 ? 'no-aprobado' : '' }}" style="width: 150px!important;height: 150px!important;" for="id_check_vehiculo_{{ $tari->id_tarifario}}_{{$conteoGen}}">
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
{{--                                                            <a href="#" class="btn-ver" data-bs-toggle="modal" data-bs-target="#modalDetalleTarifa" wire:click="modal_detalle_tarifario({{ $tari->id_tarifario }})">--}}
{{--                                                                <i class="fas fa-eye"></i>--}}
{{--                                                            </a>--}}
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

                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Otros S/</h6>
                                </div>
                                <div class="col-lg-12">
                                    <input type="text" class="form-control" id="otros_gastos" name="otros_gastos" wire:model="otros_gastos" wire:change="save_cliente_data({{$clienteindex}})" onkeyup="validar_numeros(this.id)" />
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
                    </div>

                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Departamento (*)</h6>
                                </div>
                                <div class="col-lg-12">
                                    <select class="form-select" name="depar" id="depar" wire:change="deparTari" wire:model="depar">
                                        <option value="">Seleccionar...</option>
                                        @foreach($listar_departamento as $de)
                                            <option value="{{ $de->id_departamento }}" {{ $de->id_departamento == $depar ? 'selected' : '' }} >{{ $de->departamento_nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('depar')
                                        <span class="message-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Provincia (*)</h6>
                                </div>
                                <div class="col-lg-12">
                                    <select class="form-select" name="provin" id="provin" wire:model="provin" wire:change="proviTari" {{ empty($arrayProvinciaPronvicial) ? 'disabled' : '' }}>
                                        <option value="">Seleccionar...</option>
                                        @foreach($arrayProvinciaPronvicial as $pr)
                                            <option value="{{ $pr->id_provincia }}" {{ $pr->id_provincia == $provin ? 'selected' : '' }}>{{ $pr->provincia_nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('provin')
                                        <span class="message-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Distrito</h6>
                                </div>
                                <div class="col-lg-12">
                                    <select class="form-select" name="distri" id="distri"  wire:model="distri" wire:change="distriTari" {{ empty($arrayDistritoPronvicial) ? 'disabled' : '' }}>
                                        <option value="">Todos los distritos</option>
                                        @foreach($arrayDistritoPronvicial as $di)
                                            <option value="{{ $di->id_distrito }}" {{ $di->id_distrito == $distri ? 'selected' : '' }}>{{ $di->distrito_nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 mt-2 text-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
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
            {{--    BUSCADOR DE COMPROBANTES    --}}
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                            <h6>COMPROBANTES Y COMPROBANTES DE CLIENTE</h6>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" wire:change="buscar_facturas_clientes" class="form-control">
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" wire:change="buscar_facturas_clientes" class="form-control">
                            </div>
                            <div class="col-lg-12">
                                <div class="position-relative">
                                    <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                                           placeholder="Buscar comprobante o cliente"
                                           wire:model="searchFacturaCliente"
                                           wire:change="buscar_facturas_clientes"
                                           style="border: none; outline: none;" />
                                    <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                                </div>
                            </div>
                        </div>

                        @if($searchFacturaCliente !== '')
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
                                                @if(count($filteredFacturasYClientes) > 0 )
                                                    @foreach($filteredFacturasYClientes as $factura)
                                                        @php
                                                            $CFTD = $factura->CFTD;
                                                            $CFNUMSER = $factura->CFNUMSER;
                                                            $CFNUMDOC = $factura->CFNUMDOC;
                                                            $comprobanteExiste = collect($this->selectedFacturasLocal)->first(function ($facturaVa) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
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
            <!-- Overlay y Spinner solo al seleccionar una factura -->
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
                                                <input type="radio"  name="vehiculo" id="id_check_vehiculo_{{ $vehiculo->id_vehiculo }}_{{ $vehiculo->id_tarifario}}_{{$conteoGen}}" class="inputCheckRadio" value="{{ $vehiculo->id_vehiculo }}-{{ $vehiculo->id_tarifario }}"  wire:click="seleccionarVehiculo({{ $vehiculo->id_vehiculo }},{{ $vehiculo->id_tarifario }})" />
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
                                                    <div class="placa-container">
                                                        <span class="vehiculo-placa">{{ $vehiculo->vehiculo_placa }}</span>
                                                    </div>
                                                    <div class="tarifa-container">
                                                        <span class="tarifa-monto">
                                                            @php
                                                                $tarifa = number_format($vehiculo->tarifa_monto, 2, '.', ',');
                                                                $tarifa = strpos($tarifa, '.00') !== false ? number_format($vehiculo->tarifa_monto, 0, '.', ',') : $tarifa;
                                                            @endphp
                                                            S/ {{ $tarifa }}
                                                        </span>
                                                    </div>
                                                    <div class="peso-container">
                                                        <span class="capacidad-peso">
                                                            @php
                                                                $pesovehiculo = number_format($vehiculo->vehiculo_capacidad_peso, 2, '.', ',');
                                                                $pesovehiculo = strpos($pesovehiculo, '.00') !== false ? number_format($vehiculo->vehiculo_capacidad_peso, 0, '.', ',') : $pesovehiculo;
                                                            @endphp
                                                            {{ $pesovehiculo }} kg
                                                        </span>
                                                    </div>
                                                    <div class="peso-container">
                                                        <span class="capacidad-peso">
                                                            @php
                                                                $pesovolumen = number_format($vehiculo->vehiculo_capacidad_volumen, 2, '.', ',');
                                                                $pesovolumen = strpos($pesovolumen, '.00') !== false ? number_format($vehiculo->vehiculo_capacidad_volumen, 0, '.', ',') : $pesovolumen;
                                                            @endphp
                                                            {{ $pesovolumen }} cm³
                                                        </span>
                                                    </div>
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
                                                        <span class="d-block text-black"><b>Peso:</b></span>
                                                        <div style="color: {{ $pesoPorcentaje <= 25 ? 'red' : ($pesoPorcentaje <= 50 ? 'orange' : ($pesoPorcentaje <= 75 ? 'yellow' : 'green')) }};">
                                                            <span>{{ $pesoPorcentaje }}%</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 text-center">
                                                        <span class="d-block text-black"><b>Volumen:</b></span>
                                                        <div style="color: {{ $volumenPorcentaje <= 25 ? 'red' : ($volumenPorcentaje <= 50 ? 'orange' : ($volumenPorcentaje <= 75 ? 'yellow' : 'green')) }};">
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
                    <div class="card-body">
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
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
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
                        </div>

                        {{--    TABLA LOCAL --}}
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <!-- Tabla para Local -->
                                <div class="m-0 table-responsive">
                                    <h5>LOCAL</h5>
                                    <hr>
                                    @if(count($selectedFacturasLocal) > 0)
                                        <x-table-general>
                                            <x-slot name="thead">
                                                <tr>
                                                    <th class="">Provincial</th>
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
                                                @foreach($selectedFacturasLocal as $factura)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox"
                                                                   class="form-check-input"
                                                                   wire:model.defer="selectedFacturasLocal.{{ $loop->index }}.isChecked"
                                                                   wire:click="actualizarFactura('{{ $factura['CFTD'] }}', '{{ $factura['CFNUMSER'] }}', '{{ $factura['CFNUMDOC'] }}', $event.target.checked)" />
                                                        </td>
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
                                        <p>No hay comprobantes seleccionados para Local.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- TABLA PROVINCIAL -->
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="mt-5 table-responsive">
                                    <h5>PROVINCIAL</h5>
                                    <hr>
                                    @if(count($clientes_provinciales) > 0)
                                        <x-table-general>
                                            <x-slot name="thead">
                                                <tr>
                                                    <th class="">Serie / Guía</th>
                                                    <th class="">F. Emisión</th>
                                                    <th class="">Importe sin IGV</th>
                                                    <th class="">Peso y Volumen</th>
                                                    <th class="">Dirección</th>
                                                    <th class="">Acciones</th>
                                                </tr>
                                            </x-slot>
                                            <x-slot name="tbody">
                                                @foreach($clientes_provinciales as $indexCliete => $cli)
                                                    <tr>
                                                        <td colspan="4">
                                                            <h6 class="mb-0">{{ $cli['nombreCliente'] }}</h6>
                                                        </td>
                                                        <td colspan="3">
                                                            <a
                                                                href="#"
                                                                class="btn {{$cli['listo'] ? 'btn-success' : 'btn-danger'}} btn-sm text-white w-100"
                                                                data-bs-toggle="modal" data-bs-target="#modalComprobantes"
                                                                wire:click="abrirModalComprobantes('{{ $cli['codigoCliente'] }}',{{$indexCliete}})">
                                                                {{$cli['listo'] ? 'Modificar Programación' : 'Realizar Programación'}}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @foreach($cli['comprobantes'] as $comprobantes)
                                                        <tr>
                                                            <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $comprobantes['CFNUMSER'] }} - {{ $comprobantes['CFNUMDOC'] }}
                                                        </span>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $comprobantes['guia'] }}
                                                        </span>
                                                            </td>
                                                            @php
                                                                $me = new \App\Models\General();
                                                                $importe = "0";
                                                                if ($comprobantes['CFIMPORTE']){
                                                                    $importe = $me->formatoDecimal($comprobantes['CFIMPORTE']);
                                                                }
                                                            @endphp
                                                            @php
                                                                $fe = new \App\Models\General();
                                                                $feFor = "";
                                                                if ($comprobantes['GREFECEMISION']){
                                                                    $feFor = $fe->obtenerNombreFecha($comprobantes['GREFECEMISION'],'DateTime','Date');
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
                                                            @php
                                                                $me = new \App\Models\General();
                                                                $pesoTabla = "0";
                                                                if ($comprobantes['total_kg']){
                                                                    $pesoTabla = $me->formatoDecimal($comprobantes['total_kg']);
                                                                }
                                                            @endphp
                                                            @php
                                                                $me = new \App\Models\General();
                                                                $volumenTabla = "0";
                                                                if ($comprobantes['total_volumen']){
                                                                    $volumenTabla = $me->formatoDecimal($comprobantes['total_volumen']);
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
                                                                 {{ $comprobantes['LLEGADADIRECCION'] }}
                                                             </span>
                                                                <br>
                                                                <span class="d-block tamanhoTablaComprobantes" style="color: black;font-weight: bold">
                                                                 {{ $comprobantes['DEPARTAMENTO'] }} - {{ $comprobantes['PROVINCIA'] }}- {{ $comprobantes['DISTRITO'] }}
                                                             </span>
                                                            </td>
                                                            <td>
                                                                <a wire:click="eliminarFacturaProvincial('{{ $comprobantes['CFTD'] }}', '{{ $comprobantes['CFNUMSER'] }}', '{{ $comprobantes['CFNUMDOC'] }}')"
                                                                   class="btn btn-danger btn-sm text-white">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </x-slot>
                                        </x-table-general>


{{--                                        --}}
{{--                                        <table class="table">--}}
{{--                                            <thead>--}}
{{--                                                <tr>--}}
{{--                                                    <th>Serie</th>--}}
{{--                                                    <th>Guía</th>--}}
{{--                                                    <th>Fecha de Emisión</th>--}}
{{--                                                    <th>Dirección</th>--}}
{{--                                                    <th>Nombre Cliente</th>--}}
{{--                                                    <th>Peso</th>--}}
{{--                                                    <th>Volumen</th>--}}
{{--                                                    <th>Acciones</th>--}}
{{--                                                </tr>--}}
{{--                                            </thead>--}}
{{--                                            <tbody>--}}
{{--                                            @foreach($clientes_provinciales as $indexCliete => $cli)--}}
{{--                                                <tr>--}}
{{--                                                    <td colspan="4">--}}
{{--                                                        <h6 class="mb-0">{{ $cli['nombreCliente'] }}</h6>--}}
{{--                                                    </td>--}}
{{--                                                    <td colspan="3">--}}
{{--                                                        <a--}}
{{--                                                            href="#"--}}
{{--                                                            class="btn {{$cli['listo'] ? 'btn-success' : 'btn-danger'}} btn-sm text-white w-100"--}}
{{--                                                            data-bs-toggle="modal" data-bs-target="#modalComprobantes"--}}
{{--                                                            wire:click="abrirModalComprobantes('{{ $cli['codigoCliente'] }}',{{$indexCliete}})">--}}
{{--                                                            {{$cli['listo'] ? 'Modificar Programación' : 'Realizar Programación'}}--}}
{{--                                                        </a>--}}
{{--                                                    </td>--}}
{{--                                                </tr>--}}
{{--                                                @foreach($cli['comprobantes'] as $comprobantes)--}}
{{--                                                    <tr>--}}
{{--                                                        <td>{{ $comprobantes['CFNUMSER'] }} - {{ $comprobantes['CFNUMDOC'] }}</td>--}}
{{--                                                        <td>{{ $comprobantes['guia'] }}</td>--}}
{{--                                                        @php--}}
{{--                                                            $me2 = new \App\Models\General();--}}
{{--                                                            $fechaFormateAprobacion2 = "-";--}}
{{--                                                            if ($comprobantes['fecha_guia']){--}}
{{--                                                                $fechaFormateAprobacion2 = $me2->obtenerNombreFecha($comprobantes['fecha_guia'],'DateTime', 'Date');--}}
{{--                                                            }--}}
{{--                                                        @endphp--}}
{{--                                                        <td>{{ $fechaFormateAprobacion2 }}</td>--}}
{{--                                                        <td>{{ $comprobantes['direccion_guia'] }}</td>--}}

{{--                                                        @php--}}
{{--                                                            $me = new \App\Models\General();--}}
{{--                                                            $pesoTablaPro = "0";--}}
{{--                                                            if ($comprobantes['total_kg']){--}}
{{--                                                                $pesoTablaPro = $me->formatoDecimal($comprobantes['total_kg']);--}}
{{--                                                            }--}}
{{--                                                        @endphp--}}
{{--                                                        <td>{{ $pesoTablaPro }} kg</td>--}}
{{--                                                        @php--}}
{{--                                                            $me = new \App\Models\General();--}}
{{--                                                            $volumenTablaPro = "0";--}}
{{--                                                            if ($comprobantes['total_volumen']){--}}
{{--                                                                $volumenTablaPro = $me->formatoDecimal($comprobantes['total_volumen']);--}}
{{--                                                            }--}}
{{--                                                        @endphp--}}
{{--                                                        <td>{{ $volumenTablaPro }} cm³</td>--}}
{{--                                                        <td>--}}
{{--                                                            <a wire:click="eliminarFacturaProvincial('{{ $comprobantes['CFTD'] }}', '{{ $comprobantes['CFNUMSER'] }}', '{{ $comprobantes['CFNUMDOC'] }}')"--}}
{{--                                                               class="btn btn-danger btn-sm text-white">--}}
{{--                                                                <i class="fas fa-trash-alt"></i>--}}
{{--                                                            </a>--}}
{{--                                                        </td>--}}
{{--                                                    </tr>--}}
{{--                                                @endforeach--}}
{{--                                            @endforeach--}}
{{--                                            </tbody>--}}
{{--                                        </table>--}}
                                    @else
                                        <p>No hay comprobantes seleccionados para Provincial.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading indicador -->
                <div wire:loading wire:target="eliminarFacturaSeleccionada, eliminarSeleccion" class="overlay__eliminar">
                    <div class="spinner__container__eliminar">
                        <div class="spinner__eliminar"></div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="row">
                        @if(count($selectedFacturasLocal) > 0 && count($selectedFacturasProvincial) > 0)
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
        /* COMPROBANTES */
        .custom-checkbox input {
            display: none;
        }
        .custom-checkbox {
            display: block;
            position: relative;
            cursor: pointer;
            font-size: 20px;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        .checkmark {
            position: relative;
            top: 0;
            left: 0;
            height: 1.3em;
            width: 1.3em;
            background-color: #2196F300;
            border-radius: 0.25em;
            transition: all 0.25s;
        }
        .custom-checkbox input:checked ~ .checkmark {
            background-color: #e51821;
        }
        .checkmark:after {
            content: "";
            position: absolute;
            transform: rotate(0deg);
            border: 0.1em solid black;
            left: 0;
            top: 0;
            width: 1.05em;
            height: 1.05em;
            border-radius: 0.25em;
            transition: all 0.25s, border-width 0.1s;
        }
        .custom-checkbox input:checked ~ .checkmark:after {
            left: 0.50em;
            top: 0.30em;
            width: 0.30em;
            height: 0.5em;
            border-color: #fff0 white white #fff0;
            border-width: 0 0.15em 0.15em 0;
            border-radius: 0em;
            transform: rotate(45deg);
        }
        .factura-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            background-color: #f8f9fa;
            cursor: pointer;
        }
        .serie-correlativa,
        .nombre-cliente,
        .peso,
        .volumen{
            font-size: 13px;
            color: #333;
            margin: 0px 10px;
        }
        .overlay__eliminar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: transparent;
            z-index: 9998;
            display: none;
        }
        .spinner__container__eliminar {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        .spinner__eliminar {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #c3121a;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        /* VEHICULOS */
        .vehiculos-scroll-container-horizontal {
            display: flex;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        .circulo-vehiculo-container {
            position: relative;
            width: 170px;
            height: 170px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .circulo-vehiculo-container.no-aprobado {
            cursor: not-allowed;
        }
        .circulo-vehiculo {
            position: relative;
            text-align: center;
            width: 110px;
            height: 110px;
        }
        .vehiculo-placa {
            font-size: 13px;
            font-weight: bold;
            color: #333;
        }
        .tarifa-monto,
        .capacidad-peso {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .boton-container {
            margin-top: 5px;
        }
        .circulo-vehiculo-container.no-aprobado .estado-circulo {
            border: 2px solid red;
        }
        .circulo-vehiculo-container:not(.no-aprobado) .estado-circulo {
            border: 2px solid green;
        }
        .estado-circulo {
            position: absolute;
            top: -5px;
            left: -5px;
            width: 20px;
            height: 20px;
            background-color: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.2);
        }
        .check-icon,
        .warning-icon {
            font-size: 14px;
            display: none;
        }
        /* Muestra el icono de check cuando está seleccionado */
        .vehiculo-radio:checked + .progreso-circular + .circulo-vehiculo + .estado-circulo .check-icon {
            display: inline;
            color: green;
        }
        /* Muestra el icono de advertencia cuando no está aprobado */
        .no-aprobado .estado-circulo .warning-icon {
            display: inline;
            color: red;
        }
        /* Progreso Circular */
        .progreso-circular {
            position: absolute;
            width: 100%;
            height: 100%;
        }
        .progreso-circular-bg,
        .progreso-circular-fg {
            fill: none;
            stroke-width: 2.8;
        }
        .progreso-circular-bg {
            stroke: #e6e6e6;
        }
        .progreso-circular-fg {
            stroke-linecap: round;
            transition: stroke-dasharray 0.3s;
        }
    </style>

</div>
@script
<script>
    $wire.on('hideModal', () => {
        $('#modalComprobantes').modal('hide');
    });
</script>
@endscript
