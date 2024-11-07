<?php

namespace App\Livewire\Registroflete;

use App\Models\RegistrarHistorialUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Tarifario;
use App\Models\Logs;

class Validartarifas extends Component
{
    use WithPagination, WithoutUrlPagination;
    public $search_tarifario;
    public $pagination_tarifario = 10;
    public $id_tarifario = "";
    public $id_users = "";
    public $id_transportistas = "";
    public $id_tipo_servicio = "";
    public $id_tipo_vehiculo = "";
    public $id_ubigeo_salida = "";
    public $id_ubigeo_llegada = "";
    public $tarifa_cap_min = "";
    public $tarifa_cap_max = "";
    public $tarifa_monto = "";
    public $tarifa_tipo_bulto = "";
    public $tarifa_estado = "";
    public $tarifa_estado_aprobacion = "";
    public $messageValidarAprobacion = "";
    public $messageDeleteTarifario = "";
    public $historial_registros = [];
    public $detalles = [];
    private $logs;
    private $tarifario;
    private $registrar_historial;
    public function __construct(){
        $this->logs = new Logs();
        $this->tarifario = new Tarifario();
        $this->registrar_historial = new RegistrarHistorialUpdate();
    }
    public function render()
    {
        $tarifario = $this->tarifario->lista_tarifas_pendientes($this->search_tarifario,$this->pagination_tarifario);
        return view('livewire.registroflete.validartarifas', compact('tarifario'));
    }

    public function ver_detalle($id){
        $id_tarifario = base64_decode($id);
        $this->detalles = Tarifario::where('id_tarifario', $id_tarifario)->first();
    }
    public function ver_registro($id) {
        $tarifario_id = base64_decode($id);
        $this->historial_registros = RegistrarHistorialUpdate::where('id_tarifario', $tarifario_id)
            ->join('users as u', 'registrar_historial_updates.id_users', '=', 'u.id_users')
            ->orderBy('registrar_historial_updates.created_at', 'desc')
            ->get();
    }

    public function btn_validar_aprobacion($id_tarifa){
        $id = base64_decode($id_tarifa);
        if ($id) {
            $this->id_tarifario = $id;
            $this->tarifa_estado_aprobacion = 1;
            $this->messageValidarAprobacion = "¿Está seguro que desea aprobar este registro?";
        }
    }

    public function aprobar_tarifa(){
        try {
            if (!Gate::allows('aprobar_tarifa')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }

            $this->validate([
                'id_tarifario' => 'required|integer',
                'tarifa_estado_aprobacion' => 'required|integer',
            ], [
                'id_tarifario.required' => 'El identificador es obligatorio.',
                'id_tarifario.integer' => 'El identificador debe ser un número entero.',

                'tarifa_estado_aprobacion.required' => 'El estado es obligatorio.',
                'tarifa_estado_aprobacion.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $aprobar_tarifa = Tarifario::find($this->id_tarifario);
            $aprobar_tarifa->tarifa_estado_aprobacion = 1;

            if ($aprobar_tarifa->save()) {
                DB::commit();
                $this->dispatch('hideModalDelete');
                session()->flash('success', 'Registro aprobado correctamente.');
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

    public function btn_disable($id_tarifa, $esta){
        $id = base64_decode($id_tarifa);
        $status = $esta;
        if ($id) {
            $this->id_tarifario = $id;
            $this->tarifa_estado = $status;
            $this->messageDeleteTarifario = "¿Está seguro que desea deshabilitar este registro?";
        }
    }

    public function disable_tarifario_validar(){
        try {
            if (!Gate::allows('disable_tarifario_validar')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }

            $this->validate([
                'id_tarifario' => 'required|integer',
                'tarifa_estado' => 'required|integer',
            ], [
                'id_tarifario.required' => 'El identificador es obligatorio.',
                'id_tarifario.integer' => 'El identificador debe ser un número entero.',

                'tarifa_estado.required' => 'El estado es obligatorio.',
                'tarifa_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $tarifarioDelete = Tarifario::find($this->id_tarifario);
            $tarifarioDelete->tarifa_estado = 0;
            if ($tarifarioDelete->save()) {
                DB::commit();
                $this->dispatch('hideModalDelete');
                session()->flash('success', 'Registro deshabilitado correctamente.');
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
}
