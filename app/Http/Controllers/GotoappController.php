<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logs;
use App\Models\User;
use App\Models\Campania;
use App\Models\General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GotoappController extends Controller{
    private $logs;
    private $user;
    private $campania;
    private $general;
    public function __construct(){
        $this->logs = new Logs();
        $this->user = new User();
        $this->campania = new Campania();
        $this->general = new General();
    }

    public function login_goto_api(Request $request){
        try {
            // Validación de entrada
            $validated = $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);

            // Consultar usuario
            $user = $this->user->obtener_usuario_api($validated['email']);

            // Validar si el usuario existe
            if (!$user) {
                return response()->json([
                    'code' => 2,
                    'message' => 'Correo o contraseña incorrectos.',
                ], 401);
            }

            // Validar si el usuario está activo
            if ($user->users_status == 0) {
                return response()->json([
                    'code' => 3,
                    'message' => 'El usuario está inhabilitado. Por favor, contacta al administrador.',
                ], 403);
            }

            // Validar si las credenciales son correctas
            if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
                return response()->json([
                    'code' => 2,
                    'message' => 'Correo o contraseña incorrectos.',
                ], 401);
            }

            // Respuesta exitosa con mensaje personalizado
            $response = [
                'code' => 1,
                'data' => [
                    'id_users' => (string)$user->id_users,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'profile_picture' => asset($user->profile_picture),
                    'users_phone' => $user->users_phone,
                ],
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Ocurrió un error al intentar iniciar sesión.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listar_campania_por_usuario_api(Request $request){
        try {
            // Validación de entrada
            $validated = $request->validate([
                'id_users' => 'required|string',
            ]);

            $id_users = $validated['id_users'];

            $campanias = $this->campania->listar_campanias_por_usuario($id_users);

            // Verificar si hay campañas
            if ($campanias->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'message' => 'No se encontraron campañas para este usuario.',
                    'data' => []
                ], 404);
            }

            // Formatear los datos para la respuesta
            $data = $campanias->map(function($campania) {
                return [
                    'id_campania' => (string)$campania->id_campania,
                    'campania_nombre' => $campania->campania_nombre,
                ];
            });

            return response()->json([
                'code' => 200,
                'message' => 'Campañas obtenidas exitosamente.',
                'data' => $data
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return response()->json([
                'code' => 500,
                'message' => 'Ocurrió un error interno.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listar_campania_api(Request $request){
        try {
            // Validación de entrada
            $validated = $request->validate([
                'id_users' => 'required|string',
                'id_campania' => 'nullable|string',
            ]);

            $id_users = $validated['id_users'];
            $id_campania = $validated['id_campania'] ?? null;

            // Verificar si no se seleccionó campaña
            if (empty($id_campania)) {
                return response()->json([
                    'code' => 400,
                    'message' => 'DEBE SELECCIONAR UNA CAMPAÑA'
                ], 400);
            }

            // Listar documentos por id_campania
            $documentos = DB::table('campanias_documentos')
                ->select('campania_documento_adjunto')
                ->where('campania_documento_estado', '=', 1)
                ->where('id_campania', $id_campania)
                ->get();

            // Aplicar asset() a los documentos
            $documentos = $documentos->map(function($documento) {
                $documento->campania_documento_adjunto = asset($documento->campania_documento_adjunto);
                return $documento;
            });

            // Inicializar puntos por defecto
            $punto_ganado = 0;
            $punto_canjeado = 0;
            $punto_restante = 0;

            // Obtener puntos ganados
            // Primero obtener el vendedor_intranet_dni del usuario
            $user = DB::table('users')
                ->where('id_users', $id_users)
                ->whereNotNull('id_vendedor_intranet')
                ->first();

            if ($user && $user->id_vendedor_intranet) {
                $vendedor = DB::table('vendedores_intranet')
                    ->where('vendedor_intranet_estado', '=', 1)
                    ->where('id_vendedor_intranet', $user->id_vendedor_intranet)
                    ->first();

                if ($vendedor && $vendedor->vendedor_intranet_dni) {
                    // Obtener puntos ganados
                    $puntos_ganados_sum = DB::table('puntos_detalles as pd')
                        ->join('puntos as p', 'pd.id_punto', '=', 'p.id_punto')
                        ->where('pd.punto_detalle_vendedor', $vendedor->vendedor_intranet_dni)
                        ->where('p.id_campania', $id_campania)
                        ->where('pd.punto_detalle_estado', 1)
                        ->sum('pd.punto_detalle_punto_ganado');

                    $punto_ganado = $puntos_ganados_sum ?: 0;
                }
            }

            // Obtener puntos canjeados
            $canjear_puntos = DB::table('canjear_puntos')
                ->where('id_campania', $id_campania)
                ->where('id_users', $id_users)
                ->where('canjear_punto_estado', 1)
                ->get();

            foreach ($canjear_puntos as $canje) {
                $detalles_canje = DB::table('canjear_puntos_detalles')
                    ->where('id_canjear_punto', $canje->id_canjear_punto)
                    ->where('canjear_punto_detalle_estado', 1)
                    ->get();

                foreach ($detalles_canje as $detalle) {
                    $punto_canjeado += ($detalle->canjear_punto_detalle_cantidad * $detalle->canjear_punto_detalle_pts_unitario);
                }
            }

            // Calcular puntos restantes
            $punto_restante = $punto_ganado - $punto_canjeado;

            // Obtener premios canjeados (guardados en BD)
            $premios_canjeados = DB::table('canjear_puntos as cp')
                ->join('canjear_puntos_detalles as cpd', 'cp.id_canjear_punto', '=', 'cpd.id_canjear_punto')
                ->join('premios as p', 'cpd.id_premio', '=', 'p.id_premio')
                ->where('cp.id_campania', $id_campania)
                ->where('cp.id_users', $id_users)
                ->where('cp.canjear_punto_estado', 1)
                ->where('cpd.canjear_punto_detalle_estado', 1)
                ->pluck('p.id_premio')
                ->toArray();

            // Fecha fin del canje
            $campania = DB::table('campanias')
                ->where('id_campania', '=', $id_campania)
                ->where('campania_estado_ejecucion', '=', 1)
                ->where('campania_estado', '=', 1)
                ->first();

            // Verificar si la campaña ha finalizado por fecha
            $campania_finalizada = false;
            if ($campania && $campania->campania_fecha_fin_canje) {
                $hoy = now('America/Lima')->startOfDay();
                $fin = \Carbon\Carbon::parse($campania->campania_fecha_fin_canje)->endOfDay();
                $campania_finalizada = $hoy->gt($fin);
            }

            // Obtener premios de la campaña
            $premios = DB::table('campanias_premios as cp')
                ->join('premios as p', 'cp.id_premio', '=', 'p.id_premio')
                ->where('cp.id_campania', $id_campania)
                ->where('p.premio_estado', 1)
                ->where('cp.campania_premio_estado', 1)
                ->select(
                    'p.id_premio',
                    'p.premio_descripcion',
                    'p.premio_documento',
                    'cp.campania_premio_puntaje'
                )
                ->get();

            // Procesar premios y determinar estado del botón
            $premios = $premios->map(function($premio) use ($premios_canjeados, $campania_finalizada, $punto_restante) {
                // Aplicar asset() al documento
                $premio->id_premio = (string)$premio->id_premio;
                $premio->premio_documento = asset($premio->premio_documento);
                $premio->campania_premio_puntaje = number_format($premio->campania_premio_puntaje, 0) . ' pts';

                // Determinar estado del botón
                $yaCanjeado = in_array($premio->id_premio, $premios_canjeados);
                $puntosInsuficientes = $punto_restante < $premio->campania_premio_puntaje;

                if ($campania_finalizada) {
                    if ($yaCanjeado) {
                        $premio->estado_boton = 'Canjeado - Fin campaña';
                        $premio->boton_habilitado = false;
                    } else {
                        $premio->estado_boton = 'Fin campaña';
                        $premio->boton_habilitado = false;
                    }
                } else {
                    if ($yaCanjeado) {
                        $premio->estado_boton = 'Seleccionado';
                        $premio->boton_habilitado = false;
                    } else {
                        if ($puntosInsuficientes) {
                            $premio->estado_boton = 'Seleccionar';
                            $premio->boton_habilitado = false;
                        } else {
                            $premio->estado_boton = 'Seleccionar';
                            $premio->boton_habilitado = true;
                        }
                    }
                }

                return $premio;
            });

            // Respuesta exitosa
            return response()->json([
                'code' => 200,
                'message' => 'Datos obtenidos correctamente',
                'data' => [
                    'fecha_fin' => $campania->campania_fecha_fin_canje ? $this->general->obtenerNombreFecha($campania->campania_fecha_fin_canje, 'Date', 'Date') : '-',
                    'campania_finalizada' => $campania_finalizada,
                    'documentos' => $documentos,
                    'puntos' => [
                        'punto_ganado' => (string)number_format($punto_ganado, 0),
                        'punto_canjeado' => (string)number_format($punto_canjeado, 0),
                        'punto_restante' => (string)number_format($punto_restante, 0)
                    ],
                    'premios' => $premios
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return response()->json([
                'code' => 500,
                'message' => 'Ocurrió un error interno.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
