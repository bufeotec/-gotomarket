<?php

namespace App\Livewire\Gestiontransporte;

use App\Livewire\Intranet\sidebar;
use App\Models\General;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Transportista;
use App\Models\Ubigeo;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Transportistas extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $general;
    private $transportistas;
    private $ubigeo;
    private $vehiculo;
    /* ATRIBUTOS PARA DATATABLES */
    public $search_transportistas;
    public $pagination_transportistas = 10;

    /* FIN ATRIBUTOS PARA DATATABLES */

    /* ATRIBUTOS PARA GUARDAR TRANSPORTISTAS */
    public $id_transportistas = "";
//    public $id_tipo_servicios = "";
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
    public $messageConsulta = "";

//    public $urlActual;
    /* FIN  ATRIBUTOS PARA GUARDAR TRANSPORTISTAS */

    public function __construct(){
        $this->logs = new Logs();
        $this->transportistas = new Transportista();
        $this->ubigeo = new Ubigeo();
        $this->general = new General();
        $this->vehiculo = new Vehiculo();
    }
//
    public function render(){
//        $listar_ubigeos = $this->ubigeo->listar_ubigeos();
        $transportistas = $this->transportistas->listar_transportistas_new($this->search_transportistas,$this->pagination_transportistas);
        return view('livewire.gestiontransporte.transportistas',compact('transportistas'));
    }

    public function clear_form_transportistas(){
        $this->id_transportistas = "";
//        $this->id_tipo_servicios = "";
//        $this->id_ubigeo = "";
        $this->transportista_ruc = "";
        $this->transportista_razon_social = "";
        $this->transportista_nom_comercial = "";
        $this->transportista_direccion = "";
        $this->transportista_correo = "";
        $this->transportista_telefono = "";
        $this->transportista_contacto = "";
        $this->transportista_cargo = "";
        $this->transportista_estado = "";
        $this->dispatch('select_ubigeo',['text' => null]);
    }
    public function consultDocument(){
        try {
            $this->messageConsulta = "";
            $this->transportista_razon_social = "";
            $this->transportista_direccion = "";
            $resultado = $this->general->consultar_documento(4,$this->transportista_ruc);
            if ($resultado['result']['tipo'] == 'success'){
                $this->transportista_razon_social = $resultado['result']['name'];
                $this->transportista_direccion = $resultado['result']['direccion'];
            }
            $this->messageConsulta = array('mensaje'=>$resultado['result']['mensaje'],'type'=>$resultado['result']['tipo']);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    public function edit_data($id){
        $transportistasEdit = Transportista::find(base64_decode($id));
        if ($transportistasEdit){
//            $this->id_tipo_servicios = $transportistasEdit->id_tipo_servicios;
//            $this->id_ubigeo = $transportistasEdit->id_ubigeo;
            $this->transportista_ruc = $transportistasEdit->transportista_ruc;
            $this->transportista_razon_social = $transportistasEdit->transportista_razon_social;
            $this->transportista_nom_comercial = $transportistasEdit->transportista_nom_comercial;
            $this->transportista_direccion = $transportistasEdit->transportista_direccion;
            $this->transportista_correo = $transportistasEdit->transportista_correo;
            $this->transportista_telefono = $transportistasEdit->transportista_telefono;
            $this->transportista_contacto = $transportistasEdit->transportista_contacto;
            $this->transportista_cargo = $transportistasEdit->transportista_cargo;
            $this->id_transportistas = $transportistasEdit->id_transportistas;
            $opcionSelect = "";
            if ($transportistasEdit->id_ubigeo){
                $ubi = Ubigeo::find($transportistasEdit->id_ubigeo);
                $opcionSelect = $ubi->ubigeo_departamento." - ".$ubi->ubigeo_provincia." - ".$ubi->ubigeo_distrito;
            }
            $this->dispatch('select_ubigeo',['text' => $opcionSelect]);
        }
    }

    public function saveTransportista(){
        try {
            $this->validate([
                'id_ubigeo' => 'nullable|integer',
                'transportista_ruc' => 'required|size:11',
                'transportista_razon_social' => 'required|string',
                'transportista_nom_comercial' => 'required|string',
                'transportista_direccion' => 'required|string',
                'transportista_correo' => 'nullable|email|max:200',
                'transportista_telefono' => 'nullable|size:9',
                'transportista_contacto' => 'required|string',
                'transportista_cargo' => 'required|string',
                'transportista_estado' => 'nullable|integer',
                'id_transportistas' => 'nullable|integer',
            ], [
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

                'transportista_correo.email' => 'El correo electrónico debe ser un email válido.',

                'transportista_telefono.size' => 'El número de teléfono debe tener exactamente 9 caracteres.',

                'transportista_contacto.required' => 'El contacto es obligatorio.',
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

                $validar = DB::table('transportistas')->where('transportista_ruc', '=',$this->transportista_ruc)->exists();
                if (!$validar){
                    $microtime = microtime(true);
                    DB::beginTransaction();
                    $transportistas_save = new Transportista();
                    $transportistas_save->id_users = Auth::id();
                    $transportistas_save->id_ubigeo = !empty($this->id_ubigeo) ? $this->id_ubigeo : null;
                    $transportistas_save->transportista_ruc = $this->transportista_ruc;
                    $transportistas_save->transportista_razon_social = $this->transportista_razon_social;
                    $transportistas_save->transportista_nom_comercial = $this->transportista_nom_comercial;
                    $transportistas_save->transportista_direccion = $this->transportista_direccion;
                    $transportistas_save->transportista_correo = !empty($this->transportista_correo) ? $this->transportista_correo : null;
                    $transportistas_save->transportista_telefono = !empty($this->transportista_telefono) ? $this->transportista_telefono : null;
                    $transportistas_save->transportista_contacto = !empty($this->transportista_contacto) ? $this->transportista_contacto : null;
                    $transportistas_save->transportista_cargo = $this->transportista_cargo;
                    $transportistas_save->transportista_estado = 1;
                    $transportistas_save->transportista_microtime = $microtime;

                    if ($transportistas_save->save()) {
                        DB::commit();
                        // Emitir el evento al componente sidebar
                        $this->dispatch('hideModal');
                        session()->flash('success', 'Registro guardado correctamente.');

                    } else {
                        DB::rollBack();
                        session()->flash('error', 'Ocurrió un error al guardar el registro.');
                        return;
                    }
                } else{
                    session()->flash('error', 'El RUC ingresado ya está registrado. Por favor, verifica los datos o ingresa un RUC diferente.');
                    return;
                }
            } else {
                if (!Gate::allows('update_transportistas')) {
                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
                    return;
                }

                $validar_update = DB::table('transportistas')
                    ->where('id_transportistas', '<>',$this->id_transportistas)
                    ->where('transportista_ruc', '=',$this->transportista_ruc)
                    ->exists();
                if (!$validar_update){
                    DB::beginTransaction();
                    // Actualizar los datos del menú
                    $transportistas_update = Transportista::findOrFail($this->id_transportistas);
                    $transportistas_update->id_ubigeo = !empty($this->id_ubigeo) ? $this->id_ubigeo : null;
                    $transportistas_update->transportista_ruc = $this->transportista_ruc;
                    $transportistas_update->transportista_razon_social = $this->transportista_razon_social;
                    $transportistas_update->transportista_nom_comercial = $this->transportista_nom_comercial;
                    $transportistas_update->transportista_direccion = $this->transportista_direccion;
                    $transportistas_update->transportista_correo = !empty($this->transportista_correo) ? $this->transportista_correo : null;
                    $transportistas_update->transportista_telefono = !empty($this->transportista_telefono) ? $this->transportista_telefono : null;
                    $transportistas_update->transportista_contacto = !empty($this->transportista_contacto) ? $this->transportista_contacto : null;
                    $transportistas_update->transportista_cargo = $this->transportista_cargo;

                    if (!$transportistas_update->save()) {
                        session()->flash('error', 'No se pudo actualizar el registro.');
                        return;
                    }
                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Registro actualizado correctamente.');
                } else{
                    session()->flash('error', 'El RUC ingresado ya está registrado. Por favor, verifica los datos o ingresa un RUC diferente.');
                    return;
                }
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
                session()->flash('error_delete', 'No se pudo cambiar el estado del registro.');
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
