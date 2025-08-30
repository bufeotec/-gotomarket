<?php

namespace App\Livewire\Configuracion;

use App\Models\Logs;
use App\Models\Menu;
use App\Models\Submenu;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Gate;
class Permisos extends Component
{
    // PERMISOS
    public $permissions_name = "";
    public $descripcion = "";
    public $id_submenu_permisos = "";
    public $permissions_submenu = array();
    private $logs;
    private $menu_per;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->menu_per = new Menu();

    }
    #[On('listar_permisos_submenu_events')]
    public function submenu_permisos($id){
        $this->list_permissions_by_submenus($id);
    }

    public function render()
    {
        return view('livewire.configuracion.permisos');
    }
    public function form_permissions_per_view()
    {
        try {

            if (!Gate::allows('create_permissions_submenus')) {
                session()->flash('form_error_permissions', 'No tiene permisos para crear permisos.');
                return;
            }

            $this->validate([
                'permissions_name' => 'required|string|max:255',
                'descripcion' => 'required|string|max:255',
                'id_submenu_permisos' => 'required|integer',
            ], [
                'permissions_name.required' => 'Por favor, ingrese un nombre para el permiso.',
                'permissions_name.string' => 'El nombre del permiso debe contener solo texto.',
                'permissions_name.max' => 'El nombre del permiso no debe exceder los 255 caracteres.',

                'descripcion.required' => 'Por favor, ingrese una descripción para el permiso.',
                'descripcion.string' => 'La descripción del permiso debe contener solo texto.',
                'descripcion.max' => 'La descripción del permiso no debe exceder los 255 caracteres.',

                'id_submenu_permisos.required' => 'Seleccione un submenú para asociar el permiso.',
                'id_submenu_permisos.integer' => 'El ID del submenú debe ser un número válido.',
            ]);

            $validar = $this->menu_per->validar_permiso($this->permissions_name);

            if (!$validar) {
                DB::beginTransaction();

                $permission = new Permission();
                $permission->name = $this->permissions_name;
                $permission->descripcion = $this->descripcion;
                $permission->permissions_group = 3;
                $permission->permissions_group_id = $this->id_submenu_permisos;
                $permission->permission_status = 1;

                if ($permission->save()) {
                    $role = Role::find(1);
                    if ($role) {
                        $permission->syncRoles([$role->id]);
                        DB::commit();
                        $this->list_permissions_by_submenus($this->id_submenu_permisos);
                        $this->permissions_name = "";
                        session()->flash('success_permissions', 'Permiso creado correctamente.');
                    } else {
                        DB::rollBack();
                        session()->flash('form_error_permissions', 'No se encontró el rol. Verifique la configuración de roles.');
                        return;
                    }
                } else {
                    DB::rollBack();
                    session()->flash('form_error_permissions', 'Error al intentar guardar el permiso. Intente nuevamente.');
                    return;
                }
            } else {
                session()->flash('form_error_permissions', 'Ya existe un permiso con ese nombre. Utilice uno diferente.');
                return;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('form_error', 'Ocurrió un error inesperado al guardar el permiso. Intente nuevamente más tarde.');
        }
    }
    public function delete_permissions($id)
    {
        try {
            if (!Gate::allows('delete_permissions_submenus')) {
                session()->flash('form_error_permissions', 'No tiene permisos para eliminar los permisos.');
                return;
            }

            DB::beginTransaction();
            $permissionDelete = Permission::findOrFail($id);

            if ($permissionDelete->delete()) {
                DB::commit();
                session()->flash('success_permissions', 'Permiso eliminado correctamente.');
                $this->list_permissions_by_submenus($this->id_submenu_permisos);
            } else {
                DB::rollBack();
                session()->flash('form_error_permissions', 'No se pudo eliminar el permiso. Intente nuevamente.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('form_error_permissions', 'Ocurrió un error al intentar eliminar el permiso. Por favor, contacte al soporte si el problema persiste.');
        }
    }
    public function list_permissions_by_submenus($id){
        $this->permissions_submenu = array();
        $id_submenus = $id;
        if ($id_submenus){
            $this->permissions_name = "";
            $this->id_submenu_permisos = $id_submenus;
            $this->permissions_submenu = DB::table('permissions')
                ->where([['permissions_group_id','=',$id_submenus],['permission_status','=',1]])
                ->where('permissions_group','=',3)->get();
        }
    }
}
