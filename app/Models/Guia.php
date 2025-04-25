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
    private $general;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
        $this->general = new General();
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
                ->leftJoin('departamentos as depar', 'depar.id_departamento', '=', 'd.id_departamento')
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
                    $queryReporteTiemposAtencion->whereNull('d.id_departamento'); // los locales no tienen id departamento
                }elseif ($type == 2){ // PROVINCIAS
                    $queryReporteTiemposAtencion->whereIn('depar.departamento_nombre', array_merge($arrayDe[1], $arrayDe[2]));
                }
            }else{

                if ($tipo == 1){// F. Emisión
                    $queryReporteTiemposAtencion->whereDate('g.guia_fecha_emision', '>=', $desde)->whereDate('g.guia_fecha_emision', '<=', $hasta);
                }else{// F. Programación
                    $queryReporteTiemposAtencion->whereDate('p.programacion_fecha', '>=', $desde)->whereDate('p.programacion_fecha', '<=', $hasta);
                }

                if ($type){
                    if ($type == 1){
                        $queryReporteTiemposAtencion->whereNull('d.id_departamento'); // los locales no tienen id departamento
                    }else{
                        if ($type == 2){ // PROVINCIA 1
                            $queryReporteTiemposAtencion->whereIn('depar.departamento_nombre',$arrayDe[1]);
                        }elseif ($type == 3){ // PROVINCIA 2
                            $queryReporteTiemposAtencion->whereIn('depar.departamento_nombre',$arrayDe[2]);
                        }
                    }
                }
            }

            // Obtener la suma total de las diferencias entre las fechas y la cantidad de registros
//            if ($tipo == 1){
//                // F. Emisión
//                $queryReporteTiemposAtencion->selectRaw('
//                    SUM(DATEDIFF(g.updated_at, g.guia_fecha_emision)) as suma_tiempos_entrega,
//                    COUNT(*) as cantidad_registros
//                ');
//            }else{
//                // F. Programación
//                $queryReporteTiemposAtencion->selectRaw('
//                    SUM(DATEDIFF(g.updated_at, p.programacion_fecha)) as suma_tiempos_entrega,
//                    COUNT(*) as cantidad_registros
//                ');
//            }
            $queryReporteTiemposAtencion->selectRaw('
                    SUM(DATEDIFF(g.updated_at, g.guia_fecha_emision)) as suma_tiempos_entrega,
                    COUNT(*) as cantidad_registros
            ');


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
    public function listar_informacion_reporte_indicador_de_valor_transportado($tipo, $desde, $hasta,$arrayDe,$type,$typeGrafico = null,$mesGrafico = null,$geneType = null){
        try {

            $queryReporteTiemposAtencion = DB::table('despachos as d')
                ->select('d.id_despacho')
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->leftJoin('departamentos as depar', 'depar.id_departamento', '=', 'd.id_departamento')
                ->where('d.despacho_estado_aprobacion', '=', 3)
                ->where('d.despacho_liquidado', '=', 1)
            ;


            if ($typeGrafico){

                $anio = substr($mesGrafico, 0, 4);
                $mes = substr($mesGrafico, 5, 2);


                if ($tipo){

                    if ($tipo == 1){
                        // F. Emisión
                        $queryReporteTiemposAtencion->whereYear('g.guia_fecha_emision', $anio)->whereMonth('g.guia_fecha_emision', $mes);

                    }else{
                        // F. Programación
                        $queryReporteTiemposAtencion->whereYear('d.despacho_fecha_aprobacion', $anio)->whereMonth('d.despacho_fecha_aprobacion', $mes);
                    }
                }


                if ($typeGrafico == 2){ // Grafico de flete lima o provincia

                    if ($type == 1){ // LOCAL
                        $queryReporteTiemposAtencion->whereNull('d.id_departamento'); // los locales no tienen id departamento
                    }elseif ($type == 2){ // PROVINCIAS
                        $queryReporteTiemposAtencion->whereIn('depar.departamento_nombre', array_merge($arrayDe[1], $arrayDe[2]));
                    }

                } elseif ($typeGrafico == 3){
                    if ($type == 1){ // LOCAL
                        $queryReporteTiemposAtencion->whereNull('d.id_departamento'); // los locales no tienen id departamento
                    }elseif ($type == 2){ // PROVINCIAS 1
                        $queryReporteTiemposAtencion->whereIn('depar.departamento_nombre', array_merge($arrayDe[1]));
                    }elseif ($type == 3){ // PROVINCIAS 2
                        $queryReporteTiemposAtencion->whereIn('depar.departamento_nombre', array_merge($arrayDe[2]));
                    }
                }

            }else{
                if ($tipo){
                    if ($tipo == 1){
                        // F. Emisión
                        $queryReporteTiemposAtencion->whereDate('g.guia_fecha_emision', '>=', $desde)
                            ->whereDate('g.guia_fecha_emision', '<=', $hasta);

                    }else{
                        // F. Programación
                        $queryReporteTiemposAtencion->whereDate('d.despacho_fecha_aprobacion', '>=', $desde)
                            ->whereDate('d.despacho_fecha_aprobacion', '<=', $hasta);
                    }
                }

                if ($type == 1){
                    $queryReporteTiemposAtencion->whereNull('d.id_departamento'); // los locales no tienen id departamento
                }else{
                    if ($type == 2){ // PROVINCIA 1
                        $queryReporteTiemposAtencion->whereIn('depar.departamento_nombre',$arrayDe[1]);
                    }elseif ($type == 3){ // PROVINCIA 2
                        $queryReporteTiemposAtencion->whereIn('depar.departamento_nombre',$arrayDe[2]);
                    }
                }
            }

            $result = $queryReporteTiemposAtencion->distinct()->pluck('d.id_despacho');

            // sacamos la liquidación
            $totalDespachos = 0;

            foreach( $result as $iRe) {

                $subTotal = $this->general->sacarMontoLiquidacion($iRe);

                $totalDespachos+= $subTotal;

            }


            /* -------Sumar detalles de guías asociadas-------*/
            $totalDetalles = DB::table('guias as g')
                ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
                ->whereIn('dv.id_despacho', $result)
                ->where('g.guia_estado_aprobacion','=',8)
                ->sum('g.guia_importe_total_sin_igv');

            if ($typeGrafico){

                if (!$geneType){
                    $result = $totalDespachos;
                }else{
                    if ($geneType == 1){
                        $result = $totalDespachos;
                    }elseif ($geneType == 2){
                        $result = $totalDetalles;
                    }
                }

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
    public function listar_informacion_reporte_total_de_valor_transportado($tipo, $desde, $hasta,$arrayDe,$type,$typeGrafico = null,$mesGrafico = null){
        try {

            $queryReporteTiemposAtencion = DB::table('despachos as d')
                ->select('d.id_despacho')
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->leftJoin('departamentos as depar', 'depar.id_departamento', '=', 'd.id_departamento')
                ->where('d.despacho_estado_aprobacion', '=', 3)
                ->where('d.despacho_liquidado', '=', 1)
            ;


            if ($typeGrafico){

                $anio = substr($mesGrafico, 0, 4);
                $mes = substr($mesGrafico, 5, 2);

                if ($tipo){
                    if ($tipo == 1){ // F. Emisión
                        $queryReporteTiemposAtencion->whereYear('g.guia_fecha_emision', $anio)->whereMonth('g.guia_fecha_emision', $mes);
                    }else{ // F. Programación
                        $queryReporteTiemposAtencion->whereYear('d.despacho_fecha_aprobacion', $anio)->whereMonth('d.despacho_fecha_aprobacion', $mes);
                    }
                }

            }else{
                if ($tipo){
                    if ($tipo == 1){
                        // F. Emisión
                        $queryReporteTiemposAtencion->whereDate('g.guia_fecha_emision', '>=', $desde)
                            ->whereDate('g.guia_fecha_emision', '<=', $hasta);

                    }else{
                        // F. Programación
                        $queryReporteTiemposAtencion->whereDate('d.despacho_fecha_aprobacion', '>=', $desde)
                            ->whereDate('d.despacho_fecha_aprobacion', '<=', $hasta);
                    }
                }
            }
            $result = $queryReporteTiemposAtencion->distinct()->pluck('d.id_despacho');

            $totalDespachos = 0;
            $idProgramacion = [];

            foreach( $result as $iRe) {

                $despa = DB::table('despachos')->where('id_despacho','=',$iRe)->first();
                if ($despa) {
                    // Validar que no esté ya en el array
                    if (!in_array($despa->id_programacion, $idProgramacion)) {
                        $idProgramacion[] = $despa->id_programacion;
                    }
                }
            }
            foreach ($idProgramacion as $idPro){
                // sacamos el primer despacho
                $despacho =  DB::table('despachos')->where('id_programacion','=',$idPro)
                    ->where('despacho_estado_aprobacion', '=', 3)
                    ->where('despacho_liquidado', '=', 1)
                    ->orderBy('id_despacho','asc')->first();

                if ($despacho){
                    $totalDetalles = DB::table('guias as g')
                        ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
                        ->where('dv.id_despacho', '=',$despacho->id_despacho)
                        ->where('g.guia_estado_aprobacion','=',8)
                        ->sum('g.guia_importe_total_sin_igv');

                    $totalDespachos+= $totalDetalles;
                }
            }

            $result = $totalDespachos;

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = 0;
        }

        return $result;
    }
    public function listar_informacion_reporte_total_de_peso_transportado($tipo, $desde, $hasta,$arrayDe,$type,$typeGrafico = null,$mesGrafico = null){
        try {

            $queryReporteTiemposAtencion = DB::table('despachos as d')
                ->select('d.id_despacho')
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->leftJoin('departamentos as depar', 'depar.id_departamento', '=', 'd.id_departamento')
                ->where('d.despacho_estado_aprobacion', '=', 3)
                ->where('d.despacho_liquidado', '=', 1)
            ;


            if ($typeGrafico){

                $anio = substr($mesGrafico, 0, 4);
                $mes = substr($mesGrafico, 5, 2);

                if ($tipo){
                    if ($tipo == 1){ // F. Emisión
                        $queryReporteTiemposAtencion->whereYear('g.guia_fecha_emision', $anio)->whereMonth('g.guia_fecha_emision', $mes);
                    }else{ // F. Programación
                        $queryReporteTiemposAtencion->whereYear('d.despacho_fecha_aprobacion', $anio)->whereMonth('d.despacho_fecha_aprobacion', $mes);
                    }
                }

            }else{
                if ($tipo){
                    if ($tipo == 1){
                        // F. Emisión
                        $queryReporteTiemposAtencion->whereDate('g.guia_fecha_emision', '>=', $desde)
                            ->whereDate('g.guia_fecha_emision', '<=', $hasta);

                    }else{
                        // F. Programación
                        $queryReporteTiemposAtencion->whereDate('d.despacho_fecha_aprobacion', '>=', $desde)
                            ->whereDate('d.despacho_fecha_aprobacion', '<=', $hasta);
                    }
                }
            }
            $result = $queryReporteTiemposAtencion->distinct()->pluck('d.id_despacho');

            $idProgramacion = [];
            foreach( $result as $iRe) {

                $despa = DB::table('despachos')->where('id_despacho','=',$iRe)->first();
                if ($despa) {
                    // Validar que no esté ya en el array
                    if (!in_array($despa->id_programacion, $idProgramacion)) {
                        $idProgramacion[] = $despa->id_programacion;
                    }
                }
            }
            $pesoTotalKilos = 0;
            foreach ($idProgramacion as $idPro){
                // sacamos el primer despacho
                $despacho =  DB::table('despachos')->where('id_programacion','=',$idPro)
                    ->where('despacho_estado_aprobacion', '=', 3)
                    ->where('despacho_liquidado', '=', 1)
                    ->orderBy('id_despacho','asc')->first();

                if ($despacho){
                    $detalleDesp = DB::table('despacho_ventas as dv')
                        ->select('dv.id_despacho', 'g.id_guia', 'st.serv_transpt_peso')
                        ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                        ->leftJoin('servicios_transportes as st', 'dv.id_serv_transpt', '=', 'st.id_serv_transpt')
                        ->where('dv.id_despacho', '=', $despacho->id_despacho)->get();

                    foreach ($detalleDesp as $deta){

                        if ($deta->id_guia) {
                            $detallesGuia = DB::table('guias_detalles')->where('id_guia', '=', $deta->id_guia)->get();
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
                }
            }

            $result = $pesoTotalKilos;

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = 0;
        }

        return $result;
    }
    public function listar_informacion_reporte_indicador_de_peso($tipo,$desde, $hasta,$arrayDe,$type,$typeGrafico = null,$mesGrafico = null){
        try {

            $resultDespachos = DB::table('despachos as d')
                ->select('d.id_despacho')
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->leftJoin('departamentos as depar', 'depar.id_departamento', '=', 'd.id_departamento')
                ->where('d.despacho_estado_aprobacion', '=', 3)
                ->where('d.despacho_liquidado', '=', 1)
            ;

            if ($typeGrafico){
                $anio = substr($mesGrafico, 0, 4);
                $mes = substr($mesGrafico, 5, 2);

                if ($tipo){
                    if ($tipo == 1){
                        // F. Emisión
                        $resultDespachos->whereYear('g.guia_fecha_emision', $anio)->whereMonth('g.guia_fecha_emision', $mes);
                    }else{
                        // F. Programación
                        $resultDespachos->whereYear('d.despacho_fecha_aprobacion', $anio)->whereMonth('d.despacho_fecha_aprobacion', $mes);
                    }
                }

                if ($typeGrafico == 2){ // Grafico de flete lima o provincia
                    if ($type == 1){ // LOCAL
                        $resultDespachos->whereNull('d.id_departamento'); // los locales no tienen id departamento
                    }elseif ($type == 2){ // PROVINCIAS
                        $resultDespachos->whereIn('depar.departamento_nombre', array_merge($arrayDe[1], $arrayDe[2]));
                    }
                }elseif ($typeGrafico == 1){
                    if ($type == 1){ // LOCAL
                        $resultDespachos->whereNull('d.id_departamento');
                    }else{
                        if ($type == 2){ // PROVINCIA 1
                            $resultDespachos->whereIn('depar.departamento_nombre',$arrayDe[1]);
                        }elseif ($type == 3){ // PROVINCIA 2
                            $resultDespachos->whereIn('depar.departamento_nombre',$arrayDe[2]);
                        }
                    }
                }
            }else{
                if ($tipo){
                    if ($tipo == 1){
                        // F. Emisión
                        $resultDespachos->whereDate('g.guia_fecha_emision', '>=', $desde)->whereDate('g.guia_fecha_emision', '<=', $hasta);
                    }else{
                        // F. Programación
                        $resultDespachos->whereDate('d.despacho_fecha_aprobacion', '>=', $desde)->whereDate('d.despacho_fecha_aprobacion', '<=', $hasta);
                    }
                }
                if ($type == 1){ // LOCAL
                    $resultDespachos->whereNull('d.id_departamento');
                }else{
                    if ($type == 2){ // PROVINCIA 1
                        $resultDespachos->whereIn('depar.departamento_nombre',$arrayDe[1]);
                    }elseif ($type == 3){ // PROVINCIA 2
                        $resultDespachos->whereIn('depar.departamento_nombre',$arrayDe[2]);
                    }
                }
            }
            $resultDespachos = $resultDespachos->distinct()->get();

            $totalDespachos = 0;
            foreach ($resultDespachos as $re){

                $re->detalle = DB::table('despacho_ventas as dv')
                    ->select('dv.id_despacho', 'g.id_guia', 'st.serv_transpt_peso')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->leftJoin('servicios_transportes as st', 'dv.id_serv_transpt', '=', 'st.id_serv_transpt')
                    ->where('dv.id_despacho', '=', $re->id_despacho)->get();

                $subTotal = $this->general->sacarMontoLiquidacion($re->id_despacho);
                $totalDespachos+= $subTotal;
            }

            $pesoTotalKilos = 0;
            foreach ($resultDespachos as $ite) {
                foreach ($ite->detalle as $deta){
                    if ($deta->id_guia) {
                        $detallesGuia = DB::table('guias_detalles')->where('id_guia', '=', $deta->id_guia)->get();
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
            }
            if ($typeGrafico){

                if ($typeGrafico == 1){

                    $result = $pesoTotalKilos / 1000; // convertir a toneladas

                }else{

                    $result = $pesoTotalKilos > 0 ? round($totalDespachos / $pesoTotalKilos, 2) : 0;
                }
            }else{
                $result = [
                    'costoTotal' => $totalDespachos,
                    'pesoKilos' => $pesoTotalKilos,
                    'porcentaje' => $pesoTotalKilos > 0 ? round($totalDespachos / $pesoTotalKilos, 3) : 0
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
                    'guias.guia_importe_total_sin_igv',
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

    public function obtener_datos_total_efectividad($tipo_reporte, $desde, $hasta){
        try {
            // Primero obtenemos el rango de meses para los gráficos
            $fechaDesde = \Carbon\Carbon::parse($desde);
            $fechaHasta = \Carbon\Carbon::parse($hasta);

            $meses = [];
            $datosMensuales = [];

            while ($fechaDesde <= $fechaHasta) {
                $mesKey = $fechaDesde->format('Y-m');
                $mesNombre = ucfirst($fechaDesde->locale('es')->isoFormat('MMMM'));

                $meses[] = $mesNombre;

                // Inicializar datos mensuales
                $datosMensuales[$mesKey] = [
                    'total_pedidos' => 0,
                    'pedidos_con_devolucion' => 0,
                    'monto_total' => 0,
                    'monto_con_devolucion' => 0,
                    'clientes_unicos' => [],
                    'programaciones_procesadas' => []
                ];

                $fechaDesde->addMonth();
            }

            // Reiniciamos la fecha para la consulta principal
            $fechaDesde = \Carbon\Carbon::parse($desde);

            // Consulta principal para obtener todos los datos
            $query = DB::table('programaciones as p')
                ->join('despachos as d', 'd.id_programacion', '=', 'p.id_programacion')
                ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                ->where('d.despacho_estado_aprobacion', '!=', 4)
                ->where('g.guia_estado_aprobacion', '=', 8);

            // Aplicar filtro de fecha según tipo de reporte
            if ($tipo_reporte == 1) { // F. Emisión
                $query->whereBetween('g.guia_fecha_emision', [$desde, $hasta]);
            } elseif ($tipo_reporte == 2) { // F. Programación
                $query->whereBetween('p.programacion_fecha', [$desde, $hasta]);
            }

            // Obtener todas las guías que cumplen con los filtros
            $guias = $query->select(
                'g.*',
                'd.id_tipo_servicios',
                'p.id_programacion as id_programacion',
                DB::raw('DATE_FORMAT('.($tipo_reporte == 1 ? 'g.guia_fecha_emision' : 'p.programacion_fecha').', "%Y-%m") as mes')
            )
                ->get();

            // Variables para el total general
            $totalGeneral = [
                'total_pedidos' => 0,
                'pedidos_con_devolucion' => 0,
                'monto_total' => 0,
                'monto_con_devolucion' => 0,
                'clientes_unicos' => [],
                'programaciones_procesadas' => []
            ];

            foreach ($guias as $guia) {
                $mesKey = $guia->mes;
                $idProgramacion = $guia->id_programacion;

                // Verificar si la guía tiene devolución
                $tieneDevolucion = DB::table('notas_creditos')
                    ->where('not_cred_nro_doc_ref', $guia->guia_nro_doc_ref)
                    ->where('not_cred_motivo', '=', '1')
                    ->exists();

                // Procesar datos mensuales
                if (isset($datosMensuales[$mesKey])) {
                    // Sumar al monto total (todas las guías)
                    $datosMensuales[$mesKey]['monto_total'] += $guia->guia_importe_total;
                    $totalGeneral['monto_total'] += $guia->guia_importe_total;

                    if ($tieneDevolucion) {
                        $datosMensuales[$mesKey]['monto_con_devolucion'] += $guia->guia_importe_total;
                        $totalGeneral['monto_con_devolucion'] += $guia->guia_importe_total;
                    }

                    // Para despachos locales (tipo 1), contamos por cliente único
                    if ($guia->id_tipo_servicios == 1) {
                        if (!isset($datosMensuales[$mesKey]['clientes_unicos'][$idProgramacion])) {
                            $datosMensuales[$mesKey]['clientes_unicos'][$idProgramacion] = [];
                        }

                        if (!in_array($guia->guia_nombre_cliente, $datosMensuales[$mesKey]['clientes_unicos'][$idProgramacion])) {
                            $datosMensuales[$mesKey]['clientes_unicos'][$idProgramacion][] = $guia->guia_nombre_cliente;
                            $datosMensuales[$mesKey]['total_pedidos']++;
                            $totalGeneral['total_pedidos']++;

                            if ($tieneDevolucion) {
                                $datosMensuales[$mesKey]['pedidos_con_devolucion']++;
                                $totalGeneral['pedidos_con_devolucion']++;
                            }
                        }
                    }
                    // Para despachos provinciales (tipo 2), cada programación cuenta como 1
                    elseif ($guia->id_tipo_servicios == 2 &&
                        !in_array($idProgramacion, $datosMensuales[$mesKey]['programaciones_procesadas'])) {
                        $datosMensuales[$mesKey]['programaciones_procesadas'][] = $idProgramacion;
                        $datosMensuales[$mesKey]['total_pedidos']++;
                        $totalGeneral['total_pedidos']++;

                        if ($tieneDevolucion) {
                            $datosMensuales[$mesKey]['pedidos_con_devolucion']++;
                            $totalGeneral['pedidos_con_devolucion']++;
                        }
                    }
                }
            }

            // Preparar datos para los gráficos
            $graficoDespachos = [
                'pedidos_entregados' => [],
                'entregados_sin_devolucion' => [],
                'efectividad' => []
            ];

            $graficoValores = [
                'soles_entregados' => [],
                'soles_sin_devolucion' => [],
                'efectividad_valor' => []
            ];

            foreach ($meses as $mesNombre) {
                foreach ($datosMensuales as $mesKey => $datos) {
                    if (str_contains($mesKey, $fechaDesde->format('Y-m'))) {
                        $graficoDespachos['pedidos_entregados'][] = $datos['total_pedidos'];
                        $graficoDespachos['entregados_sin_devolucion'][] = $datos['total_pedidos'] - $datos['pedidos_con_devolucion'];
                        $efectividad = $datos['total_pedidos'] > 0 ?
                            (($datos['total_pedidos'] - $datos['pedidos_con_devolucion']) / $datos['total_pedidos']) * 100 : 0;
                        $graficoDespachos['efectividad'][] = round($efectividad, 2);

                        $graficoValores['soles_entregados'][] = $datos['monto_total']; // En miles
                        $graficoValores['soles_sin_devolucion'][] = ($datos['monto_total'] - $datos['monto_con_devolucion']);
                        $efectividadValor = $datos['monto_total'] > 0 ?
                            (($datos['monto_total'] - $datos['monto_con_devolucion']) / $datos['monto_total']) * 100 : 0;
                        $graficoValores['efectividad_valor'][] = round($efectividadValor, 2);

                        $fechaDesde->addMonth();
                        break;
                    }
                }
            }

            // Calcular totales generales
            $enviosSinDevolucion = $totalGeneral['total_pedidos'] - $totalGeneral['pedidos_con_devolucion'];
            $efectividadCantidad = $totalGeneral['total_pedidos'] > 0 ?
                ($enviosSinDevolucion / $totalGeneral['total_pedidos']) * 100 : 0;

            $montoSinDevolucion = $totalGeneral['monto_total'] - $totalGeneral['monto_con_devolucion'];
            $efectividadValor = $totalGeneral['monto_total'] > 0 ?
                ($montoSinDevolucion / $totalGeneral['monto_total']) * 100 : 0;

            return [
                // Datos para tablas
                'total_pedidos_despachados' => $totalGeneral['total_pedidos'],
                'despacho_con_devoluciones' => $totalGeneral['pedidos_con_devolucion'],
                'envios_sin_devoluciones' => $enviosSinDevolucion,
                'indicador_efectividad' => round($efectividadCantidad, 2),
                'monto_total_despachado' => round($totalGeneral['monto_total'], 2),
                'monto_con_devolucion' => round($totalGeneral['monto_con_devolucion'], 2),
                'monto_sin_devolucion' => round($montoSinDevolucion, 2),
                'efectividad_valor' => round($efectividadValor, 2),

                // Datos para gráficos
                'grafico_meses' => $meses,
                'grafico_despachos' => $graficoDespachos,
                'grafico_valores' => $graficoValores
            ];

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [
                'total_pedidos_despachados' => 0,
                'despacho_con_devoluciones' => 0,
                'envios_sin_devoluciones' => 0,
                'indicador_efectividad' => 0,
                'monto_total_despachado' => 0,
                'monto_con_devolucion' => 0,
                'monto_sin_devolucion' => 0,
                'efectividad_valor' => 0,
                'grafico_meses' => [],
                'grafico_despachos' => [
                    'pedidos_entregados' => [],
                    'entregados_sin_devolucion' => [],
                    'efectividad' => []
                ],
                'grafico_valores' => [
                    'soles_entregados' => [],
                    'soles_sin_devolucion' => [],
                    'efectividad_valor' => []
                ]
            ];
        }
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
