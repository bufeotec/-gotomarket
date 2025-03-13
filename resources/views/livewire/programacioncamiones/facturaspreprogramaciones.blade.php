<div>
    @php
        $me = new \App\Models\General();
    @endphp
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleFactura</x-slot>
        <x-slot name="titleModal">Detalles del Documento Seleccionado</x-slot>
        <x-slot name="modalContent">
            <div class="modal-body">
                <h6>Detalles del Documento</h6>
                <hr>
                @if($detalleFactura)
                    <div>
                        <p><strong>Número de Documento:</strong> {{ $detalleFactura['nro_doc'] }}</p>
                        <p><strong>Almacén de Origen:</strong> {{ $detalleFactura['almacen_origen'] }}</p>
                        <p><strong>Tipo de Documento:</strong> {{ $detalleFactura['tipo_doc'] }}</p>
                        <p><strong>Fecha de Emisión:</strong> {{ \Carbon\Carbon::parse($detalleFactura['fecha_emision'])->format('d/m/Y') }}</p>
                        <p><strong>Tipo de Movimiento:</strong> {{ $detalleFactura['tipo_movimiento'] }}</p>
                        <p><strong>Tipo de Documento Referencial:</strong> {{ $detalleFactura['tipo_doc_ref'] }}</p>
                        <p><strong>Número de Documento Referencial:</strong> {{ $detalleFactura['nro_doc_ref'] }}</p>
                        <p><strong>Glosa:</strong> {{ $detalleFactura['glosa'] }}</p>
                        <p><strong>Estado:</strong> {{ $detalleFactura['estado'] }}</p>
                        <p><strong>Importe Total:</strong> {{ number_format($detalleFactura['importe_total'], 2) }}</p>
                        <p><strong>Descuento Total (sin IGV):</strong> {{ number_format($detalleFactura['descuento_total_sin_igv'], 2) }}</p>
                        <p><strong>IGV Total:</strong> {{ number_format($detalleFactura['igv_total'], 2) }}</p>
                        <p><strong>Peso Total:</strong> {{ $detalleFactura['peso_total'] }} kg</p>
                        <p><strong>Volumen:</strong> {{ $detalleFactura['volumen'] }} cm³</p>
                        <p><strong>Tipo de Cambio:</strong> {{ $detalleFactura['tipo_cambio'] }}</p>
                        <p><strong>Moneda:</strong> {{ $detalleFactura['moneda'] }}</p>
                        <p><strong>Dirección de Entrega:</strong> {{ $detalleFactura['direccion_entrega'] }}</p>
                        <p><strong>Departamento:</strong> {{ $detalleFactura['departamento'] }}</p>
                        <p><strong>Provincia:</strong> {{ $detalleFactura['provincia'] }}</p>
                        <p><strong>Distrito:</strong> {{ $detalleFactura['distrito'] }}</p>
                    </div>
                @else
                    <p>No hay detalles disponibles para mostrar.</p>
                @endif
            </div>
        </x-slot>
    </x-modal-general>
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
                            <h6>DOCUMENTOS</h6>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control" min="2025-01-01">
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control" min="2025-01-01">
                            </div>
                            <div class="col-lg-9 col-md-9 col-sm-12 mb-2">
                                <div class="position-relative">
                                    <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder" placeholder="Buscar documento" wire:model="searchGuia" style="border: none; outline: none;" />
                                    <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                                <button class="btn btn-sm bg-primary text-white w-100" wire:click="buscar_comprobantes">
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
                                                <th style="font-size: 12px">N° Documento</th>
                                                <th style="font-size: 12px">Nombre del Cliente</th>
                                                <th style="font-size: 12px">Peso y Volumen</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if(!empty($filteredGuias))
                                                @php
                                                    $documentosMostrados = [];
                                                @endphp
                                                @foreach($filteredGuias as $guia)
                                                    @php
                                                        $NUMERO = isset($guia->NRO_DOC) ? $guia->NRO_DOC : null;
                                                        $comprobanteExiste = collect($this->selectedGuias)->first(function ($facturaVa) use ($NUMERO) {
                                                            return isset($facturaVa['nro_doc']) && $facturaVa['nro_doc'] === $NUMERO;
                                                        });
                                                    @endphp
                                                    @if($NUMERO && !$comprobanteExiste && !in_array($NUMERO, $documentosMostrados))
                                                        @php
                                                            $documentosMostrados[] = $NUMERO;
                                                        @endphp
                                                        <tr style="cursor: pointer" wire:click="seleccionarGuia('{{ $NUMERO }}')">
                                                            <td colspan="3" style="padding: 0px">
                                                                <table class="table">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="width: 32%">
                                                                <span class="tamanhoTablaComprobantes">
                                                                    <b class="colorBlackComprobantes">
                                                                        {{ isset($guia->{'FECHA_EMISION'}) ? date('d/m/Y', strtotime($guia->{'FECHA_EMISION'})) : 'Sin fecha' }}
                                                                    </b>
                                                                </span>
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                    GUÍA: {{ $NUMERO }}
                                                                </span>
                                                                            @isset($guia->TIPO_DOC_REF)
                                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                        {{ $guia->TIPO_DOC_REF . ': ' . $guia->NRO_DOC_REF}}
                                                                    </span>
                                                                            @endisset
                                                                        </td>
                                                                        <td style="width: 37%">
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    {{ ($guia->{'NOMBRE_CLIENTE'}) ?? 'Desconocido' }}
                                                                </span>
                                                                        </td>
                                                                        <td>
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    <b class="colorBlackComprobantes">{{ number_format($guia->PESO_GRAMOS ?? 0, 2) }} kg</b>
                                                                </span>
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                    <b class="colorBlackComprobantes">{{ number_format($guia->VOLUMEN_TOTAL_CM3 ?? 0, 2) }} cm³</b>
                                                                </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="border-top: 2px solid transparent;">
                                                                        <td colspan="3" style="padding-top: 0">
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    {{ ($guia->{'DIREC_ENTREGA'}) ?? 'Sin dirección' }} <br>
                                                                    UBIGEO: <b class="colorBlackComprobantes">
                                                                        {{ ($guia->{'DEPARTAMENTO'}) ?? 'N/A' }} -
                                                                        {{ ($guia->{'PROVINCIA'} )?? 'N/A' }} -
                                                                        {{ ($guia->{'DISTRITO'}) ?? 'N/A' }}
                                                                    </b>
                                                                </span>
                                                                        </td>
                                                                    </tr>
                                                                    @if(isset($filtereddetGuias[$NUMERO])) <!-- Muestra detalles si existen -->
                                                                    <tr>
                                                                        <td colspan="3">
                                                                            <table class="table table-bordered mt-2">
                                                                                <thead>
                                                                                <tr>
                                                                                    <th>Detalle</th>
                                                                                    <th>Cantidad</th>
                                                                                    <th>Precio</th>
                                                                                </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                @foreach($filtereddetGuias[$NUMERO] as $detalle)
                                                                                    <tr>
                                                                                        <td>{{ $detalle->descripcion ?? 'Sin descripción' }}</td>
                                                                                        <td>{{ $detalle->cantidad ?? '0' }}</td>
                                                                                        <td>{{ number_format($detalle->precio ?? 0, 2) }}</td>
                                                                                    </tr>
                                                                                @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    @endif
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3" class="text-center">
                                                        <p class="mb-0" style="font-size: 12px">No se encontraron documentos.</p>
                                                    </td>
                                                </tr>
                                            @endif
                                        </x-slot>
                                    </x-table-general>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div wire:loading wire:target="seleccionarGuia" class="overlay__eliminar">
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
                                    <div class="col-lg-12">
                                        <div class="row align-items-center">
                                            <div class="col-lg-5 col-md-12 col-sm-12 mb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">Documentos Seleccionados</h6>
                                                </div>
                                            </div>
                                            @if(count($selectedGuias) > 0)
                                                <div class="col-lg-5 col-md-12 col-sm-12 mb-2">
                                                    <h6 class="mb-1">Estado de la guía</h6>
                                                    <select class="form-select" id="estado_envio" name="estado_envio" wire:model="estado_envio">
                                                        <option value="">Seleccionar...</option>
                                                        <option value="1">Créditos</option>
                                                        <option value="2">Despacho</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button href="#" class="btn bg-info text-white d-flex align-items-center" wire:click.prevent="guardarGuias">
                                                        Enviar
                                                        <i class="fa-solid fa-right-to-bracket ms-1"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        @if(count($selectedGuias) > 0)
                                            <x-table-general id="ederTable">
                                                <x-slot name="thead">
                                                    <tr>
                                                        <th>N° Documento</th>
                                                        <th>Fecha Emisión</th>
                                                        <th>Importe Total</th>
                                                        <th>Nombre Cliente</th>
                                                        <th>Dirección</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </x-slot>
                                                <x-slot name="tbody">
                                                    @foreach($selectedGuias as $factura)
                                                        <tr>
                                                            <td>{{ $factura['nro_doc'] ?? 'No disponible' }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($factura['fecha_emision'])->format('d/m/Y') ?? 'Sin fecha' }}</td>
                                                            <td>{{ number_format($factura['importe_total'], 2) ?? '0.00' }}</td>
                                                            <td>{{ $factura['nombre_cliente'] ?? 'Desconocido' }}</td>
                                                            <td>{{ $factura['direccion_entrega'] ?? 'Sin dirección'}}</td>
                                                            <td>
                                                                <a href="#" wire:click.prevent="eliminarFacturaSeleccionada('{{ $factura['nro_doc'] }}')" class="btn btn-danger btn-sm text-white m-1">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                                <a href="#" wire:click.prevent="listar_detallesf('{{ $factura['nro_doc'] }}')" class="btn btn-sm btn-primary text-white m-1" data-bs-toggle="modal" data-bs-target="#modalDetalleFactura">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </x-slot>
                                            </x-table-general>
                                        @else
                                            <p>No hay documentos seleccionados.</p>
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
