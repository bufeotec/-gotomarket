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
                                    <p>{{ $guiaSeleccionada['FECHA_EMISION'] ? $me->obtenerNombreFecha($guiaSeleccionada['FECHA_EMISION'], 'DateTime', 'Date') : '-' }}</p>
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
                                    <p>{{ $me->formatoDecimal($guiaSeleccionada['IMPORTE_TOTAL_SIN_IGV'] ?? 0)}}</p>
                                </div>

                                <div class="col-lg-3">
                                    <strong style="color: #8c1017">Tipo de Cambio:</strong>
                                    <p>{{ $me->formatoDecimal($guiaSeleccionada['TIPO_DE_CAMBIO'] ?? 0)}}</p>
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

    <style>
        /* Estilos para el overlay y el spinner */
        .overlay__eliminar__pre {
            position: absolute;
            top: 70%;
            left: 2%;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .spinner__container__eliminar__pre {
            text-align: center;
            display: flex;
            align-items: center;
            gap: 10px; /* Espacio entre el texto y el spinner */
        }

        .loading-text {
            font-size: 16px;
            color: #333; /* Color del texto */
        }

        .spinner__eliminar_pre {
            border: 4px solid #f3f3f3; /* Color del borde */
            border-top: 4px solid #c3121a; /* Color del borde superior */
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite; /* Animación de rotación */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
{{--    MODAL DETALLE GUIA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleGuia</x-slot>
        <x-slot name="titleModal">Detalles de la guía Seleccionada</x-slot>
        <x-slot name="modalContent">
            <div class="modal-body">
                <h6>Detalles de factura</h6>
                <hr>

                <!-- Loading spinner -->
                <div wire:loading wire:target="detalle_guia" class="overlay__eliminar__pre">
                    <div class="spinner__container__eliminar__pre">
                        <span class="loading-text">Cargando...</span>
                        <div class="spinner__eliminar_pre"></div>
                    </div>
                </div>

                <!-- Contenido del modal -->
                <div wire:loading.remove wire:target="detalle_guia">
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
                                        <td>{{ $detalle->FECHA_EMISION ? $me->obtenerNombreFecha($detalle->FECHA_EMISION, 'DateTime', 'Date') : '-' }}</td>
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
            </div>
        </x-slot>
    </x-modal-general>
{{--    MODAL FIN DETALLE GUIA--}}

{{--    MODAL ENVIAR CREDITOS / DESPACHOS--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalCreditosDespachos</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="enviar_estado_guia">
                <input type="text" class="d-none" autofocus>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">¿Estas seguro de enviar a {{$messagePregunta_cd}} ?</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error_modal_credito'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_modal_credito') }}
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
{{--    FIN MODAL ENVIAR CREDITOS / DESPACHOS--}}

    {{--    MODAL ENVIAR ANULAR NC--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalAnularNC</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="enviar_anulado_nc">
                <input type="text" class="d-none" autofocus>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">¿Estas seguro de enviar a {{$messagePregunta_anular}} ?</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error_modal_credito'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_modal_credito') }}
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
    {{--    FIN MODAL ENVIAR ANULAR NC--}}

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
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <h6>GUÍAS</h6>
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
                                <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder" placeholder="Buscar guía" wire:model="searchGuia" style="border: none; outline: none;" />
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
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <div class="row" style="align-items: center">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    @if(!empty($filteredGuias) && count($filteredGuias) > 0)
                                        <input type="checkbox" id="selectAll" wire:click="seleccionar_todas_giuas_intranet" class="form-check-input"/>
                                        <label class="form-label ms-2" for="selectAll">Seleccionar todo</label>
                                    @endif
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12 text-end">
                                    @if(!empty($selectedGuiasNros) && count($selectedGuiasNros) > 0)
                                        <button class="btn btn-info btn-group-sm" wire:click="guardar_guias_intranet">
                                            INICIAR REGISTRO
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="contenedor-comprobante" style="max-height: 600px; overflow: auto">
                                <x-table-general>
                                    <x-slot name="thead">
                                        <tr>
                                            <th style="font-size: 12px"></th>
                                            <th style="font-size: 12px">N° Documento</th>
                                            <th style="font-size: 12px">Nombre del Cliente</th>
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
                                                    <tr>
                                                        <td style="width: 6%">
                                                            <input type="checkbox"
                                                                   wire:click="seleccionar_una_guia_intranet"
                                                                   wire:model="selectedGuiasNros"
                                                                   value="{{ $NRO_DOC }}"
                                                                   class="form-check-input"/>
                                                        </td>
                                                        <td style="width: 32%">
                                                            <span class="tamanhoTablaComprobantes">
                                                                <b class="colorBlackComprobantes">
                                                                    {{ isset($guia->{'FECHA_EMISION'}) ? $me->obtenerNombreFecha($guia->{'FECHA_EMISION'},'DateTime', 'Date') : 'Sin fecha' }}
                                                                </b>
                                                            </span>
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                GUÍA: {{ $NRO_DOC }}
                                                            </span>
                                                            @isset($guia->TIPO_DOC_REF)
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    {{ $guia->TIPO_DOC_REF . ': ' . $guia->NRO_DOC_REF}}
                                                                </span>
                                                            @else
                                                                <span class="d-block tamanhoTablaComprobantes">
                                                                    Sin Factura Asociada
                                                                </span>
                                                            @endisset
                                                        </td>
                                                        <td style="width: 37%">
                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                {{ ($guia->{'NOMBRE_CLIENTE'}) ?? 'Desconocido' }}
                                                            </span>
                                                            <br>
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
            <div wire:loading wire:target="guardar_guias_intranet" class="overlay__eliminar">
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
                                        <div class="col-lg-5 col-md-12 col-sm-12 mb-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Guías Seleccionados</h6>
                                            </div>
                                        </div>
                                        @if(count($listar_guias_registradas) > 0)
                                            <div class="col-lg-7">
                                                <div class="row">
                                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                                        <h6 class="mb-1">Gestionar estados de la guía</h6>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-lg-9 col-md-9 col-sm-9 mb-2">
                                                            <select class="form-select" id="estado_envio" name="estado_envio" wire:model="estado_envio">
                                                                <option value="">Seleccionar...</option>
                                                                <option value="1">Créditos</option>
                                                                <option value="2">Despacho</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-3 col-md-3 col-sm-3 mb-2 w-auto">
                                                            <a href="#" class="btn bg-info text-white d-flex align-items-center" wire:click="pregunta_modal(1)" data-bs-toggle="modal" data-bs-target="#modalCreditosDespachos">
                                                                <span>
                                                                    Enviar<i class="fa-solid fa-right-to-bracket ms-1"></i>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-2">
                                                        <div class="col-lg-9 col-md-9 col-sm-9 mb-2">
                                                            <select class="form-select" id="estado_envio_anulado" name="estado_envio_anulado" wire:model="estado_envio_anulado">
                                                                <option value="">Seleccionar...</option>
                                                                <option value="14">Anular</option>
                                                                <option value="15">Pendiente de NC</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-3 col-md-3 col-sm-3 mb-2 w-auto">
                                                            <a href="#" class="btn bg-warning text-white d-flex align-items-center" wire:click="pregunta_modal(2)" data-bs-toggle="modal" data-bs-target="#modalAnularNC">
                                                                <span>
                                                                    Anular <i class="fa-solid fa-right-to-bracket ms-1"></i>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                    @if(count($listar_guias_registradas) > 0)
                                        <x-table-general id="ederTable">
                                            <x-slot name="thead">
                                                <tr>
                                                    <th><input type="checkbox" id="select_varios" wire:click="seleccionar_varias_guias" class="form-check-input" /> Check - todo</th>
                                                    <th>Guía</th>
                                                    <th>Fecha Emisión</th>
                                                    <th>Factura</th>
                                                    <th>Importe Total (Sin IGV)</th>
                                                    <th>Nombre Cliente</th>
                                                    <th>Ubigeo</th>
                                                    <th>Forma de pago</th>
                                                    <th>Estado en Sistema Facturación</th>
                                                </tr>
                                            </x-slot>
                                            <x-slot name="tbody">
                                                @foreach($listar_guias_registradas as $lgr)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox"
{{--                                                                   wire:click="seleccionar_una_guia_cre_des({{ $lgr->id_guia }})"--}}
                                                                   wire:model="select_todas_guias"
                                                                   value="{{ $lgr->id_guia }}"
                                                                   class="form-check-input" />
                                                        </td>
                                                        <td>{{$lgr->guia_nro_doc}}</td>
                                                        <td>{{ $lgr->guia_fecha_emision ? $me->obtenerNombreFecha($lgr->guia_fecha_emision, 'DateTime', 'Date') : '-' }}</td>
                                                        <td>{{$lgr->guia_nro_doc_ref}}</td>
                                                        <td>S/ {{$lgr->guia_importe_total_sin_igv}}</td>
                                                        <td>{{$lgr->guia_nombre_cliente}}</td>
                                                        <td>
                                                            {{ $lgr->guia_departamento ?? 'N/A' }} -
                                                            {{ $lgr->guia_provincia ?? 'N/A' }} -
                                                            {{ $lgr->guia_destrito ?? 'N/A' }}
                                                        </td>
                                                        <td>{{$lgr->guia_forma_pago}}</td>
                                                        <td>{{$lgr->guia_estado}}</td>
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
                    <div wire:loading wire:target="enviar_estado_guia" class="overlay__eliminar">
                        <div class="spinner__container__eliminar">
                            <div class="spinner__eliminar"></div>
                        </div>
                    </div>

                    <div wire:loading wire:target="enviar_anulado_nc" class="overlay__eliminar">
                        <div class="spinner__container__eliminar">
                            <div class="spinner__eliminar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalCreditosDespachos');
        modal.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                modal.querySelector('form').requestSubmit();
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalAnularNC');
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
    $wire.on('hideModalCreditosDespachos', () => {
        $('#modalCreditosDespachos').modal('hide');
    });
    $wire.on('hideModalAnularNC', () => {
        $('#modalAnularNC').modal('hide');
    });
</script>
@endscript
