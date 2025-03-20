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
                                <label for="serv_transpt_remitente_ruc" class="form-label">RUC (Opciona)</label>
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
                        <label for="serv_transpt_departamento" class="form-label">Departamento</label>
                        <x-input-general  type="text" id="serv_transpt_departamento" wire:model="serv_transpt_departamento"/>
                        @error('serv_transpt_departamento')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="serv_transpt_provincia" class="form-label">Provincia</label>
                        <x-input-general  type="text" id="serv_transpt_provincia" wire:model="serv_transpt_provincia"/>
                        @error('serv_transpt_provincia')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="serv_transpt_distrito" class="form-label">Distrito</label>
                        <x-input-general  type="text" id="serv_transpt_distrito" wire:model="serv_transpt_distrito"/>
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
                                   value="{{ $nombre_archivo ?? '' }}">
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
            // Obtener el nombre del archivo seleccionado
            const nombreArchivo = input.files[0] ? input.files[0].name : '';

            // Actualizar el campo de texto con el nombre del archivo
            document.getElementById('nombre_archivo').value = nombreArchivo;
        }
    </script>

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
