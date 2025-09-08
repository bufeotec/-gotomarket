<?php

namespace App\Livewire\Configuracion;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Models\Logs;
use App\Models\User;
use App\Models\Usersvendedor;
use App\Models\General;
use App\Models\Vendedor;
use App\Models\Departamento;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Crearusuarios extends Component
{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    private $logs;
    private $user;
    private $vendedor;
    private $usersvendedor;
    private $general;
    private $departamento;
    public function __construct(){
        $this->logs = new Logs();
        $this->user = new User();
        $this->vendedor = new Vendedor();
        $this->usersvendedor = new Usersvendedor();
        $this->general = new General();
        $this->departamento = new Departamento();
    }

    public function mount($id_users=null){
        $this->ruta_img_default = "assets/images/faces/1.jpg";
        $this->dispatch('updateUserImagePreview', ['image' => asset($this->ruta_img_default)]);
        $this->id_users = $id_users;
        if(!$this->id_users) {
            $this->resetear_vista();
        } else {
            $this->cargar_datos();
        }
    }
    public $id_users;
    public $name;
    public $last_name;
    public $email;
    public $users_cargo;
    public $username;
    public $profile_picture;
    public $password;
    public $id_rol;
    public $ruta_img_default = "";
    public $id_vendedor= '';
    public $vendedor_seleccionados = [];
    public $perfil_seleccionado = [];
    public $rol_vendedor = false;
    public $users_dni = "";
    public $users_phone = "";
    public $id_departamento = "";
    public $id_provincia = "";
    public $id_distrito = "";
    public $provincias = [];
    public $distritos = [];

    public function render(){
        $listar_vendedores = $this->vendedor->listra_vendedores_activos();
        $listar_perfiles = $this->vendedor->listra_perfiles_activos();

        $listar_departamento = $this->departamento->lista_departamento();
        return view('livewire.configuracion.crearusuarios', compact('listar_vendedores', 'listar_perfiles', 'listar_departamento'));
    }

    public function deparTari(){
        $this->id_provincia = "";
        $this->id_distrito = "";
        $this->provincias = [];
        $this->distritos = [];
        $this->listar_provincias();
    }

    public function proviTari(){
        $this->listar_distritos();
    }

    public function listar_provincias(){
        $valor = $this->id_departamento;
        if ($valor) {
            $this->provincias = DB::table('provincias')->where('id_departamento', '=', $valor)->get();
        } else {
            $this->provincias = [];
            $this->id_provincia = "";
            $this->distritos = [];
            $this->id_distrito = "";
        }
    }

    public function listar_distritos(){
        $valor = $this->id_provincia;
        if ($valor) {
            $this->distritos = DB::table('distritos')->where('id_provincia', '=', $valor)->get();
        } else {
            $this->distritos = [];
            $this->id_distrito = "";
        }
    }

    public function resetear_vista(){
        $this->id_vendedor = '';
        $this->name = '';
        $this->username = '';
        $this->profile_picture = '';
        $this->users_cargo = '';
        $this->password = '';
        $this->vendedor_seleccionados = [];
        $this->perfil_seleccionado = [];
        $this->email = '';
        $this->last_name = '';
        $this->rol_vendedor = '';
        $this->users_dni = '';
        $this->users_phone = '';
        $this->id_departamento = '';
        $this->id_provincia = '';
        $this->id_distrito = '';
        $this->distritos = [];
        $this->provincias = [];
    }
    public function cargar_datos(){
        $usersEditar = User::find($this->id_users);
        if ($usersEditar) {
            // Cargar datos básicos del usuario
            $rol = DB::table('model_has_roles as mr')
                ->join('roles as r', 'r.id', '=', 'mr.role_id')
                ->where('mr.model_id', '=', $usersEditar->id_users)
                ->first();

            $this->password = null;
            $this->name = $usersEditar->name;
            $this->last_name = $usersEditar->last_name;
            $this->username = $usersEditar->username;
            $this->email = $usersEditar->email;
            $this->users_dni = $usersEditar->users_dni;
            $this->users_phone = $usersEditar->users_phone;
            $this->users_cargo = $usersEditar->users_cargo;
            $this->id_rol = $rol->id;
            $this->id_users = $usersEditar->id_users;
            $this->id_departamento = $usersEditar->id_departamento;
            $this->id_provincia = $usersEditar->id_provincia;
            $this->id_distrito = $usersEditar->id_distrito;
            // Cargar las provincias y distritos
            $this->provincias = DB::table('provincias')->where('id_departamento', $this->id_departamento)->get();
            $this->distritos = DB::table('distritos')->where('id_provincia', $this->id_provincia)->get();

            // Cargar imagen de perfil
            if (file_exists($usersEditar->profile_picture)) {
                $this->ruta_img_default = $usersEditar->profile_picture;
            } else {
                $this->ruta_img_default = "assets/images/faces/1.jpg";
            }
            $this->dispatch('updateUserImagePreview', ['image' => asset($this->ruta_img_default)]);

            // Cargar estado del perfil vendedor
            $this->rol_vendedor = $usersEditar->users_perfil_vendedor == 1;

            // Cargar perfil seleccionado
            $this->perfil_seleccionado = [[
                'id' => $rol->id,
                'rol_codigo' => $rol->rol_codigo,
                'name' => $rol->name
            ]];

            // Cargar vendedores asociados si es perfil vendedor
            if ($this->rol_vendedor) {
                $vendedoresAsociados = DB::table('users_vendedores as uv')
                    ->join('vendedores as v', 'v.id_vendedor', '=', 'uv.id_vendedor')
                    ->where('uv.id_users', $this->id_users)
                    ->where('uv.user_vendedor_estado', 1)
                    ->get();

                foreach ($vendedoresAsociados as $vendedor) {
                    $this->vendedor_seleccionados[] = [
                        'id_vendedor' => $vendedor->id_vendedor,
                        'vendedor_codigo_intranet' => $vendedor->vendedor_codigo_intranet,
                        'vendedor_codigo_vendedor_starsoft' => $vendedor->vendedor_codigo_vendedor_starsoft,
                        'vendedor_des' => $vendedor->vendedor_des
                    ];
                }
            }
        }
    }

    public function generateUsername(){
        $firstName = explode(' ', trim($this->name))[0];
        $lastName = explode(' ', trim($this->last_name))[0];
        $this->username = strtolower($firstName . '.' . $lastName);
    }

    // AGREGAR UN VENDEDOR
    public function agregar_vendedor(){
        if (empty($this->id_vendedor)) {
            session()->flash('error_select_vendedor', 'Debe seleccionar un vendedor.');
            return;
        }

        $vendedor = $this->vendedor->find($this->id_vendedor);

        // Verificar si el usuario ya fue agregado
        if (in_array($this->id_vendedor, array_column($this->vendedor_seleccionados, 'id_vendedor'))) {
            session()->flash('error_select_vendedor', 'Este vendedor ya está seleccionado.');
        } else {
            $this->vendedor_seleccionados[] = [
                'id_vendedor' => $vendedor->id_vendedor,
                'vendedor_codigo_intranet' => $vendedor->vendedor_codigo_intranet,
                'vendedor_codigo_vendedor_starsoft' => $vendedor->vendedor_codigo_vendedor_starsoft,
                'vendedor_des' => $vendedor->vendedor_des
            ];
            $this->id_vendedor = '';
        }
    }

    public function eliminar_vendedor($index){
        if (isset($this->vendedor_seleccionados[$index])) {
            unset($this->vendedor_seleccionados[$index]);
            $this->vendedor_seleccionados = array_values($this->vendedor_seleccionados);
        }
    }

    // AGREGAR UN PERFIL
    public function agregar_perfil(){
        // Que se haya seleccionado un perfil
        if (empty($this->id_rol)) {
            session()->flash('error_select_perfil', 'Debe seleccionar un perfil.');
            return;
        }

        // Que solo seleccione un perfil
        if (count($this->perfil_seleccionado) > 0) {
            session()->flash('error_select_perfil', 'Solo se puede seleccionar un perfil.');
            return;
        }

        // Buscar el perfil seleccionado
        $perfil = DB::table('roles')->where('id', $this->id_rol)->first();

        if ($perfil) {
            $this->perfil_seleccionado[] = [
                'id' => $perfil->id,
                'rol_codigo' => $perfil->rol_codigo,
                'name' => $perfil->name
            ];
            $this->id_rol = '';
        } else {
            session()->flash('error_select_perfil', 'El perfil seleccionado no existe.');
        }
    }

    public function eliminar_perfil($index){
        if (isset($this->perfil_seleccionado[$index])) {
            unset($this->perfil_seleccionado[$index]);
            $this->perfil_seleccionado = array_values($this->perfil_seleccionado);
        }
    }

//    FUNCIÓN DE GUARDADO
    public function save_usuario(){
        try {
            $this->validate([
                'id_departamento' => 'required|integer',
                'id_provincia' => 'required|integer',
                'id_distrito' => 'required|integer',
                'profile_picture' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'users_dni' => 'required|digits:8',
                'users_phone' => 'required|digits:9',
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users')->ignore($this->id_users,'id_users'),
                ],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($this->id_users,'id_users'),
                ],
                'users_cargo' => 'nullable|string',
            ], [
                'id_departamento.required' => 'El departamento es obligatorio.',
                'id_departamento.integer' => 'El departamento seleccionado no es válido.',

                'id_provincia.required' => 'La provincia es obligatoria.',
                'id_provincia.integer' => 'La provincia seleccionada no es válida.',

                'id_distrito.required' => 'El distrito es obligatorio.',
                'id_distrito.integer' => 'El distrito seleccionado no es válido.',

                'name.required' => 'El nombre es obligatorio.',
                'name.string' => 'El nombre debe ser una cadena de texto válida.',
                'name.max' => 'El nombre no puede exceder los 255 caracteres.',

                'last_name.required' => 'El apellido es obligatorio.',
                'last_name.string' => 'El apellido debe ser una cadena de texto válida.',
                'last_name.max' => 'El apellido no puede exceder los 255 caracteres.',

                'users_dni.required' => 'El DNI es obligatorio.',
                'users_dni.digits'   => 'El DNI debe tener exactamente 8 dígitos.',

                'users_phone.required'=> 'El celular es obligatorio.',
                'users_phone.digits'  => 'El celular debe tener exactamente 9 dígitos.',

                'username.required' => 'El nombre de usuario es obligatorio.',
                'username.string' => 'El nombre de usuario debe ser una cadena de texto válida.',
                'username.max' => 'El nombre de usuario no puede exceder los 255 caracteres.',
                'username.unique' => 'El nombre de usuario ya está registrado.',

                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'Debe proporcionar un correo electrónico válido.',
                'email.unique' => 'El correo electrónico ya está registrado.',

                'users_cargo.string' => 'El cargo debe ser una cadena de texto.',

                'profile_picture.file' => 'Debe cargar un archivo válido.',
                'profile_picture.mimes' => 'El archivo debe ser una imagen en formato JPG, JPEG o PNG.',
                'profile_picture.max' => 'La imagen no puede exceder los 2MB.',
            ]);

            if (count($this->perfil_seleccionado) === 0) {
                session()->flash('error_select_perfil', 'Debe seleccionar al menos un perfil.');
                return;
            }

            if ($this->rol_vendedor && count($this->vendedor_seleccionados) === 0) {
                session()->flash('error_select_vendedor', 'Debe seleccionar al menos un vendedor.');
                return;
            }

            if (!$this->id_users) { // INSERT
                if (!Gate::allows('guardar_usuario')) {
                    session()->flash('error', 'No tiene permisos para crear usuarios.');
                    return;
                }

                $this->validate([
                    'password' => 'required|string|min:8',
                ], [
                    'password.required' => 'La contraseña es obligatoria.',
                    'password.string' => 'La contraseña debe ser una cadena de texto válida.',
                    'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                ]);

                DB::beginTransaction();

                $usuario = new User();
                $usuario->id_departamento = $this->id_departamento;
                $usuario->id_provincia = $this->id_provincia;
                $usuario->id_distrito = $this->id_distrito;
                $usuario->name = $this->name;
                $usuario->last_name = $this->last_name;
                $usuario->email = $this->email;
                $usuario->username = $this->username;
                $usuario->password = bcrypt($this->password);
                $usuario->users_status = 1;
                $usuario->users_cargo = !empty($this->users_cargo) ? $this->users_cargo : null;

                if ($this->profile_picture) {
                    $usuario->profile_picture = $this->general->save_files($this->profile_picture, 'configuration/users');
                }
                $usuario->users_perfil_vendedor = $this->rol_vendedor ? 1 : 0;
                $usuario->users_dni = $this->users_dni;
                $usuario->users_phone = $this->users_phone;

                if ($usuario->save()) {
                    // Asignar el perfil seleccionado (solo uno)
                    $perfil = $this->perfil_seleccionado[0];
                    $role = Role::find($perfil['id']);
                    $usuario->assignRole($role->name);

                    // Guardar vendedores asociados solo si rol_vendedor está activo
                    if ($this->rol_vendedor) {
                        foreach ($this->vendedor_seleccionados as $vendedor) {
                            DB::table('users_vendedores')->insert([
                                'id_users' => $usuario->id_users,
                                'id_vendedor' => $vendedor['id_vendedor'],
                                'user_vendedor_estado' => 1,
                                'user_vendedor_microtime' => microtime(true)
                            ]);
                        }
                    }

                    DB::commit();
                    session()->flash('success', 'Usuario creado exitosamente.');
                    return redirect()->route('configuracion.usuarios');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'Error al guardar el usuario.');
                }
            } else { // ACTUALIZAR USUARIO
                if (!Gate::allows('actualizar_usuario')) {
                    session()->flash('error', 'No tiene permisos para actualizar los usuarios.');
                    return;
                }

                $usuario = User::findOrFail($this->id_users);
                $usuario->id_departamento = $this->id_departamento;
                $usuario->id_provincia = $this->id_provincia;
                $usuario->id_distrito = $this->id_distrito;
                $usuario->name = $this->name;
                $usuario->last_name = $this->last_name;
                $usuario->email = $this->email;
                $usuario->username = $this->username;
                $usuario->users_cargo = $this->users_cargo ?? null;
                $usuario->users_perfil_vendedor = $this->rol_vendedor ? 1 : 0;
                $usuario->users_dni = $this->users_dni;
                $usuario->users_phone = $this->users_phone;

                if ($this->password) {
                    $usuario->password = bcrypt($this->password);
                }

                if ($this->profile_picture) {
                    try {
                        if ($usuario->profile_picture && file_exists($usuario->profile_picture)) {
                            unlink($usuario->profile_picture);
                        }
                    } catch (\Exception $e) {}
                    $usuario->profile_picture = $this->general->save_files($this->profile_picture, 'configuration/users');
                }

                if ($usuario->save()) {
                    // Manejo del perfil
                    $perfil = $this->perfil_seleccionado[0];
                    $currentRole = $usuario->roles->first();

                    // Actualizar rol solo si es diferente al actual
                    if (!$currentRole || $currentRole->id != $perfil['id']) {
                        $usuario->syncRoles([$perfil['id']]);
                    }

                    // Manejo de vendedores
                    if ($this->rol_vendedor) {
                        // Obtener vendedores actuales del usuario
                        $currentVendedores = DB::table('users_vendedores')
                            ->where('id_users', $this->id_users)
                            ->where('user_vendedor_estado', 1)
                            ->pluck('id_vendedor')
                            ->toArray();

                        $selectedVendedores = array_column($this->vendedor_seleccionados, 'id_vendedor');

                        // Vendedores a desactivar
                        $toDeactivate = array_diff($currentVendedores, $selectedVendedores);
                        if (!empty($toDeactivate)) {
                            DB::table('users_vendedores')
                                ->where('id_users', $this->id_users)
                                ->whereIn('id_vendedor', $toDeactivate)
                                ->update(['user_vendedor_estado' => 0]);
                        }

                        // Vendedores a agregar
                        $toAdd = array_diff($selectedVendedores, $currentVendedores);
                        foreach ($toAdd as $vendedorId) {
                            // Verificar si ya existe pero está inactivo
                            $exists = DB::table('users_vendedores')
                                ->where('id_users', $this->id_users)
                                ->where('id_vendedor', $vendedorId)
                                ->first();

                            if ($exists) {
                                // Reactivar si existe pero está inactivo
                                DB::table('users_vendedores')
                                    ->where('id_users', $this->id_users)
                                    ->where('id_vendedor', $vendedorId)
                                    ->update([
                                        'user_vendedor_estado' => 1,
                                        'user_vendedor_microtime' => microtime(true)
                                    ]);
                            } else {
                                // Crear nuevo registro vendedor
                                DB::table('users_vendedores')->insert([
                                    'id_users' => $this->id_users,
                                    'id_vendedor' => $vendedorId,
                                    'user_vendedor_estado' => 1,
                                    'user_vendedor_microtime' => microtime(true)
                                ]);
                            }
                        }
                    } else {
                        // Si el checkbox no está marcado desactivar todos los vendedores
                        DB::table('users_vendedores')
                            ->where('id_users', $this->id_users)
                            ->update(['user_vendedor_estado' => 0]);
                    }

                    DB::commit();
                    session()->flash('success', 'Usuario actualizado exitosamente.');
                    return redirect()->route('configuracion.usuarios');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'Error al actualizar el usuario.');
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

}
