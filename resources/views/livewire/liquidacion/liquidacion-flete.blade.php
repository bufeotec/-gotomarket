<div>

    @php
        $general = new \App\Models\General();
    @endphp
{{--    MODAL DETALLE DESPACHO--}}
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
                                    <div class="col-lg-3 col-md-3 col-sm-4 mb-3">
                                        <strong class="colorgotomarket mb-2">Capacidad de la Tarifa:</strong>
                                        <p>Min: {{$general->formatoDecimal($listar_detalle_despacho->despacho_cap_min)}} Kg - Max: {{ $general->formatoDecimal($listar_detalle_despacho->despacho_cap_max) }} Kg</p>
                                    </div>
                                @endif
                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Peso del Despacho:</strong>
                                    <p>{{$general->formatoDecimal($listar_detalle_despacho->despacho_peso)}} Kg</p>
                                </div>
                                {{-- ---------------------------------PRECIOS ----------------------------------------------- --}}
                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Monto de la Tarifa:</strong>
                                    <p>S/ {{$general->formatoDecimal($listar_detalle_despacho->despacho_flete)}}</p>
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
                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Otros Gastos:</strong>
                                    <p>S/ {{$listar_detalle_despacho->despacho_gasto_otros ? $general->formatoDecimal($listar_detalle_despacho->despacho_gasto_otros) : 0}}</p>
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
                                        <p>S/ {{$listar_detalle_despacho->despacho_ayudante ? $general->formatoDecimal($listar_detalle_despacho->despacho_ayudante) : 0}}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <h6>Resumen General del Despacho</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-3 col-md-2 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Total Importe Venta:</strong>
                                    <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->totalVentaDespacho) }}</p>
                                </div>
                                <div class="col-lg-3 col-md-2 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Total Despacho:</strong>
                                    <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->despacho_costo_total) }}</p>
                                </div>
                                <div class="col-lg-3 col-md-2 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Flete / Venta</strong>
                                    @php
                                        $ra = 0;
                                        if ($listar_detalle_despacho->totalVentaDespacho != 0){
                                            $to = ($listar_detalle_despacho->despacho_costo_total / $listar_detalle_despacho->totalVentaDespacho) * 100;
                                            $ra = $general->formatoDecimal($to);
                                        }
                                    @endphp
                                    <p>{{$ra}} %</p>
                                </div>
                                <div class="col-lg-3 col-md-2 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Flete / Peso</strong>
                                    @php
                                        $ra2 = 0;
                                        if ($listar_detalle_despacho->despacho_peso  != 0){
                                            $to = $listar_detalle_despacho->despacho_costo_total / $listar_detalle_despacho->despacho_peso;
                                            $ra2 = $general->formatoDecimal($to);
                                        }
                                    @endphp
                                    <p>{{$ra2}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <h6>Información de Comprobantes</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th>N°</th>
                                                <th>Guía</th>
                                                <th>F. Emision</th>
                                                <th>Cliente</th>
                                                <th>Comprobante</th>
                                                <th>Importe Venta</th>
                                                <th>F. Despacho</th>
                                                <th>Peso Kilos</th>
                                                <th>Estado del comprobante</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if(count($listar_detalle_despacho->comprobantes) > 0)
                                                @php $conteo = 1; @endphp
                                                @foreach($listar_detalle_despacho->comprobantes as $ta)
                                                    <tr>
                                                        <td>{{$conteo}}</td>
                                                        <td>{{$general->formatearCodigo($ta->guia_nro_doc)}}</td>
                                                        <td>{{date('d-m-Y',strtotime($ta->guia_fecha_emision))}}</td>
                                                        <td>{{$ta->guia_nombre_cliente}}</td>
                                                        <td>{{$ta->guia_nro_doc_ref}}</td>
                                                        <td>S/ {{$general->formatoDecimal($ta->guia_importe_total_sin_igv)}}</td>
                                                        <td>{{date('d-m-Y',strtotime($listar_detalle_despacho->programacion_fecha))}}</td>
                                                        <td>{{$general->formatoDecimal($ta->peso_total_kilos)}} Kg</td>
                                                        <td>
                                                            <span class="d-block tamanhoTablaComproantes">
                                                                @switch($ta->guia_estado_aprobacion)
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
                                                                        Pendiente de aprobación de despacho
                                                                        @break
                                                                    @case(5)
                                                                        Aceptado por Créditos
                                                                        @break
                                                                    @case(6)
                                                                        Estado de facturación
                                                                        @break
                                                                    @case(7)
                                                                        @php
                                                                            $despacho_ventas = \Illuminate\Support\Facades\DB::table('despacho_ventas as dv')
                                                                                ->join('despachos as d','dv.id_despacho','=','d.id_despacho')
                                                                                ->where('dv.id_guia','=',$ta->id_guia)
                                                                                ->select('dv.despacho_detalle_estado_entrega')
                                                                                ->first();
                                                                        @endphp

                                                                        @if($despacho_ventas && $despacho_ventas->despacho_detalle_estado_entrega == 8)
                                                                            Guía entregada
                                                                        @else
                                                                            Guía en tránsito
                                                                        @endif
                                                                        @break
                                                                    @case(8)
                                                                        Guía entregada
                                                                        @break
                                                                    @case(9)
                                                                        Despacho aprobado
                                                                        @break
                                                                    @case(10)
                                                                        Despacho rechazado
                                                                        @break
                                                                    @case(11)
                                                                        Guía no entregada
                                                                        @break
                                                                    @case(12)
                                                                        Guía anulada
                                                                        @break
                                                                    @case(13)
                                                                        Registrada en Intranet
                                                                        @break
                                                                    @case(14)
                                                                        Guía anulada por NC
                                                                        @break
                                                                    @case(15)
                                                                        Pendiente de NC
                                                                        @break
                                                                    @case(20)
                                                                        @php
                                                                            $despacho_ventas_l = \Illuminate\Support\Facades\DB::table('despacho_ventas as dv')
                                                                            ->join('despachos as d','dv.id_despacho','=','d.id_despacho')
                                                                            ->where('dv.id_guia','=',$ta->id_guia)
                                                                            ->where('d.id_tipo_servicios','=',1)
                                                                            ->first();

                                                                            $despacho_ventas_p = \Illuminate\Support\Facades\DB::table('despacho_ventas as dv')
                                                                            ->join('despachos as d','dv.id_despacho','=','d.id_despacho')
                                                                            ->where('dv.id_guia','=',$ta->id_guia)
                                                                            ->where('d.id_tipo_servicios','=',2)
                                                                            ->first();

                                                                            if($despacho_ventas_l->despacho_detalle_estado_entrega == 0 && $despacho_ventas_p->despacho_detalle_estado_entrega == 0
                                                                            && $despacho_ventas_l->despacho_estado_aprobacion == 1 && $despacho_ventas_p->despacho_estado_aprobacion == 1
                                                                            ){
                                                                                echo 'Despacho aprobado';
                                                                            } else if ($despacho_ventas_l->despacho_detalle_estado_entrega == 0 && $despacho_ventas_p->despacho_detalle_estado_entrega == 0
                                                                             && $despacho_ventas_l->despacho_estado_aprobacion == 2 && $despacho_ventas_p->despacho_estado_aprobacion == 1
                                                                            ){
                                                                                echo 'Guía en tránsito asd';
                                                                            } else if ($despacho_ventas_l->despacho_detalle_estado_entrega == 8 && $despacho_ventas_p->despacho_detalle_estado_entrega == 0
                                                                            && $despacho_ventas_l->despacho_estado_aprobacion == 3 && $despacho_ventas_p->despacho_estado_aprobacion == 2
                                                                            ){
                                                                                echo 'Guía en tránsito a';
                                                                            } else if ($despacho_ventas_l->despacho_detalle_estado_entrega == 8 && $despacho_ventas_p->despacho_detalle_estado_entrega == 8
                                                                             && $despacho_ventas_l->despacho_estado_aprobacion == 3 && $despacho_ventas_p->despacho_estado_aprobacion == 3){
                                                                                echo 'Guía entregada';
                                                                            }
                                                                        @endphp
                                                                        @break
                                                                    @default
                                                                        Estado desconocido
                                                                @endswitch
                                                            </span>
                                                        </td>
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
                        </div>
                    </div>
                </div>
            @endif
        </x-slot>
    </x-modal-general>
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleGuias</x-slot>
        <x-slot name="titleModal">Guías Relacionadas</x-slot>
        <x-slot name="modalContent">
            @if($guiasAsociadasDespachos)
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <x-table-general>
                                <x-slot name="thead">
                                    <tr>
                                        <th>N°</th>
                                        <th>Guía</th>
                                        <th>F. Emision</th>
                                        <th>Cliente</th>
                                        <th>Comprobante</th>
                                        <th>Importe</th>
                                        <th>F. Despacho</th>
                                        <th>Peso</th>
                                    </tr>
                                </x-slot>

                                <x-slot name="tbody">
                                    @if(count($guiasAsociadasDespachos) > 0)
                                        @php $conteo = 1; @endphp
                                        @foreach($guiasAsociadasDespachos as $ta)
                                            <tr>
                                                <td>{{$conteo}}</td>
                                                <td>{{$general->formatearCodigo($ta->guia_nro_doc)}}</td>
                                                <td>
                                                    {{date('d-m-Y',strtotime($ta->guia_fecha_emision))}}
                                                </td>
                                                <td>{{$ta->guia_nombre_cliente}}</td>
                                                <td>{{$ta->guia_nro_doc_ref}}</td>
                                                <td>S/ {{$general->formatoDecimal($ta->guia_importe_total_sin_igv)}}</td>
                                                <td>{{ date('d-m-Y',strtotime($ta->programacion_fecha)) }}</td>
                                                <td>{{$general->formatoDecimal($ta->peso_total)}} Kg</td>
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
                </div>
            @endif
        </x-slot>
    </x-modal-general>

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

    <div class="row mt-2 mb-4 align-items-center">
        <div class="col-lg-2 col-md-3 col-sm-12 mb-1">
            <label for="id_transportistas" class="form-label">Lista de transportistas</label>
            <select class="form-select" name="id_transportistas" id="id_transportistas" wire:model="id_transportistas" wire:change="{{$id_liquidacion_edit ? 'seleccion_trans_edit' : 'seleccion_trans'}}">
                <option value="">Seleccionar...</option>
                @foreach($listar_transportistas as $lt)
                    <option value="{{ $lt->id_transportistas }}">{{ $lt->transportista_nom_comercial }}</option>
                @endforeach
            </select>
            @error('id_transportistas')
            <span class="message-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="col-lg-2 col-md-3 col-sm-12 mb-1">
            <label for="selectTipoSerivicio" class="form-label">Tipo</label>
            <select name="selectTipoSerivicio" id="selectTipoSerivicio" class="form-select" wire:model="selectTipoSerivicio" wire:change="{{$id_liquidacion_edit ? 'seleccion_trans_edit' : 'seleccion_trans'}}">
                <option value="">Seleccionar</option>
                @foreach($listar_tipos_servicios as $lis)
                    <option value="{{$lis->id_tipo_servicios}}">{{$lis->tipo_servicio_concepto}}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-2 col-md-3 col-sm-12 mb-1">
            <label for="date_desde" class="form-label">Desde</label>
            <x-input-general  type="date" name="date_desde" id="date_desde" wire:model="date_desde" wire:change="{{$id_liquidacion_edit ? 'seleccion_trans_edit' : 'seleccion_trans'}}"/>
        </div>

        <div class="col-lg-2 col-md-3 col-sm-12 mb-1">
            <label for="date_hasta" class="form-label">Hasta</label>
            <x-input-general  type="date" name="date_hasta" id="date_hasta" wire:model="date_hasta" wire:change="{{$id_liquidacion_edit ? 'seleccion_trans_edit' : 'seleccion_trans'}}"/>
        </div>

{{--        <div class="col-lg-2 col-md-3 col-sm-12 mt-4 mb-1 text-end">--}}
{{--            <a class="btn btn-sm bg-primary text-white" wire:click="buscar_liquidacion"><i class="fa-solid fa-magnifying-glass"></i> Buscar</a>--}}
{{--        </div>--}}
    </div>
    @if($id_transportistas)
        @php
            $conteoLiquida = \Illuminate\Support\Facades\DB::table('despachos as d')->where('d.despacho_liquidado', '=',0)
                ->where('d.id_transportistas', $id_transportistas)
                ->where('d.despacho_estado', 1)
                ->whereIn('d.despacho_estado_aprobacion', [3, 4])->count();
        @endphp
        <p class="mt-2">Existe <b class="colorgotomarket">{{$conteoLiquida}}</b> despachos que aún están pendientes de liquidación.</p>
    @endif
    @if($despachos && count($despachos) > 0)
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <label for="liquidacion_serie" class="form-label">Serie (*)</label>
                        <x-input-general  type="text" name="liquidacion_serie" id="liquidacion_serie" wire:model="liquidacion_serie" />
                        @error('liquidacion_serie')
                            <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <label for="liquidacion_correlativo" class="form-label">Correlativo (*)</label>
                        <x-input-general  type="text" name="liquidacion_correlativo" id="liquidacion_correlativo" wire:model="liquidacion_correlativo" />
                        @error('liquidacion_correlativo')
                            <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <label for="liquidacion_ruta_comprobante" class="form-label">Comprobante </label>
                        <x-input-general  type="file" name="liquidacion_ruta_comprobante" id="liquidacion_ruta_comprobante" wire:model="liquidacion_ruta_comprobante" />
                        @error('liquidacion_ruta_comprobante')
                            <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="guardar_liquidacion">
        <x-card-general-view>
            <x-slot name="content">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>Check</th>
                                    <th>N° OS</th>
                                    <th>Proveedor</th>
                                    <th>Servicio</th>
                                    <th>Fecha de Despacho</th>
                                    <th>Guías Asociadas</th>
                                    <th>Total Venta Despachada</th>
                                    <th>Peso Total</th>
                                    <th>Importe Total del Servicio</th>
                                    <th>Cambio de Tarifa</th>
                                    <th>Costo Tarifa</th>
                                    <th>Acciones</th>
                                </tr>
                            </x-slot>

                            <x-slot name="tbody">
                                @if(count($despachos) > 0)
                                    @php
                                        $subtotal = 0; // Inicializar el subtotal
                                        $totalLiquidacion = 0; // Inicializar el total de liquidación
                                    @endphp
                                    @foreach($despachos as $key => $despacho)
                                        @php
                                            // Calcular el peso total del despacho
                                            $totalPesoDespacho = $despacho->despacho_peso;
                                            if ($despacho->totalPesoNoEntregado) {
                                                $totalPesoDespacho = $despacho->despacho_peso - $despacho->totalPesoNoEntregado;
                                            }

                                            // Calcular el "Importe Total del Servicio" para este despacho
                                            $despachoGeneraLiquidacion = 0;
                                            if ($despacho->id_tipo_servicios == 1) {
                                                $despachoGeneraLiquidacion = $despacho->despacho_costo_total;
                                            } else {
                                                $despachoGeneraLiquidacion = ($despacho->despacho_monto_modificado * $totalPesoDespacho) + $despacho->despacho_ayudante + $despacho->despacho_gasto_otros;
                                            }

                                            // Sumar al subtotal si el despacho está seleccionado
                                            if (isset($select_despachos[$despacho->id_despacho])) {
                                                $subtotal += $despachoGeneraLiquidacion;

                                                // Calcular el total de liquidación
                                                $costoTarifa = isset($select_despachos[$despacho->id_despacho]['gastos']['costo_flete']['valor'])
                                                    ? floatval($select_despachos[$despacho->id_despacho]['gastos']['costo_flete']['valor'])
                                                    : 0;

                                                $costoMano = isset($select_despachos[$despacho->id_despacho]['gastos']['mano_obra']['valor'])
                                                    ? floatval($select_despachos[$despacho->id_despacho]['gastos']['mano_obra']['valor'])
                                                    : 0;

                                                $costoOtros = isset($select_despachos[$despacho->id_despacho]['gastos']['otros_gasto']['valor'])
                                                    ? floatval($select_despachos[$despacho->id_despacho]['gastos']['otros_gasto']['valor'])
                                                    : 0;

                                                $pesoFinalLi = isset($select_despachos[$despacho->id_despacho]['gastos']['peso_final_kilos']['valor'])
                                                    ? floatval($select_despachos[$despacho->id_despacho]['gastos']['peso_final_kilos']['valor'])
                                                    : 0;

                                                if ($despacho->id_tipo_servicios == 1) {
                                                    $totalDespacho = $costoTarifa + $costoMano + $costoOtros;
                                                } else {
                                                    $totalDespacho = ($costoTarifa * $pesoFinalLi) + $costoMano + $costoOtros;
                                                }

                                                $totalLiquidacion += $totalDespacho; // Sumar al total de liquidación
                                            }
                                        @endphp
                                        <tr class="tableHoverLiquidacion">
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    wire:model="select_despachos.{{ $despacho->id_despacho }}"
                                                    wire:key="{{ $despacho->id_despacho }}"
                                                    {{ isset($select_despachos[$despacho->id_despacho]) ? 'checked' : '' }}
                                                    wire:click="actualizarDespacho('{{ $despacho->id_despacho }}', $event.target.checked)"
                                                >
                                            </td>
                                            <td>{{ $despacho->despacho_numero_correlativo }}</td>
                                            <td>{{ $despacho->transportista_nom_comercial }}</td>
                                            <td>{{ $despacho->tipo_servicio_concepto }}</td>
                                            <td>{{ date('d-m-Y', strtotime($despacho->programacion_fecha)) }}</td>
                                            <td>
                                                @php
                                                    // Obtenemos las guías asociadas al despacho, uniendo con la tabla guias
                                                    $guiasComprobante = \Illuminate\Support\Facades\DB::table('despacho_ventas as dv')
                                                        ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                                                        ->where('dv.id_despacho', '=', $despacho->id_despacho)
                                                        ->select('g.guia_nro_doc', 'dv.id_guia')
                                                        ->get();

                                                    $totalGuias = count($guiasComprobante); // Contamos las guías
                                                @endphp

                                                @foreach($guiasComprobante as $indexGuias => $g)
                                                    @if($indexGuias <= 2)
                                                        <a wire:click="listar_guias_despachos({{ $despacho->id_despacho }})" data-bs-toggle="modal" data-bs-target="#modalDetalleGuias" class="cursoPointer text-primary">
                                                            {{ $g->guia_nro_doc }}
                                                        </a>
                                                        @if($indexGuias < 2 && $indexGuias < $totalGuias - 1)
                                                            , <!-- Mostrar la coma solo si no es el último elemento que se va a mostrar -->
                                                        @elseif($indexGuias == 2 && $totalGuias > 3)
                                                            ... <!-- Mostrar "..." si hay más guías después de las tres primeras -->
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td>
                                                @php
                                                    // Calcular el total ajustado restando los no entregados (estado aprobación 11)
                                                    $totalVentaDespaDespacho = $despacho->totalVentaDespacho;
                                                    if ($despacho->totalVentaNoEntregado) {
                                                        $totalVentaDespaDespacho = $despacho->totalVentaDespacho - $despacho->totalVentaNoEntregado;
                                                    }
                                                @endphp

                                                {{-- Mostrar el total original (suma de todos los guia_importe_total) --}}
                                                <span class="d-block">S/ {{ $general->formatoDecimal($despacho->totalVentaDespacho) }}</span>

                                                {{-- Mostrar el monto no entregado (guías con estado aprobación 11) --}}
                                                @if($despacho->totalVentaNoEntregado)
                                                    <span class="d-block text-danger">S/ -{{ $general->formatoDecimal($despacho->totalVentaNoEntregado) }}</span>
                                                    {{-- Mostrar el total neto (total - no entregados) --}}
                                                    <b class="colorBlackComprobantes">S/ {{ $general->formatoDecimal($totalVentaDespaDespacho) }}</b>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="d-block">{{ $general->formatoDecimal($despacho->despacho_peso) }}</span>
                                                @if($despacho->totalPesoNoEntregado)
                                                    <span class="d-block text-danger">-{{ $general->formatoDecimal($despacho->totalPesoNoEntregado) }}</span>
                                                    <b class="colorBlackComprobantes">{{ $general->formatoDecimal($totalPesoDespacho) }} kg</b>
                                                @endif
                                            </td>
                                            <td>S/ {{ $general->formatoDecimal($despachoGeneraLiquidacion) }}</td>
                                            <td>
                                                @php
                                                    $styleColor = "text-danger";
                                                    if ($despacho->despacho_estado_modificado == 1) {
                                                        $styleColor = "text-success";
                                                    }
                                                @endphp
                                                <b class="{{ $styleColor }}">{{ $despacho->despacho_estado_modificado == 1 ? 'SI' : 'NO' }}</b>
                                            </td>
                                            <td>
                                                <span class="{{ $despacho->despacho_estado_modificado == 1 ? 'text-danger' : '' }}">S/ {{ $general->formatoDecimal($despacho->despacho_flete) }}</span>
                                                <b class="{{ $styleColor }}">
                                                    {{ $despacho->despacho_estado_modificado == 1 ? '=> S/ ' . $general->formatoDecimal($despacho->despacho_monto_modificado) : '' }}
                                                </b>
                                            </td>
                                            <td>
                                                <x-btn-accion class="btn btn-sm text-primary" wire:click="listar_informacion_despacho({{ $despacho->id_despacho }})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            </td>
                                        </tr>
                                        @if(!empty($select_despachos[$despacho->id_despacho]))
                                            <tr>
                                                <td colspan="12">
                                                    <div class="px-3 row">
                                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                                            <table class="table">
                                                                <thead>
                                                                <tr>
                                                                    <th></th>
                                                                    <th>Costo de Tarifa</th>
                                                                    <th>Mano de Obra</th>
                                                                    <th>Otros Gastos</th>
                                                                    @if($despacho->id_tipo_servicios == 2)
                                                                        <th>Peso Total</th>
                                                                    @endif
                                                                    <th>Total Despacho</th>
                                                                    <th>Flete / Venta</th>
                                                                    <th>Flete / Peso</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr>
                                                                    <td><b class="colorBlackComprobantes">Monto Programado</b></td>
                                                                    <td>S/ {{ $general->formatoDecimal($despacho->despacho_monto_modificado) }}</td>
                                                                    <td>S/ {{ $despacho->despacho_ayudante ? $general->formatoDecimal($despacho->despacho_ayudante) : 0 }}</td>
                                                                    <td>S/ {{ $despacho->despacho_gasto_otros ? $general->formatoDecimal($despacho->despacho_gasto_otros) : 0 }}</td>
                                                                    @if($despacho->id_tipo_servicios == 2)
                                                                        <td>
                                                                            {{ $general->formatoDecimal($totalPesoDespacho) }}
                                                                        </td>
                                                                    @endif
                                                                    <td>S/ {{ $general->formatoDecimal($despachoGeneraLiquidacion) }}</td>
                                                                    <td>
                                                                        {{ $totalVentaDespaDespacho != 0 ? $general->formatoDecimal(($despachoGeneraLiquidacion / $totalVentaDespaDespacho) * 100) : '0.00' }} %
                                                                    </td>
                                                                    <td>
                                                                        {{ $totalPesoDespacho != 0 ? $general->formatoDecimal($despachoGeneraLiquidacion / $totalPesoDespacho) : '0.00' }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><b class="colorBlackComprobantes">Monto de Liquidación</b></td>
                                                                    @php
                                                                        $costoTarifa = isset($select_despachos[$despacho->id_despacho]['gastos']['costo_flete']['valor'])
                                                                            ? floatval($select_despachos[$despacho->id_despacho]['gastos']['costo_flete']['valor'])
                                                                            : 0;

                                                                        $costoMano = isset($select_despachos[$despacho->id_despacho]['gastos']['mano_obra']['valor'])
                                                                            ? floatval($select_despachos[$despacho->id_despacho]['gastos']['mano_obra']['valor'])
                                                                            : 0;

                                                                        $costoOtros = isset($select_despachos[$despacho->id_despacho]['gastos']['otros_gasto']['valor'])
                                                                            ? floatval($select_despachos[$despacho->id_despacho]['gastos']['otros_gasto']['valor'])
                                                                            : 0;

                                                                        $pesoFinalLi = isset($select_despachos[$despacho->id_despacho]['gastos']['peso_final_kilos']['valor'])
                                                                            ? floatval($select_despachos[$despacho->id_despacho]['gastos']['peso_final_kilos']['valor'])
                                                                            : 0;

                                                                        $mostrarItems = $despacho->id_tipo_servicios == 1 ? 3  : 4;
                                                                        $conteoIteGa = 1;
                                                                    @endphp

                                                                    @foreach($select_despachos[$despacho->id_despacho]['gastos'] as $keyGastos => $gasto)
                                                                        @if($conteoIteGa <= $mostrarItems)
                                                                            <td>
                                                                                <x-input-general
                                                                                    type="text"
                                                                                    name="{{ $keyGastos }}_valor"
                                                                                    id="{{ $keyGastos }}_valor_{{ $despacho->id_despacho }}"
                                                                                    onkeyup="validar_numeros(this.id)"
                                                                                    wire:model.live="select_despachos.{{ $despacho->id_despacho }}.gastos.{{ $keyGastos }}.valor"
                                                                                />

                                                                                <textarea
                                                                                    name="{{ $keyGastos }}_descripcion"
                                                                                    id="{{ $keyGastos }}_descripcion_{{ $despacho->id_despacho }}"
                                                                                    class="form-control mt-2"
                                                                                    rows="2"
                                                                                    wire:model="select_despachos.{{ $despacho->id_despacho }}.gastos.{{ $keyGastos }}.descripcion"
                                                                                ></textarea>
                                                                            </td>
                                                                        @endif

                                                                        @php $conteoIteGa++; @endphp
                                                                    @endforeach
                                                                    <td>S/ {{ $general->formatoDecimal($totalDespacho) }}</td>
                                                                    <td>{{ $totalVentaDespaDespacho != 0 ? $general->formatoDecimal(($totalDespacho / $totalVentaDespaDespacho) * 100) : '0' }} %</td>
                                                                    <td>{{ $pesoFinalLi != 0 ? $general->formatoDecimal($totalDespacho / $pesoFinalLi) : '0' }}</td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="col-lg-12 col-md-12 col-sm-12 mt-2 mb-2">
                                                            <label for="comentarios_{{ $despacho->id_despacho }}" class="form-label">
                                                                <b class="colorBlackComprobantes"> Comentarios:</b>
                                                            </label>
                                                            <textarea
                                                                class="form-control"
                                                                rows="3"
                                                                id="comentarios_{{ $despacho->id_despacho }}"
                                                                wire:model="select_despachos.{{ $despacho->id_despacho }}.comentarios"
                                                            ></textarea>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    <tr class="text-end">
                                        <td colspan="12" class="text-end">
                                            <h5 class="text-primary">Subtotal: S/ {{ $general->formatoDecimal($totalLiquidacion) }}</h5>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="11" class="text-center">No se han encontrado resultados.</td>
                                    </tr>
                                @endif
                            </x-slot>
                        </x-table-general>
                    </div>
                </div>
            </x-slot>
        </x-card-general-view>

        <div wire:loading wire:target="actualizarDespacho" class="overlay__eliminar">
            <div class="spinner__container__eliminar">
                <div class="spinner__eliminar"></div>
            </div>
        </div>

        <div class="row mt-4 mb-4">
            <div class="col-lg-12">
                @if(isset($select_despachos) && count($select_despachos) > 0)
                    <div class="text-center d-flex justify-content-end">
                        <button type="submit" class="btn text-white" style="background: #e51821">
                            Guardar Liquidación
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>
    <style>
        .card{
            margin-bottom:0rem;
            border: none;
        }
        .table-hover>tbody>tr:hover{
            --bs-table-accent-bg:none!important;
            /*color:var(--bs-table-hover-color)*/
        }
        .table-hover>tbody>.tableHoverLiquidacion:hover{
            --bs-table-accent-bg:var(--bs-table-hover-bg)!important;
            }
    </style>
</div>
