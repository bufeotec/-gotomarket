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
    public function listar_comprobantes_listos_local($search){
        try {
            // FACCAB - COMPROBANTES | MAECLI - CLIENTES | FACDET - COMPROBANTES DETALLE | MAEART - ARTICULOS | MAEART_ADICIONALES - detalles del articulos
            // filtrar nombre del cliente, serie, correlativo, pes y volumen
            $result = DB::connection('sqlsrv_external')
                ->table('FACCAB')
                ->select('FACCAB.CFTD', 'FACCAB.CFNUMSER', 'FACCAB.CFNUMDOC', 'c.CNOMCLI','c.CCODCLI','c.CDIRCLI', DB::raw('SUM(ad.CAMPO004) as total_volumen') ,DB::raw('SUM(ad.CAMPO005) as total_kg'))
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
                ->groupBy('FACCAB.CFTD', 'FACCAB.CFNUMSER', 'FACCAB.CFNUMDOC','c.CNOMCLI','c.CCODCLI','c.CDIRCLI')
                ->limit(15)->get();
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
            }
            }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}