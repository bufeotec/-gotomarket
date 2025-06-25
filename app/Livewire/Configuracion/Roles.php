<?php

namespace App\Livewire\Configuracion;

use App\Livewire\Intranet\Navegation;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Roles extends Component
{
    use WithPagination, WithoutUrlPagination;
    /* ----------- VARIABLES GLOBALES ----------------------*/
    public $search;
    public $pagination = 10;
    public $messageDelete = "";
    public $listar_permisos_general = array();
    /* ----------- FIN VARIABLES GLOBALES -----------------*/

    /* ----------- VARIABLES DEL CONSTRUCTOR ----------------------*/
    private $logs;
    /* ----------- FIN VARIABLES DEL CONSTRUCTOR ----------------------*/

    /* ----------------- DATOS DEL FORMULARIO ----------------*/
    public $id_rol;
    public $id_rol_permision;
    public $name;
    public $statusRol = "";
    public $check = [];
    public $urlActual = "";
    public $messageDeleteRole;
    /* ---------------- FIN DATOS DEL FORMULARIO -------------*/
    public function __construct()
    {
        $this->logs =  new Logs();
    }
    public function mount()
    {
        $this->urlActual = explode('.', \Illuminate\Support\Facades\Request::route()->getName());
    }
    public function render()
    {
        $search = $this->search;
        $roles = DB::table('roles')->where(function($q) use ($search) {$q->where('name', 'like', '%' . $search . '%');})->paginate($this->pagination);
        foreach($roles as $m){
            $m->permisos = DB::table('role_has_permissions')->select('permissions.name')
                ->join('permissions','permissions.id','=','role_has_permissions.permission_id')
                ->where('role_has_permissions.role_id','=',$m->id)
                ->count();
        }
//        $permissions = DB::table('permissions')->
        return view('livewire.configuracion.roles',compact('roles'));
    }
    public function clear_form(){
        $this->id_rol  = "";
        $this->name  = "";
        $this->statusRol  = "";
    }
    // guardar y editar
    public function save_roles(){
        try {
            $this->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                     Rule::unique('roles')->ignore($this->id_rol,'id'), // Ignorar id en caso de actualización
                ],
                'id_rol' => 'nullable|integer|exists:roles,id',
            ], [
                'name.required' => 'El nombre del rol es obligatorio.',
                'name.string' => 'El nombre del rol debe ser una cadena de texto válida.',
                'name.max' => 'El nombre del rol no puede exceder los 255 caracteres.',
                'name.unique' => 'El nombre del rol ya está registrado.',
            ]);

            if (!$this->id_rol) { // INSERT

                if (!Gate::allows('create_roles')) {
                    session()->flash('error', 'No tiene permisos para crear rol.');
                    return;
                }

                DB::beginTransaction();

                $rol = new Role();
                $rol->name = $this->name;
                $rol->roles_status = 1;

                if ($rol->save()) {
                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'El rol se ha registrado exitosamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo registrar el rol. Intente nuevamente.');
                }
            } else { // UPDATE

                if (!Gate::allows('update_roles')) {
                    session()->flash('error', 'No tiene permisos para actualizar el rol.');
                    return;
                }

                DB::beginTransaction();

                $roles = Role::findOrFail($this->id_rol);
                $roles->name = $this->name;
                if ($roles->save()) {
                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Los datos del rol se han actualizado correctamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo actualizar el rol. Intente nuevamente.');
                }
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error inesperado. Intente nuevamente más tarde.');
        }
    }
    // cambiar estado
    public function disable_roles()
    {
        try {
            if (!Gate::allows('disable_roles')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de los usuarios.');
                return;
            }

            $this->validate([
                'id_rol' => 'required|integer',
                'statusRol' => 'required|integer',
            ], [
                'id_rol.required' => 'El identificador de rol es obligatorio.',
                'id_rol.integer' => 'El identificador de es debe ser un número entero.',

                'statusRol.required' => 'El estado del rol es obligatorio.',
                'statusRol.integer' => 'El estado del rol debe ser un número entero.',
            ]);
            DB::beginTransaction();
            // Buscar el usuario y actualizar el estado
            $rol = Role::findOrFail($this->id_rol);
            $rol->roles_status = $this->statusRol;
            if ($rol->save()) {
                DB::commit();
                $this->dispatch('hideModalDelete');
                $action = $this->statusRol == 0 ? 'deshabilitado' : 'habilitado';
                session()->flash('success', "El rol ha sido {$action} correctamente.");
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'Ocurrió un error al actualizar el estado del rol. Intente nuevamente.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error inesperado. Por favor, inténtelo nuevamente.');
        }
    }
    //
    public function btn_disable($id_rol,$esta){
        $id = base64_decode($id_rol);
        $status = $esta;
        if ($id){
            $this->id_rol = $id;
            $this->statusRol = $status;
            if ($status == 0){
                $this->messageDeleteRole = "¿Está seguro que desea deshabilitar este rol?";
            }else{
                $this->messageDeleteRole = "¿Está seguro que desea habilitar este rol?";
            }
        }
    }
    public function edit_roles($id){
        $rol_editar = Role::find(base64_decode($id));
        if ($rol_editar){
            $this->name = $rol_editar->name;
            $this->id_rol = $rol_editar->id;
        }
    }
    public function listar_permissions_roles($id){
        try {
            $id_rol = base64_decode($id);
            if ($id_rol){
                $this->check = [];
                $this->id_rol_permision = $id_rol;
                $listar_permisos = DB::table('permissions as p')
                    ->join('menus','menus.id_menu','=','p.permissions_group_id')
                    ->where([['p.permission_status','=',1],['p.permissions_group','=',1]])->get();

                foreach ($listar_permisos as $li){
                    $perMenu = DB::table('role_has_permissions')->where([['permission_id','=',$li->id],['role_id','=',$id_rol]])->first();
                    if ($perMenu){
                        $this->check[] = $li->id;
                    }

                    $li->sub = DB::table('permissions as p')
                        ->join('submenus as s','s.id_submenu','=','p.permissions_group_id')
                        ->where('s.id_menu','=',$li->id_menu)
                        ->where('p.permission_status','=',1)
                        ->where('p.permissions_group','=',2)
                        ->get();
                    foreach($li->sub as $se){
                        $peSub = DB::table('role_has_permissions')->where([['permission_id','=',$se->id],['role_id','=',$id_rol]])->first();
                        if ($peSub){
                            $this->check[] = $se->id;
                        }

                        $se->permisos = DB::table('permissions as p')
                            ->where('p.permissions_group_id','=',$se->id_submenu)
                            ->where('p.permission_status','=',1)
                            ->where('p.permissions_group','=',3)
                            ->get();

                        foreach ($se->permisos as $per){
                            $pePer = DB::table('role_has_permissions')->where([['permission_id','=',$per->id],['role_id','=',$id_rol]])->first();
                            if ($pePer){
                                $this->check[] = $per->id;
                            }
                        }
                    }
                }
                $this->listar_permisos_general = $listar_permisos;
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
        }
    }
//    public function listar_permissions_roles($id){
//        try {
//            $id_rol = base64_decode($id);
//            if ($id_rol) {
//                $this->listar_permisos_general = array();
//                $this->id_rol_permision = $id_rol;
//
//                // Consultar todos los permisos y sus relaciones en una sola consulta
//                $listar_permisos = DB::table('permissions as p')
//                    ->leftJoin('menus', 'menus.id_menu', '=', 'p.permissions_group_id')
//                    ->leftJoin('role_has_permissions as rp', function ($join) use ($id_rol) {
//                        $join->on('rp.permission_id', '=', 'p.id')
//                            ->where('rp.role_id', '=', $id_rol);
//                    })
//                    ->where('p.permission_status', '=', 1)
//                    ->where('p.permissions_group', '=', 1)
//                    ->select('p.*', 'rp.permission_id as has_permission', 'menus.menu_name as menu_name', 'menus.id_menu')
//                    ->get();
//
//                foreach ($listar_permisos as $li) {
//                    // Determinar si el rol tiene el permiso del menú
//                    $li->status_permissions_menu = $li->has_permission ? 1 : 0;
//
//                    // Submenús relacionados
//                    $li->sub = DB::table('permissions as p')
//                        ->leftJoin('submenus as s', 's.id_submenu', '=', 'p.permissions_group_id')
//                        ->leftJoin('role_has_permissions as rp', function ($join) use ($id_rol) {
//                            $join->on('rp.permission_id', '=', 'p.id')
//                                ->where('rp.role_id', '=', $id_rol);
//                        })
//                        ->where('s.id_menu', '=', $li->id_menu)
//                        ->where('p.permission_status', '=', 1)
//                        ->where('p.permissions_group', '=', 2)
//                        ->select('p.*', 'rp.permission_id as has_permission')
//                        ->get();
//
//                    foreach ($li->sub as $se) {
//                        $se->status_permissions_submenu = $se->has_permission ? 1 : 0;
//
//                        $se->permisos = DB::table('permissions as p')
//                            ->leftJoin('role_has_permissions as rp', function ($join) use ($id_rol) {
//                                $join->on('rp.permission_id', '=', 'p.id')
//                                    ->where('rp.role_id', '=', $id_rol);
//                            })
//                            ->where('p.permissions_group_id', '=', $se->id_submenu)
//                            ->where('p.permission_status', '=', 1)
//                            ->where('p.permissions_group', '=', 3)
//                            ->select('p.*', 'rp.permission_id as has_permission')
//                            ->get();
//
//                        foreach ($se->permisos as $per) {
//                            $per->status_permissions_permissions = $per->has_permission ? 1 : 0;
//                        }
//                    }
//                }
//
//                $this->listar_permisos_general = $listar_permisos;
//            }
//        } catch (\Exception $e) {
//            $this->logs->insertarLog($e);
//        }
//    }

//    public function save_roles_and_permissions(Request $request){
//        try {
//
//            $this->validate([
//                'name' => [
//                    'required',
//                    'string',
//                    'max:255',
//                ],
//                'id_rol' => 'required|integer|exists:roles,id',
//            ], [
//                'name.required' => 'El nombre del rol es obligatorio.',
//                'name.string' => 'El nombre del rol debe ser una cadena de texto válida.',
//                'name.max' => 'El nombre del rol no puede exceder los 255 caracteres.',
//            ]);
//
//            if (!$this->id_rol_permision) { // INSERT
//
//                if (!Gate::allows('create_roles')) {
//                    session()->flash('error_permissions', 'No tiene permisos para asignar los permisos al rol.');
//                    return;
//                }
//                DB::beginTransaction();
//
//                $rol = Role::find($this->id_rol_permision);
//                $rol->syncPermissions($request->check);
//                if ($rol->save()) {
//                    DB::commit();
//                    $this->dispatch('hideModalPermissions');
//                    session()->flash('success', 'El rol se ha registrado exitosamente.');
//                } else {
//                    DB::rollBack();
//                    session()->flash('error_permissions', 'No se pudo asignar los permisos al rol.');
//                }
//            }
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            $this->setErrorBag($e->validator->errors());
//        } catch (\Exception $e) {
//            DB::rollBack();
//            $this->logs->insertarLog($e);
//            session()->flash('error_permissions', 'Ocurrió un error inesperado. Intente nuevamente más tarde.');
//        }
//    }
    public function save_roles_and_permissions()
    {
        try {
            $this->validate([
                'id_rol_permision' => 'required|integer|exists:roles,id',
                'check' => 'array', // Validar que haya al menos un checkbox seleccionado
                'check.*' => 'integer|exists:permissions,id', // Validar que cada valor de checkbox sea un ID de permiso válido
            ], [
//                'check.required' => 'Debe seleccionar al menos un permiso.',
                'check.array' => 'Debe enviar una lista de permisos válida.',
//                'check.min' => 'Debe seleccionar al menos un permiso.',
                'check.*.integer' => 'Cada permiso debe ser un número entero.',
                'check.*.exists' => 'El permiso seleccionado no es válido.',
            ]);

            if ($this->id_rol_permision) { // INSERT
                if (!Gate::allows('assign_permissions_to_role')) {
                    session()->flash('error_permissions', 'No tiene permisos para asignar los permisos al rol.');
                    return;
                }

                DB::beginTransaction();

                $rol = Role::find($this->id_rol_permision);
                $permissions = Permission::whereIn('id', $this->check)->where('permission_status', 1)->get();
                $datosPermissions = [];
                foreach ($permissions as $per){
                    $datosPermissions[] = $per->name;
                }
                $rol->syncPermissions($datosPermissions);
                if ($rol->save()) {
                    DB::commit();
                    $this->dispatch('refresh_sidebar_menu',$this->urlActual)->to(Navegation::class);
                    session()->flash('success_permissions', 'El rol se ha registrado exitosamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error_permissions', 'No se pudo asignar los permisos al rol.');
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_permissions', 'Ocurrió un error inesperado. Intente nuevamente más tarde.');
        }
    }

}
