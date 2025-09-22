@extends('layouts.dashboard_template')
@section('title','CRM')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Opciones." />
    </div>

    <div class="row">
        @if (Gate::allows('gestion_vendedores'))
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>GESTIÓN DE VENDEDORES</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a href="{{route('CRM.gestion_vendedores')}}" class="btn btn-success">Ingresar</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (Gate::allows('gestion_campanias'))
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>GESTIÓN DE CAMPAÑAS</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a href="{{route('CRM.gestion_campanias')}}" class="btn btn-success">Ingresar</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (Gate::allows('gestion_premios'))
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>GESTIÓN DE PREMIOS</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a href="{{route('CRM.gestion_premios')}}" class="btn btn-success">Ingresar</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (Gate::allows('gestion_puntos'))
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>GESTIÓN DE PUNTOS</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a href="{{route('CRM.gestion_puntos')}}" class="btn btn-success">Ingresar</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (Gate::allows('seleccionar_premios'))
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>SELECCIONAR PREMIOS</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a href="{{route('CRM.seleccionar_premios')}}" class="btn btn-success">Ingresar</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (Gate::allows('reporte_campania'))
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>REPORTES DE SISTEMA DE PUNTOS</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a href="{{route('CRM.reporte_campania')}}" class="btn btn-success">Ingresar</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (Gate::allows('historial_puntos'))
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>HISTORIAL DE PUNTOS</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a href="{{route('CRM.historial_puntos')}}" class="btn btn-success">Ingresar</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Mensaje si no tiene ningún permiso --}}
    @if (!Gate::any(['gestion_vendedores', 'gestion_campanias', 'gestion_premios', 'gestion_puntos', 'seleccionar_premios', 'reporte_campania', 'historial_puntos']))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <h5>No tienes permisos para acceder a ninguna opción del CRM</h5>
                    <p>Contacta con el administrador para solicitar acceso.</p>
                </div>
            </div>
        </div>
    @endif

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
