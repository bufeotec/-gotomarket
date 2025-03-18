<div>
    @php
        $general = new \App\Models\General();
    @endphp
{{--    MODAL DETALLES DEL DESPACHO--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="id_modal">modalDetalleDespacho</x-slot>
        <x-slot name="titleModal">Detalles del Despacho</x-slot>
        <x-slot name="modalContent">
            @if($listar_detalle_despacho)
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <h6>Información Adicional del Despacho</h6>
                                    <hr>
                                </div>
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
{{--                                    @php--}}
{{--                                        $tarifa = \Illuminate\Support\Facades\DB::table('tarifarios as t')--}}
{{--                                        ->where('t.id_tarifario','=',$listar_detalle_despacho->id_tarifario)->first();--}}
{{--                                        $medida = \Illuminate\Support\Facades\DB::table('medida')->where('id_medida','=',$tarifa->id_medida)->first();--}}
{{--                                        $meMed = "";--}}
{{--                                        if ($medida){--}}
{{--                                            $meMed = $medida->id_medida == 23 ? ' Kg' : ' cm³';--}}
{{--                                        }else{--}}
{{--                                            $meMed = ' Kg';--}}
{{--                                        }--}}
{{--                                    @endphp--}}

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

{{--                        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                    <h6>Información de Comprobantes</h6>--}}
{{--                                    <hr>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                    <x-table-general>--}}
{{--                                        <x-slot name="thead">--}}
{{--                                            <tr>--}}
{{--                                                <th>N°</th>--}}
{{--                                                <th>Número Documento</th>--}}
{{--                                                <th>Fecha Emision</th>--}}
{{--                                                <th>Cliente</th>--}}
{{--                                                <th>Guía de Remisión</th>--}}
{{--                                                <th>Importe Venta</th>--}}
{{--                                                <th>Peso Kilos</th>--}}
{{--                                            </tr>--}}
{{--                                        </x-slot>--}}

{{--                                        <x-slot name="tbody">--}}
{{--                                            @if(count($listar_detalle_despacho->comprobantes) > 0)--}}
{{--                                                @php $conteo = 1; @endphp--}}
{{--                                                @foreach($listar_detalle_despacho->comprobantes as $ta)--}}
{{--                                                    <tr>--}}
{{--                                                        <td>{{$conteo}}</td>--}}
{{--                                                        <td>{{$ta->despacho_venta_factura}}</td>--}}
{{--                                                        <td>--}}
{{--                                                            {{$general->obtenerNombreFecha($ta->despacho_venta_grefecemision,'DateTime','Date')}}--}}
{{--                                                        </td>--}}
{{--                                                        <td>{{$ta->despacho_venta_cnomcli}}</td>--}}
{{--                                                        <td>{{$ta->despacho_venta_guia}}</td>--}}
{{--                                                        <td>S/ {{$general->formatoDecimal($ta->despacho_venta_cfimporte)}}</td>--}}
{{--                                                        <td>{{$general->formatoDecimal($ta->despacho_venta_total_kg)}} Kg</td>--}}
{{--                                                    </tr>--}}
{{--                                                    @php $conteo++; @endphp--}}
{{--                                                @endforeach--}}
{{--                                            @else--}}
{{--                                                <tr class="odd">--}}
{{--                                                    <td valign="top" colspan="7" class="dataTables_empty text-center">--}}
{{--                                                        No se han encontrado resultados.--}}
{{--                                                    </td>--}}
{{--                                                </tr>--}}
{{--                                            @endif--}}
{{--                                        </x-slot>--}}
{{--                                    </x-table-general>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </div>
                </div>
            @endif
        </x-slot>
    </x-modal-general>
{{--    MODAL FIN DETALLES DEL DESPACHO--}}

{{--    MODAL APROBAR / RECHAZAR PROGRAMACIÓN--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalAprobarProgramacion</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiarEstadoProgramacionFormulario">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if($estadoPro == 1)
                            <h2 class="deleteTitle">¿Está seguro de aprobar esta programación?</h2>
                        @else
                            <h2 class="deleteTitle">¿Está seguro de rechazar esta programación?</h2>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_progr') <span class="message-error">{{ $message }}</span> @enderror

                        @error('estadoPro') <span class="message-error">{{ $message }}</span> @enderror

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

{{--    MODAL APROBAR / RECHAZAR SERVICIO TRANSPORTE--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalAprobarServicioTransp</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiarEstadoServicioTranspFormulario">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if($serv_transpt_estado_aprobacion == 1)
                            <h2 class="deleteTitle">¿Está seguro de aprobar este servicio transporte?</h2>
                        @else
                            <h2 class="deleteTitle">¿Está seguro de rechazar esta servicio transporte?</h2>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_serv_transpt') <span class="message-error">{{ $message }}</span> @enderror

                        @error('serv_transpt_estado_aprobacion') <span class="message-error">{{ $message }}</span> @enderror

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
{{--    MODAL FIN APROBAR / RECHAZAR SERVICIO TRANSPORTE--}}

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
                                    <strong style="color: #8c1017">Almacen de Origen:</strong>
                                    <p>{{ $guiainfo->guia_almacen_origen }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo Documento:</strong>
                                    <p>{{ $guiainfo->guia_tipo_doc }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Fecha Emisión:</strong>
                                    <p>{{ $guiainfo->guia_fecha_emision ? $general->obtenerNombreFecha($guiainfo->guia_fecha_emision, 'DateTime', 'DateTime') : '-' }}</p>
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

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Importe Total:</strong>
                                    <p>{{ $general->formatoDecimal($guiainfo->guia_importe_total ?? 0)}}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Cambio:</strong>
                                    <p>{{ $general->formatoDecimal($guiainfo->guia_tipo_cambio ?? 0)}}</p>
                                </div>

                                <div class="col-lg-3">
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
                                    <td>{{ $detalle->guia_det_fecha_emision ? $general->obtenerNombreFecha($detalle->guia_det_fecha_emision, 'DateTime', 'DateTime') : '-' }}</td>
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


    <div class="row align-items-center mt-2">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_desde" class="form-label">Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" wire:model.live="desde" class="form-control">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_hasta" class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model.live="hasta" class="form-control">
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <p>Actualmente, hay <b class="colorgotomarket">{{$conteoProgramacionesPend}}</b> programaciones pendientes y <b class="colorgotomarket">{{$conteoServicioTransporte}}</b> servicios transportes pendientes.</p>
        </div>
    </div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(count($resultado) > 0)
        <div class="row mt-3">
            <div class="col-lg col-md-3 col-sm-3 mb-1">
                <h6 class="m-0">FD : Fecha de Despacho</h6>
            </div>
            <div class="col-lg col-md-3 col-sm-3 mb-1">
                <h6 class="m-0">UR : Usuario de Registro</h6>
            </div>
            <div class="col-lg col-md-3 col-sm-3 mb-1">
                <h6 class="m-0">FC : Fecha de Creación</h6>
            </div>
            <div class="col-lg col-md-3 col-sm-3 mb-1">
                <h6 class="m-0">E : Estado</h6>
            </div>
            <div class="col-lg-12 mt-4">
                <h5>Programación de despacho: </h5>
            </div>
        </div>
    @endif

    <div class="accordion mt-3" id="accordionExample" >
        @php $conteoGeneral = 1; @endphp
        @foreach($resultado as $index => $r)
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
            @endphp
{{--            {{route('Programacioncamion.detalle_programacion',['data'=>base64_encode($r->id_programacion) ])}}--}}
            <div class="accordion-item" >
                <h2 class="accordion-header">
                    <button class="accordion-button {{$index == 0 ? '' : 'collapsed'}}" wire:ignore.self type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{$index}}" aria-expanded="true" aria-controls="collapseOne_{{$index}}">
                        #{{$conteoGeneral}} | FD : {{$fe}} | UR : {{$usuarios}} | FC : {{$fc}} | E : {{$r->programacion_estado == 1 ? 'ACTIVO' : 'DESHABILITADO'}}
                    </button>
                </h2>
                <div id="collapseOne_{{$index}}" class="accordion-collapse collapse {{$index == 0 ? 'show' : ''}}" data-bs-parent="#accordionExample" wire:ignore.self >
                    <div class="accordion-body" >
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3 text-end">
                                @php
                                    $user = \Illuminate\Support\Facades\Auth::user(); // Obtiene el usuario autenticado
                                    // Obtén el primer rol del usuario y su ID
                                    $roleId = $user->roles->first()->id ?? null;
                                @endphp
                                @if($roleId == 1 || $roleId == 2)
                                    <button class="btn btn-sm text-white bg-success" wire:click="cambiarEstadoProgramacion({{$r->id_programacion}},1)" data-bs-toggle="modal" data-bs-target="#modalAprobarProgramacion"><i class="fa-solid fa-check"></i> APROBAR</button>
                                    <button class="btn btn-sm text-white bg-danger" wire:click="cambiarEstadoProgramacion({{$r->id_programacion}},4)" data-bs-toggle="modal" data-bs-target="#modalAprobarProgramacion"><i class="fa fa-x"></i> RECHAZAR</button>
                                @endif
                                <a class="btn btn-sm text-white bg-primary" href="{{route('Despachotransporte.editar_programaciones',['data'=>base64_encode($r->id_programacion)])}}"><i class="fa-solid fa-pencil"></i> EDITAR</a>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr style="background: #f5f5f9">
                                        <th>N°</th>
                                        <th>Servicio</th>
                                        <th>Proveedor</th>
                                        <th>Importe Total</th>
                                        <th>Peso</th>
                                        <th>Llenado en Peso</th>
                                        <th>Cambio de Tarifa</th>
                                        <th>Coso Flete</th>
                                        <th>Flete / Venta</th>
                                        <th>Flete / Peso</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($r->despacho) > 0)
                                        @php $conteoGeneral2 = 1; @endphp
                                        @foreach($r->despacho as $des)
                                            <tr>
                                                <td>{{$conteoGeneral2}}</td>
                                                <td>{{$des->tipo_servicio_concepto}}</td>
                                                <td>{{$des->transportista_nom_comercial}}</td>
                                                <td>S/ {{$general->formatoDecimal($des->totalVentaDespacho)}}</td>
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
                                                    $styleColor = "text-danger";
                                                    if ($des->despacho_estado_modificado == 1){
                                                        $styleColor = "text-success";
                                                    }
                                                @endphp
                                                <td><b class="{{$styleColor}}">{{$des->despacho_estado_modificado == 1 ? 'SI' : 'NO'}}</b></td>
                                                <td>
                                                    <span class="{{$des->despacho_estado_modificado == 1 ? 'text-danger' : ''}}">S/ {{$des->despacho_flete}}</span>
                                                    <b class="{{$styleColor}}">
                                                        {{$des->despacho_estado_modificado == 1 ? '=> S/ '.$des->despacho_monto_modificado : ''}}
                                                    </b>
                                                </td>
                                                @php
                                                    $ra = 0;
                                                    if ($des->despacho_costo_total && $des->totalVentaDespacho > 0) {
                                                        $to = ($des->despacho_costo_total / $des->totalVentaDespacho) * 100;
                                                        $ra = $general->formatoDecimal($to);
                                                    }
                                                @endphp
                                                <td>{{$ra}} %</td>
                                                @php
                                                    $ra2 = 0;
                                                    if ($des->despacho_costo_total){
                                                        $to = $des->despacho_costo_total / $des->despacho_peso;
                                                        $ra2 = $general->formatoDecimal($to);
                                                    }
                                                @endphp
                                                <td>{{$ra2}}</td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm text-white mb-2" wire:click="listar_informacion_despacho({{$des->id_despacho}})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">
                                                        <i class="fa-solid fa-eye"></i> Despacho
                                                    </button>

                                                    <button class="btn btn-info btn-sm text-white mb-2" wire:click="modal_guia_info({{$des->id_guia}})" data-bs-toggle="modal" data-bs-target="#modalInformacionGuia">
                                                        <i class="fa-solid fa-eye"></i> Guía
                                                    </button>

                                                    <button class="btn btn-warning btn-sm text-white mb-2" wire:click="listar_detalle_guia({{$des->id_guia}})" data-bs-toggle="modal" data-bs-target="#modalDetalleGuia">
                                                        <i class="fa-solid fa-eye"></i> Factura
                                                    </button>
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

    @if(count($serviciosTransporte) > 0)
        <div class="row mt-3">
            <div class="col-lg-12 mt-4">
                <h5>Servicio de transporte: </h5>
            </div>
        </div>
    @endif
    <div class="accordion" id="accordionServicios">
        @foreach($serviciosTransporte as $index => $servicio)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button {{$index == 0 ? '' : 'collapsed'}}" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseServicio_{{$index}}"
                            aria-expanded="false">
                        #{{$loop->iteration}} | UR: {{$servicio->name}} {{$servicio->last_name}}
                        | FC: {{$general->obtenerNombreFecha($servicio->serv_transpt_fecha_creacion,'DateTime','DateTime')}}
                    </button>
                </h2>
                <div id="collapseServicio_{{$index}}"
                     class="accordion-collapse collapse {{$index == 0 ? 'show' : ''}}"
                     data-bs-parent="#accordionServicios">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-12 text-end mb-3">
                                <button class="btn btn-sm text-white bg-success" wire:click="cambiarEstadoServicioTransp({{$servicio->id_serv_transpt}},1)" data-bs-toggle="modal" data-bs-target="#modalAprobarServicioTransp"><i class="fa-solid fa-check"></i> APROBAR</button>
                                <button class="btn btn-sm text-white bg-danger" wire:click="cambiarEstadoServicioTransp({{$servicio->id_serv_transpt}},4)" data-bs-toggle="modal" data-bs-target="#modalAprobarServicioTransp"><i class="fa fa-x"></i> RECHAZAR</button>
                            </div>
                            <div class="col-12">
                                <table class="table">
                                    <thead>
                                    <tr style="background: #f5f5f9">
                                        <th>Codigo</th>
                                        <th>Motivo</th>
                                        <th>Detalle Motivo</th>
                                        <th>Remitente</th>
                                        <th>Destinatario</th>
                                        <th>Peso</th>
                                        <th>Volumen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>{{$servicio->serv_transpt_codigo}}</td>
                                        <td>{{$servicio->serv_transpt_motivo}}</td>
                                        <td>{{$servicio->serv_transpt_detalle_motivo}}</td>
                                        <td>
                                            {{ $servicio->serv_transpt_remitente_ruc }} <br><br>
                                            {{ $servicio->serv_transpt_remitente_razon_social }} <br><br>
                                            {{ $servicio->serv_transpt_remitente_direccion }}
                                        </td>
                                        <td>
                                            {{ $servicio->serv_transpt_destinatario_ruc }} <br><br>
                                            {{ $servicio->serv_transpt_destinatario_razon_social }} <br><br>
                                            {{ $servicio->serv_transpt_destinatario_direccion }}
                                        </td>
                                        <td>{{$general->formatoDecimal($servicio->serv_transpt_peso)}} <b>(kg)</b></td>
                                        <td>{{$general->formatoDecimal($servicio->serv_transpt_volumen)}} <b>(cm³)</b></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{ $resultado->links(data: ['scrollTo' => false]) }}

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

    </style>
</div>
@script
<script>

    $wire.on('hideModalDelete', () => {
        $('#modalAprobarProgramacion').modal('hide');
    });

    $wire.on('hideModalDeleteSerTr', () => {
        $('#modalAprobarServicioTransp').modal('hide');
    });
</script>
@endscript


