<?php

namespace App\Livewire\Gestiontransporte;

use App\Livewire\Intranet\sidebar;
use App\Models\Logs;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Servicios extends Component
{
    private $logs;
    public function __construct()
    {
        $this->logs = new Logs();
    }
    /* ATRIBUTOS PARA GUARDAR SERVICIOS */
    public $name;
    public $id_servicios; // activar y desactivar servicios
    public $status; // 1 => desctivar , 0 =>activar
    /* FIN ATRIBUTOS PARA GUARDAR SERVICIOS */

    public function render()
    {
        $listar_servicios = []; // consulta¿;
        return view('livewire.gestiontransporte.servicios',compact('listar_servicios'));
    }


    public function saveServicios(){
        try {
            $this->validate([
                'name' => 'required|string',
                'id_servicios' => 'nullable|integer',
                'status' => 'nullable|integer',
            ], [
                'name.required' => 'El nombre del ser es obligatorio.',
                'name.string' => 'El nombre del menú debe ser una cadena de texto.',

                'id_servicios.integer' => 'El identificador debe ser un número entero.',
                'status.integer' => 'El esta debe ser un número entero.',
            ]);

            if (!$this->id_servicios) { // INSERT

                if (!Gate::allows('crear_servicios')) {
                    session()->flash('error', 'No tiene permisos para crear el servicios.');
                    return;
                }
                $microtime = microtime(true);
                DB::beginTransaction(); // encapsular los movimientos de la base de datos
                $menu = new Menu();
                $menu->menu_name = $this->name;
                $menu->menu_controller = $this->controller;
                $menu->menu_icons = $this->icons;
                $menu->menu_order = $this->order;
                $menu->menu_show = $this->show ? 1 : 0;
                $menu->menu_status = 1;
                $menu->menu_microtime = $microtime;

                if ($menu->save()) {
                    DB::commit();
                    // Emitir el evento al componente sidebar
                    $this->dispatch('refresh_select_servicios')->to(Transportistas::class);
//                    $this->dispatch('hideModal');
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
}
