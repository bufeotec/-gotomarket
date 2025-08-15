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
                <a class="btn btn-sm bg-danger text-white ms-2" href="{{route('Despachotransporte.generar_pdf_os',['id_despacho'=>$listar_info->id_despacho])}}" target="_blank"><i class="fa-solid fa-file-pdf"></i> Descargar OS</a>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
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
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Datos de la OS</h5>
                            </div>
                            <div class="card-body mt-3">
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="ms-2">Fecha de Aprobación: <b>{{ $listar_info->despacho_fecha_aprobacion ? $general->obtenerNombreFecha($listar_info->despacho_fecha_aprobacion, 'Date', 'Date') : '-' }}</b></p>
                                        <p class="ms-2">Fecha de Inicio de Servicio: <b>{{ $listar_info->despacho_fecha_aprobacion ? $general->obtenerNombreFecha($listar_info->despacho_fecha_aprobacion, 'Date', 'Date') : '-' }}</b></p>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="ms-2">Plazo de Entrega: <b>2 dias hábiles</b></p>
                                        <p class="ms-2">Fecha de Entrega Esperada: <b>{{ $listar_info->despacho_fecha_aprobacion ? $general->obtenerNombreFecha($listar_info->despacho_fecha_aprobacion, 'Date', 'Date') : '-' }}</b></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Datos del Proveedor</h5>
                            </div>
                            <div class="card-body mt-3">
                                @if($editando)
                                    <select wire:model="transportista_seleccionado" id="transportista_seleccionado" name="transportista_seleccionado" wire:change="actualizar_transportista" class="form-select mb-3">
                                        <option value="">Seleccione un transportista</option>
                                        @foreach($transportistas as $transportista)
                                            <option value="{{$transportista->id_transportistas}}">
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
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header" style="background: #e7f1ff">
                                <h5>Acuerdos Comerciales de la OS</h5>
                            </div>
                            <div class="card-body mt-3">
{{--                                <p>Referencia: <b>Cotización N° 123456</b></p>--}}
{{--                                <p>Presentado: <b>Por Correo Electrónico e-mail absa@gmail.com</b></p>--}}
{{--                                <p>Contacto Comercial: <b>Josue Pomachua</b></p>--}}
{{--                                <p>Conformidad de Factura: <b>Después de Entrega</b></p>--}}
{{--                                <p>Modo de Pago: <b>Crédito a 15 días de presentación de factura</b></p>--}}
{{--                                <p>Garantías del Servicio: <b>100% de pérdida. Retorno gratuito por deterioro</b></p>--}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-9">
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
                    <div class="col-lg-3">
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
                                        <strong class="colorgotomarket mb-2">Estado de la OS</strong>
                                        <p>{{ $mensaje_estado }}</p>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                        <strong class="colorgotomarket mb-2">OS Editada</strong>
                                        <p>SI</p>
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
                            <div class="col-lg-9">
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
                                            <th>Volumen (m³)</th>
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
                            <div class="col-lg-3">
                                <h6>Gestión de Entrega</h6>
                                <hr>
                                <x-table-general>
                                    <x-slot name="thead">
                                        <tr>
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
                                                    <td>
                                                        @switch($guia->guia_estado_aprobacion)
                                                            @case(1)
                                                                Enviado a Créditos
                                                                @break
                                                            @case(2)
                                                                Enviado a Despacho
                                                                @break
                                                            @case(3)
                                                                Listo para despacho
                                                                @break
                                                            @case(4)
                                                                Pendiente de aprobación de despacho
                                                                @break
                                                            @case(5)
                                                                Aceptado por Créditos
                                                                @break
                                                            @case(6)
                                                                Estado de facturación
                                                                @break
                                                            @case(7)
                                                                Guía en tránsito
                                                                @break
                                                            @case(8)
                                                                Guía entregada
                                                                @break
                                                            @case(9)
                                                                Despacho aprobado
                                                                @break
                                                            @case(10)
                                                                Despacho rechazado
                                                                @break
                                                            @case(11)
                                                                Guía no entregada
                                                                @break
                                                            @case(12)
                                                                Guía anulada
                                                                @break
                                                            @case(13)
                                                                Registrada en Intranet
                                                                @break
                                                            @case(14)
                                                                Guía anulada por NC
                                                                @break
                                                            @case(15)
                                                                Pendiente de NC
                                                                @break
                                                            @case(20)
                                                                @php
                                                                    $despacho_ventas_l = DB::table('despacho_ventas as dv')
                                                                    ->join('despachos as d','dv.id_despacho','=','d.id_despacho')
                                                                    ->where('dv.id_guia','=',$guia->id_guia)
                                                                    ->where('d.id_tipo_servicios','=',1)
                                                                    ->first();

                                                                    $despacho_ventas_p = DB::table('despacho_ventas as dv')
                                                                    ->join('despachos as d','dv.id_despacho','=','d.id_despacho')
                                                                    ->where('dv.id_guia','=',$guia->id_guia)
                                                                    ->where('d.id_tipo_servicios','=',2)
                                                                    ->first();

                                                                    if($despacho_ventas_l->despacho_detalle_estado_entrega == 0 && $despacho_ventas_p->despacho_detalle_estado_entrega == 0
                                                                    && $despacho_ventas_l->despacho_estado_aprobacion == 1 && $despacho_ventas_p->despacho_estado_aprobacion == 1
                                                                    ){
                                                                        echo 'Despacho aprobado';
                                                                    } else if ($despacho_ventas_l->despacho_detalle_estado_entrega == 0 && $despacho_ventas_p->despacho_detalle_estado_entrega == 0
                                                                     && $despacho_ventas_l->despacho_estado_aprobacion == 2 && $despacho_ventas_p->despacho_estado_aprobacion == 1
                                                                    ){
                                                                        echo 'Guía en tránsito';
                                                                    } else if ($despacho_ventas_l->despacho_detalle_estado_entrega == 8 && $despacho_ventas_p->despacho_detalle_estado_entrega == 0
                                                                    && $despacho_ventas_l->despacho_estado_aprobacion == 3 && $despacho_ventas_p->despacho_estado_aprobacion == 2
                                                                    ){
                                                                        echo 'Guía en tránsito';
                                                                    } else if ($despacho_ventas_l->despacho_detalle_estado_entrega == 8 && $despacho_ventas_p->despacho_detalle_estado_entrega == 8
                                                                     && $despacho_ventas_l->despacho_estado_aprobacion == 3 && $despacho_ventas_p->despacho_estado_aprobacion == 3){
                                                                        echo 'Guía entregada';
                                                                    }
                                                                @endphp
                                                                @break
                                                            @default
                                                                Estado desconocido
                                                        @endswitch
                                                    </td>
                                                    <td></td>
                                                    <td></td>
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
                        <div class="card">
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
                        <div class="card">
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
                        <div class="card">
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
</script>
@endscript
