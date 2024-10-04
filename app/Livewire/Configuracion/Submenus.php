<?php

namespace App\Livewire\Configuracion;

use App\Livewire\Intranet\sidebar;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Submenu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;

class Submenus extends Component
{
    use WithPagination, WithoutUrlPagination;

    /* ------------- CONSTRUCTION -------------*/
    private $logs;
    private $menu;
    private $submenu;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->menu = new Menu();
        $this->submenu = new Submenu();
    }
    /* ------------- FIN CONSTRUCTION ---------*/

    /* ------------- VARIABLES GLOBALES -------------*/
    public $id_menu = "";
    public $message_error = "";
    public $status_submenu_delete = "";
    public $search_submenus;
    public $pagination_submenus = 10;
    public $urlActual;
    /* ---------- FIN ARIABLES GLOBALES -------------*/

    /*---------------- VARIABLES DEL FORMULARIO -----------------*/
    public $name = "";
    public $function = "";
    public $order = "";
    public $show = "";
    public $id_submenu = "";


    /*------------ FIN VARIABLES DEL FORMULARIO -----------------*/
    public function mount($id)
    {
        $this->id_menu = $id;
        $this->urlActual = explode('.', Request::route()->getName());
    }

    public function render()
    {
        $listar_submenu = $this->submenu->listar_submenus_x_menu_general_livewire($this->id_menu, $this->search_submenus, $this->pagination_submenus);

        return view('livewire.configuracion.submenus',compact('listar_submenu'));
    }

    /* ------------------- METODOS DEL CRUD --------------------------*/

    public function clear_form_submenu(){
        $this->name = "";
        $this->function = "";
        $this->order = "";
        $this->id_submenu = "";
        $this->show = false;
    }
    public function list_submenu_information($id){
        $submenu_edit = Submenu::find(base64_decode($id));
        if ($submenu_edit){
            $this->name = $submenu_edit->submenu_name;
            $this->function = $submenu_edit->submenu_function;
            $this->order = $submenu_edit->submenu_order;
            $this->id_submenu = $submenu_edit->id_submenu;
            $this->show = $submenu_edit->submenu_show == 1 ? true : false;
        }
    }
    public function activate_permissions($id){
        $id_submenus = base64_decode($id);
        if ($id_submenus){
            $this->dispatch('listar_permisos_submenu_events', $id_submenus);
        }
    }
    public function button_to_disable_submenu($id_submenu,$sta){
        $id = base64_decode($id_submenu);
        $status = $sta;
        if ($id){
            $this->id_submenu = $id;
            $this->status_submenu_delete = $status;
            if ($status == 0){
                $this->message_error = "¿Está seguro que desea deshabilitar este submenú?";
            }else{
                $this->message_error = "¿Está seguro que desea habilitar este submenú?";
            }
        }
    }
    public function change_submenu_state(){
        try {
            if (!Gate::allows('disable_submenu')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados del submenús.');
                return;
            }

            $this->validate([
                'id_submenu' => 'required|integer',
                'status_submenu_delete' => 'required|integer',
            ], [
                'id_submenu.required' => 'El identificador es obligatorio.',
                'id_submenu.integer' => 'El identificador debe ser un número entero.',

                'status_submenu_delete.required' => 'El estado es obligatorio.',
                'status_submenu_delete.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $submenu_delete = Submenu::find($this->id_submenu);
            $submenu_delete->submenu_status = $this->status_submenu_delete;
            if ($submenu_delete->save()) {
                DB::commit();

                if ($this->status_submenu_delete == 0){
                    session()->flash('success', 'Registro deshabilitado correctamente.');
                }else{
                    session()->flash('success', 'Registro habilitado correctamente.');
                }
                $this->dispatch('refresh_sidebar_menu',$this->urlActual)->to(sidebar::class);
                $this->dispatch('hideModalDeleteSubmenu');
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo asignar el rol, el rol con ID 1 no existe.');
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_delete', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }
    public function savesSubmenu()
    {
        try {
            $this->validate([
                'name' => 'required|string',
                'function' => 'required|string',
                'show' => 'nullable|boolean',
                'order' => 'required|integer',
                'id_menu' => 'required|integer',
                'id_submenu' => 'nullable|integer',
            ], [
                'name.required' => 'El nombre del submenú es obligatorio.',
                'name.string' => 'El nombre del submenú debe ser una cadena de caracteres.',
                'function.required' => 'El nombre de la función es obligatorio.',
                'function.string' => 'El nombre de la función debe ser una cadena de caracteres.',
                'show.boolean' => 'El valor de visibilidad debe ser de tipo booleano.',
                'order.required' => 'El número de orden es obligatorio.',
                'order.integer' => 'El número de orden debe ser un número entero.',
                'id_menu.required' => 'El identificador del menú es obligatorio.',
                'id_menu.integer' => 'El identificador del menú debe ser un número entero.',
                'id_submenu.integer' => 'El identificador del submenú debe ser un número entero.',
            ]);


            if (!$this->id_submenu) { // INSERT
                if (!Gate::allows('crear_submenu')) {
                    session()->flash('form_error', 'No tiene permisos para crear submenús.');
                    return;
                }

                $val = $this->menu->validar_permiso($this->function);
                if ($val) {
                    session()->flash('form_error', 'Ya existe un permiso asociado con el nombre de la función.');
                    return;
                }
                $microtime = microtime(true);

                DB::beginTransaction();
                $submenu = new Submenu();
                $submenu->id_menu = $this->id_menu;
                $submenu->submenu_name = $this->name;
                $submenu->submenu_function = $this->function;
                $submenu->submenu_order = $this->order;
                $submenu->submenu_show = $this->show ? 1 : 0;
                $submenu->submenu_status = 1;
                $submenu->submenu_microtime = $microtime;
                if ($submenu->save()) {

                    $submenucreated = DB::table('submenus')->where('submenu_microtime','=',$microtime)->first();
                    if ($submenucreated) {
                        $permission = new Permission();
                        $permission->id_menu = null;
                        $permission->id_submenu = $submenucreated->id_submenu;
                        $permission->name = $this->function;
                        $permission->permissions_group = 2;
                        $permission->permissions_group_id = $submenucreated->id_submenu;
                        $permission->permission_status = 1;
                        if ($permission->save()) {
                            $role = Role::find(1);
                            if ($role) {
                                $permission->syncRoles([$role->id]);
                                DB::commit();
                                // Emitir el evento al componente sidebar
                                $this->dispatch('refresh_sidebar_menu',$this->urlActual)->to(sidebar::class);
                                $this->dispatch('hideModalSubmenu');
                                session()->flash('success', 'Registro guardado correctamente.');
                            } else {
                                DB::rollBack();
                                session()->flash('form_error', 'No se pudo asignar el rol, el rol con ID 1 no existe.');
                                return;
                            }
                        } else {
                            DB::rollBack();
                            session()->flash('form_error', 'Ha ocurrido un error al guardar el permiso relacionado con el submenú.');
                            return;
                        }
                    } else {
                        DB::rollBack();
                        session()->flash('form_error', 'No se encontró el submenú creado.');
                        return;
                    }
                } else {
                    DB::rollBack();
                    session()->flash('form_error', 'Ocurrió un error al guardar el submenú.');
                    return;
                }
            } else {
                if (!Gate::allows('update_submenu')) {
                    session()->flash('form_error', 'No tiene permisos para actualizar los submenús.');
                    return;
                }
                // Obtener el menú actual y los datos del permiso relacionado
                $dato_submenu = $this->submenu->listar_datos_x_id($this->id_submenu);
                $datos_permiso_actual = $this->menu->permisos_datos($dato_submenu->submenu_function);
                $nuevo_permiso_datos = $this->menu->permisos_datos($this->function);

                // Verificar si el nombre del controlador ya está en uso por otro menú
                if ($nuevo_permiso_datos && $nuevo_permiso_datos->id_submenu !== $this->id_submenu) {
                    session()->flash('form_error', 'El nombre del controlador ya está asociado a otro permiso.');
                    return;
                }
                DB::beginTransaction();

                // Actualizar los datos del menú
                $submenuUpdate = Submenu::findOrFail($this->id_submenu);
                $submenuUpdate->submenu_name = $this->name;
                $submenuUpdate->submenu_function = $this->function;
                $submenuUpdate->submenu_order = $this->order;
                $submenuUpdate->submenu_show = $this->show ? 1 : 0;
                if (!$submenuUpdate->save()) {
                    session()->flash('form_error', 'No se pudo actualizar el submenú.');
                    return;
                }
                // Actualizar los datos del permiso relacionado
                $permiso = Permission::findOrFail($datos_permiso_actual->id);
                $permiso->name = $this->function;

                if (!$permiso->save()) {
                    session()->flash('form_error', 'No se pudo actualizar el permiso relacionado.');
                    return;
                }

                DB::commit();
                $this->dispatch('refresh_sidebar_menu',$this->urlActual)->to(sidebar::class);
                $this->dispatch('hideModalSubmenu');
                session()->flash('success', 'Submenú actualizado correctamente.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('form_error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }

}
