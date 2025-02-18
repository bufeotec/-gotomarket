<div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <div class="w-50">
                <label class="form-label">Ingrese número de comprobante</label>
                <input type="text" class="form-control w-100" id="search_compro" name="search_compro" wire:model="search_compro" placeholder="Buscar">
            </div>
            <button class="btn btn-primary ms-4 mt-4" wire:click="buscar">BUSCAR</button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible show fade mt-2">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($codigoEncontrado)
        <x-card-general-view>
            <x-slot name="content">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 m-3">
                        <!-- ETAPAS -->
                        <div class="row justify-content-center text-center mt-3">
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/comprobante_check.png') }}" alt="Pre Programación" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 1 ? 'text-dark' : 'text-muted' }}">PRE PROGRAMACIÓN</p>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/despacho_aprobado.png') }}" alt="En Despacho" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 2 ? 'text-dark' : 'text-muted' }}">EN DESPACHO</p>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/comprobante_entregado.png') }}" alt="Despacho Entregado" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 3 ? 'text-dark' : 'text-muted' }}">DESPACHO ENTREGADO</p>
                            </div>
                        </div>

                        <!-- Línea de progreso con círculos -->
                        <div class="d-flex justify-content-center position-relative">
                            <div class="progress-line mt-4">
                                <div class="progress-bar" style="width: {{ $etapaActual * 33 }}%;"></div>

                                <!-- Círculo 1 -->
                                <div class="tracking-circle {{ $etapaActual >= 1 ? 'circle-green' : 'circle-gray' }}" style="left: 0%;"></div>
                                <!-- Círculo 2 -->
                                <div class="tracking-circle {{ $etapaActual >= 2 ? 'circle-green' : 'circle-gray' }}" style="left: 50%;"></div>
                                <!-- Círculo 3 -->
                                <div class="tracking-circle {{ $etapaActual >= 3 ? 'circle-green' : 'circle-gray' }}" style="left: 100%;"></div>
                            </div>
                        </div>

                        <!-- Botones de navegación -->
                        <div class="d-flex justify-content-center w-100">
                            <div class="d-flex justify-content-between w-75 mt-4">
                                @if($botonAnteriorVisible)
                                    <button class="btn btn-secondary" wire:click="cambiarEtapa({{ $etapaMostrada - 1 }})">← Anterior</button>
                                @else
                                    <div></div> <!-- Espacio vacío para mantener el alignment -->
                                @endif

                                @if($botonSiguienteVisible)
                                    <button class="btn btn-primary" wire:click="cambiarEtapa({{ $etapaMostrada + 1 }})">
                                        Siguiente →
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Mensajes y Estados -->
                        <div class="d-flex justify-content-center">
                            <div class=" m-3">
                                @if ($etapaMostrada == 1)
                                    <p>
                                        PRE PROGRAMACION
                                    </p>
                                @elseif ($etapaMostrada == 2)
                                    <p>
                                        EN DESPACHO
                                    </p>
                                @elseif ($etapaMostrada == 3)
                                    <p>
                                        DESPACHO ENTREGADO
                                    </p>
                                @endif
                            </div>
                            <div class="text-center mt-4">
                                @if ($etapaMostrada  == 1)
                                    @if ($estadoMensaje)
                                        <!-- Mostrar todos los mensajes de estado del historial -->
                                        @foreach ($estadoMensaje as $mensaje)
                                            <div class="d-flex align-items-center">
                                                <div class="timeline-circle"></div>
                                                <p class="ms-2 mt-3"><b>{{ $mensaje }}</b></p>
                                            </div>
                                        @endforeach
                                    @endif
                                        @if ($mensajeEtapa1)
                                            <div class="d-flex align-items-center">
                                                <div class="timeline-circle"></div>
                                                <p class="ms-2 mt-3"><b>{{ $mensajeEtapa1 }}</b></p>
                                            </div>
                                        @endif
                                @elseif ($etapaMostrada == 2)
                                    @if ($mensajeEstadoFacturaEtapa2)
                                        <div class="d-flex align-items-center">
                                            <div class="timeline-circle"></div>
                                            <p class="ms-2 mt-3"><b>{{ $mensajeEstadoFacturaEtapa2 }}</b></p>
                                        </div>
                                    @endif
                                    @foreach ($mensajeEstadoEtapa2 as $mensaje)
                                            <div class="d-flex align-items-center">
                                                <div class="timeline-circle"></div>
                                                <p class="ms-2 mt-3"><b>{{ $mensaje }}</b></p>
                                            </div>
                                        @endforeach
                                    @if ($mensajeEtapa2)
                                        <div class="d-flex align-items-center">
                                            <div class="timeline-circle"></div>
                                            <p class="ms-2 mt-3"><b>{{ $mensajeEtapa2 }}</b></p>
                                        </div>
                                    @endif
                                @elseif ($etapaMostrada == 3)
                                    @foreach ($mensajeEstadoEtapa3 as $mensaje)
                                        <div class="d-flex align-items-center">
                                            <div class="timeline-circle"></div>
                                            <p class="ms-2 mt-3"><b>{{ $mensaje }}</b></p>
                                        </div>
                                    @endforeach
                                    @if ($mensajeEtapa3)
                                        <div class="d-flex align-items-center">
                                            <div class="timeline-circle"></div>
                                            <p class="ms-2 mt-3"><b>{{ $mensajeEtapa3 }}</b></p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot>
        </x-card-general-view>
    @endif

    <style>
        .tracking-img {
            width: 70px;
            height: 70px;
        }

        .progress-line {
            width: 70%;
            height: 4px;
            background: lightgray;
            position: relative;
            margin: auto;
        }

        .progress-bar {
            height: 100%;
            background: #3AB54A;
            position: absolute;
            top: 0;
            left: 0;
        }

        .tracking-circle {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            position: absolute;
            top: -8px; /* Ajuste para que quede centrado en la línea */
            transform: translateX(-50%);
        }

        .circle-green {
            background-color: #3AB54A;
        }

        .circle-gray {
            background-color: gray;
        }


        .timeline-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            position: relative;
            background: #3AB54A;
        }

        .timeline-circle::before {
            content: "";
            position: absolute;
            width: 2px;
            height: 45px;
            left: 50%;
            top: 100%;
            transform: translateX(-50%);
            background: #3AB54A;
        }

        .d-flex.align-items-center:last-child .timeline-circle::before {
            display: none;
        }
    </style>
</div>
