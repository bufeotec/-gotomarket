<div>
    @php
        $me = new \App\Models\General();
    @endphp
    {{--    MODAL CAMBIAR ESTA PRE PROGRAMACION--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalPrePro</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_pre_pro">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messagePrePro}}</h2>
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
                        <label for="fmanual">Modificar fecha y hora:</label>
                        <input type="datetime-local" id="fmanual" wire:model="fmanual" wire:change="actualizarMensaje" class="form-control">
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">ENVIAR</button>
                        <button type="button" class="btn btn-success btnDelete" id="btnEditar">EDITAR</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>

    {{--    MODAL VER DETALLES--}}
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
                                    <strong style="color: #8c1017">Fecha Emisión:</strong>
                                    <p>{{ $guiainfo->guia_fecha_emision }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Factura:</strong>
                                    <p>{{ $guiainfo->guia_nro_doc_ref }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Cliente:</strong>
                                    <p>{{ $guiainfo->guia_nombre_cliente }}</p>
                                </div>
                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Dirección:</strong>
                                    <p>{{ $guiainfo->guia_direc_entrega }}</p>
                                </div>
                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Cliente:</strong>
                                    <p>{{ $guiainfo->guia_nombre_cliente }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Documento Referencial:</strong>
                                    <p>{{ $guiainfo->guia_tipo_doc_ref }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Número de Documento Referencial:</strong>
                                    <p>{{ $guiainfo->guia_tipo_doc_ref }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Glosa:</strong>
                                    <p>{{ $guiainfo->guia_glosa }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Estado:</strong>
                                    <p>{{ $guiainfo->guia_estado }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Importe Total:</strong>
                                    <p>{{ $guiainfo->guia_importe_total }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Cambio:</strong>
                                    <p>{{ $guiainfo->guia_tipo_cambio }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Moneda:</strong>
                                    <p>{{ $guiainfo->guia_moneda }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Dirección de Entrega:</strong>
                                    <p>{{ $guiainfo->guia_direc_entrega }}</p>
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
    {{--    FIN MODAL VER DETALLES--}}

    {{--    MODAL DETALLE GUIA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleGuia</x-slot>
        <x-slot name="titleModal">Detalles de la guía Seleccionada</x-slot>
        <x-slot name="modalContent">
            <div class="modal-body">
                <h6>Detalles de la Guía</h6>
                <hr>
                @if(!empty($detallesGuia))
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
                            @foreach($detallesGuia as $detalle)
                                <tr>
                                    <td>{{ $detalle->ALMACEN_SALIDA ?? '-' }}</td>
                                    <td>{{ $detalle->FECHA_EMISION ? $me->obtenerNombreFecha($detalle->FECHA_EMISION, 'DateTime', 'DateTime') : '-' }}</td>
                                    <td>{{ $detalle->ESTADO ?? '-'}}</td>
                                    <td>{{ $detalle->TIPO_DOCUMENTO ?? '-' }}</td>
                                    <td>{{ $detalle->NRO_DOCUMENTO ?? '-'}}</td>
                                    <td>{{ $detalle->NRO_LINEA ?? '-'}}</td>
                                    <td>{{ $detalle->COD_PRODUCTO ?? '-'}}</td>
                                    <td>{{ $detalle->DESCRIPCION_PRODUCTO ?? '-'}}</td>
                                    <td>{{ $detalle->LOTE ?? '-'}}</td>
                                    <td>{{ $detalle->UNIDAD ?? '-'}}</td>
                                    <td>{{ $detalle->CANTIDAD ?? '-'}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->PRECIO_UNIT_FINAL_INC_IGV ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->PRECIO_UNIT_ANTES_DESCUENTO_INC_IGV ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->DESCUENTO_TOTAL_SIN_IGV ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->IGV_TOTAL ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->IMPORTE_TOTAL_INC_IGV ?? 0) }}</td>
                                    <td>{{ $detalle->MONEDA ?? '-'}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->TIPO_CAMBIO ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->PESO_GRAMOS ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->VOLUMEN_CM3 ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->PESO_TOTAL_GRAMOS ?? 0)}}</td>
                                    <td>{{ $me->formatoDecimal($detalle->VOLUMEN_TOTAL_CM3 ?? 0)}}</td>
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

    {{-- MODAL RECHAZAR FACTURA EN APROBRAR --}}
    <x-modal-delete wire:ignore.self style="z-index: 1056;">
        <x-slot name="id_modal">modaRecFac</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="rechazar_factura_aprobar">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{ $messageRecFactApro }}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 my-3">
                        <textarea id="fac_mov_area_motivo_rechazo" class="form-control" rows="3" wire:model="fac_mov_area_motivo_rechazo" placeholder="Ingrese motivo rechazo..."></textarea>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error-modal-rechazo'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error-modal-rechazo') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">SI</button>
                        <button type="button" data-bs-dismiss="modal" class="btn btn-danger btnDelete">No</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
    {{-- MODAL RECHAZAR FACTURA EN APROBRAR --}}

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
                        <h6>Recibidos por validar</h6>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            @if(count($facturas_pre_prog_estado_dos) > 0)
                                <x-table-general id="facturasPreProgTable">
                                    <x-slot name="thead">
                                        <tr>
                                            <th>Guía</th>
                                            <th>F. Emisión</th>
                                            <th>Factura</th>
                                            <th>Importe sin IGV</th>
                                            <th>Cliente</th>
                                            <th>Dirección</th>
                                            <th>Peso y Volumen</th>
                                            <th>Fecha/Hora Recibida</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @foreach($facturas_pre_prog_estado_dos as $factura)
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
                                        <span class="d-block tamanhoTablaComprobantes">
                                            {{ $factura->guia_nro_doc_ref }}
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
                                            {{date('d/m/Y - h:i A', strtotime($factura->updated_at)) }}
                                        </span>
                                                </td>
                                                <td>
                                                    <x-btn-accion class="btn bg-success btn-sm text-white" wire:click="cambio_estado('{{ base64_encode($factura->id_guia) }}', 3)" data-bs-toggle="modal" data-bs-target="#modalPrePro">
                                                        <x-slot name="message">
                                                            <i class="fa-solid fa-check"></i>
                                                        </x-slot>
                                                    </x-btn-accion>
                                                    <x-btn-accion class="btn btn-primary btn-sm text-white" wire:click.prevent="modal_guia_info('{{ $factura->id_guia}}')" data-bs-toggle="modal" data-bs-target="#modalInformacionGuia">
                                                        <x-slot name="message">
                                                            <i class="fas fa-eye"></i>Guía
                                                        </x-slot>
                                                    </x-btn-accion>
                                                    @if(!empty($factura->guia_nro_doc_ref)) <!-- Verifica si el campo factura no está vacío -->
                                                    <x-btn-accion class="btn btn-warning btn-sm text-white" wire:click.prevent="listar_detallex('{{ $factura->guia_nro_doc }}')" data-bs-toggle="modal" data-bs-target="#modalDetallex">
                                                        <x-slot name="message">
                                                            <i class="fas fa-eye"></i>Factura
                                                        </x-slot>
                                                    </x-btn-accion>
                                                    @endif
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

    document.getElementById("btnEditar").addEventListener("click", function() {
        let container = document.getElementById("fechaHoraContainer");
        let inputFecha = document.getElementById("fmanual");

        // Mostrar el contenedor con el label y el input
        container.style.display = "block";
        inputFecha.focus();
    });
</script>
@endscript
