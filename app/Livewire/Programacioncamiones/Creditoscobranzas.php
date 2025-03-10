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
    public $fechaHoraManual2 = "";
    public $fechaHoraManual3 = "";

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
        $this->fechaHoraManual3 = '';
        $id = base64_decode($id_fac);
        $this->fac_pre_pro_motivo_credito = "";
        if ($id) {
            $this->id_fac_pre_prog = $id;
            $fechaHoraActual3 = Carbon::now('America/Lima')->format('d/m/Y - h:i a');
            $this->messageMotCre = "¿Está seguro de aceptar esta Guía con fecha $fechaHoraActual3?";
        }
    }
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
                                'fac_acept_valpago' =>  $this->fechaHoraManual3 ? Carbon::parse($this->fechaHoraManual3, 'America/Lima') : Carbon::now('America/Lima'),
//                                'fac_envio_est_fac' => Carbon::now('America/Lima'), // Actualiza con la fecha actual
                            ]);
                    } else {
                        // Si no existe, crear un nuevo registro
                        DB::table('facturas_mov')->insert([
                            'id_fac_pre_prog' => $this->id_fac_pre_prog,
                            'fac_acept_valpago' =>  $this->fechaHoraManual3 ? Carbon::parse($this->fechaHoraManual3, 'America/Lima') : Carbon::now('America/Lima'),
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

//Documentos seleccionados
    public function enviar_fac_apro($id_fac){
        $this->fechaHoraManual2 = '';
        $id = base64_decode($id_fac);
        $this->fac_pre_pro_motivo_credito = "";
        if ($id) {
            $this->id_fac_pre_prog = $id;
            $fechaHoraActual2 = Carbon::now('America/Lima')->format('d/m/Y - h:i a');
            $this->messageFacApro = "¿Está seguro de enviar a estados de facturación con fecha $fechaHoraActual2?";
        }
    }
    public function actualizarMensaje()
    {
        // Si hay una fecha y hora manual, usarla; de lo contrario, usar la fecha y hora actual
        $fechaHora = $this->fechaHoraManual2
            ? Carbon::parse($this->fechaHoraManual2, 'America/Lima')->format('d/m/Y - h:i a')
            : Carbon::now('America/Lima')->format('d/m/Y - h:i a');

        // Actualizar el mensaje con la nueva fecha y hora
        $this->messageFacApro = "¿Estás seguro de enviar con fecha $fechaHora?";
    }
    public function actualizarMensaje2()
    {
        // Si hay una fecha y hora manual, usarla; de lo contrario, usar la fecha y hora actual
        $fhora = $this->fechaHoraManual3
            ? Carbon::parse($this->fechaHoraManual3, 'America/Lima')->format('d/m/Y - h:i a')
            : Carbon::now('America/Lima')->format('d/m/Y - h:i a');

        // Actualizar el mensaje con la nueva fecha y hora
        $this->messageMotCre = "¿Estás seguro de enviar con fecha $fhora?";
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
                                'fac_envio_est_fac' =>  $this->fechaHoraManual2 ? Carbon::parse($this->fechaHoraManual2, 'America/Lima') : Carbon::now('America/Lima'),
                            ]);
                    } else {
                        // Si no existe, crear un nuevo registro
                        DB::table('facturas_mov')->insert([
                            'id_fac_pre_prog' => $this->id_fac_pre_prog,
                            'fac_acept_valpago' => $this->fechaHoraManual2 ? Carbon::parse($this->fechaHoraManual2, 'America/Lima') : Carbon::now('America/Lima'),
                            'fac_envio_valpago' =>  $this->fechaHoraManual2 ? Carbon::parse($this->fechaHoraManual2, 'America/Lima') : Carbon::now('America/Lima'),
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
