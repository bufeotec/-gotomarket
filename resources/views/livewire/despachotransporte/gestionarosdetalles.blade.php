<div>
    @php
        $general = new \App\Models\General();
    @endphp
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
        <div wire:loading wire:target="editar_gestionar_os, actualizar_despacho_os" class="overlay__eliminar">
            <div class="spinner__container__eliminar">
                <div class="spinner__eliminar"></div>
            </div>
        </div>
    </div>

{{--    MODAL ANULAR OS--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modal_anular_os</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="anular_os">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">¿Estas seguro de anular esta OS?</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_despacho') <span class="message-error">{{ $message }}</span> @enderror

                        @if (session()->has('error_anular_os'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_anular_os') }}
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
{{--    FIN MODAL ANULAR OS--}}

{{--    MODAL FECHA ENTREGA--}}
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modal_fecha_entrega</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="fecha_entrega">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <h2 class="deleteTitle">Programar Entrega</h2>
                    </div>

                    <div class="col-lg-2"></div>
                    <div class="col-lg-8 col-md-8 col-sm-12 mb-3">
                        <input type="date" class="form-control" id="despacho_fecha_entrega" wire:model.live="despacho_fecha_entrega" wire:change="validar_fecha" />
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        @error('id_despacho') <span class="message-error">{{ $message }}</span> @enderror

                        @if (session()->has('error_fecha_entrega'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                {{ session('error_fecha_entrega') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-3 text-center">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-danger btnDelete">No</button>
                        <button type="submit" class="btn btn-primary text-white btnDelete">SI</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-delete>
{{--    FIN MODAL FECHA ENTREGA--}}

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
            <div class="row align-items-center">
                <div class="col-lg-4 d-flex align-items-center">
                    <img src="{{asset('isologoCompleteGo.png')}}" style="height: 40px;width: 40px" alt="Logo" srcset="">
                    <h3 class="mb-0 font-weight-bold ms-2" style="font-weight: 800!important;">Go To Market</h3>
                </div>
                <div class="col-lg-4">
                    <h5 class="d-flex text-center align-items-center">
                        ORDEN DE SERVICIO DE TRANSPORTE DE MERCADERÍA
                    </h5>
                </div>
                <div class="col-lg-4 text-center">
                    <h5>N° {{$numero_os ?? '-'}}</h5>
                </div>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="actualizar_despacho_os">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3 text-end">
                <a class="btn btn-sm bg-primary text-white ms-2" wire:click="editar_gestionar_os"><i class="fa-solid fa-pen-to-square"></i> {{ $editando ? 'Cancelar' : 'Editar' }}</a>
                <button type="submit" class="btn btn-sm bg-success text-white ms-2" {{ !$editando ? 'disabled' : '' }}><i class="fa-solid fa-check"></i> Aprobar</button>
                <a class="btn btn-sm bg-warning text-black ms-2" wire:click="listar_guias_despachos('{{ base64_encode($listar_info->id_despacho)}}')" data-bs-toggle="modal" data-bs-target="#modal_anular_os"><i class="fa-solid fa-ban"></i> Anular</a>
                @if(!empty($listar_info->despacho_numero_correlativo))
                    <a class="btn btn-sm bg-danger text-white ms-2" href="{{route('Despachotransporte.generar_pdf_os',['id_despacho'=>$listar_info->id_despacho])}}" target="_blank"><i class="fa-solid fa-file-pdf"></i> Descargar OS</a>
                @endif
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Datos Solicitante</h5>
                            </div>
                            <div class="card-body mt-3">
                                <p>GO TO MARKET SAC</p>
                                <p>RUC <strong>20537638045</strong></p>
                                <p>CAL.CALLE 1 MZA. X LOTE. 4V INT. C COO. LAS VERTIENTES DE TABLADA DE LURÍN LIMA - LIMA - VILLA EL SALVADOR</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Datos de la OS</h5>
                            </div>
                            <div class="card-body mt-3">
                                <div class="mt-2">
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 mb-3">
                                            <p class="ms-2">Fecha de Aprobación: <b>{{ $listar_info->despacho_fecha_aprobacion ? $general->obtenerNombreFecha($listar_info->despacho_fecha_aprobacion, 'Date', 'Date') : '-' }}</b></p>
                                        </div>
                                        <div class="col-lg-6 mb-3">
                                            @if($editando)
                                                <label for="programacion_fecha_edit" class="form-label">Fecha Inicio</label>
                                                <input type="date" class="form-control" id="programacion_fecha_edit" wire:model.live="programacion_fecha_edit" />
                                            @else
                                                <p class="ms-2">Fecha de Inicio de Servicio: <b>{{ $listar_info->programacion_fecha ? $general->obtenerNombreFecha($listar_info->programacion_fecha, 'Date', 'Date') : '-' }}</b></p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 mb-3">
                                            <p class="ms-2">Plazo de Entrega: <b>{{ $tarifa_tiempo_transporte ? $tarifa_tiempo_transporte . ' días hábiles' : '-' }}</b></p>
                                        </div>
                                        <div class="col-lg-6 mb-3">
                                            <p class="ms-2">Fecha de Entrega Esperada:
                                                <b>{{ $fecha_entrega_espera !== '-' ? $general->obtenerNombreFecha($fecha_entrega_espera, 'Date', 'Date') : '-' }}</b>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Datos del Proveedor</h5>
                            </div>
                            <div class="card-body mt-3">
                                @if($editando)
                                    <select wire:model="transportista_seleccionado" id="transportista_seleccionado" name="transportista_seleccionado" wire:change="actualizar_transportista" class="form-select mb-3">
                                        <option value="">Seleccione un transportista</option>
                                        @foreach($transportistas as $transportista)
                                            <option value="{{$transportista->id_transportistas}}" {{ $transportista_seleccionado == $transportista->id_transportistas ? 'selected' : '' }}>
                                                {{ $transportista->transportista_razon_social }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <p>{{ $listar_info->transportista_razon_social }}</p>
                                @endif

                                <p>RUC <strong>{{ $editando ? ($transportista_actual->transportista_ruc ?? '') : $listar_info->transportista_ruc }}</strong></p>
                                <p>{{ $editando ? ($transportista_actual->transportista_direccion ?? '') : $listar_info->transportista_direccion }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Acuerdos Comerciales de la OS</h5>
                            </div>
                            <div class="card-body mt-3">
                                <div class="row">
                                    <div class="col-lg-6 mb-3">
                                        @if($editando)
                                            <label for="despacho_referencia_acuerdo_comercial" class="form-label">Referencia:</label>
                                            <textarea class="form-control" rows="2" id="despacho_referencia_acuerdo_comercial" wire:model.live="despacho_referencia_acuerdo_comercial"></textarea>
                                        @else
                                            <p>Referencia: <b>{{$listar_info->despacho_referencia_acuerdo_comercial}}</b></p>
                                        @endif
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <p>Contacto Comercial: <b>{{$transportista_actual->transportista_contacto_uno_comercial_operativo}}</b></p>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        @php
                                            $conformidad_factura = "";
                                            if ($listar_info->despacho_conformidad_factura == 1){
                                                $conformidad_factura = "Anticipado";
                                            } elseif ($listar_info->despacho_conformidad_factura == 2) {
                                                $conformidad_factura = "Después de Entrega";
                                            } else {
                                                $conformidad_factura = "-";
                                            }
                                        @endphp

                                        @if($editando)
                                            <label for="despacho_conformidad_factura" class="form-label">Conformidad de Factura:</label>
                                            <select class="form-control" id="despacho_conformidad_factura" wire:model.live="despacho_conformidad_factura">
                                                <option value="">Seleccionar...</option>
                                                <option value="1">Anticipado</option>
                                                <option value="2">Después de Entrega</option>
                                            </select>
                                        @else
                                            <p>Conformidad de Factura: <b>{{$conformidad_factura}}</b></p>
                                        @endif
                                    </div>
                                    <div class="col-lg-6 mb-3"></div>
                                    <div class="col-lg-6 mb-3">
                                        @php
                                            $modo_pago = "";
                                            if ($listar_info->despacho_modo_pago_factura == 1){
                                                $modo_pago = "Contado";
                                            } elseif ($listar_info->despacho_modo_pago_factura == 2) {
                                                $modo_pago = "Crédito";
                                            } else {
                                                $modo_pago = "-";
                                            }
                                        @endphp

                                        @if($editando)
                                            <label for="despacho_modo_pago_factura" class="form-label">Modo de Pago:</label>
                                            <select class="form-control" id="despacho_modo_pago_factura" wire:model.live="despacho_modo_pago_factura">
                                                <option value="">Seleccionar...</option>
                                                <option value="1">Contado</option>
                                                <option value="2">Crédito</option>
                                            </select>
                                        @else
                                            <p>Modo de Pago: <b>{{$modo_pago}}</b></p>
                                        @endif
                                    </div>
                                    <div class="col-lg-6 mb-3"></div>
                                    <div class="col-lg-6 mb3">
                                        @if($editando)
                                            <label for="despacho_garantias_servicio" class="form-label">Garantías del Servicio:</label>
                                            <textarea class="form-control" rows="2" id="despacho_garantias_servicio" wire:model.live="despacho_garantias_servicio"></textarea>
                                        @else
                                            <p>Garantías del Servicio: <b>{{$listar_info->despacho_garantias_servicio}}</b></p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8 mb-3">
                        <div class="card">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Resumen General de la OS</h5>
                            </div>
                            <div class="card-body mt-3">
                                <div class="row">
                                    <div class="col-lg-2 col-md-3 col-sm-4 mb-2">
                                        <strong class="colorgotomarket mb-2">Tipo de Servicio</strong>
                                        <p>{{ $listar_info->tipo_servicio_concepto }}</p>
                                    </div>
                                    @if($listar_info->id_tipo_servicios == 2)
                                        @php
                                            $departamento = \Illuminate\Support\Facades\DB::table('departamentos')
                                            ->where('id_departamento','=',$listar_info->id_departamento)->first();
                                            $provincia = \Illuminate\Support\Facades\DB::table('provincias')
                                            ->where('id_provincia','=',$listar_info->id_provincia)->first();
                                            $distrito = \Illuminate\Support\Facades\DB::table('distritos')
                                            ->where('id_distrito','=',$listar_info->id_distrito)->first();
                                        @endphp
                                        <div class="col-lg-5 col-md-3 col-sm-4 mb-2">
                                            <strong class="colorgotomarket mb-2">Ubigeo del Servicio:</strong>
                                            <p>{{ $departamento ? $departamento->departamento_nombre : '' }} - {{ $provincia ? $provincia->provincia_nombre : '' }} - {{ $distrito ? $distrito->distrito_nombre : 'TODOS LOS DISTRITOS' }}</p>
                                        </div>
                                    @endif
                                    @if($listar_info->id_vehiculo)
                                        @php
                                            $vehiculo = \Illuminate\Support\Facades\DB::table('vehiculos')->where('id_vehiculo','=',$listar_info->id_vehiculo)->first();
                                        @endphp
                                        <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                                            <strong class="colorgotomarket mb-2">Capacidad del Vehículo:</strong>
                                            <p>{{ $general->formatoDecimal($vehiculo->vehiculo_capacidad_peso) }} Kg</p>
                                        </div>
                                    @endif
                                    @if($listar_info->id_tarifario)
                                        <div class="col-lg-3 col-md-3 col-sm-4 mb-2">
                                            <strong class="colorgotomarket mb-2">Capacidad de la Tarifa:</strong>
                                            <p>Min: {{$general->formatoDecimal($listar_info->despacho_cap_min)}} Kg - Max: {{ $general->formatoDecimal($listar_info->despacho_cap_max) }} Kg</p>
                                        </div>
                                    @endif
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-2">
                                        <strong class="colorgotomarket mb-2">Peso Despacho:</strong>
                                        <p>{{$general->formatoDecimal($listar_info->despacho_peso)}} Kg</p>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-2">
                                        <strong class="colorgotomarket mb-2">Volumen Despacho:</strong>
                                        <p>{{$general->formatoDecimal($listar_info->despacho_volumen)}} Kg</p>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-2">
                                        <strong class="colorgotomarket mb-2">Monto de la Tarifa:</strong>
                                        @if($editando)
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">S/</span>
                                                <input type="text" id="despacho_flete" wire:model="despacho_flete" wire:change="calcularTotales" class="form-control" onkeyup="validar_numeros(this.id)" />
                                            </div>
                                        @else
                                            <p>S/ {{ $general->formatoDecimal($listar_info->despacho_flete) }}</p>
                                        @endif
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-2">
                                        <strong class="colorgotomarket mb-2">Otros Gastos:</strong>
                                        @if($editando)
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">S/</span>
                                                <input type="text" id="despacho_gasto_otros" wire:model="despacho_gasto_otros" wire:change="calcularTotales" class="form-control" onkeyup="validar_numeros(this.id)" />
                                            </div>
                                        @else
                                            <p>S/ {{ $general->formatoDecimal($listar_info->despacho_gasto_otros) }}</p>
                                        @endif
                                    </div>
                                    @if($listar_info->id_tipo_servicios == 1)
                                        <div class="col-lg-2 col-md-3 col-sm-12 mb-2">
                                            <strong class="colorgotomarket mb-2">Mano de Obra:</strong>
                                            @if($editando)
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="text" id="despacho_ayudante" wire:model="despacho_ayudante" wire:change="calcularTotales" class="form-control" onkeyup="validar_numeros(this.id)" />
                                                </div>
                                            @else
                                                <p>S/ {{ $general->formatoDecimal($listar_info->despacho_ayudante) }}</p>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-2">
                                        <strong class="colorgotomarket mb-2">Total del Servicio sin IGV:</strong>
                                        <p>S/ {{ $general->formatoDecimal($editando ? $despacho_costo_total_sin_igv : $listar_info->despacho_costo_total) }}</p>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-2">
                                        <strong class="colorgotomarket mb-2">Total del Servicio con IGV:</strong>
                                        <p>S/ {{ $general->formatoDecimal($editando ? $despacho_costo_total_con_igv : $listar_info->despacho_costo_total * (1 + $igv)) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Datos Internos de la OS</h5>
                            </div>
                            <div class="card-body mt-3">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                        @php
                                            $ra = 0;
                                            if ($listar_info->despacho_costo_total && $listar_info->totalVentaDespacho > 0) {
                                                $to = ($listar_info->despacho_costo_total / $listar_info->totalVentaDespacho) * 100;
                                                $ra = $general->formatoDecimal($to);
                                            }
                                        @endphp
                                        <strong class="colorgotomarket mb-2">Flete / Venta</strong>
                                        <p>{{ $ra }} %</p>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                        @php
                                            $ra2 = 0;
                                            // Verificar que despacho_peso no sea 0 antes de dividir
                                            if ($listar_info->despacho_costo_total && $listar_info->despacho_peso > 0) {
                                                $to = $listar_info->despacho_costo_total / $listar_info->despacho_peso;
                                                $ra2 = $general->formatoDecimal($to);
                                            } elseif ($listar_info->despacho_costo_total) {
                                                // Opcional: Manejar el caso cuando hay costo pero peso es 0
                                                $ra2 = 'N/A'; // O cualquier valor que quieras mostrar en este caso
                                            }
                                        @endphp
                                        <strong class="colorgotomarket mb-2">Flete / Peso</strong>
                                        <p>{{ $ra2 }}</p>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                        @php
                                            $mensaje_estado = "";
                                                if($listar_info->despacho_estado_aprobacion == 0)
                                                    $mensaje_estado = "Pendiente";
                                                elseif($listar_info->despacho_estado_aprobacion == 1)
                                                    $mensaje_estado = "Aprobado";
                                                elseif($listar_info->despacho_estado_aprobacion == 2)
                                                    $mensaje_estado = "En camino";
                                                elseif($listar_info->despacho_estado_aprobacion == 3)
                                                    $mensaje_estado = "Culminado";
                                                elseif($listar_info->despacho_estado_aprobacion == 4)
                                                    $mensaje_estado = "Rechazado";
                                                else
                                                    $mensaje_estado = "Estado desconocido";
                                        @endphp
                                        @if($editando)
                                            <p>Estado actual: <b>{{$mensaje_estado}}</b></p>
                                            <select class="form-control" id="despacho_estado_aprobacion_edit" wire:model.live="despacho_estado_aprobacion_edit">
                                                <option value="">Seleccionar...</option>
                                                <option value="2">En Ejecución</option>
                                                <option value="3">Terminado</option>
                                                <option value="4">Anulado</option>
                                            </select>
                                        @else
                                            <strong class="colorgotomarket mb-2">Estado de la OS</strong>
                                            <p>{{ $mensaje_estado }}</p>
                                        @endif

                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                        <strong class="colorgotomarket mb-2">OS Editada</strong>
                                        <p>{{ $listar_info->id_users_programacion ? 'SI' : 'NO' }}</p>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                        <strong class="colorgotomarket mb-2">Comentario</strong>
                                        @if($editando)
                                            <textarea class="form-control" id="despacho_descripcion_modificado" wire:model="despacho_descripcion_modificado" rows="3"></textarea>
                                        @else
                                            <p>{{$listar_info->despacho_descripcion_modificado ?? '-'}}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <h6>Detalle del Servicio</h6>
                                <hr>
                                <x-table-general>
                                    <x-slot name="thead">
                                        <tr>
                                            <th>N°</th>
                                            <th>Guía o Doc. Solicitante</th>
                                            <th>RUC Destinatario</th>
                                            <th>Nombre Destinatario</th>
                                            <th>Factura / Doc. Referencial</th>
                                            <th>Dirección de Entrega</th>
                                            <th>Ubigeo</th>
                                            <th>Peso (kg)</th>
                                            <th>Volumen (cm³)</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @if($listar_info->guias->count() > 0)
                                            @php $conteo = 1; @endphp
                                            @foreach($listar_info->guias as $index => $guia)
                                                <tr>
                                                    <td>{{ $conteo }}</td>
                                                    <td>{{ $guia->guia_nro_doc }}</td>
                                                    <td>{{ $guia->guia_ruc_cliente }}</td>
                                                    <td>{{ $guia->guia_nombre_cliente }}</td>
                                                    <td>{{ $guia->guia_nro_doc_ref }}</td>
                                                    <td>{{ $guia->guia_direc_entrega }}</td>
                                                    <td>{{ $guia->guia_departamento }} - {{ $guia->guia_provincia }} - {{ $guia->guia_destrito ?? '' }}</td>
                                                    <td>{{ $general->formatoDecimal($guia->pesoTotalKilos) }} kg</td>
                                                    <td>{{ $general->formatoDecimal($guia->volumenTotal) }} m³</td>
                                                </tr>
                                                @php $conteo++; @endphp
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="9" class="text-center">No hay guías asociadas a este despacho</td>
                                            </tr>
                                        @endif
                                    </x-slot>
                                </x-table-general>
                            </div>
                            <div class="col-lg-4">
                                <h6>Gestión de Entrega</h6>
                                <hr>
                                <x-table-general>
                                    <x-slot name="thead">
                                        <tr>
                                            <th>N°</th>
                                            <th>Estado de la Guía</th>
                                            <th>Fecha de Entrega</th>
                                            <th>Adjuntar Cargo</th>
                                        </tr>
                                    </x-slot>
                                    <x-slot name="tbody">
                                        @if($listar_info->guias->count() > 0)
                                            @php $conteoEst = 1; @endphp
                                            @foreach($listar_info->guias as $index => $guia)
                                                <tr>
                                                    <td>{{$conteoEst}}</td>
                                                    <td>
                                                        @if($editando)
                                                            @php
                                                                $estado = $general->obtener_estado($guia->guia_estado_aprobacion, $guia->id_guia, 3, $listar_info->id_despacho);
                                                            @endphp
                                                            <p>Estado actual: <b>{{$estado}}</b></p>

                                                            <select class="form-control" wire:model.live="guias_estados.{{$guia->id_guia}}">
                                                                <option value="">Seleccionar...</option>
                                                                <option value="7">Transito</option>
                                                                <option value="3">Por Programar</option>
                                                                <option value="8">Entregado</option>
                                                                <option value="15">Enviar a NC</option>
                                                            </select>
                                                        @else
                                                            @php
                                                                $estado = $general->obtener_estado($guia->guia_estado_aprobacion, $guia->id_guia, 3, $listar_info->id_despacho);
                                                            @endphp
                                                            <p>{{$estado}}</p>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if($editando)
                                                            <input type="date"
                                                                   class="form-control"
                                                                   wire:model.live="guias_fechas.{{$guia->id_guia}}">
                                                            @error('guias_fechas.'.$guia->id_guia)
                                                            <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        @else
                                                            @php
                                                                $f = $guias_fechas[$guia->id_guia] ?? null;
                                                            @endphp
                                                            <span>{{ $f ? $general->obtenerNombreFecha($f, 'Date', 'Date') : '-' }}</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if($editando)
                                                            <input type="file"
                                                                   id="file-input-{{ $guia->id_guia }}"
                                                                   class="d-none"
                                                                   wire:model="guias_cargos.{{ $guia->id_guia }}" />

                                                            <a class="btn btn-sm bg-success text-white"
                                                               onclick="document.getElementById('file-input-{{ $guia->id_guia }}').click()">
                                                                <i class="fa-solid fa-file"></i> Adjuntar
                                                            </a>

                                                            <div wire:loading wire:target="guias_cargos.{{ $guia->id_guia }}" class="mt-1 ms-4">
                                                                <div class="spinner-border spinner-border-sm text-success" role="status">
                                                                    <span class="visually-hidden">Cargando...</span>
                                                                </div>
                                                            </div>
                                                        @else
                                                            @php
                                                                $doc = $guias_cargos[$guia->id_guia] ?? null;
                                                            @endphp
                                                            @if($doc)
                                                                <a href="{{ asset($doc) }}" target="_blank">
                                                                    <i class="fa-solid fa-file"></i> Ver archivo
                                                                </a>
                                                            @else
                                                                -
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                                @php $conteoEst++; @endphp
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center">No hay guías asociadas a este despacho</td>
                                            </tr>
                                        @endif
                                    </x-slot>
                                </x-table-general>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-5">
                        <div class="card h-100">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Nota:</h5>
                            </div>
                            <div class="card-body mt-3">
                                <ul>
                                    <li>El Proveedor debe emitir su factura según lo especificado en la Orden de Servicio; donde cada ítem de la factura es una OS o, en su defecto, emitir una factura por OS</li>
                                    <li>El Proveedor debe indicar el número de OS en el detalle de su Factura y las guías de remisión o documento solicitante</li>
                                    <li>El Proveedor debe entregar los cargos de entrega en un plazo de 7 días hábiles</li>
                                    <li> El Proveedor puede enviar su Factura electrónica al correo electrónico: operaciones@gotomarket.com.pe</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Firma del Solicitante</h5>
                            </div>
                            <div class="card-body mt-3">
                                <p>Autorizado con Usuario y contraseña:</p>
                                <br>
                                <p>Antonio Angulo Casanova</p>
                                <p>Gerente de Operaciones</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card h-100">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Firma del Proveedor</h5>
                            </div>
                            <div class="card-body mt-3">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@script
<script>
    $wire.on('hide_anular_os', () => {
        $('#modal_anular_os').modal('hide');
    });

    $wire.on('hide_modal_fecha_entrega', () => {
        $('#modal_fecha_entrega').modal('hide');
    });
</script>
@endscript
