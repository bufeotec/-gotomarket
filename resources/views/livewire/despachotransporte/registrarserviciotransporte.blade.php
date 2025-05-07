<div>
    @php
        $general = new \App\Models\General();
    @endphp
    {{--    MODAL REGISTRO SERVICIO TRASNPORTE--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalRegistrarServicioTransporte</x-slot>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="titleModal">Gestionar Servicio de Transporte</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveServicioTransporte" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información del Servicio de Transporte</small>
                        <hr class="mb-0">
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="serv_transpt_motivo" class="form-label">Motivo</label>
                        <x-input-general  type="text" id="serv_transpt_motivo" wire:model="serv_transpt_motivo"/>
                        @error('serv_transpt_motivo')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="serv_transpt_detalle_motivo" class="form-label">Detalle del motivo</label>
                        <textarea class="form-control" id="serv_transpt_detalle_motivo" name="serv_transpt_detalle_motivo" wire:model="serv_transpt_detalle_motivo"></textarea>
                        @error('serv_transpt_detalle_motivo')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <small class="text-primary">Remitente / Lugar de recojo</small>
                                <hr class="mb-0">
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="serv_transpt_remitente_ruc" class="form-label">RUC (Opcional)</label>
                                <x-input-general  type="text" id="serv_transpt_remitente_ruc" wire:model="serv_transpt_remitente_ruc" wire:change="consulta_documento_remitente" />
                                <div wire:loading wire:target="consulta_documento_remitente">
                                    Consultando información
                                </div>

                                @if($message_consulta_remitente)
                                    <span class="text-{{$message_consulta_remitente['type']}} d-block">{{$message_consulta_remitente['mensaje']}}</span>
                                @endif

                                @error('serv_transpt_remitente_ruc')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="serv_transpt_remitente_razon_social" class="form-label">Razón social</label>
                                <x-input-general  type="text" id="serv_transpt_remitente_razon_social" wire:model="serv_transpt_remitente_razon_social"/>
                                @error('serv_transpt_remitente_razon_social')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="serv_transpt_remitente_direccion" class="form-label">Dirección</label>
                                <x-input-general  type="text" id="serv_transpt_remitente_direccion" wire:model="serv_transpt_remitente_direccion"/>
                                @error('serv_transpt_remitente_direccion')
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
                                <label for="serv_transpt_destinatario_ruc" class="form-label">RUC (Opcional)</label>
                                <x-input-general  type="text" id="serv_transpt_destinatario_ruc" wire:model="serv_transpt_destinatario_ruc" wire:change="consulta_documento_destinatario" />
                                <div wire:loading wire:target="consulta_documento_destinatario">
                                    Consultando información
                                </div>

                                @if($message_consulta_destinatario)
                                    <span class="text-{{$message_consulta_destinatario['type']}} d-block">{{$message_consulta_destinatario['mensaje']}}</span>
                                @endif

                                @error('serv_transpt_destinatario_ruc')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="serv_transpt_destinatario_razon_social" class="form-label">Razón social</label>
                                <x-input-general  type="text" id="serv_transpt_destinatario_razon_social" wire:model="serv_transpt_destinatario_razon_social"/>
                                @error('serv_transpt_destinatario_razon_social')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="serv_transpt_destinatario_direccion" class="form-label">Dirección</label>
                                <x-input-general  type="text" id="serv_transpt_destinatario_direccion" wire:model="serv_transpt_destinatario_direccion"/>
                                @error('serv_transpt_destinatario_direccion')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="id_departamento" class="form-label">Departamento</label>
                        <select class="form-select" name="id_departamento" id="id_departamento" wire:change="deparTari" wire:model="id_departamento">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_departamento as $de)
                                <option value="{{ $de->id_departamento }}">{{ $de->departamento_nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_departamento')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="id_provincia" class="form-label">Provincia</label>
                        <select class="form-select" name="id_provincia" id="id_provincia" wire:model="id_provincia" wire:change="proviTari" {{ empty($provincias) ? 'disabled' : '' }}>
                            <option value="">Seleccionar...</option>
                            @foreach($provincias as $pr)
                                <option value="{{ $pr->id_provincia }}" {{ $pr->id_provincia == $id_provincia ? 'selected' : '' }}>{{ $pr->provincia_nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_provincia')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="id_distrito" class="form-label">Distrito</label>
                        <select class="form-select" name="id_distrito" id="id_distrito" wire:model="id_distrito" {{ empty($distritos) ? 'disabled' : '' }}>
                            <option value="">Todos los distritos</option>
                            @foreach($distritos as $di)
                                <option value="{{ $di->id_distrito }}" {{ $di->id_distrito == $id_distrito ? 'selected' : '' }}>{{ $di->distrito_nombre }}</option>
                            @endforeach
                        </select>
                        @error('serv_transpt_distrito')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="serv_transpt_peso" class="form-label">Peso</label>
                        <x-input-general  type="text" id="serv_transpt_peso" wire:model="serv_transpt_peso" onkeyup="validar_numeros(this.id)"/>
                        @error('serv_transpt_peso')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="serv_transpt_volumen" class="form-label">Volumen</label>
                        <x-input-general  type="text" id="serv_transpt_volumen" wire:model="serv_transpt_volumen" onkeyup="validar_numeros(this.id)"/>
                        @error('serv_transpt_volumen')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="serv_transpt_documento" class="form-label">Documento</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="nombre_archivo" readonly
                                   wire:model="nombre_archivo">
                            <label class="input-group-text curso-pointer" for="serv_transpt_documento">
                                <i class="fa-solid fa-upload"></i>
                            </label>
                            <x-input-general type="file" id="serv_transpt_documento" wire:model="serv_transpt_documento"
                                             class="d-none" onchange="mostrarNombreArchivo(this)"/>
                        </div>
                        <div wire:loading wire:target="serv_transpt_documento">
                            <span class="text-primary">Cargando archivo...</span>
                        </div>
                        @error('serv_transpt_documento')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

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
    {{--    FIN MODAL REGISTRO SERVICIO TRANSPORTE--}}

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_servicio_transp" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_servicio_transp" />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form_serv_transp" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalRegistrarServicioTransporte" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar un servicio transporte
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
                                <th>Codigo</th>
                                <th>Motivo</th>
                                <th>Detalle Motivo</th>
                                <th>Remitente</th>
                                <th>Destinatario</th>
                                <th>Peso</th>
                                <th>Volumen</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_servicio_transporte) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($listar_servicio_transporte as $lst)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$lst->serv_transpt_codigo}}</td>
                                        <td>{{$lst->serv_transpt_motivo}}</td>
                                        <td>{{$lst->serv_transpt_detalle_motivo}}</td>
                                        <td>
                                            {{ $lst->serv_transpt_remitente_ruc }} <br><br>
                                            {{ $lst->serv_transpt_remitente_razon_social }} <br><br>
                                            {{ $lst->serv_transpt_remitente_direccion }}
                                        </td>
                                        <td>
                                            {{ $lst->serv_transpt_destinatario_ruc }} <br><br>
                                            {{ $lst->serv_transpt_destinatario_razon_social }} <br><br>
                                            {{ $lst->serv_transpt_destinatario_direccion }}
                                        </td>
                                        <td>{{$general->formatoDecimal($lst->serv_transpt_peso)}} <b>(kg)</b></td>
                                        <td>{{$general->formatoDecimal($lst->serv_transpt_volumen)}} <b>(cm³)</b></td>
                                        <td>
                                            <span class="font-bold badge
                                                @if($lst->serv_transpt_estado_aprobacion == 0) bg-label-secondary
                                                @elseif($lst->serv_transpt_estado_aprobacion == 1) bg-label-primary
                                                @elseif($lst->serv_transpt_estado_aprobacion == 2) bg-label-info
                                                @elseif($lst->serv_transpt_estado_aprobacion == 3) bg-label-warning
                                                @elseif($lst->serv_transpt_estado_aprobacion == 4) bg-label-dark
                                                @elseif($lst->serv_transpt_estado_aprobacion == 5) bg-label-success
                                                @elseif($lst->serv_transpt_estado_aprobacion == 6) bg-label-danger
                                                @endif">

                                                @if($lst->serv_transpt_estado_aprobacion == 0) Por despachar
                                                @elseif($lst->serv_transpt_estado_aprobacion == 1) Despachado
                                                @elseif($lst->serv_transpt_estado_aprobacion == 2) Aprobado
                                                @elseif($lst->serv_transpt_estado_aprobacion == 3) Rechazado
                                                @elseif($lst->serv_transpt_estado_aprobacion == 4) En camino
                                                @elseif($lst->serv_transpt_estado_aprobacion == 5) Entregado
                                                @elseif($lst->serv_transpt_estado_aprobacion == 6) No entregado
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($lst->id_serv_transpt) }}')" data-bs-toggle="modal" data-bs-target="#modalRegistrarServicioTransporte">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>

                                            <a class="btn text-success" href="{{ asset($lst->serv_transpt_documento) }}" target="_blank">
                                                <i class="fa-solid fa-file-lines"></i>
                                            </a>
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
    {{ $listar_servicio_transporte->links(data: ['scrollTo' => false]) }}

    <script>
        function mostrarNombreArchivo(input) {
            const nombreArchivo = input.files[0] ? input.files[0].name : '';
            document.getElementById('nombre_archivo').value = nombreArchivo;

            // Opcional: También puedes emitir un evento de Livewire para actualizar la propiedad
        @this.set('nombre_archivo', nombreArchivo);
        }
    </script>

    <style>
        .bg-label-info {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }
        .bg-label-dark {
            background-color: rgba(33, 37, 41, 0.1);
            color: #212529;
        }
        .bg-label-secondary {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
    </style>

</div>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalRegistrarServicioTransporte').modal('hide');
    });
    $wire.on('hideModalDelete', () => {
        $('#modalDeleteVehiculos').modal('hide');
    });
</script>
@endscript
