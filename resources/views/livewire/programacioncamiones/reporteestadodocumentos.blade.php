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

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-responsive table-hover table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th style="background: #BDD7EE; color: black">Zona</th>
                            <th style="background: #BDD7EE; color: black">Promedio de días</th>
                            <th style="background: #E2EFDB; color: black">Cant. Guías</th>
                            @if($tipo_reporte == 1)
                                <th style="background: #E2EFDB; color: black">Acciones</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($resultados as $resultado)
                            <tr>
                                <td>{{ $resultado['zona'] }}</td>
                                <td>{{ $resultado['promedio'] }} días</td>
                                <td>{{ $resultado['cantidad'] }}</td>
                                @if($tipo_reporte == 1)
                                    <td>
                                        <button wire:click="cargarDetalles('{{ $resultado['estado_id'] }}', '{{ $resultado['zona'] }}')"
                                                class="btn btn-sm btn-primary"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#detalle-container">
                                            Ver Detalle
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($detallesZona)
            <div id="detalle-{{ $detallesZona[0]->guia_estado_aprobacion ?? '' }}" class="collapse mt-3 show">
                <div class="card">
                    <div class="card-body">
                        <h6>
                            Zona: {{ $zonaSeleccionada }}
                        </h6>
                        @if($cargandoDetalles)
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        @else
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
                                        @foreach($detallesZona as $index => $guia)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
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
                                        @endforeach
                                    </x-slot>
                                </x-table-general>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @elseif($tipo_reporte)
        <div class="alert alert-danger alert-dismissible show fade mt-2">
            No se encontraron guías que excedan el tiempo de atención.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>
