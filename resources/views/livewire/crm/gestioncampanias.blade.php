<div>
    @php
        $me = new \App\Models\General();
    @endphp

{{--    MODAL AGREGAR / EDITAR CAMPAÑA--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modal_campania</x-slot>
        <x-slot name="titleModal">Gestionar Campaña</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="save_campania">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="campania_nombre" class="form-label">Nombre de la campaña <b class="text-danger">(*)</b></label>
                        <x-input-general type="text" id="campania_nombre" wire:model="campania_nombre"/>
                        @error('campania_nombre')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="campania_estado_ejecucion" class="form-label">Estado <b class="text-danger">(*)</b></label>
                       <select class="form-control" id="campania_estado_ejecucion" wire:model="campania_estado_ejecucion">
                           <option value="">Seleccionar...</option>
                           <option value="1">Activa</option>
                           <option value="2">Cerrada</option>
                       </select>
                        @error('campania_estado_ejecucion')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="campania_fecha_inicio" class="form-label">Fecha de Inicio <b class="text-danger">(*)</b></label>
                        <x-input-general type="date" id="campania_fecha_inicio" wire:model="campania_fecha_inicio"/>
                        @error('campania_fecha_inicio')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="campania_fecha_fin" class="form-label">Fecha Fin <b class="text-danger">(*)</b></label>
                        <x-input-general type="date" id="campania_fecha_fin" wire:model="campania_fecha_fin"/>
                        @error('campania_fecha_fin')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="campania_fecha_fin_canje" class="form-label">Fecha Final de Canje <b class="text-danger">(*)</b></label>
                        <x-input-general type="date" id="campania_fecha_fin_canje" wire:model="campania_fecha_fin_canje"/>
                        @error('campania_fecha_fin_canje')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Adjuntos:</h6>
                            <input type="file" id="fileInput" class="d-none"
                                multiple
                                wire:model="archivosNuevos"
                                accept=".pdf,.xlsx,.xls,.ppt,.pptx,.jpg,.jpeg,.png" />
                            <a class="btn bg-info text-white" onclick="document.getElementById('fileInput').click()">
                                Examinar
                            </a>
                        </div>

                        {{-- loading al seleccionar archivos --}}
                        <div wire:loading wire:target="archivosNuevos" class="alert alert-info py-2 mb-2">
                            Cargando archivos…
                        </div>

                        {{-- contenedor estilo tarjeta grande (como en la imagen) --}}
                        <div class="border rounded px-3 py-4" style="min-height: 160px;">
                            @if (count($archivos) === 0)
                                <p class="text-muted m-0">Aún no hay archivos seleccionados.</p>
                            @else
                                <div class="row g-4">
                                    @foreach ($archivos as $i => $file)
                                        @php
                                            // Determinar si es un archivo nuevo (objeto) o existente (string)
                                            if (is_object($file)) {
                                                // Archivo nuevo subido
                                                $nombreArchivo = $file->getClientOriginalName();
                                                $ext = strtolower($file->getClientOriginalExtension());
                                            } else {
                                                // Archivo existente de la base de datos
                                                $nombreArchivo = basename($file);
                                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            }

                                            $icon = match(true) {
                                                $ext === 'pdf' => asset('assets/images/gestion_campania/pdf.png'),
                                                in_array($ext, ['xlsx','xls']) => asset('assets/images/gestion_campania/xlsx.png'),
                                                in_array($ext, ['ppt','pptx']) => asset('assets/images/gestion_campania/pptx.png'),
                                                in_array($ext, ['jpg','jpeg','png']) => asset('assets/images/gestion_campania/jpg.png'),
                                                default => asset('assets/images/gestion_campania/jpg.png'),
                                            };
                                        @endphp

                                        <div class="col-6 col-md-3" wire:key="adjunto-{{ $i }}">
                                            <div class="text-center position-relative">
                                                <a class="btn btn-sm text-danger btn-light border position-absolute"
                                                   style="right: -6px; top: -10px;"
                                                   wire:click="removeArchivo({{ $i }})">
                                                    &times;
                                                </a>

                                                {{-- icono --}}
                                                <img src="{{ $icon }}" alt="icono" class="img-fluid mb-2" style="max-height:70px;">

                                                {{-- nombre del archivo --}}
                                                <div class="small text-truncate" title="{{ $nombreArchivo }}">
                                                    {{ $nombreArchivo }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error_modal'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_modal') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Guardar Registros</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL AGREGAR / EDITAR CAMPAÑA--}}

{{--    MODAL DESHABILITAR CAMPAÑA--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modal_delete_campania</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_campania">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageDelete}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_campania') <span class="message-error">{{ $message }}</span> @enderror

                        @error('campania_estado') <span class="message-error">{{ $message }}</span> @enderror

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
{{--    FIN MODAL DESHABILITAR CAMPAÑA--}}


    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label class="form-label">Desde</label>
            <input type="date" name="desde" id="desde" wire:model.live="desde" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <label class="form-label">Hasta</label>
            <input type="date" name="hasta" id="hasta" wire:model.live="hasta" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 mb-2">
            <label class="form-label">Estado</label>
            <select class="form-select" id="buscar_estado" wire:model.live="buscar_estado">
                <option value="">Seleccionar...</option>
                <option value="1">Activa</option>
                <option value="2">Cerrada</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_campania" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_campania" />
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-2 text-end">
            <x-btn-export wire:click="clear_form" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modal_campania" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Campaña
            </x-btn-export>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mt-4 text-end mb-2">
            <a href="{{route('CRM.sistema_puntos_vendedor_cliente')}}" class="btn bg-secondary text-white"><i class="fa-solid fa-arrow-left me-2"></i> Regresar</a>
        </div>
    </div>

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

    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Nombre de la campaña</th>
                                <th>Fecha de Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Fecha Final canje</th>
                                <th>Adjunto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_campanias) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($listar_campanias as $lc)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$lc->campania_nombre}}</td>
                                        <td>{{ $lc->campania_fecha_inicio ? $me->obtenerNombreFecha($lc->campania_fecha_inicio, 'Date', 'Date') : '-' }}</td>
                                        <td>{{ $lc->campania_fecha_fin ? $me->obtenerNombreFecha($lc->campania_fecha_fin, 'Date', 'Date') : '-' }}</td>
                                        <td>{{ $lc->campania_fecha_fin_canje ? $me->obtenerNombreFecha($lc->campania_fecha_fin_canje, 'Date', 'Date') : '-' }}</td>
                                        <td>
                                            @php
                                                $documentos = \Illuminate\Support\Facades\DB::table('campanias_documentos')
                                                    ->where('campania_documento_estado', '=', 1)
                                                    ->where('id_campania', $lc->id_campania)
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

                                                        <div class="d-flex align-items-center gap-2">
                                                            <a href="{{ asset($doc->campania_documento_adjunto) }}"
                                                               target="_blank"
                                                               style="font-size: 18px;"
                                                               class="text-decoration-none"
                                                               title="{{ basename($doc->campania_documento_adjunto) }}">
                                                                <i class="fa-solid {{ $icono }}"></i>
                                                            </a>
                                                            <span class="small">{{ $nombre }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">Sin archivos</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-bold badge {{$lc->campania_estado_ejecucion == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$lc->campania_estado_ejecucion == 1 ? 'Activa ' : 'Cerrada'}}
                                            </span>
                                        </td>
                                        <td>
                                            <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($lc->id_campania) }}')" data-bs-toggle="modal" data-bs-target="#modal_campania">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>

                                            @if($lc->campania_estado == 1)
                                                <x-btn-accion class=" text-danger" wire:click="btn_disable('{{ base64_encode($lc->id_campania) }}',0)" data-bs-toggle="modal" data-bs-target="#modal_delete_campania">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @else
                                                <x-btn-accion class=" text-success" wire:click="btn_disable('{{ base64_encode($lc->id_campania) }}',1)" data-bs-toggle="modal" data-bs-target="#modal_delete_campania">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-check"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @endif
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
    {{ $listar_campanias->links(data: ['scrollTo' => false]) }}
</div>

@script
<script>
    $wire.on('hide_modal_campania', () => {
        $('#modal_campania').modal('hide');
    });
    $wire.on('hide_modal_delete_campania', () => {
        $('#modal_delete_campania').modal('hide');
    });
</script>
@endscript
