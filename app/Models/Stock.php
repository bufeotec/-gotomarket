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

    public function listar_stock_registrados($id_familia, $id_marca, $search, $pagination, $order = 'asc'){
        try {
            $query = DB::table('stocks as s')
                ->join('familias as f', 's.id_familia', 'f.id_familia')
                ->join('marcas as m', 's.id_marca', 'm.id_marca')
                ->where('s.stock_estado', '=', 1);

            // Filtro opcional por familia
            if (!empty($id_familia) && $id_familia !== 'Seleccionar...') {
                $query->where('s.id_familia', '=', $id_familia);
            }

            // Filtro opcional por marca
            if (!empty($id_marca) && $id_marca !== 'Seleccionar...') {
                $query->where('s.id_marca', '=', $id_marca);
            }

            // Filtro de búsqueda
            $query->where(function($q) use ($search) {
                $q->where('s.stock_codigo_caja', 'like', '%' . $search . '%')
                    ->orWhere('s.stock_descripcion_producto', 'like', '%' . $search . '%')
                    ->orWhereNull('s.stock_codigo_caja')
                    ->orWhereNull('s.stock_descripcion_producto');
            });

            $query->orderBy('s.id_stock', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_stock_registrados_excel(){
        try {
            $result = DB::table('stocks')
                ->where('stock_estado', '=', 1)
                ->get();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
