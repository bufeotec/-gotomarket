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
    public function prueba(){
        try {
            // MAECLI - CLIENTES
            $result = DB::connection('sqlsrv_external')->table('FACCAB')
                ->where('CFESTADO','=','V')->where('CFNUMSER','like','%F%')->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_comprobantes_listos_local($search,$desde,$hasta){
        try {
            // FACCAB - COMPROBANTES | MAECLI - CLIENTES | FACDET - COMPROBANTES DETALLE | MAEART - ARTICULOS | MAEART_ADICIONALES - detalles del articulos
            $subquery = DB::connection('sqlsrv_external')
            ->table('FACCAB')
            ->select(
                'CFTD',
                'CFNUMSER',
                'CFNUMDOC',
                'CFIMPORTE',
                'CFCODMON',
                'CFTEXGUIA',
                'CFCODCLI',
                'CFESTADO',
                'CFFECDOC',
                DB::raw("CASE WHEN LEN(CFTEXGUIA) >= 5 THEN SUBSTRING(CFTEXGUIA, 1, 4) ELSE NULL END AS GRENUMSER"),
                DB::raw("CASE WHEN LEN(CFTEXGUIA) >= 5 THEN SUBSTRING(CFTEXGUIA, 5, LEN(CFTEXGUIA) - 4) ELSE NULL END AS GRENUMDOC")
            );

            $result = DB::connection('sqlsrv_external')
            ->table(DB::raw("({$subquery->toSql()}) as FACCAB")) // Convertimos la subconsulta en tabla derivada
            ->mergeBindings($subquery) // Vinculamos las variables de la subconsulta
            ->select(
                'FACCAB.CFTD',
                'FACCAB.CFNUMSER',
                'FACCAB.CFNUMDOC',
                'FACCAB.CFIMPORTE',
                'FACCAB.CFCODMON',
                'FACCAB.CFTEXGUIA',
                'FACCAB.CFFECDOC as GREFECEMISION',
                'c.CNOMCLI',
                'c.CCODCLI',
                'c.CDIRCLI',
                DB::raw('MAX(FACCAB.CFESTADO) as CFESTADO'),// campo nuevo
                DB::raw('MAX(c.CESTADO) as CESTADO'), // campo nuevo
                DB::raw('SUM(ad.CAMPO004) as total_volumen'),
                DB::raw('SUM(ad.CAMPO005) as total_kg'),
                DB::raw('MAX(GREMISION_CAB.LLEGADAUBIGEO) as LLEGADAUBIGEO'),
                DB::raw('MAX(GREMISION_CAB.LLEGADADIRECCION) as LLEGADADIRECCION'),
                DB::raw('MAX(CATALOGO_13_UBIGEO.CODIGO) as CODIGO'),
                DB::raw('MAX(CATALOGO_13_UBIGEO.DEPARTAMENTO) as DEPARTAMENTO'),
                DB::raw('MAX(CATALOGO_13_UBIGEO.PROVINCIA) as PROVINCIA'),
                DB::raw('MAX(CATALOGO_13_UBIGEO.DISTRITO) as DISTRITO')
            )
            ->join('FACDET AS cd', function ($join) {
                $join->on('cd.DFTD', '=', 'FACCAB.CFTD') // Condición 1
                ->on('cd.DFNUMSER', '=', 'FACCAB.CFNUMSER') // Condición 2
                ->on('cd.DFNUMDOC', '=', 'FACCAB.CFNUMDOC'); // Condición 3
            })
            ->join('MAEART AS a', 'a.ACODIGO', '=', 'cd.DFCODIGO') // Unión con artículos
            ->join('MAEART_ADICIONALES AS ad', 'ad.CAMPO000', '=', 'a.ACODIGO') // Unión con detalles de artículos
            ->join('MAECLI AS c', 'c.CCODCLI', '=', 'FACCAB.CFCODCLI') // Unión con clientes
            ->leftJoin('GREMISION_CAB', function ($join) {
                $join->on('GREMISION_CAB.GRENUMSER', '=', 'FACCAB.GRENUMSER')
                    ->on('GREMISION_CAB.GRENUMDOC', '=', 'FACCAB.GRENUMDOC');
            })
            ->join('CATALOGO_13_UBIGEO', function ($join) {
                $join->on('CATALOGO_13_UBIGEO.CODIGO', '=', 'GREMISION_CAB.LLEGADAUBIGEO')
                    ->whereNotNull('GREMISION_CAB.LLEGADAUBIGEO'); // Solo aplica el INNER JOIN si hay relación en GREMISION_CAB
            })->where('FACCAB.CFTD','=','FT')
            ->where('FACCAB.CFESTADO','=','V')
            ->where('c.CESTADO', '=', 'V');
            if ($search){
                $result->where(function ($q) use ($search) {
                    $q->where('c.CNOMCLI', 'like', '%' . $search . '%')
                        ->orWhere('c.CCODCLI', 'like', '%' . $search . '%')
                        ->orWhere('c.CDIRCLI', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFTD', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFNUMDOC', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFNUMSER', 'like', '%' . $search . '%');
                });
            }
            // Filtro por rango de fechas
            if ($desde && $hasta) {
                $result->whereBetween(DB::raw('CONVERT(DATE, FACCAB.CFFECDOC)'), [$desde, $hasta]);
            }
            $result->groupBy(
                'FACCAB.CFTD',
                'FACCAB.CFNUMSER',
                'FACCAB.CFNUMDOC',
                'FACCAB.CFIMPORTE',
                'FACCAB.CFCODMON',
                'FACCAB.CFTEXGUIA',
                'c.CNOMCLI',
                'c.CCODCLI',
                'c.CDIRCLI',
                'FACCAB.CFFECDOC' // Asegúrate de incluirlo aquí
            );

            $result = $result->limit(50)->get();
            if (count($result) > 0){
                // Extraer los comprobantes en un formato fácil de consultar
                $comprobantes = $result->map(function ($item) {
                    return [
                        'CFTD' => $item->CFTD,
                        'CFNUMSER' => $item->CFNUMSER,
                        'CFNUMDOC' => $item->CFNUMDOC,
                    ];
                })->toArray();
                // Consulta a la base de datos del proyecto para obtener comprobantes ya existentes
                $comprobantesExistentes = DB::table('despacho_ventas as dv')
                    ->select(
                        'dv.despacho_venta_cftd',
                        'dv.despacho_venta_cfnumser',
                        'dv.despacho_venta_cfnumdoc'
                    )
                    ->whereIn('dv.despacho_venta_cftd', array_column($comprobantes, 'CFTD'))
                    ->whereIn('dv.despacho_venta_cfnumser', array_column($comprobantes, 'CFNUMSER'))
                    ->whereIn('dv.despacho_venta_cfnumdoc', array_column($comprobantes, 'CFNUMDOC'))
                    ->whereIn('dv.despacho_detalle_estado_entrega', [0, 1, 2])
                    ->whereRaw('
                            dv.despacho_detalle_estado_entrega = (
                                SELECT MAX(sub.despacho_detalle_estado_entrega)
                                FROM despacho_ventas AS sub
                                WHERE sub.despacho_venta_cftd = dv.despacho_venta_cftd
                                  AND sub.despacho_venta_cfnumser = dv.despacho_venta_cfnumser
                                  AND sub.despacho_venta_cfnumdoc = dv.despacho_venta_cfnumdoc
                            )
                        ')
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
            $subquery = DB::connection('sqlsrv_external')
                ->table('FACCAB')
                ->select(
                    'CFTD',
                    'CFNUMSER',
                    'CFNUMDOC',
                    'CFIMPORTE',
                    'CFCODMON',
                    'CFTEXGUIA',
                    'CFCODCLI',
                    'CFESTADO',
                    'CFFECDOC',
                    DB::raw("CASE WHEN LEN(CFTEXGUIA) >= 5 THEN SUBSTRING(CFTEXGUIA, 1, 4) ELSE NULL END AS GRENUMSER"),
                    DB::raw("CASE WHEN LEN(CFTEXGUIA) >= 5 THEN SUBSTRING(CFTEXGUIA, 5, LEN(CFTEXGUIA) - 4) ELSE NULL END AS GRENUMDOC")
                );

            $result = DB::connection('sqlsrv_external')
                ->table(DB::raw("({$subquery->toSql()}) as FACCAB")) // Convertimos la subconsulta en tabla derivada
                ->mergeBindings($subquery) // Vinculamos las variables de la subconsulta
                ->select(
                    'FACCAB.CFTD',
                    'FACCAB.CFNUMSER',
                    'FACCAB.CFNUMDOC',
                    'FACCAB.CFIMPORTE',
                    'FACCAB.CFCODMON',
                    'FACCAB.CFTEXGUIA',
                    'FACCAB.CFFECDOC as GREFECEMISION',
                    DB::raw('MAX(FACCAB.CFESTADO) as CFESTADO'),// campo nuevo
                    DB::raw('SUM(ad.CAMPO004) as total_volumen'),
                    DB::raw('SUM(ad.CAMPO005) as total_kg'),
//                    DB::raw('MAX(GREMISION_CAB.GREFECEMISION) as GREFECEMISION'),
                    DB::raw('MAX(GREMISION_CAB.LLEGADAUBIGEO) as LLEGADAUBIGEO'),
                    DB::raw('MAX(GREMISION_CAB.LLEGADADIRECCION) as LLEGADADIRECCION'),
                    DB::raw('MAX(CATALOGO_13_UBIGEO.CODIGO) as CODIGO'),
                    DB::raw('MAX(CATALOGO_13_UBIGEO.DEPARTAMENTO) as DEPARTAMENTO'),
                    DB::raw('MAX(CATALOGO_13_UBIGEO.PROVINCIA) as PROVINCIA'),
                    DB::raw('MAX(CATALOGO_13_UBIGEO.DISTRITO) as DISTRITO')
                )
                ->join('FACDET AS cd', function ($join) {
                    $join->on('cd.DFTD', '=', 'FACCAB.CFTD') // Condición 1
                    ->on('cd.DFNUMSER', '=', 'FACCAB.CFNUMSER') // Condición 2
                    ->on('cd.DFNUMDOC', '=', 'FACCAB.CFNUMDOC'); // Condición 3
                })
                ->join('MAEART AS a', 'a.ACODIGO', '=', 'cd.DFCODIGO') // Unión con artículos
                ->join('MAEART_ADICIONALES AS ad', 'ad.CAMPO000', '=', 'a.ACODIGO') // Unión con detalles de artículos
                ->leftJoin('GREMISION_CAB', function ($join) {
                    $join->on('GREMISION_CAB.GRENUMSER', '=', 'FACCAB.GRENUMSER')
                        ->on('GREMISION_CAB.GRENUMDOC', '=', 'FACCAB.GRENUMDOC');
                })
                ->join('CATALOGO_13_UBIGEO', function ($join) {
                    $join->on('CATALOGO_13_UBIGEO.CODIGO', '=', 'GREMISION_CAB.LLEGADAUBIGEO')
                        ->whereNotNull('GREMISION_CAB.LLEGADAUBIGEO'); // Solo aplica el INNER JOIN si hay relación en GREMISION_CAB
                })
                ->where('FACCAB.CFCODCLI','=',$codigo_cliente)
                ->where('FACCAB.CFTD','=','FT')
                ->where('FACCAB.CFESTADO','=','V')
                ;
            if ($search){
                $result->where(function ($q) use ($search) {
                    $q->where('FACCAB.CFTD', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFNUMDOC', 'like', '%' . $search . '%')
                        ->orWhere('FACCAB.CFNUMSER', 'like', '%' . $search . '%');
                });
            }
            if ($desde && $hasta) {
                $result->whereBetween(DB::raw('CONVERT(DATE, FACCAB.CFFECDOC)'), [$desde, $hasta]);
            }
            $result->groupBy(
                'FACCAB.CFTD',
                'FACCAB.CFNUMSER',
                'FACCAB.CFNUMDOC',
                'FACCAB.CFIMPORTE',
                'FACCAB.CFCODMON',
                'FACCAB.CFTEXGUIA',
                'FACCAB.CFFECDOC' // Asegúrate de incluirlo aquí
            );
            $result = $result->limit(50)->get();

            if (count($result) > 0){
                $comprobantes = $result->map(function ($item) {
                    return [
                        'CFTD' => $item->CFTD,
                        'CFNUMSER' => $item->CFNUMSER,
                        'CFNUMDOC' => $item->CFNUMDOC,
                    ];
                })->toArray();
                // Consulta a la base de datos del proyecto para obtener comprobantes ya existentes
                $comprobantesExistentes = DB::table('despacho_ventas as dv')
                    ->select(
                        'dv.despacho_venta_cftd',
                        'dv.despacho_venta_cfnumser',
                        'dv.despacho_venta_cfnumdoc'
                    )
                    ->whereIn('dv.despacho_venta_cftd', array_column($comprobantes, 'CFTD'))
                    ->whereIn('dv.despacho_venta_cfnumser', array_column($comprobantes, 'CFNUMSER'))
                    ->whereIn('dv.despacho_venta_cfnumdoc', array_column($comprobantes, 'CFNUMDOC'))
                    ->whereIn('dv.despacho_detalle_estado_entrega', [0, 1, 2])
                    ->whereRaw('
                            dv.despacho_detalle_estado_entrega = (
                                SELECT MAX(sub.despacho_detalle_estado_entrega)
                                FROM despacho_ventas AS sub
                                WHERE sub.despacho_venta_cftd = dv.despacho_venta_cftd
                                  AND sub.despacho_venta_cfnumser = dv.despacho_venta_cfnumser
                                  AND sub.despacho_venta_cfnumdoc = dv.despacho_venta_cfnumdoc
                            )
                        ')
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
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
