<?php

namespace App\Livewire\Crm;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Campania;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Reportescampanias extends Component{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $campania;
    public function __construct(){
        $this->logs = new Logs();
        $this->campania = new Campania();
    }
    public $paginate_reporte = 10;
    public $id_campania = "";

    public function render(){
        $listar_campania = $this->campania->listar_campanias_ejecucion();
        $resultados = $this->campania->obtener_resultados_por_campania($this->id_campania, $this->paginate_reporte);

        // Obtener datos adicionales para cada resultado
        if(count($resultados) > 0 && $this->id_campania) {
            foreach($resultados as $r) {
                // Obtener id_user desde vendedor_intranet
                $user = DB::table('users')
                    ->where('id_vendedor_intranet', '=', $r->id_vendedor_intranet)
                    ->first();

                if($user){
                    // Obtener canjear_puntos
                    $canjear_puntos = DB::table('canjear_puntos')
                        ->where('id_users', '=', $user->id_users)
                        ->where('id_campania', '=', $this->id_campania)
                        ->get();

                    $cant_premios_canjeados = 0;
                    $puntos_canjeados_total = 0;

                    foreach($canjear_puntos as $cp) {
                        // Obtener detalles de canjear_puntos_detalles
                        $detalles = DB::table('canjear_puntos_detalles')
                            ->where('id_canjear_punto', '=', $cp->id_canjear_punto)
                            ->get();

                        foreach($detalles as $detalle) {
                            $cant_premios_canjeados += (int)$detalle->canjear_punto_detalle_cantidad;
                            $puntos_canjeados_total += (int)$detalle->canjear_punto_detalle_total_puntos;
                        }
                    }

                    // Agregar datos al resultado
                    $r->cant_premios_canjeados = $cant_premios_canjeados;
                    $r->puntos_canjeados_total = $puntos_canjeados_total;
                    $r->puntos_ganados_total = $r->vendedor_intranet_punto;
                } else {
                    $r->cant_premios_canjeados = 0;
                    $r->puntos_canjeados_total = 0;
                    $r->puntos_ganados_total = $r->vendedor_intranet_punto;
                }
            }
        }

        return view('livewire.crm.reportescampanias', compact('listar_campania', 'resultados'));
    }
}
