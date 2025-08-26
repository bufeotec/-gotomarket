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
            $query = DB::table('puntos as p')
                ->join('campanias as c', 'p.id_campania', 'c.id_campania')
                ->join('clientes as cl', 'p.id_cliente', 'cl.id_cliente')
                ->join('vendedores_intranet as vi', 'cl.id_cliente', 'vi.id_cliente')
                ->where('p.id_campania', '=', $id_campania)
                ->select(
                    'vi.id_vendedor_intranet',
                    'vi.vendedor_intranet_nombre',
                    'cl.cliente_codigo_cliente',
                    'cl.cliente_ruc_cliente',
                    'cl.cliente_nombre_cliente',
                    'vi.vendedor_intranet_punto'
                );

            $query->orderBy('p.id_campania', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
