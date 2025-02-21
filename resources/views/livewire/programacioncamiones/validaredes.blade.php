<div>
    @php
        $me = new \App\Models\General();
    @endphp
    {{--    MODAL CAMBIAR ESTA PRE PROGRAMACION--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalPrePro</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_pre_pro">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messagePrePro}}</h2>
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
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="submit" class="btn btn-primary text-white btnDelete">SI</button>
                        <button type="button" data-bs-dismiss="modal" class="btn btn-danger btnDelete">No</button>
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
                        <h6>Recibidos por validar</h6>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            @if(count($facturas_pre_prog_estado_dos) > 0)
                                <x-table-general id="facturasPreProgTable">
                                    <x-slot name="thead">
                                        <tr>
                                            <th>Serie / Factura</th>
                                            <th>F. Emisión</th>
                                            <th>Importe sin IGV</th>
                                            <th>Nombre Cliente</th>
                                            <th>Peso y Volumen</th>
                                            <th>Dirección</th>
                                            <th>Fecha/Hora Recibida</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @foreach($facturas_pre_prog_estado_dos as $factura)
                                            <tr>
                                                <td>
                                                <span class="d-block tamanhoTablaComprobantes">
                                                    {{ $factura->fac_pre_prog_cfnumser }} - {{ $factura->fac_pre_prog_cfnumdoc }}
                                                </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $fechaEmision = \Carbon\Carbon::parse($factura->fac_pre_prog_grefecemision)->format('d/m/Y');
                                                    @endphp
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                    {{ $fechaEmision }}
                                                </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $importe = number_format($factura->fac_pre_prog_cfimporte, 2, '.', ',');
                                                    @endphp
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                    <b class="colorBlackComprobantes">{{ $importe }}</b>
                                                </span>
                                                </td>
                                                <td>
                                                <span class="d-block tamanhoTablaComprobantes">
                                                    {{ $factura->fac_pre_prog_cnomcli }}
                                                </span>
                                                </td>
                                                <td>
                                                <span class="d-block tamanhoTablaComprobantes">
                                                    <b class="colorBlackComprobantes">{{ number_format($factura->fac_pre_prog_total_kg, 2, '.', ',') }} kg</b>
                                                </span>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                    <b class="colorBlackComprobantes">{{ number_format($factura->fac_pre_prog_total_volumen, 2, '.', ',') }} cm³</b>
                                                </span>
                                                </td>
                                                <td>
                                                <span class="d-block tamanhoTablaComprobantes">
                                                    {{ $factura->fac_pre_prog_direccion_llegada }}
                                                </span>
                                                    <br>
                                                    <span class="d-block tamanhoTablaComprobantes" style="color: black;font-weight: bold">
                                                    {{ $factura->fac_pre_prog_departamento }} - {{ $factura->fac_pre_prog_provincia }} - {{ $factura->fac_pre_prog_distrito }}
                                                </span>
                                                </td>
                                                <td>
                                                <span class="d-block tamanhoTablaComprobantes">
                                                    {{date('d/m/Y - h:i A', strtotime($factura->updated_at)) }}
                                                </span>
                                                </td>
                                                <td>
                                                    <x-btn-accion class="btn bg-success btn-sm text-white" wire:click="cambio_estado('{{ base64_encode($factura->id_fac_pre_prog) }}', 3)" data-bs-toggle="modal" data-bs-target="#modalPrePro">
                                                        <x-slot name="message">
                                                            <i class="fa-solid fa-check"></i>
                                                        </x-slot>
                                                    </x-btn-accion>

                                                    {{--                                                    <x-btn-accion class="btn btn-danger btn-sm text-white" wire:click="rech_fact('{{ base64_encode($factura->id_fac_pre_prog) }}')" data-bs-toggle="modal" data-bs-target="#modaRecFac">--}}
                                                    {{--                                                        <x-slot name="message">--}}
                                                    {{--                                                            <i class="fa-regular fa-circle-xmark"></i>--}}
                                                    {{--                                                        </x-slot>--}}
                                                    {{--                                                    </x-btn-accion>--}}
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
</script>
@endscript
