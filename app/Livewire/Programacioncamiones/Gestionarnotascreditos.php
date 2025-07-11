<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Logs;

class Gestionarnotascreditos extends Component{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    public function __construct(){
        $this->logs = new Logs();
    }
    public $fecha_desde = "";
    public $fecha_hasta = "";
    public $buscar_ruc_nombre = "";
    public $buscar_numero_nc = "";
    public $buscar_estado = "";
    public $listar_nc = [];
    public function mount(){
        $this->fecha_desde = date('Y-m-01');
        $this->fecha_hasta = date('Y-m-d');
    }

    public function render(){
        return view('livewire.programacioncamiones.gestionarnotascreditos');
    }

    public function buscar_nc(){
        if (!Gate::allows('buscar_nc')) {
            session()->flash('error', 'No tiene permisos para buscar una nota de crédito.');
            return;
        }

        $query = DB::table('notas_creditos')
            ->where('not_cred_estado_aprobacion', '=', 2);

        // Aplicar filtro por nombre de cliente si existe
        if (!empty($this->buscar_ruc_nombre)) {
            $busqueda = trim($this->buscar_ruc_nombre);

            // Verificar si tiene el formato "RUC - Nombre"
            if (preg_match('/^(\d+)\s*-\s*(.+)$/', $busqueda, $matches)) {
                $ruc = trim($matches[1]);
                $nombre = trim($matches[2]);

                $query->where(function($q) use ($ruc, $nombre) {
                    $q->where('not_cred_ruc_cliente', 'LIKE', '%' . $ruc . '%')
                        ->where('not_cred_nombre_cliente', 'LIKE', '%' . $nombre . '%');
                });
            } else {
                // Búsqueda normal (RUC o Nombre)
                $query->where(function($q) use ($busqueda) {
                    $q->where('not_cred_ruc_cliente', 'LIKE', '%' . $busqueda . '%')
                        ->orWhere('not_cred_nombre_cliente', 'LIKE', '%' . $busqueda . '%');
                });
            }
        }

        // Aplicar filtro por rango de fechas si existen
        if (!empty($this->buscar_numero_nc)) {
            $query->where('not_cred_nro_doc', 'LIKE', '%' . $this->buscar_numero_nc . '%');
        } else {
            // Aplicar filtros de fecha
            if ($this->fecha_desde) {
                $query->whereDate('not_cred_fecha_emision', '>=', $this->fecha_desde);
            }
            if ($this->fecha_hasta) {
                $query->whereDate('not_cred_fecha_emision', '<=', $this->fecha_hasta);
            }

            // Filtro por estado de aprobación
            if (!empty($this->buscar_estado)) {
                $query->where('not_cred_estado', $this->buscar_estado);
            }
        }
        $this->listar_nc = $query->get();
    }
}
