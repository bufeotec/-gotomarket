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
    public function transportista() {
        return $this->belongsTo(Transportista::class, 'id_transportistas');
    }
    public function tipo() {
        return $this->belongsTo(TipoVehiculo::class, 'id_tipo_vehiculo');
    }
    public function tarifasMovil()
    {
        return $this->hasMany(TarifaMovil::class, 'id_vehiculo', 'id_vehiculo');
    }

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
                'v.id_vehiculo',
                'v.id_users',
                'v.id_transportistas',
                'v.id_tipo_vehiculo',
                'v.vehiculo_placa',
                'v.vehiculo_capacidad_peso',
                'v.vehiculo_ancho',
                'v.vehiculo_largo',
                'v.vehiculo_alto',
                'v.vehiculo_capacidad_volumen',
                'v.vehiculo_estado',
                'v.vehiculo_microtime',
                'v.created_at',
                'v.updated_at',
                'tr.id_transportistas',
                'tr.id_users',
                'tr.id_ubigeo',
                'tr.transportista_ruc',
                'tr.transportista_razon_social',
                'tr.transportista_nom_comercial',
                'tr.transportista_direccion',
                'tr.transportista_correo',
                'tr.transportista_telefono',
                'tr.transportista_contacto',
                'tr.transportista_cargo',
                'tr.transportista_estado',
                'tr.transportista_microtime',
                'tr.created_at',
                'tr.updated_at'
            );
            $result = $query->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function obtener_vehiculos_con_tarifarios_local($pesot, $volument, $type, $idt = null) {
        try {
            $query = DB::table('tarifas_movil as tm')
                ->select(
                    'v.id_vehiculo',
                    'v.vehiculo_placa',
                    'v.vehiculo_capacidad_peso',
                    'v.vehiculo_capacidad_volumen',
                    'tr.id_transportistas',
                    'tr.transportista_razon_social',
                    't.id_tarifario',
                    't.tarifa_cap_min',
                    't.tarifa_cap_max',
                    't.tarifa_monto',
                    't.tarifa_estado_aprobacion'
                )
                ->join('vehiculos as v', 'v.id_vehiculo', '=', 'tm.id_vehiculo')
                ->join('tarifarios as t', 't.id_tarifario', '=', 'tm.id_tarifario')
                ->join('transportistas as tr', 'tr.id_transportistas', '=', 'v.id_transportistas')
                ->where('v.vehiculo_estado', '=', 1)
                ->where('t.tarifa_estado_aprobacion', '=', 1)
                ->where('t.id_tipo_servicio', '=', $type);

            if ($idt) {
                $query->where('tr.id_transportistas', '=', $idt)
                    ->where('v.id_transportistas', '=', $idt);
            }

            $result = $query->get();

            // Calcular porcentajes de uso y determinar si está lleno
            foreach ($result as $r) {
                $r->vehiculo_capacidad_usada = ($pesot / $r->vehiculo_capacidad_peso) * 100;
                $r->vehiculo_volumen_usado = ($volument / $r->vehiculo_capacidad_volumen) * 100;
                $r->porcentaje_uso_maximo = max($r->vehiculo_capacidad_usada, $r->vehiculo_volumen_usado);
                $r->esta_lleno = ($r->vehiculo_capacidad_usada >= 100 || $r->vehiculo_volumen_usado >= 100) ? 1 : 0;
            }

            // Eliminar duplicados por placa de vehículo
            $uniqueResults = $result->unique('vehiculo_placa')->values();

            // Ordenar: primero los no llenos (ordenados por porcentaje de uso descendente), luego los llenos
            $sortedResults = $uniqueResults->sortBy([
                ['esta_lleno', 'asc'],  // Los no llenos (0) primero
                ['porcentaje_uso_maximo', 'desc']  // Dentro de cada grupo, los más llenos primero
            ]);

            return $sortedResults->values();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
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
                ->join('transportistas as tr', 't.id_transportistas', 'tr.id_transportistas')
                ->where('t.tarifa_estado','=', 1)
                ->where('t.id_tipo_servicio','=', $type)
//                ->where('t.tarifa_cap_min', '<=', $pesot)
//                ->where('t.tarifa_cap_max', '>=', $pesot)
            ;
            if ($idt) {
                $query->where('t.id_transportistas', '=', $idt);
            }
            if ($iddepartamento) {
                $query->where('t.id_departamento', $iddepartamento);
            }
            if ($idprovincia) {
                $query->where('t.id_provincia', $idprovincia);
            }
//            if ($iddistrito) {
//                $query->where('t.id_distrito', $iddistrito);
//            }

            $result = $query->get();

            foreach ($result as $r){
                $r->capacidad_usada =  ($pesot / $r->tarifa_cap_max) * 100;
            }
            // Ordenar los resultados por vehiculo_capacidad_usada de mayor a menor
            $result = $result->sortByDesc(function($item) {
                return [$item->tarifa_estado_aprobacion, $item->capacidad_usada];
            });

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function obtener_vehiculos_con_tarifarios_provincial_os_detalle($pesot,$type, $idt = null,$iddepartamento = null,$idprovincia = null,$iddistrito = null){
        try {
            $query = DB::table('tarifarios as t')
                ->select(
                    'tr.transportista_razon_social',
                    'tr.id_transportistas',
                    't.id_tarifario',
                    'tr.id_ubigeo',
                    't.tarifa_cap_min',
                    't.tarifa_cap_max',
                    't.tarifa_monto',
                    't.tarifa_estado_aprobacion'
                )
                ->join('transportistas as tr', 't.id_transportistas', 'tr.id_transportistas')
                ->where('t.tarifa_estado','=', 1)
                ->where('t.id_tipo_servicio','=', $type)
//                ->where('t.tarifa_cap_min', '<=', $pesot)
//                ->where('t.tarifa_cap_max', '>=', $pesot)
            ;
            if ($idt) {
                $query->where('t.id_transportistas', '=', $idt);
            }
            if ($iddepartamento) {
                $query->where('t.id_departamento', $iddepartamento);
            }
            if ($idprovincia) {
                $query->where('t.id_provincia', $idprovincia);
            }
//            if ($iddistrito) {
//                $query->where('t.id_distrito', $iddistrito);
//            }

            $result = $query->get();

            foreach ($result as $r){
                $r->capacidad_usada =  ($pesot / $r->tarifa_cap_max) * 100;
            }
            // Ordenar los resultados por vehiculo_capacidad_usada de mayor a menor
            $result = $result->sortByDesc(function($item) {
                return [$item->tarifa_estado_aprobacion, $item->capacidad_usada];
            });

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

    public function listar_vehiculos_activos(){
        try {
            $result = DB::table('vehiculos')
                ->where('vehiculo_estado', '=', 1)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
