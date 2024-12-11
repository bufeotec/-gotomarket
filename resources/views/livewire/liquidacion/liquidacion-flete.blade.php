<div>

    @php
        $general = new \App\Models\General();
    @endphp
{{--    MODAL DETALLE DESPACHO--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-xl</x-slot>
        <x-slot name="id_modal">modalDetalleDespacho</x-slot>
        <x-slot name="titleModal">Detalles del Despacho</x-slot>
        <x-slot name="modalContent">
            @if($listar_detalle_despacho)
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <h6>Información Adicional del Despacho</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                    <strong class="colorgotomarket mb-2">Usuario de Registro</strong>
                                    <p>{{ $listar_detalle_despacho->name }}</p>
                                </div>
                                @if($listar_detalle_despacho->id_vehiculo)
                                    @php
                                        $vehiculo = \Illuminate\Support\Facades\DB::table('vehiculos')->where('id_vehiculo','=',$listar_detalle_despacho->id_vehiculo)->first();
                                    @endphp
                                    <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                        <strong class="colorgotomarket mb-2">Placa del Vehículo:</strong>
                                        <p>{{ $vehiculo->vehiculo_placa }}</p>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                                        <strong class="colorgotomarket mb-2">Capacidad del Vehículo:</strong>
                                        <p>{{ $general->formatoDecimal($vehiculo->vehiculo_capacidad_peso) }} Kg</p>
                                    </div>
                                @endif

                                @if($listar_detalle_despacho->id_tipo_servicios == 2)
                                    @php
                                        $departamento = \Illuminate\Support\Facades\DB::table('departamentos')
                                        ->where('id_departamento','=',$listar_detalle_despacho->id_departamento)->first();
                                        $provincia = \Illuminate\Support\Facades\DB::table('provincias')
                                        ->where('id_provincia','=',$listar_detalle_despacho->id_provincia)->first();
                                        $distrito = \Illuminate\Support\Facades\DB::table('distritos')
                                        ->where('id_distrito','=',$listar_detalle_despacho->id_distrito)->first();
                                    @endphp
                                    <div class="col-lg-5 col-md-3 col-sm-4 mb-3">
                                        <strong class="colorgotomarket mb-2">Ubigeo Seleccionado en el Despacho:</strong>
                                        <p>{{ $departamento ? $departamento->departamento_nombre : '' }} - {{ $provincia ? $provincia->provincia_nombre : '' }} - {{ $distrito ? $distrito->distrito_nombre : 'TODOS LOS DISTRITOS' }}</p>
                                    </div>
                                @endif


                                @if($listar_detalle_despacho->id_tarifario)
                                    {{--                                    @php--}}
                                    {{--                                        $tarifa = \Illuminate\Support\Facades\DB::table('tarifarios as t')--}}
                                    {{--                                        ->where('t.id_tarifario','=',$listar_detalle_despacho->id_tarifario)->first();--}}
                                    {{--                                        $medida = \Illuminate\Support\Facades\DB::table('medida')->where('id_medida','=',$tarifa->id_medida)->first();--}}
                                    {{--                                        $meMed = "";--}}
                                    {{--                                        if ($medida){--}}
                                    {{--                                            $meMed = $medida->id_medida == 23 ? ' Kg' : ' cm³';--}}
                                    {{--                                        }else{--}}
                                    {{--                                            $meMed = ' Kg';--}}
                                    {{--                                        }--}}
                                    {{--                                    @endphp--}}

                                    <div class="col-lg-3 col-md-3 col-sm-4 mb-3">
                                        <strong class="colorgotomarket mb-2">Capacidad de la Tarifa:</strong>
                                        <p>Min: {{$general->formatoDecimal($listar_detalle_despacho->despacho_cap_min)}} Kg - Max: {{ $general->formatoDecimal($listar_detalle_despacho->despacho_cap_max) }} Kg</p>
                                    </div>
                                @endif
                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Monto de la Tarifa:</strong>
                                    <p>S/ {{$general->formatoDecimal($listar_detalle_despacho->despacho_flete)}}</p>
                                </div>

                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Peso del Despacho:</strong>
                                    <p>{{$general->formatoDecimal($listar_detalle_despacho->despacho_peso)}} Kg</p>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Otros Gastos:</strong>
                                    <p>S/ {{$general->formatoDecimal($listar_detalle_despacho->despacho_gasto_otros)}}</p>
                                </div>
                                @if($listar_detalle_despacho->despacho_gasto_otros > 0)
                                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                                        <strong class="colorgotomarket mb-2">Descripción del Gasto:</strong>
                                        <p>{{ $listar_detalle_despacho->despacho_descripcion_otros }}</p>
                                    </div>
                                @endif
                                @if($listar_detalle_despacho->id_tipo_servicios == 1)
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                        <strong class="colorgotomarket mb-2">Mano de Obra:</strong>
                                        <p>S/ {{$general->formatoDecimal($listar_detalle_despacho->despacho_ayudante)}}</p>
                                    </div>
                                @endif
                                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                    <strong class="colorgotomarket mb-2">Total de Despacho:</strong>
                                    <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->despacho_costo_total) }}</p>
                                </div>
                                @if($listar_detalle_despacho->despacho_estado_modificado == 1)
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                        <strong class="colorgotomarket mb-2">Monto Modificado:</strong>
                                        <p>S/ {{ $general->formatoDecimal($listar_detalle_despacho->despacho_monto_modificado) }}</p>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                        <strong class="colorgotomarket mb-2">Descripción:</strong>
                                        <p>{{ $listar_detalle_despacho->despacho_descripcion_modificado }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <h6>Información de Comprobantes</h6>
                                    <hr>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th>N°</th>
                                                <th>Número Documento</th>
                                                <th>Fecha Emision</th>
                                                <th>Cliente</th>
                                                <th>Guía de Remisión</th>
                                                <th>Importe Venta</th>
                                                <th>Peso Kilos</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if(count($listar_detalle_despacho->comprobantes) > 0)
                                                @php $conteo = 1; @endphp
                                                @foreach($listar_detalle_despacho->comprobantes as $ta)
                                                    <tr>
                                                        <td>{{$conteo}}</td>
                                                        <td>{{$ta->despacho_venta_factura}}</td>
                                                        <td>
                                                            {{$general->obtenerNombreFecha($ta->despacho_venta_grefecemision,'DateTime','Date')}}
                                                        </td>
                                                        <td>{{$ta->despacho_venta_cnomcli}}</td>
                                                        <td>{{$ta->despacho_venta_guia}}</td>
                                                        <td>S/ {{$general->formatoDecimal($ta->despacho_venta_cfimporte)}}</td>
                                                        <td>{{$general->formatoDecimal($ta->despacho_venta_total_kg)}} Kg</td>
                                                    </tr>
                                                    @php $conteo++; @endphp
                                                @endforeach
                                            @else
                                                <tr class="odd">
                                                    <td valign="top" colspan="7" class="dataTables_empty text-center">
                                                        No se han encontrado resultados.
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
            @endif
        </x-slot>
    </x-modal-general>

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
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h6>Lista de transportistas</h6>
                    <select class="form-select" name="id_transportistas" id="id_transportistas" wire:model="id_transportistas" wire:change="seleccion_trans">
                        <option value="">Seleccionar...</option>
                        @foreach($listar_transportistas as $lt)
                            <option value="{{ $lt->id_transportistas }}">{{ $lt->transportista_nom_comercial }}</option>
                        @endforeach
                    </select>
                    @error('id_transportistas')
                    <span class="message-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{--    DATOS DEL DESPACHO SEGUN EL TRANSPORTISTA--}}
    @if($id_transportistas && $despachos->isEmpty())
        <div class="alert alert-danger mt-1">
            <p>Transportista sin despachos.</p>
        </div>
    @elseif($despachos && count($despachos) > 0)
        {{-- CAMPOS QUE MUESTRA SEGUN EL TRANSPORTISTA SELECCIONADO --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                        <h6>Serie</h6>
                                    </div>
                                    <div class="col-lg-12">
                                        <input class="form-control" type="text" id="liquidacion_serie" name="liquidacion_serie" wire:model="liquidacion_serie">
                                        @error('liquidacion_serie')
                                        <span class="message-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                        <h6>Correlativo</h6>
                                    </div>
                                    <div class="col-lg-12">
                                        <input class="form-control" type="text" id="liquidacion_correlativo" name="liquidacion_correlativo" wire:model="liquidacion_correlativo">
                                        @error('liquidacion_correlativo')
                                        <span class="message-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                                        <h6>Comprobante</h6>
                                    </div>
                                    <div class="col-lg-12">
                                        <input class="form-control" type="file" id="liquidacion_ruta_comprobante" name="liquidacion_ruta_comprobante" wire:model="liquidacion_ruta_comprobante">
                                        @error('liquidacion_ruta_comprobante')
                                        <span class="message-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{--    DESPACHOS DEL TRANSPORTISTA --}}
        <div class="row mt-3">
            <div class="col-12">
                @foreach($despachos as $key => $despacho)
                    <div class="p-3 mb-3" style="background-color: #e8e8f1; border-radius: 8px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <input
                                    type="checkbox"
                                    class="form-check-input me-3"
                                    wire:model.defer="select_despachos.{{ $despacho->id_despacho }}"
                                    wire:click="actualizarDespacho('{{ $despacho->id_despacho }}', $event.target.checked)"
                                >
                                <div>
                                    <strong>Despacho #{{ $despacho->despacho_numero_correlativo }}</strong>
                                    <p class="mb-0">
                                        Peso: {{ $despacho->despacho_peso }} | Volumen: {{ $despacho->despacho_volumen }} | Flete: {{ $despacho->despacho_flete }} | <button class="btn btn-sm text-primary" wire:click="listar_informacion_despacho({{ $despacho->id_despacho }})" data-bs-toggle="modal" data-bs-target="#modalDetalleDespacho">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                        {{-- Mostrar los campos adicionales solo si el despacho está seleccionado --}}
                        @if(!empty($select_despachos[$despacho->id_despacho]))
                            <div class="mt-3 p-3" style="background-color: #FFFFFF; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                                <div class="row">
                                    <div class="col-lg-4 mb-3">
                                        <label class="form-label" for="gasto_concepto_{{ $key }}">Concepto(*)</label>
                                        <input type="text" wire:model.defer="gastos.{{ $despacho->id_despacho }}.concepto" id="gasto_concepto_{{ $key }}" class="form-control">
                                        @error("gastos.$despacho->id_despacho.concepto")
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <label class="form-label" for="gasto_monto_{{ $key }}">Monto(*)</label>
                                        <input type="text" wire:model.defer="gastos.{{ $despacho->id_despacho }}.monto" id="gasto_monto_{{ $key }}" onkeyup="validar_numeros(this.id)" class="form-control">
                                        @error("gastos.$despacho->id_despacho.monto")
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <label class="form-label" for="gasto_descripcion_{{ $key }}">Descripción</label>
                                        <textarea class="form-control" wire:model.defer="gastos.{{ $despacho->id_despacho }}.descripcion" id="gasto_descripcion_{{ $key }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            @if(isset($select_despachos) && count($select_despachos) > 0)
                <div class="text-center d-flex justify-content-end">
                    <a href="#" wire:click.prevent="guardar_liquidacion" class="btn text-white" style="background: #e51821">
                        Guardar Liquidación
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
