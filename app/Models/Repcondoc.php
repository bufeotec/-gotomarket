<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Repcondoc extends Model
//hola
{
    use HasFactory;

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
    public function listar_datos($desde, $hasta, $pagination)
    {
        try {
            $query = DB::table('despachos as d')
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->select(
                    'd.id_despacho',
                    'd.despacho_flete',  // Flete total en el gráfico
                    'd.despacho_costo_total',  // Flete Lima y Provincia
                    'dv.despacho_venta_total_volumen',  // Volumen TN despachados
                    'dv.despacho_venta_provincia',  //
                    'dv.despacho_venta_total_kg',  // Peso total en KG
                    DB::raw('(d.despacho_costo_total * dv.despacho_venta_total_kg) AS flete_soles_kg') // Flete en soles por KG
                );

            if ($desde && $hasta) {
                $query->whereBetween('d.created_at', [$desde, $hasta])
                ->whereBetween('dv.created_at', [$desde, $hasta]);
            }

            $result = $query->paginate($pagination);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_total_pedidos_des($desde, $hasta)
    {
        try {
            // Obtener todos los despachos con estado 1
            $query = DB::table('despachos as d')
                ->where('d.despacho_estado', 1);

            // Filtrar por rango de fechas si se proporciona
            if ($desde && $hasta) {
                $query->whereBetween('d.created_at', [$desde, $hasta]);
            }

            $despachos = $query->select('d.id_despacho')->get();

            // Contar los despachos que tienen al menos una nota de crédito asociada
            $despachos_con_error = DB::table('despacho_ventas as dv')
                ->join('notas_creditos as nt', 'dv.id_despacho_venta', '=', 'nt.id_despacho_venta')
                ->select('dv.id_despacho')
                ->distinct()
                ->pluck('dv.id_despacho');

            // Contar despachos sin errores
            $total_despachos_sin_errores = $despachos->whereNotIn('id_despacho', $despachos_con_error)->count();

            return [
                'total_despachos' => count($despachos),
                'total_despachos_sin_nota_credito' => $total_despachos_sin_errores,
                'despachos' => $despachos,
            ];
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [
                'total_despachos' => 0,
                'total_despachos_sin_nota_credito' => 0,
            ];
        }
    }

    public function listarEfectividad($desde, $hasta)
    {
        try {
            // Obtener total de ventas despachadas
            $query_ventas = DB::table('despacho_ventas as dv')
                ->where('dv.despacho_detalle_estado', 1);

            // Filtrar por rango de fechas si se proporciona
            if ($desde && $hasta) {
                $query_ventas->whereBetween('dv.created_at', [$desde, $hasta]);
            }

            $ventas_despachadas = $query_ventas
                ->select(DB::raw('SUM(dv.despacho_venta_cfimporte) as total_ventas'))
                ->first();

            // Obtener total de notas de crédito
            $query_notas = DB::table('despacho_ventas as dv')
                ->join('notas_creditos as nc', 'dv.id_despacho_venta', '=', 'nc.id_despacho_venta')
                ->where('nc.nota_credito_motivo', 1); // Solo motivo 1

            // Filtrar por rango de fechas si se proporciona
            if ($desde && $hasta) {
                $query_notas->whereBetween('dv.created_at', [$desde, $hasta]);
            }

            $notas_credito = $query_notas
                ->select(DB::raw('SUM(dv.despacho_venta_cfimporte) as total_notas_credito'))
                ->first();

            return [
                'ventas_despachadas' => $ventas_despachadas->total_ventas ?? 0, // Total de ventas despachadas
                'notas_credito' => $notas_credito->total_notas_credito ?? 0, // Total de notas de crédito con motivo 1
            ];
        } catch (\Exception $e) {
            report($e);
            return [
                'ventas_despachadas' => 0,
                'notas_credito' => 0,
            ];
        }
    }

    public function listar_dt($search, $pagination, $desde, $hasta, $order = 'desc') {
        try {
            $query = DB::table('facturas_pre_programaciones as fp')
                ->join('facturas_mov as fm', 'fp.id_fac_pre_prog', '=', 'fm.id_fac_pre_prog')
                ->select('fp.*',
                    'fm.fac_envio_valpago',
                    'fm.fac_acept_valpago',
                    'fm.fac_envio_est_fac',
                    'fm.fac_acept_est_fac',
                    'fm.fac_envio_val_rec',
                    'fm.fac_acept_val_rec',
                    'fm.fac_env_ges_fac',
                    'fm.fac_acept_ges_fac',
                    'fm.fac_despacho')
                ->where('fp.fac_pre_prog_estado', '=', 1)

                ->where(function($q) use ($search) {
                    $q->where('fp.fac_pre_prog_factura', 'like', '%' . $search . '%')
                        ->orWhere('fp.fac_pre_prog_cfcodcli', 'like', '%' . $search . '%');
                });

            if ($desde && $hasta) {
                $query->whereBetween('fp.created_at', [$desde, $hasta]);
            }

            $result = $query->paginate($pagination);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
