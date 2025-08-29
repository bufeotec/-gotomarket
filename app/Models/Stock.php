<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stock extends Model{
    use HasFactory;
    protected $table = "stocks";
    protected $primaryKey = "id_stock";
    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_stock_registrados($search,$pagination,$order = 'asc'){
        try {
            $query = DB::table('stocks')
                ->where('stock_estado', '=', 1)
                ->where(function($q) use ($search) {
                    $q->where('stock_codigo_caja', 'like', '%' . $search . '%')
                        ->orWhere('stock_descripcion_producto', 'like', '%' . $search . '%')
                        ->orWhereNull('stock_codigo_caja')
                        ->orWhereNull('stock_descripcion_producto');
                })->orderBy('id_stock', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
