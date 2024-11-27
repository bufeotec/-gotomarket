<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DespachoVenta extends Model
{
    use HasFactory;protected $table = "despacho_ventas";
    protected $primaryKey = "id_despacho_venta";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
    public function listar_detalle_x_despacho($id){
        try {
            $result = DB::table('despacho_ventas')
                ->where('id_despacho','=',$id)->get();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
