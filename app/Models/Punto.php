<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Punto extends Model{
    use HasFactory;
    protected $table = "puntos";
    protected $primaryKey = "id_punto";
    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_puntos_registrados($id_campania = null, $id_cliente = null, $search = null){
        try {
            $query = DB::table('puntos')
                ->where('punto_estado', '=', 1);

            // Filtrar por campaña si se proporciona
            if ($id_campania && $id_campania != '') {
                $query->where('id_campania', $id_campania);
            }

            // Filtrar por cliente si se proporciona
            if ($id_cliente && $id_cliente != '') {
                $query->where('id_cliente', $id_cliente);
            }

            // Filtrar por búsqueda (punto_codigo) si se proporciona
            if ($search && $search != '') {
                $query->where('punto_codigo', 'like', '%' . $search . '%');
            }

            $result = $query->get();

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function obtener_resultado_puntos($id_campania = null, $id_cliente = null){
        try {
            $query = DB::table('puntos as p')
                ->join('campanias as c', 'p.id_campania', 'c.id_campania')
                ->join('clientes as cl', 'p.id_cliente', 'cl.id_cliente')
                ->where('p.punto_estado', '=', 1);

            // Filtrar por campaña si se proporciona
            if ($id_campania && $id_campania != '') {
                $query->where('p.id_campania', $id_campania);
            }

            // Filtrar por cliente si se proporciona
            if ($id_cliente && $id_cliente != '') {
                $query->where('p.id_cliente', $id_cliente);
            }

            $result = $query->get();

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function obtener_historial_puntos($id_cliente, $id_campania){
        try {
            $vendedores = DB::table('puntos_detalles as pd')
                ->join('vendedores_intranet as vi', 'pd.punto_detalle_vendedor', '=', 'vi.vendedor_intranet_dni')
                ->join('puntos as p', 'pd.id_punto', '=', 'p.id_punto')
                ->select(
                    'vi.vendedor_intranet_dni as vendedor_dni',
                    'vi.vendedor_intranet_nombre as vendedor_nombre',
                    DB::raw('SUM(pd.punto_detalle_punto_ganado) as total_puntos_ganados')
                )
                ->where('p.id_cliente', '=', $id_cliente)
                ->where('p.id_campania', '=', $id_campania)
                ->groupBy('vi.vendedor_intranet_dni', 'vi.vendedor_intranet_nombre')
                ->having('total_puntos_ganados', '>', 0)
                ->get();

            return $vendedores;
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return collect();
        }
    }
}
