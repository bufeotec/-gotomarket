<div>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="user-profile-header-banner">
                            <img src="{{asset('profile-banner.png')}}" alt="Banner image" class="w-100 rounded-top">
                        </div>
                        @php
                            if ($info->profile_picture && file_exists($info->profile_picture)){
                                $rutaImagen = $info->profile_picture;
                            }else{
                                $rutaImagen = "assets/images/faces/1.jpg";
                            }
                        @endphp
                        <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                            <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                                <img src="{{asset($rutaImagen)}}" alt="user image" class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" style="    border: 5px solid white;">
                            </div>
                            <div class="flex-grow-1 mt-3 mt-sm-5">
                                <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                                    <div class="user-profile-info">
                                        <h3>{{$info->name}} {{$info->last_name}}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="row">
                <div class="col-lg-3 col-md-12 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <small class="text-muted text-uppercase">Acerca de</small>
                            <ul class="list-unstyled mb-4 mt-3">
                                <li class="d-flex align-items-center mb-3"><i class="bx bx-user"></i><span class="fw-medium mx-2">Nombre completo:</span> <span class="font-bold text-dark" style="cursor: pointer" data-bs-tooltip="tooltip" data-bs-placement="top" data-bs-original-title="{{$info->name}} {{$info->last_name}}">{{$info->name}}...</span></li>
                                <li class="d-flex align-items-center mb-3"><i class="bx bx-check"></i><span class="fw-medium mx-2">Estado:</span> <span class="font-bold {{$info->users_status == 1 ? 'text-success' : 'text-danger'}}">{{$info->users_status == 1 ? 'Activo' : 'Deshabilitado'}}</span></li>
                                @php
                                    $roleName = "";
                                        if ($auth){
                                            $roleName = Auth::user()->roles()->first()->name;
                                        }else{
                                            $user = \App\Models\User::find($info->id_users);
                                            $role = $user->roles()->first();
                                            if ($role) {
                                                $roleName = $role->name;
                                            }
                                        }
                                @endphp
                                <li class="d-flex align-items-center mb-3"><i class="bx bx-star"></i><span class="fw-medium mx-2">Role:</span> <span style="text-transform: capitalize" class="font-bold text-dark">{{$roleName}}</span></li>
                            </ul>
                            <small class="text-muted text-uppercase">Contactos</small>
                            <ul class="list-unstyled mb-4 mt-3">
                                {{--<li class="d-flex align-items-center mb-3"><i class="bx bx-phone"></i><span class="fw-medium mx-2">Contacto:</span> <span>(51) {{$datos_usuario->telefono}}</span></li>--}}
                                <li class="d-flex align-items-center mb-3"><i class="bx bx-envelope"></i><span class="fw-medium mx-2">Email:</span> <span><a href="mailto:{{$info->email}}">{{$info->email}}</a></span></li>
                                @if($info->users_phone)
                                    <li class="d-flex align-items-center mb-3"><i class="bx bx-envelope"></i><span class="fw-medium mx-2">Teléfono:</span> <span><a href="tel:{{$info->users_phone}}">{{$info->users_phone}}</a></span></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9 col-md-12 col-sm-12">
                    <div class="card">
                        <h5 class="card-header">Detalles del perfil</h5>
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{!$tabSegu ? 'active' : ''}} " id="home-tab" data-bs-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Cuenta</a>
                                </li>
                                @if($auth)
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link  {{$tabSegu ? 'active' : ''}}" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Seguridad</a>
                                    </li>
                                @endif

                            </ul>
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade  {{!$tabSegu ? 'active show' : ''}} " id="home" role="tabpanel" aria-labelledby="home-tab">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                                            <img src="{{asset($rutaImagen)}}"  alt="user-avatar" class="d-block rounded" height="100" width="100" id="imgUpdateUsers">
                                            <div class="button-wrapper">
                                                @if($auth)
                                                    <label for="input_cambiar_imagen_perfil" class="btn bg-primary-goto text-white me-2 mb-4" tabindex="0">
                                                        <span class="d-none d-sm-block">Subir nueva foto</span>
                                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                                        <input onchange="previewImage(this, 'imgUpdateUsers')" wire:model="photo" type="file" id="input_cambiar_imagen_perfil" class="account-file-input" hidden="">
                                                    </label>
                                                    <button type="button" class="btn btn-outline-secondary account-image-reset mb-4" wire:click="deletePhotoUsers">
                                                        <i class="bx bx-reset d-block d-sm-none"></i>
                                                        <span class="d-none d-sm-block">Reiniciar</span>
                                                    </button>
                                                    <p class="text-muted mb-0">JPG, GIF o PNG permitidos. Tamaño máximo de 2 MB</p>
                                                @endif
                                            </div>
                                        </div>
                                        @error('photo')
                                            <span class="message-error">{{ $message }}</span>
                                        @enderror

                                        @if (session()->has('error_update_img_users'))
                                            <div class="alert alert-danger alert-dismissible show fade">
                                                {{ session('error_update_img_users') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif
                                        @if (session()->has('success_update_img_users'))
                                            <div class="alert alert-success alert-dismissible show fade mt-2">
                                                {{ session('success_update_img_users') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif
                                    </div>
                                    <hr class="my-0">
                                    <div class="card-body">
                                        @if($auth)
                                            <form wire:submit.prevent="update_informacion_users">
                                        @endif
                                            <div class="row">
                                                <div class="col-lg-2 mb-4 col-md-4">
                                                    <label for="dni" class="form-label">N° de DNI </label>
                                                    <x-input-general onkeyup="validar_numeros(this.id)" type="text" id="dni" wire:model="dni"/>
                                                    @error('dni')
                                                        <span class="message-error">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-5 mb-4 col-md-4">
                                                    <label for="nombre_users" class="form-label">Nombre (*)</label>
                                                    <x-input-general  type="text" id="nombre_users" wire:model="name"/>
                                                    @error('name')
                                                        <span class="message-error">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-5 mb-4 col-md-4">
                                                    <label for="paterno" class="form-label">Apellido (*)</label>
                                                    <x-input-general  type="text" id="paterno" wire:model="last_name"/>
                                                    @error('last_name')
                                                        <span class="message-error">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="telefono">N° de teléfono</label>
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text">PE (+51)</span>
                                                        <x-input-general onkeyup="validar_numeros(this.id)"  type="text" id="telefono" wire:model="phone"/>
                                                    </div>
                                                    @error('phone')
                                                        <span class="message-error">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="nacimiento" class="form-label">Fecha de nacimiento</label>
                                                    <x-input-general  type="date" id="nacimiento" wire:model="naci"/>
                                                    @error('naci')
                                                        <span class="message-error">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="email" class="form-label">E-mail (*)</label>
                                                    <x-input-general  type="email" id="email" wire:model="email" placeholder="example@example.com" />
                                                    @error('email')
                                                        <span class="message-error">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="username" class="form-label">Nombre de usuario (*)</label>
                                                    <x-input-general  type="text" id="username" wire:model="username" />
                                                    @error('username')
                                                        <span class="message-error">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                @if (session()->has('error'))
                                                    <div class="alert alert-danger alert-dismissible show fade">
                                                        {{ session('error') }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                    </div>
                                                @endif
                                                @if (session()->has('success'))
                                                    <div class="alert alert-success alert-dismissible show fade mt-2">
                                                        {{ session('success') }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                    </div>
                                                @endif
                                            </div>
                                            @if($auth)
                                                <div class="mt-2">
                                                    <button type="submit" class="btn bg-primary-goto text-white me-2 mb-2">Guardar cambios</button>
                                                    <button type="reset" class="btn btn-outline-secondary">Cancelar</button>
                                                </div>
                                            @endif
                                        @if($auth)
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                @if($auth)
                                    <div class="tab-pane fade {{$tabSegu ? 'active show' : ''}}" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                        <h5 class="card-header">Cambiar la contraseña</h5>
                                        <div class="card-body">
                                            <form  method="POST" wire:submit="update_password_users" class="fv-plugins-bootstrap5 fv-plugins-framework">
                                                @csrf
                                                <div class="row">
                                                    <div class="mb-3 col-md-6 form-password-toggle fv-plugins-icon-container fv-plugins-bootstrap5-row-invalid">
                                                        <label class="form-label" for="currentPassword">Contraseña actual</label>
                                                        <div class="input-group input-group-merge has-validation">
                                                            <input class="form-control" type="password" wire:model="current_password" name="currentPassword" id="currentPassword" placeholder="············">
                                                            <span class="input-group-text cursor-pointer toggle-password">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </span>
                                                        </div>
                                                        @error('current_password')
                                                        <span class="message-error">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-6 form-password-toggle fv-plugins-icon-container fv-plugins-bootstrap5-row-invalid">
                                                        <label class="form-label" for="newPassword">Nueva contraseña</label>
                                                        <div class="input-group input-group-merge has-validation">
                                                            <input class="form-control" type="password" wire:model="New_password" id="newPassword" name="newPassword" placeholder="············">
                                                            <span class="input-group-text cursor-pointer toggle-password">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </span>
                                                        </div>
                                                        @error('New_password')
                                                        <span class="message-error">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="mb-3 col-md-6 form-password-toggle fv-plugins-icon-container fv-plugins-bootstrap5-row-invalid">
                                                        <label class="form-label" for="newPassword_confirmation">Confirmar nueva contraseña</label>
                                                        <div class="input-group input-group-merge has-validation">
                                                            <input class="form-control" type="password" wire:model="confirm_new_password" name="newPassword_confirmation" id="newPassword_confirmation" placeholder="············">
                                                            <span class="input-group-text cursor-pointer toggle-password">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </span>
                                                        </div>
                                                        @error('confirm_new_password')
                                                        <span class="message-error">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-4">
                                                    <p class="fw-medium mt-2">Requisitos de Contraseña:</p>
                                                    <ul class="ps-3 mb-0">
                                                        <li class="mb-1">Mínimo 8 caracteres de longitud: cuantos más, mejor</li>
                                                        <li class="mb-1">Al menos un carácter en mayúscula</li>
                                                        <li>Al menos un número, símbolo o carácter especial</li>
                                                    </ul>
                                                </div>
                                                @if (session()->has('error_update_password_users'))
                                                    <div class="alert alert-danger alert-dismissible show fade">
                                                        {{ session('error_update_password_users') }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                    </div>
                                                @endif
                                                @if (session()->has('success_update_password_users'))
                                                    <div class="alert alert-success alert-dismissible show fade mt-2">
                                                        {{ session('success_update_password_users') }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                    </div>
                                                @endif
                                                <div class="col-12 mt-1">
                                                    <button type="submit" class="btn bg-primary-goto text-white me-2 mb-2">Guardar cambios</button>
                                                    <button type="reset" class="btn btn-outline-secondary">Cancelar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
