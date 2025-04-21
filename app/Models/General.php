<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class General extends Model
{
    use HasFactory;
    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
    public function listar_departamento_zona(){
        $departamentos = [
            ['CALLAO','LIMA'], // LOCAL
            ['ANCASH', 'AYACUCHO', 'HUANCAVELICA', 'HUANUCO', 'JUNIN', 'LA LIBERTAD', 'LAMBAYEQUE', 'PASCO', 'ICA'], // PROVINCIA 1
            ['AMAZONAS','APURIMAC','AREQUIPA','CAJAMARCA','CUSCO','LORETO','MADRE DE DIOS','MOQUEGUA', 'SAN MARTIN', 'TACNA', 'UCAYALI', 'TUMBES', 'PIURA', 'PUNO'] // PROVINCIA 2
        ];
        return $departamentos;
    }

    public function consultar_documento($tipo,$num){
        try {
            $tipoDocumento = $tipo;
            $valorNum = $num;

            $respuesta = [
                'mensaje' => '',
                'tipo' => 'info',
                'nombre' => '',
                'direccion' => '',
                'condicion' => ''
            ];

            $name = "";
            $direccion = "";
            $estado = "";
            $mensaje = "";
            $tipo_mensaje = "";
            if ($tipoDocumento == 4) { // RUC
                if (strlen($valorNum) == 11 && is_numeric($valorNum)) {
                    if ($valorNum == "00000000000") {
                        $respuesta['mensaje'] = 'Proveedor Extranjero';
                        $respuesta['tipo'] = 'success';
                        $respuesta['condicion'] = 'HABIDO';
                    } else {
                        $url = "https://api.migo.pe/api/v1/ruc";
                        $token = "uTZu2aTvMPpqWFuzKATPRWNujUUe7Re1scFlRsTy9Q15k1sjdJVAc9WGy57m";

                        $data = [
                            'token' => $token,
                            'ruc' => $valorNum
                        ];

                        $options = [
                            'http' => [
                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method'  => 'POST',
                                'content' => http_build_query($data),
                            ],
                        ];

                        $context = stream_context_create($options);
                        $result = file_get_contents($url, false, $context);

                        if ($result === FALSE) {
                            $respuesta['mensaje'] = 'Error en la consulta.';
                            $respuesta['tipo'] = 'danger';
                        } else {
                            $data = json_decode($result, true);

                            if ($data['success']) {
                                $respuesta['mensaje'] = 'Datos Encontrados';
                                $respuesta['tipo'] = 'success';

                                $name = $data['nombre_o_razon_social'];
                                $direccion  = $data['direccion'];
                                $estado = $data['condicion_de_domicilio'];
//                                $respuesta['condicion'] = $data['condicion_de_domicilio'];

                                if ($data['condicion_de_domicilio'] == "NO HABIDO") {
                                    $respuesta['mensaje'] = 'Este RUC se encuentra como NO HABIDO.';
                                    $respuesta['tipo'] = 'danger';
                                }

                            } else {
                                $respuesta['mensaje'] = $data['message'];
                                $respuesta['tipo'] = 'danger';
                            }
                        }
                    }
                } else {
                    $respuesta['mensaje'] = 'El RUC debe contener 11 dígitos.';
                    $respuesta['tipo'] = 'danger';

                }
            } else {
                if (strlen($valorNum) == 8 && is_numeric($valorNum)) {
                    if ($valorNum == "00000000") {

                        $respuesta['mensaje'] = 'CLIENTE GENERAL';
                        $respuesta['tipo'] = 'success';
                        $respuesta['condicion'] = 'HABIDO';

                    } else {

                        $url = "https://api.migo.pe/api/v1/dni";
                        $token = "A5UB9oaNM7VPs4NgZsPfZXu9SAzxmPI5Yyvzo5B5b5i2NQn5KruzvMXus4Ma";

                        $data = [
                            'token' => $token,
                            'dni' => $valorNum
                        ];

                        $options = [
                            'http' => [
                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method'  => 'POST',
                                'content' => http_build_query($data),
                            ],
                        ];

                        $context = stream_context_create($options);
                        $result = file_get_contents($url, false, $context);

                        if ($result === FALSE) {

                            $respuesta['mensaje'] = 'Error en la consulta.';
                            $respuesta['tipo'] = 'error';

                        } else {

                            $data = json_decode($result, true);

                            if ($data['success']) {

                                $respuesta['mensaje'] = 'Datos Encontrados';
                                $respuesta['tipo'] = 'success';

                                $name = $data['nombre'];
                                $estado = 'HABIDO';

//                                $this->num_document = $data['dni'];
//                                $this->status_clienteDNI = false;
                            } else {
                                $respuesta['mensaje'] = $data['message'];
                                $respuesta['tipo'] = 'danger';
                            }
                        }
                    }
                } else {
                    $respuesta['mensaje'] = 'El DNI debe contener 8 dígitos.';
                    $respuesta['tipo'] = 'danger';
                }
            }

            $mensaje = $respuesta['mensaje'];
            $tipo_mensaje = $respuesta['tipo'];

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $message = "Ocurrió un error al consulta del numero de documento. Por favor, inténtelo nuevamente.";
        }
        return array("result" => array("name" => $name,'direccion'=>$direccion,'estado'=>$estado,'mensaje'=>$mensaje,'tipo'=>$tipo_mensaje));
    }
    public function sacarMontoLiquidacion($id){
        try {

            $subTotal = 0;
            $liquidaDetalelle = DB::table('liquidacion_detalles as lq')
                ->join('liquidaciones as l', 'lq.id_liquidacion', '=', 'l.id_liquidacion')
                ->join('despachos as d', 'd.id_despacho', '=', 'lq.id_despacho')
                ->where([['l.liquidacion_estado','=',1],['l.liquidacion_estado_aprobacion','=',1],['lq.liquidacion_detalle_estado','=',1]])
                ->where('lq.id_despacho','=',$id)->first();

            if ($liquidaDetalelle){

                $gastos = DB::table('liquidacion_gastos')->where('id_liquidacion_detalle','=',$liquidaDetalelle->id_liquidacion_detalle)->get();

                $costoTotal = $gastos[0]->liquidacion_gasto_monto;
                $manoObra = $gastos[1]->liquidacion_gasto_monto;
                $otrosGastos = $gastos[2]->liquidacion_gasto_monto;
                $pesoFinal = $gastos[3]->liquidacion_gasto_monto;

                if (!$liquidaDetalelle->id_departamento){ // local
                    $subTotal = $costoTotal + $manoObra + $otrosGastos;
                }else{ // provincial
                    $subTotal = ($costoTotal * $pesoFinal) + $manoObra + $otrosGastos;
                }
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $subTotal = 0;
        }
        return $subTotal;
    }
    public function save_files($archivo, $rutaImg)
    {
        try {
            if ($archivo) {
                // Obtén el nombre original del archivo
                $originalName = $archivo->getClientOriginalName();
                // Obtén la fecha y hora actuales en el formato deseado
                $timestamp = now()->format('Ymd_His');
                // Combina la fecha y hora con el nombre original del archivo
                $newFileName = $timestamp . '_' . $originalName;
                // Especifica la subcarpeta dentro de 'uploads' (por ejemplo, 'uploads/documentos')
                $subcarpeta = $rutaImg;
                // Verifica si la subcarpeta existe, si no, la crea
                if (!Storage::disk('public_uploads')->exists($subcarpeta)) {
                    Storage::disk('public_uploads')->makeDirectory($subcarpeta);
                }
                // Guarda el archivo directamente en la subcarpeta con el nuevo nombre
                $path = $archivo->storeAs($subcarpeta, $newFileName, 'public_uploads');
                // Guarda la ruta en la base de datos
                return 'uploads/'.$path;

            } else {
                return [];
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
    }

    public function obtenerNombreFecha($fecha, $formatoDado, $formatoRetorno, $idioma = 'es', $incluirAnio = true)
    {
        try {
            // Configuración del idioma para Carbon
            Carbon::setLocale($idioma);

            // Convertir la fecha a una instancia de Carbon
            if ($formatoDado === 'DateTime') {
                $fechaCarbon = Carbon::parse($fecha);
            } elseif ($formatoDado === 'Date') {
                $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
            } else {
                throw new \InvalidArgumentException('Formato de fecha no soportado');
            }

            // Preparar el formato de salida
            if ($formatoRetorno === 'Date') {
                $formatoSalida = $incluirAnio ? 'd M Y' : 'd M';
            } elseif ($formatoRetorno === 'DateTime') {
                $formatoSalida = 'd M Y, h:i a';
            } else {
                throw new \InvalidArgumentException('Formato de retorno no soportado');
            }

            // Formatear la fecha
            $fechaFormateada = $fechaCarbon->translatedFormat($formatoSalida);

        }catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $fechaFormateada = "";
        }
        return $fechaFormateada;

    }

    public function formatoDecimal($valor)
    {
        try {
            // Verificar si el valor es numérico y mayor que 0
            if (!is_numeric($valor)) {
                \Log::error('Valor no numérico:', ['valor' => $valor]);
                return 0;
            }

            // Convertir el valor a cadena y eliminar cualquier carácter no numérico (excepto el punto decimal)
            $valorStr = preg_replace('/[^0-9.]/', '', (string)$valor);

            // Verificar si el valor es mayor que 0
            if ($valorStr <= 0) {
                \Log::error('Valor no positivo:', ['valor' => $valorStr]);
                return 0;
            }

            // Truncar el número a dos decimales sin redondear usando bcdiv
            $formattedValue = bcdiv($valorStr, '1', 2);

            // Si el valor truncado no tiene decimales, mostramos sin decimales
            if ($formattedValue == floor($formattedValue)) {
                return number_format($formattedValue, 0, '.', ',');
            }

            // Si tiene decimales, mostramos dos decimales
            return number_format($formattedValue, 2, '.', ',');
        } catch (\Exception $e) {
            // En caso de error, insertamos el log y retornamos 0
            $this->logs->insertarLog($e);
            return 0;
        }
    }

    public function obtenerColorPorPorcentaje($valor)
    {
        try {
            // Determinar el color según los rangos específicos
            if ($valor >= 1 && $valor <= 25) {
                return 'red'; // 1 a 25: rojo
            } elseif ($valor >= 26 && $valor <= 50) {
                return 'orange'; // 26 a 50: naranja
            } elseif ($valor >= 51 && $valor <= 75) {
                return '#a68b02'; // 51 a 75: amarillo oscuro
            } elseif ($valor >= 76 && $valor <= 100) {
                return 'green'; // 76 a 100: verde
            }

            // Si el valor no está en el rango esperado, retorna un color por defecto
            return 'red';

        } catch (\Exception $e) {
            // Manejo de errores: log y color por defecto
            $this->logs->insertarLog($e);
            return 'red';
        }
    }

    function formatearCodigo($codigo) {
        // Validar si la longitud de la cadena es mayor o igual a 4
        if (strlen($codigo) >= 4) {
            // Insertar el guion después del cuarto carácter
            return substr($codigo, 0, 4) . ' - ' . substr($codigo, 4);
        } else {
            // Si la longitud es menor a 4, agregar el guion al final
            return $codigo . '-';
        }
    }
}
