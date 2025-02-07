<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Gate;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Logs;
use App\Models\DespachoVenta;
use App\Models\Notacredito;

class Notascreditos extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $despachoventa;
    private $notacredito;
    public function __construct(){
        $this->logs = new Logs();
        $this->despachoventa = new DespachoVenta();
        $this->notacredito = new Notacredito();
    }
    public $search_nota_credito;
    public $pagination_nota_credito = 10;
    public $id_nota_credito = "";
    public $id_despacho_venta = "";
    public $nota_credito_motivo = "";
    public $nota_credito_motivo_descripcion = "";
    public $nota_credito_estado = "";

    public function render(){
        $fac_despacho = $this->despachoventa->listar_despacho_nota_credito();
        $listar_nota_credito = $this->notacredito->listar_nota_credito_activo($this->search_nota_credito, $this->pagination_nota_credito);
        return view('livewire.programacioncamiones.notascreditos', compact('fac_despacho', 'listar_nota_credito'));
    }

    public function clear_form_nota_credito(){
        $this->id_nota_credito = "";
        $this->id_despacho_venta = "";
        $this->nota_credito_motivo = "";
        $this->nota_credito_motivo_descripcion = "";
    }

    public function saveNotaCredito() {
        try {
            $this->validate([
                'nota_credito_motivo' => 'required|integer',
                'nota_credito_motivo_descripcion' => 'required|string',
                'id_despacho_venta' => 'required|integer',
                'id_nota_credito' => 'nullable|integer',
            ], [
                'nota_credito_motivo.required' => 'Debes seleccionar un motivo.',
                'nota_credito_motivo.integer' => 'El motivo debe ser un número entero.',

                'nota_credito_motivo_descripcion.required' => 'La descripción es obligatoria.',
                'nota_credito_motivo_descripcion.string' => 'La descripción debe ser una cadena de texto.',

                'id_despacho_venta.required' => 'Debes seleccionar una factura.',
                'id_despacho_venta.integer' => 'La factura debe ser un número entero.',

                'id_nota_credito.integer' => 'El identificador debe ser un número entero.',
            ]);

            if (!$this->id_nota_credito) {
                if (!Gate::allows('create_nota_credito')) {
                    session()->flash('error', 'No tiene permisos para crear.');
                    return;
                }

                $microtime = microtime(true);
                DB::beginTransaction();
                $notacredito_save = new Notacredito();
                $notacredito_save->id_users = Auth::id();
                $notacredito_save->id_despacho_venta = $this->id_despacho_venta;
                $notacredito_save->nota_credito_motivo = $this->nota_credito_motivo;
                $notacredito_save->nota_credito_motivo_descripcion = $this->nota_credito_motivo_descripcion;
                $notacredito_save->nota_credito_estado = 1;
                $notacredito_save->nota_credito_microtime = $microtime;

                if ($notacredito_save->save()) {
                    // Actualizar el estado del despacho_venta
                    DB::table('despacho_ventas')
                        ->where('id_despacho_venta', $this->id_despacho_venta)
                        ->update(['despacho_detalle_estado_entrega' => 5]);

                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Registro guardado correctamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar.');
                    return;
                }
            } else { // UPDATE
                if (!Gate::allows('update')) {
                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
                    return;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro: ' . $e->getMessage());
        }
    }

}
