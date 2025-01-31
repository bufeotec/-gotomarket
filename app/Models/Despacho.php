<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Despacho extends Model
{
    use HasFactory;
    protected $table = "despachos";
    protected $primaryKey = "id_despacho";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
    public function listar_despachos_por_programacion($id_program){
        try {
            $result =  DB::table('despachos as d')
                ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                ->where('d.id_programacion','=',$id_program)->get();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_ultima_aprobacion_despacho(){
        try {
            $añoActual = date('y'); // Solo tomamos el año, no toda la fecha

            $result = DB::table('despachos')->whereIn('despacho_estado_aprobacion',[1,2,3])->orderBy('despacho_numero_correlativo','desc')->first();

            if ($result) {
                // Extraer el año y el correlativo del último despacho
                preg_match('/OS(\d+)-(\d+)/', $result->despacho_numero_correlativo, $matches);

                $ultimoAño = $matches[1]; // Año del último despacho (e.g., 24)
                $ultimoCorrelativo = (int) $matches[2]; // Correlativo del último despacho (e.g., 000006)

                if ($ultimoAño == $añoActual) {
                    // Mismo año: incrementar el correlativo
                    $nuevoCorrelativo = str_pad($ultimoCorrelativo + 1, 5, '0', STR_PAD_LEFT);
                    $corr = "OS$añoActual-$nuevoCorrelativo";
                } else {
                    // Año diferente: reiniciar el correlativo
                    $corr = "OS$añoActual-00001";
                }
            } else {
                // No hay registros previos: iniciar con el primer correlativo
                $corr = "OS$añoActual-00001";
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $corr = "";
        }
        return $corr;
    }

}
