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
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error') }}
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

{{--    MODAL EDITAR PUNTO--}}
    <x-modal-general wire:ignore.self >
        <x-slot name="id_modal">modal_editar_punto</x-slot>
        <x-slot name="titleModal">Gestionar Puntos</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
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
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="id_cliente" class="form-label">Cliente <b class="text-danger">(*)</b></label>
                        <select class="form-select" id="id_cliente" wire:model="id_cliente">
                            <option>Seleccionar...</option>
                        </select>
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
                                                    {{$ld->punto_detalle_punto_ganado}}
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
        <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
            <label for="id_campania_busqueda" class="form-label">Campaña</label>
            <select class="form-select" id="id_campania_busqueda" wire:model="id_campania_busqueda">
                <option>Seleccionar...</option>
            </select>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
            <label for="id_cliente_busqueda" class="form-label">Cliente</label>
            <select class="form-select" id="id_cliente_busqueda" wire:model="id_cliente_busqueda">
                <option>Seleccionar...</option>
            </select>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12 d-flex align-items-center mt-4 mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_puntos" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_puntos" />
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2 text-end">
            <x-btn-export wire:click="clear_form" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modal_carga_excel" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Cargar Excel
            </x-btn-export>
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
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Código</th>
                                <th width="10%">Campaña</th>
                                <th width="15%">Archivo Excel</th>
                                <th width="15%">Archivo PDF</th>
                                <th width="15%">Fecha Registro</th>
                                <th width="15%">Usuario</th>
                                <th width="10%">Acciones</th>
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
                                        @if($punto->punto_documento_excel)
                                            <a href="{{ asset($punto->punto_documento_excel) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-file-excel"></i> Ver Excel
                                            </a>
                                        @else
                                            <span class="text-muted">No disponible</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($punto->punto_documento_pdf)
                                            <a href="{{ asset($punto->punto_documento_pdf) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-file-pdf"></i> Ver PDF
                                            </a>
                                        @else
                                            <span class="text-muted">No disponible</span>
                                        @endif
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
                                    <td colspan="8">
                                        <div class="p-3 bg-light">
                                            <h6 class="mb-3">Detalles del Punto: {{ $punto->punto_codigo }}</h6>

                                            @if($punto->puntos_detalles && count($punto->puntos_detalles) > 0)
                                                <x-table-general>
                                                    <x-slot name="thead">
                                                        <tr>
                                                            <th>N°</th>
                                                            <th>Motivo</th>
                                                            <th>Vendedor</th>
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
                                                                <td>{{ $detalle->punto_detalle_vendedor }}</td>
                                                                <td>{{ $detalle->punto_detalle_punto_ganado }}</td>
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
</script>
@endscript
