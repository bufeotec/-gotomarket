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

    public function listar_vehiculo(){
        try {

            $result = Vehiculo::get();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_vehiculos_por_transportistas($search,$pagination,$order = 'desc'){
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

    public function obtener_vehiculos_con_tarifarios(){
        try {
            $query = DB::table('vehiculos as v')
                ->join('tarifarios as t', 'v.id_transportistas', '=', 't.id_transportistas')
                ->join('transportistas as tr', 'v.id_transportistas', '=', 'tr.id_transportistas')
                ->select(
                    'v.id_vehiculo', 'v.vehiculo_capacidad_peso', 'v.vehiculo_placa', 't.tarifa_cap_min', 't.tarifa_cap_max', 't.tarifa_estado_aprobacion',  'tr.*', 'v.*', DB::raw('MAX(t.tarifa_monto) as tarifa_monto'), 't.id_departamento', 't.id_provincia', 'v.id_transportistas','tr.id_transportistas', 't.id_distrito'
                )
                ->distinct()
                ->where('t.tarifa_estado', 1)
                ->where('v.vehiculo_estado', 1);
            if ($this->id_departamento) {
                $query->where('t.id_departamento', $this->id_departamento);
            }
            if ($this->id_provincia) {
                $query->where('t.id_provincia', $this->id_provincia);
            }
            if ($this->id_distrito !== null) {
                $query->where(function ($query) {
                    $query->where('t.id_distrito', $this->id_distrito)
                        ->orWhereNull('t.id_distrito');
                });
            }
            $query->groupBy(
                'v.id_vehiculo', 'v.vehiculo_capacidad_peso', 'v.vehiculo_placa', 't.tarifa_cap_min', 't.tarifa_cap_max', 't.tarifa_estado_aprobacion', 't.id_departamento', 't.id_provincia', 't.id_distrito'
            );
            $result = $query->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
