<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Despacho;
use App\Models\DespachoVenta;
use App\Models\General;
use App\Models\Logs;
use App\Models\Programacion;
use App\Models\Serviciotransporte;
use App\Models\Transportista;
use App\Models\Historialdespachoventa;
use App\Models\Guia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class HistorialProgramacion extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $desde;
    public $hasta;
    public $serie_correlativo;
    public $listar_detalle_despacho = [];
    public $id_despacho = "";
    public $estadoPro = "";
    public $id_serv_transpt = "";
    public $serv_transpt_estado_aprobacion = "";
    public $id_programacionRetorno = "";
    public $serv_transpt_entrega = "";
    // Atributo público para almacenar los checkboxes seleccionados
    public $selectedItems = [];
    public $estadoComprobante = [];
    public $estadoServicio = [];
    public $guias_info = [];
    public $guia_detalle = [];
    /* ---------------------------------------- */
    private $logs;
    private $programacion;
    private $despacho;
    private $general;
    private $historialdespachoventa;
    private $serviciotransporte;
    private $guia;
    public $tipo_reporte = '';
    public $roleId;
    public $currentDespachoId;
    public $serviciosTransportes = [];
    public function __construct()
    {
        $this->logs = new Logs();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->general = new General();
        $this->historialdespachoventa = new Historialdespachoventa();
        $this->serviciotransporte = new Serviciotransporte();
        $this->guia = new Guia();
    }
    public function mount(){
        $this->desde = Carbon::today()->toDateString();
        $this->hasta = Carbon::today()->addDays(6)->toDateString();
    }

//    public function render(){
//        // Lógica existente para obtener $resultado
//        $resultado = $this->programacion->listar_programaciones_historial_programacion($this->desde, $this->hasta, $this->serie_correlativo, $this->estadoPro);
//
//        foreach ($resultado as $rehs) {
//            $rehs->despacho = DB::table('despachos as d')
//                ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
//                ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
//                ->where('d.id_programacion', '=', $rehs->id_programacion)
//                ->get();
//
//            foreach ($rehs->despacho as $des) {
//                $totalVenta = 0;
//                $guiasProcesadas =[];
//                $des->comprobantes = DB::table('despacho_ventas as dv')
//                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
//                    ->where('dv.id_despacho', '=', $des->id_despacho)
//                    ->select('dv.*', 'g.guia_importe_total')
//                    ->get();
//
//                foreach ($des->comprobantes as $com) {
//                    // Verificar si el id_guia ya fue procesado
//                    if (!in_array($com->id_guia, $guiasProcesadas)) {
//                        $precio = floatval($com->guia_importe_total);  // Usar guia_importe_total
//                        $totalVenta += round($precio, 2);
//                        $guiasProcesadas[] = $com->id_guia; // Marcar el id_guia como procesado
//                    }
//                }
//                $des->totalVentaDespacho = $totalVenta;
//                // Agregar el id_guia al objeto $des (usamos el primer id_guia encontrado)
//                if (count($des->comprobantes) > 0) {
//                    $des->id_guia = $des->comprobantes[0]->id_guia;
//                } else {
//                    $des->id_guia = null; // O un valor por defecto si no hay comprobantes
//                }
//            }
//        }
//
//        $roleId = auth()->user()->roles->first()->id ?? null;
//
//        return view('livewire.programacioncamiones.historial-programacion', compact('resultado', 'roleId'));
//    }

    public function render(){
        // Obtener programaciones filtradas
        $resultado = $this->programacion->listar_programaciones_historial_programacion($this->desde, $this->hasta, $this->tipo_reporte, $this->estadoPro);

        // Inicializar variables para las sumas
        $totalLocal = 0;
        $totalProvincia1 = 0;
        $totalProvincia2 = 0;

        // Variables para fletes
        $fleteAprobadoLocal = 0;
        $fletePenalLocal = 0;
        $fleteAprobadoProv1 = 0;
        $fletePenalProv1 = 0;
        $fleteAprobadoProv2 = 0;
        $fletePenalProv2 = 0;

        // Definir el mapeo de departamentos a provincias
        $departamentosProvincia1 = ['ANCASH', 'AYACUCHO', 'HUANCAVELICA', 'HUANUCO', 'ICA', 'JUNIN', 'LA LIBERTAD', 'LAMBAYEQUE', 'PASCO', 'LIMA'];
        $departamentosProvincia2 = ['AMAZONAS', 'APURIMAC', 'AREQUIPA', 'CAJAMARCA', 'CUSCO', 'LORETO', 'MADRE DE DIOS', 'MOQUEGUA', 'PIURA', 'PUNO', 'SAN MARTIN', 'TACNA', 'TUMBES', 'UCAYALI'];
        $departamentosLocal = ['CALLAO', 'LIMA'];

        // Rastrear programaciones para determinar si son mixtas
        $programacionesContador = [];

        // Arrays para llevar el seguimiento de guías procesadas
        $guiasYaProcesadasTotal = [];
        $guiasYaProcesadasLocal = [];
        $guiasYaProcesadasProv1 = [];
        $guiasYaProcesadasProv2 = [];

        // Variables para valores calculados correctamente
        $valorTotalRequerido = 0;
        $valorLocalCorregido = 0;

        // Primera pasada para contar cuántas veces aparece cada programación
        foreach ($resultado as $rehs) {
            if (!isset($programacionesContador[$rehs->id_programacion])) {
                $programacionesContador[$rehs->id_programacion] = 0;
            }

            $rehs->despacho = DB::table('despachos as d')
                ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                ->leftJoin('departamentos as dep', 'dep.id_departamento', '=', 'd.id_departamento')
                ->where('d.id_programacion', '=', $rehs->id_programacion)
                ->select('d.*', 't.*', 'ts.*', 'dep.departamento_nombre')
                ->get();

            $programacionesContador[$rehs->id_programacion] += count($rehs->despacho);
        }

        // Procesar cada programación y sus despachos
        foreach ($resultado as $rehs) {
            $rehs->despacho = DB::table('despachos as d')
                ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                ->leftJoin('departamentos as dep', 'dep.id_departamento', '=', 'd.id_departamento')
                ->where('d.id_programacion', '=', $rehs->id_programacion)
                ->select('d.*', 't.*', 'ts.*', 'dep.departamento_nombre')
                ->get();

            $esMixto = $programacionesContador[$rehs->id_programacion] > 1;

            foreach ($rehs->despacho as $des) {
                // Calcular totalVentaDespacho para TODOS los despachos
                $totalVenta = 0;
                $guiasProcesadas = [];

                $des->comprobantes = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                    ->where('dv.id_despacho', '=', $des->id_despacho)
                    ->select('dv.*', 'g.guia_importe_total_sin_igv', 'g.guia_estado_aprobacion')
                    ->get();

                foreach ($des->comprobantes as $com) {
                    if (!in_array($com->id_guia, $guiasProcesadas)) {
                        if ($com->guia_estado_aprobacion != 11) {
                            $precio = floatval($com->guia_importe_total_sin_igv);
                            $totalVenta += round($precio, 2);
                        }
                        $guiasProcesadas[] = $com->id_guia;
                    }
                }
                $des->totalVentaDespacho = $totalVenta;

                // Solo hacer cálculos para el resumen si NO es RECHAZADO (estado != 4)
                if ($des->despacho_estado_aprobacion != 4) {
                    // Verificar estado de liquidación
                    $aprobado = $this->verificarAprobacion($des->id_despacho);

                    // Obtener valores liquidados si existe liquidación
                    $valoresLiquidados = $this->obtenerValoresLiquidados($des->id_despacho);

                    // Si hay filtro por estado de liquidación, validar coincidencia
                    $mostrarDespacho = true;
                    if ($this->estadoPro !== null && $this->estadoPro !== '') {
                        if (($this->estadoPro == 1 && !$aprobado) || ($this->estadoPro == 0 && $aprobado)) {
                            $mostrarDespacho = false;
                        }
                    }

                    if ($mostrarDespacho) {
                        // Obtener departamento desde despacho_venta_departamento si está disponible
                        $departamentoNombre = '';
                        if (isset($des->comprobantes[0]->despacho_venta_departamento) && !empty($des->comprobantes[0]->despacho_venta_departamento)) {
                            $departamentoNombre = strtoupper(trim($des->comprobantes[0]->despacho_venta_departamento));
                        } else {
                            $departamentoNombre = strtoupper(trim($des->departamento_nombre ?? ''));
                        }

                        // Determinar a qué categoría pertenece este despacho
                        if ($des->id_tipo_servicios == 1) { // Local
                            // Calcular el valor local correctamente (sin duplicar guías)
                            foreach ($des->comprobantes as $com) {
                                if (!in_array($com->id_guia, $guiasYaProcesadasLocal)) {
                                    $precio = floatval($com->guia_importe_total_sin_igv);
                                    $valorLocalCorregido += round($precio, 2);
                                    $guiasYaProcesadasLocal[] = $com->id_guia;
                                }
                            }

                            // Agregar al valor total requerido si es despacho local directo o mixto local
                            if (!$esMixto || ($esMixto && $des->id_tipo_servicios == 1)) {
                                foreach ($des->comprobantes as $com) {
                                    if (!in_array($com->id_guia, $guiasYaProcesadasTotal)) {
                                        $precio = floatval($com->guia_importe_total_sin_igv);
                                        $valorTotalRequerido += round($precio, 2);
                                        $guiasYaProcesadasTotal[] = $com->id_guia;
                                    }
                                }
                            }

                            // Calcular fletes para Local - usar valores liquidados si existen
                            $costoTarifa = $valoresLiquidados['costo_flete'] ??
                                (($des->despacho_estado_modificado == 1) ? $des->despacho_monto_modificado : $des->despacho_flete);
                            $costoMano = $valoresLiquidados['mano_obra'] ?? $des->despacho_ayudante ?? 0;
                            $costoOtros = $valoresLiquidados['otros_gasto'] ?? $des->despacho_gasto_otros ?? 0;
                            $totalFlete = ($costoTarifa + $costoMano + $costoOtros);

                            if ($aprobado) {
                                $fleteAprobadoLocal += $totalFlete;
                            } else {
                                $fletePenalLocal += $totalFlete;
                            }

                        } elseif ($des->id_tipo_servicios == 2) { // Provincial
                            // Agregar al valor total requerido si es despacho provincial directo
                            if (!$esMixto) {
                                foreach ($des->comprobantes as $com) {
                                    if (!in_array($com->id_guia, $guiasYaProcesadasTotal)) {
                                        $precio = floatval($com->guia_importe_total_sin_igv);
                                        $valorTotalRequerido += round($precio, 2);
                                        $guiasYaProcesadasTotal[] = $com->id_guia;
                                    }
                                }
                            }

                            // Calcular fletes para Provincial - usar valores liquidados si existen
                            $costoTarifa = $valoresLiquidados['costo_flete'] ??
                                (($des->despacho_estado_modificado == 1) ? $des->despacho_monto_modificado : $des->despacho_flete);
                            $costoOtros = $valoresLiquidados['otros_gasto'] ?? $des->despacho_gasto_otros ?? 0;
                            $peso = $valoresLiquidados['peso_final_kilos'] ?? $des->despacho_peso ?? 1;
                            $totalFlete = (($costoTarifa * $peso) + $costoOtros);

                            // Calcular el valor para cada zona de provincia (sin duplicar guías)
                            if (in_array($departamentoNombre, $departamentosProvincia1)) {
                                foreach ($des->comprobantes as $com) {
                                    if (!in_array($com->id_guia, $guiasYaProcesadasProv1)) {
                                        $precio = floatval($com->guia_importe_total_sin_igv);
                                        $totalProvincia1 += round($precio, 2);
                                        $guiasYaProcesadasProv1[] = $com->id_guia;
                                    }
                                }

                                if ($aprobado) {
                                    $fleteAprobadoProv1 += $totalFlete;
                                } else {
                                    $fletePenalProv1 += $totalFlete;
                                }
                            } elseif (in_array($departamentoNombre, $departamentosProvincia2)) {
                                foreach ($des->comprobantes as $com) {
                                    if (!in_array($com->id_guia, $guiasYaProcesadasProv2)) {
                                        $precio = floatval($com->guia_importe_total_sin_igv);
                                        $totalProvincia2 += round($precio, 2);
                                        $guiasYaProcesadasProv2[] = $com->id_guia;
                                    }
                                }

                                if ($aprobado) {
                                    $fleteAprobadoProv2 += $totalFlete;
                                } else {
                                    $fletePenalProv2 += $totalFlete;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Calcular los totales
        $totalGeneral = $valorTotalRequerido;
        $totalLocal = $valorLocalCorregido;
        $totalProvincial = $totalProvincia1 + $totalProvincia2;

        // Totales de fletes
        $totalFleteAprobado = $fleteAprobadoLocal + $fleteAprobadoProv1 + $fleteAprobadoProv2;
        $totalFletePenal = $fletePenalLocal + $fletePenalProv1 + $fletePenalProv2;
        $totalFleteGeneral = $totalFleteAprobado + $totalFletePenal;

        $totalFleteAprobadoProv = $fleteAprobadoProv1 + $fleteAprobadoProv2;
        $totalFletePenalProv = $fletePenalProv1 + $fletePenalProv2;
        $totalFleteProv = $totalFleteAprobadoProv + $totalFletePenalProv;

        // Preparar los datos para la vista
        $zonaDespachoData = [
            [
                'zona' => 'Total',
                'valor_transportado' => number_format($totalGeneral, 2),
                'flete_aprobado' => number_format($totalFleteAprobado, 2),
                'flete_penal' => number_format($totalFletePenal, 2),
                'total_flete' => number_format($totalFleteGeneral, 2)
            ],
            [
                'zona' => 'Local',
                'valor_transportado' => number_format($totalLocal, 2),
                'flete_aprobado' => number_format($fleteAprobadoLocal, 2),
                'flete_penal' => number_format($fletePenalLocal, 2),
                'total_flete' => number_format(($fleteAprobadoLocal + $fletePenalLocal), 2)
            ],
            [
                'zona' => 'Provincia 1',
                'valor_transportado' => number_format($totalProvincia1, 2),
                'flete_aprobado' => number_format($fleteAprobadoProv1, 2),
                'flete_penal' => number_format($fletePenalProv1, 2),
                'total_flete' => number_format(($fleteAprobadoProv1 + $fletePenalProv1), 2)
            ],
            [
                'zona' => 'Provincia 2',
                'valor_transportado' => number_format($totalProvincia2, 2),
                'flete_aprobado' => number_format($fleteAprobadoProv2, 2),
                'flete_penal' => number_format($fletePenalProv2, 2),
                'total_flete' => number_format(($fleteAprobadoProv2 + $fletePenalProv2), 2)
            ],
            [
                'zona' => 'Total Provincia',
                'valor_transportado' => number_format($totalProvincial, 2),
                'flete_aprobado' => number_format($totalFleteAprobadoProv, 2),
                'flete_penal' => number_format($totalFletePenalProv, 2),
                'total_flete' => number_format($totalFleteProv, 2)
            ]
        ];

        $roleId = auth()->user()->roles->first()->id ?? null;

        return view('livewire.programacioncamiones.historial-programacion', compact('resultado', 'roleId', 'zonaDespachoData'));
    }


    // Nueva función para obtener valores liquidados
    public function obtenerValoresLiquidados($idDespacho){
        $valores = [
            'costo_flete' => null,
            'mano_obra' => null,
            'otros_gasto' => null,
            'peso_final_kilos' => null
        ];

        // Buscar en liquidacion_detalles
        $liquidacionDetalle = DB::table('liquidacion_detalles')
            ->where('id_despacho', $idDespacho)
            ->first();

        if ($liquidacionDetalle) {
            // Buscar en liquidacion_gastos los conceptos modificados
            $gastos = DB::table('liquidacion_gastos')
                ->where('id_liquidacion_detalle', $liquidacionDetalle->id_liquidacion_detalle)
                ->get();

            foreach ($gastos as $gasto) {
                if ($gasto->liquidacion_gasto_concepto === 'costo_flete') {
                    $valores['costo_flete'] = $gasto->liquidacion_gasto_monto;
                } elseif ($gasto->liquidacion_gasto_concepto === 'mano_obra') {
                    $valores['mano_obra'] = $gasto->liquidacion_gasto_monto;
                } elseif ($gasto->liquidacion_gasto_concepto === 'otros_gasto') {
                    $valores['otros_gasto'] = $gasto->liquidacion_gasto_monto;
                } elseif ($gasto->liquidacion_gasto_concepto === 'peso_final_kilos') {
                    $valores['peso_final_kilos'] = $gasto->liquidacion_gasto_monto;
                }
            }
        }

        return $valores;
    }

    // Función para verificar si un despacho está aprobado
     public function verificarAprobacion($idDespacho){
        $liquidacionDetalle = DB::table('liquidacion_detalles')
            ->where('id_despacho', $idDespacho)
            ->first();

        if ($liquidacionDetalle) {
            $liquidacion = DB::table('liquidaciones')
                ->where('id_liquidacion', $liquidacionDetalle->id_liquidacion)
                ->first();

            if ($liquidacion && $liquidacion->liquidacion_estado_aprobacion == 1) {
                return true;
            }
        }

        return false;
    }

    public function listar_informacion_despacho($id_despacho) {
        try {
            // Limpiar estados anteriores
            $this->reset(['estadoComprobante', 'estadoServicio']);
            $this->currentDespachoId = $id_despacho;

            $this->listar_detalle_despacho = DB::table('despachos as d')
                ->join('users as u', 'u.id_users', '=', 'd.id_users')
                ->where('d.id_despacho', '=', $id_despacho)
                ->first();

            if ($this->listar_detalle_despacho) {
                // Obtener comprobantes con ambos estados
                $comprobantes = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                    ->where('dv.id_despacho', '=', $id_despacho)
                    ->select('dv.*', 'g.*', 'dv.despacho_detalle_estado_entrega')
                    ->get();

                $this->listar_detalle_despacho->comprobantes = $comprobantes;

                foreach ($comprobantes as $comp) {
                    $key = $id_despacho.'_'.$comp->id_despacho_venta;

                    $estado = $comp->guia_estado_aprobacion;
                    if (isset($comp->despacho_detalle_estado_entrega)) {
                        if ($comp->despacho_detalle_estado_entrega == 0) {
                            $estado = $comp->guia_estado_aprobacion;
                        } elseif (in_array($comp->despacho_detalle_estado_entrega, [8, 11])) {
                            $estado = $comp->despacho_detalle_estado_entrega;
                        }
                    }

                    $this->estadoComprobante[$key] = in_array($estado, [8, 11, 12]) ? $estado : 8;
                }

                // Saber el estado de los servicios transporte
                $servicios = DB::table('despacho_ventas as dv')
                    ->join('servicios_transportes as st', 'st.id_serv_transpt', '=', 'dv.id_serv_transpt')
                    ->where('dv.id_despacho', '=', $id_despacho)
                    ->get();

                $this->listar_detalle_despacho->servicios_transportes = $servicios;

                foreach ($servicios as $serv) {
                    $key = $id_despacho.'_'.$serv->id_despacho_venta;
                    $this->estadoServicio[$key] = in_array($serv->serv_transpt_estado_aprobacion, [5, 6, 3])
                        ? $serv->serv_transpt_estado_aprobacion
                        : 5;
                }
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
        }
    }

    public function cambiarEstadoDespacho($id){ //  $estado = 1 aprobar , 2 desaprobar
        if ($id){
            $this->id_despacho = $id;
        }
    }
    public function retornarProgamacionApro($id){
        try {
            if ($id){
                $this->id_programacionRetorno = $id;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
        }
    }
    public function generar_excel_historial_programacion(){
        try {
//            if (!Gate::allows('generar_excel_historial_programacion')) {
//                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
//                return;
//            }
            $resultado = $this->programacion->listar_programaciones_historial_programacion_excel($this->desde, $this->hasta, $this->tipo_reporte, $this->estadoPro);
            $conteoDesp = 0;

            // Inicializar variables para las sumas
            $totalLocal = 0;
            $totalProvincia1 = 0;
            $totalProvincia2 = 0;
            $totalGeneral = 0;

            // Variables para fletes
            $fleteAprobadoLocal = 0;
            $fletePenalLocal = 0;
            $fleteAprobadoProv1 = 0;
            $fletePenalProv1 = 0;
            $fleteAprobadoProv2 = 0;
            $fletePenalProv2 = 0;

            // Variables para peso y volumen
            $pesoTotalKilos = 0;
            $volumenTotal = 0;

            // Definir el mapeo de departamentos a provincias
            $departamentosProvincia1 = ['ANCASH', 'AYACUCHO', 'HUANCAVELICA', 'HUANUCO', 'ICA', 'JUNIN', 'LA LIBERTAD', 'LAMBAYEQUE', 'PASCO', 'LIMA'];
            $departamentosProvincia2 = ['AMAZONAS', 'APURIMAC', 'AREQUIPA', 'CAJAMARCA', 'CUSCO', 'LORETO', 'MADRE DE DIOS', 'MOQUEGUA', 'PIURA', 'PUNO', 'SAN MARTIN', 'TACNA', 'TUMBES', 'UCAYALI'];
            $departamentosLocal = ['CALLAO', 'LIMA'];

            // Rastrear programaciones para determinar si son mixtas
            $programacionesContador = [];

            // Arrays para llevar el seguimiento de despachos procesados
            $despachosProcesadosLocal = [];
            $despachosProcesadosProv1 = [];
            $despachosProcesadosProv2 = [];
            $guiasProcesadasTotal = []; // Para evitar duplicados en el total general
            // Primera pasada para contar cuántas veces aparece cada programación
            foreach ($resultado as $rehs) {
                if (!isset($programacionesContador[$rehs->id_programacion])) {
                    $programacionesContador[$rehs->id_programacion] = 0;
                }

                $rehs->despacho = DB::table('despachos as d')
                    ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                    ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                    ->leftJoin('departamentos as dep', 'dep.id_departamento', '=', 'd.id_departamento')
                    ->where('d.id_programacion', '=', $rehs->id_programacion)
                    ->where('d.despacho_estado_aprobacion', '!=', 4)
                    ->select('d.*', 't.*', 'ts.*', 'dep.departamento_nombre')
                    ->get();

                $programacionesContador[$rehs->id_programacion] += count($rehs->despacho);
            }

            // Procesar cada programación y sus despachos
            foreach ($resultado as $rehs) {
                $rehs->despacho = DB::table('despachos as d')
                    ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                    ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                    ->leftJoin('departamentos as dep', 'dep.id_departamento', '=', 'd.id_departamento')
                    ->where('d.id_programacion', '=', $rehs->id_programacion)
                    ->where('d.despacho_estado_aprobacion', '!=', 4)
                    ->select('d.*', 't.*', 'ts.*', 'dep.departamento_nombre')
                    ->get();

                $esMixto = $programacionesContador[$rehs->id_programacion] > 1;

                if (count($rehs->despacho) > 0) {
                    $conteoDesp++;
                    foreach ($rehs->despacho as $des) {
                        // Calcular totalVentaDespacho para TODOS los despachos
                        $totalVenta = 0;
                        $guiasProcesadas = [];

                        // Manteniendo el código original para obtener comprobantes con detalles
                        if ($des->id_tipo_servicios == 1) { // Si es mixto
                            $datLocales = DB::table('despacho_ventas as dv')
                                ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                                ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                                ->where('d.id_despacho', '=', $des->id_despacho)
                                ->where('d.id_programacion', '=', $rehs->id_programacion)
                                ->where('d.despacho_estado_aprobacion', '!=', 4)
                                ->whereNotExists(function ($query) use ($rehs) {
                                    $query->select(DB::raw(1))
                                        ->from('despacho_ventas as dv_provincial')
                                        ->join('despachos as d_provincial', 'd_provincial.id_despacho', '=', 'dv_provincial.id_despacho')
                                        ->join('guias as g_provincial', 'g_provincial.id_guia', '=', 'dv_provincial.id_guia')
                                        ->where('d_provincial.id_programacion', '=', $rehs->id_programacion)
                                        ->where('d_provincial.id_tipo_servicios', '=', 2)
                                        ->whereRaw('g_provincial.id_guia = g.id_guia');
                                })
                                ->select('g.*', 'dv.id_despacho')
                                ->get();

                            foreach ($datLocales as $guia) {
                                $guia->detalles = DB::table('guias_detalles')
                                    ->where('id_guia', $guia->id_guia)
                                    ->get();
                            }

                            $desProvinciPro = DB::table('despachos')
                                ->where('id_programacion', '=', $rehs->id_programacion)
                                ->where('id_tipo_servicios', '=', 2)
                                ->pluck('id_despacho');

                            $datProvinciales = collect();
                            if ($desProvinciPro->isNotEmpty()) {
                                $datProvinciales = DB::table('despacho_ventas')
                                    ->join('guias', 'guias.id_guia', '=', 'despacho_ventas.id_guia')
                                    ->whereIn('despacho_ventas.id_despacho', $desProvinciPro)
                                    ->select('guias.*', 'despacho_ventas.id_despacho')
                                    ->get();

                                foreach ($datProvinciales as $guia) {
                                    $guia->detalles = DB::table('guias_detalles')
                                        ->where('id_guia', $guia->id_guia)
                                        ->get();
                                }
                            }

                            $datCombinado = $datLocales->merge($datProvinciales);
                            $des->comprobantes = $datCombinado;
                        } else {
                            // Para no mixtos
                            $des->comprobantes = DB::table('despacho_ventas as dv')
                                ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                                ->where('dv.id_despacho', '=', $des->id_despacho)
                                ->select('g.*', 'dv.id_despacho')
                                ->get();

                            foreach ($des->comprobantes as $guia) {
                                $guia->detalles = DB::table('guias_detalles')
                                    ->where('id_guia', $guia->id_guia)
                                    ->get();
                            }
                        }

                        // Calcular valor transportado (suma de guias sin IGV)
                        $valorTransportado = $des->comprobantes->sum('guia_importe_total_sin_igv');
                        $des->totalVentaDespacho = $valorTransportado;

                        // Cálculo de peso y volumen (manteniendo este código)
                        foreach ($des->comprobantes as $com) {
                            $totalDetalles = 0;
                            $pesoTotalGramos = 0;
                            $volumenTotalGuia = 0;
                            foreach ($com->detalles as $detalle) {
                                $totalDetalles += floatval($detalle->guia_det_importe_total_inc_igv);
                                $pesoTotalGramos += $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                                $volumenTotalGuia += $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
                            }
                            $com->total_detalles = $totalDetalles;
                            $com->peso_total_kilos = $pesoTotalGramos / 1000;
                            $com->volumen_total = $volumenTotalGuia;

                            // Acumular peso y volumen total
                            $pesoTotalKilos += $com->peso_total_kilos;
                            $volumenTotal += $com->volumen_total;
                        }

                        // Solo hacer cálculos para el resumen si NO es RECHAZADO (estado != 4)
                        if ($des->despacho_estado_aprobacion != 4) {
                            // Verificar estado de liquidación
                            $aprobado = $this->verificarAprobacion($des->id_despacho);

                            // Obtener valores liquidados si existe liquidación
                            $valoresLiquidados = $this->obtenerValoresLiquidados($des->id_despacho);

                            // Si hay filtro por estado de liquidación, validar coincidencia
                            $mostrarDespacho = true;
                            if ($this->estadoPro !== null && $this->estadoPro !== '') {
                                if (($this->estadoPro == 1 && !$aprobado) || ($this->estadoPro == 0 && $aprobado)) {
                                    $mostrarDespacho = false;
                                }
                            }

                            if ($mostrarDespacho) {
                                // Obtener departamento desde despacho_venta_departamento si está disponible
                                $departamentoNombre = '';
                                if (isset($des->comprobantes[0]->despacho_venta_departamento) && !empty($des->comprobantes[0]->despacho_venta_departamento)) {
                                    $departamentoNombre = strtoupper(trim($des->comprobantes[0]->despacho_venta_departamento));
                                } else {
                                    $departamentoNombre = strtoupper(trim($des->departamento_nombre ?? ''));
                                }

                                // Determinar a qué categoría pertenece este despacho
                                if ($des->id_tipo_servicios == 1) { // Local
                                    // Solo sumar si no hemos procesado este despacho antes
                                    if (!in_array($des->id_despacho, $despachosProcesadosLocal)) {
                                        $totalLocal += $valorTransportado;
                                        $despachosProcesadosLocal[] = $des->id_despacho;
                                    }

                                    // Sumar al total general (local directo o local en mixto)
                                    foreach ($des->comprobantes as $com) {
                                        if (!in_array($com->id_guia, $guiasProcesadasTotal)) {
                                            $totalGeneral += floatval($com->guia_importe_total_sin_igv);
                                            $guiasProcesadasTotal[] = $com->id_guia;
                                        }
                                    }

                                    // Calcular fletes para Local - usar valores liquidados si existen
                                    $costoTarifa = $valoresLiquidados['costo_flete'] ??
                                        (($des->despacho_estado_modificado == 1) ? $des->despacho_monto_modificado : $des->despacho_flete);
                                    $costoMano = $valoresLiquidados['mano_obra'] ?? $des->despacho_ayudante ?? 0;
                                    $costoOtros = $valoresLiquidados['otros_gasto'] ?? $des->despacho_gasto_otros ?? 0;
                                    $totalFlete = ($costoTarifa + $costoMano + $costoOtros);

                                    if ($aprobado) {
                                        $fleteAprobadoLocal += $totalFlete;
                                    } else {
                                        $fletePenalLocal += $totalFlete;
                                    }

                                } elseif ($des->id_tipo_servicios == 2) { // Provincial
                                    // Sumar al total general solo si no es mixto (provincial directo)
                                    if (!$esMixto) {
                                        foreach ($des->comprobantes as $com) {
                                            if (!in_array($com->id_guia, $guiasProcesadasTotal)) {
                                                $totalGeneral += floatval($com->guia_importe_total_sin_igv);
                                                $guiasProcesadasTotal[] = $com->id_guia;
                                            }
                                        }
                                    }

                                    // Calcular fletes para Provincial - usar valores liquidados si existen
                                    $costoTarifa = $valoresLiquidados['costo_flete'] ??
                                        (($des->despacho_estado_modificado == 1) ? $des->despacho_monto_modificado : $des->despacho_flete);
                                    $costoOtros = $valoresLiquidados['otros_gasto'] ?? $des->despacho_gasto_otros ?? 0;
                                    $peso = $valoresLiquidados['peso_final_kilos'] ?? $des->despacho_peso ?? 1;
                                    $totalFlete = (($costoTarifa * $peso) + $costoOtros);

                                    // Calcular el valor para cada zona de provincia
                                    if (in_array($departamentoNombre, $departamentosProvincia1)) {
                                        // Solo sumar si no hemos procesado este despacho antes
                                        if (!in_array($des->id_despacho, $despachosProcesadosProv1)) {
                                            $totalProvincia1 += $valorTransportado;
                                            $despachosProcesadosProv1[] = $des->id_despacho;
                                        }

                                        if ($aprobado) {
                                            $fleteAprobadoProv1 += $totalFlete;
                                        } else {
                                            $fletePenalProv1 += $totalFlete;
                                        }
                                    } elseif (in_array($departamentoNombre, $departamentosProvincia2)) {
                                        // Solo sumar si no hemos procesado este despacho antes
                                        if (!in_array($des->id_despacho, $despachosProcesadosProv2)) {
                                            $totalProvincia2 += $valorTransportado;
                                            $despachosProcesadosProv2[] = $des->id_despacho;
                                        }

                                        if ($aprobado) {
                                            $fleteAprobadoProv2 += $totalFlete;
                                        } else {
                                            $fletePenalProv2 += $totalFlete;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($conteoDesp > 0){
                $spreadsheet = new Spreadsheet();
                $sheet1  = $spreadsheet->getActiveSheet();
                $sheet1->setTitle('Historial programación');

                $mensaje = "RESULTADO DE BÚSQUEDA: ";
                $textMeF = "";

                if (isset($this->desde, $this->hasta)) {
                    $mensaje .= " | Rango de fechas: " . date("d-m-Y", strtotime($this->desde)) . " al " . date("d-m-Y", strtotime($this->hasta));
                    $textMeF = date("d-m-Y", strtotime($this->desde)) . " al " . date("d-m-Y", strtotime($this->hasta));
                }
                $row = 1;
                // Configurar título
                $sheet1->setCellValue('A'.$row, 'HISTORIAL DE PROGRAMACIONES');
                $titleStyle = $sheet1->getStyle('A'.$row);
                $titleStyle->getFont()->setSize(12);
                $titleStyle->getFont()->setBold(true);
                $sheet1->mergeCells('A'.$row.':Y'.$row);
                $row++;
                $sheet1->setCellValue('A'.$row, $mensaje);
                $titleStyle = $sheet1->getStyle('A'.$row);
                $titleStyle->getFont()->setSize(12);
                $titleStyle->getFont()->setBold(true);
                $sheet1->mergeCells('A'.$row.':Y'.$row);
                $row++;
                $sheet1->setCellValue('A'.$row, "");
                $sheet1->mergeCells('A'.$row.':Y'.$row);
                $row++;
                /* --------------------------------------------------------------------------------- */
                $sheet1->setCellValue('A'.$row, 'LIQUIDACIÓN DE GASTOS DE TRANSPORTE');
                $titleStyle = $sheet1->getStyle('A'.$row);
                $titleStyle->getFont()->setSize(8);
                $cellRange = 'A'.$row.':J'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE699'); // Fondo

                $sheet1->setCellValue('K'.$row, 'FECHA PRESENTACIÓN');
                $titleStyle = $sheet1->getStyle('K'.$row);
                $titleStyle->getFont()->setSize(8);
                $cellRange = 'K'.$row.':L'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFC000'); // Fondo

                $sheet1->setCellValue('M'.$row, date('d/m/Y'));
                $titleStyle = $sheet1->getStyle('M'.$row);
                $titleStyle->getFont()->setSize(8);
                $cellRange = 'M'.$row.':O'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFC000'); // Fondo

                $sheet1->setCellValue('P'.$row, $textMeF);
                $titleStyle = $sheet1->getStyle('P'.$row);
                $titleStyle->getFont()->setSize(8);
                $cellRange = 'P'.$row.':T'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00'); // Fondo

                $cellRange = 'A'.$row.':T'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFont()->setSize(10);
                $rowStyle->getFont()->setBold(true);
                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $row++;

                $sheet1->setCellValue('A'.$row, 'DATOS DEL DESPACHO');
                $titleStyle = $sheet1->getStyle('A'.$row);
                $titleStyle->getFont()->setSize(8);
                $cellRange = 'A'.$row.':J'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('C4D79B'); // Fondo

                $sheet1->setCellValue('K'.$row, 'TRANSPORTE LOCAL');
                $titleStyle = $sheet1->getStyle('K'.$row);
                $titleStyle->getFont()->setSize(8);
                $cellRange = 'K'.$row.':R'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92CDDC'); // Fondo

                $sheet1->setCellValue('W'.$row, '');
                $cellRange = 'W'.$row.':W'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('DDD9C4'); // Fondo
                $rowStyle->getFont()->setBold(true); // Hacer negritas

                $sheet1->setCellValue('S'.$row, 'TRANSPORTE PROVINCIA');
                $titleStyle = $sheet1->getStyle('S'.$row);
                $titleStyle->getFont()->setSize(8);
                $cellRange = 'S'.$row.':Y'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E2EFDA'); // Fondo

                $cellRange = 'A'.$row.':Y'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFont()->setSize(10);
                $rowStyle->getFont()->setBold(true);
                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $row++;
                // APARTIR DE ACA COMIENZA EL CONTENIDO DONDE QUIERO QUE AGREGUES ESA CONDICION
                $sheet1->setCellValue('A'.$row, 'N° GUÍA');
                $sheet1->setCellValue('B'.$row, 'F. EMISION GUÍA');
                $sheet1->setCellValue('C'.$row, 'CLIENTE');
                $sheet1->setCellValue('D'.$row, 'N° FACTURA BOLETA');
                $sheet1->setCellValue('E'.$row, 'IMPORTE');
                $sheet1->setCellValue('F'.$row, 'N° PRO');
                $sheet1->setCellValue('G'.$row, 'F. DESPACHO');
                $sheet1->setCellValue('H'.$row, 'ENTREGADO');
                $sheet1->setCellValue('I'.$row, 'TIPO SERVICIO');
                $sheet1->setCellValue('J'.$row, 'PESO'); // DATOS DESPACHO FIN
                $cellRange = 'A'.$row.':J'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('C4D79B'); // Fondo
                $rowStyle->getFont()->setBold(true); // Hacer negritas

                $sheet1->setCellValue('K'.$row, 'N° OS');
                $sheet1->setCellValue('L'.$row, 'FLETE');
                $sheet1->setCellValue('M'.$row, 'OTROS');
                $sheet1->setCellValue('N'.$row, 'AYUDANTES');
                $sheet1->setCellValue('O'.$row, 'TRANSPORTISTA');
                $sheet1->setCellValue('P'.$row, 'FACTURA PROVEEDOR');
                $sheet1->setCellValue('Q'.$row, 'FLETE TOTAL');
                $sheet1->setCellValue('R'.$row, '%'); // TRANSPORTE LOCAL FIN
                $cellRange = 'K'.$row.':R'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92CDDC'); // Fondo
                $rowStyle->getFont()->setBold(true); // Hacer negritas

                $sheet1->setCellValue('S'.$row, 'N° OS');
                $sheet1->setCellValue('T'.$row, 'TRANSPORTISTA');
                $sheet1->setCellValue('U'.$row, 'DEPARTAMENTO - PROVINCIA');
                $sheet1->setCellValue('V'.$row, 'ZONA DE DESPACHO');
                $sheet1->setCellValue('W'.$row, 'FACTURA PROVEEDOR');
                $sheet1->setCellValue('X'.$row, 'FLETE TOTAL');
                $sheet1->setCellValue('Y'.$row, '%'); // TRANSPORTE PROVINCIAL FIN
                $cellRange = 'S'.$row.':Y'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('DDD9C4'); // Fondo
                $rowStyle->getFont()->setBold(true); // Hacer negritas

                $sheet1->getColumnDimension('A')->setWidth(15);
                $sheet1->getColumnDimension('B')->setWidth(15);
                $sheet1->getColumnDimension('C')->setWidth(60);
                $sheet1->getColumnDimension('D')->setWidth(15);
                $sheet1->getColumnDimension('E')->setWidth(17);
                $sheet1->getColumnDimension('F')->setWidth(12);
                $sheet1->getColumnDimension('G')->setWidth(15);
                $sheet1->getColumnDimension('H')->setWidth(13);
                $sheet1->getColumnDimension('I')->setWidth(14);
                $sheet1->getColumnDimension('J')->setWidth(15);
                $sheet1->getColumnDimension('K')->setWidth(15);
                $sheet1->getColumnDimension('L')->setWidth(15);
                $sheet1->getColumnDimension('M')->setWidth(15);
                $sheet1->getColumnDimension('N')->setWidth(15);
                $sheet1->getColumnDimension('O')->setWidth(60);
                $sheet1->getColumnDimension('P')->setWidth(20);
                $sheet1->getColumnDimension('R')->setWidth(12);
                $sheet1->getColumnDimension('S')->setWidth(15);
                $sheet1->getColumnDimension('T')->setWidth(60);
                $sheet1->getColumnDimension('U')->setWidth(26);
                $sheet1->getColumnDimension('V')->setWidth(18);
                $sheet1->getColumnDimension('W')->setWidth(20);
                $sheet1->getColumnDimension('X')->setWidth(18);
                $sheet1->getColumnDimension('Y')->setWidth(6);

                $cellRange = 'A'.$row.':Y'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
                $rowStyle->getFont()->setSize(10);
                $rowStyle->getFont()->setBold(true);

                $row++;
                // ESO BASICAMENTE SERIA LOS DATOS, VALORES QUE MUESTRO
                foreach($resultado as $resPro){
                    $despachosFiltrados = collect($resPro->despacho)->filter(function($des) use ($resPro) {
                        if ($this->estadoPro === null || $this->estadoPro === '') {
                            return true; // Mostrar todos si no hay filtro
                        }

                        $aprobado = $this->verificarAprobacion($des->id_despacho);

                        // Para programaciones mixtas (tiene despachos local y provincial)
                        if (count($resPro->despacho) > 1) {
                            // Si es el despacho local (tipo 1) y estadoPro es 0 (pendientes)
                            if ($des->id_tipo_servicios == 1 && $this->estadoPro == 0) {
                                return false; // No mostrar el local en pendientes
                            }
                        }

                        if ($this->estadoPro == 1) {
                            return $aprobado; // Solo aprobados
                        } elseif ($this->estadoPro == 0) {
                            return !$aprobado; // Solo pendientes
                        }

                        return true;
                    });
                    if ($despachosFiltrados->count() > 0) {
                        foreach ($resPro->despacho as $inD => $des){
                            if ($inD == 0){
                                $des->comprobantes = collect($des->comprobantes)->sortBy('guia_fecha_emision')->values();
                                $filaPorcentajeLocal = null;
                                $fleteFinalLocal = null;

                                $fleteFinalProvin = null;
                                $filaPorcentajeProvin = null;

                                $totalPesoDespachos = 0;
                                $importeTotalDespachos = 0;
                                $osAnteriorMixto = null;
                                foreach ($des->comprobantes  as $indexComprobante => $comproba) {
                                    // buscar si es mixto o un servicio normal
                                    // Verificar si es mixto
                                    $validarMixto = DB::table('despacho_ventas as dv')
                                        ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                                        ->where('dv.id_guia', '=', $comproba->id_guia)
                                        ->where('dv.id_despacho', '<>', $des->id_despacho)
                                        ->where('d.id_programacion', '=', $resPro->id_programacion)
                                        ->where('d.id_tipo_servicios', '=', 2)
                                        ->select('d.*')
                                        ->first();

                                    // Determinar si debemos mostrar este despacho mixto
                                    $mostrarMixto = true;
                                    if ($validarMixto && ($this->estadoPro !== null && $this->estadoPro !== '')) {
                                        $aprobadoMixto = $this->verificarAprobacion($validarMixto->id_despacho);

                                        if (($this->estadoPro == 1 && !$aprobadoMixto) || ($this->estadoPro == 0 && $aprobadoMixto)) {
                                            $mostrarMixto = false;
                                        }
                                    }

                                    // Determinar el tipo de comprobante
                                    if ($validarMixto && $mostrarMixto) {
                                        $typeComprop = 3; // Es mixto y cumple con el filtro
                                    } else {
                                        $typeComprop = $des->id_tipo_servicios;

                                        // Si es local y estamos filtrando por pendientes, no mostrar
                                        if ($typeComprop == 1 && $this->estadoPro == 0) {
                                            continue; // Saltar esta iteración del foreach
                                        }
                                    }

                                    $loc = match ($typeComprop) {
                                        1 => 'LOCAL',
                                        2 => 'PROVINCIAL',
                                        3 => 'MIXTO',
                                        default => '',
                                    };

                                    //  Obtener estado de entrega desde despacho_ventas
                                    $estadoEntrega = DB::table('despacho_ventas')
                                        ->where('id_guia', $comproba->id_guia)
                                        ->value('despacho_detalle_estado_entrega');

                                    // Estado de aprobación
                                    $estadoAprobacion = $comproba->guia_estado_aprobacion;
                                    $estado = '';

                                    // -------- CELDA A-G --------
                                    $sheet1->setCellValue('A' . $row, $comproba->guia_nro_doc);
                                    $sheet1->setCellValue('B' . $row, date('d/m/Y', strtotime($comproba->guia_fecha_emision)));
                                    $sheet1->setCellValue('C' . $row, $comproba->guia_nombre_cliente);
                                    $sheet1->setCellValue('D' . $row, $comproba->guia_nro_doc_ref ?? '-');

                                    // -------- CELDA E (IMPORTE) --------
                                    $yellowStyle = [
                                        'fill' => [
                                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                            'startColor' => ['argb' => 'FFFFFF00'],
                                        ],
                                    ];

                                    if ($estadoEntrega == 0) {
                                        if ($estadoAprobacion == 8) {
                                            $sheet1->setCellValue('E' . $row, $this->general->formatoDecimal($comproba->guia_importe_total_sin_igv));
                                        } elseif ($estadoAprobacion == 11) {
                                            $sheet1->setCellValue('E' . $row, 0);
                                            $sheet1->getStyle('E' . $row)->applyFromArray($yellowStyle);
                                        }
                                    } elseif ($estadoEntrega == 8) {
                                        $sheet1->setCellValue('E' . $row, $this->general->formatoDecimal($comproba->guia_importe_total_sin_igv));
                                    } elseif ($estadoEntrega == 11) {
                                        if ($estadoAprobacion == 11) {
                                            $sheet1->setCellValue('E' . $row, 0);
                                            $sheet1->getStyle('E' . $row)->applyFromArray($yellowStyle);
                                        } else {
                                            $sheet1->setCellValue('E' . $row, 0);
                                        }
                                    }

                                    $sheet1->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                                    // -------- CELDA F-G --------
                                    $sheet1->setCellValue('F' . $row, $resPro->programacion_numero_correlativo);
                                    $sheet1->setCellValue('G' . $row, date('d/m/Y', strtotime($resPro->programacion_fecha)));

                                    // -------- CELDA H (SI / NO) --------
                                    if ($estadoEntrega == 0) {
                                        $estado = ($estadoAprobacion == 8) ? 'SI' : (($estadoAprobacion == 11) ? 'NO' : '');
                                    } elseif ($estadoEntrega == 8) {
                                        $estado = 'SI';
                                    } elseif ($estadoEntrega == 11) {
                                        $estado = 'NO';
                                    }
                                    $sheet1->setCellValue('H' . $row, $estado);

                                    // -------- CELDA I-J --------
                                    $sheet1->setCellValue('I' . $row, $loc);

                                    if ($estadoEntrega == 0) {
                                        if ($estadoAprobacion == 8) {
                                            $sheet1->setCellValue('J' . $row, $this->general->formatoDecimal($comproba->peso_total_kilos));
                                        } elseif ($estadoAprobacion == 11) {
                                            $sheet1->setCellValue('J' . $row, 0);
                                            $sheet1->getStyle('J' . $row)->applyFromArray($yellowStyle);
                                        }
                                    } elseif ($estadoEntrega == 8) {
                                        $sheet1->setCellValue('J' . $row, $this->general->formatoDecimal($comproba->peso_total_kilos));
                                    } elseif ($estadoEntrega == 11) {
                                        if ($estadoAprobacion == 11) {
                                            $sheet1->setCellValue('J' . $row, 0);
                                            $sheet1->getStyle('J' . $row)->applyFromArray($yellowStyle);
                                        } else {
                                            $sheet1->setCellValue('J' . $row, 0);
                                        }
                                    }
                                    $sheet1->getStyle('J' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                                    if ($indexComprobante == 0 || $indexComprobante == 1) {
                                        /* LOCAL */
                                        // EN ESTA PARTE DE MI CODIGO MUESTRA LO DE LOCAL TANTO PARA LOCAL DIRECTO Y LOCAL EN MIXTO
                                        if ($des->id_tipo_servicios == 1) {
                                            // Verificar si es parte de una programación mixta
                                            $esMixto = DB::table('despachos')
                                                ->where('id_programacion', $resPro->id_programacion)
                                                ->where('id_tipo_servicios', 2) // Buscar si hay despachos provinciales en la misma programación
                                                ->exists();

                                            // Solo mostrar si:
                                            // 1. No estamos filtrando por pendientes (estadoPro != 0) O
                                            // 2. Es un local directo (no es mixto) O
                                            // 3. Es mixto pero el filtro no es por pendientes
                                            if ($indexComprobante == 0 && ($this->estadoPro != 0 || !$esMixto)) {
                                                $informacionliquidacion = DB::table('despachos')
                                                    ->where('id_despacho', '=', $des->id_despacho)
                                                    ->orderBy('id_despacho', 'desc')->first();

                                                $sheet1->setCellValue('K' . $row, $des->despacho_numero_correlativo);

                                                $liquidacion_detalle = DB::table('liquidacion_detalles')
                                                    ->where('id_despacho', '=', $des->id_despacho)
                                                    ->first();

                                                $fac_pro = "";
                                                $costoTarifa = 0;
                                                $costoMano = 0;
                                                $costoOtros = 0;

                                                if ($liquidacion_detalle) {
                                                    // Verificar si la liquidación está aprobada
                                                    $liquidacion = DB::table('liquidaciones')
                                                        ->where('id_liquidacion', '=', $liquidacion_detalle->id_liquidacion)
                                                        ->where('liquidacion_estado_aprobacion', 1)
                                                        ->first();

                                                    if ($liquidacion) {
                                                        // Liquidación aprobada - obtener gastos de liquidación
                                                        $gastos_liquidacion = DB::table('liquidacion_gastos')
                                                            ->where('id_liquidacion_detalle', '=', $liquidacion_detalle->id_liquidacion_detalle)
                                                            ->get();

                                                        foreach ($gastos_liquidacion as $gasto) {
                                                            switch ($gasto->liquidacion_gasto_concepto) {
                                                                case 'costo_flete':
                                                                    $costoTarifa = $gasto->liquidacion_gasto_monto;
                                                                    break;
                                                                case 'mano_obra':
                                                                    $costoMano = $gasto->liquidacion_gasto_monto;
                                                                    break;
                                                                case 'otros_gasto':
                                                                    $costoOtros = $gasto->liquidacion_gasto_monto;
                                                                    break;
                                                            }
                                                        }

                                                        $fac_pro = '';

                                                        // Obtener factura del proveedor
                                                        $fac_proveedor = DB::table('liquidaciones')
                                                            ->where('id_transportistas', '=', $des->id_transportistas)
                                                            ->where('id_liquidacion', '=', $liquidacion_detalle->id_liquidacion)
                                                            ->first();

                                                        if ($fac_proveedor) {
                                                            $fac_pro = $fac_proveedor->liquidacion_serie . ' - ' . $fac_proveedor->liquidacion_correlativo;
                                                        }
                                                    } else {
                                                        // Liquidación no aprobada - usar valores del despacho
                                                        $costoTarifa = ($informacionliquidacion->despacho_estado_modificado == 1)
                                                            ? $informacionliquidacion->despacho_monto_modificado
                                                            : $informacionliquidacion->despacho_flete;
                                                        $costoMano = $informacionliquidacion->despacho_ayudante ?? 0;
                                                        $costoOtros = $informacionliquidacion->despacho_gasto_otros ?? 0;
                                                    }
                                                } else {
                                                    // No existe liquidación - usar valores del despacho
                                                    $costoTarifa = ($informacionliquidacion->despacho_estado_modificado == 1)
                                                        ? $informacionliquidacion->despacho_monto_modificado
                                                        : $informacionliquidacion->despacho_flete;
                                                    $costoMano = $informacionliquidacion->despacho_ayudante ?? 0;
                                                    $costoOtros = $informacionliquidacion->despacho_gasto_otros ?? 0;
                                                }

                                                $rowO_W = $row; // Mantiene una fila separada para O:W
                                                $totalGeneralLocal = ($costoTarifa + $costoMano + $costoOtros);
                                                // Determinar si mostrar PEND o el monto
                                                $valorCeldaP = $this->verificarAprobacion($informacionliquidacion->id_despacho) ? $fac_pro : 'PEND';

                                                // Celdas afectadas entre O y W
                                                $sheet1->setCellValue('L' . $rowO_W, $this->general->formatoDecimal($costoTarifa));
                                                $sheet1->setCellValue('M' . $rowO_W, $this->general->formatoDecimal($costoOtros));
                                                $sheet1->setCellValue('N' . $rowO_W, $this->general->formatoDecimal($costoMano));
                                                $sheet1->setCellValue('O' . $rowO_W, $des->transportista_nom_comercial);
                                                $sheet1->setCellValue('P' . $row, $valorCeldaP);
                                                $sheet1->setCellValue('Q' . $rowO_W, $this->general->formatoDecimal($totalGeneralLocal));
                                                $sheet1->setCellValue('R' . $rowO_W, "");
                                                $sheet1->setCellValue('S' . $rowO_W, "");

                                                $fleteFinalLocal = $totalGeneralLocal;
                                                $filaPorcentajeLocal = $rowO_W;

                                                $rowO_W++;

                                                $vehiculo = DB::table('vehiculos as v')
                                                    ->join('tipo_vehiculos as tv', 'tv.id_tipo_vehiculo', '=', 'v.id_tipo_vehiculo')
                                                    ->where('v.id_vehiculo', '=', $des->id_vehiculo)->first();
                                                $vehiT = "";
                                                if ($vehiculo) {
                                                    $vehiT = $vehiculo->tipo_vehiculo_concepto . ': ' . $vehiculo->vehiculo_capacidad_peso . 'kg - ' . $vehiculo->vehiculo_placa;
                                                }

                                                $comentario = DB::table('despachos')
                                                    ->where('id_despacho', '=', $des->id_despacho)->first();
                                                $comen = "";
                                                if ($comentario && !empty($comentario->despacho_descripcion_otros)) {
                                                    $comen = $comentario->despacho_descripcion_otros;
                                                }

                                                $comentariosLiquidacion = DB::table('liquidacion_detalles')
                                                    ->where('id_despacho', '=', $des->id_despacho)
                                                    ->orderBy('id_liquidacion_detalle', 'desc')
                                                    ->orderBy('id_despacho', 'desc')
                                                    ->first();
                                                $comenLi = "";
                                                if ($comentariosLiquidacion && !empty($comentariosLiquidacion->liquidacion_detalle_comentarios)) {
                                                    $comenLi = $comentariosLiquidacion->liquidacion_detalle_comentarios;
                                                }

                                                // Segunda fila solo en O-W
                                                $sheet1->setCellValue('K' . $rowO_W, "");
                                                $sheet1->setCellValue('L' . $rowO_W, "");
                                                $sheet1->setCellValue('O' . $rowO_W, $vehiT);

                                                if (!empty($comenLi)) {
                                                    // Si hay comentario de liquidación
                                                    $sheet1->setCellValue('M' . $rowO_W, $comen);
                                                    $sheet1->setCellValue('N' . $rowO_W, "");
                                                    $sheet1->setCellValue('P' . $rowO_W, $comenLi);
                                                    $sheet1->setCellValue('Q' . $rowO_W, "");
                                                    $sheet1->setCellValue('R' . $rowO_W, "");

                                                    // Formato para comentario de despacho (solo si hay contenido)
                                                    if (!empty($comen)) {
                                                        $cellRange = 'M' . $rowO_W . ':N' . $rowO_W;
                                                        $sheet1->mergeCells($cellRange);
                                                        $rowStyle = $sheet1->getStyle($cellRange);
                                                        $rowStyle->getFont()->setSize(10);
                                                        $rowStyle->getFont()->setBold(true);
                                                        $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FABF8F');
                                                    }

                                                    // Formato para comentario de liquidación
                                                    $cellRange = 'P' . $rowO_W . ':R' . $rowO_W;
                                                    $sheet1->mergeCells($cellRange);
                                                    $rowStyle = $sheet1->getStyle($cellRange);
                                                    $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FABF8F');
                                                } else {
                                                    // Si no hay comentario de liquidación
                                                    if (!empty($comen)) {
                                                        // Solo mostrar y formatear si hay comentario de despacho
                                                        $sheet1->setCellValue('M' . $rowO_W, $comen);
                                                        $sheet1->setCellValue('N' . $rowO_W, "");

                                                        $cellRange = 'M' . $rowO_W . ':N' . $rowO_W;
                                                        $sheet1->mergeCells($cellRange);
                                                        $rowStyle = $sheet1->getStyle($cellRange);
                                                        $rowStyle->getFont()->setSize(10);
                                                        $rowStyle->getFont()->setBold(true);
                                                        $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FABF8F');
                                                    } else {
                                                        // No mostrar nada si no hay comentarios
                                                        $sheet1->setCellValue('M' . $rowO_W, "");
                                                        $sheet1->setCellValue('N' . $rowO_W, "");
                                                    }

                                                    $sheet1->setCellValue('P' . $rowO_W, "");
                                                    $sheet1->setCellValue('Q' . $rowO_W, "");
                                                    $sheet1->setCellValue('R' . $rowO_W, "");
                                                }
                                            }
                                        } else {
                                            $sheet1->setCellValue('O' . $row, '');
                                            $sheet1->setCellValue('P' . $row, '');
                                            $sheet1->setCellValue('Q' . $row, '');
                                            $sheet1->setCellValue('R' . $row, '');
                                        }

                                        /* PROVINCIAL */
                                        if ($des->id_tipo_servicios == 2) {
                                            if ($indexComprobante == 0) {
                                                $informacionliquidacion = DB::table('despachos')
                                                    ->where('id_despacho', '=', $des->id_despacho)
                                                    ->orderBy('id_despacho', 'desc')->first();

                                                $sheet1->setCellValue('S' . $row, $des->despacho_numero_correlativo);

                                                $liquidacion_detalle = DB::table('liquidacion_detalles')
                                                    ->where('id_despacho', '=', $des->id_despacho)
                                                    ->first();

                                                $fac_pro = "";
                                                $peso = 0;
                                                $costoTarifa = 0;
                                                $costoOtros = 0;

                                                if ($liquidacion_detalle) {
                                                    // Verificar si la liquidación está aprobada
                                                    $liquidacion = DB::table('liquidaciones')
                                                        ->where('id_liquidacion', '=', $liquidacion_detalle->id_liquidacion)
                                                        ->where('liquidacion_estado_aprobacion', 1)
                                                        ->first();

                                                    if ($liquidacion) {
                                                        // Liquidación aprobada - obtener gastos de liquidación (conservando signos)
                                                        $gastos_liquidacion = DB::table('liquidacion_gastos')
                                                            ->where('id_liquidacion_detalle', '=', $liquidacion_detalle->id_liquidacion_detalle)
                                                            ->get();

                                                        foreach ($gastos_liquidacion as $gasto) {
                                                            $valor = floatval($gasto->liquidacion_gasto_monto); // Convertir a float manteniendo signo

                                                            switch ($gasto->liquidacion_gasto_concepto) {
                                                                case 'costo_flete':
                                                                    $costoTarifa = $valor; // Mantenemos el signo original
                                                                    break;
                                                                case 'otros_gasto':
                                                                    $costoOtros = $valor; // Mantenemos el signo original
                                                                    break;
                                                                case 'peso_final_kilos':
                                                                    $peso = $valor; // El peso mantiene su signo (puede ser negativo)
                                                                    break;
                                                            }
                                                        }

                                                        // Obtener factura del proveedor
                                                        $fac_proveedor = DB::table('liquidaciones')
                                                            ->where('id_transportistas', '=', $des->id_transportistas)
                                                            ->where('id_liquidacion', '=', $liquidacion_detalle->id_liquidacion)
                                                            ->first();

                                                        if ($fac_proveedor) {
                                                            $fac_pro = $fac_proveedor->liquidacion_serie . ' - ' . $fac_proveedor->liquidacion_correlativo;
                                                        }
                                                    } else {
                                                        // Liquidación no aprobada - usar valores del despacho (conservando signos)
                                                        $peso = isset($informacionliquidacion->despacho_peso) ? floatval($informacionliquidacion->despacho_peso) : 0;

                                                        $costoTarifa = isset($informacionliquidacion->despacho_estado_modificado) && $informacionliquidacion->despacho_estado_modificado == 1
                                                            ? floatval($informacionliquidacion->despacho_monto_modificado)
                                                            : (isset($informacionliquidacion->despacho_flete) ? floatval($informacionliquidacion->despacho_flete) : 0);

                                                        $costoOtros = isset($informacionliquidacion->despacho_gasto_otros) ? floatval($informacionliquidacion->despacho_gasto_otros) : 0;
                                                    }
                                                } else {
                                                    // No existe liquidación - usar valores del despacho (conservando signos)
                                                    $peso = isset($informacionliquidacion->despacho_peso) ? floatval($informacionliquidacion->despacho_peso) : 0;

                                                    $costoTarifa = isset($informacionliquidacion->despacho_estado_modificado) && $informacionliquidacion->despacho_estado_modificado == 1
                                                        ? floatval($informacionliquidacion->despacho_monto_modificado)
                                                        : (isset($informacionliquidacion->despacho_flete) ? floatval($informacionliquidacion->despacho_flete) : 0);

                                                    $costoOtros = isset($informacionliquidacion->despacho_gasto_otros) ? floatval($informacionliquidacion->despacho_gasto_otros) : 0;
                                                }

                                                $rowW_X = $row;
                                                $totalGeneralLocalProvin = (floatval($costoTarifa) * floatval($peso) + floatval($costoOtros));

                                                $destino = "";
                                                $departamentoNombre = "";
                                                if (isset($informacionliquidacion->id_departamento) && $informacionliquidacion->id_departamento) {
                                                    $dep = DB::table('departamentos')->where('id_departamento', '=', $informacionliquidacion->id_departamento)->first();
                                                    $departamentoNombre = $dep->departamento_nombre ?? '';
                                                    $destino .= $departamentoNombre;
                                                }
                                                if (isset($informacionliquidacion->id_provincia) && $informacionliquidacion->id_provincia) {
                                                    $provi = DB::table('provincias')->where('id_provincia', '=', $informacionliquidacion->id_provincia)->first();
                                                    $destino .= "-" . ($provi->provincia_nombre ?? '');
                                                }

                                                // Determinar la zona (LOCAL, PROVINCIA 1 o PROVINCIA 2)
                                                $zonaP = "PROVINCIA"; // Valor por defecto
                                                if (!empty($departamentoNombre)) {
                                                    $departamentosZona = $this->general->listar_departamento_zona();

                                                    if (in_array($departamentoNombre, $departamentosZona[0] ?? [])) {
                                                        $zonaP = "LOCAL";
                                                    } elseif (in_array($departamentoNombre, $departamentosZona[1] ?? [])) {
                                                        $zonaP = "PROVINCIA 1";
                                                    } elseif (in_array($departamentoNombre, $departamentosZona[2] ?? [])) {
                                                        $zonaP = "PROVINCIA 2";
                                                    }
                                                }
                                                // Determinar si mostrar PEND o el monto
                                                $valorCeldaPro = $this->verificarAprobacion($informacionliquidacion->id_despacho) ? $fac_pro : 'PEND';

                                                // Primera fila (datos principales)
                                                $sheet1->setCellValue('T' . $rowW_X, $des->transportista_nom_comercial ?? '');
                                                $sheet1->setCellValue('U' . $rowW_X, $destino);
                                                $sheet1->setCellValue('V' . $rowW_X, $zonaP);
                                                $sheet1->setCellValue('W' . $rowW_X, $valorCeldaPro);
                                                $sheet1->setCellValue('X' . $rowW_X, $this->general->formatoDecimal($totalGeneralLocalProvin));
                                                $sheet1->setCellValue('Y' . $rowW_X, '');

                                                // Aplicar bordes a la primera fila
                                                $firstRowRange = 'A' . $rowW_X . ':Y' . $rowW_X;
                                                $sheet1->getStyle($firstRowRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                                                $fleteFinalProvin = $totalGeneralLocalProvin;
                                                $filaPorcentajeProvin = $rowW_X;

                                                $rowW_X++;

                                                $comentariosLiquidacion = DB::table('liquidacion_detalles')
                                                    ->where('id_despacho', '=', $des->id_despacho)
                                                    ->orderBy('id_liquidacion_detalle', 'desc')
                                                    ->orderBy('id_despacho', 'desc')
                                                    ->first();
                                                $comenLi = $comentariosLiquidacion->liquidacion_detalle_comentarios ?? "";

                                                $comentario = DB::table('despachos')
                                                    ->where('id_despacho', '=', $des->id_despacho)
                                                    ->first();
                                                $comenDesPro = $comentario->despacho_descripcion_otros ?? "";

                                                // Configuración común para todas las celdas
                                                $sheet1->setCellValue('S' . $rowW_X, "");
                                                $sheet1->setCellValue('T' . $rowW_X, "");
                                                $sheet1->setCellValue('U' . $rowW_X, "");
                                                $sheet1->setCellValue('V' . $rowW_X, "");
                                                $sheet1->setCellValue('W' . $rowW_X, "");
                                                $sheet1->setCellValue('X' . $rowW_X, "");
                                                $sheet1->setCellValue('Y' . $rowW_X, "");

                                                // Solo procesar comentarios de despacho si existen
                                                if (!empty($comenDesPro)) {
                                                    $sheet1->setCellValue('S' . $rowW_X, $comenDesPro);
                                                    $cellRangeDespacho = 'S' . $rowW_X . ':T' . $rowW_X;
                                                    $sheet1->mergeCells($cellRangeDespacho);
                                                    $rowStyleDespacho = $sheet1->getStyle($cellRangeDespacho);
                                                    $rowStyleDespacho->getFont()->setSize(10);
                                                    $rowStyleDespacho->getFont()->setBold(true);
                                                    $rowStyleDespacho->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FABF8F');
                                                }

                                                // Solo procesar comentarios de liquidación si existen
                                                if (!empty($comenLi)) {
                                                    $sheet1->setCellValue('W' . $rowW_X, $comenLi);
                                                    $cellRangeLiquidacion = 'W' . $rowW_X . ':Y' . $rowW_X;
                                                    $sheet1->mergeCells($cellRangeLiquidacion);
                                                    $rowStyleLiquidacion = $sheet1->getStyle($cellRangeLiquidacion);
                                                    $rowStyleLiquidacion->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FABF8F');
                                                }

                                                $cellRange = 'A' . $rowW_X . ':Y' . $rowW_X;
                                                $rowStyle = $sheet1->getStyle($cellRange);
                                                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                                                $rowStyle = $sheet1->getStyle($cellRange);
//                                                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                            }
                                        }
                                    }
                                    // ACA ESTA PARTE MUESTRO LO QUE SERIA MIXTA DE PROVINCIAL
                                    // Dentro del foreach($des->comprobantes as $indexComprobante => $comproba):
                                    if ($typeComprop == 3) {
                                        /*--------------------------------------------------------------------------------------- */
                                        /* SI EN CASO LA PROGRAMACIÓN ES MIXTA LLENAR LAS OS DE PROVINCIAL */
                                        $osMixtoProgramacion = DB::table('despachos as d')
                                            ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                                            ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                                            ->where('d.id_programacion', '=', $resPro->id_programacion)
                                            ->where('dv.id_guia', '=', $comproba->id_guia)
                                            ->where('d.id_tipo_servicios', '=', 2)
                                            ->first();

                                        // Aplicar filtro por estado de liquidación para despachos mixtos
                                        $mostrarDespachoMixto = true;
                                        if ($this->estadoPro !== null && $this->estadoPro !== '' && $osMixtoProgramacion) {
                                            $aprobadoMixto = $this->verificarAprobacion($osMixtoProgramacion->id_despacho);

                                            if (($this->estadoPro == 1 && !$aprobadoMixto) || ($this->estadoPro == 0 && $aprobadoMixto)) {
                                                $mostrarDespachoMixto = false;
                                            }
                                        }

                                        if ($osMixtoProgramacion && $mostrarDespachoMixto) {
                                            if (!$osAnteriorMixto) { // SI ESTO ES NULO ES POR QUE AUN NO ESTA CON VALOR DE OS
                                                $osAnteriorMixto = $osMixtoProgramacion->despacho_numero_correlativo ?? '';
                                                $ingreExcelMixto = true;
                                            } else {
                                                if (($osMixtoProgramacion->despacho_numero_correlativo ?? '') == $osAnteriorMixto) { // SI LA OS QUE INGRESA YA ESTA COMO VALOR EN $osAnteriorMixto ES POR QUE YA SE PUSO COMO VALOR AL PRIMERO
                                                    $ingreExcelMixto = false;
                                                } else {
                                                    $osAnteriorMixto = $osMixtoProgramacion->despacho_numero_correlativo ?? '';
                                                    $ingreExcelMixto = true;
                                                }
                                            }

                                            if ($ingreExcelMixto) {
                                                $totalImporteComprobanteDespachoPro = DB::table('despacho_ventas as dv')
                                                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                                                    ->where('dv.id_despacho', '=', $osMixtoProgramacion->id_despacho)
                                                    ->sum('g.guia_importe_total_sin_igv');

                                                $InformacionDespachoMixto = DB::table('despachos')
                                                    ->where('id_despacho', '=', $osMixtoProgramacion->id_despacho)
                                                    ->orderBy('id_despacho', 'desc')->first();

                                                // Verificar si existe liquidación para este despacho
                                                $liquidacion_detalle = DB::table('liquidacion_detalles')
                                                    ->where('id_despacho', '=', $osMixtoProgramacion->id_despacho)
                                                    ->first();

                                                $fac_pro = "";
                                                $costoTarifa = 0;
                                                $costoOtros = 0;
                                                $peso = 0;

                                                if ($liquidacion_detalle) {
                                                    // Buscar liquidación aprobada
                                                    $liquidacion = DB::table('liquidaciones')
                                                        ->where('id_liquidacion', '=', $liquidacion_detalle->id_liquidacion)
                                                        ->where('liquidacion_estado_aprobacion', 1)
                                                        ->first();

                                                    if ($liquidacion) {
                                                        // Obtener valores de liquidación (respetando signos)
                                                        $gastos_liquidacion = DB::table('liquidacion_gastos')
                                                            ->where('id_liquidacion_detalle', '=', $liquidacion_detalle->id_liquidacion_detalle)
                                                            ->get();

                                                        foreach ($gastos_liquidacion as $gasto) {
                                                            $valor = floatval($gasto->liquidacion_gasto_monto);
                                                            switch ($gasto->liquidacion_gasto_concepto) {
                                                                case 'costo_flete':
                                                                    $costoTarifa = $valor; // Mantiene signo
                                                                    break;
                                                                case 'otros_gasto':
                                                                    $costoOtros = $valor; // Mantiene signo
                                                                    break;
                                                                case 'peso_final_kilos':
                                                                    $peso = $valor; // Mantiene signo
                                                                    break;
                                                            }
                                                        }

                                                        // Obtener factura del proveedor
                                                        $fac_proveedor = DB::table('liquidaciones')
                                                            ->where('id_transportistas', '=', $osMixtoProgramacion->id_transportistas)
                                                            ->where('id_liquidacion', '=', $liquidacion_detalle->id_liquidacion)
                                                            ->first();

                                                        if ($fac_proveedor) {
                                                            $fac_pro = $fac_proveedor->liquidacion_serie . ' - ' . $fac_proveedor->liquidacion_correlativo;
                                                        }
                                                    }
                                                }

                                                // Si no hay liquidación o no está aprobada, usar valores del despacho
                                                if (!$liquidacion_detalle || !isset($liquidacion)) {
                                                    $peso = floatval($InformacionDespachoMixto->despacho_peso ?? 0);
                                                    $costoTarifa = ($InformacionDespachoMixto->despacho_estado_modificado == 1)
                                                        ? floatval($InformacionDespachoMixto->despacho_monto_modificado)
                                                        : floatval($InformacionDespachoMixto->despacho_flete);
                                                    $costoOtros = floatval($InformacionDespachoMixto->despacho_gasto_otros ?? 0);

                                                    // Obtener factura del proveedor (versión original)
                                                    $fac_proveedor = DB::table('liquidaciones')
                                                        ->where('id_transportistas', '=', $osMixtoProgramacion->id_transportistas)
                                                        ->first();
                                                    if ($fac_proveedor) {
                                                        $fac_pro = $fac_proveedor->liquidacion_serie . ' - ' . $fac_proveedor->liquidacion_correlativo;
                                                    }
                                                }

                                                $rowW_XX = $row;
                                                $totalGeneralMixto = ($costoTarifa * $peso) + $costoOtros;

                                                $destino = "";
                                                $departamentoNombre = "";
                                                if ($InformacionDespachoMixto->id_departamento ?? false) {
                                                    $dep = DB::table('departamentos')->where('id_departamento', '=', $InformacionDespachoMixto->id_departamento)->first();
                                                    $departamentoNombre = $dep->departamento_nombre ?? '';
                                                    $destino .= $departamentoNombre;
                                                }
                                                if ($InformacionDespachoMixto->id_provincia ?? false) {
                                                    $provi = DB::table('provincias')->where('id_provincia', '=', $InformacionDespachoMixto->id_provincia)->first();
                                                    $destino .= "-" . ($provi->provincia_nombre ?? '');
                                                }

                                                // Determinar la zona (LOCAL, PROVINCIA 1 o PROVINCIA 2)
                                                $zona = "PROVINCIA"; // Valor por defecto
                                                if (!empty($departamentoNombre)) {
                                                    $departamentosZona = $this->general->listar_departamento_zona();

                                                    if (in_array($departamentoNombre, $departamentosZona[0] ?? [])) {
                                                        $zona = "LOCAL";
                                                    } elseif (in_array($departamentoNombre, $departamentosZona[1] ?? [])) {
                                                        $zona = "PROVINCIA 1";
                                                    } elseif (in_array($departamentoNombre, $departamentosZona[2] ?? [])) {
                                                        $zona = "PROVINCIA 2";
                                                    }
                                                }

                                                $aprPenMX = "";
                                                if (!$this->verificarAprobacion($osMixtoProgramacion->id_despacho)) {
                                                    $aprPenMX = 'PEND';
                                                }

                                                $comentariosLiquidacion = DB::table('liquidacion_detalles')->where('id_despacho', '=', $des->id_despacho)->orderBy('id_liquidacion_detalle', 'desc')->orderBy('id_despacho', 'desc')->first();

                                                $sheet1->setCellValue('S' . $rowW_XX, $osMixtoProgramacion->despacho_numero_correlativo ?? '');
                                                $sheet1->setCellValue('T' . $rowW_XX, $osMixtoProgramacion->transportista_nom_comercial ?? '');
                                                $sheet1->setCellValue('U' . $rowW_XX, $destino);
                                                $sheet1->setCellValue('V' . $rowW_XX, $zona);
                                                $sheet1->setCellValue('W' . $rowW_XX, ($aprPenMX == 'PEND') ? 'PEND' : $fac_pro);
                                                $sheet1->setCellValue('X' . $rowW_XX, $this->general->formatoDecimal($totalGeneralMixto));
                                                $poMixto = $totalImporteComprobanteDespachoPro != 0 ? ($totalGeneralMixto / floatval($totalImporteComprobanteDespachoPro)) * 100 : 0;
                                                $sheet1->setCellValue('Y' . $rowW_XX, $this->general->formatoDecimal($poMixto));

                                                $firstRowRange = 'A' . $rowW_XX . ':Y' . $rowW_XX;
                                                $sheet1->getStyle($firstRowRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                                                $cellRange = 'A' . $rowW_XX . ':Y' . $rowW_XX;
                                                $rowStyle = $sheet1->getStyle($cellRange);
                                                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                                                $rowStyle = $sheet1->getStyle($cellRange);

                                                $rowW_XX++;

                                                // Obtener comentarios con sintaxis más limpia
                                                $comenDesMix = DB::table('despachos')
                                                    ->where('id_despacho', '=', $osMixtoProgramacion->id_despacho)
                                                    ->value('despacho_descripcion_otros') ?? "";

                                                $comenLi = DB::table('liquidacion_detalles')
                                                    ->where('id_despacho', '=', $osMixtoProgramacion->id_despacho)
                                                    ->orderBy('id_liquidacion_detalle', 'desc')
                                                    ->orderBy('id_despacho', 'desc')
                                                    ->value('liquidacion_detalle_comentarios') ?? "";

                                                // Inicializar todas las celdas como vacías
                                                $sheet1->setCellValue('S' . $rowW_XX, "");
                                                $sheet1->setCellValue('T' . $rowW_XX, "");
                                                $sheet1->setCellValue('U' . $rowW_XX, "");
                                                $sheet1->setCellValue('V' . $rowW_XX, "");
                                                $sheet1->setCellValue('W' . $rowW_XX, "");
                                                $sheet1->setCellValue('X' . $rowW_XX, "");
                                                $sheet1->setCellValue('Y' . $rowW_XX, "");

                                                // Aplicar formato solo si hay contenido
                                                if (!empty($comenDesMix)) {
                                                    $sheet1->setCellValue('S' . $rowW_XX, $comenDesMix);
                                                    $cellRangeDespacho = 'S' . $rowW_XX . ':T' . $rowW_XX;
                                                    $sheet1->mergeCells($cellRangeDespacho);

                                                    $styleDespacho = $sheet1->getStyle($cellRangeDespacho);
                                                    $styleDespacho->getFont()
                                                        ->setSize(10)
                                                        ->setBold(true);
                                                    $styleDespacho->getFill()
                                                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                                        ->getStartColor()->setARGB('FABF8F');
                                                }

                                                if (!empty($comenLi)) {
                                                    $sheet1->setCellValue('W' . $rowW_XX, $comenLi);
                                                    $cellRangeLiquidacion = 'W' . $rowW_XX . ':Y' . $rowW_XX;
                                                    $sheet1->mergeCells($cellRangeLiquidacion);

                                                    $styleLiquidacion = $sheet1->getStyle($cellRangeLiquidacion);
                                                    $styleLiquidacion->getFill()
                                                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                                        ->getStartColor()->setARGB('FABF8F');
                                                }
                                            }
                                        }
                                    }
                                    /*--------------------------------------------------------------------------------------- */
                                    $cellRange = 'A' . $row . ':Y' . $row;
                                    $rowStyle = $sheet1->getStyle($cellRange);
                                    $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                                    $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
                                    $cellRange = 'E' . $row . ':E' . $row;
                                    $rowStyle = $sheet1->getStyle($cellRange);
//                        $rowStyle->getFont()->setSize(10);
                                    $rowStyle->getFont()->setBold(false);

                                    $cellRange = 'O' . $row . ':AA' . $row;
                                    $rowStyle = $sheet1->getStyle($cellRange);
//                        $rowStyle->getFont()->setBold(true);
//                                    $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
//                                    $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
//                                $sheet1->mergeCells('P'.$row.':Q'.$row);

                                    $row++;
                                    // Obtener valores necesarios
                                    $importe_sin_igv = $comproba->guia_importe_total_sin_igv;
                                    $peso_total_pro = $comproba->peso_total_kilos;
                                    $estadoEntrega = DB::table('despacho_ventas')
                                        ->where('id_guia', $comproba->id_guia)
                                        ->value('despacho_detalle_estado_entrega');
                                    $estadoAprobacion = $comproba->guia_estado_aprobacion;

                                    // Sumar importe bajo condiciones
                                    if ($estadoEntrega == 0 && $estadoAprobacion == 8) {
                                        $importeTotalDespachos += $importe_sin_igv;
                                        $totalPesoDespachos += $peso_total_pro;
                                    } elseif ($estadoEntrega == 8) {
                                        $importeTotalDespachos += $importe_sin_igv;
                                        $totalPesoDespachos += $peso_total_pro;
                                    }
                                }

                                $sheet1->setCellValue('A'.$row, "");
                                $sheet1->setCellValue('B'.$row, "");
                                $sheet1->setCellValue('C'.$row, "");
                                $sheet1->setCellValue('D'.$row, "");
                                $sheet1->setCellValue('E'.$row, $this->general->formatoDecimal($importeTotalDespachos));
                                $sheet1->setCellValue('F'.$row, "");
                                $sheet1->getStyle('E'.$row)->getFont()->setBold(true);
                                $sheet1->getStyle('E'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                                $sheet1->setCellValue('G'.$row, "");
                                $sheet1->setCellValue('H'.$row, "");
                                $sheet1->setCellValue('I'.$row, "");
                                $sheet1->setCellValue('J'.$row, $this->general->formatoDecimal($totalPesoDespachos));
                                $sheet1->setCellValue('K'.$row, "");
                                $sheet1->setCellValue('L'.$row, "");
                                $sheet1->setCellValue('M'.$row, "");
                                $sheet1->setCellValue('N'.$row, "");
                                $cellRange = 'A'.$row.':N'.$row;
                                $rowStyle = $sheet1->getStyle($cellRange);
                                $rowStyle->getFont()->setSize(10);
                                $rowStyle->getFont()->setBold(true);
                                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                                $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
                                $row++;
                                /* ----------------------------------------------- */
                                if ($filaPorcentajeLocal) {
                                    if ($importeTotalDespachos != 0) {
                                        $porcentaje = (($fleteFinalLocal / $importeTotalDespachos) * 100);
                                        $porcentaje = $this->general->formatoDecimal($porcentaje);
                                        $sheet1->setCellValue('R'.$filaPorcentajeLocal, $this->general->formatoDecimal($porcentaje));
                                    } else {
                                        $sheet1->setCellValue('R'.$filaPorcentajeLocal, '0%');
                                    }
                                }

                                if ($filaPorcentajeProvin) {
                                    if ($importeTotalDespachos != 0) {
                                        $porcentaje = (($fleteFinalProvin / $importeTotalDespachos) * 100);
                                        $porcentaje = $this->general->formatoDecimal($porcentaje);
                                        $sheet1->setCellValue('Y'.$filaPorcentajeProvin, $this->general->formatoDecimal($porcentaje));
                                    } else {
                                        $sheet1->setCellValue('Y'.$filaPorcentajeProvin, '0%');
                                    }
                                }

                                /* ----------------------------------------------- */

                                $cellRange = 'A'.$row.':Y'.$row;
//                                $sheet1->mergeCells($cellRange);
                                $row++;
                            }
                        }
                    }
                }

                // Crear la hoja de resumen
                $sheet2 = $spreadsheet->createSheet();
                $sheet2->setTitle('Resumen por Zonas');

                $row = 1;
                // Calcular los totales provinciales
                $totalProvincial = $totalProvincia1 + $totalProvincia2;
                // Totales de fletes
                $totalFleteAprobado = $fleteAprobadoLocal + $fleteAprobadoProv1 + $fleteAprobadoProv2;
                $totalFletePenal = $fletePenalLocal + $fletePenalProv1 + $fletePenalProv2;
                $totalFleteGeneral = $totalFleteAprobado + $totalFletePenal;

                $totalFleteAprobadoProv = $fleteAprobadoProv1 + $fleteAprobadoProv2;
                $totalFletePenalProv = $fletePenalProv1 + $fletePenalProv2;
                $totalFleteProv = $totalFleteAprobadoProv + $totalFletePenalProv;
                // Insertar la tabla en el Excel
                $sheet2->setCellValue('A'.$row, 'Zona de Despacho');
                $sheet2->setCellValue('B'.$row, 'Valor Transportado (Soles sin IGV)');
                $sheet2->setCellValue('C'.$row, 'Flete Aprobados (Soles)');
                $sheet2->setCellValue('D'.$row, 'Flete Pend. De Aprobación');
                $sheet2->setCellValue('E'.$row, 'Total Flete (Soles)');
                // Estilo para los encabezados
                $headerStyle = $sheet2->getStyle('A'.$row.':E'.$row);
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('D9D9D9');
                $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $row++;
                // Datos de la tabla con los valores calculados (igual que en la vista)
                $tableData = [
                    ['Total', $totalGeneral, $totalFleteAprobado, $totalFletePenal, $totalFleteGeneral],
                    ['Local', $totalLocal, $fleteAprobadoLocal, $fletePenalLocal, ($fleteAprobadoLocal + $fletePenalLocal)],
                    ['Provincia 1', $totalProvincia1, $fleteAprobadoProv1, $fletePenalProv1, ($fleteAprobadoProv1 + $fletePenalProv1)],
                    ['Provincia 2', $totalProvincia2, $fleteAprobadoProv2, $fletePenalProv2, ($fleteAprobadoProv2 + $fletePenalProv2)],
                    ['Total Provincia', $totalProvincial, $totalFleteAprobadoProv, $totalFletePenalProv, $totalFleteProv]
                ];

                foreach ($tableData as $data) {
                    $sheet2->setCellValue('A'.$row, $data[0]);
                    $sheet2->setCellValue('B'.$row, $data[1]);
                    $sheet2->setCellValue('C'.$row, $data[2]);
                    $sheet2->setCellValue('D'.$row, $data[3]);
                    $sheet2->setCellValue('E'.$row, $data[4]);

                    // Formato numérico para las columnas de valores
                    $sheet2->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet2->getStyle('C'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet2->getStyle('D'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet2->getStyle('E'.$row)->getNumberFormat()->setFormatCode('#,##0.00');

                    // Bordes para las celdas
                    $cellStyle = $sheet2->getStyle('A'.$row.':E'.$row);
                    $cellStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    $row++;
                }
                // Ajustar anchos de columnas
                $sheet2->getColumnDimension('A')->setWidth(20);
                $sheet2->getColumnDimension('B')->setWidth(25);
                $sheet2->getColumnDimension('C')->setWidth(20);
                $sheet2->getColumnDimension('D')->setWidth(20);
                $sheet2->getColumnDimension('E')->setWidth(20);
            }else{
                session()->flash('error', 'Ocurrió un error: no existen despachos finalizados.');
                return;
            }
            $desde = $this->desde;
            $hasta = $this->hasta;
            // Formatear el nombre del archivo Excel
            $nombre_excel = sprintf(
                "historial_de_programacion_de_%s_a_%s_.xlsx",
                date('d-m-Y', strtotime($desde)),
                $hasta
            );

            $response = response()->stream(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename=' . $nombre_excel,
                ]
            );
            return $response;

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
        }
    }

//    CAMBIAR EL ESTADO A EN RUTA
    public function cambiarEstadoDespachoFormulario() {
        try {
            if (!Gate::allows('cambiar_estado_despacho')) {
                session()->flash('error_delete', 'No tiene permisos para poder cambiar el estado del despacho.');
                return;
            }

            if (count($this->selectedItems) > 0) {
                // Validación para múltiples despachos
                $this->validate([
                    'selectedItems' => 'required|array|min:1',
                ], [
                    'selectedItems.required' => 'Debe seleccionar al menos una opción.',
                    'selectedItems.array'    => 'La selección debe ser válida.',
                    'selectedItems.min'      => 'Debe seleccionar al menos una opción.',
                ]);

                DB::beginTransaction();

                foreach ($this->selectedItems as $select) {
                    $this->procesarCambioEstadoDespacho($select);
                }

                DB::commit();
                $this->selectedItems = [];
                $this->dispatch('hideModalDelete');
                session()->flash('success', 'Despachos en camino.');
            } else {
                // Validación para un solo despacho
                $this->validate([
                    'id_despacho' => 'required|integer',
                ], [
                    'id_despacho.required' => 'El identificador es obligatorio.',
                    'id_despacho.integer' => 'El identificador debe ser un número entero.',
                ]);

                DB::beginTransaction();
                $this->procesarCambioEstadoDespacho($this->id_despacho);
                DB::commit();
                $this->dispatch('hideModalDelete');
                session()->flash('success', 'Despacho en camino.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al cambiar el estado del registro. Por favor, inténtelo nuevamente.');
        }
    }
    public function procesarCambioEstadoDespacho($idDespacho) {
        $updateDespacho = Despacho::find($idDespacho);
        $updateDespacho->despacho_estado_aprobacion = 2;

        if (!$updateDespacho->save()) {
            session()->flash('error', 'No se pudo cambiar el estado del despacho');
        }

        // Verificar si es una programación mixta
        $esMixta = $this->esProgramacionMixta($updateDespacho->id_programacion);

        // Obtener despacho provincial si es mixta
        $despachoProvincial = null;
        if ($esMixta) {
            $despachoProvincial = DB::table('despachos')
                ->where('id_programacion', $updateDespacho->id_programacion)
                ->where('id_tipo_servicios', 2) // Tipo servicio provincial
                ->first();
        }

        // Obtener las guías relacionadas con el despacho
        $despachoVentas = DB::table('despacho_ventas')
            ->where('id_despacho', $idDespacho)
            ->get();

        $guiasProcesadas = [];

        foreach ($despachoVentas as $despachoVenta) {
            if (in_array($despachoVenta->id_guia, $guiasProcesadas)) {
                continue;
            }

            // Actualizar estado de la guía
            DB::table('guias')
                ->where('id_guia', $despachoVenta->id_guia)
                ->update([
                    'guia_estado_aprobacion' => 7, // "En Ruta"
                    'updated_at' => now('America/Lima')
                ]);

            $guia = DB::table('guias')
                ->where('id_guia', $despachoVenta->id_guia)
                ->first();

            if ($guia) {
                $registrarHistorial = false;

                if (!$esMixta) {
                    $registrarHistorial = true;
                } elseif ($esMixta) {
                    // Verificar si la guía está asociada a otro despacho de tipo provincial
                    $guiaEnDespachoProvincial = DB::table('despacho_ventas')
                        ->join('despachos', 'despacho_ventas.id_despacho', '=', 'despachos.id_despacho')
                        ->where('despacho_ventas.id_guia', $guia->id_guia)
                        ->where('despachos.id_programacion', $updateDespacho->id_programacion)
                        ->where('despachos.id_tipo_servicios', 2) // Provincial
                        ->exists();

                    // Si la guía no tiene un despacho provincial asociado => es solo local
                    if (!$guiaEnDespachoProvincial) {
                        $registrarHistorial = true;
                    }

                    // O si este despacho es el despacho provincial
                    if ($despachoProvincial && $despachoProvincial->id_despacho == $idDespacho) {
                        $registrarHistorial = true;
                    }
                }

                if ($registrarHistorial) {
                    DB::table('historial_guias')->insert([
                        'id_users' => Auth::id(),
                        'id_guia' => $guia->id_guia,
                        'guia_nro_doc' => $guia->guia_nro_doc,
                        'historial_guia_estado_aprobacion' => 7,
                        'historial_guia_fecha_hora' => Carbon::now('America/Lima'),
                        'historial_guia_estado' => 1,
                        'created_at' => Carbon::now('America/Lima'),
                        'updated_at' => Carbon::now('America/Lima'),
                    ]);
                }

                $guiasProcesadas[] = $despachoVenta->id_guia;
            }
        }

        // Actualizar servicios de transporte
        foreach ($despachoVentas as $servicio) {
            DB::table('servicios_transportes')
                ->where('id_serv_transpt', $servicio->id_serv_transpt)
                ->update(['serv_transpt_estado_aprobacion' => 4]);
        }
    }
    public function esProgramacionMixta($idProgramacion) {
        // Contar cuántos despachos diferentes comparten la misma programación
        $countDespachos = DB::table('despachos')
            ->where('id_programacion', $idProgramacion)
            ->count();

        return ($countDespachos > 1);
    }
//    FIN CAMBIAR EL ESTADO A EN RUTA

    public function cambiarEstadoProgramacionAprobada(){
        try {

            if (!Gate::allows('retornarProgramacionAprobada')) {
                session()->flash('error_retornar', 'No tiene permisos para poder retornar esta programación a "Programaciones Pendientes".');
                return;
            }
            $this->validate([
                'id_programacionRetorno' => 'required|integer',
            ], [
                'id_programacionRetorno.required' => 'El identificador es obligatorio.',
                'id_programacionRetorno.integer' => 'El identificador debe ser un número entero.',
            ]);

            DB::beginTransaction();

            $updateProgramacion = Programacion::find($this->id_programacionRetorno);
            $updateProgramacion->programacion_estado_aprobacion = 0;
            if ($updateProgramacion->save()){
                DB::commit();
                $this->dispatch('hideModalDeleteRetornar');
                session()->flash('success', 'Programación retornada a "Programaciones Pendientes".');
            }else{
                DB::rollBack();
                session()->flash('error_retornar', 'No se pudo retornar la programación  a "Programaciones Pendientes"');
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

    public function cambiarEstadoComprobante() {
        try {
            if (!Gate::allows('cambiar_estado_comprobante')) {
                session()->flash('errorComprobante', 'No tiene permisos para poder cambiar el estado del comprobante.');
                return;
            }

            DB::beginTransaction();
            $id_despacho = $this->currentDespachoId;

            // Obtener información del despacho actual
            $despachoActual = DB::table('despachos')
                ->where('id_despacho', $id_despacho)
                ->first();

            if (!$despachoActual) {
                DB::rollBack();
                session()->flash('errorComprobante', 'Despacho no encontrado.');
                return;
            }

            // Verificar si es programación mixta
            $esProgramacionMixta = $this->esProgramacionMixta($despachoActual->id_programacion);
            $esDespachoProvincial = ($despachoActual->id_tipo_servicios == 2);

            // Variables para determinar el estado final del despacho
            $tieneGuias = false;
            $tieneGuiasEntregadas = false;
            $tieneGuiasNoEntregadas = false;
            $tieneServicios = false;
            $tieneServiciosEntregados = false;
            $tieneServiciosNoEntregados = false;

            // Actualizar estados en despacho_ventas y evaluar estados
            foreach ($this->estadoComprobante as $key => $estado) {
                $parts = explode('_', $key);
                if ($parts[0] != $id_despacho) continue;

                $id_despacho_venta = $parts[1];
                $es = (int)$estado;

                if (!in_array($es, [8, 11])) {
                    DB::rollBack();
                    session()->flash('errorComprobante', 'Estado inválido seleccionado para guía.');
                    return;
                }

                $despachoVenta = DB::table('despacho_ventas')
                    ->where('id_despacho_venta', $id_despacho_venta)
                    ->first();

                if (!$despachoVenta) {
                    DB::rollBack();
                    session()->flash('errorComprobante', 'Comprobante no encontrado.');
                    return;
                }

                // Actualizar estado en despacho_ventas
                DB::table('despacho_ventas')
                    ->where('id_despacho_venta', $id_despacho_venta)
                    ->update([
                        'despacho_detalle_estado_entrega' => $es,
                        'updated_at' => now('America/Lima')
                    ]);

                // Actualización adicional para la tabla guías
                if ($es == 11) {
                    DB::table('guias')
                        ->where('id_guia', $despachoVenta->id_guia)
                        ->update([
                            'guia_estado_aprobacion' => 11,
                            'updated_at' => now('America/Lima')
                        ]);
                }

                // Evaluar estado para determinar estado del despacho
                $tieneGuias = true;
                if ($es == 8) {
                    $tieneGuiasEntregadas = true;
                } elseif ($es == 11) {
                    $tieneGuiasNoEntregadas = true;
                }

                // Obtener guía para historial
                $guia = DB::table('guias')->where('id_guia', $despachoVenta->id_guia)->first();

                if ($guia) {
                    $registrarHistorial = false;

                    if (!$esProgramacionMixta) {
                        $registrarHistorial = true;
                    } elseif ($esProgramacionMixta) {
                        // Verificar si la guía también está en un despacho provincial en la misma programación
                        $guiaEnDespachoProvincial = DB::table('despacho_ventas')
                            ->join('despachos', 'despacho_ventas.id_despacho', '=', 'despachos.id_despacho')
                            ->where('despacho_ventas.id_guia', $guia->id_guia)
                            ->where('despachos.id_programacion', $despachoActual->id_programacion)
                            ->where('despachos.id_tipo_servicios', 2) // Provincial
                            ->exists();

                        // Si no está en un despacho provincial => registrar historial
                        if (!$guiaEnDespachoProvincial) {
                            $registrarHistorial = true;
                        }

                        // O si el despacho actual es provincial
                        if ($esDespachoProvincial) {
                            $registrarHistorial = true;
                        }
                    }

                    if ($registrarHistorial) {
                        DB::table('historial_guias')->insert([
                            'id_users' => Auth::id(),
                            'id_guia' => $guia->id_guia,
                            'guia_nro_doc' => $guia->guia_nro_doc,
                            'historial_guia_estado_aprobacion' => $es,
                            'historial_guia_fecha_hora' => Carbon::now('America/Lima'),
                            'historial_guia_estado' => 1,
                            'created_at' => Carbon::now('America/Lima'),
                            'updated_at' => Carbon::now('America/Lima'),
                        ]);
                    }
                }
            }

            // Actualizar estados de servicios de transporte y evaluar estados
            foreach ($this->estadoServicio as $key => $estado) {
                $parts = explode('_', $key);
                if ($parts[0] != $id_despacho) continue;

                $id_despacho_venta = $parts[1];
                $es = (int)$estado;

                if (!in_array($es, [5, 6])) {
                    DB::rollBack();
                    session()->flash('errorComprobante', 'Estado inválido seleccionado para servicio de transporte.');
                    return;
                }

                $despachoVenta = DB::table('despacho_ventas')
                    ->where('id_despacho_venta', $id_despacho_venta)
                    ->first();

                if (!$despachoVenta) {
                    DB::rollBack();
                    session()->flash('errorComprobante', 'Servicio de transporte no encontrado.');
                    return;
                }

                DB::table('servicios_transportes')
                    ->where('id_serv_transpt', $despachoVenta->id_serv_transpt)
                    ->update(['serv_transpt_estado_aprobacion' => $es]);

                // Evaluar estado para determinar estado del despacho
                $tieneServicios = true;
                if ($es == 5) {
                    $tieneServiciosEntregados = true;
                } elseif ($es == 6) {
                    $tieneServiciosNoEntregados = true;
                }
            }

            // Determinar el estado final del despacho actual
            $estadoDespacho = 4; // Por defecto rechazado

            if ($tieneGuias || $tieneServicios) {
                if ($tieneGuiasEntregadas || $tieneServiciosEntregados) {
                    $estadoDespacho = 3; // Culminado
                } elseif (($tieneGuiasNoEntregadas && !$tieneGuiasEntregadas) ||
                    ($tieneServiciosNoEntregados && !$tieneServiciosEntregados)) {
                    $estadoDespacho = 4; // Rechazado
                }
            }

            // Actualizar estado del despacho
            DB::table('despachos')
                ->where('id_despacho', $id_despacho)
                ->update(['despacho_estado_aprobacion' => $estadoDespacho]);

            DB::commit();
            session()->flash('successComprobante', 'Los estados fueron actualizados correctamente.');
            $this->listar_informacion_despacho($id_despacho);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('errorComprobante', 'Ocurrió un error al cambiar el estado del registro. Por favor, inténtelo nuevamente.');
        }
    }

//    public function cambiarEstadoComprobante(){
//        try {
//            // $estado sebe contener el valor del select
//            if (!Gate::allows('cambiar_estado_comprobante')) {
//                session()->flash('errorComprobante', 'No tiene permisos para poder cambiar el estado del comprobante.');
//                return;
//            }
//
//            DB::beginTransaction();
//            foreach ($this->estadoComprobante as $id_comprobante => $estado){
//                $informacionDespachoVenta = DB::table('despacho_ventas as dv')
//                    ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
//                    ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
//                    ->where('d.id_tipo_servicios','=',1)
//                    ->where('dv.id_despacho_venta','=',$id_comprobante)
//                    ->first();
//
//                // Validar cada estado
//                if (!in_array((int)$estado, [2, 3])) {
//                    DB::rollBack();
//                    session()->flash('errorComprobante', 'Estado inválido seleccionado.');
//                    return;
//                }
//                $comprobante = DespachoVenta::find($id_comprobante);
//                if (!$comprobante) {
//                    DB::rollBack();
//                    session()->flash('errorComprobante', 'Comprobante no encontrado.');
//                    return;
//                }
//                // Actualizar el estado del comprobante
//                $es = (int)$estado;
//                $comprobante->despacho_detalle_estado_entrega = $es;
//                if ($comprobante->save()){
//
//                    if ($es == 3 && $informacionDespachoVenta){
//                        $comprobanteProvincialProgramacion = DB::table('despacho_ventas as dv')
//                            ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
//                            ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
//                            ->where('p.id_programacion','=',$informacionDespachoVenta->id_programacion)
//                            ->where('d.id_tipo_servicios','=',2)
//                            ->where('dv.despacho_venta_guia','=',$informacionDespachoVenta->despacho_venta_guia)
//                            ->where('dv.despacho_venta_cfnumser','=',$informacionDespachoVenta->despacho_venta_cfnumser)
//                            ->where('dv.despacho_venta_cfnumdoc','=',$informacionDespachoVenta->despacho_venta_cfnumdoc)
//                            ->first();
//
//                        if ($comprobanteProvincialProgramacion){
//                            $comprobanteProvi = DespachoVenta::find($comprobanteProvincialProgramacion->id_despacho_venta);
//                            if (!$comprobanteProvi) {
//                                DB::rollBack();
//                                session()->flash('errorComprobante', 'Comprobante no encontrado.');
//                                return;
//                            }
//                            $ress = DB::table('despacho_ventas')->where('id_despacho_venta','=',$comprobanteProvincialProgramacion->id_despacho_venta)
//                                ->update(['despacho_detalle_estado_entrega'=>3]);
//                            if ($ress == 1){
//                                // si el provincial no hay otros comprobantes poner como culminado.
//                                $conteDe = DB::table('despacho_ventas')->where('id_despacho','=',$comprobanteProvincialProgramacion->id_despacho)->count();
//                                $conteDeEstadoEntrega = DB::table('despacho_ventas')->where('id_despacho','=',$comprobanteProvincialProgramacion->id_despacho)
//                                    ->where('despacho_detalle_estado_entrega','=',3)->count();
//                                // si todos los despachos detalles ($conteDeEstadoEntrega) esta como no entregados cambiar el despacho como culminado
//                                if ($conteDe == $conteDeEstadoEntrega){
//                                    DB::table('despachos')->where('id_despacho','=',$comprobanteProvincialProgramacion->id_despacho)->update(['despacho_estado_aprobacion'=>3]);
//                                }
//                            }else{
//                                DB::rollBack();
//                                session()->flash('errorComprobante', 'Ocurrió un error al cambiar el estado.');
//                                return;
//                            }
//                        }
//                    }
//                }else{
//                    DB::rollBack();
//                    session()->flash('errorComprobante', 'Ocurrió un error al cambiar el estado.');
//                    return;
//                }
//            }
//
//            $id_despacho = $this->listar_detalle_despacho->id_despacho;
//            Despacho::where('id_despacho', $id_despacho)->update(['despacho_estado_aprobacion' => 3]);
//
//            DB::commit();
//            session()->flash('success', 'Los estados fueron actualizados correctamente.');
//            $this->listar_informacion_despacho($id_despacho);
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            $this->setErrorBag($e->validator->errors());
//        } catch (\Exception $e) {
//            DB::rollBack();
//            $this->logs->insertarLog($e);
//            session()->flash('errorComprobante', 'Ocurrió un error al cambiar el estado del registro. Por favor, inténtelo nuevamente.');
//        }
//    }

    public function listar_detalle_guia($id_despacho) {
        // Obtener los id_guia desde despacho_ventas usando el id_despacho
        $id_guias = DB::table('despacho_ventas')
            ->where('id_despacho', $id_despacho)
            ->pluck('id_guia')
            ->toArray();

        // Obtener los detalles de las guías desde la tabla guias_detalles
        $this->guia_detalle = DB::table('guias_detalles')
            ->whereIn('id_guia', $id_guias)
            ->get();
    }

    public function cambiarEstadoServicioTr($id){
        if ($id) {
            $this->id_serv_transpt = $id;
        }
    }

}
