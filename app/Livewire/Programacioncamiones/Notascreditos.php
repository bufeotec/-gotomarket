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
    public $messageNotCret;
    public $despacho_venta;
    public $search_factura = "";
    protected $listeners = ['refreshComponent' => 'render'];

    public function render(){
        $fac_despacho = $this->despachoventa->listar_despacho_nota_credito($this->id_despacho_venta);
        $listar_nota_credito = $this->notacredito->listar_nota_credito_activo($this->search_nota_credito, $this->pagination_nota_credito);
        return view('livewire.programacioncamiones.notascreditos', compact('listar_nota_credito', 'fac_despacho'));
    }

    public function clear_form_nota_credito(){
        $this->id_nota_credito = "";
        $this->id_despacho_venta = "";
        $this->nota_credito_motivo = "";
        $this->nota_credito_motivo_descripcion = "";
    }

    public function edit_data($id){
        $notaCredEdit = Notacredito::find(base64_decode($id));
        if ($notaCredEdit) {
            $this->id_despacho_venta = $notaCredEdit->id_despacho_venta;
            $this->nota_credito_motivo = $notaCredEdit->nota_credito_motivo;
            $this->nota_credito_motivo_descripcion = $notaCredEdit->nota_credito_motivo_descripcion;
            $this->id_nota_credito = $notaCredEdit->id_nota_credito;

            $this->dispatch('updateSelect2', value: $notaCredEdit->id_despacho_venta);
        }
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
                $notacredito_save->nota_credito_estado_aprobacion = 0;

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
                if (!Gate::allows('update_nota_credito')) {
                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
                    return;
                }
                DB::beginTransaction();
                $notaCredito_update = Notacredito::findOrFail($this->id_nota_credito);

                // Verificar si el estado de aprobación es 1 y si los campos relevantes han cambiado
                if (
                    $notaCredito_update->nota_credito_estado_aprobacion == 1 &&
                    ($notaCredito_update->nota_credito_motivo != $this->nota_credito_motivo ||
                        $notaCredito_update->nota_credito_motivo_descripcion != $this->nota_credito_motivo_descripcion)
                ) {
                    $notaCredito_update->nota_credito_estado_aprobacion = 0;
                }

                $notaCredito_update->id_despacho_venta = $this->id_despacho_venta;
                $notaCredito_update->nota_credito_motivo = $this->nota_credito_motivo;
                $notaCredito_update->nota_credito_motivo_descripcion = $this->nota_credito_motivo_descripcion;

                if (!$notaCredito_update->save()) {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo actualizar el registro.');
                    return;
                }

                DB::commit();
                $this->dispatch('hideModal');
                session()->flash('success', 'Registro actualizado correctamente.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro: ' . $e->getMessage());
        }
    }

    public function cambio_estado($id_notCre){
        $id = base64_decode($id_notCre);
        if ($id) {
            $this->id_nota_credito = $id;
            $this->messageNotCret = "¿Está seguro de aprobar esta nota de credito?";
        }
    }

    public function cambiar_estado_aprobacion(){
        try {
            if (!Gate::allows('cambiar_estado_aprobacion')) {
                session()->flash('error_pre_pro', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }
            $this->validate([
                'id_nota_credito' => 'required|integer',
            ], [
                'id_nota_credito.required' => 'El identificador es obligatorio.',
                'id_nota_credito.integer' => 'El identificador debe ser un número entero.',
            ]);
            DB::beginTransaction();
            $factura = Notacredito::find($this->id_nota_credito);

            if ($factura) {
                $factura->nota_credito_estado_aprobacion = 1;

                if ($factura->save()) {
                    DB::commit();
                    $this->dispatch('hideModalDelete');
                    session()->flash('success', 'Nota de credito aprobada.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo cambiar el estado de la factura.');
                }
            } else {
                DB::rollBack();
                session()->flash('error', 'La nota de credito no existe.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al aceptar la factura. Por favor, inténtelo nuevamente.');
        }
    }

}
