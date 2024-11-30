<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Server extends Model
{
    use HasFactory;
    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs  = new Logs();
    }
    public function listar_comprobantes_listos_local($search,$desde,$hasta){
        try {
            // FACCAB - COMPROBANTES | MAECLI - CLIENTES | FACDET - COMPROBANTES DETALLE | MAEART - ARTICULOS | MAEART_ADICIONALES - detalles del articulos
            // filtrar nombre del cliente, serie, correlativo, pes y volumen
            $result = DB::connection('sqlsrv_external')
                ->table('FACCAB')
                ->select('FACCAB.CFTD', 'FACCAB.CFNUMSER', 'FACCAB.CFNUMDOC', 'FACCAB.CFIMPORTE', 'FACCAB.CFCODMON', 'FACCAB.CFTEXGUIA', 'c.CNOMCLI', 'c.CCODCLI', 'c.CDIRCLI', DB::raw('SUM(ad.CAMPO004) as total_volumen'), DB::raw('SUM(ad.CAMPO005) as total_kg'), DB::raw('MAX(GREMISION_CAB.GREFECEMISION) as GREFECEMISION'),DB::raw('MAX(GREMISION_CAB.LLEGADAUBIGEO) as LLEGADAUBIGEO'),DB::raw('MAX(GREMISION_CAB.LLEGADADIRECCION) as LLEGADADIRECCION'),DB::raw('MAX(CATALOGO_13_UBIGEO.CODIGO) as CODIGO'),DB::raw('MAX(CATALOGO_13_UBIGEO.DEPARTAMENTO) as DEPARTAMENTO'),DB::raw('MAX(CATALOGO_13_UBIGEO.PROVINCIA) as PROVINCIA'),DB::raw('MAX(CATALOGO_13_UBIGEO.DISTRITO) as DISTRITO'))
                ->join('FACDET AS cd', function ($join) {
                    $join->on('cd.DFTD', '=', 'FACCAB.CFTD') // Condición 1
                    ->on('cd.DFNUMSER', '=', 'FACCAB.CFNUMSER') // Condición 2
                    ->on('cd.DFNUMDOC', '=', 'FACCAB.CFNUMDOC'); // Condición 3
                })
                ->join('MAEART AS a','a.ACODIGO' ,'=','cd.DFCODIGO') // Unión con articulos
                ->join('MAEART_ADICIONALES AS ad','ad.CAMPO000' ,'=','a.ACODIGO') // Unión con articulos detalles
                ->join('MAECLI AS c','c.CCODCLI' ,'=','FACCAB.CFCODCLI') // Unión con clientes
                ->leftJoin('GREMISION_CAB', function ($join) {
                    $join->on('GREMISION_CAB.GRENUMSER', '=', DB::raw('CASE WHEN LEN(FACCAB.CFTEXGUIA) >= 5 THEN SUBSTRING(FACCAB.CFTEXGUIA, 1, 4) ELSE NULL END'))
                        ->on('GREMISION_CAB.GRENUMDOC', '=', DB::raw('CASE WHEN LEN(FACCAB.CFTEXGUIA) >= 5 THEN SUBSTRING(FACCAB.CFTEXGUIA, 5, LEN(FACCAB.CFTEXGUIA) - 4) ELSE NULL END'));
                })->join('CATALOGO_13_UBIGEO', function ($join) {
                    $join->on('CATALOGO_13_UBIGEO.CODIGO', '=', 'GREMISION_CAB.LLEGADAUBIGEO')
                        ->whereNotNull('GREMISION_CAB.LLEGADAUBIGEO'); // Solo aplica el INNER JOIN si hay relación en GREMISION_CAB
                })
                ->where('FACCAB.CFESTADO','=','V')
                ->where('c.CESTADO','=','V')
                ->where(function ($q) use ($search) {
                    $q->where('c.CNOMCLI', 'like', '%' . $search . '%')
                        ->orWhere('c.CCODCLI', 'like', '%' . $search . '%')
                        ->orWhere('c.CDIRCLI', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFTD', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFNUMDOC', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFNUMSER', 'like', '%' . $search . '%');
                })
                ->whereBetween(DB::raw("ISNULL(TRY_CONVERT(DATE, GREMISION_CAB.GREFECEMISION), '1900-01-01')"), [$desde, $hasta])
                ->groupBy('FACCAB.CFTD', 'FACCAB.CFNUMSER', 'FACCAB.CFNUMDOC', 'FACCAB.CFIMPORTE', 'FACCAB.CFCODMON', 'FACCAB.CFTEXGUIA', 'c.CNOMCLI', 'c.CCODCLI', 'c.CDIRCLI')
                ->get();
            // Extraer los comprobantes en un formato fácil de consultar
            $comprobantes = $result->map(function ($item) {
                return [
                    'CFTD' => $item->CFTD,
                    'CFNUMSER' => $item->CFNUMSER,
                    'CFNUMDOC' => $item->CFNUMDOC,
                ];
            })->toArray();
            // Consulta a la base de datos del proyecto para obtener comprobantes ya existentes
            $comprobantesExistentes = DB::table('despacho_ventas')
                ->whereIn('despacho_venta_cftd', array_column($comprobantes, 'CFTD'))
                ->whereIn('despacho_venta_cfnumser', array_column($comprobantes, 'CFNUMSER'))
                ->whereIn('despacho_venta_cfnumdoc', array_column($comprobantes, 'CFNUMDOC'))
                ->select('despacho_venta_cftd', 'despacho_venta_cfnumser', 'despacho_venta_cfnumdoc')
                ->get()
                ->map(function ($item) {
                    return $item->despacho_venta_cftd . $item->despacho_venta_cfnumser . $item->despacho_venta_cfnumdoc;
                })
                ->toArray();

            // Filtrar comprobantes para eliminar los que ya existen
            $result = $result->filter(function ($item) use ($comprobantesExistentes) {
                $comprobanteKey = $item->CFTD . $item->CFNUMSER . $item->CFNUMDOC;
                return !in_array($comprobanteKey, $comprobantesExistentes);
            });

            foreach ($result as $re){
                $valornew = $re->total_kg / 1000;
                $re->total_kg = $valornew;
            }

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_clientes($search){
        try {
            // MAECLI - CLIENTES
            $result = DB::connection('sqlsrv_external')->table('MAECLI')
                ->where(function ($q) use ($search) {
                    $q->where('CNOMCLI', 'like', '%' . $search . '%')
                        ->orWhere('CCODCLI', 'like', '%' . $search . '%')
                        ->orWhere('CDIRCLI', 'like', '%' . $search . '%');
                })->where('CESTADO','=','V')->get();

            }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_comprobantes_por_cliente($codigo_cliente,$search,$desde, $hasta){
        try {
            // FACCAB - COMPROBANTES | MAECLI - CLIENTES | FACDET - COMPROBANTES DETALLE | MAEART - ARTICULOS | MAEART_ADICIONALES - detalles del articulos
            // filtrar nombre del cliente, serie, correlativo, pes y volumen
            $result = DB::connection('sqlsrv_external')
                ->table('FACCAB')
                ->select('FACCAB.CFTD', 'FACCAB.CFNUMSER', 'FACCAB.CFNUMDOC', 'FACCAB.CFIMPORTE', 'FACCAB.CFCODMON', 'FACCAB.CFTEXGUIA', DB::raw('SUM(ad.CAMPO004) as total_volumen'), DB::raw('SUM(ad.CAMPO005) as total_kg'), DB::raw('MAX(GREMISION_CAB.GREFECEMISION) as GREFECEMISION'),DB::raw('MAX(GREMISION_CAB.LLEGADAUBIGEO) as LLEGADAUBIGEO'),DB::raw('MAX(GREMISION_CAB.LLEGADADIRECCION) as LLEGADADIRECCION'),DB::raw('MAX(CATALOGO_13_UBIGEO.CODIGO) as CODIGO'),DB::raw('MAX(CATALOGO_13_UBIGEO.DEPARTAMENTO) as DEPARTAMENTO'),DB::raw('MAX(CATALOGO_13_UBIGEO.PROVINCIA) as PROVINCIA'),DB::raw('MAX(CATALOGO_13_UBIGEO.DISTRITO) as DISTRITO'))
                ->join('FACDET AS cd', function ($join) {
                    $join->on('cd.DFTD', '=', 'FACCAB.CFTD') // Condición 1
                    ->on('cd.DFNUMSER', '=', 'FACCAB.CFNUMSER') // Condición 2
                    ->on('cd.DFNUMDOC', '=', 'FACCAB.CFNUMDOC'); // Condición 3
                })
                ->join('MAEART AS a','a.ACODIGO' ,'=','cd.DFCODIGO') // Unión con articulos
                ->join('MAEART_ADICIONALES AS ad','ad.CAMPO000' ,'=','a.ACODIGO') // Unión con articulos detalles
                ->leftJoin('GREMISION_CAB', function ($join) {
                    $join->on('GREMISION_CAB.GRENUMSER', '=', DB::raw('CASE WHEN LEN(FACCAB.CFTEXGUIA) >= 5 THEN SUBSTRING(FACCAB.CFTEXGUIA, 1, 4) ELSE NULL END'))
                        ->on('GREMISION_CAB.GRENUMDOC', '=', DB::raw('CASE WHEN LEN(FACCAB.CFTEXGUIA) >= 5 THEN SUBSTRING(FACCAB.CFTEXGUIA, 5, LEN(FACCAB.CFTEXGUIA) - 4) ELSE NULL END'));
                })->join('CATALOGO_13_UBIGEO', function ($join) {
                    $join->on('CATALOGO_13_UBIGEO.CODIGO', '=', 'GREMISION_CAB.LLEGADAUBIGEO')
                        ->whereNotNull('GREMISION_CAB.LLEGADAUBIGEO'); // Solo aplica el INNER JOIN si hay relación en GREMISION_CAB
                })
                ->where('FACCAB.CFESTADO','=','V')
//                ->where('c.CESTADO','=','V')
                ->where('FACCAB.CFCODCLI','=',$codigo_cliente)
                ->where(function ($q) use ($search) {
                    $q->Where('FACCAB.CFTD', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFNUMDOC', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFNUMSER', 'like', '%' . $search . '%');
                })
                ->whereBetween(DB::raw("ISNULL(TRY_CONVERT(DATE, GREMISION_CAB.GREFECEMISION), '1900-01-01')"), [$desde, $hasta])
                ->groupBy('FACCAB.CFTD', 'FACCAB.CFNUMSER', 'FACCAB.CFNUMDOC', 'FACCAB.CFIMPORTE', 'FACCAB.CFCODMON', 'FACCAB.CFTEXGUIA')
                ->get();
            // Extraer los comprobantes en un formato fácil de consultar
            $comprobantes = $result->map(function ($item) {
                return [
                    'CFTD' => $item->CFTD,
                    'CFNUMSER' => $item->CFNUMSER,
                    'CFNUMDOC' => $item->CFNUMDOC,
                ];
            })->toArray();
            // Consulta a la base de datos del proyecto para obtener comprobantes ya existentes
            $comprobantesExistentes = DB::table('despacho_ventas')
                ->whereIn('despacho_venta_cftd', array_column($comprobantes, 'CFTD'))
                ->whereIn('despacho_venta_cfnumser', array_column($comprobantes, 'CFNUMSER'))
                ->whereIn('despacho_venta_cfnumdoc', array_column($comprobantes, 'CFNUMDOC'))
                ->select('despacho_venta_cftd', 'despacho_venta_cfnumser', 'despacho_venta_cfnumdoc')
                ->get()
                ->map(function ($item) {
                    return $item->despacho_venta_cftd . $item->despacho_venta_cfnumser . $item->despacho_venta_cfnumdoc;
                })
                ->toArray();

            // Filtrar comprobantes para eliminar los que ya existen
            $result = $result->filter(function ($item) use ($comprobantesExistentes) {
                $comprobanteKey = $item->CFTD . $item->CFNUMSER . $item->CFNUMDOC;
                return !in_array($comprobanteKey, $comprobantesExistentes);
            });

            foreach ($result as $re){
                $valornew = $re->total_kg / 1000;
                $re->total_kg = $valornew;
            }

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_comprobantes_listos_mixto($search){
        try {
            // FACCAB - COMPROBANTES | MAECLI - CLIENTES | FACDET - COMPROBANTES DETALLE | MAEART - ARTICULOS | MAEART_ADICIONALES - detalles del articulos
            // filtrar nombre del cliente, serie, correlativo, pes y volumen
            $result = DB::connection('sqlsrv_external')
                ->table('FACCAB')
                ->select('FACCAB.CFTD', 'FACCAB.CFNUMSER', 'FACCAB.CFNUMDOC', 'FACCAB.CFIMPORTE', 'FACCAB.CFCODMON', 'FACCAB.CFTEXGUIA', 'c.CNOMCLI','c.CCODCLI','c.CDIRCLI', DB::raw('SUM(ad.CAMPO004) as total_volumen') ,DB::raw('SUM(ad.CAMPO005) as total_kg'))
                ->join('FACDET AS cd', function ($join) {
                    $join->on('cd.DFTD', '=', 'FACCAB.CFTD') // Condición 1
                    ->on('cd.DFNUMSER', '=', 'FACCAB.CFNUMSER') // Condición 2
                    ->on('cd.DFNUMDOC', '=', 'FACCAB.CFNUMDOC'); // Condición 3
                })
                ->join('MAEART AS a','a.ACODIGO' ,'=','cd.DFCODIGO') // Unión con articulos
                ->join('MAEART_ADICIONALES AS ad','ad.CAMPO000' ,'=','a.ACODIGO') // Unión con articulos detalles
                ->join('MAECLI AS c','c.CCODCLI' ,'=','FACCAB.CFCODCLI') // Unión con clientes
                ->where('FACCAB.CFESTADO','=','V')
                ->where('c.CESTADO','=','V')
                ->where(function ($q) use ($search) {
                    $q->where('c.CNOMCLI', 'like', '%' . $search . '%')
                        ->orWhere('c.CCODCLI', 'like', '%' . $search . '%')
                        ->orWhere('c.CDIRCLI', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFTD', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFNUMDOC', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFNUMSER', 'like', '%' . $search . '%');
                })
                ->groupBy('FACCAB.CFTD', 'FACCAB.CFNUMSER', 'FACCAB.CFNUMDOC','FACCAB.CFIMPORTE','FACCAB.CFCODMON','FACCAB.CFTEXGUIA','c.CNOMCLI','c.CCODCLI','c.CDIRCLI')
                ->limit(10)->get();
            foreach ($result as $re){
                $valornew = $re->total_kg / 1000;
                $re->total_kg = $valornew;
                /* -------------------------------------- */
                $re->show = 1;
                // validar comprobante existe
                $validar =  DB::table('despacho_ventas')->where([['despacho_venta_cftd','=',$re->CFTD], ['despacho_venta_cfnumser','=',$re->CFNUMSER], ['despacho_venta_cfnumdoc','=',$re->CFNUMDOC],])->first();
                if ($validar){ // si es que el comprobante ya hay en la tabla despacho_ventas significa que ya fue usado.
                    $re->show = 0;
                }
                $code = $re->CFTEXGUIA;
                // Extraer las primeras 4 letras
                $serie = substr($code, 0, 4);
                // Extraer el resto de la cadena
                $resto = substr($code, 4);
                $re->guia =  DB::connection('sqlsrv_external')
                    ->table('GREMISION_CAB')
                    ->select('GREFECEMISION','LLEGADAUBIGEO','LLEGADADIRECCION')
                    ->where('GRENUMSER','=',$serie)
                    ->where('GRENUMDOC','=',$resto)
                    ->first();
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

}
