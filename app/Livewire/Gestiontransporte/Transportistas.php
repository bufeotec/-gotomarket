<?php

namespace App\Livewire\Gestiontransporte;

use App\Livewire\Intranet\sidebar;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Transportista;
use App\Models\TipoServicio;
use App\Models\Ubigeo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Transportistas extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $transportistas;
    private $ubigeo;

    /* ATRIBUTOS PARA DATATABLES */
    public $search_transportistas;
    public $pagination_transportistas = 10;

    /* FIN ATRIBUTOS PARA DATATABLES */

    /* ATRIBUTOS PARA GUARDAR TRANSPORTISTAS */
    public $id_transportistas = "";
    public $id_tipo_servicios = "";
    public $id_ubigeo = "";
    public $transportista_ruc = "";
    public $transportista_razon_social = "";
    public $transportista_nom_comercial = "";
    public $transportista_direccion = "";
    public $transportista_correo = "";
    public $transportista_telefono = "";
    public $transportista_contacto = "";
    public $transportista_cargo = "";
    public $transportista_estado = "";
    public $messageDeleteTranspor = "";

    public $listar_servicios = array();
    /* FIN  ATRIBUTOS PARA GUARDAR TRANSPORTISTAS */
    public function mount() {
        $this->listarServiciosSelect();
    }

    public function __construct()
    {
        $this->logs = new Logs();
        $this->transportistas = new Transportista();
        $this->ubigeo = new Ubigeo();
    }

    #[On('refresh_select_servicios')]
    public function listarServiciosSelect(){
        $this->listar_servicios = TipoServicio::where('tipo_servicio_estado', 1)->get();
    }
    public function render(){
        $listar_ubigeos = $this->ubigeo->listar_ubigeos();
        $transportistas = $this->transportistas->listar_transportistas($this->search_transportistas,$this->pagination_transportistas);
        return view('livewire.gestiontransporte.transportistas',compact('transportistas', 'listar_ubigeos'));
    }

    public function clear_form_transportistas(){
        $this->id_transportistas = "";
        $this->id_tipo_servicios = "";
        $this->id_ubigeo = "";
        $this->transportista_ruc = "";
        $this->transportista_razon_social = "";
        $this->transportista_nom_comercial = "";
        $this->transportista_direccion = "";
        $this->transportista_correo = "";
        $this->transportista_telefono = "";
        $this->transportista_contacto = "";
        $this->transportista_cargo = "";
        $this->transportista_estado = "";
    }

    public function edit_data($id){
        $transportistasEdit = Transportista::find(base64_decode($id));
        if ($transportistasEdit){
            $this->id_tipo_servicios = $transportistasEdit->id_tipo_servicios;
            $this->id_ubigeo = $transportistasEdit->id_ubigeo;
            $this->transportista_ruc = $transportistasEdit->transportista_ruc;
            $this->transportista_razon_social = $transportistasEdit->transportista_razon_social;
            $this->transportista_nom_comercial = $transportistasEdit->transportista_nom_comercial;
            $this->transportista_direccion = $transportistasEdit->transportista_direccion;
            $this->transportista_correo = $transportistasEdit->transportista_correo;
            $this->transportista_telefono = $transportistasEdit->transportista_telefono;
            $this->transportista_contacto = $transportistasEdit->transportista_contacto;
            $this->transportista_cargo = $transportistasEdit->transportista_cargo;
            $this->id_transportistas = $transportistasEdit->id_transportistas;
        }
    }

    public function saveTransportista(){
        try {
            $this->validate([
                'id_tipo_servicios' => 'required|integer',
                'id_ubigeo' => 'required|integer',
                'transportista_ruc' => 'required|size:11',
                'transportista_razon_social' => 'required|string',
                'transportista_nom_comercial' => 'required|string',
                'transportista_direccion' => 'required|string',
                'transportista_correo' => 'required|email|max:200',
                'transportista_telefono' => 'required|size:9',
                'transportista_contacto' => 'required|string',
                'transportista_cargo' => 'required|string',
                'transportista_estado' => 'nullable|integer',
                'id_transportistas' => 'nullable|integer',
            ], [
                'id_tipo_servicios.required' => 'Debes seleccionar un servicio.',
                'id_tipo_servicios.integer' => 'El servicio debe ser un número entero.',

                'id_ubigeo.required' => 'Debes seleccionar un ubigeo.',
                'id_ubigeo.integer' => 'El ubigeo debe ser un número entero.',

                'transportista_ruc.required' => 'El RUC es obligatorio',
                'transportista_ruc.string' => 'El RUC debe ser una cadena de texto.',
                'transportista_ruc.size' => 'El número RUC debe tener exactamente 11 caracteres.',

                'transportista_razon_social.required' => 'La razon social es obligatorio.',
                'transportista_razon_social.string' => 'La razon social debe ser una cadena de texto.',

                'transportista_direccion.required' => 'El nombre comercial es obligatorio.',
                'transportista_direccion.string' => 'El nombre comercial debe ser una cadena de texto.',

                'transportista_nom_comercial.required' => 'La dirección es obligatorio.',
                'transportista_nom_comercial.string' => 'La dirección debe ser una cadena de texto.',

                'transportista_correo.required' => 'El correo electrónico es obligatorio.',
                'transportista_correo.email' => 'El correo electrónico debe ser un email válido.',

                'transportista_telefono.required' => 'El número de teléfono debe ser una cadena de texto.',
                'transportista_telefono.size' => 'El número de teléfono debe tener exactamente 9 caracteres.',

                'transportista_contacto.required' => 'El cotacto es obligatorio.',
                'transportista_contacto.string' => 'El contacto debe ser una cadena de texto.',

                'transportista_cargo.required' => 'El cargo es obligatorio.',
                'transportista_cargo.string' => 'El cargo debe ser una cadena de texto.',

                'transportista_estado.integer' => 'El estado debe ser un número entero.',

                'id_transportistas.integer' => 'El identificador debe ser un número entero.',
            ]);

            if (!$this->id_transportistas) { // INSERT
                if (!Gate::allows('create_transportistas')) {
                    session()->flash('error', 'No tiene permisos para crear.');
                    return;
                }
                $microtime = microtime(true);
                DB::beginTransaction();
                $transportistas_save = new Transportista();
                $transportistas_save->id_users = Auth::id();
                $transportistas_save->id_tipo_servicios = $this->id_tipo_servicios;
                $transportistas_save->id_ubigeo = $this->id_ubigeo;
                $transportistas_save->transportista_ruc = $this->transportista_ruc;
                $transportistas_save->transportista_razon_social = $this->transportista_razon_social;
                $transportistas_save->transportista_nom_comercial = $this->transportista_nom_comercial;
                $transportistas_save->transportista_direccion = $this->transportista_direccion;
                $transportistas_save->transportista_correo = $this->transportista_correo;
                $transportistas_save->transportista_telefono = $this->transportista_telefono;
                $transportistas_save->transportista_contacto = $this->transportista_contacto;
                $transportistas_save->transportista_cargo = $this->transportista_cargo;
                $transportistas_save->transportista_estado = 1;
                $transportistas_save->transportista_microtime = $microtime;

                if ($transportistas_save->save()) {
                    DB::commit();
                    // Emitir el evento al componente sidebar
//                    $this->dispatch('refresh_select_servicios')->to(Transportistas::class);
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Registro guardado correctamente.');

                } else {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar el menú.');
                    return;
                }
            } else {
                if (!Gate::allows('update_transportistas')) {
                    session()->flash('error', 'No tiene permisos para actualizar los menús.');
                    return;
                }
                DB::beginTransaction();
                // Actualizar los datos del menú
                $transportistas_update = Transportista::findOrFail($this->id_transportistas);
                $transportistas_update->id_tipo_servicios = $this->id_tipo_servicios;
                $transportistas_update->id_ubigeo = $this->id_ubigeo;
                $transportistas_update->transportista_ruc = $this->transportista_ruc;
                $transportistas_update->transportista_razon_social = $this->transportista_razon_social;
                $transportistas_update->transportista_nom_comercial = $this->transportista_nom_comercial;
                $transportistas_update->transportista_direccion = $this->transportista_direccion;
                $transportistas_update->transportista_correo = $this->transportista_correo;
                $transportistas_update->transportista_telefono = $this->transportista_telefono;
                $transportistas_update->transportista_contacto = $this->transportista_contacto;
                $transportistas_update->transportista_cargo = $this->transportista_cargo;

                if (!$transportistas_update->save()) {
                    session()->flash('error', 'No se pudo actualizar el menú.');
                    return;
                }
                DB::commit();
                $this->dispatch('hideModal');
                session()->flash('success', 'Menú actualizado correctamente.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }

    public function btn_disable($id_transpor,$esta){
        $id = base64_decode($id_transpor);
        $status = $esta;
        if ($id){
            $this->id_transportistas = $id;
            $this->transportista_estado = $status;
            if ($status == 0){
                $this->messageDeleteTranspor = "¿Está seguro que desea deshabilitar este registro?";
            }else{
                $this->messageDeleteTranspor = "¿Está seguro que desea habilitar este registro?";
            }
        }
    }

    public function disable_transportistas(){
        try {

            if (!Gate::allows('disable_transportistas')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }


            $this->validate([
                'id_transportistas' => 'required|integer',
                'transportista_estado' => 'required|integer',
            ], [
                'id_transportistas.required' => 'El identificador es obligatorio.',
                'id_transportistas.integer' => 'El identificador debe ser un número entero.',

                'transportista_estado.required' => 'El estado es obligatorio.',
                'transportista_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $transportistasDelete = Transportista::find($this->id_transportistas);
            $transportistasDelete->transportista_estado = $this->transportista_estado;
            if ($transportistasDelete->save()) {
                DB::commit();
                $this->dispatch('hideModalDelete');
                if ($this->transportista_estado == 0){
                    session()->flash('success', 'Registro deshabilitado correctamente.');
                }else{
                    session()->flash('success', 'Registro habilitado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado del menú.');
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }

    public function limpiar_nombre_convenio(){
        $this->dispatch('limpiar_nombre_convenio');
    }
}