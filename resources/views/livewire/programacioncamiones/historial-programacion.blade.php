<div>
    <div class="row align-items-center mt-2">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_desde" class="form-label">Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="fecha_hasta" class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control">
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Fecha de Entrega</th>
                                <th>Número de Programación</th>
                                <th>Usuario de Aprobación</th>
                                <th>Fecha de Aprobación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($resultado) > 0)
                                @php $conteoGeneral = 1; @endphp
                                @foreach($resultado as $r)
                                        <tr>
                                            <td>{{$conteoGeneral}}</td>
                                            <td>{{date('d-m-Y',strtotime($r->programacion_fecha))}}</td>
                                            <td>Número de Programación</td>
                                            <td>Usuario de Aprobación</td>
                                            <td>Fecha de Aprobación</td>
                                            <td>Estado</td>
                                            <td>
                                                <x-btn-accion style="color: green" class=" text-green"  wire:click="btn_validar_aprobacion('{{ base64_encode($r->id_programacion) }}')" data-bs-toggle="modal" data-bs-target="#modalValidarTarifario"><x-slot name="message"><i class="fa-solid fa-check"></i></x-slot></x-btn-accion>
                                            </td>
                                        </tr>
                                    @php $conteoGeneral++; @endphp
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


