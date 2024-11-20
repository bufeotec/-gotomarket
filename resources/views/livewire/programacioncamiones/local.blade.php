<div>
    <div class="row">
        <div class="col-lg-5">
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
                                                   wire:click="seleccionarFactura({{ $factura->CFNUMDOC }})" class="form-check-input">
                                            <div class="checkmark"></div>
                                            <span class="serie-correlativa">{{ $factura->CFNUMDOC }}</span>
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
