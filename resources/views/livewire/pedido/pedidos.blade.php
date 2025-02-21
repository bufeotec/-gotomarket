<div>
    @php
        $general = new \App\Models\General();
    @endphp

    {{-- MODAL REGISTRO PEDIDO --}}
    <x-modal-general wire:ignore.self>
        <x-slot name="id_modal">modalRegPedido</x-slot>
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="titleModal">Registrar Pedido</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="savePedido">
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <small class="text-primary">Información del Pedido</small>
                        <hr class="mb-0">
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label for="factura_ped_nomcli" class="form-label">Cliente</label>
                        <x-input-general type="text" id="factura_ped_nomcli" wire:model="factura_ped_nomcli"/>
                        @error('factura_ped_nomcli')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label for="factura_ped_codcli" class="form-label">N° Documento</label>
                        <x-input-general type="text" id="factura_ped_codcli" wire:model="factura_ped_codcli"/>
                        @error('factura_ped_codcli')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label for="factura_ped_direccion" class="form-label">Dirección</label>
                        <x-input-general type="text" id="factura_ped_direccion" wire:model="factura_ped_direccion"/>
                        @error('factura_ped_direccion')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label for="factura_ped_departamento" class="form-label">Departamento</label>
                        <select class="form-select" wire:model="factura_ped_departamento" id="factura_ped_departamento">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_dep as $l)
                                <option value="{{ $l->departamento_nombre }}">{{ $l->departamento_nombre }}</option>
                            @endforeach
                        </select>
                        @error('factura_ped_departamento')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label for="factura_ped_provincia" class="form-label">Provincia</label>
                        <select class="form-select" wire:model="factura_ped_provincia" id="factura_ped_provincia">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_prov as $lp)
                                <option value="{{ $lp->provincia_nombre }}">{{ $lp->provincia_nombre }}</option>
                            @endforeach
                        </select>
                        @error('factura_ped_provincia')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label for="factura_ped_distrito" class="form-label">Distrito</label>
                        <select class="form-select" wire:model="factura_ped_distrito" id="factura_ped_distrito">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_dis as $ld)
                                <option value="{{ $ld->distrito_nombre }}">{{ $ld->distrito_nombre }}</option>
                            @endforeach
                        </select>
                        @error('factura_ped_distrito')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <hr class="mb-3 mt-3">
                    <div class="col-lg-4 mb-3">
                        <label for="id_producto" class="form-label">Producto</label>
                        <select class="form-select" wire:model="id_producto" id="id_producto">
                            <option value="">Seleccionar...</option>
                            @foreach($listar_producto as $ld)
                                <option value="{{ $ld->id_producto }}">{{ $ld->producto_nom }}</option>
                            @endforeach
                        </select>
                        @error('id_producto')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-2 mb-3">
                        <label for="cantidad" class="form-label">Cantidad</label>
                        <x-input-general type="number" id="cantidad" wire:model="cantidad" min="1"/>
                        @error('cantidad')
                        <span class="message-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-4 mb-3">
                        <button type="button" wire:click="addProduct" class="btn btn-primary" style="margin-top: 13%">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="col-lg-12 mb-3">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Precio Unitario</th>
                                <th class="text-center">Subtotal</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($productosAgregados as $index => $item)
                                <tr>
                                    <td>{{ $item['nombre'] }}</td>
                                    <td class="text-center">{{ $item['cantidad'] }}</td>
                                    <td class="text-center">{{ $item['precio'] }}</td>
                                    <td class="text-center">{{ $item['precio'] * $item['cantidad'] }}</td>
                                    <td class="text-center">
                                        <button type="button" wire:click="removeProduct({{ $index }})" class="btn btn-danger btn-sm">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No se han agregado productos.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <div class="text-end mt-2">
                            <h5 class="text-primary">Total: S/ {{ $subtotal }}</h5>
                        </div>
                    </div>
                    <div class="col-lg-12 mt-3 text-end">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cerrar</button>
                        <button type="submit" class="btn btn-success text-white">Guardar Registro</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
    {{-- FIN MODAL REGISTRO PEDIDO --}}

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <div class="row align-items-center mt-2">
                {{--                <div class="col-lg-5 col-md-2 col-sm-12 mb-2">--}}
                {{--                    <label for="fecha_desde" class="form-label">Desde</label>--}}
                {{--                    <input type="date" name="fecha_desde" id="fecha_desde" wire:model.live="desde" class="form-control">--}}
                {{--                </div>--}}
                {{--                <div class="col-lg-5 col-md-2 col-sm-12 mb-2">--}}
                {{--                    <label for="fecha_hasta" class="form-label">Hasta</label>--}}
                {{--                    <input type="date" name="fecha_hasta" id="fecha_hasta" wire:model.live="hasta" class="form-control">--}}
                {{--                </div>--}}
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form_rp" class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalRegPedido">
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Registrar Pedido
            </x-btn-export>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-card-general-view>
        <x-slot name="content">
            <div class="row">
                <div class="col-lg-12">
                    <x-table-general>
                        <x-slot name="thead">
                            <tr>
                                <th>N°</th>
                                <th>Factura</th>
                                <th>N° Documento</th>
                                <th>Nombre del Cliente</th>
                                <th>Fecha de Emisión</th>
                                <th>Importe</th>
                            </tr>
                        </x-slot>
                        <x-slot name="tbody">
                            @if(count($factura) > 0)
                                @php $conteo = 1; @endphp
                                @foreach($factura as $f)
                                    <tr>
                                        <td>{{ $conteo}}</td>
                                        <td>{{ $f->factura_ped_factura}}</td>
                                        <td>{{ $f->factura_ped_codcli}}</td>
                                        <td>{{ $f->factura_ped_nomcli}}</td>
                                        <td>{{ $f->factura_femision}}</td>
                                        <td>S/ {{ $f->factura_ped_cfimporte}}</td>
                                    </tr>
                                    @php $conteo++; @endphp
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8">No hay registros disponibles.</td>
                                </tr>
                            @endif
                        </x-slot>
                    </x-table-general>
                </div>
            </div>
        </x-slot>
    </x-card-general-view>

</div>

@script
<script>
    $wire.on('hideModal', () => {
        $('#modalRegPedido').modal('hide');
    });
</script>

@endscript
