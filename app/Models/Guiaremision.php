<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Guiaremision extends Model
{
    use HasFactory;
    protected $table = "guias_remisiones";
    protected $primaryKey = "id_guia_rem";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_guias_remision($search,$pagination,$order = 'desc'){
        try {
            $query = DB::table('guias_remisiones as gr')
                ->join('vehiculos as v', 'gr.id_vehiculo', '=', 'v.id_vehiculo')
                ->where(function($q) use ($search) {
                    $q->where('gr.guia_rem_numero_guia', 'like', '%' . $search . '%')
                        ->orWhere('gr.guia_rem_motivo', 'like', '%' . $search . '%')
                        ->orWhere('gr.guia_rem_remitente_ruc', 'like', '%' . $search . '%')
                        ->orWhere('gr.guia_rem_remitente_razon_social', 'like', '%' . $search . '%')
                        ->orWhere('gr.guia_rem_remitente_direccion', 'like', '%' . $search . '%')
                        ->orWhere('gr.guia_rem_destinatario_ruc', 'like', '%' . $search . '%')
                        ->orWhere('gr.guia_rem_destinatario_razon_social', 'like', '%' . $search . '%')
                        ->orWhere('gr.guia_rem_destinatario_direccion', 'like', '%' . $search . '%')
                        ->orWhere('v.vehiculo_placa', 'like', '%' . $search . '%');
                })->orderBy('id_guia_rem', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
