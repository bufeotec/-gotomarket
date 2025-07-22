<div>
    @php
        $me = new \App\Models\General();
    @endphp

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

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <h6>GUÍAS DISPONIBLES</h6>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <div class="position-relative">
                                <input type="text"
                                       class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder"
                                       placeholder="Buscar guia"
                                       wire:model.live="searchGuia"
                                       style="border: none; outline: none;"
                                       oninput="document.getElementById('buscarBtn').disabled = this.value.trim() === '';" />
                                <i class="fas fa-search position-absolute"
                                   style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="contenedor-comprobante" style="height: 650px; overflow: auto">
                                @if($guias_estado_tres->isEmpty())
                                    <p class="text-center text-muted">No hay facturas disponibles.</p>
                                @else
                                    <table class="table table-responsive ">
                                        <thead style="background: #E7E7FF; color: #696cff">
                                        <tr>
                                            <th>#</th>
                                            <th style="font-size: 14px">Fecha emisión guía</th>
                                            <th style="font-size: 14px">Guía</th>
                                            <th style="font-size: 14px">Factura</th>
                                            <th style="font-size: 14px">Nombre del cliente</th>
                                            <th style="font-size: 14px">Peso y Volumen</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($guias_estado_tres as $factura)
                                            @if (!in_array($factura->id_guia, array_column($selectedFacturas, 'id_guia')))
                                                <tr>
                                                    <td>
                                                        <button class="btn btn-success btn-sm text-white mb-2 cursoPointer" wire:click="seleccionarFactura({{ $factura->id_guia }})">
                                                            <i class="fa-solid fa-check"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                    <span class="tamanhoTablaComprobantes">
                                                        <b class="colorBlackComprobantes">{{ $me->obtenerNombreFecha($factura->guia_fecha_emision,'DateTime', 'Date')}}</b>
                                                    </span>
                                                    </td>
                                                    <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_nro_doc }}
                                                    </span>
                                                        <button class="btn btn-sm text-primary mb-2" wire:click="modal_guia_info({{$factura->id_guia}})" data-bs-toggle="modal" data-bs-target="#modalInformacionGuia">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        @if($factura->guia_tipo_doc)
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                            {{ $factura->guia_nro_doc_ref}}
                                                        </span>
                                                            <button class="btn btn-sm text-primary mb-2" wire:click="listar_detalle_guia({{$factura->id_guia}})" data-bs-toggle="modal" data-bs-target="#modalDetalleGuia">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                        @else
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                            Sin Factura Asociada
                                                        </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_nombre_cliente }}
                                                    </span>
                                                    </td>
                                                    <td>
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura->peso_total)}} kg</b>
                                                    </span>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                        <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura->volumen_total )}} cm³</b>
                                                    </span>
                                                    </td>
                                                </tr>
                                                <tr style="border-top: 2px solid transparent;">
                                                    <td colspan="5" style="padding-top: 0; user-select: all;">
                                                    <span class="d-block tamanhoTablaComprobantes">
                                                        {{ $factura->guia_direc_entrega }} <br> UBIGEO: <b class="colorBlackComprobantes">{{ $factura->guia_departamento }} - {{ $factura->guia_provincia }} - {{ $factura->guia_destrito }}</b>
                                                    </span>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="card ">
                        <div class="card-body">
                            <h6>Fecha de despacho</h6>
                            <input type="date" class="form-control" id="programacion_fecha" name="programacion_fecha" wire:model="programacion_fecha" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 text-start">
                                            @php
                                                $peso = "0";
                                                if ($pesoTotal){
                                                    $peso = $me->formatoDecimal($pesoTotal);
                                                }
                                            @endphp
                                            @php
                                                $volumen = "0";
                                                if ($volumenTotal){
                                                    $volumen = $me->formatoDecimal($volumenTotal);
                                                }
                                            @endphp
                                            <small class="textTotalComprobantesSeleccionados me-2">
                                                Kg: <span>{{ $peso }}</span>
                                            </small>
                                            <small class="textTotalComprobantesSeleccionados">
                                                Cm³: <span>{{ $volumen }}</span>
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                    <div class="mb-3">
                                        <h6 class="mb-0">Guías Seleccionadas</h6>
                                    </div>
                                    @if(count($selectedFacturas) > 0)
                                        <x-table-general id="ederTable">
                                            <x-slot name="thead">
                                                <tr>
                                                    <th>Fecha emisión guía</th>
                                                    <th>Guía</th>
                                                    <th>Factura</th>
                                                    <th>Nombre del cliente</th>
                                                    <th>Peso y Volumen</th>
                                                    <th>Dirección</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </x-slot>
                                            <x-slot name="tbody">
                                                @foreach($selectedFacturas as $factura)
                                                    <tr style="font-size: 13px">
                                                        <td>
                                                        <span class="d-block">
                                                            {{ $me->obtenerNombreFecha($factura['guia_fecha_emision'], 'DateTime', 'Date') }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block">
                                                            {{ $factura['guia_nro_doc'] }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block">
                                                            {{ $factura['guia_nro_doc_ref'] }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block">
                                                            {{ $factura['guia_nombre_cliente'] }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block">
                                                            <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura['peso_total']) }} kg</b>
                                                        </span>
                                                            <span class="d-block">
                                                            <b class="colorBlackComprobantes">{{ $me->formatoDecimal($factura['volumen_total']) }} cm³</b>
                                                        </span>
                                                        </td>
                                                        <td>
                                                        <span class="d-block">
                                                            {{ $factura['guia_direc_entrega'] }}
                                                        </span>
                                                            <br>
                                                            <span class="d-block" style="color: black;font-weight: bold">
                                                            {{ $factura['guia_departamento'] }} - {{ $factura['guia_provincia'] }} - {{ $factura['guia_destrito'] }}
                                                        </span>
                                                        </td>
                                                        <td>
                                                            <a href="#" wire:click.prevent="eliminarFacturaSeleccionada('{{ $factura['id_guia'] }}')" class="btn btn-danger btn-sm text-white">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </x-slot>
                                        </x-table-general>
                                    @else
                                        <p>No hay guías seleccionadas.</p>
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
