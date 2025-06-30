<?php

namespace App\Livewire\Configuracion;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use App\Models\Logs;
use App\Models\User;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Nuevoperfiles extends Component
{
    use WithPagination, WithoutUrlPagination;

    private $logs;
    private $user;
    public $check = [];
    public function __construct(){
        $this->logs = new Logs();
        $this->user = new User();
    }
    public $codigo_perfil = '';
    public $urlActual;
    public $id_users = '';
    public $id_perfil;
    public $name = '';
    public $rol_descripcion = '';
    public $menuSeleccionados = [];
    public $users_seleccionados = [];
    public $submenuSeleccionados = [];
    public $permisosSeleccionados = [];
    public $rol_vendedor = 0;
    public $listar_permisos_general = array();
    public function mount($id_perfil=null){
        $this->id_perfil = $id_perfil;
        $this->urlActual = explode('.', Request::route()->getName());

        // Solo inicializar si es un nuevo perfil (sin ID)
        if(!$this->id_perfil) {
            $this->resetear_vista();
        } else {
            $this->cargar_datos();
        }
    }

    public function render(){
        // OBTENER MENUS PRINCIPALES
        $menus_show = DB::table('menus')
            ->where('menu_show', '=', 1)
            ->get();

        $this->check = [];
        $listar_permisos = DB::table('permissions as p')
            ->join('menus','menus.id_menu','=','p.permissions_group_id')
            ->where([['p.permission_status','=',1],['p.permissions_group','=',1]])
            ->where([['menus.menu_show','=',1]])
            ->get();

        foreach ($listar_permisos as $li){
            $perMenu = DB::table('role_has_permissions')->where([['permission_id','=',$li->id],['role_id','=',$this->id_perfil]])->first();
            if ($perMenu){
                $this->check[] = $li->id;
            }

            $li->sub = DB::table('permissions as p')
                ->join('submenus as s','s.id_submenu','=','p.permissions_group_id')
                ->where('s.id_menu','=',$li->id_menu)
                ->where('s.submenu_show','=',1)
                ->where('p.permission_status','=',1)
                ->where('p.permissions_group','=',2)
                ->whereNotIn('s.submenu_name', ['Menus', 'Iconos', 'Empresas'])
                ->get();
            foreach($li->sub as $se){

                $peSub = DB::table('role_has_permissions')->where([['permission_id','=',$se->id],['role_id','=',$this->id_perfil]])->first();
                if ($peSub){
                    $this->check[] = $se->id;
                }

                $se->permisos = DB::table('permissions as p')
                    ->where('p.permissions_group_id','=',$se->id_submenu)
                    ->where('p.permission_status','=',1)
                    ->where('p.permissions_group','=',3)
                    ->get();

                foreach ($se->permisos as $per){
                    $pePer = DB::table('role_has_permissions')->where([['permission_id','=',$per->id],['role_id','=',$this->id_perfil]])->first();
                    if ($pePer){
                        $this->check[] = $per->id;
                    }
                }


            }
        }
        $this->listar_permisos_general = $listar_permisos;

        // OBTENER SUBMENUS Y PERMISOS PARA CADA MENU
//        foreach ($menus_show as $menu) {
//            // Verificar si el menú está en los permisos del rol (solo en edición)
//            if ($this->id_perfil) {
//                $hasMenuPermission = DB::table('role_has_permissions as rhp')
//                    ->join('permissions as p', 'rhp.permission_id', '=', 'p.id')
//                    ->where('rhp.role_id', $this->id_perfil)
//                    ->where('p.id_menu', $menu->id_menu)
//                    ->exists();
//
//                if ($hasMenuPermission) {
//                    $this->menuSeleccionados[$menu->id_menu] = true;
//                }
//            }
//
//            // Obtener submenús para este menú
//            $menu->submenus = DB::table('submenus')
//                ->where('id_menu', $menu->id_menu)
//                ->where('submenu_show', 1)
//                ->where('submenu_status', 1)
//                ->whereNotIn('submenu_name', ['Menús', 'Iconos', 'Empresas'])
//                ->get();
//
//            // Obtener permisos para cada submenú
//            foreach ($menu->submenus as $submenu) {
//                $submenu->permisos = DB::table('permissions')
//                    ->where('permissions_group_id', $submenu->id_submenu)
//                    ->orderBy('descripcion')
//                    ->get();
//
//                // Verificar si el submenú está en los permisos del rol (solo en edición)
//                if ($this->id_perfil) {
//                    // Marcar submenú si tiene algún permiso asignado al rol
//                    $hasSubmenuPermission = DB::table('role_has_permissions as rhp')
//                        ->join('permissions as p', 'rhp.permission_id', '=', 'p.id')
//                        ->where('rhp.role_id', $this->id_perfil)
//                        ->where('p.permissions_group_id', $submenu->id_submenu)
//                        ->exists();
//
//                    if ($hasSubmenuPermission) {
//                        $this->submenuSeleccionados[$submenu->id_submenu] = true;
//                    }
//
//                    // Marcar permisos individuales
//                    foreach ($submenu->permisos as $permiso) {
//                        $perMenu = DB::table('role_has_permissions')->where([
//                            ['permission_id', '=', $permiso->id],
//                            ['role_id', '=', $this->id_perfil]
//                        ])->first();
//
//                        if ($perMenu) {
//                            $this->check[] = $permiso->id;
//                            $this->permisosSeleccionados[$permiso->id] = true;
//                        }
//                    }
//                }
//            }
//        }

        $listar_users = $this->user->listra_usuarios_activos();
        return view('livewire.configuracion.nuevoperfiles', compact('listar_users', 'menus_show'));
    }

    public function resetear_vista(){
        $this->name = '';
        $this->rol_descripcion = '';
        $this->permisosSeleccionados = [];
        $this->check = [];
        $this->users_seleccionados = [];
        $this->submenuSeleccionados = [];

        // Generar código para nuevo perfil
        $ultimoId = DB::table('roles')
            ->select('id')
            ->orderBy('id', 'desc')
            ->first();
        $nuevoNumero = $ultimoId ? ($ultimoId->id + 1) : 1;
        $this->codigo_perfil = 'PU' . $nuevoNumero;
    }
    public function cargar_datos(){
        $data_perfil = DB::table('roles')
            ->where('id', $this->id_perfil)
            ->first();

        if($data_perfil) {
            $this->name = $data_perfil->name;
            $this->rol_descripcion = $data_perfil->rol_descripcion;
            $this->rol_vendedor = $data_perfil->rol_vendedor == 1;
            $this->codigo_perfil = 'PU' . $data_perfil->id;

            // Cargar usuarios existentes
            $this->users_seleccionados = DB::table('model_has_roles as mhr')
                ->join('users as u', 'mhr.model_id', '=', 'u.id_users')
                ->where('mhr.role_id', $this->id_perfil)
                ->select('u.id_users', 'u.name', 'u.username')
                ->get()
                ->map(function($user) {
                    return [
                        'id_users' => $user->id_users,
                        'name' => $user->name,
                        'username' => $user->username
                    ];
                })
                ->toArray();
        }
    }

    public function agregar_usuario(){
        if (empty($this->id_users)) {
            session()->flash('error_select_user', 'Debe seleccionar un usuario.');
            return;
        }

        $usuario = $this->user->find($this->id_users);

        // Verificar si el usuario ya tiene un rol asignado
        $tieneRol = DB::table('model_has_roles')
            ->where('model_id', $usuario->id_users)
            ->exists();

        if ($tieneRol) {
            session()->flash('error_select_user', 'El usuario ya tiene un perfil asignado.');
            return;
        }

        // Verificar si el usuario ya fue agregado (en los seleccionados)
        $todos_usuarios = array_column($this->users_seleccionados, 'id_users');

        if (in_array($this->id_users, $todos_usuarios)) {
            session()->flash('error_select_user', 'Este usuario ya está seleccionado.');
        } else {
            $this->users_seleccionados[] = [
                'id_users' => $usuario->id_users,
                'name' => $usuario->name,
                'username' => $usuario->username
            ];
            $this->id_users = '';
        }
    }

    public function eliminar_usuario($index){
        if (isset($this->users_seleccionados[$index])) {
            unset($this->users_seleccionados[$index]);
            $this->users_seleccionados = array_values($this->users_seleccionados);
        }
    }

    public function guardar_editar_perfil() {
        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'rol_descripcion' => 'required|string',
                'check' => 'array',
                'check.*' => 'integer|exists:permissions,id',
            ], [
                'name.required' => 'El nombre del perfil es obligatorio.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no debe exceder los 255 caracteres.',

                'rol_descripcion.required' => 'La descripción del perfil es obligatoria.',
                'rol_descripcion.string' => 'La descripción debe ser una cadena de texto.',

                'check.array' => 'Debe enviar una lista de permisos válida.',
                'check.*.integer' => 'Cada permiso debe ser un número entero.',
                'check.*.exists' => 'El permiso seleccionado no es válido.',
            ]);

            DB::beginTransaction();

            // Mapeo de permisos principales a sus relacionados
            $relatedPermissionsMap = [
                /*29 => 193,*/
                49 => [187, 181], //Crear Usuario
                50 => 188, //Actualizar Usuario
                53 => [179, 182], //Crear Perfil
                54 => 183, //Actualizar Perfil
                217 => [139, 140], //Ver Detalle del Tracking
                143 => [168, 173, 169, 174, 170, 175, 171, 176, 172, 177], //Reportes Control Documentario
                149 => [76, 78], //Tarifas
                200 => 166, //Editar Programación
                225 => 165,
                178 => [157, 158, 159, 160] //Reporte Despacho y Transporte
            ];

            // Función para manejar permisos relacionados
            $handleRelatedPermissions = function($permissions) use ($relatedPermissionsMap) {
                $changed = false;

                // Primero eliminamos permisos relacionados cuyos padres no están seleccionados
                foreach ($relatedPermissionsMap as $main => $related) {
                    $relatedArray = is_array($related) ? $related : [$related];

                    if (!in_array($main, $permissions)) {
                        foreach ($relatedArray as $rel) {
                            if (in_array($rel, $permissions)) {
                                $permissions = array_diff($permissions, [$rel]);
                                $changed = true;
                            }
                        }
                    }
                }

                // Luego agregamos permisos relacionados para los padres seleccionados
                foreach ($relatedPermissionsMap as $main => $related) {
                    $relatedArray = is_array($related) ? $related : [$related];

                    if (in_array($main, $permissions)) {
                        foreach ($relatedArray as $rel) {
                            if (!in_array($rel, $permissions)) {
                                $permissions[] = $rel;
                                $changed = true;
                            }
                        }
                    }
                }

                return $changed ? array_unique($permissions) : $permissions;
            };

            if (!$this->id_perfil) { // CREAR NUEVO PERFIL
                if (!Gate::allows('guardar_perfil')) {
                    session()->flash('error', 'No tiene permisos para crear.');
                    return;
                }

                $role_save = new Role();
                $role_save->name = $this->name;
                $role_save->guard_name = 'web';
                $role_save->rol_descripcion = $this->rol_descripcion;
                $role_save->rol_tipo = 1;
                $role_save->rol_codigo = $this->codigo_perfil;
                $role_save->roles_status = 1;

                if ($role_save->save()) {
                    // Aplicar reglas de permisos relacionados
                    $selectedPermissions = $handleRelatedPermissions($this->check);

                    // Obtener permisos seleccionados
                    $permissions = Permission::whereIn('id', $selectedPermissions)
                        ->where('permission_status', 1)
                        ->get();

                    $datosPermissions = [];
                    foreach ($permissions as $per) {
                        $datosPermissions[] = $per->name;
                    }

                    // Asignar permisos al rol
                    $role_save->syncPermissions($datosPermissions);

                    DB::commit();
                    session()->flash('success', 'Perfil creado correctamente con sus permisos.');
                    $this->resetExcept(['id_perfil']);
                } else {
                    DB::rollBack();
                    session()->flash('error', 'Error al guardar el perfil.');
                }
            } else { // ACTUALIZAR PERFIL EXISTENTE
                if (!Gate::allows('actualizar_perfil')) {
                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
                    return;
                }

                $role_save = Role::find($this->id_perfil);
                if ($role_save) {
                    $role_save->name = $this->name;
                    $role_save->rol_descripcion = $this->rol_descripcion;

                    if ($role_save->save()) {
                        // 1. Obtener todos los permisos actuales del rol
                        $currentPermissions = DB::table('role_has_permissions')
                            ->where('role_id', $this->id_perfil)
                            ->pluck('permission_id')
                            ->toArray();

                        // 2. Obtener todos los permisos que se muestran en la vista
                        $displayedPermissionIds = [];
                        foreach ($this->listar_permisos_general as $menu) {
                            $displayedPermissionIds[] = $menu->id;
                            foreach ($menu->sub as $submenu) {
                                $displayedPermissionIds[] = $submenu->id;
                                foreach ($submenu->permisos as $permiso) {
                                    $displayedPermissionIds[] = $permiso->id;
                                }
                            }
                        }

                        // 3. Separar los permisos en dos grupos:
                        $permissionsToKeep = array_diff($currentPermissions, $displayedPermissionIds);

                        // 4. Aplicar reglas de permisos relacionados a los nuevos seleccionados
                        $selectedPermissions = $handleRelatedPermissions($this->check);

                        // 5. Filtrar permissionsToKeep para eliminar permisos relacionados no deseados
                        $permissionsToKeep = array_filter($permissionsToKeep, function($perm) use ($selectedPermissions, $relatedPermissionsMap) {
                            // Verificar si el permiso es uno de los relacionados en cualquier grupo
                            foreach ($relatedPermissionsMap as $main => $related) {
                                $relatedArray = is_array($related) ? $related : [$related];

                                if (in_array($perm, $relatedArray)) {
                                    // Solo mantenerlo si el permiso principal está seleccionado
                                    return in_array($main, $selectedPermissions);
                                }
                            }
                            return true;
                        });

                        // 6. Combinar los permisos
                        $finalPermissions = array_merge($permissionsToKeep, $selectedPermissions);

                        // 7. Obtener los nombres de los permisos para la sincronización
                        $permissions = Permission::whereIn('id', $finalPermissions)
                            ->where('permission_status', 1)
                            ->get();

                        $datosPermissions = [];
                        foreach ($permissions as $per) {
                            $datosPermissions[] = $per->name;
                        }

                        // 8. Sincronizar todos los permisos
                        $role_save->syncPermissions($datosPermissions);

                        DB::commit();
                        session()->flash('success', 'Perfil actualizado correctamente con sus permisos.');
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'Error al actualizar el perfil.');
                    }
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

}
