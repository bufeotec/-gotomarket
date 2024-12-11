<div>
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
                                            <td>{{$rs->transportista_razon_social}}</td>
                                            <td>{{$rs->liquidacion_serie}}</td>
                                            <td>{{$rs->liquidacion_correlativo}}</td>
                                            <td>{{$rs->liquidacion_ruta_comprobante}}</td>
                                            <td></td>
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
