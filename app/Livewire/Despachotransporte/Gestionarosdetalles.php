<?php

namespace App\Livewire\Despachotransporte;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;

class Gestionarosdetalles extends Component{
    private $logs;
    private $despacho;
    public function __construct(){
        $this->logs = new Logs();
        $this->despacho = new Despacho();
    }
    public $id_despacho = "";
    public $numero_os = "";
    public function mount($id_despacho){
        $this->id_despacho = $id_despacho;
        $this->numero_os = DB::table('despachos')->where('id_despacho', '=', $id_despacho)->value('despacho_numero_correlativo');
    }

    public function render() {
        $listar_info = $this->despacho->listar_info_por_id($this->id_despacho);

        // Verificar si se encontró el despacho
        if ($listar_info) {
            $totalVenta = 0;
            $guiasProcesadas = [];

            // Obtener las guías del despacho con sus detalles
            $guias = DB::table('despacho_ventas as dv')
                ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                ->where('dv.id_despacho', '=', $this->id_despacho)
                ->select('dv.*', 'g.*')
                ->get();

            // Calcular peso y volumen para cada guía
            foreach ($guias as $guia) {
                if (!in_array($guia->id_guia, $guiasProcesadas)) {
                    // Calcular el peso y volumen total para cada guía
                    $detalles = DB::table('guias_detalles')
                        ->where('id_guia', $guia->id_guia)
                        ->get();

                    // Calcular el peso total en kilogramos
                    $pesoTotalGramos = $detalles->sum(function ($detalle) {
                        return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                    });

                    // Convertir el peso total a kilogramos
                    $guia->pesoTotalKilos = $pesoTotalGramos / 1000;

                    $guia->volumenTotal = $detalles->sum(function ($detalle) {
                        return $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
                    });

                    $totalVenta += round(floatval($guia->guia_importe_total_sin_igv), 2);
                    $guiasProcesadas[] = $guia->id_guia;
                }
            }

            // Filtrar guías únicas
            $guiasUnicas = $guias->whereIn('id_guia', $guiasProcesadas);

            $listar_info->guias = $guiasUnicas;
            $listar_info->totalVentaDespacho = $totalVenta;
        }

        return view('livewire.despachotransporte.gestionarosdetalles', compact('listar_info'));
    }
}
