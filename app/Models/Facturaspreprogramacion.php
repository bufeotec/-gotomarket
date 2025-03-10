<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Facturaspreprogramacion extends Model
{
    use HasFactory;
    protected $table = "guias";
    protected $primaryKey = "id_guia";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_facturas_pre_programacion_estado_dos(){
        try {
            $result = DB::table('facturas_pre_programaciones')
                ->where('fac_pre_prog_estado_aprobacion', '=', 2)
                ->where('fac_pre_prog_estado', '=', 1)
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
            $result = DB::table('facturas_pre_programaciones')
                ->where('fac_pre_prog_estado_aprobacion', '=', 6)
                ->where('fac_pre_prog_estado', '=', 1)
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
}
