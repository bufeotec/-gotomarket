<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = "vehiculos";
    protected $primaryKey = "id_vehiculo";
    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_vehiculos_por_transportistas($search,$pagination,$order = 'asc'){
        try {
            $query = DB::table('vehiculos as v')
                ->join('transportistas as t','v.id_transportistas','=','t.id_transportistas')
                ->join('tipo_vehiculos as tv','v.id_tipo_vehiculo','=','tv.id_tipo_vehiculo')
                ->where(function($q) use ($search) {
                    $q->where('v.vehiculo_placa', 'like', '%' . $search . '%')
                        ->orWhere('v.vehiculo_capacidad_peso', 'like', '%' . $search . '%')
                        ->orWhere('tv.tipo_vehiculo_concepto', 'like', '%' . $search . '%')
                        ->orWhere('t.transportista_nom_comercial', 'like', '%' . $search . '%')
                        ->orWhere('t.transportista_razon_social', 'like', '%' . $search . '%')
                        ->orWhere('t.transportista_direccion', 'like', '%' . $search . '%')
                        ->orWhere('t.transportista_ruc', 'like', '%' . $search . '%')
                        ->orWhere('v.vehiculo_ancho', 'like', '%' . $search . '%')
                        ->orWhere('v.vehiculo_largo', 'like', '%' . $search . '%')
                        ->orWhere('v.vehiculo_alto', 'like', '%' . $search . '%')
                        ->orWhere('v.vehiculo_capacidad_volumen', 'like', '%' . $search . '%');
//                        ->orWhereNull('v.vehiculo_placa')
//                        ->orWhereNull('v.vehiculo_capacidad_peso')
//                        ->orWhereNull('v.vehiculo_capacidad_volumen');
                })->orderBy('v.id_vehiculo', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
