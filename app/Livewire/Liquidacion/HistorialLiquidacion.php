<?php

namespace App\Livewire\Liquidacion;

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
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
    }
    public function render(){
        $resultado = $this->liquidacion->listar_liquidacion($this->despacho, $this->hasta);
        return view('livewire.liquidacion.historial-liquidacion', compact('resultado'));
    }
}
