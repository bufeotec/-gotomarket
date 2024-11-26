<div>
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
            {{--    BUSCADOR DE COMPROBANTES    --}}
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                            <h6>COMPROBANTES Y COMPROBANTES DE CLIENTE</h6>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="position-relative mb-3">
                                <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                                       placeholder="Buscar comprobante o cliente"
                                       wire:model="searchFacturaCliente"
                                       wire:change="buscar_facturas_clientes"
                                       style="border: none; outline: none;" />
                                <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                            </div>
                            @if($searchFacturaCliente !== '')
                                <div class="factura-list">
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
                                                <div class="row factura-item align-items-center mb-2"  wire:click="seleccionarFactura('{{$factura->CFTD}}','{{ $factura->CFNUMSER }}','{{ $factura->CFNUMDOC }}')">
                                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                                        <p class="serie-correlativa ms-0">Serie y Correlativo:</p>
                                                        <b style="font-size: 16px;color: black">{{ $factura->CFNUMSER }} - {{ $factura->CFNUMDOC }}</b>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                                        <p class="serie-correlativa ms-0">N° de Guía:</p>
                                                        <b style="font-size: 16px;color: black">{{ $factura->CFTEXGUIA }}</b>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                                        <p class="peso ms-0">Importe: </p>
                                                        <b style="font-size: 16px;color: black">{{ $factura->CFIMPORTE }}</b>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                                        <p class="peso ms-0">Fecha de Emisión: </p>
                                                        <b style="font-size: 16px;color: black">{{ $factura->guia ? date('d-m-Y',strtotime($factura->guia->GREFECEMISION))  : '-' }}</b>
                                                    </div>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                                        <p class="nombre-cliente ms-0">Cliente:</p>
                                                        <b style="font-size: 15px;color: black">{{ $factura->CNOMCLI }}</b>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                                        <p class="peso ms-0">Peso: </p>
                                                        <b style="font-size: 16px;color: black">{{ $factura->total_kg }} kg</b>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                                        <p class="peso ms-0">Volumen: </p>
                                                        <b style="font-size: 16px;color: black">{{ $factura->total_volumen }} cm³</b>
                                                    </div>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                                        <p class="peso ms-0">Dirección: </p>
                                                        <b style="font-size: 16px;color: black">{{$factura->guia ? $factura->guia->LLEGADADIRECCION : '-' }}</b>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <p>No se encontró el comprobante.</p>
                                    @endif
                                </div>
                            @endif
                        </div>
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
                                    <h6>Fecha de entrega</h6>
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
                                    <p class="text-end mb-0">Monto de la tarifa del vehículo seleccionado: S/ <strong>{{ $tarifaMontoSeleccionado }}</strong></p>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="vehiculos-scroll-container-horizontal">
                                    @php $conteoGen = 1; @endphp
                                    @foreach($vehiculosSugeridos as $index => $vehiculo)
                                        <div class="position-relative">
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

            {{-- OTROS - MANO DE OBRA --}}
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Otros S/</h6>
                                </div>
                                <div class="col-lg-12">
                                    <input type="text" class="form-control" id="despacho_otros" name="despacho_otros" wire:model="despacho_otros" onkeyup="validar_numeros(this.id)" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                    <h6>Mano de obra S/</h6>
                                </div>
                                <div class="col-lg-12">
                                    <input type="text" class="form-control" id="despacho_mano_obra" name="despacho_mano_obra" wire:model="despacho_mano_obra" onkeyup="validar_numeros(this.id)" />
                                </div>
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
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">COMPROBANTES SELECCIONADOS</h4>
                                <div class="d-flex flex-column align-items-end ml-auto">
                                    <div class="d-flex justify-content-center text-center py-1">
                                        <p class="mb-0 me-2">Peso total: </p>
                                        <h4 class="mb-0 text-dark">{{ $pesoTotal }} kg</h4>
                                    </div>
                                    <div class="d-flex justify-content-center text-center py-1">
                                        <p class="mb-0 me-2">Volumen total: </p>
                                        <h4 class="mb-0 text-dark">{{ $volumenTotal }} cm³</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <!-- Tabla para Local -->
                                <div class="m-0">
                                    <h5>LOCAL</h5>
                                    <hr>
                                    @if(count($selectedFacturasLocal) > 0)
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Provincial</th>
                                                <th>Serie</th>
                                                <th>Guía</th>
                                                <th>Nombre Cliente</th>
                                                <th>Peso</th>
                                                <th>Volumen</th>
                                                <th>Acciones</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($selectedFacturasLocal as $factura)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox"
                                                               class="form-check-input"
                                                               wire:model.defer="selectedFacturasLocal.{{ $loop->index }}.isChecked"
                                                               wire:click="actualizarFactura('{{ $factura['CFTD'] }}', '{{ $factura['CFNUMSER'] }}', '{{ $factura['CFNUMDOC'] }}', $event.target.checked)" />
                                                    </td>
                                                    <td>{{ $factura['CFNUMSER'] }} - {{ $factura['CFNUMDOC'] }}</td>
                                                    <td>{{ $factura['guia'] }}</td>
                                                    <td>{{ $factura['CNOMCLI'] }}</td>
                                                    <td>{{ $factura['total_kg'] }} kg</td>
                                                    <td>{{ $factura['total_volumen'] }} cm³</td>
                                                    <td>
                                                        <a  wire:click="eliminarFacturaSeleccionada('{{ $factura['CFTD'] }}', '{{ $factura['CFNUMSER'] }}', '{{ $factura['CFNUMDOC'] }}')" class="btn btn-danger btn-sm text-white">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p>No hay comprobantes seleccionados para Local.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <!-- Tabla para Provincial -->
                                <div class="mt-5">
                                    <h5>PROVINCIAL</h5>
                                    <hr>
                                    @if(count($selectedFacturasProvincial) > 0)
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Serie</th>
                                                <th>Guía</th>
                                                <th>Nombre Cliente</th>
                                                <th>Peso</th>
                                                <th>Volumen</th>
                                                <th>Acciones</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($selectedFacturasProvincial as $cliente => $facturas)
                                                <tr>
                                                    <td colspan="3">
                                                        <h6 class="mb-0">{{ $cliente }}</h6>
                                                    </td>
                                                    <td colspan="4">
                                                        <a
                                                            href="#"
                                                            class="btn btn-danger btn-sm text-white"
                                                            data-bs-toggle="modal" data-bs-target="#modalComprobantes"
                                                            wire:click="abrirModalComprobantes('{{ $cliente }}')">
                                                            Ver Comprobantes
                                                        </a>
                                                    </td>
                                                </tr>
                                                @foreach($facturas as $factura)
                                                    <tr>
                                                        <td>{{ $factura['CFNUMSER'] }} - {{ $factura['CFNUMDOC'] }}</td>
                                                        <td>{{ $factura['guia'] }}</td>
                                                        <td>{{ $factura['CNOMCLI'] }}</td>
                                                        <td>{{ $factura['total_kg'] }} kg</td>
                                                        <td>{{ $factura['total_volumen'] }} cm³</td>
                                                        <td>
                                                            <a wire:click="eliminarFacturaProvincial('{{ $factura['CFTD'] }}', '{{ $factura['CFNUMSER'] }}', '{{ $factura['CFNUMDOC'] }}')"
                                                               class="btn btn-danger btn-sm text-white">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            </tbody>
                                        </table>
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

            {{--    MODAL DE COMPROBANTES PROVINCIALES  --}}
            <x-modal-general wire:ignore.self>
                <x-slot name="tama">modal-lg</x-slot>
                <x-slot name="id_modal">modalComprobantes</x-slot>
                <x-slot name="titleModal">Comprobantes Seleccionados</x-slot>
                <x-slot name="modalContent">
                    <div class="row">
                        <!-- Lista de comprobantes -->
                        <div class="col-lg-4">
                            <h6>Comprobantes del Cliente</h6>
                            <hr>
                            <ul class="list-group">
                                @foreach($comprobantesSeleccionados as $factura)
                                    <li class="list-group-item">
                                        {{ $factura['CFNUMSER'] }} - {{ $factura['CFNUMDOC'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Espacio adicional -->
                        <div class="col-lg-8">
                            <h6>Contenido adicional</h6>
                            <hr>
                            <p>Aquí puedes agregar cualquier información relevante para este cliente.</p>
                        </div>
                    </div>
                </x-slot>
            </x-modal-general>

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
