<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Liquidacion extends Model
{
    use HasFactory;
    protected $table = "liquidaciones";
    protected $primaryKey = "id_liquidacion";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
    public function detalles()
    {
        return $this->hasMany(LiquidacionDetalles::class, 'id_liquidacion');
    }
    public function listar_liquidacion_pendientes($search,$desde, $hasta)
    {
        try {
            $result = DB::table('liquidaciones as li')
                ->select('li.*','tr.transportista_razon_social','u.name','u.last_name','li.created_at as creacion_liquidacion')
                ->join('transportistas as tr', 'li.id_transportistas', '=', 'tr.id_transportistas')
                ->join('users as u', 'li.id_users', '=', 'u.id_users')
                ->where('li.liquidacion_estado', '=', 1)
                ->where('li.liquidacion_estado_aprobacion', '=', 0);
                if ($desde && $hasta){
                    $result->whereBetween(DB::raw('DATE(li.created_at)'), [$desde, $hasta]);
                }
                if ($search) {
                    $result->where(function ($q) use ($search) {
                        $q->where('li.liquidacion_serie', 'like', '%' . $search . '%')
                            ->orWhere('li.liquidacion_correlativo', 'like', '%' . $search . '%')
                            ->orWhere('tr.transportista_ruc', 'like', '%' . $search . '%')
                            ->orWhere('tr.transportista_razon_social', 'like', '%' . $search . '%')
                            ->orWhere('tr.transportista_nom_comercial', 'like', '%' . $search . '%')
                            ->orWhere('tr.transportista_direccion', 'like', '%' . $search . '%')
                            ->orWhere('u.name', 'like', '%' . $search . '%')
                            ->orWhere('u.last_name', 'like', '%' . $search . '%');
                    });
                }
                $result = $result->orderBy('li.id_liquidacion','desc')->get();
                foreach ($result as $re){
                    $re->detalles = DB::table('liquidacion_detalles as ld')
                        ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
                        ->join('programaciones as pr','pr.id_programacion','=','d.id_programacion')
                        ->join('tipo_servicios as ts','ts.id_tipo_servicios','=','d.id_tipo_servicios')
                        ->where('ld.id_liquidacion','=',$re->id_liquidacion)
                        ->orderBy('pr.programacion_fecha', 'desc')->get();

                    foreach ($re->detalles as $des){
                        $des->comprobantes = DB::table('despacho_ventas as dv')
                            ->join('guias as g','dv.id_guia','=','g.id_guia')
                            ->where('dv.id_despacho', '=', $des->id_despacho)
                            ->get();
                        $totalVenta = 0;
                        $totalVentaRestar = 0;
                        $totalPesoRestar = 0;
                        foreach ($des->comprobantes as $com) {
                            $precio = floatval($com->guia_importe_total_sin_igv);
                            $pesoMenos = $com->despacho_venta_total_kg;
                            $totalVenta += $precio;
                            if ($com->despacho_detalle_estado_entrega == 3){
                                $totalVentaRestar += $precio;
                                $totalPesoRestar += $pesoMenos;
                            }
                        }
                        $des->totalVentaDespacho = $totalVenta;
                        $des->totalVentaNoEntregado = $totalVentaRestar;
                        $des->totalPesoNoEntregado = $totalPesoRestar;
                    }
                }

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
//
//    public function listar_liquidacion_aprobadas_excel($search,$desde, $hasta)
//    {
//        try {
//            // se debe traer los transportistas que tenga liquidación
//            $result = DB::table('liquidaciones as li')
//                ->select('tr.id_transportistas','tr.transportista_ruc','tr.transportista_nom_comercial','tr.transportista_razon_social')
//                ->join('transportistas as tr', 'li.id_transportistas', '=', 'tr.id_transportistas')
//                ->where('li.liquidacion_estado', '=', 1);
////                if ($tipo){
////                    $result->where('li.liquidacion_estado_aprobacion', '=', $tipo);
////                }else{
////                    $result->where('li.liquidacion_estado_aprobacion', '<>', 0);
////                }
//                if ($desde && $hasta){
//                    $result->whereBetween(DB::raw('DATE(li.created_at)'), [$desde, $hasta]);
//                }
//                if ($search) {
//                    $result->where(function ($q) use ($search) {
//                        $q->where('tr.transportista_ruc', 'like', '%' . $search . '%')
//                            ->orWhere('tr.transportista_razon_social', 'like', '%' . $search . '%')
//                            ->orWhere('tr.transportista_nom_comercial', 'like', '%' . $search . '%')
//                            ->orWhere('tr.transportista_direccion', 'like', '%' . $search . '%')
//                        ;
//                    });
//                }
//                $result = $result->groupBy('tr.id_transportistas','tr.transportista_ruc','tr.transportista_nom_comercial','tr.transportista_razon_social')->get();
//
//                foreach ($result as $re){
//                    $queryLiquida =  DB::table('liquidaciones')->where('id_transportistas','=',$re->id_transportistas);
////                    if ($tipo){
////                        $queryLiquida->where('liquidacion_estado_aprobacion', '=', $tipo);
////                    }else{
////                        $queryLiquida->where('liquidacion_estado_aprobacion', '<>', 0);
////                    }
//                    if ($desde && $hasta){
//                        $queryLiquida->whereBetween(DB::raw('DATE(created_at)'), [$desde, $hasta]);
//                    }
//                    $queryLiquida = $queryLiquida->orderBy('id_liquidacion','desc')->get();
//                    $re->liquidaciones = $queryLiquida;
//                    /* LISTAREMOS LOS DETALLES PARA SABER EL TOTAL GENERAL */
//                    foreach ($re->liquidaciones as $li){
//                        $li->liquidacion_detalle = DB::table('liquidacion_detalles as ld')
//                            ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
//                            ->where('ld.id_liquidacion','=',$li->id_liquidacion)->get();
//
//
//                        if (count($li->liquidacion_detalle) > 0){
//                            $totalDespachoMontoLiquidado = 0;
//                            foreach ($li->liquidacion_detalle as $li_de){
//                                /* ---------------------- GASTOS --------------------- */
//                                $li_de->gastos = DB::table('liquidacion_gastos')->where('id_liquidacion_detalle','=',$li_de->id_liquidacion_detalle)->get();
//                                $costoTarifa = 0;
//                                $costoMano = 0;
//                                $costoOtros = 0;
//                                $pesoFinalLiquidacion = 0;
//                                if (count($li_de->gastos) >= 3){
//                                    $costoTarifa = $li_de->gastos[0]->liquidacion_gasto_monto;
//                                    $costoMano = $li_de->gastos[1]->liquidacion_gasto_monto;
//                                    $costoOtros = $li_de->gastos[2]->liquidacion_gasto_monto;
//                                    $pesoFinalLiquidacion = $li_de->gastos[3]->liquidacion_gasto_monto;
//                                }
//                                if ($li_de->id_tipo_servicios == 1){
//                                    $totalDespachoMontoLiquidado = $costoTarifa + $costoMano + $costoOtros;
//                                }else{
//                                    $totalDespachoMontoLiquidado = ($costoTarifa * $pesoFinalLiquidacion) + $costoMano + $costoOtros;
//                                }
//                                /* ------------------------------------------------------------ */
//
////                                $li_de->comprobantes = DB::table('despacho_ventas as dv')->where('id_despacho', '=', $li_de->id_despacho)->get();
////                                $totalVenta = 0;
////                                $totalVentaRestar = 0;
////                                $totalPesoRestar = 0;
////                                foreach ($li_de->comprobantes as $com) {
////                                    $precio = floatval($com->despacho_venta_cfimporte);
////                                    $pesoMenos = $com->despacho_venta_total_kg;
////                                    $totalVenta += $precio;
////                                    if ($com->despacho_detalle_estado_entrega == 3){
////                                        $totalVentaRestar += $precio;
////                                        $totalPesoRestar += $pesoMenos;
////                                    }
////                                }
////                                $li_de->totalVentaDespacho = $totalVenta;
////                                $li_de->totalVentaNoEntregado = $totalVentaRestar;
////                                $li_de->totalPesoNoEntregado = $totalPesoRestar;
//                            }
//                            $li->total_sin_igv  = $totalDespachoMontoLiquidado;
//                        }
//                    }
//                }
//
//        } catch (\Exception $e) {
//            $this->logs->insertarLog($e);
//            $result = [];
//        }
//        return $result;
//    }
    public function listar_liquidacion_aprobadas_excel($search, $desde, $hasta, $tipo_reporte = null)
    {
        try {
            // se debe traer los transportistas que tenga liquidación
            $result = DB::table('liquidaciones as li')
                ->join('transportistas as tr', 'li.id_transportistas', '=', 'tr.id_transportistas')
                ->join('users as u', 'u.id_users', '=', 'li.id_users')
                ->where('li.liquidacion_estado', '=', 1)
                ->where('li.liquidacion_estado_aprobacion', '=', 1)
                ->orderBy('li.id_liquidacion');

            // Aplicar filtro por tipo de fecha
            if ($desde && $hasta) {
                if ($tipo_reporte == 1) {
                    // Filtro por fecha de programación
                    $result->whereExists(function ($query) use ($desde, $hasta) {
                        $query->select(DB::raw(1))
                            ->from('liquidacion_detalles as ld')
                            ->join('despachos as d', 'd.id_despacho', '=', 'ld.id_despacho')
                            ->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                            ->whereColumn('ld.id_liquidacion', 'li.id_liquidacion')
                            ->whereBetween(DB::raw('DATE(p.programacion_fecha)'), [$desde, $hasta]);
                    });
                } elseif ($tipo_reporte == 2) {
                    // Filtro por fecha de aprobación
                    $result->whereBetween(DB::raw('DATE(li.liquidacion_fecha_aprobacion)'), [$desde, $hasta]);
                } else {
                    // Filtro por defecto (fecha de creación)
                    $result->whereBetween(DB::raw('DATE(li.created_at)'), [$desde, $hasta]);
                }
            }

            if ($search) {
                $result->where(function ($q) use ($search) {
                    $q->where('li.liquidacion_serie', 'like', '%' . $search . '%')
                        ->orWhere('li.liquidacion_correlativo', 'like', '%' . $search . '%')
                        ->orWhere('tr.transportista_ruc', 'like', '%' . $search . '%')
                        ->orWhere('tr.transportista_razon_social', 'like', '%' . $search . '%')
                        ->orWhere('tr.transportista_nom_comercial', 'like', '%' . $search . '%')
                        ->orWhere('tr.transportista_direccion', 'like', '%' . $search . '%')
                        ->orWhere('u.name', 'like', '%' . $search . '%')
                        ->orWhere('u.last_name', 'like', '%' . $search . '%');
                });
            }

            $result = $result->orderBy('tr.id_transportistas')->get();

            if (count($result) > 0) {
                foreach ($result as $li) {
                    $totalIngresosSinIgv = 0;
                    $totalDespachoMontoLiquidado = 0;
                    $detalles_liquidacion = DB::table('liquidacion_detalles as ld')
                        ->join('despachos as d', 'd.id_despacho', '=', 'ld.id_despacho')
                        ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                        ->leftJoin('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                        ->leftJoin('departamentos as dep', 'dep.id_departamento', '=', 'd.id_departamento')
                        ->leftJoin('provincias as prov', 'prov.id_provincia', '=', 'd.id_provincia')
                        ->where('ld.id_liquidacion', '=', $li->id_liquidacion)
                        ->get();

                    $textProvin = null;
                    $provinciasProcesadas = [];

                    foreach ($detalles_liquidacion as $li_de) {
                        $datelleDespacho = DB::table('liquidacion_gastos')
                            ->where('id_liquidacion_detalle', '=', $li_de->id_liquidacion_detalle)
                            ->get();

                        $costoTarifa = 0;
                        $costoMano = 0;
                        $costoOtros = 0;
                        $pesoFinalLiquidacion = 0;

                        if (count($datelleDespacho) >= 3) {
                            $costoTarifa = $datelleDespacho[0]->liquidacion_gasto_monto;
                            $costoMano = $datelleDespacho[1]->liquidacion_gasto_monto;
                            $costoOtros = $datelleDespacho[2]->liquidacion_gasto_monto;
                            $pesoFinalLiquidacion = $datelleDespacho[3]->liquidacion_gasto_monto ?? 1;
                        }

                        if ($li_de->id_tipo_servicios == 1) {
                            $totalDespachoMontoLiquidado += $costoTarifa + $costoMano + $costoOtros;
                        } else {
                            $totalDespachoMontoLiquidado += ($costoTarifa * $pesoFinalLiquidacion) + $costoMano + $costoOtros;
                        }

                        if ($li_de->id_tipo_servicios) {
                            $provinci = $li_de->tipo_servicio_concepto;
                            if ($provinci) {
                                $provinciasProcesadas[] = $li_de->tipo_servicio_concepto;
                            }
                        }

                        // Guardar información de fechas y ubicación
                        if (!isset($li->programacion_fecha) && isset($li_de->programacion_fecha)) {
                            $li->programacion_fecha = $li_de->programacion_fecha;
                        }

                        if (!isset($li->despacho_fecha_aprobacion) && isset($li_de->despacho_fecha_aprobacion)) {
                            $li->despacho_fecha_aprobacion = $li_de->despacho_fecha_aprobacion;
                        }

                        if (!isset($li->departamento_nombre) && isset($li_de->departamento_nombre)) {
                            $li->departamento_nombre = $li_de->departamento_nombre;
                        }

                        if (!isset($li->provincia_nombre) && isset($li_de->provincia_nombre)) {
                            $li->provincia_nombre = $li_de->provincia_nombre;
                        }
                    }

                    $totalIngresosSinIgv += $totalDespachoMontoLiquidado;
                    $li->total_sin_igv = $totalIngresosSinIgv;

                    if (!empty($provinciasProcesadas)) {
                        $provinciasProcesadas = array_unique($provinciasProcesadas);
                        $textProvin = implode(' | ', $provinciasProcesadas);
                    }
                    $li->servicios = $textProvin ?: '-';
                }
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_liquidacion_aprobadas_new($search, $desde, $hasta, $tipo_reporte = null){
        try {
            $result = DB::table('liquidaciones as li')
                ->select('li.*','tr.transportista_nom_comercial','tr.transportista_razon_social','u.name','u.last_name','li.created_at as creacion_liquidacion')
                ->join('transportistas as tr', 'li.id_transportistas', '=', 'tr.id_transportistas')
                ->join('users as u', 'li.id_users', '=', 'u.id_users')
                ->where('li.liquidacion_estado', '=', 1)
                ->whereIn('li.liquidacion_estado_aprobacion', [1,2]);

            // Filtro por tipo de fecha
            if ($tipo_reporte == 1) {
                // Filtro por fecha de programación
                if ($desde && $hasta) {
                    $result->whereExists(function ($query) use ($desde, $hasta) {
                        $query->select(DB::raw(1))
                            ->from('liquidacion_detalles as ld')
                            ->join('despachos as d', 'd.id_despacho', '=', 'ld.id_despacho')
                            ->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                            ->whereColumn('ld.id_liquidacion', 'li.id_liquidacion')
                            ->whereBetween(DB::raw('DATE(li.created_at)'), [$desde, $hasta]);
                    });
                }
            } elseif ($tipo_reporte == 2) {
                // Filtro por fecha de aprobación
                if ($desde && $hasta) {
                    $result->whereBetween(DB::raw('DATE(li.liquidacion_fecha_aprobacion)'), [$desde, $hasta]);
                }
            } else {
                // Filtro por defecto (fecha de creación)
                if ($desde && $hasta) {
                    $result->whereBetween(DB::raw('DATE(li.created_at)'), [$desde, $hasta]);
                }
            }

            if ($search) {
                $result->where(function ($q) use ($search) {
                    $q->where('li.liquidacion_serie', 'like', '%' . $search . '%')
                        ->orWhere('li.liquidacion_correlativo', 'like', '%' . $search . '%')
                        ->orWhere('tr.transportista_ruc', 'like', '%' . $search . '%')
                        ->orWhere('tr.transportista_razon_social', 'like', '%' . $search . '%')
                        ->orWhere('tr.transportista_nom_comercial', 'like', '%' . $search . '%')
                        ->orWhere('tr.transportista_direccion', 'like', '%' . $search . '%')
                        ->orWhere('u.name', 'like', '%' . $search . '%')
                        ->orWhere('u.last_name', 'like', '%' . $search . '%');
                });
            }

            $result = $result->orderBy('li.id_liquidacion','desc')->get();

            foreach ($result as $re) {
                $re->detalles = DB::table('liquidacion_detalles as ld')
                    ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
                    ->join('programaciones as pr','pr.id_programacion','=','d.id_programacion')
                    ->join('tipo_servicios as ts','ts.id_tipo_servicios','=','d.id_tipo_servicios')
                    ->where('ld.id_liquidacion','=',$re->id_liquidacion)->get();

                $totalSinIGV = 0;
                foreach ($re->detalles as $des) {
                    $des->comprobantes = DB::table('despacho_ventas as dv')
                        ->join('guias as g', 'dv.id_guia', 'g.id_guia')
                        ->where('dv.id_despacho', '=', $des->id_despacho)
                        ->get();

                    $totalVenta = 0;
                    $totalVentaRestar = 0;
                    $totalPesoRestar = 0;

                    foreach ($des->comprobantes as $com) {
                        $precio = floatval($com->guia_importe_total_sin_igv);
                        $pesoMenos = $com->despacho_venta_total_kg;
                        $totalVenta += $precio;
                        if ($com->despacho_detalle_estado_entrega == 3) {
                            $totalVentaRestar += $precio;
                            $totalPesoRestar += $pesoMenos;
                        }
                    }

                    $des->totalVentaDespacho = $totalVenta;
                    $des->totalVentaNoEntregado = $totalVentaRestar;
                    $des->totalPesoNoEntregado = $totalPesoRestar;

                    // Calcular total sin IGV para la liquidación
                    $gastos = DB::table('liquidacion_gastos')
                        ->where('id_liquidacion_detalle', $des->id_liquidacion_detalle)
                        ->get();

                    $costoTotal = 0;
                    if (count($gastos) >= 3) {
                        $costoTarifa = $gastos[0]->liquidacion_gasto_monto;
                        $costoMano = $gastos[1]->liquidacion_gasto_monto;
                        $costoOtros = $gastos[2]->liquidacion_gasto_monto;
                        $pesoFinal = $gastos[3]->liquidacion_gasto_monto ?? 1;

                        if ($des->id_tipo_servicios == 1) {
                            $costoTotal = $costoTarifa + $costoMano + $costoOtros;
                        } else {
                            $costoTotal = ($costoTarifa * $pesoFinal) + $costoMano + $costoOtros;
                        }
                    }

                    $totalSinIGV += $costoTotal;
                }

                $re->total_sin_igv = $totalSinIGV;
            }

            return $result;

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
    }
    public function listar_liquidacion($desde, $hasta)
    {
        try {
            $result = DB::table('liquidaciones as li')
                ->join('transportistas as tr', 'li.id_transportistas', '=', 'tr.id_transportistas')
                ->join('users as u', 'li.id_users', '=', 'u.id_users')
                ->whereBetween(DB::raw('DATE(li.created_at)'), [$desde, $hasta])
                ->where('li.liquidacion_estado', '=', 1)
                ->orderBy('li.id_liquidacion','desc')
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_liquidacion_id($id)
    {
        try {
            $result = DB::table('liquidaciones as li')->where('li.id_liquidacion', '=', $id)->first();

            $result->detalle = DB::table('liquidacion_detalles')->where('id_liquidacion','=',$result->id_liquidacion)->get();
            foreach ($result->detalle as $de){

                $de->gastos = DB::table('liquidacion_gastos')->where('id_liquidacion_detalle','=',$de->id_liquidacion_detalle)->get();
            }

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_ultima_aprobacion(){
        try {
            $añoActual = date('Y'); // Solo tomamos el año, no toda la fecha

            $result = DB::table('liquidaciones')->where('liquidacion_estado_aprobacion','=',1)->orderBy('liquidacion_numero_correlativo','desc')->first();

            if ($result) {
                // Extraer el año y el correlativo de la última programación
                preg_match('/L-(\d+)-(\d+)/', $result->liquidacion_numero_correlativo, $matches);

                $ultimoAño = $matches[1]; // Año de la última programación
                $ultimoCorrelativo = (int) $matches[2]; // Correlativo de la última programación

                if ($ultimoAño == $añoActual) {
                    // Mismo año: incrementar el correlativo
                    $nuevoCorrelativo = str_pad($ultimoCorrelativo + 1, 5, '0', STR_PAD_LEFT);
                    $corr = "L-$añoActual-$nuevoCorrelativo";
                } else {
                    // Año diferente: reiniciar el correlativo
                    $corr = "L-$añoActual-00001";
                }
            } else {
                // No hay registros previos: iniciar con el primer correlativo
                $corr = "L-$añoActual-00001";
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $corr = "";
        }
        return $corr;
    }


}
