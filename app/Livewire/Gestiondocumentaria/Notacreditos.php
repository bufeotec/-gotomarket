<?php

namespace App\Livewire\Gestiondocumentaria;

use App\Models\Notacredito;
use App\Models\DespachoVenta;
use App\Models\Logs;
use PDF;
use App\Models\General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Notacreditos extends Component
{
    use WithPagination, WithoutUrlPagination;

    private $logs;
    private $general;
    private $notacredito;
    public $search_nc;
    public $pagination_nc = 10;
    public $id_nota_credito;
    public $id_despacho_venta;
    public $nota_credito_ruc_cliente;
    public $nota_credito_nombre_cliente;
    public $nota_credito_incidente_registro;
    public $nota_credito_motivo;
    public $nota_credito_fecha_emision;
    public $desde;
    public $hasta;
    public $filterRuc;
    public $filterMotivo;
    public $listar_nota_credito = [];

    public function __construct()
    {
        $this->logs = new Logs();
        $this->general = new General();
        $this->notacredito = new Notacredito();
    }

    public function mount()
    {
        $this->listar_nota_credito = Notacredito::with('despachoVenta')->get();
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
        $this->filterRuc = '';
        $this->filterMotivo = '';
        $this->applyFilters();

    }

    public function render()
    {
        $listar_nota_credito = $this->notacredito->listar_nota_credito($this->search_nc, $this->pagination_nc);
        $listar_despacho = $this->notacredito->listar_despacho();
        return view('livewire.gestiondocumentaria.notacredito', compact('listar_nota_credito', 'listar_despacho'));
    }

    public function clear_form_nc()
    {
        $this->id_nota_credito = "";
        $this->id_despacho_venta = "";
        $this->nota_credito_fecha_emision = "";
        $this->nota_credito_ruc_cliente = "";
        $this->nota_credito_incidente_registro = "";
        $this->nota_credito_nombre_cliente = "";
        $this->nota_credito_motivo = "";
    }

    public function saveNc()
    {
        try {
            $this->validate([
                'id_despacho_venta' => 'required|integer',
                'nota_credito_nombre_cliente' => 'required|string',
                'nota_credito_ruc_cliente' => 'required|string',
                'nota_credito_motivo' => 'required|integer',
            ], [
                'id_despacho_venta.required' => 'Debes seleccionar la factura de venta.',
                'nota_credito_nombre_cliente.required' => 'El nombre del cliente es obligatorio.',
                'nota_credito_ruc_cliente.required' => 'Debes ingresar el RUC del Cliente.',
                'nota_credito_motivo.required' => 'Debes seleccionar el motivo de la nota de crédito.',
            ]);

            DB::beginTransaction();

            $despachoVenta = DespachoVenta::find($this->id_despacho_venta);
            if ($despachoVenta) {
                $despachoVenta->despacho_detalle_estado_entrega = 5; // Cambia el estado a 5 (Anulado)
                $despachoVenta->save();
            }

            if (!$this->id_nota_credito) { // INSERTAR
                if (!Gate::allows('create_nc')) {
                    session()->flash('error', 'No tiene permisos para crear.');
                    return;
                }

                $notaCredito = new Notacredito();
                $notaCredito->id_users = Auth::id();
                $notaCredito->id_despacho_venta = $this->id_despacho_venta;
                // $notaCredito->nota_credito_fecha_emision = $this->nota_credito_fecha_emision;
                $notaCredito->nota_credito_ruc_cliente = $this->nota_credito_ruc_cliente; // Se mantiene para el registro
                $notaCredito->nota_credito_nombre_cliente = $this->nota_credito_nombre_cliente;
                $notaCredito->nota_credito_motivo = $this->nota_credito_motivo;
                // $notaCredito->nota_credito_incidente_registro = $this->nota_credito_incidente_registro;
                $notaCredito->save();
            } else { // ACTUALIZAR
                if (!Gate::allows('update_nc')) {
                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
                    return;
                }

                $notaCredito = Notacredito::findOrFail($this->id_nota_credito);
                $notaCredito->id_despacho_venta = $this->id_despacho_venta;
                // $notaCredito->nota_credito_fecha_emision = $this->nota_credito_fecha_emision;
                $notaCredito->nota_credito_ruc_cliente = $this->nota_credito_ruc_cliente; // Se mantiene para el registro
                $notaCredito->nota_credito_nombre_cliente = $this->nota_credito_nombre_cliente;
                $notaCredito->nota_credito_motivo = $this->nota_credito_motivo;
                // $notaCredito->nota_credito_incidente_registro = $this->nota_credito_incidente_registro;
                $notaCredito->save();
            }

            DB::commit();
            $this->dispatch('hideModal');
            session()->flash('success', 'Registro guardado correctamente.');

            // Actualiza la lista de notas de crédito
            $this->listar_nota_credito = Notacredito::all(); // Recarga la lista
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al procesar la solicitud. Por favor, inténtelo nuevamente.');
        }
    }

    public function updated($propertyName)
    {
        $this->applyFilters();
    }

    public function applyFilters()
    {
        $query = NotaCredito::query();

        if ($this->filterRuc) {
            $query->where('nota_credito_ruc_cliente', 'LIKE', '%' . $this->filterRuc . '%');
        }
        if ($this->filterMotivo) {
            $query->where('nota_credito_motivo', $this->filterMotivo);
        }
        if ($this->desde) {
            $query->whereDate('created_at', '>=', $this->desde);
        }
        if ($this->hasta) {
            $query->whereDate('created_at', '<=', $this->hasta);
        }

        $this->listar_nota_credito = $query->get();
    }
}
