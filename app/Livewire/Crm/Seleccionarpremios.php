<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use App\Models\Logs;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Premio;
use App\Models\Campania;
use App\Models\Campaniaprecio;

class Seleccionarpremios extends Component{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $premio;
    private $campania;
    private $campaniaprecio;
    public function __construct(){
        $this->logs = new Logs();
        $this->premio = new Premio();
        $this->campania = new Campania();
        $this->campaniaprecio = new Campaniaprecio();
    }
    public $id_campania = "";
    public $select_premios = [];

    public function render(){
        $listar_premios_disponibles = $this->premio->listar_premios_disponible();
        $listar_campania = $this->campania->listar_campanias_activos();
        return view('livewire.crm.seleccionarpremios', compact('listar_premios_disponibles', 'listar_campania'));
    }
}
