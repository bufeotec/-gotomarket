<?php

namespace App\Livewire\Configuracion;

use App\Livewire\Intranet\Navegation;
use App\Models\Logs;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Gate;
class Menus extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $menus;
    public $urlActual;

    public function __construct()
    {
        $this->logs = new Logs();
        $this->menus = new Menu();
    }
    public function mount()
    {
        $this->urlActual = explode('.', Request::route()->getName());
    }
    public $search_menus;
    public $pagination_menus = 10;

    protected $listeners = ['refresh_table_component_menu' => 'render'];

    /* CAMPOS PARA EL GUARDADO DEL MENÚ */
    public $name = "";
    public $controller = "";
    public $icons = "";
    public $order = "";
    public $id_menu = "";
    public $show = false;
    public $statusMenu = "";
    public $messageDelete = "";

    public function render()
    {
        try {
            $menus = $this->menus->listar_menus_livewire($this->search_menus,$this->pagination_menus);
            return view('livewire.configuracion.menus',compact('menus'));
        }catch (\Exception $e) {
            $this->logs->insertarLog($e);
        }
    }

    public function saveMenu()
    {
        try {
            $this->validate([
                'name' => 'required|string',
                'controller' => 'required|string',
                'icons' => 'required|string',
                'order' => 'required|integer',
                'id_menu' => 'nullable|integer',
            ], [
                'name.required' => 'El nombre del menú es obligatorio.',
                'name.string' => 'El nombre del menú debe ser una cadena de texto.',
                'controller.required' => 'El nombre del controlador es obligatorio.',
                'controller.string' => 'El nombre del controlador debe ser una cadena de texto.',
                'icons.required' => 'El ícono es obligatorio.',
                'icons.string' => 'El ícono debe ser una cadena de texto.',
                'order.required' => 'El número de orden es obligatorio.',
                'order.integer' => 'El número de orden debe ser un número entero.',
                'id_menu.integer' => 'El identificador debe ser un número entero.',
            ]);

            if (!$this->id_menu) { // INSERT
                if (!Gate::allows('create_menus')) {
                    session()->flash('error', 'No tiene permisos para crear menús.');
                    return;
                }

                $val = $this->menus->validar_permiso($this->controller);
                if ($val) {
                    session()->flash('error', 'Ya existe un permiso asociado con el nombre del controlador.');
                    return;
                }

                $microtime = microtime(true);

                DB::beginTransaction();
                $menu = new Menu();
                $menu->menu_name = $this->name;
                $menu->menu_controller = $this->controller;
                $menu->menu_icons = $this->icons;
                $menu->menu_order = $this->order;
                $menu->menu_show = $this->show ? 1 : 0;
                $menu->menu_status = 1;
                $menu->menu_microtime = $microtime;

                if ($menu->save()) {
                    $menucreated = $this->menus->buscar_menu_x_microtime($microtime);
                    if ($menucreated) {
                        $permission = new Permission();
                        $permission->id_menu = $menucreated->id_menu;
                        $permission->id_submenu = null;
                        $permission->name = $this->controller;
                        $permission->permissions_group = 1;
                        $permission->permissions_group_id = $menucreated->id_menu;
                        $permission->permission_status = 1;
                        if ($permission->save()) {
                            $role = Role::find(1);
                            if ($role) {
                                $permission->syncRoles([$role->id]);
                                DB::commit();
                                // Emitir el evento al componente sidebarNew
                                $this->dispatch('refresh_sidebar_menu',$this->urlActual)->to(Navegation::class);
                                $this->dispatch('hideModal');
                                session()->flash('success', 'Registro guardado correctamente.');
                            } else {
                                DB::rollBack();
                                session()->flash('error', 'No se pudo asignar el rol, el rol con ID 1 no existe.');
                                return;
                            }
                        } else {
                            DB::rollBack();
                            session()->flash('error', 'Ha ocurrido un error al guardar el permiso relacionado con el menú.');
                            return;
                        }
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'No se encontró el menú creado.');
                        return;
                    }
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

                // Obtener el menú actual y los datos del permiso relacionado
                $dato_menu = $this->menus->listar_menu_x_id($this->id_menu);
                $datos_permiso_actual = $this->menus->permisos_datos($dato_menu->menu_controller);
                $nuevo_permiso_datos = $this->menus->permisos_datos($this->controller);

                // Verificar si el nombre del controlador ya está en uso por otro menú
                if ($nuevo_permiso_datos && $nuevo_permiso_datos->id_menu !== $this->id_menu) {
                    session()->flash('error', 'El nombre del controlador ya está asociado a otro permiso.');
                    return;
                }
                DB::beginTransaction();

                // Actualizar los datos del menú
                $menu = Menu::findOrFail($this->id_menu);
                $menu->menu_name = $this->name;
                $menu->menu_controller = $this->controller;
                $menu->menu_icons = $this->icons;
                $menu->menu_order = $this->order;
                $menu->menu_show = $this->show ? 1 : 0;

                if (!$menu->save()) {
                    session()->flash('error', 'No se pudo actualizar el menú.');
                    return;
                }

                // Actualizar los datos del permiso relacionado
                $permiso = Permission::findOrFail($datos_permiso_actual->id);
                $permiso->name = $this->controller;

                if (!$permiso->save()) {
                    session()->flash('error', 'No se pudo actualizar el permiso relacionado.');
                    return;
                }

                DB::commit();
                $this->dispatch('refresh_sidebar_menu',$this->urlActual)->to(Navegation::class);

//                $this->dispatch('refresh_sidebar_menu')->to(sidebarNew::class);
                $this->dispatch('hideModal');
                session()->flash('success', 'Menú actualizado correctamente.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }

    public function clear_form(){
        $this->name = "";
        $this->controller = "";
        $this->icons = "";
        $this->order = "";
        $this->id_menu = "";
        $this->statusMenu = "";
        $this->show = false;
    }
    public function edit_data($id){
        $menuEdit = Menu::find(base64_decode($id));
        if ($menuEdit){
            $this->name = $menuEdit->menu_name;
            $this->controller = $menuEdit->menu_controller;
            $this->icons = $menuEdit->menu_icons;
            $this->order = $menuEdit->menu_order;
            $this->id_menu = $menuEdit->id_menu;
            $this->show = $menuEdit->menu_show == 1 ? true : false;
        }
    }
    public function btn_disable($id_menu,$esta){
        $id = base64_decode($id_menu);
        $status = $esta;
        if ($id){
            $this->id_menu = $id;
            $this->statusMenu = $status;
            if ($status == 0){
                $this->messageDelete = "¿Está seguro que desea deshabilitar este menú?";
            }else{
                $this->messageDelete = "¿Está seguro que desea habilitar este menú?";
            }
        }
    }

    public function disable_menu(){
        try {

            if (!Gate::allows('disable_menus')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados del menú.');
                return;
            }


            $this->validate([
                'id_menu' => 'required|integer',
                'statusMenu' => 'required|integer',
            ], [
                'id_menu.required' => 'El identificador es obligatorio.',
                'id_menu.integer' => 'El identificador debe ser un número entero.',

                'statusMenu.required' => 'El estado es obligatorio.',
                'statusMenu.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $menu_delete = Menu::find($this->id_menu);
            $menu_delete->menu_status = $this->statusMenu;
            if ($menu_delete->save()) {
                DB::commit();
                $this->dispatch('refresh_sidebar_menu',$this->urlActual)->to(Navegation::class);
                $this->dispatch('hideModalDelete');
                if ($this->statusMenu == 0){
                    session()->flash('success', 'Registro deshabilitado correctamente.');
                }else{
                    session()->flash('success', 'Registro habilitado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado del menú.');
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
