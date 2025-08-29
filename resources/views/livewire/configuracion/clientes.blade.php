<div>

{{--    MODAL DIRECCIONES DE ENTREGA CLIENTES --}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modal_direcciones_entrega_cliente</x-slot>
        <x-slot name="titleModal">Direcciones de Entrega del Cliente - {{$cliente_nombre_cliente}}</x-slot>
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
                    <div class="loader mt-2 w-100" wire:loading wire:target="obtener_direccion_entrega_cliente">
                    </div>
                </div>

                <div class="col-lg-12">
                    @if($listar_direccion_cliente && $listar_direccion_cliente->count() > 0)
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>N°</th>
                                    <th>Dirección</th>
                                    <th>Departamento</th>
                                    <th>Provincia</th>
                                    <th>Distrito</th>
                                </tr>
                            </x-slot>

                            <x-slot name="tbody">
                                @php $conteOb = 1; @endphp
                                @foreach($listar_direccion_cliente as $ldc)
                                    <tr>
                                        <td>{{ $conteOb }}</td>
                                        <td>{{ $ldc->cliente_direccion_direccion_entrega ?? ' - ' }}</td>
                                        <td>{{ $ldc->cliente_direccion_departamento ?? ' - ' }}</td>
                                        <td>{{ $ldc->cliente_direccion_provincia ?? ' - ' }}</td>
                                        <td>{{ $ldc->cliente_direccion_distrito ?? ' - ' }}</td>
                                    </tr>
                                    @php $conteOb++; @endphp
                                @endforeach
                            </x-slot>
                        </x-table-general>
                    @else
                        <p class="text-center">No se encontraron direcciones de entrega para este cliente.</p>
                    @endif
                </div>
            </div>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL DIRECCIONES DE ENTREGA CLIENTES --}}

{{--    MODAL CONTACTOS CLIENTE--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modal_contactos_cliente</x-slot>
        <x-slot name="titleModal">Contactos del Cliente - {{$cliente_nombre_cliente}}</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                    <div class="loader mt-2 w-100" wire:loading wire:target="obtener_contacto_cliente">
                    </div>
                </div>

                @if (session()->has('success_modal_cont'))
                    <div class="alert alert-success alert-dismissible show fade mt-2">
                        {{ session('success_modal_cont') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('error_modal_cont'))
                    <div class="alert alert-danger alert-dismissible show fade mt-2">
                        {{ session('error_modal_cont') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="col lg-12">
                    @if($listar_contacto_cliente && $listar_contacto_cliente->count() > 0)
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>N°</th>
                                    <th>Nombre</th>
                                    <th>Área</th>
                                    <th>Cargo</th>
                                    <th>Correo</th>
                                    <th>Número</th>
                                </tr>
                            </x-slot>

                            <x-slot name="tbody">
                                @php $conteCon= 1; @endphp
                                @foreach($listar_contacto_cliente as $oc)
                                    <tr>
                                        <td>{{ $conteCon }}</td>
                                        <td>{{ $oc->cliente_contacto_nombre }}</td>
                                        <td>{{ $oc->cliente_contacto_area }}</td>
                                        <td>{{ $oc->cliente_contacto_cargo }}</td>
                                        <td>{{ $oc->cliente_contacto_correo }}</td>
                                        <td>{{ $oc->cliente_contacto_celular }}</td>
                                    </tr>
                                    @php $conteCon++; @endphp
                                @endforeach
                            </x-slot>
                        </x-table-general>
                    @else
                        <p class="text-center">No se encontraron contactos para este cliente.</p>
                    @endif
                </div>
            </div>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL CONTACTOS CLIENTE--}}

    <div class="row align-items-center">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
            <label for="" class="form-label">Zona:</label>
            <select id="" wire:model="" class="form-select">
                <option>Seleccionar...</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
            <label for="" class="form-label">Estado:</label>
            <select id="" wire:model="" class="form-select">
                <option>Seleccionar...</option>
            </select>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 mt-4 mb-3 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_cliente" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_cliente" />
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-3 text-end">
            <a class="btn btn-sm bg-success text-white" wire:click="actualizar_cliente">Actualizar</a>
        </div>

        <div wire:loading wire:target="actualizar_cliente" class="overlay__eliminar">
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
                                <th>Cod. Cliente</th>
                                <th>Lista de Precios</th>
                                <th>RUC / DNI</th>
                                <th>Nombre / Razón social</th>
                                <th>Zona</th>
                                <th>Vendedor</th>
                                <th>Dirección Fiscal</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_cliente) > 0)
                                @php $conteo= 1; @endphp
                                @foreach($listar_cliente as $me)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$me->cliente_codigo_cliente}}</td>
                                        <td>{{$me->cliente_lista_precio}}</td>
                                        <td>{{$me->cliente_ruc_cliente}}</td>
                                        <td>{{$me->cliente_nombre_cliente}}</td>
                                        <td>{{$me->cliente_zona}}</td>
                                        <td>{{$me->cliente_vendedor}}</td>
                                        <td>{{$me->cliente_direccion_fiscal}}</td>
                                        <td>
                                            <a class="btn text-primary" data-bs-toggle="modal" data-bs-target="#modal_ver_detalle"><i class="fa fa-eye"></i></a>

                                            <a class="btn text-danger" wire:click="obtener_direccion_entrega_cliente('{{$me->cliente_codigo_cliente}}')" data-bs-toggle="modal" data-bs-target="#modal_direcciones_entrega_cliente"><i class="fa-solid fa-location-dot"></i></a>

                                            <a class="btn text-success" wire:click="obtener_contacto_cliente('{{$me->cliente_codigo_cliente}}')" data-bs-toggle="modal" data-bs-target="#modal_contactos_cliente"><i class="fa-solid fa-address-book"></i></a>
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
{{--        {{ $listar_cliente->links(data: ['scrollTo' => false]) }}--}}
    {{ $listar_cliente->links(data: ['scrollTo' => false]) }}
</div>
