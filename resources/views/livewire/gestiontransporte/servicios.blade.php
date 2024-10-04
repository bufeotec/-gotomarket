<div>
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalServicios</x-slot>
        <x-slot name="titleModal">Gestionar servicios</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveServicios">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-12 mb-3">
                        <label for="name" class="form-label">Nombre del servicio</label>
                        <x-input-general  type="text" id="name" wire:model="name"/>
                        @error('name')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <button type="submit" class="btn btn-success text-white">Guardar Registros</button>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @foreach($listar_servicios as $ser)
                                <tr>
                                    <td>N°</td>
                                    <td></td>
                                    <td>

                                    </td>
                                    <td>Acciones</td>
                                </tr>
                            @endforeach
                        </x-slot>
                    </x-table-general>
                </div>
            </div>
        </x-slot>
    </x-modal-general>
</div>
