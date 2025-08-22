<div>

{{--    MODAL VER SELECCIÓN--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modal_ver_seleccion</x-slot>
        <x-slot name="titleModal">Canjear Premios - </x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="save_canjear_puntos">
                <div class="row">

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3 text-end">
                        <h6 class="me-3">Puntos Ganados: <b></b></h6>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>N°</th>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                </tr>
                            </x-slot>

                            <x-slot name="tbody">
{{--                                @if(count($menus) > 0)--}}
{{--                                    @php $conteo = 1; @endphp--}}
{{--                                    @foreach($menus as $me)--}}
{{--                                        <tr>--}}
{{--                                            <td>{{$conteo}}</td>--}}
{{--                                            <td></td>--}}
{{--                                            <td></td>--}}
{{--                                            <td></td>--}}
{{--                                        </tr>--}}
{{--                                        @php $conteo++; @endphp--}}
{{--                                    @endforeach--}}
{{--                                @else--}}
{{--                                    <tr class="odd">--}}
{{--                                        <td valign="top" colspan="9" class="dataTables_empty text-center">--}}
{{--                                            No se han encontrado resultados.--}}
{{--                                        </td>--}}
{{--                                    </tr>--}}
{{--                                @endif--}}
                            </x-slot>
                        </x-table-general>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2 text-end">
                        <h6 class="me-3">Puntos Canjeados: <b></b></h6>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2 text-end">
                        <h6 class="me-3">Puntos Restantes: <b></b></h6>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @if (session()->has('error_modal_seleccion'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_modal_seleccion') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Canjear</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
{{--    FIN MODAL VER SELECCIÓN--}}

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
           <select id="id_campania" class=" form-select" wire:model="id_campania">
               <option value="">Seleccionar...</option>
               @foreach($listar_campania as $lc)
                   <option value="{{ $lc->id_campania }}">{{ $lc->campania_nombre }}</option>
               @endforeach
           </select>
       </div>
       <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-3">
           <a class="btn btn-sm bg-success text-white" data-bs-toggle="modal" data-bs-target="#modal_descargar_campania"><i class="fa-solid fa-download"></i> Descargar Archivos</a>
       </div>
       <div class="col-lg-2 col-md-2 col-sm-12 mb-3"></div>
       <div class="col-lg-2 col-md-2 col-sm-12 mb-3"></div>
       <div class="col-lg-2 col-md-2 col-sm-12 mb-3"></div>
       <div class="col-lg-2 col-md-2 col-sm-12 mt-4 mb-3">
           <a class="btn btn-sm bg-success text-white" data-bs-toggle="modal" data-bs-target="#modal_ver_seleccion"><i class="fa-solid fa-eye"></i> Ver Selección</a>
       </div>
   </div>

    <div class="row">
        @foreach($listar_premios_disponibles as $premio)
            <div class="col-lg-3 mt-2 mb-4">
                <div class="border p-1 h-100 text-center">
                    <div class="text-end">
                        <input type="checkbox" class="form-check-input"
                               id="premio_{{ $premio->id_premio }}"
                               value="{{ $premio->id_premio }}">
                    </div>
                    <div class="">
                        <img src="{{ asset($premio->premio_documento) }}"
                             alt="{{ $premio->premio_descripcion }}"
                             class="img-fluid mb-3" style="max-height: 150px;">
                        <h5 class="card-title">{{ $premio->premio_descripcion }}</h5>
                        <p class="text-success">{{ $premio->campania_premio_puntaje }} ptos</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
