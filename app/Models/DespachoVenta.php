<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DespachoVenta extends Model
{
    use HasFactory;protected $table = "despacho_ventas";
    protected $primaryKey = "id_despacho_venta";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
    public function listar_detalle_x_despacho($id){
        try {
            $result = DB::table('despacho_ventas')
                ->where('id_despacho','=',$id)->get();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_facturas_estado_cinco() {
        try {
            $result = DB::table('notas_creditos as nc')
                ->join('despacho_ventas as dv', 'nc.id_despacho_venta', '=', 'dv.id_despacho_venta')
                ->where('nc.nota_credito_estado', '=', 1)
                ->select('dv.id_despacho_venta', 'dv.despacho_venta_factura')
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_despacho_nota_credito(){
        try {
            $result = DB::table('despacho_ventas')
                ->whereIn('despacho_detalle_estado_entrega', [2,3,4])
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_comprobantes($search, $pagination, $order = 'desc'){
        try {
            // Mapeo de términos de búsqueda a valores de estado
            $estadoMapping = [
                'creditos' => 1,
                'programar' => 2,
                'programado' => 3,
                'ruta' => 4
            ];

            // Convertir el término de búsqueda a su valor correspondiente si existe en el mapeo
            $estadoValue = $estadoMapping[strtoupper($search)] ?? null;

            $query = DB::table('facturas_pre_programaciones')
                ->where(function ($q) use ($search, $estadoValue) {
                    $q->where('fac_pre_prog_cnomcli', 'like', '%' . $search . '%')
                        ->orWhere('fac_pre_prog_cfcodcli', 'like', '%' . $search . '%')
                        ->orWhere('fac_pre_prog_grefecemision', 'like', '%' . $search . '%');

                    // Si el término de búsqueda coincide con un estado, filtrar por el campo correspondiente
                    if (!is_null($estadoValue)) {
                        $q->orWhere('fac_pre_prog_estado_aprobacion', $estadoValue);
                    }
                })
                ->orderBy('id_fac_pre_prog', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }

}
