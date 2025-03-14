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
            <div class="card">
                <div class="card-body table-responsive">
                    <div class="row mb-2">
                        <h6>Gestionar el estado de la Factura</h6>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            @if(count($facturas_pre_prog_estadox) > 0)
                                <x-table-general id="facturasPreProgTable">
                                    <x-slot name="thead">
                                        <tr>
                                            <th>Guía</th>
                                            <th>F. Emisión</th>
                                            <th>Factura</th>
                                            <th>Importe sin IGV</th>
                                            <th>Cliente</th>
                                            <th>Dirección</th>
{{--                                            <th>Peso y Volumen</th>--}}
                                            <th>Fecha/Hora Recibida</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @foreach($facturas_pre_prog_estadox as $factura)
                                            <tr>
                                                <td>
                                                <span class="d-block tamanhoTablaComprobantes">
                                                    {{ $factura->guia_nro_doc }}
                                                </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $fechaEmision = \Carbon\Carbon::parse($factura->guia_fecha_emision)->format('d/m/Y');
                                                    @endphp
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                    {{ $fechaEmision }}
                                                </span>
                                                </td>
                                                <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                    {{ $factura->guia_nro_doc }} - {{ $factura->guia_nro_doc_ref }}
                                                </span>
                                               </td>
                                                <td>
                                                    @php
                                                        $importe = number_format($factura->guia_importe_total, 2, '.', ',');
                                                    @endphp
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                    <b class="colorBlackComprobantes">{{ $importe }}</b>
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
                                                </td>
                                                <td>
                                                <span class="d-block tamanhoTablaComprobantes">
                                                    {{date('d/m/Y - h:i A', strtotime($factura->updated_at)) }}
                                                </span>
                                                </td>
                                                <td>
                                                    <x-btn-accion class="btn bg-success btn-sm text-white" wire:click="cambio_estado('{{ base64_encode($factura->id_guia) }}', 2)" data-bs-toggle="modal" data-bs-target="#modalPrePro">
                                                        <x-slot name="message">
                                                            <i class="fa-solid fa-check"></i>
                                                        </x-slot>
                                                    </x-btn-accion>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </x-slot>
                                </x-table-general>
                            @else
                                <p>No hay facturas pre programación disponibles.</p>
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

    document.getElementById("btnEditar").addEventListener("click", function() {
        let container = document.getElementById("fechaHoraContainer");
        let inputFecha = document.getElementById("fechaHoraManual");

        // Mostrar el contenedor con el label y el input
        container.style.display = "block";
        inputFecha.focus();
    });
</script>
@endscript
