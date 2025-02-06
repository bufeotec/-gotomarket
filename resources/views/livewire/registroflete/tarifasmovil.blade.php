<div>
    @php
        $general = new \App\Models\General();
    @endphp

    {{-- MODAL DELETE --}}
    <x-modal-delete wire:ignore.self>
        <x-slot name="id_modal">modalDeleteTarifaMovil</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_tm">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageDeleteTm}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_vehiculo') <span class="message-error">{{ $message }}</span> @enderror
                        @error('id_vehiculo') <span class="message-error">{{ $message }}</span> @enderror
                        @if (session()->has('error_delete'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_delete') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">SI</button>
                        <button type="button" data-bs-dismiss="modal" class="btn btn-danger btnDelete">No</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
    {{-- FIN MODAL DELETE --}}

{{--    <div class="row">--}}
{{--        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">--}}
{{--            <div class="row align-items-center mt-2">--}}
{{--                <div class="col-lg-5 col-md-2 col-sm-12 mb-2">--}}
{{--                    <label for="fecha_desde" class="form-label">Desde</label>--}}
{{--                    <input type="date" name="fecha_desde" id="fecha_desde" wire:model.live="desde" class="form-control">--}}
{{--                </div>--}}
{{--                <div class="col-lg-5 col-md-2 col-sm-12 mb-2">--}}
{{--                    <label for="fecha_hasta" class="form-label">Hasta</label>--}}
{{--                    <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model.live="hasta" class="form-control">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Transportista</th>
                                <th>Tipo Vehículo</th>
                                <th>Placa</th>
                                <th>Tarifa</th>
                                <th class="text-center">Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>
                        <x-slot name="tbody">
                            @if(count($listar_tarifamovil) > 0)
                                @foreach($listar_tarifamovil as $index => $lt)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $lt->transportista_nom_comercial ?? 'N/A' }}</td>
                                        <td>{{ $lt->tipo_vehiculo_concepto ?? 'N/A' }}</td>
                                        <td>{{ $lt->vehiculo_placa ?? 'N/A' }}</td>
                                        <td>{{ $lt->tarifa_monto }}</td>
                                        <td class="text-center">{{ $lt->updated_at  }}</td>
                                        <td>{{ $lt->vehiculo_estado == 0 ? 'Pendiente' : 'Aprobado' }}</td>
                                        <td>
                                            @if($lt->vehiculo_estado == 0)
                                                <x-btn-accion class="text-success" wire:click="btn_disable('{{ base64_encode($lt->id_vehiculo) }}', 1)" data-bs-toggle="modal" data-bs-target="#modalDeleteTarifaMovil">
                                                    <x-slot name="message">
                                                        <span class="bg-success p-2 text-white rounded">
                                                            <i class="fa-solid fa-check"></i>
                                                        Aprobar</span>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @else
                                                <span class="text-success">Confirmado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8">No hay registros disponibles.</td>
                                </tr>
                            @endif
                        </x-slot>
                    </x-table-general>
                </div>
            </div>
        </x-slot>
    </x-card-general-view>
</div>

@script
<script>
    $wire.on('hideModalDelete', () => {
        $('#modalDeleteTarifaMovil').modal('hide');
    });
</script>
@endscript
