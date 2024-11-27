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

    public function obtener_vehiculos_con_tarifarios_local($pesot, $volument,$type, $idt = null){
        try {
            $query = DB::table('vehiculos as v')
                ->join('tipo_vehiculos as tv', 'tv.id_tipo_vehiculo', '=', 'v.id_tipo_vehiculo')
                ->join('tarifarios as t', 't.id_tipo_vehiculo', '=', 'tv.id_tipo_vehiculo')
                ->select('v.id_vehiculo','v.vehiculo_placa','v.vehiculo_capacidad_peso','v.vehiculo_capacidad_volumen','t.tarifa_cap_min','t.tarifa_cap_max','t.tarifa_monto','t.tarifa_estado_aprobacion','t.id_tarifario')
                ->where('t.tarifa_estado','=', 1)
                ->where('v.vehiculo_estado','=', 1)
                ->where('t.id_tipo_servicio','=', $type)
                ->where('v.vehiculo_capacidad_peso', '>=', $pesot)
                ->where('t.tarifa_cap_min', '<=', $pesot)
                ->where('t.tarifa_cap_max', '>=', $pesot)
            ;

            if ($volument) {
                $query->where('v.vehiculo_capacidad_volumen','>=',$volument);
            }

            if ($idt) {
                $query->where('v.id_transportistas', $idt);
            }

            // Verificar rango de tarifa

            $query->groupBy('v.id_vehiculo','v.vehiculo_placa','v.vehiculo_capacidad_peso','v.vehiculo_capacidad_volumen','t.tarifa_cap_min','t.tarifa_cap_max','t.tarifa_monto','t.tarifa_estado_aprobacion','t.id_tarifario');
            $result = $query->get();

            foreach ($result as $r){
                $r->vehiculo_capacidad_usada =  ($pesot / $r->vehiculo_capacidad_peso) * 100;
            }
            // Ordenar los resultados por vehiculo_capacidad_usada de mayor a menor
            $result = $result->sortByDesc('vehiculo_capacidad_usada');

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_informacion_vehiculo($id){
        try {
            $result = DB::table('vehiculos as v')
                ->join('tipo_vehiculos as tv', 'v.id_tipo_vehiculo', '=', 'tv.id_tipo_vehiculo')
                ->join('transportistas as t', 'v.id_transportistas', '=', 't.id_transportistas')
                ->where('v.id_vehiculo','=', $id)
                ->first();


        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function obtener_vehiculos_con_tarifarios_provincial($pesot,$type, $idt = null,$iddepartamento = null,$idprovincia = null,$iddistrito = null){
        try {
            $query = DB::table('tarifarios as t')
                ->where('t.tarifa_estado','=', 1)
                ->where('t.id_tipo_servicio','=', $type)
                ->where('t.tarifa_cap_min', '<=', $pesot)
                ->where('t.tarifa_cap_max', '>=', $pesot)
            ;
            if ($idt) {
                $query->where('t.id_transportistas', $idt);
            }
            if ($iddepartamento) {
                $query->where('t.id_departamento', $iddepartamento);
            }
            if ($idprovincia) {
                $query->where('t.id_provincia', $idprovincia);
            }
            if ($iddistrito) {
                $query->where('t.id_distrito', $iddistrito);
            }

            $result = $query->get();

            foreach ($result as $r){
                $r->capacidad_usada =  ($pesot / $r->tarifa_cap_max) * 100;
            }
            // Ordenar los resultados por vehiculo_capacidad_usada de mayor a menor
            $result = $result->sortByDesc('capacidad_usada');

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function obtener_vehiculos_con_tarifarios_mixto($pesot, $volument,$type, $idt = null){
        try {
            $query = DB::table('vehiculos as v')
                ->join('tipo_vehiculos as tv', 'tv.id_tipo_vehiculo', '=', 'v.id_tipo_vehiculo')
                ->join('tarifarios as t', 't.id_tipo_vehiculo', '=', 'tv.id_tipo_vehiculo')
                ->select('v.id_vehiculo','v.vehiculo_placa','v.vehiculo_capacidad_peso','v.vehiculo_capacidad_volumen','t.tarifa_cap_min','t.tarifa_cap_max','t.tarifa_monto','t.tarifa_estado_aprobacion','t.id_tarifario')
                ->where('t.tarifa_estado','=', 1)
                ->where('v.vehiculo_estado','=', 1)
                ->where('t.id_tipo_servicio','=', $type)
                ->where('v.vehiculo_capacidad_peso', '>=', $pesot)
                ->where('t.tarifa_cap_min', '<=', $pesot)
                ->where('t.tarifa_cap_max', '>=', $pesot)
            ;
            if ($volument) {
                $query->where('v.vehiculo_capacidad_volumen','>=',$volument);
            }
            if ($idt) {
                $query->where('v.id_transportistas', $idt);
            }

            // Verificar rango de tarifa

            $query->groupBy('v.id_vehiculo','v.vehiculo_placa','v.vehiculo_capacidad_peso','v.vehiculo_capacidad_volumen','t.tarifa_cap_min','t.tarifa_cap_max','t.tarifa_monto','t.tarifa_estado_aprobacion','t.id_tarifario');
            $result = $query->get();

            foreach ($result as $r){
                $r->vehiculo_capacidad_usada =  ($pesot / $r->vehiculo_capacidad_peso) * 100;
            }
            // Ordenar los resultados por vehiculo_capacidad_usada de mayor a menor
            $result = $result->sortByDesc('vehiculo_capacidad_usada');

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
