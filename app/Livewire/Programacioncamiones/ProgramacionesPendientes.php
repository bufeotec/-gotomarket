<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Despacho;
use App\Models\General;
use App\Models\Logs;
use App\Models\Programacion;
use App\Models\Transportista;
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
    /* ---------------------------------------- */
    private $logs;
    private $programacion;
    private $despacho;
    private $general;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->general = new General();
    }
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
    }

    public function render()
    {
        $resultado = $this->programacion->listar_programaciones_realizadas_x_fechas_x_estado($this->desde,$this->hasta,0);
        foreach ($resultado as $re){
            $totalVenta = 0;
            $re->despacho = DB::table('despachos as d')
                ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                ->join('tipo_servicios as ts','ts.id_tipo_servicios','=','d.id_tipo_servicios')
                ->where('d.id_programacion','=',$re->id_programacion)
                ->get();
            foreach ($re->despacho as $des){
                $des->comprobantes =  DB::table('despacho_ventas as dv')
                    ->where('id_despacho','=',$des->id_despacho)
                    ->get();
                foreach ($des->comprobantes as $com){
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $totalVenta+= round($precio,2);
                }
                $des->totalVentaDespacho = $totalVenta;
            }
        }
        return view('livewire.programacioncamiones.programaciones-pendientes',compact('resultado'));
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

}
