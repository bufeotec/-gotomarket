<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Logs;
use App\Models\Programacion;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class HistorialProgramacion extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $desde;
    public $hasta;
    /* ---------------------------------------- */
    private $logs;
    private $programacion;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->programacion = new Programacion();
    }
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
    }

    public function render()
    {
        $resultado = $this->programacion->listar_programaciones_realizadas_x_fechas($this->desde,$this->hasta);
        return view('livewire.programacioncamiones.historial-programacion',compact('resultado'));
    }

}
