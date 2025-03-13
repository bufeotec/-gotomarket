<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\TipoServicio;
use App\Models\Server;
//use App\Models\Facturaspreprogramacion;
use App\Models\Historialpreprogramacion;
use App\Models\Guia;
use Carbon\Carbon;

class Facturaspreprogramaciones extends Component
{
    private $logs;
    private $tiposervicio;
    private $server;
    private $facpreprog;
    private $guia;
    private $historialpreprogramacion;
    public function __construct(){
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
        $this->server = new Server();
        $this->guia = new Guia();
//        $this->facpreprog = new Facturaspreprogramacion();
        $this->historialpreprogramacion = new Historialpreprogramacion();
    }
    public $selectedGuias = [];
    public $filteredGuias = [];
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $importeTotalVenta = 0;
    public $id_tipo_servicios = "";
    public $searchFactura = "";
    public $desde;
    public $hasta;
    public $detalleFactura;
    public $estado_envio = 1;
    public $errorMessage;
    public function mount(){
        $this->desde = date('Y-01-01');
        $this->hasta = date('Y-m-d');
        $this->selectedGuias = [];
    }

    public function render(){
        $listar_tipo_servicios = $this->tiposervicio->listar_tipo_servicios();
        return view('livewire.programacioncamiones.facturaspreprogramaciones', compact('listar_tipo_servicios'));
    }
    public function buscar_comprobantes() {
        if (empty($this->desde) && empty($this->hasta) && empty($this->searchFactura)) {
            session()->flash('error', 'Debe ingresar al menos una fecha o un criterio de búsqueda.');
            return;
        }

        if (!empty($this->desde) && !empty($this->hasta)) {
            $yearDesde = date('Y', strtotime($this->desde));
            $yearHasta = date('Y', strtotime($this->hasta));
            if ($yearDesde < 2025 || $yearHasta < 2025) {
                session()->flash('error', 'Las fechas deben ser a partir de 2025.');
                return;
            }
        }

        // Obtener documentos de remisión
        $this->filteredGuias = $this->server->obtenerDocumentosRemision($this->desde, $this->hasta) ?? [];
//dd($this->filteredGuias);
        $this->filtereddetGuias = [];
        foreach ($this->filteredGuias as $guia) {
            $serie = isset($guia->serie) ? $guia->serie : null; // Verifica la existencia de serie
            $numero = isset($guia->numero) ? $guia->numero : null; // Verifica la existencia de numero

            if ($serie && $numero) { // Solo llama a la función si ambos existen
                $detalles = $this->obtenerDetalleRemision($serie, $numero);
                $this->filtereddetGuias[$numero] = $detalles; // Almacena los detalles
            }
        }
    }
    public function obtenerDetalleRemision($serie, $numero) {
        try {
            $result = array();
            $client = new \GuzzleHttp\Client();
            $url = "http://161.132.173.106:8081/api_goto/public/api/v1/list_local_receipts";

            $response = $client->post($url, [
                'form_params' => [
                    'serie' => $serie,
                    'numero' => $numero,
                ],
            ]);

            // Procesar la respuesta
            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);

            if ($responseData->code === 200) {
                $result = collect($responseData->data);
            }

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }
    public function seleccionarGuia($NRO_DOC) {
        if (!is_array($this->selectedGuias)) {
            $this->selectedGuias = [];
        }

        $comprobanteExiste = collect($this->selectedGuias)->first(function ($guia) use ($NRO_DOC) {
            return isset($guia['nro_documento']) && $guia['nro_documento'] === $NRO_DOC;
        });

        if ($comprobanteExiste) {
            // Si ya existe, eliminar de la selección (deseleccionar)
            $this->selectedGuias = collect($this->selectedGuias)->reject(function ($guia) use ($NRO_DOC) {
                return isset($guia['nro_documento']) && $guia['nro_documento'] === $NRO_DOC;
            })->values()->toArray(); // Deselecciona el documento
        } else {
            // Si no existe, agregar a la selección
            $guia = collect($this->filteredGuias)->first(function ($guia_) use ($NRO_DOC) {
                return isset($guia_->NRO_DOCUMENTO) && $guia_->NRO_DOCUMENTO === $NRO_DOC;
            });

            if ($guia) {
                $this->selectedGuias[] = [
                    'almacen_salida' => $guia->ALMACEN_SALIDA, // Cambio realizado aquí
                    'estado' => $guia->ESTADO,
                    'tipo_documento' => $guia->TIPO_DOCUMENTO,
                    'nro_documento' => $guia->NRO_DOCUMENTO,
                    'fecha_emision' => $guia->FECHA_EMISION,
                    'nro_linea' => $guia->NRO_LINEA, // Nuevo campo
                    'cod_producto' => $guia->COD_PRODUCTO, // Nuevo campo
                    'descripcion_producto' => $guia->DESCRIPCION_PRODUCTO, // Nuevo campo
                    'lote' => $guia->LOTE, // Nuevo campo
                    'unidad' => $guia->UNIDAD,
                    'cantidad' => $guia->CANTIDAD,
                    'precio_unit_final_inc_igv' => $guia->PRECIO_UNIT_FINAL_INC_IGV, // Nuevo campo
                    'precio_unit_antes_descuento_inc_igv' => $guia->PRECIO_UNIT_ANTES_DESCUENTO_INC_IGV, // Nuevo campo
                    'descuento_total_sin_igv' => $guia->DESCUENTO_TOTAL_SIN_IGV, // Nuevo campo
                    'igv_total' => $guia->IGV_TOTAL, // Nuevo campo
                    'importe_total_inc_igv' => $guia->IMPORTE_TOTAL_INC_IGV, // Nuevo campo
                    'moneda' => $guia->MONEDA,
                    'tipo_cambio' => $guia->TIPO_CAMBIO,
                    'peso' => $guia->PESO_GRAMOS,
                    'volumen' => $guia->VOLUMEN_CM3,
                    'peso_total' => $guia->PESO_TOTAL_GRAMOS, // Nuevo campo
                    'volumen_total' => $guia->VOLUMEN_TOTAL_CM3, // Nuevo campo
                ];

                // Elimina la guía de la lista de guías filtradas
                $this->filteredGuias = collect($this->filteredGuias)->reject(function ($guia_) use ($NRO_DOC) {
                    return isset($guia_->NRO_DOCUMENTO) && $guia_->NRO_DOCUMENTO === $NRO_DOC;
                })->values();
            }
        }
    }
    public function eliminarFacturaSeleccionada($NRO_DOC) {
        // Encuentra la guía en las seleccionadas
        $guia = collect($this->selectedGuias)->first(function ($f) use ($NRO_DOC) {
            return isset($f['nro_documento']) && $f['nro_documento'] === $NRO_DOC;
        });

        if ($guia) {
            // Elimina la guía de las seleccionadas
            $this->selectedGuias = collect($this->selectedGuias)
                ->reject(function ($f) use ($NRO_DOC) {
                    return isset($f['nro_documento']) && $f['nro_documento'] === $NRO_DOC;
                })
                ->values()
                ->toArray();

            // Vuelve a agregar la guía a la lista de filtrados, asegurándose de incluir todos los campos necesarios
            $this->filteredGuias[] = (object) [
                'ALMACEN_SALIDA' => $guia['almacen_salida'],
                'ESTADO' => $guia['estado'],
                'TIPO_DOCUMENTO' => $guia['tipo_documento'],
                'NRO_DOCUMENTO' => $guia['nro_documento'],
                'FECHA_EMISION' => $guia['fecha_emision'],
                'NRO_LINEA' => $guia['nro_linea'],
                'COD_PRODUCTO' => $guia['cod_producto'],
                'DESCRIPCION_PRODUCTO' => $guia['descripcion_producto'],
                'LOTE' => $guia['lote'],
                'UNIDAD' => $guia['unidad'],
                'CANTIDAD' => $guia['cantidad'],
                'PRECIO_UNIT_FINAL_INC_IGV' => $guia['precio_unit_final_inc_igv'],
                'PRECIO_UNIT_ANTES_DESCUENTO_INC_IGV' => $guia['precio_unit_antes_descuento_inc_igv'],
                'DESCUENTO_TOTAL_SIN_IGV' => $guia['descuento_total_sin_igv'],
                'IGV_TOTAL' => $guia['igv_total'],
                'IMPORTE_TOTAL_INC_IGV' => $guia['importe_total_inc_igv'],
                'MONEDA' => $guia['moneda'],
                'TIPO_CAMBIO' => $guia['tipo_cambio'],
                'PESO_GRAMOS' => $guia['peso'],
                'VOLUMEN_CM3' => $guia['volumen'],
                'PESO_TOTAL_GRAMOS' => $guia['peso_total'],
                'VOLUMEN_TOTAL_CM3' => $guia['volumen_total'],
            ];
        }
    }
//    public function guardarFacturas() {
//        try {
//            // Validar que haya facturas seleccionadas y un estado seleccionado
//            $this->validate([
//                'estado_envio' => 'required|integer',
//                'selectedFacturas' => 'required|array|min:1',
//            ], [
//                'estado_envio.required' => 'Debes seleccionar un estado.',
//                'estado_envio.integer' => 'El estado seleccionado no es válido.',
//                'selectedFacturas.required' => 'Debes seleccionar al menos una factura.',
//                'selectedFacturas.min' => 'Debes seleccionar al menos una factura.',
//            ]);
//
//            DB::beginTransaction();
//
//            foreach ($this->selectedFacturas as $factura) {
//                // Verificar si la factura ya existe en la tabla
//                $facturaExistente = Facturaspreprogramacion::where('fac_pre_prog_cftd', $factura['CFTD'])
//                    ->where('fac_pre_prog_cfnumser', $factura['CFNUMSER'])
//                    ->where('fac_pre_prog_cfnumdoc', $factura['CFNUMDOC'])
//                    ->first();
//
//                if ($facturaExistente) {
//                    // Si la factura existe, actualizar el estado
//                    $facturaExistente->fac_pre_prog_estado_aprobacion = $this->estado_envio;
//                    $facturaExistente->fac_pre_prog_estado = 1;
//                    $facturaExistente->fac_pre_prog_fecha = Carbon::now('America/Lima');
//                    $facturaExistente->save();
//
//                    // Guardar en la tabla historial_pre_programacion
//                    $historial = new Historialpreprogramacion();
//                    $historial->id_fac_pre_prog = $facturaExistente->id_fac_pre_prog;
//                    $historial->fac_pre_prog_cfnumdoc = $facturaExistente->fac_pre_prog_cfnumdoc;
//                    $historial->fac_pre_prog_estado_aprobacion = $facturaExistente->fac_pre_prog_estado_aprobacion;
//                    $historial->fac_pre_prog_estado = $facturaExistente->fac_pre_prog_estado;
//                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
//                    $historial->save();
//                } else {
//                    // Si no existe, crear un nuevo registro
//                    $nuevaFactura = new Facturaspreprogramacion();
//                    $nuevaFactura->id_users = Auth::id();
//                    $nuevaFactura->fac_pre_prog_cftd = $factura['CFTD'];
//                    $nuevaFactura->fac_pre_prog_cfnumser = $factura['CFNUMSER'];
//                    $nuevaFactura->fac_pre_prog_cfnumdoc = $factura['CFNUMDOC'];
//                    $nuevaFactura->fac_pre_prog_factura = $factura['CFNUMSER'] . '-' . $factura['CFNUMDOC'];
//                    $nuevaFactura->fac_pre_prog_grefecemision = $factura['GREFECEMISION'];
//                    $nuevaFactura->fac_pre_prog_cnomcli = $factura['CNOMCLI'];
//                    $nuevaFactura->fac_pre_prog_cfcodcli = $factura['CCODCLI'];
//                    $nuevaFactura->fac_pre_prog_guia = $factura['guia'];
//                    $nuevaFactura->fac_pre_prog_cfimporte = $factura['CFIMPORTE'];
//                    $nuevaFactura->fac_pre_prog_total_kg = $factura['total_kg'];
//                    $nuevaFactura->fac_pre_prog_total_volumen = $factura['total_volumen'];
//                    $nuevaFactura->fac_pre_prog_direccion_llegada = $factura['LLEGADADIRECCION'];
//                    $nuevaFactura->fac_pre_prog_departamento = $factura['DEPARTAMENTO'];
//                    $nuevaFactura->fac_pre_prog_provincia = $factura['PROVINCIA'];
//                    $nuevaFactura->fac_pre_prog_distrito = $factura['DISTRITO'];
//                    $nuevaFactura->fac_pre_prog_estado_aprobacion = $this->estado_envio;
//                    $nuevaFactura->fac_pre_prog_estado = 1;
//                    $nuevaFactura->fac_pre_prog_fecha = Carbon::now('America/Lima');
//                    $nuevaFactura->save();
//
//                    // Guardar en la tabla historial_pre_programacion
//                    $historial = new Historialpreprogramacion();
//                    $historial->id_fac_pre_prog = $nuevaFactura->id_fac_pre_prog;
//                    $historial->fac_pre_prog_cfnumdoc = $nuevaFactura->fac_pre_prog_cfnumdoc;
//                    $historial->fac_pre_prog_estado_aprobacion = $nuevaFactura->fac_pre_prog_estado_aprobacion;
//                    $historial->fac_pre_prog_estado = $nuevaFactura->fac_pre_prog_estado;
//                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
//                    $historial->save();
//
//                }    // Insertar en facturas_mov
//                DB::table('facturas_mov')->insert([
//                    'id_fac_pre_prog' => $historial->id_fac_pre_prog, // Usar el ID de la nueva factura creada
//                    'fac_envio_valpago' => Carbon::now('America/Lima'), // Establecer la fecha de envío
//                    'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
//                ]);
//            }
//            DB::commit();
//            $this->selectedGuias = [];
//            session()->flash('success', 'Guías enviadas correctamente.');
//        } catch (\Exception $e) {
//            DB::rollBack();
//            session()->flash('error', 'Ocurrió un error al guardar las facturas: ' . $e->getMessage());
//        }
//    }
    public function guardarGuias() {
        try {
            // Validar que haya facturas seleccionadas y un estado seleccionado
            $this->validate([
                'estado_envio' => 'required|integer',
                'selectedFacturas' => 'required|array|min:1',
            ], [
                'estado_envio.required' => 'Debes seleccionar un estado.',
                'estado_envio.integer' => 'El estado seleccionado no es válido.',
                'selectedFacturas.required' => 'Debes seleccionar al menos una factura.',
                'selectedFacturas.min' => 'Debes seleccionar al menos una factura.',
            ]);

            DB::beginTransaction();

            foreach ($this->selectedFacturas as $factura) {
                // Verificar si la factura ya existe en la tabla
                $facturaExistente = Facturaspreprogramacion::where('fac_pre_prog_cftd', $factura['CFTD'])
                    ->where('fac_pre_prog_cfnumser', $factura['CFNUMSER'])
                    ->where('fac_pre_prog_cfnumdoc', $factura['CFNUMDOC'])
                    ->first();

                if ($facturaExistente) {
                    // Si la factura existe, actualizar el estado
                    $facturaExistente->fac_pre_prog_estado_aprobacion = $this->estado_envio;
                    $facturaExistente->fac_pre_prog_estado = 1;
                    $facturaExistente->fac_pre_prog_fecha = Carbon::now('America/Lima');
                    $facturaExistente->save();

                    // Guardar en la tabla historial_pre_programacion
                    $historial = new Historialpreprogramacion();
                    $historial->id_fac_pre_prog = $facturaExistente->id_fac_pre_prog;
                    $historial->fac_pre_prog_cfnumdoc = $facturaExistente->fac_pre_prog_cfnumdoc;
                    $historial->fac_pre_prog_estado_aprobacion = $facturaExistente->fac_pre_prog_estado_aprobacion;
                    $historial->fac_pre_prog_estado = $facturaExistente->fac_pre_prog_estado;
                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
                    $historial->save();
                } else {
                    // Si no existe, crear un nuevo registro
                    $nuevaFactura = new Facturaspreprogramacion();
                    $nuevaFactura->id_users = Auth::id();
                    $nuevaFactura->fac_pre_prog_cftd = $factura['CFTD'];
                    $nuevaFactura->fac_pre_prog_cfnumser = $factura['CFNUMSER'];
                    $nuevaFactura->fac_pre_prog_cfnumdoc = $factura['CFNUMDOC'];
                    $nuevaFactura->fac_pre_prog_factura = $factura['CFNUMSER'] . '-' . $factura['CFNUMDOC'];
                    $nuevaFactura->fac_pre_prog_grefecemision = $factura['GREFECEMISION'];
                    $nuevaFactura->fac_pre_prog_cnomcli = $factura['CNOMCLI'];
                    $nuevaFactura->fac_pre_prog_cfcodcli = $factura['CCODCLI'];
                    $nuevaFactura->fac_pre_prog_guia = $factura['guia'];
                    $nuevaFactura->fac_pre_prog_cfimporte = $factura['CFIMPORTE'];
                    $nuevaFactura->fac_pre_prog_total_kg = $factura['total_kg'];
                    $nuevaFactura->fac_pre_prog_total_volumen = $factura['total_volumen'];
                    $nuevaFactura->fac_pre_prog_direccion_llegada = $factura['LLEGADADIRECCION'];
                    $nuevaFactura->fac_pre_prog_departamento = $factura['DEPARTAMENTO'];
                    $nuevaFactura->fac_pre_prog_provincia = $factura['PROVINCIA'];
                    $nuevaFactura->fac_pre_prog_distrito = $factura['DISTRITO'];
                    $nuevaFactura->fac_pre_prog_estado_aprobacion = $this->estado_envio;
                    $nuevaFactura->fac_pre_prog_estado = 1;
                    $nuevaFactura->fac_pre_prog_fecha = Carbon::now('America/Lima');
                    $nuevaFactura->save();

                    // Guardar en la tabla historial_pre_programacion
                    $historial = new Historialpreprogramacion();
                    $historial->id_fac_pre_prog = $nuevaFactura->id_fac_pre_prog;
                    $historial->fac_pre_prog_cfnumdoc = $nuevaFactura->fac_pre_prog_cfnumdoc;
                    $historial->fac_pre_prog_estado_aprobacion = $nuevaFactura->fac_pre_prog_estado_aprobacion;
                    $historial->fac_pre_prog_estado = $nuevaFactura->fac_pre_prog_estado;
                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
                    $historial->save();

                }    // Insertar en facturas_mov
                DB::table('facturas_mov')->insert([
                    'id_fac_pre_prog' => $historial->id_fac_pre_prog, // Usar el ID de la nueva factura creada
                    'fac_envio_valpago' => Carbon::now('America/Lima'), // Establecer la fecha de envío
                    'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
                ]);
            }
            DB::commit();
            $this->selectedGuias = [];
            session()->flash('success', 'Guías enviadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al guardar las facturas: ' . $e->getMessage());
        }
    }

    public function listar_detallesf($nro_doc) {
        // Encuentra la guía en las seleccionadas
        $guia = collect($this->selectedGuias)->first(function ($f) use ($nro_doc) {
            return isset($f['nro_documento']) && $f['nro_documento'] === $nro_doc;
        });

        if ($guia) {
            $this->detalleFactura = $guia; // Guardar los detalles de la guía seleccionada
        } else {
            $this->detalleFactura = null; // Reiniciar si no se encuentra
        }
    }
    public function eliminarGuia($SERIE, $NUMERO)
    {
        // Filtrar y eliminar la guía seleccionada
        $this->selectedGuias = array_filter($this->selectedGuias, function ($guia) use ($SERIE, $NUMERO) {
            return !($guia->SERIE === $SERIE && $guia->NUMERO === $NUMERO);
        });

        // Convertir el array filtrado en una colección de objetos nuevamente
        $this->selectedGuias = array_values(array_map(fn($guia) => (object) $guia, $this->selectedGuias));
    }
}
