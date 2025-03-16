<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\TipoServicio;
use App\Models\Server;
use App\Models\Facturaspreprogramacion;
use App\Models\Historialguia;
use App\Models\Guiadetalle;
//use App\Models\Guia;
use Carbon\Carbon;

class Facturaspreprogramaciones extends Component
{
    private $logs;
    private $tiposervicio;
    private $server;
    private $facpreprog;
//    private $guia;
    private $historialguia;
    private $guiadetalle;
    public function __construct(){
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
        $this->server = new Server();
//        $this->guia = new Guia();
        $this->facpreprog = new Facturaspreprogramacion();
        $this->historialguia = new Historialguia();
        $this->guiadetalle = new Guiadetalle();
    }
    public $selectedGuias = [];
    public $filteredGuias = [];
    public $filtereddetGuias = [];
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
    public $guiaSeleccionada = null;
    public $detallesGuia = [];
    public $isSaving = false;
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
        if (empty($this->desde) && empty($this->hasta) && empty($this->searchGuia)) {
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
        $this->filteredGuias = $this->server->obtenerDocumentosRemision($this->desde, $this->hasta) ?? [];
        $this->filtereddetGuias = [];
        foreach ($this->filteredGuias as $guia) {
            $serie = isset($guia->serie) ? $guia->serie : null;
            $numero = isset($guia->numero) ? $guia->numero : null;

            if ($serie && $numero) {
                $detalles = $this->obtenerDetalleRemision($serie, $numero);
                $this->filtereddetGuias[$numero] = $detalles;
            }
        }
    }
    public function seleccionarGuia($NRO_DOC) {
        if (!is_array($this->selectedGuias)) {
            $this->selectedGuias = [];
        }

        $comprobanteExiste = collect($this->selectedGuias)->first(function ($guia) use ($NRO_DOC) {
            return isset($guia['NRO_DOC']) && $guia['NRO_DOC'] === $NRO_DOC;
        });

        if ($comprobanteExiste) {
            // Si ya existe, eliminar de la selección (deseleccionar)
            $this->selectedGuias = collect($this->selectedGuias)->reject(function ($guia) use ($NRO_DOC) {
                return isset($guia['NRO_DOC']) && $guia['NRO_DOC'] === $NRO_DOC;
            })->values()->toArray(); // Deselecciona el documento
        } else {
            // Si no existe, agregar a la selección
            $guia = collect($this->filteredGuias)->first(function ($guia_) use ($NRO_DOC) {
                return isset($guia_->NRO_DOC) && $guia_->NRO_DOC === $NRO_DOC;
            });

            if ($guia) {
                $this->selectedGuias[] = [
                    'ALMACEN_ORIGEN' => $guia->ALMACEN_ORIGEN,
                    'TIPO_DOC' => $guia->TIPO_DOC,
                    'NRO_DOC' => $NRO_DOC,
                    'FECHA_EMISION' => $guia->FECHA_EMISION,
                    'TIPO_MOVIMIENTO' => $guia->TIPO_MOVIMIENTO,
                    'TIPO_DOC_REF' => $guia->TIPO_DOC_REF,
                    'NRO_DOC_REF' => $guia->NRO_DOC_REF,
                    'GLOSA' => $guia->GLOSA,
                    'FECHA_DE_PROCESO' => $guia->FECHA_DE_PROCESO,
                    'HORA_DE_PROCESO' => $guia->HORA_DE_PROCESO,
                    'USUARIO' => $guia->USUARIO,
                    'COD_CLIENTE' => $guia->COD_CLIENTE,
                    'RUC_CLIENTE' => $guia->RUC_CLIENTE,
                    'NOMBRE_CLIENTE' => $guia->NOMBRE_CLIENTE,
                    'FORMA_DE_PAGO' => $guia->FORMA_DE_PAGO,
                    'VENDEDOR' => $guia->VENDEDOR,
                    'MONEDA' => $guia->MONEDA,
                    'TIPO_DE_CAMBIO' => $guia->TIPO_DE_CAMBIO,
                    'ESTADO' => $guia->ESTADO,
                    'DIREC_ENTREGA' => $guia->DIREC_ENTREGA,
                    'NRO_PEDIDO' => $guia->NRO_PEDIDO,
                    'IMPORTE_TOTAL' => $guia->IMPORTE_TOTAL,
                    'DEPARTAMENTO' => $guia->DEPARTAMENTO,
                    'PROVINCIA' => $guia->PROVINCIA,
                    'DISTRITO' => $guia->DISTRITO,
                    // Puedes agregar otros campos si los necesitas
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
            return isset($f['NRO_DOC']) && $f['NRO_DOC'] === $NRO_DOC;
        });

        if ($guia) {
            // Elimina la guía de las seleccionadas
            $this->selectedGuias = collect($this->selectedGuias)
                ->reject(function ($f) use ($NRO_DOC) {
                    return isset($f['NRO_DOC']) && $f['NRO_DOC'] === $NRO_DOC;
                })
                ->values()
                ->toArray();
        }
    }

    public function listar_detallesf($NRO_DOC) {
        // Busca la guía seleccionada en la lista de guías seleccionadas
        $guiaSeleccionada = collect($this->selectedGuias)->first(function ($guia) use ($NRO_DOC) {
            return isset($guia['NRO_DOC']) && $guia['NRO_DOC'] === $NRO_DOC;
        });

        // Asigna la guía seleccionada a una propiedad para que esté disponible en la vista del modal
        $this->guiaSeleccionada = $guiaSeleccionada;
    }

    public function detalle_guia($NRO_DOC) {
        // Llama a la API para obtener los detalles de la guía
        $detalles = $this->server->obtenerDetalleRemision($NRO_DOC);

        // Almacena los detalles en la propiedad $detallesGuia
        $this->detallesGuia = $detalles;
    }

//    public function guardarGuias() {
//        try {
//            // Validar que haya facturas seleccionadas y un estado seleccionado
//            $this->validate([
//                'estado_envio' => 'required|integer',
//                'selectedGuias' => 'required|array|min:1',
//            ], [
//                'estado_envio.required' => 'Debes seleccionar un estado.',
//                'estado_envio.integer' => 'El estado seleccionado no es válido.',
//                'selectedGuias.required' => 'Debes seleccionar al menos una factura.',
//                'selectedGuias.min' => 'Debes seleccionar al menos una factura.',
//            ]);
//
//            DB::beginTransaction();
//
//            foreach ($this->selectedGuias as $factura) {
//                // Verificar si la factura ya existe en la tabla
//                $facturaExistente = Facturaspreprogramacion::where('guia_nro_doc', $factura['NRO_DOC'])
//                    ->first();
//
//                if ($facturaExistente) {
//                    // Si la factura existe, actualizar el estado
//                    $facturaExistente->guia_estado_aprobacion = $this->estado_envio;
//                    $facturaExistente->guia_estado_registro = 1;
//                    $facturaExistente->guia_fecha = Carbon::now('America/Lima');
//                    $facturaExistente->save();
//
//                    // Guardar en la tabla historial_pre_programacion
////                    $historial = new Historialpreprogramacion();
////                    $historial->id_fac_pre_prog = $facturaExistente->id_fac_pre_prog;
////                    $historial->fac_pre_prog_cfnumdoc = $facturaExistente->fac_pre_prog_cfnumdoc;
////                    $historial->fac_pre_prog_estado_aprobacion = $facturaExistente->fac_pre_prog_estado_aprobacion;
////                    $historial->fac_pre_prog_estado = $facturaExistente->fac_pre_prog_estado;
////                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
////                    $historial->save();
//                } else {
//                    // Si no existe, crear un nuevo registro
//                    $nuevaFactura = new Facturaspreprogramacion();
//                    $nuevaFactura->id_users = Auth::id();
//                    $nuevaFactura->guia_almacen_origen = $factura['ALMACEN_ORIGEN'] ?: null;
//                    $nuevaFactura->guia_tipo_doc = $factura['TIPO_DOC'] ?: null;
//                    $nuevaFactura->guia_nro_doc = $factura['NRO_DOC'] ?: null;
//                    $nuevaFactura->guia_fecha_emision = $factura['FECHA_EMISION'] ?: null;
//                    $nuevaFactura->guia_tipo_movimiento = $factura['TIPO_MOVIMIENTO'] ?: null;
//                    $nuevaFactura->guia_tipo_doc_ref = $factura['TIPO_DOC_REF'] ?: null;
//                    $nuevaFactura->guia_nro_doc_ref = $factura['NRO_DOC_REF'] ?: null;
//                    $nuevaFactura->guia_glosa = $factura['GLOSA'] ?: null;
//                    $nuevaFactura->guia_fecha_proceso = $factura['FECHA_DE_PROCESO'] ?: null;
//                    $nuevaFactura->guia_hora_proceso = $factura['HORA_DE_PROCESO'] ?: null;
//                    $nuevaFactura->guia_usuario = $factura['USUARIO'] ?: null;
//                    $nuevaFactura->guia_cod_cliente = $factura['COD_CLIENTE'] ?: null;
//                    $nuevaFactura->guia_ruc_cliente = $factura['RUC_CLIENTE'] ?: null;
//                    $nuevaFactura->guia_nombre_cliente = $factura['NOMBRE_CLIENTE'] ?: null;
//                    $nuevaFactura->guia_forma_pago = $factura['FORMA_DE_PAGO'] ?: null;
//                    $nuevaFactura->guia_vendedor = $factura['VENDEDOR'] ?: null;
//                    $nuevaFactura->guia_moneda = $factura['MONEDA'] ?: null;
//                    $nuevaFactura->guia_tipo_cambio = $factura['TIPO_DE_CAMBIO'] ?: null;
//                    $nuevaFactura->guia_estado = $factura['ESTADO'] ?: null;
//                    $nuevaFactura->guia_direc_entrega = $factura['DIREC_ENTREGA'] ?: null;
//                    $nuevaFactura->guia_nro_pedido = $factura['NRO_PEDIDO'] ?: null;
//                    $nuevaFactura->guia_importe_total = $factura['IMPORTE_TOTAL'] ?: null;
//                    $nuevaFactura->guia_departamento = $factura['DEPARTAMENTO'] ?: null;
//                    $nuevaFactura->guia_provincia = $factura['PROVINCIA'] ?: null;
//                    $nuevaFactura->guia_destrito = $factura['DISTRITO'] ?: null;
//                    $nuevaFactura->guia_estado_aprobacion = $this->estado_envio;
//                    $nuevaFactura->guia_estado_registro = 1;
//                    $nuevaFactura->guia_fecha = Carbon::now('America/Lima');
//                    $nuevaFactura->save();
//
//                    // Obtener los detalles de la guía
//                    $detalles = $this->server->obtenerDetalleRemision($factura['NRO_DOC']);
//                    // Guardar los detalles en la tabla notas_creditos_detalles
//                    foreach ($detalles as $detalle) {
//                        $nuevoDetalle = new Guiadetalle();
//                        $nuevoDetalle->id_users = Auth::id();
//                        $nuevoDetalle->id_guia = $nuevaFactura->id_guia;
//                        $nuevoDetalle->guia_det_almacen_salida = $detalle->ALMACEN_SALIDA ?: null;
//                        $nuevoDetalle->guia_det_fecha_emision = $detalle->FECHA_EMISION ?: null;
//                        $nuevoDetalle->guia_det_estado = $detalle->ESTADO ?: null;
//                        $nuevoDetalle->guia_det_tipo_documento = $detalle->TIPO_DOCUMENTO ?: null;
//                        $nuevoDetalle->guia_det_nro_documento = $detalle->NRO_DOCUMENTO ?: null;
//                        $nuevoDetalle->guia_det_nro_linea = $detalle->NRO_LINEA ?: null;
//                        $nuevoDetalle->guia_det_cod_producto = $detalle->COD_PRODUCTO ?: null;
//                        $nuevoDetalle->guia_det_descripcion_producto = $detalle->DESCRIPCION_PRODUCTO ?: null;
//                        $nuevoDetalle->guia_det_lote = $detalle->LOTE ?: null;
//                        $nuevoDetalle->guia_det_unidad = $detalle->UNIDAD ?: null;
//                        $nuevoDetalle->guia_det_cantidad = $detalle->CANTIDAD ?: null;
//                        $nuevoDetalle->guia_det_precio_unit_final_inc_igv = $detalle->PRECIO_UNIT_FINAL_INC_IGV ?: null;
//                        $nuevoDetalle->guia_det_precio_unit_antes_descuente_inc_igv = $detalle->PRECIO_UNIT_ANTES_DESCUENTO_INC_IGV ?: null;
//                        $nuevoDetalle->guia_det_descuento_total_sin_igv = $detalle->DESCUENTO_TOTAL_SIN_IGV ?: null;
//                        $nuevoDetalle->guia_det_igv_total = $detalle->IGV_TOTAL ?: null;
//                        $nuevoDetalle->guia_det_importe_total_inc_igv = $detalle->IMPORTE_TOTAL_INC_IGV ?: null;
//                        $nuevoDetalle->guia_det_moneda = $detalle->MONEDA ?: null;
//                        $nuevoDetalle->guia_det_tipo_cambio = $detalle->TIPO_CAMBIO ?: null;
//                        $nuevoDetalle->guia_det_peso_gramo = $detalle->PESO_GRAMOS ?: null;
//                        $nuevoDetalle->guia_det_volumen = $detalle->VOLUMEN_CM3 ?: null;
//                        $nuevoDetalle->guia_det_peso_total_gramo = $detalle->PESO_TOTAL_GRAMOS ?: null;
//                        $nuevoDetalle->guia_det_volumen_total = $detalle->VOLUMEN_TOTAL_CM3 ?: null;
//                        $nuevoDetalle->save();
//                    }
//
//                    // Guardar en la tabla historial_pre_programacion
////                    $historial = new Historialpreprogramacion();
////                    $historial->id_fac_pre_prog = $nuevaFactura->id_fac_pre_prog;
////                    $historial->fac_pre_prog_cfnumdoc = $nuevaFactura->fac_pre_prog_cfnumdoc;
////                    $historial->fac_pre_prog_estado_aprobacion = $nuevaFactura->fac_pre_prog_estado_aprobacion;
////                    $historial->fac_pre_prog_estado = $nuevaFactura->fac_pre_prog_estado;
////                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
////                    $historial->save();
//
//                }    // Insertar en facturas_mov
//                DB::table('facturas_mov')->insert([
//                    'id_fac_pre_prog' => $nuevaFactura->id_guia, // Usar el ID de la nueva factura creada
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
        $this->filteredGuias = [];
        $this->isSaving = true; // Activar el estado de guardado
        try {
            // Validar que haya facturas seleccionadas y un estado seleccionado
            $this->validate([
                'estado_envio' => 'required|integer',
                'selectedGuias' => 'required|array|min:1',
            ], [
                'estado_envio.required' => 'Debes seleccionar un estado.',
                'estado_envio.integer' => 'El estado seleccionado no es válido.',
                'selectedGuias.required' => 'Debes seleccionar al menos una factura.',
                'selectedGuias.min' => 'Debes seleccionar al menos una factura.',
            ]);

            DB::beginTransaction();
            foreach ($this->selectedGuias as $factura) {
                // Verificar si la factura ya existe en la tabla
                $facturaExistente = Facturaspreprogramacion::where('guia_nro_doc', $factura['NRO_DOC'])
                    ->first();

                if ($facturaExistente) {
                    // Si la factura existe, actualizar el estado
                    $facturaExistente->guia_estado_aprobacion = $this->estado_envio;
                    $facturaExistente->guia_estado_registro = 1;
                    $facturaExistente->guia_fecha = Carbon::now('America/Lima');
                    $facturaExistente->save();

                    // Guardar en la tabla historial guias
                    $historial = new Historialguia();
                    $historial->id_users = Auth::id();
                    $historial->id_guia = $facturaExistente->id_guia;
                    $historial->guia_nro_doc = $facturaExistente->guia_nro_doc;
                    $historial->historial_guia_estado_aprobacion = $facturaExistente->guia_estado_aprobacion;
                    $historial->historial_guia_fecha_hora = Carbon::now('America/Lima');
                    $historial->historial_guia_estado = $facturaExistente->guia_estado_registro;
                    $historial->save();
                } else {
                    // Si no existe, crear un nuevo registro
                    $nuevaFactura = new Facturaspreprogramacion();
                    $nuevaFactura->id_users = Auth::id();
                    $nuevaFactura->guia_almacen_origen = $factura['ALMACEN_ORIGEN'] ?: null;
                    $nuevaFactura->guia_tipo_doc = $factura['TIPO_DOC'] ?: null;
                    $nuevaFactura->guia_nro_doc = $factura['NRO_DOC'] ?: null;
                    $nuevaFactura->guia_fecha_emision = $factura['FECHA_EMISION'] ?: null;
                    $nuevaFactura->guia_tipo_movimiento = $factura['TIPO_MOVIMIENTO'] ?: null;
                    $nuevaFactura->guia_tipo_doc_ref = $factura['TIPO_DOC_REF'] ?: null;
                    $nuevaFactura->guia_nro_doc_ref = $factura['NRO_DOC_REF'] ?: null;
                    $nuevaFactura->guia_glosa = $factura['GLOSA'] ?: null;
                    $nuevaFactura->guia_fecha_proceso = $factura['FECHA_DE_PROCESO'] ?: null;
                    $nuevaFactura->guia_hora_proceso = $factura['HORA_DE_PROCESO'] ?: null;
                    $nuevaFactura->guia_usuario = $factura['USUARIO'] ?: null;
                    $nuevaFactura->guia_cod_cliente = $factura['COD_CLIENTE'] ?: null;
                    $nuevaFactura->guia_ruc_cliente = $factura['RUC_CLIENTE'] ?: null;
                    $nuevaFactura->guia_nombre_cliente = $factura['NOMBRE_CLIENTE'] ?: null;
                    $nuevaFactura->guia_forma_pago = $factura['FORMA_DE_PAGO'] ?: null;
                    $nuevaFactura->guia_vendedor = $factura['VENDEDOR'] ?: null;
                    $nuevaFactura->guia_moneda = $factura['MONEDA'] ?: null;
                    $nuevaFactura->guia_tipo_cambio = $factura['TIPO_DE_CAMBIO'] ?: null;
                    $nuevaFactura->guia_estado = $factura['ESTADO'] ?: null;
                    $nuevaFactura->guia_direc_entrega = $factura['DIREC_ENTREGA'] ?: null;
                    $nuevaFactura->guia_nro_pedido = $factura['NRO_PEDIDO'] ?: null;
                    $nuevaFactura->guia_importe_total = $factura['IMPORTE_TOTAL'] ?: null;
                    $nuevaFactura->guia_departamento = $factura['DEPARTAMENTO'] ?: null;
                    $nuevaFactura->guia_provincia = $factura['PROVINCIA'] ?: null;
                    $nuevaFactura->guia_destrito = $factura['DISTRITO'] ?: null;
                    $nuevaFactura->guia_estado_aprobacion = $this->estado_envio;
                    $nuevaFactura->guia_estado_registro = 1;
                    $nuevaFactura->guia_fecha = Carbon::now('America/Lima');
                    $nuevaFactura->save();

                    // Obtener los detalles de la guía
                    $detalles = $this->server->obtenerDetalleRemision($factura['NRO_DOC']);
                    // Guardar los detalles en la tabla notas_creditos_detalles
                    foreach ($detalles as $detalle) {
                        $nuevoDetalle = new Guiadetalle();
                        $nuevoDetalle->id_users = Auth::id();
                        $nuevoDetalle->id_guia = $nuevaFactura->id_guia;
                        $nuevoDetalle->guia_det_almacen_salida = $detalle->ALMACEN_SALIDA ?: null;
                        $nuevoDetalle->guia_det_fecha_emision = $detalle->FECHA_EMISION ?: null;
                        $nuevoDetalle->guia_det_estado = $detalle->ESTADO ?: null;
                        $nuevoDetalle->guia_det_tipo_documento = $detalle->TIPO_DOCUMENTO ?: null;
                        $nuevoDetalle->guia_det_nro_documento = $detalle->NRO_DOCUMENTO ?: null;
                        $nuevoDetalle->guia_det_nro_linea = $detalle->NRO_LINEA ?: null;
                        $nuevoDetalle->guia_det_cod_producto = $detalle->COD_PRODUCTO ?: null;
                        $nuevoDetalle->guia_det_descripcion_producto = $detalle->DESCRIPCION_PRODUCTO ?: null;
                        $nuevoDetalle->guia_det_lote = $detalle->LOTE ?: null;
                        $nuevoDetalle->guia_det_unidad = $detalle->UNIDAD ?: null;
                        $nuevoDetalle->guia_det_cantidad = $detalle->CANTIDAD ?: null;
                        $nuevoDetalle->guia_det_precio_unit_final_inc_igv = $detalle->PRECIO_UNIT_FINAL_INC_IGV ?: null;
                        $nuevoDetalle->guia_det_precio_unit_antes_descuente_inc_igv = $detalle->PRECIO_UNIT_ANTES_DESCUENTO_INC_IGV ?: null;
                        $nuevoDetalle->guia_det_descuento_total_sin_igv = $detalle->DESCUENTO_TOTAL_SIN_IGV ?: null;
                        $nuevoDetalle->guia_det_igv_total = $detalle->IGV_TOTAL ?: null;
                        $nuevoDetalle->guia_det_importe_total_inc_igv = $detalle->IMPORTE_TOTAL_INC_IGV ?: null;
                        $nuevoDetalle->guia_det_moneda = $detalle->MONEDA ?: null;
                        $nuevoDetalle->guia_det_tipo_cambio = $detalle->TIPO_CAMBIO ?: null;
                        $nuevoDetalle->guia_det_peso_gramo = $detalle->PESO_GRAMOS ?: null;
                        $nuevoDetalle->guia_det_volumen = $detalle->VOLUMEN_CM3 ?: null;
                        $nuevoDetalle->guia_det_peso_total_gramo = $detalle->PESO_TOTAL_GRAMOS ?: null;
                        $nuevoDetalle->guia_det_volumen_total = $detalle->VOLUMEN_TOTAL_CM3 ?: null;
                        $nuevoDetalle->save();
                    }
                    // Guardar en la tabla historial guias
                    $historial = new Historialguia();
                    $historial->id_users = Auth::id();
                    $historial->id_guia = $nuevaFactura->id_guia;
                    $historial->guia_nro_doc = $nuevaFactura->guia_nro_doc;
                    $historial->historial_guia_estado_aprobacion = $nuevaFactura->guia_estado_aprobacion;
                    $historial->historial_guia_fecha_hora = Carbon::now('America/Lima');
                    $historial->historial_guia_estado = $nuevaFactura->guia_estado_registro;
                    $historial->save();
                }

                // Insertar en facturas_mov
                if (isset($nuevaFactura)) {
                    DB::table('facturas_mov')->insert([
                        'id_guia' => $nuevaFactura->id_guia, // Usar el ID de la nueva factura creada
                        'fac_envio_valpago' => Carbon::now('America/Lima'), // Establecer la fecha de envío
                        'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
                    ]);
                }
            }
            DB::commit();
            $this->selectedGuias = [];
            session()->flash('success', 'Guías enviadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al guardar las facturas: ' . $e->getMessage());
        } finally {
            $this->isSaving = false; // Restablecer el estado de guardado
        }
    }

    public function eliminarGuia($SERIE, $NUMERO)
    {
        $this->selectedGuias = array_filter($this->selectedGuias, function ($guia) use ($SERIE, $NUMERO) {
            return !($guia->SERIE === $SERIE && $guia->NUMERO === $NUMERO);
        });

        // Convertir el array filtrado en una colección de objetos nuevamente
        $this->selectedGuias = array_values(array_map(fn($guia) => (object) $guia, $this->selectedGuias));
    }
}
