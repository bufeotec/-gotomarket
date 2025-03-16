<?php

namespace App\Livewire\Gestionvendedor;

use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Logs;
use App\Models\DespachoVenta;
use App\Models\Guia;

class Trackings extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $despachoventa;
    private $guia;
    public function __construct(){
        $this->logs = new Logs();
        $this->despachoventa = new DespachoVenta();
        $this->guia = new Guia();
    }
    public $search_tracking;
    public $pagination_tracking = 10;

    public function render(){
//        $listar_comprobantes = $this->despachoventa->listar_comprobantes($this->search_tracking, $this->pagination_tracking);
        $listar_comprobantes = $this->guia->listar_comprobantes($this->search_tracking, $this->pagination_tracking);
        return view('livewire.gestionvendedor.trackings', compact('listar_comprobantes'));
    }
}
