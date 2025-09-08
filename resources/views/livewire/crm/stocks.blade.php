<div>
    @php
        $general = new \App\Models\General();
    @endphp

    {{--    MODAL VER STOCK LOTE --}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modal_ver_stock_lote</x-slot>
        <x-slot name="titleModal">Detalles del Lote - {{ $codigo_unitario_actual }}</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                @if (session()->has('success_modal'))
                    <div class="alert alert-success alert-dismissible show fade mt-2">
                        {{ session('success_modal') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('error_modal'))
                    <div class="alert alert-danger alert-dismissible show fade mt-2">
                        {{ session('error_modal') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                    <div class="loader mt-2 w-100" wire:loading wire:target="obtener_detalle_stock_lote">
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                    @if($listar_stock_lote && count($listar_stock_lote) > 0)
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>N°</th>
                                    <th>Lote</th>
                                    <th>Fecha de Fabricación</th>
                                    <th>Fecha de Vencimiento</th>
                                    <th>Stock Cajas</th>
                                    <th>Stock UND</th>
                                </tr>
                            </x-slot>

                            <x-slot name="tbody">
                                @php $conteoSL = 1; @endphp
                                @foreach($listar_stock_lote as $od)
                                    <tr>
                                        <td>{{ $conteoSL }}</td>
                                        <td>{{ $od->stock_lote_lote ?? ' - ' }}</td>
                                        <td>{{ $od->stock_lote_fecha_fabricacion ? $general->obtenerNombreFecha($od->stock_lote_fecha_fabricacion, 'DateTime', 'Date') : '-' }}</td>
                                        <td>{{ $od->stock_lote_fecha_vencimiento ? $general->obtenerNombreFecha($od->stock_lote_fecha_vencimiento, 'DateTime', 'Date') : '-' }}</td>
                                        <td>{{ number_format($od->stock_lote_stock_caja, 2) ?? ' 0 ' }}</td>
                                        <td>{{ number_format($od->stock_lote_stock_unitario, 2) ?? ' 0 ' }}</td>
                                    </tr>
                                    @php $conteoSL++; @endphp
                                @endforeach
                            </x-slot>
                        </x-table-general>
                    @else
                        <p class="text-center">No se encontraron lotes para este producto.</p>
                    @endif
                </div>
            </div>
        </x-slot>
    </x-modal-general>
    {{--    FIN MODAL VER STOCK LOTE --}}

    <div class="row align-items-center">
        <div class="col-lg-2">
            <label for="id_familia" class="form-label">Familia</label>
            <select class="form-select" id="id_familia" wire:model.live="id_familia">
                <option>Seleccionar...</option>
                @foreach($listar_familia as $lf)
                    <option value="{{ $lf->id_familia }}">{{ $lf->familia_concepto }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2">
            <label for="id_marca" class="form-label">Marca</label>
            <select class="form-select" id="id_marca" wire:model.live="id_marca">
                <option>Seleccionar...</option>
                @foreach($listar_marca as $lf)
                    <option value="{{ $lf->id_marca }}">{{ $lf->marca_concepto }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 mt-4 mb-3 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_stock" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_stock" />
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-3 text-end">
            <a class="btn btnsm bg-primary text-white" wire:click="generar_excel_stock">Descargar</a>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-3 text-end">
            <a class="btn btnsm bg-success text-white" wire:click="actualizar_stock">Actualizar</a>
        </div>

        <div wire:loading wire:target="actualizar_stock, generar_excel_stock" class="overlay__eliminar">
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

    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Familia</th>
                                <th>Linea</th>
                                <th>Marca</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Unidad</th>
                                <th>Código Unidad</th>
                                <th>Factor</th>
                                <th>Stock Cajas</th>
                                <th>Stock Unidades</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_stock_registrados) > 0)
                                @php $conteo= 1; @endphp
                                @foreach($listar_stock_registrados as $me)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$me->stock_familia ?? ' - '}}</td>
                                        <td>{{$me->stock_linea ?? ' - '}}</td>
                                        <td>{{$me->stock_marca ?? ' - '}}</td>
                                        <td>{{$me->stock_codigo_caja ?? ' - '}}</td>
                                        <td>{{$me->stock_descripcion_producto ?? ' - '}}</td>
                                        <td>{{$me->stock_unidad ?? ' - '}}</td>
                                        <td>{{$me->stock_codigo_unitario ?? ' - '}}</td>
                                        <td>{{number_format($me->stock_factor, 0) ?? ' - '}}</td>
                                        <td>{{number_format($me->stock_stock_caja, 2) ?? ' - '}}</td>
                                        <td>{{number_format($me->stock_stock_unitario, 2) ?? ' - '}}</td>
                                        <td>
                                            <a class="btn text-success" wire:click="obtener_detalle_stock_lote('{{$me->stock_codigo_unitario}}')" data-bs-toggle="modal" data-bs-target="#modal_ver_stock_lote"><i class="fa fa-eye"></i></a>
                                        </td>
                                    </tr>
                                    @php $conteo++; @endphp
                                @endforeach
                            @else
                                <tr class="odd">
                                    <td valign="top" colspan="12" class="dataTables_empty text-center">
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
    <div class="mt-3">
        {{ $listar_stock_registrados->links(data: ['scrollTo' => false]) }}
    </div>


</div>
