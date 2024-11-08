<div>

    <div class="row">
        <!-- Transportistas Card -->
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Transportistas</h4>
                </div>
                <div class="card-body">
                    <!-- Campo de búsqueda sin indicador de carga -->
                    <div class="position-relative mb-3">
                        <input type="text" wire:model="search" wire:input="listarTransportistasProgramarCamion"
                               class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                               placeholder="Buscar transportista" style="border: none; outline: none;">
                        <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                    </div>

                    @if(count($listar_transportistas) == 0)
                        <p>No se encontró el transportista.</p>
                    @else
                        @foreach($listar_transportistas as $tr)
                            <div class="transportista-option position-relative">
                                <input type="radio" id="{{ $tr->id_transportistas }}" name="id_transportistas"
                                       value="{{ $tr->id_transportistas }}" class="d-none"
                                       wire:click="selectTransportista({{ $tr->id_transportistas }})"
                                       @if($selected_transportista == $tr->id_transportistas) checked @endif />
                                <label class="transportista-label" for="{{ $tr->id_transportistas }}">
                                    <span>{{ $tr->transportista_nom_comercial }}</span>
                                </label>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div wire:loading wire:target="selectTransportista" class="overlay-transportista overlay">
                <div class="spinner-container-transportista">
                    <div class="spinner-transportista-circulo"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row">
                {{-- Tipo Servicio --}}
                @if($selected_transportista)
                    <div class="col-lg-4 col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Tipo de Servicio</h4>
                            </div>
                            <div class="card-body">
                                <div class="radio-button-container">
                                    @foreach($listar_tipo_servicio as $tipo)
                                        <div class="radio-button">
                                            <input type="radio" id="tipo_{{ $tipo->id_tipo_servicios }}" wire:change="resetearCampoTipoServicio" name="id_tipo_servicios" class="radio-button__input"
                                                   wire:model.lazy="id_tipo_servicios" value="{{ $tipo->id_tipo_servicios }}"
                                                   @if($id_tipo_servicios == $tipo->id_tipo_servicios) checked @endif />
                                            <label class="radio-button__label" for="tipo_{{ $tipo->id_tipo_servicios }}">
                                                <span class="radio-button__custom"></span>
                                                {{ $tipo->tipo_servicio_concepto }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="overlay" wire:loading wire:target="id_tipo_servicios">
                                    <div class="spinner-container">
                                        <div class="spinner"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if($id_tipo_servicios == 1)
                    {{-- Tipo Vehículo --}}
                    <div class="col-lg-4 col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Tipo de Vehículo</h4>
                            </div>
                            <div class="card-body">
                                <select class="form-select" wire:model="id_tipo_vehiculo" wire:change="calcularTarifa" wire:input="listar_vehiculos" name="id_tipo_vehiculo" id="id_tipo_vehiculo">
                                    <option value="" disabled>Seleccionar...</option>
                                    @foreach($listar_tipo_vehiculo as $ltv)
                                        <option value="{{ $ltv->id_tipo_vehiculo }}">{{ $ltv->tipo_vehiculo_concepto }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Vehículo</h4>
                            </div>
                            <div class="card-body">
                                <select class="form-select" wire:model="id_vehiculo" name="id_vehiculo" id="id_vehiculo">
                                    <option value="" disabled>Seleccionar...</option>
                                    @foreach($vehiculos as $lv)
                                        <option value="{{ $lv->id_vehiculo }}">{{ $lv->vehiculo_placa }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="row">
                            <div class="col-lg-4 col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Mano de obra S/</h4>
                                    </div>
                                    <div class="card-body">
                                        <input class="form-control" type="text" id="despacho_mano_obra" onkeyup="validar_numeros(this.id)" name="despacho_mano_obra" wire:model="despacho_mano_obra" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Otros S/</h4>
                                    </div>
                                    <div class="card-body">
                                        <input class="form-control" type="text" id="despacho_otro" onkeyup="validar_numeros(this.id)" name="despacho_otro" wire:model="despacho_otro" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- FACTURAS--}}
                    <div class="col-lg-12 col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>FACTURAS</h4>
                                @if($tarifa_estado_aprobacion === 0)
                                    <h6 class="text-danger ms-2">El tarifario no ha sido aprobado</h6>
                                @endif
                            </div>
                            <div class="card-body">
                                {{-- Buscador de facturas--}}
                                <div class="position-relative mb-3">
                                    <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder" placeholder="Buscar factura" wire:model="searchFactura" wire:input="buscarFacturas" style="border: none; outline: none;" />
                                    <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                                </div>
                                <div class="factura-list">
                                    @if(count($filteredFacturas) == 0)
                                        <p>No se encontró la factura.</p>
                                    @else
                                        @foreach($filteredFacturas as $factura)
                                            <label class="custom-checkbox factura-item d-flex align-items-center mb-2" for="factura_{{ $factura['id'] }}">
                                                <input type="checkbox" id="factura_{{ $factura['id'] }}" value="{{ $factura['id'] }}"
                                                       wire:model="selectedFacturas" wire:input="calculateTotalPeso" class="form-check-input">
                                                <div class="checkmark"></div>
                                                <span class="serie-correlativa">{{ $factura['serie'] }}</span>
                                                <span class="nombre-cliente mx-2">{{ $factura['nombre'] }}</span>
                                                <span class="peso">Peso: {{ $factura['peso'] }} kg</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="mt-3 d-flex justify-content-between align-items-center text-center">
                                    <div class="text-center d-flex justify-content-center align-items-center gap-2">
                                        <p class="mb-0">Total Peso Seleccionado:</p>
                                        <h4 class="mb-0 text-dark">
                                            <span wire:loading.remove>{{ $totalPeso }} kg</span>
                                            <div wire:loading wire:target="calculateTotalPeso" class="loading-circle-container">
                                                <div class="loading-circle"></div>
                                            </div>
                                        </h4>
                                    </div>

                                    <div class="text-center d-flex justify-content-center align-items-center gap-2">
                                        @if($tarifa)
                                            <p class="mb-0">Tarifa: </p>
                                            <h4 class="mb-0 text-dark">S/ {{ $tarifa }}</h4>
                                        @else
                                            <p class="mb-0">No se encontró una tarifa.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($tarifa)
                        <button wire:click="saveDespacho" class="btn text-white" style="background: #e51821">Guardar Despacho</button>
                    @endif
                @endif
                @if($id_tipo_servicios == 2)
                    <div class="col-lg-4 col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Mano de obra S/</h4>
                            </div>
                            <div class="card-body">
                                <input class="form-control" type="text" id="despacho_mano_obra" name="despacho_mano_obra" wire:model="despacho_mano_obra" />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Otros S/</h4>
                            </div>
                            <div class="card-body">
                                <input class="form-control" type="text" id="despacho_otro" name="despacho_otro" wire:model="despacho_otro" />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Departamento (*)</h4>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-select" name="id_departamento" id="id_departamento" wire:model="id_departamento" wire:change="actualizarSeleccion" >
                                            <option value="">Seleccionar...</option>
                                            @foreach($listar_departamento as $de)
                                                <option value="{{ $de->id_departamento }}">{{ $de->departamento_nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Provincia (*)</h4>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-select" name="id_provincia" id="id_provincia" wire:model="id_provincia" wire:change="actualizarSeleccionDistritos" {{ empty($provincias) ? 'disabled' : '' }}>
                                            <option value="">Seleccionar...</option>
                                            @foreach($provincias as $pr)
                                                <option value="{{ $pr->id_provincia }}" {{ $pr->id_provincia == $id_provincia ? 'selected' : '' }}>{{ $pr->provincia_nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Distrito</h4>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-select" name="id_distrito" id="id_distrito" wire:model="id_distrito" wire:change="calcularTarifa" {{ empty($distritos) ? 'disabled' : '' }}>
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
                                                               wire:model="selectedClientes" wire:input="calculateTotalPesoCliente" class="form-check-input">
                                                        <div class="checkmark"></div>
                                                        <span class="serie-correlativa">{{ $factura['serie'] }}</span>
                                                        <span class="nombre-cliente mx-2">{{ $factura['nombre'] }}</span>
                                                        <span class="peso">Peso: {{ $factura['peso'] }} kg</span>
                                                    </label>
                                                @endforeach
                                                <div class="mt-3 d-flex justify-content-between align-items-center text-center">
                                                    <div class="text-center d-flex justify-content-center align-items-center gap-2">
                                                        <p class="mb-0">Total Peso Seleccionado:</p>
                                                        <h4 class="mb-0 text-dark">
                                                            <span wire:loading.remove>{{ $totalPeso }} kg</span>
                                                            <div wire:loading wire:target="calculateTotalPesoCliente" class="loading-circle-container">
                                                                <div class="loading-circle"></div>
                                                            </div>
                                                        </h4>
                                                    </div>

                                                    <div class="text-center d-flex justify-content-center align-items-center gap-2">
                                                        @if($tarifa)
                                                            <p class="mb-0">Tarifa: </p>
                                                            <h4 class="mb-0 text-dark">S/ {{  $tarifa }}</h4>
                                                        @else
                                                            <p class="mb-0">No se encontró una tarifa.</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <p>No se encontró las facturas para este cliente.</p>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($tarifa)
                        <button wire:click="saveDespacho" class="btn text-white" style="background: #e51821">Guardar Despacho</button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Circulo loading de trasnportista*/
        .overlay-transportista {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            display: none;
        }
        .spinner-container-transportista {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        .spinner-transportista-circulo {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #c3121a;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }


        /* Estilo para el círculo de carga de total peso factura */
        .loading-circle-container {
            position: absolute;
            top: 90%;
            left: 30%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }

        .loading-circle {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #c3121a;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: rotate 1s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Estilo para centrar el círculo rotatorio tipo servicio */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: transparent;
            z-index: 9998;
            display: none;
        }
        .spinner-container {
            position: fixed;
            top: 60%;
            left: 70%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #c3121a;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /*ESTILOS PARA TRANSPORTISTAS*/
        .transportista-option {
            margin-bottom: 10px;
        }
        .transportista-label {
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
        .transportista-label i {
            font-size: 20px;
            color: #fff;
        }
        /* Estilo cuando está seleccionado */
        input[type="radio"]:checked + .transportista-label {
            background-color: #e30613;
            color: #fff;
        }
        input[type="radio"]:checked + .transportista-label i {
            color: #fff;
        }
        .custom-placeholder::placeholder {
            color: white !important;
            opacity: 0.9!important;
        }
        .radio-button-container {
            display: flex;
            align-items: center;
            gap: 24px;
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

        /*FACTURA*/
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
        .peso {
            font-size: 15px;
            color: #333;
            margin: 0px 10px;
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
        .card {
            margin-bottom: 1rem;
            border: none
        }

    </style>

</div>


