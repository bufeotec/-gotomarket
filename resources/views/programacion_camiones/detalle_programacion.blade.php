@extends('layouts.dashboard_template')
@section('title','Programación de camiones')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Información detallada de la programación." />
        <div class="card mt-2">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h6>Información de Programación</h6>
                        <hr>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                        <small>Usuario de Registro</small>
                        <p class="mb-0 textBlack">{{$programacion->nombre_creacion}} {{$programacion->apellido_creacion}}</p>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                        <small>Fecha de Registro</small>
                        @php
                            $me = new \App\Models\General();
                            $fechaFormate = $me->obtenerNombreFecha($programacion->created_at,'DateTime', 'DateTime')
                        @endphp
                        <p class="mb-0 textBlack">{{$fechaFormate}}</p>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                        <small>Fecha de Entrega</small>
                        @php
                            $meEntrega = new \App\Models\General();
                            $fechaFormateEntrega = $meEntrega->obtenerNombreFecha($programacion->programacion_fecha,'Date', 'Date')
                        @endphp
                        <p class="mb-0 textBlack">{{$fechaFormateEntrega}}</p>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                        <small>Usuario de Aprobación</small>
                        <p class="mb-0 textBlack">{{$programacion->nombre_aprobacion ? $programacion->nombre_aprobacion.' '.$programacion->apellido_aprobacion : '-'}}</p>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                        <small>Fecha de Aprobación</small>
                        @php
                            $me = new \App\Models\General();
                            $fechaFormateAprobacion = "-";
                            if ($programacion->programacion_fecha_aprobacion){
                                $fechaFormateAprobacion = $me->obtenerNombreFecha($programacion->programacion_fecha_aprobacion,'Date', 'Date');
                            }
                        @endphp
                        <p class="mb-0 textBlack">{{$fechaFormateAprobacion}}</p>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                        <small>Número de Programación</small>
                        <p class="mb-0 textBlack">{{$programacion->programacion_numero_correlativo ? $programacion->programacion_numero_correlativo : '-'}}</p>
                    </div>
                </div>
            </div>
        </div>
        @php $contador = 1; @endphp
        @foreach($despacho as $des)
            <div class="card mt-2">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <h6>Despacho 00{{$contador}}</h6>
                            <hr>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                            <small>Transportista</small>
                            <p class="mb-0 textBlack">{{$des->transportista_nom_comercial}}</p>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                            <small>Tipo Servicio</small>
                            <p class="mb-0 textBlack">{{$des->id_tipo_servicios == 1 ? 'LOCAL' : 'PROVINCIAL'}}</p>
                        </div>
                        @if($des->id_tipo_servicios == 1)
                            @php
                                $vehi = \Illuminate\Support\Facades\DB::table('vehiculos as v')
                                ->join('tipo_vehiculos as tv','tv.id_tipo_vehiculo','=','v.id_tipo_vehiculo')
                                ->select('v.vehiculo_placa','tv.tipo_vehiculo_concepto')->where('v.id_vehiculo','=',$des->id_vehiculo)->first();
                            @endphp
                            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                                <small>Vehículo</small>
                                <p class="mb-0 textBlack">{{$vehi->vehiculo_placa}}</p>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                                <small>Tipo Vehículo</small>
                                <p class="mb-0 textBlack">{{$vehi->tipo_vehiculo_concepto}}</p>
                            </div>
                        @endif
                        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                            <small>Tarifa</small>
                            <p class="mb-0 textBlack">S/ {{$des->despacho_flete}}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                            <small>Peso Total</small>
                            <p class="mb-0 textBlack">{{$des->despacho_peso}} Kg</p>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                            <small>Volumen Total</small>
                            @php
                                $va = fmod($des->despacho_volumen, 1) != 0 ? number_format($des->despacho_volumen, 2, '.', ',') : number_format($des->despacho_volumen, 0, '.', ',');
                            @endphp
                            <p class="mb-0 textBlack">{{$va}} cm³</p>
                        </div>
                    </div>
                </div>
            </div>
            @php $contador++; @endphp
        @endforeach
    </div>

    <script src="{{asset('js/domain.js')}}"></script>
@endsection
