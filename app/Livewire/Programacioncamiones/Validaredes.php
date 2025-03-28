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

class Validaredes extends Component
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
    public $id_guia;
    public $guia_estado_aprobacion;
    public $fmanual;
    public $fac_mov_area_motivo_rechazo = "";
    public $guiaSeleccionada;
    public $messageRecFactApro;
    public $guiainfo = [];
    public $guia_detalle = [];
    public $selectedGuiaIds = [];

    public function render(){
        $facturas_pre_prog_estado_dos = $this->guia->listar_facturas_pre_programacion_estado_dos();
        return view('livewire.programacioncamiones.validaredes', compact('facturas_pre_prog_estado_dos'));
    }

    public function cambio_estado($id_factura = null, $estado = null){
        $this->fmanual = ''; // Inicializar la variable de fecha y hora manual

        if ($id_factura) {
            $id = base64_decode($id_factura);
            if ($id){
                $this->id_guia = $id;
            }
        }

        if ($estado){
            $this->guia_estado_aprobacion = $estado; // Asignar el estado de aprobación
        }
        // Obtener la fecha y hora actual en la zona horaria de Lima
        $fhactual = Carbon::now('America/Lima')->format('d/m/Y - h:i a');
        // Actualizar el mensaje con la fecha y hora actual
        $this->messagePrePro = "¿Estás seguro de enviar con fecha $fhactual?";
    }
    public function actualizarMensaje(){
        // Si hay una fecha y hora manual, usarla; de lo contrario, usar la fecha y hora actual
        $fechaHora = $this->fmanual
            ? Carbon::parse($this->fmanual, 'America/Lima')->format('d/m/Y - h:i a')
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
            if (count($this->selectedGuiaIds) > 0) {
                // Validar que al menos un checkbox esté seleccionado
                $this->validate([
                    'selectedGuiaIds' => 'required|array|min:1',
                ], [
                    'selectedGuiaIds.required' => 'Debe seleccionar al menos una opción.',
                    'selectedGuiaIds.array'    => 'La selección debe ser válida.',
                    'selectedGuiaIds.min'      => 'Debe seleccionar al menos una opción.',
                ]);
                DB::beginTransaction();
                foreach ($this->selectedGuiaIds as $select) {
                    $factura = Guia::find($select);
                    $factura->guia_estado_aprobacion = 3;
                    if ($factura->save()) {
                        // Registrar en historial guias
                        $historial = new Historialguia();
                        $historial->id_users = Auth::id();
                        $historial->id_guia = $select;
                        $historial->guia_nro_doc = $factura->guia_nro_doc;
                        $historial->historial_guia_estado_aprobacion = 3;
                        $historial->historial_guia_fecha_hora = Carbon::now('America/Lima');
                        $historial->historial_guia_estado = 1;
                        $historial->save();
                        // Buscar si ya existe un registro en la tabla facturas_mov
                        $facturaMov = DB::table('facturas_mov')
                            ->where('id_guia', $select)
                            ->first();

                        if ($facturaMov) {
                            // Actualizar el registro existente
                            DB::table('facturas_mov')
                                ->where('id_guia', $select)
                                ->update([
                                    'fac_acept_val_rec' => $this->fmanual ? Carbon::parse($this->fmanual, 'America/Lima') : Carbon::now('America/Lima'),
                                    'fac_env_ges_fac' => $this->fmanual ? Carbon::parse($this->fmanual, 'America/Lima') : Carbon::now('America/Lima'),
                                ]);
                        } else {
                            // Crear un nuevo registro en facturas_mov
                            DB::table('facturas_mov')->insert([
                                'id_fac_pre_prog' => $select,
                                'fac_acept_val_rec' => $this->fmanual ? Carbon::parse($this->fmanual, 'America/Lima') : Carbon::now('America/Lima'),
                                'fac_env_ges_fac' => $this->fmanual ? Carbon::parse($this->fmanual, 'America/Lima') : Carbon::now('America/Lima'),
                                'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
                            ]);
                        }

                        // Confirmar la transacción
                        DB::commit();

                        // Cerrar el modal y mostrar mensaje de éxito
                        $this->dispatch('hidemodalPrePro');
                        session()->flash('success', 'Factura aprobada.');
                    } else {
                        DB::rollBack();
                        session()->flash('error_pre_pro', 'No se pudo cambiar el estado de la factura.');
                    }
                }
                DB::commit();
                $this->selectedGuiaIds = [];
                $this->dispatch('hidemodalPrePro');
                session()->flash('success', 'Guías aprobadas correctamente.');
            } else {
                $this->validate([
                    'id_guia' => 'required|integer',
                ], [
                    'id_guia.required' => 'El identificador es obligatorio.',
                    'id_guia.integer' => 'El identificador debe ser un número entero.',
                ]);

                DB::beginTransaction();
                $factura = Guia::find($this->id_guia);
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
                                'fac_acept_val_rec' => $this->fmanual ? Carbon::parse($this->fmanual, 'America/Lima') : Carbon::now('America/Lima'),
                                'fac_env_ges_fac' => $this->fmanual ? Carbon::parse($this->fmanual, 'America/Lima') : Carbon::now('America/Lima'),
                            ]);
                    } else {
                        // Crear un nuevo registro en facturas_mov
                        DB::table('facturas_mov')->insert([
                            'id_fac_pre_prog' => $this->id_guia,
                            'fac_acept_val_rec' => $this->fmanual ? Carbon::parse($this->fmanual, 'America/Lima') : Carbon::now('America/Lima'),
                            'fac_env_ges_fac' => $this->fmanual ? Carbon::parse($this->fmanual, 'America/Lima') : Carbon::now('America/Lima'),
                            'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
                        ]);
                    }

                    // Confirmar la transacción
                    DB::commit();

                    // Cerrar el modal y mostrar mensaje de éxito
                    $this->dispatch('hidemodalPrePro');
                    session()->flash('success', 'Guia aprobada.');
                } else {
                    DB::rollBack();
                    session()->flash('error_pre_pro', 'No se pudo cambiar el estado de la guía.');
                }
                DB::commit();
                $this->id_guia = "";
                $this->dispatch('hidemodalPrePro');
                session()->flash('success', 'Guia aprobada correctamente.');
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
    public function modal_guia_info($id_guia) {
        $this->guiainfo = $this->guia->listar_guia_x_id($id_guia);
    }

    public function listar_detalle_guia($id_guia) {
        $this->guia_detalle = $this->guia->listar_guia_detalle_x_id($id_guia);
    }

}
