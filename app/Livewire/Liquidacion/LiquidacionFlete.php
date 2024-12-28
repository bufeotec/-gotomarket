<?php

namespace App\Livewire\Liquidacion;

use Illuminate\Support\Facades\Gate;
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
    public $id_liquidacion_edit = '';
    public $id_transportistas = '';
    public $liquidacion_serie = '';
    public $liquidacion_correlativo = '';
    public $liquidacion_ruta_comprobante = '';
    public $date_desde = '';
    public $date_hasta = '';
    public $selectTipoSerivicio = '';
    public $despachos = [];
    public $select_despachos = [];
    public $listar_detalle_despacho = [];
    public $gastos = [];
    public $guiasAsociadasDespachos = [];
    public function mount($id = null){
        if (!$id){
            $this->date_desde = date('Y-m-d', strtotime('-1 week'));
            $this->date_hasta = date('Y-m-d');
        }else{ // cuando hay id es para editar
            $informacacionLiquidacion  = $this->liquidacion->listar_liquidacion_id($id);
            if ($informacacionLiquidacion){
                $this->id_liquidacion_edit = $id;
                $this->id_transportistas = $informacacionLiquidacion->id_transportistas;
                $this->seleccion_trans_edit();
                $this->liquidacion_serie = $informacacionLiquidacion->liquidacion_serie;
                $this->liquidacion_correlativo = $informacacionLiquidacion->liquidacion_correlativo;
                foreach ($informacacionLiquidacion->detalle as $in){
                    $this->select_despachos[$in->id_despacho] = [
                        'id_despacho' => $in->id_despacho,
                        'comentarios' => $in->liquidacion_detalle_comentarios,
                        'gastos' => [
                            'costo_flete' => [
                                'valor' => $in->gastos[0]->liquidacion_gasto_monto,
                                'descripcion' => $in->gastos[0]->liquidacion_gasto_descripcion,
                            ],
                            'mano_obra' => [
                                'valor' => $in->gastos[1]->liquidacion_gasto_monto,
                                'descripcion' => $in->gastos[1]->liquidacion_gasto_descripcion,
                            ],
                            'otros_gasto' => [
                                'valor' => $in->gastos[2]->liquidacion_gasto_monto,
                                'descripcion' => $in->gastos[2]->liquidacion_gasto_descripcion,
                            ],
                            'peso_final_kilos' => [
                                'valor' => $in->gastos[3]->liquidacion_gasto_monto,
                                'descripcion' => $in->gastos[3]->liquidacion_gasto_descripcion,
                            ],
                        ],
                    ];
                }
            }
        }

    }
    public function render(){
        $listar_transportistas = $this->transportistas->listar_transportista_sin_id();
        $listar_tipos_servicios = DB::table('tipo_servicios')->where('tipo_servicio_estado','=',1)->limit(2)->get();
        return view('livewire.liquidacion.liquidacion-flete', compact('listar_transportistas','listar_tipos_servicios'));
    }

    public function actualizarDespacho($idDespacho, $isChecked){
        if ($isChecked) {
            $despachoInfo = DB::table('despachos')->where('id_despacho','=',$idDespacho)->first();
            $despachoInfo->comprobantes = DB::table('despacho_ventas as dv')->where('id_despacho', '=', $despachoInfo->id_despacho)->get();
            $totalVenta = 0;
            $totalVentaRestar = 0;
            $totalPesoRestar = 0;
            foreach ($despachoInfo->comprobantes as $com) {
                $precio = floatval($com->despacho_venta_cfimporte);
                $pesoMenos = $com->despacho_venta_total_kg;
                $totalVenta += $precio;
                if ($com->despacho_detalle_estado_entrega == 3){
                    $totalVentaRestar += $precio;
                    $totalPesoRestar += $pesoMenos;
                }
            }
            $despachoInfo->totalVentaDespacho = $totalVenta;
            $despachoInfo->totalVentaNoEntregado = $totalVentaRestar;
            $despachoInfo->totalPesoNoEntregado = $totalPesoRestar;
            /* ------------------------------------------------------ */
            $totalPesoDespacho = $despachoInfo->despacho_peso;
            if ($despachoInfo->totalPesoNoEntregado){
                $totalPesoDespacho = $despachoInfo->despacho_peso - $despachoInfo->totalPesoNoEntregado;
            }
            $totalPesoDespacho = floor($totalPesoDespacho * 100) / 100;
            $this->select_despachos[$idDespacho] = [
                'id_despacho' => $idDespacho,
                'comentarios' => "",
                'gastos' => [
                    'costo_flete' => [
                        'valor' => $despachoInfo->despacho_monto_modificado ? $despachoInfo->despacho_monto_modificado : 0,
                        'descripcion' => '',
                    ],
                    'mano_obra' => [
                        'valor' => $despachoInfo->despacho_ayudante ? $despachoInfo->despacho_ayudante : 0,
                        'descripcion' => '',
                    ],
                    'otros_gasto' => [
                        'valor' => $despachoInfo->despacho_gasto_otros ? $despachoInfo->despacho_gasto_otros : 0,
                        'descripcion' => '',
                    ],
                    'peso_final_kilos' => [
                        'valor' => $totalPesoDespacho,
                        'descripcion' => '',
                    ],
                ],
            ];
        } else {
            unset($this->select_despachos[$idDespacho]);
        }
    }

    public function seleccion_trans(){
        $value = $this->id_transportistas;
        if ($value) {
            $queryConsult = DB::table('despachos as d')
                ->join('programaciones as pr', 'pr.id_programacion', '=', 'd.id_programacion')
                ->join('transportistas as t', 'd.id_transportistas', '=', 't.id_transportistas')
                ->join('tipo_servicios as ts', 'd.id_tipo_servicios', '=', 'ts.id_tipo_servicios')
                ->where('d.id_transportistas', $value)
                ->where('d.despacho_liquidado', '=',0)
                ->where('d.despacho_estado', 1)
                ->where('d.despacho_estado_aprobacion','=',3);
            if ($this->date_desde && $this->date_hasta) {
                $queryConsult->whereBetween('pr.programacion_fecha', [$this->date_desde, $this->date_hasta]);
            }
            if ($this->selectTipoSerivicio) {
                $queryConsult->where('d.id_tipo_servicios','=',$this->selectTipoSerivicio);
            }
            $queryConsult = $queryConsult->orderBy('d.despacho_fecha_aprobacion','desc')->get();

            $this->despachos = $queryConsult;


            foreach ($this->despachos as $des) {
                $des->comprobantes = DB::table('despacho_ventas as dv')
                    ->where('id_despacho', '=', $des->id_despacho)
                    ->get();
                $totalVenta = 0;
                $totalVentaRestar = 0;
                $totalPesoRestar = 0;
                foreach ($des->comprobantes as $com) {
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $pesoMenos = $com->despacho_venta_total_kg;
                    $totalVenta += $precio;
                    if ($com->despacho_detalle_estado_entrega == 3){
                        $totalVentaRestar += $precio;
                        $totalPesoRestar += $pesoMenos;
                    }
                }
                $des->totalVentaDespacho = $totalVenta;
                $des->totalVentaNoEntregado = $totalVentaRestar;
                $des->totalPesoNoEntregado = $totalPesoRestar;
            }
        } else {
            $this->despachos = [];
        }
        $this->select_despachos = [];
        $this->liquidacion_serie = '';
        $this->liquidacion_correlativo = '';
        $this->liquidacion_ruta_comprobante = '';
    }
    public function seleccion_trans_edit(){
        $value = $this->id_transportistas;
        if ($value) {

        $id_despachos_editar = [];
        $detalleEdi = DB::table('liquidacion_detalles')->where('id_liquidacion', '=', $this->id_liquidacion_edit)->get();
        foreach ($detalleEdi as $item) {
            $id_despachos_editar[] = $item->id_despacho;
        }

        $queryConsult = DB::table('despachos as d')
            ->join('programaciones as pr', 'pr.id_programacion', '=', 'd.id_programacion')
            ->join('transportistas as t', 'd.id_transportistas', '=', 't.id_transportistas')
            ->join('tipo_servicios as ts', 'd.id_tipo_servicios', '=', 'ts.id_tipo_servicios')
            ->where(function ($query) use ($value) {
                $query->where('d.id_transportistas', $value)
                    ->where('d.despacho_estado', 1)
                    ->where('d.despacho_estado_aprobacion', '=', 3);
            })
            ->where(function ($query) use ($id_despachos_editar) {
                $query->where('d.despacho_liquidado', '=', 0)
                    ->orWhere(function ($subQuery) use ($id_despachos_editar) {
                        $subQuery->where('d.despacho_liquidado', '=', 1)
                            ->whereIn('d.id_despacho', $id_despachos_editar);
                    });
            });
            if ($this->date_desde && $this->date_hasta) {
                $queryConsult->whereBetween('pr.programacion_fecha', [$this->date_desde, $this->date_hasta]);
            }
            if ($this->selectTipoSerivicio) {
                $queryConsult->where('d.id_tipo_servicios','=',$this->selectTipoSerivicio);
            }
            $queryConsult = $queryConsult->orderBy('d.despacho_fecha_aprobacion','desc')->get();

            $this->despachos = $queryConsult;


            foreach ($this->despachos as $des) {
                $des->comprobantes = DB::table('despacho_ventas as dv')
                    ->where('id_despacho', '=', $des->id_despacho)
                    ->get();
                $totalVenta = 0;
                $totalVentaRestar = 0;
                $totalPesoRestar = 0;
                foreach ($des->comprobantes as $com) {
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $pesoMenos = $com->despacho_venta_total_kg;
                    $totalVenta += $precio;
                    if ($com->despacho_detalle_estado_entrega == 3){
                        $totalVentaRestar += $precio;
                        $totalPesoRestar += $pesoMenos;
                    }
                }
                $des->totalVentaDespacho = $totalVenta;
                $des->totalVentaNoEntregado = $totalVentaRestar;
                $des->totalPesoNoEntregado = $totalPesoRestar;
            }
        }
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

    public function eliminarGasto($idDespacho, $index) {
        // Verificar si el despacho y el índice existen
        if (isset($this->gastos[$idDespacho][$index])) {
            unset($this->gastos[$idDespacho][$index]);
            $this->gastos[$idDespacho] = array_values($this->gastos[$idDespacho]);
        }
    }

    public function listar_informacion_despacho($id){
        try {
            $this->listar_detalle_despacho = DB::table('despachos as d')
                ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
                ->join('users as u','u.id_users','=','d.id_users')
                ->where('d.id_despacho','=',$id)->first();
            if ($this->listar_detalle_despacho){
                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas')
                    ->where('id_despacho','=',$id)->get();

                $totalVenta = 0;
                foreach ($this->listar_detalle_despacho->comprobantes as $com) {
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $totalVenta += $precio;
                }
                $this->listar_detalle_despacho->totalVentaDespacho = $totalVenta;
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
        }
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
    public function guardar_liquidacion(){
        try {
            if (!Gate::allows('guardar_liquidacion')) {
                session()->flash('error', 'No tiene permisos para guardar una liquidación.');
                return;
            }
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
                ->where('liquidacion_correlativo', '=', $this->liquidacion_correlativo);
                if ($this->id_liquidacion_edit){
                    $validar->where('id_liquidacion', '<>', $this->id_liquidacion_edit);
                }
            $validar = $validar->exists();
            if (!$validar){
                if ($this->id_liquidacion_edit){
                    DB::table('liquidacion_detalles as ld')
                        ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
                        ->where('ld.id_liquidacion','=',$this->id_liquidacion_edit)
                        ->update(['d.despacho_liquidado'=>0]);

                    // Obtiene todos los detalles relacionados con la liquidación
                    $liquidacionDetalles = Liquidacion::find($this->id_liquidacion_edit)->detalles;
                    // Elimina los gastos relacionados con cada detalle
                    foreach ($liquidacionDetalles as $detalle) {
                        // Elimina todos los gastos relacionados con el detalle
                        $detalle->gastos()->delete();
                    }
                    // Elimina todos los detalles relacionados con la liquidación
                    foreach ($liquidacionDetalles as $detalle) {
                        $detalle->delete();
                    }

                    $liquidacion = Liquidacion::find($this->id_liquidacion_edit);
                }else{
                    $liquidacion = new Liquidacion();
                    $liquidacion->id_users = Auth::id();
                }
                $liquidacion->id_transportistas = $this->id_transportistas;
                $liquidacion->liquidacion_serie = $this->liquidacion_serie;
                $liquidacion->liquidacion_correlativo = $this->liquidacion_correlativo;
                if ($this->liquidacion_ruta_comprobante) {
                    $liquidacion->liquidacion_ruta_comprobante = $this->general->save_files($this->liquidacion_ruta_comprobante, 'liquidacion/comprobantes');
                }
                $liquidacion->liquidacion_estado = 1;
                $liquidacion->liquidacion_estado_aprobacion = 0;
                $liquidacion->liquidacion_microtime = microtime(true);
                if ($liquidacion->save()){
                    //                    // GUARDA EN LA TABLA LIQUIDACION_DETALLES
                    foreach ($this->select_despachos as $id_despacho => $despachoData) {
                        $conteoGastos = isset($despachoData['gastos']) && is_array($despachoData['gastos'])
                            ? count($despachoData['gastos'])
                            : 0;
                        if ($conteoGastos > 0){
                            $detalle = new LiquidacionDetalles();
                            $detalle->id_liquidacion = $liquidacion->id_liquidacion;
                            $detalle->id_despacho = $id_despacho;
                            $detalle->liquidacion_detalle_comentarios = $despachoData['comentarios'];
                            $detalle->liquidacion_detalle_estado = 1;
                            $detalle->liquidacion_detalle_microtime = microtime(true);
                            if ($detalle->save()){
                                /* ------------------------------------------------- */
                                DB::table('despachos')->where('id_despacho','=',$id_despacho)->update(['despacho_liquidado'=>1]);
                                /* ------------------------------------------------- */
                                // GUARDAR LOS GASTOS ACA
                                foreach ($despachoData['gastos'] as $concepto => $gasto) {
                                    $gastoModel = new LiquidacionGastos();
                                    $gastoModel->id_liquidacion_detalle = $detalle->id_liquidacion_detalle;
                                    $gastoModel->liquidacion_gasto_concepto = $concepto; // 'costo_flete', 'mano_obra', 'otros_gasto'
                                    $gastoModel->liquidacion_gasto_monto = $gasto['valor'];
                                    $gastoModel->liquidacion_gasto_descripcion = $gasto['descripcion'];
                                    $gastoModel->liquidacion_gasto_estado = 1;
                                    $gastoModel->liquidacion_gasto_microtime = microtime(true);
                                    if (!$gastoModel->save()) {
                                        DB::rollBack();
                                        session()->flash('error', 'Ocurrió un error al guardar uno de los gastos.');
                                        return;
                                    }
                                }
                            }else{
                                DB::rollBack();
                                session()->flash('error', 'A ocurrido un error al guardar los detalles del despacho');
                                return;
                            }
                        }
                    }
                }else{
                    DB::rollBack();
                    session()->flash('error', 'Error al guardar la liquidación.');
                    return;
                }
            } else{
                DB::rollBack();
                session()->flash('error', 'La serie y el correlativo ya existen.');
                return;
            }
            DB::commit();
            if ($this->id_liquidacion_edit){
                return redirect()->route('Liquidacionflete.liquidaciones_pendientes')->with('success', '¡Registro actualizado correctamente!');
            }else{
                session()->flash('success', 'Liquidación guardada correctamente.');
                $this->limpiar_campos_liquidacion();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            $this->limpiar_campos_liquidacion();
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
//        $this->gastos = [];
    }
}
