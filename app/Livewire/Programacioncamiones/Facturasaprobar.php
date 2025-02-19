<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Logs;
use App\Models\Facturamovimientoarea;
use App\Models\Facturaspreprogramacion;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Historialpreprogramacion;
use Carbon\Carbon;

class Facturasaprobar extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $facpreprog;
    private $facmovarea;
    private $historialpreprogramacion;
    public function __construct(){
        $this->logs = new Logs();
        $this->facpreprog = new Facturaspreprogramacion();
        $this->facmovarea = new Facturamovimientoarea();
        $this->historialpreprogramacion = new Historialpreprogramacion();
    }
    public $messagePrePro = "";
    public $id_fac_pre_prog = "";
    public $fac_pre_prog_estado_aprobacion = "";
    public $fac_mov_area_motivo_rechazo = "";
    public $messageRecFactApro;

    public function render(){
        $facturas_pre_prog_estado_dos = $this->facpreprog->listar_facturas_pre_programacion_estado_dos();
        return view('livewire.programacioncamiones.facturasaprobar', compact('facturas_pre_prog_estado_dos'));
    }

    public function cambio_estado($id_factura, $estado){
        $id = base64_decode($id_factura);
        if ($id) {
            $this->id_fac_pre_prog = $id;
            $this->fac_pre_prog_estado_aprobacion = $estado;
            $this->messagePrePro = "¿Está seguro de aceptar esta factura?";
        }
    }
    public function disable_pre_pro(){
        try {
            if (!Gate::allows('disable_pre_pro')) {
                session()->flash('error_pre_pro', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }
            $this->validate([
                'id_fac_pre_prog' => 'required|integer',
                'fac_pre_prog_estado_aprobacion' => 'required|integer',
            ], [
                'id_fac_pre_prog.required' => 'El identificador es obligatorio.',
                'id_fac_pre_prog.integer' => 'El identificador debe ser un número entero.',
                'fac_pre_prog_estado_aprobacion.required' => 'El estado es obligatorio.',
                'fac_pre_prog_estado_aprobacion.integer' => 'El estado debe ser un número entero.',
            ]);
            DB::beginTransaction();
            $factura = Facturaspreprogramacion::find($this->id_fac_pre_prog);

            if ($factura) {
                $factura->fac_pre_prog_estado_aprobacion = $this->fac_pre_prog_estado_aprobacion;

                if ($factura->save()) {
                    // Guardar en historial_pre_programacion
                    $historial = new Historialpreprogramacion();
                    $historial->id_fac_pre_prog = $this->id_fac_pre_prog;
                    $historial->fac_pre_prog_cfnumdoc = $factura->fac_pre_prog_cfnumdoc;
                    $historial->fac_pre_prog_estado_aprobacion = $this->fac_pre_prog_estado_aprobacion;
                    $historial->fac_pre_prog_estado = 1;
                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
                    $historial->save();

                    DB::commit();
                    $this->dispatch('hidemodalPrePro');
                    session()->flash('success', 'Factura aprobada.');
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
            session()->flash('error', 'Ocurrió un error al aceptar la factura. Por favor, inténtelo nuevamente.');
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
                    // Registrar en historial_pre_programacion
                    $historial = new Historialpreprogramacion();
                    $historial->id_fac_pre_prog = $this->id_fac_pre_prog;
                    $historial->fac_pre_prog_cfnumdoc = $facturaPreprogramada->fac_pre_prog_cfnumdoc;
                    $historial->fac_pre_prog_estado_aprobacion = 6;
                    $historial->fac_pre_prog_estado = 1;
                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
                    $historial->save();

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
}
