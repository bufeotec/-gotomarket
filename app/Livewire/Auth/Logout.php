<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
class Logout extends Component
{
    public function logout()
    {
        Auth::logout(); // Cierra la sesión del usuario autenticado

        session()->invalidate(); // Invalida la sesión actual
        session()->regenerateToken(); // Regenera el token CSRF para evitar ataques CSRF

        return redirect()->route('login')->with('status', '¡Sesión cerrada con éxito!');
    }

    public function render()
    {
        return view('livewire.auth.logout');
    }
}
