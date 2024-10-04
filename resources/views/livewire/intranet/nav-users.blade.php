<div>
    @php
        if ($userAuth){
            $user = \App\Models\User::find($userAuth->id_users);
            $role = $user->roles()->first();
            $roleName = "";
            if ($role) {
                $roleName = $role->name;
            }
             if (file_exists($userAuth->profile_picture)){
                $rutaImagen = $userAuth->profile_picture;
            }else{
                $rutaImagen = "assets/images/faces/1.jpg";
            }
        }
    @endphp

    <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
        <div class="user-menu d-flex">
            <div class="user-name text-end me-3">

                <h6 class="mb-0 text-gray-600">
                    @if($userAuth)
                        {{ $userAuth->name }}
                    @endif
                </h6>
                <p class="mb-0 text-sm text-gray-600">{{$roleName }}</p>
            </div>
            <div class="user-img d-flex align-items-center">
                <div class="avatar avatar-md">
                    <img src="{{asset($rutaImagen)}}">
                </div>
            </div>
        </div>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
        <li>
            <h6 class="dropdown-header">
                Hola,
                @if($userAuth)
                    {{ $userAuth->name }}
                @endif
            </h6>
        </li>
        <li>
            <a class="dropdown-item" href="{{route('intranet.perfil')}}">
                <i class="icon-mid bi bi-person me-2"></i>Mi perfil
            </a>
        </li>
{{--        <li>--}}
{{--            <a class="dropdown-item" href="#">--}}
{{--                <i class="icon-mid bi bi-gear me-2"></i>Ajustes--}}
{{--            </a>--}}
{{--        </li>--}}
{{--        <li>--}}
{{--            <a class="dropdown-item" href="#">--}}
{{--                <i class="icon-mid bi bi-wallet me-2"></i>Billetera--}}
{{--            </a>--}}
{{--        </li>--}}
        <li>
            <hr class="dropdown-divider">
        </li>
        <li>
            @livewire('auth.logout')
        </li>
    </ul>
</div>
