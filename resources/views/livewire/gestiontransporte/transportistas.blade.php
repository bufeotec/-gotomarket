<div>
{{--    MODAL REGISTRO TRANSPORTISTAS--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalTransportistas</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="titleModal">Gestionar Transportistas</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveTransportista">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información del Transportista</small>
                        <hr class="mb-0">
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <div class="" wire:ignore>
                            <label for="id_ubigeo" class="form-label">Ubigeo (*)</label>
                            <select class="form-select" name="id_ubigeo" id="id_ubigeo" wire:model="id_ubigeo">
                                <option value="" >Seleccionar...</option>
                                @foreach($listar_ubigeos as $lu)
                                    <option value="{{$lu->id_ubigeo}}">{{$lu->ubigeo_departamento . ' - ' . $lu->ubigeo_provincia . ' - ' . $lu->ubigeo_distrito}}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('id_ubigeo')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                        <label for="transportista_ruc" class="form-label">RUC (*)</label>
                        <x-input-general  type="text" id="transportista_ruc" wire:model="transportista_ruc" wire:change="consultDocument"/>
                        <div wire:loading wire:target="consultDocument">
                            Consultando información
                        </div>
                        @if($messageConsulta)
                            <span class="text-{{$messageConsulta['type']}} d-block">{{$messageConsulta['mensaje']}}</span>
                        @endif

                        @error('transportista_ruc')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-8 col-sm-12 mb-3">
                        <label for="transportista_razon_social" class="form-label">Razón social (*)</label>
                        <x-input-general  type="text" id="transportista_razon_social" wire:model="transportista_razon_social"/>
                        @error('transportista_razon_social')
                            <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="transportista_nom_comercial" class="form-label">Nombre comercial (*)</label>
                        <x-input-general  type="text" id="transportista_nom_comercial" wire:model="transportista_nom_comercial"/>
                        @error('transportista_nom_comercial')
                            <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="transportista_direccion" class="form-label">Dirección (*)</label>
                        <x-input-general  type="text" id="transportista_direccion" wire:model="transportista_direccion"/>
                        @error('transportista_direccion')
                            <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información de Contacto</small>
                        <hr class="mb-0">
                    </div>
                    <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                        <label for="transportista_contacto" class="form-label">Contacto (*)</label>
                        <x-input-general  type="text" id="transportista_contacto" wire:model="transportista_contacto"/>
                        @error('transportista_contacto')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                        <label for="transportista_cargo" class="form-label">Cargo (*)</label>
                        <x-input-general  type="text" id="transportista_cargo" wire:model="transportista_cargo"/>
                        @error('transportista_cargo')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                        <label for="transportista_correo" class="form-label">Correo</label>
                        <x-input-general  type="text" id="transportista_correo" wire:model="transportista_correo"/>
                        @error('transportista_correo')
                            <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                        <label for="transportista_telefono" class="form-label">Teléfono</label>
                        <x-input-general  type="text" onkeyup="validar_numeros(this.id)" id="transportista_telefono" wire:model="transportista_telefono"/>
                        @error('transportista_telefono')
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
{{--    FIN MODAL REGISTRO TRANSPORTISTAS--}}

{{--    MODAL DELETE--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalDeleteTransportistas</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_transportistas">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageDeleteTranspor}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_transportistas') <span class="message-error">{{ $message }}</span> @enderror

                        @error('transportista_estado') <span class="message-error">{{ $message }}</span> @enderror

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
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_transportistas" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_transportistas" />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form_transportistas" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalTransportistas" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Transportistas
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
                                <th>Ubigeo</th>
                                <th>RUC</th>
                                <th>Razón social</th>
                                <th>Nombre comercial</th>
                                <th>Contacto</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Cargo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($transportistas) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($transportistas as $tr)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$tr->ubigeo_departamento . ' - ' . $tr->ubigeo_provincia . ' - ' . $tr->ubigeo_distrito}}</td>
                                        <td>{{$tr->transportista_ruc}}</td>
                                        <td>{{$tr->transportista_razon_social}}</td>
                                        <td>{{$tr->transportista_nom_comercial}}</td>
                                        <td>{{$tr->transportista_contacto}}</td>
                                        <td>{{ $tr->transportista_correo ?? '-' }}</td>
                                        <td>{{ $tr->transportista_telefono ?? '-' }}</td>
                                        <td>{{$tr->transportista_cargo}}</td>
                                        <td>
                                            <span class="font-bold badge {{$tr->transportista_estado == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$tr->transportista_estado == 1 ? 'Habilitado ' : 'Desabilitado'}}
                                            </span>
                                        </td>

                                        <td>
                                            <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($tr->id_transportistas) }}')" data-bs-toggle="modal" data-bs-target="#modalTransportistas">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>

                                            @if($tr->transportista_estado == 1)
                                                <x-btn-accion class=" text-danger" wire:click="btn_disable('{{ base64_encode($tr->id_transportistas) }}',0)" data-bs-toggle="modal" data-bs-target="#modalDeleteTransportistas">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @else
                                                <x-btn-accion class=" text-success" wire:click="btn_disable('{{ base64_encode($tr->id_transportistas) }}',1)" data-bs-toggle="modal" data-bs-target="#modalDeleteTransportistas">
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
    {{ $transportistas->links(data: ['scrollTo' => false]) }}
    <style>
        .select2-container--default .select2-selection--single {
            display: block;
            width: 100%;
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #6e707e;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d3e2;
            border-radius: .35rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

    </style>
</div>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalTransportistas').modal('hide');
    });
    $wire.on('hideModalDelete', () => {
        $('#modalDeleteTransportistas').modal('hide');
    });

    // Inicializar Select2 cuando se cargue el modal
    $wire.on('select_ubigeo', (data) => {
        const text = data[0].text || null; // Asegúrate de que 'text' sea null si no se envía
        $('#id_ubigeo').select2({
            dropdownParent: $('#modalTransportistas .modal-body')
        });
        if(text){
            $('#select2-ubigeo-container').html(text)
        }else{
            $('#select2-ubigeo-container').html('Seleccionar')
        }
        // Sincronizar cambios de Select2 con Livewire
        $('#id_ubigeo').on('change', function () {
            let selectedValue = $(this).val();
            $wire.set('id_ubigeo', selectedValue); // Actualizar modelo de Livewire
        });
    });
    // // Reinicializar Select2 cuando se abra el modal
    window.addEventListener('show-modal', function () {
        $('#id_ubigeo').select2({
            dropdownParent: $('#modalTransportistas .modal-body')
        });
    });

</script>
@endscript
