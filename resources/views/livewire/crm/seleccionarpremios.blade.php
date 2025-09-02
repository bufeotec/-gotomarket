<div>

{{--    MODAL VER SELECCIÓN--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modal_ver_seleccion</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="titleModal">Canjear Premios - {{ $campania_seleccionada->campania_nombre ?? '' }}</x-slot>
        <x-slot name="modalContent">
            <div class="col-lg-12 col-md-12 col-sm-12">
                @if (session()->has('error_modal'))
                    <div class="alert alert-danger alert-dismissible show fade mt-2">
                        {{ session('error_modal') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('success_modal'))
                    <div class="alert alert-success alert-dismissible show fade">
                        {{ session('success_modal') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>
            <form wire:submit.prevent="save_canjear_puntos">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3 text-end">
                        <h6 class="me-3">Puntos Ganados: <b class="text-success ms-2">{{number_format($puntos_ganados, 2)}}</b></h6>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>N°</th>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Puntos Unit.</th>
                                    <th>Cantidad</th>
                                    <th>Total Puntos</th>
                                    <th>Estado</th>
                                </tr>
                            </x-slot>

                            <x-slot name="tbody">
                                @if(count($premios_seleccionados) > 0)
                                    @php $conteo = 1; @endphp
                                    @foreach($premios_seleccionados as $premio)
                                        @php
                                            $id = $premio['id_premio'];
                                            $unit = (int)$premio['campania_premio_puntaje'];
                                            $cant = (int)$premio['cantidad'];
                                            $yaCanjeado = isset($premios_ya_canjeados[$id])
                                                          && !in_array($id, $premios_canjeados_deseleccionados ?? [], true);
                                            $total = $unit * $cant;
                                        @endphp
                                        <tr class="@if($yaCanjeado) table-info @endif">
                                            <td>{{ $conteo }}</td>
                                            <td>{{ $premio['premio_codigo'] }}</td>
                                            <td>{{ $premio['premio_descripcion'] }}</td>
                                            <td>{{ $unit }} pts</td>
                                            <td>
                                                <input type="number"
                                                       min="0"
                                                       value="{{ $cant }}"
                                                       @disabled($campania_cerrada)
                                                       wire:change="actualizarCantidad('{{ $id }}', $event.target.value)"
                                                       class="form-control form-control-sm">
                                                <small class="text-muted d-block mt-1">
                                                    Actual: <b>{{ $cant }}</b>
                                                </small>
                                            </td>
                                            <td>{{ $total }} pts</td>
                                            <td>
                                                @if($campania_cerrada)
                                                    <span class="badge bg-secondary">Bloqueado</span>
                                                @else
                                                    @if($yaCanjeado)
                                                        <span class="badge bg-info">Ya canjeado</span>
                                                    @else
                                                        <span class="badge bg-success">Nuevo</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        @php $conteo++; @endphp
                                    @endforeach

                                @else
                                    <tr class="odd">
                                        <td valign="top" colspan="7" class="dataTables_empty text-center">
                                            No se han seleccionado premios.
                                        </td>
                                    </tr>
                                @endif
                            </x-slot>
                        </x-table-general>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2 text-end">
                        <h6 class="me-3">Puntos Canjeados (vigentes): <b class="text-danger ms-2">{{number_format($puntos_canjeados, 2)}}</b></h6>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2 text-end">
                        <h6 class="me-3">Puntos Restantes (saldo): <b class="text-primary ms-2">{{number_format($puntos_restantes, 2)}}</b></h6>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        @if(!$campania_cerrada)
                            <button type="submit" class="btn btn-success text-white">Guardar Cambios</button>
                        @endif
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL VER SELECCIÓN--}}

{{--    MODAL VER DOCUMENTO POR CAMPAÑA--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modal_descargar_campania</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="titleModal">Descargar Archivos Adjuntos - {{ $campania_seleccionada->campania_nombre ?? '' }}</x-slot>
        <x-slot name="modalContent">
            <div class="col-lg-12 col-md-12 col-sm-12">
                @if (session()->has('error_modal_doc'))
                    <div class="alert alert-danger alert-dismissible show fade mt-2">
                        {{ session('error_modal_doc') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('success_modal_doc'))
                    <div class="alert alert-success alert-dismissible show fade">
                        {{ session('success_modal_doc') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="border rounded px-3 py-4" style="min-height: 160px;">
                        @if (count($archivos_adjuntos) === 0)
                            <p class="text-muted m-0">No hay archivos adjuntos para esta campaña.</p>
                        @else
                            <div class="row g-4">
                                @foreach ($documentos_campania as $i => $documento)
                                    @php
                                        $ext = strtolower($documento['extension']);
                                        $icon = match(true) {
                                            $ext === 'pdf' => asset('assets/images/gestion_campania/pdf.png'),
                                            in_array($ext, ['xlsx','xls']) => asset('assets/images/gestion_campania/xlsx.png'),
                                            in_array($ext, ['doc','docx']) => asset('assets/images/gestion_campania/doc.png'),
                                            in_array($ext, ['ppt','pptx']) => asset('assets/images/gestion_campania/pptx.png'),
                                            in_array($ext, ['jpg','jpeg','png','gif','bmp']) => asset('assets/images/gestion_campania/jpg.png'),
                                            in_array($ext, ['zip','rar','7z']) => asset('assets/images/gestion_campania/zip.png'),
                                            default => asset('assets/images/gestion_campania/file.png'),
                                        };
                                    @endphp

                                    <div class="col-6 col-md-3" wire:key="doc-{{ $documento['id_campania_documento'] }}-{{ $i }}">
                                        <div class="text-center position-relative">
                                            {{-- Icono --}}
                                            <img src="{{ $icon }}" alt="icono" class="img-fluid mb-2" style="max-height:70px;">

                                            {{-- Nombre del archivo --}}
                                            <div class="small text-truncate" title="{{ $documento['nombre'] }}">
                                                {{ $documento['nombre'] }}
                                            </div>

                                            {{-- Botón de descarga --}}
                                            <a href="{{ asset($documento['campania_documento_adjunto']) }}"
                                               class="btn btn-sm btn-primary mt-2"
                                               download="{{ $documento['nombre'] }}"
                                               target="_blank">
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-12 text-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL VER DOCUMENTO POR CAMPAÑA--}}

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

   <div class="row align-items-center">
       <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
           <label for="id_campania" class="form-label">Campaña: </label>
           <select id="id_campania" class="form-select" wire:model.live="id_campania" wire:change="cargarDocumentosCampania">
               <option value="">Seleccionar...</option>
               @foreach($listar_campania as $lc)
                   <option value="{{ $lc->id_campania }}">{{ $lc->campania_nombre }}</option>
               @endforeach
           </select>
       </div>

       <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-3">
           <a class="btn btn-sm bg-success text-white" data-bs-toggle="modal" data-bs-target="#modal_descargar_campania"><i class="fa-solid fa-download"></i> Descargar Archivos</a>
       </div>

       <div class="col-lg-2 col-md-2 col-sm-12 mb-3 d-flex flex-column align-items-center justify-content-center">
           <p class="mb-2">Pts. Ganados</p>
           <div class="d-flex align-items-center justify-content-center rounded-circle border border-dark" style="width: 80px; height: 80px; background: white">
               <h6 class="text-success m-0 p-0">{{number_format($puntos_ganados, 2)}}</h6>
           </div>
       </div>

       <div class="col-lg-2 col-md-2 col-sm-12 mb-3 d-flex flex-column align-items-center justify-content-center">
           <p class="mb-2">Pts. Canjeados</p>
           <div class="d-flex align-items-center justify-content-center rounded-circle border border-dark" style="width: 80px; height: 80px; background: white">
               <h6 class="text-danger m-0 p-0">{{number_format($puntos_canjeados, 2)}}</h6>
           </div>
       </div>

       <div class="col-lg-2 col-md-2 col-sm-12 mb-3 d-flex flex-column align-items-center justify-content-center">
           <p class="mb-2">Pts. Restantes</p>
           <div class="d-flex align-items-center justify-content-center rounded-circle border border-dark" style="width: 80px; height: 80px; background: white">
               <h6 class="text-primary m-0 p-0">{{number_format($puntos_restantes, 2)}}</h6>
           </div>
       </div>

       @if(empty(!$id_campania))
           <div class="col-lg-2 col-md-2 col-sm-12 mt-4 text-end mb-3">
               <a class="btn btn-sm bg-success text-white" data-bs-toggle="modal" data-bs-target="#modal_ver_seleccion"><i class="fa-solid fa-eye"></i> Ver Selección</a>
           </div>
       @endif

       <div class="col-lg-12 col-md-12 col-sm-12 mt-4 text-end mb-2">
           <a href="{{route('CRM.sistema_puntos_vendedor_cliente')}}" class="btn bg-secondary text-white"><i class="fa-solid fa-arrow-left me-2"></i> Regresar</a>
       </div>
   </div>

    <!-- loading -->
    <div wire:loading wire:target="id_campania, seleccionar_premio" class="overlay__eliminar">
        <div class="spinner__container__eliminar">
            <div class="spinner__eliminar"></div>
        </div>
    </div>

    @if(empty($id_campania))
        <div class="text-center my-3">
            <h6 class="text-black">Selecciona una campaña para ver sus premios disponibles.</h6>
        </div>
    @else
        <div class="row">
            @forelse($listar_premios_disponibles as $premio)
                <div class="col-lg-3 mt-2 mb-4">
                    <div class="border p-3 h-100 text-center shadow-sm rounded">
                        <div class="text-end mb-2">
                            @php
                                $yaCanjeado = isset($premios_ya_canjeados[$premio->id_premio])
                                              && !in_array($premio->id_premio, $premios_canjeados_deseleccionados ?? [], true);

                                $estaSeleccionado = isset($premios_seleccionados[$premio->id_premio])
                                                    && (($premios_seleccionados[$premio->id_premio]['id_campania'] ?? null) === $id_campania);
                            @endphp


                            <input type="checkbox" class="form-check-input"
                                   id="premio_{{ $premio->id_premio }}"
                                   value="{{ $premio->id_premio }}"
                                   @checked($estaSeleccionado || $yaCanjeado)
                                   @disabled($campania_cerrada)
                                   wire:click="seleccionar_premio('{{ $premio->id_premio }}', $event.target.checked)">

                            @if($yaCanjeado)
                                <small class="text-muted d-block">Ya canjeado</small>
                            @endif
                            @if($campania_cerrada)
                                <small class="text-danger d-block">Campaña finalizada</small>
                            @endif

                        </div>
                        <div>
                            <img src="{{ asset($premio->premio_documento) }}"
                                 alt="{{ $premio->premio_descripcion }}"
                                 class="img-fluid mb-3" style="max-height: 150px; object-fit: cover;">
                            <h5 class="card-title">{{ $premio->premio_descripcion }}</h5>
                            <p class="text-success fw-bold">{{ $premio->campania_premio_puntaje }} pts</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center my-3">
                        <h6 class="text-black">Esta campaña no tiene premios activos disponibles.</h6>
                    </div>
                </div>
            @endforelse
        </div>
    @endif

</div>

@script
<script>
    $wire.on('hide_modal_ver_seleccion', () => {
        $('#modal_ver_seleccion').modal('hide');
    });
</script>
@endscript
