<?php

namespace App\Livewire\Liquidacion;

use App\Models\Programacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;
use App\Models\Liquidacion;
use App\Models\General;
use Livewire\WithFileUploads;

class LiquidacionesPendientes extends Component
{
    use WithFileUploads;
    private $logs;
    private $despacho;
    private $liquidacion;
    private $general;
    private $programacion;
    public function __construct(){
        $this->logs = new Logs();
        $this->despacho = new Despacho();
        $this->programacion = new Programacion();
        $this->liquidacion = new Liquidacion();
        $this->general = new General();
    }
    public $desde;
    public $hasta;
    public $search;
    public $listar_detalle_liquidacion = [];
    public $listar_detalle_despacho = [];
    public $guiasAsociadasDespachos = [];

    public $id_liquidacion = '';
    public $id_liqui = '';
    public $estado_liquidacion = '';
    public $liquidacion_ruta_comprobante = '';
    /* ----------------------------------------------- */
    public $id_liquidacion_observacion = '';
    public $liquidacion_observacion = '';
    /* ----------------------------------------------- */
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
    }
    public function render(){
        $resultado = $this->liquidacion->listar_liquidacion_pendientes($this->search,$this->desde, $this->hasta);
        return view('livewire.liquidacion.liquidaciones-pendientes', compact('resultado'));
    }
    public function listar_guias_despachos($id){
        try {
            $this->guiasAsociadasDespachos = DB::table('despacho_ventas as dv')
                ->select('dv.*','p.programacion_fecha')
                ->join('despachos as d','d.id_despacho','=','dv.id_despacho' )
                ->join('programaciones as p','p.id_programacion','=','d.id_programacion' )
                ->where('dv.id_despacho','=',$id)->get();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
        }
    }
    public function listar_informacion_liquidacion($id){
        try {
            $this->listar_detalle_liquidacion = DB::table('liquidaciones as l')
                ->join('users as u', 'l.id_users', '=', 'u.id_users')
                ->join('liquidacion_detalles as ld', 'ld.id_liquidacion', '=', 'l.id_liquidacion')
                ->join('despachos as d', 'ld.id_despacho', '=', 'd.id_despacho')
                ->where('l.id_liquidacion', '=', $id)
                ->get();
            // Asignar los gastos a cada detalle de liquidación
            $totalVenta = 0;
            foreach ($this->listar_detalle_liquidacion as $detalle) {
                $detalle->gastos = DB::table('liquidacion_detalles as ld')
                    ->join('liquidacion_gastos as lg', 'ld.id_liquidacion_detalle', '=', 'lg.id_liquidacion_detalle')
                    ->where('ld.id_liquidacion', '=', $id)
                    ->where('ld.id_despacho', '=', $detalle->id_despacho)
                    ->get();

                foreach ($detalle->gastos as $com){
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $totalVenta += round($precio, 2);
                }
                $detalle->totalVentaDespacho = $totalVenta;
            }

        } catch (\Exception $e) {
            // Registrar el error en los logs
            $this->logs->insertarLog($e);
        }
    }

    public function agregar_comprobante($id_liquidqcion){
        try {
            if ($id_liquidqcion){
                $id = $id_liquidqcion;
                $this->id_liquidacion = $id;
                $this->liquidacion_ruta_comprobante = '';
            }

        }catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return;
        }

    }

    public function guardar_comprobante_new(){
        try {
            if (!Gate::allows('guardar_comprobante_liquidacion')) {
                session()->flash('error', 'No tiene permisos para guardar el comprobante relacionado con la liquidación.');
                return;
            }
            $this->validate([
                'liquidacion_ruta_comprobante' => 'nullable|file|mimes:jpg,jpeg,pdf,png|max:2048',
            ], [
                'liquidacion_ruta_comprobante.file' => 'Debe cargar un archivo válido.',
                'liquidacion_ruta_comprobante.mimes' => 'El archivo debe ser JPG, JPEG, PNG o PDF.',
                'liquidacion_ruta_comprobante.max' => 'El archivo no puede exceder los 2MB.',
            ]);

            DB::beginTransaction();
            Log::info($this->id_liquidacion);
            $liquidacion = Liquidacion::find($this->id_liquidacion);
            if ($liquidacion) {
                if ($this->liquidacion_ruta_comprobante) {
                    $liquidacion->liquidacion_ruta_comprobante = $this->general->save_files($this->liquidacion_ruta_comprobante, 'liquidacion/comprobantes');
                }
                if ($liquidacion->save()) {
                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Comprobante agregado correctamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo agregar el comprobante.');
                }
            } else {
                DB::rollBack();
                session()->flash('error', 'Liquidación no encontrada.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el comprobante');
            return;
        }
    }

    public function cambiarEstadoLiquidacion($id,$estado){ //  $estado = 1 aprobar , 2 desaprobar
        if ($id){
            $this->id_liqui = $id;
            $this->estado_liquidacion = $estado;
        }
    }
    public function gestionObservacionLiquidacion($id){ //  $estado = 1 aprobar , 2 desaprobar
        if ($id){
            $inf = DB::table('liquidaciones')->where('id_liquidacion','=',$id)->first();
            if ($inf){
                $this->id_liquidacion_observacion = $id;
                $this->liquidacion_observacion = $inf->liquidacion_observaciones;
            }
        }
    }
    public function cambiarEstadoLiquidacionFormulario(){
        try {

            if (!Gate::allows('aprobar_rechazar_liquidacion')) {
                session()->flash('error_delete', 'No tiene permisos para aprobar o rechazar esta liquidación.');
                return;
            }
            $this->validate([
                'id_liqui' => 'required|integer',
                'estado_liquidacion' => 'required|integer',
            ], [
                'id_liqui.required' => 'El identificador es obligatorio.',
                'id_liqui.integer' => 'El identificador debe ser un número entero.',

                'estado_liquidacion.required' => 'El estado es obligatorio.',
                'estado_liquidacion.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $correlaApro = null;
            if ($this->estado_liquidacion == 1){ // APROBACIÓN
                /* Listar ultima programación aprobada */
                $correlaApro = $this->liquidacion->listar_ultima_aprobacion();
            }
            $liquidacionUpdate = Liquidacion::find($this->id_liqui);
            $liquidacionUpdate->liquidacion_id_users_aprobacion = Auth::id();
            $liquidacionUpdate->liquidacion_fecha_aprobacion = date('Y-m-d H:i:s');
            $liquidacionUpdate->liquidacion_estado_aprobacion = $this->estado_liquidacion;
            if ($correlaApro){
                $liquidacionUpdate->liquidacion_numero_correlativo = $correlaApro;
            }
            if ($liquidacionUpdate->save()) {
                if ($this->estado_liquidacion == 2){
                    $detalleLi = DB::table('liquidacion_detalles')->where('id_liquidacion','=',$this->id_liqui)->get();
                    foreach ($detalleLi as $de){
                        DB::table('despachos')->where('id_despacho','=',$de->id_despacho)->update(['despacho_liquidado'=>0]);
                    }
                }
                DB::commit();
                $this->dispatch('hideModalDeleteA');

                if ($this->estado_liquidacion == 1){
                    session()->flash('success', 'Registro aprobado correctamente.');
                }else{
                    session()->flash('success', 'Registro rechazado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado de la liquidación.');
                return;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
        }
    }
    public function saveObseracion(){
        try {
            if (!Gate::allows('guardar_observacion')) {
                session()->flash('error_delete', 'Usted no tiene los permisos necesarios para guardar la observación correspondiente a la liquidación.');
                return;
            }
            $this->validate([
                'id_liquidacion_observacion' => 'required|integer',
                'liquidacion_observacion' => 'nullable|string',
            ], [
                'id_liquidacion_observacion.required' => 'El identificador es obligatorio.',
                'id_liquidacion_observacion.integer' => 'El identificador debe ser un número entero.',

                'liquidacion_observacion.string' => 'La información ingresada debe ser un texto.',
            ]);

            DB::beginTransaction();

            $liquidacionUpdate = Liquidacion::find($this->id_liquidacion_observacion);
            $liquidacionUpdate->liquidacion_observaciones = $this->liquidacion_observacion;
            if ($liquidacionUpdate->save()) {
                DB::commit();
                $this->dispatch('hideModalLiquidacionOb');
                session()->flash('success', 'Registro actualizado correctamente.');
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo gestionar la observación.');
                return;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
        }
    }
    public function listar_informacion_despacho($id,$liquidacion){
        try {
            $this->listar_detalle_despacho = DB::table('liquidacion_detalles as ld')
                ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
                ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
                ->join('users as u','u.id_users','=','d.id_users')
                ->where('d.id_despacho','=',$id)
                ->where('ld.id_liquidacion','=',$liquidacion)
                ->first();
            if ($this->listar_detalle_despacho){
                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas')
                    ->where('id_despacho','=',$id)->get();

                $totalVenta = 0;
                $totalVentaRestar = 0;
                $totalPesoRestar = 0;
                foreach ($this->listar_detalle_despacho->comprobantes as $com) {
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $pesoMenos = $com->despacho_venta_total_kg;
                    $totalVenta += $precio;
                    if ($com->despacho_detalle_estado_entrega == 3){
                        $totalVentaRestar += $precio;
                        $totalPesoRestar += $pesoMenos;
                    }
                }
                $this->listar_detalle_despacho->totalVentaDespacho = $totalVenta;
                $this->listar_detalle_despacho->totalVentaNoEntregado = $totalVentaRestar;
                $this->listar_detalle_despacho->totalPesoNoEntregado = $totalPesoRestar;

            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
        }
    }

}