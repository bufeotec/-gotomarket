<div>
    @php
        $general = new \App\Models\General();
    @endphp

{{--    MODAL CARGAR EXCEL--}}
    <x-modal-general wire:ignore.self >
        <x-slot name="id_modal">modal_carga_excel</x-slot>
        <x-slot name="titleModal">Gestionar Carga</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="save_carga_excel">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="loader mt-2 w-100" wire:loading
                             wire:target="buscarClientesFiltroModal,seleccionar_cliente_modal">
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="id_campania" class="form-label">Campaña</label>
                        <select class="form-select" id="id_campania" wire:model.live="id_campania">
                            <option>Seleccionar...</option>
                            @foreach($listar_campania_formulario as $lc)
                                <option value="{{ $lc->id_campania }}">{{ $lc->campania_nombre }}</option>
                            @endforeach
                            @error('id_campania') <span class="message-error">{{ $message }}</span> @enderror
                        </select>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3 position-relative">
                        <label class="form-label">Cliente</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Buscar"
                               wire:model="buscar_clientes"
                               wire:keyup="buscarClientesFiltroModal()">

                        @if($abrirListasClienteModal)
                            <div style="width: 120%; z-index: 999" class="position-absolute top-100 start-0 mt-1 z-10" id="lista_cliente_reporte">
                                <div class="list-group bg-white shadow-sm">
                                    @foreach($listaClientesFiltro as $l)
                                        <a style="cursor: pointer" class="list-group-item list-group-item-action"
                                           wire:click="seleccionar_cliente_modal('{{base64_encode($l->id_cliente)}}')">
                                            {{ $l->cliente_codigo_cliente . ' - ' . $l->cliente_nombre_cliente }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @error('id_cliente') <span class="message-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Bloque Excel --}}
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label">Formato Excel</label>
                            <label class="btn btn-sm btn-primary text-white mb-0">
                                Examinar
                                <input type="file" wire:model="archivo_excel" accept=".xlsx,.xls" hidden>
                            </label>
                        </div>

                        {{-- Loading Excel --}}
                        <div wire:loading wire:target="archivo_excel" class="text-center my-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Cargando archivo Excel...</p>
                        </div>

                        {{-- Vista previa Excel --}}
                        @if ($archivo_excel)
                            <div class="border rounded text-center p-4">
                                <i class="fa fa-file-excel fa-4x text-success"></i>
                                <p class="mt-2">{{ $archivo_excel->getClientOriginalName() }}</p>
                            </div>
                        @else
                            <div class="border rounded text-center p-4">
                                <i class="fa fa-file-excel fa-4x text-secondary"></i>
                                <p class="mt-2">Formato Excel</p>
                            </div>
                        @endif
                    </div>

                    {{-- Bloque PDF --}}
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label">Archivo de referencia</label>
                            <label class="btn btn-sm btn-primary text-white mb-0">
                                Examinar
                                <input type="file" wire:model="archivo_pdf" accept=".pdf" hidden>
                            </label>
                        </div>

                        {{-- Loading PDF --}}
                        <div wire:loading wire:target="archivo_pdf" class="text-center my-3">
                            <div class="spinner-border text-danger" role="status"></div>
                            <p class="mt-2">Cargando archivo PDF...</p>
                        </div>

                        {{-- Vista previa PDF --}}
                        @if ($archivo_pdf)
                            <div class="border rounded text-center p-4">
                                <i class="fa fa-file-pdf fa-4x text-danger"></i>
                                <p class="mt-2">{{ $archivo_pdf->getClientOriginalName() }}</p>
                            </div>
                        @else
                            <div class="border rounded text-center p-4">
                                <i class="fa fa-file-pdf fa-4x text-secondary"></i>
                                <p class="mt-2">Archivo PDF</p>
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error_modal'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_modal') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Guardar Registros</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL CARGAR EXCEL--}}

{{--    MODAL REGISTRAR PUNTOS MANUALMENTE--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modal_registrar_puntos_manualmente</x-slot>
        <x-slot name="titleModal">Registrar Puntos Manualmente</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="registrar_puntos_manualmente">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="loader mt-2 w-100" wire:loading
                             wire:target="buscar_cliente_modal_registrar_puntos_manulmente,seleccionar_cliente_modal_registrar_puntos_manualmente, agregar_vendedor, eliminar_vendedor">
                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error_modal'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_modal') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="id_campania_rpm" class="form-label">Campaña</label>
                        <select class="form-select" id="id_campania_rpm" wire:model.live="id_campania_rpm">
                            <option>Seleccionar...</option>
                            @foreach($listar_campania_formulario as $lc)
                                <option value="{{ $lc->id_campania }}">{{ $lc->campania_nombre }}</option>
                            @endforeach
                            @error('id_campania_rpm') <span class="message-error">{{ $message }}</span> @enderror
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3 position-relative">
                        <label class="form-label">Cliente</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Buscar"
                               wire:model="buscar_clientes_modal_rpm"
                               wire:keyup="buscar_cliente_modal_registrar_puntos_manulmente()">

                        @if($abrir_modal_rpm)
                            <div style="width: 120%; z-index: 999" class="position-absolute top-100 start-0 mt-1 z-10" id="lista_cliente_reporte">
                                <div class="list-group bg-white shadow-sm">
                                    @foreach($listaClientesFiltro as $l)
                                        <a style="cursor: pointer" class="list-group-item list-group-item-action"
                                           wire:click="seleccionar_cliente_modal_registrar_puntos_manualmente('{{base64_encode($l->id_cliente)}}')">
                                            {{ $l->cliente_codigo_cliente . ' - ' . $l->cliente_nombre_cliente }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @error('id_cliente_rpm') <span class="message-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <label for="punto_detalle_motivo" class="form-label">Motivo</label>
                        <input type="text" class="form-control" id="punto_detalle_motivo" wire:model="punto_detalle_motivo" name="punto_detalle_motivo" />
                        @error('punto_detalle_motivo') <span class="message-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label class="form-label">Vendedores</label>
                        <select class="form-select" wire:model="vendedor_seleccionado" wire:change="agregar_vendedor">
                            <option value="">{{ $id_cliente_rpm ? 'Seleccionar vendedor...' : 'Primero selecciona un cliente' }}</option>
                            @if($vendedores_disponibles && count($vendedores_disponibles) > 0)
                                @foreach($vendedores_disponibles as $vendedor)
                                    <option value="{{ $vendedor->id_vendedor_intranet }}">
                                        {{ $vendedor->vendedor_intranet_dni }} - {{ $vendedor->vendedor_intranet_nombre }} {{ $vendedor->vendedor_intranet_apellido }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="" class="form-label">Archivo PDF</label>
                        <input class="form-control" type="file" id="manual_archivo_pdf" wire:model="manual_archivo_pdf" />
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>N°</th>
                                    <th>Vendedor</th>
                                    <th>DNI</th>
                                    <th>Puntos</th>
                                    <th>Acciones</th>
                                </tr>
                            </x-slot>

                            <x-slot name="tbody">
                                @if(count($vendedores_seleccionados) > 0)
                                    @php $conteoRP = 1; @endphp
                                    @foreach($vendedores_seleccionados as $index => $vendedor)
                                        <tr>
                                            <td>{{$conteoRP}}</td>
                                            <td>{{ $vendedor['vendedor_intranet_nombre'] }} {{ $vendedor['vendedor_intranet_apellido'] }}</td>
                                            <td>{{ $vendedor['vendedor_intranet_dni'] }}</td>
                                            <td>
                                                <input type="text" class="form-control" id="vendedores_seleccionados.{{ $index }}.puntos" wire:model="vendedores_seleccionados.{{ $index }}.puntos" onkeyup="validar_numeros(this.id)" placeholder="Ingrese puntos" />
                                            </td>
                                            <td class="text-center">
                                                <a class="btn btn-sm text-danger" wire:click="eliminar_vendedor('{{ $vendedor['id_vendedor_intranet'] }}')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @php $conteoRP++; @endphp
                                    @endforeach
                                @else
                                    <tr class="odd">
                                        <td valign="top" colspan="5" class="dataTables_empty text-center">
                                            {{ $id_cliente_rpm ? 'Selecciona vendedores para mostrar en la tabla.' : 'Primero selecciona un cliente para ver vendedores disponibles.' }}
                                        </td>
                                    </tr>
                                @endif
                            </x-slot>
                        </x-table-general>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Guardar Registros</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL REGISTRAR PUNTOS MANUALMENTE--}}

{{--    MODAL EDITAR PUNTO--}}
    <x-modal-general wire:ignore.self >
        <x-slot name="id_modal">modal_editar_punto</x-slot>
        <x-slot name="titleModal">Gestionar Puntos</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="loader mt-2 w-100" wire:loading
                         wire:target="buscarClientesFiltroModal,seleccionar_cliente_modal">
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12">
                @if (session()->has('error_moda_editar'))
                    <div class="alert alert-danger alert-dismissible show fade">
                        {{ session('error_moda_editar') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session()->has('success_modal_editar'))
                    <div class="alert alert-success alert-dismissible show fade">
                        {{ session('success_modal_editar') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>
            <form wire:submit.prevent="save_editar_punto">
                <h5 class="mb-3 text-end me-3">Código: <b>{{$punto_codigo}}</b></h5>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="id_campania" class="form-label">Campaña <b class="text-danger">(*)</b></label>
                        <select id="id_campania" class="form-select" wire:model="id_campania">
                            <option value="">Seleccionar</option>
                            @foreach($listar_campanias as $lc)
                                <option value="{{ $lc->id_campania }}"{{ $id_campania == $lc->id_campania ? 'selected' : '' }}>{{ $lc->campania_nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3 position-relative">
                        <label class="form-label">CLIENTE <b class="text-danger">(*)</b>:</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Buscar cliente"
                               wire:model="buscar_clientes"
                               wire:keyup="buscarClientesFiltroModal">

                        @if($abrirListasClienteModal)
                            <div style="width: 100%; z-index: 999" class="position-absolute top-100 start-0 mt-1 z-10" id="lista_cliente_modal">
                                <div class="list-group bg-white shadow-sm">
                                    @foreach($listaClientesFiltro as $l)
                                        <a style="cursor: pointer" class="list-group-item list-group-item-action"
                                           wire:click="seleccionar_cliente_modal('{{base64_encode($l->id_cliente)}}')">
                                            {{ $l->cliente_codigo_cliente . ' - ' . $l->cliente_nombre_cliente }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @error('id_cliente') <span class="message-error">{{ $message }}</span> @enderror
                    </div>

                    @if(count($listar_detalles) > 0)
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <x-table-general>
                                <x-slot name="thead">
                                    <tr>
                                        <th>N°</th>
                                        <th>Motivo</th>
                                        <th>Vendedor</th>
                                        <th>Puntos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </x-slot>
                                <x-slot name="tbody">
                                    @php $pd = 1 @endphp
                                    @foreach($listar_detalles as $ld)
                                        <tr>
                                            <td>{{$pd}}</td>
                                            <td>
                                                @if(in_array($ld->id_punto_detalle, $editando_registros))
                                                    <input type="text" class="form-control form-control-sm"
                                                           wire:model="datos_edicion.{{$ld->id_punto_detalle}}.motivo"
                                                           placeholder="Motivo">
                                                @else
                                                    {{$ld->punto_detalle_motivo}}
                                                @endif
                                            </td>
                                            <td>
                                                @if(in_array($ld->id_punto_detalle, $editando_registros))
                                                    <input type="text" class="form-control form-control-sm"
                                                           wire:model="datos_edicion.{{$ld->id_punto_detalle}}.vendedor"
                                                           placeholder="Vendedor">
                                                @else
                                                    {{$ld->punto_detalle_vendedor}}
                                                @endif
                                            </td>
                                            <td>
                                                @if(in_array($ld->id_punto_detalle, $editando_registros))
                                                    <input type="text" class="form-control form-control-sm"
                                                           wire:model="datos_edicion.{{$ld->id_punto_detalle}}.puntos"
                                                           placeholder="Puntos">
                                                @else
                                                    {{number_format($ld->punto_detalle_punto_ganado, 0)}}
                                                @endif
                                            </td>
                                            <td>
                                                @if(in_array($ld->id_punto_detalle, $editando_registros))
                                                    <a class="btn text-danger me-1"
                                                       wire:click="cancelar_edicion_registro({{$ld->id_punto_detalle}})">
                                                        <i class="fa-regular fa-rectangle-xmark"></i>
                                                    </a>
                                                @else
                                                    <a class="btn btn-sm text-primary me-2" wire:click="activar_edicion({{$ld->id_punto_detalle}})">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <a class="btn btn-sm text-danger me-2" wire:click="eliminar_punto_detalle({{$ld->id_punto_detalle}})">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                        @php $pd++ @endphp
                                    @endforeach
                                </x-slot>
                            </x-table-general>
                        </div>
                    @else
                        <p>No se han encontrado resultados.</p>
                    @endif

                    <!-- boton de guardar, editar, cancelar edición y eliminar -->
                    <div wire:loading wire:target="save_editar_punto, activar_edicion, eliminar_punto_detalle, cancelar_edicion_registro" class="overlay__eliminar">
                        <div class="spinner__container__eliminar">
                            <div class="spinner__eliminar"></div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Guardar Registros</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL EDITAR PUNTO--}}

{{--    MODAL DELETE PUNTO--}}
    <x-modal-delete wire:ignore.self >
        <x-slot name="id_modal">modal_delete_punto</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="delete_punto">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">¿Estás seguro de eliminar este punto?</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_punto') <span class="message-error">{{ $message }}</span> @enderror

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
{{--    FIN MODAL DELETE PUNTO--}}

    <div class="row align-items-center mb-3">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
            <label for="id_campania_busqueda" class="form-label">Campaña</label>
            <select class="form-select" id="id_campania_busqueda" wire:model.live="id_campania_busqueda">
                <option>Seleccionar...</option>
                @foreach($listar_campanias as $lc)
                    <option value="{{ $lc->id_campania }}">{{ $lc->campania_nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mb-3 position-relative">
            <label class="form-label">Cliente</label>
            <input type="text"
                   class="form-control"
                   placeholder="Buscar"
                   wire:model="buscar_clientes_search"
                   wire:keyup="buscarClientesFiltroVista()">

            @if($abrirListasCliente)
                <div style="width: 120%; z-index: 999" class="position-absolute top-100 start-0 mt-1 z-10" id="lista_cliente_reporte">
                    <div class="list-group bg-white shadow-sm">
                        @foreach($listaClientesFiltro as $l)
                            <a style="cursor: pointer" class="list-group-item list-group-item-action"
                               wire:click="seleccionar_cliente_vista('{{base64_encode($l->id_cliente)}}')">
                                {{ $l->cliente_codigo_cliente . ' - ' . $l->cliente_nombre_cliente }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

{{--        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">--}}
{{--            <label class="form-label">Buscar Vendedor</label>--}}
{{--            <input type="text" class="form-control w-100 me-4"  wire:model.live="search_puntos" placeholder="Buscar">--}}
{{--        </div>--}}

        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2 text-end">
            <a class="btn btn-sm bg-primary text-white" wire:click="clear_form" data-bs-toggle="modal" data-bs-target="#modal_registrar_puntos_manualmente">Registrar Puntos</a>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2 text-end">
            <a class="btn btn-sm bg-warning text-black" wire:click="descargar_formato_excel_puntos">Descargar Formato Excel</a>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2 text-end">
            <x-btn-export wire:click="clear_form" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modal_carga_excel" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Cargar Excel
            </x-btn-export>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 text-end mb-2">
            <a href="{{route('CRM.sistema_puntos_vendedor_cliente')}}" class="btn bg-secondary text-white"><i class="fa-solid fa-arrow-left me-2"></i> Regresar</a>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2 w-100" wire:loading
                 wire:target="buscarClientesFiltroVista,seleccionar_cliente_vista">
            </div>
        </div>

        <div wire:loading wire:target="id_campania_busqueda" class="overlay__eliminar">
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

    <div class="row">
        <div class="col-lg-12 mt-2 mb-3">
            <div class="card">
                <div class="card-body">
                    @if(empty($id_campania_busqueda) && empty($id_cliente_busqueda))
                        <h6 class="text-black">Seleccione una campaña o cliente para ver los resultados.</h6>
                    @else
                        @if(count($listar_puntos) > 0)
                            <x-table-general>
                                <x-slot name="thead">
                                    <tr>
                                        <th>#</th>
                                        <th>Código</th>
                                        <th>Campaña</th>
                                        <th>Cliente</th>
                                        <th>Historial Registro Puntos</th>
                                        <th>Fecha Registro</th>
                                        <th>Usuario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </x-slot>
                                <x-slot name="tbody">
                                    @foreach($listar_puntos as $index => $punto)
                                        <tr>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapse{{ $punto->id_punto }}"
                                                        aria-expanded="false"
                                                        aria-controls="collapse{{ $punto->id_punto }}">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                                {{ $index + 1 }}
                                            </td>
                                            <td>{{ $punto->punto_codigo }}</td>
                                            <td>
                                                @php
                                                    $campania = \Illuminate\Support\Facades\DB::table('campanias')->where('id_campania','=',$punto->id_campania)->first();
                                                @endphp
                                                {{ $campania ? $campania->campania_nombre : '-' }}
                                            </td>
                                            <td>
                                                @php
                                                    $cliente = \Illuminate\Support\Facades\DB::table('clientes')->where('id_cliente','=',$punto->id_cliente)->first();
                                                @endphp
                                                {{ $cliente ? $cliente->cliente_nombre_cliente : '-' }}
                                            </td>
                                            <td>
                                                <a class="btn btn-sm bg-success text-white" wire:click="generar_historial_puntos({{$punto->id_cliente}})">
                                                    HISTORIAL
                                                </a>
                                            </td>
                                            <td>{{ $punto->created_at ? $general->obtenerNombreFecha($punto->created_at, 'DateTime', 'Date') : '-' }}</td>
                                            <td>
                                                @php
                                                    $user = \App\Models\User::find($punto->id_users);
                                                @endphp
                                                {{ $user ? $user->name : 'Usuario no encontrado' }}
                                            </td>
                                            <td>
                                                <a class="btn text-primary" wire:ignore.self wire:click="editar_punto('{{ base64_encode($punto->id_punto) }}')" data-bs-toggle="modal" data-bs-target="#modal_editar_punto">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a class="btn text-danger" wire:ignore.self wire:click="btn_punto('{{ base64_encode($punto->id_punto) }}')" data-bs-toggle="modal" data-bs-target="#modal_delete_punto">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <!-- Fila de detalles (acordeón) -->
                                        <tr class="collapse" wire:ignore.self id="collapse{{ $punto->id_punto }}">
                                            <td colspan="9">
                                                <div class="p-3 bg-light">
                                                    <h6 class="mb-3">Detalles del Punto: {{ $punto->punto_codigo }}</h6>

                                                    @if($punto->puntos_detalles && count($punto->puntos_detalles) > 0)
                                                        <x-table-general>
                                                            <x-slot name="thead">
                                                                <tr>
                                                                    <th>N°</th>
                                                                    <th>Motivo</th>
                                                                    <th>Vendedor Nombre</th>
                                                                    <th>Vendedor DNI</th>
                                                                    <th>Puntos Ganados</th>
                                                                    <th>Fecha de Registro</th>
                                                                    <th>Fecha de Modificación</th>
                                                                </tr>
                                                            </x-slot>
                                                            <x-slot name="tbody">
                                                                @php $conteo_detalle = 1; @endphp
                                                                @foreach($punto->puntos_detalles as $detalle)
                                                                    <tr>
                                                                        <td>{{$conteo_detalle}}</td>
                                                                        <td>{{ $detalle->punto_detalle_motivo }}</td>
                                                                        <td>{{ $detalle->vendedor_nombre ?? '-' }}</td>
                                                                        <td>{{ $detalle->punto_detalle_vendedor }}</td>
                                                                        <td>{{ number_format($detalle->punto_detalle_punto_ganado, 0) }}</td>
                                                                        <td>{{ $detalle->punto_detalle_fecha_registro ? $general->obtenerNombreFecha($detalle->punto_detalle_fecha_registro, 'DateTime', 'Date') : '-' }}</td>
                                                                        <td>
                                                                            @if($detalle->punto_detalle_fecha_modificacion)
                                                                                {{ $detalle->punto_detalle_fecha_modificacion ? $general->obtenerNombreFecha($detalle->punto_detalle_fecha_modificacion, 'DateTime', 'Date') : '-' }}
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                    @php $conteo_detalle++; @endphp
                                                                @endforeach
                                                            </x-slot>
                                                        </x-table-general>
                                                    @else
                                                        <div class="alert alert-info mb-0">
                                                            <i class="fas fa-info-circle"></i> No hay detalles registrados para este punto.
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </x-slot>
                            </x-table-general>
                        @else
                            <h6 class="text-black">No se encontraron resultados para los filtros seleccionados.</h6>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@script
<script>
    $wire.on('hide_modal_carga_excel', () => {
        $('#modal_carga_excel').modal('hide');
    });

    $wire.on('hide_modal_editar_punto', () => {
        $('#modal_editar_punto').modal('hide');
    });

    $wire.on('hide_modal_delete_punto', () => {
        $('#modal_delete_punto').modal('hide');
    });

    $wire.on('hide_modal_registrar_puntos_manualmente', () => {
        $('#modal_registrar_puntos_manualmente').modal('hide');
    });
</script>
@endscript
