<div>
    @php
        $general = new \App\Models\General();
    @endphp

    {{-- MODAL REGISTRO NOTA CRÉDITO --}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalNotaCredito</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="titleModal">Gestionar Nota de Crédito</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveNc">
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <small class="text-primary">Información de Nota de Crédito</small>
                        <hr class="mb-0">
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label for="id_despacho_venta" class="form-label">Factura</label>
                        <select class="form-select" wire:model="id_despacho_venta" id="despachoSelect">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_despacho as $ld)
                                <option value="{{ $ld->id_despacho_venta }}"
                                        data-ruc="{{ $ld->despacho_venta_cfcodcli }}"
                                        data-nombre="{{ $ld->despacho_venta_cnomcli }}">
                                    {{ $ld->despacho_venta_factura }} | {{ $ld->despacho_venta_cnomcli }}

                                </option>
                            @endforeach
                        </select>
                        @error('id_despacho_venta')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label for="nota_credito_motivo" class="form-label">Motivo (*)</label>
                        <select class="form-select" wire:model="nota_credito_motivo">
                            <option value="">Seleccionar...</option>
                            <option value="1">Deuda</option>
                            <option value="2">Calidad</option>
                            <option value="3">Cobranza</option>
                            <option value="4">Error de Facturación</option>
                            <option value="5">Otros Comercial</option>
                        </select>
                        @error('nota_credito_motivo')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label for="nota_credito_ruc_cliente" class="form-label">RUC</label>
                        <x-input-general type="text" wire:model="nota_credito_ruc_cliente" />
                        @error('nota_credito_ruc_cliente')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label for="nota_credito_nombre_cliente" class="form-label">Nombre</label>
                        <x-input-general type="text" wire:model="nota_credito_nombre_cliente" />
                        @error('nota_credito_nombre_cliente')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Guardar Registro</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
    {{-- FIN MODAL REGISTRO NOTA CRÉDITO --}}

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <div class="row align-items-center mt-2">
                <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
                    <label for="fecha_desde" class="form-label">Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" wire:model.live="desde" class="form-control">
                </div>
                <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
                    <label for="fecha_hasta" class="form-label">Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model.live="hasta" class="form-control">
                </div>
                <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
                    <label for="filter_ruc" class="form-label">RUC</label>
                    <input type="text" id="filter_ruc" wire:model.live="filterRuc" class="form-control" placeholder="Filtrar por RUC">
                </div>

                <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
                    <label for="filter_motivo" class="form-label">Motivo</label>
                    <select id="filter_motivo" wire:model.live="filterMotivo" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Deuda</option>
                        <option value="2">Calidad</option>
                        <option value="3">Cobranza</option>
                        <option value="4">Error de facturación</option>
                        <option value="5">Otros comercial</option>
                    </select>
                </div>

                {{-- Botón Exportar PDF: Solo visible si hay registros --}}
                @if(count($listar_nota_credito) > 0)
                    <div class="col-lg-4 col-md-2 col-sm-12 mt-3">
                        <x-btn-export onclick="window.location='{{ route('exportar.pdf') }}'" class="bg-primary text-white pdf">
                            <x-slot name="icons">
                                fa-solid fa-download
                            </x-slot>
                            Exportar PDF
                        </x-btn-export>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form_nc" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalNotaCredito">
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Registrar Nota Crédito
            </x-btn-export>
        </div>
    </div>

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
                                <th>Fecha de Emisión </th>
                                <th>RUC del Cliente</th>
                                <th>Nombre del Cliente</th>
                                <th>Motivo </th>
{{--                                <th>Estado</th>--}}
                            </tr>
                        </x-slot>
                        <x-slot name="tbody">
                            @if(count($listar_nota_credito) > 0)
                                @foreach($listar_nota_credito as $index => $lnc)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($lnc->created_at)->format('d/m/Y') }}</td>
                                        <td>{{ $lnc->nota_credito_ruc_cliente }}</td>
                                        <td>{{ $lnc->nota_credito_nombre_cliente }}</td>
                                        <td>
                                            @php
                                                $motivos = [
                                                    1 => 'Deuda',
                                                    2 => 'Calidad',
                                                    3 => 'Cobranza',
                                                    4 => 'Error de facturación',
                                                    5 => 'Otros comercial'
                                                ];
                                                $motivo = $motivos[$lnc->nota_credito_motivo] ?? 'Desconocido';
                                            @endphp
                                            {{ $motivo }}
                                        </td>
{{--                                        <td>--}}
{{--                                            <x-btn-accion class="text-primary" wire:click="edit_data('{{ base64_encode($lnc->id_nota_credito) }}')" data-bs-toggle="modal" data-bs-target="#modalNotaCredito">--}}
{{--                                                <x-slot name="message">--}}
{{--                                                    <i class="fa-solid fa-pen-to-square"></i>--}}
{{--                                                </x-slot>--}}
{{--                                            </x-btn-accion>--}}
{{--                                            <x-btn-accion class="text-danger" wire:click="btn_delete('{{ base64_encode($lnc->id_nota_credito) }}')" data-bs-toggle="modal" data-bs-target="#modalDeleteNc">--}}
{{--                                                <x-slot name="message">--}}
{{--                                                    <i class="fa-solid fa-ban"></i>--}}
{{--                                                </x-slot>--}}
{{--                                            </x-btn-accion>--}}
{{--                                        </td>--}}
{{--                                        <td>--}}
{{--                                            @php--}}
{{--                                                $estados = [--}}
{{--                                                    2 => 'Entregado',--}}
{{--                                                    3 => 'No Entregado',--}}
{{--                                                    4 => 'Rechazado',--}}
{{--                                                    5 => 'Anulado',--}}
{{--                                                ];--}}
{{--                                            @endphp--}}
{{--                                            {{ $estados[$lnc->despachoVenta->despacho_detalle_estado_entrega] ?? 'Desconocido' }}--}}
{{--                                        </td>--}}
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

    <style>
        .pdf {
            margin-left: 0rem !important;
        }
    </style>
</div>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalNotaCredito').modal('hide');
    });

    document.getElementById('despachoSelect').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var ruc = selectedOption.getAttribute('data-ruc');
        var nombre = selectedOption.getAttribute('data-nombre');

        // Asignar valores a los campos de Livewire
    @this.set('nota_credito_ruc_cliente', ruc);
    @this.set('nota_credito_nombre_cliente', nombre);
    });

</script>

@endscript
