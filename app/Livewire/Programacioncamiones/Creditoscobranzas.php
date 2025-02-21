<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Logs;
use App\Models\Facturaspreprogramacion;
use Illuminate\Support\Facades\Auth;
use App\Models\Facturamovimientoarea;
use App\Models\Historialpreprogramacion;
use Carbon\Carbon;

class Creditoscobranzas extends Component
{
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
    public $desde;
    public $hasta;
    public $filteredFacturas = [];
    public $selectedFacturas = [];
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $importeTotalVenta = 0;
    public $id_fac_pre_prog  = "";
    public $fac_pre_prog_estado_aprobacion = "";
    public $fac_pre_pro_motivo_credito = "";
    public $messageMotCre;
    public $facturasCreditoAprobadas;
    public $messageMotReCre;
    public $fac_mov_area_motivo_rechazo = "";
    public $messageFacApro;

    public function mount(){
//        $this->desde = date('Y-m-d');
//        $this->hasta =  date('Y-m-d');
        $this->buscar_comprobantes();
    }

    public function render(){
        $this->facturasCreditoAprobadas = DB::table('facturas_pre_programaciones')
            ->where('fac_pre_prog_estado_aprobacion', 5)
            ->get();

        return view('livewire.programacioncamiones.creditoscobranzas');
    }

    public function buscar_comprobantes(){
        // Construir la consulta base
        $query = DB::table('facturas_pre_programaciones')
            ->where('fac_pre_prog_estado_aprobacion', 1)
            ->where('fac_pre_prog_estado', 1);
        // Aplicar filtros de fecha si están presentes
        if ($this->desde) {
            $query->whereDate('created_at', '>=', $this->desde);
        }
        if ($this->hasta) {
            $query->whereDate('created_at', '<=', $this->hasta);
        }
        // Obtener los resultados de la consulta
        $this->filteredFacturas = $query->get();
    }

    public function pre_mot_cre($id_fac){
        $id = base64_decode($id_fac);
        $this->fac_pre_pro_motivo_credito = "";
        if ($id) {
            $this->id_fac_pre_prog = $id;
            $this->messageMotCre = "¿Está seguro de aceptar esta factura?";
        }
    }

//    public function aceptar_fac_credito(){
//        try {
//            // Verifica permisos
//            if (!Gate::allows('aceptar_fac_credito')) {
//                session()->flash('error', 'No tiene permisos para cambiar los estados del menú.');
//                return;
//            }
//
//            // Iniciar transacción
//            DB::beginTransaction();
//
//            // Buscar la factura preprogramada por su ID
//            $facturaPreprogramada = Facturaspreprogramacion::find($this->id_fac_pre_prog);
//
//            if ($facturaPreprogramada) {
//                // Actualizar el estado de aprobación a 5
//                $facturaPreprogramada->fac_pre_prog_estado_aprobacion = 5;
//
//                // Guardar los cambios
//                if ($facturaPreprogramada->save()) {
//                    // Registrar en historial_pre_programacion
//                    $historial = new Historialpreprogramacion();
//                    $historial->id_fac_pre_prog = $this->id_fac_pre_prog;
//                    $historial->fac_pre_prog_cfnumdoc = $facturaPreprogramada->fac_pre_prog_cfnumdoc;
//                    $historial->fac_pre_prog_estado_aprobacion = 5;
//                    $historial->fac_pre_prog_estado = 1;
//                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
//                    $historial->save();
//
//                    DB::commit();
//                    session()->flash('success', 'Factura preprogramada aprobada correctamente.');
//                    $this->dispatch('hidemodalMotCre');
//                    $this->buscar_comprobantes();
//                } else {
//                    DB::rollBack();
//                    session()->flash('error', 'No se pudo actualizar el estado de la factura preprogramada.');
//                }
//            } else {
//                DB::rollBack();
//                session()->flash('error', 'No se encontró la factura preprogramada.');
//            }
//        } catch (\Exception $e) {
//            DB::rollBack();
//            $this->logs->insertarLog($e);
//            session()->flash('error', 'Ocurrió un error al aprobar la factura preprogramada.');
//        }
//    }
    public function aceptar_fac_credito(){
        try {
            // Verifica permisos
            if (!Gate::allows('aceptar_fac_credito')) {
                session()->flash('error', 'No tiene permisos para cambiar los estados del menú.');
                return;
            }

            // Iniciar transacción
            DB::beginTransaction();

            // Buscar la factura preprogramada por su ID
            $facturaPreprogramada = Facturaspreprogramacion::find($this->id_fac_pre_prog);

            if ($facturaPreprogramada) {
                // Actualizar el estado de aprobación a 5
                $facturaPreprogramada->fac_pre_prog_estado_aprobacion = 5;

                // Guardar los cambios
                if ($facturaPreprogramada->save()) {
                    // Registrar en historial_pre_programacion
                    $historial = new Historialpreprogramacion();
                    $historial->id_fac_pre_prog = $this->id_fac_pre_prog;
                    $historial->fac_pre_prog_cfnumdoc = $facturaPreprogramada->fac_pre_prog_cfnumdoc;
                    $historial->fac_pre_prog_estado_aprobacion = 5;
                    $historial->fac_pre_prog_estado = 1;
                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
                    $historial->save();

                    // Buscar el registro en la tabla facturas_mov
                    $facturaMov = DB::table('facturas_mov')
                        ->where('id_fac_pre_prog', $this->id_fac_pre_prog) // Asegúrate de usar el campo correcto
                        ->first();

                    if ($facturaMov) {
                        // Si existe, actualizar los campos
                        DB::table('facturas_mov')
                            ->where('id_fac_pre_prog', $this->id_fac_pre_prog)
                            ->update([
                                'fac_acept_valpago' => Carbon::now('America/Lima'), // Actualiza con la fecha actual
                            ]);
                    } else {
                        // Si no existe, crear un nuevo registro
                        DB::table('facturas_mov')->insert([
                            'id_fac_pre_prog' => $this->id_fac_pre_prog,
                            'fac_acept_valpago' => Carbon::now('America/Lima'), // Establecer la fecha de aceptación
                            'fac_envio_valpago' => Carbon::now('America/Lima'), // Establecer la fecha de envío
                            'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
                        ]);
                    }

                    DB::commit();
                    session()->flash('success', 'Factura preprogramada aprobada correctamente.');
                    $this->dispatch('hidemodalMotCre');
                    $this->buscar_comprobantes();
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo actualizar el estado de la factura preprogramada.');
                }
            } else {
                DB::rollBack();
                session()->flash('error', 'No se encontró la factura preprogramada.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al aprobar la factura preprogramada.');
        }
    }
    public function rech_mot_cre($id_fac){
        $id = base64_decode($id_fac);
        $this->fac_mov_area_motivo_rechazo = "";
        if ($id) {
            $this->id_fac_pre_prog = $id;
            $this->fac_mov_area_motivo_rechazo = "";
            $this->messageMotReCre = "¿Está seguro de rechazar esta factura?";
        }
    }

    public function rechazar_fac_credito(){
        try {
            // Verifica permisos
            if (!Gate::allows('rechazar_fac_credito')) {
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
                $facturaPreprogramada->fac_pre_prog_estado = 0;

                // Guardar cambios en la factura preprogramada
                if ($facturaPreprogramada->save()) {
                    // Guardar en historial_pre_programacion
                    $historial = new Historialpreprogramacion();
                    $historial->id_fac_pre_prog = $this->id_fac_pre_prog;
                    $historial->fac_pre_prog_cfnumdoc = $facturaPreprogramada->fac_pre_prog_cfnumdoc;
                    $historial->fac_pre_prog_estado = 0;
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
                        session()->flash('success', 'Factura preprogramada rechazada correctamente.');
                        $this->dispatch('hidemodalMotReCre');
                        $this->buscar_comprobantes();
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'No se pudo guardar el motivo de rechazo.');
                    }
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo actualizar el estado de la factura preprogramada.');
                }
            } else {
                DB::rollBack();
                session()->flash('error', 'No se encontró la factura preprogramada.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al rechazar la factura preprogramada.');
        }
    }

    public function enviar_fac_apro($id_fac){
        $id = base64_decode($id_fac);
        $this->fac_pre_pro_motivo_credito = "";
        if ($id) {
            $this->id_fac_pre_prog = $id;
            $this->messageFacApro = "¿Está seguro de enviar a estados de facturación?";
        }
    }

    public function enviar_facturas_aprobrar(){
        try {
            // Verifica permisos
            if (!Gate::allows('enviar_facturas_aprobrar')) {
                session()->flash('error', 'No tiene permisos para cambiar los estados del menú.');
                return;
            }

            // Iniciar transacción
            DB::beginTransaction();

            // Buscar la factura preprogramada por su ID
            $facturaPreprogramada = Facturaspreprogramacion::find($this->id_fac_pre_prog);

            if ($facturaPreprogramada) {
                // Actualizar el estado de aprobación a 2
                $facturaPreprogramada->fac_pre_prog_estado_aprobacion = 6;

                // Guardar los cambios
                if ($facturaPreprogramada->save()) {
                    // Guardar en historial_pre_programacion
                    $historial = new Historialpreprogramacion();
                    $historial->id_fac_pre_prog = $this->id_fac_pre_prog;
                    $historial->fac_pre_prog_cfnumdoc = $facturaPreprogramada->fac_pre_prog_cfnumdoc;
                    $historial->fac_pre_prog_estado_aprobacion = 6;
                    $historial->fac_pre_prog_estado = 1;
                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
                    $historial->save();
                    // Buscar el registro en la tabla facturas_mov
                    $facturaMov = DB::table('facturas_mov')
                        ->where('id_fac_pre_prog', $this->id_fac_pre_prog) // Asegúrate de usar el campo correcto
                        ->first();

                    if ($facturaMov) {
                        // Si existe, actualizar los campos
                        DB::table('facturas_mov')
                            ->where('id_fac_pre_prog', $this->id_fac_pre_prog)
                            ->update([
                                'fac_envio_est_fac' => Carbon::now('America/Lima'), // Actualiza con la fecha actual
                            ]);
                    } else {
                        // Si no existe, crear un nuevo registro
                        DB::table('facturas_mov')->insert([
                            'id_fac_pre_prog' => $this->id_fac_pre_prog,
                            'fac_envio_est_fac' => Carbon::now('America/Lima'), // Establecer la fecha de aceptación
                            'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
                        ]);
                    }

                    DB::commit();
                    session()->flash('success', 'Factura enviada.');
                    $this->dispatch('hidemodalFacApro');
                    $this->buscar_comprobantes();
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo enviar la factura.');
                }
            } else {
                DB::rollBack();
                session()->flash('error', 'No se encontró la factura.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al enviar la factura.');
        }
    }
}
