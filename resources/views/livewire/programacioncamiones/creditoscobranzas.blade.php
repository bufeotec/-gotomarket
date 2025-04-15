<div>
    @php
        $me = new \App\Models\General();
    @endphp
    {{--    MODAL ACEPTAR CREDITO --}}
    <x-modal-delete wire:ignore.self style="z-index: 1056;">
        <x-slot name="id_modal">modalMotCre</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="aceptar_fac_credito">
                <div class="row">
                    @error('selectedGuiaIds') <span class="message-error">{{ $message }}</span> @enderror
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{ $messageMotCre }}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3" id="fechaHoraContainer3" style="display: none;">
                        <label for="fechaHoraManual3">Modificar fecha y hora:</label>
                        <input type="datetime-local" id="fechaHoraManual3" wire:model="fechaHoraManual3" wire:change="actualizarMensaje2" class="form-control">
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">ACEPTAR</button>
                        <button type="button" class="btn btn-success btnDelete" id="btnEdit">EDITAR</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
    {{--    FIN MODAL ACEPTAR CREDITO --}}

    {{-- MODAL RECHAZAR CREDITO --}}
    <x-modal-delete wire:ignore.self style="z-index: 1056;">
        <x-slot name="id_modal">modalMotReCre</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="rechazar_fac_credito">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{ $messageMotReCre }}</h2>
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
    {{-- FIN MODAL RECHAZAR CREDITO --}}

    {{--    MODAL ENVIAR A FACTURAS POR APROBAR --}}
    <x-modal-delete wire:ignore.self style="z-index: 1056;">
        <x-slot name="id_modal">modalFacApro</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="confirmarEnvio">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{ $messageFacApro }}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">ENVIAR</button>
                        <button type="button" class="btn btn-danger btnDelete" id="btnEditar" data-bs-dismiss="modal">CANCELAR</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
    {{--    FIN MODAL ENVIAR A FACTURAS POR APROBAR --}}

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
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <h6>GUÍAS PENDIENTE POR APROBAR</h6>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                            <input type="date" name="desde" id="desde" wire:model.live="desde" class="form-control" min="2025-01-01">
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                            <input type="date" name="hasta" id="hasta" wire:model.live="hasta" class="form-control" min="2025-01-01">
                        </div>
                        <div class="col-lg-4 col-md-3 col-sm-12 mb-2 mt-1">
                            <button class="btn btn-sm bg-primary text-white w-100" wire:click="buscar_comprobantes" >
                                <i class="fa fa-search"></i> BUSCAR
                            </button>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="loader mt-2" wire:loading wire:target="buscar_comprobantes"></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="contenedor-comprobante" style="max-height: 600px; overflow: auto">
                                <x-table-general>
                                    <x-slot name="thead">
                                        <tr>
                                            <th>
                                                <input type="checkbox" wire:model.live="select_guias_all" id="select_guias_all-all" class="form-check-input">
                                            </th>
                                            <th style="font-size: 12px">Guía / Factura</th>
                                            <th style="font-size: 12px">Fecha Emisión</th>
                                            <th style="font-size: 12px">Importe sin IGV</th>
                                            <th style="font-size: 12px">Nombre del Cliente</th>
                                            <th style="font-size: 12px">Acciones</th>
                                        </tr>
                                    </x-slot>

                                    <x-slot name="tbody">
                                        @if(!empty($filteredFacturas))
                                            @foreach($filteredFacturas as $guia)
                                                <tr style="cursor: pointer">
                                                    <td colspan="6" style="padding: 0px">
                                                        <table class="table">
                                                            <tbody>
                                                            <tr>
                                                                <td>
                                                                    <input type="checkbox" wire:model.live="selectedGuiaIds" value="{{ $guia->id_guia }}" id="checkbox-{{ $guia->id_guia }}" class="form-check-input">
                                                                </td>
                                                                <td style="width: 39.6%">
                                                                    <span class="d-block tamanhoTablaComprobantes">
                                                                        {{ $guia->guia_nro_doc }} - {{ $guia->guia_nro_doc_ref }}
                                                                    </span>
                                                                </td>
                                                                <td style="width: 32.2%">
                                                                    <span class="d-block tamanhoTablaComprobantes">
                                                                        {{ $me->obtenerNombreFecha($guia->guia_fecha_emision, 'DateTime', 'Date') }}
                                                                    </span>
                                                                </td>
                                                                <td style="width: 32.2%">
                                                                    <span class="d-block tamanhoTablaComprobantes">
                                                                        {{ $me->formatoDecimal(($guia->guia_importe_total ?? 0) / 1.18) }}
                                                                    </span>
                                                                </td>
                                                                <td style="width: 32.2%">
                                                                    <span class="d-block tamanhoTablaComprobantes">
                                                                        {{ $guia->guia_nombre_cliente }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <x-btn-accion class="btn btn-success btn-sm text-white" wire:click="pre_mot_cre('{{ base64_encode($guia->id_guia) }}')" data-bs-toggle="modal" data-bs-target="#modalMotCre">
                                                                        <x-slot name="message">
                                                                            <i class="fa-solid fa-check"></i>
                                                                        </x-slot>
                                                                    </x-btn-accion>
                                                                </td>
                                                            </tr>
                                                            <tr style="border-top: 2px solid transparent;">
                                                                <td colspan="6" style="padding-top: 0">
                                                                    <span class="d-block tamanhoTablaComprobantes">

                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center">
                                                    <p class="mb-0" style="font-size: 12px">No se encontraron comprobantes.</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </x-slot>
                                </x-table-general>
                            </div>
                            @if(count($selectedGuiaIds) > 0)
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-2 text-end">
                                    <button
                                        class="btn text-white bg-warning mt-4"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalMotCre"
                                        wire:click="pre_mot_cre()"
                                    >
                                        Aceptar Guías ({{ count($selectedGuiaIds) }})
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div wire:loading wire:target="seleccionarFactura" class="overlay__eliminar">
                <div class="spinner__container__eliminar">
                    <div class="spinner__eliminar"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <div class="row">
                                <div class="col-lg-12 mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                            <h6 class="mb-0">Guías Recepcionadas</h6>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2 text-end">
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFacApro" id="sendButton" disabled>
                                                Enviar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="contenedor-comprobante" style="max-height: 600px; overflow: auto">
                                        @if(count($facturasCreditoAprobadas) > 0)
                                            <x-table-general id="ederTable">
                                                <x-slot name="thead">
                                                    <tr>
                                                        <th class="">
                                                            <input class="form-check-input" type="checkbox" wire:model="selectAll" id="selectAll" onchange="toggleAllCheckboxes(this)">
                                                        </th>
                                                        <th class="">Guía / Factura</th>
                                                        <th class="">F. Emisión</th>
                                                        <th class="">Importe sin IGV</th>
                                                        <th class="">Nombre Cliente</th>
                                                        <th class="">Dirección</th>
                                                        <th class="">Acciones</th>
                                                    </tr>
                                                </x-slot>
                                                <x-slot name="tbody">
                                                    @foreach($facturasCreditoAprobadas as $factura)
                                                        <tr>
                                                            <td>
                                                                <input class="form-check-input" type="checkbox" wire:model="selectedItems" value="{{ (string) $factura->id_guia }}" onchange="toggleButton()">
                                                            </td>
                                                            <td>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    {{ $factura->guia_nro_doc }} - {{ $factura->guia_nro_doc_ref }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    {{ $me->obtenerNombreFecha($factura->guia_fecha_emision, 'DateTime', 'Date')}}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    <b class="colorBlackComprobantes">
                                                                        @php
                                                                            $importe = floatval($factura->guia_importe_total ?? 0); // Forzar conversión a número
                                                                            echo $me->formatoDecimal($importe / 1.18);
                                                                        @endphp
                                                                    </b>
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
                                                                <x-btn-accion class="btn btn-success btn-sm text-white" wire:click="enviar_fac_apro('{{ base64_encode($factura->id_guia) }}')" data-bs-toggle="modal" data-bs-target="#modalFacApro">
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
                                            <p>No hay facturas aprobadas para crédito.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function toggleAllCheckboxes(source) {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][wire\\:model="selectedItems"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
            checkbox.dispatchEvent(new Event('change'));
        });
        toggleButton(); // Actualiza el estado del botón
    }

    function toggleButton() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][wire\\:model="selectedItems"]');
        const sendButton = document.getElementById('sendButton');
        const isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
        sendButton.disabled = !isChecked; // Habilitar o deshabilitar el botón
    }
</script>
@script
<script>
    $wire.on('hidemodalMotCre', () => {
        $('#modalMotCre').modal('hide');
    });

    $wire.on('hidemodalMotReCre', () => {
        $('#modalMotReCre').modal('hide');
    });

    $wire.on('hidemodalFacApro', () => {
        $('#modalFacApro').modal('hide');
    });
    document.getElementById("btnEdit").addEventListener("click", function() {
        let container = document.getElementById("fechaHoraContainer3");
        let inputFecha = document.getElementById("fechaHoraManual3");

        // Mostrar el contenedor con el label y el input
        container.style.display = "block";
        inputFecha.focus();
    });

    document.getElementById("btnEditar").addEventListener("click", function() {
        let container = document.getElementById("fechaHoraContainer");
        let inputFecha = document.getElementById("fechaHoraManual2");

        // Mostrar el contenedor con el label y el input
        container.style.display = "block";
        inputFecha.focus();
    });
</script>
@endscript
