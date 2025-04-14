<div>
    @php
        $general = new \App\Models\General();
    @endphp

    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3 position-relative">
            <input type="text" class="form-control w-100 me-4 ps-5 rounded-pill"  wire:model.live="buscar_guia" placeholder="Buscar guía">
            <i class="fas fa-search position-absolute"
               style="left: 30px; top: 50%; transform: translateY(-50%); color: #bbb;"></i>
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12 mb-3">
            <input type="date" name="desde" id="desde" wire:model.live="desde" class="form-control" min="2025-01-01">
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12 mb-3">
            <input type="date" name="hasta" id="hasta" wire:model.live="hasta" class="form-control" min="2025-01-01">
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
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($listar_comprobantes) > 0)
                                @php $conteoMenu = 1; @endphp
                                @foreach($listar_comprobantes as $me)
                                    <tr>
                                        <td>{{$conteoMenu}}</td>
                                        <td>{{$me->guia_nombre_cliente}}</td>
                                        <td>{{$me->guia_ruc_cliente}}</td>
                                        <td>{{$me->guia_nro_doc}}</td>
                                        <td>{{$me->guia_nro_doc_ref}}</td>
                                        <td>{{ $general->obtenerNombreFecha($me->guia_fecha_emision,'DateTime','Date') }}</td>
                                        <td>S/ {{$general->formatoDecimal($me->guia_importe_total / 1.18) ?? 0 }}</td>
                                        <td>S/ {{ $general->formatoDecimal($me->guia_importe_total) }}</td>
                                        <td>
                                            @php
                                                $estado = [
                                                    1 => 'Enviado a Créditos',
                                                    2 => 'Enviado a Despacho',
                                                    3 => 'Listo para despacho',
                                                    4 => 'Pendiente de aprobación de despacho',
                                                    5 => 'Aceptado por Créditos',
                                                    6 => 'Estado de facturación',
                                                    7 => 'Guía en transtio',
                                                    8 => 'Guía entregada',
                                                    9 => 'Despacho aprobado',
                                                    10 => 'Despacho rechazado',
                                                    11 => 'Guía no entregada'
                                                ];
                                            @endphp
                                            {{ $estado[$me->guia_estado_aprobacion] ?? 'Desconocido' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('Programacioncamion.vistatracking', ['data' => base64_encode(json_encode(['id' => $me->id_guia, 'numdoc' => $me->guia_nro_doc]))]) }}"
                                               target="_blank"
                                               class="btn text-primary">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @php $conteoMenu++; @endphp
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
