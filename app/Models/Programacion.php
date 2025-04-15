<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Programacion extends Model
{
    use HasFactory;
    protected $table = "programaciones";
    protected $primaryKey = "id_programacion";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
    public function informacion_id($id){
        try {
            $result = DB::table('programaciones')
                ->where('id_programacion','=',$id)
                ->first();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_programaciones_realizadas_x_fechas_x_estado($desde,$hasta,$estado){
        try {
            $result = DB::table('programaciones')
                ->whereBetween('programacion_fecha',[$desde,$hasta])
                ->where('programacion_estado_aprobacion','=',$estado)
                ->orderBy('id_programacion','desc')
                ->paginate(20);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_ultima_aprobacion(){
        try {
            // Obtener los últimos dos dígitos del año actual
            $añoActual = date('y'); // 'y' devuelve el año en formato de dos dígitos (e.g., 24 para 2024)

            // Consultar la última programación aprobada
            $result = DB::table('programaciones')
                ->where('programacion_estado_aprobacion', '=', 1)
                ->orderBy('programacion_numero_correlativo', 'desc')
                ->first();

            if ($result) {
                // Extraer el año y el correlativo de la última programación
                preg_match('/P(\d+)-(\d+)/', $result->programacion_numero_correlativo, $matches);

                $ultimoAño = $matches[1]; // Año de la última programación (e.g., 24)
                $ultimoCorrelativo = (int) $matches[2]; // Correlativo de la última programación (e.g., 00005)

                if ($ultimoAño == $añoActual) {
                    // Mismo año: incrementar el correlativo
                    $nuevoCorrelativo = str_pad($ultimoCorrelativo + 1, 5, '0', STR_PAD_LEFT);
                    $corr = "P$añoActual-$nuevoCorrelativo";
                } else {
                    // Año diferente: reiniciar el correlativo
                    $corr = "P$añoActual-00001";
                }
            } else {
                // No hay registros previos: iniciar con el primer correlativo
                $corr = "P$añoActual-00001";
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $corr = "";
        }
        return $corr;
    }
    public function listar_informacion_x_id($id){
        try {
            $result = DB::table('programaciones as p')
                ->select('p.*','us_one.name as nombre_creacion','us_one.last_name as apellido_creacion','us_down.name as nombre_aprobacion','us_down.last_name as apellido_aprobacion')
                ->join('users as us_one','us_one.id_users','=','p.id_users')
                ->leftJoin('users as us_down','us_down.id_users','=','p.id_users_programacion')
                ->where('p.id_programacion','=',$id)->first();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_programaciones_historial_programacion($desde, $hasta, $tipo_reporte = null, $tipo = null) {
        try {
            $result = DB::table('programaciones as p')
                ->leftJoin('despachos as d', 'd.id_programacion', '=', 'p.id_programacion');

            // Selección de campos básicos
            $result->select(
                'p.id_programacion',
                'p.id_users',
                'p.id_users_programacion',
                'p.programacion_fecha',
                'p.programacion_estado_aprobacion',
                'p.programacion_numero_correlativo',
                'p.programacion_fecha_aprobacion',
                'p.programacion_estado',
                'p.created_at',
                'd.id_despacho' // Asegurarnos de incluir este campo
            );

            // Filtro por estado de liquidación
            if ($tipo !== null && $tipo !== '') {
                if ($tipo == 1) { // OS Aprobadas
                    $result->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('liquidacion_detalles as ld')
                            ->join('liquidaciones as l', 'l.id_liquidacion', '=', 'ld.id_liquidacion')
                            ->whereColumn('ld.id_despacho', 'd.id_despacho')
                            ->where('l.liquidacion_estado_aprobacion', 1);
                    });
                } elseif ($tipo == 0) { // OS Pendientes
                    $result->where(function($query) {
                        $query->whereNotExists(function ($subQuery) {
                            $subQuery->select(DB::raw(1))
                                ->from('liquidacion_detalles as ld')
                                ->whereColumn('ld.id_despacho', 'd.id_despacho');
                        })
                            ->orWhereExists(function ($subQuery) {
                                $subQuery->select(DB::raw(1))
                                    ->from('liquidacion_detalles as ld')
                                    ->join('liquidaciones as l', 'l.id_liquidacion', '=', 'ld.id_liquidacion')
                                    ->whereColumn('ld.id_despacho', 'd.id_despacho')
                                    ->where('l.liquidacion_estado_aprobacion', 0);
                            });
                    });
                }
            }

            // Filtro por tipo de reporte
            if ($tipo_reporte == 1 && $desde && $hasta) { // Fecha de Despacho
                $result->whereBetween('d.despacho_fecha_aprobacion', [$desde, $hasta]);
            } elseif ($tipo_reporte == 2 && $desde && $hasta) { // Fecha de Emisión
                $result->whereExists(function ($query) use ($desde, $hasta) {
                    $query->select(DB::raw(1))
                        ->from('despacho_ventas as dv')
                        ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                        ->whereColumn('dv.id_despacho', 'd.id_despacho')
                        ->whereBetween('g.guia_fecha_emision', [$desde, $hasta]);
                });
            } elseif ($desde && $hasta) { // Fecha de Programación por defecto
                $result->whereBetween('p.programacion_fecha', [$desde, $hasta]);
            }

            // Agrupamiento y condiciones finales
            $result->where('p.programacion_estado_aprobacion', '<>', 0)
                ->groupBy(
                    'p.id_programacion',
                    'p.id_users',
                    'p.id_users_programacion',
                    'p.programacion_fecha',
                    'p.programacion_estado_aprobacion',
                    'p.programacion_numero_correlativo',
                    'p.programacion_fecha_aprobacion',
                    'p.programacion_estado',
                    'p.created_at',
                    'd.id_despacho'
                )
                ->orderBy('p.programacion_fecha', 'desc');

            return $result->paginate(20);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            // Alternativa para retornar paginación vacía
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }
    }
    public function listar_programaciones_historial_programacion_excel($desde, $hasta, $tipo_reporte = null, $tipo = null) {
        try {
            $result = DB::table('programaciones as p')
                ->leftJoin('despachos as d', 'd.id_programacion', '=', 'p.id_programacion');

            // Selección de campos básicos
            $result->select(
                'p.id_programacion',
                'p.id_users',
                'p.id_users_programacion',
                'p.programacion_fecha',
                'p.programacion_estado_aprobacion',
                'p.programacion_numero_correlativo',
                'p.programacion_fecha_aprobacion',
                'p.programacion_estado',
                'p.created_at',
                'd.id_despacho'
            );

            // Filtro por estado de liquidación
            if ($tipo !== null && $tipo !== '') {
                if ($tipo == 1) { // OS Aprobadas
                    $result->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('liquidacion_detalles as ld')
                            ->join('liquidaciones as l', 'l.id_liquidacion', '=', 'ld.id_liquidacion')
                            ->whereColumn('ld.id_despacho', 'd.id_despacho')
                            ->where('l.liquidacion_estado_aprobacion', 1);
                    });
                } elseif ($tipo == 0) { // OS Pendientes
                    $result->where(function($query) {
                        $query->whereNotExists(function ($subQuery) {
                            $subQuery->select(DB::raw(1))
                                ->from('liquidacion_detalles as ld')
                                ->whereColumn('ld.id_despacho', 'd.id_despacho');
                        })
                            ->orWhereExists(function ($subQuery) {
                                $subQuery->select(DB::raw(1))
                                    ->from('liquidacion_detalles as ld')
                                    ->join('liquidaciones as l', 'l.id_liquidacion', '=', 'ld.id_liquidacion')
                                    ->whereColumn('ld.id_despacho', 'd.id_despacho')
                                    ->where('l.liquidacion_estado_aprobacion', 0);
                            });
                    });
                }
            }

            // Filtro por tipo de reporte
            if ($tipo_reporte == 1 && $desde && $hasta) { // Fecha de Despacho
                $result->whereBetween('d.despacho_fecha_aprobacion', [$desde, $hasta]);
            } elseif ($tipo_reporte == 2 && $desde && $hasta) { // Fecha de Emisión
                $result->whereExists(function ($query) use ($desde, $hasta) {
                    $query->select(DB::raw(1))
                        ->from('despacho_ventas as dv')
                        ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                        ->whereColumn('dv.id_despacho', 'd.id_despacho')
                        ->whereBetween('g.guia_fecha_emision', [$desde, $hasta]);
                });
            } elseif ($desde && $hasta) { // Fecha de Programación por defecto
                $result->whereBetween('p.programacion_fecha', [$desde, $hasta]);
            }

            // Agrupamiento y condiciones finales
            $result->where('p.programacion_estado_aprobacion', '<>', 0)
                ->groupBy(
                    'p.id_programacion',
                    'p.id_users',
                    'p.id_users_programacion',
                    'p.programacion_fecha',
                    'p.programacion_estado_aprobacion',
                    'p.programacion_numero_correlativo',
                    'p.programacion_fecha_aprobacion',
                    'p.programacion_estado',
                    'p.created_at',
                    'd.id_despacho'
                )
                ->orderBy('p.programacion_fecha', 'desc');

            return $result->get();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
    }
}
