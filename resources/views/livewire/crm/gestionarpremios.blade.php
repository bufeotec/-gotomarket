<div>

    @php
        use Illuminate\Support\Facades\Storage;
        $TmpClass = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::class;
    @endphp

{{--    MODAL CREAR / EDITAR PREMIO--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modal_premio</x-slot>
        <x-slot name="titleModal">Gestionar Premio</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="save_premio">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <label for="premio_descripcion" class="form-label">Premio <b class="text-danger">(*)</b></label>
                        <x-input-general type="text" id="premio_descripcion" wire:model="premio_descripcion"/>
                        @error('premio_descripcion')<span class="message-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Foto</label>
                            <input type="file" class="d-none" id="premio_documento" accept="image/*" wire:model="premio_documento">
                            <label for="premio_documento" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus"></i>
                                {{ $premio_documento || $existingImage ? 'Cambiar imagen' : 'Agregar imagen' }}
                            </label>
                        </div>

                        @error('premio_documento')<span class="message-error">{{ $message }}</span>@enderror

                        <!-- Loading -->
                        <div wire:loading wire:target="premio_documento" class="text-center mt-3 mb-2">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            <span class="ms-2">Cargando imagen...</span>
                        </div>

                        <!-- Preview imagen nueva -->
                        @if ($premio_documento instanceof $TmpClass)
                            <div class="mt-3 d-flex justify-content-center" wire:loading.remove wire:target="premio_documento">
                                <img
                                    wire:key="preview-{{ $premio_documento->getFilename() }}"
                                    src="{{ $premio_documento->temporaryUrl() }}"
                                    class="img-fluid" style="max-width: 200px;" alt="Preview">
                            </div>

                        @elseif ($existingImage)
                            <div class="mt-3 d-flex justify-content-center">
                                {{-- Para imagen ya guardada en disco "public" --}}
                                <img src="{{ asset($existingImage) }}" class="img-fluid" style="max-width: 200px;" alt="Imagen existente">
                            </div>
                        @endif
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
                        <button type="submit" class="btn btn-success text-white">Guardar Registros</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL CREAR / EDITAR PREMIO--}}

{{--    MODAL DELETE PREMIO--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modal_delete_premio</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_premio">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageDelete}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_premio') <span class="message-error">{{ $message }}</span> @enderror

                        @error('premio_estado') <span class="message-error">{{ $message }}</span> @enderror

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
{{--    FIN MODAL DELETE PREMIO--}}

{{--    MODAL CONFIRMAR PREMIOS - CAMPAÑAS--}}
    <x-modal-delete wire:ignore.self >
        <x-slot name="id_modal">modal_confirmar_premios</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="confirmar_premios_campania">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">¿Confirmar premios para la campaña?</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">

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
{{--    FIN MODAL CONFIRMAR PREMIOS - CAMPAÑAS--}}

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

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 text-end mb-3">
            <a href="{{route('CRM.sistema_puntos_vendedor_cliente')}}" class="btn bg-secondary text-white"><i class="fa-solid fa-arrow-left me-2"></i> Regresar</a>
        </div>
    </div>

    <div class="row">
        <!-- PREMIOS -->
        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
                            <input type="text" class="form-control w-100 me-4" wire:model.live="search_premios" placeholder="Buscar">
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
                            <x-btn-export wire:click="clear_form" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modal_premio" >
                                <x-slot name="icons">
                                    fa-solid fa-plus
                                </x-slot>
                                Agregar Premios
                            </x-btn-export>
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
                                                <th>#</th>
                                                <th>Código</th>
                                                <th>Descripción</th>
                                                <th>Imagen</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if(count($listar_premios) > 0)
                                                @php $conteo = 1; @endphp
                                                @foreach($listar_premios as $lp)
                                                    @if(!in_array($lp->id_premio, $premios_seleccionados))
                                                        <tr>
                                                            <td>{{$conteo}}</td>
                                                            <td>
                                                                <button class="btn btn-lg text-success" wire:click="agregarPremio('{{ base64_encode($lp->id_premio) }}')">
                                                                    <i class="fa-regular fa-circle-right"></i>
                                                                </button>
                                                            </td>
                                                            <td>{{$lp->premio_codigo}}</td>
                                                            <td>{{$lp->premio_descripcion}}</td>
                                                            <td>
                                                                <img src="{{asset($lp->premio_documento)}}" style="width: 200px;" class="" alt="">
                                                            </td>
                                                            <td>
                                                                <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($lp->id_premio) }}')" data-bs-toggle="modal" data-bs-target="#modal_premio">
                                                                    <x-slot name="message">
                                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                                    </x-slot>
                                                                </x-btn-accion>

                                                                @if($lp->premio_estado == 1)
                                                                    <x-btn-accion class=" text-danger" wire:click="btn_disable('{{ base64_encode($lp->id_premio) }}',0)" data-bs-toggle="modal" data-bs-target="#modal_delete_premio">
                                                                        <x-slot name="message">
                                                                            <i class="fa-solid fa-ban"></i>
                                                                        </x-slot>
                                                                    </x-btn-accion>
                                                                @else
                                                                    <x-btn-accion class=" text-success" wire:click="btn_disable('{{ base64_encode($lp->id_premio) }}',1)" data-bs-toggle="modal" data-bs-target="#modal_delete_premio">
                                                                        <x-slot name="message">
                                                                            <i class="fa-solid fa-check"></i>
                                                                        </x-slot>
                                                                    </x-btn-accion>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @php $conteo++; @endphp
                                                    @endif
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
                    <div wire:loading wire:target="agregarPremio" class="overlay__eliminar">
                        <div class="spinner__container__eliminar">
                            <div class="spinner__eliminar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CAMPAÑAS - PREMIOS -->
        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <label for="id_campania" class="form-label">Seleccionar Campaña</label>
                            <select id="id_campania" class=" form-select" wire:model.live="id_campania">
                                <option value="">Seleccionar...</option>
                                @foreach($listar_campanias as $lc)
                                    <option value="{{ $lc->id_campania }}">{{ $lc->campania_nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
                            <button class="btn bg-success text-white" data-bs-toggle="modal" data-bs-target="#modal_confirmar_premios">Guardar</button>
                        </div>
                    </div>
                    <x-card-general-view>
                        <x-slot name="content">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <x-table-general>
                                        <x-slot name="thead">
                                            <tr>
                                                <th>Código</th>
                                                <th>Descripción</th>
                                                <th>Foto</th>
                                                <th>Puntaje</th>
                                                <th>Quitar</th>
                                            </tr>
                                        </x-slot>

                                        <x-slot name="tbody">
                                            @if(count($listar_campania_premios) > 0)
                                                @foreach($listar_campania_premios as $lcp)
                                                    <tr>
                                                        <td>{{ $lcp->premio_codigo }}</td>
                                                        <td>{{ $lcp->premio_descripcion }}</td>
                                                        <td>
                                                            <img src="{{ asset($lcp->premio_documento) }}" style="width: 200px;" alt="">
                                                        </td>
                                                        <td>
                                                            <input type="text" id="puntaje_premio_{{ $lcp->id_premio }}" onkeyup="validar_numeros(this.id)" class="form-control" placeholder="Puntaje" wire:model="puntajes_premios.{{ $lcp->id_premio }}">
                                                        </td>
                                                        <td>
                                                            <a class="btn btn-lg text-danger" wire:click="quitarPremio('{{ base64_encode($lcp->id_premio) }}')">
                                                                <i class="fa-regular fa-rectangle-xmark"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="odd">
                                                    <td valign="top" colspan="9" class="dataTables_empty text-center">
                                                        No se han agregado premios a la campaña.
                                                    </td>
                                                </tr>
                                            @endif
                                        </x-slot>
                                    </x-table-general>
                                </div>
                            </div>
                        </x-slot>
                    </x-card-general-view>
                    <div wire:loading wire:target="quitarPremio" class="overlay__eliminar">
                        <div class="spinner__container__eliminar">
                            <div class="spinner__eliminar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div wire:loading wire:target="id_campania" class="overlay__eliminar">
            <div class="spinner__container__eliminar">
                <div class="spinner__eliminar"></div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('hide_modal_premio', () => {
        $('#modal_premio').modal('hide');
    });
    $wire.on('hide_modal_detele_premio', () => {
        $('#modal_delete_premio').modal('hide');
    });
    $wire.on('hide_modal_confirmar_premios', () => {
        $('#modal_confirmar_premios').modal('hide');
    });
</script>
@endscript
