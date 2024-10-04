<div>
    @livewire('configuracion.permisos')
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalSubmenu</x-slot>
        <x-slot name="titleModal">Gestionar Submenú</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="savesSubmenu">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-12 mb-3">
                        <label for="name" class="form-label">Nombre del menú</label>
                        <x-input-general  type="text" id="name" wire:model="name"/>
                        @error('name')
                            <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3 d-flex align-items-center justify-content-center">
                        <div class="form-check">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" wire:model="show" class="form-check-input form-check-primary" id="customColorCheck1">
                                <label class="form-check-label" for="customColorCheck1">¿Visible?</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-7 col-sm-12 mb-3">
                        <label for="controller" class="form-label">Nombre de la función</label>
                        <x-input-general  type="text" id="function" wire:model="function"/>
                        @error('function') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-12 mb-3">
                        <label for="order" class="form-label">Orden del Submenú</label>
                        <x-input-general  type="number" id="order" wire:model="order"/>
                        @error('order') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('form_error'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('form_error') }}
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
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalDeleteSubMenu</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="change_submenu_state">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$message_error}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_submenu') <span class="message-error">{{ $message }}</span> @enderror

                        @error('status_submenu_delete') <span class="message-error">{{ $message }}</span> @enderror

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


    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_submenus" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_submenus" />
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 text-end">

            <a class="btn bg-white text-dark create-new ms-3" href="{{route('configuracion.menus')}}" >
                <span>
                    <i class="fa-solid fa-arrow-left me-sm-1"></i>
                    <span class="d-none d-sm-inline-block">Regresar</span>
                </span>
            </a>

            <x-btn-export wire:click="clear_form_submenu" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalSubmenu" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Submenú
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
                                <th>Nombre del submenú</th>
                                <th>Función</th>
                                <th>Orden</th>
                                <th>¿Visible?</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_submenu) > 0)
                                @php $conteoSubmenu = 1; @endphp
                                @foreach($listar_submenu as $me)
                                    <tr>
                                        <td>{{$conteoSubmenu}}</td>
                                        <td>{{$me->submenu_name}}</td>
                                        <td>{{$me->submenu_function}}</td>
                                        <td>{{$me->submenu_order}}</td>
                                        <td>
                                            <span class="font-bold badge {{$me->submenu_show == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$me->submenu_show == 1 ? 'SI ' : 'NO'}}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-bold badge {{$me->submenu_status == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$me->submenu_status == 1 ? 'Habilitado ' : 'Desabilitado'}}
                                            </span>
                                        </td>

                                        <td>

                                            <x-btn-accion class="text-primary"  wire:click="list_submenu_information('{{ base64_encode($me->id_submenu) }}')" data-bs-toggle="modal" data-bs-target="#modalSubmenu"  >
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>

                                            @if($me->submenu_status == 1)
                                                <x-btn-accion class=" text-danger" wire:click="button_to_disable_submenu('{{ base64_encode($me->id_submenu) }}',0)" data-bs-toggle="modal" data-bs-target="#modalDeleteSubMenu">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @else
                                                <x-btn-accion class=" text-success" wire:click="button_to_disable_submenu('{{ base64_encode($me->id_submenu) }}',1)" data-bs-toggle="modal" data-bs-target="#modalDeleteSubMenu">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-check"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @endif
                                            <x-btn-accion class="text-warning"  wire:click="activate_permissions('{{ base64_encode($me->id_submenu) }}')" data-bs-toggle="modal" data-bs-target="#modal_permissions_per_view" >
                                                <x-slot name="message">
                                                    <i class=" btn_editar fa-solid fa-list"></i>
                                                </x-slot>
                                            </x-btn-accion>
                                        </td>
                                    </tr>
                                    @php $conteoSubmenu++; @endphp
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
        </x-slot>
    </x-card-general-view>
    {{ $listar_submenu->links(data: ['scrollTo' => false]) }}

</div>

@script
<script>
    $wire.on('hideModalSubmenu', () => {
        $('#modalSubmenu').modal('hide');
    });
    $wire.on('hideModalDeleteSubmenu', () => {
        $('#modalDeleteSubMenu').modal('hide');
    });
</script>
@endscript
