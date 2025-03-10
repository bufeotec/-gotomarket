<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Despacho;
use App\Models\General;
use App\Models\Logs;
use App\Models\Programacion;
use App\Models\Transportista;
use App\Models\Historialdespachoventa;
use App\Models\Serviciotransporte;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class ProgramacionesPendientes extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $desde;
    public $hasta;
    public $listar_detalle_despacho = [];
    public $id_progr = "";
    public $estadoPro = "";
    public $id_serv_transpt = "";
    public $serv_transpt_estado_aprobacion = "";
    /* ---------------------------------------- */
    private $logs;
    private $programacion;
    private $despacho;
    private $general;
    private $historialdespachoventa;
    private $serviciotransporte;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->general = new General();
        $this->historialdespachoventa = new Historialdespachoventa();
        $this->serviciotransporte = new Serviciotransporte();
    }
    public function mount()
    {
        $this->desde = Carbon::today()->toDateString(); // Fecha actual
        $this->hasta = Carbon::tomorrow()->toDateString(); // Un día después de la fecha actual
    }

    public function render()
    {
        $resultado = $this->programacion->listar_programaciones_realizadas_x_fechas_x_estado($this->desde,$this->hasta,0);
        foreach ($resultado as $re){
            $re->despacho = DB::table('despachos as d')
                ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                ->join('tipo_servicios as ts','ts.id_tipo_servicios','=','d.id_tipo_servicios')
                ->where('d.id_programacion','=',$re->id_programacion)
                ->get();
            foreach ($re->despacho as $des){
                $totalVenta = 0;
                $des->comprobantes =  DB::table('despacho_ventas as dv')
                    ->where('dv.id_despacho','=',$des->id_despacho)
                    ->get();
                foreach ($des->comprobantes as $com){
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $totalVenta+= round($precio,2);
                }
                $des->totalVentaDespacho = $totalVenta;
            }
        }
        // Obtener servicios de transporte
        $serviciosTransporte = DB::table('servicios_transportes as st')
            ->join('users as u', 'u.id_users', '=', 'st.id_users')
            ->select('st.*', 'u.name', 'u.last_name')
            ->where('st.serv_transpt_estado_aprobacion', '=', 0)
            ->whereBetween('st.serv_transpt_fecha_creacion', [$this->desde, $this->hasta])
            ->orderBy('st.created_at', 'desc')
            ->get();
        $conteoProgramacionesPend = DB::table('programaciones')->where('programacion_estado_aprobacion','=',0)->count();
        $conteoServicioTransporte = DB::table('servicios_transportes')->where('serv_transpt_estado_aprobacion','=',0)->count();
        return view('livewire.programacioncamiones.programaciones-pendientes',compact('resultado','conteoProgramacionesPend', 'serviciosTransporte', 'conteoServicioTransporte'));
    }

    public function listar_informacion_despacho($id){
        try {
            $this->listar_detalle_despacho = DB::table('despachos as d')
                ->join('users as u','u.id_users','=','d.id_users')
                ->where('d.id_despacho','=',$id)->first();
            if ($this->listar_detalle_despacho){
                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas')
                    ->where('id_despacho','=',$id)->get();
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
        }
    }

    public function cambiarEstadoProgramacion($id,$estado){ //  $estado = 1 aprobar , 2 desaprobar
        if ($id){
            $this->id_progr = $id;
            $this->estadoPro = $estado;
        }
    }
    public function cambiarEstadoProgramacionFormulario(){
        try {

            if (!Gate::allows('aprobar_rechazar_programacion')) {
                session()->flash('error_delete', 'No tiene permisos para aprobar o rechazar esta programación.');
                return;
            }
            $this->validate([
                'id_progr' => 'required|integer',
                'estadoPro' => 'required|integer',
            ], [
                'id_progr.required' => 'El identificador es obligatorio.',
                'id_progr.integer' => 'El identificador debe ser un número entero.',

                'estadoPro.required' => 'El estado es obligatorio.',
                'estadoPro.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $correlaApro = null;
            if ($this->estadoPro == 1){ // APROBACIÓN
                /* Listar ultima programación aprobada */
                $correlaApro = $this->programacion->listar_ultima_aprobacion();
            }
            $programacionUpdate = Programacion::find($this->id_progr);
            $programacionUpdate->id_users_programacion = Auth::id();
            $programacionUpdate->programacion_fecha_aprobacion = date('Y-m-d');
            $programacionUpdate->programacion_estado_aprobacion = $this->estadoPro == 4 ? 2 : 1;
            if ($correlaApro){
                $programacionUpdate->programacion_numero_correlativo = $correlaApro;
            }
            if ($programacionUpdate->save()) {
                // listar despachos realizados
                $despachos = DB::table('despachos')->where('id_programacion','=',$this->id_progr)->get();
                foreach ($despachos as $des){
                    $updateDespacho = Despacho::find($des->id_despacho);
                    $updateDespacho->id_users_programacion = Auth::id();
                    $updateDespacho->despacho_estado_aprobacion = $this->estadoPro;
                    if ($this->estadoPro == 1){
                        $correlaApro = $this->despacho->listar_ultima_aprobacion_despacho();
                        $updateDespacho->despacho_numero_correlativo = $correlaApro;
                    }
                    $updateDespacho->despacho_fecha_aprobacion = date('Y-m-d');
                    if (!$updateDespacho->save()){
                        DB::rollBack();
                        session()->flash('error_delete', 'No se pudo aprobar los despachos relacionados a la programación.');
                        return;
                    }
                    // Guardar historial de cambios
                    $historialDespacho = new Historialdespachoventa();
                    $historialDespacho->id_despacho = $des->id_despacho;
                    $historialDespacho->id_programacion = $this->id_progr;
                    $historialDespacho->programacion_estado_aprobacion = $this->estadoPro;
                    $historialDespacho->despacho_estado_aprobacion = $this->estadoPro;
                    $historialDespacho->his_desp_vent_fecha = Carbon::now('America/Lima');
                    if (!$historialDespacho->save()) {
                        DB::rollBack();
                        session()->flash('error_delete', 'No se pudo guardar el historial del despacho.');
                        return;
                    }
                }
                DB::commit();
                $this->dispatch('hideModalDelete');
                if ($this->estadoPro == 1){
                    session()->flash('success', 'Registro aprobado correctamente.');
                }else{
                    session()->flash('success', 'Registro rechazado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado de la programación.');
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al cambiar el estado del registro. Por favor, inténtelo nuevamente.');
        }
    }

    public function cambiarEstadoServicioTransp($id_ser_trn,$estado_aprob){ //  $estado = 1 aprobar , 2 desaprobar
        if ($id_ser_trn){
            $this->id_serv_transpt = $id_ser_trn;
            $this->serv_transpt_estado_aprobacion = $estado_aprob;
        }
    }

    public function cambiarEstadoServicioTranspFormulario() {
        try {
            if (!Gate::allows('aprobar_rechazar_servicio_transp')) {
                session()->flash('error_delete', 'No tiene permisos para aprobar o rechazar este servicio transporte.');
                return;
            }

            $this->validate([
                'id_serv_transpt' => 'required|integer',
                'serv_transpt_estado_aprobacion' => 'required|integer',
            ], [
                'id_serv_transpt.required' => 'El identificador es obligatorio.',
                'id_serv_transpt.integer' => 'El identificador debe ser un número entero.',
                'serv_transpt_estado_aprobacion.required' => 'El estado es obligatorio.',
                'serv_transpt_estado_aprobacion.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $servicio_transp_update = Serviciotransporte::find($this->id_serv_transpt);

            // Asignar el estado directamente sin modificarlo
            $servicio_transp_update->serv_transpt_estado_aprobacion = $this->serv_transpt_estado_aprobacion;

            if ($this->serv_transpt_estado_aprobacion == 1) { // APROBACIÓN
                $correlaApro = $this->despacho->listar_ultima_aprobacion_despacho();
                $servicio_transp_update->serv_transpt_codigo_os = $correlaApro;
            }

            if ($servicio_transp_update->save()) {
                DB::commit();
                $this->dispatch('hideModalDeleteSerTr');
                if ($this->serv_transpt_estado_aprobacion == 1) {
                    session()->flash('success', 'Registro aprobado correctamente.');
                } else {
                    session()->flash('success', 'Registro rechazado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado del servicio de transporte.');
                return;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al cambiar el estado del registro. Por favor, inténtelo nuevamente.');
        }
    }

}
