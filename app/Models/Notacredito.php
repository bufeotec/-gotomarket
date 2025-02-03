<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Notacredito extends Model
{
    use HasFactory;
    protected $table = "nota_creditos";
    protected $primaryKey = 'id_nota_credito';

    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_nota_creditox($search, $pagination, $order = 'asc')
    {
        try {
            $query = DB::table('nota_creditos as t')
                ->join('despacho_ventas as dv', 't.id_despacho_venta', '=', 'dv.id_despacho_venta')
                ->where(function($q) use ($search) {
                    $q->where('t.nota_credito_fecha_emision', 'like', '%' . $search . '%')
                        ->orWhere('t.nota_credito_ruc_cliente', 'like', '%' . $search . '%')
                        ->orWhere('t.nota_credito_nombre_cliente', 'like', '%' . $search . '%')
                        ->orWhere('t.nota_credito_motivo', 'like', '%' . $search . '%');
                })
                ->select(
                    't.id_nota_credito',
                    't.nota_credito_fecha_emision',
                    't.nota_credito_ruc_cliente',
                    't.nota_credito_nombre_cliente',
                    't.nota_credito_motivo',
                    't.nota_credito_incidente_registro',
                    'dv.despacho_venta_cfcodcli', // RUC
                    'dv.despacho_venta_cnomcli',  // Nombre del cliente
                    'dv.despacho_venta_total_kg'   // Total KG
                )
                ->orderBy('t.id_nota_credito', $order);

            // Paginación de resultados
            return $query->paginate($pagination);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return []; // Manejo de errores
        }
    }

    public function despachoVenta()
    {
        return $this->belongsTo(DespachoVenta::class, 'id_despacho_venta', 'id_despacho_venta');
    }

    public function listar_nota_credito($search, $pagination, $order = 'asc')
    {
        try {
            $query = DB::table('nota_creditos as nc')
                ->join('despacho_ventas as dv', 'nc.id_despachoventa', '=', 'dv.id_despacho_venta')
                ->where(function ($q) use ($search) {
                    $q->where('nc.nota_credito_fecha_emision', 'like', '%' . $search . '%') // Corregido aquí
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


}
