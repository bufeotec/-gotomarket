<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vendedorintranet extends Model{
    use HasFactory;
    private $logs;
    protected $table = "vendedores_intranet";
    protected $primaryKey = "id_vendedor_intranet";
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_gestion_vendedores($id_cliente = null, $search, $pagination, $order = 'desc'){
        try {
            $query = DB::table('vendedores_intranet as vi')
                ->join('clientes as c', 'vi.id_cliente', 'c.id_cliente')
                ->where(function($q) use ($id_cliente, $search) {
                    // Si se proporciona un id_cliente, filtrar por él
                    if ($id_cliente) {
                        $q->where('vi.id_cliente', '=', $id_cliente);
                    }

                    $q->where(function($subq) use ($search) {
                        $subq->where('vi.vendedor_intranet_dni', 'like', '%' . $search . '%')
                            ->orWhere('vi.vendedor_intranet_nombre', 'like', '%' . $search . '%')
                            ->orWhereNull('vi.vendedor_intranet_dni')
                            ->orWhereNull('vi.vendedor_intranet_nombre');
                    });
                })
                ->orderBy('vi.id_vendedor_intranet', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
