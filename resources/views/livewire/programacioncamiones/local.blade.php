<div>
    <div class="row">
        <div class="col-lg-5">
            {{--    BUSCADOR DE COMPROBANTES    --}}
            <div class="col-lg-12 col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>COMPROBANTES</h4>
                    </div>
                    <div class="card-body">
                        <div class="position-relative mb-3">
                            <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                                   placeholder="Buscar comprobante" wire:model="searchFactura" wire:input="buscar_comprobantes"
                                   style="border: none; outline: none;" />
                            <i class="fas fa-search position-absolute"
                               style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                        </div>

                        <!-- Mostrar los resultados solo si hay texto en el campo de búsqueda -->
                        @if($searchFactura !== '')
                            <div class="factura-list">
                                @if(count($filteredFacturas) == 0)
                                    <p>No se encontró el comprobante.</p>
                                @else
                                    @foreach($filteredFacturas as $factura)
                                        <label class="custom-checkbox factura-item d-flex align-items-center mb-2" for="factura_{{ $factura->CFNUMDOC }}">
                                            <input type="checkbox" id="factura_{{ $factura->CFNUMDOC }}" value="{{ $factura->CFNUMDOC }}"
                                                   wire:click="seleccionarFactura('{{$factura->CFTD}}','{{ $factura->CFNUMSER }}','{{ $factura->CFNUMDOC }}')" class="form-check-input">
                                            <div class="checkmark"></div>
                                            <span class="serie-correlativa">{{ $factura->CFNUMSER }} - {{ $factura->CFNUMDOC }}</span>
                                            <span class="nombre-cliente mx-2">{{ $factura->CNOMCLI }}</span>
                                            <span class="peso">Peso: {{ $factura->total_kg }} kg</span>
                                            <span class="peso">Volumen: {{ $factura->total_volumen }} cm³</span>
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
        </div>

        <div class="col-lg-7">
            {{--    TRANSPORTISTA   --}}
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

            {{--    TABLA DE COMPROBANTES SELECCIONADOS --}}
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
                                        <td>{{ $factura['CFNUMSER'] }} - {{ $factura['CFNUMDOC'] }}</td>
                                        <td>{{ $factura['CNOMCLI'] }}</td>
                                        <td>{{ $factura['total_kg'] }} kg</td>
                                        <td>{{ $factura['total_volumen'] }} cm³</td>
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
        </div>
    </div>


    <style>
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
    </style>

</div>
