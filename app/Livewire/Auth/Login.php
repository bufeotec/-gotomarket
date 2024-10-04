<?php

namespace App\Livewire\Auth;

use App\Models\Logs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Login extends Component
{
    #[Rule('required', message: 'El correo electronico es obligatorio.')]
//    #[Rule('email', message: 'El correo electronico debe ser un correo valido.')]
    public $email;
    #[Rule('required', message: 'La contraseña es obligatorio.')]
    public $password;

    public $remember = true;

    private $logs;

    public function __construct()
    {
        $this->logs = new Logs();
    }

    public function login()
    {
        try {
            $this->validate();

            // Chequear si el usuario ha intentado iniciar sesión demasiadas veces
            $this->ensureIsNotRateLimited();

            // Verificar si el valor ingresado es un correo electrónico
            $credentials = filter_var($this->email, FILTER_VALIDATE_EMAIL) ?
                ['email' => $this->email, 'password' => $this->password] :
                ['username' => $this->email, 'password' => $this->password];

            // Intentar autenticar al usuario
            if (Auth::attempt($credentials, $this->remember)) {
                // Redirigir a la página de inicio si la autenticación es exitosa
                session()->flash('status', '¡Inicio de sesión exitoso! Redirigiendo...');
                $this->dispatch('redirectAfterSuccess', route('intranet'));
            }else{
                // Si la autenticación falla, incrementar el contador de intentos fallidos
                RateLimiter::hit($this->throttleKey());
                session()->flash('error', 'Credenciales incorrectas. Verifica el correo y la contraseña ingresados.');
                return;
            }
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error inesperado durante el inicio de sesión. Por favor, intenta nuevamente o contacta al soporte.');
        }
    }

    protected function ensureIsNotRateLimited()
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            throw ValidationException::withMessages([
                'email' => __('Demasiados intentos fallidos. Por favor, inténtalo de nuevo en :seconds segundos.', [
                    'seconds' => RateLimiter::availableIn($this->throttleKey())
                ]),
            ]);
        }
    }

    protected function throttleKey()
    {
        return strtolower($this->email) . '|' . request()->ip();
    }

    public function render()
    {
        try {

            return view('livewire.auth.login');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
        }
    }
}
