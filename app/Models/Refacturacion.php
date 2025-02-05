<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Refacturacion extends Model
{
    use HasFactory;
    protected $table = "refacturaciones";
    protected $primaryKey = "id_refacturacion";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_refacturacion($search,$pagination,$order = 'desc'){
        try {
            $query = DB::table('refacturaciones as r')
                ->join('despacho_ventas as dv','r.id_despacho_venta','=','dv.id_despacho_venta')
                ->where(function($q) use ($search) {
                    $q->where('r.refacturacion_cftd', 'like', '%' . $search . '%')
                        ->orWhere('r.refacturacion_cfnumser', 'like', '%' . $search . '%')
                        ->orWhere('r.refacturacion_cfnumdoc', 'like', '%' . $search . '%')
                        ->orWhere('r.refacturacion_factura', 'like', '%' . $search . '%')
                        ->orWhere('r.refacturacion_grefecemision', 'like', '%' . $search . '%')
                        ->orWhere('r.refacturacion_cnomcli', 'like', '%' . $search . '%')
                        ->orWhere('r.refacturacion_cfcodcli', 'like', '%' . $search . '%')
                        ->orWhere('r.refacturacion_guia', 'like', '%' . $search . '%')
                        ->orWhere('r.refacturacion_cfimporte', 'like', '%' . $search . '%')
                        ->orWhere('r.refacturacion_total_kg', 'like', '%' . $search . '%');
                })->orderBy('r.id_refacturacion', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
