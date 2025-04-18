<div>
    @livewire('gestiontransporte.servicios')

    @php
        $general = new \App\Models\General();
    @endphp

    {{--    MODAL REGISTRO TARIFARIOS--}}
    <x-modal-general  wire:ignore.self >
        <x-slot name="id_modal">modalTarifario</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="titleModal">Gestionar Tarifarios</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveTarifario">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <small class="text-primary">Información de tarifas</small>
                        <hr class="mb-0">
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-12 col-sm-12 mb-4">
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
                            <div class="col-lg-6 col-md-12 col-sm-12 mb-4">
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
                        @endif
                    </div>

                    @if($id_tipo_servicio == 2)
                        <div class="row">
                            <div class="col-lg-6 col-md-12 col-sm-12 mb-4">
                                <div class="" wire:ignore>
                                    <label for="id_ubigeo_salida" class="form-label">Ubigeo salida (*)</label>
                                    <select class="form-select" name="id_ubigeo_salida" id="id_ubigeo_salida" wire:model="id_ubigeo_salida">
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

                            <div class="col-lg-6 col-md-12 col-sm-12 mb-4">
                                <label for="id_medida" class="form-label">Unidad de medida (*)</label>
                                <select class="form-select" name="id_medida" id="id_medida" wire:model.live="id_medida">
                                    <option value="">Seleccionar...</option>
                                    <option value="23"> Peso </option>
                                    <option value="9"> Volumen </option>
                                </select>
                                @error('id_medida')
                                <span class="message-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-4">
                                <label for="id_departamento" class="form-label">Departamento llegada (*)</label>
                                <select class="form-select" name="id_departamento" id="id_departamento" wire:model="id_departamento" wire:change="deparTari">
                                    <option value="">Seleccionar...</option>
                                    @foreach($listar_departamento as $de)
                                        <option value="{{ $de->id_departamento }}">{{ $de->departamento_nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_departamento') <span class="message-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-lg-4 col-md-4 col-sm-12 mb-4">
                                <label for="id_provincia" class="form-label">Provincia llegada (*)</label>
                                <select class="form-select" name="id_provincia" id="id_provincia" wire:model="id_provincia"  wire:change="proviTari" {{ empty($provincias) ? 'disabled' : '' }}>
                                    <option value="">Seleccionar...</option>
                                    @foreach($provincias as $pr)
                                        <option value="{{ $pr->id_provincia }}" {{ $pr->id_provincia == $id_provincia ? 'selected' : '' }}>{{ $pr->provincia_nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_provincia') <span class="message-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-lg-4 col-md-4 col-sm-12 mb-4">
                                <label for="id_distrito" class="form-label">Distrito llegada</label>
                                <select class="form-select" name="id_distrito" id="id_distrito" wire:model="id_distrito" {{ empty($distritos) ? 'disabled' : '' }}>
                                    <option value="">Todos los distritos</option>
                                    @foreach($distritos as $di)
                                        <option value="{{ $di->id_distrito }}" {{ $di->id_distrito == $id_distrito ? 'selected' : '' }}>{{ $di->distrito_nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_distrito') <span class="message-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    @php
                        if ($id_tipo_servicio == 2) {
                            $capacidadTexto = ($id_medida == 23) ? '(Capacidad en kg)' : (($id_medida == 9) ? '(Capacidad en cm³)' : '');
                        } else {
                            $capacidadTexto = '';
                        }
                    @endphp

                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <label for="tarifa_cap_min" class="form-label">Capacidad mínima (*) {{ $capacidadTexto }}</label>
                            <x-input-general type="text" id="tarifa_cap_min" wire:model="tarifa_cap_min" onkeyup="validar_numeros(this.id)" />
                            @error('tarifa_cap_min')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <label for="tarifa_cap_max" class="form-label">Capacidad máxima (*) {{ $capacidadTexto }}</label>
                            <x-input-general type="text" id="tarifa_cap_max" wire:model="tarifa_cap_max" onkeyup="validar_numeros(this.id)" />
                            @error('tarifa_cap_max')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>

                        @php
                            $cobroViaje = ($id_tipo_servicio == 1) ? '/ Cobro por viaje' : '';
                        @endphp

                        <div class="col-lg-4 col-md-12 col-sm-12 mb-4">
                            <label for="tarifa_monto" class="form-label">Monto de la tarifa sin IGV (*) {{ $cobroViaje }}</label>
                            <x-input-general type="text" id="tarifa_monto" wire:model="tarifa_monto" onkeyup="validar_numeros(this.id)" />
                            @error('tarifa_monto')
                            <span class="message-error">{{ $message }}</span>
                            @enderror
                        </div>
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

                            $departamento = \App\Models\Departamento::find($detalles->id_departamento);
                            $nombre_departamento = $departamento ? $departamento->departamento_nombre : 'Sin departamento';

                            $provincia = \App\Models\Provincia::find($detalles->id_provincia);
                            $nombre_provincia = $provincia ? $provincia->provincia_nombre : 'Sin provincia';

                            if ($detalles->id_distrito) {
                                $distrito = \App\Models\Distrito::where('id_distrito', $detalles->id_distrito)->first();
                            } else {
                                $distrito = null;
                            }

                            $nombre_medida = "";
                            if ($detalles->id_medida){
                                $media = \App\Models\Medida::find($detalles->id_medida);
                                if ($media){
                                    $nombre_medida =  $media->id_medida == 23 ? 'PESO' : "VOLUMEN";
                                }
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

                                <div class="col-lg-4 mb-3">
                                    <strong style="color: #8c1017">Servicio:</strong>
                                    <p>{{ $nombre_tipo_servicio }}</p>
                                </div>

                                @if($detalles->id_tipo_servicio == 2)
                                    <div class="col-lg-4 mb-3">
                                        <strong style="color: #8c1017">Unidad de medida:</strong>
                                        <p>{{ $nombre_medida }}</p>
                                    </div>
                                @endif

                                @if($detalles->id_tipo_servicio == 1)
                                    <div class="col-lg-4 mb-3">
                                        <strong style="color: #8c1017">Tipo de Vehículo:</strong>
                                        <p>{{ $nombre_tipo_vehiculo }}</p>
                                    </div>
                                @elseif($detalles->id_tipo_servicio == 2)
                                    <div class="col-lg-4 mb-3">
                                        <strong style="color: #8c1017">Ubigeo Salida:</strong>
                                        <p>{{ $nombre_ubigeo_salida }}</p>
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        <div class="row">
                                            <div class="col-lg-12">
{{--                                                <strong style="color: #8c1017">Ubigeo Llegada</strong>--}}
                                                <h6>Ubigeo Llegada</h6>
                                            </div>
                                            <div class="col-lg-4">
                                                <strong style="color: #8c1017">Departamento:</strong>
                                                <p>{{ $nombre_departamento }}</p>
                                            </div>
                                            <div class="col-lg-4">
                                                <strong style="color: #8c1017">Provincia:</strong>
                                                <p>{{ $nombre_provincia }}</p>
                                            </div>
                                            <div class="col-lg-4">
                                                <strong style="color: #8c1017">Distrito:</strong>
                                                @if($distrito)
                                                    <p>{{ $distrito->distrito_nombre }}</p>
                                                @else
                                                    <p>TODOS LOS DISTRITOS</p>
                                                @endif
                                            </div>
                                        </div>
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
                                    <p>
                                        @php
                                            $capacidadMinima = "0";
                                            if ($detalles->tarifa_cap_min){
                                                $capacidadMinima = $general->formatoDecimal($detalles->tarifa_cap_min);
                                            }
                                        @endphp
                                        {{ isset($detalles->tarifa_cap_min) ? $capacidadMinima : 'No disponible' }}
                                        <small>
                                            {{ $detalles->id_tipo_servicio == 1 ? 'Kg' : ($detalles->id_tipo_servicio == 2 ? ($detalles->id_medida == 23 ? 'Kg' : ($detalles->id_medida == 9 ? 'cm³' : '')) : '') }}
                                        </small>
                                    </p>
                                </div>

                                <div class="col-lg-4 mb-3">
                                    <strong style="color: #8c1017">Capacidad Máxima:</strong>
                                    <p>
                                        @php
                                            $capacidadMaxima = "0";
                                            if ($detalles->tarifa_cap_max){
                                                $capacidadMaxima = $general->formatoDecimal($detalles->tarifa_cap_max);
                                            }
                                        @endphp
                                        {{ isset($detalles->tarifa_cap_max) ? $capacidadMaxima : 'No disponible' }}
                                        <small>
                                            {{ $detalles->id_tipo_servicio == 1 ? 'Kg' : ($detalles->id_tipo_servicio == 2 ? ($detalles->id_medida == 23 ? 'Kg' : ($detalles->id_medida == 9 ? 'cm³' : '')) : '') }}
                                        </small>
                                    </p>
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <strong style="color: #8c1017">Monto de Tarifa sin IGV:</strong>
                                    <p>
                                        @php
                                            $monto = "0";
                                            if ($detalles->tarifa_monto){
                                                $monto = $general->formatoDecimal($detalles->tarifa_monto);
                                            }
                                        @endphp
                                        {{ isset($detalles->tarifa_monto) ? 'S/ ' . $monto : 'No disponible' }}
                                    </p>
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
        <x-slot name="titleModal">Detalles de los registros actualizados</x-slot>
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
                                <td>{{ $registro->name }} {{ $registro->last_name }}</td>
                                <td>{{ $registro->registro_concepto }}</td>
                                <td>
                                    @php
                                        $fe = new \App\Models\General();
                                        $feFor = "";
                                        if ($registro->registro_hora_fecha){
                                            $feFor = $fe->obtenerNombreFecha($registro->registro_hora_fecha,'DateTime','DateTime');
                                        }
                                    @endphp
                                    {{ $feFor }}
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
        </x-slot>
    </x-modal-general>
    {{--    FIN MODAL DE VER REGISTRO--}}

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
            <a class="btn bg-white text-dark create-new ms-3" href="{{route('Tarifario.fletes')}}" >
                <span>
                    <i class="fa-solid fa-arrow-left me-sm-1"></i>
                    <span class="d-none d-sm-inline-block">Regresar</span>
                </span>
            </a>

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
                                <th>Unidad de medida</th>
                                <th>Capacidad mínima</th>
                                <th>Capacidad máxima</th>
                                <th>Monto de la tarifa sin IGV</th>
                                @if($tarifario->contains('id_tipo_servicio', 2))
                                    <th>Departamento - Provincia</th>
                                @endif
                                <th>Estado aprobación</th>
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
                                        <td>{{ $ta->tipo_servicio_concepto }}</td>
                                        <td>{{ is_null($ta->id_medida) ? '-' : ($ta->id_medida == 23 ? 'PESO' : 'VOLUMEN') }}</td>
                                        <td>
                                            {{ $general->formatoDecimal($ta->tarifa_cap_min) }}
                                            <small class="text-dark">
                                                {{ $ta->id_tipo_servicio == 1 ? '(Kg)' : ($ta->id_tipo_servicio == 2 ? ($ta->id_medida == 9 ? '(cm³)' : ($ta->id_medida == 23 ? '(Kg)' : '')) : '') }}
                                            </small>
                                        </td>
                                        <td>
                                            {{ $general->formatoDecimal($ta->tarifa_cap_max) }}
                                            <small class="text-dark">
                                                {{ $ta->id_tipo_servicio == 1 ? '(Kg)' : ($ta->id_tipo_servicio == 2 ? ($ta->id_medida == 9 ? '(cm³)' : ($ta->id_medida == 23 ? '(Kg)' : '')) : '') }}
                                            </small>
                                        </td>
                                        <td>
                                            S/ {{ $general->formatoDecimal($ta->tarifa_monto) }}
                                            <small class="text-dark">
                                                {{ $ta->id_tipo_servicio == 1 ? '/ VIAJE' : ($ta->id_tipo_servicio == 2 ? '/ kg' : '') }}
                                            </small>
                                        </td>
                                        @if($ta->id_tipo_servicios == 2)
                                            <td>
                                                @if($ta->id_tipo_servicios == 2)
                                                    @php
                                                        $departamento = \Illuminate\Support\Facades\DB::table('departamentos')
                                                        ->where('id_departamento','=',$ta->id_departamento)->first();
                                                        $provincia = \Illuminate\Support\Facades\DB::table('provincias')
                                                        ->where('id_provincia','=',$ta->id_provincia)->first();
                                                    @endphp
                                                    <div class="col-lg-5 col-md-3 col-sm-4 mb-3">
                                                        <p>{{ $departamento ? $departamento->departamento_nombre : '' }} - {{ $provincia ? $provincia->provincia_nombre : '' }}</p>
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            <span class="font-bold badge {{$ta->tarifa_estado_aprobacion == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$ta->tarifa_estado_aprobacion == 1 ? 'Aprobado ' : 'Pendiente'}}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-bold badge {{$ta->tarifa_estado == 1 ? 'bg-label-success ' : 'bg-label-danger'}}">
                                                {{$ta->tarifa_estado == 1 ? 'Habilitado ' : 'Desabilitado'}}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $rol = \Illuminate\Support\Facades\Auth::user()->roles->first();
                                                $rolId = $rol->id; // ID del rol
                                            @endphp
                                            <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalTarifario"><x-slot name="message"><i class="fa-solid fa-pen-to-square"></i></x-slot></x-btn-accion>

                                            <x-btn-accion style="color: green"  wire:click="ver_detalles('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalVerDetalles"><x-slot name="message"><i class="fa-solid fa-eye"></i></x-slot></x-btn-accion>

                                            @if($rolId == 1 || $rolId == 2)
                                                <x-btn-accion class="text-warning"  wire:click="ver_registro('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalVerRegistro"><x-slot name="message"><i class="fa-solid fa-clock-rotate-left"></i></x-slot></x-btn-accion>
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
{{--                                                <x-btn-accion class=" text-primary"  wire:click="edit_data('{{ base64_encode($ta->id_tarifario) }}')" data-bs-toggle="modal" data-bs-target="#modalTarifario"><x-slot name="message"><i class="fa-solid fa-pen-to-square"></i></x-slot></x-btn-accion>--}}
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
    {{ $tarifario->links(data: ['scrollTo' => false]) }}
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
