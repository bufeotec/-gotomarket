<div>
    @php
        $general = new \App\Models\General();
    @endphp
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible show fade mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible show fade mt-2">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row align-items-center mb-3">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="guia_estado_aprobacion" class="form-label">Estados</label>
            <select name="guia_estado_aprobacion" id="guia_estado_aprobacion" wire:model="guia_estado_aprobacion" class="form-select">
                <option value="">Seleccionar...</option>
                <option value="1">Creditos</option>
                <option value="3">Pendiente de Programación</option>
                <option value="4">Programado</option>
                <option value="7">En camino</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label for="tipo_reporte" class="form-label">Tipo de reporte</label>
            <select name="tipo_reporte" id="tipo_reporte" wire:model.live="tipo_reporte" class="form-select">
                <option value="">Seleccionar...</option>
                <option value="1">Consulta</option>
                <option value="2">Historial</option>
            </select>
        </div>

        @if($mostrarFechas)
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <label for="fecha_desde" class="form-label">Desde</label>
                <input type="date" name="fecha_desde" id="fecha_desde" wire:model="desde" class="form-control">
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <label for="fecha_hasta" class="form-label">Hasta</label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model="hasta" class="form-control">
            </div>
        @endif

        <div class="col-lg-2 col-md-3 col-sm-12 mt-4">
            <button class="btn btn-sm bg-primary text-white" wire:click="buscar_estado_documento">
                <i class="fa fa-search"></i> BUSCAR
            </button>
        </div>

        @if($resultados)
            <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
                <button class="btn btn-success btn-sm text-white mt-4" wire:click="generar_excel_estado_documentos" wire:loading.attr="disabled"><i class="fa-solid fa-file-excel"></i> Exportar</button>
            </div>
        @endif
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="loader mt-2" wire:loading wire:target="buscar_estado_documento"></div>
        </div>
    </div>

    @if(count($resultados) > 0)
        <div class="accordion mt-3" id="accordionEstados">
            @php $conteoGeneral = 1; @endphp
            @foreach($resultados as $index => $resultado)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                            #{{ $conteoGeneral }} | Zona: {{ $resultado['zona'] }} | Promedio: {{ $resultado['promedio'] }} días | Cantidad de guías: {{ $resultado['cantidad'] }}
                        </button>
                    </h2>
                    <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" data-bs-parent="#accordionEstados">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <x-table-general id="facturasPreProgTable">
                                    <x-slot name="thead">
                                        <tr>
                                            <th></th>
                                            <th>Guía</th>
                                            <th>Fecha Emisión</th>
                                            <th>Tipo de Movimiento</th>
                                            <th>Tipo de Documento Referencial</th>
                                            <th>Número de Documento Referencial</th>
                                            <th>Glosa</th>
                                            <th>Estado</th>
                                            <th>Importe Total</th>
                                            <th>Moneda</th>
                                            <th>Dirección de Entrega</th>
                                            <th>Departamento</th>
                                            <th>Provincia</th>
                                            <th>Distrito</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @php $conteoGeneral2 = 1; @endphp
                                        @foreach($resultado['guias'] as $guia)
                                            <tr>
                                                <td>{{$conteoGeneral2}}</td>
                                                <td>{{ $guia->guia_nro_doc ?? '-' }}</td>
                                                <td>{{ $guia->guia_fecha_emision ? $general->obtenerNombreFecha($guia->guia_fecha_emision, 'DateTime', 'DateTime') : '-' }}</td>
                                                <td>{{ $guia->guia_tipo_movimiento ?? '-' }}</td>
                                                <td>{{ $guia->guia_tipo_doc_ref ?? '-' }}</td>
                                                <td>{{ $guia->guia_nro_doc_ref ?? '-' }}</td>
                                                <td>{{ $guia->guia_glosa ?? '-' }}</td>
                                                <td>{{ $guia->guia_estado ?? '-' }}</td>
                                                <td>{{ $general->formatoDecimal($guia->guia_importe_total ?? 0) }}</td>
                                                <td>{{ $guia->guia_moneda ?? '-' }}</td>
                                                <td>{{ $guia->guia_direc_entrega ?? '-' }}</td>
                                                <td>{{ $guia->guia_departamento ?? '-' }}</td>
                                                <td>{{ $guia->guia_provincia ?? '-' }}</td>
                                                <td>{{ $guia->guia_distrito ?? '-' }}</td>
                                            </tr>
                                            @php $conteoGeneral2++; @endphp
                                        @endforeach
                                    </x-slot>
                                </x-table-general>
                            </div>
                        </div>
                    </div>
                </div>
                @php $conteoGeneral++; @endphp
            @endforeach
        </div>
    @elseif($tipo_reporte)
        <div class="alert alert-danger alert-dismissible show fade mt-2">
            No se encontraron guías que excedan el tiempo de atención.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>
