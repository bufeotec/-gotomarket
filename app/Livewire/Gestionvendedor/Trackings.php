<?php

namespace App\Livewire\Gestionvendedor;

use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Logs;
use App\Models\DespachoVenta;

class Trackings extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $despachoventa;
    public function __construct(){
        $this->logs = new Logs();
        $this->despachoventa = new DespachoVenta();
    }
    public $search_tracking;
    public $pagination_tracking = 10;

    public function render(){
        $listar_comprobantes = $this->despachoventa->listar_comprobantes($this->search_tracking, $this->pagination_tracking);
        return view('livewire.gestionvendedor.trackings', compact('listar_comprobantes'));
    }
}
