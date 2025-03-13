<div>
    @php
        $me = new \App\Models\General();
    @endphp
{{--    MODAL INFORMACION GUIA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalInformacionGuia</x-slot>
        <x-slot name="titleModal">Información de la guia Seleccionada</x-slot>
        <x-slot name="modalContent">
            @if($guiaSeleccionada)
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h6>Información general</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Numero documento:</strong>
                                    <p>{{ $guiaSeleccionada['NRO_DOC'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Almacen de Origen:</strong>
                                    <p>{{ $guiaSeleccionada['ALMACEN_ORIGEN'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo Documento:</strong>
                                    <p>{{ $guiaSeleccionada['TIPO_DOC'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Fecha de Emision:</strong>
                                    <p>{{ $guiaSeleccionada['FECHA_EMISION'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Movimiento:</strong>
                                    <p>{{ $guiaSeleccionada['TIPO_MOVIMIENTO'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Documento Referencial:</strong>
                                    <p>{{ $guiaSeleccionada['TIPO_DOC_REF'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Número de Documento Referencial:</strong>
                                    <p>{{ $guiaSeleccionada['NRO_DOC_REF'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Glosa:</strong>
                                    <p>{{ $guiaSeleccionada['GLOSA'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Estado:</strong>
                                    <p>{{ $guiaSeleccionada['ESTADO'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Importe Total:</strong>
                                    <p>{{ $guiaSeleccionada['IMPORTE_TOTAL'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Cambio:</strong>
                                    <p>{{ $guiaSeleccionada['TIPO_DE_CAMBIO'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Moneda:</strong>
                                    <p>{{ $guiaSeleccionada['MONEDA'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Dirección de Entrega:</strong>
                                    <p>{{ $guiaSeleccionada['DIREC_ENTREGA'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Departamento:</strong>
                                    <p>{{ $guiaSeleccionada['DEPARTAMENTO'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Provincia:</strong>
                                    <p>{{ $guiaSeleccionada['PROVINCIA'] }}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Distrito:</strong>
                                    <p>{{ $guiaSeleccionada['DISTRITO'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @else
                <p>No hay información disponibles para mostrar.</p>
            @endif
        </x-slot>
    </x-modal-general>
{{--    MODAL FIN INFORMACION GUIA--}}

{{--    MODAL DETALLE GUIA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleGuia</x-slot>
        <x-slot name="titleModal">Detalles de la guía Seleccionada</x-slot>
        <x-slot name="modalContent">
            <div class="modal-body">
                <h6>Detalles de la Guía</h6>
                <hr>
                @if(!empty($detallesGuia))
                    <x-table-general>
                        <x-slot name="thead">
                        <tr>
                            <th>Almacén Salida</th>
                            <th>Fecha Emisión</th>
                            <th>Estado</th>
                            <th>Tipo Documento</th>
                            <th>Nro Documento</th>
                            <th>Nro Línea</th>
                            <th>Cód Producto</th>
                            <th>Descripción Producto</th>
                            <th>Lote</th>
                            <th>Unidad</th>
                            <th>Cantidad</th>
                            <th>Precio Unit Final Inc IGV</th>
                            <th>Precio Unit Antes Descuento Inc IGV</th>
                            <th>Descuento Total Sin IGV</th>
                            <th>IGV Total</th>
                            <th>Importe Total Inc IGV</th>
                            <th>Moneda</th>
                            <th>Tipo Cambio</th>
                            <th>Peso Gramos</th>
                            <th>Volumen CM3</th>
                            <th>Peso Total Gramos</th>
                            <th>Volumen Total CM3</th>
                        </tr>
                        </x-slot>
                        <x-slot name="tbody">
                            @foreach($detallesGuia as $detalle)
                            <tr>
                                <td>{{ $detalle->ALMACEN_SALIDA ?? '-' }}</td>
                                <td>{{ $detalle->FECHA_EMISION ? $me->obtenerNombreFecha($detalle->FECHA_EMISION, 'DateTime', 'DateTime') : '-' }}</td>
                                <td>{{ $detalle->ESTADO ?? '-'}}</td>
                                <td>{{ $detalle->TIPO_DOCUMENTO ?? '-' }}</td>
                                <td>{{ $detalle->NRO_DOCUMENTO ?? '-'}}</td>
                                <td>{{ $detalle->NRO_LINEA ?? '-'}}</td>
                                <td>{{ $detalle->COD_PRODUCTO ?? '-'}}</td>
                                <td>{{ $detalle->DESCRIPCION_PRODUCTO ?? '-'}}</td>
                                <td>{{ $detalle->LOTE ?? '-'}}</td>
                                <td>{{ $detalle->UNIDAD ?? '-'}}</td>
                                <td>{{ $detalle->CANTIDAD ?? '-'}}</td>
                                <td>{{ $me->formatoDecimal($detalle->PRECIO_UNIT_FINAL_INC_IGV ?? 0)}}</td>
                                <td>{{ $me->formatoDecimal($detalle->PRECIO_UNIT_ANTES_DESCUENTO_INC_IGV ?? 0)}}</td>
                                <td>{{ $me->formatoDecimal($detalle->DESCUENTO_TOTAL_SIN_IGV ?? 0)}}</td>
                                <td>{{ $me->formatoDecimal($detalle->IGV_TOTAL ?? 0)}}</td>
                                <td>{{ $me->formatoDecimal($detalle->IMPORTE_TOTAL_INC_IGV ?? 0) }}</td>
                                <td>{{ $detalle->MONEDA ?? '-'}}</td>
                                <td>{{ $me->formatoDecimal($detalle->TIPO_CAMBIO ?? 0)}}</td>
                                <td>{{ $me->formatoDecimal($detalle->PESO_GRAMOS ?? 0)}}</td>
                                <td>{{ $me->formatoDecimal($detalle->VOLUMEN_CM3 ?? 0)}}</td>
                                <td>{{ $me->formatoDecimal($detalle->PESO_TOTAL_GRAMOS ?? 0)}}</td>
                                <td>{{ $me->formatoDecimal($detalle->VOLUMEN_TOTAL_CM3 ?? 0)}}</td>
                            </tr>
                        @endforeach
                        </x-slot>
                    </x-table-general>
                @else
                    <p>No hay detalles disponibles para mostrar.</p>
                @endif
            </div>
        </x-slot>
    </x-modal-general>
{{--    MODAL FIN DETALLE GUIA--}}

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
{{--                                                <th style="font-size: 12px">Peso y Volumen</th>--}}
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if(!empty($filteredGuias))
                                                @php
                                                    $documentosMostrados = [];
                                                @endphp
                                                @foreach($filteredGuias as $guia)
                                                    @php
                                                        $NRO_DOC = isset($guia->NRO_DOC) ? $guia->NRO_DOC : null;
                                                        $comprobanteExiste = collect($this->selectedGuias)->first(function ($facturaVa) use ($NRO_DOC) {
                                                            return isset($facturaVa['NRO_DOC']) && $facturaVa['NRO_DOC'] === $NRO_DOC;
                                                        });
                                                    @endphp
                                                    @if($NRO_DOC && !$comprobanteExiste && !in_array($NRO_DOC, $documentosMostrados))
                                                        @php
                                                            $documentosMostrados[] = $NRO_DOC;
                                                        @endphp
                                                        <tr style="cursor: pointer" wire:click="seleccionarGuia('{{ $NRO_DOC }}')">
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
                                                                                GUÍA: {{ $NRO_DOC }}
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
{{--                                                                        <td>--}}
{{--                                                                <span class="d-block tamanhoTablaComprobantes">--}}
{{--                                                                    <b class="colorBlackComprobantes">{{ number_format($guia->PESO_GRAMOS ?? 0, 2) }} kg</b>--}}
{{--                                                                </span>--}}
{{--                                                                            <span class="d-block tamanhoTablaComprobantes">--}}
{{--                                                                    <b class="colorBlackComprobantes">{{ number_format($guia->VOLUMEN_TOTAL_CM3 ?? 0, 2) }} cm³</b>--}}
{{--                                                                </span>--}}
{{--                                                                        </td>--}}
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
                                                                    @if(isset($filtereddetGuias[$NRO_DOC])) <!-- Muestra detalles si existen -->
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
                                                                                @foreach($filtereddetGuias[$NRO_DOC] as $detalle)
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
                                                <div class="col-lg-2 mt-1">
                                                    <button href="#" class="btn bg-info text-white d-flex align-items-center" wire:click.prevent="guardarGuias" wire:loading.attr="disabled">
                                                        <span wire:loading.remove>
                                                            Enviar
                                                            <i class="fa-solid fa-right-to-bracket ms-1"></i>
                                                        </span>
                                                        <span wire:loading>
                                                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                                        </span>
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
                                                            <td>{{ $factura['NRO_DOC'] ?? 'No disponible' }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($factura['FECHA_EMISION'])->format('d/m/Y') ?? 'Sin fecha' }}</td>
                                                            <td>{{ number_format($factura['IMPORTE_TOTAL'], 2) ?? '0.00' }}</td>
                                                            <td>{{ $factura['NOMBRE_CLIENTE'] ?? 'Desconocido' }}</td>
                                                            <td>{{ $factura['DIREC_ENTREGA'] ?? 'Sin dirección'}}</td>
                                                            <td>
                                                                <a href="#" wire:click.prevent="eliminarFacturaSeleccionada('{{ $factura['NRO_DOC'] }}')" class="btn btn-danger btn-sm text-white m-1">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                                <a href="#" wire:click.prevent="listar_detallesf('{{ $factura['NRO_DOC'] }}')" class="btn btn-sm btn-primary text-white m-1" data-bs-toggle="modal" data-bs-target="#modalInformacionGuia">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>

                                                                <a href="#" wire:click.prevent="detalle_guia('{{ $factura['NRO_DOC'] }}')" class="btn btn-sm btn-warning text-white m-1" data-bs-toggle="modal" data-bs-target="#modalDetalleGuia">
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
