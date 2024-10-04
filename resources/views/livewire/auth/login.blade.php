<div>
    <form wire:submit.prevent="login" class="pt-3">
        @csrf
        <div class="form-group">
            <input type="text"  class="form-control form-control-lg   @error('email') is-invalid @enderror " wire:model.defer="email"  autofocus id="email" placeholder="Nombre de usuario o correo electrónico">
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <div class="input-group input-group-merge has-validation">
                <input type="password" class="form-control form-control-lg   @error('password') is-invalid @enderror" wire:model.defer="password"  id="password" placeholder="Contraseña">
                <span class="input-group-text cursor-pointer toggle-password bg-white" style="cursor: pointer!important;">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>
            @error('password')
            <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
            @enderror
        </div>


        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (session()->has('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-3">
            <button type="submit" class="btn btn-block   btn-lg font-weight-medium auth-form-btn bg-primary-goto text-white" >INICIAR SESIÓN</button>
        </div>

        <div class="my-2 d-flex justify-content-between align-items-center">

            <div class="form-check form-check-flat form-check-primary">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" wire:model="remember">
                    Recordar sesión
                    <i class="input-helper"></i></label>
            </div>
            <a href="{{route('password.request')}}" class="auth-link text-black">¿Has olvidado tu contraseña?</a>
        </div>
    </form>
    <style>
        .border_ra{
            border-radius: 15px!important;
        }
    </style>
</div>

@assets
    <script src="{{asset('js/domain.js')}}"></script>
@endassets
@script
    <script>
        $wire.on('redirectAfterSuccess', function (url) {
            setTimeout(function() {
                window.location.href = url;
            }, 2000);
        });
    </script>
@endscript
