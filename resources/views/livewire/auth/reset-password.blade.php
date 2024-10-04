<div>
    <form wire:submit.prevent="Send_recovery_link_by_email" class="pt-3">
        @csrf
        <div class="form-group">
            <label for="password" class="form-label">Nueva contraseña</label>
            <div class="input-group input-group-merge has-validation">
                <input type="password"  class="form-control form-control-lg border_ra  @error('password') is-invalid @enderror " wire:model.defer="password"  autofocus id="password">
                <span class="input-group-text cursor-pointer toggle-password" style="cursor: pointer!important;">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>

            @error('password')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="confirmPassword" class="form-label">Confirmar Contraseña</label>
            <div class="input-group input-group-merge has-validation">
                <input type="password"  class="form-control form-control-lg border_ra  @error('confirmPassword') is-invalid @enderror " wire:model.defer="confirmPassword"  autofocus id="confirmPassword">
                <span class="input-group-text cursor-pointer toggle-password" style="cursor: pointer!important;">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>

            @error('confirmPassword')
            <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>


        <div class="mb-4">
            <p class="fw-medium mt-2">Requisitos de Contraseña:</p>
            <ul class="ps-3 mb-0">
                <li class="mb-1">Mínimo 8 caracteres de longitud: cuantos más, mejor</li>
                <li class="mb-1">Al menos un carácter en mayúscula</li>
                <li>Al menos un número, símbolo o carácter especial</li>
            </ul>
        </div>

        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->has('error'))
            <div class="alert alert-danger">
                {{ $errors->first('error') }}
            </div>
        @endif

        @if (session()->has('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-3">
            <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn" > ESTABLECER NUEVA CONTRASEÑA</button>
        </div>

    </form>
    <div class="mt-4">
        <a href="{{route('login')}}" class="d-flex align-items-center justify-content-center">
            <i class="fa-solid fa-chevron-left mr-2"></i>
            Regresar
        </a>
    </div>
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
