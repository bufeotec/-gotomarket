<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Guia extends Model
{
    use HasFactory;
    protected $table = "guias";
    protected $primaryKey = "id_guia";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_comprobantes($search, $pagination, $order = 'desc'){
        try {
            $query = DB::table('guias')
                ->where(function ($q) use ($search) {
                    $q->where('guia_nombre_cliente', 'like', '%' . $search . '%')
                        ->orWhere('guia_ruc_cliente', 'like', '%' . $search . '%')
                        ->orWhere('guia_nro_doc', 'like', '%' . $search . '%');
                })
                ->orderBy('id_guia', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }

    public function listar_guia_x_id($id){
        try {
            $result = DB::table('guias')
                ->where('id_guia','=',$id)
                ->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_guia_detalle_x_id($id) {
        try {
            $result = DB::table('guias as g')
                ->join('guias_detalles as gd', 'g.id_guia', '=', 'gd.id_guia')
                ->where('gd.id_guia', '=', $id)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

//    NUEVO ""
    public function listar_facturas_pre_programacion_estado_dos() {
        try {
            $result = DB::table('guias')
                ->leftJoin('guias_detalles', 'guias.id_guia', '=', 'guias_detalles.id_guia')
                ->where('guias.guia_estado_aprobacion', '=', 2)
                ->select(
                    'guias.*',
                    DB::raw('SUM(guias_detalles.guia_det_cantidad * guias_detalles.guia_det_peso_gramo) as total_peso'),
                    DB::raw('SUM(guias_detalles.guia_det_cantidad * guias_detalles.guia_det_volumen) as total_volumen')
                )
                ->groupBy('guias.id_users',
                    'guias.id_guia',
                    'guias.guia_almacen_origen',
                    'guias.guia_tipo_doc',
                    'guias.guia_nro_doc',
                    'guias.guia_fecha_emision',
                    'guias.guia_tipo_movimiento',
                    'guias.guia_tipo_doc_ref',
                    'guias.guia_nro_doc_ref',
                    'guias.guia_glosa',
                    'guias.guia_fecha_proceso',
                    'guias.guia_hora_proceso',
                    'guias.guia_usuario',
                    'guias.guia_cod_cliente',
                    'guias.guia_ruc_cliente',
                    'guias.guia_nombre_cliente',
                    'guias.guia_forma_pago',
                    'guias.guia_vendedor',
                    'guias.guia_moneda',
                    'guias.guia_tipo_cambio',
                    'guias.guia_estado',
                    'guias.guia_direc_entrega',
                    'guias.guia_nro_pedido',
                    'guias.guia_importe_total',
                    'guias.guia_departamento',
                    'guias.guia_provincia',
                    'guias.guia_destrito',
                    'guias.guia_estado_aprobacion',
                    'guias.guia_estado_registro',
                    'guias.guia_fecha',
                    'guias.created_at',
                    'guias.updated_at',)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_facturas_pre_programacion_estadox($nombre_cliente = null, $fecha_desde = null, $fecha_hasta = null){
        try {
            $query = DB::table('guias as g')
                ->leftJoin('guias_detalles as gd', 'g.id_guia', '=', 'gd.id_guia')
                ->where('g.guia_estado_registro', '=', 1);

            // Aplicar filtro por nombre de cliente si existe
            if (!empty($nombre_cliente)) {
                $query->where('g.guia_nombre_cliente', 'like', '%' . $nombre_cliente . '%');
            }

            // Aplicar filtro por rango de fechas si existen
            if (!empty($fecha_desde)) {
                $query->whereDate('g.guia_fecha_emision', '>=', $fecha_desde);
            }

            if (!empty($fecha_hasta)) {
                $query->whereDate('g.guia_fecha_emision', '<=', $fecha_hasta);
            }

            $result = $query->select(
                'g.*',
                DB::raw('SUM(gd.guia_det_cantidad * gd.guia_det_peso_gramo) as total_peso'),
                DB::raw('SUM(gd.guia_det_cantidad * gd.guia_det_volumen) as total_volumen')
            )
                ->groupBy(
                    'g.id_users',
                    'g.id_guia',
                    'g.guia_almacen_origen',
                    'g.guia_tipo_doc',
                    'g.guia_nro_doc',
                    'g.guia_fecha_emision',
                    'g.guia_tipo_movimiento',
                    'g.guia_tipo_doc_ref',
                    'g.guia_nro_doc_ref',
                    'g.guia_glosa',
                    'g.guia_fecha_proceso',
                    'g.guia_hora_proceso',
                    'g.guia_usuario',
                    'g.guia_cod_cliente',
                    'g.guia_ruc_cliente',
                    'g.guia_nombre_cliente',
                    'g.guia_forma_pago',
                    'g.guia_vendedor',
                    'g.guia_moneda',
                    'g.guia_tipo_cambio',
                    'g.guia_estado',
                    'g.guia_direc_entrega',
                    'g.guia_nro_pedido',
                    'g.guia_importe_total',
                    'g.guia_departamento',
                    'g.guia_provincia',
                    'g.guia_destrito',
                    'g.guia_estado_aprobacion',
                    'g.guia_estado_registro',
                    'g.guia_fecha',
                    'g.created_at',
                    'g.updated_at'
                )
                ->get();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_buscar_guia($nombreCliente)
    {
        try {
            return DB::table('guias')
                ->where('guia_nombre_cliente', 'like', '%' . $nombreCliente . '%')
                ->where('guia_estado_aprobacion', 3)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
    }
}
