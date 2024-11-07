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

    public function lista_tarifas_pendientes($search, $pagination, $order = 'desc')
    {
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
                ->orderBy('t.id_tarifario', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
