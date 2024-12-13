<?php

namespace App\Livewire\Liquidacion;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;
use App\Models\Liquidacion;
use App\Models\General;
use Livewire\WithFileUploads;

class HistorialLiquidacion extends Component
{
    use WithFileUploads;
    private $logs;
    private $despacho;
    private $liquidacion;
    private $general;
    public function __construct(){
        $this->logs = new Logs();
        $this->despacho = new Despacho();
        $this->liquidacion = new Liquidacion();
        $this->general = new General();
    }
    public $desde;
    public $hasta;
    public $listar_detalle_liquidacion = [];
    public $id_liquidacion = '';
    public $liquidacion_ruta_comprobante = '';
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
    }
    public function render(){
        $resultado = $this->liquidacion->listar_liquidacion($this->desde, $this->hasta);
        return view('livewire.liquidacion.historial-liquidacion', compact('resultado'));
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
        $id = base64_decode($id_liquidqcion);
        $this->id_liquidacion = $id;
        $this->liquidacion_ruta_comprobante = '';
    }

    public function guardar_comprobante(){
        $this->validate([
            'liquidacion_ruta_comprobante' => 'nullable|file|mimes:jpg,jpeg,pdf,png|max:2048',
        ], [
            'liquidacion_ruta_comprobante.file' => 'Debe cargar un archivo válido.',
            'liquidacion_ruta_comprobante.mimes' => 'El archivo debe ser JPG, JPEG, PNG o PDF.',
            'liquidacion_ruta_comprobante.max' => 'El archivo no puede exceder los 2MB.',
        ]);

        try {
            DB::beginTransaction();

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
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al guardar el comprobante: ' . $e->getMessage());
        }
    }

}
