<div>
    {{--    MODAL REGISTRO TRANSPORTISTAS--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalTransportistas</x-slot>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="titleModal">Gestionar Transportistas</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveTransportista">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información del Transportista</small>
                        <hr class="mb-0">
                    </div>

                    <div class="col-lg-6 col-md-4 col-sm-12 mb-3">
                        <label for="transportista_ruc" class="form-label">RUC (*)</label>
                        <x-input-general  type="text" id="transportista_ruc" wire:model="transportista_ruc" wire:change="consultDocument"/>
                        <div wire:loading wire:target="consultDocument">
                            Consultando información
                        </div>
                        @if($messageConsulta)<span class="text-{{$messageConsulta['type']}} d-block">{{$messageConsulta['mensaje']}}</span>@endif

                        @error('transportista_ruc')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-8 col-sm-12 mb-3">
                        <label for="transportista_razon_social" class="form-label">Razón social (*)</label>
                        <x-input-general  type="text" id="transportista_razon_social" wire:model="transportista_razon_social"/>
                        @error('transportista_razon_social')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="transportista_nom_comercial" class="form-label">Nombre comercial (*)</label>
                        <x-input-general  type="text" id="transportista_nom_comercial" wire:model="transportista_nom_comercial"/>
                        @error('transportista_nom_comercial')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="transportista_direccion" class="form-label">Dirección (*)</label>
                        <x-input-general  type="text" id="transportista_direccion" wire:model="transportista_direccion"/>
                        @error('transportista_direccion')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información de Contacto</small>
                        <hr class="mb-0">
                    </div>

                    <!-- CONTACTO 1 -->
                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_uno_comercial_operativo" class="form-label">Contacto 1: Comercial / Operativo(*)</label>
                        <x-input-general  type="text" id="transportista_contacto_uno_comercial_operativo" wire:model="transportista_contacto_uno_comercial_operativo"/>
                        @error('transportista_contacto_uno_comercial_operativo')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_uno_cargo" class="form-label">Contacto 1: Cargo (*)</label>
                        <x-input-general  type="text" id="transportista_contacto_uno_cargo" wire:model="transportista_contacto_uno_cargo"/>
                        @error('transportista_contacto_uno_cargo')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_uno_correo" class="form-label">Contacto 1: Correo (*)</label>
                        <x-input-general  type="text" id="transportista_contacto_uno_correo" wire:model="transportista_contacto_uno_correo"/>
                        @error('transportista_contacto_uno_correo')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_uno_telefono" class="form-label">Contacto 1: Teléfono (*)</label>
                        <x-input-general  type="text" onkeyup="validar_numeros(this.id)" id="transportista_contacto_uno_telefono" wire:model="transportista_contacto_uno_telefono"/>
                        @error('transportista_contacto_uno_telefono')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <!-- CONTACTO 2 -->
                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_dos_contabilidad_pago" class="form-label">Contacto 2: Contabilidad / Pagos(*)</label>
                        <x-input-general  type="text" id="transportista_contacto_dos_contabilidad_pago" wire:model="transportista_contacto_dos_contabilidad_pago"/>
                        @error('transportista_contacto_dos_contabilidad_pago')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_dos_cargo" class="form-label">Contacto 2: Cargo (*)</label>
                        <x-input-general  type="text" id="transportista_contacto_dos_cargo" wire:model="transportista_contacto_dos_cargo"/>
                        @error('transportista_contacto_dos_cargo')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_dos_correo" class="form-label">Contacto 2: Correo (*)</label>
                        <x-input-general  type="text" id="transportista_contacto_dos_correo" wire:model="transportista_contacto_dos_correo"/>
                        @error('transportista_contacto_dos_correo')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_dos_telefono" class="form-label">Contacto 2: Teléfono (*)</label>
                        <x-input-general  type="text" onkeyup="validar_numeros(this.id)" id="transportista_contacto_dos_telefono" wire:model="transportista_contacto_dos_telefono"/>
                        @error('transportista_contacto_dos_telefono')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <!-- CONTACTO 3 -->
                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_tres" class="form-label">Contacto 3 (*)</label>
                        <x-input-general  type="text" id="transportista_contacto_tres" wire:model="transportista_contacto_tres"/>
                        @error('transportista_contacto_tres')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_tres_cargo" class="form-label">Contacto 3: Cargo (*)</label>
                        <x-input-general  type="text" id="transportista_contacto_tres_cargo" wire:model="transportista_contacto_tres_cargo"/>
                        @error('transportista_contacto_tres_cargo')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_tres_correo" class="form-label">Contacto 3: Correo (*)</label>
                        <x-input-general  type="text" id="transportista_contacto_tres_correo" wire:model="transportista_contacto_tres_correo"/>
                        @error('transportista_contacto_tres_correo')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <label for="transportista_contacto_tres_telefono" class="form-label">Contacto 3: Teléfono (*)</label>
                        <x-input-general  type="text" onkeyup="validar_numeros(this.id)" id="transportista_contacto_tres_telefono" wire:model="transportista_contacto_tres_telefono"/>
                        @error('transportista_contacto_tres_telefono')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <!-- ACUERDO COMERCIAL -->
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Acuerdos Comerciales Base</small>
                        <hr class="mb-0">
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="transportista_conformidad_factura" class="form-label">Conformidad de Factura</label>
                        <select class="form-control" id="transportista_conformidad_factura" wire:model="transportista_conformidad_factura">
                            <option value="">Seleccionar...</option>
                            <option value="1">Anticipado</option>
                            <option value="2">Después de Entrega</option>
                        </select>
                        @error('transportista_conformidad_factura')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="transportista_modo_pago_factura" class="form-label">Modo de Pago de Factura</label>
                        <select class="form-control" id="transportista_modo_pago_factura" wire:model.live="transportista_modo_pago_factura">
                            <option value="">Seleccionar...</option>
                            <option value="1">Contado</option>
                            <option value="2">Crédito</option>
                        </select>
                        @error('transportista_modo_pago_factura')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    @if($transportista_modo_pago_factura == 2)
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                            <label for="transportista_dias_credito_factura" class="form-label">Dias de Crédito de Factura</label>
                            <select class="form-control" id="transportista_dias_credito_factura" wire:model="transportista_dias_credito_factura">
                                <option value="">Seleccionar...</option>
                                <option value="1">7 Días</option>
                                <option value="2">15 Días</option>
                                <option value="3">30 Días</option>
                                <option value="4">45 Días</option>
                            </select>
                            @error('transportista_dias_credito_factura')<span class="message-error">{{ $message }}</span>@enderror
                        </div>
                    @endif

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="transportista_referencia_acuerdo_comercial" class="form-label">Referencia de Acuerdo Comercial</label>
                        <x-input-general  type="text" id="transportista_referencia_acuerdo_comercial" wire:model="transportista_referencia_acuerdo_comercial"/>
                        @error('transportista_referencia_acuerdo_comercial')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="transportista_garantias_servicio" class="form-label">Garantías de Servicio</label>
                        <x-input-general  type="text" id="transportista_garantias_servicio" wire:model="transportista_garantias_servicio"/>
                        @error('transportista_garantias_servicio')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error_modal'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_modal') }}
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
        <div class="col-lg-4 col-md-4 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_transportistas" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_transportistas" />
        </div>
        <div class="col-lg-4"></div>
        <div class="col-lg-2 col-md-2 col-sm-12 text-end">
            <a class="btn bg-primary text-white" wire:click="descargar_transportistas_excel">Descargar Transportistas</a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form_transportistas" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalTransportistas" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Transportista
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
        <div class="alert alert-danger alert-dismissible show fade mt-2">
            {{ session('error') }}
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
                                <th>Razón social</th>
                                <th>RUC</th>
                                <th>Dirección</th>
                                <th>Contacto</th>
                                <th>Cargo</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
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
                                        <td>{{$tr->transportista_razon_social}}</td>
                                        <td>{{$tr->transportista_ruc}}</td>
                                        <td>{{$tr->transportista_direccion ?? '-'}}</td>
                                        <td>{{ $tr->transportista_contacto_uno_comercial_operativo ?? '-' }}</td>
                                        <td>{{ $tr->transportista_contacto_uno_cargo ?? '-' }}</td>
                                        <td>{{ $tr->transportista_contacto_uno_correo ?? '-' }}</td>
                                        <td>{{ $tr->transportista_contacto_uno_telefono ?? '-' }}</td>
                                        <td>
                                            <span class="font-bold badge {{$tr->transportista_estado == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$tr->transportista_estado == 1 ? 'Habilitado ' : 'Desabilitado'}}
                                            </span>
                                        </td>

                                        <td>
                                            <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($tr->id_transportistas) }}')"
                                                          data-bs-toggle="modal" data-bs-target="#modalTransportistas">
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
            console.log(text)
            $('#select2-id_ubigeo-container').html(text)
        }else{
            $('#select2-id_ubigeo-container').html('Seleccionar')
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
