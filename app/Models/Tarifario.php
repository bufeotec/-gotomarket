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

    public function listar_tarifarios($id, $search, $pagination, $order = 'desc')
    {
        try {

            $query = DB::table('tarifarios as t')
                ->join('transportistas as tr', 't.id_transportistas', '=', 'tr.id_transportistas')
                ->join('tipo_servicios as ts', 't.id_tipo_servicio', '=', 'ts.id_tipo_servicios')
                ->leftJoin('medida as m', 't.id_medida', '=', 'm.id_medida')
                ->where('t.id_transportistas', '=', $id)
                ->where('t.tarifa_estado', '=', 1)
                ->where(function ($q) use ($search) {
                    $q->where('ts.tipo_servicio_concepto', 'like', '%' . $search . '%')
                        ->orWhere('t.tarifa_cap_min', 'like', '%' . $search . '%')
                        ->orWhere('t.tarifa_cap_max', 'like', '%' . $search . '%')
                        ->orWhere('t.tarifa_monto', 'like', '%' . $search . '%');
                })
                ->orderBy('t.id_tarifario', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function lista_tarifas_pendientes($search, $pagination, $order = 'desc'){
        try {
            // Quitar de la tabla de validar tarifas los ubigeos.
            // buscar por las tablas tarifarios,transportistas,tipo_servicios,users
            // Campos para la busqueda : Usuario Transportista	Tipo de servicio,Capacidad mínima	Capacidad máxima	Monto de la tarifa
            $query = DB::table('tarifarios as t')
                ->join('transportistas as tr', 't.id_transportistas', '=', 'tr.id_transportistas')
                ->join('tipo_servicios as ts', 't.id_tipo_servicio', '=', 'ts.id_tipo_servicios')
                ->join('users as us', 't.id_users', '=', 'us.id_users')
                ->select('t.*', 'tr.*', 'ts.*', 'us.*')
                ->where('t.tarifa_estado_aprobacion', '=', 0)
                ->where('t.tarifa_estado', '=', 1)
                ->where(function ($q) use ($search) {
                    $q->where('us.name', 'like', '%' . $search . '%')
                        ->orWhere('tr.transportista_nom_comercial', 'like', '%' . $search . '%')
                        ->orWhere('ts.tipo_servicio_concepto', 'like', '%' . $search . '%')
                        ->orWhere('t.tarifa_cap_min', 'like', '%' . $search . '%')
                        ->orWhere('t.tarifa_cap_max', 'like', '%' . $search . '%')
                        ->orWhere('t.tarifa_monto', 'like', '%' . $search . '%')
                        ->orWhereNull('us.name')
                        ->orWhereNull('tr.transportista_nom_comercial')
                        ->orWhereNull('ts.tipo_servicio_concepto')
                        ->orWhereNull('t.tarifa_cap_min')
                        ->orWhereNull('t.tarifa_cap_max')
                        ->orWhereNull('t.tarifa_monto');
                })
                ->orderBy('t.updated_at', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_informacion_tarifa($id){
        try {
            $result = DB::table('tarifarios as t')
                ->join('transportistas as tr','tr.id_transportistas','=','t.id_transportistas')
                ->where('t.id_tarifario', '=',$id)
                ->first();


        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function obtener_comparativo_provincia(){
        try {
            // Subconsulta para obtener la tarifa mínima por departamento, provincia y unidad de medida
            $subquery = DB::table('tarifarios as t')
                ->select('t.id_departamento', 't.id_provincia', 't.id_medida', DB::raw('MIN(t.tarifa_monto) as tarifa_minima'))
                ->where('t.id_tipo_servicio', '=', 2)
                ->where('t.tarifa_estado', '=', 1)
                ->where('t.tarifa_estado_aprobacion', '=', 1)
                ->groupBy('t.id_departamento', 't.id_provincia', 't.id_medida');

            // Consulta principal que usa la subconsulta para obtener los datos completos
            $result = DB::table('tarifarios as t')
                ->join('transportistas as tr', 't.id_transportistas', '=', 'tr.id_transportistas')
                ->join('departamentos as d', 't.id_departamento', '=', 'd.id_departamento')
                ->join('provincias as p', 't.id_provincia', '=', 'p.id_provincia')
                ->join('medida as m', 't.id_medida', '=', 'm.id_medida')
                ->join('tipo_servicios as ts', 't.id_tipo_servicio', '=', 'ts.id_tipo_servicios')
                ->joinSub($subquery, 'min_tarifas', function ($join) {
                    $join->on('t.id_departamento', '=', 'min_tarifas.id_departamento')
                        ->on('t.id_provincia', '=', 'min_tarifas.id_provincia')
                        ->on('t.id_medida', '=', 'min_tarifas.id_medida')
                        ->on('t.tarifa_monto', '=', 'min_tarifas.tarifa_minima');
                })
                ->select([
                    'd.departamento_nombre',
                    'p.provincia_nombre',
                    'ts.tipo_servicio_concepto',
                    'm.medida_nombre',
                    't.tarifa_monto',
                    't.id_medida',
                    'tr.transportista_razon_social'
                ])
                ->where('tr.transportista_estado', '=', 1)
                ->where('t.id_tipo_servicio', '=', 2)
                ->where('t.tarifa_estado', '=', 1)
                ->where('t.tarifa_estado_aprobacion', '=', 1)
                ->orderBy('t.tarifa_monto', 'asc')
                ->get();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function obtener_comparativo_local(){
        try {
            // Subconsulta para obtener la tarifa mínima por capacidad máxima y tipo de vehículo
            $subquery = DB::table('tarifarios as t')
                ->select('t.tarifa_cap_max', 't.id_tipo_vehiculo', DB::raw('MIN(t.tarifa_monto) as tarifa_minima'))
                ->where('t.id_tipo_servicio', '=', 1)
                ->where('t.tarifa_estado', '=', 1)
                ->where('t.tarifa_estado_aprobacion', '=', 1)
                ->groupBy('t.tarifa_cap_max', 't.id_tipo_vehiculo');

            // Consulta principal que usa la subconsulta para obtener los datos completos
            $result = DB::table('tarifarios as t')
                ->join('transportistas as tr', 't.id_transportistas', '=', 'tr.id_transportistas')
                ->join('tipo_vehiculos as tv', 't.id_tipo_vehiculo', '=', 'tv.id_tipo_vehiculo')
                ->join('tipo_servicios as ts', 't.id_tipo_servicio', '=', 'ts.id_tipo_servicios')
                ->joinSub($subquery, 'min_tarifas', function ($join) {
                    $join->on('t.tarifa_cap_max', '=', 'min_tarifas.tarifa_cap_max')
                        ->on('t.id_tipo_vehiculo', '=', 'min_tarifas.id_tipo_vehiculo')
                        ->on('t.tarifa_monto', '=', 'min_tarifas.tarifa_minima');
                })
                ->select([
                    't.tarifa_cap_max',
                    't.tarifa_monto',
                    'ts.tipo_servicio_concepto',
                    'tv.tipo_vehiculo_concepto',
                    'tr.transportista_razon_social'
                ])
                ->where('tr.transportista_estado', '=', 1)
                ->where('t.id_tipo_servicio', '=', 1)
                ->where('t.tarifa_estado', '=', 1)
                ->where('t.tarifa_estado_aprobacion', '=', 1)
                ->orderBy('t.tarifa_monto', 'asc')
                ->get();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function obtener_tiempo_trasnsporte(){
        try {
            // Primero obtenemos todos los datos filtrados
            $datos_base = DB::table('tarifarios as t')
                ->join('transportistas as tr','tr.id_transportistas','=','t.id_transportistas')
                ->join('departamentos as d','t.id_departamento','=','d.id_departamento')
                ->join('provincias as p','t.id_provincia','=','p.id_provincia')
                ->select(
                    't.id_departamento',
                    't.id_provincia',
                    'd.departamento_nombre',
                    'p.provincia_nombre',
                    't.tarifa_tiempo_transporte',
                    'tr.transportista_razon_social'
                )
                ->where('t.id_tipo_servicio', '=', 2)
                ->where('tr.transportista_estado', '=', 1)
                ->where('t.tarifa_estado', '=', 1)
                ->where('t.tarifa_estado_aprobacion', '=', 1)
                ->get();

            // Agrupamos por departamento y provincia
            $agrupados = $datos_base->groupBy(function($item) {
                return $item->id_departamento . '-' . $item->id_provincia;
            });

            $resultado = [];

            foreach ($agrupados as $grupo) {
                // Encontramos el mínimo y máximo tiempo de transporte
                $tiempo_minimo = $grupo->min('tarifa_tiempo_transporte');
                $tiempo_maximo = $grupo->max('tarifa_tiempo_transporte');

                // Encontramos los transportistas con tiempo mínimo y máximo
                $transportista_minimo = $grupo->where('tarifa_tiempo_transporte', $tiempo_minimo)->first();
                $transportista_maximo = $grupo->where('tarifa_tiempo_transporte', $tiempo_maximo)->first();

                $resultado[] = (object) [
                    'departamento_nombre' => $transportista_minimo->departamento_nombre,
                    'provincia_nombre' => $transportista_minimo->provincia_nombre,
                    'tiempo_minimo' => $tiempo_minimo,
                    'tiempo_maximo' => $tiempo_maximo,
                    'proveedor_minimo' => $transportista_minimo->transportista_razon_social,
                    'proveedor_maximo' => $transportista_maximo->transportista_razon_social
                ];
            }

            return collect($resultado);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
