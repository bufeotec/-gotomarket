<div>
    @livewire('gestiontransporte.servicios')

    {{--    MODAL REGISTRO TARIFARIOS--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalTarifario</x-slot>
        <x-slot name="titleModal">Gestionar Tarifarios</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveTarifario">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información de tarifas</small>
                        <hr class="mb-0">
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <label for="id_tipo_servicio" class="form-label">Tipo de servicios (*)</label>
                        <select class="form-select" name="id_tipo_servicios" id="id_tipo_servicio" wire:model.live="id_tipo_servicio">
                            <option value="" disabled>Seleccionar...</option>
                            @foreach($listar_servicios as $li)
                                <option value="{{$li->id_tipo_servicios}}">{{$li->tipo_servicio_concepto}}</option>
                            @endforeach
                        </select>
                        @error('id_tipo_servicio')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    @if($id_tipo_servicio == 1)
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <label for="id_tipo_vehiculo" class="form-label">Tipo de vehículos (*)</label>
                            <select class="form-select" name="id_tipo_vehiculo" id="id_tipo_vehiculo" wire:model="id_tipo_vehiculo">
                                <option value="" >Seleccionar...</option>
                                @foreach($listar_tipovehiculo as $tv)
                                    <option value="{{$tv->id_tipo_vehiculo}}">{{$tv->tipo_vehiculo_concepto}}</option>
                                @endforeach
                            </select>
                            @error('id_tipo_vehiculo')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>
                    @elseif($id_tipo_servicio == 2)
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <div class="" wire:ignore>
                                <label for="id_ubigeo_salida" class="form-label">Ubigeo salida (*)</label>
                                <select class="form-select" name="id_ubigeo" id="id_ubigeo_salida" wire:model="id_ubigeo_salida">
                                    <option value="" >Seleccionar...</option>
                                    @foreach($listar_ubigeos as $lu)
                                        <option value="{{$lu->id_ubigeo}}">{{$lu->ubigeo_departamento . ' - ' . $lu->ubigeo_provincia . ' - ' . $lu->ubigeo_distrito}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('id_ubigeo_salida')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                            <div class="" wire:ignore>
                                <label for="id_ubigeo_llegada" class="form-label">Ubigeo llegada (*)</label>
                                <select class="form-select" name="id_ubigeo_llegada" id="id_ubigeo" wire:model="id_ubigeo_llegada">
                                    <option value="" >Seleccionar...</option>
                                    @foreach($listar_ubigeos as $lu)
                                        <option value="{{$lu->id_ubigeo}}">{{$lu->ubigeo_departamento . ' - ' . $lu->ubigeo_provincia . ' - ' . $lu->ubigeo_distrito}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('id_ubigeo_llegada')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="col-lg-6 col-md-8 col-sm-12 mb-3">
                        <label for="tarifa_cap_min" class="form-label">Capacidad mínima (*) (Capacidad en kg)</label>
                        <x-input-general  type="text" id="tarifa_cap_min" wire:model="tarifa_cap_min"/>
                        @error('tarifa_cap_min')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-8 col-sm-12 mb-3">
                        <label for="tarifa_cap_max" class="form-label">Capacidad máxima (*) (Capacidad en kg)</label>
                        <x-input-general  type="text" id="tarifa_cap_max" wire:model="tarifa_cap_max"/>
                        @error('tarifa_cap_max')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-8 col-sm-12 mb-3">
                        <label for="tarifa_monto" class="form-label">Monto de la tarifa (*)</label>
                        <x-input-general  type="text" id="tarifa_monto" wire:model="tarifa_monto"/>
                        @error('tarifa_monto')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg-6 col-md-8 col-sm-12 mb-3">
                        <label for="tarifa_tipo_bulto" class="form-label">Tipo de bulto (*)</label>
                        <x-input-general  type="text" id="tarifa_tipo_bulto" wire:model="tarifa_tipo_bulto"/>
                        @error('tarifa_tipo_bulto')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
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
                        <button type="submit" class="btn btn-success text-white">Guardar Registro</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
    {{--    FIN MODAL REGISTRO TRANSPORTISTAS--}}

{{--    MODAL DE VER REGISTRO--}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalVerRegistro</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="titleModal">Detalles de los registros actualizados</x-slot>
        <x-slot name="modalContent">
            <x-table-general class="table table-bordered">
                <x-slot name="thead">
                <tr>
                    <th>N°</th>
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

    {{--    MODAL VER DETALLES --}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalVerDetalles</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="titleModal">Detalles de la Tarifa</x-slot>
        <x-slot name="modalContent">
            <div class="container">
                <div class="row">
                    @php
                        // Obtener información del usuario, transportista, etc.
                        $user = \App\Models\User::find($id_users);
                        $nombre_usuario = $user ? $user->name : 'No disponible';

                        $transportista = \App\Models\Transportista::find($id_transportistas);
                        $nombre_transportista = $transportista ? $transportista->transportista_nom_comercial : 'No disponible';

                        $tipo_servicio = \App\Models\TipoServicio::find($id_tipo_servicio);
                        $nombre_tipo_servicio = $tipo_servicio ? $tipo_servicio->tipo_servicio_concepto : 'No disponible';

                        $tipo_vehiculo = \App\Models\TipoVehiculo::find($id_tipo_vehiculo);
                        $nombre_tipo_vehiculo = $tipo_vehiculo ? $tipo_vehiculo->tipo_vehiculo_concepto : 'No disponible';

                        $ubigeo_salida = \App\Models\Ubigeo::find($id_ubigeo_salida);
                        $nombre_ubigeo_salida = $ubigeo_salida ? "{$ubigeo_salida->ubigeo_departamento}, {$ubigeo_salida->ubigeo_provincia}, {$ubigeo_salida->ubigeo_distrito}" : 'No disponible';

                        $ubigeo_llegada = \App\Models\Ubigeo::find($id_ubigeo_llegada);
                        $nombre_ubigeo_llegada = $ubigeo_llegada ? "{$ubigeo_llegada->ubigeo_departamento}, {$ubigeo_llegada->ubigeo_provincia}, {$ubigeo_llegada->ubigeo_distrito}" : 'No disponible';
                    @endphp

                        <!-- Información en columnas de 4 -->
                    <div class="col-lg-4 mb-3">
                        <strong style="color: #8c1017">Apellidos y nombres:</strong>
                        <p>{{ $nombre_usuario }}</p>
                    </div>

                    <div class="col-lg-4 mb-3">
                        <strong style="color: #8c1017">Transportista:</strong>
                        <p>{{ $nombre_transportista }}</p>
                    </div>

                    <div class="col-lg-4 mb-3">
                        <strong style="color: #8c1017">Tipo de Servicio:</strong>
                        <p>{{ $nombre_tipo_servicio }}</p>
                    </div>

                    @if($id_tipo_servicio != 1)
                        <div class="col-lg-4 mb-3">
                            <strong style="color: #8c1017">Ubigeo Salida:</strong>
                            <p>{{ $nombre_ubigeo_salida }}</p>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <strong style="color: #8c1017">Ubigeo Llegada:</strong>
                            <p>{{ $nombre_ubigeo_llegada }}</p>
                        </div>
                    @endif

                    @if($id_tipo_servicio != 2)
                        <div class="col-lg-4 mb-3">
                            <strong style="color: #8c1017">Tipo de Vehículo:</strong>
                            <p>{{ $nombre_tipo_vehiculo }}</p>
                        </div>
                    @endif

                    <div class="col-lg-4 mb-3">
                        <strong style="color: #8c1017">Capacidad Mínima:</strong>
                        <p>{{ $tarifa_cap_min }}</p>
                    </div>

                    <div class="col-lg-4 mb-3">
                        <strong style="color: #8c1017">Capacidad Máxima:</strong>
                        <p>{{ $tarifa_cap_max }}</p>
                    </div>

                    <div class="col-lg-4 mb-3">
                        <strong style="color: #8c1017">Monto de Tarifa:</strong>
                        <p>{{ $tarifa_monto }}</p>
                    </div>

                    <div class="col-lg-4 mb-3">
                        <strong style="color: #8c1017">Tipo de Bulto:</strong>
                        <p>{{ $tarifa_tipo_bulto }}</p>
                    </div>
                </div></div>
        </x-slot>
    </x-modal-general>
    {{--    FIN MODAL VER DETALLES --}}

    {{--    MODAL DELETE--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalDeleteTarifario</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_tarifario">
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
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
{{--            <x-btn-export class="bg-secondary text-white" wire:click="limpiar_nombre_convenio" data-bs-toggle="modal" data-bs-target="#modalServicios">--}}
{{--                <x-slot name="icons">--}}
{{--                    fa-solid fa-plus--}}
{{--                </x-slot>--}}
{{--                Agregar Servicios--}}
{{--            </x-btn-export>--}}
            <x-btn-export wire:click="clear_form_tarifario" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalTarifario" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Tarifario
            </x-btn-export>
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
                                <th>Tipo de servicio</th>
                                <th>Capacidad mínima</th>
                                <th>Capacidad máxima</th>
                                <th>Monto de la tarifa</th>
                                <th>Estado de aprobación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($tarifario) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($tarifario as $ta)
                                    <tr>
                                        <td>{{$conteo}}</td>
                                        <td>{{$ta->tipo_servicio_concepto}}</td>
                                        <td>{{$ta->tarifa_cap_min}} <small class="text-dark">(KG)</small></td>
                                        <td>{{$ta->tarifa_cap_max}} <small class="text-dark">(KG)</small></td>
                                        <td>S/ {{$ta->tarifa_monto}}</td>
                                        <td>
                                            <span class="font-bold badge {{$ta->tarifa_estado_aprobacion == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$ta->tarifa_estado_aprobacion == 1 ? 'Aprobado' : 'Pendiente'}}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-bold badge {{$ta->tarifa_estado == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$ta->tarifa_estado == 1 ? 'Habilitado ' : 'Desabilitado'}}
                                            </span>
                                        </td>

                                        <td>
                                            @php
                                                $rol = \Illuminate\Support\Facades\Auth::user()->roles->first(); // Obtén el primer rol asignado
                                                $rolId = $rol->id; // ID del rol
                                            @endphp
                                            @if($rolId == 1 || $rolId == 2)
                                                <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalTarifario"><x-slot name="message"><i class="fa-solid fa-pen-to-square"></i></x-slot></x-btn-accion>

                                                <x-btn-accion class="text-warning"  wire:click="ver_registro('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalVerRegistro"><x-slot name="message"><i class="fa-solid fa-clock-rotate-left"></i></x-slot></x-btn-accion>

                                                <x-btn-accion style="color: green"  wire:click="ver_detalles('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalVerDetalles"><x-slot name="message"><i class="fa-solid fa-eye"></i></x-slot></x-btn-accion>

                                                @if($ta->tarifa_estado == 1)
                                                    <x-btn-accion class=" text-danger" wire:click="btn_disable('{{ base64_encode($ta->id_tarifario) }}',0)" data-bs-toggle="modal" data-bs-target="#modalDeleteTarifario">
                                                        <x-slot name="message">
                                                            <i class="fa-solid fa-ban"></i>
                                                        </x-slot>
                                                    </x-btn-accion>
                                                @else
                                                    <x-btn-accion class=" text-success" wire:click="btn_disable('{{ base64_encode($ta->id_tarifario) }}',1)" data-bs-toggle="modal" data-bs-target="#modalDeleteTarifario">
                                                        <x-slot name="message">
                                                            <i class="fa-solid fa-check"></i>
                                                        </x-slot>
                                                    </x-btn-accion>
                                                @endif
                                            @else
                                                <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalTarifario"><x-slot name="message"><i class="fa-solid fa-pen-to-square"></i></x-slot></x-btn-accion>
                                            @endif
                                        </td>
                                    </tr>
                                    @php $conteo++; @endphp
                                @endforeach
                            @else
                                <tr class="odd">
                                    <td valign="top" colspan="12" class="dataTables_empty text-center">
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
    {{--    {{ $transportistas->links(data: ['scrollTo' => false]) }}--}}
</div>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalTarifario').modal('hide');
    });
    $wire.on('hideModalDelete', () => {
        $('#modalDeleteTarifario').modal('hide');
    });

    // UBIGEO SALIDA
    $wire.on('select_ubigeo_salida', (data) => {
        const text = data[0].text || null; // Asegúrate de que 'text' sea null si no se envía
        $('#id_ubigeo_salida').select2({
            dropdownParent: $('#modalTarifario .modal-body')
        });
        if(text){
            $('#select2-ubigeo-container').html(text)
        }else{
            $('#select2-ubigeo-container').html('Seleccionar')
        }
        // Sincronizar cambios de Select2 con Livewire
        $('#id_ubigeo_salida').on('change', function () {
            let selectedValue = $(this).val();
            $wire.set('id_ubigeo', selectedValue); // Actualizar modelo de Livewire
        });
    });
    // // Reinicializar Select2 cuando se abra el modal
    window.addEventListener('show-modal', function () {
        $('#id_ubigeo_salida').select2({
            dropdownParent: $('#modalTarifario .modal-body')
        });
    });




    // UBIGEO LLEGADA
    $wire.on('select_ubigeo_llegada', (data) => {
        const text = data[0].text || null; // Asegúrate de que 'text' sea null si no se envía
        $('#id_ubigeo_llegada').select2({
            dropdownParent: $('#modalTarifario .modal-body')
        });
        if(text){
            $('#select2-ubigeo-container').html(text)
        }else{
            $('#select2-ubigeo-container').html('Seleccionar')
        }
        // Sincronizar cambios de Select2 con Livewire
        $('#id_ubigeo_llegada').on('change', function () {
            let selectedValue = $(this).val();
            $wire.set('id_ubigeo', selectedValue); // Actualizar modelo de Livewire
        });
    });
    // // Reinicializar Select2 cuando se abra el modal
    window.addEventListener('show-modal', function () {
        $('#id_ubigeo_llegada').select2({
            dropdownParent: $('#modalTarifario .modal-body')
        });
    });
</script>
@endscript
