<div>
    @php
        $me = new \App\Models\General();
    @endphp
{{--    MODAL DESHABILITAR VENDEDOR--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalDeshabilitarVendedor</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="deshabilitar_vendedor">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageDeshabilitarVendedor}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_vendedor') <span class="message-error">{{ $message }}</span> @enderror

                        @error('vendedor_estado') <span class="message-error">{{ $message }}</span> @enderror

                        @if (session()->has('error_deshabilitar_vendedor'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_deshabilitar_vendedor') }}
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
{{--    FIN MODAL DESHABILITAR VENDEDOR--}}

{{--    MODAL ELIMINAR VENDEDOR--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalEliminarVendedor</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="eliminar_vendedor">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageEliminarVendedor}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_vendedor') <span class="message-error">{{ $message }}</span> @enderror

                        @if (session()->has('error_eliminar_vendedor'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_eliminar_vendedor') }}
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
{{--    FIN MODAL ELIMINAR VENDEDOR--}}

    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalCodigoIntranet</x-slot>
        <x-slot name="titleModal">Generar código intranet</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="guardar_codigo_intranet">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                @if (session()->has('error_codigo_intranet'))
                                    <div class="alert alert-danger alert-dismissible show fade">
                                        {{ session('error_codigo_intranet') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <label for="vendedor_codigo_intranet" class="form-label">Correlativo</label>
                                <div class="input-group">
                                    <span class="input-group-text">VEN</span>
                                    <input type="text"
                                           class="form-control"
                                           id="vendedor_codigo_intranet"
                                           wire:model="vendedor_codigo_intranet"
                                           onkeyup="validar_numeros(this.id)"
                                           placeholder="Ingrese el número correlativo">
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                                <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                                <button type="submit" class="btn btn-success text-white">Guardar Registro</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <h5 class="text-dark">Fecha de última actualización: <strong>{{ $ultimaActualizacion ? $me->obtenerNombreFecha($ultimaActualizacion, 'DateTime', 'DateTime') : '-' }}</strong></h5>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <a class="btn btn-sm  btn-warning text-white fs-6" wire:click="actualizar_vendedores">
                Actualizar
            </a>
        </div>
    </div>

    <div wire:loading wire:target="actualizar_vendedores" class="overlay__eliminar">
        <div class="spinner__container__eliminar">
            <div class="spinner__eliminar"></div>
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
                                <th>Código INTRANET (Autogenerado)</th>
                                <th>Código Vendedor STARSOFT</th>
                                <th>Nombre Vendedor STARSOFT</th>
                                <th>Seg. Terri</th>
                                <th>Ruta</th>
                                <th>Zona</th>
                                <th>Usuario INTRANET</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($vendedores) > 0)
                                @php $a = 1 @endphp
                                @foreach($vendedores as $v)
                                    <tr style="background: {{ $v->vendedor_estado == 2 ? '#ffcccc' : 'transparent' }}">
                                        <td>{{ $a }}</td>
                                        <td>{{ $v->vendedor_codigo_intranet ?? '-' }}</td>
                                        <td>{{ $v->vendedor_codigo_vendedor_starsoft ?? '-' }}</td>
                                        <td>{{ $v->vendedor_des ?? '-' }}</td>
                                        <td>{{ $v->vendedor_seg_terri ?? '-' }}</td>
                                        <td>{{ $v->vendedor_ruta ?? '-' }}</td>
                                        <td>{{ $v->vendedor_codigo_zona ?? '-' }}</td>
                                        <td>{{ $v->vendedor_usuario ?? '-' }}</td>
                                        <td>
                                            @if($v->vendedor_estado == 1)
                                                <x-btn-accion class="text-danger" wire:click="btn_deshabilitar_vendedor('{{ base64_encode($v->id_vendedor) }}',2)" data-bs-toggle="modal" data-bs-target="#modalDeshabilitarVendedor">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @else
                                                <x-btn-accion class="text-success" wire:click="btn_deshabilitar_vendedor('{{ base64_encode($v->id_vendedor) }}',1)" data-bs-toggle="modal" data-bs-target="#modalDeshabilitarVendedor">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-check"></i>
                                                    </x-slot>
                                                </x-btn-accion>
                                            @endif
                                            <x-btn-accion class="text-primary" wire:click="btn_eliminar_vendedor('{{ base64_encode($v->id_vendedor) }}')" data-bs-toggle="modal" data-bs-target="#modalEliminarVendedor">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-trash"></i>
                                                </x-slot>
                                            </x-btn-accion>

                                            @if(empty($v->vendedor_codigo_intranet))
                                                <button class="btn text-info btn-sm mb-2" wire:click="btn_codigo_intranet('{{ base64_encode($v->id_vendedor) }}')" data-bs-toggle="modal" data-bs-target="#modalCodigoIntranet">
                                                    <i class="fa-regular fa-square-plus"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @php $a++ @endphp
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
    {{ $vendedores->links(data: ['scrollTo' => false]) }}
</div>

@script
<script>
    $wire.on('hideModalDeshabilitarVendedor', () => {
        $('#modalDeshabilitarVendedor').modal('hide');
    });

    $wire.on('hideModalEliminarVendedor', () => {
        $('#modalEliminarVendedor').modal('hide');
    });

    $wire.on('hideModalCodigoIntranet', () => {
        $('#modalCodigoIntranet').modal('hide');
    });
</script>
@endscript
