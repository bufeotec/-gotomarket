<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Logs;
use App\Models\User;
use App\Models\Campania;
use App\Models\General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Nette\Schema\ValidationException;

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

    //SELECCIONAR PUNTOS
    public function login_goto_api(Request $request){
        try {
            // Validación de entrada
            $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);

            // Obtener el username o email directamente desde la solicitud
            $usernameOrEmail = $request->input('email');
            $password = $request->input('password');

            // Consultar usuario por username o email
            $user = $this->user->obtener_usuario_api($usernameOrEmail);

            // Validar si el usuario existe
            if (!$user) {
                return ApiResponse::error('Correo o contraseña incorrectos.', [], 401);
            }

            // Validar si el usuario está activo
            if ($user->users_status == 0) {
                return ApiResponse::error('El usuario está inhabilitado. Por favor, contacta al administrador.', [], 403);
            }

            // Validar si las credenciales son correctas
            if (!Auth::attempt(['username' => $user->username, 'password' => $password]) &&
                !Auth::attempt(['email' => $user->email, 'password' => $password])) {
                return ApiResponse::error('Correo o contraseña incorrectos.', [], 401);
            }

            // Respuesta exitosa con mensaje personalizado usando el helper
            return ApiResponse::success('Inicio de sesión exitoso.', [
                'id_users' => (string)$user->id_users,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'username' => $user->username,
                'profile_picture' => asset($user->profile_picture),
                'users_phone' => $user->users_phone,
            ], 200);

        } catch (ValidationException $e) {
            return ApiResponse::error('Errores de validación.', $e->errors(), 422);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return ApiResponse::error('Ocurrió un error interno.', ['exception' => $e->getMessage()], 500);
        }
    }

    public function listar_campania_por_usuario_api(Request $request){
        try {
            // Validación de entrada
            $request->validate([
                'id_users' => 'required|string',
            ]);

            // Obtener el id_users directamente de la solicitud
            $id_users = $request->input('id_users');

            // Consultar las campañas
            $campanias = $this->campania->listar_campanias_por_usuario($id_users);

            // Verificar si hay campañas
            if ($campanias->isEmpty()) {
                return ApiResponse::error('No se encontraron campañas para este usuario.', [], 404);
            }

            // Formatear los datos para la respuesta y convertir la colección en un array
            $data = $campanias->map(function($campania) {
                return [
                    'id_campania' => (string)$campania->id_campania,
                    'campania_nombre' => $campania->campania_nombre,
                ];
            })->toArray(); // Convertir la colección en un array

            // Respuesta exitosa usando el helper ApiResponse
            return ApiResponse::success('Campañas obtenidas exitosamente.', $data, 200);

        } catch (ValidationException $e) {
            return ApiResponse::error('Errores de validación.', $e->errors(), 422);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return ApiResponse::error('Ocurrió un error interno.', ['exception' => $e->getMessage()], 500);
        }
    }

    public function listar_campania_api(Request $request){
        try {
            // Validación de entrada
            $request->validate([
                'id_users' => 'required|string',
                'id_campania' => 'nullable|string',
            ]);

            $id_users = $request->input('id_users');
            $id_campania = $request->input('id_campania');

            // Verificar si no se seleccionó campaña
            if (empty($id_campania)) {
                return ApiResponse::error('DEBE SELECCIONAR UNA CAMPAÑA.', [], 404);
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

            // Obtener premios canjeados
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
                $premio->id_premio = (string)$premio->id_premio;
                $premio->premio_documento = asset($premio->premio_documento);
                $premio->campania_premio_puntaje = number_format($premio->campania_premio_puntaje, 0) . ' pts';

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

            // Respuesta exitosa usando ApiResponse
            return ApiResponse::success('Datos obtenidos correctamente', [
                'fecha_fin' => $campania->campania_fecha_fin_canje ? $this->general->obtenerNombreFecha($campania->campania_fecha_fin_canje, 'Date', 'Date') : '-',
                'campania_finalizada' => $campania_finalizada,
                'documentos' => $documentos,
                'puntos' => [
                    'punto_ganado' => (string)number_format($punto_ganado, 0),
                    'punto_canjeado' => (string)number_format($punto_canjeado, 0),
                    'punto_restante' => (string)number_format($punto_restante, 0)
                ],
                'premios' => $premios
            ], 200);

        } catch (ValidationException $e) {
            return ApiResponse::error('Errores de validación.', $e->errors(), 422);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return ApiResponse::error('Ocurrió un error interno.', ['exception' => $e->getMessage()], 500);
        }
    }

    // HISTORIAL PUNTOS
    public function historial_puntos_api(Request $request){
        try {
            // Validación de entrada
            $request->validate([
                'id_users' => 'required|string',
                'id_campania' => 'nullable|string',
                'estado_campania' => 'nullable|string',
                'anio_campania' => 'nullable|string',
            ]);

            $id_users = $request->input('id_users');
            $id_campania = $request->input('id_campania');
            $estado_campania = $request->input('estado_campania');
            $anio_campania = $request->input('anio_campania');

            // Construir consulta base
            $query = DB::table('campanias')
                ->where('campania_estado', '=', 1);

            // Filtrar por id_campania si se proporciona
            if ($id_campania) {
                $query->where('id_campania', $id_campania);
            }

            // Filtrar por estado_campania si se proporciona
            if ($estado_campania) {
                $query->where('campania_estado_ejecucion', $estado_campania);
            }

            // Filtrar por año si se proporciona
            if ($anio_campania) {
                $query->whereYear('campania_fecha_inicio', $anio_campania);
            }

            // Obtener los resultados
            $campanias = $query->get();

            // Si no hay resultados
            if ($campanias->isEmpty()) {
                return ApiResponse::error('No se encontraron campañas.', [], 404);
            }

            // Obtener el id_cliente del vendedor a través de las relaciones
            $id_cliente = DB::table('users as u')
                ->join('vendedores_intranet as vt', 'u.id_vendedor_intranet', '=', 'vt.id_vendedor_intranet')
                ->where('u.id_users', $id_users)
                ->whereNotNull('u.id_vendedor_intranet')
                ->value('vt.id_cliente');

            // Obtener dni vendedor
            $dni_vendedor = DB::table('users as u')
                ->join('vendedores_intranet as vt', 'u.id_vendedor_intranet', '=', 'vt.id_vendedor_intranet')
                ->where('u.id_users', $id_users)
                ->whereNotNull('u.id_vendedor_intranet')
                ->value('vt.vendedor_intranet_dni');

            // Preparar los datos de la campaña
            $resultados = [];

            foreach ($campanias as $campania) {
                // Obtener los documentos asociados a la campaña
                $documentos = DB::table('campanias_documentos')
                    ->select('id_campania_documento', 'campania_documento_adjunto')
                    ->where('campania_documento_estado', '=', 1)
                    ->where('id_campania', $campania->id_campania)
                    ->get()
                    ->map(function ($doc) {
                        $doc->id_campania_documento = (string)$doc->id_campania_documento;
                        $doc->campania_documento_adjunto = asset($doc->campania_documento_adjunto);
                        return $doc;
                    });

                // Obtener los puntos ganados, canjeados y restantes
                $puntos_ganados = DB::table('puntos_detalles as pd')
                    ->join('puntos as p', 'pd.id_punto', '=', 'p.id_punto')
                    ->where('p.id_campania', '=', $campania->id_campania)
                    ->where('p.id_cliente', '=', $id_cliente)
                    ->where('pd.punto_detalle_vendedor', '=', $dni_vendedor)
                    ->where('pd.punto_detalle_estado', '=', 1)
                    ->sum('pd.punto_detalle_punto_ganado');

                $puntos_canjeados = DB::table('canjear_puntos as cp')
                    ->join('canjear_puntos_detalles as cpd', 'cp.id_canjear_punto', '=', 'cpd.id_canjear_punto')
                    ->where('cp.id_campania', '=', $campania->id_campania)
                    ->where('cp.id_users', '=', $id_users)
                    ->where('cpd.canjear_punto_detalle_estado', '=', 1)
                    ->sum(DB::raw('cpd.canjear_punto_detalle_cantidad * cpd.canjear_punto_detalle_pts_unitario'));

                $puntos_restantes = $puntos_ganados - $puntos_canjeados;

                // Obtener el número de WhatsApp del admin
                $whatsapp = $campania->campania_celular ? 'https://wa.me/'.$campania->campania_celular : '-';

                // Agregar la campaña al resultado
                $resultados[] = [
                    'nombre_campania' => $campania->campania_nombre,
                    'fecha_inicio' => $campania->campania_fecha_inicio,
                    'fecha_fin' => $campania->campania_fecha_fin,
                    'fecha_fin_canje' => $campania->campania_fecha_fin_canje,
                    'documentos' => $documentos,
                    'estado' => $campania->campania_estado_ejecucion == 1 ? 'Activa' : 'Cerrada',
                    'puntos_ganados' => number_format($puntos_ganados, 0),
                    'puntos_canjeados' => number_format($puntos_canjeados, 0),
                    'puntos_restantes' => number_format($puntos_restantes, 0),
                    'whatsapp' => $whatsapp
                ];
            }

            // Respuesta exitosa
            return ApiResponse::success('Datos obtenidos correctamente', $resultados, 200);

        } catch (ValidationException $e) {
            return ApiResponse::error('Errores de validación.', $e->errors(), 422);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return ApiResponse::error('Ocurrió un error interno.', ['exception' => $e->getMessage()], 500);
        }
    }

}
