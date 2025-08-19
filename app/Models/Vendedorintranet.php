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

    public function listar_gestion_vendedores($search,$pagination,$order = 'desc'){
        try {
            $query = DB::table('vendedores_intranet')
                ->where(function($q) use ($search) {
                    $q->where('vendedor_intranet_dni', 'like', '%' . $search . '%')
                        ->orWhere('vendedor_intranet_nombre', 'like', '%' . $search . '%')
                        ->orWhereNull('vendedor_intranet_dni')
                        ->orWhereNull('vendedor_intranet_nombre');
                })->orderBy('id_vendedor_intranet', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
