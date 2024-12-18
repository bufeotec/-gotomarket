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
                                                <th>Número Documento</th>
                                                <th>Fecha Emision</th>
                                                <th>Cliente</th>
                                                <th>Guía de Remisión</th>
                                                <th>Importe Venta</th>
                                                <th>Peso Kilos</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if(count($listar_detalle_despacho->comprobantes) > 0)
                                                @php $conteo = 1; @endphp
                                                @foreach($listar_detalle_despacho->comprobantes as $ta)
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



    <div class="row align-items-center mt-2">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_desde" class="form-label">Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" wire:model.live="desde" class="form-control">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_hasta" class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model.live="hasta" class="form-control">
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
                <h6 class="m-0">FE : Fecha de Entrega</h6>
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
                        #{{$conteoGeneral}} | FE : {{$fe}} | UR : {{$usuarios}} | FC : {{$fc}} | E : {{$r->programacion_estado == 1 ? 'ACTIVO' : 'DESHABILITADO'}}
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
                                <a class="btn btn-sm text-white bg-primary" href="{{route('Programacioncamion.editar_programacion',['data'=>base64_encode($r->id_programacion)])}}"><i class="fa-solid fa-pencil"></i> EDITAR</a>
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
                                                <td>{{$des->despacho_peso}} kg</td>
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
                                                        $to = $des->despacho_costo_total / $des->totalVentaDespacho;
                                                        $ra = $general->formatoDecimal($to);
                                                    }
                                                @endphp
                                                <td>{{$ra}}</td>
                                                @php
                                                    $ra2 = 0;
                                                    if ($des->despacho_costo_total){
                                                        $to = $des->despacho_costo_total / $des->despacho_peso;
                                                        $ra2 = $general->formatoDecimal($to);
                                                    }
                                                @endphp
                                                <td>{{$ra2}}</td>
                                                <td>
                                                    <button class="btn btn-sm text-primary" wire:click="listar_informacion_despacho({{$des->id_despacho}})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">
                                                        <i class="fa-solid fa-eye"></i>
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
</script>
@endscript


