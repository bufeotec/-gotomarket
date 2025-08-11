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
                                    <a class="btn btn-sm bg-success text-white">Gestionar OS</a>
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
                                @endif@if($listar_detalle_despacho->id_tipo_servicios == 2)
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
                                                    <th>Dirección de Entrega</th>
                                                    <th>UBIGEO</th>
                                                    @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 || $listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                                        <th>Estado del comprobante</th>
                                                    @endif
                                                    @if($listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                                        <th>Cambio de estado</th>
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
                                                            <td>{{ $ta->guia_direc_entrega }}</td>
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

    <div class="card">
        <div class="card-body">
            <div class="row align-items-center mt-2">
                <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                    <label for="select_tipo" class="form-label">Seleccione tipo</label>
                    <select class="form-select" wire:model.live="select_tipo" wire:change="limpiar_tipo_select">
                        <option value="">Seleccionar...</option>
                        <option value="1">Orden de servicio</option>
                        <option value="2">Guía / Clientes</option>
                    </select>
                </div>
            </div>

            @if($select_tipo == 1)
                <div class="row align-items-center mt-2">
                    <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                        <label for="estado_os" class="form-label">Estado de OS</label>
                        <select class="form-select" wire:model.live="estado_os_temp" wire:change="agregarEstado">
                            <option value="">Seleccionar...</option>
                            <option value="0">Emitido</option>
                            <option value="1">Aprobado</option>
                            <option value="2">En Ejecución</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="codigo_os" class="form-label">OS</label>
                        <input type="text" name="codigo_os" id="codigo_os" wire:model.live="codigo_os" placeholder="Ej: OS25-01234" class="form-control">
                    </div>
                    <div class="col-lg-2">
                        <label for="id_tipo_servicios" class="form-label">Tipo de OS</label>
                        <select class="form-select" name="id_tipo_servicios" id="id_tipo_servicios" wire:model="id_tipo_servicios">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_tipo_servicio as $lts)
                                <option value="{{ $lts->id_tipo_servicios }}">{{ $lts->tipo_servicio_concepto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="fecha_desde_os" class="form-label">Desde</label>
                        <input type="date" name="fecha_desde_os" id="fecha_desde_os" wire:model.live="fecha_desde_os" class="form-control">
                    </div>
                    <div class="col-lg-2">
                        <label for="fecha_hasta_os" class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta_os" id="fecha_hasta_os" wire:model.live="fecha_hasta_os" class="form-control">
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 mt-3">
                        <a class="btn btn-sm btn-primary text-white" wire:click="buscar_orden_servicio"> <i class="fa-solid fa-magnifying-glass"></i> Buscar</a>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12 mt-1">
                        @if(count($estadosSeleccionados) > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Estados Seleccionados ({{ count($estadosSeleccionados) }})</h6>
                                </div>
                                <div class="card-body">
                                    @foreach($estadosSeleccionados as $index => $estado)
                                        <div class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                <span>{{ $estado['nombre'] }}</span>
                                            </div>
                                            <a class="btn btn-sm btn-outline-danger"
                                               wire:click="eliminarEstado({{ $index }})"
                                               title="Eliminar estado">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="mt-1">
                                No hay estados seleccionados.
                            </p>
                        @endif
                    </div>
                </div>
            @elseif($select_tipo == 2)
                <div class="row align-items-center mt-2">
                    <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                        <label for="ruc_cliente" class="form-label">RUC - Nombre del cliente</label>
                        <input type="text" name="ruc_cliente" id="ruc_cliente" wire:model.live="ruc_cliente" class="form-control">
                    </div>
                    <div class="col-lg-2">
                        <label for="codigo_guia" class="form-label">Guía</label>
                        <input type="text" name="codigo_guia" id="codigo_guia" wire:model.live="codigo_guia" placeholder="Ej: T0123456789" class="form-control">
                    </div>
                    <div class="col-lg-2">
                        <label for="estado_guia" class="form-label">Estado de guía</label>
                        <select class="form-select" name="estado_guia" id="estado_guia" wire:model="estado_guia">
                            <option value="">Seleccionar...</option>
                            <option value="4">Programado</option>
                            <option value="7">Guía en tránsito</option>
                            <option value="8">Guía entregada</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="fecha_desde_guia" class="form-label">Desde</label>
                        <input type="date" name="fecha_desde_guia" id="fecha_desde_guia" wire:model.live="fecha_desde_guia" class="form-control">
                    </div>
                    <div class="col-lg-2">
                        <label for="fecha_hasta_guia" class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta_guia" id="fecha_hasta_guia" wire:model.live="fecha_hasta_guia" class="form-control">
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 mt-3">
                        <a class="btn btn-sm btn-primary text-white" wire:click="buscar_guia_cliente"> <i class="fa-solid fa-magnifying-glass"></i> Buscar</a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_orden_servicio"></div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_guia_cliente"></div>
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
                <h6 class="m-0">FA : Fecha de Aprobación</h6>
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
            <div class="col-lg-12 mt-2">
                <h5>Programación de despacho: </h5>
            </div>
        </div>
    @endif

    <div class="accordion mt-3" id="accordionExample">
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
            <div class="accordion-item" >
                <h2 class="accordion-header d-flex justify-content-between align-items-center">
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
                            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr style="background: #f5f5f9">
                                        <th>N°</th>
                                        <th>OS</th>
                                        <th>Proveedor</th>
                                        <th>Tipo Servicio</th>
                                        <th>Guías Asociadas</th>
                                        <th>Cambio Tarifa</th>
                                        <th>Tarifa</th>
                                        <th>Venta Despachada(sin IGV)</th>
                                        <th>Flete Total</th>
                                        <th>Flete / Venta</th>
                                        <th>Flete / Peso</th>
                                        <th>Estado de OS</th>
                                        <th>OS EDITADA</th>
                                        <th style="background: #e7f1ff">Acciones</th>
                                        <th style="background: #e7f1ff">Gestionar OS</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($r->despacho) > 0)
                                        @php $conteoGeneral2 = 1; @endphp
                                        @foreach($r->despacho as $des)
                                            <tr>
                                                <td>{{$conteoGeneral2}}</td>
                                                <td>{{$des->despacho_numero_correlativo ?? '-'}}</td>
                                                <td>{{$des->transportista_nom_comercial}}</td>
                                                <td>{{$des->tipo_servicio_concepto}}</td>
                                                <td>
                                                    @php
                                                        $guiasComprobante = \Illuminate\Support\Facades\DB::table('despacho_ventas as dv')->join('guias as g','dv.id_guia','=','g.id_guia')->where('dv.id_despacho', '=', $des->id_despacho)->get();
                                                        $totalGuias = count($guiasComprobante); // Contamos las guías
                                                    @endphp
                                                    @foreach($guiasComprobante as $indexGuias => $g)
                                                        @if($indexGuias <= 2)
                                                            {{ $general->formatearCodigo($g->guia_nro_doc) }}
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
                                                        $styleColor = "text-danger";
                                                        if ($des->despacho_estado_modificado == 1){
                                                            $styleColor = "text-success";
                                                        }
                                                    @endphp
                                                    <b class="{{$styleColor}}">{{$des->despacho_estado_modificado == 1 ? 'SI' : 'NO'}}</b>
                                                </td>
                                                <td>S/ {{$des->despacho_flete}}</td>
                                                <td>S/ {{$general->formatoDecimal($des->totalVentaDespacho)}}</td>
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
                                                    // Verificar que despacho_peso no sea 0 antes de dividir
                                                    if ($des->despacho_costo_total && $des->despacho_peso > 0) {
                                                        $to = $des->despacho_costo_total / $des->despacho_peso;
                                                        $ra2 = $general->formatoDecimal($to);
                                                    } elseif ($des->despacho_costo_total) {
                                                        // Opcional: Manejar el caso cuando hay costo pero peso es 0
                                                        $ra2 = 'N/A'; // O cualquier valor que quieras mostrar en este caso
                                                    }
                                                @endphp
                                                <td>S/ {{ $ra2 }}</td>
                                                <td>
                                                    @if($des->despacho_estado_aprobacion == 0)
                                                        Pendiente
                                                    @elseif($des->despacho_estado_aprobacion == 1)
                                                        Aprobado
                                                    @elseif($des->despacho_estado_aprobacion == 2)
                                                        En camino
                                                    @elseif($des->despacho_estado_aprobacion == 3)
                                                        Culminado
                                                    @elseif($des->despacho_estado_aprobacion == 4)
                                                        Rechazado
                                                    @else
                                                        Estado desconocido
                                                    @endif
                                                </td>
                                                <td></td>
                                                <td>
                                                    <a class="btn btn-primary btn-sm text-white mb-2" wire:click="listar_informacion_despacho({{$des->id_despacho}})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">
                                                        <i class="fa-solid fa-eye"></i> Detalle OS
                                                    </a>
                                                </td>
                                                <td>
                                                    <a class="btn btn-primary btn-sm text-white mb-2" target="_blank" href="{{route('Despachotransporte.gestionar_os_detalle',['id_despacho'=>base64_encode($des->id_despacho)])}}">
                                                        <i class="fa-solid fa-eye"></i> Gestionar OS
                                                    </a>
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

</div>
