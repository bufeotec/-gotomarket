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
    public function listar_programaciones_realizadas_x_fechas($desde,$hasta){
        try {
            $result = DB::table('programaciones')
                ->whereBetween('programacion_fecha',[$desde,$hasta])
                ->orderBy('id_programacion','desc')
                ->paginate(20);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
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
}
