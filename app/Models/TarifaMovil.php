<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class TarifaMovil extends Model
{
    use hasFactory;

    protected $table =  'tarifas_movil';
    protected $primaryKey = 'id_tarifa_movil';
    private $logs;

    protected $fillable = [
        'id_vehiculo',
        'id_tarifario',
        'tarifa_movil_estado',
    ];

    public function __construct(){
        parent::__construct();
        $this->logs = new  Logs();
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }

    public function tarifario()
    {
        return $this->belongsTo(Tarifario::class, 'id_tarifario');
    }
    public function listar_vehiculo(){

        try {
            $result = DB::table('vehiculos')
                ->whereIn('vehiculo_estado', [1])
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_tarifario(){

        try {
            $result = DB::table('tarifarios')
                ->whereIn('id_tipo_servicio', [1])
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_tarifamovil($search, $pagination, $order = 'asc')
    {
        try {
            $query = DB::table('tarifas_movil as tm')
                ->join('vehiculos as v', 'tm.id_vehiculo', '=', 'v.id_vehiculo')
                ->join('tarifarios as t', 'tm.id_tarifario', '=', 't.id_tarifario')
                ->join('transportistas as tra', 'tra.id_transportistas', '=', 'v.id_transportistas')
                ->join('tipo_vehiculos as tv', 'tv.id_tipo_vehiculo', '=', 'v.id_tipo_vehiculo')
                ->select('tm.*', 'v.vehiculo_placa', 'tv.tipo_vehiculo_concepto','v.vehiculo_estado', 't.tarifa_monto','tra.transportista_nom_comercial')
                ->where(function ($q) use ($search) {
                    $q->where('v.vehiculo_placa', 'like', '%' . $search . '%')
                        ->orWhere('tra.transportista_nom_comercial', 'like', '%' . $search . '%')
                        ->orWhere('t.tarifa_monto', 'like', '%' . $search . '%');
                    // Verifica si el valor de búsqueda no está vacío
//                    if (!empty($search)) {
//                        $q->where('tm.id_tarifario', 'like', '%' . $search . '%')
//                            ->orWhere('tm.id_vehiculo', 'like', '%' . $search . '%')
//                            ->orWhere('tm.tarifa_movil_estado', 'like', '%' . $search . '%');
//                    }
                })->orderBy('tm.id_tarifa_movil', $order);
//               if ($desde  && $hasta){
//                   $query->whereBetween('tm.created_at',[$desde,$hasta]);
//               }

            $result = $query->paginate($pagination);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

}
