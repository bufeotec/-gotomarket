<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            // Verificar si el estado en la sesión coincide con el estado actual del usuario en la base de datos
            if ($user->users_status == 0) {
                // Usuario inactivo, realizar acciones necesarias (por ejemplo, cerrar sesión)
                Auth::logout();
                session()->invalidate(); // Invalida la sesión actual
                session()->regenerateToken(); // Regenera el token CSRF para evitar ataques CSRF

                return redirect()->route('login')->with('status', '¡Sesión cerrada! Su cuenta ha sido desactivada.');
            }
            // Verificar si alguno de los roles del usuario está desactivado (roles_status = 0)
            foreach ($user->roles as $role) {
                if ($role->roles_status == 0) {
                    Auth::logout();
                    session()->invalidate();
                    session()->regenerateToken();

                    return redirect()->route('login')->with('status', '¡Sesión cerrada! Su rol ha sido desactivado.');
                }
            }
        }

        return $next($request);
    }
}
