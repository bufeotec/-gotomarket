<?php

namespace App\Livewire\Registroflete;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\Logs;
use App\Models\General;
use App\Models\TarifaMovil;
use App\Models\Vehiculo;
use App\Models\Tarifario;

class Tarifasmovil extends Component
{
    private $logs;
    private $tarifammovil;
    public $searchx = '';
    public $paginationx = 10;
//
//    public $desde;
//    public $hasta;
    public $id_vehiculo;
    public $id_tarifario;

    public $messageDeleteTm = "";
    public $vehiculo_estado; // Estado del vehículo

    public function __construct() {
        $this->logs = new Logs;
        $this->tarifammovil = new TarifaMovil;
    }

    public function mount() {
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');

    }

    public function render() {
        $listar_tarifamovil = $this->tarifammovil->listar_tarifamovil($this->searchx, $this->paginationx,'desc');
        $listar_vehiculo = $this->tarifammovil->listar_vehiculo();
        $listar_tarifario = $this->tarifammovil->listar_tarifario();
        return view('livewire.registroflete.tarifasmovil', compact('listar_vehiculo', 'listar_tarifamovil', 'listar_tarifario'));
    }



    public function btn_disable($id_vehiculo, $estado) {
        $id = base64_decode($id_vehiculo);
        $status = $estado;

        if ($id) {
            $this->id_vehiculo = $id;
            $this->vehiculo_estado = $status;
            $this->messageDeleteTm = $status == 0 ? "¿Está seguro que desea Cancelar este registro?" : "¿Está seguro que desea Aprobar este registro?";
        }
    }
    public function disable_tm() {
        try {
            if (!Gate::allows('disable_tm')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }

            $this->validate([
                'id_vehiculo' => 'required|integer',
                'vehiculo_estado' => 'required|integer',
            ], [
                'id_vehiculo.required' => 'El identificador es obligatorio.',
                'id_vehiculo.integer' => 'El identificador debe ser un número entero.',
                'vehiculo_estado.required' => 'El estado es obligatorio.',
                'vehiculo_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();

            // Obtener el vehículo
            $vehiculo = Vehiculo::find($this->id_vehiculo);
            if (!$vehiculo) {
                session()->flash('error_delete', 'Registro de vehículo no encontrado.');
                return;
            }

            // Cambiar el estado del vehículo
            $vehiculo->vehiculo_estado = $this->vehiculo_estado;
            $vehiculo->save();

            // Si el estado es 1, actualizar la fecha y hora en tarifas_movil
            $this->updateTarifaMovilFechaHora($this->id_vehiculo);


            DB::commit();
            $this->dispatch('hideModalDelete');
            session()->flash('success', $this->vehiculo_estado == 0 ? 'Vehículo deshabilitado correctamente.' : 'Vehículo habilitado correctamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }
    private function updateTarifaMovilFechaHora($id_vehiculo) {
        // Buscar o crear la entrada en tarifas_movil
        $tarifaMovil = TarifaMovil::where('id_vehiculo', $id_vehiculo)->first();

        if ($tarifaMovil) {
            // Si existe, actualizar la fecha y hora
            $tarifaMovil->updated_at = date('Y-m-d H:i:s'); // Aquí se usa now()
            $tarifaMovil->save();
        } else {
            // Si no existe, crear un nuevo registro
            TarifaMovil::create([
                'id_users' => Auth::id(),
                'id_tarifario' => $this->id_tarifario,
                'id_vehiculo' => $id_vehiculo,
                'updated_at' => now(),
            ]);
        }
    }

}
