<div>
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modal_permissions_per_view</x-slot>
        <x-slot name="titleModal">Permisos</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 mb-4">
                    <form wire:submit.prevent="form_permissions_per_view">
                        <div class="row gap-2 align-items-center">
                            <div class="col-lg-1 col-md-12 col-sm-12 mb-2">
                                <b>Función: </b>
                            </div>
                            <div class="col-lg-8 col-md-12 col-sm-12 mb-2 d-flex align-items-center ">
                                <input type="text" wire:model="permissions_name"   class=" form-control form-control-sm" style="width: 65% !important;margin-right: 6%;">
                                <button class="btn btn-sm bg-success text-white" type="submit"><i class="fa fa-plus"></i> Guardar Permiso</button>
                            </div>
                            @error('permissions_name')
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                <span class="message-error">{{ $message }}</span>
                            </div>
                            @enderror
                            @if (session()->has('form_error_permissions'))
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="alert alert-danger alert-dismissible show fade">
                                        {{ session('form_error_permissions') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            @endif
                            @if (session()->has('success_permissions'))
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="alert alert-success alert-dismissible show fade">
                                        {{ session('success_permissions') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <x-table-general class="table-bordered">
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>ID</th>
                                <th>Permiso</th>
                                <th>Acción</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($permissions_submenu) > 0)
                                @php $conteoPermiso = 1; @endphp
                                @foreach($permissions_submenu as $permi)
                                    <tr>
                                        <td>{{$conteoPermiso}}</td>
                                        <td>{{$permi->id}}</td>
                                        <td>{{$permi->name}}</td>
                                        <td>
                                            <button class="btn btn-sm bg-danger text-white" wire:click="delete_permissions({{$permi->id}})" >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @php $conteoPermiso++; @endphp
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
    </x-modal-general>

</div>
