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
                    <button class="btn btn-sm bg-success text-white w-75">
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
                                                <td>{{$lnc->not_cred_fecha_emision}}</td>
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
                                                <td>SI</td>
                                                <td>S/ {{ number_format($lnc->not_cred_importe_total / 1.18, 2) }}</td>
                                                <td>{{$lnc->not_cred_nombre_cliente}}</td>
                                                <td>{{$lnc->not_cred_estado}}</td>
                                                <td>
                                                    @if($lnc->not_cred_estado_aprobacion == 1)
                                                        Registrado
                                                    @elseif($lnc->not_cred_estado_aprobacion == 2)
                                                        Emitido
                                                    @else
                                                        Estado desconocido
                                                    @endif
                                                </td>
                                                <td></td>
                                                <td></td>
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
