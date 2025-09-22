<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GotoappController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/prueba', fn () => response()->json(['mensaje' => '¡Hola desde la API!']));

// APIS v1
Route::prefix('v1')->group(function () {
    //SELECCIONAR PUNTOS
    Route::post('/login_goto_api', [GotoappController::class, 'login_goto_api']);
    Route::post('/listar_campania_por_usuario_api', [GotoappController::class, 'listar_campania_por_usuario_api']);
    Route::post('/listar_campania_api', [GotoappController::class, 'listar_campania_api']);

    // HISTORIAL PUNTOS
    Route::post('/historial_puntos_api', [GotoappController::class, 'historial_puntos_api']);
});
