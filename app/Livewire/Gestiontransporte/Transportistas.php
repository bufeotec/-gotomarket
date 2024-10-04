<?php

namespace App\Livewire\Gestiontransporte;

use App\Models\Logs;
use App\Models\Menu;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Transportistas extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;

    /* ATRIBUTOS PARA DATATABLES */
    public $search_transportistas;
    public $pagination_transportistas = 10;

    /* FIN ATRIBUTOS PARA DATATABLES */

    /* ATRIBUTOS PARA GUARDAR TRANSPORTISTAS */
    public $transportista_ruc;
    public $listar_servicios = array();
    /* FIN  ATRIBUTOS PARA GUARDAR TRANSPORTISTAS */


    public function __construct()
    {
        $this->logs = new Logs();
    }
    #[On('refresh_transportistas')]
    public function render()
    {
        return view('livewire.gestiontransporte.transportistas');
    }

    public function clear_form_transportistas()
    {
        $this->transportista_ruc = "";
    }
    #[On('refresh_select_servicios')]
    public function listarServiciosSelect(){
        $this->listar_servicios =  []; // listar servicos activos;
    }
}
