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
            // Obtener información de la liquidación con un JOIN a la tabla liquidacion_detalles
            $this->listar_detalle_liquidacion = DB::table('liquidaciones as l')
                ->join('users as u', 'l.id_users', '=', 'u.id_users')
                ->join('liquidacion_detalles as ld', 'ld.id_liquidacion', '=', 'l.id_liquidacion')
                ->join('despachos as d', 'ld.id_despacho', '=', 'd.id_despacho')
                ->where('l.id_liquidacion', '=', $id)
                ->first();
            // Si se encontró la liquidación, buscar gastos relacionados
            if ($this->listar_detalle_liquidacion) {
                $this->listar_detalle_liquidacion->gastos = DB::table('liquidacion_detalles as ld')
                    ->join('liquidacion_gastos as lg', 'ld.id_liquidacion_detalle', '=', 'lg.id_liquidacion_detalle')
                    ->where('ld.id_liquidacion', '=', $id)
                    ->get();
            }
        } catch (\Exception $e) {
            // Registrar el error en los logs
            $this->logs->insertarLog($e);
        }
    }

}
