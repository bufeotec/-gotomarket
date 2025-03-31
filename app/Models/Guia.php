<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Guia extends Model
{
    use HasFactory;
    protected $table = "guias";
    protected $primaryKey = "id_guia";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_comprobantes($search, $pagination, $order = 'desc'){
        try {
            $query = DB::table('guias')
                ->where(function ($q) use ($search) {
                    $q->where('guia_nombre_cliente', 'like', '%' . $search . '%')
                        ->orWhere('guia_ruc_cliente', 'like', '%' . $search . '%')
                        ->orWhere('guia_nro_doc', 'like', '%' . $search . '%');
                })
                ->orderBy('id_guia', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }

    public function listar_guia_x_id($id){
        try {
            $result = DB::table('guias')
                ->where('id_guia','=',$id)
                ->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_guia_detalle_x_id($id) {
        try {
            $result = DB::table('guias as g')
                ->join('guias_detalles as gd', 'g.id_guia', '=', 'gd.id_guia')
                ->where('gd.id_guia', '=', $id)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

//    NUEVO ""
    public function listar_facturas_pre_programacion_estado_dos() {
        try {
            $result = DB::table('guias')
                ->leftJoin('guias_detalles', 'guias.id_guia', '=', 'guias_detalles.id_guia')
                ->where('guias.guia_estado_aprobacion', '=', 2)
                ->select(
                    'guias.*',
                    DB::raw('SUM(guias_detalles.guia_det_cantidad * guias_detalles.guia_det_peso_gramo) as total_peso'),
                    DB::raw('SUM(guias_detalles.guia_det_cantidad * guias_detalles.guia_det_volumen) as total_volumen')
                )
                ->groupBy('g.id_users', 'guias.id_guia', 'guias.guia_nro_doc', 'guias.guia_fecha_emision', 'guias.guia_importe_total', 'guias.guia_nombre_cliente', 'guias.guia_direc_entrega') // Agrupamos por columnas necesarias
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_facturas_pre_programacion_estadox(){
        try {
            $result = DB::table('guias as g')
                ->leftJoin('guias_detalles as gd', 'g.id_guia', '=', 'gd.id_guia')
                ->where('g.guia_estado_registro', '=', 1)
                ->select(
                    'g.*',
                    DB::raw('SUM(gd.guia_det_cantidad * gd.guia_det_peso_gramo) as total_peso'),
                    DB::raw('SUM(gd.guia_det_cantidad * gd.guia_det_volumen) as total_volumen')
                )
                ->groupBy('g.id_guia', 'g.guia_nro_doc', 'g.guia_fecha_emision', 'g.guia_importe_total', 'g.guia_nombre_cliente', 'g.guia_direc_entrega') // Agrupamos por columnas necesarias
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_buscar_guia($nombreCliente)
    {
        try {
            return DB::table('guias')
                ->where('guia_nombre_cliente', 'like', '%' . $nombreCliente . '%')
                ->where('guia_estado_aprobacion', 3)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
    }
}
