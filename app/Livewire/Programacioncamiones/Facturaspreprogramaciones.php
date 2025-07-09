<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Guia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use App\Models\TipoServicio;
use App\Models\Server;
use App\Models\Facturaspreprogramacion;
use App\Models\Historialguia;
use App\Models\Guiadetalle;
use Carbon\Carbon;

class Facturaspreprogramaciones extends Component
{
    private $logs;
    private $tiposervicio;
    private $server;
    private $facpreprog;
    private $historialguia;
    private $guiadetalle;
    private $guia;

    public function __construct(){
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
        $this->server = new Server();
        $this->facpreprog = new Facturaspreprogramacion();
        $this->historialguia = new Historialguia();
        $this->guiadetalle = new Guiadetalle();
        $this->guia = new Guia();
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
    public $estado_envio = "";
    public $estado_envio_anulado = "";
    public $errorMessage;
    public $guiaSeleccionada = null;
    public $detallesGuia = [];
    public $isSaving = false;

    public $selectedGuiasNros = [];
    public $selectAll = false;
//    NUEVO
    public $select_varios = false;
    public $select_todas_guias = [];
    public $tipo_pregunta = "";
    public $messagePregunta_cd;
    public $messagePregunta_anular;

    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
        $this->selectedGuias = [];
    }

    public function render(){
        $listar_tipo_servicios = $this->tiposervicio->listar_tipo_servicios();
        $listar_guias_registradas = $this->guia->listar_guias_registradas();
        return view('livewire.programacioncamiones.facturaspreprogramaciones', compact('listar_tipo_servicios', 'listar_guias_registradas'));
    }

    public function buscar_comprobantes() {
        if (!Gate::allows('buscar_guias')) {
            session()->flash('error', 'No tiene permisos para buscar guías.');
            return;
        }

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

        // Resetear selecciones cuando se hace una nueva búsqueda
        $this->selectedGuiasNros = [];
        $this->selectAll = false;

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

    public function seleccionar_una_guia_intranet(){
        // Obtener todos los NRO_DOC únicos disponibles
        $allAvailableNros = collect($this->filteredGuias)
            ->pluck('NRO_DOC')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Verificar si están todos seleccionados
        $this->selectAll = count($this->selectedGuiasNros) === count($allAvailableNros) &&
            count($this->selectedGuiasNros) > 0;
    }

    public function seleccionar_todas_giuas_intranet(){
        $this->selectAll = !$this->selectAll;

        if ($this->selectAll) {
            // Seleccionar todos los NRO_DOC únicos disponibles
            $this->selectedGuiasNros = collect($this->filteredGuias)
                ->pluck('NRO_DOC')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        } else {
            // Deseleccionar todos
            $this->selectedGuiasNros = [];
        }
    }

    public function seleccionarGuia($NRO_DOC) {
        if (!Gate::allows('seleccionar_guias')) {
            session()->flash('error', 'No tiene permisos para seleccionar guías.');
            return;
        }

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
            })->values()->toArray();
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
                    'IMPORTE_TOTAL_SIN_IGV' => $guia->IMPORTE_TOTAL / 1.18,
                    'DEPARTAMENTO' => $guia->DEPARTAMENTO,
                    'PROVINCIA' => $guia->PROVINCIA,
                    'DISTRITO' => $guia->DISTRITO,
                ];

                // NO eliminar de filteredGuias aquí - mantener la lista original
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

            // También remover de selectedGuiasNros
            $this->selectedGuiasNros = array_values(array_diff($this->selectedGuiasNros, [$NRO_DOC]));
        }
    }

    public function listar_detallesf($NRO_DOC) {
        $guiaSeleccionada = collect($this->selectedGuias)->first(function ($guia) use ($NRO_DOC) {
            return isset($guia['NRO_DOC']) && $guia['NRO_DOC'] === $NRO_DOC;
        });

        $this->guiaSeleccionada = $guiaSeleccionada;
    }

    public function detalle_guia($NRO_DOC) {
        $detalles = $this->server->obtenerDetalleRemision($NRO_DOC);
        $this->detallesGuia = $detalles;
    }

    public function guardar_guias_intranet(){
        if (!Gate::allows('guardar_guias_intranet')) {
            session()->flash('error', 'No tiene permisos para enviar guías.');
            return;
        }

        if (empty($this->selectedGuiasNros)) {
            session()->flash('error', 'Debes seleccionar al menos una guía.');
            return;
        }

        $this->isSaving = true;

        try {
            DB::beginTransaction();

            // Obtener las guías completas seleccionadas
            $guiasParaGuardar = collect($this->filteredGuias)
                ->whereIn('NRO_DOC', $this->selectedGuiasNros)
                ->all();

            foreach ($guiasParaGuardar as $guia) {
                // Verificar si la guía ya existe
                $guiaExistente = Guia::where('guia_nro_doc', $guia->NRO_DOC)->first();

                if ($guiaExistente) {
                    // Actualizar guía existente
                    $guiaExistente->guia_estado_aprobacion = 13;
                    $guiaExistente->guia_estado_registro = 1;
                    $guiaExistente->guia_fecha = Carbon::now('America/Lima');
                    $guiaExistente->save();

                    // Guardar historial
                    $historial = new Historialguia();
                    $historial->id_users = Auth::id();
                    $historial->id_guia = $guiaExistente->id_guia;
                    $historial->guia_nro_doc = $guiaExistente->guia_nro_doc;
                    $historial->historial_guia_estado_aprobacion = $guiaExistente->guia_estado_aprobacion;
                    $historial->historial_guia_fecha_hora = Carbon::now('America/Lima');
                    $historial->historial_guia_estado = $guiaExistente->guia_estado_registro;
                    $historial->save();
                } else {
                    // Crear nueva guía
                    $nuevaFactura = new Guia();
                    $nuevaFactura->id_users = Auth::id();
                    $nuevaFactura->guia_almacen_origen = $guia->ALMACEN_ORIGEN ?? null;
                    $nuevaFactura->guia_tipo_doc = $guia->TIPO_DOC ?? null;
                    $nuevaFactura->guia_nro_doc = $guia->NRO_DOC ?? null;
                    $nuevaFactura->guia_fecha_emision = $guia->FECHA_EMISION ?? null;
                    $nuevaFactura->guia_tipo_movimiento = $guia->TIPO_MOVIMIENTO ?? null;
                    $nuevaFactura->guia_tipo_doc_ref = $guia->TIPO_DOC_REF ?? null;
                    $nuevaFactura->guia_nro_doc_ref = $guia->NRO_DOC_REF ?? null;
                    $nuevaFactura->guia_glosa = $guia->GLOSA ?? null;
                    $nuevaFactura->guia_fecha_proceso = $guia->FECHA_DE_PROCESO ?? null;
                    $nuevaFactura->guia_hora_proceso = $guia->HORA_DE_PROCESO ?? null;
                    $nuevaFactura->guia_usuario = $guia->USUARIO ?? null;
                    $nuevaFactura->guia_cod_cliente = $guia->COD_CLIENTE ?? null;
                    $nuevaFactura->guia_ruc_cliente = $guia->RUC_CLIENTE ?? null;
                    $nuevaFactura->guia_nombre_cliente = $guia->NOMBRE_CLIENTE ?? null;
                    $nuevaFactura->guia_forma_pago = $guia->FORMA_DE_PAGO ?? null;
                    $nuevaFactura->guia_vendedor = $guia->VENDEDOR ?? null;
                    $nuevaFactura->guia_moneda = $guia->MONEDA ?? null;
                    $nuevaFactura->guia_tipo_cambio = $guia->TIPO_DE_CAMBIO ?? null;
                    $nuevaFactura->guia_estado = $guia->ESTADO ?? null;
                    $nuevaFactura->guia_direc_entrega = $guia->DIREC_ENTREGA ?? null;
                    $nuevaFactura->guia_nro_pedido = $guia->NRO_PEDIDO ?? null;
                    $nuevaFactura->guia_importe_total = $guia->IMPORTE_TOTAL ?? null;
                    $nuevaFactura->guia_importe_total_sin_igv = $guia->IMPORTE_TOTAL / 1.18 ?? null;
                    $nuevaFactura->guia_departamento = $guia->DEPARTAMENTO ?? null;
                    $nuevaFactura->guia_provincia = $guia->PROVINCIA ?? null;
                    $nuevaFactura->guia_destrito = $guia->DISTRITO ?? null;
                    $nuevaFactura->guia_estado_aprobacion = 13;
                    $nuevaFactura->guia_estado_registro = 1;
                    $nuevaFactura->guia_fecha = Carbon::now('America/Lima');
                    $nuevaFactura->save();

                    // Guardar detalles
                    $detalles = $this->server->obtenerDetalleRemision($guia->NRO_DOC);
                    foreach ($detalles as $detalle) {
                        $nuevoDetalle = new Guiadetalle();
                        $nuevoDetalle->id_users = Auth::id();
                        $nuevoDetalle->id_guia = $nuevaFactura->id_guia;
                        $nuevoDetalle->guia_det_almacen_salida = $detalle->ALMACEN_SALIDA ?? null;
                        $nuevoDetalle->guia_det_fecha_emision = $detalle->FECHA_EMISION ?? null;
                        $nuevoDetalle->guia_det_estado = $detalle->ESTADO ?? null;
                        $nuevoDetalle->guia_det_tipo_documento = $detalle->TIPO_DOCUMENTO ?? null;
                        $nuevoDetalle->guia_det_nro_documento = $detalle->NRO_DOCUMENTO ?? null;
                        $nuevoDetalle->guia_det_nro_linea = $detalle->NRO_LINEA ?? null;
                        $nuevoDetalle->guia_det_cod_producto = $detalle->COD_PRODUCTO ?? null;
                        $nuevoDetalle->guia_det_descripcion_producto = $detalle->DESCRIPCION_PRODUCTO ?? null;
                        $nuevoDetalle->guia_det_lote = $detalle->LOTE ?? null;
                        $nuevoDetalle->guia_det_unidad = $detalle->UNIDAD ?? null;
                        $nuevoDetalle->guia_det_cantidad = $detalle->CANTIDAD ?? null;
                        $nuevoDetalle->guia_det_precio_unit_final_inc_igv = $detalle->PRECIO_UNIT_FINAL_INC_IGV ?? null;
                        $nuevoDetalle->guia_det_precio_unit_antes_descuente_inc_igv = $detalle->PRECIO_UNIT_ANTES_DESCUENTO_INC_IGV ?? null;
                        $nuevoDetalle->guia_det_descuento_total_sin_igv = $detalle->DESCUENTO_TOTAL_SIN_IGV ?? null;
                        $nuevoDetalle->guia_det_igv_total = $detalle->IGV_TOTAL ?? null;
                        $nuevoDetalle->guia_det_importe_total_inc_igv = $detalle->IMPORTE_TOTAL_INC_IGV ?? null;
                        $nuevoDetalle->guia_det_moneda = $detalle->MONEDA ?? null;
                        $nuevoDetalle->guia_det_tipo_cambio = $detalle->TIPO_CAMBIO ?? null;
                        $nuevoDetalle->guia_det_peso_gramo = $detalle->PESO_GRAMOS ?? null;
                        $nuevoDetalle->guia_det_volumen = $detalle->VOLUMEN_CM3 ?? null;
                        $nuevoDetalle->guia_det_peso_total_gramo = $detalle->PESO_TOTAL_GRAMOS ?? null;
                        $nuevoDetalle->guia_det_volumen_total = $detalle->VOLUMEN_TOTAL_CM3 ?? null;
                        $nuevoDetalle->save();
                    }

                    // Guardar historial
                    $historial = new Historialguia();
                    $historial->id_users = Auth::id();
                    $historial->id_guia = $nuevaFactura->id_guia;
                    $historial->guia_nro_doc = $nuevaFactura->guia_nro_doc;
                    $historial->historial_guia_estado_aprobacion = $nuevaFactura->guia_estado_aprobacion;
                    $historial->historial_guia_fecha_hora = Carbon::now('America/Lima');
                    $historial->historial_guia_estado = $nuevaFactura->guia_estado_registro;
                    $historial->save();

                    // Insertar en facturas_mov
                    DB::table('facturas_mov')->insert([
                        'id_guia' => $nuevaFactura->id_guia,
                        'fac_envio_valpago' => Carbon::now('America/Lima'),
                        'id_users_responsable' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            // Limpiar selección
            $this->selectedGuiasNros = [];
            $this->filteredGuias = [];
            $this->selectAll = false;

            session()->flash('success', 'Guías procesadas correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al procesar guías: ' . $e->getMessage());
            \Log::error('Error al guardar guías: ' . $e->getMessage());
        } finally {
            $this->isSaving = false;
        }
    }

    public function eliminarGuia($SERIE, $NUMERO)
    {
        $this->selectedGuias = array_filter($this->selectedGuias, function ($guia) use ($SERIE, $NUMERO) {
            return !($guia->SERIE === $SERIE && $guia->NUMERO === $NUMERO);
        });

        $this->selectedGuias = array_values(array_map(fn($guia) => (object) $guia, $this->selectedGuias));
    }

//    NUEVAS FUNCIONES
    public function seleccionar_varias_guias(){
        $this->select_varios = !$this->select_varios;

        if ($this->select_varios) {
            $this->select_todas_guias = $this->guia->listar_guias_registradas()
                ->pluck('id_guia')
                ->toArray();
        } else {
            $this->select_todas_guias = [];
        }
    }

    public function pregunta_modal($tipo){
        $tipo_pregunta = $tipo;
        if ($tipo_pregunta == '1'){

            if (empty($this->estado_envio)) {
                session()->flash('error_modal_credito', 'Debe seleccionar un estado (Créditos o Despachos).');
                return;
            }

            if ($this->estado_envio == '1'){
                $this->messagePregunta_cd = "Créditos";
            } elseif ($this->estado_envio == '2'){
                $this->messagePregunta_cd = "Despachos";
            }
        } elseif($tipo_pregunta == '2') {

            if (empty($this->estado_envio_anulado)) {
                session()->flash('error_modal_credito', 'Debe seleccionar un estado (Anulado o Anulado NC).');
                return;
            }

            if ($this->estado_envio_anulado == '14'){
                $this->messagePregunta_anular = "Anulado";
            } elseif ($this->estado_envio_anulado == '15'){
                $this->messagePregunta_anular = "Pendiente de NC";
            }
        }
    }

    public function enviar_estado_guia() {
        try {
            // Verifica permisos
            if (!Gate::allows('enviar_estado_guia')) {
                session()->flash('error', 'No tiene permisos para aceptar las guías.');
                return;
            }

            // Validar que se haya seleccionado un estado
            if (empty($this->estado_envio)) {
                session()->flash('error', 'Debe seleccionar un estado (Créditos o Despacho).');
                return;
            }

            // Validar que al menos una guía esté seleccionada
            if (count($this->select_todas_guias) == 0) {
                session()->flash('error', 'Debe seleccionar al menos una guía.');
                return;
            }

            DB::beginTransaction();

            foreach ($this->select_todas_guias as $id_guia) {
                $guia = Guia::find($id_guia);

                if ($guia) {
                    // Actualizar estado de la guía
                    $guia->guia_estado_aprobacion = $this->estado_envio;

                    if ($guia->save()) {
                        // Registrar en historial de guías
                        $historial = new Historialguia();
                        $historial->id_users = Auth::id();
                        $historial->id_guia = $id_guia;
                        $historial->guia_nro_doc = $guia->guia_nro_doc;
                        $historial->historial_guia_estado_aprobacion = $this->estado_envio;
                        $historial->historial_guia_fecha_hora = Carbon::now('America/Lima');
                        $historial->historial_guia_estado = 1;

                        if (!$historial->save()) {
                            DB::rollBack();
                            session()->flash('error', 'Error al guardar el historial de la guía.');
                            return;
                        }
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'Error al actualizar el estado de la guía.');
                        return;
                    }
                }
            }

            DB::commit();
            session()->flash('success', 'Estado de las guías actualizado correctamente.');
            $this->dispatch('hideModalCreditosDespachos');
            // Limpiar selección después de guardar
            $this->select_todas_guias = [];
            $this->select_varios = false;
            $this->estado_envio = "";

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al actualizar las guías: ' . $e->getMessage());
        }
    }

    public function enviar_anulado_nc() {
        try {
            // Verifica permisos
            if (!Gate::allows('enviar_anulado_nc')) {
                session()->flash('error', 'No tiene permisos para aceptar las guías.');
                return;
            }

            // Validar que se haya seleccionado un estado
            if (empty($this->estado_envio_anulado)) {
                session()->flash('error', 'Debe seleccionar un estado (Créditos o Despacho).');
                return;
            }

            // Validar que al menos una guía esté seleccionada
            if (count($this->select_todas_guias) == 0) {
                session()->flash('error', 'Debe seleccionar al menos una guía.');
                return;
            }

            DB::beginTransaction();

            foreach ($this->select_todas_guias as $id_guia) {
                $guia = Guia::find($id_guia);

                if ($guia) {
                    // Actualizar estado de la guía
                    $guia->guia_estado_aprobacion = $this->estado_envio_anulado;

                    if ($guia->save()) {
                        // Registrar en historial de guías
                        $historial = new Historialguia();
                        $historial->id_users = Auth::id();
                        $historial->id_guia = $id_guia;
                        $historial->guia_nro_doc = $guia->guia_nro_doc;
                        $historial->historial_guia_estado_aprobacion = $this->estado_envio_anulado;
                        $historial->historial_guia_fecha_hora = Carbon::now('America/Lima');
                        $historial->historial_guia_estado = 1;

                        if (!$historial->save()) {
                            DB::rollBack();
                            session()->flash('error', 'Error al guardar el historial de la guía.');
                            return;
                        }
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'Error al actualizar el estado de la guía.');
                        return;
                    }
                }
            }

            DB::commit();
            session()->flash('success', 'Estado de las guías actualizado correctamente.');
            $this->dispatch('hideModalAnularNC');
            // Limpiar selección después de guardar
            $this->select_todas_guias = [];
            $this->select_varios = false;
            $this->estado_envio_anulado = "";

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al actualizar las guías: ' . $e->getMessage());
        }
    }
}
