<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Programacion extends Model
{
    use HasFactory;
    protected $table = "programaciones";
    protected $primaryKey = "id_programacion";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
    public function informacion_id($id){
        try {
            $result = DB::table('programaciones')
                ->where('id_programacion','=',$id)
                ->first();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_programaciones_realizadas_x_fechas_x_estado($desde,$hasta,$estado){
        try {
            $result = DB::table('programaciones')
                ->whereBetween('programacion_fecha',[$desde,$hasta])
                ->where('programacion_estado_aprobacion','=',$estado)
                ->orderBy('id_programacion','desc')
                ->paginate(20);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_ultima_aprobacion(){
        try {
            // Obtener los últimos dos dígitos del año actual
            $añoActual = date('y'); // 'y' devuelve el año en formato de dos dígitos (e.g., 24 para 2024)

            // Consultar la última programación aprobada
            $result = DB::table('programaciones')
                ->where('programacion_estado_aprobacion', '=', 1)
                ->orderBy('programacion_numero_correlativo', 'desc')
                ->first();

            if ($result) {
                // Extraer el año y el correlativo de la última programación
                preg_match('/P(\d+)-(\d+)/', $result->programacion_numero_correlativo, $matches);

                $ultimoAño = $matches[1]; // Año de la última programación (e.g., 24)
                $ultimoCorrelativo = (int) $matches[2]; // Correlativo de la última programación (e.g., 00005)

                if ($ultimoAño == $añoActual) {
                    // Mismo año: incrementar el correlativo
                    $nuevoCorrelativo = str_pad($ultimoCorrelativo + 1, 5, '0', STR_PAD_LEFT);
                    $corr = "P$añoActual-$nuevoCorrelativo";
                } else {
                    // Año diferente: reiniciar el correlativo
                    $corr = "P$añoActual-00001";
                }
            } else {
                // No hay registros previos: iniciar con el primer correlativo
                $corr = "P$añoActual-00001";
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $corr = "";
        }
        return $corr;
    }
    public function listar_informacion_x_id($id){
        try {
            $result = DB::table('programaciones as p')
                ->select('p.*','us_one.name as nombre_creacion','us_one.last_name as apellido_creacion','us_down.name as nombre_aprobacion','us_down.last_name as apellido_aprobacion')
                ->join('users as us_one','us_one.id_users','=','p.id_users')
                ->leftJoin('users as us_down','us_down.id_users','=','p.id_users_programacion')
                ->where('p.id_programacion','=',$id)->first();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_programaciones_historial_programacion($desde,$hasta,$serie = null,$tipo = null){
        try {
            $result = DB::table('programaciones as p');
            if ($tipo){
                $result->select('p.id_programacion','p.id_users','p.id_users_programacion','p.programacion_fecha','p.programacion_estado_aprobacion','p.programacion_numero_correlativo','p.programacion_fecha_aprobacion','p.programacion_estado','p.created_at')
                    ->join('despachos as d','d.id_programacion','=','p.id_programacion');
                if ($tipo == 1){
                    $result->whereIn('d.despacho_estado_aprobacion',[1,2,3]);
                } else {
                    $result->where('d.despacho_estado_aprobacion', '=', $tipo);
                }
            }else{
                $result->select('p.*');
            }
            if ($desde  && $hasta){
                $result->whereBetween('p.programacion_fecha',[$desde,$hasta]);
            }
            if ($serie){
                $result->where('p.programacion_numero_correlativo','like',"%$serie%");
            }

            if ($tipo){
                $result->groupBy('p.id_programacion','p.id_users','p.id_users_programacion','p.programacion_fecha','p.programacion_estado_aprobacion','p.programacion_numero_correlativo','p.programacion_fecha_aprobacion','p.programacion_estado','p.created_at');
            }

            $result = $result->where('p.programacion_estado_aprobacion','<>',0)
                ->orderBy('p.programacion_fecha', 'desc')->paginate(20);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_programaciones_historial_programacion_excel($desde,$hasta){
        try {
            $result = DB::table('programaciones as p');

            if ($desde  && $hasta){
                $result->whereBetween('p.programacion_fecha',[$desde,$hasta]);
            }

            $result = $result->where('p.programacion_estado_aprobacion','=',1)
                ->orderBy('p.programacion_fecha', 'desc')->get();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
