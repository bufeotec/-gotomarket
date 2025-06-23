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
    public $users_seleccionados = [];
    public $submenuSeleccionados = [];
    public $permisosSeleccionados = [];
    public $rol_vendedor = 0;
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

    public function render()
    {
        // OBTENER MENUS PRINCIPALES
        $menus_show = DB::table('menus')
            ->where('menu_show', '=', 1)
            ->get();

        // OBTENER SUBMENUS PARA CADA MENU
        $this->check = [];
        foreach ($menus_show as $menu) {
            $menu->submenus = DB::table('submenus')
                ->where('id_menu', $menu->id_menu)
                ->where('submenu_show', 1)
                ->where('submenu_status', 1)
                ->whereNotIn('submenu_name', ['Menús', 'Iconos', 'Empresas'])
                ->get();

            // Obtener permisos para cada submenú
            foreach ($menu->submenus as $submenu) {
                $submenu->permisos = DB::table('permissions')
                    ->where('permissions_group',3)
                    ->where('permissions_group_id', $submenu->id_submenu)
                    ->orderBy('descripcion')
                    ->get();

                // Verificar si el submenú está en los permisos del rol
                if ($this->id_perfil) {
                    // Marcar submenú si tiene algún permiso asignado al rol
                    $hasPermission = DB::table('role_has_permissions as rhp')
                        ->join('permissions as p', 'rhp.permission_id', '=', 'p.id')
                        ->where('rhp.role_id', $this->id_perfil)
                        ->where('p.permissions_group_id', $submenu->id_submenu)
                        ->exists();

                    if ($hasPermission) {
                        $this->submenuSeleccionados[$submenu->id_submenu] = true;
                    }

                    // Marcar permisos individuales
                    foreach ($submenu->permisos as $permiso) {
                        $perMenu = DB::table('role_has_permissions')->where([
                            ['permission_id', '=', $permiso->id],
                            ['role_id', '=', $this->id_perfil]
                        ])->first();
                        if ($perMenu) {
                            $this->check[] = $permiso->id;
                            $this->permisosSeleccionados[$permiso->id] = true;
                        }
                    }
                }
            }
        }

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
            $this->rol_vendedor = $data_perfil->rol_vendedor;
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
            // Validar que se haya seleccionado al menos un usuario
            if (count($this->users_seleccionados) === 0) {
                session()->flash('error', 'Debes seleccionar al menos un usuario.');
                return;
            }

            $this->validate([
                'name' => 'required|string|max:255',
                'rol_descripcion' => 'required|string',
                'rol_vendedor' => 'required|boolean',
            ], [
                'name.required' => 'El nombre del perfil es obligatorio.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no debe exceder los 255 caracteres.',

                'rol_descripcion.required' => 'La descripción del perfil es obligatoria.',
                'rol_descripcion.string' => 'La descripción debe ser una cadena de texto.',

                'rol_vendedor.required' => 'Debe especificar si es perfil vendedor.',
                'rol_vendedor.boolean' => 'El valor debe ser verdadero o falso.',
            ]);

            DB::beginTransaction();

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
                $role_save->rol_vendedor = $this->rol_vendedor ? 1 : 0;
                $role_save->rol_codigo = $this->codigo_perfil;
                $role_save->roles_status = 1;

                if ($role_save->save()) {
                    // Asignar usuarios al rol
                    foreach ($this->users_seleccionados as $usuario) {
                        $user = User::find($usuario['id_users']);
                        if ($user) {
                            DB::table('model_has_roles')->insert([
                                'role_id' => $role_save->id,
                                'model_type' => 'App\Models\User',
                                'model_id' => $user->id_users
                            ]);
                        }
                    }

                    // Procesar submenús seleccionados
                    $permisosSubmenus = [];
                    foreach ($this->submenuSeleccionados as $id_submenu => $seleccionado) {
                        if ($seleccionado) {
                            // Buscar el permiso principal del submenú
                            $permisoSubmenu = DB::table('permissions')
                                ->where('id_submenu', $id_submenu)
                                ->first();

                            if ($permisoSubmenu) {
                                $permisosSubmenus[] = $permisoSubmenu->id;
                            }
                        }
                    }

                    // Obtener permisos normales seleccionados
                    $permisosNormales = array_keys(array_filter($this->permisosSeleccionados));

                    // Combinar ambos tipos de permisos
                    $permisosFinales = array_merge($permisosNormales, $permisosSubmenus);

                    // Asignar todos los permisos al rol
                    if (!empty($permisosFinales)) {
                        $role_save->syncPermissions($permisosFinales);
                    }

                    DB::commit();
                    session()->flash('success', 'Perfil creado correctamente con sus permisos.');
                    $this->resetExcept(['id_perfil']);
                } else {
                    DB::rollBack();
                    session()->flash('error', 'Error al guardar el perfil.');
                }
            } else { // ACTUALIZAR PERFIL EXISTENTE
                if (!Gate::allows('update_vehiculos')) {
                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
                    return;
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
