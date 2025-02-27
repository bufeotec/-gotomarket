<div>
    @php
        $general = new \App\Models\General();
    @endphp
    <div class="row">
        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
{{--            <label for="desde" class="form-label">Desde</label>--}}
            <input type="date" name="desde" id="desde" wire:model.live="desde" class="form-control">
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
{{--            <label for="hasta" class="form-label">Hasta</label>--}}
            <input type="date" name="hasta" id="hasta" wire:model.live="hasta" class="form-control">
        </div>
        <div class="col-lg-4 col-md-2 col-sm-12 mb-2">
{{--            <label for="factura" class="form-label">Factura</label>--}}
            <input type="text" name="factura" id="factura" wire:model.live="search" placeholder="Buscar por factura" class="form-control">
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12 text-end">
            <a class="btn bg-white text-dark create-new ms-3" onclick="history.back()">
        <span>
            <i class="fa-solid fa-arrow-left me-sm-1"></i>
            <span class="d-none d-sm-inline-block">Regresar</span>
        </span>
            </a>
        </div>
    </div>
{{--    hola--}}

    @if(count($listar_datos) > 0)
        <x-card-general-view>
            <x-slot name="content">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <x-table-general>
                            <x-slot name="thead">
                                <tr>
                                    <th>N°</th>
                                    <th>Factura</th>
                                    <th>RUC</th>
                                    <th>Nombre del Cliente</th>
                                    <th>F. Emisión de Comprobante</th>
                                    <th>F. Validación de Pago</th>
                                    <th>F/G. Estado de Factura</th>
                                    <th>F. Validación Despacho</th>
                                    <th>F. Programación de Despacho</th>
                                    <th>F. Despacho</th>
                                </tr>
                            </x-slot>

                            <x-slot name="tbody">
                                @php $conteo = 1; @endphp
                                @foreach($listar_datos as $ld)
                                    <tr>
{{--                                        <td>--}}
{{--                                            {{ $ld->fac_envio_valpago ? \Carbon\Carbon::parse($ld->fac_envio_valpago)->format('d M Y - h:i a') : '---' }}--}}
{{--                                        </td>--}}
                                        <td>{{$conteo}}</td>
                                        <td>{{$ld->fac_pre_prog_factura}}</td>
                                        <td>{{$ld->fac_pre_prog_cfcodcli}}</td>
                                        <td>{{$ld->fac_pre_prog_cnomcli}}</td>
                                        <td class="text-center">
                                            {{ $ld->fac_envio_valpago ? \Carbon\Carbon::parse($ld->fac_envio_valpago)->format('d M Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $ld->fac_acept_valpago ? \Carbon\Carbon::parse($ld->fac_acept_valpago)->format('d M Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $ld->fac_acept_est_fac ? \Carbon\Carbon::parse($ld->fac_acept_est_fac)->format('d M Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $ld->fac_acept_val_rec ? \Carbon\Carbon::parse($ld->fac_acept_val_rec)->format('d M Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $ld->fac_acept_ges_fac ? \Carbon\Carbon::parse($ld->fac_acept_ges_fac)->format('d M Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $ld->fac_despacho ? \Carbon\Carbon::parse($ld->fac_despacho)->format('d M Y') : '-' }}
                                        </td>
                                    </tr>
                                    @php $conteo++; @endphp
                                @endforeach
                            </x-slot>
                        </x-table-general>
                    </div>
                </div>
            </x-slot>
        </x-card-general-view>
    @endif
</div>
