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
                ->join('medida as m', 't.id_medida', '=', 'm.id_medida')
                ->leftJoin('tipo_vehiculos as tv', 't.id_tipo_vehiculo', '=', 'tv.id_tipo_vehiculo')
                ->leftJoin('ubigeos as u', 't.id_ubigeo_salida', '=', 'u.id_ubigeo')
                ->leftJoin('ubigeos as ub', 't.id_ubigeo_llegada', '=', 'ub.id_ubigeo')
                ->select('t.*', 'tr.*', 'ts.*', 'tv.*', 'm.*',
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
                ->where('t.tarifa_estado', '=', 1)
                ->where(function($q) use ($search) {
                    $q->where('ts.tipo_servicio_concepto', 'like', '%' . $search . '%')
                        ->orWhere('m.medida_nombre', 'like', '%' . $search . '%')
                        ->orWhere('t.tarifa_cap_min', 'like', '%' . $search . '%')
                        ->orWhere('t.tarifa_cap_max', 'like', '%' . $search . '%')
                        ->orWhere('t.tarifa_monto', 'like', '%' . $search . '%')

                        ->orWhereNull('ts.tipo_servicio_concepto')
                        ->orWhereNull('m.medida_nombre')
                        ->orWhereNull('t.tarifa_cap_min')
                        ->orWhereNull('t.tarifa_cap_max')
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

    public function lista_tarifas_pendientes($search,$pagination,$order = 'asc'){
        try {
            $query = DB::table('tarifarios as t')
                ->join('transportistas as tr', 't.id_transportistas', '=', 'tr.id_transportistas')
                ->join('tipo_servicios as ts', 't.id_tipo_servicio', '=', 'ts.id_tipo_servicios')
                ->join('users as us', 't.id_users', '=', 'us.id_users')
                ->leftJoin('ubigeos as u', 't.id_ubigeo_salida', '=', 'u.id_ubigeo')
                ->leftJoin('ubigeos as ub', 't.id_ubigeo_llegada', '=', 'ub.id_ubigeo')
                ->select('t.*', 'tr.*', 'ts.*', 'us.*',
                    // Campos de ubigeo salida
                    'u.ubigeo_departamento as salida_departamento',
                    'u.ubigeo_provincia as salida_provincia',
                    'u.ubigeo_distrito as salida_distrito',
                    // Campos de ubigeo llegada
                    'ub.ubigeo_departamento as llegada_departamento',
                    'ub.ubigeo_provincia as llegada_provincia',
                    'ub.ubigeo_distrito as llegada_distrito'
                )
                ->where('t.tarifa_estado_aprobacion', '=', 0)
                ->where('t.tarifa_estado', '=', 1)
                ->where(function($q) use ($search) {
                    $q->where('us.name', 'like', '%' . $search . '%')
                        ->orWhere('tr.transportista_nom_comercial', 'like', '%' . $search . '%')
                        ->orWhere('ts.tipo_servicio_concepto', 'like', '%' . $search . '%')
                        ->orWhere('u.ubigeo_departamento', 'like', '%' . $search . '%')
                        ->orWhere('u.ubigeo_provincia', 'like', '%' . $search . '%')
                        ->orWhere('u.ubigeo_distrito', 'like', '%' . $search . '%')
                        ->orWhere('ub.ubigeo_departamento', 'like', '%' . $search . '%')
                        ->orWhere('ub.ubigeo_provincia', 'like', '%' . $search . '%')
                        ->orWhere('ub.ubigeo_distrito', 'like', '%' . $search . '%')

                        ->orWhereNull('us.name')
                        ->orWhereNull('tr.transportista_nom_comercial')
                        ->orWhereNull('ts.tipo_servicio_concepto')
                        ->orWhereNull('u.ubigeo_departamento')
                        ->orWhereNull('u.ubigeo_provincia')
                        ->orWhereNull('u.ubigeo_distrito')
                        ->orWhereNull('ub.ubigeo_departamento')
                        ->orWhereNull('ub.ubigeo_provincia')
                        ->orWhereNull('ub.ubigeo_distrito');
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
