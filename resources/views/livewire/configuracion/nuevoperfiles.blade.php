<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible show fade mt-2">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <form wire:submit.prevent="guardar_editar_perfil">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <h6>Datos del perfil</h6>
                                <hr>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                        <label for="name" class="form-label">Nombre del perfil</label>
                                        <x-input-general  type="text" id="name" wire:model="name"/>
                                        @error('name') <span class="message-error">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                        <label for="rol_descripcion" class="form-label">Descripción del perfil</label>
                                        <textarea id="rol_descripcion" name="rol_descripcion" wire:model="rol_descripcion" class="form-control" rows="3"></textarea>
                                        @error('rol_descripcion') <span class="message-error">{{ $message }}</span> @enderror
                                    </div>

{{--                                    <div class="col-lg-12 col-sm-12 mb-3 mt-4">--}}
{{--                                        <h6>Usuarios asignados al perfil</h6>--}}
{{--                                        <hr>--}}
{{--                                    </div>--}}

{{--                                    <div class="col-lg-12 col-sm-12 mb-3 d-flex justify-content-center align-content-center">--}}
{{--                                        <select class="form-select" name="id_users" id="id_users" wire:model="id_users">--}}
{{--                                            <option value="">Seleccionar...</option>--}}
{{--                                            @foreach($listar_users as $ld)--}}
{{--                                                <option value="{{ $ld->id_users }}">{{ $ld->name }} - {{$ld->username}}</option>--}}
{{--                                            @endforeach--}}
{{--                                        </select>--}}
{{--                                        <div class="align-content-center ms-3">--}}
{{--                                            <a class="btn btn-success text-white btn-sm" wire:click="agregar_usuario">--}}
{{--                                                <i class="fa-solid fa-plus"></i>--}}
{{--                                            </a>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

{{--                                    <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                        <div class="loader mt-2" wire:loading wire:target="agregar_usuario"></div>--}}
{{--                                    </div>--}}

{{--                                    <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                        <div class="loader mt-2" wire:loading wire:target="eliminar_usuario"></div>--}}
{{--                                    </div>--}}

{{--                                    @if (session()->has('error_select_user'))--}}
{{--                                        <div class="alert alert-danger alert-dismissible show fade mt-2">--}}
{{--                                            {{ session('error_select_user') }}--}}
{{--                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--                                        </div>--}}
{{--                                    @endif--}}

{{--                                    @if(count($users_seleccionados) > 0)--}}
{{--                                        <div class="col-lg-12 col-sm-12 mb-5">--}}
{{--                                            <x-table-general>--}}
{{--                                                <x-slot name="thead">--}}
{{--                                                    <tr>--}}
{{--                                                        <th>N°</th>--}}
{{--                                                        <th>Nombre usuario</th>--}}
{{--                                                        <th>Acciones</th>--}}
{{--                                                    </tr>--}}
{{--                                                </x-slot>--}}
{{--                                                <x-slot name="tbody">--}}
{{--                                                    @php $a = 1 @endphp--}}
{{--                                                    @foreach($users_seleccionados as $index => $us)--}}
{{--                                                        <tr>--}}
{{--                                                            <td>{{ $a }}</td>--}}
{{--                                                            <td>{{ $us['name'] }} - {{ $us['username'] }}</td>--}}
{{--                                                            <td>--}}
{{--                                                                <a class="btn btn-danger btn-sm"--}}
{{--                                                                        wire:click="eliminar_usuario({{ $index }})">--}}
{{--                                                                    <i class="fa-solid fa-trash"></i>--}}
{{--                                                                </a>--}}
{{--                                                            </td>--}}
{{--                                                        </tr>--}}
{{--                                                        @php $a++; @endphp--}}
{{--                                                    @endforeach--}}
{{--                                                </x-slot>--}}
{{--                                            </x-table-general>--}}
{{--                                        </div>--}}
{{--                                    @else--}}
{{--                                        <h6 class="mt-3 text-danger">--}}
{{--                                            No se han seleccionado usuarios.--}}
{{--                                        </h6>--}}
{{--                                    @endif--}}

                                    <div class="col-lg-12 col-sm-12 mb-3 mt-4">
                                        <h6>Permisos de usuario</h6>
                                        <hr>
                                    </div>

                                    <div class="col-lg-12 col-sm-12 mb-3 mt4">
                                        @foreach($listar_permisos_general as $index => $v)
                                            <div class="col-lg-12">
                                                <div class="accordion" id="accordionExample_{{$index}}">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{$index}}" aria-expanded="false" aria-controls="collapseOne_{{$index}}">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" wire:model="check"  id="edit_check_permisos_{{ $v->id }}" value="{{ $v->id }}" >
                                                                    <label class="form-check-label" for="edit_check_permisos_{{ $v->id }}"> {{ $v->menu_name }} </label>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseOne_{{$index}}" class="accordion-collapse collapse " data-bs-parent="#accordionExample_{{$index}}">
                                                            <div class="">
                                                                <ul class="list-group">
                                                                    @foreach($v->sub as $s)
                                                                        <li class="list-group-item">
                                                                            <input class="form-check-input  me-1"  wire:model="check" type="checkbox"  id="edit_check_permisos_{{ $s->id }}" value="{{ $s->id }}" >
                                                                            <label class="form-check-label" for="edit_check_permisos_{{ $s->id }}"> {{ $s->descripcion }} </label>
                                                                            <ul class="list-group mt-2">
                                                                                @if(!empty($s->permisos))
                                                                                    @foreach($s->permisos as $p)
                                                                                        <li class="list-group-item" style="border: none!important;">
                                                                                            <input class="form-check-input  me-1"  wire:model="check" type="checkbox"  id="edit_check_permisos_{{ $p->id }}" value="{{ $p->id }}"  >
                                                                                            <label class="form-check-label" for="edit_check_permisos_{{ $p->id }}"> {{ $p->descripcion }} </label>
                                                                                        </li>
                                                                                    @endforeach
                                                                                @endif
                                                                            </ul>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-1"></div>

                            <div class="col-lg-5 col-md-5 col-sm-12 mb-3">
                                <div class="row">
{{--                                    <div class="col-lg-12 d-flex align-items-center justify-content-between mb-3">--}}
{{--                                        <label for="rol_vendedor" class="form-label">Perfil vendedor</label>--}}
{{--                                        <div class="form-check form-switch">--}}

{{--                                            <input class="form-check-input" type="checkbox" role="switch" name="credito_check" id="rol_vendedor"  wire:model="rol_vendedor">--}}
{{--                                            <label class="form-check-label" for="rol_vendedor"></label>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

                                    <div class="col-lg-12 d-flex align-items-center justify-content-between mb-3">
                                        <label for="codigo" class="form-label">
                                            Código del perfil
                                        </label>
                                        <h5 >{{ $codigo_perfil }}</h5>
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
