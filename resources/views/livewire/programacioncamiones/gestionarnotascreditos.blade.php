<div>
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
    @php
        $general = new \App\Models\General();
    @endphp

    {{--    MODAL NOTA DE CREDITO DETALLES--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleNotaCredito</x-slot>
        <x-slot name="titleModal">Detalles de la nota de crédito</x-slot>
        <x-slot name="modalContent">
            @if($nota_credito_detalle)
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <h6>Información del detalle</h6>
                            <hr>
                            <div class="table-responsive">
                                <x-table-general>
                                    <x-slot name="thead">
                                        <tr>
                                            <th>N°</th>
                                            <th>Almacén de Entrada</th>
                                            <th>Fecha Emisión</th>
                                            <th>Estado</th>
                                            <th>Tipo Documento</th>
                                            <th>Nro. Documento</th>
                                            <th>Nro. Línea</th>
                                            <th>Cód. Producto</th>
                                            <th>Descripción Producto</th>
                                            <th>Lote</th>
                                            <th>Unidad</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unitario</th>
                                            <th>Texto</th>
                                            <th>IGV Total</th>
                                            <th>Importe Total</th>
                                            <th>Moneda</th>
                                            <th>Tipo Cambio</th>
                                            <th>Peso (g)</th>
                                            <th>Volumen (cm³)</th>
                                            <th>Peso Total (g)</th>
                                            <th>Volumen Total (cm³)</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @php $a = 1; @endphp
                                        @foreach($nota_credito_detalle as $detalle)
                                            <tr>
                                                <td>{{$a}}</td>
                                                <td>{{ $detalle->not_cred_det_almacen_entrada }}</td>
                                                <td>{{ $general->obtenerNombreFecha($detalle->not_cred_det_fecha_emision,'DateTime', 'Date')}}</td>
                                                <td>{{ $detalle->not_cred_det_estado }}</td>
                                                <td>{{ $detalle->not_cred_det_tipo_doc }}</td>
                                                <td>{{ $detalle->not_cred_det_nro_doc }}</td>
                                                <td>{{ $detalle->not_cred_det_nro_linea }}</td>
                                                <td>{{ $detalle->not_cred_det_cod_producto }}</td>
                                                <td>{{ $detalle->not_cred_det_descripcion_procd ?? '-'}}</td>
                                                <td>{{ $detalle->not_cred_det_lote ?? '-' }}</td>
                                                <td>{{ $detalle->not_cred_det_unidad }}</td>
                                                <td>{{ $detalle->not_cred_det_cantidad }}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_precio_unit_final_inc_igv)}}</td>
                                                <td>{{ $detalle->not_cred_det_texto ?? '-'}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_igv_total)}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_importe_total_inc_igv)}}</td>
                                                <td>{{ $detalle->not_cred_det_moneda }}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_tipo_cambio)}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_peso_gramos)}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_volumen)}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_peso_toal_gramos)}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_volumen_total)}}</td>
                                            </tr>
                                            @php $a++; @endphp
                                        @endforeach
                                    </x-slot>
                                </x-table-general>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </x-slot>
    </x-modal-general>
    {{--    MODAL FIN NOTA DE CREDITO DETALLES--}}

    {{--    MODAL EDITAR NOTA CREDITO--}}
    <x-modal-general wire:ignore.self style="z-index: 1055">
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalEditarNotaCredito</x-slot>
        <x-slot name="titleModal">Editar Nota de Crédito</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                    <div class="table-responsive">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>Estado de NC en Sistema Facturación</th>
                                    <th>Estado de NC en Intranet</th>
                                    <th>Código de Motivo</th>
                                    <th>Factura Vinculada</th>
                                    <th>Estado de Factura en Intranet</th>
                                    <th>Editar Estado NC</th>
                                </tr>
                            </x-slot>
                            <x-slot name="tbody">
                                @php $a = 1; @endphp
                                @foreach($nota_credito_detalle as $detalle)
                                    <tr>
                                        <td>{{$detalle->not_cred_estado}}</td>
                                        <td>
                                            @if($detalle->not_cred_estado_aprobacion == 1)
                                                Registrado
                                            @elseif($detalle->not_cred_estado_aprobacion == 2)
                                                Emitido
                                            @elseif($detalle->not_cred_estado_aprobacion == 3)
                                                Anulada
                                            @else
                                                Estado desconocido
                                            @endif
                                        </td>
                                        <td>
                                            @if($detalle->not_cred_motivo == 1)
                                                Devolución
                                            @elseif($detalle->not_cred_motivo == 2)
                                                Calidad
                                            @elseif($detalle->not_cred_motivo == 3)
                                                Cobranza
                                            @elseif($detalle->not_cred_motivo == 4)
                                                Error de facturación
                                            @elseif($detalle->not_cred_motivo == 5)
                                                Otros comercial
                                            @else
                                                Código no reconocido
                                            @endif
                                        </td>
                                        <td>{{$detalle->not_cred_nro_doc_ref}}</td>
                                        <td>
                                            @php
                                                $nota = \Illuminate\Support\Facades\DB::table('guias')
                                                    ->where('guia_nro_doc_ref', '=', $detalle->not_cred_nro_doc_ref)
                                                    ->first();
                                            @endphp

                                            @if($nota)
                                                @switch($nota->guia_estado_aprobacion)
                                                    @case(1) Créditos @break
                                                    @case(2) Despachador @break
                                                    @case(3) listo para despacho @break
                                                    @case(4) factura despachadas @break
                                                    @case(5) aceptado por créditos @break
                                                    @case(6) estado de facturación @break
                                                    @case(0) guía anulada @break
                                                    @case(7) guía en tránsito @break
                                                    @case(8) guía entregada @break
                                                    @case(9) Programación aprobado @break
                                                    @case(10) Programación rechazada @break
                                                    @case(11) guía no entregada @break
                                                    @case(12) guía rechazada @break
                                                    @case(13) registrado en intranet @break
                                                    @case(14) Anular @break
                                                    @case(15) Pendiente de NC @break
                                                    @default Estado desconocido
                                                @endswitch
                                            @else
                                                factura aún no registrada
                                            @endif
                                        </td>
                                        <td>
                                            <select class="form-select" wire:model="estado_nota_credito">
                                                <option value="">Seleccione...</option>
                                                <option value="2" {{ $detalle->not_cred_estado_aprobacion == 2 ? 'selected' : '' }}>Emitido</option>
                                                <option value="3" {{ $detalle->not_cred_estado_aprobacion == 3 ? 'selected' : '' }}>Anulada</option>
                                            </select>
                                        </td>
                                    </tr>
                                    @php $a++; @endphp
                                @endforeach
                            </x-slot>
                        </x-table-general>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 text-end">
                    <a class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</a>
                    <a class="btn text-white bg-success" data-bs-toggle="modal" data-bs-target="#modalConfirmarEditarCredtio">Grabar</a>
                </div>
            </div>
        </x-slot>
    </x-modal-general>
    {{--    FIN MODAL EDITAR NOTA CREDITO--}}

{{--        MODAL CONFIRMAR EDITAR--}}
        <x-modal-delete  wire:ignore.self style="z-index: 1056;">
            <x-slot name="id_modal">modalConfirmarEditarCredtio</x-slot>
            <x-slot name="modalContentDelete">
                <form wire:submit.prevent="cambiar_estado_nota_credito">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <h2 class="deleteTitle">¿Desea confirmar el cambio de estado?</h2>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
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
{{--        FIN MODAL CONFIRMAR EDITAR--}}


    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-6 col-sm-12 mb-3 position-relative">
                    <label for="buscar_ruc_nombre" class="form-label">RUC - Nombre del cliente</label>
                    <input type="text" name="buscar_ruc_nombre" id="buscar_ruc_nombre" wire:model.live="buscar_ruc_nombre" class="form-control" placeholder="Buscar por RUC o nombre de cliente" >
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                    <label for="buscar_numero_nc" class="form-label">N° NC</label>
                    <input type="text" name="buscar_numero_nc" id="buscar_numero_nc" wire:model.live="buscar_numero_nc" class="form-control" placeholder="Buscar por número de NC">
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                    <label for="buscar_estado" class="form-label">Estado NC</label>
                    <select name="buscar_estado" id="buscar_estado" wire:model.live="buscar_estado" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="EMITIDA">EMITIDA</option>
                        <option value="ANULADA">ANULADA</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                    <label for="fecha_desde" class="form-label">Desde (Fecha emisión)</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" wire:model.live="fecha_desde" class="form-control">
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                    <label for="fecha_hasta" class="form-label">Hasta (Fecha emisión)</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model.live="fecha_hasta" class="form-control">
                </div>
                <div class="col-lg-1 col-md-1 col-sm-12 mb-3 mt-4">
                    <button class="btn btn-sm bg-primary text-white w-75" wire:click="buscar_nc" wire:loading.attr="disabled">
                        <i class="fa fa-search"></i>
                        <spanc class="ms-1" wire:loading.remove wire:target="buscar_nc">BUSCAR</spanc>
                        <spanc class="ms-1" wire:loading wire:target="buscar_nc">BUSCANDO...</spanc>
                    </button>
                </div>
                <div class="col-lg-1 col-md-1 col-sm-12 mb-3 mt-4">
                    <button class="btn btn-sm bg-success text-white w-75" wire:click="generar_excel_nota_credito" wire:loading.attr="disabled">
                        <i class="fa-solid fa-file-excel"></i> Exportar
                    </button>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="loader mt-2" wire:loading wire:target="buscar_nc"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
            <div class="card">
                <div class="card-body table-responsive">
                    <div class="row">
                        <div class="col-lg-12">
                            @if(count($listar_nc) > 0)
                                <x-table-general id="facturasPreProgTable">
                                    <x-slot name="thead">
                                        <tr>
                                            <th>N°</th>
                                            <th>Fecha Emisión NC</th>
                                            <th>Código de Motivo</th>
                                            <th>Factura Vinculada</th>
                                            <th>¿Factura Registrada en Intranet?</th>
                                            <th>Importe sin IGV</th>
                                            <th>Nombre Cliente</th>
                                            <th>Estado NC Sistema Facturación</th>
                                            <th>Estado NC en Intranet</th>
                                            <th>Ver Detalle NC</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @php $conteo = 1; @endphp
                                        @foreach($listar_nc as $lnc)
                                            <tr>
                                                <td>{{$conteo}}</td>
                                                <td>{{ $lnc->not_cred_fecha_emision ? $general->obtenerNombreFecha($lnc->not_cred_fecha_emision, 'DateTime', 'Date') : '-' }}</td>
                                                <td>
                                                    @if($lnc->not_cred_motivo == 1)
                                                        Devolución
                                                    @elseif($lnc->not_cred_motivo == 2)
                                                        Calidad
                                                    @elseif($lnc->not_cred_motivo == 3)
                                                        Cobranza
                                                    @elseif($lnc->not_cred_motivo == 4)
                                                        Error de facturación
                                                    @elseif($lnc->not_cred_motivo == 5)
                                                        Otros comercial
                                                    @else
                                                        Código no reconocido
                                                    @endif
                                                </td>
                                                <td>{{$lnc->not_cred_nro_doc_ref}}</td>
                                                <td>
                                                    @php
                                                        // Busca el registro en la tabla 'guias'
                                                        $nota = \Illuminate\Support\Facades\DB::table('guias')
                                                            ->where('guia_nro_doc_ref', '=', $lnc->not_cred_nro_doc_ref)
                                                            ->first();
                                                    @endphp
                                                    <span class="font-bold badge {{ $nota ? 'bg-label-success' : 'bg-label-danger' }}">
                                                        {{ $nota ? 'SI' : 'NO' }}
                                                    </span>
                                                </td>
                                                <td>S/ {{ number_format($lnc->not_cred_importe_total / 1.18, 2) }}</td>
                                                <td>{{$lnc->not_cred_nombre_cliente}}</td>
                                                <td>{{$lnc->not_cred_estado}}</td>
                                                <td>
                                                    @if($lnc->not_cred_estado_aprobacion == 1)
                                                        Registrado
                                                    @elseif($lnc->not_cred_estado_aprobacion == 2)
                                                        Emitido
                                                    @elseif($lnc->not_cred_estado_aprobacion == 3)
                                                        Anulada
                                                    @else
                                                        Estado desconocido
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="#" class="btn-ver" data-bs-toggle="modal" data-bs-target="#modalDetalleNotaCredito" wire:click="modal_info_nota_credito('{{base64_encode($lnc->id_not_cred)}}')">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <a href="#" class="btn text-success" wire:click="exportar_excel_x_id_nota_credito({{ $lnc->id_not_cred }})">
                                                        <i class="fa-solid fa-download"></i>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a data-bs-toggle="modal" data-bs-target="#modalActualizarDetalle" style="cursor:pointer;" class="btn text-warning">
                                                        <i class="fa fa-refresh"></i>
                                                    </a>

                                                    <a href="#" class="btn text-primary" wire:click="modal_info_nota_credito('{{base64_encode($lnc->id_not_cred)}}')" data-bs-toggle="modal" data-bs-target="#modalEditarNotaCredito">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @php $conteo++; @endphp
                                        @endforeach
                                    </x-slot>
                                </x-table-general>
                            @else
                                <h6 class="text-black">No se han encontrado resultados.</h6>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalConfirmarEditarCredtio');
        modal.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                modal.querySelector('form').requestSubmit();
            }
        });
    });
</script>

@script
<script>
    $wire.on('hideModalConfirmacionNota', () => {
        $('#modalConfirmarEditarCredtio').modal('hide');
    });
</script>
@endscript
