<div>
    @livewire('gestiontransporte.tipovehiculos')

    {{--    MODAL REGISTRO TRANSPORTISTAS--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalVehiculos</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="titleModal">Gestionar Vehiculos</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveTransportista">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información del Vehículo</small>
                        <hr class="mb-0">
                    </div>

                    <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                        <label for="id_transportistas" class="form-label">Lista de transportistas (*)</label>
                        <select class="form-select" name="id_tipo_vehiculo" id="id_transportistas" wire:model="id_transportistas">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_transportistas as $lt)
                                <option value="{{$lt->id_transportistas}}">{{$lt->transportista_nom_comercial}}</option>
                            @endforeach
                        </select>
                        @error('id_transportistas')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                        <label for="id_tipo_vehiculo" class="form-label">Tipo de vehículo (*)</label>
                        <select class="form-select" name="id_tipo_vehiculo" id="id_tipo_vehiculo" wire:model="id_tipo_vehiculo">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_tipo_vehiculo as $lpv)
                                <option value="{{$lpv->id_tipo_vehiculo}}">{{$lpv->tipo_vehiculo_concepto}}</option>
                            @endforeach
                        </select>
                        @error('id_tipo_vehiculo')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                        <label for="vehiculo_placa" class="form-label">Placa (*)</label>
                        <x-input-general  type="text" id="vehiculo_placa" wire:model="vehiculo_placa"/>
                        @error('vehiculo_placa')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                        <label for="vehiculo_capacidad_peso" class="form-label">Capacidad de peso (*)</label>
                        <x-input-general  type="text" id="vehiculo_capacidad_peso" wire:model="vehiculo_capacidad_peso"/>
                        @error('vehiculo_capacidad_peso')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="vehiculo_ancho" class="form-label">Ancho (*) (Medida en metros)</label>
                        <x-input-general type="text" id="vehiculo_ancho" wire:model="vehiculo_ancho" wire:input="calcularVolumen"/>
                        @error('vehiculo_ancho')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="vehiculo_largo" class="form-label">Largo (*) (Medida en metros)</label>
                        <x-input-general type="text" id="vehiculo_largo" wire:model="vehiculo_largo" wire:input="calcularVolumen"/>
                        @error('vehiculo_largo')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="vehiculo_alto" class="form-label">Alto (*) (Medida en metros)</label>
                        <x-input-general type="text" id="vehiculo_alto" wire:model="vehiculo_alto" wire:input="calcularVolumen"/>
                        @error('vehiculo_alto')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    @if($vehiculo_capacidad_volumen > 0)
                        <div class="col-lg-12 col-md-4 col-sm-12 mb-3">
                            <small class="d-flex justify-content-end mt-4">Capacidad de Volumen</small>
                            <div class="d-flex justify-content-end align-items-center">
                                <h3 class="numero_vehiculo">
                                    {{$vehiculo_capacidad_volumen}} <span class="span_vehiculo">(m³)</span>
                                </h3>
                            </div>
                            @error('vehiculo_capacidad_volumen')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Guardar Registro</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
    {{--    FIN MODAL REGISTRO TRANSPORTISTAS--}}

    {{--    MODAL DELETE--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalDeleteVehiculos</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_vehiculo">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageDeleteVehiculo}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_vehiculo') <span class="message-error">{{ $message }}</span> @enderror

                        @error('vehiculo_estado') <span class="message-error">{{ $message }}</span> @enderror

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
    {{--    FIN MODAL DELETE--}}

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_vehiculos" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_vehiculos" />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export class="bg-secondary text-white" wire:click="limpiar_campo_tipo_vehiculo" data-bs-toggle="modal" data-bs-target="#modalTipoVehiculo">
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar tipo de vehículos
            </x-btn-export>
            <x-btn-export wire:click="clear_form_vehiculos" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalVehiculos" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar un vehículo
            </x-btn-export>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Transportista</th>
                                <th>Tipo de vehículo</th>
                                <th>Placa</th>
                                <th>Capacidad de peso</th>
                                <th>Ancho</th>
                                <th>Largo</th>
                                <th>Alto</th>
                                <th>Volumen</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_vehiculos) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($listar_vehiculos as $lv)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$lv->transportista_nom_comercial}}</td>
                                        <td>{{$lv->tipo_vehiculo_concepto}}</td>
                                        <td>{{$lv->vehiculo_placa}}</td>
                                        <td>{{$lv->vehiculo_capacidad_peso}}</td>
                                        <td>{{$lv->vehiculo_ancho}} <b>(m)</b></td>
                                        <td>{{$lv->vehiculo_largo}} <b>(m)</b></td>
                                        <td>{{$lv->vehiculo_alto}} <b>(m)</b></td>
                                        <td>{{$lv->vehiculo_capacidad_volumen}} <b>(m³)</b></td>
                                        <td>
                                            <span class="font-bold badge {{$lv->vehiculo_estado == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$lv->vehiculo_estado == 1 ? 'Habilitado ' : 'Desabilitado'}}
                                            </span>
                                        </td>

                                        <td>
                                            <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($lv->id_vehiculo) }}')" data-bs-toggle="modal" data-bs-target="#modalVehiculos">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>

                                            @if($lv->vehiculo_estado == 1)
                                                <x-btn-accion class=" text-danger" wire:click="btn_disable('{{ base64_encode($lv->id_vehiculo) }}',0)" data-bs-toggle="modal" data-bs-target="#modalDeleteVehiculos">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @else
                                                <x-btn-accion class=" text-success" wire:click="btn_disable('{{ base64_encode($lv->id_vehiculo) }}',1)" data-bs-toggle="modal" data-bs-target="#modalDeleteVehiculos">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-check"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @endif
                                        </td>
                                    </tr>
                                    @php $conteo++; @endphp
                                @endforeach
                            @else
                                <tr class="odd">
                                    <td valign="top" colspan="9" class="dataTables_empty text-center">
                                        No se han encontrado resultados.
                                    </td>
                                </tr>
                            @endif
                        </x-slot>
                    </x-table-general>
                </div>
            </div>
        </x-slot>
    </x-card-general-view>
    {{--    {{ $menus->links(data: ['scrollTo' => false]) }}--}}
</div>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalVehiculos').modal('hide');
    });
    $wire.on('hideModalDelete', () => {
        $('#modalDeleteVehiculos').modal('hide');
    });
</script>
@endscript