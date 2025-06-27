<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Facturamovimientoarea;
use App\Models\Facturaspreprogramacion;
use App\Models\Historialguia;
use App\Models\Logs;
use App\Models\Guia;
use App\Models\Server;
use App\Models\Guiadetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Facturacion extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $facpreprog;
    private $facmovarea;
    private $historialguia;
    private $guia;
    private $server;
    public function __construct(){
        $this->logs = new Logs();
        $this->facpreprog = new Facturaspreprogramacion();
        $this->facmovarea = new Facturamovimientoarea();
        $this->historialguia = new Historialguia();
        $this->guia = new Guia();
        $this->server = new Server();
    }
    public $fecha_hasta;
    public $fecha_desde;
    public $buscar_ruc_nombre = "";
    public $messagePrePro = "";
    public $id_guia = "";
    public $guia_estado_aprobacion;
    public $fechaHoraManual;
    public $fechaHoraManual2;
    public $fac_pre_prog_estado_aprobacion = "";
    public $fac_mov_area_motivo_rechazo = "";
    public $messageRecFactApro;
    public $listar_comprobantes = [];
    public $buscar_numero_guia;
    public $buscar_estado;

    public $guia_fecha_emision;
    public $historialEstados = [];
    public $fechasEditadas = [];
    public $comentariosEditados = [];
    public $fecha_emision_edit;
    public $comentario_emision;
    public $guia_nro_doc;

    public function mount(){
        $this->fecha_desde = date('Y-m-01');
        $this->fecha_hasta = date('Y-m-d');
    }
    public function render(){
//        $facturas_pre_prog_estadox = $this->guia->listar_facturas_pre_programacion_estadox($this->nombre_cliente, $this->fecha_desde, $this->fecha_hasta);
        return view('livewire.programacioncamiones.facturacion');
    }

    public function buscar_comprobantes(){

        if (!Gate::allows('buscar_comprobantes_ged')) {
            session()->flash('error_pre_pro', 'No tiene permisos para cambiar los estados de este registro.');
            return;
        }

        $query = DB::table('guias as g')
            ->leftJoin('guias_detalles as gd', 'g.id_guia', '=', 'gd.id_guia')
            ->where('g.guia_estado_registro', '=', 1);

        // Aplicar filtro por nombre de cliente si existe
        if (!empty($this->buscar_ruc_nombre)) {
            $busqueda = trim($this->buscar_ruc_nombre);

            // Verificar si tiene el formato "RUC - Nombre"
            if (preg_match('/^(\d+)\s*-\s*(.+)$/', $busqueda, $matches)) {
                $ruc = trim($matches[1]);
                $nombre = trim($matches[2]);

                $query->where(function($q) use ($ruc, $nombre) {
                    $q->where('guia_ruc_cliente', 'LIKE', '%' . $ruc . '%')
                        ->where('guia_nombre_cliente', 'LIKE', '%' . $nombre . '%');
                });
            } else {
                // Búsqueda normal (RUC o Nombre)
                $query->where(function($q) use ($busqueda) {
                    $q->where('guia_ruc_cliente', 'LIKE', '%' . $busqueda . '%')
                        ->orWhere('guia_nombre_cliente', 'LIKE', '%' . $busqueda . '%');
                });
            }
        }

        // Aplicar filtro por rango de fechas si existen
        if (!empty($this->buscar_numero_guia)) {
            $query->where('guia_nro_doc', 'LIKE', '%' . $this->buscar_numero_guia . '%');
        } else {
            // Aplicar filtros de fecha
            if ($this->fecha_desde) {
                $query->whereDate('guia_fecha_emision', '>=', $this->fecha_desde);
            }
            if ($this->fecha_hasta) {
                $query->whereDate('guia_fecha_emision', '<=', $this->fecha_hasta);
            }

            // Filtro por estado de aprobación
            if (!empty($this->buscar_estado)) {
                $query->where('guia_estado_aprobacion', $this->buscar_estado);
            }
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
                'g.guia_importe_total_sin_igv',
                'g.guia_departamento',
                'g.guia_provincia',
                'g.guia_destrito',
                'g.guia_estado_aprobacion',
                'g.guia_estado_registro',
                'g.guia_fecha',
                'g.created_at',
                'g.updated_at'
            );
            $this->listar_comprobantes = $result->get();
    }
    public function actualizar_detalle_guia($num_doc,$id){

        if (!Gate::allows('actualizar_detalle_guia')) {
            session()->flash('error_pre_pro', 'No tiene permisos para cambiar los estados de este registro.');
            return;
        }

        $detalle_actual = $this->guia->listar_guia_detalle_x_num_doc($num_doc);
        $detalle_real = $this->server->obtenerDetalleRemision($num_doc);
        $id_ =  base64_decode($id);
        if($detalle_actual != count($detalle_real)){
            DB::beginTransaction();
            $eliminar_detalle = $this->guia->eliminar_guia_detalle($num_doc);
            if($eliminar_detalle == 0){
                foreach ($detalle_real as $dg){
                    $GuiaDetalle = new Guiadetalle();
                    $GuiaDetalle->id_users = 4;
                    $GuiaDetalle->id_guia  = $id_;
                    $GuiaDetalle->guia_det_almacen_salida = $dg->ALMACEN_SALIDA ?: null;
                    $GuiaDetalle->guia_det_fecha_emision = $dg->FECHA_EMISION ?: null;
                    $GuiaDetalle->guia_det_estado = $dg->ESTADO ?: null;
                    $GuiaDetalle->guia_det_tipo_documento = $dg->TIPO_DOCUMENTO ?: null;
                    $GuiaDetalle->guia_det_nro_documento = $dg->NRO_DOCUMENTO ?: null;
                    $GuiaDetalle->guia_det_nro_linea = $dg->NRO_LINEA ?: null;
                    $GuiaDetalle->guia_det_cod_producto = $dg->COD_PRODUCTO ?: null;
                    $GuiaDetalle->guia_det_descripcion_producto = $dg->DESCRIPCION_PRODUCTO ?: null;
                    $GuiaDetalle->guia_det_lote = $dg->LOTE ?: null;
                    $GuiaDetalle->guia_det_unidad = $dg->UNIDAD ?: null;
                    $GuiaDetalle->guia_det_cantidad = $dg->CANTIDAD ?: null;
                    $GuiaDetalle->guia_det_precio_unit_final_inc_igv = $dg->PRECIO_UNIT_FINAL_INC_IGV ?: null;
                    $GuiaDetalle->guia_det_precio_unit_antes_descuente_inc_igv = $dg->PRECIO_UNIT_ANTES_DESCUENTO_INC_IGV ?: null;
                    $GuiaDetalle->guia_det_descuento_total_sin_igv = $dg->DESCUENTO_TOTAL_SIN_IGV ?: null;
                    $GuiaDetalle->guia_det_igv_total = $dg->IGV_TOTAL ?: null;
                    $GuiaDetalle->guia_det_importe_total_inc_igv = $dg->IMPORTE_TOTAL_INC_IGV ?: null;
                    $GuiaDetalle->guia_det_moneda = $dg->MONEDA ?: null;
                    $GuiaDetalle->guia_det_tipo_cambio = $dg->TIPO_CAMBIO ?: null;
                    $GuiaDetalle->guia_det_peso_gramo = $dg->PESO_GRAMOS ?: null;
                    $GuiaDetalle->guia_det_volumen = $dg->VOLUMEN_CM3 ?: null;
                    $GuiaDetalle->guia_det_peso_total_gramo = $dg->PESO_TOTAL_GRAMOS ?: null;
                    $GuiaDetalle->guia_det_volumen_total = $dg->VOLUMEN_TOTAL_CM3 ?: null;

                    if (!$GuiaDetalle->save()){
                        DB::rollBack();
                        session()->flash('error', 'Ocurrió un error, contactar a soporte');
                        return;
                    }
                }
                DB::commit();
                session()->flash('success', 'Detalle de la Guía Actualizada Correctamente!.');
            }
        }
    }

    public function cambio_estado($id_guia, $estado_aprobacion){
        $this->fechaHoraManual = '';
        $this->id_guia = base64_decode($id_guia);
        $this->guia_estado_aprobacion = $estado_aprobacion;

        if ($this->id_guia) {
            // Obtener la fecha y hora actual en la zona horaria de Lima
            $fechaHoraActual = Carbon::now('America/Lima')->format('d/m/Y - h:i a');

            // Actualizar el mensaje con la fecha y hora actual
            $this->messagePrePro = "¿Estás seguro de enviar con fecha $fechaHoraActual?";
        }
    }

    public function edit_guia($id_guia){
        $this->id_guia = base64_decode($id_guia);
    }

    public function actualizarMensaje()
    {
        // Si hay una fecha y hora manual, usarla; de lo contrario, usar la fecha y hora actual
        $fechaHora = $this->fechaHoraManual
            ? Carbon::parse($this->fechaHoraManual, 'America/Lima')->format('d/m/Y - h:i a')
            : Carbon::now('America/Lima')->format('d/m/Y - h:i a');

        // Actualizar el mensaje con la nueva fecha y hora
        $this->messagePrePro = "¿Estás seguro de enviar con fecha $fechaHora?";
    }
    public function disable_pre_pro(){
        try {
            // Verificar permisos del usuario
            if (!Gate::allows('disable_pre_pro')) {
                session()->flash('error_pre_pro', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }

            // Validar los datos de entrada
            $this->validate([
                'id_guia' => 'required|integer',
                'guia_estado_aprobacion' => 'required|integer',
                'fechaHoraManual' => 'nullable|date',
            ], [
                'id_guia.required' => 'El identificador es obligatorio.',
                'id_guia.integer' => 'El identificador debe ser un número entero.',
                'guia_estado_aprobacion.required' => 'El estado es obligatorio.',
                'guia_estado_aprobacion.integer' => 'El estado debe ser un número entero.',
                'fechaHoraManual.date' => 'La fecha y hora manual debe ser una fecha válida.',
            ]);

            // Iniciar una transacción de base de datos
            DB::beginTransaction();

            // Buscar la factura por ID
            $factura = Guia::find($this->id_guia);

            if ($factura) {
                $factura->guia_estado_aprobacion = $this->guia_estado_aprobacion;

                if ($factura->save()) {
                    // Registrar en historial guias
                    $historial = new Historialguia();
                    $historial->id_users = Auth::id();
                    $historial->id_guia = $this->id_guia;
                    $historial->guia_nro_doc = $factura->guia_nro_doc;
                    $historial->historial_guia_estado_aprobacion = $this->guia_estado_aprobacion;
                    $historial->historial_guia_fecha_hora = Carbon::now('America/Lima');
                    $historial->historial_guia_estado = 1;
                    $historial->save();
                    // Buscar si ya existe un registro en la tabla facturas_mov
                    $facturaMov = DB::table('facturas_mov')
                        ->where('id_guia', $this->id_guia)
                        ->first();

                    if ($facturaMov) {
                        // Actualizar el registro existente
                        DB::table('facturas_mov')
                            ->where('id_guia', $this->id_guia)
                            ->update([
                                'fac_acept_est_fac' => $this->fechaHoraManual2 ? Carbon::parse($this->fechaHoraManual2, 'America/Lima') : Carbon::now('America/Lima'),
                                'fac_envio_val_rec' => $this->fechaHoraManual2 ? Carbon::parse($this->fechaHoraManual2, 'America/Lima') : Carbon::now('America/Lima'),
                            ]);
                    } else {
                        // Crear un nuevo registro en facturas_mov
                        DB::table('facturas_mov')->insert([
                            'id_guia' => $this->id_guia,
                            'fac_acept_est_fac' => $this->fechaHoraManual2 ? Carbon::parse($this->fechaHoraManual2, 'America/Lima') : Carbon::now('America/Lima'),
                            'fac_envio_val_rec' => $this->fechaHoraManual2 ? Carbon::parse($this->fechaHoraManual2, 'America/Lima') : Carbon::now('America/Lima'),
                            'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
                        ]);
                    }

                    // Confirmar la transacción
                    DB::commit();

                    // Cerrar el modal y mostrar mensaje de éxito
                    $this->dispatch('hidemodalPrePro');
                    session()->flash('success', 'Estado cambiado exitosamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error_pre_pro', 'No se pudo cambiar el estado de la factura.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_pre_pro', 'La factura no existe.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al aceptar la factura. Detalles: ' . $e->getMessage());
            \Log::error('Error en disable_pre_pro: ' . $e->getMessage());
        }
    }
    public function rech_fact($id_fac){
        $id = base64_decode($id_fac);
        $this->fac_mov_area_motivo_rechazo = "";
        if ($id) {
            $this->id_fac_pre_prog = $id;
            $this->fac_mov_area_motivo_rechazo = "";
            $this->messageRecFactApro = "¿Está seguro de rechazar esta factura?";
        }
    }

    public function rechazar_factura_aprobar(){
        try {
            // Verifica permisos
            if (!Gate::allows('rechazar_factura_aprobar')) {
                session()->flash('error', 'No tiene permisos para cambiar los estados del menú.');
                return;
            }

            // Verificar si el motivo de rechazo está vacío
            if (empty($this->fac_mov_area_motivo_rechazo)) {
                session()->flash('error-modal-rechazo', 'Debe ingresar un motivo de rechazo.');
                return;
            }

            // Validar que el motivo de rechazo no esté vacío
            $this->validate([
                'fac_mov_area_motivo_rechazo' => 'required|string',
            ]);

            // Iniciar transacción
            DB::beginTransaction();

            // Buscar la factura preprogramada por su ID
            $facturaPreprogramada = Facturaspreprogramacion::find($this->id_fac_pre_prog);

            if ($facturaPreprogramada) {
                // Actualizar el estado de aprobación a 0 (rechazado)
                $facturaPreprogramada->fac_pre_prog_estado_aprobacion = 5;

                // Guardar cambios en la factura preprogramada
                if ($facturaPreprogramada->save()) {
                    // Crear un nuevo registro en la tabla facturas_movimientos_areas
                    $movimientoArea = new Facturamovimientoarea();
                    $movimientoArea->id_users_responsable = Auth::id();
                    $movimientoArea->id_fac_pre_prog = $this->id_fac_pre_prog;
                    $movimientoArea->fac_mov_area_motivo_rechazo = $this->fac_mov_area_motivo_rechazo;
                    $movimientoArea->fac_mov_area_fecha = now()->toDateString();
                    $movimientoArea->fac_mov_area_hora = now()->toTimeString();

                    if ($movimientoArea->save()) {
                        DB::commit();
                        session()->flash('success', 'Factura rachazada. Enviado a creditos y cobranzas');
                        $this->dispatch('hidemodaRecFac');
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'No se pudo guardar el motivo de rechazo.');
                    }
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo rechazar la factura.');
                }
            } else {
                DB::rollBack();
                session()->flash('error', 'No se encontró la factura.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al rechazar la factura.');
        }
    }

    public function edit_cambio_estado($id){
        if ($id){
            $this->id_guia = base64_decode($id);
            $this->guia_estado_aprobacion = "";
        }
    }

    public function cambio_estado_edit(){
        try {
            if (!Gate::allows('cambio_estado_edit')) {
                session()->flash('error-edit-guia', 'No tiene permisos para aprobar o rechazar este servicio de transporte.');
                return;
            }

            $this->validate([
                'id_guia' => 'required|integer',
                'guia_estado_aprobacion' => 'required|in:1,2,3,4,7,12,8',
            ], [
                'id_guia.required' => 'El identificador es obligatorio.',
                'id_guia.integer' => 'El identificador debe ser un número entero.',
                'guia_estado_aprobacion.required' => 'El estado de la guía es obligatorio.',
                'guia_estado_aprobacion.in' => 'Debe seleccionar un estado válido.',
            ]);

            DB::beginTransaction();

            // Buscar el servicio de transporte
            $edit_guia_update = Guia::find($this->id_guia);

            if (!$edit_guia_update) {
                DB::rollBack();
                session()->flash('error-edit-guia', 'La guía no fue encontrado.');
                return;
            }

            // Cambiar el estado de la guía
            $edit_guia_update->guia_estado_aprobacion = $this->guia_estado_aprobacion;

            if ($edit_guia_update->save()) {
                DB::commit();
                $this->dispatch('modalEditCambioEstado');
                session()->flash('success', 'La guía cambio de estado.');
                $this->guia_estado_aprobacion = "";
                $this->buscar_comprobantes();
            } else {
                DB::rollBack();
                session()->flash('error-edit-guia', 'No se pudo cambiar el estado de la guía.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al cambiar el estado del registro. Por favor, inténtelo nuevamente.');
        }
    }
//
    public function edit_fecha_guia($id_guia){
        try {
            $this->id_guia = base64_decode($id_guia);
            $this->fechasEditadas = [];
            $this->comentariosEditados = [];

            // Obtener fecha de emisión de la guía
            $guia = DB::table('guias')
                ->where('id_guia', $this->id_guia)
                ->first();

            $this->guia_fecha_emision = $guia->guia_fecha_emision ?? null;
            $this->fecha_emision_edit = $this->formatDateForInput($this->guia_fecha_emision);
            $this->guia_nro_doc = $guia->guia_nro_doc;

            // Estados que nos interesan
            $estadosRelevantes = [
                5 => 'En Crédito',
                3 => 'Por Programar',
                9 => 'Programado',
                7 => 'En Ruta',
                8 => 'Entregado',
                11 => 'Anulado'
            ];

            $this->historialEstados = [];

            foreach ($estadosRelevantes as $estado => $label) {
                $registro = DB::table('historial_guias as hg')
                    ->join('users as u', 'hg.id_users', '=', 'u.id_users')
                    ->where('hg.id_guia', $this->id_guia)
                    ->where('hg.historial_guia_estado_aprobacion', $estado)
                    ->orderBy('hg.historial_guia_fecha_hora', 'desc')
                    ->select('hg.*', 'u.name')
                    ->first();

                if ($registro) {
                    $this->historialEstados[$estado] = [
                        'historial_guia_fecha_hora' => $registro->historial_guia_fecha_hora,
                        'name' => $registro->name,
                        'fecha_formateada' => $this->formatDateForInput($registro->historial_guia_fecha_hora)
                    ];
                    $this->fechasEditadas[$estado] = $this->historialEstados[$estado]['fecha_formateada'];
                } else {
                    $this->historialEstados[$estado] = null;
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error: '.$e->getMessage());
        }
    }

    public function formatDateForInput($date) {
        if (!$date) return null;
        try {
            return Carbon::parse($date)->format('Y-m-d\TH:i');
        } catch (\Exception $e) {
            return null;
        }
    }
}
