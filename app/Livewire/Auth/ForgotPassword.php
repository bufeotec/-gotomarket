<?php

namespace App\Livewire\Auth;

use App\Models\Logs;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPassword extends Component
{
    /* variables para el guardado*/
    public $email;
    /* fin variables para el guardado*/
    private $logs;
    public function __construct()
    {
        $this->logs = new Logs();
    }
    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
    public  function  Send_recovery_link_by_email()
    {
        try {
            $this->validate([
                'email' => [
                    'required',
                    'email',
                    function($attribute, $value, $fail) {
                        // Verificar si el email pertenece a un usuario con estado activo (1)
                        $user = User::where('email', $value)->where('users_status', 1)->first();
                        if (!$user) {
                            $fail('El correo electrónico no está registrado o el usuario no está activo.');
                        }
                    }
                ]
            ], [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'Por favor, ingrese un correo electrónico válido.',
            ]);

            // Intentar enviar el enlace de restablecimiento de contraseña
            $status = Password::sendResetLink([
                'email' => $this->email // Ahora el email se pasa dentro de un array
            ]);

            // Verificar el estado del envío del enlace
            if ($status === Password::RESET_LINK_SENT) {
                $this->email = "";
                session()->flash('status', 'Un enlace para restablecer su contraseña ha sido enviado.');
            } else {
                session()->flash('error', 'No se pudo enviar el enlace para restablecer la contraseña.');
            }
        }catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al enviar el enlace de recuperación.');
        }
    }
}
