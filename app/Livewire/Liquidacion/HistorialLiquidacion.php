<?php

namespace App\Livewire\Liquidacion;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;
use App\Models\Liquidacion;

class HistorialLiquidacion extends Component
{
    private $logs;
    private $despacho;
    private $liquidacion;
    public function __construct(){
        $this->logs = new Logs();
        $this->despacho = new Despacho();
        $this->liquidacion = new Liquidacion();
    }
    public $desde;
    public $hasta;
    public $listar_detalle_liquidacion = [];
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
    }
    public function render(){
        $resultado = $this->liquidacion->listar_liquidacion($this->desde, $this->hasta);
        return view('livewire.liquidacion.historial-liquidacion', compact('resultado'));
    }

    public function listar_informacion_liquidacion($id){
        try {
            $this->listar_detalle_liquidacion = DB::table('liquidaciones as l')
                ->join('users as u', 'l.id_users', '=', 'u.id_users')
                ->join('liquidacion_detalles as ld', 'ld.id_liquidacion', '=', 'l.id_liquidacion')
                ->join('despachos as d', 'ld.id_despacho', '=', 'd.id_despacho')
                ->where('l.id_liquidacion', '=', $id)
                ->get();
            // Asignar los gastos a cada detalle de liquidación
            foreach ($this->listar_detalle_liquidacion as $detalle) {
                $detalle->gastos = DB::table('liquidacion_detalles as ld')
                    ->join('liquidacion_gastos as lg', 'ld.id_liquidacion_detalle', '=', 'lg.id_liquidacion_detalle')
                    ->where('ld.id_liquidacion', '=', $id)
                    ->where('ld.id_despacho', '=', $detalle->id_despacho)
                    ->get();
            }
        } catch (\Exception $e) {
            // Registrar el error en los logs
            $this->logs->insertarLog($e);
        }
    }

}
