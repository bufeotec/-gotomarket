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
                    <!-- Primera columna -->
                    <div class="col-lg-4">
                        @php
                            // Obtener nombre del usuario
                            $user = \App\Models\User::find($id_users);
                            $nombre_usuario = $user ? $user->name : 'No disponible';

                            // Obtener nombre del transportista
                            $transportista = \App\Models\Transportista::find($id_transportistas);
                            $nombre_transportista = $transportista ? $transportista->transportista_nom_comercial : 'No disponible';

                            // Obtener nombre del tipo de servicio
                            $tipo_servicio = \App\Models\TipoServicio::find($id_tipo_servicio);
                            $nombre_tipo_servicio = $tipo_servicio ? $tipo_servicio->tipo_servicio_concepto : 'No disponible';

                            // Obtener nombre del tipo de vehículo
                            $tipo_vehiculo = \App\Models\TipoVehiculo::find($id_tipo_vehiculo);
                            $nombre_tipo_vehiculo = $tipo_vehiculo ? $tipo_vehiculo->tipo_vehiculo_concepto : 'No disponible';
                        @endphp

                        <p><strong>Usuario:</strong> {{ $nombre_usuario }}</p>
                        <p><strong>Transportista:</strong> {{ $nombre_transportista }}</p>
                        <p><strong>Tipo de Servicio:</strong> {{ $nombre_tipo_servicio }}</p>
                        <p><strong>Tipo de Vehículo:</strong> {{ $nombre_tipo_vehiculo }}</p>
                    </div>

                    <!-- Segunda columna -->
                    <div class="col-lg-4">
                        @php
                            // Obtener nombre de ubigeo salida
                                $ubigeo_salida = \App\Models\Ubigeo::find($id_ubigeo_salida);
                                $nombre_ubigeo_salida = $ubigeo_salida ? "{$ubigeo_salida->ubigeo_departamento}, {$ubigeo_salida->ubigeo_provincia}, {$ubigeo_salida->ubigeo_distrito}" : 'No disponible';

                                // Obtener nombre de ubigeo llegada
                                $ubigeo_llegada = \App\Models\Ubigeo::find($id_ubigeo_llegada);
                                $nombre_ubigeo_llegada = $ubigeo_llegada ? "{$ubigeo_llegada->ubigeo_departamento}, {$ubigeo_llegada->ubigeo_provincia}, {$ubigeo_llegada->ubigeo_distrito}" : 'No disponible';
                        @endphp
                        <p><strong>Ubigeo Salida:</strong> {{ $nombre_ubigeo_salida }}</p>
                        <p><strong>Ubigeo Llegada:</strong> {{ $nombre_ubigeo_llegada }}</p>
                        <p><strong>Capacidad Mínima:</strong> {{ $tarifa_cap_min }}</p>
                        <p><strong>Capacidad Máxima:</strong> {{ $tarifa_cap_max }}</p>
                    </div>

                    <!-- Tercera columna -->
                    <div class="col-lg-4">
                        <p><strong>Monto de Tarifa:</strong> {{ $tarifa_monto }}</p>
                        <p><strong>Tipo de Bulto:</strong> {{ $tarifa_tipo_bulto }}</p>
                    </div>
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

                                            <x-btn-accion class="text-warning"  wire:click="ver_registro('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalVerRegistro"><x-slot name="message"><i class="fa-solid fa-hurricane"></i></x-slot></x-btn-accion>

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
