<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Despacho extends Model
{
    use HasFactory;
    protected $table = "despachos";
    protected $primaryKey = "id_despacho";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
    public function listar_despachos_por_programacion($id_program){
        try {
            $result =  DB::table('despachos as d')
                ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                ->where('d.id_programacion','=',$id_program)->get();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_ultima_aprobacion_despacho() {
        try {
            $añoActual = date('y'); // Solo tomamos el año, no toda la fecha

            // Consultar la última OS generada en despachos
            $ultimaOSDespacho = DB::table('despachos')
                ->whereIn('despacho_estado_aprobacion', [1, 2, 3])
                ->orderBy('despacho_numero_correlativo', 'desc')
                ->first();

            // Consultar la última OS generada en servicios de transporte
            $ultimaOSServicioTransporte = DB::table('servicios_transportes')
                ->whereNotNull('serv_transpt_codigo_os')
                ->orderBy('serv_transpt_codigo_os', 'desc')
                ->first();

            // Determinar cuál es la última OS generada
            $ultimaOS = null;
            if ($ultimaOSDespacho && $ultimaOSServicioTransporte) {
                $ultimaOS = max($ultimaOSDespacho->despacho_numero_correlativo, $ultimaOSServicioTransporte->serv_transpt_codigo_os);
            } elseif ($ultimaOSDespacho) {
                $ultimaOS = $ultimaOSDespacho->despacho_numero_correlativo;
            } elseif ($ultimaOSServicioTransporte) {
                $ultimaOS = $ultimaOSServicioTransporte->serv_transpt_codigo_os;
            }

            if ($ultimaOS) {
                // Verificar si el formato de $ultimaOS es correcto
                if (preg_match('/OS(\d+)-(\d+)/', $ultimaOS, $matches)) {
                    $ultimoAño = $matches[1]; // Año de la última OS (e.g., 24)
                    $ultimoCorrelativo = (int) $matches[2]; // Correlativo de la última OS (e.g., 00012)

                    if ($ultimoAño == $añoActual) {
                        // Mismo año: incrementar el correlativo
                        $nuevoCorrelativo = str_pad($ultimoCorrelativo + 1, 5, '0', STR_PAD_LEFT);
                        $corr = "OS$añoActual-$nuevoCorrelativo";
                    } else {
                        // Año diferente: reiniciar el correlativo
                        $corr = "OS$añoActual-00001";
                    }
                } else {
                    // Si el formato no coincide, iniciar con el primer correlativo
                    $corr = "OS$añoActual-00001";
                }
            } else {
                // No hay registros previos: iniciar con el primer correlativo
                $corr = "OS$añoActual-00001";
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $corr = "";
        }
        return $corr;
    }

    public function listar_despachos_camino_aprobar($search, $pagination, $order = 'desc'){
        try {
            $query = DB::table('despachos as d')
                ->join('transportistas as t', 'd.id_transportistas', '=', 't.id_transportistas')
                ->leftJoin('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->select(
                    'd.*',
                    't.transportista_razon_social',
                    DB::raw('SUM(ROUND(CAST(dv.despacho_venta_cfimporte AS FLOAT), 2)) as totalVentaDespacho')
                )
                ->where('d.despacho_estado_aprobar_camino', '=', 0)
                ->where(function ($q) use ($search) {
                    $q->where('d.despacho_peso', 'like', '%' . $search . '%')
                        ->orWhere('d.despacho_numero_correlativo', 'like', '%' . $search . '%');
                })
                ->groupBy('d.id_despacho', 't.transportista_razon_social')
                ->orderBy('d.id_despacho', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = collect();
        }

        return $result;
    }

    public function listar_programaciones_historial_programacion($search = '', $pagination = 10, $order = 'asc'){
        try {
            $query = DB::table('despachos as d')
                ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                ->where('d.despacho_estado_aprobar_entregado', '=', 0)
                ->where(function($q) use ($search) {
                    $q->where('d.despacho_numero_correlativo', 'like', '%' . $search . '%')
                        ->orWhere('t.transportista_nom_comercial', 'like', '%' . $search . '%');
                })
                ->orderBy('d.id_despacho', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_despacho_x_id($id){
        try {
            $result = DB::table('despachos')
                ->where('id_despacho','=',$id)
                ->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_info_por_id($id){
        try {
            $result = DB::table('programaciones as p')
                ->join('despachos as d', 'p.id_programacion', 'd.id_programacion')
                ->join('transportistas as t', 'd.id_transportistas', 't.id_transportistas')
                ->join('tipo_servicios as ts', 'd.id_tipo_servicios', 'ts.id_tipo_servicios')
                ->join('tarifarios as tar', 'd.id_tarifario', 'tar.id_tarifario')
                ->where('d.id_despacho','=',$id)
                ->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

}
