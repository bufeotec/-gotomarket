<div>
    @php
        $rol = \Illuminate\Support\Facades\Auth::user()->roles->first(); // Obtén el primer rol asignado
        $rolId = $rol->id; // ID del rol
    @endphp

    @if($rolId == 1 || $rolId == 2)
    {{--    MODAL VER DETALLES --}}
        <x-modal-general wire:ignore.self>
            <x-slot name="id_modal">modalVerDetalles</x-slot>
            <x-slot name="tama">modal-lg</x-slot>
            <x-slot name="titleModal">Detalles de la Tarifa</x-slot>
            <x-slot name="modalContent">
                <div class="container">
                    <div class="row">
                        @if(isset($detalles) && $detalles)
                            @php
                                $nombre_usuario = "No disponible";
                                if ($detalles->id_users){
                                    $user = \App\Models\User::find($detalles->id_users);
                                    $nombre_usuario = $user ? $user->name . ' ' . $user->last_name : '';
                                }
                                $nombre_transportista = "No disponible";
                                if ($detalles->id_transportistas){
                                    $transportista = \App\Models\Transportista::find($detalles->id_transportistas);
                                    $nombre_transportista = $transportista ? $transportista->transportista_nom_comercial.' - '.$transportista->transportista_ruc : '';
                                }

                                $nombre_tipo_servicio = "No disponible";
                                if ($detalles->id_tipo_servicio){
                                    $tipo_servicio = \App\Models\TipoServicio::find($detalles->id_tipo_servicio);
                                    $nombre_tipo_servicio = $tipo_servicio ? $tipo_servicio->tipo_servicio_concepto : 'No disponible';
                                }

                                $nombre_tipo_vehiculo = "";
                                if ($detalles->id_tipo_vehiculo){
                                    $tipo_vehiculo = \App\Models\TipoVehiculo::find($detalles->id_tipo_vehiculo);
                                    $nombre_tipo_vehiculo = $tipo_vehiculo ? $tipo_vehiculo->tipo_vehiculo_concepto : '';
                                }

                                $nombre_ubigeo_salida = "";
                                if ($detalles->id_ubigeo_salida){
                                    $ubigeo_salida = \App\Models\Ubigeo::find($detalles->id_ubigeo_salida);
                                    $nombre_ubigeo_salida = $ubigeo_salida ? "{$ubigeo_salida->ubigeo_departamento}, {$ubigeo_salida->ubigeo_provincia}, {$ubigeo_salida->ubigeo_distrito}" : '';
                                }

                                $nombre_ubigeo_llegada = "";
                                if ($detalles->id_ubigeo_llegada){
                                    $ubigeo_llegada = \App\Models\Ubigeo::find($detalles->id_ubigeo_llegada);
                                    $nombre_ubigeo_llegada = $ubigeo_llegada ? "{$ubigeo_llegada->ubigeo_departamento}, {$ubigeo_llegada->ubigeo_provincia}, {$ubigeo_llegada->ubigeo_distrito}" : '';
                                }

                                $nombre_medida = "";
                                if ($detalles->id_medida){
                                    $media = \App\Models\Medida::find($detalles->id_medida);
                                    $nombre_medida = $media ? $media->medida_nombre : "";
                                }
                            @endphp

                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <h6>Información del transporte</h6>
                                        <hr>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                        <strong style="color: #8c1017">Usuario de registro:</strong>
                                        <p>{{ $nombre_usuario}}</p>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                        <strong style="color: #8c1017">Transportista:</strong>
                                        <p>{{ $nombre_transportista }}</p>
                                    </div>

                                    <div class="col-lg-3 mb-3">
                                        <strong style="color: #8c1017">Servicio:</strong>
                                        <p>{{ $nombre_tipo_servicio }}</p>
                                    </div>

                                    <div class="col-lg-3 mb-3">
                                        <strong style="color: #8c1017">Tipo de cobro:</strong>
                                        <p>{{ $nombre_medida }}</p>
                                    </div>

                                    @if($detalles->id_tipo_servicio == 1)
                                        <div class="col-lg-4 mb-3">
                                            <strong style="color: #8c1017">Tipo de Vehículo:</strong>
                                            <p>{{ $nombre_tipo_vehiculo }}</p>
                                        </div>
                                    @elseif($detalles->id_tipo_servicio == 2)
                                        <div class="col-lg-3 mb-3">
                                            <strong style="color: #8c1017">Ubigeo Salida:</strong>
                                            <p>{{ $nombre_ubigeo_salida }}</p>
                                        </div>
                                        <div class="col-lg-3 mb-3">
                                            <strong style="color: #8c1017">Ubigeo Llegada:</strong>
                                            <p>{{ $nombre_ubigeo_llegada }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>


                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <h6>Capacidad y Costos</h6>
                                        <hr>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <strong style="color: #8c1017">Capacidad Mínima:</strong>
                                        <p>{{ isset($detalles->tarifa_cap_min) ? number_format($detalles->tarifa_cap_min, 2, '.', ',') : 'No disponible' }}
                                            <small>
                                                {{ $detalles->id_medida == 23 ? 'Kg' : ($detalles->id_medida == 9 ? 'cm³' : '') }}
                                            </small>
                                        </p>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <strong style="color: #8c1017">Capacidad Máxima:</strong>
                                        <p>{{ isset($detalles->tarifa_cap_max) ? number_format($detalles->tarifa_cap_max, 2, '.', ',') : 'No disponible' }}
                                            <small>
                                                {{ $detalles->id_medida == 23 ? 'Kg' : ($detalles->id_medida == 9 ? 'cm³' : '') }}
                                            </small>
                                        </p>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <strong style="color: #8c1017">Monto de Tarifa:</strong>
                                        <p>{{ isset($detalles->tarifa_monto) ? 'S/ ' . number_format($detalles->tarifa_monto, 2, '.', ',') : 'No disponible' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </x-slot>
        </x-modal-general>
        {{--    FIN MODAL VER DETALLES --}}

        {{--    MODAL DE VER REGISTRO--}}
        <x-modal-general wire:ignore.self>
            <x-slot name="id_modal">modalVerRegistro</x-slot>
            <x-slot name="tama">modal-lg</x-slot>
            <x-slot name="titleModal">Detalles de los registros</x-slot>
            <x-slot name="modalContent">
                <x-table-general class="table table-bordered">
                    <x-slot name="thead">
                        <tr>
                            <th>N°</th>
                            <th>Usuario</th>
                            <th>Concepto</th>
                            <th>Fecha y Hora</th>
                        </tr>
                    </x-slot>
                    <x-slot name="tbody">
                        @if(count($historial_registros) > 0)
                            @php $conteo = 1; @endphp
                            @foreach ($historial_registros as $registro)
                                <tr>
                                    <td>{{ $conteo }}</td>
                                    <td>{{ $registro->name }}</td>
                                    <td>{{ $registro->registro_concepto }}</td>
                                    <td>{{ $registro->registro_hora_fecha }}</td>
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
            </x-slot>
        </x-modal-general>
        {{--    FIN MODAL DE VER REGISTRO--}}

    {{--    MODAL VALIDAR TARIFA --}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalValidarTarifario</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="aprobar_tarifa">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageValidarAprobacion}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_tarifario') <span class="message-error">{{ $message }}</span> @enderror

                        @error('tarifa_estado_aprobacion') <span class="message-error">{{ $message }}</span> @enderror

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
    {{--    FIN MODAL VALIDAR TARIFA --}}

{{--    MODAL DELETE--}}

    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalDeleteTarifario</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_tarifario_validar">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageDeleteTarifario}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_tarifario') <span class="message-error">{{ $message }}</span> @enderror

                        @error('tarifa_estado') <span class="message-error">{{ $message }}</span> @enderror

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
                <input type="text" class="form-control w-50 me-4"  wire:model.live="search_tarifario" placeholder="Buscar">
                <x-select-filter wire:model.live="pagination_tarifario" />
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
                                <th>Usuario</th>
                                <th>Transportista</th>
                                <th>Tipo de servicio</th>
                                <th>Ubigeo salida</th>
                                <th>Ubigeo llegada</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($tarifario) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($tarifario as $ta)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$ta->name}}</td>
                                        <td>{{$ta->transportista_nom_comercial}}</td>
                                        <td>{{$ta->tipo_servicio_concepto}}</td>
                                        <!-- Ubigeo salida -->
                                        <td>
                                            {{ ($ta->salida_departamento ?? '') . ' - ' . ($ta->salida_provincia ?? '') . ' - ' . ($ta->salida_distrito ?? '') }}
                                        </td>
                                        <!-- Ubigeo llegada -->
                                        <td>
                                            {{ ($ta->llegada_departamento ?? '') . ' - ' . ($ta->llegada_provincia ?? '') . ' - ' . ($ta->llegada_distrito ?? '') }}
                                        </td>
                                        <td>
                                            <x-btn-accion class=" text-primary"  wire:click="ver_detalle('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalVerDetalles"><x-slot name="message"><i class="fa-solid fa-eye"></i></x-slot></x-btn-accion>

                                            <x-btn-accion style="color: green" class=" text-green"  wire:click="btn_validar_aprobacion('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalValidarTarifario"><x-slot name="message"><i class="fa-solid fa-check"></i></x-slot></x-btn-accion>

                                            <x-btn-accion class="text-warning"  wire:click="ver_registro('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalVerRegistro"><x-slot name="message"><i class="fa-solid fa-clock-rotate-left"></i></x-slot></x-btn-accion>

                                        @if($ta->tarifa_estado == 1)
                                                <x-btn-accion class=" text-danger" wire:click="btn_disable('{{ base64_encode($ta->id_tarifario) }}',0)" data-bs-toggle="modal" data-bs-target="#modalDeleteTarifario">
                                                    <x-slot name="message">
                                                        <i class="fa-solid fa-ban"></i>
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
    @else
        {{-- Si el rol no es 1 ni 2, mostrar el mensaje "Hola" --}}
        <div class="col-lg-12">
            <h5 class="text-danger">Hola, no tienes permiso para ver esta tabla.</h5>
        </div>
    @endif
    {{--    {{ $transportistas->links(data: ['scrollTo' => false]) }}--}}
</div>

@script
<script>
    $wire.on('hideModalDelete', () => {
        $('#modalValidarTarifario').modal('hide');
    });

    $wire.on('hideModalDelete', () => {
        $('#modalDeleteTarifario').modal('hide');
    });
</script>
@endscript
