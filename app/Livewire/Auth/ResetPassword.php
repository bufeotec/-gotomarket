<?php

namespace App\Livewire\Auth;

use App\Models\Logs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class ResetPassword extends Component
{
    /*------------------------------------------*/
    public $token;
    public $email;
    public $password;
    public $confirmPassword;
    /*------------------------------------------*/
    private $logs;
    public function mount($token,$email){
        $this->token = $token;
        $this->email = $email;
    }
    public function __construct()
    {
        $this->logs = new Logs();
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }

    public  function  Send_recovery_link_by_email()
    {
        try {
            $this->validate([
                'password' => [
                    'required',
                    'min:8', // Mínimo 8 caracteres
                    'regex:/[A-Z]/', // Al menos una letra mayúscula
                    'regex:/[0-9]/', // Al menos un número
                    'regex:/[@$!%*#?&]/' // Al menos un carácter especial
                ],
                'confirmPassword' => [
                    'required',
                    'same:password' // Debe coincidir con la nueva contraseña
                ],
            ], [
                'password.required' => 'La nueva contraseña es requerida.',
                'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
                'password.regex' => 'La nueva contraseña debe contener al menos una letra mayúscula, un número y un carácter especial.',
                'confirmPassword.required' => 'La confirmación de la nueva contraseña es requerida.',
                'confirmPassword.same' => 'La confirmación de la nueva contraseña no coincide.',
            ]);
            // Intentar restablecer la contraseña usando el token y email
            $status = Password::reset(
                [
                    'email' => $this->email,
                    'password' => $this->password,
                    'password_confirmation' => $this->confirmPassword,
                    'token' => $this->token,
                ],
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();
                }
            );

            // Verificar el estado del restablecimiento
            if ($status === Password::PASSWORD_RESET) {
                session()->flash('status', 'La contraseña ha sido restablecida con éxito.');
                $this->dispatch('redirectAfterSuccess', route('login'));
            } else {
                // Agrega el error de token o email inválido a los errores de la vista
                throw ValidationException::withMessages([
                    'error' => [trans($status)]
                ]);
            }

        }catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al enviar el enlace de recuperación.');
        }
    }

}
