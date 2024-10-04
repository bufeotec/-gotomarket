<?php

namespace App\Livewire\Intranet;

use App\Models\General;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
class Profile extends Component
{
    use WithFileUploads; // Usa el trait aquí

    /* ----------------- VARIABLES DEL CONSTRUCTOR --------------------*/
    private $logs;
    private $users;
    private $general;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->users = new User();
        $this->general = new General();
    }
    /* ---------------- VARIABLES GLOBALES ------------------*/
    public $id_users;
    public $auth;
    /* ---------------- DATOS DEL FORMULARIO ------------------*/
    public $dni;
    public $name;
    public $last_name;
    public $phone;
    public $naci;
    public $email;
    public $username;
    public $photo;
    public $current_password; // contraseña actual;
    public $New_password; // Nueva contraseña;
    public $confirm_new_password; // Confirmar nueva contraseña;
    public $tabSegu;
    public function mount($id_users,$auth){
        $this->auth = $auth;
        $this->id_users = $id_users;
        $this->tabSegu = false;
        if ($id_users){
            $users = $this->users->informacion_x_id($this->id_users);
            if ($users){
                $this->dni = $users->users_dni;
                $this->phone = $users->users_phone;
                $this->naci = $users->users_birthdate;

                $this->name = $users->name;
                $this->last_name = $users->last_name;
                $this->email = $users->email;
                $this->username = $users->username;
            }
        }
    }
    public function render()
    {
        $info = $this->users->informacion_x_id($this->id_users);
        return view('livewire.intranet.profile',compact('info'));
    }
    public function update_informacion_users(){
        try {
            $this->tabSegu = false;
            $this->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users')->ignore($this->id_users, 'id_users'), // Ignorar id en caso de actualización
                ],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($this->id_users, 'id_users'), // Ignorar id en caso de actualización
                ],
                'phone' => 'nullable|digits:9', // Para validar exactamente 9 dígitos
                'dni' => 'nullable|digits:8', // Para validar exactamente 8 dígitos
                'naci' => [
                    'nullable',
                    'date',
                    'before_or_equal:' . now()->subYears(18)->toDateString() // Validar que sea mayor de 18 años
                ],
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

                'phone.digits' => 'El número de teléfono debe tener exactamente 9 dígitos.',

                'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',

                'naci.date' => 'La fecha de nacimiento no es válida.',
                'naci.before_or_equal' => 'Debe tener al menos 18 años para registrarse.',
            ]);

            DB::beginTransaction();
            $userUpdate= User::find($this->id_users);
            $userUpdate->users_dni = $this->dni ? $this->dni : null;
            $userUpdate->name = $this->name;
            $userUpdate->last_name = $this->last_name;
            $userUpdate->users_phone = $this->phone ? $this->phone : null;
            $userUpdate->users_birthdate = $this->naci ? $this->naci : null;
            $userUpdate->email = $this->email;
            $userUpdate->username = $this->username;
            if ($userUpdate->save()){
                DB::commit();
                $this->dispatch('refresh_profile')->to(NavUsers::class);
                session()->flash('success', 'Registro actualizado correctamente.');
            }else{
                DB::rollBack();
                session()->flash('error', 'No se pudo actualizar la información del usuario.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }
    public function updatedPhoto()
    {
        $this->updatePhotoUsers();
    }
    public function updatePhotoUsers(){
        try {
            $this->tabSegu = false;

            $this->validate([
                'photo' => 'required|mimes:jpg,jpeg,png,gif|max:2048' // validación de imagen
            ], [
                'photo.required' => 'La imagen es obligatorio.',
                'photo.mimes' => 'La imagen debe ser un archivo de tipo: JPG, JPEG, PNG o GIF.',
                'photo.max' => 'El tamaño máximo permitido para la imagen es de 2 MB.',
            ]);
            DB::beginTransaction();
            $usersPhotoUpdate = User::findOrFail($this->id_users);
            if ($this->photo) {
                try{
                    if (file_exists($usersPhotoUpdate->profile_picture)){
                        unlink($usersPhotoUpdate->profile_picture);
                    }
                }catch  (\Exception $e){}
                $usersPhotoUpdate->profile_picture = $this->general->save_files($this->photo, 'configuration/users');
            }
            if ($usersPhotoUpdate->save()) {
                DB::commit();
                $this->dispatch('refresh_profile')->to(NavUsers::class);
                session()->flash('success_update_img_users', 'La imagen del usuario se han actualizado correctamente.');
            } else {
                DB::rollBack();
                session()->flash('error_update_img_users', 'No se pudo actualizar la imagen. Intente nuevamente.');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_update_img_users', 'Ocurrió un error inesperado. Intente nuevamente más tarde.');
        }
    }
    public function deletePhotoUsers(){
        try {
            $this->tabSegu = false;

            DB::beginTransaction();
            $usersPhotoDelete = User::findOrFail($this->id_users);
            try{
                if (file_exists($usersPhotoDelete->profile_picture)){
                    unlink($usersPhotoDelete->profile_picture);
                }
            }catch  (\Exception $e){}

            $usersPhotoDelete->profile_picture = null;

            if ($usersPhotoDelete->save()) {
                DB::commit();
                $this->dispatch('refresh_profile')->to(NavUsers::class);
                session()->flash('success_update_img_users', 'La imagen del usuario fue reiniciado correctamente.');
            } else {
                DB::rollBack();
                session()->flash('error_update_img_users', 'No se pudo reiniciar la imagen. Intente nuevamente.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_update_img_users', 'Ocurrió un error inesperado. Intente nuevamente más tarde.');
        }
    }
    public function update_password_users(){
        try {
            $this->tabSegu = true;
            // Validaciones
            $this->validate([
                'current_password' => [
                    'required',
                    function($attribute, $value, $fail) {
                        // Validar que la contraseña actual sea correcta
                        if (!Hash::check($value, auth()->user()->password)) {
                            $fail('La contraseña actual es incorrecta.');
                        }
                    }
                ],
                'New_password' => [
                    'required',
                    'min:8', // Mínimo 8 caracteres
                    'regex:/[A-Z]/', // Al menos una letra mayúscula
                    'regex:/[0-9]/', // Al menos un número
                    'regex:/[@$!%*#?&]/' // Al menos un carácter especial
                ],
                'confirm_new_password' => [
                    'required',
                    'same:New_password' // Debe coincidir con la nueva contraseña
                ],
            ], [
                'current_password.required' => 'La contraseña actual es requerida.',
                'New_password.required' => 'La nueva contraseña es requerida.',
                'New_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
                'New_password.regex' => 'La nueva contraseña debe contener al menos una letra mayúscula, un número y un carácter especial.',
                'confirm_new_password.required' => 'La confirmación de la nueva contraseña es requerida.',
                'confirm_new_password.same' => 'La confirmación de la nueva contraseña no coincide.',
            ]);
            DB::beginTransaction();
            $usersPasswordUpdate = User::findOrFail($this->id_users);
            $usersPasswordUpdate->password = bcrypt($this->New_password);

            if ($usersPasswordUpdate->save()) {
                DB::commit();
                $this->current_password = null;
                $this->New_password = null;
                $this->confirm_new_password = null;
                session()->flash('success_update_password_users', 'La contraseña del usuario se ha actualizado correctamente.');
            } else {
                DB::rollBack();
                session()->flash('error_update_password_users', 'No se pudo actualizar la contraseña. Intente nuevamente.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_update_password_users', 'Ocurrió un error inesperado. Intente nuevamente más tarde.');
        }
    }
}
