<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Logs;
use App\Models\Facturaspreprogramacion;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Facturasaprobar extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $facpreprog;
    public function __construct(){
        $this->logs = new Logs();
        $this->facpreprog = new Facturaspreprogramacion();
    }
    public $messagePrePro = "";
    public $id_fac_pre_prog = "";
    public $fac_pre_prog_estado_aprobacion = "";

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
}
