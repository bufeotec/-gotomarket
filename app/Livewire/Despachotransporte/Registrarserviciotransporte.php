<?php

namespace App\Livewire\Despachotransporte;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use App\Models\Departamento;
use Livewire\WithPagination;
use App\Models\Logs;
use App\Models\Serviciotransporte;
use App\Models\General;

class Registrarserviciotransporte extends Component
{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    private $logs;
    private $departamento;
    private $serviciotransporte;
    private $general;
    public function __construct(){
        $this->logs = new Logs();
        $this->serviciotransporte = new Serviciotransporte();
        $this->general = new General();
        $this->departamento = new Departamento();
    }
    public $search_servicio_transp;
    public $pagination_servicio_transp = 10;
    public $id_serv_transpt = "";
    public $serv_transpt_motivo = "";
    public $serv_transpt_detalle_motivo = "";
    public $serv_transpt_remitente_ruc = "";
    public $serv_transpt_remitente_razon_social = "";
    public $serv_transpt_remitente_direccion = "";
    public $serv_transpt_destinatario_ruc = "";
    public $serv_transpt_destinatario_razon_social = "";
    public $serv_transpt_destinatario_direccion = "";
    public $id_departamento = "";
    public $id_provincia = "";
    public $id_distrito = "";
    public $provincias = [];
    public $distritos = [];
    public $serv_transpt_peso = "";
    public $serv_transpt_volumen = "";
    public $serv_transpt_documento = "";
    public $serv_transpt_codigo = "";
    public $serv_transpt_estado = "";
    public $serv_transpt_estado_aprobacion = "";
    public $message_consulta_remitente = "";
    public $message_consulta_destinatario = "";
    public $nombre_archivo;

    public function render(){
        $listar_departamento = $this->departamento->lista_departamento();
        $listar_servicio_transporte = $this->serviciotransporte->listar_servicio_transporte($this->search_servicio_transp, $this->pagination_servicio_transp);
        return view('livewire.despachotransporte.registrarserviciotransporte', compact('listar_servicio_transporte','listar_departamento'));
    }
    public function listar_provincias(){
        $valor = $this->id_departamento;
        if ($valor) {
            $this->provincias = DB::table('provincias')->where('id_departamento', '=', $valor)->get();
        } else {
            $this->provincias = [];
            $this->id_provincia = "";
            $this->distritos = [];
            $this->id_distrito = "";
        }
    }
    public function listar_distritos(){
        $valor = $this->id_provincia;
        if ($valor) {
            $this->distritos = DB::table('distritos')->where('id_provincia', '=', $valor)->get();
        } else {
            $this->distritos = [];
            $this->id_distrito = "";
        }
    }
    public function deparTari(){
        $this->id_provincia = "";
        $this->id_distrito = "";
        $this->provincias = [];
        $this->distritos = [];
        $this->listar_provincias();
    }
    public function proviTari(){
        $this->listar_distritos();
    }

    public function consulta_documento_remitente(){
        try {
            $this->message_consulta_remitente = "";
            $this->serv_transpt_remitente_razon_social = "";
            $this->serv_transpt_remitente_direccion = "";
            $resultado = $this->general->consultar_documento(4,$this->serv_transpt_remitente_ruc);
            if ($resultado['result']['tipo'] == 'success'){
                $this->serv_transpt_remitente_razon_social = $resultado['result']['name'];
                $this->serv_transpt_remitente_direccion = $resultado['result']['direccion'];
            }
            $this->message_consulta_remitente = array('mensaje'=>$resultado['result']['mensaje'],'type'=>$resultado['result']['tipo']);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    public function consulta_documento_destinatario(){
        try {
            $this->message_consulta_destinatario = "";
            $this->serv_transpt_destinatario_razon_social = "";
            $this->serv_transpt_destinatario_direccion = "";
            $resultado = $this->general->consultar_documento(4,$this->serv_transpt_destinatario_ruc);
            if ($resultado['result']['tipo'] == 'success'){
                $this->serv_transpt_destinatario_razon_social = $resultado['result']['name'];
                $this->serv_transpt_destinatario_direccion = $resultado['result']['direccion'];
            }
            $this->message_consulta_destinatario = array('mensaje'=>$resultado['result']['mensaje'],'type'=>$resultado['result']['tipo']);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    public function clear_form_serv_transp(){
        $this->id_serv_transpt = "";
        $this->serv_transpt_motivo = "";
        $this->serv_transpt_detalle_motivo = "";
        $this->serv_transpt_remitente_ruc = "";
        $this->serv_transpt_remitente_razon_social = "";
        $this->serv_transpt_remitente_direccion = "";
        $this->serv_transpt_destinatario_ruc = "";
        $this->serv_transpt_destinatario_razon_social = "";
        $this->serv_transpt_destinatario_direccion = "";
        $this->id_departamento = "";
        $this->id_provincia = "";
        $this->id_distrito = "";
        $this->serv_transpt_peso = "";
        $this->serv_transpt_volumen = "";
        $this->serv_transpt_documento = "";
        $this->serv_transpt_codigo = "";
        $this->nombre_archivo = "";
    }

    public function edit_data($id){
        $servTrnspEdit = Serviciotransporte::find(base64_decode($id));
        if ($servTrnspEdit){
            $this->serv_transpt_motivo = $servTrnspEdit->serv_transpt_motivo;
            $this->serv_transpt_detalle_motivo = $servTrnspEdit->serv_transpt_detalle_motivo;
            $this->serv_transpt_remitente_ruc = $servTrnspEdit->serv_transpt_remitente_ruc;
            $this->serv_transpt_remitente_razon_social = $servTrnspEdit->serv_transpt_remitente_razon_social;
            $this->serv_transpt_remitente_direccion = $servTrnspEdit->serv_transpt_remitente_direccion;
            $this->serv_transpt_destinatario_ruc = $servTrnspEdit->serv_transpt_destinatario_ruc;
            $this->serv_transpt_destinatario_razon_social = $servTrnspEdit->serv_transpt_destinatario_razon_social;
            $this->serv_transpt_destinatario_direccion = $servTrnspEdit->serv_transpt_destinatario_direccion;
            $this->id_departamento = $servTrnspEdit->id_departamento;
            $this->id_provincia = $servTrnspEdit->id_provincia;
            $this->id_distrito = $servTrnspEdit->id_distrito;
            $this->serv_transpt_peso = $servTrnspEdit->serv_transpt_peso;
            $this->serv_transpt_volumen = $servTrnspEdit->serv_transpt_volumen;
            $this->serv_transpt_documento = $servTrnspEdit->serv_transpt_documento;
            $this->nombre_archivo = basename($servTrnspEdit->serv_transpt_documento);
            $this->id_serv_transpt = $servTrnspEdit->id_serv_transpt;
        }
    }

    public function saveServicioTransporte(){
        try {
            $this->validate([
                'serv_transpt_motivo' => 'required|string',
                'serv_transpt_detalle_motivo' => 'required|string',
                'serv_transpt_remitente_ruc' => 'nullable|size:11',
                'serv_transpt_remitente_razon_social' => 'required|string',
                'serv_transpt_remitente_direccion' => 'required|string',
                'serv_transpt_destinatario_ruc' => 'nullable|size:11',
                'serv_transpt_destinatario_razon_social' => 'required|string',
                'id_departamento' => 'required|numeric',
                'id_provincia' => 'required|numeric',
                'id_distrito' => 'required|numeric',
                'serv_transpt_peso' => 'required|numeric',
                'serv_transpt_volumen' => 'required|numeric',
                'serv_transpt_documento' => is_string($this->serv_transpt_documento) ? 'nullable' : 'file|mimes:jpg,jpeg,pdf,png|max:2048',
                'serv_transpt_estado' => 'nullable|integer',
                'id_serv_transpt' => 'nullable|integer',
            ], [
                'serv_transpt_motivo.required' => 'El motivo es obligatorio.',
                'serv_transpt_motivo.string' => 'El motivo debe ser una cadena de texto.',

                'serv_transpt_detalle_motivo.required' => 'El detalle del motivo es obligatorio.',
                'serv_transpt_detalle_motivo.string' => 'El detalle del motivo debe ser una cadena de texto.',

                'serv_transpt_remitente_ruc.required' => 'El RUC del remitente es obligatorio',
                'serv_transpt_remitente_ruc.string' => 'El RUC del remitente debe ser una cadena de texto.',
                'serv_transpt_remitente_ruc.size' => 'El número RUC debe tener exactamente 11 caracteres.',

                'serv_transpt_remitente_razon_social.required' => 'La razón social del remitente es obligatorio.',
                'serv_transpt_remitente_razon_social.string' => 'La razón social del remitente debe ser una cadena de texto.',

                'serv_transpt_remitente_direccion.required' => 'La dirección del remitente es obligatorio.',
                'serv_transpt_remitente_direccion.string' => 'La dirección del remitente debe ser una cadena de texto.',

                'id_departamento.required' => 'El departamento del destinatario es obligatorio.',
                'id_departamento.numeric' => 'El departamento debe ser un valor numérico.',

                'id_provincia.required' => 'La provincia del destinatario es obligatorio.',
                'id_provincia.numeric' => 'La provincia  debe ser un valor numérico.',

                'id_distrito.required' => 'El distrito del destinatario es obligatorio.',
                'id_distrito.numeric' => 'El distrito debe ser un valor numérico.',

                'serv_transpt_destinatario_ruc.required' => 'El RUC del destinatario es obligatorio',
                'serv_transpt_destinatario_ruc.string' => 'El RUC del destinatario debe ser una cadena de texto.',
                'serv_transpt_destinatario_ruc.size' => 'El número RUC debe tener exactamente 11 caracteres.',

                'serv_transpt_destinatario_razon_social.required' => 'La razón social del destinatario es obligatorio.',
                'serv_transpt_destinatario_razon_social.string' => 'La razón social del destinatario debe ser una cadena de texto.',

                'serv_transpt_destinatario_direccion.required' => 'La direccón del destinatario es obligatorio.',
                'serv_transpt_destinatario_direccion.string' => 'La direccón del destinatario debe ser una cadena de texto.',

                'serv_transpt_peso.required' => 'EL peso es obligatoria.',
                'serv_transpt_peso.numeric' => 'EL peso debe ser un valor numérico.',

                'serv_transpt_volumen.required' => 'El volumen obligatoria.',
                'serv_transpt_volumen.numeric' => 'El volumen debe ser un valor numérico.',

                'serv_transpt_documento.mimes' => 'El archivo debe ser un JPG, JPEG, PNG o PDF.',
                'serv_transpt_documento.max' => 'El archivo no debe superar los 2 MB.',

                'serv_transpt_estado.integer' => 'El estado debe ser un número entero.',

                'id_serv_transpt.integer' => 'El identificador debe ser un número entero.',
            ]);

            if (!$this->id_serv_transpt) { // INSERT
                if (!Gate::allows('create_servicio_transporte')) {
                    session()->flash('error', 'No tiene permisos para crear.');
                    return;
                }

                $microtime = microtime(true);
                $codigo = $this->serviciotransporte->generar_codigo_servicio_transporte();
                DB::beginTransaction();
                $serv_transp_save = new Serviciotransporte();
                $serv_transp_save->id_users = Auth::id();
                $serv_transp_save->serv_transpt_motivo = $this->serv_transpt_motivo;
                $serv_transp_save->serv_transpt_detalle_motivo = $this->serv_transpt_detalle_motivo;
                $serv_transp_save->serv_transpt_remitente_ruc = $this->serv_transpt_remitente_ruc;
                $serv_transp_save->serv_transpt_remitente_razon_social = $this->serv_transpt_remitente_razon_social;
                $serv_transp_save->serv_transpt_remitente_direccion = $this->serv_transpt_remitente_direccion;
                $serv_transp_save->serv_transpt_destinatario_ruc = $this->serv_transpt_destinatario_ruc;
                $serv_transp_save->serv_transpt_destinatario_razon_social = $this->serv_transpt_destinatario_razon_social;
                $serv_transp_save->serv_transpt_destinatario_direccion = $this->serv_transpt_destinatario_direccion;
                $serv_transp_save->id_departamento = $this->id_departamento;
                $serv_transp_save->id_provincia = $this->id_provincia;
                $serv_transp_save->id_distrito = $this->id_distrito;
                $serv_transp_save->serv_transpt_peso = $this->serv_transpt_peso;
                $serv_transp_save->serv_transpt_volumen = $this->serv_transpt_volumen;

                // Lógica para crear un nuevo registro
                if (!is_string($this->serv_transpt_documento)) {
                    $serv_transp_save->serv_transpt_documento = $this->general->save_files($this->serv_transpt_documento, 'despachotransporte/serviciotransporte');
                }

                $serv_transp_save->serv_transpt_codigo = $codigo;
                $serv_transp_save->serv_transpt_estado_aprobacion = 0;
                $serv_transp_save->serv_transpt_microtime = $microtime;
                $serv_transp_save->serv_transpt_estado = 1;
                $serv_transp_save->serv_transpt_fecha_creacion = Carbon::now('America/Lima');

                if ($serv_transp_save->save()) {
                    DB::commit();
                    $this->dispatch('hideModal');
                    $this->clear_form_serv_transp();
                    session()->flash('success', 'Registro guardado correctamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar el registro.');
                    return;
                }
            } else { // UPDATE
                if (!Gate::allows('update_servicio_transporte')) {
                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
                    return;
                }

                DB::beginTransaction();
                // Actualizar los datos del vehículo
                $serv_trnsp_update = Serviciotransporte::findOrFail($this->id_serv_transpt);
                $serv_trnsp_update->serv_transpt_motivo = $this->serv_transpt_motivo;
                $serv_trnsp_update->serv_transpt_detalle_motivo = $this->serv_transpt_detalle_motivo;
                $serv_trnsp_update->serv_transpt_remitente_ruc = $this->serv_transpt_remitente_ruc;
                $serv_trnsp_update->serv_transpt_remitente_razon_social = $this->serv_transpt_remitente_razon_social;
                $serv_trnsp_update->serv_transpt_remitente_direccion = $this->serv_transpt_remitente_direccion;
                $serv_trnsp_update->serv_transpt_destinatario_ruc = $this->serv_transpt_destinatario_ruc;
                $serv_trnsp_update->serv_transpt_destinatario_razon_social = $this->serv_transpt_destinatario_razon_social;
                $serv_trnsp_update->serv_transpt_destinatario_direccion = $this->serv_transpt_destinatario_direccion;
                $serv_trnsp_update->id_departamento = $this->id_departamento;
                $serv_trnsp_update->id_provincia = $this->id_provincia;
                $serv_trnsp_update->id_distrito = $this->id_distrito;
                $serv_trnsp_update->serv_transpt_peso = $this->serv_transpt_peso;
                $serv_trnsp_update->serv_transpt_volumen = $this->serv_transpt_volumen;

                // Lógica para actualizar un registro existente
                if (!is_string($this->serv_transpt_documento)) {
                    // Si se proporciona un nuevo archivo, guárdalo
                    $serv_trnsp_update->serv_transpt_documento = $this->general->save_files($this->serv_transpt_documento, 'despachotransporte/serviciotransporte');
                } else {
                    // Si no se proporciona un nuevo archivo, conserva el archivo existente
                    $serv_trnsp_update->serv_transpt_documento = $this->serv_transpt_documento;
                }

                // Guardar cambios en el vehículo
                if (!$serv_trnsp_update->save()) {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo actualizar el registro del vehículo.');
                    return;
                }

                DB::commit();
                $this->dispatch('hideModal');
                session()->flash('success', 'Registro actualizado correctamente.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }
}
