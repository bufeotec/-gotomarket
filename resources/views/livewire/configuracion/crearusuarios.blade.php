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

        <style>
            .iconsPreviewImage {
                position: relative;
                top: -12px;
                left: 120px;
                background: white;
                width: 24px;
                border-radius: 50%;
                color: black;
                cursor: pointer;
                height: 24px;
                text-align: center;

        </style>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <form wire:submit.prevent="save_usuario">
                        <div class="row ">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-1">
                                <small class="text-primary" style="font-size: 11pt">Datos personales</small>
                                <hr>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="row align-items-center">
                                    <div class="col-lg-7 col-md-7 col-sm-12 ">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                                <label for="name" class="form-label">Nombre (*)</label>
                                                <x-input-general   type="text" id="name" wire:model="name" wire:input="generateUsername" />
                                                @error('name')
                                                <span class="message-error">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                                <label for="last_name" class="form-label">Apellido (*)</label>
                                                <x-input-general  type="text" id="last_name" wire:model="last_name" wire:input="generateUsername"/>
                                                @error('last_name')
                                                <span class="message-error">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-5 col-sm-12 d-flex align-items-center justify-content-center">
                                        <div style="width: 150px;" wire:ignore>
                                            <img src="" id="previeImageUsers" class="w-100" style="margin-top: 10%;" alt="">
                                            <label for="profile_picture" class="iconsPreviewImage">
                                                <i class="fa-solid fa-camera "></i>
                                            </label>
                                        </div>
                                        <input type="file" class="d-none" id="profile_picture" name="profile_picture" onchange="previewImage(this,'previeImageUsers')" wire:model="profile_picture" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-1">
                                <small class="text-primary" style="font-size: 11pt">Datos del usuario</small>
                                <hr>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                <label for="username" class="form-label">Nombre de Usuario (*)</label>
                                <x-input-general type="text" id="username" wire:model="username"/>
                                @error('username')<span class="message-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                <label for="email" class="form-label">Correo Electronico (*)</label>
                                <x-input-general  type="email" id="email" wire:model="email"/>
                                @error('email')<span class="message-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                <label for="users_cargo" class="form-label">Cargo</label>
                                <x-input-general   type="text" id="users_cargo" wire:model="users_cargo"/>
                                @error('users_cargo')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                <label for="password" class="form-label">{{!empty($id_users) ? 'Actualizar Contraseña': 'Contraseña'}}</label>
                                <div class="input-group input-group-merge has-validation">
                                    <x-input-general type="password" id="password" wire:model="password" />
                                    <span class="input-group-text cursor-pointer toggle-password bg-white" style="cursor: pointer!important;">
                                    <i class="fa-solid fa-eye"></i>
                                </span>
                                </div>
                                @error('password')
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
                                @error('profile_picture')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 mt-3">
                                <small class="text-primary" style="font-size: 11pt">Perfiles en Intranet</small>
                                <hr>
                            </div>

                            <div class="col-lg-12">
                                <div class="row align-items-center">
                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-12 col-sm-12 mb-3 d-flex justify-content-center align-content-center">
                                                <select class="form-select" name="id_rol" id="id_vendedor" wire:model="id_rol">
                                                    <option value="">Seleccionar...</option>
                                                    @foreach($listar_perfiles as $lp)
                                                        <option value="{{ $lp->id }}">{{$lp->rol_codigo}} - {{$lp->name}}</option>
                                                    @endforeach
                                                </select>
                                                <div class="align-content-center ms-3">
                                                    <a class="btn btn-success text-white btn-sm" wire:click="agregar_perfil">
                                                        <i class="fa-solid fa-plus"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="loader mt-2" wire:loading wire:target="agregar_perfil"></div>
                                            </div>

                                            @if (session()->has('error_select_perfil'))
                                                <div class="alert alert-danger alert-dismissible show fade mt-2">
                                                    {{ session('error_select_perfil') }}
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            @endif

                                            @if(count($perfil_seleccionado) > 0)
                                                <div class="col-lg-12 col-sm-12 mb-5">
                                                    <x-table-general>
                                                        <x-slot name="thead">
                                                            <tr>
                                                                <th>N°</th>
                                                                <th>Perfiles Asignados</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </x-slot>
                                                        <x-slot name="tbody">
                                                            @php $a = 1 @endphp
                                                            @foreach($perfil_seleccionado as $index => $us)
                                                                <tr>
                                                                    <td>{{ $a }}</td>
                                                                    <td>{{ $us['rol_codigo'] }} - {{ $us['name'] }}</td>
                                                                    <td>
                                                                        <button class="btn btn-danger btn-sm"
                                                                                wire:click="eliminar_perfil({{ $index }})">
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
                                                    No se a seleccionado el perfil.
                                                </h6>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 mt-3 d-flex justify-content-between">
                                <label for="rol_vendedor" class="text-primary" style="font-size: 11pt">Perfil vendedor</label>
                                <div class="form-check form-switch mb-2 d-flex align-items-end">
                                    <input class="form-check-input" type="checkbox" role="switch" name="credito_check" id="rol_vendedor" wire:model.live="rol_vendedor">
                                    <label class="form-check-label" for="rol_vendedor"></label>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                            <div class="col-lg-6 mb-3"><hr></div>

                            @if($rol_vendedor)
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                            <div class="row">
                                                <div class="col-lg-12 col-sm-12 mb-3 d-flex justify-content-center align-content-center">
                                                    <select class="form-select" name="id_vendedor" id="id_vendedor" wire:model="id_vendedor">
                                                        <option value="">Seleccionar...</option>
                                                        @foreach($listar_vendedores as $lv)
                                                            <option value="{{ $lv->id_vendedor }}">{{$lv->vendedor_des}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="align-content-center ms-3">
                                                        <a class="btn btn-success text-white btn-sm" wire:click="agregar_vendedor">
                                                            <i class="fa-solid fa-plus"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                                <div class="col-lg-12 col-md-12 col-sm-12">
                                                    <div class="loader mt-2" wire:loading wire:target="agregar_vendedor"></div>
                                                </div>

                                                @if (session()->has('error_select_vendedor'))
                                                    <div class="alert alert-danger alert-dismissible show fade mt-2">
                                                        {{ session('error_select_vendedor') }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                    </div>
                                                @endif

                                                @if(count($vendedor_seleccionados) > 0)
                                                    <div class="col-lg-12 col-sm-12 mb-5">
                                                        <x-table-general>
                                                            <x-slot name="thead">
                                                                <tr>
                                                                    <th>N°</th>
                                                                    <th>Código INTRANET</th>
                                                                    <th>Código Vendedor STARSOFT</th>
                                                                    <th>Nombre vendedor</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </x-slot>
                                                            <x-slot name="tbody">
                                                                @php $a = 1 @endphp
                                                                @foreach($vendedor_seleccionados as $index => $us)
                                                                    <tr>
                                                                        <td>{{ $a }}</td>
                                                                        <td>{{ $us['vendedor_codigo_intranet'] }}</td>
                                                                        <td>{{ $us['vendedor_codigo_vendedor_starsoft'] }}</td>
                                                                        <td>{{ $us['vendedor_des'] }}</td>
                                                                        <td>
                                                                            <button class="btn btn-danger btn-sm"
                                                                                    wire:click="eliminar_vendedor({{ $index }})">
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
                                                        No se han seleccionado vendedores.
                                                    </h6>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row">
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

@script
<script>
    $wire.on('updateUserImagePreview',function(event) {
        const image = document.getElementById('previeImageUsers');
        if (image) {
            console.log(event[0].image);
            image.src = event[0].image;
        }
    });
</script>
@endscript
