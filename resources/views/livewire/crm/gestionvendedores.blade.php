<div>

{{--    MODAL AGREGAR / EDITAR VENDEDOR--}}
    <x-modal-general wire:ignore.self >
        <x-slot name="id_modal">modal_vendedor</x-slot>
        <x-slot name="titleModal">Gestionar Vendedor</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="save_vendedor">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="vendedor_intranet_dni" class="form-label">DNI <b class="text-danger">(*)</b></label>
                        <x-input-general type="text" id="vendedor_intranet_dni" wire:model="vendedor_intranet_dni" onkeyup="validar_numeros(this.id)" />
                        @error('vendedor_intranet_dni')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="vendedor_intranet_nombre" class="form-label">Nombre <b class="text-danger">(*)</b></label>
                        <x-input-general type="text" id="vendedor_intranet_nombre" wire:model="vendedor_intranet_nombre"/>
                        @error('vendedor_intranet_nombre')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label class="form-label" for="id_cliente">Cliente <b class="text-danger">(*)</b></label>
                        <select class="form-control" wire:model="id_cliente">
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="vendedor_intranet_correo" class="form-label">Correo <b class="text-danger">(*)</b></label>
                        <x-input-general type="text" id="vendedor_intranet_correo" wire:model="vendedor_intranet_correo"/>
                        @error('vendedor_intranet_correo')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label class="form-label" for="">Departamento <b class="text-danger">(*)</b></label>
                        <select class="form-select" name="id_departamento" id="id_departamento" wire:change="deparTari" wire:model="id_departamento">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_departamento as $de)
                                <option value="{{ $de->id_departamento }}">{{ $de->departamento_nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_departamento')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label class="form-label" for="id_provincia">Provincia <b class="text-danger">(*)</b></label>
                        <select class="form-select" name="id_provincia" id="id_provincia" wire:model="id_provincia" wire:change="proviTari" {{ empty($provincias) ? 'disabled' : '' }}>
                            <option value="">Seleccionar...</option>
                            @foreach($provincias as $pr)
                                <option value="{{ $pr->id_provincia }}" {{ $pr->id_provincia == $id_provincia ? 'selected' : '' }}>
                                    {{ $pr->provincia_nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_provincia')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label class="form-label" for="id_distrito">Distrito <b class="text-danger">(*)</b></label>
                        <select class="form-select" name="id_distrito" id="id_distrito" wire:model="id_distrito" {{ empty($distritos) ? 'disabled' : '' }}>
                            <option value="">Seleccionar...</option>
                            @foreach($distritos as $di)
                                <option value="{{ $di->id_distrito }}" {{ $di->id_distrito == $id_distrito ? 'selected' : '' }}>
                                    {{ $di->distrito_nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_distrito')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="id_rol" class="form-label">Rol <b class="text-danger">(*)</b></label>
                        <select id="id_rol" class=" form-select" wire:model="id_rol">
                            <option value="">Seleccionar</option>
                            @foreach($roles as $indexRol => $re)
                                @if($roleId == 1 || $indexRol > 0)
                                    <option value="{{ $re->id }}">{{ $re->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('id_rol')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Guardar Registros</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL AGREGAR / EDITAR VENDEDOR--}}

{{--    MODAL DESHABILITAR VENDEDOR--}}
    <x-modal-delete wire:ignore.self >
        <x-slot name="id_modal">modal_detele_vendedor</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_vendedor">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageDelete}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_vendedor_intranet') <span class="message-error">{{ $message }}</span> @enderror

                        @error('vendedor_intranet_estado') <span class="message-error">{{ $message }}</span> @enderror

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
{{--    FIN MODAL DESHABILITAR VENDEDOR--}}


    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_gestion_vendedor" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_gestion_vendedor" />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modal_vendedor" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Vendedor
            </x-btn-export>
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
                                <th>Perfil</th>
                                <th>Cliente</th>
                                <th>DNI del Vendedor</th>
                                <th>Nombre del Vendedor</th>
                                <th>Correo</th>
                                <th>Departamento</th>
                                <th>Provincia</th>
                                <th>Distrito</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_vendedores) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($listar_vendedores as $lv)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>
                                            @php
                                                $rol = \Illuminate\Support\Facades\DB::table('roles')->where('id','=',$lv->id_perfil)->first();
                                            @endphp
                                            {{ $rol ? $rol->name : '' }}
                                        </td>
                                        <td>CLIENTE</td>
                                        <td>{{$lv->vendedor_intranet_dni}}</td>
                                        <td>{{$lv->vendedor_intranet_nombre}}</td>
                                        <td>{{$lv->vendedor_intranet_correo}}</td>
                                        <td>
                                            @php
                                                $departamento = \Illuminate\Support\Facades\DB::table('departamentos')->where('id_departamento','=',$lv->id_departamento)->first();
                                            @endphp
                                            {{ $departamento ? $departamento->departamento_nombre : '' }}
                                        </td>
                                        <td>
                                            @php
                                                $provincia = \Illuminate\Support\Facades\DB::table('provincias')->where('id_provincia','=',$lv->id_provincia)->first();
                                            @endphp
                                            {{ $provincia ? $provincia->provincia_nombre : '' }}
                                        </td>
                                        <td>
                                            @php
                                                $distrito = \Illuminate\Support\Facades\DB::table('distritos')->where('id_distrito','=',$lv->id_distrito)->first();
                                            @endphp
                                            {{ $distrito ? $distrito->distrito_nombre : '' }}
                                        </td>
                                        <td>
                                            <span class="font-bold badge {{$lv->vendedor_intranet_estado == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$lv->vendedor_intranet_estado == 1 ? 'Habilitado ' : 'Desabilitado'}}
                                            </span>
                                        </td>
                                        <td>
                                            <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($lv->id_vendedor_intranet) }}')" data-bs-toggle="modal" data-bs-target="#modal_vendedor">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>
                                            @if($lv->vendedor_intranet_estado == 1)
                                                <x-btn-accion class=" text-danger" wire:click="btn_disable('{{ base64_encode($lv->id_vendedor_intranet) }}',0)" data-bs-toggle="modal" data-bs-target="#modal_detele_vendedor">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @else
                                                <x-btn-accion class=" text-success" wire:click="btn_disable('{{ base64_encode($lv->id_vendedor_intranet) }}',1)" data-bs-toggle="modal" data-bs-target="#modal_detele_vendedor">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-check"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @endif
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
    {{ $listar_vendedores->links(data: ['scrollTo' => false]) }}
</div>

@script
<script>
    $wire.on('hide_modal_vendedor', () => {
        $('#modal_vendedor').modal('hide');
    });
    $wire.on('hide_modal_detele_vendedor', () => {
        $('#modal_detele_vendedor').modal('hide');
    });
</script>
@endscript
