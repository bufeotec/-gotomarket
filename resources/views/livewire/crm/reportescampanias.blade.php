<div>
{{--    MODAL VER PUNTOS DE VENDEDORES--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modal_ver_puntos</x-slot>
        <x-slot name="titleModal">Puntos de los Vendedores</x-slot>
        <x-slot name="modalContent">
            <div class="col-lg-12 col-md-12 col-sm-12">
                @if (session()->has('error_modal_doc'))
                    <div class="alert alert-danger alert-dismissible show fade mt-2">
                        {{ session('error_modal_doc') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                <div class="loader mt-2 w-100" wire:loading wire:target="ver_puntos">
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Vendedor</th>
                                <th>Puntos Ganados</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($vendedores_modal) > 0)
                                @php $conteoP = 1; @endphp
                                @foreach($vendedores_modal as $vend)
                                    <tr>
                                        <td>{{ $conteoP }}</td>
                                        <td>{{ $vend['vendedor_nombre'] ?? '-' }}</td>
                                        <td>{{ $vend['total_puntos_ganados'] ?? 0 }}</td>
                                    </tr>
                                    @php $conteoP++; @endphp
                                @endforeach
                            @else
                                <tr class="odd">
                                    <td valign="top" colspan="3" class="dataTables_empty text-center">
                                        No se han encontrado resultados.
                                    </td>
                                </tr>
                            @endif
                        </x-slot>
                    </x-table-general>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-12 text-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL VER PUNTOS DE VENDEDORES--}}

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

        @if(count($resultados) > 0)
            <div class="col-lg-3 col-md-3 col-sm-12 mt-4 mb-3 text-end">
                <a class="btn btn-sm bg-success text-white" wire:click="generar_excel_detalle_ganador_cliente" wire:loading.attr="disabled">Detalle de Ganadores por Cliente</a>
            </div>

            <div class="col-lg-3 col-md-3 col-sm-12 mt-4 mb-3 text-end">
                <a class="btn btn-sm bg-success text-white" wire:click="generar_excel_consolidado_premios" wire:loading.attr="disabled">Consolidado de Premios</a>
            </div>
        @endif
        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 text-end mb-2">
            <a href="{{route('CRM.sistema_puntos_vendedor_cliente')}}" class="btn bg-secondary text-white"><i class="fa-solid fa-arrow-left me-2"></i> Regresar</a>
        </div>

        <div wire:loading wire:target="id_campania" class="overlay__eliminar">
            <div class="spinner__container__eliminar">
                <div class="spinner__eliminar"></div>
            </div>
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
                                            <td>{{$r->cliente_codigo_cliente}}</td>
                                            <td>{{$r->cliente_ruc_cliente}}</td>
                                            <td>{{$r->cliente_nombre_cliente}}</td>
                                            <td>{{ $r->cant_vendedores_con_premio ?? 0 }}</td>
                                            <td>{{ $r->cant_premios_canjeados ?? 0 }}</td>
                                            <td>{{ number_format($r->puntos_ganados_total, 0) ?? 0 }}</td>
                                            <td>{{ number_format($r->puntos_canjeados_total, 0) ?? 0 }}</td>
                                            <td>
                                                <a class="btn btn-sm bg-info text-white my-1 ms-2" wire:click="ver_puntos({{$r->id_cliente}})" data-bs-toggle="modal" data-bs-target="#modal_ver_puntos">
                                                    <i class="fa fa-eye"></i>
                                                </a>

                                                <a class="btn btn-sm bg-primary text-white my-1 ms-2" wire:click="generar_excel_detalle_cliente({{$r->id_cliente}})" wire:loading.attr="disabled">Detalle</a>
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
