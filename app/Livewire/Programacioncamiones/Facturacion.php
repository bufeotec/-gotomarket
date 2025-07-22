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
    public $comentarios_fecha_edits = [];
    public $fecha_emision_edit;
    public $comentario_emision;
    public $guia_nro_doc;
    public $nombre_usuario;
    public $ids_historial_guia = [];
    public $estado_actual;
    public $estado_actual_texto;
    public $estado_entrega;

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
                'g.guia_vendedor_codigo',
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
                $this->dispatch('hideModalActualizarDetalle');
                session()->flash('success', 'Detalle de la Guía Actualizada Correctamente!.');
            }
        }
        $this->dispatch('hideModalActualizarDetalle');
        session()->flash('success', 'No se encontraron cambios para actualizar.');
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

        // Obtener estado de la guía
        $obtener_estado = DB::table('guias')
            ->where('id_guia', '=', $this->id_guia)
            ->first();

        // Obtener estado de entrega desde despacho_ventas
        $this->estado_entrega = DB::table('despacho_ventas')
            ->where('id_guia', $this->id_guia)
            ->value('despacho_detalle_estado_entrega');

        $this->estado_actual = $obtener_estado->guia_estado_aprobacion;
        $this->estado_actual_texto = $this->getEstadoTexto($obtener_estado->guia_estado_aprobacion, $this->estado_entrega);
    }

    public function getEstadoTexto($estado, $estado_entrega = null){
        // Primero verificar si es estado 7 (en tránsito) y tiene estado_entrega 8
        if($estado == 7 && $estado_entrega == 8) {
            return 'Guía entregada';
        }

        $estados = [
            0 => 'Guía anulada',
            1 => 'Enviado a Créditos',
            2 => 'Enviado a Despacho',
            3 => 'Listo para despacho',
            4 => 'Pendiente de aprobación de despacho',
            5 => 'Aceptado por Créditos',
            6 => 'Estado de facturación',
            7 => 'Guía en tránsito',
            8 => 'Guía entregada',
            9 => 'Despacho aprobado',
            10 => 'Despacho rechazado',
            11 => 'Guía no entregada',
            12 => 'Guía anulada'
        ];

        return $estados[$estado] ?? 'Estado desconocido';
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

    public function cambio_estado_edit() {
        try {

            $this->validate([
                'id_guia' => 'required|integer',
                'guia_estado_aprobacion' => 'required|in:1,2,3,4,5,7,12,8,9',
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

            // Obtener el estado actual antes de cambiarlo
            $estado_actual = $edit_guia_update->guia_estado_aprobacion;
            $nuevo_estado = $this->guia_estado_aprobacion;

            // Cambiar el estado de la guía
            $edit_guia_update->guia_estado_aprobacion = $nuevo_estado;

            if ($edit_guia_update->save()) {
                // Lógica para manejar el historial según los estados
                if ($nuevo_estado == 1) { // Si cambia a Créditos
                    // Eliminar todo el historial excepto Créditos (estado 1)
                    DB::table('historial_guias')
                        ->where('id_guia', $this->id_guia)
                        ->where('historial_guia_estado_aprobacion', '!=', 1)
                        ->delete();
                }
                elseif ($nuevo_estado == 3) { // Si cambia a Por Programar
                    // Eliminar historial de estados posteriores (Programado, En ruta, Entregado)
                    DB::table('historial_guias')
                        ->where('id_guia', $this->id_guia)
                        ->whereIn('historial_guia_estado_aprobacion', [4, 7, 8, 9])
                        ->delete();
                }
                elseif ($nuevo_estado == 4) { // Si cambia a Programado
                    // Eliminar historial de estados posteriores (En ruta, Entregado)
                    DB::table('historial_guias')
                        ->where('id_guia', $this->id_guia)
                        ->whereIn('historial_guia_estado_aprobacion', [7, 8])
                        ->delete();
                }
                elseif ($nuevo_estado == 7) { // Si cambia a En ruta
                    // Eliminar historial de estado Entregado
                    DB::table('historial_guias')
                        ->where('id_guia', $this->id_guia)
                        ->where('historial_guia_estado_aprobacion', 8)
                        ->delete();
                }
                elseif ($nuevo_estado == 8) { // Si cambia a Entregado
                    // No se elimina nada, solo se agrega el nuevo estado
                }
                elseif ($nuevo_estado == 12) { // Si cambia a Anulado
                    // Eliminar todo el historial
                    DB::table('historial_guias')
                        ->where('id_guia', $this->id_guia)
                        ->delete();
                }

                // crear los registros intermedios faltantes
                if ($nuevo_estado == 8) {
                    $historial_existente = DB::table('historial_guias')
                        ->where('id_guia', $this->id_guia)
                        ->pluck('historial_guia_estado_aprobacion')
                        ->toArray();

                    $estados_faltantes = [];

                    // Verificar qué estados intermedios faltan
                    if (!in_array(3, $historial_existente)) $estados_faltantes[] = 3; // Listo para despachar
                    if (!in_array(5, $historial_existente)) $estados_faltantes[] = 5; // Aceptado por créditos
                    if (!in_array(4, $historial_existente)) $estados_faltantes[] = 4; // Guía despachada
                    if (!in_array(7, $historial_existente)) $estados_faltantes[] = 7; // Guía en ruta
                    if (!in_array(9, $historial_existente)) $estados_faltantes[] = 9; // Programación abrobada

                    // Insertar los estados faltantes
                    foreach ($estados_faltantes as $estado) {
                        DB::table('historial_guias')->insert([
                            'id_users' => Auth::id(),
                            'id_guia' => $this->id_guia,
                            'guia_nro_doc' => $edit_guia_update->guia_nro_doc,
                            'historial_guia_estado_aprobacion' => $estado,
                            'historial_guia_fecha_hora' => Carbon::now('America/Lima'),
                            'historial_guia_estado' => 1,
                            'created_at' => Carbon::now('America/Lima'),
                            'updated_at' => Carbon::now('America/Lima'),
                        ]);
                    }
                }

                // Insertar el nuevo estado en el historial
                DB::table('historial_guias')->insert([
                    'id_users' => Auth::id(),
                    'id_guia' => $this->id_guia,
                    'guia_nro_doc' => $edit_guia_update->guia_nro_doc,
                    'historial_guia_estado_aprobacion' => $nuevo_estado,
                    'historial_guia_fecha_hora' => Carbon::now('America/Lima'),
                    'historial_guia_estado' => 1,
                    'created_at' => Carbon::now('America/Lima'),
                    'updated_at' => Carbon::now('America/Lima'),
                ]);

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
            $this->comentarios_fecha_edits = [];
            $this->ids_historial_guia = [];

            // Obtener fecha de emisión de la guía
            $guia = DB::table('guias as g')
                ->join('users as u', 'g.id_users', 'u.id_users')
                ->where('g.id_guia', $this->id_guia)
                ->first();

            $this->guia_fecha_emision = $guia->guia_fecha_emision ?? null;
            $this->fecha_emision_edit = $this->formatDateForInput($this->guia_fecha_emision);
            $this->guia_nro_doc = $guia->guia_nro_doc;
            $this->nombre_usuario = $guia->name;

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
                        'id_historial_guia' => $registro->id_historial_guia,
                        'historial_guia_fecha_hora' => $registro->historial_guia_fecha_hora,
                        'name' => $registro->name,
                        'fecha_formateada' => $this->formatDateForInput($registro->historial_guia_fecha_hora),
                        'comentario' => $registro->historial_guia_descripcion // Agregar el comentario existente
                    ];
                    $this->fechasEditadas[$estado] = $this->historialEstados[$estado]['fecha_formateada'];
                    $this->ids_historial_guia[$estado] = $registro->id_historial_guia;
                    $this->comentarios_fecha_edits[$estado] = $registro->historial_guia_descripcion;
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

    public function cambio_fecha_edit_guia() {
        try {
            /*if (!Gate::allows('cambiar_fecha_historial')) {
                session()->flash('error_fecha_guia', 'No tiene permisos para aprobar o rechazar este servicio de transporte.');
                return;
            }*/

            // Validar que los comentarios estén presentes cuando se cambia la fecha
            $errores = [];
            foreach ($this->fechasEditadas as $estado => $fechaEditada) {
                $fechaOriginal = $this->historialEstados[$estado]['fecha_formateada'] ?? null;
                $comentario = $this->comentarios_fecha_edits[$estado] ?? null;

                if ($fechaEditada != $fechaOriginal && empty($comentario)) {
                    $nombreEstado = [
                        5 => 'En Crédito',
                        3 => 'Por Programar',
                        9 => 'Programado',
                        7 => 'En Ruta',
                        8 => 'Entregado',
                        11 => 'Anulado'
                    ][$estado] ?? 'Desconocido';

                    $errores[] = "Debe ingresar un comentario para el estado '$nombreEstado' ya que modificó la fecha";
                }
            }

            if (!empty($errores)) {
                session()->flash('error_fecha_guia', implode('<br>', $errores));
                return;
            }

            DB::beginTransaction();

            // Actualizar solo los registros modificados
            foreach ($this->fechasEditadas as $estado => $fechaEditada) {
                $idHistorial = $this->ids_historial_guia[$estado] ?? null;
                $fechaOriginal = $this->historialEstados[$estado]['fecha_formateada'] ?? null;
                $comentario = $this->comentarios_fecha_edits[$estado] ?? null;

                if ($idHistorial && $fechaEditada != $fechaOriginal) {
                    DB::table('historial_guias')
                        ->where('id_historial_guia', $idHistorial)
                        ->update([
                            'historial_guia_fecha_hora' => Carbon::parse($fechaEditada)->format('Y-m-d H:i:s'),
                            'historial_guia_descripcion' => $comentario,
                            'updated_at' => now('America/Lima')
                        ]);
                }
            }

            DB::commit();
            session()->flash('success', 'Fechas actualizadas correctamente');
            $this->dispatch('modalEditFechaGuia');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al actualizar las fechas: '.$e->getMessage());
        }
    }
}
