<?php

namespace App\Livewire\Gestiontransporte;

use App\Models\Logs;
use App\Models\TipoVehiculo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

class Tipovehiculos extends Component
{
    private $logs;
    private $tipovehiculo;
    public function __construct(){
        $this->logs = new Logs();
        $this->tipovehiculo = new Tipovehiculo();
    }

    /* ATRIBUTOS PARA GUARDAR TIPO VEHICULO */
    public $id_tipo_vehiculo = "";
    public $tipo_vehiculo_concepto = "";
    public $tipo_vehiculo_estado;

    public function render()
    {
        $listar_tipo_vehiculos = $this->tipovehiculo->listar_tipo_vehiculo();
        return view('livewire.gestiontransporte.tipovehiculos', compact('listar_tipo_vehiculos'));
    }

    #[On('limpiar_campo_tipo_vehiculo')]
    public function escuchar_limpieza_nombre(){
        $this->tipo_vehiculo_concepto = "";
    }

    public function saveTipoVehiculo(){
        try {
            $this->validate([
                'id_tipo_vehiculo' => 'nullable|integer',
                'tipo_vehiculo_concepto' => 'required|string',
                'tipo_vehiculo_estado' => 'nullable|integer',
            ], [
                'tipo_vehiculo_concepto.required' => 'El tipo de vehiculo debe ser es obligatorio.',
                'tipo_vehiculo_concepto.string' => 'El tipo de vehiculo debe ser una cadena de texto.',

                'id_tipo_vehiculo.integer' => 'El identificador debe ser un número entero.',
                'tipo_vehiculo_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            if (!$this->id_tipo_vehiculo) { // INSERT

                if (!Gate::allows('crear_tipo_vehiculo')) {
                    session()->flash('error', 'No tiene permisos para crear el servicios.');
                    return;
                }
                $microtime = microtime(true);
                DB::beginTransaction();
                $tipovehiculo_save = new TipoVehiculo();
                $tipovehiculo_save->id_users = Auth::id();
                $tipovehiculo_save->tipo_vehiculo_concepto = $this->tipo_vehiculo_concepto;
                $tipovehiculo_save->tipo_vehiculo_estado = 1;
                $tipovehiculo_save->tipo_vehiculo_microtime = $microtime;

                if ($tipovehiculo_save->save()) {
                    DB::commit();
                    // Emitir el evento al componente sidebar
                    $this->dispatch('refresh_select_tipo_vehiculo')->to(Vehiculos::class);
//                    $this->dispatch('hideModal');
                    $this->tipo_vehiculo_concepto = "";
                    session()->flash('success', 'Registro guardado correctamente.');

                } else {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar el menú.');
                    return;
                }
            } else {
                if (!Gate::allows('update_menus')) {
                    session()->flash('error', 'No tiene permisos para actualizar los menús.');
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

    public function disable_tipo_vehiculo($id_tipo_vehiculo, $tipo_vehiculo_estado) {
        try {
            if (!Gate::allows('disable_tipo_vehiculo')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados del servicio.');
                return;
            }

            // Decodificar el ID y asignar a la propiedad
            $id = base64_decode($id_tipo_vehiculo);
            $this->id_tipo_vehiculo = $id;
            $this->tipo_vehiculo_estado = $tipo_vehiculo_estado;

            $this->validate([
                'id_tipo_vehiculo' => 'required|integer',
                'tipo_vehiculo_estado' => 'required|integer',
            ], [
                'id_tipo_vehiculo.required' => 'El identificador es obligatorio.',
                'id_tipo_vehiculo.integer' => 'El identificador debe ser un número entero.',

                'tipo_vehiculo_estado.required' => 'El estado es obligatorio.',
                'tipo_vehiculo_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $tipovehiculo_delete = Tipovehiculo::find($this->id_tipo_vehiculo);
            if ($tipovehiculo_delete) {
                $tipovehiculo_delete->tipo_vehiculo_estado = $this->tipo_vehiculo_estado;
                if ($tipovehiculo_delete->save()) {
                    DB::commit();
                    $this->dispatch('refresh_select_tipo_vehiculo')->to(Vehiculos::class);
                    if ($this->tipo_vehiculo_estado == 0){
                        session()->flash('success', 'Registro deshabilitado correctamente.');
                    }else{
                        session()->flash('success', 'Registro habilitado correctamente.');
                    }
                } else {
                    DB::rollBack();
                    session()->flash('error_delete', 'No se pudo cambiar el estado del servicio.');
                }
            } else {
                session()->flash('error_delete', 'Servicio no encontrado.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }
}
