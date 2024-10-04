<div>
    <x-modal-general  wire:ignore.self >
        <x-slot name="tama">modal-lg</x-slot>
        <x-slot name="id_modal">modalEmpresa</x-slot>
        <x-slot name="titleModal">Gestionar Empresa</x-slot>
        <x-slot name="modalContent">
            <form wire:submit.prevent="saveEmpresas">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h6 class="text-primary">Información legal</h6>
                        <hr>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="row align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="d-flex align-items-center justify-content-center mt-4">
                                    <div style="max-width: 200px;" wire:ignore>
                                        <img src="" id="previeImageEmpresa" class="w-100"  alt="">
                                        <label for="profile_picture" class="iconsPreviewImage" style="left: 90%;top: -10px;">
                                            <i class="fa-solid fa-camera "></i>
                                        </label>
                                    </div>
                                    <input type="file" class="d-none" id="profile_picture" name="profile_picture" onchange="previewImage(this,'previeImageEmpresa')" wire:model="logo" >
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-8 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                        <label for="ruc" class="form-label">RUC (*)</label>
                                        <x-input-general type="text" id="ruc" wire:model="ruc" wire:change="consultDocument"/>
                                        <div wire:loading wire:target="consultDocument">
                                            Consultando información
                                        </div>
                                        @if($messageConsulta)
                                            <span class="text-{{$messageConsulta['type']}}">{{$messageConsulta['mensaje']}}</span>
                                        @endif

                                        @error('ruc') <span class="message-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3" >
                                        <div class="" wire:ignore>
                                            <label for="ubigeo" class="form-label">Ubigeo (*)</label>
                                            <select id="ubigeo" class="form-select" wire:model="ubigeo">
                                                <option value="">Seleccionar</option>
                                                @foreach($ubigeos_ as $u)
                                                    <option value="{{$u->id_ubigeo}}">{{$u->ubigeo_departamento}} - {{$u->ubigeo_provincia}} - {{$u->ubigeo_distrito}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('ubigeo') <span class="message-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <label for="company_name" class="form-label">Razón social (*)</label>
                        <x-input-general type="text" id="company_name" wire:model="company_name"/>
                        @error('company_name') <span class="message-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <label for="tax_address" class="form-label">Domicilio fiscal</label>
                        <x-input-general type="text" id="tax_address" wire:model="tax_address"/>
                        @error('tax_address') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h6 class="text-primary">Información comercial</h6>
                        <hr>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <label for="trade_name" class="form-label">Nombre comercial (*)</label>
                        <x-input-general type="text" id="trade_name" wire:model="trade_name"/>
                        @error('trade_name') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 mb-3">
                        <label for="phone1" class="form-label">Teléfono 1</label>
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">+51</span>
                            <x-input-general type="text" id="phone1"  onkeyup="validar_numeros(this.id)" wire:model="phone1" aria-describedby="basic-addon1"/>
                        </div>
                        @error('phone1') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 mb-3">
                        <label for="phone2" class="form-label">Teléfono 2</label>
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon2">+51</span>
                            <x-input-general type="text" id="phone2"  onkeyup="validar_numeros(this.id)" wire:model="phone2" aria-describedby="basic-addon2" />
                        </div>
                        @error('phone2') <span class="message-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-6 mb-3">
                        <label for="email1" class="form-label">Correo electrónico 1</label>
                        <x-input-general type="email" id="email1" wire:model="email1"/>
                        @error('email1') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 mb-3">
                        <label for="email2" class="form-label">Correo electrónico 2</label>
                        <x-input-general type="email" id="email2" wire:model="email2"/>
                        @error('email2') <span class="message-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea id="description" rows="3" class="form-control" wire:model="description"></textarea>
                        @error('description') <span class="message-error">{{ $message }}</span> @enderror
                    </div>


                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h6 class="text-primary">Credenciales</h6>
                        <hr>
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12 mb-3">
                        <label for="secondary_sun_user" class="form-label">Usuario sol secundario</label>
                        <x-input-general type="text" id="secondary_sun_user" wire:model="secondary_sun_user"/>
                        @error('secondary_sun_user') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-8 col-md-12 col-sm-12 mb-3">
                        <label for="treble_clef" class="form-label">Clave sol</label>
                        <x-input-general type="text" id="treble_clef" wire:model="treble_clef"/>
                        @error('treble_clef') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h6 class="text-primary">Certificado</h6>
                        <hr>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <div
                            x-data="{ uploading: false, progress: 0 }"
                            x-on:livewire-upload-start="uploading = true"
                            x-on:livewire-upload-finish="uploading = false"
                            x-on:livewire-upload-cancel="uploading = false"
                            x-on:livewire-upload-error="uploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress"
                        >
                            <!-- File Input -->
                            <label for="certificate" class="form-label">Certificado Digital (.pfx)</label>
                            <x-input-general type="file" id="certificate" wire:model="certificate"/>
                            <!-- Progress Bar -->
                            <div x-show="uploading">
                                <progress max="100" x-bind:value="progress"></progress>
                            </div>
                        </div>
                        @error('certificate') <span class="message-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <label for="certificate_key" class="form-label">Clave del Certificado </label>
                        <x-input-general type="text" id="certificate_key" wire:model="certificate_key"/>
                        @error('certificate_key') <span class="message-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h6 class="text-primary">Opciones de facturación</h6>
                        <hr>
                    </div>
                    <div class="col-lg-4 col-md-6 col-md-12">
                        <label for="" class="form-label"><b>FACTURACIÓN</b></label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" wire:model="produccion">
                            <label class="form-check-label" for="flexSwitchCheckChecked">Activar Producción</label>
                        </div>
                        @error('produccion') <span class="message-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-lg-4 col-md-6 col-md-12">
                        <label for="" class="form-label"><b>ENVÍO DE BOLETAS</b></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="inlineCheckbox1" wire:model="typeBoleta" value="1">
                            <label class="form-check-label" for="inlineCheckbox1">Resumen Diario</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="inlineCheckbox2" wire:model="typeBoleta" value="2">
                            <label class="form-check-label" for="inlineCheckbox2">Envío Directo</label>
                        </div>
                        @error('typeBoleta') <span class="message-error">{{ $message }}</span> @enderror
                    </div>
                   <div class="col-lg-12 col-md-12 col-sm-12">
                       @if (session()->has('error'))
                           <div class="alert alert-danger alert-dismissible show fade">
                               {{ session('error') }}
                               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                           </div>
                       @endif
                   </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 mt-5">
                        <button type="submit" class="btn btn-primary w-100" id="btn_guardar_empresa"><i class="fa fa-plus"></i> Agregar registro</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal-general>
    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalDeleteEmpresa</x-slot>
        <x-slot name="modalContentDelete">
            <form wire:submit.prevent="disable_company">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2 class="deleteTitle">{{$messageDeleteEmpresas}}</h2>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @error('id_empresa_delete') <span class="message-error">{{ $message }}</span> @enderror

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


    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 d-flex align-items-center mb-2">
            <input type="text" class="form-control w-50 me-4"  wire:model.live="search_empresas" placeholder="Buscar">
            <x-select-filter wire:model.live="pagination_empresas" />
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-end">
            <x-btn-export wire:click="clear_form_empresa"  class="bg-success text-white" data-bs-toggle="modal" data-bs-target="#modalEmpresa" >
                <x-slot name="icons">
                    fa-solid fa-plus
                </x-slot>
                Agregar Empresa
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
                                <th>Logo</th>
                                <th>Razón social</th>
                                <th>Domicilio fiscal</th>
                                <th>Nombre comercial</th>
                                <th>Descripción</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>¿Está en producción?</th>
                                <th>Envío de boleta</th>
                                <th>Acciones</th>
                            </tr>
                        </x-slot>

                        <x-slot name="tbody">
                            @if(count($empresas) > 0)
                                @php $conteoEmpresas = 1; @endphp
                                @foreach($empresas as $me)
                                    <tr>
                                        <td>{{$conteoEmpresas}}</td>
                                        <td>
                                            @if($me->empresa_logo && file_exists($me->empresa_logo))
                                                <img src="{{asset($me->empresa_logo)}}"  style="max-width: 150px" alt="">
                                            @else
                                                <img src="{{asset('not_photo.png')}}" style="max-width: 150px" alt="">
                                            @endif
                                        </td>
                                        <td>
                                            <b>{{$me->empresa_ruc}}</b>
                                            <br>
                                            {{$me->empresa_razon_social}}
                                        </td>
                                        <td>{{$me->empresa_domicilio_fiscal}}</td>
                                        <td>{{$me->empresa_nombre_comercial}}</td>
                                        <td>{{$me->empresa_descricion}}</td>
                                        <td>
                                            @if($me->empresa_telefono_uno)
                                                <span class="d-block">
                                                    <a href="tel:+51{{$me->empresa_telefono_uno}}">
                                                        +51 {{$me->empresa_telefono_uno}}
                                                    </a>
                                                </span>
                                            @endif
                                            @if($me->empresa_telefono_dos)
                                                <span class="d-block">
                                                    <a href="tel:+51{{$me->empresa_telefono_dos}}">
                                                        +51 {{$me->empresa_telefono_dos}}
                                                    </a>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($me->empresa_email_uno)
                                                <span class="d-block">
                                                    <a href="mailto:{{$me->empresa_email_uno}}">
                                                        {{$me->empresa_email_uno}}
                                                    </a>
                                                </span>
                                            @endif
                                            @if($me->empresa_email_dos)
                                                <span class="d-block">
                                                    <a href="mailto:{{$me->empresa_email_dos}}">
                                                        {{$me->empresa_email_dos}}
                                                    </a>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-label-{{$me->empresa_estado_produccion == 0 ? 'danger' : 'success'}} me-1">{{$me->empresa_estado_produccion == 0 ? 'NO' : 'SI'}} </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-success me-1">{{$me->empresa_estado_boleta == 1 ? 'Resumen Diario' : 'Envío Directo'}} </span>
                                        </td>
                                        <td>
                                            <x-btn-accion class=" text-primary"  wire:click="edit_data_empresa('{{ base64_encode($me->id_empresa) }}')" data-bs-toggle="modal" data-bs-target="#modalEmpresa">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </x-slot>
                                            </x-btn-accion>

                                            <x-btn-accion class=" text-danger" wire:click="btn_disable('{{ base64_encode($me->id_empresa) }}')" data-bs-toggle="modal" data-bs-target="#modalDeleteEmpresa">
                                                <x-slot name="message">
                                                    <i class="fa-solid fa-ban"></i>
                                                </x-slot>
                                            </x-btn-accion>
                                        </td>
                                    </tr>
                                    @php $conteoEmpresas++; @endphp
                                @endforeach
                            @else
                                <tr class="odd">
                                    <td valign="top" colspan="10" class="dataTables_empty text-center">
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
    {{ $empresas->links(data: ['scrollTo' => false]) }}
</div>



@script
<script>
    $wire.on('updateUserImagePreview',function(event) {
        const image = document.getElementById('previeImageEmpresa');
        if (image) {
            image.src = event[0].image;
        }
    });

    $wire.on('hideModal', () => {
        $('#modalEmpresa').modal('hide');
    });
    $wire.on('hideModalDelete', () => {
        $('#modalDeleteEmpresa').modal('hide');
    });

    // Inicializar Select2 cuando se cargue el modal
    $wire.on('select_ubigeo', (data) => {
        const text = data[0].text || null; // Asegúrate de que 'text' sea null si no se envía
        $('#ubigeo').select2({
            dropdownParent: $('#modalEmpresa .modal-body')
        });
        if(text){
            $('#select2-ubigeo-container').html(text)
        }else{
            $('#select2-ubigeo-container').html('Seleccionar')
        }
        // Sincronizar cambios de Select2 con Livewire
        $('#ubigeo').on('change', function () {
            let selectedValue = $(this).val();
            $wire.set('ubigeo', selectedValue); // Actualizar modelo de Livewire
        });
    });
    //
    // // Reinicializar Select2 cuando se abra el modal
    window.addEventListener('show-modal', function () {
        $('#ubigeo').select2({
            dropdownParent: $('#modalEmpresa .modal-body')
        });
    });
</script>
@endscript
