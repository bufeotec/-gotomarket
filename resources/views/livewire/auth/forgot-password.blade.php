<div>
    <form wire:submit.prevent="Send_recovery_link_by_email" class="pt-3">
        @csrf
        <div class="form-group">
            <input type="text"  class="form-control form-control-lg border_ra  @error('email') is-invalid @enderror " wire:model.defer="email"  autofocus id="email" placeholder="Correo electrónico">

            @error('email')
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
            <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn" >ENVIAR ENLACE DE REINICIO</button>
        </div>

    </form>
    <div class="mt-4">
        <a href="{{route('login')}}" class="d-flex align-items-center justify-content-center">
            <i class="fa-solid fa-chevron-left mr-2"></i>
            Regresar
        </a>
    </div>
</div>
