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
                ->paginate(20);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
