<?php

namespace App\Livewire\Programacioncamiones;

use Livewire\Component;
use App\Models\Logs;
use App\Models\Server;


class Local extends Component
{
    private $logs;
    private $server;
    public function __construct(){
        $this->logs = new Logs();
        $this->server = new Server();
    }
    public $searchFactura = "";
    public $filteredFacturas = [];
    public function render(){
        return view('livewire.programacioncamiones.local');
    }

    public function buscar_comprobantes(){
        if ($this->searchFactura !== "") {
            $this->filteredFacturas = $this->server->listar_comprobantes_listos_local($this->searchFactura);
            if (!$this->filteredFacturas || count($this->filteredFacturas) == 0) {
                $this->filteredFacturas = [];
            }
        } else {
            $this->filteredFacturas = [];
        }
    }
}
