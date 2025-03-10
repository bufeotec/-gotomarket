<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Repcondoc;
use App\Models\Logs;

class Reporteventaindicadores extends Component
{
    private $logs;
    private $repcondoc;
    public $search = '';
    public $pagination = 10;
    public $desde;
    public $hasta;

    public function __construct(){
        $this->logs = new Logs();
        $this->repcondoc = new Repcondoc();
    }

    public function mount()
    {
        $this->hasta = date('Y-m-d');
        $this->desde = date('Y-m-d', strtotime('-1 months'));
    }




    public function render()
    {
        return view('livewire.programacioncamiones.reporteventaindicadores', [
        'list_data' => $this->listar_datos(),
        'total_ped_des' => $this->listar_total_pedidos_des(),
        'listarEfectividad' => $this->listarEfectividad(),
        'list_dt' => $this->listar_dt(),
            ]);
    }

//Obtiene datos para los gràficos
    public function listar_datos()
    {
        try {
            $query = DB::table('despachos as d')
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->select(
                    'd.id_despacho',
                    'd.despacho_flete',
                    'd.despacho_costo_total',
                    'dv.despacho_venta_total_volumen',
                    'dv.despacho_venta_provincia',
                    'dv.despacho_venta_total_kg',
                    DB::raw('(d.despacho_costo_total * dv.despacho_venta_total_kg) AS flete_soles_kg')
                );

            if ($this->desde && $this->hasta) {
                $query->whereBetween('d.created_at', [$this->desde, $this->hasta])
                    ->whereBetween('dv.created_at', [$this->desde, $this->hasta]);
            }

            return $query->paginate($this->pagination);
        } catch (\Exception $e) {
            return [];
        }
    }

//    Obtiene datos para los totales
    public function listar_total_pedidos_des()
    {
        try {
            $query = DB::table('despachos as d')
                ->where('d.despacho_estado', 1);

            if ($this->desde && $this->hasta) {
                $query->whereBetween('d.created_at', [$this->desde, $this->hasta]);
            }

            $despachos = $query->select('d.id_despacho')->get();

            $despachos_con_error = DB::table('despacho_ventas as dv')
                ->join('notas_creditos as nt', 'dv.id_despacho_venta', '=', 'nt.id_despacho_venta')
                ->select('dv.id_despacho')
                ->distinct()
                ->pluck('dv.id_despacho');

            $total_despachos_sin_errores = $despachos->whereNotIn('id_despacho', $despachos_con_error)->count();

            return [
                'total_despachos' => count($despachos),
                'total_despachos_sin_nota_credito' => $total_despachos_sin_errores,
            ];
        } catch (\Exception $e) {
            return [
                'total_despachos' => 0,
                'total_despachos_sin_nota_credito' => 0,
            ];
        }
    }

    public function listarEfectividad()
    {
        try {
            $query_ventas = DB::table('despacho_ventas as dv')
                ->where('dv.despacho_detalle_estado', 1);

            if ($this->desde && $this->hasta) {
                $query_ventas->whereBetween('dv.created_at', [$this->desde, $this->hasta]);
            }

            $ventas_despachadas = $query_ventas
                ->select(DB::raw('SUM(dv.despacho_venta_cfimporte) as total_ventas'))
                ->first();

            $query_notas = DB::table('despacho_ventas as dv')
                ->join('notas_creditos as nc', 'dv.id_despacho_venta', '=', 'nc.id_despacho_venta')
                ->where('nc.nota_credito_motivo', 1);

            if ($this->desde && $this->hasta) {
                $query_notas->whereBetween('dv.created_at', [$this->desde, $this->hasta]);
            }

            $notas_credito = $query_notas
                ->select(DB::raw('SUM(dv.despacho_venta_cfimporte) as total_notas_credito'))
                ->first();

            return [
                'ventas_despachadas' => $ventas_despachadas->total_ventas ?? 0,
                'notas_credito' => $notas_credito->total_notas_credito ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'ventas_despachadas' => 0,
                'notas_credito' => 0,
            ];
        }
    }

//    Obtiene datos para los reportes de comprobante
    public function listar_dt()
    {
        try {
            $query = DB::table('facturas_pre_programaciones as fp')
                ->join('facturas_mov as fm', 'fp.id_fac_pre_prog', '=', 'fm.id_fac_pre_prog')
                ->select(
                    'fp.*',
                    'fm.fac_envio_valpago',
                    'fm.fac_acept_valpago',
                    'fm.fac_envio_est_fac',
                    'fm.fac_acept_est_fac',
                    'fm.fac_envio_val_rec',
                    'fm.fac_acept_val_rec',
                    'fm.fac_env_ges_fac',
                    'fm.fac_acept_ges_fac',
                    'fm.fac_despacho'
                )
                ->where('fp.fac_pre_prog_estado', '=', 1)
                ->where(function ($q) {
                    $q->where('fp.fac_pre_prog_factura', 'like', '%' . $this->search . '%')
                        ->orWhere('fp.fac_pre_prog_cfcodcli', 'like', '%' . $this->search . '%');
                });

            if ($this->desde && $this->hasta) {
                $query->whereBetween('fp.created_at', [$this->desde, $this->hasta]);
            }

            return $query->paginate($this->pagination);
        } catch (\Exception $e) {
            return [];
        }
    }
}
