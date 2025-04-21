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
    public function listar_guias_antiguas() {
            try {
                $result = DB::table('despacho_ventas')
                    ->select('id_despacho_venta','despacho_venta_guia')
                    ->whereNull('id_guia')
                    ->where('despacho_detalle_estado_entrega', 2)
                    ->groupBy('id_despacho_venta','despacho_venta_guia')
                    ->orderByDesc('id_despacho_venta')
                    ->get();

            } catch (\Exception $e) {
                $this->logs->insertarLog($e);
                $result = [];
            }
            return $result;
    }
    public function listar_guia_existente($num) {
            try {
                $result = DB::table('guias')
                    ->where('guia_nro_doc','=',$num)
                    ->first();
            } catch (\Exception $e) {
                $this->logs->insertarLog($e);
                $result = false;
            }
            return $result;
    }

    public function listar_despacho_nota_credito($id_despacho_venta = null){
        try {
            $query = DB::table('despacho_ventas')
                ->whereIn('despacho_detalle_estado_entrega', [2,3,4]);

            // Si estamos editando y hay un despacho ya guardado, incluirlo en la lista
            if (!is_null($id_despacho_venta)) {
                $query->orWhere('id_despacho_venta', $id_despacho_venta);
            }

            $result = $query->get();
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

    public function actualizarGuiasAntiguas()
    {
        try {
            // Obtener despachos con id_guia null
            $despachos = DB::table('despacho_ventas')
                ->select('id_despacho_venta', 'despacho_venta_guia')
                ->whereNull('id_guia')
                ->groupBy('id_despacho_venta', 'despacho_venta_guia')
                ->get();

            $actualizados = 0;

            foreach ($despachos as $despacho) {
                // Buscar guía correspondiente
                $guia = DB::table('guias')
                    ->select('id_guia')
                    ->where('guia_nro_doc', $despacho->despacho_venta_guia)
                    ->first();

                if ($guia) {
                    // Actualizar el despacho con el id_guia encontrado
                    DB::table('despacho_ventas')
                        ->where('id_despacho_venta', $despacho->id_despacho_venta)
                        ->update(['id_guia' => $guia->id_guia]);

                    $actualizados++;
                }
            }

            return [
                'total_despachos' => count($despachos),
                'actualizados' => $actualizados,
                'message' => 'Proceso completado correctamente'
            ];

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [
                'error' => true,
                'message' => 'Error al actualizar guías: ' . $e->getMessage()
            ];
        }
    }

}
