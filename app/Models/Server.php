<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
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
            $result = array();

            $client = new \GuzzleHttp\Client();
//            $url = "http://127.0.0.1/api_goto/public/api/v1/list_local_receipts";
            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_local_receipts";
            // Enviar la solicitud POST con los parámetros proporcionados
            $response = $client->post($url, [
                'form_params' => [
                    'buscar' => $search,
                    'desde' => $desde,
                    'hasta' => $hasta,
                ],
            ]);

            // Procesar la respuesta
            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);

            if ($responseData->code === 200) {
//                $result = $responseData->data;
                $result = collect($responseData->data);

                if (count($result) > 0){
                    // Iteramos sobre el resultado
                    foreach ($result as $key => $re) {
                        // Verificamos si existe el despacho en la tabla 'despacho_ventas'
                        $validarExistencia = DB::table('despacho_ventas as dv')
                            ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
                            ->where('d.despacho_estado_aprobacion','<>',4)
                            ->where('dv.despacho_venta_cftd', $re->CFTD)
                            ->where('dv.despacho_venta_cfnumser', $re->CFNUMSER)
                            ->where('dv.despacho_venta_cfnumdoc', $re->CFNUMDOC)
                            ->whereIn('dv.despacho_detalle_estado_entrega', [0,1,2])
                            ->orderBy('dv.id_despacho_venta','desc')
                            ->exists();

                        // Si existe, eliminamos el registro de $result
                        if ($validarExistencia) {
                            unset($result[$key]); // Elimina el elemento del array
                        }
                    }
                    foreach ($result as $re){
                        $valornew = $re->total_kg / 1000;
                        $re->total_kg = $valornew;
                    }
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
            $result =  array();
            $client = new \GuzzleHttp\Client();
//            $url = "http://127.0.0.1/api_goto/public/api/v1/list_local_receipts";
            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_customers";

            // Enviar la solicitud POST con los parámetros proporcionados
            $response = $client->post($url, [
                'form_params' => [
                    'buscar' => $search,
                ],
            ]);

            // Procesar la respuesta
            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);
            if ($responseData->code === 200){

//                $result = $responseData->data;
                $result = collect($responseData->data);

            }

            }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_comprobantes_por_cliente($codigo_cliente,$search,$desde, $hasta){
        try {
            $result =  array();
            $client = new \GuzzleHttp\Client();
//            $url = "http://127.0.0.1/api_goto/public/api/v1/list_local_receipts";

            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_customer_receipts";

            // Enviar la solicitud POST con los parámetros proporcionados
            $response = $client->post($url, [
                'form_params' => [
                    'code' => $codigo_cliente,
                    'buscar' => $search,
                    'desde' => $desde,
                    'hasta' => $hasta,
                ],
            ]);

            // Procesar la respuesta
            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);
            if ($responseData->code === 200){
//                $result = $responseData->data;
                $result = collect($responseData->data);

                if (count($result) > 0){
                    foreach ($result as $key => $re) {
                        // Verificamos si existe el despacho en la tabla 'despacho_ventas'
                        $validarExistencia = DB::table('despacho_ventas as dv')
                            ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
                            ->where('d.despacho_estado_aprobacion','<>',4)
                            ->where('dv.despacho_venta_cftd', $re->CFTD)
                            ->where('dv.despacho_venta_cfnumser', $re->CFNUMSER)
                            ->where('dv.despacho_venta_cfnumdoc', $re->CFNUMDOC)
                            ->whereIn('dv.despacho_detalle_estado_entrega', [0,1,2])
                            ->orderBy('dv.id_despacho_venta','desc')
                            ->exists();

                        // Si existe, eliminamos el registro de $result
                        if ($validarExistencia) {
                            unset($result[$key]); // Elimina el elemento del array
                        }
                    }
                    foreach ($result as $re){
                        $valornew = $re->total_kg / 1000;
                        $re->total_kg = $valornew;
                    }

                }
            }


        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
