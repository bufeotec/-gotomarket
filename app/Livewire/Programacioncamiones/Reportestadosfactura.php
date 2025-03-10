<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Logs;
use Livewire\Component;
use App\Models\Repcondoc;

class Reportestadosfactura extends Component
{
    private $logs;
    private $repcondoc;

    public $search;
    public $pagination = '10';
    public $desde;
    public $hasta;
    public $order;
    public function __construct(){
        $this->logs = new Logs();
        $this->repcondoc = new Repcondoc();
    }

    public function mount()
    {
        $this->hasta = date('Y-m-d');
        $this->desde = date('Y-m-d');
    }
    public function render()
    {
        $listar_datos = $this->repcondoc->listar_dt($this->search, $this->pagination,$this->desde,$this->hasta,$this->order ='desc');
        return view('livewire.programacioncamiones.reportestadosfactura', compact('listar_datos'));
    }
}
