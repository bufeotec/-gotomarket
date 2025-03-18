<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Facturaspreprogramacion extends Model
{
    use HasFactory;
    protected $table = "facturas_pre_programaciones";
    protected $primaryKey = "id_fac_pre_prog";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }


    public function listar_facturas_pre_programacion_estado_dos() {
        try {
            $result = DB::table('guias')
                ->join('guias_detalles', 'guias.id_guia', '=', 'guias_detalles.id_guia')
                ->where('guias.guia_estado_aprobacion', '=', 2)
                ->select(
                    'guias.*',
                    DB::raw('SUM(guias_detalles.guia_det_cantidad * guias_detalles.guia_det_peso_gramo) as total_peso'),
                    DB::raw('SUM(guias_detalles.guia_det_cantidad * guias_detalles.guia_det_volumen) as total_volumen')
                )
                ->groupBy('guias.id_guia', 'guias.guia_nro_doc', 'guias.guia_fecha_emision', 'guias.guia_importe_total', 'guias.guia_nombre_cliente', 'guias.guia_direc_entrega') // Agrupamos por columnas necesarias
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_facturas_pre_programacion_estado_tres(){
        try {
            $result = DB::table('facturas_pre_programaciones')
                ->where('fac_pre_prog_estado_aprobacion', '=', 3)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_facturas_pre_programacion_estadox(){
        try {
            $result = DB::table('guias')
                ->join('guias_detalles', 'guias.id_guia', '=', 'guias_detalles.id_guia')
                    ->where('guias.guia_estado_registro', '=', 1)
                    ->select(
                        'guias.*',
                        DB::raw('SUM(guias_detalles.guia_det_cantidad * guias_detalles.guia_det_peso_gramo) as total_peso'),
                        DB::raw('SUM(guias_detalles.guia_det_cantidad * guias_detalles.guia_det_volumen) as total_volumen')
                    )
                    ->groupBy('guias.id_guia', 'guias.guia_nro_doc', 'guias.guia_fecha_emision', 'guias.guia_importe_total', 'guias.guia_nombre_cliente', 'guias.guia_direc_entrega') // Agrupamos por columnas necesarias
                    ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_fac_pre_prog_x_id($id){
        try {
            $result = DB::table('facturas_pre_programaciones')
                ->where('id_fac_pre_prog','=',$id)
                ->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_guia_existente($serie,$num){
        try {
            $result = DB::table('guias')
                ->where('guia_serie','=',$serie)
                ->where('guia_numero','=',$num)
                ->first();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_guiax_id($id) {
        try {
            $result = DB::table('guias')
                ->where('id_guia', '=', $id)
                ->first();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_guia_detalles($id) {
        try {
            $result = DB::table('guias_detalles')
                ->where('id_guia', '=', $id)
                ->get();
        } catch (\Exception $e) {
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
}
