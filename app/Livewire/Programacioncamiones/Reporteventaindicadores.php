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
        // Fechas iniciales (últimos 6 meses)
        $this->hasta = date('Y-m-d');
        $this->desde = date('Y-m-d', strtotime('-6 months'));
    }
    public function render()
    {
        $list_data = $this->repcondoc->listar_datos($this->desde, $this->hasta, $this->pagination);
        $total_ped_des = $this->repcondoc->listar_total_pedidos_des($this->desde, $this->hasta);
        $listarEfectividad = $this->repcondoc->listarEfectividad($this->desde, $this->hasta);

        return view('livewire.programacioncamiones.reporteventaindicadores', compact('list_data', 'total_ped_des', 'listarEfectividad'));
    }

//hola
}
