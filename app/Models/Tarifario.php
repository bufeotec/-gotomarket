<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tarifario extends Model
{
    use HasFactory;
    protected $table = "tarifarios";
    protected $primaryKey = "id_tarifario";
    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_tarifarios($id,$search,$pagination,$order = 'asc'){
        try {

            $query = DB::table('tarifarios as t')
                ->join('transportistas as tr', 't.id_transportistas', '=', 'tr.id_transportistas')
                ->join('tipo_servicios as ts', 't.id_tipo_servicio', '=', 'ts.id_tipo_servicios')
                ->leftJoin('ubigeos as u', 't.id_ubigeo_salida', '=', 'u.id_ubigeo')
                ->leftJoin('ubigeos as ub', 't.id_ubigeo_llegada', '=', 'ub.id_ubigeo')
                ->select('t.*', 'tr.*', 'ts.*',
                    // Campos de ubigeo salida
                    'u.ubigeo_departamento as salida_departamento',
                    'u.ubigeo_provincia as salida_provincia',
                    'u.ubigeo_distrito as salida_distrito',
                    // Campos de ubigeo llegada
                    'ub.ubigeo_departamento as llegada_departamento',
                    'ub.ubigeo_provincia as llegada_provincia',
                    'ub.ubigeo_distrito as llegada_distrito'
                )
                ->where('t.id_transportistas', '=', $id)
                ->where(function($q) use ($search) {
                    $q->where('t.tarifa_monto', 'like', '%' . $search . '%')
                        ->orWhereNull('t.tarifa_monto');
                })
                ->orderBy('t.id_tarifario', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
