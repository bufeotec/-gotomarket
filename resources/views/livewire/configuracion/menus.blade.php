<div>

    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalMenu</x-slot>
        <x-slot name="titleModal">Gestionar Menú</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveMenu">
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
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <label for="controller" class="form-label">Nombre del controlador</label>
                        <x-input-general  type="text" id="controller" wire:model="controller"/>
                        @error('controller') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="order" class="form-label">Orden del menú</label>
                        <x-input-general  type="number" id="order" wire:model="order"/>
                        @error('order') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="icons" class="form-label">Icono del menú</label>
                        <x-input-general  type="text" id="icons" wire:model="icons"/>
                        @error('icons') <span class="message-error">{{ $message }}</span> @enderror
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
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalDeleteMenu</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_menu">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageDelete}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_menu') <span class="message-error">{{ $message }}</span> @enderror

                        @error('statusMenu') <span class="message-error">{{ $message }}</span> @enderror

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
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_menus" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_menus" />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
{{--            <x-btn-export class="bg-white text-dark">--}}
{{--                <x-slot name="icons">--}}
{{--                    fa-solid fa-arrow-up-from-bracket--}}
{{--                </x-slot>--}}
{{--                Exportar--}}
{{--            </x-btn-export>--}}
            <x-btn-export wire:click="clear_form" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalMenu" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Menú
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
                                <th>Nombre del Menú</th>
                                <th>Controlador</th>
                                <th>Ícono</th>
                                <th>Orden</th>
                                <th>¿Visible?</th>
                                <th>Estado</th>
                                <th>Submenús</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($menus) > 0)
                                @php $conteoMenu = 1; @endphp
                                @foreach($menus as $me)
                                    <tr>
                                        <td>{{$conteoMenu}}</td>
                                        <td>{{$me->menu_name}}</td>
                                        <td>{{$me->menu_controller}}</td>
                                        <td><i class="@php echo $me->menu_icons @endphp"></i></td>
                                        <td>{{$me->menu_order}}</td>
                                        <td>
                                            <span class="font-bold badge {{$me->menu_show == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$me->menu_show == 1 ? 'SI ' : 'NO'}}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-bold badge {{$me->menu_status == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$me->menu_status == 1 ? 'Habilitado ' : 'Desabilitado'}}
                                            </span>
                                        </td>

                                        <td>
                                            @php
                                                $submenu = \Illuminate\Support\Facades\DB::table('submenus')
                                                ->where([['submenu_status','=',1],['id_menu','=',$me->id_menu]])->count();
                                            @endphp
                                            <a href="{{route('configuracion.submenu',['data'=>base64_encode($me->id_menu)])}}" class="btn btn-warning btn-sm text-white">
                                                    {{$submenu}}
                                            </a>
                                        </td>

                                        <td>
                                            <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($me->id_menu) }}')" data-bs-toggle="modal" data-bs-target="#modalMenu">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>

                                            @if($me->menu_status == 1)
                                                <x-btn-accion class=" text-danger" wire:click="btn_disable('{{ base64_encode($me->id_menu) }}',0)" data-bs-toggle="modal" data-bs-target="#modalDeleteMenu">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @else
                                                <x-btn-accion class=" text-success" wire:click="btn_disable('{{ base64_encode($me->id_menu) }}',1)" data-bs-toggle="modal" data-bs-target="#modalDeleteMenu">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-check"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @endif
                                        </td>
                                    </tr>
                                    @php $conteoMenu++; @endphp
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
    {{ $menus->links(data: ['scrollTo' => false]) }}
</div>

@script
    <script>
        $wire.on('hideModal', () => {
            $('#modalMenu').modal('hide');
        });
        $wire.on('hideModalDelete', () => {
            $('#modalDeleteMenu').modal('hide');
        });
    </script>
@endscript
