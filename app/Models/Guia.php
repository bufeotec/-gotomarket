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
    public function listar_informacion_reporte_tiempos_atencion_pedido($tipo, $desde, $hasta,$arrayDe,$type,$typeGrafico = null,$mesGrafico = null){
        try {

            $queryReporteTiemposAtencion = DB::table('guias as g')
                ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
                ->join('despachos as d', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('programaciones as p', 'd.id_programacion', '=', 'p.id_programacion')
                ->where('g.guia_estado_aprobacion', 8);

            if ($typeGrafico){

                $anio = substr($mesGrafico, 0, 4);
                $mes = substr($mesGrafico, 5, 2);

                if ($tipo == 1){ // F. Emisión
                    $queryReporteTiemposAtencion->whereYear('g.guia_fecha_emision', $anio)->whereMonth('g.guia_fecha_emision', $mes);
                }else{ // F. Programación
                    $queryReporteTiemposAtencion->whereYear('p.programacion_fecha', $anio)->whereMonth('p.programacion_fecha', $mes);
                }

                if ($type == 1){ // LOCAL
                    $queryReporteTiemposAtencion->whereIn('g.guia_departamento',$arrayDe[0]);

                }elseif ($type == 2){ // PROVINCIAS
                    $queryReporteTiemposAtencion->whereIn('g.guia_departamento', array_merge($arrayDe[1], $arrayDe[2]));
                }
            }else{

                if ($tipo == 1){// F. Emisión
                    $queryReporteTiemposAtencion->whereDate('g.guia_fecha_emision', '>=', $desde)->whereDate('g.guia_fecha_emision', '<=', $hasta);
                }else{// F. Programación
                    $queryReporteTiemposAtencion->whereDate('p.programacion_fecha', '>=', $desde)->whereDate('p.programacion_fecha', '<=', $hasta);
                }

                if ($type == 1){ // LOCAL
                    $queryReporteTiemposAtencion->whereIn('g.guia_departamento',$arrayDe[0]);
                }elseif ($type == 2){ // PROVINCIA 1
                    $queryReporteTiemposAtencion->whereIn('g.guia_departamento',$arrayDe[1]);
                }elseif ($type == 3){ // PROVINCIA 2
                    $queryReporteTiemposAtencion->whereIn('g.guia_departamento',$arrayDe[2]);
                }
            }

            // Obtener la suma total de las diferencias entre las fechas y la cantidad de registros
            if ($tipo == 1){
                // F. Emisión
                $queryReporteTiemposAtencion->selectRaw('
                    SUM(DATEDIFF(g.updated_at, g.guia_fecha_emision)) as suma_tiempos_entrega,
                    COUNT(*) as cantidad_registros
                ');
            }else{
                // F. Programación
                $queryReporteTiemposAtencion->selectRaw('
                    SUM(DATEDIFF(g.updated_at, p.programacion_fecha)) as suma_tiempos_entrega,
                    COUNT(*) as cantidad_registros
                ');
            }


            $resultConsulta = $queryReporteTiemposAtencion->first();
            $tiemposEntrega = $resultConsulta->suma_tiempos_entrega ?? 0;
            $cantidadRegistros = $resultConsulta->cantidad_registros ?? 0;

            $result = $cantidadRegistros > 0 ? $tiemposEntrega / $cantidadRegistros : 0;


        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = 0;
        }

        return $result;
    }
    public function listar_informacion_reporte_indicador_de_valor_transportado($tipo, $desde, $hasta,$arrayDe,$type,$typeGrafico = null,$mesGrafico = null){
        try {

            $queryReporteTiemposAtencion = DB::table('despachos as d')
                ->select('d.id_despacho')
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->where('d.despacho_estado_aprobacion', '!=', 4);


            if ($typeGrafico){

                $anio = substr($mesGrafico, 0, 4);
                $mes = substr($mesGrafico, 5, 2);

                if ($tipo == 1){ // F. Emisión
                    $queryReporteTiemposAtencion->whereYear('g.guia_fecha_emision', $anio)->whereMonth('g.guia_fecha_emision', $mes);
                }else{ // F. Programación
                    $queryReporteTiemposAtencion->whereYear('d.despacho_fecha_aprobacion', $anio)->whereMonth('d.despacho_fecha_aprobacion', $mes);
                }

                if ($typeGrafico == 2){ // Grafico de flete lima o provincia

                    if ($type == 1){ // LOCAL
                        $queryReporteTiemposAtencion->whereIn('g.guia_departamento',$arrayDe[0]);

                    }elseif ($type == 2){ // PROVINCIAS
                        $queryReporteTiemposAtencion->whereIn('g.guia_departamento', array_merge($arrayDe[1], $arrayDe[2]));
                    }

                }

            }else{
                if ($tipo == 1){
                    // F. Emisión
                    $queryReporteTiemposAtencion->whereDate('g.guia_fecha_emision', '>=', $desde)
                        ->whereDate('g.guia_fecha_emision', '<=', $hasta);

                }else{
                    // F. Programación
                    $queryReporteTiemposAtencion->whereDate('d.despacho_fecha_aprobacion', '>=', $desde)
                        ->whereDate('d.despacho_fecha_aprobacion', '<=', $hasta);
                }

                if ($type == 1){ // LOCAL
                    $queryReporteTiemposAtencion->whereIn('g.guia_departamento',$arrayDe[0]);
                }elseif ($type == 2){ // PROVINCIA 1
                    $queryReporteTiemposAtencion->whereIn('g.guia_departamento',$arrayDe[1]);
                }elseif ($type == 3){ // PROVINCIA 2
                    $queryReporteTiemposAtencion->whereIn('g.guia_departamento',$arrayDe[2]);
                }
            }

            $result = $queryReporteTiemposAtencion->distinct()->pluck('d.id_despacho');

            // Sumar costos de despacho
            $totalDespachos = DB::table('despachos')
                ->whereIn('id_despacho', $result)
                ->sum('despacho_costo_total');

            // Sumar detalles de guías asociadas
            $totalDetalles = DB::table('guias as g')
                ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
                ->whereIn('dv.id_despacho', $result)
                ->sum('g.guia_importe_total');

            if ($typeGrafico){

                $result = $totalDespachos;

            }else{
                $result = [
                    'total_despacho' => $totalDespachos,
                    'total_detalles' => $totalDetalles,
                    'porcentaje' => $totalDetalles != 0 ? ($totalDespachos / $totalDetalles) * 100 : 0
                ];
                $result = (object)$result;
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = 0;
        }

        return $result;
    }
    public function listar_informacion_reporte_indicador_de_peso($desde, $hasta,$arrayDe,$type,$typeGrafico = null,$mesGrafico = null){
        try {

            $resultDespachos = DB::table('despachos as d')
                ->select('d.id_despacho',)
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->where('d.despacho_estado_aprobacion', '!=', 4);

            if ($typeGrafico){
                $anio = substr($mesGrafico, 0, 4);
                $mes = substr($mesGrafico, 5, 2);

                $resultDespachos->whereYear('d.despacho_fecha_aprobacion', $anio)->whereMonth('d.despacho_fecha_aprobacion', $mes);

            }else{
                $resultDespachos->whereDate('d.despacho_fecha_aprobacion', '>=', $desde)->whereDate('d.despacho_fecha_aprobacion', '<=', $hasta);
            }
            if ($typeGrafico){
                if ($typeGrafico == 2){

                    if ($type == 1){ // LOCAL
                        $resultDespachos->whereIn('g.guia_departamento',$arrayDe[0]);

                    }elseif ($type == 2){ // PROVINCIAS

                        $resultDespachos->whereIn('g.guia_departamento', array_merge($arrayDe[1], $arrayDe[2]));
                    }
                }else{
                    if ($type == 1){ // LOCAL
                        $resultDespachos->whereIn('g.guia_departamento',$arrayDe[0]);
                    }elseif ($type == 2){ // PROVINCIA 1
                        $resultDespachos->whereIn('g.guia_departamento',$arrayDe[1]);
                    }elseif ($type == 3){ // PROVINCIA 2
                        $resultDespachos->whereIn('g.guia_departamento',$arrayDe[2]);
                    }
                }
            }else{
                if ($type == 1){ // LOCAL
                    $resultDespachos->whereIn('g.guia_departamento',$arrayDe[0]);
                }elseif ($type == 2){ // PROVINCIA 1
                    $resultDespachos->whereIn('g.guia_departamento',$arrayDe[1]);
                }elseif ($type == 3){ // PROVINCIA 2
                    $resultDespachos->whereIn('g.guia_departamento',$arrayDe[2]);
                }
            }

            $resultDespachos = $resultDespachos->distinct()->get();

            foreach ($resultDespachos as $re){

                $re->detalle = DB::table('despacho_ventas as dv')
                    ->select('dv.id_despacho', 'g.id_guia', 'st.serv_transpt_peso')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->leftJoin('servicios_transportes as st', 'dv.id_serv_transpt', '=', 'st.id_serv_transpt')
                    ->where('dv.id_despacho', '=', $re->id_despacho)->get();

            }
            $totalFlete = 0;
            $pesoTotalKilos = 0;
            // 3. Calculamos el peso para cada despacho
            foreach ($resultDespachos as $ite) {

                $despa = DB::table('despachos')->where('id_despacho','=',$ite->id_despacho)->first();

                foreach ($ite->detalle as $deta){

                    if ($deta->id_guia) {

                        $detallesGuia = DB::table('guias_detalles')
                            ->where('id_guia', '=', $deta->id_guia)
                            ->get();

                        $pesoTotalGramos = $detallesGuia->sum(function ($detalle) {
                            return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                        });
                        $pesoTotalKilos += $pesoTotalGramos / 1000;
                    }
                    // Sumar peso de servicio de transporte (si existe)
                    if ($deta->serv_transpt_peso) {
                        $pesoTotalKilos += $deta->serv_transpt_peso;
                    }
                }
                if ($despa){
                    $totalFlete += $despa->despacho_costo_total;
                }
            }

            if ($typeGrafico){

                if ($typeGrafico == 1){

                    $result = $pesoTotalKilos / 1000; // convertir a toneladas

                }else{

                    $result = $pesoTotalKilos > 0 ? round($totalFlete / $pesoTotalKilos, 2) : 0;
                }
            }else{
                $result = [
                    'costoTotal' => $totalFlete,
                    'pesoKilos' => $pesoTotalKilos,
                    'porcentaje' => $pesoTotalKilos > 0 ? round($totalFlete / $pesoTotalKilos, 3) : 0
                ];
                $result = (object)$result;
            }


        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = 0;
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

    public function obtener_datos_efectividad_pedidos($tipoReporte, $desde, $hasta, $tipoDato = 'cantidad'){
        try {
            $result = DB::table('despachos as d')
                ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                ->whereNotNull('d.despacho_numero_correlativo');

            // Aplicar filtro por tipo de fecha
            if ($tipoReporte == '2') {
                $result->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                    ->whereBetween('p.programacion_fecha', [$desde, $hasta]);
            } else {
                $result->whereBetween('g.guia_fecha_emision', [$desde, $hasta]);
            }

            if ($tipoDato === 'cantidad') {
                // Primero obtenemos todos los despachos únicos con su programación
                $despachos = $result->select('d.id_despacho', 'd.id_programacion')->distinct()->get();

                // Agrupamos despachos por programación para identificar mixtos
                $despachosPorProgramacion = [];
                foreach ($despachos as $despacho) {
                    $despachosPorProgramacion[$despacho->id_programacion][] = $despacho->id_despacho;
                }

                // Contamos despachos únicos considerando los mixtos
                $despachosUnicos = [];
                foreach ($despachosPorProgramacion as $programacion => $idsDespachos) {
                    // Verificar si es mixto (más de un despacho con la misma programación)
                    if (count($idsDespachos) > 1) {
                        // Si es mixto, contamos como un solo despacho
                        $despachosUnicos[] = $idsDespachos[0]; // Tomamos el primero como representante
                    } else {
                        // Si no es mixto, lo agregamos normal
                        $despachosUnicos[] = $idsDespachos[0];
                    }
                }

                $total = count($despachosUnicos);

                // Despachos con devolución (aplicando misma lógica de mixtos)
                $conDevolucionQuery = DB::table('despachos as d')
                    ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                    ->join('notas_creditos as nc', 'nc.not_cred_nro_doc_ref', '=', 'g.guia_nro_doc_ref')
                    ->where('nc.not_cred_motivo', '1')
                    ->whereNotNull('d.despacho_numero_correlativo');

                if ($tipoReporte == '2') {
                    $conDevolucionQuery->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                        ->whereBetween('p.programacion_fecha', [$desde, $hasta]);
                } else {
                    $conDevolucionQuery->whereBetween('g.guia_fecha_emision', [$desde, $hasta]);
                }

                $despachosConDev = $conDevolucionQuery->select('d.id_despacho', 'd.id_programacion')->distinct()->get();

                // Procesamos despachos con devolución considerando mixtos
                $despachosConDevUnicos = [];
                $programacionesConDev = [];

                foreach ($despachosConDev as $despacho) {
                    $programacionesConDev[$despacho->id_programacion][] = $despacho->id_despacho;
                }

                foreach ($programacionesConDev as $programacion => $idsDespachos) {
                    if (count($idsDespachos) > 1) {
                        // Es mixto, contar como uno
                        if (in_array($idsDespachos[0], $despachosUnicos)) {
                            $despachosConDevUnicos[] = $idsDespachos[0];
                        }
                    } else {
                        if (in_array($idsDespachos[0], $despachosUnicos)) {
                            $despachosConDevUnicos[] = $idsDespachos[0];
                        }
                    }
                }

                $conDevolucion = count($despachosConDevUnicos);
                $sinDevolucion = $total - $conDevolucion;
                $porcentajeEfectividad = ($total > 0) ? round(($sinDevolucion / $total) * 100, 2) : 0;

                return [
                    'total' => $total,
                    'con_devolucion' => $conDevolucion,
                    'sin_devolucion' => $sinDevolucion,
                    'porcentaje_efectividad' => $porcentajeEfectividad
                ];
            } else {
                // Para valores monetarios, no aplicamos la lógica de mixtos, sumamos normalmente
                $total = $result->sum(DB::raw('g.guia_importe_total / 1.18'));

                $conDevolucion = $result->clone()
                    ->join('notas_creditos as nc', 'nc.not_cred_nro_doc_ref', '=', 'g.guia_nro_doc_ref')
                    ->where('nc.not_cred_motivo', '1')
                    ->sum(DB::raw('g.guia_importe_total / 1.18'));

                $sinDevolucion = $total - $conDevolucion;
                $porcentajeEfectividad = ($total > 0) ? round(($sinDevolucion / $total) * 100, 2) : 0;

                return [
                    'total' => $total,
                    'con_devolucion' => $conDevolucion,
                    'sin_devolucion' => $sinDevolucion,
                    'porcentaje_efectividad' => $porcentajeEfectividad
                ];
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = 0;
        }

        return $result;
    }

    public function obtener_datos_mensuales_efectividad_pedidos($tipoReporte, $desde, $hasta, $tipoDato = 1){
        try {
            $result = DB::table('despachos as d')
                ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                ->whereNotNull('d.despacho_numero_correlativo');

            // Subconsulta para identificar despachos mixtos
            $subqueryMixto = DB::table('despachos as d2')
                ->join('despacho_ventas as dv2', 'dv2.id_despacho', '=', 'd2.id_despacho')
                ->join('guias as g2', 'g2.id_guia', '=', 'dv2.id_guia')
                ->join('guias_detalles as gd2', 'gd2.id_guia', '=', 'g2.id_guia')
                ->whereColumn('d2.id_programacion', 'd.id_programacion')
                ->where('d2.id_tipo_servicios', 2)
                ->select(DB::raw('1'))
                ->limit(1);

            // Seleccionar campos según tipo de dato
            if ($tipoDato === 1) {
                $result->select(
                    DB::raw('YEAR(' . ($tipoReporte == '2' ? 'p.programacion_fecha' : 'g.guia_fecha_emision') . ') as anio'),
                    DB::raw('MONTH(' . ($tipoReporte == '2' ? 'p.programacion_fecha' : 'g.guia_fecha_emision') . ') as mes'),
                    DB::raw('COUNT(DISTINCT CASE
                    WHEN EXISTS (' . $subqueryMixto->toSql() . ') THEN d.id_programacion
                    ELSE d.id_despacho
                END) as total'),
                    DB::raw('SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM notas_creditos nc
                    WHERE nc.not_cred_nro_doc_ref = g.guia_nro_doc_ref
                    AND nc.not_cred_motivo = 1
                ) THEN 1 ELSE 0 END) as con_devolucion')
                )->mergeBindings($subqueryMixto);
            } else {
                $result->select(
                    DB::raw('YEAR(' . ($tipoReporte == '2' ? 'p.programacion_fecha' : 'g.guia_fecha_emision') . ') as anio'),
                    DB::raw('MONTH(' . ($tipoReporte == '2' ? 'p.programacion_fecha' : 'g.guia_fecha_emision') . ') as mes'),
                    DB::raw('SUM(g.guia_importe_total / 1.18) as total'),
                    DB::raw('SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM notas_creditos nc
                    WHERE nc.not_cred_nro_doc_ref = g.guia_nro_doc_ref
                    AND nc.not_cred_motivo = 1
                ) THEN g.guia_importe_total / 1.18 ELSE 0 END) as con_devolucion')
                );
            }

            // Aplicar filtro por tipo de fecha
            if ($tipoReporte == '2') {
                $result->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                    ->whereBetween('p.programacion_fecha', [$desde, $hasta])
                    ->groupBy(DB::raw('YEAR(p.programacion_fecha)'), DB::raw('MONTH(p.programacion_fecha)'));
            } else {
                $result->whereBetween('g.guia_fecha_emision', [$desde, $hasta])
                    ->groupBy(DB::raw('YEAR(g.guia_fecha_emision)'), DB::raw('MONTH(g.guia_fecha_emision)'));
            }

            return $result->orderBy('anio', 'asc')->orderBy('mes', 'asc')->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = 0;
        }

        return $result;
    }

    public function obtenerReporteEstadoDocumentos($estado = null, $tipoReporte, $desde = null, $hasta = null, $diasLimite){
        try {
            $result = DB::table('guias')
                ->select(
                    'guia_estado_aprobacion as estado_id',
                    DB::raw('CASE guia_estado_aprobacion
                WHEN 1 THEN "Créditos"
                WHEN 3 THEN "Pendiente de Programación"
                WHEN 4 THEN "Programado"
                WHEN 7 THEN "En camino"
                ELSE "Desconocido" END as zona'),
                    DB::raw('ROUND(AVG(DATEDIFF(NOW(), updated_at)), 2) as promedio'),
                    DB::raw('COUNT(*) as cantidad')
                )
                ->whereIn('guia_estado_aprobacion', [1, 3, 4, 7]);

            // Filtro por estado específico
            if ($estado) {
                $result->where('guia_estado_aprobacion', $estado);
            }

            // Filtro por fechas para historial
            if ($tipoReporte == 2) {
                $result->whereBetween('guia_fecha_emision', [$desde, $hasta]);
            }

            // Filtro por días excedidos según el estado
            $result->where(function($q) use ($diasLimite) {
                foreach ($diasLimite as $estadoId => $dias) {
                    $q->orWhere(function($sub) use ($estadoId, $dias) {
                        $sub->where('guia_estado_aprobacion', $estadoId)
                            ->whereRaw('DATEDIFF(NOW(), updated_at) > ?', [$dias]);
                    });
                }
            });

            return $result->groupBy('guia_estado_aprobacion', 'zona')->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = 0;
        }

        return $result;
    }

    public function obtenerDetallesZona($estado, $tipoReporte, $desde, $hasta, $diasAlerta){
        try {
            $result = DB::table('guias')
                ->where('guia_estado_aprobacion', $estado)
                ->whereRaw("DATEDIFF(NOW(), updated_at) > ?", [$diasAlerta]);

            if ($tipoReporte == 2) {
                $result->whereBetween('guia_fecha_emision', [$desde, $hasta]);
            }

            return $result->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = 0;
        }

        return $result;
    }
}
