<div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <div class="row align-items-center mt-2">
                <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
                    <label for="fecha_desde" class="form-label">Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control">
                </div>
                <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
                    <label for="fecha_hasta" class="form-label">Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control">
                </div>
                <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
                    <label for="filter_ruc" class="form-label">RUC</label>
                    <input type="text" id="filter_ruc" wire:model="filterRuc" class="form-control" placeholder="Filtrar por RUC">
                </div>
                <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
                    <label for="filter_motivo" class="form-label">Motivo</label>
                    <select id="filter_motivo" wire:model="filterMotivo" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Deuda</option>
                        <option value="2">Calidad</option>
                        <option value="3">Cobranza</option>
                        <option value="4">Error de facturación</option>
                        <option value="5">Otros comercial</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Costo Flete/Ventas</th>
                                <th>Kilos Despachados</th>
                                <th>Pedidos Entregados</th>
                                <th>Tiempos de Entrega</th>
                                <th>Incidentes Registrados</th>
                            </tr>
                        </x-slot>
                        <x-slot name="tbody">
                            @if(count($list_nc_dv) > 0)
                                @foreach($list_nc_dv as $index => $lnc)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $lnc->despachoVenta->despacho_venta_cfimporte ?? 'N/A' }}</td>
                                        <td>{{ $lnc->despachoVenta->despacho_venta_total_kg ?? 'N/A' }}</td>
                                        <td>{{ $lnc->nota_credito_ruc_cliente }}</td>
                                        <td>{{ $lnc->nota_credito_nombre_cliente }}</td>
                                        <td>{{ $lnc->nota_credito_motivo }}</td>
                                        <td>{{ $lnc->nota_credito_incidente_registro }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6">No hay registros disponibles.</td>
                                </tr>
                            @endif
                        </x-slot>
                    </x-table-general>
                </div>
            </div>
        </x-slot>
    </x-card-general-view>
</div>
