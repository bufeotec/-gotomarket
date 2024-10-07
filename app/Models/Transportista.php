<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transportista extends Model
{
    use HasFactory;

    protected $table = "transportistas";
    protected $primaryKey = "id_transportistas";
    private $logs;

    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_transportistas($search,$pagination,$order = 'asc'){
        try {

            $query = DB::table('transportistas as t')
                ->join('tipo_servicios as ts','t.id_tipo_servicios','=','ts.id_tipo_servicios')
                ->join('ubigeos as u','t.id_ubigeo','=','u.id_ubigeo')
                ->where(function($q) use ($search) {
                    $q->where('t.transportista_ruc', 'like', '%' . $search . '%')
                        ->orWhere('t.transportista_razon_social', 'like', '%' . $search . '%')
                        ->orWhere('ts.tipo_servicio_concepto', 'like', '%' . $search . '%')
                        ->orWhereNull('t.transportista_ruc')
                        ->orWhereNull('t.transportista_razon_social')
                        ->orWhereNull('ts.tipo_servicio_concepto');
                })->orderBy('t.id_transportistas', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
