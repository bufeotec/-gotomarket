<?php

namespace App\Livewire\Reporte;

use Livewire\Component;
use App\Models\Logs;
use App\Models\General;
use App\Models\Notacredito;
use App\Models\DespachoVenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;


class Verreportes extends Component
{
    private $logs;
    private $general;
    private $notacredito;
    private $despachoventa;
    public $list_nc_dv = [];
    public $search_nc_dv;
    public $pagination_nc_dv = 10;

    public $desde;

    public $hasta;
    public $filterRuc;
    public $filterMotivo;

    use WithPagination, WithoutUrlPagination;

    public function __construct(){
        $this->logs = new Logs();
        $this->general = new General();
        $this->notacredito = new Notacredito();
}

    public function mount()
    {
        $this->list_nc_dv = Notacredito::with('despachoVenta')->get();
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
        $this->filterRuc = '';
        $this->filterMotivo = '';
        $this->applyFilters();

    }

    public function render()
    {
        $list_nc_dv = $this->notacredito->listar_nota_credito($this->search_nc_dv, $this->pagination_nc_dv);
        return view('livewire.reporte.verreportes', compact('list_nc_dv'));
    }

    public function updated($propertyName)
    {
        $this->applyFilters();
    }

    public function applyFilters()
    {
        $query = NotaCredito::query();

        if ($this->filterRuc) {
            $query->where('nota_credito_ruc_cliente', 'LIKE', '%' . $this->filterRuc . '%');
        }
        if ($this->filterMotivo) {
            $query->where('nota_credito_motivo', $this->filterMotivo);
        }
        if ($this->desde) {
            $query->whereDate('created_at', '>=', $this->desde);
        }
        if ($this->hasta) {
            $query->whereDate('created_at', '<=', $this->hasta);
        }

        $this->listar_nota_credito = $query->get();
    }

}
