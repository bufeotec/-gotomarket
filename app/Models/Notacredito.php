<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Notacredito extends Model
{
    use HasFactory;
    protected $table = "notas_creditos";
    protected $primaryKey = 'id_nota_credito';

    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function despachoVenta()
    {
        return $this->belongsTo(DespachoVenta::class, 'id_despacho_venta', 'id_despacho_venta');
    }

    public function listar_nota_credito($search, $pagination, $order = 'asc')
    {
        try {
            $query = DB::table('nota_creditos as nc')
                ->join('despacho_ventas as dv', 'nc.id_despacho_venta', '=', 'dv.id_despacho_venta')
                ->where(function ($q) use ($search) {
                    $q->where('nc.created_at', 'like', '%' . $search . '%')
                    ->orWhere('nc.nota_credito_ruc_cliente', 'like', '%' . $search . '%')
                        ->orWhere('nc.nota_credito_nombre_cliente', 'like', '%' . $search . '%')
                        ->orWhere('nc.nota_credito_motivo', 'like', '%' . $search . '%')
                        ->orWhere('dv.despacho_venta_total_kg', 'like', '%' . $search . '%');
                })
                ->orderBy('nc.id_nota_credito', $order);

            $result = $query->paginate($pagination);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_despacho(){
        try {
            $result = DB::table('despacho_ventas')
                ->whereIn('despacho_detalle_estado_entrega', [2,3,4])
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_nota_credito_activo($search,$pagination,$order = 'desc'){
        try {

            $query = DB::table('notas_creditos_guias')
//                ->join('despacho_ventas as dv', 'nt.id_despacho_venta', '=', 'dv.id_despacho_venta')
//                ->where('nt.nota_credito_estado', '=', 1)
                ->where(function($q) use ($search) {
                    $q->where('not_cre_guia_num_doc', 'like', '%' . $search . '%')
                    ->orWhere('not_cre_guia_codigo_cliente', 'like', '%' . $search . '%')
                    ->orWhere('not_cre_guia_nombre_cliente', 'like', '%' . $search . '%');
                })->orderBy('id_nota_credito_guia', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

}
