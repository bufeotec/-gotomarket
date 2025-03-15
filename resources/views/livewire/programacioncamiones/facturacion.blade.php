<div>
    @php
        $me = new \App\Models\General();
    @endphp
    {{--    MODAL CAMBIAR ESTADO PRE PROGRAMACION--}}
    <x-modal-delete wire:ignore.self>
        <x-slot name="id_modal">modalPrePro</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_pre_pro">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{ $messagePrePro }}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_fac_pre_prog') <span class="message-error">{{ $message }}</span> @enderror
                        @error('fac_pre_prog_estado_aprobacion') <span class="message-error">{{ $message }}</span> @enderror
                        @if (session()->has('error_pre_pro'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_pre_pro') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3" id="fechaHoraContainer" style="display: none;">
                        <label for="fechaHoraManual">Modificar fecha y hora:</label>
                        <input type="datetime-local" id="fechaHoraManual" wire:model="fechaHoraManual" wire:change="actualizarMensaje" class="form-control">
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">ENVIAR</button>
                        <button type="button" class="btn btn-success btnDelete" id="btnEditar">EDITAR</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
    {{-- MODAL RECHAZAR FACTURA EN APROBRAR --}}

    {{--    MODAL GESTIONAR ESTADOS--}}
    <x-modal-delete wire:ignore.self>
        <x-slot name="id_modal">modalGeStado</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiarEstado">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{ $messagePrePro }}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error_pre_pro'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_pre_pro') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3">
                        <select id="estadoSelect" wire:model="guia_estado_aprobacion" class="form-control">
                            <option value="">Seleccionar</option>
                            <option value="0">Anulado</option>
                            <option value="8">Entregado</option>
                        </select>
                    </div>
                    @error('id_guia') <span class="message-error">{{ $message }}</span> @enderror
                    @error('guia_estado_aprobacion') <span class="message-error">{{ $message }}</span> @enderror
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">ENVIAR</button>
                        <button type="button" class="btn btn-danger btnDelete" id="btnEditar" data-bs-dismiss="modal">CANCELAR</button>

                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
    {{--    FIN MODAL GESTIONAR ESTADOS--}}

    {{--    MODAL VER GUIA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalGuiaDetalles</x-slot>
        <x-slot name="titleModal">Información de la guia Seleccionada</x-slot>
        <x-slot name="modalContent">
            @if($guiadetalle)
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
                                    <p>{{ $guiadetalle->guia_nro_doc }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Fecha Emisión:</strong>
                                    <p>{{ $guiadetalle->guia_fecha_emision }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Factura:</strong>
                                    <p>{{ $guiadetalle->guia_nro_doc_ref }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Cliente:</strong>
                                    <p>{{ $guiadetalle->guia_nombre_cliente }}</p>
                                </div>
                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Dirección:</strong>
                                    <p>{{ $guiadetalle->guia_direc_entrega }}</p>
                                </div>
                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Cliente:</strong>
                                    <p>{{ $guiadetalle->guia_nombre_cliente }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Documento Referencial:</strong>
                                    <p>{{ $guiadetalle->guia_tipo_doc_ref }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Número de Documento Referencial:</strong>
                                    <p>{{ $guiadetalle->guia_tipo_doc_ref }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Glosa:</strong>
                                    <p>{{ $guiadetalle->guia_glosa }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Estado:</strong>
                                    <p>{{ $guiadetalle->guia_estado }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Importe Total:</strong>
                                    <p>{{ $guiadetalle->guia_importe_total }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Cambio:</strong>
                                    <p>{{ $guiadetalle->guia_tipo_cambio }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Moneda:</strong>
                                    <p>{{ $guiadetalle->guia_moneda }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Dirección de Entrega:</strong>
                                    <p>{{ $guiadetalle->guia_direc_entrega }}</p>
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
    {{--    FINMODAL VER GUIA--}}

    {{--    MODAL DETALLES GUIAS --}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleGuia</x-slot>
        <x-slot name="titleModal">Detalles de la guía Seleccionada</x-slot>
        <x-slot name="modalContent">
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <h6>Detalles de la Guía</h6>
                <hr>
                @if(!empty($facturadetalle))
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
                            @foreach($facturadetalle as $detalle)
                                <tr>
                                    <td>{{ $detalle->guia_det_almacen_salida ?? '-' }}</td>
                                    <td>@if (is_object($detalle) && isset($detalle->guia_det_fecha_emision))
                                            {{ $detalle->guia_det_fecha_emision ? $me->obtenerNombreFecha($detalle->guia_det_fecha_emision, 'DateTime', 'DateTime') : '-' }}
                                        @else
                                            {{ '-' }}
                                        @endif</td>
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
    {{--    FINMODAL DETALLES GUIAS--}}

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

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <div class="row mb-2">
                        <h6>Gestionar el estado de la Factura</h6>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            @if(count($facturas_pre_prog_estadox) > 0)
                                <x-table-general id="facturasPreProgTable">
                                    <x-slot name="thead">
                                        <tr>
                                            <th>Guía</th>
                                            <th>Emisión</th>
                                            <th>Factura</th>
                                            <th>Importe sin IGV</th>
                                            <th>Cliente</th>
                                            <th>Dirección</th>
                                            <th>Peso / Volumen</th>
                                            <th>Recibido</th>
                                            <th>Movimientos</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @foreach($facturas_pre_prog_estadox as $factura)
                                            <tr>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_nro_doc }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $fechaEmision = \Carbon\Carbon::parse($factura->guia_fecha_emision)->format('d/m/Y');
                                                    @endphp
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $fechaEmision }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes text-center">
                                                        {{ $factura->guia_nro_doc_ref ?? '---' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $importe = number_format($factura->guia_importe_total, 2, '.', ',');
                                                    @endphp
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        <b class="colorBlackComprobantes">{{ $importe }}</b>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_nombre_cliente }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_direc_entrega }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $factura->total_peso }} g
                                                    <br>
                                                    {{ $factura->total_volumen }} cm³
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ date('d/m/Y - h:i a', strtotime($factura->updated_at)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes text-primary">
                                                        @switch($factura->guia_estado_aprobacion)
                                                            @case(0)
                                                                <span style="color: red;">Anulado</span>
                                                                @break
                                                            @case(1)
                                                                Enviado a Créditos
                                                                @break
                                                            @case(2)
                                                                Enviado a Despacho
                                                                @break
                                                            @case(3)
                                                                Listo para despacho
                                                                @break
                                                            @case(4)
                                                                Facturas despachadas
                                                                @break
                                                            @case(5)
                                                                Aceptado por Créditos
                                                                @break
                                                            @case(6)
                                                                Estado de facturación
                                                                @break
                                                            @case(7)
                                                                En Tránsito
                                                                @break
                                                            @case(8)
                                                                <span style="color: green;">Entregado</span>
                                                                @break
                                                                @break
                                                                @default
                                                                Estado desconocido
                                                        @endswitch
                                                    </span>
                                                </td>
{{--                                                <td>--}}
{{--                                                    @if ($factura->guia_estado_aprobacion != 2)--}}
{{--                                                        <x-btn-accion class="btn bg-success btn-sm text-white" wire:click="cambio_estado('{{ base64_encode($factura->id_guia) }}', 2)" data-bs-toggle="modal" data-bs-target="#modalPrePro">--}}
{{--                                                            <x-slot name="message">--}}
{{--                                                                <i class="fa-solid fa-check"></i>--}}
{{--                                                            </x-slot>--}}
{{--                                                        </x-btn-accion>--}}
{{--                                                    @endif--}}
{{--                                                        <x-btn-accion class="btn bg-primary btn-sm text-white" wire:click="abrirModal('{{ base64_encode($factura->id_guia) }}')" data-bs-toggle="modal" data-bs-target="#modalGeStado">--}}
{{--                                                            <x-slot name="message">--}}
{{--                                                                <i class="fa-solid fa-edit"></i>--}}
{{--                                                            </x-slot>--}}
{{--                                                        </x-btn-accion>--}}
{{--                                                        <x-btn-accion class="btn btn-info btn-sm text-white" wire:click.prevent="modal_guia_detalle('{{ $factura->id_guia}}')" data-bs-toggle="modal" data-bs-target="#modalGuiaDetalles">--}}
{{--                                                            <x-slot name="message">--}}
{{--                                                                <i class="fas fa-eye"></i>--}}
{{--                                                                Guía--}}
{{--                                                            </x-slot>--}}
{{--                                                        </x-btn-accion>--}}
{{--                                                        <x-btn-accion class="btn btn-warning btn-sm text-white" wire:click.prevent="modal_factura_detalle('{{ $factura->id_guia}}')" data-bs-toggle="modal" data-bs-target="#modalDetalleGuia">--}}
{{--                                                            <x-slot name="message">--}}
{{--                                                                <i class="fas fa-eye"></i>--}}
{{--                                                                Factura--}}
{{--                                                            </x-slot>--}}
{{--                                                        </x-btn-accion>--}}
{{--                                                </td>--}}
                                                <td>
                                                    @if (!in_array($factura->guia_estado_aprobacion, [0, 2, 8]))
                                                        <x-btn-accion class="btn bg-success btn-sm text-white" wire:click="cambio_estado('{{ base64_encode($factura->id_guia) }}', 2)" data-bs-toggle="modal" data-bs-target="#modalPrePro">
                                                            <x-slot name="message">
                                                                <i class="fa-solid fa-check"></i>
                                                            </x-slot>
                                                        </x-btn-accion>
                                                    @endif
                                                    <x-btn-accion class="btn bg-primary btn-sm text-white" wire:click="abrirModal('{{ base64_encode($factura->id_guia) }}')" data-bs-toggle="modal" data-bs-target="#modalGeStado">
                                                        <x-slot name="message">
                                                            <i class="fa-solid fa-edit"></i>
                                                        </x-slot>
                                                    </x-btn-accion>
                                                    <x-btn-accion class="btn btn-warning btn-sm text-white" wire:click.prevent="modal_factura_detalle('{{ $factura->id_guia }}')" data-bs-toggle="modal" data-bs-target="#modalDetalleGuia">
                                                            <x-slot name="message">
                                                                <i class="fas fa-eye"></i>
                                                                Factura
                                                            </x-slot>
                                                        </x-btn-accion>
                                                    <x-btn-accion class="btn btn-info btn-sm text-white" wire:click.prevent="modal_guia_detalle('{{ $factura->id_guia }}')" data-bs-toggle="modal" data-bs-target="#modalGuiaDetalles">
                                                        <x-slot name="message">
                                                            <i class="fas fa-eye"></i>
                                                            Guía
                                                        </x-slot>
                                                    </x-btn-accion>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </x-slot>
                                </x-table-general>
                            @else
                                <p>No hay facturas pre programación disponibles.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('hidemodalPrePro', () => {
        $('#modalPrePro').modal('hide');
    });

    $wire.on('hidemodaRecFac', () => {
        $('#modaRecFac').modal('hide');
    });

    $wire.on('hidemodalGeStado', () => {
        $('#modalGeStado').modal('hide');
    });

    document.getElementById("btnEditar").addEventListener("click", function() {
        let container = document.getElementById("fechaHoraContainer");
        let inputFecha = document.getElementById("fechaHoraManual");

        // Mostrar el contenedor con el label y el input
        container.style.display = "block";
        inputFecha.focus();
    });
</script>
@endscript
