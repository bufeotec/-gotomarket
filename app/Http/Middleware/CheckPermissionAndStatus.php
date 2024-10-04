<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionAndStatus
{
    public function handle($request, Closure $next, $permission)
    {
        $user = Auth::user();

        // Verificar si el usuario tiene el permiso
        if (!$user->hasPermissionTo($permission)) {
            return $this->abortWithCustomError();
        }

        // Verificar si el permiso está habilitado
        if (!$this->isPermissionEnabled($permission)) {
            return $this->abortWithCustomError();
        }

        return $next($request);
    }

    protected function isPermissionEnabled($permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();

        return $permissionModel;
    }

    protected function abortWithCustomError()
    {
        // Devolver una respuesta 403 con la vista de error personalizada
        return response()->view('errors.403', [], 403);
    }
}
