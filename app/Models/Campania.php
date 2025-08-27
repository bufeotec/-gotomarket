<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Campania extends Model{
    use HasFactory;
    protected $table = "campanias";
    protected $primaryKey = "id_campania";
    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_campanias($desde = null, $hasta = null, $estado = null, $search, $pagination, $order = 'desc'){
        try {
            $query = DB::table('campanias')
                ->where(function($q) use ($search) {
                    $q->where('campania_nombre', 'like', '%' . $search . '%')
                        ->orWhereNull('campania_nombre');
                });

            // Filtro por fecha desde (opcional)
            if ($desde) {
                $query->whereDate('campania_fecha_inicio', '>=', $desde);
            }

            // Filtro por fecha hasta (opcional)
            if ($hasta) {
                $query->whereDate('campania_fecha_inicio', '<=', $hasta);
            }

            // Filtro por estado (opcional) - CORREGIDO: estaba usando $hasta en lugar de $estado
            if ($estado) {
                $query->where('campania_estado_ejecucion', '=', $estado);
            }

            $query->orderBy('id_campania', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_campanias_activos(){
        try {
            $result = DB::table('campanias')
                ->where('campania_estado', '=', 1)
                ->get();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_campanias_activos_new($id_users = null){
        try {
            $query = DB::table('campanias as c')
                ->where('campania_estado', '=', 1);

            // Si se proporciona id_users, filtrar por cliente del vendedor
            if (!is_null($id_users)) {
                // Obtener el id_cliente del vendedor a través de las relaciones
                $id_cliente = DB::table('users as u')
                    ->join('vendedores_intranet as vt', 'u.id_vendedor_intranet', '=', 'vt.id_vendedor_intranet')
                    ->where('u.id_users', $id_users)
                    ->whereNotNull('u.id_vendedor_intranet') // Validar que no sea null
                    ->value('vt.id_cliente');

                // Solo aplicar el filtro si se encontró un id_cliente válido
                if (!is_null($id_cliente)) {
                    $query->join('puntos as p', 'c.id_campania', '=', 'p.id_campania')
                        ->where('p.id_cliente', $id_cliente)
                        ->select('c.*') // Solo seleccionar campos de campanias
                        ->distinct(); // Evitar duplicados
                } else {
                    // Si no hay id_cliente válido, retornar array vacío
                    return [];
                }
            }

            $result = $query->get();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }

    public function listar_campanias_ejecucion(){
        try {
            $result = DB::table('campanias')
                ->where('campania_estado_ejecucion', '=', 1)
                ->where('campania_estado', '=', 1)
                ->get();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function obtener_resultados_por_campania($id_campania, $pagination, $order = 'desc'){
        try {
            $query = DB::table('canjear_puntos as cp')
                ->join('users as u', 'cp.id_users', '=', 'u.id_users')
                ->join('vendedores_intranet as vi', 'u.id_vendedor_intranet', '=', 'vi.id_vendedor_intranet')
                ->join('clientes as cl', 'vi.id_cliente', '=', 'cl.id_cliente')
                ->where('cp.id_campania', '=', $id_campania)
                ->select(
                    'vi.id_vendedor_intranet',
                    'vi.vendedor_intranet_nombre',
                    'cl.id_cliente',
                    'cl.cliente_codigo_cliente',
                    'cl.cliente_ruc_cliente',
                    'cl.cliente_nombre_cliente',
                    'vi.vendedor_intranet_punto'
                )
                ->distinct();

            $query->orderBy('vi.vendedor_intranet_nombre', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }


    // generar_excel_detalle_cliente
    public function reporte_por_cliente($id_campania, $id_cliente){
        try {
            // Obtener datos del cliente y campaña
            $result = DB::table('puntos as p')
                ->join('campanias as c', 'p.id_campania', '=', 'c.id_campania')
                ->join('clientes as cl', 'p.id_cliente', '=', 'cl.id_cliente')
                ->select(
                    'c.campania_nombre',
                    'cl.cliente_zona',
                    'cl.cliente_codigo_cliente',
                    'cl.cliente_nombre_cliente',
                    'cl.id_cliente'
                )
                ->where('c.id_campania', '=', $id_campania)
                ->where('cl.id_cliente', '=', $id_cliente)
                ->where('c.campania_estado_ejecucion', '=', 1)
                ->where('c.campania_estado', '=', 1)
                ->first();

            return $result;
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return null;
        }
    }

    public function obtener_vendedores_cliente($id_cliente){
        try {
            $vendedores = DB::table('vendedores_intranet')
                ->where('id_cliente', '=', $id_cliente)
                ->get();

            return $vendedores;
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return collect();
        }
    }

    public function obtener_premios_campania($id_campania){
        try {
            $premios = DB::table('campanias_premios as cp')
                ->join('premios as p', 'cp.id_premio', '=', 'p.id_premio')
                ->select(
                    'p.premio_descripcion',
                    'cp.campania_premio_puntaje',
                    'p.id_premio'
                )
                ->where('cp.id_campania', '=', $id_campania)
                ->orderBy('cp.campania_premio_puntaje', 'asc')
                ->get();

            return $premios;
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return collect();
        }
    }

    public function obtener_puntos_canjeados_vendedor($id_vendedor_intranet, $id_campania){
        try {
            $puntos_canjeados = DB::table('vendedores_intranet as vi')
                ->join('users as u', 'vi.id_vendedor_intranet', '=', 'u.id_vendedor_intranet')
                ->join('canjear_puntos as cp', 'u.id_users', '=', 'cp.id_users')
                ->join('canjear_puntos_detalles as cpd', 'cp.id_canjear_punto', '=', 'cpd.id_canjear_punto')
                ->where('vi.id_vendedor_intranet', '=', $id_vendedor_intranet)
                ->where('cp.id_campania', '=', $id_campania)
                ->sum('cpd.canjear_punto_detalle_total_puntos');

            return $puntos_canjeados ?: 0;
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return 0;
        }
    }

    public function obtener_premios_canjeados_vendedor($id_vendedor_intranet, $id_premio, $id_campania){
        try {
            $cantidad_canjeada = DB::table('vendedores_intranet as vi')
                ->join('users as u', 'vi.id_vendedor_intranet', '=', 'u.id_vendedor_intranet')
                ->join('canjear_puntos as cp', 'u.id_users', '=', 'cp.id_users')
                ->join('canjear_puntos_detalles as cpd', 'cp.id_canjear_punto', '=', 'cpd.id_canjear_punto')
                ->where('vi.id_vendedor_intranet', '=', $id_vendedor_intranet)
                ->where('cpd.id_premio', '=', $id_premio)
                ->where('cp.id_campania', '=', $id_campania)
                ->sum('cpd.canjear_punto_detalle_cantidad');

            return $cantidad_canjeada ?: 0;
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return 0;
        }
    }


    // generar_excel_detalle_ganador_cliente
    public function obtener_detalle_cliente($id_campania){
        try {
            $result = DB::table('puntos as p')
                ->join('campanias as c', 'p.id_campania', '=', 'c.id_campania')
                ->join('clientes as cl', 'p.id_cliente', '=', 'cl.id_cliente')
                ->select(
                    'cl.cliente_zona',
                    'cl.cliente_codigo_cliente',
                    'cl.cliente_nombre_cliente',
                    'cl.id_cliente'
                )
                ->where('c.id_campania', '=', $id_campania)
                ->where('c.campania_estado_ejecucion', '=', 1)
                ->where('c.campania_estado', '=', 1)
                ->groupBy('cl.id_cliente', 'cl.cliente_zona', 'cl.cliente_codigo_cliente', 'cl.cliente_nombre_cliente')
                ->orderBy('cl.cliente_zona')
                ->orderBy('cl.cliente_nombre_cliente')
                ->get();

            return $result;
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return collect();
        }
    }

    public function obtener_info_campania($id_campania){
        try {
            $result = DB::table('campanias')
                ->select('campania_nombre')
                ->where('id_campania', '=', $id_campania)
                ->where('campania_estado_ejecucion', '=', 1)
                ->where('campania_estado', '=', 1)
                ->first();

            return $result;
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return null;
        }
    }


    //generar_excel_consolidado_premios
    public function obtener_consolidado_premios($id_campania){
        try {
            // Primero obtener todos los premios de la campaña
            $premios_campania = DB::table('campanias_premios as camp')
                ->join('premios as p', 'camp.id_premio', '=', 'p.id_premio')
                ->where('camp.id_campania', $id_campania)
                ->select(
                    'p.id_premio',
                    'p.premio_descripcion',
                    'camp.campania_premio_puntaje'
                )
                ->get();

            // Obtener todos los detalles de canje para esta campaña
            $detalles_canjes = DB::table('canjear_puntos as cp')
                ->join('canjear_puntos_detalles as cpd', 'cp.id_canjear_punto', '=', 'cpd.id_canjear_punto')
                ->where('cp.id_campania', $id_campania)
                ->select(
                    'cpd.id_premio',
                    'cpd.canjear_punto_detalle_cantidad'
                )
                ->get();

            // Crear array para consolidar las cantidades
            $consolidado = [];

            foreach ($premios_campania as $premio) {
                $cantidad_total = 0;

                foreach ($detalles_canjes as $detalle) {
                    if ($detalle->id_premio == $premio->id_premio) {
                        $cantidad_total += $detalle->canjear_punto_detalle_cantidad;
                    }
                }

                $consolidado[] = (object)[
                    'premio_descripcion' => $premio->premio_descripcion,
                    'campania_premio_puntaje' => $premio->campania_premio_puntaje,
                    'cantidad_reclamada' => $cantidad_total
                ];
            }

            $campania_nombre = DB::table('campanias')
                ->where('id_campania', $id_campania)
                ->value('campania_nombre');

            return (object)[
                'campania_nombre' => $campania_nombre,
                'premios' => collect($consolidado)
            ];

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return null;
        }
    }
}
