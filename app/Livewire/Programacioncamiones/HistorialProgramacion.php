<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Despacho;
use App\Models\DespachoVenta;
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

class HistorialProgramacion extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $desde;
    public $hasta;
    public $serie_correlativo;
    public $listar_detalle_despacho = [];
    public $id_despacho = "";
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
        $this->tipo_aprobacacion = null;
    }

    public function render()
    {
        $resultado = $this->programacion->listar_programaciones_historial_programacion($this->desde,$this->hasta,$this->serie_correlativo);
        foreach($resultado as $rehs){
            $totalVenta = 0;
            $rehs->despacho = DB::table('despachos as d')
                ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                ->join('tipo_servicios as ts','ts.id_tipo_servicios','=','d.id_tipo_servicios')
                ->where('d.id_programacion','=',$rehs->id_programacion)
                ->get();
            foreach ($rehs->despacho as $des){
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


        return view('livewire.programacioncamiones.historial-programacion',compact('resultado'));
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

    public function cambiarEstadoDespacho($id){ //  $estado = 1 aprobar , 2 desaprobar
        if ($id){
            $this->id_despacho = $id;
        }
    }
    public function cambiarEstadoDespachoFormulario(){
        try {

            if (!Gate::allows('cambiar_estado_despacho')) {
                session()->flash('error_delete', 'No tiene permisos para poder cambiar el estado del despacho.');
                return;
            }
            $this->validate([
                'id_despacho' => 'required|integer',
            ], [
                'id_despacho.required' => 'El identificador es obligatorio.',
                'id_despacho.integer' => 'El identificador debe ser un número entero.',
            ]);

            DB::beginTransaction();

            $updateDespacho = Despacho::find($this->id_despacho);
            $updateDespacho->despacho_estado_aprobacion = 2;
            if ($updateDespacho->save()){
                $existeComprobanteCamino = DB::table('despacho_ventas')->where('id_despacho', $this->id_despacho)->update(['despacho_detalle_estado_entrega'=>1]);
                if ($existeComprobanteCamino){
                    DB::commit();
                    $this->dispatch('hideModalDelete');
                    session()->flash('success', 'Despacho en camino.');
                }
            }else{
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado del despacho a "En Camino".');
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
    public function cambiarEstadoComprobante($id_comprobante,$estado){
        try {
            // Validar el estado recibido
            if (!in_array((int)$estado, [2, 3])) {
                session()->flash('errorComprobante', 'Estado inválido seleccionado.');
                return;
            }
            // $estado sebe contener el valor del select
            if (!Gate::allows('cambiar_estado_comprobante')) {
                session()->flash('errorComprobante', 'No tiene permisos para poder cambiar el estado del comprobante.');
                return;
            }
            if ($id_comprobante){
                DB::beginTransaction();
                $updateComprobante = DespachoVenta::find($id_comprobante);
                if (!$updateComprobante) {
                    DB::rollBack();
                    session()->flash('errorComprobante', 'Comprobante no encontrado.');
                    return;
                }
                $updateComprobante->despacho_detalle_estado_entrega = (int)$estado;
                if (!$updateComprobante->save()){
                    DB::rollBack();
                    session()->flash('errorComprobante', 'No se pudo cambiar el estado del comprobante.');
                    return;
                }
                // validar cambiar el estado del despacho
                $id_despacho = $updateComprobante->id_despacho;
                if ($id_despacho){
                    // validar si existe algún comprobante con estado 1 'En transito"
                    $existeComprobanteCamino = DespachoVenta::where('id_despacho', $id_despacho)
                        ->where('despacho_detalle_estado_entrega', 1)
                        ->exists();

                    if (!$existeComprobanteCamino){
                        // si no existe ninguno en transito cambiar a culminado
                        Despacho::where('id_despacho', $id_despacho)
                            ->update(['despacho_estado_aprobacion' => 3]);
                    }
                    DB::commit();
                    session()->flash('successComprobante', 'El estado del comprobante se cambió correctamente.');
                }

            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('errorComprobante', 'Ocurrió un error al cambiar el estado del registro. Por favor, inténtelo nuevamente.');
        }
    }

}
