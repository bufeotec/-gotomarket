<div>
    @php
        $general = new \App\Models\General();
    @endphp
{{--    MODAL DETALLES DEL DESPACHO--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleDespacho</x-slot>
        <x-slot name="titleModal">Detalles del Despacho</x-slot>
        <x-slot name="modalContent">
            @if($listar_detalle_despacho)
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h6>Información General de OS</h6>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 d-flex align-items-center">
                                    @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 || $listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                        <strong class="colorgotomarket mb-2">N° OS: </strong>
                                        <h6 class="ms-2">{{ $listar_detalle_despacho->despacho_numero_correlativo }}</h6>
                                    @endif
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                                    <a class="btn btn-sm bg-success text-white" target="_blank" href="{{route('Despachotransporte.gestionar_os_detalle',['id_despacho'=>base64_encode($listar_detalle_despacho->id_despacho)])}}">
                                        Gestionar OS
                                    </a>
                                </div>
                                <hr>
                                <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                    <strong class="colorgotomarket mb-2">Usuario de Registro</strong>
                                    <p>{{ $listar_detalle_despacho->name }}</p>
                                </div>
                                @if($listar_detalle_despacho->id_vehiculo)
                                    @php
                                        $vehiculo = \Illuminate\Support\Facades\DB::table('vehiculos')->where('id_vehiculo','=',$listar_detalle_despacho->id_vehiculo)->first();
                                    @endphp
                                    <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                        <strong class="colorgotomarket mb-2">Placa del Vehículo:</strong>
                                        <p>{{ $vehiculo->vehiculo_placa }}</p>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                                        <strong class="colorgotomarket mb-2">Capacidad del Vehículo:</strong>
                                        <p>{{ $general->formatoDecimal($vehiculo->vehiculo_capacidad_peso) }} Kg</p>
                                    </div>
                                @endif

                                @if($listar_detalle_despacho->id_tipo_servicios == 2)
                                    @php
                                        $departamento = \Illuminate\Support\Facades\DB::table('departamentos')
                                        ->where('id_departamento','=',$listar_detalle_despacho->id_departamento)->first();
                                        $provincia = \Illuminate\Support\Facades\DB::table('provincias')
                                        ->where('id_provincia','=',$listar_detalle_despacho->id_provincia)->first();
                                        $distrito = \Illuminate\Support\Facades\DB::table('distritos')
                                        ->where('id_distrito','=',$listar_detalle_despacho->id_distrito)->first();
                                    @endphp
                                    <div class="col-lg-5 col-md-3 col-sm-4 mb-3">
                                        <strong class="colorgotomarket mb-2">Ubigeo Seleccionado en el Despacho:</strong>
                                        <p>{{ $departamento ? $departamento->departamento_nombre : '' }} - {{ $provincia ? $provincia->provincia_nombre : '' }} - {{ $distrito ? $distrito->distrito_nombre : 'TODOS LOS DISTRITOS' }}</p>
                                    </div>
                                @endif

                                @if($listar_detalle_despacho->id_tarifario)
                                    <div class="col-lg-3 col-md-3 col-sm-4 mb-3">
                                        <strong class="colorgotomarket mb-2">Capacidad de la Tarifa:</strong>
                                        <p>Min: {{$general->formatoDecimal($listar_detalle_despacho->despacho_cap_min)}} Kg - Max: {{ $general->formatoDecimal($listar_detalle_despacho->despacho_cap_max) }} Kg</p>
                                    </div>
                                @endif
                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Monto de la Tarifa:</strong>
                                    <p>S/ {{$general->formatoDecimal($listar_detalle_despacho->despacho_flete)}}</p>
                                </div>

                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Peso del Despacho:</strong>
                                    <p>{{$general->formatoDecimal($listar_detalle_despacho->despacho_peso)}} Kg</p>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Otros Gastos:</strong>
                                    <p>S/ {{$general->formatoDecimal($listar_detalle_despacho->despacho_gasto_otros)}}</p>
                                </div>
                                @if($listar_detalle_despacho->despacho_gasto_otros > 0)
                                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                                        <strong class="colorgotomarket mb-2">Descripción del Gasto:</strong>
                                        <p>{{ $listar_detalle_despacho->despacho_descripcion_otros }}</p>
                                    </div>
                                @endif
                                @if($listar_detalle_despacho->id_tipo_servicios == 1)
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                        <strong class="colorgotomarket mb-2">Mano de Obra:</strong>
                                        <p>S/ {{$general->formatoDecimal($listar_detalle_despacho->despacho_ayudante)}}</p>
                                    </div>
                                @endif
                                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Total de Despacho:</strong>
                                    <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->despacho_costo_total) }}</p>
                                </div>
                                @if($listar_detalle_despacho->despacho_estado_modificado == 1)
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                        <strong class="colorgotomarket mb-2">Monto Modificado:</strong>
                                        <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->despacho_monto_modificado) }}</p>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                        <strong class="colorgotomarket mb-2">Descripción:</strong>
                                        <p>{{ $listar_detalle_despacho->despacho_descripcion_modificado }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12 mb-4">
                            <form wire:submit.prevent="cambiarEstadoComprobante">
                                <div class="row mb-3">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <h6>Información de la guía</h6>
                                        <hr>
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        @if (session()->has('error_fecha'))
                                            <div class="alert alert-danger alert-dismissible show fade mt-2">
                                                {{ session('error_fecha') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <x-table-general>
                                            <x-slot name="thead">
                                                <tr>
                                                    <th>N°</th>
                                                    <th>Guía</th>
                                                    <th>Nombre cliente</th>
                                                    <th>Fecha Emisión</th>
                                                    <th>Factura</th>
                                                    <th>Venta Despachada (sin IGV)</th>
                                                    <th>UBIGEO</th>
                                                    @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 || $listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                                        <th>Estado del comprobante</th>
                                                    @endif
                                                    @if($listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                                        <th>Cambio de estado</th>
                                                    @endif
                                                    @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 || $listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                                        <th>Fecha de Entrega</th>
                                                    @endif
                                                </tr>
                                            </x-slot>
                                            <x-slot name="tbody">
                                                @if(count($listar_detalle_despacho->comprobantes) > 0)
                                                    @php $conteo = 1; @endphp
                                                    @foreach($listar_detalle_despacho->comprobantes as $indexComprobantes => $ta)
                                                        <tr>
                                                            <td>{{$conteo}}</td>
                                                            <td>{{ $ta->guia_nro_doc }}</td>
                                                            <td>{{ $ta->guia_nombre_cliente }}</td>
                                                            <td>{{ $ta->guia_fecha_emision ? $general->obtenerNombreFecha($ta->guia_fecha_emision, 'DateTime', 'Date') : '-' }}</td>
                                                            <td>{{ $ta->guia_nro_doc_ref }}</td>
                                                            <td>S/ {{ $general->formatoDecimal($ta->guia_importe_total_sin_igv ?? 0) }}</td>
                                                            <td>{{ $ta->guia_departamento }} - {{ $ta->guia_provincia }} - {{ $ta->guia_destrito }}</td>
                                                            @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 || $listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                                                <td>
                                                                    @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 && !in_array($ta->guia_estado_aprobacion, [8, 11, 12]) )
                                                                        <select
                                                                            name="estadoComprobante[{{ $indexComprobantes }}]"
                                                                            class="form-control form-select"
                                                                            wire:model="estadoComprobante.{{ $listar_detalle_despacho->id_despacho }}_{{ $ta->id_despacho_venta }}"
                                                                        >
                                                                            <option value="8">Entregado</option>
                                                                            @if(!$listar_detalle_despacho->es_mixto_provincial)
                                                                                <option value="11">No entregado</option>
                                                                            @endif
                                                                        </select>
                                                                    @else
                                                                        @php
                                                                            $estadoMostrar = $ta->guia_estado_aprobacion;

                                                                            if (isset($ta->despacho_detalle_estado_entrega)) {
                                                                                if ($ta->despacho_detalle_estado_entrega == 0) {
                                                                                    $estadoMostrar = $ta->guia_estado_aprobacion;
                                                                                } elseif (in_array($ta->despacho_detalle_estado_entrega, [8, 11])) {
                                                                                    $estadoMostrar = $ta->despacho_detalle_estado_entrega;
                                                                                }
                                                                            }
                                                                        @endphp

                                                                        <span class="font-bold badge {{$estadoMostrar == 8 ? 'bg-label-success' : 'bg-label-danger'}}">
                                                                            {{$estadoMostrar == 8 ? 'ENTREGADO' : 'NO ENTREGADO'}}
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            @endif
                                                            @if($listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                                                <td>
                                                                    <select
                                                                        name="estadoComprobante[{{ $indexComprobantes }}]"
                                                                        class="form-control form-select"
                                                                        wire:model="estadoComprobante.{{ $listar_detalle_despacho->id_despacho }}_{{ $ta->id_despacho_venta }}"
                                                                    >
                                                                        <option value="8">Entregado</option>
                                                                        <option value="11">No entregado</option>
                                                                    </select>
                                                                </td>
                                                            @endif
                                                            @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 || $listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                                                <td>
                                                                    @if(empty($ta->despacho_detalle_fecha_entrega))
                                                                        <input type="date"
                                                                               class="form-control"
                                                                               id="fecha_entrega_guia_{{ $ta->id_despacho_venta }}"
                                                                               wire:model.live="fecha_entrega_guia.{{ $listar_detalle_despacho->id_despacho }}_{{ $ta->id_despacho_venta }}"
                                                                               wire:change="validar_fecha({{ $listar_detalle_despacho->id_despacho }}, {{ $ta->id_despacho_venta }})" />
                                                                    @else
                                                                        <strong>{{ $ta->despacho_detalle_fecha_entrega ? $general->obtenerNombreFecha($ta->despacho_detalle_fecha_entrega, 'Date', 'Date') : ' ' }}</strong>
                                                                    @endif
                                                                </td>
                                                            @endif
                                                        </tr>
                                                        @php $conteo++; @endphp
                                                    @endforeach
                                                @else
                                                    <tr class="odd">
                                                        <td valign="top" colspan="7" class="dataTables_empty text-center">
                                                            No se han encontrado resultados.
                                                        </td>
                                                    </tr>
                                                @endif
                                            </x-slot>
                                        </x-table-general>
                                    </div>
                                </div>

                                <!-- Información del servicio transporte -->
                                @if(count($listar_detalle_despacho->servicios_transportes) > 0)
                                    <div class="row mb-3">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <h6>Información del servicio transporte</h6>
                                            <hr>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <x-table-general>
                                                <x-slot name="thead">
                                                    <tr>
                                                        <th>N°</th>
                                                        <th>Codigo</th>
                                                        <th>Motivo</th>
                                                        <th>Detalle del Motivo</th>
                                                        <th>Remitente</th>
                                                        <th>Destinatario</th>
                                                        <th>Ubigeo</th>
                                                        <th>Documento</th>
                                                        {{--                                                    <th>Peso</th>--}}
                                                        {{--                                                    <th>Volumen</th>--}}
                                                    </tr>
                                                </x-slot>

                                                <x-slot name="tbody">
                                                    @php $a = 1; @endphp
                                                    @foreach($listar_detalle_despacho->servicios_transportes as $st)
                                                        <tr>
                                                            <td>{{$a}}</td>
                                                            <td>{{ $st->serv_transpt_codigo }}</td>
                                                            <td>{{ $st->serv_transpt_motivo }}</td>
                                                            <td>{{ $st->serv_transpt_detalle_motivo }}</td>
                                                            <td>
                                                                {{ $st->serv_transpt_remitente_ruc }} <br>
                                                                {{ $st->serv_transpt_remitente_razon_social }} <br>
                                                                {{ $st->serv_transpt_remitente_direccion }}
                                                            </td>
                                                            <td>
                                                                {{ $st->serv_transpt_destinatario_ruc }} <br>
                                                                {{ $st->serv_transpt_destinatario_razon_social }} <br>
                                                                {{ $st->serv_transpt_destinatario_direccion }}
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $departamento = \Illuminate\Support\Facades\DB::table('departamentos')
                                                                    ->where('id_departamento','=',$st->id_departamento)->first();
                                                                    $provincia = \Illuminate\Support\Facades\DB::table('provincias')
                                                                    ->where('id_provincia','=',$st->id_provincia)->first();
                                                                    $distrito = \Illuminate\Support\Facades\DB::table('distritos')
                                                                    ->where('id_distrito','=',$st->id_distrito)->first();
                                                                @endphp
                                                                {{ $departamento ? $departamento->departamento_nombre : '' }} - {{ $provincia ? $provincia->provincia_nombre : '' }} - {{ $distrito ? $distrito->distrito_nombre : 'TODOS LOS DISTRITOS' }}
                                                            </td>
                                                            <td>
                                                                <a class="btn text-success" href="{{ asset($st->serv_transpt_documento) }}" target="_blank">
                                                                    <i class="fa-solid fa-file-lines"></i>
                                                                </a>
                                                            </td>
                                                            {{--                                                        <td>{{ $general->formatoDecimal($st->serv_transpt_peso) }} kg</td>--}}
                                                            {{--                                                        <td>{{ $general->formatoDecimal($st->serv_transpt_volumen) }} cm³</td>--}}
                                                        </tr>
                                                        @php $a++; @endphp
                                                    @endforeach
                                                </x-slot>
                                            </x-table-general>
                                        </div>
                                    </div>
                                @endif

                                <!-- BOTON CAMBIO DE COMPROBANTE -->
                                @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 || $listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 mb-2 text-end">
                                            <button class="btn  text-white bg-primary" type="submit">Guardar Estados de Comprobantes</button>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </x-slot>
    </x-modal-general>
{{--    MODAL FIN DETALLES DEL DESPACHO--}}

{{--    MODAL APROBAR / RECHAZAR PROGRAMACIÓN--}}
    <x-modal-delete wire:ignore.self>
        <x-slot name="id_modal">modalAprobarProgramacion</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiarEstadoProgramacionFormulario">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if($actionType == 1)
                            <h2 class="deleteTitle">¿Está seguro de aprobar las {{ count($selectedProgramaciones) }} programaciones seleccionadas?</h2>
                        @else
                            <h2 class="deleteTitle">¿Está seguro de rechazar las {{ count($selectedProgramaciones) }} programaciones seleccionadas?</h2>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('selectedProgramaciones') <span class="message-error">{{ $message }}</span> @enderror
                        @if (session()->has('error_delete'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_delete') }}
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
{{--    MODAL FIN APROBAR / RECHAZAR PROGRAMACIÓN--}}

{{--    MODAL APROBAR / RECHAZAR SERVICIO TRANSPORTE--}}

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
                                    <td>{{ $detalle->guia_det_fecha_emision ? $general->obtenerNombreFecha($detalle->guia_det_fecha_emision, 'DateTime', 'Date') : '-' }}</td>
                                    <td>{{ $detalle->guia_det_estado ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_tipo_documento ?? '-' }}</td>
                                    <td>{{ $detalle->guia_det_nro_documento ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_nro_linea ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_cod_producto ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_descripcion_producto ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_lote ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_unidad ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_cantidad ?? '-'}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_precio_unit_final_inc_igv ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_precio_unit_antes_descuente_inc_igv ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_descuento_total_sin_igv ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_igv_total ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_importe_total_inc_igv ?? 0) }}</td>
                                    <td>{{ $detalle->guia_det_moneda ?? '-'}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_tipo_cambio ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_peso_gramo ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_volumen ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_peso_total_gramo ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_volumen_total ?? 0)}}</td>
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

{{--    MODAL EN CAMINO--}}
    <x-modal-delete wire:ignore.self>
        <x-slot name="id_modal">modalEnCamino</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiarEstadoEnCamino">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">¿Confirma cambiar a estado "En Camino"?</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">Confirmar</button>
                        <button type="button" data-bs-dismiss="modal" class="btn btn-danger btnDelete">Cancelar</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
{{--    FIN MODAL EN CAMINO--}}


    <div class="row align-items-center mt-2">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label class="form-label">Estado de Programación</label>
            <select class="form-select" wire:model="estado_programacion">
                <option value="">Seleccionar...</option>
                <option value="0">Emitido</option>
                <option value="1">Aprobado</option>
                <option value="2">Tránsito</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_desde" class="form-label">Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" wire:model.live="desde" class="form-control">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_hasta" class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model.live="hasta" class="form-control">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mt-3">
            <a class="btn btnsm btn-primary text-white" wire:click="buscar_programacion"> <i class="fa-solid fa-magnifying-glass"></i> Buscar</a>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_programacion"></div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12 mt-2">
            <p>Actualmente, hay <b class="colorgotomarket">{{$conteoProgramacionesPend}}</b> programaciones pendientes.</p>
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

    @if(count($resultados) > 0)
        <div class="row mt-3">
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <h6 class="m-0">FD : Fecha de Despacho</h6>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <h6 class="m-0">UR : Usuario de Registro</h6>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <h6 class="m-0">FE : Fecha de Emisión</h6>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <h6 class="m-0">FE : Fecha de Aprobación</h6>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <h6 class="m-0">N° C : Número Correlativo</h6>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <h6 class="m-0">OS : Orden de Servicio</h6>
            </div>
        </div>
    @endif

    @if(count($resultados) > 0)
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3 mt-2 text-end">
                @php
                    $user = \Illuminate\Support\Facades\Auth::user(); // Obtiene el usuario autenticado
                    // Obtén el primer rol del usuario y su ID
                    $roleId = $user->roles->first()->id ?? null;
                @endphp
                {{--                                @if($roleId == 1 || $roleId == 2)--}}
                <a class="btn btn-sm text-white bg-warning" wire:click="confirmar_encamino" data-bs-toggle="modal" data-bs-target="#modalEnCamino">
                    Iniciar Tránsito
                </a>
                <button class="btn btn-sm text-white bg-success" wire:click="prepareAction(1)" data-bs-toggle="modal" data-bs-target="#modalAprobarProgramacion">
                    <i class="fa-solid fa-check"></i> APROBAR
                </button>
                <button class="btn btn-sm text-white bg-danger" wire:click="prepareAction(4)" data-bs-toggle="modal" data-bs-target="#modalAprobarProgramacion">
                    <i class="fa fa-x"></i> RECHAZAR
                </button>
                {{--                                @endif--}}
            </div>
        </div>
    @endif

    @if(count($resultados) > 0)
        <div class="row">
            <div class="col-lg-12 mt-2">
                <h5>Programación de despacho: </h5>
            </div>
        </div>
    @endif

    <div class="accordion mt-3" id="accordionExample" >
        @php $conteoGeneral = 1; @endphp
        @foreach($resultados as $index => $r)
            @php
                $usuarios = "-";
                $usuarios2 = "-";
                if ($r->id_users){
                    $e = \Illuminate\Support\Facades\DB::table('users')->where('id_users','=',$r->id_users)->first();
                    if ($e){
                        $usuarios = $e->name.' '.$e->last_name;
                    }
                }
                if ($r->id_users_programacion){
                    $e2 = \Illuminate\Support\Facades\DB::table('users')->where('id_users','=',$r->id_users_programacion)->first();
                    if ($e2){
                        $usuarios2 = $e->name.' '.$e->last_name;
                    }
                }
                $fe = $general->obtenerNombreFecha($r->programacion_fecha,'Date','Date');
                $fc = $general->obtenerNombreFecha($r->created_at,'DateTime','DateTime');
                $fa = $general->obtenerNombreFecha($r->programacion_fecha_aprobacion,'DateTime','Date');

                $estado_despacho = "Desconocido";
                if(count($r->despacho) > 0) {
                    $primerDespacho = $r->despacho[0];
                    switch($primerDespacho->despacho_estado_aprobacion) {
                        case 0: $estado_despacho = "Pendiente"; break;
                        case 1: $estado_despacho = "Aprobado"; break;
                        case 2: $estado_despacho = "Transito"; break;
                        case 3: $estado_despacho = "Culminado"; break;
                        case 4: $estado_despacho = "Rechazado"; break;
                    }
                }

                $mostrarBotonEditar = false;
                if(count($r->despacho) > 0) {
                    $primerDespacho = $r->despacho[0];
                    $mostrarBotonEditar = ($primerDespacho->despacho_estado_aprobacion == 0);
                }
            @endphp
{{--            {{route('Programacioncamion.detalle_programacion',['data'=>base64_encode($r->id_programacion) ])}}--}}
            <div class="accordion-item" >
                <h2 class="accordion-header d-flex justify-content-between align-items-center">
                    <!-- PRIMER CHECK BOX DEL BOTON DEL ACORDION -->
                    @if(count($r->despacho) > 0 && in_array($r->despacho[0]->despacho_estado_aprobacion, [0, 1]))
                        <div style="display: flex; padding: 12px" class="checkbox-container {{$index == 0 ? 'active' : ''}}" data-accordion-target="collapseOne_{{$index}}">
                            <input
                                style="font-size: 20px"
                                type="checkbox"
                                class="form-check-input programacion-checkbox"
                                wire:model="selectedProgramaciones"
                                value="{{$r->id_programacion}}"
                            />
                        </div>
                    @endif
                    <button class="accordion-button {{$index == 0 ? '' : 'collapsed'}} flex-grow-1" wire:ignore.self type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{$index}}" aria-expanded="true" aria-controls="collapseOne_{{$index}}">
                        #{{$conteoGeneral}} | FD : {{$fe}} | UR : {{$usuarios}}
                        @if(!empty($fa)) | FA : {{$fa}} @endif
                        @if(!empty($r->programacion_numero_correlativo)) | N° C : {{$r->programacion_numero_correlativo}} @endif
                        | ED : {{$estado_despacho}}
                    </button>
                </h2>
                <div id="collapseOne_{{$index}}" class="accordion-collapse collapse {{$index == 0 ? 'show' : ''}}" data-bs-parent="#accordionExample" wire:ignore.self >
                    <div class="accordion-body" >
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 text-end mb-3">
                                @if($mostrarBotonEditar && Gate::allows('editar_aprobar_programacion'))
                                    <a class="btn btn-sm text-white bg-info me-2" href="{{route('Despachotransporte.editar_programaciones',['data'=>base64_encode($r->id_programacion)])}}">
                                        <i class="fa-solid fa-pencil"></i> EDITAR
                                    </a>
                                @endif
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr style="background: #f5f5f9">
                                        <th>
                                            @if(count($r->despacho) > 0 && in_array($r->despacho[0]->despacho_estado_aprobacion, [0, 1]))
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input programacion-checkbox"
                                                    wire:model="selectedProgramaciones"
                                                    value="{{$r->id_programacion}}"
                                                />
                                            @endif
                                        </th>
                                        <th>N°</th>
                                        <th>Tipo Servicio</th>
                                        <th>OS</th>
                                        <th>Proveedor</th>
                                        <th>Venta Despachada(sin IGV)</th>
                                        <th>Cambio Tarifa</th>
                                        <th>Tarifa</th>
                                        <th>Flete Total</th>
                                        <th>Flete / Venta</th>
                                        <th>Peso(kg)</th>
                                        <th>Llenado en Peso</th>
                                        <th>Flete / Peso</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($r->despacho) > 0)
                                        @php $conteoGeneral2 = 1; @endphp
                                        @foreach($r->despacho as $des)
                                            <tr>
                                                <td></td>
                                                <td>{{$conteoGeneral2}}</td>
                                                <td>{{$des->tipo_servicio_concepto}}</td>
                                                <td>{{$des->despacho_numero_correlativo ?? '-'}}</td>
                                                <td>{{$des->transportista_nom_comercial}}</td>
                                                <td>S/ {{$general->formatoDecimal($des->totalVentaDespacho)}}</td>
                                                @php
                                                    $styleColor = "text-danger";
                                                    if ($des->despacho_estado_modificado == 1){
                                                        $styleColor = "text-success";
                                                    }
                                                @endphp
                                                <td><b class="{{$styleColor}}">{{$des->despacho_estado_modificado == 1 ? 'SI' : 'NO'}}</b></td>
                                                <td>S/ {{$des->despacho_flete}}</td>
                                                <td>S/ {{$des->despacho_costo_total}}</td>
{{--                                                <td>--}}
{{--                                                    <span class="{{$des->despacho_estado_modificado == 1 ? 'text-danger' : ''}}">S/ {{$des->despacho_flete}}</span>--}}
{{--                                                    <b class="{{$styleColor}}">--}}
{{--                                                        {{$des->despacho_estado_modificado == 1 ? '=> S/ '.$des->despacho_monto_modificado : ''}}--}}
{{--                                                    </b>--}}
{{--                                                </td>--}}
                                                @php
                                                    $ra = 0;
                                                    if ($des->despacho_costo_total && $des->totalVentaDespacho > 0) {
                                                        $to = ($des->despacho_costo_total / $des->totalVentaDespacho) * 100;
                                                        $ra = $general->formatoDecimal($to);
                                                    }
                                                @endphp
                                                <td>{{$ra}} %</td>
                                                <td>{{$general->formatoDecimal($des->despacho_peso)}} kg</td>
                                                @php
                                                    $indi = "";
                                                    if ($des->id_vehiculo){
                                                        $vehi = \Illuminate\Support\Facades\DB::table('vehiculos')->where('id_vehiculo','=',$des->id_vehiculo)->first();
                                                        $indi = ($des->despacho_peso / $vehi->vehiculo_capacidad_peso) * 100;
                                                        $indi = $general->formatoDecimal($indi);
                                                    }else{
                                                        $indi = "-";
                                                    }
                                                @endphp
                                                <td style="color: {{$general->obtenerColorPorPorcentaje($indi)}}">{{ $indi > 0 ? $indi.'%' : '-' }}</td>
                                                @php
                                                    $ra2 = 0;
                                                    // Verificar que despacho_peso no sea 0 antes de dividir
                                                    if ($des->despacho_costo_total && $des->despacho_peso > 0) {
                                                        $to = $des->despacho_costo_total / $des->despacho_peso;
                                                        $ra2 = $general->formatoDecimal($to);
                                                    } elseif ($des->despacho_costo_total) {
                                                        // Opcional: Manejar el caso cuando hay costo pero peso es 0
                                                        $ra2 = 'N/A'; // O cualquier valor que quieras mostrar en este caso
                                                    }
                                                @endphp
                                                <td>{{ $ra2 }}</td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm text-white mb-2" wire:click="listar_informacion_despacho({{$des->id_despacho}})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">
                                                        <i class="fa-solid fa-eye"></i> Detalle OS
                                                    </button>

{{--                                                    <button class="btn btn-warning btn-sm text-white mb-2" wire:click="listar_detalle_guia({{$des->id_despacho}})" data-bs-toggle="modal" data-bs-target="#modalDetalleGuia">--}}
{{--                                                        <i class="fa-solid fa-eye"></i> Facturas--}}
{{--                                                    </button>--}}
                                                </td>
                                            </tr>
                                            @php $conteoGeneral2++; @endphp
                                        @endforeach
                                    @else
                                        <tr class="odd">
                                            <td valign="top" colspan="11" class="dataTables_empty text-center">
                                                No se han encontrado resultados.
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @php $conteoGeneral++; @endphp
        @endforeach
    </div>

{{--    {{ $resultados->links(data: ['scrollTo' => false]) }}--}}

    <style>
        .select2-container--default .select2-selection--single {
            display: block;
            width: 100%;
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #6e707e;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d3e2;
            border-radius: .35rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        .checkbox-container.active {
            background: #e7f1ff !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const accordionElement = document.getElementById('accordionExample');

            // Escuchar eventos de mostrar/ocultar del acordeón
            accordionElement.addEventListener('show.bs.collapse', function (event) {
                // Remover la clase active de todos los checkboxes
                document.querySelectorAll('.checkbox-container').forEach(container => {
                    container.classList.remove('active');
                });

                // Agregar la clase active al checkbox del acordeón que se está abriendo
                const targetId = event.target.id;
                const checkboxContainer = document.querySelector(`[data-accordion-target="${targetId}"]`);
                if (checkboxContainer) {
                    checkboxContainer.classList.add('active');
                }
            });

            accordionElement.addEventListener('hide.bs.collapse', function (event) {
                // Remover la clase active del checkbox del acordeón que se está cerrando
                const targetId = event.target.id;
                const checkboxContainer = document.querySelector(`[data-accordion-target="${targetId}"]`);
                if (checkboxContainer) {
                    checkboxContainer.classList.remove('active');
                }
            });
        });
    </script>
</div>
@script
<script>

    $wire.on('hideModalDelete', () => {
        $('#modalAprobarProgramacion').modal('hide');
    });

    $wire.on('hideModalEnCamino', () => {
        $('#modalEnCamino').modal('hide');
    });
</script>
@endscript


