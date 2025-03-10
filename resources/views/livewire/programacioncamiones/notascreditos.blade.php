<div>
    @php
        $general = new \App\Models\General();
    @endphp
    {{--    MODAL REGISTRO NOTA CREDITO--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalNotaCredito</x-slot>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="titleModal">Gestionar Nota de Credito</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveNotaCredito">
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
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control" min="2025-01-01">
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control" min="2025-01-01">
                            </div>
{{--                            <div class="col-lg-9 col-md-9 col-sm-12 mb-2">--}}
{{--                                <div class="position-relative">--}}
{{--                                    <input type="text" class="form-control bg-dark text-white rounded-pill ps-5 custom-placeholder" placeholder="Buscar comprobante" wire:model="searchFactura" style="border: none; outline: none;" />--}}
{{--                                    <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>--}}
{{--                                </div>--}}
{{--                            </div>--}}
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
                                                <th style="font-size: 11px">Serie y Correlativo / Guía</th>
                                                <th style="font-size: 12px">Descripción</th>
                                                <th style="font-size: 12px">Cantidad / Precio</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if(!empty($filteredGuias))
                                                @foreach($filteredGuias as $factura)
                                                    @php
                                                        $TIPO_DOCUMENTO = $factura->TIPO_DOCUMENTO;
                                                        $SERIE = $factura->SERIE;
                                                        $NUMERO_DOCUMENTO = $factura->NUMERO_DOCUMENTO;
                                                        $comprobanteExiste = collect($this->selectedGuias)->first(function ($facturaVa) use ($TIPO_DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO) {
                                                            return $facturaVa['TIPO_DOCUMENTO'] === $TIPO_DOCUMENTO
                                                                && $facturaVa['SERIE'] === $SERIE
                                                                && $facturaVa['NUMERO_DOCUMENTO'] === $NUMERO_DOCUMENTO;
                                                        });
                                                    @endphp
                                                    @if(!$comprobanteExiste)
                                                        <tr style="cursor: pointer" wire:click="seleccionar_guia('{{ $factura->TIPO_DOCUMENTO }}', '{{ $factura->SERIE }}', '{{ $factura->NUMERO_DOCUMENTO }}')">
                                                            <td colspan="3" style="padding: 0px">
                                                                <table class="table">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="width: 34.6%">
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->TIPO_DOCUMENTO }} - {{ $factura->SERIE }} - {{ $factura->NUMERO_DOCUMENTO }}
                                                                        </span>
                                                                        </td>
                                                                        <td style="width: 37.2%">
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                            {{ $factura->DESCRIPCION_ARTICULO }}
                                                                        </span>
                                                                        </td>
                                                                        <td>
                                                                            <span class="d-block tamanhoTablaComprobantes">
                                                                            CANTIDAD: <b class="colorBlackComprobantes">{{$factura->CANTIDAD}}</b> <br> PRECIO: <b class="colorBlackComprobantes">{{ $factura->PRECIO_VENTA }}</b>
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
                                                        <p class="mb-0" style="font-size: 12px">No se encontraron guias.</p>
                                                    </td>
                                                </tr>
                                            @endif
                                        </x-slot>
                                    </x-table-general>
                                </div>
                            </div>
                        </div>
                        <div wire:loading wire:target="seleccionar_guia" class="overlay__eliminar">
                            <div class="spinner__container__eliminar">
                                <div class="spinner__eliminar"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 m-3">
                                <label class="mb-2">Guía seleccionada</label>
                                @if (!empty($selectedGuias))
                                    @foreach ($selectedGuias as $factura)
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">
                                                {{ $factura['TIPO_DOCUMENTO'] }} - {{ $factura['SERIE'] }} - {{ $factura['NUMERO_DOCUMENTO'] }}
                                            </h6>
                                            <a href="#" class="btn btn-danger btn-sm mx-3" wire:click.prevent="eliminar_guia_seleccionada('{{ $factura['TIPO_DOCUMENTO'] }}', '{{ $factura['SERIE'] }}', '{{ $factura['NUMERO_DOCUMENTO'] }}')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted mb-0">No hay guía seleccionada.</p>
                                @endif
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="not_cre_guia_motivo" class="form-label">Código de motivo</label>
                                <select class="form-select" name="not_cre_guia_motivo" id="not_cre_guia_motivo" wire:model="not_cre_guia_motivo">
                                    <option value="">Seleccionar...</option>
                                    <option value="1">1 - Devolución</option>
                                    <option value="2">2 - Calidad</option>
                                    <option value="3">3 - Cobranza</option>
                                    <option value="4">4 - Error de facturación</option>
                                    <option value="5">5 - Otros comercial</option>
                                </select>
                                @error('not_cre_guia_motivo')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <label for="not_cre_guia_motivo_descripcion" class="form-label">Motivo descripción</label>
                                <textarea class="form-control" id="not_cre_guia_motivo_descripcion" name="not_cre_guia_motivo_descripcion" wire:model="not_cre_guia_motivo_descripcion" rows="4"></textarea>
                                @error('not_cre_guia_motivo_descripcion')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div wire:loading wire:target="eliminar_guia_seleccionada" class="overlay__eliminar">
                            <div class="spinner__container__eliminar">
                                <div class="spinner__eliminar"></div>
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
        <x-slot name="id_modal">modalCambioEstado</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="cambiar_estado_aprobacion">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageNotCret}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_nota_credito') <span class="message-error">{{ $message }}</span> @enderror

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
{{--    MODAL FIN CAMBIAR ESTADO APROBACION--}}

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_nota_credito" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_nota_credito" />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form_nota_credito" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalNotaCredito" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Nota credito
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
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="alert alert-danger alert-dismissible show fade mt-2">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Guía</th>
                                <th>Motivo</th>
                                <th>Descripción</th>
                                <th>Precio / Valor venta</th>
                                <th>RUC</th>
                                <th>Nombre del cliente</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_nota_credito) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($listar_nota_credito as $lnc)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$lnc->not_cre_guia_tipo_doc}} - {{$lnc->not_cre_guia_serie}} - {{$lnc->not_cre_guia_num_doc}}</td>
                                        <td>
                                            {{ [
                                                1 => '1 - Devolución',
                                                2 => '2 - Calidad',
                                                3 => '3 - Cobranza',
                                                4 => '4 - Error de facturación',
                                                5 => '5 - Otros comercial'
                                            ][$lnc->not_cre_guia_motivo] ?? 'No definido' }}
                                        </td>
                                        <td>{{$lnc->not_cre_guia_motivo_descripcion}}</td>
                                        <td>{{$lnc->not_cre_guia_precio_venta}} / {{$lnc->not_cre_guia_valor_venta}}</td>
                                        <td>{{$lnc->not_cre_guia_codigo_cliente}}</td>
                                        <td>{{$lnc->not_cre_guia_nombre_cliente}}</td>
                                        <td>{{$lnc->not_cre_guia_estado_guia}}</td>

                                        <td>
                                            <x-btn-accion class="text-primary"  wire:click="edit_data('{{ base64_encode($lnc->id_nota_credito_guia) }}')" data-bs-toggle="modal" data-bs-target="#modalNotaCredito">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>
                                        </td>
                                    </tr>
                                    @php $conteo++; @endphp
                                @endforeach
                            @else
                                <tr class="odd">
                                    <td valign="top" colspan="9" class="dataTables_empty text-center">
                                        No se han encontrado resultados.
                                    </td>
                                </tr>
                            @endif
                        </x-slot>
                    </x-table-general>
                </div>
            </div>
        </x-slot>
    </x-card-general-view>
    {{ $listar_nota_credito->links(data: ['scrollTo' => false]) }}

</div>

<script src="{{ asset('select2/dist/js/select2.min.js') }}"></script>
<script>
    function initSelect2() {
        $('#id_despacho_venta').select2({
            width: '100%',
            allowClear: true,
            placeholder: 'Seleccionar...',
            minimumResultsForSearch: 1,
            dropdownParent: $('#id_despacho_venta').parent(),
            language: {
                noResults: function() {
                    return "No se encontraron resultados.";
                }
            }
        });

        // Escuchar el cambio de valor y actualizar el modelo de Livewire
        $('#id_despacho_venta').on('change', function () {
        @this.set('id_despacho_venta', $(this).val());
        });
    }

    $(document).ready(function() {
        initSelect2();
    });

    document.addEventListener('livewire:load', function () {
        Livewire.hook('message.processed', (message, component) => {
            initSelect2(); // Re-inicializar Select2 después de cada actualización de Livewire
        });

        // Escuchar el evento para actualizar Select2
        Livewire.on('updateSelect2', (value) => {
            $('#id_despacho_venta').val(value).trigger('change');
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
</script>
@endscript
