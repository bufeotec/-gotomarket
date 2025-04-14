<?php

namespace App\Livewire\Gestionvendedor;

use Illuminate\Support\Facades\DB;
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
    public $listar_comprobantes = [];
    public $desde;
    public $hasta;
    public $buscar_guia;
    public function mount(){
        $this->desde = date('Y-01-01');
        $this->hasta =  date('Y-m-d');
    }
    public function render(){
        return view('livewire.gestionvendedor.trackings');
    }

    public function buscar_comprobantes(){
        // Construir la consulta base
        $query = DB::table('guias');

        // Aplicar filtros de fecha si están presentes
        if ($this->desde) {
            $query->whereDate('guia_fecha_emision', '>=', $this->desde);
        }
        if ($this->hasta) {
            $query->whereDate('guia_fecha_emision', '<=', $this->hasta);
        }

        // Aplicar filtro por nombre de cliente si está presente
        if (!empty($this->buscar_guia)) {
            $query->where('guia_nombre_cliente', 'LIKE', '%' . $this->buscar_guia . '%');
        }

        // Obtener los resultados de la consulta
        $this->listar_comprobantes = $query->get();
    }
}
