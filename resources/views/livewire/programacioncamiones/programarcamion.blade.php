<div>

    <div class="row">
        <div class="col-lg-5">
            {{-- TIPO SERVICIOS --}}
            <div class="col-lg-12 col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Tipo de Servicio</h4>
                    </div>
                    <div class="card-body">
                        <div class="radio-button-container">
                            @foreach($listar_tipo_servicio as $tipo)
                                <div class="radio-button">
                                    <input type="radio" id="tipo_{{ $tipo->id_tipo_servicios }}" wire:change="resetearCampoTipoServicio" name="id_tipo_servicios" class="radio-button__input" wire:model.lazy="id_tipo_servicios" value="{{ $tipo->id_tipo_servicios }}"
                                           @if($id_tipo_servicios == $tipo->id_tipo_servicios) checked @endif />
                                    <label class="radio-button__label" for="tipo_{{ $tipo->id_tipo_servicios }}">
                                        <span class="radio-button__custom"></span>
                                        {{ $tipo->tipo_servicio_concepto }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{--   COMPROBANTES --}}
            @if($id_tipo_servicios == 1)
                <div class="col-lg-12 col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>COMPROBANTES</h4>
                        </div>
                        <div class="card-body">
                            <div class="position-relative mb-3">
                                <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                                       placeholder="Buscar comprobante" wire:model="searchFactura" wire:input="buscarFacturas"
                                       style="border: none; outline: none;" />
                                <i class="fas fa-search position-absolute"
                                   style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                            </div>

                            @if($searchFactura != '')
                                <div class="factura-list">
                                    @if(count($filteredFacturas) == 0)
                                        <p>No se encontró el comprobante.</p>
                                    @else
                                        @foreach($filteredFacturas as $factura)
                                            <label class="custom-checkbox factura-item d-flex align-items-center mb-2" for="factura_{{ $factura['id'] }}">
                                                <input type="checkbox" id="factura_{{ $factura['id'] }}" value="{{ $factura['id'] }}"
                                                       wire:click="seleccionarFactura({{ $factura['id'] }})" class="form-check-input">
                                                <div class="checkmark"></div>
                                                <span class="serie-correlativa">{{ $factura['serie'] }}</span>
                                                <span class="nombre-cliente mx-2">{{ $factura['nombre'] }}</span>
                                                <span class="peso">Peso: {{ $factura['peso'] }} kg</span>
                                                <span class="peso">Volumen: {{ $factura['volumen'] }} cm³</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Overlay y Spinner solo al seleccionar una factura -->
                    <div wire:loading wire:target="seleccionarFactura" class="overlay__factura">
                        <div class="spinner__container__factura">
                            <div class="spinner__factura"></div>
                        </div>
                    </div>
                </div>
            @endif

            {{--   BUSCAR CLIENTE COMPROBANTES --}}
            @if($id_tipo_servicios == 2)
                <div class="col-lg-12 col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Clientes</h4>
                        </div>
                        <div class="card-body">
                            {{-- Buscador de clientes --}}
                            <div class="position-relative mb-3">
                                <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder" placeholder="Buscar cliente" wire:model="searchCliente" wire:input="buscarClientes" value="{{ $searchCliente }}" style="border: none; outline: none;" />
                                <i class="fas fa-search position-absolute"
                                   style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                            </div>
                            @if(!empty($searchCliente))
                                <div class="cliente-lista">
                                    @if($selectedCliente && count($filteredClientes) == 0)

                                    @elseif(count($filteredClientes) == 0)
                                        <p>No se encontró el cliente.</p>
                                    @else
                                        @foreach($filteredClientes as $cl)
                                            <div class="cliente-option">
                                                <input type="radio" id="{{ $cl['id'] }}" name="selected_cliente" value="{{ $cl['id'] }}" wire:model="selectedCliente" wire:change="updateSearchCliente({{ $cl['id'] }})" class="d-none"
                                                       @if($selectedCliente == $cl['id']) checked @endif />
                                                <label class="cliente-label" for="{{ $cl['id'] }}">
                                                    <span class="nombre-cliente">{{ $cl['nombre'] }}</span>
                                                    <span class="ruc-cliente mx-2">{{ $cl['ruc'] }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="factura-list">
                                    @if($selectedCliente)
                                        @if($facturasPorCliente !== null && count($facturasPorCliente) > 0)
                                            @foreach($facturasPorCliente as $factura)
                                                <label class="custom-checkbox factura-item d-flex align-items-center mb-2" for="cliente_factura{{ $factura['id'] }}">
                                                    <input type="checkbox" id="cliente_factura{{ $factura['id'] }}" name="facturaSeleccionada" value="{{ $factura['id'] }}"
                                                           wire:click="seleccionarFacturaCliente({{ $factura['id'] }})" class="form-check-input">
                                                    <div class="checkmark"></div>
                                                    <span class="serie-correlativa">{{ $factura['serie'] }}</span>
                                                    <span class="nombre-cliente mx-2">{{ $factura['nombre'] }}</span>
                                                    <span class="peso">Peso: {{ $factura['peso'] }} kg</span>
                                                    <span class="volumen">Volumen: {{ $factura['volumen'] }} cm³</span>
                                                </label>
                                            @endforeach
                                        @else
                                            <p>No se encontró los comprobantes para este cliente.</p>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Overlay y Spinner solo al seleccionar una factura -->
                    <div wire:loading wire:target="seleccionarFacturaCliente" class="overlay__factura">
                        <div class="spinner__container__factura">
                            <div class="spinner__factura"></div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- BUSCADOR Y SELECTOR DE TIPO DE SERVICIO --}}
            @if($id_tipo_servicios == 3)
                <div class="col-lg-12 col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>COMPROBANTES Y COMPROBANTES DE CLIENTE</h4>
                        </div>
                        <div class="card-body">
                            {{-- Selector de tipo de servicio --}}
                            <div class="mb-3">
                                <select class="form-select" wire:model="tipoServicioSeleccionado">
                                    <option value="" disabled>Seleccionar...</option>
                                    @foreach($tipo_servicio_local_provincial as $tipo)
                                        <option value="{{ $tipo->id_tipo_servicios }}">{{ $tipo->tipo_servicio_concepto }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Buscador --}}
                            <div class="position-relative mb-3">
                                <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                                       placeholder="Buscar comprobante o cliente" wire:model="searchFacturaCliente" wire:input="buscarFacturasYClientes"
                                       style="border: none; outline: none;" />
                                <i class="fas fa-search position-absolute"
                                   style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                            </div>

                            {{-- Resultados del buscador --}}
                            @if($searchFacturaCliente != '')
                                <div class="factura-list">
                                    @if(count($filteredFacturasYClientes) == 0)
                                        <p>No se encontraron resultados.</p>
                                    @else
                                        @foreach($filteredFacturasYClientes as $factura)
                                            <label class="custom-checkbox factura-item d-flex align-items-center mb-2" for="factura_cliente_{{ $factura['id'] }}">
                                                <input type="checkbox" id="factura_cliente_{{ $factura['id'] }}" value="{{ $factura['id'] }}"
                                                       wire:click="seleccionarFacturaClienteJunto({{ $factura['id'] }})" class="form-check-input">
                                                <div class="checkmark"></div>
                                                <span class="serie-correlativa">{{ $factura['serie'] }}</span>
                                                <span class="nombre-cliente mx-2">{{ $factura['nombre'] }}</span>
                                                <span class="peso">Peso: {{ $factura['peso'] }} kg</span>
                                                <span class="peso">Volumen: {{ $factura['volumen'] }} cm³</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div wire:loading wire:target="seleccionarFacturaClienteJunto" class="overlay__factura">
                        <div class="spinner__container__factura">
                            <div class="spinner__factura"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-7">
            {{--   TRANSPORTISTAS--}}
            @if($id_tipo_servicios == 1 || $id_tipo_servicios == 2 || $id_tipo_servicios == 3)
                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4>Lista de transportistas</h4>
                        </div>
                        <div class="card-body">
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
            @endif

            @if($id_tipo_servicios == 1 || $id_tipo_servicios == 2 || $id_tipo_servicios == 3)
                {{-- VEHICULOS --}}
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Vehículos Sugeridos</h4>
                        </div>
                        <div class="card-body">
                            <div class="vehiculos-scroll-container-horizontal">
                                @foreach($vehiculosSugeridos as $vehiculo)
                                    <label class="circulo-vehiculo-container m-2 {{ $vehiculo->tarifa_estado_aprobacion == 0 ? 'no-aprobado' : '' }}">
                                        @if($vehiculo->tarifa_estado_aprobacion == 1)
                                            <input type="radio" name="vehiculo" class="vehiculo-radio d-none" value="{{ $vehiculo->id_vehiculo }}" wire:model="selectedVehiculo" wire:click="seleccionarVehiculo({{ $vehiculo->id_vehiculo }})" />
                                        @endif

                                        <!-- Progreso Circular usando SVG -->
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
                                            <div class="boton-container">
                                                <a href="#" class="btn-ver" data-bs-toggle="modal" data-bs-target="#modalVehiculo_{{ $vehiculo->id_vehiculo }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="estado-circulo">
                                            <i class="fa-solid fa-circle-check check-icon"></i>
                                            <i class="fas fa-exclamation-circle warning-icon"></i>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('selectedVehiculo')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- FECHA ENTREGA - OTROS - MANO DE OBRA --}}
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Fecha de entrega</h4>
                            </div>
                            <div class="card-body">
                                <input type="date" class="form-control" id="despacho_fecha" name="despacho_fecha" wire:model="despacho_fecha" />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Otros S/</h4>
                            </div>
                            <div class="card-body">
                                <input type="text" class="form-control" id="despacho_otros" name="despacho_otros" wire:model="despacho_otros" onkeyup="validar_numeros(this.id)" />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Mano de obra S/</h4>
                            </div>
                            <div class="card-body">
                                <input type="text" class="form-control" id="despacho_mano_obra" name="despacho_mano_obra" wire:model="despacho_mano_obra" onkeyup="validar_numeros(this.id)" />
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{--  DEPARTAMENTO - PROVINCIA - DISTRITO --}}
            @if($id_tipo_servicios == 2)
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Departamento (*)</h4>
                                </div>
                                <div class="card-body">
                                    <select class="form-select" name="id_departamento" id="id_departamento" wire:model="id_departamento" wire:change="listar_provincias">
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
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Provincia (*)</h4>
                                </div>
                                <div class="card-body">
                                    <select class="form-select" name="id_provincia" id="id_provincia" wire:model="id_provincia" wire:change="listar_distritos" {{ empty($provincias) ? 'disabled' : '' }}>
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
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Distrito</h4>
                                </div>
                                <div class="card-body">
                                    <select class="form-select" name="id_distrito" id="id_distrito" wire:model="id_distrito" {{ empty($distritos) ? 'disabled' : '' }}>
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
            @endif

            <!-- MODAL DETALLE DE VEHÍCULO -->
            @foreach($vehiculosSugeridos as $vehiculo)
                <div class="modal fade" id="modalVehiculo_{{ $vehiculo->id_vehiculo }}" tabindex="-1"
                     aria-labelledby="modalVehiculoLabel_{{ $vehiculo->id_vehiculo }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalVehiculoLabel_{{ $vehiculo->id_vehiculo }}">
                                    Detalles del Vehículo
                                </h5>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <h6>Información del transportista</h6>
                                                <hr>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                                <strong style="color: #8c1017">Nombre comercial:</strong>
                                                <p>{{ $vehiculo->transportista_nom_comercial }}</p>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                                <strong style="color: #8c1017">RUC:</strong>
                                                <p>{{ $vehiculo->transportista_ruc }}</p>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                                <strong style="color: #8c1017">Monto de la tarifa:</strong>
                                                <p>S/ {{ $vehiculo->tarifa_monto }}</p>
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
                                                <p>{{ $vehiculo->vehiculo_placa }}</p>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                                <strong style="color: #8c1017">Capacidad en peso:</strong>
                                                <p>{{ (substr(number_format($vehiculo->vehiculo_capacidad_peso, 2, '.', ','), -3) == '.00') ? number_format($vehiculo->vehiculo_capacidad_peso, 0, '.', ',') : number_format($vehiculo->vehiculo_capacidad_peso, 2, '.', ',') }} kg</p>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                                <strong style="color: #8c1017">Ancho:</strong>
                                                <p>{{ (substr(number_format($vehiculo->vehiculo_ancho, 2, '.', ','), -3) == '.00') ? number_format($vehiculo->vehiculo_ancho, 0, '.', ',') : number_format($vehiculo->vehiculo_ancho, 2, '.', ',') }} cm</p>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                                <strong style="color: #8c1017">Largo:</strong>
                                                <p>{{ (substr(number_format($vehiculo->vehiculo_largo, 2, '.', ','), -3) == '.00') ? number_format($vehiculo->vehiculo_largo, 0, '.', ',') : number_format($vehiculo->vehiculo_largo, 2, '.', ',') }} cm</p>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                                <strong style="color: #8c1017">Alto:</strong>
                                                <p>{{ (substr(number_format($vehiculo->vehiculo_alto, 2, '.', ','), -3) == '.00') ? number_format($vehiculo->vehiculo_alto, 0, '.', ',') : number_format($vehiculo->vehiculo_alto, 2, '.', ',') }} cm</p>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                                <strong style="color: #8c1017">Volumen:</strong>
                                                <p>{{ (substr(number_format($vehiculo->vehiculo_capacidad_volumen, 2, '.', ','), -3) == '.00') ? number_format($vehiculo->vehiculo_capacidad_volumen, 0, '.', ',') : number_format($vehiculo->vehiculo_capacidad_volumen, 2, '.', ',') }} cm³</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{--  TABLA DE LAS COMPROBANTES SELECCIONADAS--}}
            @if($id_tipo_servicios == 1 || $id_tipo_servicios ==2)
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">COMPROBANTES SELECCIONADAS</h4>
                            <!-- Peso total y Volumen total alineados a la derecha -->

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

                        <div class="card-body">
                            @if(count($selectedFacturas) > 0)
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>Serie</th>
                                        <th>Nombre Cliente</th>
                                        <th>Peso</th>
                                        <th>Volumen</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($selectedFacturas as $factura)
                                        <tr>
                                            <td>{{ $factura['serie'] }}</td>
                                            <td>{{ $factura['nombre'] }}</td>
                                            <td>{{ $factura['peso'] }} kg</td>
                                            <td>{{ $factura['volumen'] }} cm³</td>
                                            <td>
                                                <a href="#" wire:click.prevent="eliminarFacturaSeleccionada({{ $factura['id'] }})" class="btn btn-danger btn-sm text-white">
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
                    <!-- Loading solo al eliminar una factura -->
                    <div wire:loading wire:target="eliminarFacturaSeleccionada" class="overlay__eliminar">
                        <div class="spinner__container__eliminar">
                            <div class="spinner__eliminar"></div>
                        </div>
                    </div>

                    @if($tarifaMontoSeleccionado > 0)
                        <div class="col-lg-12 d-none">
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-center">Monto de la tarifa del vehículo seleccionado: S/ <strong>{{ $tarifaMontoSeleccionado }}</strong></p>
                                </div>
                            </div>
                        </div>
                    @endif

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
            @endif

            {{--  TABLA PARA MIXTO  --}}
            @if($id_tipo_servicios == 3)
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
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

                        <div class="card-body">
                            <!-- Tabla para Local -->
                            <div class="m-0">
                                <h5>LOCAL</h5>
                                <hr>
                                @if(count($selectedFacturasLocal) > 0)
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>Serie</th>
                                            <th>Nombre Cliente</th>
                                            <th>Peso</th>
                                            <th>Volumen</th>
                                            <th>Acciones</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($selectedFacturasLocal as $factura)
                                            <tr>
                                                <td>{{ $factura['serie'] }}</td>
                                                <td>{{ $factura['nombre'] }}</td>
                                                <td>{{ $factura['peso'] }} kg</td>
                                                <td>{{ $factura['volumen'] }} cm³</td>
                                                <td>
                                                    <a href="#"
                                                       wire:click.prevent="eliminarSeleccion({{ $factura['id'] }}, 'local')"
                                                       class="btn btn-danger btn-sm text-white">
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
                            <!-- Tabla para Provincial -->
                            <div class="mt-5">
                                <h5>PROVINCIAL</h5>
                                <hr>
                                @if(count($selectedFacturasProvincial) > 0)
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>Serie</th>
                                            <th>Nombre Cliente</th>
                                            <th>Peso</th>
                                            <th>Volumen</th>
                                            <th>Acciones</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($selectedFacturasProvincial as $cliente => $facturas)
                                            <tr>
                                                <td colspan="2">
                                                    <h6 class="mb-0">{{ $cliente }}</h6>
                                                </td>
                                                <td colspan="5">
                                                    <label for="transportista_{{ $cliente }}" class="form-label mb-1">Transportista</label>
                                                    <select class="form-select"
                                                            id="transportista_{{ $cliente }}"
                                                            wire:model="transportistasPorCliente.{{ $cliente }}">
                                                        @foreach($listar_transportistas as $lt)
                                                            <option value="{{ $lt->id_transportistas }}">{{ $lt->transportista_nom_comercial }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                            @foreach($facturas as $factura)
                                                <tr>
                                                    <td>{{ $factura['serie'] }}</td>
                                                    <td>{{ $factura['nombre'] }}</td>
                                                    <td>{{ $factura['peso'] }} kg</td>
                                                    <td>{{ $factura['volumen'] }} cm³</td>
                                                    <td>
                                                        <a href="#"
                                                           wire:click.prevent="eliminarSeleccion({{ $factura['id'] }}, 'provincial')"
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
                    <!-- Loading indicador -->
                    <div wire:loading wire:target="eliminarSeleccion, eliminarFacturaSeleccionada" class="overlay__eliminar">
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
            @endif

            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible show fade mt-2">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>



    <style>
        /* VEHICULOS */
        .vehiculos-scroll-container-horizontal {
            display: flex;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        .circulo-vehiculo-container {
            position: relative;
            width: 140px;
            height: 140px;
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

        /*LOADING DE FACTURAS*/
        .overlay__factura {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: transparent;
            z-index: 9998;
            display: none;
        }
        .spinner__container__factura{
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        .spinner__factura {
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

        /* LOADING DE ELIMINAR COMPROBANTES */
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

        /*ESTILOS PARA CLIENTES*/
        .cliente-option {
            margin-bottom: 10px;
        }
        .cliente-label {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #f0f0f0;
            border-radius: 8px;
            cursor: pointer;
            color: #333;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
        }
        .cliente-label .nombre-cliente {
            font-size: 16px;
        }
        .cliente-label .ruc-cliente {
            font-size: 14px;
            color: #777;
        }
        /* Estilo cuando está seleccionado */
        input[type="radio"]:checked + .cliente-label {
            background-color: #e30613;
            color: #fff;
        }
        input[type="radio"]:checked + .cliente-label .nombre-cliente,
        input[type="radio"]:checked + .cliente-label .ruc-cliente {
            color: #fff;
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

        /* Estilo de tipo servicios cuando está seleccionado */
        .custom-placeholder::placeholder {
            color: white !important;
            opacity: 0.9!important;
        }
        .radio-button-container {
            display: flex;
            align-items: center;
            gap: 24px;
            justify-content: space-evenly;
        }
        .radio-button {
            display: inline-block;
            position: relative;
            cursor: pointer;
        }
        .radio-button__input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .radio-button__label {
            display: inline-block;
            padding-left: 30px;
            margin-bottom: 10px;
            position: relative;
            font-size: 15px;
            color: #555;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }
        .radio-button__custom {
            position: absolute;
            top: 0;
            left: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #555;
            transition: all 0.3s ease;
        }
        .radio-button__input:checked + .radio-button__label .radio-button__custom {
            background-color: #e51821;
            border-color: transparent;
            transform: scale(0.8);
            box-shadow: 0 0 20px #a11319;
        }
        .radio-button__input:checked + .radio-button__label {
            color: #e51821;
        }
        .radio-button__label:hover .radio-button__custom {
            transform: scale(1.2);
            border-color: #e51821;
            box-shadow: 0 0 20px #a11319;
        }
    </style>

</div>


