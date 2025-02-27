<?php

namespace App\Livewire\Programacioncamiones;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Logs;
use App\Models\Guiaremision;
use App\Models\Vehiculo;
use App\Models\General;

class Registrarguiasremisiones extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $guiaremision;
    private $vehiculo;
    private $general;
    public function __construct(){
        $this->logs = new Logs();
        $this->guiaremision = new Guiaremision();
        $this->vehiculo = new Vehiculo();
        $this->general = new General();
    }
    public $search_guia_remision;
    public $pagination_guia_remision = 10;
    public $id_guia_rem = "";
    public $id_vehiculo = "";
    public $guia_rem_numero_guia = "";
    public $guia_rem_fecha_emision = "";
    public $guia_rem_fecha_traslado = "";
    public $guia_rem_motivo = "";
    public $guia_rem_remitente_ruc = "";
    public $guia_rem_remitente_razon_social = "";
    public $guia_rem_remitente_direccion = "";
    public $guia_rem_destinatario_ruc = "";
    public $guia_rem_destinatario_razon_social = "";
    public $guia_rem_destinatario_direccion = "";
    public $guia_rem_estado_aprobacion = "";
    public $guia_rem_estado = "";
    public $message_consulta_remitente = "";
    public $message_consulta_destinatario = "";
    public $messageGuiaRem;
    public $isEditing = false;


    public function render(){
        $listar_vehiculos = $this->vehiculo->listar_vehiculos_activos();
// Solo establece la fecha y hora actual si no estás en modo de edición
        if (!$this->isEditing) {
            $this->guia_rem_fecha_emision = Carbon::now('America/Lima')->format('Y-m-d\TH:i');
        }
        $listar_guias_remision = $this->guiaremision->listar_guias_remision($this->search_guia_remision, $this->pagination_guia_remision);
        return view('livewire.programacioncamiones.registrarguiasremisiones', compact('listar_vehiculos', 'listar_guias_remision'));
    }

    public function consulta_documento_remitente(){
        try {
            $this->message_consulta_remitente = "";
            $this->guia_rem_remitente_razon_social = "";
            $this->guia_rem_remitente_direccion = "";
            $resultado = $this->general->consultar_documento(4,$this->guia_rem_remitente_ruc);
            if ($resultado['result']['tipo'] == 'success'){
                $this->guia_rem_remitente_razon_social = $resultado['result']['name'];
                $this->guia_rem_remitente_direccion = $resultado['result']['direccion'];
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
            $this->guia_rem_destinatario_razon_social = "";
            $this->guia_rem_destinatario_direccion = "";
            $resultado = $this->general->consultar_documento(4,$this->guia_rem_destinatario_ruc);
            if ($resultado['result']['tipo'] == 'success'){
                $this->guia_rem_destinatario_razon_social = $resultado['result']['name'];
                $this->guia_rem_destinatario_direccion = $resultado['result']['direccion'];
            }
            $this->message_consulta_destinatario = array('mensaje'=>$resultado['result']['mensaje'],'type'=>$resultado['result']['tipo']);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    public function clear_form_guia_remision(){
        $this->id_guia_rem = "";
        $this->id_vehiculo = "";
        $this->guia_rem_numero_guia = "";
        $this->guia_rem_fecha_emision = "";
        $this->guia_rem_motivo = "";
        $this->guia_rem_remitente_ruc = "";
        $this->guia_rem_remitente_razon_social = "";
        $this->guia_rem_remitente_direccion = "";
        $this->guia_rem_destinatario_ruc = "";
        $this->guia_rem_destinatario_razon_social = "";
        $this->guia_rem_destinatario_direccion = "";
    }

    public function edit_data($id){
        $this->isEditing = true; // Activa el modo de edición

        $guiaRemEdit = Guiaremision::find(base64_decode($id));
        if ($guiaRemEdit) {
            $this->guia_rem_numero_guia = $guiaRemEdit->guia_rem_numero_guia;
            $this->id_vehiculo = $guiaRemEdit->id_vehiculo;
            $this->guia_rem_fecha_emision = $guiaRemEdit->guia_rem_fecha_emision;
            $this->guia_rem_motivo = $guiaRemEdit->guia_rem_motivo;
            $this->guia_rem_remitente_ruc = $guiaRemEdit->guia_rem_remitente_ruc;
            $this->guia_rem_remitente_razon_social = $guiaRemEdit->guia_rem_remitente_razon_social;
            $this->guia_rem_remitente_direccion = $guiaRemEdit->guia_rem_remitente_direccion;
            $this->guia_rem_destinatario_ruc = $guiaRemEdit->guia_rem_destinatario_ruc;
            $this->guia_rem_destinatario_razon_social = $guiaRemEdit->guia_rem_destinatario_razon_social;
            $this->guia_rem_destinatario_direccion = $guiaRemEdit->guia_rem_destinatario_direccion;
            $this->id_guia_rem = $guiaRemEdit->id_guia_rem;
        }
    }

    public function saveGuiaRemision() {
        try {
            $this->validate([
                'id_vehiculo' => 'required|integer',
                'guia_rem_numero_guia' => 'required|string',
                'guia_rem_fecha_emision' => 'required|date',
                'guia_rem_motivo' => 'required|string',
                'guia_rem_remitente_ruc' => 'required|size:11',
                'guia_rem_remitente_razon_social' => 'required|string',
                'guia_rem_remitente_direccion' => 'required|string',
                'guia_rem_destinatario_ruc' => 'required|size:11',
                'guia_rem_destinatario_razon_social' => 'required|string',
                'guia_rem_destinatario_direccion' => 'required|string',
                'guia_rem_estado' => 'nullable|integer',
                'id_guia_rem' => 'nullable|integer',
            ], [
                'id_vehiculo.required' => 'El identificador es obligatorio.',
                'id_vehiculo.integer' => 'El identificador debe ser un número entero.',

                'guia_rem_numero_guia.required' => 'La guía es obligatorio.',
                'guia_rem_numero_guia.string' => 'La descripción debe ser una cadena de texto.',

                'guia_rem_fecha_emision.required' => 'La fecha de emisión es obligatoria.',
                'guia_rem_fecha_emision.date' => 'La fecha de emisión debe ser una fecha válida.',

                'guia_rem_motivo.required' => 'La descripción es obligatoria.',
                'guia_rem_motivo.string' => 'La descripción debe ser una cadena de texto.',

                'guia_rem_remitente_ruc.required' => 'El RUC del remitente es obligatorio',
                'guia_rem_remitente_ruc.string' => 'El RUC del remitente debe ser una cadena de texto.',
                'guia_rem_remitente_ruc.size' => 'El número RUC debe tener exactamente 11 caracteres.',

                'guia_rem_remitente_razon_social.required' => 'La razón social del remitente es obligatorio.',
                'guia_rem_remitente_razon_social.string' => 'La razón social del remitente debe ser una cadena de texto.',

                'guia_rem_remitente_direccion.required' => 'La dirección del remitente es obligatorio.',
                'guia_rem_remitente_direccion.string' => 'La dirección del remitente debe ser una cadena de texto.',

                'guia_rem_destinatario_ruc.required' => 'El RUC del destinatario es obligatorio',
                'guia_rem_destinatario_ruc.string' => 'El RUC del destinatario debe ser una cadena de texto.',
                'guia_rem_destinatario_ruc.size' => 'El número RUC debe tener exactamente 11 caracteres.',

                'guia_rem_destinatario_razon_social.required' => 'La razón social del destinatario es obligatorio.',
                'guia_rem_destinatario_razon_social.string' => 'La razón social del destinatario debe ser una cadena de texto.',

                'guia_rem_destinatario_direccion.required' => 'La direccón del destinatario es obligatorio.',
                'guia_rem_destinatario_direccion.string' => 'La direccón del destinatario debe ser una cadena de texto.',

                'id_despacho_venta.required' => 'Debes seleccionar una factura.',
                'id_despacho_venta.integer' => 'La factura debe ser un número entero.',

                'guia_rem_estado.integer' => 'El identificador debe ser un número entero.',

                'id_nota_credito.integer' => 'El identificador debe ser un número entero.',
            ]);

            if (!$this->id_guia_rem) {
                if (!Gate::allows('create_guia_remision')) {
                    session()->flash('error-modal', 'No tiene permisos para crear.');
                    return;
                }
                $validar = DB::table('guias_remisiones')->where('guia_rem_numero_guia', '=', $this->guia_rem_numero_guia)->exists();
                if (!$validar) {
                    $microtime = microtime(true);
                    DB::beginTransaction();
                    $guia_remision_save = new Guiaremision();
                    $guia_remision_save->id_users = Auth::id();
                    $guia_remision_save->id_vehiculo = $this->id_vehiculo;
                    $guia_remision_save->guia_rem_numero_guia = $this->guia_rem_numero_guia;
                    $guia_remision_save->guia_rem_fecha_emision = $this->guia_rem_fecha_emision;
                    $guia_remision_save->guia_rem_fecha_traslado = Carbon::now('America/Lima');
                    $guia_remision_save->guia_rem_motivo = $this->guia_rem_motivo;
                    $guia_remision_save->guia_rem_remitente_ruc = $this->guia_rem_remitente_ruc;
                    $guia_remision_save->guia_rem_remitente_razon_social = $this->guia_rem_remitente_razon_social;
                    $guia_remision_save->guia_rem_remitente_direccion = $this->guia_rem_remitente_direccion;
                    $guia_remision_save->guia_rem_destinatario_ruc = $this->guia_rem_destinatario_ruc;
                    $guia_remision_save->guia_rem_destinatario_razon_social = $this->guia_rem_destinatario_razon_social;
                    $guia_remision_save->guia_rem_destinatario_direccion = $this->guia_rem_destinatario_direccion;
                    $guia_remision_save->guia_rem_estado_aprobacion = 0;
                    $guia_remision_save->guia_rem_microtime = $microtime;
                    $guia_remision_save->guia_rem_estado = 1;

                    if ($guia_remision_save->save()) {
                        DB::commit();
                        $this->dispatch('hideModal');
                        session()->flash('success', 'Registro guardado correctamente.');
                    } else {
                        DB::rollBack();
                        session()->flash('error-modal', 'Ocurrió un error al guardar.');
                        return;
                    }
                } else {
                    session()->flash('error-modal', 'El número de guía ingresado ya ha sido registrado.');
                    return;
                }
            } else { // UPDATE
                if (!Gate::allows('update_guia_remision')) {
                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
                    return;
                }

                // Validar si la placa ya está registrada en otro vehículo
                $validar_update = DB::table('guias_remisiones')
                    ->where('id_guia_rem', '<>', $this->id_guia_rem)
                    ->where('guia_rem_numero_guia', '=', $this->guia_rem_numero_guia)
                    ->exists();
                if (!$validar_update) {
                    $guia_remision_update = Guiaremision::findOrFail($this->id_guia_rem);
                    // Verificar si el estado de aprobación es 1 y si los campos relevantes han cambiado
                    if (
                        $guia_remision_update->guia_rem_estado_aprobacion == 1 &&
                        ($guia_remision_update->guia_rem_motivo != $this->guia_rem_motivo ||
                            $guia_remision_update->guia_rem_fecha_emision != $this->guia_rem_fecha_emision ||
                            $guia_remision_update->guia_rem_numero_guia != $this->guia_rem_numero_guia)
                    ) {
                        $guia_remision_update->guia_rem_estado_aprobacion = 0;
                    }

                    DB::beginTransaction();
                    $guia_remision_update->id_vehiculo = $this->id_vehiculo;
                    $guia_remision_update->guia_rem_numero_guia = $this->guia_rem_numero_guia;
                    $guia_remision_update->guia_rem_fecha_emision = $this->guia_rem_fecha_emision;
                    $guia_remision_update->guia_rem_motivo = $this->guia_rem_motivo;
                    $guia_remision_update->guia_rem_remitente_ruc = $this->guia_rem_remitente_ruc;
                    $guia_remision_update->guia_rem_remitente_razon_social = $this->guia_rem_remitente_razon_social;
                    $guia_remision_update->guia_rem_remitente_direccion = $this->guia_rem_remitente_direccion;
                    $guia_remision_update->guia_rem_destinatario_ruc = $this->guia_rem_destinatario_ruc;
                    $guia_remision_update->guia_rem_destinatario_razon_social = $this->guia_rem_destinatario_razon_social;
                    $guia_remision_update->guia_rem_destinatario_direccion = $this->guia_rem_destinatario_direccion;

                    if (!$guia_remision_update->save()) {
                        DB::rollBack();
                        session()->flash('error', 'No se pudo actualizar el registro.');
                        return;
                    }
                } else {
                    session()->flash('error', 'El número de guís ingresado ya ha sido registrado.');
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
            session()->flash('error', 'Ocurrió un error al guardar el registro: ' . $e->getMessage());
        }
    }

    public function cambio_estado($id_notCre){
        $id = base64_decode($id_notCre);
        if ($id) {
            $this->id_guia_rem = $id;
            $this->messageGuiaRem = "¿Está seguro de aprobar esta guía de remisión?";
        }
    }

    public function cambiar_aprobacion_guiar(){
        try {
            if (!Gate::allows('cambiar_aprobacion_guiar')) {
                session()->flash('error_pre_pro', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }
            $this->validate([
                'id_guia_rem' => 'required|integer',
            ], [
                'id_guia_rem.required' => 'El identificador es obligatorio.',
                'id_guia_rem.integer' => 'El identificador debe ser un número entero.',
            ]);
            DB::beginTransaction();
            $guiaRem = Guiaremision::find($this->id_guia_rem);

            if ($guiaRem) {
                $guiaRem->guia_rem_estado_aprobacion = 1;

                if ($guiaRem->save()) {
                    DB::commit();
                    $this->dispatch('hideModalAprobacion');
                    session()->flash('success', 'Guía de remisión aprobada.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo cambiar el estado del registro.');
                }
            } else {
                DB::rollBack();
                session()->flash('error', 'La guía de remisión no existe.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al aceptar la factura. Por favor, inténtelo nuevamente.');
        }
    }
}
