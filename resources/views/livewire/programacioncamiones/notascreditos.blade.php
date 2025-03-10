<div>
    @php
        $general = new \App\Models\General();
    @endphp
    {{--    MODAL REGISTRO NOTA CREDITO--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalNotaCredito</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="titleModal">Gestionar Nota de Credito</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveNotaCredito">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información de la nota de credito</small>
                        <hr class="mb-0">
                    </div>

                    <!-- Campo para mostrar el select o el h6 -->
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="id_despacho_venta" class="form-label">Lista de facturas</label>
                        @if (!$id_nota_credito)
                            <!-- Mostrar el select cuando se está creando un nuevo registro -->
                            <div wire:ignore>
                                <select class="form-control form-select form-select-sm" name="id_despacho_venta" id="id_despacho_venta" wire:model.live="id_despacho_venta">
                                    <option value="">Seleccionar...</option>
                                    @foreach($fac_despacho as $fd)
                                        <option value="{{ $fd->id_despacho_venta }}" {{ $fd->id_despacho_venta == $id_despacho_venta ? 'selected' : '' }}>
                                            {{ $fd->despacho_venta_factura }} - {{ $fd->despacho_venta_cnomcli }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <!-- Mostrar el h6 cuando se está editando un registro -->
                            @php
                                $factura = $fac_despacho->firstWhere('id_despacho_venta', $id_despacho_venta);
                            @endphp
                            @if ($factura)
                                <h6>{{ $factura->despacho_venta_factura }} - {{ $factura->despacho_venta_cnomcli }}</h6>
                            @else
                                <h6 class="text-danger">Factura no encontrada</h6>
                            @endif
                        @endif
                        @error('id_despacho_venta')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="nota_credito_motivo" class="form-label">Código de motivo</label>
                        <select class="form-select" name="nota_credito_motivo" id="nota_credito_motivo" wire:model="nota_credito_motivo">
                            <option value="">Seleccionar...</option>
                            <option value="1">1 - Devolución</option>
                            <option value="2">2 - Calidad</option>
                            <option value="3">3 - Cobranza</option>
                            <option value="4">4 - Error de facturación</option>
                            <option value="5">5 - Otros comercial</option>
                        </select>
                        @error('nota_credito_motivo')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <label for="nota_credito_motivo_descripcion" class="form-label">Motivo descripción</label>
                        <textarea class="form-control" id="nota_credito_motivo_descripcion" name="nota_credito_motivo_descripcion" wire:model="nota_credito_motivo_descripcion" rows="4"></textarea>
                        @error('nota_credito_motivo_descripcion')
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
    {{--    FIN MODAL REGISTRO NOTA CREDITO--}}

{{--    MODAL CAMBIAR ESTADO APROBACION--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalCambioEstado</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiar_estado_aprobacion">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageNotCret}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_nota_credito') <span class="message-error">{{ $message }}</span> @enderror

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
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_nota_credito" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_nota_credito" />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form_nota_credito" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalNotaCredito" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Nota credito
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
                                <th>N°</th>
                                <th>Factura</th>
                                <th>Motivo</th>
                                <th>Motivo descripción</th>
                                <th>Fecha de emision</th>
                                <th>RUC</th>
                                <th>Nombre del cliente</th>
                                <th>Importe sin IGV</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_nota_credito) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($listar_nota_credito as $lnc)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$lnc->despacho_venta_factura}}</td>
                                        <td>
                                            {{ [
                                                1 => '1 - Devolución',
                                                2 => '2 - Calidad',
                                                3 => '3 - Cobranza',
                                                4 => '4 - Error de facturación',
                                                5 => '5 - Otros comercial'
                                            ][$lnc->nota_credito_motivo] ?? 'No definido' }}
                                        </td>
                                        <td>{{$lnc->nota_credito_motivo_descripcion}}</td>
                                        <td>{{$general->obtenerNombreFecha($lnc->despacho_venta_grefecemision,'DateTime','Date')}}</td>
                                        <td>{{$lnc->despacho_venta_cfcodcli}}</td>
                                        <td>{{$lnc->despacho_venta_cnomcli}}</td>
                                        <td>{{$general->formatoDecimal($lnc->despacho_venta_cfimporte)}}</td>
                                        <td>
                                            <span class="font-bold badge {{$lnc->nota_credito_estado_aprobacion == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$lnc->nota_credito_estado_aprobacion == 1 ? 'Aprobado ' : 'Pendiente ' }}
                                            </span>
                                        </td>
                                        <td>
                                            <x-btn-accion class="text-primary"  wire:click="edit_data('{{ base64_encode($lnc->id_nota_credito) }}')" data-bs-toggle="modal" data-bs-target="#modalNotaCredito">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>
                                            @php
                                                $user = \Illuminate\Support\Facades\Auth::user();
                                                $roleId = $user->roles->first()->id ?? null;
                                            @endphp

                                            @if($lnc->nota_credito_estado_aprobacion == 0 && in_array($roleId, [1, 2]))
                                                <x-btn-accion class="text-success m-2" wire:click="cambio_estado('{{ base64_encode($lnc->id_nota_credito) }}')" data-bs-toggle="modal" data-bs-target="#modalCambioEstado">
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
    {{ $listar_nota_credito->links(data: ['scrollTo' => false]) }}

</div>

<script src="{{ asset('select2/dist/js/select2.min.js') }}"></script>
<script>
    function initSelect2() {
        $('#id_despacho_venta').select2({
            width: '100%',
            allowClear: true,
            placeholder: 'Seleccionar...',
            minimumResultsForSearch: 1,
            dropdownParent: $('#id_despacho_venta').parent(),
            language: {
                noResults: function() {
                    return "No se encontraron resultados.";
                }
            }
        });

        // Escuchar el cambio de valor y actualizar el modelo de Livewire
        $('#id_despacho_venta').on('change', function () {
        @this.set('id_despacho_venta', $(this).val());
        });
    }

    $(document).ready(function() {
        initSelect2();
    });

    document.addEventListener('livewire:load', function () {
        Livewire.hook('message.processed', (message, component) => {
            initSelect2(); // Re-inicializar Select2 después de cada actualización de Livewire
        });

        // Escuchar el evento para actualizar Select2
        Livewire.on('updateSelect2', (value) => {
            $('#id_despacho_venta').val(value).trigger('change');
        });
    });
</script>



@script
<script>
    $wire.on('hideModal', () => {
        $('#modalNotaCredito').modal('hide');
    });
    $wire.on('hideModalDelete', () => {
        $('#modalCambioEstado').modal('hide');
    });
</script>
@endscript
