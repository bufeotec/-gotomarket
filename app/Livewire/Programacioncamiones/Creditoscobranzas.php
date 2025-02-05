<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Logs;
use App\Models\Facturaspreprogramacion;

class Creditoscobranzas extends Component
{
    private $logs;
    private $facpreprog;
    public function __construct(){
        $this->logs = new Logs();
        $this->facpreprog = new Facturaspreprogramacion();
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

    public function mount(){
        $this->desde = null;
        $this->hasta = null;
        $this->buscar_comprobantes();
    }

    public function render(){
        $this->facturasCreditoAprobadas = DB::table('facturas_pre_programaciones')
            ->where('fac_pre_prog_estado_aprobacion', 5)
            ->when($this->desde, function($query) {
                return $query->whereDate('created_at', '>=', $this->desde);
            })
            ->when($this->hasta, function($query) {
                return $query->whereDate('created_at', '<=', $this->hasta);
            })
            ->get();

        return view('livewire.programacioncamiones.creditoscobranzas');
    }

    public function buscar_comprobantes(){
        // Construir la consulta base
        $query = DB::table('facturas_pre_programaciones')
            ->where('fac_pre_prog_estado_aprobacion', 1);
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

    public function aceptar_fac_credito(){
        try {
            // Verifica permisos
            if (!Gate::allows('aceptar_fac_credito')) {
                session()->flash('error_clave', 'No tiene permisos para cambiar los estados del menú.');
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
}
