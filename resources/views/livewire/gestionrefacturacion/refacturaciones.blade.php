<div>
    @php
        $me = new \App\Models\General();
    @endphp
    {{--    MODAL REGISTRO VEHICULO--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalRefacturacion</x-slot>
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="titleModal">Gestionar factura</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveRefacturacion">
                <div class="row">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                            <label for="id_despacho_venta" class="form-label">Facturas anuladas</label>
                            <select class="form-select" name="id_despacho_venta" id="id_despacho_venta" wire:model="id_despacho_venta" wire:change="cargar_datos_factura">
                                <option value="">Seleccionar...</option>
                                @foreach($refac as $lt)
                                    <option value="{{ $lt->id_despacho_venta }}">{{$lt->despacho_venta_factura}}</option>
                                @endforeach
                            </select>
                            @error('id_despacho_venta')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <small class="text-primary">Información de la factura</small>
                            <hr class="mb-0">
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="cftd" class="form-label">cftd</label>
                            <x-input-general  type="text" id="cftd" wire:model="cftd"/>
                            @error('cftd')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="cfnumser" class="form-label">Numero serie</label>
                            <x-input-general  type="text" id="cfnumser" wire:model="cfnumser"/>
                            @error('cfnumser')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="cfnumdoc" class="form-label">Numero documento</label>
                            <x-input-general  type="text" id="cfnumdoc" wire:model="cfnumdoc"/>
                            @error('cfnumdoc')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="factura" class="form-label">Factura</label>
                            <x-input-general  type="text" id="factura" wire:model="factura"/>
                            @error('factura')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="grefecemision" class="form-label">Fecha emision</label>
                            <x-input-general type="datetime-local" id="grefecemision" wire:model="grefecemision"/>
                            @error('grefecemision')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="cnomcli" class="form-label">Nombre cliente</label>
                            <x-input-general  type="text" id="cnomcli" wire:model="cnomcli"/>
                            @error('cnomcli')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="cfcodcli" class="form-label">Codigo cliente</label>
                            <x-input-general  type="text" id="cfcodcli" wire:model="cfcodcli"/>
                            @error('cfcodcli')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="guia" class="form-label">Guia</label>
                            <x-input-general  type="text" id="guia" wire:model="guia"/>
                            @error('guia')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="cfimporte" class="form-label">Importe</label>
                            <x-input-general  type="text" id="cfimporte" wire:model="cfimporte"/>
                            @error('cfimporte')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="total_kg" class="form-label">Total kg</label>
                            <x-input-general  type="text" id="total_kg" wire:model="total_kg"/>
                            @error('total_kg')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="total_volumen" class="form-label">Total volumen</label>
                            <x-input-general  type="text" id="total_volumen" wire:model="total_volumen"/>
                            @error('total_volumen')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="direccion_llegada" class="form-label">Direccion llegada</label>
                            <x-input-general  type="text" id="direccion_llegada" wire:model="direccion_llegada"/>
                            @error('direccion_llegada')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="departamento" class="form-label">Departamento</label>
                            <x-input-general  type="text" id="departamento" wire:model="departamento"/>
                            @error('departamento')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="provincia" class="form-label">Provincia</label>
                            <x-input-general  type="text" id="provincia" wire:model="provincia"/>
                            @error('provincia')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                            <label for="distrito" class="form-label">Distrito</label>
                            <x-input-general  type="text" id="distrito" wire:model="distrito"/>
                            @error('distrito')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Guardar Registro</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
    {{--    FIN MODAL REGISTRO VEHICULO--}}

    {{--    MODAL DELETE--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalDeleteVehiculos</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_vehiculo">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
{{--                        <h2 class="deleteTitle">{{$messageDeleteVehiculo}}</h2>--}}
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_vehiculo') <span class="message-error">{{ $message }}</span> @enderror

                        @error('vehiculo_estado') <span class="message-error">{{ $message }}</span> @enderror

                        @if (session()->has('error_delete'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_delete') }}
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
    {{--    FIN MODAL DELETE--}}

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_refactutacion" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_refactutacion" />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form_refacturacion" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalRefacturacion" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar
            </x-btn-export>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                <th>Serie / Guía</th>
                                <th>F. Emisión</th>
                                <th>Importe sin IGV</th>
                                <th>Nombre Cliente</th>
                                <th>Peso y Volumen</th>
                                <th>Dirección</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_refactura) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($listar_refactura as $lr)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>
                                            <span class="d-block tamanTablaComprobantes">
                                                {{ $lr->refacturacion_cfnumser }} - {{ $lr->refacturacion_cfnumdoc }}
                                            </span>
                                            @php
                                                $guia2 = $me->formatearCodigo($lr->refacturacion_guia)
                                            @endphp
                                            <span class="d-block tamanTablaComprobantes">
                                                {{ $guia2 }}
                                            </span>
                                        </td>
                                        @php
                                            $me = new \App\Models\General();
                                            $importe = "0";
                                            if ($lr->refacturacion_cfimporte){
                                                $importe = $me->formatoDecimal($lr->refacturacion_cfimporte);
                                            }
                                        @endphp
                                        @php
                                            $feFor = "";
                                            if ($lr->refacturacion_grefecemision){
                                                $feFor = $me->obtenerNombreFecha($lr->refacturacion_grefecemision,'DateTime','Date');
                                            }
                                        @endphp
                                        <td>
                                            <span class="d-block tamanhablaComprobantes">
                                                {{ $feFor }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-block tamanhablaComprobantes">
                                                <b class="colorBlackComprobantes">{{ $importe }}</b>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-block tamanhablaComprobantes">
                                                {{ $lr->refacturacion_cnomcli }}
                                            </span>
                                        </td>
                                        @php
                                            $pesoTabla = "0";
                                            if ($lr->refacturacion_total_kg){
                                                $pesoTabla = $me->formatoDecimal($lr->refacturacion_total_kg);
                                            }
                                        @endphp
                                        @php
                                            $volumenTabla = "0";
                                            if ($lr->refacturacion_total_volumen){
                                                $volumenTabla = $me->formatoDecimal($lr->refacturacion_total_volumen);
                                            }
                                        @endphp
                                        <td>
                                            <span class="d-block tamanhoblaComprobantes">
                                                <b class="colorBlackComprobantes">{{ $pesoTabla }}  kg</b>
                                            </span>
                                            <span class="d-block tamanhablaComprobantes">
                                                <b class="colorBlackComprobantes">{{ $volumenTabla }} cm³</b>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-block tamanhablaComprobantes">
                                                {{ $lr->refacturacion_direccion_llegada }}
                                            </span>
                                            <br>
                                            <span class="d-block tamanhablaComprobantes" style="color: black;font-weight: bold">
                                                {{ $lr->refacturacion_departamento }} - {{ $lr->refacturacion_provincia }}- {{ $lr->refacturacion_distrito }}
                                            </span>
                                        </td>
                                        <td>

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
    {{ $listar_refactura->links(data: ['scrollTo' => false]) }}

</div>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalRefacturacion').modal('hide');
    });
    $wire.on('hideModalDelete', () => {
        $('#modalDeleteVehiculos').modal('hide');
    });

    function soloNumerosYPuntuacion(event) {
        const input = event.target;
        input.value = input.value.replace(/[^0-9.,]/g, '');
    }
</script>
@endscript
