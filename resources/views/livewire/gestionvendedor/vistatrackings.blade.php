<div>
    @php
        $me = new \App\Models\General();
    @endphp
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

    {{--    MODAL DETALLES FACTURAS --}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalDetallesFact</x-slot>
        <x-slot name="titleModal">Detalles de la factura</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
            <div class="row">

            </div>
        </x-slot>
    </x-modal-general>

        <x-card-general-view>
            <x-slot name="content">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-end m-4">
                            <a class="btn bg-primary btn-lg text-white text-center">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 m3">
                        <!-- ETAPAS -->
                        <div class="row justify-content-center text-center mt-3">
                            <div class="col-lg-2 col-md-4 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/creditos.png') }}" alt="Pre Programación" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 1 ? 'text-dark' : 'text-muted' }}">EN CREDITOS</p>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/despacho_por_aprobar.png') }}" alt="En Despacho" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 2 ? 'text-dark' : 'text-muted' }}">POR PROGRAMAR</p>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/despacho_aprobado.png') }}" alt="Despacho Entregado" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 3 ? 'text-dark' : 'text-muted' }}">PROGRAMADO</p>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/comprobante_en_camino.png') }}" alt="Despacho Entregado" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 4 ? 'text-dark' : 'text-muted' }}">EN RUTA</p>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-12">
                                @php
                                    $comprobanteNoEntregado = collect($mensajeEstadoEtapa3 ?? [])->contains(fn($mensaje) => str_contains($mensaje, 'Estado: Comprobante no entregado.'));
                                @endphp

                                @if ($comprobanteNoEntregado)
                                    <img src="{{ asset('assets/images/tracking/comprobante_no_entregado.png') }}" alt="Comprobante No Entregado" class="tracking-img">
                                    <p class="fw-bold text-dark">COMPROBANTE NO ENTREGADO</p>
                                @else
                                    <img src="{{ asset('assets/images/tracking/comprobante_entregado.png') }}" alt="Comprobante Entregado" class="tracking-img">
                                    <p class="fw-bold {{ $etapaActual >= 5 ? 'text-dark' : 'text-muted' }}">COMPROBANTE ENTREGADO</p>
                                @endif
                            </div>

                        </div>

                        <!-- Línea de progreso con círculos -->
                        <div class="d-flex justify-content-center position-relative">
                            <div class="progress-line mt-4">
                                <div class="progress-bar" style="width: {{ ($etapaActual - 1) * 25 }}%;"></div>

                                <!-- Círculo 1 -->
                                <div class="tracking-circle {{ $etapaActual >= 1 ? 'circle-green' : 'circle-gray' }}" style="left: 0%;"></div>
                                <!-- Círculo 2 -->
                                <div class="tracking-circle {{ $etapaActual >= 2 ? 'circle-green' : 'circle-gray' }}" style="left: 25%;"></div>
                                <!-- Círculo 3 -->
                                <div class="tracking-circle {{ $etapaActual >= 3 ? 'circle-green' : 'circle-gray' }}" style="left: 50%;"></div>
                                <!-- Círculo 4 -->
                                <div class="tracking-circle {{ $etapaActual >= 4 ? 'circle-green' : 'circle-gray' }}" style="left: 75%;"></div>
                                <!-- Círculo 5 -->
                                <div class="tracking-circle {{ $etapaActual >= 5 ? 'circle-green' : 'circle-gray' }}" style="left: 100%;"></div>
                            </div>
                        </div>

                        <!-- Mensajes y Estados -->
                        @if (!empty($mensajesCompletos))
                            <div class="col-lg-12 d-flex justify-content-center mt-3">
                                <div class="ard">
                                    <div class="card-body">
                                        @foreach ($mensajesCompletos as $mensaje)
                                            <div class="d-flex align-items-center">
                                                <div class="timeline-circle"></div>
                                                <p class="ms-2 mt-3"><b>{{ $mensaje }}</b></p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-lg-12 p-3">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h6 class="mb-2">
                                        Información de la Facturas
                                    </h6>
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th>Factura</th>
                                                <th>Guía</th>
                                                <th>Monto</th>
                                                <th>Ver detalle</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @foreach($facturas as $f)
                                                <tr>
                                                    <td>{{ $f['fac_pre_prog_cfnumdoc'] }}</td>
                                                    <td>{{ $f['fac_pre_prog_guia'] }}</td>
                                                    <td>{{ $me->formatoDecimal($f['fac_pre_prog_cfimporte']) }}</td>
                                                    <td class="text-center">
                                                        <x-btn-accion class="text-primary"  data-bs-toggle="modal" data-bs-target="#modalDetallesFact">
                                                            <x-slot name="message">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </x-slot>
                                                        </x-btn-accion>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </x-slot>
                                    </x-table-general>
                                </div>
                                <div class="col-lg-6">
                                    <h6 class="mb-2">
                                        Facturas relacionadas
                                    </h6>
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th>Factura</th>
                                                <th>Guía</th>
                                                <th>Monto</th>
                                                <th>Peso Kilos</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if (!empty($facturasRelacionadas))
                                                @foreach ($facturasRelacionadas as $fr)
                                                    <tr>
                                                        <td>{{ $fr['despacho_venta_cfnumdoc'] }}</td>
                                                        <td>{{ $fr['despacho_venta_guia'] }}</td>
                                                        <td>{{ $me->formatoDecimal($fr['despacho_venta_cfimporte']) }}</td>
                                                        <td>{{ $me->formatoDecimal($fr['despacho_venta_total_kg']) }} kg</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay facturas relacionadas.</td>
                                                </tr>
                                            @endif
                                        </x-slot>
                                    </x-table-general>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </x-slot>
        </x-card-general-view>

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
