<div>

    @php
        $general = new \App\Models\General();
    @endphp

{{--    --}}{{-- MODAL DETALLE LIQUIDACIÓN --}}
{{--    <x-modal-general  wire:ignore.self   >--}}
{{--        <x-slot name="tama">modal-xl</x-slot>--}}
{{--        <x-slot name="id_modal">modalDetalleDespacho</x-slot>--}}
{{--        <x-slot name="titleModal"></x-slot>--}}
{{--        <x-slot name="modalContent">--}}
{{--            @if($listar_detalle_liquidacion)--}}
{{--                <div class="modal-body">--}}
{{--                    <div class="row mb-2">--}}
{{--                        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                            <h5>Información de liquidación</h5>--}}
{{--                            <hr>--}}
{{--                        </div>--}}
{{--                        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                            <strong class="colorgotomarket mb-2">N° de liquidación</strong>--}}
{{--                            <p>{{ $listar_detalle_liquidacion->liquidacion_numero_correlativo }}</p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                            <div class="ps-4">--}}
{{--                                @php $conteoDespachosDetalle = 1; @endphp--}}
{{--                                @foreach($listar_detalle_liquidacion->detalles as $listar_detalle_despacho)--}}
{{--                                    <div class="row">--}}
{{--                                        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                            <div class="row">--}}
{{--                                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                                    <h6>Información Adicional del Despacho #{{$conteoDespachosDetalle}}</h6>--}}
{{--                                                    <hr>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-lg-2 col-md-3 col-sm-4 mb-3">--}}
{{--                                                    <strong class="colorgotomarket mb-2">Usuario de Registro</strong>--}}
{{--                                                    <p>{{ $listar_detalle_despacho->name }}</p>--}}
{{--                                                </div>--}}
{{--                                                @if($listar_detalle_despacho->id_vehiculo)--}}
{{--                                                    @php--}}
{{--                                                        $vehiculo = \Illuminate\Support\Facades\DB::table('vehiculos')->where('id_vehiculo','=',$listar_detalle_despacho->id_vehiculo)->first();--}}
{{--                                                    @endphp--}}
{{--                                                    <div class="col-lg-2 col-md-3 col-sm-4 mb-3">--}}
{{--                                                        <strong class="colorgotomarket mb-2">Placa del Vehículo:</strong>--}}
{{--                                                        <p>{{ $vehiculo->vehiculo_placa }}</p>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">--}}
{{--                                                        <strong class="colorgotomarket mb-2">Capacidad del Vehículo:</strong>--}}
{{--                                                        <p>{{ $general->formatoDecimal($vehiculo->vehiculo_capacidad_peso) }} Kg</p>--}}
{{--                                                    </div>--}}
{{--                                                @endif--}}

{{--                                                @if($listar_detalle_despacho->id_tipo_servicios == 2)--}}
{{--                                                    @php--}}
{{--                                                        $departamento = \Illuminate\Support\Facades\DB::table('departamentos')--}}
{{--                                                        ->where('id_departamento','=',$listar_detalle_despacho->id_departamento)->first();--}}
{{--                                                        $provincia = \Illuminate\Support\Facades\DB::table('provincias')--}}
{{--                                                        ->where('id_provincia','=',$listar_detalle_despacho->id_provincia)->first();--}}
{{--                                                        $distrito = \Illuminate\Support\Facades\DB::table('distritos')--}}
{{--                                                        ->where('id_distrito','=',$listar_detalle_despacho->id_distrito)->first();--}}
{{--                                                    @endphp--}}
{{--                                                    <div class="col-lg-5 col-md-3 col-sm-4 mb-3">--}}
{{--                                                        <strong class="colorgotomarket mb-2">Ubigeo Seleccionado en el Despacho:</strong>--}}
{{--                                                        <p>{{ $departamento ? $departamento->departamento_nombre : '' }} - {{ $provincia ? $provincia->provincia_nombre : '' }} - {{ $distrito ? $distrito->distrito_nombre : 'TODOS LOS DISTRITOS' }}</p>--}}
{{--                                                    </div>--}}
{{--                                                @endif--}}


{{--                                                @if($listar_detalle_despacho->id_tarifario)--}}
{{--                                                    <div class="col-lg-3 col-md-3 col-sm-4 mb-3">--}}
{{--                                                        <strong class="colorgotomarket mb-2">Capacidad de la Tarifa:</strong>--}}
{{--                                                        <p>Min: {{$general->formatoDecimal($listar_detalle_despacho->despacho_cap_min)}} Kg - Max: {{ $general->formatoDecimal($listar_detalle_despacho->despacho_cap_max) }} Kg</p>--}}
{{--                                                    </div>--}}
{{--                                                @endif--}}
{{--                                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">--}}
{{--                                                    <strong class="colorgotomarket mb-2">Peso del Despacho:</strong>--}}
{{--                                                    <p>{{$general->formatoDecimal($listar_detalle_despacho->despacho_peso)}} Kg</p>--}}
{{--                                                </div>--}}
{{--                                                --}}{{-- ---------------------------------PRECIOS ----------------------------------------------- --}}
{{--                                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">--}}
{{--                                                    <strong class="colorgotomarket mb-2">Monto de la Tarifa:</strong>--}}
{{--                                                    <p>S/ {{$general->formatoDecimal($listar_detalle_despacho->despacho_flete)}}</p>--}}
{{--                                                </div>--}}
{{--                                                @if($listar_detalle_despacho->despacho_estado_modificado == 1)--}}
{{--                                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3">--}}
{{--                                                        <strong class="colorgotomarket mb-2">Monto Modificado:</strong>--}}
{{--                                                        <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->despacho_monto_modificado) }}</p>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3">--}}
{{--                                                        <strong class="colorgotomarket mb-2">Descripción:</strong>--}}
{{--                                                        <p>{{ $listar_detalle_despacho->despacho_descripcion_modificado }}</p>--}}
{{--                                                    </div>--}}
{{--                                                @endif--}}
{{--                                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">--}}
{{--                                                    <strong class="colorgotomarket mb-2">Otros Gastos:</strong>--}}
{{--                                                    <p>S/ {{$listar_detalle_despacho->despacho_gasto_otros ? $general->formatoDecimal($listar_detalle_despacho->despacho_gasto_otros) : 0}}</p>--}}
{{--                                                </div>--}}
{{--                                                @if($listar_detalle_despacho->despacho_gasto_otros > 0)--}}
{{--                                                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">--}}
{{--                                                        <strong class="colorgotomarket mb-2">Descripción del Gasto:</strong>--}}
{{--                                                        <p>{{ $listar_detalle_despacho->despacho_descripcion_otros }}</p>--}}
{{--                                                    </div>--}}
{{--                                                @endif--}}
{{--                                                @if($listar_detalle_despacho->id_tipo_servicios == 1)--}}
{{--                                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3">--}}
{{--                                                        <strong class="colorgotomarket mb-2">Mano de Obra:</strong>--}}
{{--                                                        <p>S/ {{$listar_detalle_despacho->despacho_ayudante ? $general->formatoDecimal($listar_detalle_despacho->despacho_ayudante) : 0}}</p>--}}
{{--                                                    </div>--}}
{{--                                                @endif--}}
{{--                                            </div>--}}
{{--                                            <div class="row">--}}
{{--                                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                                    <h6>Resumen General del Despacho</h6>--}}
{{--                                                    <hr>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-lg-3 col-md-2 col-sm-12 mb-3">--}}
{{--                                                    <strong class="colorgotomarket mb-2">Total Venta Despachada:</strong>--}}
{{--                                                    <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->totalVentaDespacho) }}</p>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-lg-3 col-md-3 col-sm-12 mb-3">--}}
{{--                                                    <strong class="colorgotomarket mb-2 d-block">Peso Total:</strong>--}}
{{--                                                    <span class="mb-0">{{ $general->formatoDecimal($listar_detalle_despacho->despacho_peso) }} Kg</span>--}}

{{--                                                </div>--}}
{{--                                                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">--}}
{{--                                                    <strong class="colorgotomarket mb-2">Total Despacho:</strong>--}}
{{--                                                    <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->despacho_costo_total) }}</p>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">--}}
{{--                                                    <strong class="colorgotomarket mb-2">Flete / Venta</strong>--}}
{{--                                                    <p>{{ $general->formatoDecimal(($listar_detalle_despacho->despacho_costo_total / $listar_detalle_despacho->totalVentaDespacho) * 100) }} %</p>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">--}}
{{--                                                    <strong class="colorgotomarket mb-2">Flete / Peso</strong>--}}
{{--                                                    <p>{{ $general->formatoDecimal($listar_detalle_despacho->despacho_costo_total / $listar_detalle_despacho->despacho_peso) }}</p>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <div class="row">--}}
{{--                                                @php--}}
{{--                                                    $totalVentaDespaDespachoModal = $listar_detalle_despacho->totalVentaDespacho;--}}
{{--                                                   if ($listar_detalle_despacho->totalVentaNoEntregado){--}}
{{--                                                       $totalVentaDespaDespachoModal = $listar_detalle_despacho->totalVentaDespacho - $listar_detalle_despacho->totalVentaNoEntregado;--}}
{{--                                                   }--}}

{{--                                                   $totalPesoDespachoModal = $listar_detalle_despacho->despacho_peso;--}}
{{--                                                   if ($listar_detalle_despacho->totalPesoNoEntregado){--}}
{{--                                                      $totalPesoDespachoModal = $listar_detalle_despacho->despacho_peso - $listar_detalle_despacho->totalPesoNoEntregado;--}}
{{--                                                   }--}}

{{--                                                  $despachoGeneraLiquidacionModal = 0;--}}
{{--                                                  if ($listar_detalle_despacho->id_tipo_servicios == 1){--}}
{{--                                                      $despachoGeneraLiquidacionModal = $listar_detalle_despacho->despacho_costo_total;--}}
{{--                                                  }else{--}}
{{--                                                      $despachoGeneraLiquidacionModal = ($listar_detalle_despacho->despacho_monto_modificado * $totalPesoDespachoModal) + $listar_detalle_despacho->despacho_ayudante + $listar_detalle_despacho->despacho_gasto_otros;--}}
{{--                                                  }--}}
{{--                                                @endphp--}}
{{--                                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                                    <div class="row">--}}
{{--                                                        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                                            <h6>Resumen Completo del Despacho Con Facturas Entregadas</h6>--}}
{{--                                                            <hr>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">--}}
{{--                                                            <strong class="colorgotomarket mb-2 d-block">Total Venta Despachada:</strong>--}}
{{--                                                            <span class="mb-0">S/ {{ $general->formatoDecimal($listar_detalle_despacho->totalVentaDespacho) }}</span>--}}
{{--                                                            @if($listar_detalle_despacho->totalVentaNoEntregado)--}}
{{--                                                                <span class="text-danger mb-0">S/ -{{ $general->formatoDecimal($listar_detalle_despacho->totalVentaNoEntregado) }}</span>--}}
{{--                                                                <p class="colorBlackComprobantes">S/ {{ $general->formatoDecimal($listar_detalle_despacho->totalVentaDespacho - $listar_detalle_despacho->totalVentaNoEntregado) }}</p>--}}
{{--                                                            @endif--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">--}}
{{--                                                            <strong class="colorgotomarket mb-2 d-block">Peso Total:</strong>--}}
{{--                                                            <span class="mb-0">{{ $general->formatoDecimal($listar_detalle_despacho->despacho_peso) }} {{$listar_detalle_despacho->totalPesoNoEntregado ? '' :'Kg'}}</span>--}}
{{--                                                            @if($listar_detalle_despacho->totalPesoNoEntregado)--}}
{{--                                                                <span class="text-danger mb-0">-{{ $general->formatoDecimal($listar_detalle_despacho->totalPesoNoEntregado) }}</span>--}}
{{--                                                                <p class="colorBlackComprobantes">{{ $general->formatoDecimal($listar_detalle_despacho->despacho_peso - $listar_detalle_despacho->totalPesoNoEntregado) }} Kg</p>--}}
{{--                                                            @endif--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">--}}
{{--                                                            <strong class="colorgotomarket mb-2">Total Despacho:</strong>--}}
{{--                                                            <p>S/ {{ $general->formatoDecimal($despachoGeneraLiquidacionModal) }}</p>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">--}}
{{--                                                            <strong class="colorgotomarket mb-2">Flete / Venta</strong>--}}
{{--                                                            <p>{{ $general->formatoDecimal(($despachoGeneraLiquidacionModal / $totalVentaDespaDespachoModal) * 100) }} %</p>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">--}}
{{--                                                            <strong class="colorgotomarket mb-2">Flete / Peso</strong>--}}
{{--                                                            <p>{{ $general->formatoDecimal($despachoGeneraLiquidacionModal / $totalPesoDespachoModal) }}</p>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                                    @php--}}
{{--                                                        $gastosLiquidacionOS = [];--}}
{{--                                                        $informacionLiquidacion = "";--}}
{{--                                                        if ($listar_detalle_despacho->id_liquidacion_detalle){--}}
{{--                                                            $gastosLiquidacionOS =  \Illuminate\Support\Facades\DB::table('liquidacion_gastos')->where('id_liquidacion_detalle','=',$listar_detalle_despacho->id_liquidacion_detalle)->get();--}}
{{--                                                            $informacionLiquidacion =  \Illuminate\Support\Facades\DB::table('liquidacion_detalles')->where('id_liquidacion_detalle','=',$listar_detalle_despacho->id_liquidacion_detalle)->first();--}}
{{--                                                        }--}}
{{--                                                    @endphp--}}
{{--                                                    @if($informacionLiquidacion)--}}
{{--                                                        <div class="row">--}}
{{--                                                            <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                                                <h6>Resumen Liquidación</h6>--}}
{{--                                                                <hr>--}}
{{--                                                            </div>--}}
{{--                                                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">--}}
{{--                                                                <x-table-general>--}}
{{--                                                                    <x-slot name="thead">--}}
{{--                                                                        <tr>--}}
{{--                                                                            <th>Costo de Tarifa</th>--}}
{{--                                                                            <th>Mano de Obra</th>--}}
{{--                                                                            <th>Otros Gastos</th>--}}
{{--                                                                            <th>Peso Total</th>--}}
{{--                                                                            <th>Total Servicio</th>--}}
{{--                                                                            <th>Flete / Venta</th>--}}
{{--                                                                            <th>Flete / Peso</th>--}}
{{--                                                                        </tr>--}}
{{--                                                                    </x-slot>--}}

{{--                                                                    <x-slot name="tbody">--}}
{{--                                                                        @if(count($gastosLiquidacionOS) > 0)--}}
{{--                                                                            <tr>--}}
{{--                                                                                @php--}}
{{--                                                                                    $costoTari = $gastosLiquidacionOS[0]->liquidacion_gasto_monto;--}}
{{--                                                                                    $manoObra = $gastosLiquidacionOS[1]->liquidacion_gasto_monto;--}}
{{--                                                                                    $otrosGastos = $gastosLiquidacionOS[2]->liquidacion_gasto_monto;--}}
{{--                                                                                    $pesoFiL = $gastosLiquidacionOS[3]->liquidacion_gasto_monto;--}}
{{--                                                                                    $totalDespachoLiqui = 0;--}}
{{--                                                                                    if ($listar_detalle_despacho->id_tipo_servicios == 1){--}}
{{--                                                                                        $totalDespachoLiqui = $costoTari + $manoObra + $otrosGastos;--}}
{{--                                                                                    }else{--}}
{{--                                                                                        $totalDespachoLiqui = ($costoTari * $pesoFiL) + $manoObra + $otrosGastos;--}}
{{--                                                                                    }--}}
{{--                                                                                @endphp--}}
{{--                                                                                @foreach($gastosLiquidacionOS  as $ind => $gas)--}}
{{--                                                                                    <td>--}}
{{--                                                                                        <span>{{$ind <=2 ? 'S/' : ''}} {{$gas->liquidacion_gasto_monto ? $general->formatoDecimal($gas->liquidacion_gasto_monto) : 0}} {{$ind <=2 ? '' : 'Kg'}}</span>--}}
{{--                                                                                        <p class="mt-2">--}}
{{--                                                                                            <b class="colorBlackComprobantes">{{$gas->liquidacion_gasto_descripcion}}</b>--}}
{{--                                                                                        </p>--}}
{{--                                                                                    </td>--}}
{{--                                                                                @endforeach--}}
{{--                                                                                <td>S/ {{ $general->formatoDecimal($totalDespachoLiqui) }}</td>--}}
{{--                                                                                <td>{{ $general->formatoDecimal(($totalDespachoLiqui / $totalVentaDespaDespachoModal) * 100) }} %</td>--}}
{{--                                                                                <td>{{ $general->formatoDecimal($totalDespachoLiqui / $pesoFiL) }}</td>--}}
{{--                                                                            </tr>--}}
{{--                                                                        @endif--}}
{{--                                                                    </x-slot>--}}
{{--                                                                </x-table-general>--}}
{{--                                                            </div>--}}
{{--                                                            <div class="col-lg-12 mb-2">--}}
{{--                                                                <strong class="colorgotomarket mb-2">Comentario General:</strong>--}}
{{--                                                                <p>{{ $informacionLiquidacion->liquidacion_detalle_comentarios ? $informacionLiquidacion->liquidacion_detalle_comentarios : 'Sin Comentarios' }}</p>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    @endif--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                            <div class="row">--}}
{{--                                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                                    <h6>Información de Comprobantes</h6>--}}
{{--                                                    <hr>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                                    <x-table-general>--}}
{{--                                                        <x-slot name="thead">--}}
{{--                                                            <tr>--}}
{{--                                                                <th>N°</th>--}}
{{--                                                                <th>Guía</th>--}}
{{--                                                                <th>F. Emision</th>--}}
{{--                                                                <th>Cliente</th>--}}
{{--                                                                <th>Comprobante</th>--}}
{{--                                                                <th>Importe Venta</th>--}}
{{--                                                                <th>F. Despacho</th>--}}
{{--                                                                <th>Peso Kilos</th>--}}
{{--                                                                <th>Estado del comprobante</th>--}}
{{--                                                            </tr>--}}
{{--                                                        </x-slot>--}}

{{--                                                        <x-slot name="tbody">--}}
{{--                                                            @if(count($listar_detalle_despacho->comprobantes) > 0)--}}
{{--                                                                @php $conteo = 1; @endphp--}}
{{--                                                                @foreach($listar_detalle_despacho->comprobantes as $ta)--}}
{{--                                                                    <tr>--}}
{{--                                                                        <td>{{$conteo}}</td>--}}
{{--                                                                        <td>{{$general->formatearCodigo($ta->despacho_venta_guia)}}</td>--}}
{{--                                                                        <td>{{date('d-m-Y',strtotime($ta->despacho_venta_grefecemision))}}</td>--}}
{{--                                                                        <td>{{$ta->despacho_venta_cnomcli}}</td>--}}
{{--                                                                        <td>{{$ta->despacho_venta_factura}}</td>--}}
{{--                                                                        <td>S/ {{$general->formatoDecimal($ta->despacho_venta_cfimporte)}}</td>--}}
{{--                                                                        <td>{{date('d-m-Y',strtotime($listar_detalle_despacho->programacion_fecha))}}</td>--}}
{{--                                                                        <td>{{$general->formatoDecimal($ta->despacho_venta_total_kg)}} Kg</td>--}}
{{--                                                                        <td>--}}
{{--                                                            <span class="font-bold badge  {{$ta->despacho_detalle_estado_entrega == 2 ? 'bg-label-success' : 'bg-label-danger'}}">--}}
{{--                                                                {{$ta->despacho_detalle_estado_entrega == 2 ? 'ENTREGADO ' : 'NO ENTREGADO'}}--}}
{{--                                                            </span>--}}
{{--                                                                        </td>--}}
{{--                                                                    </tr>--}}
{{--                                                                    @php $conteo++; @endphp--}}
{{--                                                                @endforeach--}}
{{--                                                            @else--}}
{{--                                                                <tr class="odd">--}}
{{--                                                                    <td valign="top" colspan="7" class="dataTables_empty text-center">--}}
{{--                                                                        No se han encontrado resultados.--}}
{{--                                                                    </td>--}}
{{--                                                                </tr>--}}
{{--                                                            @endif--}}
{{--                                                        </x-slot>--}}
{{--                                                    </x-table-general>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    @php $conteoDespachosDetalle++; @endphp--}}
{{--                                @endforeach--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                </div>--}}
{{--            @endif--}}
{{--        </x-slot>--}}
{{--    </x-modal-general>--}}

{{--    --}}{{-- MODAL GUIAS--}}
{{--    <x-modal-general  wire:ignore.self >--}}
{{--        <x-slot name="tama">modal-xl</x-slot>--}}
{{--        <x-slot name="id_modal">modalDetalleGuias</x-slot>--}}
{{--        <x-slot name="titleModal">Guías Relacionadas</x-slot>--}}
{{--        <x-slot name="modalContent">--}}
{{--            @if($guiasAsociadasLiquidacion)--}}
{{--                <div class="modal-body">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                            <x-table-general>--}}
{{--                                <x-slot name="thead">--}}
{{--                                    <tr>--}}
{{--                                        <th>N°</th>--}}
{{--                                        <th>OS Relacionada</th>--}}
{{--                                        <th>Guía</th>--}}
{{--                                        <th>F. Emision</th>--}}
{{--                                        <th>Cliente</th>--}}
{{--                                        <th>Comprobante</th>--}}
{{--                                        <th>Importe</th>--}}
{{--                                        <th>F. Despacho</th>--}}
{{--                                        <th>Peso</th>--}}
{{--                                    </tr>--}}
{{--                                </x-slot>--}}

{{--                                <x-slot name="tbody">--}}
{{--                                    @if(count($guiasAsociadasLiquidacion) > 0)--}}
{{--                                        @php $conteo = 1; @endphp--}}
{{--                                        @foreach($guiasAsociadasLiquidacion as $ta)--}}
{{--                                            <tr>--}}
{{--                                                <td>{{$conteo}}</td>--}}
{{--                                                <td>{{$ta->despacho_numero_correlativo}}</td>--}}
{{--                                                <td>{{$general->formatearCodigo($ta->despacho_venta_guia)}}</td>--}}
{{--                                                <td>--}}
{{--                                                    {{date('d-m-Y',strtotime($ta->despacho_venta_grefecemision))}}--}}
{{--                                                </td>--}}
{{--                                                <td>{{$ta->despacho_venta_cnomcli}}</td>--}}
{{--                                                <td>{{$ta->despacho_venta_factura}}</td>--}}
{{--                                                <td>S/ {{$general->formatoDecimal($ta->despacho_venta_cfimporte)}}</td>--}}
{{--                                                <td>{{ date('d-m-Y',strtotime($ta->programacion_fecha)) }}</td>--}}
{{--                                                <td>{{$general->formatoDecimal($ta->despacho_venta_total_kg)}} Kg</td>--}}
{{--                                            </tr>--}}
{{--                                            @php $conteo++; @endphp--}}
{{--                                        @endforeach--}}
{{--                                    @else--}}
{{--                                        <tr class="odd">--}}
{{--                                            <td valign="top" colspan="7" class="dataTables_empty text-center">--}}
{{--                                                No se han encontrado resultados.--}}
{{--                                            </td>--}}
{{--                                        </tr>--}}
{{--                                    @endif--}}
{{--                                </x-slot>--}}
{{--                            </x-table-general>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}
{{--        </x-slot>--}}
{{--    </x-modal-general>--}}

{{--    --}}{{-- MODAL AGREGAR COMPROBANTE --}}
{{--    <x-modal-general wire:ignore.self>--}}
{{--        <x-slot name="id_modal">modalAgregarComprobante</x-slot>--}}
{{--        <x-slot name="titleModal">Agregar comprobante</x-slot>--}}
{{--        <x-slot name="modalContent">--}}
{{--            <form wire:submit="guardar_comprobante">--}}
{{--                <div class="row">--}}
{{--                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">--}}
{{--                        <label for="liquidacion_ruta_comprobante" class="form-label">Comprobante</label>--}}
{{--                        <input type="file" class="form-control" id="liquidacion_ruta_comprobante" name="liquidacion_ruta_comprobante" wire:model="liquidacion_ruta_comprobante">--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">--}}
{{--                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>--}}
{{--                    <button type="submit" class="btn btn-primary" >Guardar</button>--}}
{{--                </div>--}}
{{--            </form>--}}
{{--        </x-slot>--}}
{{--    </x-modal-general>--}}

{{--    @if (session()->has('success'))--}}
{{--        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--            <div class="alert alert-success alert-dismissible show fade mt-2">--}}
{{--                {{ session('success') }}--}}
{{--                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    @endif--}}
{{--    @if (session()->has('error'))--}}
{{--        <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--            <div class="alert alert-danger alert-dismissible show fade mt-2">--}}
{{--                {{ session('error') }}--}}
{{--                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    @endif--}}


{{--    <div class="row align-items-center mt-2">--}}
{{--        <div class="col-lg-3 col-md-2 col-sm-12 mb-2">--}}
{{--            <label for="buscar_search" class="form-label">Buscar</label>--}}
{{--            <x-input-general  type="text" id="buscar_search" wire:model.live="search" />--}}
{{--        </div>--}}
{{--        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">--}}
{{--            <label for="fecha_desde" class="form-label">Tipo</label>--}}
{{--            <select name="" id="" class="form-select" wire:model.live="tipoLiqui">--}}
{{--                <option value="">Seleccionar</option>--}}
{{--                <option value="1">APROBADO</option>--}}
{{--                <option value="2">RECHAZADO</option>--}}
{{--            </select>--}}
{{--        </div>--}}
{{--        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">--}}
{{--            <label for="fecha_desde" class="form-label">Desde</label>--}}
{{--            <x-input-general  type="date" id="fecha_desde" wire:model.live="desde" />--}}
{{--        </div>--}}
{{--        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">--}}
{{--            <label for="fecha_hasta" class="form-label">Hasta</label>--}}
{{--            <x-input-general  type="date" id="fecha_hasta" wire:model.live="hasta" />--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <div class="row mt-4">--}}
{{--        @foreach($resultado as $resu)--}}
{{--            <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                <p class="mb-0">NOMBRE COMERCIAL: <b>{{$resu->transportista_nom_comercial}}</b> - RUC: <b>{{$resu->transportista_ruc}}</b></p>--}}
{{--            </div>--}}
{{--            <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                <x-card-general-view>--}}
{{--                    <x-slot name="content">--}}
{{--                        <div class="row">--}}
{{--                            <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                <x-table-general>--}}
{{--                                    <x-slot name="thead">--}}
{{--                                        <tr>--}}
{{--                                            <th>N°</th>--}}
{{--                                            <th>N° Factura</th>--}}
{{--                                            <th>Guías Despachadas</th>--}}
{{--                                            <th>Importe Factura (sin IGV)</th>--}}
{{--                                            <th> Importe Factura (con IGV)</th>--}}
{{--                                            <th>Acciones</th>--}}
{{--                                        </tr>--}}
{{--                                    </x-slot>--}}

{{--                                    <x-slot name="tbody">--}}
{{--                                        @if(count($resu->liquidaciones) > 0)--}}
{{--                                            @php--}}
{{--                                                $conteoGeneral = 1;--}}
{{--                                                $conteoTotalSinIGV = 0;--}}
{{--                                            @endphp--}}
{{--                                            @foreach($resu->liquidaciones as $re)--}}
{{--                                                <tr>--}}
{{--                                                    <td>{{$conteoGeneral}}</td>--}}
{{--                                                    <td>{{$re->liquidacion_serie}} - {{$re->liquidacion_correlativo}}</td>--}}
{{--                                                    <td>--}}
{{--                                                        @php--}}
{{--                                                            $guiasComprobante = \Illuminate\Support\Facades\DB::table('liquidacion_detalles as ld')--}}
{{--                                                            ->join('despachos as d','d.id_despacho','=','ld.id_despacho')--}}
{{--                                                            ->join('despacho_ventas as dv','dv.id_despacho','=','d.id_despacho')--}}
{{--                                                            ->where('ld.id_liquidacion', '=', $re->id_liquidacion)->get();--}}
{{--                                                            $totalGuias = count($guiasComprobante); // Contamos las guías--}}
{{--                                                        @endphp--}}
{{--                                                        @foreach($guiasComprobante as $indexGuias => $g)--}}
{{--                                                            @if($indexGuias <= 2)--}}
{{--                                                                <a wire:click="listar_guias_liquidacion({{ $re->id_liquidacion }})" data-bs-toggle="modal" data-bs-target="#modalDetalleGuias" class="cursoPointer text-primary">--}}
{{--                                                                    {{ $general->formatearCodigo($g->despacho_venta_guia) }}--}}
{{--                                                                </a>--}}
{{--                                                                @if($indexGuias < 2 && $indexGuias < $totalGuias - 1)--}}
{{--                                                                    , <!-- Mostrar la coma solo si no es el último elemento que se va a mostrar -->--}}
{{--                                                                @elseif($indexGuias == 2 && $totalGuias > 3)--}}
{{--                                                                    ... <!-- Mostrar "..." si hay más guías después de las tres primeras -->--}}
{{--                                                                @endif--}}
{{--                                                            @endif--}}
{{--                                                        @endforeach--}}
{{--                                                    </td>--}}
{{--                                                    <td>S/ {{$general->formatoDecimal($re->total_sin_igv)}}</td>--}}
{{--                                                    <td>S/ {{$general->formatoDecimal($re->total_sin_igv * 1.18)}}</td>--}}
{{--                                                    <td>--}}
{{--                                                        <x-btn-accion class="btn btn-sm text-primary" wire:click="listar_informacion_despacho({{ $re->id_liquidacion }})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">--}}
{{--                                                            <x-slot name="message">--}}
{{--                                                                <i class="fa-solid fa-eye"></i>--}}
{{--                                                            </x-slot>--}}
{{--                                                        </x-btn-accion>--}}
{{--                                                    </td>--}}
{{--                                                </tr>--}}
{{--                                                @php--}}
{{--                                                    $conteoGeneral++;--}}
{{--                                                    $conteoTotalSinIGV+=$re->total_sin_igv;--}}
{{--                                                @endphp--}}
{{--                                            @endforeach--}}
{{--                                            <tr style="--bs-table-accent-bg: var(--bs-table-hover-bg);color: var(--bs-table-hover-color);}">--}}
{{--                                                <td colspan="3" class="text-center">TOTAL</td>--}}
{{--                                                <td>S/ {{$general->formatoDecimal($conteoTotalSinIGV)}}</td>--}}
{{--                                                <td>S/ {{$general->formatoDecimal($conteoTotalSinIGV * 1.18)}}</td>--}}
{{--                                                <td>--}}

{{--                                                </td>--}}
{{--                                            </tr>--}}
{{--                                        @endif--}}
{{--                                    </x-slot>--}}
{{--                                </x-table-general>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </x-slot>--}}
{{--                </x-card-general-view>--}}
{{--            </div>--}}
{{--        @endforeach--}}

{{--    </div>--}}




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
                                    <p>{{ $general->formatoDecimal(($listar_detalle_despacho->despacho_costo_total / $listar_detalle_despacho->totalVentaDespacho) * 100) }} %</p>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Flete / Peso</strong>
                                    <p>{{ $general->formatoDecimal($listar_detalle_despacho->despacho_costo_total / $listar_detalle_despacho->despacho_peso) }}</p>
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
                                            <p>{{ $general->formatoDecimal(($despachoGeneraLiquidacionModal / $totalVentaDespaDespachoModal) * 100) }} %</p>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                            <strong class="colorgotomarket mb-2">Flete / Peso</strong>
                                            <p>{{ $general->formatoDecimal($despachoGeneraLiquidacionModal / $totalPesoDespachoModal) }}</p>
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
                                                            <th>Importe del Servicio</th>
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
                                                                <td>{{ $general->formatoDecimal(($totalDespachoLiqui / $totalVentaDespaDespachoModal) * 100) }} %</td>
                                                                <td>{{ $general->formatoDecimal($totalDespachoLiqui / $pesoFiL) }}</td>
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
                                                        <td>{{$general->formatearCodigo($ta->despacho_venta_guia)}}</td>
                                                        <td>{{date('d-m-Y',strtotime($ta->despacho_venta_grefecemision))}}</td>
                                                        <td>{{$ta->despacho_venta_cnomcli}}</td>
                                                        <td>{{$ta->despacho_venta_factura}}</td>
                                                        <td>S/ {{$general->formatoDecimal($ta->despacho_venta_cfimporte)}}</td>
                                                        <td>{{date('d-m-Y',strtotime($listar_detalle_despacho->programacion_fecha))}}</td>
                                                        <td>{{$general->formatoDecimal($ta->despacho_venta_total_kg)}} Kg</td>
                                                        <td>
                                                            <span class="font-bold badge  {{$ta->despacho_detalle_estado_entrega == 2 ? 'bg-label-success' : 'bg-label-danger'}}">
                                                                {{$ta->despacho_detalle_estado_entrega == 2 ? 'ENTREGADO ' : 'NO ENTREGADO'}}
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
                                                <td>{{$general->formatearCodigo($ta->despacho_venta_guia)}}</td>
                                                <td>
                                                    {{date('d-m-Y',strtotime($ta->despacho_venta_grefecemision))}}
                                                </td>
                                                <td>{{$ta->despacho_venta_cnomcli}}</td>
                                                <td>{{$ta->despacho_venta_factura}}</td>
                                                <td>S/ {{$general->formatoDecimal($ta->despacho_venta_cfimporte)}}</td>
                                                <td>{{ date('d-m-Y',strtotime($ta->programacion_fecha)) }}</td>
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
            @endif
        </x-slot>
    </x-modal-general>
    {{-- MODAL AGREGAR COMPROBANTE--}}
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
        @if(count($resultado) > 0)
            <div class="col-lg-2 col-md-2 col-sm-12">
                <button class="btn bg-success text-white mt-3" wire:click="generar_excel_historial_liquidacion"><i class="fa-solid fa-file-excel"></i> Generar Excel</button>
            </div>
        @endif

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
                <h6 class="m-0">ES : Estado de liquidación</h6>
            </div>
            <div class="col-lg col-md-3 col-sm-3 mb-1">
                <h6 class="m-0">SC : Serie y Correlativo</h6>
            </div>


        </div>
    @endif

    <div class="accordion mt-3" id="accordionExample" >
        @if(count($resultado) > 0)
            @php $conteoGeneral = 1; @endphp
            @foreach($resultado as $index => $r)
                <div class="accordion-item" >
                    <h2 class="accordion-header">
                        <button class="accordion-button {{$index == 0 ? '' : 'collapsed'}}" wire:ignore.self type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{$index}}" aria-expanded="true" aria-controls="collapseOne_{{$index}}">
                            #{{$conteoGeneral}} | FR : {{$r->creacion_liquidacion}} | UR : {{$r->name}} | TR : {{$r->transportista_nom_comercial}}  | ES : <b class="{{$r->liquidacion_estado_aprobacion == 1 ? 'text-success' : 'text-danger'}} ms-1 me-1"> {{$r->liquidacion_estado_aprobacion == 1 ? 'APROBADO' : 'RECHAZADO'}}</b> | SC : <b class="colorgotomarket ms-1" style="font-size: 20px"> {{$r->liquidacion_serie}} - {{$r->liquidacion_correlativo}}</b>
                        </button>
                    </h2>
                    <div id="collapseOne_{{$index}}" class="accordion-collapse collapse {{$index == 0 ? 'show' : ''}}" data-bs-parent="#accordionExample" wire:ignore.self >
                        <div class="accordion-body" >
                            <div class="row">
                                @if($r->liquidacion_observaciones)
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <label for="">Observaciones:</label>
                                        <p>{{$r->liquidacion_observaciones}}</p>
                                    </div>
                                @endif
                                <div class="col-lg-12 col-md-12 col-sm-12 table-responsive">
                                    <table class="table table-bordered">
                                        @php
                                            //                                            $colorMontoProgramado = "background:  #A9A9A9;color: white ";
                                            //                                            $colorMontoLiquidado = "background:  #32CD32;color: white ";
                                            //                                            $colorMontoLiquidado = "background:  #62dc62;color: white ";
                                                                                        $colorMontoProgramado = "";
                                                                                        $colorMontoLiquidado = "";
                                        @endphp
                                        <thead>
                                        {{--                                            <tr>--}}
                                        {{--                                                <th colspan="6"></th>--}}
                                        {{--                                                <th colspan="6" class="text-center text-white" style="{{$colorMontoProgramado}}">--}}
                                        {{--                                                    Monto Programado--}}
                                        {{--                                                </th>--}}
                                        {{--                                                <th colspan="6" class="text-center text-white" style="{{$colorMontoLiquidado}}">Monto de Liquidación</th>--}}
                                        {{--                                            </tr>--}}
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
                                            {{--                                                <th>N° OS</th>--}}
                                            {{--                                                <th>Servicio</th>--}}
                                            {{--                                                <th>T. Venta Despachada</th>--}}
                                            {{--                                                <th>Peso Total</th>--}}
                                            {{--                                                <th>Importe Total del Servicio</th>--}}

                                            {{--                                                <th style="{{$colorMontoProgramado}}">Costo de Tarifa</th>--}}
                                            {{--                                                <th style="{{$colorMontoProgramado}}">Mano de Obra</th>--}}
                                            {{--                                                <th style="{{$colorMontoProgramado}}">Otros Gastos</th>--}}
                                            {{--                                                <th style="{{$colorMontoProgramado}}">Total Despacho</th>--}}
                                            {{--                                                <th style="{{$colorMontoProgramado}}">Flete / Venta</th>--}}
                                            {{--                                                <th style="{{$colorMontoProgramado}}">Flete / Peso</th>--}}

                                            {{--                                                <th style="{{$colorMontoLiquidado}}">Costo de Tarifa</th>--}}
                                            {{--                                                <th style="{{$colorMontoLiquidado}}">Mano de Obra</th>--}}
                                            {{--                                                <th style="{{$colorMontoLiquidado}}">Otros Gastos</th>--}}
                                            <th style="{{$colorMontoLiquidado}}">Importe del Servicio</th>
                                            <th style="{{$colorMontoLiquidado}}">Flete / Venta</th>
                                            <th style="{{$colorMontoLiquidado}}">Flete / Peso</th>
                                            <th >Acción</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $conteo = 1;
                                        @endphp
                                        @foreach($r->detalles as $de)
                                            <tr >
                                                <th>{{$conteo}}</th>
                                                <th>
                                                    {{$de->despacho_numero_correlativo}}
                                                    {{--                                                        <a class="text-primary cursoPointer" wire:click="listar_informacion_despacho({{ $de->id_despacho }})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">--}}
                                                    {{--                                                           --}}
                                                    {{--                                                        </a>--}}
                                                </th>
                                                <td>{{ $r->transportista_razon_social }}</td>
                                                <td>{{ $de->tipo_servicio_concepto }}</td>
                                                <td>{{ date('d-m-Y',strtotime($de->programacion_fecha)) }}</td>
                                                <td>
                                                    @php
                                                        $guiasComprobante = \Illuminate\Support\Facades\DB::table('despacho_ventas')->where('id_despacho', '=', $de->id_despacho)->get();
                                                        $totalGuias = count($guiasComprobante); // Contamos las guías
                                                    @endphp
                                                    @foreach($guiasComprobante as $indexGuias => $g)
                                                        @if($indexGuias <= 2)
                                                            <a wire:click="listar_guias_despachos({{ $de->id_despacho }})" data-bs-toggle="modal" data-bs-target="#modalDetalleGuias" class="cursoPointer text-primary">
                                                                {{ $general->formatearCodigo($g->despacho_venta_guia) }}
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

                                                {{--                                                    <td style="{{$colorMontoProgramado}}">{{$de->despacho_monto_modificado ? $general->formatoDecimal($de->despacho_monto_modificado): 0}}</td>--}}
                                                {{--                                                    <td style="{{$colorMontoProgramado}}">{{$de->despacho_ayudante ? $general->formatoDecimal($de->despacho_ayudante): 0}}</td>--}}
                                                {{--                                                    <td style="{{$colorMontoProgramado}}">{{$de->despacho_gasto_otros ? $general->formatoDecimal($de->despacho_gasto_otros) : 0}}</td>--}}
                                                {{--                                                    <td style="{{$colorMontoProgramado}}">{{$de->despacho_costo_total ? $general->formatoDecimal($de->despacho_costo_total) : 0}}</td>--}}
                                                {{--                                                    <td style="{{$colorMontoProgramado}}">{{ $general->formatoDecimal($despachoGeneraLiquidacion / $totalVentaDespaDespacho) }} % </td>--}}
                                                {{--                                                    <td style="{{$colorMontoProgramado}}">{{ $general->formatoDecimal($despachoGeneraLiquidacion / $totalPesoDespacho) }} %</td>--}}

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
                                                {{--                                                    @foreach($gastosDespachos as $fa)--}}
                                                {{--                                                        <td style="{{$colorMontoLiquidado}}">{{$fa->liquidacion_gasto_monto}}</td>--}}
                                                {{--                                                    @endforeach--}}
                                                @php
                                                    $totalDespachoMontoLiquidado = 0;
                                                    if ($de->id_tipo_servicios == 1){
                                                        $totalDespachoMontoLiquidado = $costoTarifa + $costoMano + $costoOtros;
                                                    }else{
                                                        $totalDespachoMontoLiquidado = ($costoTarifa * $pesoFinalLiquidacion) + $costoMano + $costoOtros;
                                                    }
                                                @endphp
                                                <td style="{{$colorMontoLiquidado}}">S/ {{ $general->formatoDecimal($totalDespachoMontoLiquidado) }}</td>
                                                <td style="{{$colorMontoLiquidado}}">{{ $general->formatoDecimal(($totalDespachoMontoLiquidado / $totalVentaDespaDespacho) * 100) }} % </td>
                                                <td style="{{$colorMontoLiquidado}}">{{ $general->formatoDecimal($totalDespachoMontoLiquidado / $pesoFinalLiquidacion) }} </td>
                                                <td>
                                                    <x-btn-accion class="btn btn-sm text-primary" wire:click="listar_informacion_despacho({{ $de->id_despacho }})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">
                                                        <x-slot name="message">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </x-slot>
                                                    </x-btn-accion>
                                                </td>
                                            </tr>
                                            @php
                                                $conteo++;
                                            @endphp
                                        @endforeach
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
            <p class="text-center"> Registros Insuficientes</p>
        @endif
    </div>

</div>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalAgregarComprobante').modal('hide');
    });
</script>
@endscript
