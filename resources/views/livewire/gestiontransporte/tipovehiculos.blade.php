<div>
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-md</x-slot>
        <x-slot name="id_modal">modalTipoVehiculo</x-slot>
        <x-slot name="titleModal">Gestionar los tipos de vehículos</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveTipoVehiculo">
                <div class="row d-flex justify-content-center align-items-center">
                    <div class="col-lg-8 col-md-8 col-sm-12 mb-3">
                        <label for="tipo_vehiculo_concepto" class="form-label">Nombre del tipo de vehículo</label>
                        <x-input-general  type="text" id="tipo_vehiculo_concepto" wire:model="tipo_vehiculo_concepto"/>
                        @error('tipo_vehiculo_concepto')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3 d-flex text-center">
                        <button type="submit" class="btn btn-success text-white mt-4">Guardar</button>
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
            <div class="row mt-3">
                <div class="col-lg-12 col-md-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Nombre del vehículo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @php $conteo = 1; @endphp
                            @foreach($listar_tipo_vehiculos as $lpv)
                                <tr>
                                    <td>{{$conteo}}</td>
                                    <td>{{$lpv->tipo_vehiculo_concepto}}</td>
                                    <td>
                                        <span class="font-bold badge {{$lpv->tipo_vehiculo_estado == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                            {{$lpv->tipo_vehiculo_estado == 1 ? 'Habilitado ' : 'Desabilitado'}}
                                        </span>
                                    </td>
                                    <td>
                                        @if($lpv->tipo_vehiculo_estado == 1)
                                            <x-btn-accion class="text-danger" wire:click="disable_tipo_vehiculo('{{ base64_encode($lpv->id_tipo_vehiculo) }}', 0)">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-ban"></i>
                                                </x-slot>
                                            </x-btn-accion>
                                        @else
                                            <x-btn-accion class="text-success" wire:click="disable_tipo_vehiculo('{{ base64_encode($lpv->id_tipo_vehiculo) }}', 1)">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-check"></i>
                                                </x-slot>
                                            </x-btn-accion>
                                        @endif
                                    </td>
                                </tr>
                                @php $conteo++; @endphp
                            @endforeach
                        </x-slot>
                    </x-table-general>
                </div>
            </div>
        </x-slot>
    </x-modal-general>
</div>
