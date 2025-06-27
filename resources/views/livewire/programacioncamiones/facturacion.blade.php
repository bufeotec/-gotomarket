<div>
    @php
        $me = new \App\Models\General();
    @endphp
    {{--    MODAL CAMBIAR ESTADO PRE PROGRAMACION--}}
    <x-modal-delete wire:ignore.self>
        <x-slot name="id_modal">modalPrePro</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_pre_pro">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{ $messagePrePro }}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_fac_pre_prog') <span class="message-error">{{ $message }}</span> @enderror
                        @error('fac_pre_prog_estado_aprobacion') <span class="message-error">{{ $message }}</span> @enderror
                        @if (session()->has('error_pre_pro'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_pre_pro') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3" id="fechaHoraContainer" style="display: none;">
                        <label for="fechaHoraManual">Modificar fecha y hora:</label>
                        <input type="datetime-local" id="fechaHoraManual" wire:model="fechaHoraManual" wire:change="actualizarMensaje" class="form-control">
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">ENVIAR</button>
                        <button type="button" class="btn btn-success btnDelete" id="btnEditar">EDITAR</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
    {{-- MODAL RECHAZAR FACTURA EN APROBRAR --}}

    {{--    MODAL EDITAR ESTADO--}}
    <x-modal-delete wire:ignore.self>
        <x-slot name="id_modal">modalEditCambioEstado</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambio_estado_edit">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">Cambio de estado de la guía</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('guia_estado_aprobacion') <span class="message-error">{{ $message }}</span> @enderror
                        @error('id_guia') <span class="message-error">{{ $message }}</span> @enderror
                        @if (session()->has('error-edit-guia'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_delete') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-12">
                        <label for="guia_estado_aprobacion" class="form-label">Estado Guía</label>
                        <select name="guia_estado_aprobacion" id="guia_estado_aprobacion" wire:model.live="guia_estado_aprobacion" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option value="1">Creditos</option>
                            <option value="2">Despacho</option>
                            <option value="3">Por programar</option>
                            <option value="4">Programado</option>
                            <option value="7">En ruta</option>
                            <option value="12">Anulado</option>
                            <option value="8">Entregado</option>
                        </select>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">ENVIAR</button>
                        <button type="button" data-bs-dismiss="modal" class="btn btn-danger btnDelete">No</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
    <x-modal-delete wire:ignore.self>
        <x-slot name="id_modal">modalActualizarDetalle</x-slot>
        <x-slot name="modalContentDelete">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <h2 class="deleteTitle">Actualizando detalle de la guía</h2>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="loader mt-2" wire:loading wire:target="actualizar_detalle_guia"></div>
                </div>
                @if (session()->has('success'))
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="alert alert-success alert-dismissible show fade mt-2">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="alert alert-danger alert-dismissible show fade mt-2">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif
            </div>
        </x-slot>
    </x-modal-delete>
    {{-- MODAL FIN EDITAR ESTADO --}}

    {{--    MODAL GESTIONAR ESTADOS--}}
    <x-modal-delete wire:ignore.self>
        <x-slot name="id_modal">modalGeStado</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiarEstado">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{ $messagePrePro }}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_fac_pre_prog') <span class="message-error">{{ $message }}</span> @enderror
                        @error('fac_pre_prog_estado_aprobacion') <span class="message-error">{{ $message }}</span> @enderror
                        @if (session()->has('error_pre_pro'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_pre_pro') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3" id="fechaHoraContainer" style="display: none;">
                        <label for="fechaHoraManual">Modificar fecha y hora:</label>
                        <input type="datetime-local" id="fechaHoraManual" wire:model="fechaHoraManual" wire:change="actualizarMensaje" class="form-control">
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">ENVIAR</button>
                        <button type="button" class="btn btn-success btnDelete" id="btnEditar">EDITAR</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
    {{--    FIN MODAL GESTIONAR ESTADOS--}}

    <x-modal-delete wire:ignore.self style="z-index: 1056;">
        <x-slot name="id_modal">modaRecFac</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="rechazar_factura_aprobar">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{ $messageRecFactApro }}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 my-3">
                        <textarea id="fac_mov_area_motivo_rechazo" class="form-control" rows="3" wire:model="fac_mov_area_motivo_rechazo" placeholder="Ingrese motivo rechazo..."></textarea>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error-modal-rechazo'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error-modal-rechazo') }}
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
    {{-- MODAL RECHAZAR FACTURA EN APROBRAR --}}

{{--    EDITAR FECHA DE GUÍA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalEditCambioFecha</x-slot>
        <x-slot name="titleModal">Cambio de fecha de la guía</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="cambio_fecha_edit_guia">
                <div class="row">
                    <div class="col-lg-12">
                        <h5 class="text-dark mb-3">Guía: <strong>{{$guia_nro_doc}}</strong></h5>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_guia') <span class="message-error">{{ $message }}</span> @enderror
                        @error('guia_estado_aprobacion') <span class="message-error">{{ $message }}</span> @enderror
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>Estado</th>
                                    <th>Fecha de registro</th>
                                    <th>Ultimo usuario que registro</th>
                                    <th>Ultimo comentario</th>
                                    <th>Editar fecha</th>
                                    <th>Motivo de modificación</th>
                                </tr>
                            </x-slot>
                            <x-slot name="tbody">
                                <tr>
                                    <td>Emisión</td>
                                    <td>{{ $guia_fecha_emision ? $me->obtenerNombreFecha($guia_fecha_emision, 'DateTime', 'Date') : ' ' }}</td>
                                    <td>{{$nombre_usuario}}</td>
                                    <td></td>
                                    <td>
                                        <h6 class="text-dark">{{ $guia_fecha_emision ? $me->obtenerNombreFecha($guia_fecha_emision, 'DateTime', 'Date') : '-' }}</h6>
                                    </td>
                                    <td></td>
                                </tr>

                                @php
                                    $estadosConfig = [
                                        5 => 'En Crédito',
                                        3 => 'Por Programar',
                                        9 => 'Programado',
                                        7 => 'En Ruta',
                                        8 => 'Entregado',
                                        11 => 'Anulado'
                                    ];
                                @endphp

                                @foreach($estadosConfig as $estado => $label)
                                    @php
                                        $historial = $historialEstados[$estado] ?? null;
                                    @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>
                                            @isset($historial['historial_guia_fecha_hora'])
                                                {{ $me->obtenerNombreFecha($historial['historial_guia_fecha_hora'], 'DateTime', 'DateTime') }}
                                            @else
                                                -
                                            @endisset
                                        </td>
                                        <td>{{ $historial['name'] ?? '' }}</td>
                                        <td></td>
                                        <td>
                                            @if($historial)
                                                <input type="datetime-local" class="form-control form-control-sm"
                                                       wire:model="fechasEditadas.{{ $estado }}"
                                                       value="{{ $fechasEditadas[$estado] ?? '' }}">
                                            @endif
                                        </td>
                                        <td>
                                            @if($historial)
                                                <textarea class="form-control form-control-sm" rows="3" wire:model="comentariosEditados.{{ $estado }}"></textarea>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                <!-- Estados fijos sin datos -->
                                <tr>
                                    <td>Pendiente de NC</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>

                                <tr>
                                    <td>Anulado con NC</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </x-slot>
                        </x-table-general>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
{{--                        <button type="submit" class="btn btn-success text-white">Guardar Registros</button>--}}
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
{{--    FIN EDITAR FECHA DE GUÍA--}}

    <div class="row">
        @if (session()->has('success'))
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="alert alert-success alert-dismissible show fade mt-2">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="alert alert-danger alert-dismissible show fade mt-2">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="col-lg-12">
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-6 col-sm-12 mb-3 position-relative">
                    <label for="buscar_numero_guia" class="form-label">Número de guía</label>
                    <input type="text" name="buscar_numero_guia" id="buscar_numero_guia" wire:model.live="buscar_numero_guia" class="form-control" placeholder="Ingrese número de guía (ej: T0123456)" >
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                    <label for="buscar_ruc_nombre" class="form-label">Nombre del cliente</label>
                    <input type="text" name="buscar_ruc_nombre" id="buscar_ruc_nombre" wire:model.live="buscar_ruc_nombre" class="form-control" placeholder="Buscar por RUC o nombre de cliente">
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                    <label for="buscar_ruc_nombre" class="form-label">Seleccionar estado</label>
                    <select name="guia_estado_aprobacion" id="guia_estado_aprobacion" wire:model="buscar_estado" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="1">Creditos</option>
                        <option value="2">Despacho</option>
                        <option value="3">Por programar</option>
                        <option value="4">Programado</option>
                        <option value="7">En ruta</option>
                        <option value="12">Anulado</option>
                        <option value="8">Entregado</option>
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
                <div class="col-lg-2 col-md-3 col-sm-12 mb-3 mt-4">
                    <button class="btn btn-sm bg-primary text-white w-75" wire:click="buscar_comprobantes" wire:loading.attr="disabled">
                        <i class="fa fa-search"></i>
                        <spanc class="ms-1" wire:loading.remove wire:target="buscar_comprobantes">BUSCAR</spanc>
                        <spanc class="ms-1" wire:loading wire:target="buscar_comprobantes">BUSCANDO...</spanc>
                    </button>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="loader mt-2" wire:loading wire:target="buscar_comprobantes"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <div class="row mb-2">
                        <div class="col-lg-12 ">
                            <h6>Gestionar el estado de la Guía</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            @if(count($listar_comprobantes) > 0)
                                <x-table-general id="facturasPreProgTable">
                                    <x-slot name="thead">
                                        <tr>
                                            <th>Guía</th>
                                            <th>Emisión</th>
                                            <th>Factura</th>
                                            <th>Importe sin IGV</th>
                                            <th>Cliente</th>
                                            <th>Dirección</th>
                                            <th>Peso / Volumen</th>
{{--                                            <th>Recibido</th>--}}
                                            <th>Movimientos</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @foreach($listar_comprobantes as $factura)
                                            <tr>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_nro_doc }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_fecha_emision ? $me->obtenerNombreFecha($factura->guia_fecha_emision, 'DateTime', 'Date') : '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_nro_doc_ref }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        <b class="colorBlackComprobantes">S/ {{ $me->formatoDecimal(($factura->guia_importe_total_sin_igv ?? 0)) }}</b>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_nombre_cliente }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_direc_entrega }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $me->formatoDecimal($factura->total_peso ?? 0)}} g
                                                    <br>
                                                    {{ $me->formatoDecimal($factura->total_volumen ?? 0)}} cm³
                                                </td>
{{--                                                <td>--}}
{{--                                                    <span class="d-block tamanhoTablaComprobantes">--}}
{{--                                                        {{ date('d/m/Y - h:i a', strtotime($factura->updated_at)) }}--}}
{{--                                                    </span>--}}
{{--                                                </td>--}}
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes text-primary">
                                                        @switch($factura->guia_estado_aprobacion)
                                                            @case(1)
                                                                Enviado a Créditos
                                                                @break
                                                            @case(2)
                                                                Enviado a Despacho
                                                                @break
                                                            @case(3)
                                                                Listo para despacho
                                                                @break
                                                            @case(4)
                                                                Pendiente de aprobación de despacho
                                                                @break
                                                            @case(5)
                                                                Aceptado por Créditos
                                                                @break
                                                            @case(6)
                                                                Estado de facturación
                                                                @break
                                                            @case(7)
                                                                Guía en transtio
                                                                @break
                                                            @case(8)
                                                                Guía entregada
                                                                @break
                                                            @case(9)
                                                                Despacho aprobado
                                                                @break
                                                            @case(10)
                                                                Despacho rechazado
                                                                @break
                                                            @case(11)
                                                                Guía no entregada
                                                                @break
                                                            @case(12)
                                                                Guía anulada
                                                                @break
                                                                @default
                                                                Estado desconocido
                                                        @endswitch
                                                    </span>
                                                </td>
                                                <td>
{{--                                                    @if ($factura->guia_estado_aprobacion == 6)--}}
{{--                                                        <x-btn-accion class="btn bg-success btn-sm text-white" wire:click="cambio_estado('{{ base64_encode($factura->id_guia) }}', 2)" data-bs-toggle="modal" data-bs-target="#modalPrePro">--}}
{{--                                                            <x-slot name="message">--}}
{{--                                                                <i class="fa-solid fa-check"></i>--}}
{{--                                                            </x-slot>--}}
{{--                                                        </x-btn-accion>--}}
{{--                                                    @endif--}}
                                                    @if($factura->guia_estado_aprobacion != 7)
                                                        <x-btn-accion class="btn bg-primary btn-sm text-white" wire:click="edit_guia('{{ base64_encode($factura->id_guia) }}')"
                                                                      data-bs-toggle="modal" data-bs-target="#modalEditCambioEstado">
                                                            <x-slot name="message">
                                                                <i class="fa-solid fa-edit"></i>
                                                            </x-slot>
                                                        </x-btn-accion>

                                                        <x-btn-accion class="btn bg-info btn-sm text-white" wire:click="edit_fecha_guia('{{ base64_encode($factura->id_guia) }}')" data-bs-toggle="modal" data-bs-target="#modalEditCambioFecha">
                                                            <x-slot name="message">
                                                                <i class="fa-solid fa-calendar-days"></i>
                                                            </x-slot>
                                                        </x-btn-accion>
                                                    @endif
                                                    <br>
                                                    <a data-bs-toggle="modal" data-bs-target="#modalActualizarDetalle" wire:click="actualizar_detalle_guia('{{ $factura->guia_nro_doc }}',
                                                    '{{ base64_encode($factura->id_guia) }}')" style="cursor:pointer;" class="btn-sm btn-warning text-white">
                                                        <i class="fa fa-refresh"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </x-slot>
                                </x-table-general>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('hidemodalPrePro', () => {
        $('#modalPrePro').modal('hide');
    });

    $wire.on('hidemodaRecFac', () => {
        $('#modaRecFac').modal('hide');
    });

    $wire.on('modalEditCambioEstado', () => {
        $('#modalEditCambioEstado').modal('hide');
    });

    document.getElementById("btnEditar").addEventListener("click", function() {
        let container = document.getElementById("fechaHoraContainer");
        let inputFecha = document.getElementById("fechaHoraManual");

        // Mostrar el contenedor con el label y el input
        container.style.display = "block";
        inputFecha.focus();
    });
</script>
@endscript
