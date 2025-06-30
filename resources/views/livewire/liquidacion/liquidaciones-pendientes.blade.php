<div>
    @php
        $general = new \App\Models\General();
    @endphp

    {{-- AGREGAR COMENTARIO --}}
    <x-modal-general  wire:ignore.self >
        {{--        <x-slot name="tama">modal-lg</x-slot>--}}
        <x-slot name="id_modal">modalComentarioLiquidacion</x-slot>
        <x-slot name="titleModal">Gestionar Observación</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveObseracion">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <label for="liquidacion_observacion">Observación</label>
                        <textarea name="liquidacion_observacion" class="form-control" id="liquidacion_observacion" rows="2" wire:model="liquidacion_observacion"></textarea>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-4">
                        <button class="btn btn-primary text-white btn-sm w-100" type="submit">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
    {{-- MODAL DETALLE DESPACHO --}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleDespacho</x-slot>
        <x-slot name="titleModal">Información de Orden de Servicio</x-slot>
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
                                    <strong class="colorgotomarket mb-2">Total Venta Despachada:</strong>
                                    <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->totalVentaDespacho) }}</p>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2 d-block">Peso Total:</strong>
                                    <span class="mb-0">{{ $general->formatoDecimal($listar_detalle_despacho->despacho_peso) }} Kg</span>

                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Total Despacho:</strong>
                                    <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->despacho_costo_total) }}</p>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Flete / Venta</strong>
                                    <p>{{$listar_detalle_despacho->totalVentaDespacho != 0 ? $general->formatoDecimal(($listar_detalle_despacho->despacho_costo_total / $listar_detalle_despacho->totalVentaDespacho) * 100) : 0 }} %</p>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Flete / Peso</strong>
                                    <p>{{$listar_detalle_despacho->despacho_peso != 0 ? $general->formatoDecimal($listar_detalle_despacho->despacho_costo_total / $listar_detalle_despacho->despacho_peso) : 0 }}</p>
                                </div>
                            </div>
                            <div class="row">
                                @php
                                    $totalVentaDespaDespachoModal = $listar_detalle_despacho->totalVentaDespacho;
                                   if ($listar_detalle_despacho->totalVentaNoEntregado){
                                       $totalVentaDespaDespachoModal = $listar_detalle_despacho->totalVentaDespacho - $listar_detalle_despacho->totalVentaNoEntregado;
                                   }

                                   $totalPesoDespachoModal = $listar_detalle_despacho->despacho_peso;
                                   if ($listar_detalle_despacho->totalPesoNoEntregado){
                                      $totalPesoDespachoModal = $listar_detalle_despacho->despacho_peso - $listar_detalle_despacho->totalPesoNoEntregado;
                                   }

                                  $despachoGeneraLiquidacionModal = 0;
                                  if ($listar_detalle_despacho->id_tipo_servicios == 1){
                                      $despachoGeneraLiquidacionModal = $listar_detalle_despacho->despacho_costo_total;
                                  }else{
                                      $despachoGeneraLiquidacionModal = ($listar_detalle_despacho->despacho_monto_modificado * $totalPesoDespachoModal) + $listar_detalle_despacho->despacho_ayudante + $listar_detalle_despacho->despacho_gasto_otros;
                                  }
                                @endphp
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <h6>Resumen Completo del Despacho Con Facturas Entregadas</h6>
                                            <hr>
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                                            <strong class="colorgotomarket mb-2 d-block">Total Venta Despachada:</strong>
                                            <span class="mb-0">S/ {{ $general->formatoDecimal($listar_detalle_despacho->totalVentaDespacho) }}</span>
                                            @if($listar_detalle_despacho->totalVentaNoEntregado)
                                                <span class="text-danger mb-0">S/ -{{ $general->formatoDecimal($listar_detalle_despacho->totalVentaNoEntregado) }}</span>
                                                <p class="colorBlackComprobantes">S/ {{ $general->formatoDecimal($listar_detalle_despacho->totalVentaDespacho - $listar_detalle_despacho->totalVentaNoEntregado) }}</p>
                                            @endif
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                                            <strong class="colorgotomarket mb-2 d-block">Peso Total:</strong>
                                            <span class="mb-0">{{ $general->formatoDecimal($listar_detalle_despacho->despacho_peso) }} {{$listar_detalle_despacho->totalPesoNoEntregado ? '' :'Kg'}}</span>
                                            @if($listar_detalle_despacho->totalPesoNoEntregado)
                                                <span class="text-danger mb-0">-{{ $general->formatoDecimal($listar_detalle_despacho->totalPesoNoEntregado) }}</span>
                                                <p class="colorBlackComprobantes">{{ $general->formatoDecimal($listar_detalle_despacho->despacho_peso - $listar_detalle_despacho->totalPesoNoEntregado) }} Kg</p>
                                            @endif
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                            <strong class="colorgotomarket mb-2">Total Despacho:</strong>
                                            <p>S/ {{ $general->formatoDecimal($despachoGeneraLiquidacionModal) }}</p>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                            <strong class="colorgotomarket mb-2">Flete / Venta</strong>
                                            <p>{{$totalVentaDespaDespachoModal != 0 ? $general->formatoDecimal(($despachoGeneraLiquidacionModal / $totalVentaDespaDespachoModal) * 100) : 0 }} %</p>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                            <strong class="colorgotomarket mb-2">Flete / Peso</strong>
                                            <p>{{$totalPesoDespachoModal != 0 ? $general->formatoDecimal($despachoGeneraLiquidacionModal / $totalPesoDespachoModal) : 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    @php
                                        $gastosLiquidacionOS = [];
                                        $informacionLiquidacion = "";
                                        if ($listar_detalle_despacho->id_liquidacion_detalle){
                                            $gastosLiquidacionOS =  \Illuminate\Support\Facades\DB::table('liquidacion_gastos')->where('id_liquidacion_detalle','=',$listar_detalle_despacho->id_liquidacion_detalle)->get();
                                            $informacionLiquidacion =  \Illuminate\Support\Facades\DB::table('liquidacion_detalles')->where('id_liquidacion_detalle','=',$listar_detalle_despacho->id_liquidacion_detalle)->first();
                                        }
                                    @endphp
                                    @if($informacionLiquidacion)
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <h6>Resumen Liquidación</h6>
                                                <hr>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                                <x-table-general>
                                                    <x-slot name="thead">
                                                        <tr>
                                                            <th>Costo de Tarifa</th>
                                                            <th>Mano de Obra</th>
                                                            <th>Otros Gastos</th>
                                                            <th>Peso Total</th>
                                                            <th>Total Servicio</th>
                                                            <th>Flete / Venta</th>
                                                            <th>Flete / Peso</th>
                                                        </tr>
                                                    </x-slot>

                                                    <x-slot name="tbody">
                                                        @if(count($gastosLiquidacionOS) > 0)
                                                            <tr>
                                                                @php
                                                                    $costoTari = $gastosLiquidacionOS[0]->liquidacion_gasto_monto;
                                                                    $manoObra = $gastosLiquidacionOS[1]->liquidacion_gasto_monto;
                                                                    $otrosGastos = $gastosLiquidacionOS[2]->liquidacion_gasto_monto;
                                                                    $pesoFiL = $gastosLiquidacionOS[3]->liquidacion_gasto_monto;
                                                                    $totalDespachoLiqui = 0;
                                                                    if ($listar_detalle_despacho->id_tipo_servicios == 1){
                                                                        $totalDespachoLiqui = $costoTari + $manoObra + $otrosGastos;
                                                                    }else{
                                                                        $totalDespachoLiqui = ($costoTari * $pesoFiL) + $manoObra + $otrosGastos;
                                                                    }
                                                                @endphp
                                                                @foreach($gastosLiquidacionOS  as $ind => $gas)
                                                                    <td>
                                                                        <span>{{$ind <=2 ? 'S/' : ''}} {{$gas->liquidacion_gasto_monto ? $general->formatoDecimal($gas->liquidacion_gasto_monto) : 0}} {{$ind <=2 ? '' : 'Kg'}}</span>
                                                                        <p class="mt-2">
                                                                            <b class="colorBlackComprobantes">{{$gas->liquidacion_gasto_descripcion}}</b>
                                                                        </p>
                                                                    </td>
                                                                @endforeach
                                                                <td>S/ {{ $general->formatoDecimal($totalDespachoLiqui) }}</td>
                                                                <td>{{ $totalVentaDespaDespachoModal != 0 ? $general->formatoDecimal(($totalDespachoLiqui / $totalVentaDespaDespachoModal) * 100) : 0 }} %</td>
                                                                <td>{{ $pesoFiL != 0 ? $general->formatoDecimal($totalDespachoLiqui / $pesoFiL) : 0 }}</td>
                                                            </tr>
                                                        @endif
                                                    </x-slot>
                                                </x-table-general>
                                            </div>
                                            <div class="col-lg-12 mb-2">
                                                <strong class="colorgotomarket mb-2">Comentario General:</strong>
                                                <p>{{ $informacionLiquidacion->liquidacion_detalle_comentarios ? $informacionLiquidacion->liquidacion_detalle_comentarios : 'Sin Comentarios' }}</p>
                                            </div>
                                        </div>
                                    @endif
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
                                                            <span class="font-bold badge  {{$ta->guia_estado_aprobacion == 8 ? 'bg-label-success' : 'bg-label-danger'}}">
                                                                {{$ta->guia_estado_aprobacion == 8 ? 'ENTREGADO ' : 'NO ENTREGADO'}}
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
    {{-- MODAL GUIAS--}}
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
    {{-- MODAL AGREGAR COMPROBANTE--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalAgregarComprobante</x-slot>
        <x-slot name="titleModal">Agregar comprobante</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="guardar_comprobante_new">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                        <label for="liquidacion_ruta_comprobante" class="form-label">Comprobante</label>
                        <input type="file" class="form-control" id="liquidacion_ruta_comprobante" name="liquidacion_ruta_comprobante" wire:model="liquidacion_ruta_comprobante">
                    </div>
                    @error('liquidacion_ruta_comprobante') <span class="message-error">{{ $message }}</span> @enderror
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" >Guardar</button>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
    {{-- APROBAR --}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalAprobarLiquidacion</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiarEstadoLiquidacionFormulario">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if($estado_liquidacion == 1)
                            <h2 class="deleteTitle">¿Está seguro de aprobar esta liquidación?</h2>
                        @else
                            <h2 class="deleteTitle">¿Está seguro de rechazar esta liquidación?</h2>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_liqui') <span class="message-error">{{ $message }}</span> @enderror
                        @error('estado_liquidacion') <span class="message-error">{{ $message }}</span> @enderror

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


    <div class="row align-items-center mt-2">
        <div class="col-lg-3 col-md-2 col-sm-12 mb-2">
            <label for="buscar_search" class="form-label">Buscar</label>
            <x-input-general  type="text" id="buscar_search" wire:model.live="search" />
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_desde" class="form-label">Desde</label>
            <x-input-general  type="date" id="fecha_desde" wire:model.live="desde" />
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_hasta" class="form-label">Hasta</label>
            <x-input-general  type="date" id="fecha_hasta" wire:model.live="hasta" />
        </div>
    </div>

    @if(count($resultado) > 0)
        <div class="row mt-4">
            <div class="col-lg col-md-3 col-sm-3 mb-1">
                <h6 class="m-0">FR : Fecha de Registro</h6>
            </div>
            <div class="col-lg col-md-3 col-sm-3 mb-1">
                <h6 class="m-0">UR : Usuario de Registro</h6>
            </div>
            <div class="col-lg col-md-3 col-sm-3 mb-1">
                <h6 class="m-0">TR : Transportista</h6>
            </div>
            <div class="col-lg col-md-3 col-sm-3 mb-1">
                <h6 class="m-0">SC : Serie y Correlativo</h6>
            </div>

        </div>
    @endif

    @php
        // ORDENAR POR FECHA
//        $resultadoOrdenado = collect($resultado)->sortBy('creacion_liquidacion');
    @endphp

    <div class="accordion mt-3" id="accordionExample">
        @if(count($resultado) > 0)
            @php $conteoGeneral = 1; @endphp
            @foreach($resultado as $index => $r)
                <div were:key="{{$r->id_liquidacion}}" class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{$index == 0 ? '' : 'collapsed'}}" wire:ignore.self type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{$index}}" aria-expanded="true" aria-controls="collapseOne_{{$index}}">
                            #{{$conteoGeneral}} | FR : {{$r->creacion_liquidacion}} | UR : {{$r->name}} | TR : {{$r->transportista_razon_social}} | SC : {{$r->liquidacion_serie}} - {{$r->liquidacion_correlativo}}
                        </button>
                    </h2>
                    <div id="collapseOne_{{$index}}" class="accordion-collapse collapse {{$index == 0 ? 'show' : ''}}" data-bs-parent="#accordionExample" wire:ignore.self>
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 text-end mb-4">
                                    @php
                                        $user = \Illuminate\Support\Facades\Auth::user(); // Obtiene el usuario autenticado
                                        $roleId = $user->roles->first()->id ?? null;
                                    @endphp
{{--                                    @if($roleId == 1 || $roleId == 2)--}}
                                        <button class="btn btn-sm text-white bg-success" wire:click="cambiarEstadoLiquidacion({{$r->id_liquidacion}},1)" data-bs-toggle="modal" data-bs-target="#modalAprobarLiquidacion"><i class="fa-solid fa-check"></i> APROBAR</button>
                                        <button class="btn btn-sm text-white bg-warning" wire:click="gestionObservacionLiquidacion({{$r->id_liquidacion}})" data-bs-toggle="modal" data-bs-target="#modalComentarioLiquidacion"><i class="fa fa-eye"></i> OBSERVAR</button>
                                        <button class="btn btn-sm text-white bg-danger" wire:click="cambiarEstadoLiquidacion({{$r->id_liquidacion}},2)" data-bs-toggle="modal" data-bs-target="#modalAprobarLiquidacion"><i class="fa fa-x"></i> RECHAZAR</button>
{{--                                    @endif--}}

                                    @php
                                        if (Gate::allows('editar_fletes')) {
                                            @endphp
                                                <a class="btn btn-sm text-white bg-primary" href="{{route('Despachotransporte.editar_liquidaciones',['data'=>base64_encode($r->id_liquidacion)])}}"><i class="fa-solid fa-pencil"></i> EDITAR</a>
                                            @php
                                        }
                                    @endphp



                                    @if(file_exists($r->liquidacion_ruta_comprobante))
                                        <a class="btn btn-sm text-white bg-secondary" href="{{asset($r->liquidacion_ruta_comprobante)}}" target="_blank"><i class="fa-solid fa-eye"></i> VER DOCUMENTO</a>
                                    @else
                                        <button class="btn btn-sm text-white bg-secondary" wire:click="agregar_comprobante({{$r->id_liquidacion}})" data-bs-toggle="modal" data-bs-target="#modalAgregarComprobante"><i class="fa-solid fa-file-circle-plus"></i> ADJUNTAR DOCUMENTO</button>
                                    @endif
                                </div>
                                @if($r->liquidacion_observaciones)
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <label for="">Observaciones:</label>
                                        <p>{{$r->liquidacion_observaciones}}</p>
                                    </div>
                                @endif
                                <div class="col-lg-12 col-md-12 col-sm-12 table-responsive">
                                    <table class="table table-bordered">
                                        @php
                                            $colorMontoProgramado = "";
                                            $colorMontoLiquidado = "";
                                            $sumaImporteServicio = 0; // Variable para la suma del importe del servicio por acordeón
                                        @endphp
                                        <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>N° OS</th>
                                            <th>Proveedor</th>
                                            <th>Servicio</th>
                                            <th>Fecha de Despacho</th>
                                            <th>Guías Asociadas</th>
                                            <th>Total Venta Despachada</th>
                                            <th>Peso Total</th>
                                            <th>Importe Programado del Servicio</th>
                                            <th>Cambio de Tarifa</th>
                                            <th style="{{$colorMontoLiquidado}}">Importe del Servicio</th>
                                            <th style="{{$colorMontoLiquidado}}">Flete / Venta</th>
                                            <th style="{{$colorMontoLiquidado}}">Flete / Peso</th>
                                            <th>Acción</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $conteo = 1;
                                        @endphp
                                        @foreach($r->detalles as $de)
                                            <tr>
                                                <th>{{$conteo}}</th>
                                                <th>{{$de->despacho_numero_correlativo}}</th>
                                                <td>{{ $r->transportista_razon_social }}</td>
                                                <td>{{ $de->tipo_servicio_concepto }}</td>
                                                <td>{{ date('d-m-Y',strtotime($de->programacion_fecha)) }}</td>
                                                <td>
                                                    @php
                                                        $guiasComprobante = \Illuminate\Support\Facades\DB::table('despacho_ventas as dv')->join('guias as g','dv.id_guia','=','g.id_guia')->where('dv.id_despacho', '=', $de->id_despacho)->get();
                                                        $totalGuias = count($guiasComprobante); // Contamos las guías
                                                    @endphp
                                                    @foreach($guiasComprobante as $indexGuias => $g)
                                                        @if($indexGuias <= 2)
                                                            <a wire:click="listar_guias_despachos({{ $de->id_despacho }})" data-bs-toggle="modal" data-bs-target="#modalDetalleGuias" class="cursoPointer text-primary">
                                                                {{ $general->formatearCodigo($g->guia_nro_doc) }}
                                                            </a>
                                                            @if($indexGuias < 2 && $indexGuias < $totalGuias - 1)
                                                                , <!-- Mostrar la coma solo si no es el último elemento que se va a mostrar -->
                                                            @elseif($indexGuias == 2 && $totalGuias > 3)
                                                                ... <!-- Mostrar "..." si hay más guías después de las tres primeras -->
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                </td>
                                                @php
                                                    $totalVentaDespaDespacho = $de->totalVentaDespacho;
                                                    if ($de->totalVentaNoEntregado){
                                                        $totalVentaDespaDespacho = $de->totalVentaDespacho - $de->totalVentaNoEntregado;
                                                    }
                                                @endphp
                                                <td>
                                                    <span class="d-block">S/ {{ $general->formatoDecimal($de->totalVentaDespacho) }}</span>
                                                    @if($de->totalVentaNoEntregado)
                                                        <span class="d-block text-danger">S/ -{{ $general->formatoDecimal($de->totalVentaNoEntregado) }}</span>
                                                        <b class="colorBlackComprobantes">S/ {{ $general->formatoDecimal($totalVentaDespaDespacho) }}</b>
                                                    @endif
                                                </td>
                                                @php
                                                    $totalPesoDespacho = $de->despacho_peso;
                                                    if ($de->totalPesoNoEntregado){
                                                        $totalPesoDespacho = $de->despacho_peso - $de->totalPesoNoEntregado;
                                                    }
                                                @endphp
                                                <td>
                                                    <span class="d-block">{{ $general->formatoDecimal($de->despacho_peso) }}</span>
                                                    @if($de->totalPesoNoEntregado)
                                                        <span class="d-block text-danger">-{{ $general->formatoDecimal($de->totalPesoNoEntregado) }}</span>
                                                        <b class="colorBlackComprobantes">{{ $general->formatoDecimal($totalPesoDespacho) }} kg</b>
                                                    @endif
                                                </td>
                                                @php
                                                    $despachoGeneraLiquidacion = 0;
                                                    if ($de->id_tipo_servicios == 1){
                                                        $despachoGeneraLiquidacion = $de->despacho_costo_total;
                                                    }else{
                                                        $despachoGeneraLiquidacion = ($de->despacho_monto_modificado * $totalPesoDespacho) + $de->despacho_ayudante + $de->despacho_gasto_otros;
                                                    }
                                                @endphp
                                                <td>S/ {{ $general->formatoDecimal($despachoGeneraLiquidacion) }}</td>
                                                @php
                                                    $styleColor = "text-danger";
                                                    if ($de->despacho_estado_modificado == 1){
                                                        $styleColor = "text-success";
                                                    }
                                                @endphp
                                                <td><b class="{{$styleColor}}">{{$de->despacho_estado_modificado == 1 ? 'SI' : 'NO'}}</b></td>

                                                @php
                                                    $gastosDespachos = \Illuminate\Support\Facades\DB::table('liquidacion_gastos')->where('id_liquidacion_detalle','=',$de->id_liquidacion_detalle)->get();
                                                    $costoTarifa = 0;
                                                    $costoMano = 0;
                                                    $costoOtros = 0;
                                                    $pesoFinalLiquidacion = 0;
                                                    if (count($gastosDespachos) >= 3){
                                                        $costoTarifa = $gastosDespachos[0]->liquidacion_gasto_monto;
                                                        $costoMano = $gastosDespachos[1]->liquidacion_gasto_monto;
                                                        $costoOtros = $gastosDespachos[2]->liquidacion_gasto_monto;
                                                        $pesoFinalLiquidacion = $gastosDespachos[3]->liquidacion_gasto_monto;
                                                    }
                                                @endphp

                                                @php
                                                    $totalDespachoMontoLiquidado = 0;
                                                    if ($de->id_tipo_servicios == 1){
                                                        $totalDespachoMontoLiquidado = $costoTarifa + $costoMano + $costoOtros;
                                                    }else{
                                                        $totalDespachoMontoLiquidado = ($costoTarifa * $pesoFinalLiquidacion) + $costoMano + $costoOtros;
                                                    }
                                                @endphp
                                                <td style="{{$colorMontoLiquidado}}">S/ {{ $general->formatoDecimal($totalDespachoMontoLiquidado) }}</td>
                                                <td style="{{$colorMontoLiquidado}}">{{$totalVentaDespaDespacho != 0 ? $general->formatoDecimal(($totalDespachoMontoLiquidado / $totalVentaDespaDespacho) * 100) : 0 }} % </td>
                                                <td style="{{$colorMontoLiquidado}}">{{$pesoFinalLiquidacion != 0 ? $general->formatoDecimal($totalDespachoMontoLiquidado / $pesoFinalLiquidacion)  : 0 }} </td>
                                                <td>
                                                    <x-btn-accion class="btn btn-sm text-primary" wire:click="listar_informacion_despacho({{ $de->id_despacho }},{{ $de->id_liquidacion }})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">
                                                        <x-slot name="message">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </x-slot>
                                                    </x-btn-accion>
                                                </td>
                                            </tr>
                                            @php
                                                $sumaImporteServicio += $totalDespachoMontoLiquidado; // Acumular el importe del servicio
                                                $conteo++;
                                            @endphp
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <!-- Fila de suma total para -->
                                    <div class="row">
                                        <div class="col-lg-12 text-end mt-2">
                                            <h5>Subtotal Factura: S/ {{ $general->formatoDecimal($sumaImporteServicio) }}</h5>
                                            @php
                                                $igv = $sumaImporteServicio * 0.18; // Calcula el IGV
                                                $totalFactura = $sumaImporteServicio + $igv; // Total factura incluyendo IGV
                                            @endphp
                                            <h5>Factura Total (inc. IGV): S/ {{ $general->formatoDecimal($totalFactura) }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php $conteoGeneral++; @endphp
                    @endforeach
                    @else
                        <p class="text-center"> Registros Insuficientes</p>
                    @endif
                </div>
    </div>
</div>
@script
<script>
    $wire.on('hideModal', () => {
        $('#modalAgregarComprobante').modal('hide');
    });
    $wire.on('hideModalDeleteA', () => {
        $('#modalAprobarLiquidacion').modal('hide');
    });
    $wire.on('hideModalLiquidacionOb', () => {
        $('#modalComentarioLiquidacion').modal('hide');
    });
</script>
@endscript
