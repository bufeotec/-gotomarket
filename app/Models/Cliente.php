<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cliente extends Model{
    use HasFactory;
    protected $table = "clientes";
    protected $primaryKey = "id_cliente";
    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_cliente_registrados($search,$pagination,$order = 'asc'){
        try {

            $query = DB::table('clientes')
//                ->where('menu_status', '=', 1)
                ->where(function($q) use ($search) {
                    $q->where('cliente_codigo_cliente', 'like', '%' . $search . '%')
                        ->orWhere('cliente_nombre_cliente', 'like', '%' . $search . '%')
                        ->orWhereNull('cliente_codigo_cliente')
                        ->orWhereNull('cliente_nombre_cliente');
                })->orderBy('id_cliente', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
