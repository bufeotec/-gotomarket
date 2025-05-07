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

    {{--    MODAL VER GUIA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalInformacionGuia</x-slot>
        <x-slot name="titleModal">Información de la guia Seleccionada</x-slot>
        <x-slot name="modalContent">
            @if($guiainfo)
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h6>Información general</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Guía:</strong>
                                    <p>{{ $guiainfo->guia_nro_doc }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Nombre cliente:</strong>
                                    <p>{{ $guiainfo->guia_nombre_cliente }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Fecha Emisión:</strong>
                                    <p>{{ $guiainfo->guia_fecha_emision ? $me->obtenerNombreFecha($guiainfo->guia_fecha_emision, 'DateTime', 'Date') : '-' }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Movimiento:</strong>
                                    <p>{{ $guiainfo->guia_tipo_movimiento }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Documento Referencial:</strong>
                                    <p>{{ $guiainfo->guia_tipo_doc_ref }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Número de Documento Referencial:</strong>
                                    <p>{{ $guiainfo->guia_nro_doc_ref }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Glosa:</strong>
                                    <p>{{ $guiainfo->guia_glosa }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Estado:</strong>
                                    <p>{{ $guiainfo->guia_estado }}</p>
                                </div>

                                <div class="col-lg-4">
                                    <strong style="color: #8c1017">Importe Total sin IGV:</strong>
                                    <p>{{ $me->formatoDecimal($guiainfo->guia_importe_total_sin_igv ?? 0)}}</p>
                                </div>

                                <div class="col-lg-4">
                                    <strong style="color: #8c1017">Moneda:</strong>
                                    <p>{{ $guiainfo->guia_moneda }}</p>
                                </div>

                                <div class="col-lg-4">
                                    <strong style="color: #8c1017">Dirección de Entrega:</strong>
                                    <p>{{ $guiainfo->guia_direc_entrega }}</p>
                                </div>

                                <div class="col-lg-4">
                                    <strong style="color: #8c1017">Departamento:</strong>
                                    <p>{{ $guiainfo->guia_departamento }}</p>
                                </div>

                                <div class="col-lg-4">
                                    <strong style="color: #8c1017">Provincia:</strong>
                                    <p>{{ $guiainfo->guia_provincia }}</p>
                                </div>

                                <div class="col-lg-4">
                                    <strong style="color: #8c1017">Distrito:</strong>
                                    <p>{{ $guiainfo->guia_destrito }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <p>No hay información disponibles para mostrar.</p>
            @endif
        </x-slot>
    </x-modal-general>
    {{--    FIN MODAL VER GUIA--}}

    {{--    MODAL DETALLE GUIA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleGuia</x-slot>
        <x-slot name="titleModal">Detalles de la guía</x-slot>
        <x-slot name="modalContent">
            <div class="modal-body">
                <h6>Detalles de la Guía</h6>
                <hr>
                @if(!empty($guia_detalle))
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>Almacén Salida</th>
                                <th>Fecha Emisión</th>
                                <th>Estado</th>
                                <th>Tipo Documento</th>
                                <th>Nro Documento</th>
                                <th>Nro Línea</th>
                                <th>Cód Producto</th>
                                <th>Descripción Producto</th>
                                <th>Lote</th>
                                <th>Unidad</th>
                                <th>Cantidad</th>
                                <th>Precio Unit Final Inc IGV</th>
                                <th>Precio Unit Antes Descuento Inc IGV</th>
                                <th>Descuento Total Sin IGV</th>
                                <th>IGV Total</th>
                                <th>Importe Total Inc IGV</th>
                                <th>Moneda</th>
                                <th>Tipo Cambio</th>
                                <th>Peso Gramos</th>
                                <th>Volumen CM3</th>
                                <th>Peso Total Gramos</th>
                                <th>Volumen Total CM3</th>
                            </tr>
                        </x-slot>
                        <x-slot name="tbody">
                            @foreach($guia_detalle as $detalle)
                                <tr>
                                    <td>{{ $detalle->guia_det_almacen_salida ?? '-' }}</td>
                                    <td>{{ $detalle->guia_det_fecha_emision ? $me->obtenerNombreFecha($detalle->guia_det_fecha_emision, 'DateTime', 'Date') : '-' }}</td>
                                    <td>{{ $detalle->guia_det_estado ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_tipo_documento ?? '-' }}</td>
                                    <td>{{ $detalle->guia_det_nro_documento ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_nro_linea ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_cod_producto ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_descripcion_producto ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_lote ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_unidad ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_cantidad ?? '-'}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->guia_det_precio_unit_final_inc_igv ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->guia_det_precio_unit_antes_descuente_inc_igv ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->guia_det_descuento_total_sin_igv ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->guia_det_igv_total ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->guia_det_importe_total_inc_igv ?? 0) }}</td>
                                    <td>{{ $detalle->guia_det_moneda ?? '-'}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->guia_det_tipo_cambio ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->guia_det_peso_gramo ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->guia_det_volumen ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->guia_det_peso_total_gramo ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->guia_det_volumen_total ?? 0)}}</td>
                                </tr>
                            @endforeach
                        </x-slot>
                    </x-table-general>
                @else
                    <p>No hay detalles disponibles para mostrar.</p>
                @endif
            </div>
        </x-slot>
    </x-modal-general>
    {{--    MODAL FIN DETALLE GUIA--}}

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="row justify-content-center text-center mt-3">
                            <div class="col-lg-12 mb-3">
                                <h5>{{$nombre}}</h5>
                            </div>
                        </div>
                        <!-- ETAPAS -->
                        <div class="row justify-content-center text-center mt-3">
                            <!-- Nueva etapa: Fecha de Emisión -->
                            <div class="col-lg-2 col-md-2 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/fecha.png') }}" alt="Fecha de Emisión" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 0 ? 'text-dark' : 'text-muted' }}">FECHA DE EMISIÓN</p>
                            </div>

                            <!-- Etapa 1: En Créditos -->
                            <div class="col-lg-2 col-md-2 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/creditos.png') }}" alt="Pre Programación" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 1 ? 'text-dark' : 'text-muted' }}">EN CREDITOS</p>
                            </div>

                            <!-- Etapa 2: Por Programar -->
                            <div class="col-lg-2 col-md-2 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/despacho_por_aprobar.png') }}" alt="En Despacho" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 2 ? 'text-dark' : 'text-muted' }}">POR PROGRAMAR</p>
                            </div>

                            <!-- Etapa 3: Programado -->
                            <div class="col-lg-2 col-md-2 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/despacho_aprobado.png') }}" alt="Despacho Entregado" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 3 ? 'text-dark' : 'text-muted' }}">PROGRAMADO</p>
                            </div>

                            <!-- Etapa 4: En Ruta -->
                            <div class="col-lg-2 col-md-2 col-sm-12">
                                <img src="{{ asset('assets/images/tracking/comprobante_en_camino.png') }}" alt="Despacho Entregado" class="tracking-img">
                                <p class="fw-bold {{ $etapaActual >= 4 ? 'text-dark' : 'text-muted' }}">EN RUTA</p>
                            </div>

                            <!-- Etapa 5: Guía Entregado -->
                            <div class="col-lg-2 col-md-2 col-sm-12">
                                @php
                                    $comprobanteNoEntregado = collect($mensajeEtapa5 ?? [])->contains(fn($mensaje) => str_contains($mensaje, 'Guía no entregado.'));
                                @endphp

                                @if ($comprobanteNoEntregado)
                                    <img src="{{ asset('assets/images/tracking/comprobante_no_entregado.png') }}" alt="Guía No Entregado" class="tracking-img">
                                    <p class="fw-bold text-dark">GUÍA NO ENTREGADO</p>
                                @else
                                    <img src="{{ asset('assets/images/tracking/comprobante_entregado.png') }}" alt="Guía Entregado" class="tracking-img">
                                    <p class="fw-bold {{ $etapaActual >= 5 ? 'text-dark' : 'text-muted' }}">GUÍA ENTREGADO</p>
                                @endif
                            </div>
                        </div>

                        <!-- Línea de progreso con círculos -->
                        <div class="d-flex justify-content-center position-relative mb-3">
                            <div class="progress-line mt-4">
                                <div class="progress-bar" style="width: {{ ($etapaActual) * 20 }}%;"></div>
                                <!-- Círculo 1 -->
                                <div class="tracking-circle {{ $etapaActual >= 0 ? 'circle-green' : 'circle-gray' }}" style="left: 0%;"></div>
                                <!-- Círculo 2 -->
                                <div class="tracking-circle {{ $etapaActual >= 1 ? 'circle-green' : 'circle-gray' }}" style="left: 20%;"></div>
                                <!-- Círculo 3 -->
                                <div class="tracking-circle {{ $etapaActual >= 2 ? 'circle-green' : 'circle-gray' }}" style="left: 40%;"></div>
                                <!-- Círculo 4 -->
                                <div class="tracking-circle {{ $etapaActual >= 3 ? 'circle-green' : 'circle-gray' }}" style="left: 60%;"></div>
                                <!-- Círculo 5 -->
                                <div class="tracking-circle {{ $etapaActual >= 4 ? 'circle-green' : 'circle-gray' }}" style="left: 80%;"></div>
                                <!-- Círculo 6 -->
                                <div class="tracking-circle {{ $etapaActual >= 5 ? 'circle-green' : 'circle-gray' }}" style="left: 100%;"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center mb-3">
                            <div class="col-lg-2 col-md-2 col-sm-12 clase__mensaje">
                                @if($mensajeEtapa0)
                                    <div class="tracking-message {{ $etapaActual >= 0 ? 'text-dark' : 'text-muted' }}">
                                        <b>{!! $mensajeEtapa0 !!}</b>
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 clase__mensaje">
                                @if($mensajeEtapa1)
                                    <div class="tracking-message {{ $etapaActual >= 1 ? 'text-dark' : 'text-muted' }}">
                                        <b>{!! $mensajeEtapa1 !!}</b>
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 clase__mensaje">
                                @if($mensajeEtapa2)
                                    <div class="tracking-message {{ $etapaActual >= 2 ? 'text-dark' : 'text-muted' }}">
                                        <b>{!! $mensajeEtapa2 !!}</b>
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 clase__mensaje">
                                @if($mensajeEtapa3)
                                    <div class="tracking-message {{ $etapaActual >= 3 ? 'text-dark' : 'text-muted' }}">
                                        <b>{!! $mensajeEtapa3 !!}</b>
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 clase__mensaje">
                                @if($mensajeEtapa4)
                                    <div class="tracking-message {{ $etapaActual >= 4 ? 'text-dark' : 'text-muted' }}">
                                        <b>{!! $mensajeEtapa4 !!}</b>
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 clase__mensaje">
                                @if($mensajeEtapa5)
                                    <div class="tracking-message {{ $etapaActual >= 5 ? 'text-dark' : 'text-muted' }}">
                                        <b>{!! $mensajeEtapa5 !!}</b>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-12 p-3">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h6 class="mb-2">
                                        Información Documentos
                                    </h6>
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th>Guía</th>
                                                <th>Factura</th>
                                                <th>Monto sin IGV</th>
                                                <th>Peso / Volumen</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @foreach($facturas as $f)
                                                <tr>
                                                    <td>
                                                        {{ $f['guia_nro_doc'] }}
                                                        <x-btn-accion class="btn text-primary" wire:click.prevent="modal_guia_info('{{ $f['id_guia']}}')" data-bs-toggle="modal" data-bs-target="#modalInformacionGuia">
                                                            <x-slot name="message">
                                                                <i class="fas fa-eye"></i>
                                                            </x-slot>
                                                        </x-btn-accion>

                                                        <x-btn-accion class="btn text-success" wire:click="generar_excel_guia_factura" wire:loading.attr="disabled">
                                                            <x-slot name="message">
                                                                <i class="fa-solid fa-file-excel"></i>
                                                            </x-slot>
                                                        </x-btn-accion>
                                                    </td>
                                                    <td>
                                                        {{ $f['guia_nro_doc_ref'] }}
                                                        <x-btn-accion class="btn text-primary" wire:click.prevent="listar_detalle_guia('{{ $f['id_guia'] }}')" data-bs-toggle="modal" data-bs-target="#modalDetalleGuia">
                                                            <x-slot name="message">
                                                                <i class="fas fa-eye"></i>
                                                            </x-slot>
                                                        </x-btn-accion>
                                                    </td>
                                                    <td>S/ {{ $me->formatoDecimal($f['guia_importe_total_sin_igv']) }}</td>
                                                    <td>
                                                        {{ $me->formatoDecimal($f['peso_total_kilogramos']) }} kg /<br>
                                                        {{ $me->formatoDecimal($f['volumen_total']) }} cm³
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </x-slot>
                                    </x-table-general>
                                </div>
                                <div class="col-lg-6">
                                    <h6 class="mb-2">
                                        Documentos Relacionados
                                    </h6>
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th>Guía</th>
                                                <th>Factura</th>
                                                <th>Monto sin IGV</th>
                                                <th>Peso / Volumen</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if (!empty($facturasRelacionadas))
                                                @foreach ($facturasRelacionadas as $fr)
                                                    <tr>
                                                        <td>
                                                            {{ $fr->guia_nro_doc }}
                                                            <x-btn-accion class="btn text-primary" wire:click.prevent="modal_guia_info('{{ $fr->id_guia }}')" data-bs-toggle="modal" data-bs-target="#modalInformacionGuia">
                                                                <x-slot name="message">
                                                                    <i class="fas fa-eye"></i>
                                                                </x-slot>
                                                            </x-btn-accion>

                                                            <x-btn-accion class="btn text-success"
                                                                          wire:click="generar_excel_guia_factura('{{ $fr->id_guia }}')"
                                                                          wire:loading.attr="disabled">
                                                                <x-slot name="message">
                                                                    <i class="fa-solid fa-file-excel"></i>
                                                                </x-slot>
                                                            </x-btn-accion>
                                                        </td>
                                                        <td>
                                                            {{ $fr->guia_nro_doc_ref }}
                                                            <x-btn-accion class="btn text-primary" wire:click.prevent="listar_detalle_guia('{{ $fr->id_guia }}')" data-bs-toggle="modal" data-bs-target="#modalDetalleGuia">
                                                                <x-slot name="message">
                                                                    <i class="fas fa-eye"></i>
                                                                </x-slot>
                                                            </x-btn-accion>
                                                        </td>
                                                        <td>S/ {{ $me->formatoDecimal($fr->guia_importe_total_sin_igv) }}</td>
                                                        <td>
                                                            {{ $me->formatoDecimal($fr->peso_total_kilogramos ?? 0) }} kg /<br>
                                                            {{ $me->formatoDecimal($fr->volumen_total ?? 0) }} cm³
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay guías relacionadas.</td>
                                                </tr>
                                            @endif
                                        </x-slot>
                                    </x-table-general>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    <style>
        .clase__mensaje{
            font-size: 14px;
            text-align: center;
        }
        .tracking-img {
            width: 60px;
            height: 60px;
        }

        .progress-line {
            width: 85%;
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
