<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Facturamovimientoarea;
use App\Models\Facturaspreprogramacion;
use App\Models\Historialguia;
use App\Models\Logs;
use App\Models\Guia;
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
    public function __construct(){
        $this->logs = new Logs();
        $this->facpreprog = new Facturaspreprogramacion();
        $this->facmovarea = new Facturamovimientoarea();
        $this->historialguia = new Historialguia();
        $this->guia = new Guia();
    }
    public $messagePrePro = "";
    public $id_guia = "";
    public $guia_estado_aprobacion;
    public $fechaHoraManual;
    public $fechaHoraManual2;
    public $fac_pre_prog_estado_aprobacion = "";
    public $fac_mov_area_motivo_rechazo = "";
    public $messageRecFactApro;

    public function render(){
        $facturas_pre_prog_estadox = $this->guia->listar_facturas_pre_programacion_estadox();
        return view('livewire.programacioncamiones.facturacion', compact('facturas_pre_prog_estadox'));
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
            $facturaPreprogramada = Guia::find($this->id_fac_pre_prog);

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
                'guia_estado_aprobacion' => 'required|in:0,8',
            ], [
                'id_guia.required' => 'El identificador es obligatorio.',
                'id_guia.integer' => 'El identificador debe ser un número entero.',
                'guia_estado_aprobacion.required' => 'El estado de la guía es obligatorio.',
                'guia_estado_aprobacion.in' => 'El estado de la guía debe ser Anulado (0) o Entregado (8).',
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
                $this->dispatch('hidemodalEditCambioEstado');
                session()->flash('success', 'La guía cambio de estado.');
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
}
