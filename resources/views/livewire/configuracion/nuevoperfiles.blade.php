<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <form wire:submit.prevent="guardar_perfil">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <h6>Datos del perfil</h6>
                                <hr>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                        <label for="nombre_perfil" class="form-label">Nombre del perfil</label>
                                        <x-input-general  type="text" id="nombre_perfil" wire:model="nombre_perfil"/>
                                        @error('nombre_perfil') <span class="message-error">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                        <label for="descripcion_perfil" class="form-label">Descripción del perfil</label>
                                        <textarea id="descripcion_perfil" name="descripcion_perfil" wire:model="descripcion_perfil" class="form-control" rows="3"></textarea>
                                        @error('descripcion_perfil') <span class="message-error">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-lg-12 col-sm-12 mb-3 mt-4">
                                        <h6>Usuarios asignados al perfil</h6>
                                        <hr>
                                    </div>

                                    <div class="col-lg-12 col-sm-12 mb-3 d-flex justify-content-center align-content-center">
                                        <select class="form-select" name="id_users" id="id_users" wire:model="id_users">
                                            <option value="">Seleccionar...</option>
                                            @foreach($listar_users as $ld)
                                                <option value="{{ $ld->id_users }}">{{ $ld->name }} - {{$ld->username}}</option>
                                            @endforeach
                                        </select>
                                        <div class="align-content-center ms-3">
                                            <a class="btn btn-success text-white btn-sm" wire:click="agregar_usuario">
                                                <i class="fa-solid fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="loader mt-2" wire:loading wire:target="agregar_usuario"></div>
                                    </div>

                                    @if (session()->has('error_select_user'))
                                        <div class="alert alert-danger alert-dismissible show fade mt-2">
                                            {{ session('error_select_user') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    @if(count($users_seleccionados) > 0)
                                        <div class="col-lg-12 col-sm-12 mb-5">
                                            <x-table-general>
                                                <x-slot name="thead">
                                                    <tr>
                                                        <th>N°</th>
                                                        <th>Nombre usuario</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </x-slot>
                                                <x-slot name="tbody">
                                                    @php $a = 1 @endphp
                                                    @foreach($users_seleccionados as $index => $us)
                                                        <tr>
                                                            <td>{{ $a }}</td>
                                                            <td>{{ $us['name'] }} - {{ $us['username'] }}</td>
                                                            <td>
                                                                <button class="btn btn-danger btn-sm"
                                                                        wire:click="eliminar_usuario({{ $index }})">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @php $a++; @endphp
                                                    @endforeach
                                                </x-slot>
                                            </x-table-general>
                                        </div>
                                    @else
                                        <h6 class="mt-3 text-danger">
                                            No se han seleccionado usuarios.
                                        </h6>
                                    @endif

                                    <div class="col-lg-12 col-sm-12 mb-3 mt-4">
                                        <h6>Permisos de usuario</h6>
                                        <hr>
                                    </div>

                                    <div class="col-lg-12 col-sm-12 mb-3 mt4">
                                        @foreach($menus_show as $ms)
                                            <h5 class="mb-3 mt-3">{{ $ms->menu_name }}</h5>
                                            {{--                                            <div class="mb-3 ms-4 d-flex align-content-center text-center">
                                                <input
                                                    type="checkbox"
                                                    class="checkbox form-check-input"
                                                    wire:change="toggleTodosConsulta({{ $ms->id_menu }})"
                                                    @checked($permisos[$ms->id_menu]['consultar'] ?? false)
                                                />
                                                <h6 style="color: #607080" class="text-capitalize mt-1 ms-2">todos solo consulta</h6>
                                            </div>

                                            <div class="mb-3 ms-4 d-flex align-content-center text-center">
                                                <input
                                                    type="checkbox"
                                                    class="checkbox form-check-input"
                                                    wire:change="toggleTodosEditar({{ $ms->id_menu }})"
                                                    @checked($permisos[$ms->id_menu]['editar'] ?? false)
                                                />
                                                <h6 style="color: #607080" class="text-capitalize mt-1 ms-2">todos editar / gestionar</h6>
                                            </div>--}}
                                            @if($ms->submenus->count() > 0)
                                                <div class="ms-5">
                                                    @foreach($ms->submenus as $subm)
                                                        <div class="mb-2 d-flex align-content-center text-center">
                                                            <h6 style="color: #607080" class="text-capitalize mt-1 ms-2">{{ $subm->submenu_name }}</h6>
                                                        </div>

                                                        @if(isset($subm->permisos) && $subm->permisos->count() > 0)
                                                            <div class="ms-4">
                                                                @foreach($subm->permisos as $permiso)
                                                                    <div class="mb-2 d-flex align-items-center">
                                                                        <input
                                                                            type="checkbox"
                                                                            class="form-check-input me-2"
                                                                            id="permiso_{{ $permiso->id }}"
                                                                            wire:model="permisosSeleccionados.{{ $permiso->id }}"
                                                                        />
                                                                        <label for="permiso_{{ $permiso->id }}" style="color: #607080">
                                                                            {{ $permiso->descripcion }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-1"></div>

                            <div class="col-lg-5 col-md-5 col-sm-12 mb-3">
                                <div class="row">
                                    <div class="col-lg-12 d-flex align-items-center justify-content-between mb-3">
                                        <label for="credito_check" class="form-label">Perfil vendedor</label>
                                        <div class="form-check form-switch">

                                            <input class="form-check-input" type="checkbox" role="switch" name="credito_check" id="credito_check"  wire:model="credito_check">
                                            <label class="form-check-label" for="credito_check"></label>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 d-flex align-items-center justify-content-between mb-3">
                                        <label for="codigo" class="form-label">
                                            Código del perfil
                                        </label>
                                        <h5>{{ $codigo_perfil }}</h5>
                                        @error('codigo') <span class="message-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                                <button type="submit" class="btn btn-success text-white">Guardar Registros</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
