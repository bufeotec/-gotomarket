<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
class  Server extends Model
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
//            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_local_receipts";
            $url = "http://161.132.73.129:8081/api_goto/public/api/v1/list_local_receipts";
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
//        $url = "http://127.0.0.1/api_goto/public/api/v1/list_local_receipts";
//            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_customers";
            $url = "http://161.132.73.129:8081/api_goto/public/api/v1/list_customers";

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
//            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_customer_receipts";
            $url = "http://161.132.73.129:8081/api_goto/public/api/v1/list_customer_receipts";

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

    public function obtenerDocumentosRemision($desde,$hasta){
        try {
            $result = array();
            $client = new \GuzzleHttp\Client();
//            $url = "http://127.0.0.1/api_goto/public/api/v1/list_r_documents";
//            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_r_documents";
            $url = "http://161.132.73.129:8081/api_goto/public/api/v1/list_r_documents";


            $response = $client->post($url, [
                'form_params' => [
                    'desde' => $desde,
                    'hasta' => $hasta,
                ],
            ]);
//            // Enviar solicitud GET sin parámetros
//            $response = $client->post($url);

            // Procesar la respuesta
            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);

            if ($responseData->code === 200){
                $result = collect($responseData->data);

                if (count($result) > 0){
                    // Iteramos sobre el resultado
                    foreach ($result as $key => $re) {
                        //Con esto verifico si la guia ya existe
                        $validarExistencia = DB::table('guias')
                            ->where('guia_nro_doc','=', $re->NRO_DOC)
                            ->orderBy('id_guia','desc')
                            ->exists();

                        //Ahora falta verificar si es antigua, pero debo buscar por factura en la tabla despacho ventas
                        $nroDocRefFormateado = substr($re->NRO_DOC_REF, 0, 4) . '-' . substr($re->NRO_DOC_REF, 4);
                        $antigua = DB::table('despacho_ventas')
                            ->whereNull('id_guia')  // Más claro que ->where('id_guia', '=', null)
                            ->where('despacho_venta_factura', $nroDocRefFormateado)
                            ->whereNotIn('despacho_detalle_estado_entrega', [0, 1, 2])
                            ->exists();


                        // Si existe, eliminamos el registro de $result
                        if ($validarExistencia || $antigua) {
                            unset($result[$key]); // Elimina el elemento del array
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }
    public function obtenervendedores(){
        try {
            $result = array();
            $client = new \GuzzleHttp\Client();
//            $url = "http://127.0.0.1/api_goto/public/api/v1/list_vendedores";
//            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_vendedores";
            $url = "http://161.132.73.129:8081/api_goto/public/api/v1/list_vendedores";


            /*$response = $client->post($url, [
                'form_params' => [
                    'desde' => $desde,
                    'hasta' => $hasta,
                ],
            ]);*/
            $response = $client->post($url);

            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);

            if ($responseData->code === 200){
                $result = collect($responseData->data);
            }

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }
    public function obtenerDetalleRemision($num_doc){
        try {
            $result = array();
            $client = new \GuzzleHttp\Client();
//            $url = "http://127.0.0.1/api_goto/public/api/v1/list_detalles_r_documents";
//            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_detalles_r_documents";
            $url = "http://161.132.73.129:8081/api_goto/public/api/v1/list_detalles_r_documents";

            $response = $client->post($url, [
                'form_params' => [
                    'num_doc' => $num_doc,
                ],
            ]);
//            // Enviar solicitud GET sin parámetros
//            $response = $client->post($url);
            //HOLA

            // Procesar la respuesta
            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);


            if ($responseData->code === 200){
                $result = collect($responseData->data);
            }

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }

    public function listar_guia_guardada($num_doc){
        try {
            $result = DB::table('guias')
                ->where('guia_nro_doc','=',$num_doc)
                ->first();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }

    public function obtenerGuia_x_numdoc($num_doc) {
        try {
            $client = new \GuzzleHttp\Client();
//            $url = "http://127.0.0.1/api_goto/public/api/v1/list_info_guia";
//            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_info_guia";
            $url = "http://161.132.73.129:8081/api_goto/public/api/v1/list_info_guia";


            $response = $client->post($url, [
                'form_params' => ['num_doc' => $num_doc],
            ]);

            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);

            if ($responseData->code !== 200 || !is_object($responseData->data)) {
                return collect(); // Devuelve colección vacía si no es el formato esperado
            }

            // Asegurar que el objeto tenga la propiedad ESTADO
            if (!property_exists($responseData->data, 'ESTADO')) {
                $responseData->data->ESTADO = null;
            }

            return collect([$responseData->data]);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return collect();
        }
    }
    public function listar_notas_credito_ss($desde, $hasta){
        try {
            $result = array();
            $client = new \GuzzleHttp\Client();
//            $url = "http://127.0.0.1/api_goto/public/api/v1/list_nc_ss";
//            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_nc_ss";
            $url = "http://161.132.73.129:8081/api_goto/public/api/v1/list_nc_ss";


//            $response = $client->post($url, [
//                'form_params' => [
////                    'desde' => $desde,
////                    'hasta' => $hasta,
//                ],
//            ]);
//            // Enviar solicitud GET sin parámetros

            $response = $client->post($url, [
                'form_params' => [
                    'desde' => $desde,
                    'hasta' => $hasta,
                ],
            ]);

            // Procesar la respuesta
            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);

            if ($responseData->code === 200){
                $result = collect($responseData->data);

                if (count($result) > 0){
                    // Iteramos sobre el resultado
                    foreach ($result as $key => $re) {
                        // Verificamos si existe el despacho en la tabla 'despacho_ventas'
                        $validarExistencia = DB::table('notas_creditos')
                            ->where('not_cred_nro_doc', $re->NRO_DOCUMENTO)
                            ->orderBy('id_not_cred','desc')
                            ->exists();

                        // Si existe, eliminamos el registro de $result
                        if ($validarExistencia) {
                            unset($result[$key]); // Elimina el elemento del array
                        }
                    }
                }

            }

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }
    public function listar_notas_credito_detalle_ss($num_doc){
        try {
            $result = array();
            $client = new \GuzzleHttp\Client();
//            $url = "http://127.0.0.1/api_goto/public/api/v1/list_detalle_nc_ss";
//            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_detalle_nc_ss";
            $url = "http://161.132.73.129:8081/api_goto/public/api/v1/list_detalle_nc_ss";
//            // Enviar solicitud GET sin parámetros

            $response = $client->post($url, [
                'form_params' => [
                    'num_doc' => $num_doc,
                ],
            ]);

            // Procesar la respuesta
            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);

            if ($responseData->code === 200){
                $result = collect($responseData->data);

            }

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }
    public function obtenerNCxNumDoc($num_doc){
        try {
            $result = array();
            $client = new \GuzzleHttp\Client();
//            $url = "http://127.0.0.1/api_goto/public/api/v1/list_nc_x_numdoc";
//            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_nc_x_numdoc";
            $url = "http://161.132.73.129:8081/api_goto/public/api/v1/list_nc_x_numdoc";
//            // Enviar solicitud GET sin parámetros

            $response = $client->post($url, [
                'form_params' => ['num_doc' => $num_doc],
            ]);

            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);

            if ($responseData->code !== 200 || !is_object($responseData->data)) {
                return collect(); // Devuelve colección vacía si no es el formato esperado
            }

            // Asegurar que el objeto tenga la propiedad ESTADO
            if (!property_exists($responseData->data, 'ESTADO')) {
                $responseData->data->ESTADO = null;
            }

            return collect([$responseData->data]);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return collect();
        }
    }
}
