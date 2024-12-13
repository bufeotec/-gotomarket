<div>

    @php
        $general = new \App\Models\General();
    @endphp

    {{--    MODAL DETALLE DESPACHO LIQUIDACION --}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleLiquidacion</x-slot>
        <x-slot name="titleModal">Detalles de La Liquidación</x-slot>
        <x-slot name="modalContent">
            @if($listar_detalle_liquidacion && $listar_detalle_liquidacion->count() > 0)
                <div class="modal-body">
                    <div class="accordion" id="accordionExample">
                        @php $conteo = 1; @endphp
                        @foreach($listar_detalle_liquidacion  as $index => $liquidacion)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $conteo }}">
                                    <button class="accordion-button {{$conteo == 1 ? '' : 'collapsed'}}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $conteo }}" aria-expanded="true" aria-controls="collapse{{ $conteo }}" wire:ignore.self>
                                        #{{ $conteo }} | Despacho: {{ $liquidacion->despacho_numero_correlativo ?? 'N/A' }}
                                    </button>
                                </h2>
                                <div id="collapse{{ $conteo }}" class="accordion-collapse collapse {{ $conteo == 1 ? 'show' : '' }}" aria-labelledby="heading{{ $conteo }}" data-bs-parent="#accordionExample" wire:ignore.self >
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h6>Información Adicional del Despacho</h6>
                                                <hr>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                                <strong class="colorgotomarket mb-2">Usuario de Registro</strong>
                                                <p>{{ $liquidacion->name }}</p>
                                            </div>
                                            @if($liquidacion->id_vehiculo)
                                                @php
                                                    $vehiculo = \Illuminate\Support\Facades\DB::table('vehiculos')->where('id_vehiculo','=',$liquidacion->id_vehiculo)->first();
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
                                            @if($liquidacion->id_tipo_servicios == 2)
                                                @php
                                                    $departamento = \Illuminate\Support\Facades\DB::table('departamentos')->where('id_departamento','=',$liquidacion->id_departamento)->first();
                                                    $provincia = \Illuminate\Support\Facades\DB::table('provincias')->where('id_provincia','=',$liquidacion->id_provincia)->first();
                                                    $distrito = \Illuminate\Support\Facades\DB::table('distritos')->where('id_distrito','=',$liquidacion->id_distrito)->first();
                                                @endphp
                                                <div class="col-lg-5 col-md-3 col-sm-4 mb-3">
                                                    <strong class="colorgotomarket mb-2">Ubigeo Seleccionado en el Despacho:</strong>
                                                    <p>{{ $departamento ? $departamento->departamento_nombre : '' }} - {{ $provincia ? $provincia->provincia_nombre : '' }} - {{ $distrito ? $distrito->distrito_nombre : 'TODOS LOS DISTRITOS' }}</p>
                                                </div>
                                            @endif
                                            @if($liquidacion->id_tarifario)
                                                <div class="col-lg-3 col-md-3 col-sm-4 mb-3">
                                                    <strong class="colorgotomarket mb-2">Capacidad de la Tarifa:</strong>
                                                    <p>Min: {{$general->formatoDecimal($liquidacion->despacho_cap_min)}} Kg - Max: {{ $general->formatoDecimal($liquidacion->despacho_cap_max) }} Kg</p>
                                                </div>
                                            @endif
                                            <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                                <strong class="colorgotomarket mb-2">Monto de la Tarifa:</strong>
                                                <p>S/ {{$general->formatoDecimal($liquidacion->despacho_flete)}}</p>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                                <strong class="colorgotomarket mb-2">Peso del Despacho:</strong>
                                                <p>{{$general->formatoDecimal($liquidacion->despacho_peso)}} Kg</p>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                                <strong class="colorgotomarket mb-2">Otros Gastos:</strong>
                                                <p>S/ {{$general->formatoDecimal($liquidacion->despacho_gasto_otros)}}</p>
                                            </div>
                                            @if($liquidacion->despacho_gasto_otros > 0)
                                                <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                                                    <strong class="colorgotomarket mb-2">Descripción del Gasto:</strong>
                                                    <p>{{ $liquidacion->despacho_descripcion_otros }}</p>
                                                </div>
                                            @endif
                                            @if($liquidacion->id_tipo_servicios == 1)
                                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                                    <strong class="colorgotomarket mb-2">Mano de Obra:</strong>
                                                    <p>S/ {{$general->formatoDecimal($liquidacion->despacho_ayudante)}}</p>
                                                </div>
                                            @endif
                                            <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                                <strong class="colorgotomarket mb-2">Total de Despacho:</strong>
                                                <p>S/ {{ $general->formatoDecimal($liquidacion->despacho_costo_total) }}</p>
                                            </div>
                                            @if($liquidacion->despacho_estado_modificado == 1)
                                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                                    <strong class="colorgotomarket mb-2">Monto Modificado:</strong>
                                                    <p>S/ {{ $general->formatoDecimal($liquidacion->despacho_monto_modificado) }}</p>
                                                </div>
                                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                                    <strong class="colorgotomarket mb-2">Descripción:</strong>
                                                    <p>{{ $liquidacion->despacho_descripcion_modificado }}</p>
                                                </div>
                                            @endif
                                            <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                                @php
                                                    $ra = 0;
                                                    if ($liquidacion->despacho_costo_total && isset($liquidacion->totalVentaDespacho) && $liquidacion->totalVentaDespacho > 0) {
                                                        $to = $liquidacion->despacho_costo_total / $liquidacion->totalVentaDespacho;
                                                        $ra = $general->formatoDecimal($to);
                                                    }
                                                @endphp
                                                <strong class="colorgotomarket mb-2">Flete / Venta:</strong>
                                                <p>{{$ra}}</p>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                                @php
                                                    $ra2 = 0;
                                                    if ($liquidacion->despacho_costo_total && isset($liquidacion->despacho_peso) && $liquidacion->despacho_peso > 0) {
                                                        $to = $liquidacion->despacho_costo_total / $liquidacion->despacho_peso;
                                                        $ra2 = $general->formatoDecimal($to);
                                                    }
                                                @endphp
                                                <strong class="colorgotomarket mb-2">Flete / Peso:</strong>
                                                <p>{{$ra2}}</p>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                                @php
                                                    $indi = "";
                                                    if ($liquidacion->id_vehiculo){
                                                        $vehi = \Illuminate\Support\Facades\DB::table('vehiculos')->where('id_vehiculo','=',$liquidacion->id_vehiculo)->first();
                                                        $indi = ($liquidacion->despacho_peso / $vehi->vehiculo_capacidad_peso) * 100;
                                                        $indi = $general->formatoDecimal($indi);
                                                    }else{
                                                        $indi = "-";
                                                    }
                                                @endphp
                                                <strong class="colorgotomarket mb-2">Llenado en Peso:</strong>
                                                <p style="color: {{$general->obtenerColorPorPorcentaje($indi)}}">{{ $indi > 0 ? $indi.'%' : '-' }}</p>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                                @php
                                                    $totalMontoLiquidacion = \Illuminate\Support\Facades\DB::table('liquidacion_gastos')
                                                    ->where('id_liquidacion_detalle', '=', $liquidacion->id_liquidacion_detalle)
                                                    ->sum('liquidacion_gasto_monto');
                                                @endphp
                                                <strong class="colorgotomarket mb-2">Monto de la liquidación:</strong>
                                                <p>
                                                    S/ {{ $general->formatoDecimal($totalMontoLiquidacion) }}
                                                </p>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                                @php
                                                    $sumaTotal = $totalMontoLiquidacion + $liquidacion->despacho_costo_total;
                                                @endphp
                                                <strong class="colorgotomarket mb-2">Suma total:</strong>
                                                <p>
                                                    S/ {{ $general->formatoDecimal($sumaTotal) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h6>Información Adicional de la liquidación</h6>
                                                <hr>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="row">
                                                    <x-table-general>
                                                        <x-slot name="thead">
                                                            <tr>
                                                                <th>N°</th>
                                                                <th>Concepto</th>
                                                                <th>Monto</th>
                                                                <th>Descripción</th>
                                                            </tr>
                                                        </x-slot>
                                                        <x-slot name="tbody">
                                                            @if(isset($liquidacion->gastos) && count($liquidacion->gastos) > 0)
                                                                @php $conteoGastos = 1; @endphp
                                                                @foreach($liquidacion->gastos as $gs)
                                                                    <tr>
                                                                        <td>{{ $conteoGastos }} </td>
                                                                        <td>{{ $gs->liquidacion_gasto_concepto }} </td>
                                                                        <td>S/ {{ $general->formatoDecimal($gs->liquidacion_gasto_monto) }} </td>
                                                                        <td>{{ $gs->liquidacion_gasto_descripcion ?? '-' }}</td>
                                                                    </tr>
                                                                    @php $conteoGastos++; @endphp
                                                                @endforeach
                                                            @else
                                                                <tr><td colspan="4">No hay gastos registrados</td></tr>
                                                            @endif
                                                        </x-slot>
                                                    </x-table-general>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php $conteo++; @endphp
                        @endforeach
                    </div>
                </div>
            @else
                <div class="modal-body">
                    <p>No se encontraron detalles de liquidación.</p>
                </div>
            @endif
        </x-slot>
    </x-modal-general>

    {{--    MODAL AGREGAR COMPROBANTE --}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalAgregarComprobante</x-slot>
        <x-slot name="titleModal">Agregar comprobante</x-slot>
        <x-slot name="modalContent">
            <form wire:submit="guardar_comprobante">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                        <label for="liquidacion_ruta_comprobante" class="form-label">Comprobante</label>
                        <input type="file" class="form-control" id="liquidacion_ruta_comprobante" name="liquidacion_ruta_comprobante" wire:model="liquidacion_ruta_comprobante">
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" >Guardar</button>
                </div>
            </form>

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

{{--        FECHA DESDE Y HASTA--}}
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

{{--        TABLA DE LIQUDACION--}}
        <x-card-general-view>
            <x-slot name="content">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>N°</th>
                                    <th>Usuario responsable</th>
                                    <th>Transportista</th>
                                    <th>Serie</th>
                                    <th>Correlativo</th>
                                    <th>Comprobante</th>
                                    <th>Acciones</th>
                                </tr>
                            </x-slot>

                            <x-slot name="tbody">
                                @if(count($resultado) > 0)
                                    @php $conteo = 1; @endphp
                                    @foreach($resultado as $rs)
                                        <tr>
                                            <td>{{$conteo}}</td>
                                            <td>{{$rs->name}}</td>
                                            <td>{{$rs->transportista_razon_social}}</td>
                                            <td>{{$rs->liquidacion_serie}}</td>
                                            <td>{{$rs->liquidacion_correlativo}}</td>
                                            <td>
                                                @if(file_exists($rs->liquidacion_ruta_comprobante))
                                                    <a href="{{asset($rs->liquidacion_ruta_comprobante)}}" class="btn btn-warning text-white btn-sm" target="_blank">
                                                        Ver Comprobante <i class="fas fa-file-invoice"></i>
                                                    </a>
                                                @else
                                                    <span class="font-bold badge bg-label-success curso-pointer" wire:click="agregar_comprobante('{{ base64_encode($rs->id_liquidacion) }}')" data-bs-toggle="modal" data-bs-target="#modalAgregarComprobante" >
                                                        Agregar comprobante <i class="fa-solid fa-file-circle-plus ms-1"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm text-primary" wire:click="listar_informacion_liquidacion({{$rs->id_liquidacion}})" data-bs-toggle="modal" data-bs-target="#modalDetalleLiquidacion">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @php $conteo++; @endphp
                                    @endforeach
                                @else
                                    <tr class="odd">
                                        <td valign="top" colspan="9" class="dataTables_empty text-center">
                                            No se han encontrado resultados.
                                        </td>
                                    </tr>
                                @endif
                            </x-slot>
                        </x-table-general>
                    </div>
                </div>
            </x-slot>
        </x-card-general-view>

</div>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalAgregarComprobante').modal('hide');
    });
</script>
@endscript
