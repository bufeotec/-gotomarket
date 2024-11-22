<div>
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
                            <div class="col-lg-12">
                                {{-- Buscador --}}
                                <div class="position-relative mb-3">
                                    <input
                                        type="text"
                                        class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                                        placeholder="{{ $selectedCliente ? 'Buscar comprobante' : 'Buscar cliente' }}"
                                        wire:model="{{ $selectedCliente ? 'searchComprobante' : 'searchCliente' }}"
                                        wire:input="{{ $selectedCliente ? 'buscar_comprobante' : 'buscar_cliente' }}"
                                        value="{{ $selectedCliente ? $searchComprobante : $searchCliente }}"
                                        style="border: none; outline: none;"
                                    />
                                    <i class="fas fa-search position-absolute"
                                       style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                                </div>

                                @if(!$selectedCliente)
                                    {{-- Mostrar clientes cuando no hay cliente seleccionado --}}
                                    @if(!empty($searchCliente))
                                        <div class="cliente-lista">
                                            @if(count($filteredClientes) == 0)
                                                <p>No se encontró el cliente.</p>
                                            @else
                                                @foreach($filteredClientes as $cl)
                                                    <div class="row factura-item align-items-center mb-2" wire:click="seleccionar_cliente({{ $cl->CCODCLI }})">
                                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                                            <p class="nombre-cliente ms-0">Razón social:</p>
                                                            <b style="font-size: 16px;color: black">{{ $cl->CNOMCLI }}</b>
                                                        </div>
                                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                                            <p class="peso ms-0">RUC O DNI:</p>
                                                            <b style="font-size: 16px;color: black">{{ $cl->CCODCLI }}</b>
                                                        </div>
                                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                                            <p class="peso ms-0">Dirección Fiscal:</p>
                                                            <b style="font-size: 16px;color: black">{{ $cl->CDIRCLI }}</b>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    {{-- Mostrar comprobantes cuando un cliente está seleccionado --}}
                                    @if(!empty($searchComprobante))
                                        <div class="comprobante-lista">
                                            @if(count($filteredComprobantes) == 0)
                                                <p>No se encontraron comprobantes.</p>
                                            @else
                                                @foreach($filteredComprobantes as $comprobante)
                                                    <div class="row factura-item align-items-center mb-2" wire:click="seleccionar_factura_cliente('{{ $comprobante->CFTD }}', '{{ $comprobante->CFNUMSER }}', '{{ $comprobante->CFNUMDOC }}')">
                                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                                            <p class="serie-correlativa">Serie y Correlativo: <b style="font-size: 16px">{{ $comprobante->CFNUMSER }} - {{ $comprobante->CFNUMDOC }}</b></p>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                                            <p class="peso">Peso: <b style="font-size: 16px">{{ $comprobante->total_kg }} kg</b></p>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                                            <p class="peso">Volumen: <b style="font-size: 16px">{{ $comprobante->total_volumen }} cm³</b></p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
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
                                    <input type="date" class="form-control" id="despacho_fecha" name="despacho_fecha" wire:model="despacho_fecha" />
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
                                                        <a href="#" class="btn-ver" data-bs-toggle="modal" data-bs-target="#modalVehiculo" wire:click="modal_por_vehiculo({{ $vehiculo->id_vehiculo }})">
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
                                    <h6 class="mb-0">COMPROBANTES SELECCIONADAS</h6>
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

                                <div class="col-lg-12">
                                    @if(count($selectedFacturas) > 0)
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Serie</th>
{{--                                                <th>Nombre Cliente</th>--}}
                                                <th>Peso</th>
                                                <th>Volumen</th>
                                                <th>Acciones</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($selectedFacturas as $factura)
                                                <tr>
                                                    <td>{{ $factura['CFNUMSER'] }} - {{ $factura['CFNUMDOC'] }}</td>
{{--                                                    <td>{{ $factura['CNOMCLI'] }}</td>--}}
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
    </style>
</div>
