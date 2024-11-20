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
        $this->filteredFacturas = $this->server->listar_comprobantes_listos_local($this->searchFactura);
        return view('livewire.programacioncamiones.local');
    }
}
