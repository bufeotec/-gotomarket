<div>
    <div class="row align-items-center">
        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <label for="id_campania" class="form-label">Campaña: </label>
            <select id="id_campania" class="form-select" wire:model.live="id_campania">
                <option value="">Seleccionar...</option>
                @foreach($listar_campania as $lc)
                    <option value="{{ $lc->id_campania }}">{{ $lc->campania_nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-2"></div>

        <div class="col-lg-3 col-md-3 col-sm-12 mt-4 mb-3 text-end">
            <a class="btn btn-sm bg-success text-white">Detalle de Ganadores por Cliente</a>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-12 mt-4 mb-3 text-end">
            <a class="btn btn-sm bg-success text-white">Consolidado de Premios</a>
        </div>

        <div wire:loading wire:target="id_campania" class="overlay__eliminar">
            <div class="spinner__container__eliminar">
                <div class="spinner__eliminar"></div>
            </div>
        </div>
    </div>

    @if(empty($id_campania))
        <p class="text-black text-center mt-4">Por favor, seleccione una campaña para ver los resultados.</p>
    @else
        <x-card-general-view>
            <x-slot name="content">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>N°</th>
                                    <th>Vendedor</th>
                                    <th>Código Cliente</th>
                                    <th>RUC</th>
                                    <th>Cliente</th>
                                    <th>Cant. Vendedores con Premio</th>
                                    <th>Cant. de Premios Canjeados</th>
                                    <th>Puntos Ganados Total</th>
                                    <th>Puntos Canjeados Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </x-slot>

                            <x-slot name="tbody">
                                @if(count($resultados) > 0)
                                    @php $conteo = 1; @endphp
                                    @foreach($resultados as $r)
                                        <tr>
                                            <td>{{$conteo}}</td>
                                            <td>{{$r->vendedor_intranet_nombre}}</td>
                                            <td>{{$r->cliente_codigo_cliente}}</td>
                                            <td>{{$r->cliente_ruc_cliente}}</td>
                                            <td>{{$r->cliente_nombre_cliente}}</td>
                                            <td></td>
                                            <td>{{$r->cant_premios_canjeados ?? 0}}</td>
                                            <td>{{$r->puntos_ganados_total ?? 0}}</td>
                                            <td>{{$r->puntos_canjeados_total ?? 0}}</td>
                                            <td>
                                                <a class="btn btn-sm bg-primary text-white" wire:click="generar_excel_detalle_cliente" wire:loading.attr="disabled">Detalle</a>
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
    @endif

    <div class="mt-2">
        {{ $resultados->links(data: ['scrollTo' => false]) }}
    </div>

</div>
