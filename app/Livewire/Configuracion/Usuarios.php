<?php

namespace App\Livewire\Configuracion;

use App\Livewire\Intranet\Navegation;
use App\Models\General;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
class Usuarios extends Component
{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    /* ----------- VARIABLES GLOBALES ----------------------*/
    public $ruta_img_default = "";
    public $search;
    public $pagination = 10;
    public $messageDelete = "";
    public $statusUsers = "";

    /* ----------- FIN VARIABLES GLOBALES -----------------*/

    /* ----------- VARIABLES DEL CONSTRUCTOR ----------------------*/
    private $logs;
    private $user;
    private $general;
    /* ----------- FIN VARIABLES DEL CONSTRUCTOR ----------------------*/

    /* ----------------- DATOS DEL FORMULARIO ----------------*/
    public $id_users;
    public $name;
    public $last_name;
    public $email;
    public $users_cargo;
    public $username;
    public $profile_picture;
    public $password;
    public $id_rol;
    /* ---------------- FIN DATOS DEL FORMULARIO -------------*/
    public function __construct()
    {
        $this->logs = new Logs();
        $this->user = new User();
        $this->general = new General();
    }

    public function generateUsername()
    {
        $firstName = explode(' ', trim($this->name))[0];
        $lastName = explode(' ', trim($this->last_name))[0];
        $this->username = strtolower($firstName . '.' . $lastName);
    }

    public function render()
    {
        try {
            $usuarios = $this->user->listar_usuarios_activos($this->search, $this->pagination);
            $roles = DB::table('roles')->where('roles_status','=',1)->get();

//            $user = \Illuminate\Support\Facades\Auth::user(); // Obtiene el usuario autenticado
            $roleId = auth()->user()->roles->first()->id ?? null;

            return view('livewire.configuracion.usuarios', compact('usuarios','roles','roleId'));
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
        }
    }
    /* FUNCIONES DEL CRUD */
    public function save_users(){
        try {
            $this->validate([
                'profile_picture' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users')->ignore($this->id_users,'id_users'), // Ignorar id en caso de actualización
                ],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($this->id_users,'id_users'), // Ignorar id en caso de actualización
                ],
                'id_rol' => 'required|integer|exists:roles,id',
                'users_cargo' => 'nullable|string',
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'name.string' => 'El nombre debe ser una cadena de texto válida.',
                'name.max' => 'El nombre no puede exceder los 255 caracteres.',

                'last_name.required' => 'El apellido es obligatorio.',
                'last_name.string' => 'El apellido debe ser una cadena de texto válida.',
                'last_name.max' => 'El apellido no puede exceder los 255 caracteres.',

                'username.required' => 'El nombre de usuario es obligatorio.',
                'username.string' => 'El nombre de usuario debe ser una cadena de texto válida.',
                'username.max' => 'El nombre de usuario no puede exceder los 255 caracteres.',
                'username.unique' => 'El nombre de usuario ya está registrado.',

                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'Debe proporcionar un correo electrónico válido.',
                'email.unique' => 'El correo electrónico ya está registrado.',

                'id_rol.required' => 'Debe seleccionar un rol.',
                'id_rol.integer' => 'El rol seleccionado es inválido.',
                'id_rol.exists' => 'El rol seleccionado no existe.',

                'users_cargo.string' => 'El cargo debe ser una cadena de texto.',

                'profile_picture.file' => 'Debe cargar un archivo válido.',
                'profile_picture.mimes' => 'El archivo debe ser una imagen en formato JPG, JPEG o PNG.',
                'profile_picture.max' => 'La imagen no puede exceder los 2MB.',
            ]);

            if (!$this->id_users) { // INSERT
                if (!Gate::allows('create_users')) {
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

                // Buscar el nombre del rol por su ID

                $role = Role::find($this->id_rol);

                if (!$role) {
                    session()->flash('error', 'No se encontró el rol asociado.');
                    return;
                }
                DB::beginTransaction();

                $usuario = new User();
                $usuario->name = $this->name;
                $usuario->last_name = $this->last_name;
                $usuario->email = $this->email;
                $usuario->username = $this->username;
                $usuario->password = bcrypt($this->password);
                $usuario->users_status = 1;
                $usuario->users_cargo = !empty($this->users_cargo) ? $this->users_cargo : null;

                $usuario->syncRoles($role->name);

                if ($this->profile_picture) {
                    $usuario->profile_picture = $this->general->save_files($this->profile_picture, 'configuration/users');
                }
                if ($usuario->save()) {
                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'El usuario se ha registrado exitosamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo registrar el usuario. Intente nuevamente.');
                }
            } else { // UPDATE

                if (!Gate::allows('update_users')) {
                    session()->flash('error', 'No tiene permisos para actualizar los usuarios.');
                    return;
                }

                $role = Role::find($this->id_rol);

                if (!$role) {
                    session()->flash('error', 'No se encontró el rol asociado.');
                    return;
                }

                $usuario = User::findOrFail($this->id_users);


                DB::beginTransaction();

                $usuario->name = $this->name;
                $usuario->last_name = $this->last_name;
                $usuario->email = $this->email;
                $usuario->username = $this->username;

                if ($this->password) {
                    $usuario->password = bcrypt($this->password);
                }

                if ($this->profile_picture) {

                    try{
                        unlink($usuario->profile_picture);
                    }catch  (\Exception $e){}

                    $usuario->profile_picture = $this->general->save_files($this->profile_picture, 'configuration/users');
                }
                $usuario->users_cargo = !empty($this->users_cargo) ? $this->users_cargo : null;

                $usuario->syncRoles($role->name);

                if ($usuario->save()) {
                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Los datos del usuario se han actualizado correctamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo actualizar el usuario. Intente nuevamente.');
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
    public function edit_users($id){
        $usersEditar = User::find(base64_decode($id));
        if ($usersEditar){
            $rol = DB::table('model_has_roles as  mr')->join('roles as r','r.id','=','mr.role_id')->where('mr.model_id','=',$usersEditar->id_users)->first();
            $this->password = null;
            $this->name = $usersEditar->name;
            $this->last_name = $usersEditar->last_name;
            $this->username = $usersEditar->username;
            $this->email = $usersEditar->email;
            $this->users_cargo = $usersEditar->users_cargo;
            $this->id_rol = $rol->id;
            $this->id_users = $usersEditar->id_users;
            if (file_exists($usersEditar->profile_picture)){
                $this->ruta_img_default = $usersEditar->profile_picture;
            }else{
                $this->ruta_img_default = "assets/images/faces/1.jpg";
            }

            $this->dispatch('updateUserImagePreview', ['image' => asset($this->ruta_img_default)]);
        }
    }
    public function clear_form(){
        $this->name  = "";
        $this->last_name  = "";
        $this->email  = "";
        $this->users_cargo  = "";
        $this->username  = "";
        $this->profile_picture  = "";
        $this->password  = null;
        $this->id_users  = "";
        $this->id_rol  = "";
//        $this->password  = "";
        $this->ruta_img_default = "assets/images/faces/1.jpg";
        $this->dispatch('updateUserImagePreview', ['image' => asset($this->ruta_img_default)]);
    }

    public function btn_disable($id_menu,$esta){
        $id = base64_decode($id_menu);
        $status = $esta;
        if ($id){
            $this->id_users = $id;
            $this->statusUsers = $status;
            if ($status == 0){
                $this->messageDelete = "¿Está seguro que desea deshabilitar este usuario?";
            }else{
                $this->messageDelete = "¿Está seguro que desea habilitar este usuario?";
            }
        }
    }
    public function disable_users()
    {
        try {
            if (!Gate::allows('disable_users')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de los usuarios.');
                return;
            }

            $this->validate([
                'id_users' => 'required|integer',
                'statusUsers' => 'required|integer',
            ], [
                'id_users.required' => 'El identificador de usuario es obligatorio.',
                'id_users.integer' => 'El identificador de usuario debe ser un número entero.',

                'statusUsers.required' => 'El estado del usuario es obligatorio.',
                'statusUsers.integer' => 'El estado del usuario debe ser un número entero.',
            ]);

            DB::beginTransaction();

            // Buscar el usuario y actualizar el estado
            $user = User::findOrFail($this->id_users);
            $user->users_status = $this->statusUsers;

            if ($user->save()) {
                DB::commit();
                $this->dispatch('hideModalDelete');
                // Mensaje según el estado del usuario
                $action = $this->statusUsers == 0 ? 'deshabilitado' : 'habilitado';
                session()->flash('success', "El usuario ha sido {$action} correctamente.");
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'Ocurrió un error al actualizar el estado del usuario. Intente nuevamente.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error inesperado. Por favor, inténtelo nuevamente.');
        }
    }


}
