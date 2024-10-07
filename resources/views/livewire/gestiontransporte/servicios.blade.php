<div>
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-md</x-slot>
        <x-slot name="id_modal">modalServicios</x-slot>
        <x-slot name="titleModal">Gestionar los servicios</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveServicios">
                <div class="row d-flex justify-content-center align-items-center">
                    <div class="col-lg-8 col-md-8 col-sm-12 mb-3">
                        <label for="tipo_servicio_concepto" class="form-label">Nombre del servicio</label>
                        <x-input-general  type="text" id="tipo_servicio_concepto" wire:model="tipo_servicio_concepto"/>
                        @error('tipo_servicio_concepto')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3 d-flex text-center">
                        <button type="submit" class="btn btn-success text-white">Guardar servicio</button>
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
                            @php $conteo = 1; @endphp
                            @foreach($listar_servicios as $ser)
                                <tr>
                                    <td>{{$conteo}}</td>
                                    <td>{{$ser->tipo_servicio_concepto}}</td>
                                    <td>
                                        <span class="font-bold badge {{$ser->tipo_servicio_estado == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$ser->tipo_servicio_estado == 1 ? 'Habilitado ' : 'Desabilitado'}}
                                            </span>
                                    </td>
                                    <td>
                                        @if($ser->tipo_servicio_estado == 1)
                                            <x-btn-accion class="text-danger" wire:click="disable_servicio('{{ base64_encode($ser->id_tipo_servicios) }}', 0)">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-ban"></i>
                                                </x-slot>
                                            </x-btn-accion>
                                        @else
                                            <x-btn-accion class="text-success" wire:click="disable_servicio('{{ base64_encode($ser->id_tipo_servicios) }}', 1)">
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
