<div>
    @php
        $general = new \App\Models\General();
    @endphp
{{--    MODAL NOTA DE CREDITO DETALLES--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleNotaCredito</x-slot>
        <x-slot name="titleModal">Detalles de la nota de crédito</x-slot>
        <x-slot name="modalContent">
            @if($nota_credito_detalle)
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <h6>Información del detalle</h6>
                            <hr>
                            <div class="table-responsive">
                                <x-table-general>
                                    <x-slot name="thead">
                                        <tr>
                                            <th>N°</th>
                                            <th>Almacén de Entrada</th>
                                            <th>Fecha Emisión</th>
                                            <th>Estado</th>
                                            <th>Tipo Documento</th>
                                            <th>Nro. Documento</th>
                                            <th>Nro. Línea</th>
                                            <th>Cód. Producto</th>
                                            <th>Descripción Producto</th>
                                            <th>Lote</th>
                                            <th>Unidad</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unitario</th>
                                            <th>Texto</th>
                                            <th>IGV Total</th>
                                            <th>Importe Total</th>
                                            <th>Moneda</th>
                                            <th>Tipo Cambio</th>
                                            <th>Peso (g)</th>
                                            <th>Volumen (cm³)</th>
                                            <th>Peso Total (g)</th>
                                            <th>Volumen Total (cm³)</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @php $a = 1; @endphp
                                        @foreach($nota_credito_detalle as $detalle)
                                            <tr>
                                                <td>{{$a}}</td>
                                                <td>{{ $detalle->not_cred_det_almacen_entrada }}</td>
                                                <td>{{ $general->obtenerNombreFecha($detalle->not_cred_det_fecha_emision,'DateTime', 'Date')}}</td>
                                                <td>{{ $detalle->not_cred_det_estado }}</td>
                                                <td>{{ $detalle->not_cred_det_tipo_doc }}</td>
                                                <td>{{ $detalle->not_cred_det_nro_doc }}</td>
                                                <td>{{ $detalle->not_cred_det_nro_linea }}</td>
                                                <td>{{ $detalle->not_cred_det_cod_producto }}</td>
                                                <td>{{ $detalle->not_cred_det_descripcion_procd ?? '-'}}</td>
                                                <td>{{ $detalle->not_cred_det_lote ?? '-' }}</td>
                                                <td>{{ $detalle->not_cred_det_unidad }}</td>
                                                <td>{{ $detalle->not_cred_det_cantidad }}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_precio_unit_final_inc_igv)}}</td>
                                                <td>{{ $detalle->not_cred_det_texto ?? '-'}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_igv_total)}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_importe_total_inc_igv)}}</td>
                                                <td>{{ $detalle->not_cred_det_moneda }}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_tipo_cambio)}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_peso_gramos)}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_volumen)}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_peso_toal_gramos)}}</td>
                                                <td>{{ $general->formatoDecimal($detalle->not_cred_det_volumen_total)}}</td>
                                            </tr>
                                            @php $a++; @endphp
                                        @endforeach
                                    </x-slot>
                                </x-table-general>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </x-slot>
    </x-modal-general>
{{--    MODAL FIN NOTA DE CREDITO DETALLES--}}

{{--    MODAL DETALLE FACTURA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleFactura</x-slot>
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
                                    <td>{{ $detalle->guia_det_almacen_salida ?? '-' }}</td>
                                    <td>{{ $detalle->guia_det_fecha_emision ? $general->obtenerNombreFecha($detalle->guia_det_fecha_emision, 'DateTime', 'Date') : '-' }}</td>
                                    <td>{{ $detalle->guia_det_estado ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_tipo_documento ?? '-' }}</td>
                                    <td>{{ $detalle->guia_det_nro_documento ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_nro_linea ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_cod_producto ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_descripcion_producto ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_lote ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_unidad ?? '-'}}</td>
                                    <td>{{ $detalle->guia_det_cantidad ?? '-'}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_precio_unit_final_inc_igv ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_precio_unit_antes_descuente_inc_igv ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_descuento_total_sin_igv ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_igv_total ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_importe_total_inc_igv ?? 0) }}</td>
                                    <td>{{ $detalle->guia_det_moneda ?? '-'}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_tipo_cambio ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_peso_gramo ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_volumen ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_peso_total_gramo ?? 0)}}</td>
                                    <td>{{ $general->formatoDecimal($detalle->guia_det_volumen_total ?? 0)}}</td>
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
{{--    MODAL FIN DETALLE FACTURA--}}

    {{--    MODAL REGISTRO NOTA CREDITO--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalNotaCredito</x-slot>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="titleModal">Gestionar Nota de Credito</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="save_nota_credito">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información de la nota de credito</small>
                        <hr class="mb-0">
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error-guia'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error-guia') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>

                    <!-- Campo para mostrar el select o el h6 -->
                    <div class="col-lg-5 col-md-5 col-sm-12 mb-3">
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control" min="2025-01-01">
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control" min="2025-01-01">
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mt-1">
                                <a class="btn btn-sm bg-primary text-white w-100" type="button" wire:click="buscar_comprobantes" >
                                    <i class="fa fa-search"></i> BUSCAR
                                </a>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <div class="loader mt-2" wire:loading wire:target="buscar_comprobantes"></div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <div class="contenedor-comprobante" style="max-height: 360px; overflow: auto">
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th style="font-size: 11px">N° Documento NC</th>
                                                <th style="font-size: 12px">Tipo de Movimiento</th>
                                                <th style="font-size: 12px">Importe total</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if(!empty($filteredGuias))
                                                @foreach($filteredGuias as $factura)
                                                    @php
                                                        $NRO_DOCUMENTO = $factura->NRO_DOCUMENTO;
                                                        $comprobanteExiste = collect($this->selectedGuias)->first(function ($facturaVa) use ($NRO_DOCUMENTO) {
                                                            return $facturaVa['NRO_DOCUMENTO'] === $NRO_DOCUMENTO;
                                                        });
                                                    @endphp
                                                    @if(!$comprobanteExiste)
                                                        <tr style="cursor: pointer" wire:click="seleccionar_nota_credito('{{ $factura->NRO_DOCUMENTO }}')">
                                                            <td colspan="3" style="padding: 0px">
                                                                <table class="table">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="width: 36.6%">
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                N° Documento NC: {{ $factura->NRO_DOCUMENTO }}
                                                                            </span>
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                                N° Factura: {{ $factura->NRO_DOCUMENTO_REF }}
                                                                            </span>
                                                                        </td>
                                                                        <td style="width: 37.2%">
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->TIPO_MOVIMIENTO }}
                                                                        </span>
                                                                        </td>
                                                                        <td>
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $general->formatoDecimal($factura->IMPORTE_TOTAL) }}
                                                                        </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="border-top: 2px solid transparent;">
                                                                        <td colspan="3" style="padding-top: 0">
                                                                        <span class="d-block tamanhoTablaComprobantes">
                                                                            RUC: <b class="colorBlackComprobantes">{{ $factura->CODIGO_CLIENTE }}</b> <br> CLIENTE: <b class="colorBlackComprobantes">{{ $factura->NOMBRE_CLIENTE }}</b>
                                                                        </span>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3" class="text-center">
                                                        <p class="mb-0" style="font-size: 12px">No se encontraron resultados.</p>
                                                    </td>
                                                </tr>
                                            @endif
                                        </x-slot>
                                    </x-table-general>
                                </div>
                            </div>
                        </div>
                        <div wire:loading wire:target="seleccionar_nota_credito" class="overlay__eliminar">
                            <div class="spinner__container__eliminar">
                                <div class="spinner__eliminar"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <label class="mb-2">Notas Seleccionadas</label>
                                @if (!empty($selectedGuias))
                                    <div class="contenedor-comprobante" style="max-height: 360px; overflow: auto">
                                        <x-table-general>
                                            <x-slot name="thead">
                                                <tr>
                                                    <th style="font-size: 12px">N° Documento NC</th>
                                                    <th style="font-size: 12px">Acciones</th>
                                                </tr>
                                            </x-slot>

                                            <x-slot name="tbody">
                                                @foreach($selectedGuias as $factura)
                                                    <tr>
                                                        <td style="font-size: 15px">{{ $factura['NRO_DOCUMENTO'] }}</td>
                                                        <td>
                                                            <a href="#" class="btn text-danger btn-sm mx-3" wire:click.prevent="eliminar_nota_credito_seleccionada('{{ $factura['NRO_DOCUMENTO'] }}')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                            {{-- Mostrar check o warning con tooltip --}}
                                                            @if ($factura['existe_en_guias'])
                                                                <i class="fa-solid fa-check text-success cursoPointer" title="Factura registrada en el intranet"></i>
                                                            @else
                                                                <i class="fas fa-exclamation-triangle text-warning cursoPointer" title="Factura no registrada en intranet"></i>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </x-slot>
                                        </x-table-general>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">Debe seleccionadar una nota de credito.</p>
                                @endif
                            </div>
                        </div>
                        <div wire:loading wire:target="eliminar_nota_credito_seleccionada" class="overlay__eliminar">
                            <div class="spinner__container__eliminar">
                                <div class="spinner__eliminar"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="not_cred_motivo" class="form-label">Código de motivo</label>
                                <select class="form-select" name="not_cred_motivo" id="not_cred_motivo" wire:model="not_cred_motivo">
                                    <option value="">Seleccionar...</option>
                                    <option value="1">1 - Devolución</option>
                                    <option value="2">2 - Calidad</option>
                                    <option value="3">3 - Cobranza</option>
                                    <option value="4">4 - Error de facturación</option>
                                    <option value="5">5 - Otros comercial</option>
                                </select>
                                @error('not_cred_motivo')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="not_cred_motivo_descripcion" class="form-label">Motivo descripción</label>
                                <textarea class="form-control" id="not_cred_motivo_descripcion" name="not_cred_motivo_descripcion" wire:model="not_cred_motivo_descripcion" rows="4"></textarea>
                                @error('not_cred_motivo_descripcion')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Guardar Registro</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
    {{--    FIN MODAL REGISTRO NOTA CREDITO--}}

{{--    MODAL CAMBIAR ESTADO APROBACION--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalCodigoMotivo</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiar_estado_codigo_motivo">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">¿Desea Registrar las Notas de Crédito?</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_nota_credito') <span class="message-error">{{ $message }}</span> @enderror

                        @if (session()->has('error_codigo_nc'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_codigo_nc') }}
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
{{--    MODAL FIN CAMBIAR ESTADO APROBACION--}}

{{--    MODAL ANULAR GUIA--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalAnularGuia</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiar_guia_anuladas">
                <input type="text" class="d-none" autofocus>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">¿Estas seguro de anular estas guías ?</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error_modal_anular_guia'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_modal_anular_guia') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-danger btnDelete">No</button>
                        <button type="submit" class="btn btn-primary text-white btnDelete">SI</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
{{--    FIN MODAL ANULAR GUIA--}}

{{--    <div class="row">--}}
{{--        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">--}}
{{--            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_nota_credito" placeholder="Buscar">--}}
{{--            <x-select-filter wire:model.live="pagination_nota_credito" />--}}
{{--        </div>--}}
{{--        <div class="col-lg-6 col-md-6 col-sm-12 text-end">--}}
{{--            <x-btn-export wire:click="clear_form_nota_credito" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalNotaCredito" >--}}
{{--                <x-slot name="icons">--}}
{{--                    fa-solid fa-plus--}}
{{--                </x-slot>--}}
{{--                Agregar Nota credito--}}
{{--            </x-btn-export>--}}
{{--        </div>--}}
{{--    </div>--}}

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                            <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control" min="2025-01-01">
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                            <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control" min="2025-01-01">
                        </div>
                        <div class="col-lg-9 col-md-9 col-sm-12 mb-2">
                            <div class="position-relative">
                                <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder" placeholder="Buscar guía" wire:model="searchGuia" style="border: none; outline: none;" />
                                <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                            <button class="btn btn-sm bg-primary text-white w-100" wire:click="buscar_comprobantes" >
                                <i class="fa fa-search"></i> BUSCAR
                            </button>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <div class="loader mt-2" wire:loading wire:target="buscar_comprobantes"></div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <div class="row" style="align-items: center">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    @if(!empty($filteredGuias) && count($filteredGuias) > 0)
                                        <input type="checkbox" id="selectAll" wire:click="seleccionar_todas_nc_intranet" class="form-check-input"/>
                                        <label class="form-label ms-2" for="selectAll">Seleccionar todo</label>
                                    @endif
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12 text-end">
                                    @if(!empty($seleccionarNCS) && count($seleccionarNCS) > 0)
                                        <button class="btn btn-info btn-group-sm" wire:click="save_nota_credito">
                                            INICIAR REGISTRO
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <div class="contenedor-comprobante" style="max-height: 360px; overflow: auto">
                                <x-table-general>
                                    <x-slot name="thead">
                                        <tr>
                                            <th style="font-size: 12px">Check box</th>
                                            <th style="font-size: 11px">N° Documento NC</th>
                                            <th style="font-size: 12px">Nombre del cliente</th>
                                        </tr>
                                    </x-slot>

                                    <x-slot name="tbody">
                                        @if(!empty($filteredGuias))
                                            @foreach($filteredGuias as $factura)
                                                @php
                                                    $NRO_DOCUMENTO = $factura->NRO_DOCUMENTO;
                                                    $comprobanteExiste = collect($this->selectedGuias)->first(function ($facturaVa) use ($NRO_DOCUMENTO) {
                                                        return $facturaVa['NRO_DOCUMENTO'] === $NRO_DOCUMENTO;
                                                    });
                                                @endphp
                                                @if(!$comprobanteExiste)
                                                    <tr>
                                                        <td style="width: 6%">
                                                            <input type="checkbox"
                                                                   wire:click="seleccionar_una_nc_intranet"
                                                                   wire:model="seleccionarNCS"
                                                                   value="{{$NRO_DOCUMENTO}}"
                                                                   class="form-check-input" />
                                                        </td>
                                                        <td style="width: 32%">
                                                            <span class="tamanhoTablaComprobantes">
                                                                <b class="colorBlackComprobantes">
                                                                    {{ isset($factura->FECHA_EMISION) ? $general->obtenerNombreFecha($factura->FECHA_EMISION,'DateTime', 'Date') : 'Sin fecha' }}
                                                                </b>
                                                            </span>
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                N° NC: {{ $factura->NRO_DOCUMENTO }}
                                                            </span>
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                N° Factura: {{ $factura->NRO_DOCUMENTO_REF }}
                                                            </span>
                                                        </td>
                                                        <td style="width: 37%">
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                <b class="colorBlackComprobantes">
                                                                    {{ $factura->NOMBRE_CLIENTE }}
                                                                </b>
                                                            </span>
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                S/ {{ number_format($factura->IMPORTE_TOTAL, 2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center">
                                                    <p class="mb-0" style="font-size: 12px">No se encontraron resultados.</p>
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
            <div wire:loading wire:target="save_nota_credito" class="overlay__eliminar">
                <div class="spinner__container__eliminar">
                    <div class="spinner__eliminar"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                    <div class="row align-items-center">
                                        @if(count($listar_nota_credito) > 0)
                                            <div class="col-lg-12">
                                                <div class="row mb-2">
                                                    <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                                        <h6>Código motivo</h6>
                                                        <select class="form-select" name="not_cred_motivo" id="not_cred_motivo" wire:model="not_cred_motivo">
                                                            <option value="">Seleccionar...</option>
                                                            <option value="1">1 - Devolución</option>
                                                            <option value="2">2 - Calidad</option>
                                                            <option value="3">3 - Cobranza</option>
                                                            <option value="4">4 - Anulación de la Operación</option>
                                                            <option value="5">5 - Otros comercial</option>
                                                        </select>
                                                        @error('not_cred_motivo')<span class="message-error">{{ $message }}</span>@enderror
                                                    </div>

                                                    <div class="col-lg-6 col-md-5 col-sm-12 mb-3">
                                                        <h6>Motivo descripción</h6>
                                                        <textarea class="form-control" rows="3" id="not_cred_motivo_descripcion" name="not_cred_motivo_descripcion" wire:model="not_cred_motivo_descripcion"></textarea>
                                                        @error('not_cred_motivo_descripcion')<span class="message-error">{{ $message }}</span>@enderror
                                                    </div>

                                                    <div class="col-lg-2 col-md-2 col-sm-12 mb-2 w-auto align-content-center">
                                                        <a href="#" class="btn bg-info text-white d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCodigoMotivo">
                                                            <span>
                                                                Registrar<i class="fa-solid fa-right-to-bracket ms-1"></i>
                                                            </span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                    @if(count($listar_nota_credito) > 0)
                                        <x-table-general id="ederTable">
                                            <x-slot name="thead">
                                                <tr>
                                                    <th><input type="checkbox" id="select_varios" wire:click="seleccionar_varias_nc_codigo" class="form-check-input" /> Check - todo</th>
                                                    <th>N° de Nota de Crédito</th>
                                                    <th>Fecha Emisión</th>
                                                    <th>Nombre del cliente</th>
                                                    <th>Importe Total (Sin IGV)</th>
                                                    <th>Motivo Contable</th>
                                                    <th>Documentos Vinculados</th>
                                                    <th>Estado NC en Sistema Facturación</th>
                                                </tr>
                                            </x-slot>
                                            <x-slot name="tbody">
                                                @foreach($listar_nota_credito as $lnc)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox"
                                                                   wire:model="select_todas_nc"
                                                                   value="{{ $lnc->id_not_cred }}"
                                                                   class="form-check-input" />
                                                        </td>
                                                        <td>{{$lnc->not_cred_nro_doc}}</td>
                                                        <td>{{ $lnc->not_cred_fecha_emision ? $general->obtenerNombreFecha($lnc->not_cred_fecha_emision, 'DateTime', 'Date') : '-' }}</td>
                                                        <td>{{$lnc->not_cred_nombre_cliente}}</td>
                                                        <td>S/ {{number_format($lnc->not_cred_importe_total / 1.18, 2)}}</td>
                                                        <td>{{$lnc->not_cred_tipo_movimiento}}</td>
                                                        <td>{{$lnc->not_cred_nro_doc_ref}}</td>
                                                        <td>{{ $lnc->not_cred_estado_aprobacion == 1 ? 'Registrado en el sistema' : $lnc->not_cred_estado_aprobacion }}</td>
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
                    <div wire:loading wire:target="cambiar_estado_codigo_motivo" class="overlay__eliminar">
                        <div class="spinner__container__eliminar">
                            <div class="spinner__eliminar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <div class="row" style="align-items: center">
                                <div class="col-lg-6"></div>
                                <div class="col-lg-6 col-md-6 col-sm-12 text-end">
                                    <a class="btn btn-info btn-group-sm" data-bs-toggle="modal" data-bs-target="#modalAnularGuia">
                                        Anular Sin NC
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <div class="contenedor-comprobante" style="max-height: 360px; overflow: auto">
                                @if(count($listar_guias_pendientes_nc) > 0)
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th style="font-size: 15px"><input type="checkbox" id="select_varias_guias" wire:click="seleccionar_varias_guias_anuladas" class="form-check-input" /> Check box</th>
                                                <th style="font-size: 15px">N° Documento</th>
                                                <th style="font-size: 15px">Nombre del cliente</th>
                                                <th style="font-size: 15px">Acciones</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @foreach($listar_guias_pendientes_nc as $lgpnc)
                                                <tr>
                                                    <td style="width: 6%">
                                                        <input type="checkbox"
                                                               wire:model="select_todas_guias"
                                                               value="{{ $lgpnc->id_guia }}"
                                                               class="form-check-input" />
                                                    </td>
                                                    <td style="width: 32%">
                                                        <span class="tamanhoTablaComprobantes">
                                                            <b class="colorBlackComprobantes">
                                                                {{ isset($lgpnc->guia_fecha_emision) ? $general->obtenerNombreFecha($lgpnc->guia_fecha_emision,'DateTime', 'Date') : 'Sin fecha' }}
                                                            </b>
                                                        </span>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            N° NC: {{ $lgpnc->guia_nro_doc }}
                                                        </span>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            N° Factura: {{ $lgpnc->guia_nro_doc_ref }}
                                                        </span>
                                                    </td>
                                                    <td style="width: 37%">
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            <b class="colorBlackComprobantes">
                                                                {{ $lgpnc->guia_nombre_cliente }}
                                                            </b>
                                                        </span>
                                                        <span class="d-block tamanhoTablaComprobantes">
                                                            ESTADO STARSOFT: <b class="colorBlackComprobantes">{{ $lgpnc->guia_estado }}</b>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a data-bs-toggle="modal" data-bs-target="#modalActualizarDetalle" style="cursor:pointer;" class="btn btn-sm btn-warning text-white">
                                                            <i class="fa fa-refresh"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </x-slot>
                                    </x-table-general>
                                @else
                                    <h6 class="mb-0">No se encontraron resultados.</h6>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div wire:loading wire:target="save_nota_credito" class="overlay__eliminar">
                <div class="spinner__container__eliminar">
                    <div class="spinner__eliminar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalCodigoMotivo');
        modal.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                modal.querySelector('form').requestSubmit();
            }
        });
    });
</script>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalNotaCredito').modal('hide');
    });
    $wire.on('hideModalDelete', () => {
        $('#modalCambioEstado').modal('hide');
    });
    $wire.on('hideModalCodigo', () => {
        $('#modalCodigoMotivo').modal('hide');
    });
    $wire.on('hideModalAnularGuia', () => {
        $('#modalAnularGuia').modal('hide');
    });
</script>
@endscript
