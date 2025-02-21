<div>
    @php
        $general = new \App\Models\General();
    @endphp

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_tracking" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_tracking" />
        </div>
    </div>

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
                                        <td>{{$me->fac_pre_prog_cnomcli}}</td>
                                        <td>{{$me->fac_pre_prog_cfcodcli}}</td>
                                        <td>{{$me->fac_pre_prog_cfnumdoc}}</td>
                                        <td>{{ $general->obtenerNombreFecha($me->fac_pre_prog_grefecemision,'DateTime','Date') }}</td>
                                        <td>S/ {{$general->formatoDecimal($me->fac_pre_prog_cfimporte / 1.18) ?? 0 }}</td>
                                        <td>S/ {{ $general->formatoDecimal($me->fac_pre_prog_cfimporte) }}</td>
                                        <td>
                                            @php
                                                $estado = [
                                                    1 => 'Créditos',
                                                    2 => 'Despachador',
                                                    3 => 'Listo para despacho',
                                                    4 => 'Factura despachada',
                                                    5 => 'Aceptado por créditos'
                                                ];
                                            @endphp
                                            {{ $estado[$me->fac_pre_prog_estado_aprobacion] ?? 'Desconocido' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('Gestionvendedor.vistatracking', ['data' => base64_encode(json_encode(['id' => $me->id_fac_pre_prog, 'numdoc' => $me->fac_pre_prog_cfnumdoc]))]) }}"
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
    {{ $listar_comprobantes->links(data: ['scrollTo' => false]) }}

</div>
