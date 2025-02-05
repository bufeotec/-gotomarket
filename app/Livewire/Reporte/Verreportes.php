<?php

namespace App\Livewire\Reporte;

use Livewire\Component;
use App\Models\Notacredito;
use App\Models\DespachoVenta;
use Livewire\WithPagination;
use Carbon\Carbon;

class Verreportes extends Component
{
    use WithPagination;

    public $list_nc_dv = [];
    public $list_dv = [];
    public $desde;
    public $hasta;
    public $totalCostoFlete = 0;
    public $totalKilosDespachados = 0;
    public $totalPedidosEntregados = 0;
    public $totalIncidentes = 0;

    public function mount()
    {
        $this->list_nc_dv = Notacredito::all();
        $this->list_dv = DespachoVenta::all();
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
        $this->calculateTotals();
    }

    private function calculateTotals()
    {
        foreach ($this->list_dv as $despacho) {
            $this->totalCostoFlete += $despacho->despacho_venta_cfimporte ?? 0;
            $this->totalKilosDespachados += $despacho->despacho_venta_total_kg ?? 0;
            if ($despacho->despacho_detalle_estado_entrega) {
                $this->totalPedidosEntregados++;
            }
        }

        $this->totalIncidentes = Notacredito::count();
    }

    public function render()
    {
        return view('livewire.reporte.verreportes', $this->getData());
    }

    private function getData()
    {
        return [
            'despachos' => $this->list_dv,
            'totalCostoFlete' => $this->totalCostoFlete,
            'totalKilosDespachados' => $this->totalKilosDespachados,
            'totalPedidosEntregados' => $this->totalPedidosEntregados,
            'totalIncidentes' => $this->totalIncidentes,
            'costoFleteData' => $this->getGroupedData('despacho_venta_cfimporte'),
            'kilosData' => $this->getGroupedData('despacho_venta_total_kg'),
            'pedidosData' => $this->getGroupedCount(),
            'incidentesData' => $this->getIncidentesData(),
        ];
    }

    private function getGroupedData($field)
    {
        return $this->list_dv->groupBy(fn($item) => Carbon::parse($item->despacho_venta_grefecemision)->format('m'))
            ->map(fn($group) => $group->sum($field))
            ->values()
            ->toArray();
    }

    private function getGroupedCount()
    {
        return $this->list_dv->groupBy(fn($item) => Carbon::parse($item->despacho_venta_grefecemision)->format('m'))
            ->map(fn($group) => $group->count())
            ->values()
            ->toArray();
    }

    private function getIncidentesData()
    {
        return $this->list_nc_dv->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('m'); // Agrupar por mes
        })->map(fn($group) => $group->count()) // Contar incidentes por mes
        ->values()
            ->toArray();
    }
}
