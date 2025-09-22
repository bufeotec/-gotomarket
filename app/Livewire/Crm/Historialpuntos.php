<?php

namespace App\Livewire\Crm;

use App\Models\Logs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Historialpuntos extends Component{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    public function __construct(){
        $this->logs = new Logs();
    }
    public $estado_campania = "";
    public $anio_campania_vigencia;
    public $text_campania = "";
    public $id_users = "";
    public $resultado_campania = [];
    public $anios = [];
    public function mount(){
        $this->id_users = Auth::id();

        // Generar el array de años desde el año actual hasta 5 años atrás, por ejemplo
        $this->anios = range(now('America/Lima')->year, now('America/Lima')->year - 5);
    }

    public function render(){
        return view('livewire.crm.historialpuntos');
    }

    public function buscar_historial_punto() {
        $query = DB::table('campanias')
            ->where('campania_estado', '=', 1);

        // Filtrar por estado de campaña, si se especifica
        if ($this->estado_campania) {
            $query->where('campania_estado_ejecucion', $this->estado_campania);
        }

        // Filtrar por año de vigencia, si se especifica
        if ($this->anio_campania_vigencia) {
            // Usar whereYear para filtrar solo por el año
            $query->whereYear('campania_fecha_inicio', $this->anio_campania_vigencia);
        }

        // Verificar si se proporciona id_users
        if (!is_null($this->id_users)) {
            // Obtener el id_cliente del vendedor a través de las relaciones
            $id_cliente = DB::table('users as u')
                ->join('vendedores_intranet as vt', 'u.id_vendedor_intranet', '=', 'vt.id_vendedor_intranet')
                ->where('u.id_users', $this->id_users)
                ->whereNotNull('u.id_vendedor_intranet')
                ->value('vt.id_cliente');

            // Obtener dni vendedor
            $dni_vendedor = DB::table('users as u')
                ->join('vendedores_intranet as vt', 'u.id_vendedor_intranet', '=', 'vt.id_vendedor_intranet')
                ->where('u.id_users', $this->id_users)
                ->whereNotNull('u.id_vendedor_intranet')
                ->value('vt.vendedor_intranet_dni');

            // Solo aplicar el filtro si se encontró un id_cliente válido
            if (!is_null($id_cliente)) {
                $query->join('puntos as p', 'campanias.id_campania', '=', 'p.id_campania')
                    ->where('p.id_cliente', $id_cliente)
                    ->distinct();
            } else {
                // Si no hay id_cliente válido, retornar array vacío
                return [];
            }
        }

        // Ejecutar la consulta y almacenar el resultado
        $this->resultado_campania = $query->get();

        // Calcular puntos ganados, canjeados y restantes
        foreach ($this->resultado_campania as $rc) {
            // Puntos ganados
            $puntos_ganados = DB::table('puntos_detalles as pd')
                ->join('puntos as p', 'pd.id_punto', '=', 'p.id_punto')
                ->where('p.id_campania', '=', $rc->id_campania)
                ->where('p.id_cliente', '=', $id_cliente)
                ->where('pd.punto_detalle_vendedor', '=', $dni_vendedor)
                ->where('pd.punto_detalle_estado', '=', 1)
                ->sum('pd.punto_detalle_punto_ganado');

            // Puntos canjeados
            $puntos_canjeados = DB::table('canjear_puntos as cp')
                ->join('canjear_puntos_detalles as cpd', 'cp.id_canjear_punto', '=', 'cpd.id_canjear_punto')
                ->where('cp.id_campania', '=', $rc->id_campania)
                ->where('cp.id_users', '=', $this->id_users)
                ->where('cpd.canjear_punto_detalle_estado', '=', 1)
                ->sum(DB::raw('cpd.canjear_punto_detalle_cantidad * cpd.canjear_punto_detalle_pts_unitario'));

            // Puntos restantes
            $puntos_restantes = $puntos_ganados - $puntos_canjeados;

            // Asignar a cada campaña los puntos calculados
            $rc->puntos_ganados = $puntos_ganados;
            $rc->puntos_canjeados = $puntos_canjeados;
            $rc->puntos_restantes = $puntos_restantes;
        }
    }

}
