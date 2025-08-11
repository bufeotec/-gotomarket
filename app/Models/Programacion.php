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
    public function listar_programaciones_realizadas_x_fechas_x_estado($desde, $hasta, $estado) {
        try {
            $query = DB::table('programaciones')
                ->whereBetween('programacion_fecha', [$desde, $hasta])
                ->orderBy('id_programacion', 'desc');

            // Solo aplicar filtro de estado si no está vacío
            if ($estado !== '' && $estado !== null) {
                // Para estado diferente de 2 (Tránsito), filtramos por programacion_estado_aprobacion
                if ($estado != 2) {
                    $query->where('programacion_estado_aprobacion', '=', $estado);
                }
                // Para estado 2 (Tránsito), no filtramos aquí (se filtra después en el despacho)
            }

            return $query->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
    }

    public function listar_programaciones_realizadas_x_fechas_x_estado_new($desde, $hasta, $estados) {
        try {
            $query = DB::table('programaciones')
                ->whereBetween('programacion_fecha', [$desde, $hasta])
                ->orderBy('id_programacion', 'desc');

            // Si $estados es un array y tiene elementos, aplicar filtro
            if (is_array($estados) && count($estados) > 0) {
                // Verificar si algún estado es 2 (Tránsito)
                $tieneTransito = in_array('2', $estados);
                $estadosSinTransito = array_filter($estados, function($estado) {
                    return $estado != '2';
                });

                // Si solo hay estados diferentes de tránsito
                if (count($estadosSinTransito) > 0 && !$tieneTransito) {
                    $query->whereIn('programacion_estado_aprobacion', $estadosSinTransito);
                }
                // Si hay tránsito y otros estados, no filtramos aquí (se filtra en el despacho)
                // Si solo hay tránsito, tampoco filtramos aquí
                else if (count($estadosSinTransito) > 0 && $tieneTransito) {
                    // Caso mixto: se maneja en la consulta de despacho
                }
            }
            // Si $estados es un string (compatibilidad con código anterior)
            else if (is_string($estados) && $estados !== '' && $estados !== null) {
                if ($estados != '2') {
                    $query->where('programacion_estado_aprobacion', '=', $estados);
                }
            }

            return $query->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return collect([]); // Retornar colección vacía en caso de error
        }
    }

    public function listar_programaciones_realizadas_x_fechas_guias($desde, $hasta) {
        try {
            // Para búsqueda por guías, obtenemos las programaciones que tienen
            // despachos relacionados con guías en el rango de fechas
            $query = DB::table('programaciones as p')
                ->join('despachos as d', 'd.id_programacion', '=', 'p.id_programacion')
                ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                ->whereBetween('g.guia_fecha_emision', [$desde, $hasta])
                ->select('p.*')
                ->distinct()
                ->orderBy('p.id_programacion', 'desc');

            return $query->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
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
            $query = DB::table('programaciones as p')
                ->select(
                    'p.id_programacion',
                    'p.id_users',
                    'p.id_users_programacion',
                    'p.programacion_fecha',
                    'p.programacion_estado_aprobacion',
                    'p.programacion_numero_correlativo',
                    'p.programacion_fecha_aprobacion',
                    'p.programacion_estado',
                    'p.created_at'
                )
                ->where('p.programacion_estado_aprobacion', '<>', 0);

            // Filtro por fechas
            if ($desde && $hasta) {
                $query->whereBetween('p.programacion_fecha', [$desde, $hasta]);
            }

            // Filtro por estado de liquidación
            if ($tipo !== null && $tipo !== '') {
                if ($tipo == 1) { // OS Aprobadas
                    $query->whereExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('despachos as d')
                            ->join('liquidacion_detalles as ld', 'ld.id_despacho', '=', 'd.id_despacho')
                            ->join('liquidaciones as l', 'l.id_liquidacion', '=', 'ld.id_liquidacion')
                            ->whereColumn('d.id_programacion', 'p.id_programacion')
                            ->where('l.liquidacion_estado_aprobacion', 1);
                    });
                } elseif ($tipo == 0) { // OS Pendientes
                    $query->where(function($q) {
                        $q->whereNotExists(function ($subQuery) {
                            $subQuery->select(DB::raw(1))
                                ->from('liquidacion_detalles as ld')
                                ->join('despachos as d', 'd.id_despacho', '=', 'ld.id_despacho')
                                ->whereColumn('d.id_programacion', 'p.id_programacion');
                        })
                            ->orWhereExists(function ($subQuery) {
                                $subQuery->select(DB::raw(1))
                                    ->from('despachos as d')
                                    ->join('liquidacion_detalles as ld', 'ld.id_despacho', '=', 'd.id_despacho')
                                    ->join('liquidaciones as l', 'l.id_liquidacion', '=', 'ld.id_liquidacion')
                                    ->whereColumn('d.id_programacion', 'p.id_programacion')
                                    ->where('l.liquidacion_estado_aprobacion', 0);
                            });
                    });
                }
            }

            // Filtro por tipo de reporte
            if ($tipo_reporte == 1 && $desde && $hasta) {
                $query->whereExists(function ($subQuery) use ($desde, $hasta) {
                    $subQuery->select(DB::raw(1))
                        ->from('despachos as d')
                        ->whereColumn('d.id_programacion', 'p.id_programacion')
                        ->whereBetween('p.programacion_fecha', [$desde, $hasta]);
                });
            } elseif ($tipo_reporte == 2 && $desde && $hasta) {
                $query->whereExists(function ($subQuery) use ($desde, $hasta) {
                    $subQuery->select(DB::raw(1))
                        ->from('despachos as d')
                        ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                        ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                        ->whereColumn('d.id_programacion', 'p.id_programacion')
                        ->whereBetween('g.guia_fecha_emision', [$desde, $hasta]);
                });
            }

            return $query->orderBy('p.programacion_fecha')->paginate(200);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 200);
        }
    }
    public function listar_programaciones_historial_programacion_excel($desde, $hasta, $tipo_reporte = null, $tipo = null) {
        try {
            // Primero obtener solo las programaciones sin JOIN con despachos
            $result = DB::table('programaciones as p')
                ->select(
                    'p.id_programacion',
                    'p.id_users',
                    'p.id_users_programacion',
                    'p.programacion_fecha',
                    'p.programacion_estado_aprobacion',
                    'p.programacion_numero_correlativo',
                    'p.programacion_fecha_aprobacion',
                    'p.programacion_estado',
                    'p.created_at'
                );

            // Aplicar filtros de fecha directamente a programaciones
            if ($desde && $hasta) {
                $result->whereBetween('p.programacion_fecha', [$desde, $hasta]);
            }

            // Filtro por estado de liquidación (usando subconsultas)
            if ($tipo !== null && $tipo !== '') {
                if ($tipo == 1) { // OS Aprobadas
                    $result->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('despachos as d')
                            ->join('liquidacion_detalles as ld', 'ld.id_despacho', '=', 'd.id_despacho')
                            ->join('liquidaciones as l', 'l.id_liquidacion', '=', 'ld.id_liquidacion')
                            ->whereColumn('d.id_programacion', 'p.id_programacion')
                            ->where('l.liquidacion_estado_aprobacion', 1);
                    });
                } elseif ($tipo == 0) { // OS Pendientes
                    $result->where(function($query) {
                        $query->whereNotExists(function ($subQuery) {
                            $subQuery->select(DB::raw(1))
                                ->from('despachos as d')
                                ->join('liquidacion_detalles as ld', 'ld.id_despacho', '=', 'd.id_despacho')
                                ->whereColumn('d.id_programacion', 'p.id_programacion');
                        })
                            ->orWhereExists(function ($subQuery) {
                                $subQuery->select(DB::raw(1))
                                    ->from('despachos as d')
                                    ->join('liquidacion_detalles as ld', 'ld.id_despacho', '=', 'd.id_despacho')
                                    ->join('liquidaciones as l', 'l.id_liquidacion', '=', 'ld.id_liquidacion')
                                    ->whereColumn('d.id_programacion', 'p.id_programacion')
                                    ->where('l.liquidacion_estado_aprobacion', 0);
                            });
                    });
                }
            }

            // Filtro por tipo de reporte
            if ($tipo_reporte == 1 && $desde && $hasta) { // Fecha de Despacho
                $result->whereExists(function ($query) use ($desde, $hasta) {
                    $query->select(DB::raw(1))
                        ->from('despachos as d')
                        ->whereColumn('d.id_programacion', 'p.id_programacion')
                        ->whereBetween('p.programacion_fecha', [$desde, $hasta]);
                });
            } elseif ($tipo_reporte == 2 && $desde && $hasta) { // Fecha de Emisión
                $result->whereExists(function ($query) use ($desde, $hasta) {
                    $query->select(DB::raw(1))
                        ->from('despachos as d')
                        ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                        ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                        ->whereColumn('d.id_programacion', 'p.id_programacion')
                        ->whereBetween('g.guia_fecha_emision', [$desde, $hasta]);
                });
            }

            $result->where('p.programacion_estado_aprobacion', '<>', 0)
                ->orderBy('p.programacion_fecha');

            return $result->get();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
    }
}
