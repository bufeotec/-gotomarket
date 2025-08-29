@extends('layouts.dashboard_template')
@section('title','CRM')
@section('content')

    <div class="page-heading">
        <x-navegation-view text="Opciones." />
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>STOCK</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('CRM.stock')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
