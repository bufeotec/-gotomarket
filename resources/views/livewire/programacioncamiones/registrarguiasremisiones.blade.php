<div>
    @php
        $general = new \App\Models\General();
    @endphp

    {{--    MODAL REGISTRO SERVICIO TRASNPORTE--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalGuiaRemision</x-slot>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="titleModal">Gestionar Guía de remisión</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveGuiaRemision" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información de la guía de remision</small>
                        <hr class="mb-0">
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="guia_rem_numero_guia" class="form-label">Numero de guia</label>
                        <x-input-general type="text" id="guia_rem_numero_guia" wire:model="guia_rem_numero_guia"/>
                        @error('guia_rem_numero_guia')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="id_vehiculo" class="form-label">Lista de vehiculos</label>
                        <select class="form-select" name="id_tipo_ id_vehiculo" id="id_vehiculo" wire:model="id_vehiculo">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_vehiculos as $lt)
                                <option value="{{$lt->id_vehiculo}}">{{$lt->vehiculo_placa}}</option>
                            @endforeach
                        </select>
                        @error('id_vehiculo')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="guia_rem_fecha_emision" class="form-label">Fecha de emisión</label>
                        <x-input-general type="datetime-local" id="guia_rem_fecha_emision" wire:model="guia_rem_fecha_emision"/>
                        @error('guia_rem_fecha_emision')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="guia_rem_motivo" class="form-label">Motivo</label>
                        <textarea class="form-control" id="guia_rem_motivo" name="guia_rem_motivo" wire:model="guia_rem_motivo"></textarea>
                        @error('guia_rem_motivo')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <small class="text-primary">Remitente</small>
                                <hr class="mb-0">
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="guia_rem_remitente_ruc" class="form-label">RUC</label>
                                <x-input-general  type="text" id="guia_rem_remitente_ruc" wire:model="guia_rem_remitente_ruc" wire:change="consulta_documento_remitente" />
                                <div wire:loading wire:target="guia_rem_remitente_ruc">
                                    Consultando información
                                </div>

                                @if($message_consulta_remitente)
                                    <span class="text-{{$message_consulta_remitente['type']}} d-block">{{$message_consulta_remitente['mensaje']}}</span>
                                @endif

                                @error('guia_rem_remitente_ruc')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="guia_rem_remitente_razon_social" class="form-label">Razón social</label>
                                <x-input-general  type="text" id="guia_rem_remitente_razon_social" wire:model="guia_rem_remitente_razon_social"/>
                                @error('guia_rem_remitente_razon_social')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="guia_rem_remitente_direccion" class="form-label">Dirección</label>
                                <x-input-general  type="text" id="guia_rem_remitente_direccion" wire:model="guia_rem_remitente_direccion"/>
                                @error('guia_rem_remitente_direccion')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <small class="text-primary">Destinatario</small>
                                <hr class="mb-0">
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="guia_rem_destinatario_ruc" class="form-label">RUC</label>
                                <x-input-general  type="text" id="guia_rem_destinatario_ruc" wire:model="guia_rem_destinatario_ruc" wire:change="consulta_documento_destinatario" />
                                <div wire:loading wire:target="guia_rem_destinatario_ruc">
                                    Consultando información
                                </div>

                                @if($message_consulta_destinatario)
                                    <span class="text-{{$message_consulta_destinatario['type']}} d-block">{{$message_consulta_destinatario['mensaje']}}</span>
                                @endif

                                @error('guia_rem_destinatario_ruc')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="guia_rem_destinatario_razon_social" class="form-label">Razón social</label>
                                <x-input-general  type="text" id="guia_rem_destinatario_razon_social" wire:model="guia_rem_destinatario_razon_social"/>
                                @error('guia_rem_destinatario_razon_social')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="guia_rem_destinatario_direccion" class="form-label">Dirección</label>
                                <x-input-general  type="text" id="guia_rem_destinatario_direccion" wire:model="guia_rem_destinatario_direccion"/>
                                @error('guia_rem_destinatario_direccion')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error-modal'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error-modal') }}
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
    {{--    FIN MODAL REGISTRO SERVICIO TRANSPORTE--}}

    {{--    MODAL CAMBIAR ESTADO APROBACION--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalCambioEstado</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiar_aprobacion_guiar">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageGuiaRem}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_guia_rem') <span class="message-error">{{ $message }}</span> @enderror

                        @if (session()->has('error_pre_pro'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_pre_pro') }}
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
    {{--    MODAL FIN CAMBIAR ESTADO APROBACION--}}

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_guia_remision" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_guia_remision" />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form_guia_remision" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalGuiaRemision" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Guía Remisión
            </x-btn-export>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="alert alert-danger alert-dismissible show fade mt-2">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>#</th>
                                <th>N° Guía</th>
                                <th>Vehiculo</th>
                                <th>Fecha de emision</th>
                                <th>Fecha de traslado</th>
                                <th>Motivo</th>
                                <th>Remitente</th>
                                <th>Destinatario</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_guias_remision) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($listar_guias_remision as $lgr)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$lgr->guia_rem_numero_guia}}</td>
                                        <td>{{$lgr->vehiculo_placa}}</td>
                                        <td>{{$general->obtenerNombreFecha($lgr->guia_rem_fecha_emision,'DateTime', 'DateTime')}}</td>
                                        <td>{{$general->obtenerNombreFecha($lgr->guia_rem_fecha_traslado,'DateTime', 'DateTime')}}</td>
                                        <td>{{$lgr->guia_rem_motivo}}</td>
                                        <td>
                                            {{ $lgr->guia_rem_remitente_ruc }} <br><br>
                                            {{ $lgr->guia_rem_remitente_razon_social }} <br><br>
                                            {{ $lgr->guia_rem_remitente_direccion }}
                                        </td>
                                        <td>
                                            {{ $lgr->guia_rem_destinatario_ruc }} <br><br>
                                            {{ $lgr->guia_rem_destinatario_razon_social }} <br><br>
                                            {{ $lgr->guia_rem_destinatario_direccion }}
                                        </td>
                                        <td>
                                            <span class="font-bold badge {{$lgr->guia_rem_estado_aprobacion == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$lgr->guia_rem_estado_aprobacion == 1 ? 'Aprobado ' : 'Pendiente ' }}
                                            </span>
                                        </td>
                                        <td>
                                            <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($lgr->id_guia_rem) }}')" data-bs-toggle="modal" data-bs-target="#modalGuiaRemision">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>

                                            @php
                                                $user = \Illuminate\Support\Facades\Auth::user();
                                                $roleId = $user->roles->first()->id ?? null;
                                            @endphp

                                            @if($lgr->guia_rem_estado_aprobacion == 0 && in_array($roleId, [1, 2]))
                                                <x-btn-accion class="text-success m-2" wire:click="cambio_estado('{{ base64_encode($lgr->id_guia_rem) }}')" data-bs-toggle="modal" data-bs-target="#modalCambioEstado">
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
    {{ $listar_guias_remision->links(data: ['scrollTo' => false]) }}

</div>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalGuiaRemision').modal('hide');
    });
    $wire.on('hideModalAprobacion', () => {
        $('#modalCambioEstado').modal('hide');
    });
</script>
@endscript
