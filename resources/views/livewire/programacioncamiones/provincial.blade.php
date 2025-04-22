<div>
    @php
        $me = new \App\Models\General();
    @endphp
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="id_modal">modalVehiculo</x-slot>
        <x-slot name="titleModal">Detalles de la Tarifa</x-slot>
        <x-slot name="modalContent">
            @if($detalle_tarifario)
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <h6>Información del transportista</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Nombre comercial:</strong>
                                    <p>{{ $detalle_tarifario->transportista_nom_comercial }}</p>
                                </div>
                                <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">RUC:</strong>
                                    <p>{{ $detalle_tarifario->transportista_ruc }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <h6>Información de la tarifa</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Precio:</strong>
                                    <p>S/ {{ $me->formatoDecimal($detalle_tarifario->tarifa_monto) }}</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    @php
                                        $modalCapMin = "0";
                                        if ($detalle_tarifario->tarifa_cap_min){
                                            $modalCapMin = $me->formatoDecimal($detalle_tarifario->tarifa_cap_min);
                                        }
                                    @endphp
                                    <strong style="color: #8c1017">Capacidad en minima:</strong>
                                    <p>{{ $modalCapMin }} {{$detalle_tarifario->id_medida == 9 ? 'cm³' : 'kg' }}</p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    @php
                                        $modalCapMax = "0";
                                        if ($detalle_tarifario->tarifa_cap_max){
                                            $modalCapMax = $me->formatoDecimal($detalle_tarifario->tarifa_cap_max);
                                        }
                                    @endphp
                                    <strong style="color: #8c1017">Capacidad maxima:</strong>
                                    <p>{{ $modalCapMax }} {{$detalle_tarifario->id_medida == 9 ? 'cm³' : 'kg' }}</p>
                                </div>

                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Departamento de llegada:</strong>
                                    @php
                                        $depar = "";
                                        if ($detalle_tarifario->id_departamento){
                                            $depar = \Illuminate\Support\Facades\DB::table('departamentos')->where('id_departamento','=',$detalle_tarifario->id_departamento)->first();
                                        }
                                    @endphp
                                    <p>{{ $depar ? $depar->departamento_nombre : '-' }} </p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Provincia de llegada:</strong>
                                    @php
                                        $provi = "";
                                        if ($detalle_tarifario->id_provincia){
                                            $provi = \Illuminate\Support\Facades\DB::table('provincias')->where('id_provincia','=',$detalle_tarifario->id_provincia)->first();
                                        }
                                    @endphp
                                    <p>{{ $provi ? $provi->provincia_nombre : '-' }} </p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <strong style="color: #8c1017">Distrito de llegada:</strong>
                                    @php
                                        $distri = "";
                                        if ($detalle_tarifario->id_distrito){
                                            $distri = \Illuminate\Support\Facades\DB::table('distritos')->where('id_distrito','=',$detalle_tarifario->id_distrito)->first();
                                        }
                                    @endphp
                                    <p>{{ $distri ? $distri->distrito_nombre : 'TODOS LOS DISTRITOS' }} </p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </x-slot>
    </x-modal-general>

    {{--    MODAL AGREGAR OTROS GASTOS --}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalRegistrarGastos</x-slot>
        <x-slot name="titleModal">Registrar Gastos Operativos</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                    <label for="despacho_gasto_otros" class="form-label">Otros S/</label>
                    <input type="text" class="form-control" id="despacho_gasto_otros" name="despacho_gasto_otros" wire:input="calcularCostoTotal" wire:model="despacho_gasto_otros" onkeyup="validar_numeros(this.id)" />
                </div>
                @if($despacho_gasto_otros > 0)
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                        <label for="despacho_descripcion_otros" class="form-label">Descripción otros</label>
                        <textarea class="form-control" id="despacho_descripcion_otros" name="despacho_descripcion_otros" wire:model="despacho_descripcion_otros"></textarea>
                        @error('despacho_descripcion_otros')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
{{--                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">--}}
{{--                    <label for="despacho_ayudante" class="form-label">Mano de obra S/</label>--}}
{{--                    <input type="text" class="form-control" id="despacho_ayudante" name="despacho_ayudante" wire:input="calcularCostoTotal" wire:model="despacho_ayudante" onkeyup="validar_numeros(this.id)" />--}}
{{--                </div>--}}
            </div>
        </x-slot>
    </x-modal-general>

    <!-- MODAL MONTO MODIFICADO -->
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalMontoModificado</x-slot>
        <x-slot name="titleModal">Modificar monto</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                    <label for="despacho_monto_modificado" class="form-label">Nuevo monto</label>
                    <input type="text" class="form-control" id="despacho_monto_modificado" name="despacho_monto_modificado" wire:input="calcularCostoTotal" wire:model.live="tarifaMontoSeleccionado">
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                    <label for="despacho_descripcion_modificado" class="form-label">Descripción</label>
                    <textarea id="despacho_descripcion_modificado" class="form-control" name="despacho_descripcion_modificado" wire:model.live="despacho_descripcion_modificado"></textarea>
                    @error('despacho_descripcion_modificado')
                    <span class="message-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </x-slot>
    </x-modal-general>

    {{--    MODAL VER INFO DE LA GUIA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalInformacionGuia</x-slot>
        <x-slot name="titleModal">Información de la guia</x-slot>
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

                                <div class="col-lg-2">
                                    <strong style="color: #8c1017">Estado:</strong>
                                    <p>{{ $guiainfo->guia_estado }}</p>
                                </div>

                                <div class="col-lg-2">
                                    <strong style="color: #8c1017">Importe Total sin IGV:</strong>
                                    <p>{{ $me->formatoDecimal($guiainfo->guia_importe_total_sin_igv ?? 0)}}</p>
                                </div>

                                <div class="col-lg-2">
                                    <strong style="color: #8c1017">Moneda:</strong>
                                    <p>{{ $guiainfo->guia_moneda }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Dirección de Entrega:</strong>
                                    <p>{{ $guiainfo->guia_direc_entrega }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Departamento:</strong>
                                    <p>{{ $guiainfo->guia_departamento }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Provincia:</strong>
                                    <p>{{ $guiainfo->guia_provincia }}</p>
                                </div>

                                <div class="col-lg-3">
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
    {{--    FIN MODAL VER INFO DE LA GUIA--}}

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
        <div class="col-lg-5">
            <div class="col-lg-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <h6>GUÍAS DISPONIBLES</h6>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <div class="position-relative">
                                    <input type="text"
                                           class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                                           placeholder="Buscar guia"
                                           wire:model.live="searchGuia"
                                           style="border: none; outline: none;"
                                           oninput="document.getElementById('buscarBtn').disabled = this.value.trim() === '';" />
                                    <i class="fas fa-search position-absolute"
                                       style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="contenedor-comprobante" style="height: 650px; overflow: auto">
                                    @if($guias_estado_tres->isEmpty())
                                        <p class="text-center text-muted">No hay facturas disponibles.</p>
                                    @else
                                        <table class="table table-responsive ">
                                            <thead style="background: #E7E7FF; color: #696cff">
                                            <tr>
                                                <th>#</th>
                                                <th style="font-size: 14px">Fecha emisión guía</th>
                                                <th style="font-size: 14px">Guía</th>
                                                <th style="font-size: 14px">Factura</th>
                                                <th style="font-size: 14px">Nombre del cliente</th>
                                                <th style="font-size: 14px">Peso y Volumen</th>
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach($guias_estado_tres as $factura)
                                                @if (!in_array($factura->id_guia, array_column($selectedFacturas, 'id_guia')))
                                                    <tr>
                                                        <td>
                                                            <button class="btn btn-success btn-sm text-white mb-2 cursoPointer" wire:click="seleccionar_factura_cliente({{ $factura->id_guia }})">
                                                                <i class="fa-solid fa-check"></i>
                                                            </button>
                                                        </td>
                                                        <td>
                                                        <span class="tamanhoTablaComprobantes">
                                                            <b class="colorBlackComprobantes">{{ $me->obtenerNombreFecha($factura->guia_fecha_emision,'DateTime', 'Date')}}</b>
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura->guia_nro_doc }}
                                                        </span>
                                                            <button class="btn btn-sm text-primary mb-2" wire:click="modal_guia_info({{$factura->id_guia}})" data-bs-toggle="modal" data-bs-target="#modalInformacionGuia">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                        </td>
                                                        <td>
                                                            @if($factura->guia_tipo_doc)
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                {{ $factura->guia_nro_doc_ref}}
                                                            </span>
                                                                <button class="btn btn-sm text-primary mb-2" wire:click="listar_detalle_guia({{$factura->id_guia}})" data-bs-toggle="modal" data-bs-target="#modalDetalleGuia">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </button>
                                                            @else
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                Sin Factura Asociada
                                                            </span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura->guia_nombre_cliente }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura->peso_total)}} kg</b>
                                                        </span>
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                            <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura->volumen_total )}} cm³</b>
                                                        </span>
                                                        </td>
                                                    </tr>
                                                    <tr style="border-top: 2px solid transparent;">
                                                        <td colspan="5" style="padding-top: 0; user-select: all;">
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura->guia_direc_entrega }} <br> UBIGEO: <b class="colorBlackComprobantes">{{ $factura->guia_departamento }} - {{ $factura->guia_provincia }} - {{ $factura->guia_destrito }}</b>
                                                        </span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <h6>SERVICIO TRANSPORTE</h6>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="contenedor-comprobante" style="height: 650px; overflow: auto">
                                    @if($serv_transp->isEmpty())
                                        <p class="text-center text-muted">No hay facturas disponibles.</p>
                                    @else
                                        <table class="table table-responsive ">
                                            <thead style="background: #E7E7FF; color: #696cff">
                                            <tr>
                                                <th>#</th>
                                                <th style="font-size: 14px">Codigo</th>
                                                <th style="font-size: 14px">Motivo</th>
                                                <th style="font-size: 14px">Remitente</th>
                                                <th style="font-size: 14px">Destinatario</th>
                                                <th style="font-size: 14px">Peso y Volumen</th>
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach($serv_transp as $factura)
                                                @if (!in_array($factura->id_serv_transpt, array_column($selectedServTrns, 'id_serv_transpt')))
                                                    <tr>
                                                        <td>
                                                            <button class="btn btn-success btn-sm text-white mb-2 cursoPointer" wire:click="seleccionarServTrns({{ $factura->id_serv_transpt }})">
                                                                <i class="fa-solid fa-check"></i>
                                                            </button>
                                                        </td>
                                                        <td>
                                                        <span class="tamanhoTablaComprobantes">
                                                            <b class="colorBlackComprobantes">{{ $factura->serv_transpt_codigo }}</b>
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura->serv_transpt_motivo }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                             {{ $factura->serv_transpt_remitente_ruc }} <br><br>
                                                            {{ $factura->serv_transpt_remitente_razon_social }} <br><br>
                                                            {{ $factura->serv_transpt_remitente_direccion }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura->serv_transpt_destinatario_ruc }} <br><br>
                                                            {{ $factura->serv_transpt_destinatario_razon_social }} <br><br>
                                                            {{ $factura->serv_transpt_destinatario_direccion }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura->serv_transpt_peso)}} kg</b>
                                                        </span>
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                            <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura->serv_transpt_volumen)}} cm³</b>
                                                        </span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div wire:loading wire:target="seleccionar_factura_cliente" class="overlay__eliminar">
            <div class="spinner__container__eliminar">
                <div class="spinner__eliminar"></div>
            </div>
        </div>
            {{--            SERVICIO TRANSPORTE--}}
            <div wire:loading wire:target="seleccionarServTrns" class="overlay__eliminar">
                <div class="spinner__container__eliminar">
                    <div class="spinner__eliminar"></div>
                </div>
            </div>

        <div class="col-lg-7">
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
                                <select class="form-select" name="id_transportistas" id="id_transportistas" wire:model="id_transportistas" wire:change="listar_tarifarios_su">
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
                                    <h6>Fecha de despacho</h6>
                                </div>
                                <div class="col-lg-12">
                                    <input type="date" class="form-control" id="programacion_fecha" name="programacion_fecha" wire:model="programacion_fecha" />
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
                                    <h6>Tarifarios Sugeridos</h6>
                                </div>
                                @if($tarifaMontoSeleccionado > 0)
                                    <div class="col-lg-8 col-md-8 col-sm-12 mb-2">
                                        <p class="text-end mb-0">Monto de la tarifa seleccionado:
                                            <span class="font-bold badge bg-label-success curso-pointer" data-bs-toggle="modal" data-bs-target="#modalMontoModificado">
                                                S/ {{ $me->formatoDecimal($tarifaMontoSeleccionado) }}
                                            </span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="vehiculos-scroll-container-horizontal">
                                        @php $conteoGen = 1; @endphp
                                        @foreach($tarifariosSugeridos as $index => $tari)
                                            <div class="position-relative mx-2">
                                                @if($tari->tarifa_estado_aprobacion == 1)
                                                    <input type="radio" name="vehiculo" id="id_check_vehiculo_{{ $tari->id_tarifario}}_{{$conteoGen}}" class="inputCheckRadio" value="{{ $tari->id_tarifario }}" wire:model="selectedTarifario"  wire:click="seleccionarTarifario({{ $tari->id_tarifario }})" />
                                                    <label for="id_check_vehiculo_{{ $tari->id_tarifario}}_{{$conteoGen}}" class="labelCheckRadios">
                                                        <div class="container_check_radios" >
                                                            <div class="cRadioBtn">
                                                                <div class="overlay"></div>
                                                                <div class="drops xsDrop"></div>
                                                                <div class="drops mdDrop"></div>
                                                                <div class="drops lgDrop"></div>
                                                            </div>
                                                        </div>
                                                    </label>
                                                @else
                                                    <label class="labelCheckRadios">
                                                        <div class="container_check_radios" >
                                                            <div class="cRadioBtnNo">
                                                                <i class="fa-solid fa-exclamation"></i>
                                                            </div>
                                                        </div>
                                                    </label>
                                                @endif

                                                    <label class="circulo-vehiculo-container m-2 {{ $tari->tarifa_estado_aprobacion == 0 ? 'no-aprobado' : '' }}" for="id_check_vehiculo_{{ $tari->id_tarifario}}_{{$conteoGen}}">
                                                        <!-- Progreso Circular usando SVG -->
                                                        @php
                                                            $colorCapacidad = $me->obtenerColorPorPorcentaje($tari->capacidad_usada);
                                                        @endphp
                                                        <svg class="progreso-circular" viewBox="0 0 36 36">
                                                            <path class="progreso-circular-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                            <path class="progreso-circular-fg"
                                                                  stroke-dasharray="{{ $tari->capacidad_usada }}, 100"
                                                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                                  style="stroke: {{ $colorCapacidad }};" />
                                                        </svg>
                                                        <div class="circulo-vehiculo">
                                                            <span class="tarifa-monto d-block" style="margin-top: 20px;">
                                                                @php
                                                                    $tarifa = "0";
                                                                    if ($tari->tarifa_monto){
                                                                        $tarifa = $me->formatoDecimal($tari->tarifa_monto);
                                                                    }
                                                                @endphp
                                                                S/ {{ $tarifa }}
                                                            </span>
                                                            <span class="capacidad-peso d-block">
                                                                @php
                                                                    $pesovehiculoMin = "0";
                                                                    if ($tari->tarifa_cap_min){
                                                                        $pesovehiculoMin = $me->formatoDecimal($tari->tarifa_cap_min);
                                                                    }
                                                                @endphp
                                                                @php
                                                                    $pesovehiculoMax = "0";
                                                                    if ($tari->tarifa_cap_max){
                                                                        $pesovehiculoMax = $me->formatoDecimal($tari->tarifa_cap_max);
                                                                    }
                                                                @endphp
                                                                {{$pesovehiculoMin}} {{$tari->id_medida == 9 ? 'cm³' : 'kg' }} - {{ $pesovehiculoMax }} {{$tari->id_medida == 9 ? 'cm³' : 'kg' }}
                                                            </span>

                                                            <div class="boton-container">
                                                                <a href="#" class="btn-ver" data-bs-toggle="modal" data-bs-target="#modalVehiculo" wire:click="modal_detalle_tarifario({{ $tari->id_tarifario }})">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </label>
                                                    @php
                                                        $capacidadPorcentaje = "0";
                                                        if ($tari->capacidad_usada){
                                                            $capacidadPorcentaje = $me->formatoDecimal($tari->capacidad_usada);
                                                        }
                                                        $colorPorcentaje = $me->obtenerColorPorPorcentaje($capacidadPorcentaje);
                                                    @endphp
                                                    <div class="row">
                                                        <div class="col-lg-12 text-center">
                                                            <span class="d-block text-black tamanhoTablaComprobantes"><b>Peso:</b></span>
                                                            <div class="tamanhoTablaComprobantes" style="color: {{ $colorPorcentaje }};">
                                                                <span>{{ $capacidadPorcentaje }}%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                            @php $conteoGen++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                                @error('selectedTarifario')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
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
                                            <select class="form-select" name="id_departamento" id="id_departamento" wire:change="deparTari" wire:model="id_departamento">
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
                                            <select class="form-select" name="id_provincia" id="id_provincia" wire:model="id_provincia" wire:change="proviTari" {{ empty($provincias) ? 'disabled' : '' }}>
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
                                            <select class="form-select" name="id_distrito" id="id_distrito"  wire:change="distriTari" wire:model="id_distrito" {{ empty($distritos) ? 'disabled' : '' }}>
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
            <div class="col-lg-12 ">
                <div class="card">
                    <div class="card-body table-responsive">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Comprobantes Seleccionadas</h6>
                                    <div class="">
                                        <span class="font-bold badge bg-label-success curso-pointer" data-bs-toggle="modal" data-bs-target="#modalRegistrarGastos" >
                                            Registrar Gastos Operativo
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <div class="row">
                                    <div class="col-lg-5 col-md-5 col-sm-12 text-start">
                                        @php
                                            $peso = "0";
                                            if ($pesoTotal){
                                                $peso = $me->formatoDecimal($pesoTotal);
                                            }
                                        @endphp
                                        @php
                                            $volumen = "0";
                                            if ($volumenTotal){
                                                $volumen = $me->formatoDecimal($volumenTotal);
                                            }
                                        @endphp
                                        <small class="textTotalComprobantesSeleccionados me-2">
                                            Kg: <span>{{ $peso }}</span>
                                        </small>
                                        <small class="textTotalComprobantesSeleccionados">
                                            Cm³: <span>{{ $volumen }}</span>
                                        </small>
                                    </div>
                                    <div class="col-lg-12 col-md-12 col-sm-12 text-end">
                                        @if($costoTotal && $importeTotalVenta)
                                            <small class="textTotalComprobantesSeleccionados me-2">
                                                @php
                                                    // Solo el importe de venta se divide entre 1.18 (sin IGV)
                                                    $importeTotalVentaSinIgv = $importeTotalVenta / 1.18;
                                                    // El costo total se mantiene con IGV (no se divide)
                                                    $divisor2 = $importeTotalVentaSinIgv != 0 ? $importeTotalVentaSinIgv : 1;
                                                    $to = ($costoTotal / $divisor2) * 100;
                                                @endphp
                                                F / V: <b class="colorBlackComprobantes">{{$me->formatoDecimal($costoTotal)}}</b> / <b class="colorBlackComprobantes">{{$me->formatoDecimal($importeTotalVenta)}}</b> =  <span>{{ $me->formatoDecimal($to) }} %</span>
                                            </small>
                                        @endif
                                        @if($costoTotal && $pesoTotal)
                                            <small class="textTotalComprobantesSeleccionados">
                                                @php
                                                    $to2 = $costoTotal / $pesoTotal;
                                                @endphp
                                                F / P: <b class="colorBlackComprobantes">{{$me->formatoDecimal($costoTotal)}}</b> / <b class="colorBlackComprobantes">{{$me->formatoDecimal($pesoTotal)}}</b> =  <span>{{ $me->formatoDecimal($to2) }}</span>
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                @if(count($selectedFacturas) > 0)
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th>Fecha emisión guía</th>
                                                <th>Guía</th>
                                                <th>Factura</th>
                                                <th>Nombre del cliente</th>
                                                <th>Peso y Volumen</th>
                                                <th>Dirección</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </x-slot>
                                        <x-slot name="tbody">
                                            @foreach($selectedFacturas as $factura)
                                                <tr style="font-size: 13px">
                                                    <td>
                                                        <span class="d-block">
                                                            {{ $me->obtenerNombreFecha($factura['guia_fecha_emision'], 'DateTime', 'Date') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block">
                                                            {{ $factura['guia_nro_doc'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block">
                                                            {{ $factura['guia_nro_doc_ref'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block">
                                                            {{ $factura['guia_nombre_cliente'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block">
                                                            <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura['peso_total']) }} kg</b>
                                                        </span>
                                                        <span class="d-block">
                                                            <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura['volumen_total']) }} cm³</b>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block">
                                                            {{ $factura['guia_direc_entrega'] }}
                                                        </span>
                                                        <br>
                                                        <span class="d-block" style="color: black;font-weight: bold">
                                                            {{ $factura['guia_departamento'] }} - {{ $factura['guia_provincia'] }} - {{ $factura['guia_destrito'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="#" wire:click.prevent="eliminarFacturaSeleccionada('{{ $factura['id_guia'] }}')" class="btn btn-danger btn-sm text-white">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </x-slot>
                                    </x-table-general>
                                @else
                                    <p>No hay comprobantes seleccionadas.</p>
                                @endif
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <div class="mb-3">
                                    <h6 class="mb-0">Servicio Transporte seleccionados</h6>
                                </div>
                                @if(count($selectedServTrns) > 0)
                                    <x-table-general id="ederTable">
                                        <x-slot name="thead">
                                            <tr>
                                                <th>Codigo</th>
                                                <th>Motivo</th>
                                                <th>Remitente</th>
                                                <th>Destinatario</th>
                                                <th>Peso y Volumen</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </x-slot>
                                        <x-slot name="tbody">
                                            @foreach($selectedServTrns as $factura)
                                                <tr style="font-size: 13px">
                                                    <td>
                                                        <span class="d-block">
                                                            {{ $factura['serv_transpt_codigo'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block">
                                                            {{ $factura['serv_transpt_motivo'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block">
                                                            {{ $factura['serv_transpt_remitente_ruc'] }} <br>
                                                            {{ $factura['serv_transpt_remitente_razon_social'] }} <br>
                                                            {{ $factura['serv_transpt_remitente_direccion'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block">
                                                            {{ $factura['serv_transpt_destinatario_ruc'] }} <br>
                                                            {{ $factura['serv_transpt_destinatario_razon_social'] }} <br>
                                                            {{ $factura['serv_transpt_destinatario_direccion'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-block">
                                                            <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura['peso_total_st']) }} kg</b>
                                                        </span>
                                                        <span class="d-block">
                                                            <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura['volumen_total_st']) }} cm³</b>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="#" wire:click.prevent="eliminarSerTrnSeleccionada('{{ $factura['id_serv_transpt'] }}')" class="btn btn-danger btn-sm text-white">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </x-slot>
                                    </x-table-general>
                                @else
                                    <p>No hay Servicios transportes seleccionados.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div wire:loading wire:target="eliminarFacturaSeleccionada" class="overlay__eliminar">
                    <div class="spinner__container__eliminar">
                        <div class="spinner__eliminar"></div>
                    </div>
                </div>
                {{-- SERVICIO TRANSPORTE--}}
                <div wire:loading wire:target="eliminarSerTrnSeleccionada" class="overlay__eliminar">
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
        .card {
            margin-bottom: 1rem;
            border: none;
        }
    </style>
</div>
