<div>

    @php
        $me = new \App\Models\General();
    @endphp

    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
            <label for="estado_campania" class="form-label">Estado de Campaña</label>
            <select class="form-control" id="estado_campania" wire:model="estado_campania">
                <option value="">Seleccionar...</option>
                <option value="1">Activos</option>
                <option value="2">Cerrados</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
            <label for="anio_campania_vigencia" class="form-label">Año de Vigencia de Campaña</label>
            <select class="form-control" id="anio_campania_vigencia" wire:model="anio_campania_vigencia">
                <option>Seleccionar...</option>
                @foreach($anios as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
            <label for="text_campania" class="form-label">Campaña</label>
            <input type="text" class="form-control" id="text_campania" wire:model="text_campania" />
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-3 text-end">
            <a class="btn btn-sm bg-primary text-white" wire:click="buscar_historial_punto">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </a>
        </div>

        <div wire:loading wire:target="buscar_historial_punto" class="overlay__eliminar">
            <div class="spinner__container__eliminar">
                <div class="spinner__eliminar"></div>
            </div>
        </div>

    </div>

    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Nombre de la Campaña</th>
                                <th>Fecha de Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Fecha Final de Canje</th>
                                <th>Adjunto</th>
                                <th>Estado</th>
                                <th>Pts. Ganados</th>
                                <th>Pts. Canjeados</th>
                                <th>Pts. Restantes</th>
                                <th>WhatsApp</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($resultado_campania) > 0)
                                @php $conteo_c = 1; @endphp
                                @foreach($resultado_campania as $rc)
                                    <tr>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse{{ $rc->id_campania }}"
                                                    aria-expanded="false"
                                                    aria-controls="collapse{{ $rc->id_campania }}">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                            {{$conteo_c}}
                                        </td>
                                        <td>{{ $rc->campania_nombre }}</td>
                                        <td>{{ $rc->campania_fecha_inicio ? $me->obtenerNombreFecha($rc->campania_fecha_inicio, 'Date', 'Date') : '-' }}</td>
                                        <td>{{ $rc->campania_fecha_fin ? $me->obtenerNombreFecha($rc->campania_fecha_fin, 'Date', 'Date') : '-' }}</td>
                                        <td>{{ $rc->campania_fecha_fin_canje ? $me->obtenerNombreFecha($rc->campania_fecha_fin_canje, 'Date', 'Date') : '-' }}</td>
                                        <td>
                                            @php
                                                $documentos = \Illuminate\Support\Facades\DB::table('campanias_documentos')
                                                    ->where('campania_documento_estado', '=', 1)
                                                    ->where('id_campania', $rc->id_campania)
                                                    ->get();
                                            @endphp

                                            @if($documentos->count() > 0)
                                                <div class="d-flex flex-column gap-1">
                                                    @foreach($documentos as $doc)
                                                        @php
                                                            $ext = strtolower(pathinfo($doc->campania_documento_adjunto, PATHINFO_EXTENSION));
                                                            $icono = match(true) {
                                                                $ext === 'pdf' => 'fa-file-pdf text-danger',
                                                                in_array($ext, ['xlsx', 'xls', 'csv']) => 'fa-file-excel text-success',
                                                                in_array($ext, ['ppt', 'pptx']) => 'fa-file-powerpoint text-warning',
                                                                in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) => 'fa-file-image text-primary',
                                                                default => 'fa-file text-secondary'
                                                            };
                                                            $nombre = \Illuminate\Support\Str::limit(basename($doc->campania_documento_adjunto), 15);
                                                        @endphp

                                                        <div class="d-flex align-items-center my-2 gap-2">
                                                            <a href="{{ asset($doc->campania_documento_adjunto) }}"
                                                               target="_blank"
                                                               style="font-size: 18px;"
                                                               class="text-decoration-none"
                                                               title="{{ basename($doc->campania_documento_adjunto) }}">
                                                                <i class="fa-solid {{ $icono }}"></i>
                                                                <span class="small">{{ $nombre }}</span>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">Sin archivos</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-bold badge {{$rc->campania_estado_ejecucion == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$rc->campania_estado_ejecucion == 1 ? 'Activa ' : 'Cerrada'}}
                                            </span>
                                        </td>
                                        <td>{{ number_format($rc->puntos_ganados, 0) }}</td>
                                        <td>{{ number_format($rc->puntos_canjeados, 0) }}</td>
                                        <td>{{ number_format($rc->puntos_restantes, 0) }}</td>
                                        <td>
                                            @if(!empty($rc->campania_celular))
                                                <a href="https://wa.me/{{ $rc->campania_celular }}" class="btn btn-lg text-success" target="_blank">
                                                    <i class="fa-brands fa-whatsapp"></i>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>

                                    <tr class="collapse" wire:ignore.self id="collapse{{ $rc->id_campania }}">
                                        <td colspan="11">
                                            <div class="p-3 bg-light">
                                                <x-table-general>
                                                    <x-slot name="thead">
                                                        <tr>
                                                            <th>N°</th>
                                                            <th>Fecha</th>
                                                            <th>Operación</th>
                                                            <th>Descripción</th>
                                                            <th>Puntos</th>
                                                            <th>Saldo</th>
                                                            <th>Archivo</th>
                                                        </tr>
                                                    </x-slot>
                                                    <x-slot name="tbody">

                                                    </x-slot>
                                                </x-table-general>
                                            </div>
                                        </td>
                                    </tr>
                                    @php $conteo_c++; @endphp
                                @endforeach
                            @else
                                <tr class="odd">
                                    <td valign="top" colspan="11" class="dataTables_empty text-center">
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

</div>
