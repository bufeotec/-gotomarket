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
        <div class="col-lg-2 col-md-6 col-sm-12 mb-3 position-relative">
            <x-input-general type="text" class="form-control w-100 me-4 ps-5 rounded-pill" wire:model="buscar_numero_guia" placeholder="Ej: T0123456789" />
            <i class="fas fa-search position-absolute" style="left: 30px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12 mb-3 position-relative">
            <x-input-general type="text" class="form-control w-100 me-4 ps-5 rounded-pill"  wire:model="buscar_ruc_nombre" placeholder="RUC – Nombre de Cliente" />
            <i class="fas fa-search position-absolute"
               style="left: 30px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 mb-2">
            <select name="guia_estado_aprobacion" id="guia_estado_aprobacion" wire:model="buscar_estado" class="form-select">
                <option value="">Seleccionar estado...</option>
                <option value="1">Creditos</option>
                <option value="2">Despacho</option>
                <option value="3">Por programar</option>
                <option value="4">Programado</option>
                <option value="7">En ruta</option>
                <option value="12">Anulado</option>
                <option value="8">Entregado</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12 mb-3">
            <input type="date" name="desde" id="desde" wire:model="desde" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12 mb-3">
            <input type="date" name="hasta" id="hasta" wire:model="hasta" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-3 col-sm-12 mb-2 mt-1">
            <button class="btn btn-sm bg-primary text-white w-75" wire:click="buscar_comprobantes" wire:loading.attr="disabled">
                <i class="fa fa-search"></i>
                <spanc class="ms-1" wire:loading.remove wire:target="buscar_comprobantes">BUSCAR</spanc>
                <spanc class="ms-1" wire:loading wire:target="buscar_comprobantes">BUSCANDO...</spanc>
            </button>
        </div>
    </div>

    <!-- Loading overlay - usando las directivas correctas de Livewire -->
    <div class="fixed inset-0 align-items-center justify-content-center w-100 bg-black m-3 bg-opacity-30"
         style="display: none;"
         wire:loading.class.remove="d-none"
         wire:loading.class.add="d-flex"
         wire:target="buscar_comprobantes">
        <div class="wrapper">
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="shadow"></div>
            <div class="shadow"></div>
            <div class="shadow"></div>
        </div>
    </div>

    @if($listar_comprobantes)
    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Nombre del cliente</th>
                                <th>RUC</th>
                                <th>Numero documento</th>
                                <th>Factura</th>
                                <th>Fecha de emision</th>
                                <th>Monto sin IGV</th>
                                <th>Monto con IGV</th>
                                <th>Vendedor</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_comprobantes) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($listar_comprobantes as $me)
                                    <tr>
                                        <td>
                                            {{$conteo}}
                                            {{--VENDEDOR: {{$me->guia_vendedor}}
                                            CODIGO: {{$me->guia_vendedor_codigo}}--}}
                                        </td>
                                        <td>{{$me->guia_nombre_cliente}}</td>
                                        <td>{{$me->guia_ruc_cliente}}</td>
                                        <td>{{$me->guia_nro_doc}}</td>
                                        <td>{{$me->guia_nro_doc_ref}}</td>
                                        <td>{{ $general->obtenerNombreFecha($me->guia_fecha_emision,'DateTime','Date') }}</td>
                                        <td>S/ {{$general->formatoDecimal($me->guia_importe_total_sin_igv) ?? 0 }}</td>
                                        <td>S/ {{ $general->formatoDecimal($me->guia_importe_total_sin_igv * 1.18) ?? 0 }}</td>
                                        <td>VENDEDOR: {{$me->guia_vendedor}}</td>
                                        <td>
                                            <span class="d-block tamanhoTablaComproantes">
                                                @switch($me->guia_estado_aprobacion)
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
                                                    @default
                                                        Estado desconocido
                                                @endswitch
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('Programacioncamion.vistatracking', ['data' => base64_encode(json_encode(['id' => $me->id_guia, 'numdoc' => $me->guia_nro_doc, 'nombre' => $me->guia_nombre_cliente]))]) }}"
                                               target="_blank"
                                               class="btn text-primary">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
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
    @endif

    <style>
        .wrapper {
            width: 200px;
            height: 60px;
            position: relative;
            z-index: 1;
        }

        .circle {
            width: 20px;
            height: 20px;
            position: absolute;
            border-radius: 50%;
            background-color: #e51821;
            left: 15%;
            transform-origin: 50%;
            animation: circle7124 .5s alternate infinite ease;
        }

        @keyframes circle7124 {
            0% {
                top: 60px;
                height: 5px;
                border-radius: 50px 50px 25px 25px;
                transform: scaleX(1.7);
            }

            40% {
                height: 20px;
                border-radius: 50%;
                transform: scaleX(1);
            }

            100% {
                top: 0%;
            }
        }

        .circle:nth-child(2) {
            left: 45%;
            animation-delay: .2s;
        }

        .circle:nth-child(3) {
            left: auto;
            right: 15%;
            animation-delay: .3s;
        }

        .shadow {
            width: 20px;
            height: 4px;
            border-radius: 50%;
            background-color: rgba(0,0,0,0.9);
            position: absolute;
            top: 62px;
            transform-origin: 50%;
            z-index: -1;
            left: 15%;
            filter: blur(1px);
            animation: shadow046 .5s alternate infinite ease;
        }

        @keyframes shadow046 {
            0% {
                transform: scaleX(1.5);
            }

            40% {
                transform: scaleX(1);
                opacity: .7;
            }

            100% {
                transform: scaleX(.2);
                opacity: .4;
            }
        }

        .shadow:nth-child(4) {
            left: 45%;
            animation-delay: .2s
        }

        .shadow:nth-child(5) {
            left: auto;
            right: 15%;
            animation-delay: .3s;
        }
    </style>

</div>
