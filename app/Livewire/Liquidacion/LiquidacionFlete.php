<?php

namespace App\Livewire\Liquidacion;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Transportista;
use App\Models\Despacho;
use App\Models\Liquidacion;
use App\Models\LiquidacionDetalles;
use App\Models\LiquidacionGastos;
use App\Models\General;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class LiquidacionFlete extends Component
{
    use WithFileUploads;
    private $logs;
    private $transportistas;
    private $despacho;
    private $liquidacion;
    private $liquidacionDetalle;
    private $liquidacionGasto;
    private $general;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->transportistas = new Transportista();
        $this->despacho = new Despacho();
        $this->liquidacion = new Liquidacion();
        $this->liquidacionDetalle = new LiquidacionDetalles();
        $this->liquidacionGasto = new LiquidacionGastos();
        $this->general = new General();
    }
    public $id_transportistas = '';
    public $liquidacion_serie = '';
    public $liquidacion_correlativo = '';
    public $liquidacion_ruta_comprobante = '';
    public $despachos = [];
    public $select_despachos = [];
    public $listar_detalle_despacho = [];
    public $gastos = [];
    public function render(){
        $listar_transportistas = $this->transportistas->listar_transportista_sin_id();
        return view('livewire.liquidacion.liquidacion-flete', compact('listar_transportistas'));
    }

    public function actualizarDespacho($idDespacho, $isChecked){
        if ($isChecked) {
            $this->select_despachos[$idDespacho] = true;
        } else {
            unset($this->select_despachos[$idDespacho]);
        }
    }

    public function seleccion_trans(){
        $value = $this->id_transportistas;

        if ($value) {
            $this->despachos = DB::table('despachos as d')
                ->join('transportistas as t', 'd.id_transportistas', '=', 't.id_transportistas')
                ->join('tipo_servicios as ts', 'd.id_tipo_servicios', '=', 'ts.id_tipo_servicios')
                ->where('d.id_transportistas', $value)
                ->where('d.despacho_estado', 1)
                ->where('d.despacho_estado_aprobacion','=',3)
                ->get();
            foreach ($this->despachos as $des) {
                $des->comprobantes = DB::table('despacho_ventas as dv')
                    ->where('id_despacho', '=', $des->id_despacho)
                    ->get();
                $totalVenta = 0;
                foreach ($des->comprobantes as $com) {
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $totalVenta += round($precio, 2);
                }
                $des->totalVentaDespacho = $totalVenta;
            }
        } else {
            $this->despachos = [];
        }
        $this->select_despachos = [];
        $this->liquidacion_serie = '';
        $this->liquidacion_correlativo = '';
        $this->liquidacion_ruta_comprobante = '';
    }

    public function agregarGasto($idDespacho){
        if (!isset($this->gastos[$idDespacho])) {
            $this->gastos[$idDespacho] = [];
        }
        $this->gastos[$idDespacho][] = [
            'concepto' => '',
            'monto' => '',
            'descripcion' => '',
        ];
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

    public function guardar_liquidacion(){
        try {
            // Validar los campos obligatorios
            $this->validate([
                'liquidacion_serie' => 'required|string',
                'liquidacion_correlativo' => 'required|string',
                'liquidacion_ruta_comprobante' => 'nullable|file|mimes:jpg,pdf,pdf,png|max:2048',
            ], [
                'liquidacion_serie.required' => 'La serie es obligatoria.',
                'liquidacion_serie.string' => 'La serie debe ser una cadena de texto.',

                'liquidacion_correlativo.required' => 'El correlatico es obligatoria.',
                'liquidacion_correlativo.string' => 'El correlatico debe ser una cadena de texto.',

                'liquidacion_ruta_comprobante.file' => 'Debe cargar un archivo válido.',
                'liquidacion_ruta_comprobante.mimes' => 'El archivo debe ser una imagen en formato JPG, JPEG o PNG.',
                'liquidacion_ruta_comprobante.max' => 'La imagen no puede exceder los 2MB.',
            ]);

            DB::beginTransaction();

            $validar = DB::table('liquidaciones')
                ->where('liquidacion_serie', '=', $this->liquidacion_serie)
                ->where('liquidacion_correlativo', '=', $this->liquidacion_correlativo)
                ->exists();
            if (!$validar){
                // GUARDA EN LA TABLA LIQUIDACION
                $liquidacion = new Liquidacion();
                $liquidacion->id_users = Auth::id();
                $liquidacion->id_transportistas = $this->id_transportistas;
                $liquidacion->liquidacion_serie = $this->liquidacion_serie;
                $liquidacion->liquidacion_correlativo = $this->liquidacion_correlativo;
                if ($this->liquidacion_ruta_comprobante) {
                    $liquidacion->liquidacion_ruta_comprobante = $this->general->save_files($this->liquidacion_ruta_comprobante, 'liquidacion/comprobantes');
                }
                $liquidacion->liquidacion_estado = 1;
                $liquidacion->liquidacion_microtime = microtime(true);
                $liquidacion->save();

                // GUARDA EN LA TABLA LIQUIDACION_DETALLES
                foreach ($this->select_despachos as $id_despacho => $isSelected) {
                    if ($isSelected) {
                        $detalle = new LiquidacionDetalles();
                        $detalle->id_liquidacion = $liquidacion->id_liquidacion;
                        $detalle->id_despacho = $id_despacho;
                        $detalle->liquidacion_detalle_estado = 1;
                        $detalle->liquidacion_detalle_microtime = microtime(true);
                        $detalle->save();

                        // GUARDA EN LA TABLA LIQUIDACION_GASTOS
                        if (isset($this->gastos[$id_despacho])) {
                            foreach ($this->gastos[$id_despacho] as $gasto) {
                                // Verificar que concepto y monto no estén vacíos
                                if (empty($gasto['concepto']) || empty($gasto['monto'])) {
                                    // Obtener el número de correlativo del despacho
                                    $despacho = DB::table('despachos')->where('id_despacho', $id_despacho)->first();
                                    // Mostrar el mensaje de error con el número de correlativo
                                    session()->flash('error', 'Existe un gasto con concepto o monto inválido para el despacho: ' . $despacho->despacho_numero_correlativo);
                                    return;
                                }
                                $gastoModel = new LiquidacionGastos();
                                $gastoModel->id_liquidacion_detalle = $detalle->id_liquidacion_detalle;
                                $gastoModel->liquidacion_gasto_concepto = $gasto['concepto'];
                                $gastoModel->liquidacion_gasto_monto = $gasto['monto'];
                                $gastoModel->liquidacion_gasto_descripcion = $gasto['descripcion'] ?: null;
                                $gastoModel->liquidacion_gasto_estado = 1;
                                $gastoModel->liquidacion_gasto_microtime = microtime(true);
                                $gastoModel->save();
                            }
                        }


                    }
                }
            } else{
                session()->flash('error', 'La serie y el correlativo ya existen.');
                return;
            }
            DB::commit(); // Confirmar la transacción si todo salió bien
            session()->flash('success', 'Liquidación guardada correctamente.');
            $this->limpiar_campos_liquidacion();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error inesperado. Por favor, inténtelo nuevamente.');
        }
    }

    public function limpiar_campos_liquidacion(){
        $this->id_transportistas = '';
        $this->liquidacion_serie = '';
        $this->liquidacion_correlativo = '';
        $this->liquidacion_ruta_comprobante = '';
        $this->despachos = [];
        $this->select_despachos = [];
        $this->listar_detalle_despacho = [];
        $this->gastos = [];
    }
}
