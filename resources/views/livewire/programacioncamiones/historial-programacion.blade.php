<div>
    @php
        $general = new \App\Models\General();
    @endphp
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

                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <form wire:submit.prevent="cambiarEstadoComprobante">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <h6>Información de Comprobantes</h6>
                                        <hr>
                                    </div>
                                    @if (session()->has('successComprobante'))
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="alert alert-success alert-dismissible show fade mt-2">
                                                {{ session('successComprobante') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        </div>
                                    @endif
                                    @if (session()->has('errorComprobante'))
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="alert alert-danger alert-dismissible show fade mt-2">
                                                {{ session('errorComprobante') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <x-table-general>
                                            <x-slot name="thead">
                                                <tr>
                                                    <th>N°</th>
                                                    <th>Número Documento</th>
                                                    <th>Fecha Emision</th>
                                                    <th>Cliente</th>
                                                    <th>Guía de Remisión</th>
                                                    <th>Importe Venta</th>
                                                    <th>Peso Kilos</th>
                                                    @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 || $listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                                        <th>Estado del comprobante</th>
                                                    @endif
                                                </tr>
                                            </x-slot>

                                            <x-slot name="tbody">
                                                @if(count($listar_detalle_despacho->comprobantes) > 0)
                                                    @php $conteo = 1; @endphp
                                                    @foreach($listar_detalle_despacho->comprobantes as $indexComprobantes => $ta)
                                                        <tr>
                                                            <td>{{$conteo}}</td>
                                                            <td>{{$ta->despacho_venta_factura}}</td>
                                                            <td>
                                                                {{$general->obtenerNombreFecha($ta->despacho_venta_grefecemision,'DateTime','Date')}}
                                                            </td>
                                                            <td>{{$ta->despacho_venta_cnomcli}}</td>
                                                            <td>{{$ta->despacho_venta_guia}}</td>
                                                            <td>S/ {{$general->formatoDecimal($ta->despacho_venta_cfimporte)}}</td>
                                                            <td>{{$general->formatoDecimal($ta->despacho_venta_total_kg)}} Kg</td>
                                                            @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 || $listar_detalle_despacho->despacho_estado_aprobacion == 3)
                                                                <td>
                                                                    @if($listar_detalle_despacho->despacho_estado_aprobacion == 2 && !in_array($ta->despacho_detalle_estado_entrega, [2, 3, 4]) )
                                                                        <select
                                                                            name="estadoComprobante[{{ $indexComprobantes }}]"
                                                                            class="form-control form-select"
                                                                            wire:model="estadoComprobante.{{ $ta->id_despacho_venta }}"
                                                                        >
                                                                            <option value="2">Entregado</option>
                                                                            <option value="3">No entregado</option>
                                                                        </select>
                                                                    @else
                                                                        <span class="font-bold badge  {{$ta->despacho_detalle_estado_entrega == 2 ? 'bg-label-success' : 'bg-label-danger'}}">
                                                                        {{$ta->despacho_detalle_estado_entrega == 2 ? 'ENTREGADO ' : 'NO ENTREGADO'}}
                                                                    </span>
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
                                    <div class="col-lg-12 col-md-12 col-sm-12 mt-4 text-end">
                                        @if($listar_detalle_despacho->despacho_estado_aprobacion == 2)
                                            <button class="btn  text-white bg-primary" type="submit">Guardar Estados de Comprobantes</button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </x-slot>
    </x-modal-general>
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalAprobarProgramacion</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiarEstadoDespachoFormulario">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">¿Confirma que desea cambiar el estado  a "En Camino"?</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('selectedItems') <span class="message-error">{{ $message }}</span> @enderror
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
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalRetornarPendiente</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiarEstadoProgramacionAprobada">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">¿Confirma que desea retornar la programación a "Programaciones Pendientes"?</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_programacionRetorno') <span class="message-error">{{ $message }}</span> @enderror
                        @if (session()->has('error_retornar'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_retornar') }}
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



    <div class="row align-items-center mt-2">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="serie_correlativo" class="form-label">Número de programación</label>
            <input type="text" name="serie_correlativo" id="serie_correlativo" wire:model.live="serie_correlativo" placeholder="Ingrese la información" class="form-control">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="estadoPro" class="form-label">Estado Despacho</label>
            <select name="estadoPro" id="estadoPro" wire:model.live="estadoPro" class="form-select">
                <option value="">Seleccionar</option>
                <option value="1">Aprobado</option>
                <option value="4">Rechazado</option>
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
        @if(count($resultado) > 0)
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <button class="btn btn-success text-white mt-4" wire:click="generar_excel_historial_programacion" wire:loading.attr="disabled"><i class="fa-solid fa-file-excel"></i> Generar Reporte</button>
            </div>
        @endif
        @if(count($selectedItems) > 0)
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2 text-end">
                <button class="btn text-white bg-warning mt-4" data-bs-toggle="modal" data-bs-target="#modalAprobarProgramacion">
                    Cambiar a "En Camino"
                </button>
            </div>
        @endif
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
    @if(count($resultado) > 0)
        <div class="row mt-4">
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
                <h6 class="m-0">FA : Fecha de Aprobación</h6>
            </div>
            <div class="col-lg col-md-3 col-sm-3 mb-1">
                <h6 class="m-0">N° C : Número Correlativo</h6>
            </div>
        </div>
    @endif

    <div class="accordion mt-3" id="accordionExample">
        @if(count($resultado) > 0)
            {{-- Ordenar y agrupar los resultados --}}
            @php
                // Convertir los resultados a una colección
                $resultadoCollection = collect($resultado->items());

                // Ordenar por fecha de programación (de la más antigua a la más reciente)
                $resultadoCollection = $resultadoCollection->sortBy('programacion_fecha');

                // Agrupar por proveedorlocal (Mixto, Local) o Provincial
                $groupedResultado = $resultadoCollection->groupBy(function ($item) {
                    // Verificar si la propiedad proveedorlocal existe
                    $proveedorlocal = property_exists($item, 'proveedorlocal') ? $item->proveedorlocal : 'Provincial';

                    if ($proveedorlocal === 'Mixto' || $proveedorlocal === 'Local') {
                        return $proveedorlocal;
                    } else {
                        return 'Provincial';
                    }
                });

                // Reorganizar los grupos: Mixto y Local primero, Provincial al final
                $sortedGroups = $groupedResultado->sortByDesc(function ($group, $key) {
                    return $key === 'Provincial' ? 1 : 0;
                });

                // Aplanar la colección para que vuelva a ser una lista simple
                $resultadoFinal = $sortedGroups->flatten();
            @endphp

            {{-- Mostrar los datos --}}
            @php $conteoGeneral = 1; @endphp
            @foreach($resultadoFinal as $index => $r)
                @php
                    $usuarios = "-";
                    $usuarios2 = "-";
                    if ($r->id_users) {
                        $e = \Illuminate\Support\Facades\DB::table('users')->where('id_users', '=', $r->id_users)->first();
                        if ($e) {
                            $usuarios = $e->name . ' ' . $e->last_name;
                        }
                    }
                    if ($r->id_users_programacion) {
                        $e2 = \Illuminate\Support\Facades\DB::table('users')->where('id_users', '=', $r->id_users_programacion)->first();
                        if ($e2) {
                            $usuarios2 = $e2->name . ' ' . $e2->last_name;
                        }
                    }
                    $general = new \App\Models\General();
                    $fe = $general->obtenerNombreFecha($r->programacion_fecha, 'Date', 'Date');
                    $fc = $general->obtenerNombreFecha($r->created_at, 'DateTime', 'DateTime');
                    $fa = $general->obtenerNombreFecha($r->programacion_fecha_aprobacion, 'DateTime', 'DateTime');
                @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }}" wire:ignore.self type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{ $index }}" aria-expanded="true" aria-controls="collapseOne_{{ $index }}">
                            #{{ $conteoGeneral }} | FD: {{ $fe }} | UR: {{ $usuarios }} | FC: {{ $fc }} | FA: {{ $fa }} | N° C: {{ $r->programacion_numero_correlativo }}
                        </button>
                    </h2>
                    <div id="collapseOne_{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" data-bs-parent="#accordionExample" wire:ignore.self>
                        <div class="accordion-body">
                            <div class="row">
                                @if($roleId == 1 || $roleId == 2)
                                    @php
                                        $conteoRetornar = 0;
                                        foreach ($r->despacho as $des) {
                                            if ($des->despacho_estado_aprobacion != 1) {
                                                $conteoRetornar++;
                                            }
                                        }
                                    @endphp
                                    @if($conteoRetornar == 0)
                                        <div class="col-lg-12 col-md-12 col-sm-12 text-end mb-4">
                                            <button class="btn btn-secondary text-white btn-sm" wire:click="retornarProgamacionApro({{ $r->id_programacion }})" data-bs-toggle="modal" data-bs-target="#modalRetornarPendiente">
                                                <i class="fa-solid fa-arrow-left"></i> Retornar a Programaciones Pendientes
                                            </button>
                                        </div>
                                    @endif
                                @endif

                                <div class="col-lg-12 col-md-12 col-sm-12 table-responsive">
                                    <table class="table">
                                        <thead>
                                        <tr style="background: #f5f5f9">
                                            <th></th>
                                            <th>N°</th>
                                            <th>Servicio</th>
                                            <th>Orden Servicio</th>
                                            <th>Proveedor</th>
                                            <th>Importe Total</th>
                                            <th>Peso</th>
                                            <th>Llenado en Peso</th>
                                            <th>Cambio de Tarifa</th>
                                            <th>Costo Flete</th>
                                            <th>Flete / Venta</th>
                                            <th>Flete / Peso</th>
                                            <th>Estado Despacho</th>
                                            <th>Acciones</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($r->despacho) > 0)
                                            @php $conteoGeneral2 = 1; @endphp
                                            @foreach($r->despacho as $des)
                                                <tr>
                                                    <td>
                                                        @if($des->despacho_estado_aprobacion == 1)
                                                            <input type="checkbox" wire:model.live="selectedItems" value="{{ $des->id_despacho }}" id="checkbox-{{ $des->id_despacho }}" class="form-check-input">
                                                        @endif
                                                    </td>
                                                    <td>{{ $conteoGeneral2 }}</td>
                                                    <td>{{ $des->tipo_servicio_concepto }}</td>
                                                    <td>{{ $des->despacho_numero_correlativo }}</td>
                                                    <td>{{ $des->transportista_nom_comercial }}</td>
                                                    <td>S/ {{ $general->formatoDecimal($des->totalVentaDespacho) }}</td>
                                                    <td>{{ $des->despacho_peso }} kg</td>
                                                    @php
                                                        $indi = "";
                                                        if ($des->id_vehiculo) {
                                                            $vehi = \Illuminate\Support\Facades\DB::table('vehiculos')->where('id_vehiculo', '=', $des->id_vehiculo)->first();
                                                            $indi = ($des->despacho_peso / $vehi->vehiculo_capacidad_peso) * 100;
                                                            $indi = $general->formatoDecimal($indi);
                                                        } else {
                                                            $indi = "-";
                                                        }
                                                    @endphp
                                                    <td style="color: {{ $general->obtenerColorPorPorcentaje($indi) }}">{{ $indi > 0 ? $indi . '%' : '-' }}</td>
                                                    @php
                                                        $styleColor = "text-danger";
                                                        if ($des->despacho_estado_modificado == 1) {
                                                            $styleColor = "text-success";
                                                        }
                                                    @endphp
                                                    <td><b class="{{ $styleColor }}">{{ $des->despacho_estado_modificado == 1 ? 'SI' : 'NO' }}</b></td>
                                                    <td>
                                                        <span class="{{ $des->despacho_estado_modificado == 1 ? 'text-danger' : '' }}">S/ {{ $des->despacho_flete }}</span>
                                                        <b class="{{ $styleColor }}">
                                                            {{ $des->despacho_estado_modificado == 1 ? '=> S/ ' . $des->despacho_monto_modificado : '' }}
                                                        </b>
                                                    </td>
                                                    @php
                                                        $ra = 0;
                                                        if ($des->despacho_costo_total && $des->totalVentaDespacho > 0) {
                                                            $to = ($des->despacho_costo_total / $des->totalVentaDespacho) * 100;
                                                            $ra = $general->formatoDecimal($to);
                                                        }
                                                    @endphp
                                                    <td>{{ $ra }} %</td>
                                                    @php
                                                        $ra2 = 0;
                                                        if ($des->despacho_costo_total) {
                                                            $to = $des->despacho_costo_total / $des->despacho_peso;
                                                            $ra2 = $general->formatoDecimal($to);
                                                        }
                                                    @endphp
                                                    <td>{{ $ra2 }}</td>
                                                    <td>
                                                        @php
                                                            $colorBadge = "";
                                                            if ($des->despacho_estado_aprobacion == 1) {
                                                                $colorBadge = "bg-label-warning";
                                                            } elseif ($des->despacho_estado_aprobacion == 2) {
                                                                $colorBadge = "bg-label-primary";
                                                            } elseif ($des->despacho_estado_aprobacion == 3) {
                                                                $colorBadge = "bg-label-success";
                                                            } else {
                                                                $colorBadge = "bg-label-danger";
                                                            }
                                                        @endphp
                                                        <span class="font-bold badge {{ $colorBadge }}">
                                                            {{ $des->despacho_estado_aprobacion == 1 ? 'APROBADO ' : '' }}
                                                            {{ $des->despacho_estado_aprobacion == 2 ? 'EN CAMINO ' : '' }}
                                                            {{ $des->despacho_estado_aprobacion == 3 ? 'CULMINADO' : '' }}
                                                            {{ $des->despacho_estado_aprobacion == 4 ? 'RECHAZADO' : '' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm text-primary" wire:click="listar_informacion_despacho({{ $des->id_despacho }})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </button>
                                                        @if($des->despacho_estado_aprobacion == 1)
                                                            <button class="btn btn-sm text-warning" wire:click="cambiarEstadoDespacho({{ $des->id_despacho }})" data-bs-toggle="modal" data-bs-target="#modalAprobarProgramacion">
                                                                <i class="fa fa-car-side"></i>
                                                            </button>
                                                        @endif
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
        @else
            <p class="text-center">Registros Insuficientes</p>
        @endif
    </div>

    {{-- Mostrar enlaces de paginación --}}
    <div class="mt-4">
        {{ $resultado->links() }}
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
    $wire.on('hideModalDeleteRetornar', () => {
        $('#modalRetornarPendiente').modal('hide');
    });
</script>
@endscript


