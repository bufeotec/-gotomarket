<?php

namespace App\Livewire\Gestionvendedor;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
    public $buscar_ruc_nombre;
    public $buscar_numero_guia;
    public $buscar_estado;
    public function mount(){
        $this->desde = date('Y-m-01');
        $this->hasta =  date('Y-m-d');
    }
    public function render(){
        return view('livewire.gestionvendedor.trackings');
    }

    public function buscar_comprobantes(){

        if (!Gate::allows('buscar_guias_tracking')) {
            session()->flash('error', 'No tiene permisos para buscar guías.');
            return;
        }


        // Construir la consulta base
        $query = DB::table('guias');

        // Si hay número de guía, solo buscar por ese campo e ignorar otros filtros
        if (!empty($this->buscar_numero_guia)) {
            $query->where('guia_nro_doc', 'LIKE', '%' . $this->buscar_numero_guia . '%');
        } else {
            // Aplicar filtros de fecha
            if ($this->desde) {
                $query->whereDate('guia_fecha_emision', '>=', $this->desde);
            }
            if ($this->hasta) {
                $query->whereDate('guia_fecha_emision', '<=', $this->hasta);
            }

            // Filtro por estado de aprobación
            if (!empty($this->buscar_estado)) {
                $query->where('guia_estado_aprobacion', $this->buscar_estado);
            }
        }

        // Filtro por RUC y/o Nombre
        if (!empty($this->buscar_ruc_nombre)) {
            $busqueda = trim($this->buscar_ruc_nombre);

            // Verificar si tiene el formato "RUC - Nombre"
            if (preg_match('/^(\d+)\s*-\s*(.+)$/', $busqueda, $matches)) {
                $ruc = trim($matches[1]);
                $nombre = trim($matches[2]);

                $query->where(function($q) use ($ruc, $nombre) {
                    $q->where('guia_ruc_cliente', 'LIKE', '%' . $ruc . '%')
                        ->where('guia_nombre_cliente', 'LIKE', '%' . $nombre . '%');
                });
            } else {
                // Búsqueda normal (RUC o Nombre)
                $query->where(function($q) use ($busqueda) {
                    $q->where('guia_ruc_cliente', 'LIKE', '%' . $busqueda . '%')
                        ->orWhere('guia_nombre_cliente', 'LIKE', '%' . $busqueda . '%');
                });
            }
        }

        $this->listar_comprobantes = $query->get();
        if($this->listar_comprobantes){
            $id_usuario = Auth::id();
            $vende = DB::table('users')
                ->where('users_perfil_vendedor', 1)
                ->where('id_users',$id_usuario)
                ->first();

            if ($vende){
                $vendedor = DB::table('users_vendedores as uv')
                    ->join('vendedores as v', 'uv.id_vendedor', '=', 'v.id_vendedor')
                    ->where('uv.id_users', $id_usuario)
                    ->where('uv.user_vendedor_estado',1)
                    ->select('v.vendedor_codigo_vendedor_starsoft')
                    ->get();

                $codigosVendedores = $vendedor->pluck('vendedor_codigo_vendedor_starsoft')->toArray();
                $this->listar_comprobantes = $this->listar_comprobantes->filter(function ($item) use ($codigosVendedores) {
                    return in_array($item->guia_vendedor_codigo, $codigosVendedores);
                });
            }
        }
    }
}
