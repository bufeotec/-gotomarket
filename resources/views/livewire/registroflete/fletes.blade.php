<div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_transportistas" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_transportistas" />
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        @foreach($transportistas as $tr)
            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                <a href="{{ route('Registroflete.tarifario', ['data' => base64_encode($tr->id_transportistas)]) }}" class="text-decoration-none">
                    <x-card-general-view>
                        <x-slot name="content">
                            <div class="card-body text-center">
                                <i class="fas fa-user fa-3x mb-3"></i>

                                <h5 class="card-title">{{ $tr->transportista_ruc }}</h5>

                                <p class="card-text">{{ $tr->transportista_nom_comercial }}</p>
                            </div>
                        </x-slot>
                    </x-card-general-view>
                </a>
            </div>
        @endforeach
    </div>
</div>

