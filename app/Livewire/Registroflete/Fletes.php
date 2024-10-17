<?php

namespace App\Livewire\Registroflete;

use App\Models\General;
use App\Models\Logs;
use Livewire\Component;
use App\Models\Transportista;
use Livewire\WithPagination;

class Fletes extends Component
{
    private $logs;
    private $transportistas;
    public $search_transportistas;
    public $pagination_transportistas = 10;
    public function __construct(){
        $this->logs = new Logs();
        $this->transportistas = new Transportista();
    }
    public function render()
    {
        $transportistas = $this->transportistas->listar_transportistas($this->search_transportistas,$this->pagination_transportistas);
        return view('livewire.registroflete.fletes', compact('transportistas'));
    }
}
