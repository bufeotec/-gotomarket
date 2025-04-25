
@extends('layouts.dashboard_template')
@section('title','Reportes de Control Documentario')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Reporte de Control Documentario" />
    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Historial de Programación de Despacho</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('Despachotransporte.reporte_programacion_despacho')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Reporte de Flete: Liquidaciones Aprobadas</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('Despachotransporte.reporte_flete_aprobados')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="{{asset('js/domain.js')}}"></script>

@endsection
