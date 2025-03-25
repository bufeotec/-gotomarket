
@extends('layouts.dashboard_template')
@section('title','Reportes de Control Documentario')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Reporte de Control Documentario" />

{{--        @livewire('programacioncamiones.reportecontroldocumentario')--}}
    </div>

    <div class="row">
{{--                <div class="col-lg-6 col-md-6 col-sm-12">--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-header">--}}
{{--                            <h4>Reporte de Venta e Indicadores</h4>--}}
{{--                        </div>--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex justify-content-end">--}}
{{--                                <a href="{{route('Programacioncamion.reporte_ventas_indicadores')}}" class="btn btn-success">Ingresar</a>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--        <div class="col-lg-6 col-md-6 col-sm-6">--}}
{{--            <div class="card">--}}
{{--                <div class="card-header">--}}
{{--                    <h4>Reporte de Estados de Comprobante</h4>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <div class="d-flex justify-content-end">--}}
{{--                        <a href="{{route('Programacioncamion.reporte_estados_factura')}}" class="btn btn-success">Ingresar</a>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

        <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Efectividad de Entrega de Pedidos</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-end">
                                <a href="{{route('Programacioncamion.reporte_estados_factura')}}" class="btn btn-success">Ingresar</a>
                            </div>
                        </div>
                    </div>
                </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h4>Reporte de Tiempos de Atención de Pedidos</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('Programacioncamion.reporte_estados_factura')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h4>Reporte Estados de Documentos</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('Programacioncamion.reporte_estados_factura')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h4>Reporte de Flete: Indicadores de Valor Transportado</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('Programacioncamion.reporte_estados_factura')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h4>Reporte de Flete: Indicador de Peso Despachado</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('Programacioncamion.reporte_estados_factura')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h4>Historial de Programación de Despacho</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('Programacioncamion.reporte_estados_factura')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h4>Reporte de Flete: Liquidaciones Aprobadas</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('Programacioncamion.reporte_estados_factura')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="{{asset('js/domain.js')}}"></script>

@endsection
