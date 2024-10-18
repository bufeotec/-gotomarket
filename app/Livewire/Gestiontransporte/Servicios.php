<?php

namespace App\Livewire\Gestiontransporte;

use App\Livewire\Intranet\sidebar;
use App\Livewire\Registroflete\Tarifarios;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\TipoServicio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;
use mysql_xdevapi\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Servicios extends Component
{
    private $logs;
    private $tiposervicio;
    /* ATRIBUTOS PARA GUARDAR SERVICIOS */
    public $tipo_servicio_concepto = "";
    public $id_tipo_servicios = ""; // activar y desactivar servicios
    public $tipo_servicio_estado; // 1 => desctivar , 0 =>activar
    /* FIN ATRIBUTOS PARA GUARDAR SERVICIOS */
    public function __construct()
    {
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
    }
    public function render()
    {
        $listar_servicios = $this->tiposervicio->listar_tipo_servicios();
        return view('livewire.gestiontransporte.servicios',compact('listar_servicios'));
    }

    public function saveServicios(){
        try {
            $this->validate([
                'tipo_servicio_concepto' => 'required|string',
                'id_tipo_servicios' => 'nullable|integer',
                'tipo_servicio_estado' => 'nullable|integer',
            ], [
                'tipo_servicio_concepto.required' => 'El nombre del servicio debe ser es obligatorio.',
                'tipo_servicio_concepto.string' => 'El nombre del servicio debe ser una cadena de texto.',

                'id_tipo_servicios.integer' => 'El identificador debe ser un número entero.',
                'tipo_servicio_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            if (!$this->id_tipo_servicios) { // INSERT

                if (!Gate::allows('crear_servicios')) {
                    session()->flash('error', 'No tiene permisos para crear el servicios.');
                    return;
                }
                $microtime = microtime(true);
                DB::beginTransaction(); // encapsular los movimientos de la base de datos
                $tiposervicio_save = new TipoServicio();
                $tiposervicio_save->id_users = Auth::id();
                $tiposervicio_save->tipo_servicio_concepto = $this->tipo_servicio_concepto;
                $tiposervicio_save->tipo_servicio_estado = 1;
                $tiposervicio_save->tipo_servicio_microtime = $microtime;

                if ($tiposervicio_save->save()) {
                    DB::commit();
                    // Emitir el evento al componente sidebar
                    $this->dispatch('refresh_select_servicios')->to(Tarifarios::class);
//                    $this->dispatch('hideModal');
                    $this->tipo_servicio_concepto = "";
                    session()->flash('success', 'Registro guardado correctamente.');

                } else {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar el registro.');
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

    public function disable_servicio($id_tipo_servicios, $tipo_servicio_estado) {
        try {
            if (!Gate::allows('disable_servicio')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados del servicio.');
                return;
            }

            // Decodificar el ID y asignar a la propiedad
            $id = base64_decode($id_tipo_servicios);
            $this->id_tipo_servicios = $id;
            $this->tipo_servicio_estado = $tipo_servicio_estado;

            $this->validate([
                'id_tipo_servicios' => 'required|integer',
                'tipo_servicio_estado' => 'required|integer',
            ], [
                'id_tipo_servicios.required' => 'El identificador es obligatorio.',
                'id_tipo_servicios.integer' => 'El identificador debe ser un número entero.',

                'tipo_servicio_estado.required' => 'El estado es obligatorio.',
                'tipo_servicio_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $tiposervicio_delete = TipoServicio::find($this->id_tipo_servicios);
            if ($tiposervicio_delete) {
                $tiposervicio_delete->tipo_servicio_estado = $this->tipo_servicio_estado;
                if ($tiposervicio_delete->save()) {
                    DB::commit();
                    $this->dispatch('refresh_select_servicios')->to(Tarifarios::class);
                    if ($this->tipo_servicio_estado == 0){
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


    #[On('limpiar_nombre_convenio')]
    public function escuchar_limpieza_nombre(){
        $this->tipo_servicio_concepto = "";
    }
}
