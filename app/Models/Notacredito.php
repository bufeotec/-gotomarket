<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Notacredito extends Model
{
    use HasFactory;
    protected $table = "notas_creditos";
    protected $primaryKey = 'id_not_cred';

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

            $query = DB::table('notas_creditos')
//                ->join('despacho_ventas as dv', 'nt.id_despacho_venta', '=', 'dv.id_despacho_venta')
//                ->where('nt.nota_credito_estado', '=', 1)
                ->where(function($q) use ($search) {
                    $q->where('not_cred_motivo', 'like', '%' . $search . '%')
                    ->orWhere('not_cred_motivo_descripcion', 'like', '%' . $search . '%')
                    ->orWhere('not_cred_ruc_cliente', 'like', '%' . $search . '%')
                    ->orWhere('not_cred_nombre_cliente', 'like', '%' . $search . '%');
                })->orderBy('id_not_cred', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_nota_credito_intranet(){
        try {
            $result = DB::table('notas_creditos')
                ->where('not_cred_estado_aprobacion','=',1)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_nc_registradas(){
        try {
            $result = DB::table('notas_creditos')
                ->where('not_cred_estado_aprobacion','=',1)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_nota_credito_detalle($id) {
        try {
            $result = DB::table('notas_creditos as nc')
                ->join('notas_creditos_detalles as ncd', 'nc.id_not_cred', '=', 'ncd.id_not_cred')
                ->where('nc.id_not_cred', '=', $id)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

}
