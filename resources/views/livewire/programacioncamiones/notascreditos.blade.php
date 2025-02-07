<div>
    @php
        $general = new \App\Models\General();
    @endphp
    {{--    MODAL REGISTRO TRANSPORTISTAS--}}
    <x-modal-general  wire:ignore.self >
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
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="id_despacho_venta" class="form-label">Lista de facturas</label>
                        <select class="form-select" name="id_despacho_venta vehiculo" id="id_despacho_venta" wire:model="id_despacho_venta">
                            <option value="">Seleccionar...</option>
                            @foreach($fac_despacho as $fd)
                                <option value="{{$fd->id_despacho_venta}}">{{$fd->despacho_venta_factura}} - {{$fd->despacho_venta_cnomcli}}</option>
                            @endforeach
                        </select>
                        @error('id_despacho_venta')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="nota_credito_motivo" class="form-label">Código de motivo</label>
                        <select class="form-select" name="nota_credito_motivo vehiculo" id="nota_credito_motivo" wire:model="nota_credito_motivo">
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
                        @error('nota_credito_motivo')
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
        <x-slot name="id_modal">modalDeleteNotacredito</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_transportistas">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
{{--                        <h2 class="deleteTitle">{{$messageDeleteTranspor}}</h2>--}}
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
{{--                                <th>Acciones</th>--}}
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

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalNotaCredito').modal('hide');
    });
    $wire.on('hideModalDelete', () => {
        $('#modalDeleteTransportistas').modal('hide');
    });
</script>
@endscript
