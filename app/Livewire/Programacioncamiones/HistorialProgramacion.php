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
    public function mount()
    {
        $this->desde = Carbon::today()->toDateString(); // Fecha actual
        $this->hasta = Carbon::today()->addDays(6)->toDateString(); // Fecha 6 días después de la actual
    }
    public $serviciosTransportes = [];

    public function render(){
        // Lógica existente para obtener $resultado
        $resultado = $this->programacion->listar_programaciones_historial_programacion($this->desde, $this->hasta, $this->serie_correlativo, $this->estadoPro);

        foreach ($resultado as $rehs) {
            $rehs->despacho = DB::table('despachos as d')
                ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                ->where('d.id_programacion', '=', $rehs->id_programacion)
                ->get();

            foreach ($rehs->despacho as $des) {
                $totalVenta = 0;
                $guiasProcesadas =[];
                $des->comprobantes = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                    ->where('dv.id_despacho', '=', $des->id_despacho)
                    ->select('dv.*', 'g.guia_importe_total')
                    ->get();

                foreach ($des->comprobantes as $com) {
                    // Verificar si el id_guia ya fue procesado
                    if (!in_array($com->id_guia, $guiasProcesadas)) {
                        $precio = floatval($com->guia_importe_total);  // Usar guia_importe_total
                        $totalVenta += round($precio, 2);
                        $guiasProcesadas[] = $com->id_guia; // Marcar el id_guia como procesado
                    }
                }
                $des->totalVentaDespacho = $totalVenta;
                // Agregar el id_guia al objeto $des (usamos el primer id_guia encontrado)
                if (count($des->comprobantes) > 0) {
                    $des->id_guia = $des->comprobantes[0]->id_guia;
                } else {
                    $des->id_guia = null; // O un valor por defecto si no hay comprobantes
                }
            }
        }

        // Nueva lógica para obtener datos de servicios_transportes
        $query = DB::table('servicios_transportes')
            ->where('serv_transpt_estado', 1)
            ->whereBetween('serv_transpt_fecha_creacion', [$this->desde, $this->hasta]);
        // Si no se ha seleccionado un tipo, por defecto muestra 1,2,3
        if (empty($this->estadoPro)) {
            $query->whereIn('serv_transpt_estado_aprobacion', [1, 2, 3]);
        } else {
            // Si se selecciona un tipo, usa whereIn si es un array, de lo contrario usa where normal
            if (is_array($this->estadoPro)) {
                $query->whereIn('serv_transpt_estado_aprobacion', $this->estadoPro);
            } else {
                $query->where('serv_transpt_estado_aprobacion', $this->estadoPro);
            }
        }
        // Obtener los resultados ordenados
        $this->serviciosTransportes = $query->orderBy('serv_transpt_fecha_creacion', 'desc')->get();

        $roleId = auth()->user()->roles->first()->id ?? null;

        return view('livewire.programacioncamiones.historial-programacion', compact('resultado', 'roleId'));
    }

    public $currentDespachoId;
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
                // Obtener comprobantes
                $comprobantes = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                    ->where('dv.id_despacho', '=', $id_despacho)
                    ->get();

                $this->listar_detalle_despacho->comprobantes = $comprobantes;

                foreach ($comprobantes as $comp) {
                    // Usar un identificador único que combine id_despacho y id_despacho_venta
                    $key = $id_despacho.'_'.$comp->id_despacho_venta;
                    $this->estadoComprobante[$key] = in_array($comp->guia_estado_aprobacion, [8, 11, 12])
                        ? $comp->guia_estado_aprobacion
                        : 8;
                }

                // Obtener servicios de transporte
                $servicios = DB::table('despacho_ventas as dv')
                    ->join('servicios_transportes as st', 'st.id_serv_transpt', '=', 'dv.id_serv_transpt')
                    ->where('dv.id_despacho', '=', $id_despacho)
                    ->get();

                $this->listar_detalle_despacho->servicios_transportes = $servicios;

                foreach ($servicios as $serv) {
                    // Usar un identificador único que combine id_despacho y id_despacho_venta
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
            if (!Gate::allows('generar_excel_historial_programacion')) {
                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
                return;
            }
            $resultado = $this->programacion->listar_programaciones_historial_programacion_excel($this->desde,$this->hasta);
            $conteoDesp = 0;
            foreach($resultado as $result){
                $totalVenta = 0;
                $result->despacho = DB::table('despachos as d')
                    ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                    ->join('tipo_servicios as ts','ts.id_tipo_servicios','=','d.id_tipo_servicios')
                    ->where('d.id_programacion','=',$result->id_programacion)
                    ->where('d.despacho_estado_aprobacion','<>',4)
                    ->get();

                if (count($result->despacho) > 0){
                    $conteoDesp++;
                    foreach ($result->despacho as $indexComprobantes => $des){
                        if ($indexComprobantes == 0){
                            if ($des->id_tipo_servicios == 1){ // Si es mixto
                                // Comprobantes locales con detalles de guía
                                $datLocales = DB::table('despacho_ventas as dv')
                                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                                    ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                                    ->where('d.id_despacho', '=', $des->id_despacho)
                                    ->where('d.id_programacion', '=', $result->id_programacion)
                                    ->whereNotExists(function ($query) use ($result) {
                                        $query->select(DB::raw(1))
                                            ->from('despacho_ventas as dv_provincial')
                                            ->join('despachos as d_provincial', 'd_provincial.id_despacho', '=', 'dv_provincial.id_despacho')
                                            ->join('guias as g_provincial', 'g_provincial.id_guia', '=', 'dv_provincial.id_guia')
                                            ->where('d_provincial.id_programacion', '=', $result->id_programacion)
                                            ->where('d_provincial.id_tipo_servicios', '=', 2)
                                            ->whereRaw('g_provincial.id_guia = g.id_guia');
                                    })
                                    ->select('g.*', 'dv.id_despacho')
                                    ->get();

                                // Obtener detalles para cada guía local
                                foreach ($datLocales as $guia) {
                                    $guia->detalles = DB::table('guias_detalles')
                                        ->where('id_guia', $guia->id_guia)
                                        ->get();
                                }

                                /* BUSCAR DESPACHOS PROVINCIALES DE LA MISMA PROGRAMACION */
                                $desProvinciPro = DB::table('despachos')
                                    ->where('id_programacion', '=', $result->id_programacion)
                                    ->where('id_tipo_servicios', '=', 2)
                                    ->pluck('id_despacho');

                                // Obtener comprobantes provinciales con detalles de guía
                                $datProvinciales = collect();
                                if ($desProvinciPro->isNotEmpty()) {
                                    $datProvinciales = DB::table('despacho_ventas')
                                        ->join('guias', 'guias.id_guia', '=', 'despacho_ventas.id_guia')
                                        ->whereIn('despacho_ventas.id_despacho', $desProvinciPro)
                                        ->select('guias.*', 'despacho_ventas.id_despacho')
                                        ->get();

                                    // Obtener detalles para cada guía provincial
                                    foreach ($datProvinciales as $guia) {
                                        $guia->detalles = DB::table('guias_detalles')
                                            ->where('id_guia', $guia->id_guia)
                                            ->get();
                                    }
                                }

                                $datCombinado = $datLocales->merge($datProvinciales);
                                $des->comprobantes = $datCombinado;

                            } else {
                                // Para no mixtos, obtener guías con sus detalles
                                $des->comprobantes = DB::table('despacho_ventas as dv')
                                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                                    ->where('dv.id_despacho','=',$des->id_despacho)
                                    ->select('g.*', 'dv.id_despacho')
                                    ->get();

                                // Obtener detalles para cada guía
                                foreach ($des->comprobantes as $guia) {
                                    $guia->detalles = DB::table('guias_detalles')
                                        ->where('id_guia', $guia->id_guia)
                                        ->get();
                                }
                            }

                            // Calcular total de venta usando guia_importe_total
                            foreach ($des->comprobantes as $com){
                                $precio = floatval($com->guia_importe_total);
                                $totalVenta += round($precio, 2);

                                // También puedes calcular totales basados en los detalles si es necesario
                                $totalDetalles = 0;
                                $pesoTotalGramos = 0;
                                foreach ($com->detalles as $detalle) {
                                    $totalDetalles += floatval($detalle->guia_det_importe_total_inc_igv);
                                    $pesoTotalGramos += $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                                }
                                $com->total_detalles = $totalDetalles;
                                $com->peso_total_kilos = $pesoTotalGramos / 1000;
                            }
                            $des->totalVentaDespacho = $totalVenta;
                        }
                    }
                }
            }
            if ($conteoDesp > 0){
                $spreadsheet = new Spreadsheet();
                $sheet1  = $spreadsheet->getActiveSheet();
                $sheet1->setTitle('historial_programacion');


                $mensaje = "RESULTADO DE BÚSQUEDA: ";
                $textMeF = "";
//            if ($this->serie_correlativo) {
//                $mensaje .= "Criterio: \"" . $this->serie_correlativo . "\"";
//            }
                if (isset($this->desde, $this->hasta)) {
                    $mensaje .= " | Rango de fechas: " . date("d-m-Y", strtotime($this->desde)) . " al " . date("d-m-Y", strtotime($this->hasta));
                    $textMeF = date("d-m-Y", strtotime($this->desde)) . " al " . date("d-m-Y", strtotime($this->hasta));
                }
//            if (isset($this->estadoPro)) {
//                $result = match ($this->estadoPro) {
//                    'case1' => 'Aprobado',
//                    'case2' => 'En Camino',
//                    'case3', 'case4' => 'Resultado para case3 o case4', // Soporta múltiples valores
//                    default => 'Resultado por defecto', // Similar a 'default' en switch
//                };
//                $mensaje .= " | Rango de fechas: " . date("d-m-Y", strtotime($this->desde)) . " al " . date("d-m-Y", strtotime($this->hasta));
//            }
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

                // Insertar la tabla después del incremento de $row
                // Encabezados de la tabla
                $sheet1->setCellValue('A'.$row, 'Zona de Despacho');
                $sheet1->setCellValue('B'.$row, 'Valor Transportado (Soles sin IGV)');
                $sheet1->setCellValue('C'.$row, 'Flete Aprobados (Soles)');
                $sheet1->setCellValue('D'.$row, 'Flete Penal. De Aprobación');
                $sheet1->setCellValue('E'.$row, 'Total Flete (Soles)');
                // Estilo para los encabezados
                $headerStyle = $sheet1->getStyle('A'.$row.':E'.$row);
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('D9D9D9');
                $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $row++;
                // Datos de la tabla
                $tableData = [
                    ['Total', 816948.47, 21758.40, '', 21758.40],
                    ['Local', 583328.10, 7825.13, '', 7825.13],
                    ['Provincia 1', 105129.17, 5525.01, '', 5525.01],
                    ['Provincia 2', 128491.20, 8408.26, '', 8408.26],
                    ['Total Provincia', 233620.37, 13933.27, '', 13933.27]
                ];

                foreach ($tableData as $data) {
                    $sheet1->setCellValue('A'.$row, $data[0]);
                    $sheet1->setCellValue('B'.$row, $data[1]);
                    $sheet1->setCellValue('C'.$row, $data[2]);
                    $sheet1->setCellValue('D'.$row, $data[3]);
                    $sheet1->setCellValue('E'.$row, $data[4]);

                    // Formato numérico para las columnas de valores
                    $sheet1->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet1->getStyle('C'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet1->getStyle('E'.$row)->getNumberFormat()->setFormatCode('#,##0.00');

                    // Bordes para las celdas
                    $cellStyle = $sheet1->getStyle('A'.$row.':E'.$row);
                    $cellStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    $row++;
                }

// Espacio después de la tabla
                $sheet1->setCellValue('A'.$row, "");
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
                $sheet1->getColumnDimension('B')->setWidth(12);
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
//            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
//            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $row++;
                foreach($resultado as $resPro){
//                $sheet1->setCellValue('A'.$row, $rehs->programacion_numero_correlativo);
//
//                $cellRange = 'A'.$row.':N'.$row;
//                $sheet1->mergeCells($cellRange);
//
//                $rowStyle = $sheet1->getStyle($cellRange);
//                $rowStyle->getFont()->setSize(10);
//                $rowStyle->getFont()->setBold(true);
//                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
//                $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
//                $row++;

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
                            foreach ($des->comprobantes  as $indexComprobante => $comproba){
                                /**/
                                // buscar si es mixto o un servicio normal
                                $validarMixto = DB::table('despacho_ventas as dv')
                                    ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia') // Unión con tabla guías
                                    ->join('guias_detalles as gd', 'gd.id_guia', '=', 'g.id_guia') // Unión con detalles de guía
                                    ->where('dv.id_guia', '=', $comproba->id_guia)
                                    ->where('dv.id_despacho', '<>', $des->id_despacho)
                                    ->where('d.id_programacion', '=', $resPro->id_programacion)
                                    ->where('d.id_tipo_servicios', '=', 2)
                                    ->select(
                                        'dv.*',
                                        'd.*',
                                        'g.*', // Seleccionar campos de guía
                                        'gd.*' // Seleccionar campos de detalles de guía
                                    )
                                    ->first();

                                if ($validarMixto) {
                                    $typeComprop = 3; // Es mixto
                                } else {
                                    $typeComprop = $des->id_tipo_servicios;
                                }

                                $loc = match ($typeComprop) {
                                    1 => 'LOCAL',
                                    2 => 'PROVINCIAL',
                                    3 => 'MIXTO',
                                    default => '',
                                };

                                $estado = '';
                                if ($comproba->guia_estado_aprobacion == 8) {
                                    $estado = 'SI';
                                } elseif ($comproba->guia_estado_aprobacion == 11) {
                                    $estado = 'NO';
                                }

                                $sheet1->setCellValue('A'.$row, $comproba->guia_nro_doc);
                                $sheet1->setCellValue('B'.$row, date('d/m/Y', strtotime($comproba->guia_fecha_emision)));
                                $sheet1->setCellValue('C'.$row, $comproba->guia_nombre_cliente);
                                $sheet1->setCellValue('D'.$row, $comproba->guia_nro_doc_ref);
                                $sheet1->setCellValue('E'.$row, $comproba->guia_importe_total);
                                $sheet1->setCellValue('F'.$row, $resPro->programacion_numero_correlativo);
                                $sheet1->setCellValue('G'.$row, date('d/m/Y', strtotime($resPro->programacion_fecha)));
                                $sheet1->setCellValue('H'.$row, $estado);
                                $sheet1->setCellValue('I'.$row, $loc);
                                $sheet1->setCellValue('J'.$row, round($comproba->peso_total_kilos, 2));
//                                $sheet1->setCellValue('K'.$row, "");
//                                $sheet1->setCellValue('L'.$row, "");
//                                $sheet1->setCellValue('M'.$row, "");
//                                $sheet1->setCellValue('N'.$row, "");
//                                $cellRange = 'M'.$row.':M'.$row;
                                $rowStyle = $sheet1->getStyle($cellRange);
                                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                                if ($indexComprobante == 0 || $indexComprobante == 1){
                                    /* LOCAL */
                                    if ($des->id_tipo_servicios == 1){
                                        if ($indexComprobante == 0) {
                                            $informacionliquidacion = DB::table('despachos')
                                                ->where('id_despacho', '=', $des->id_despacho)
                                                ->orderBy('id_despacho', 'desc')->first();

                                            $sheet1->setCellValue('K'.$row, $des->despacho_numero_correlativo);

                                            $fac_proveedor = DB::table('liquidaciones')
                                                ->where('id_transportistas', '=', $des->id_transportistas)
                                                ->first();
                                            $fac_pro = "";
                                            if ($fac_proveedor){
                                                $fac_pro = $fac_proveedor->liquidacion_serie. ' - ' . $fac_proveedor->liquidacion_correlativo;
                                            }

                                            $rowO_W = $row; // Mantiene una fila separada para O:W

                                            if ($informacionliquidacion) {
                                                $costoTarifa = ($informacionliquidacion->despacho_estado_modificado == 1)
                                                    ? $informacionliquidacion->despacho_monto_modificado
                                                    : $informacionliquidacion->despacho_flete;

                                                $costoMano = $informacionliquidacion->despacho_ayudante ?? 0;
                                                $costoOtros = $informacionliquidacion->despacho_gasto_otros ?? 0;
                                                $totalGeneralLocal = ($costoTarifa + $costoMano + $costoOtros);

                                                // Celdas afectadas entre O y W
                                                $sheet1->setCellValue('L'.$rowO_W, $this->general->formatoDecimal($costoTarifa));
                                                $sheet1->setCellValue('M'.$rowO_W,$this->general->formatoDecimal($costoOtros));
                                                $sheet1->setCellValue('N'.$rowO_W, $this->general->formatoDecimal($costoMano));
                                                $sheet1->setCellValue('O'.$rowO_W, $des->transportista_nom_comercial);
                                                $sheet1->setCellValue('P'.$rowO_W, $fac_pro);
                                                $sheet1->setCellValue('Q'.$rowO_W, $this->general->formatoDecimal($totalGeneralLocal));
                                                $sheet1->setCellValue('R'.$rowO_W, "");
                                                $sheet1->setCellValue('S'.$rowO_W, "");

                                                $fleteFinalLocal = $totalGeneralLocal;
                                                $filaPorcentajeLocal = $rowO_W;

                                                $rowO_W++; // Incrementamos solo para O-W

                                                $vehiculo = DB::table('vehiculos  as v')
                                                    ->join('tipo_vehiculos as tv','tv.id_tipo_vehiculo','=','v.id_tipo_vehiculo')
                                                    ->where('v.id_vehiculo','=',$des->id_vehiculo)->first();
                                                $vehiT = "";
                                                if ($vehiculo){
                                                    $vehiT = $vehiculo->tipo_vehiculo_concepto.': '.$vehiculo->vehiculo_capacidad_peso.'kg - '.$vehiculo->vehiculo_placa;
                                                }

                                                // Segunda fila solo en O-W
                                                $sheet1->setCellValue('K'.$rowO_W, "");
                                                $sheet1->setCellValue('L'.$rowO_W, "");
//                                                $sheet1->mergeCells('P'.$rowO_W.':Q'.$rowO_W);
                                                $sheet1->setCellValue('M'.$rowO_W, "");
                                                $sheet1->setCellValue('N'.$rowO_W, "");
                                                $sheet1->setCellValue('O'.$rowO_W, $vehiT);
                                                $sheet1->setCellValue('P'.$rowO_W, "");
                                                $sheet1->setCellValue('Q'.$rowO_W, "");
                                                $sheet1->setCellValue('R'.$rowO_W, "");

                                                // El resto del código sigue con $row sin afectar O:W
                                            }

                                        } else{
                                            /* ---------------------------------------------*/
//                                            $vehiculo = DB::table('vehiculos  as v')
//                                                ->join('tipo_vehiculos as tv','tv.id_tipo_vehiculo','=','v.id_tipo_vehiculo')
//                                                ->where('v.id_vehiculo','=',$des->id_vehiculo)->first();
//                                            $vehiT = "";
//                                            if ($vehiculo){
//                                                $vehiT = $vehiculo->tipo_vehiculo_concepto.': '.$vehiculo->vehiculo_capacidad_peso.'kg - '.$vehiculo->vehiculo_placa;
//                                            }

                                            /* ---------------------------------------------*/
//                                            $sheet1->setCellValue('O'.$row, '');
//                                            $sheet1->setCellValue('P'.$row, "");
//                                            $sheet1->mergeCells('P'.$row.':Q'.$row);
//                                            $sheet1->setCellValue('R'.$row, "");
//                                            $sheet1->setCellValue('S'.$row, "");
//                                            $sheet1->setCellValue('T'.$row, "");
//                                            $sheet1->setCellValue('U'.$row, "");
//                                            $sheet1->setCellValue('V'.$row, "");
//                                            $sheet1->setCellValue('W'.$row, "");
                                        }
                                    }else{
                                        $sheet1->setCellValue('O'.$row, '');
                                        $sheet1->setCellValue('P'.$row, '');
                                        $sheet1->setCellValue('Q'.$row, '');
                                        $sheet1->setCellValue('R'.$row, '');
                                        $sheet1->setCellValue('S'.$row, '');
                                        $sheet1->setCellValue('T'.$row, '');
                                        $sheet1->setCellValue('U'.$row, '');
                                        $sheet1->setCellValue('V'.$row, '');
                                        $sheet1->setCellValue('W'.$row, '');
                                    }



                                    /* PROVINCIAL */
                                    if ($des->id_tipo_servicios == 2){
                                        if ($indexComprobante == 0){
                                            $informacionliquidacion = DB::table('despachos')
                                                ->where('id_despacho', '=', $des->id_despacho)
                                                ->orderBy('id_despacho', 'desc')->first();
                                            $sheet1->setCellValue('S'.$row, $des->despacho_numero_correlativo);
                                            if ($informacionliquidacion){
                                                $costoTarifa = ($informacionliquidacion->despacho_estado_modificado == 1)
                                                    ? $informacionliquidacion->despacho_monto_modificado
                                                    : $informacionliquidacion->despacho_flete;

                                                $costoOtros = $informacionliquidacion->despacho_gasto_otros ?? 0;

                                                $totalGeneralLocalProvin = (($costoTarifa * $informacionliquidacion->despacho_peso) + $costoOtros);


                                                $destino = "";
                                                if ($informacionliquidacion->id_departamento){
                                                    $dep = DB::table('departamentos')->where('id_departamento','=',$informacionliquidacion->id_departamento)->first();
                                                    $destino.= $dep->departamento_nombre;
                                                }
                                                if ($informacionliquidacion->id_provincia){
                                                    $provi = DB::table('provincias')->where('id_provincia','=',$informacionliquidacion->id_provincia)->first();
                                                    $destino.= "-".$provi->provincia_nombre;
                                                }
//                                                if ($informacionliquidacion->id_distrito){
//                                                    $disti = DB::table('distritos')->where('id_distrito','=',$informacionliquidacion->id_distrito)->first();
//                                                    $destino.= "-".$disti->distrito_nombre;
//                                                }

//                                                $sheet1->setCellValue('X'.$row, '');
                                                $sheet1->setCellValue('T'.$row, $des->transportista_nom_comercial);
                                                $sheet1->setCellValue('U'.$row, $destino);
                                                $sheet1->setCellValue('V'.$row, "PROVINCIA");
                                                $sheet1->setCellValue('W'.$row, $informacionliquidacion->despacho_numero_correlativo);
                                                $sheet1->setCellValue('X'.$row, $this->general->formatoDecimal($totalGeneralLocalProvin));
                                                $sheet1->setCellValue('Y'.$row, '');

                                                $fleteFinalProvin = $totalGeneralLocalProvin;
                                                $filaPorcentajeProvin = $row;
                                            }
                                        }
                                    }else{
//                                        $sheet1->setCellValue('W'.$row, '');
//                                        $sheet1->setCellValue('X'.$row, '');
//                                        $sheet1->setCellValue('Y'.$row, '');
//                                        $sheet1->setCellValue('Z'.$row, '');
//                                        $sheet1->setCellValue('AA'.$row, '');
//                                        $sheet1->setCellValue('AB'.$row, '');
                                    }
                                }
                                if ($typeComprop == 3){

                                    /*--------------------------------------------------------------------------------------- */
                                    /* SI EN CASO LA PROGRAMACIÓN ES MIXTA LLENAR LAS OS DE PROVINCIAL */
                                    $osMixtoProgramacion = DB::table('despachos as d')
                                        ->join('despacho_ventas as dv','dv.id_despacho','=','d.id_despacho')
                                        ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                                        ->where('d.id_programacion','=',$resPro->id_programacion)
                                        ->where('dv.despacho_venta_cftd','=',$comproba->despacho_venta_cftd)
                                        ->where('dv.despacho_venta_cfnumser','=',$comproba->despacho_venta_cfnumser)
                                        ->where('dv.despacho_venta_cfnumdoc','=',$comproba->despacho_venta_cfnumdoc)
                                        ->where('d.id_tipo_servicios','=',2)->first();


                                    if(!$osAnteriorMixto){ // SI ESTO ES NULO ES POR QUE AUN NO ESTA CON VALOR DE OS
                                        $osAnteriorMixto = $osMixtoProgramacion->despacho_numero_correlativo;
                                        $ingreExcelMixto = true;
                                    }else{
                                        if ($osMixtoProgramacion->despacho_numero_correlativo == $osAnteriorMixto){ // SI LA OS QUE INGRESA YA ESTA COMO VALOR EN $osAnteriorMixto ES POR QUE YA SE PUSO COMO VALOR AL PRIMERO
                                            $ingreExcelMixto = false;
                                        }else{
                                            $osAnteriorMixto = $osMixtoProgramacion->despacho_numero_correlativo;
                                            $ingreExcelMixto = true;
                                        }

                                    }



                                    if ($osMixtoProgramacion && $ingreExcelMixto){
//                                        $rowMixto = $row;
                                        $totalImporteComprobanteDespachoPro = DB::table('despacho_ventas')
                                            ->where('id_despacho','=',$osMixtoProgramacion->id_despacho)
//                                            ->where('despacho_detalle_estado_entrega','=',2)
                                            ->sum('despacho_venta_cfimporte');

                                        $InformacionDespachoMixto = DB::table('despachos')
                                            ->where('id_despacho','=',$osMixtoProgramacion->id_despacho)
                                            ->orderBy('id_despacho', 'desc')->first();

                                        if ($InformacionDespachoMixto){
                                            $costoTarifa = ($InformacionDespachoMixto->despacho_estado_modificado == 1)
                                                ? $InformacionDespachoMixto->despacho_monto_modificado
                                                : $InformacionDespachoMixto->despacho_flete;

                                            $costoOtros = $InformacionDespachoMixto->despacho_gasto_otros ?? 0;

                                            $totalGeneralMixto = (($costoTarifa * $InformacionDespachoMixto->despacho_peso) + $costoOtros);

                                            $destino = "";
                                            if ($InformacionDespachoMixto->id_departamento){
                                                $dep = DB::table('departamentos')->where('id_departamento','=',$InformacionDespachoMixto->id_departamento)->first();
                                                $destino.= $dep->departamento_nombre;
                                            }
                                            if ($InformacionDespachoMixto->id_provincia){
                                                $provi = DB::table('provincias')->where('id_provincia','=',$InformacionDespachoMixto->id_provincia)->first();
                                                $destino.= "-".$provi->provincia_nombre;
                                            }
                                            if ($InformacionDespachoMixto->id_distrito){
                                                $disti = DB::table('distritos')->where('id_distrito','=',$InformacionDespachoMixto->id_distrito)->first();
                                                $destino.= "-".$disti->distrito_nombre;
                                            }

                                            $sheet1->setCellValue('S'.$row, $osMixtoProgramacion->despacho_numero_correlativo);
                                            $sheet1->setCellValue('T'.$row, $osMixtoProgramacion->transportista_nom_comercial);
                                            $sheet1->setCellValue('U'.$row, $destino);
                                            $sheet1->setCellValue('V'.$row, "PROVINCIA");
                                            $sheet1->setCellValue('W'.$row, $InformacionDespachoMixto->despacho_numero_correlativo);
                                            $sheet1->setCellValue('X'.$row, $this->general->formatoDecimal($totalGeneralMixto));
                                            $poMixto = $totalImporteComprobanteDespachoPro != 0 ?  ($totalGeneralMixto / $totalImporteComprobanteDespachoPro) * 100 : 0;
                                            $sheet1->setCellValue('Y'.$row, $this->general->formatoDecimal($poMixto));
//                                            if ($totalImporteComprobanteDespachoPro->liquidacion_detalle_comentarios){
//                                                $sheet1->setCellValue('AC'.$row, $totalImporteComprobanteDespachoPro->liquidacion_detalle_comentarios);
//                                                $sheet1->getColumnDimension('AC')->setWidth(15);
//                                                $cellRange = 'AC'.$row.':AC'.$row;
//                                                $sheet1->mergeCells($cellRange);
//                                                $rowStyle = $sheet1->getStyle($cellRange);
//                                                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FABF8F'); // Fondo
//                                                $rowStyle->getFont()->setBold(true); // Hacer negritas
//
//                                            }
                                        }
                                    }
                                    /*--------------------------------------------------------------------------------------- */
                                }

                                $cellRange = 'A'.$row.':Y'.$row;
                                $rowStyle = $sheet1->getStyle($cellRange);
                                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                                $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
                                $cellRange = 'E'.$row.':E'.$row;
                                $rowStyle = $sheet1->getStyle($cellRange);
//                        $rowStyle->getFont()->setSize(10);
                                $rowStyle->getFont()->setBold(true);

                                $cellRange = 'O'.$row.':AA'.$row;
                                $rowStyle = $sheet1->getStyle($cellRange);
//                        $rowStyle->getFont()->setBold(true);
                                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
//                                $sheet1->mergeCells('P'.$row.':Q'.$row);

                                $row++;
//                                if ($comproba->despacho_detalle_estado_entrega == 2){
                                $importeTotalDespachos+=$comproba->guia_importe_total;
                                $totalPesoDespachos+=$comproba->peso_total_kilos;
//                                }

                            }
                            $comentariosLiquidacion = DB::table('liquidacion_detalles')->where('id_despacho','=',$des->id_despacho)->orderBy('id_liquidacion_detalle','desc')->orderBy('id_despacho','desc')->first();
                            $comenLi = "";
                            if ($comentariosLiquidacion){
                                $comenLi = $comentariosLiquidacion->liquidacion_detalle_comentarios;
                            }
                            $sheet1->setCellValue('A'.$row, "");
                            $sheet1->setCellValue('B'.$row, "");
                            $sheet1->setCellValue('C'.$row, "");
                            $sheet1->setCellValue('D'.$row, "");
                            $sheet1->setCellValue('E'.$row, $this->general->formatoDecimal($importeTotalDespachos));
                            $sheet1->setCellValue('F'.$row, "");
                            if ($comenLi){
                                $sheet1->setCellValue('G'.$row, $comenLi);
                                $cellRange = 'G'.$row.':J'.$row;
                                $sheet1->mergeCells($cellRange);
                                $rowStyle = $sheet1->getStyle($cellRange);
                                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FABF8F'); // Fondo
                                $rowStyle->getFont()->setBold(true); // Hacer negritas
                            }else{
                                $sheet1->setCellValue('G'.$row, "");
                                $sheet1->setCellValue('H'.$row, "");
                                $sheet1->setCellValue('I'.$row, "");
                                $sheet1->setCellValue('J'.$row, $this->general->formatoDecimal($totalPesoDespachos));
                            }
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

                            $cellRange = 'A'.$row.':N'.$row;
                            $sheet1->mergeCells($cellRange);
                            $row++;
                        }



                    }
                }
            }else{
                session()->flash('error', 'Ocurrió un error: no existen despachos finalizados.');
                return;
            }


            /*---------------------------*/
//            $totalGeneralCommprobantes = 0;
//
//            $primeraFilaIngresadaTra = null;
//            $ultimoTransportista = null;
//            $sumaPorTransportista = 0;

//            foreach ($resultado as $index => $re) {
//                Contenido sin formato decimal requerido
//                $totalSinIGVExcel = $re->total_sin_igv;
//                $totalConIVCExcel = $totalSinIGVExcel * 1.18;
//
//                // Formatear valores para visualización
//                $totalSinIGVExcelFormatted = $this->general->formatoDecimal($totalSinIGVExcel);
//                $totalConIVCExcelFormatted = $this->general->formatoDecimal($totalConIVCExcel);

            // Manejo de filas combinadas por transportista
//                if ($ultimoTransportista !== null && $ultimoTransportista !== $re->id_transportistas) {
//                    // Si cambió el transportista, escribir el total acumulado para el anterior
//                    $Filahasta = $row - 1; // Última fila del transportista anterior
//                    $sheet1->mergeCells('F'.$primeraFilaIngresadaTra.':F'.$Filahasta);
//                    $sheet1->setCellValue('F'.$primeraFilaIngresadaTra, $this->general->formatoDecimal($sumaPorTransportista)); // Total acumulado
//                    $sumaPorTransportista = 0; // Reiniciar la suma para el nuevo transportista
//                    $primeraFilaIngresadaTra = $row; // Nueva fila inicial
//                }

            // Acumular el total para el transportista actual
//                $sumaPorTransportista += $totalConIVCExcel;

            // Escribir los datos en la fila actual
//                $sheet1->setCellValue('A'.$row, "");
//
//                $sheet1->setCellValue('B'.$row, "");
//
//                $sheet1->setCellValue('C'.$row, "");
//
//                $sheet1->setCellValue('D'.$row, "");
//
//                $sheet1->setCellValue('E'.$row, "");
//
//
//                // Estilo general
//                $cellRange = 'A'.$row.':F'.$row;
//                $rowStyle = $sheet1->getStyle($cellRange);
//                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
//                // Alineación del encabezado
//                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
//                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // Actualizar el transportista actual
//                if ($ultimoTransportista === null || $ultimoTransportista !== $re->id_transportistas) {
//                    $ultimoTransportista = $re->id_transportistas;
//                    $primeraFilaIngresadaTra = $row; // Primera fila del nuevo transportista
//                }
//                // Si es el último registro, cerrar el rango
//                if ($index == count($resultado) - 1) {
//                    $Filahasta = $row; // Última fila
//                    $sheet1->mergeCells('F'.$primeraFilaIngresadaTra.':F'.$Filahasta);
//                    $sheet1->setCellValue('F'.$primeraFilaIngresadaTra, $this->general->formatoDecimal($sumaPorTransportista)); // Total acumulado
//                }

//                $sheet1->getColumnDimension('A')->setWidth(12); // Ancho de la columna A
//                $sheet1->getColumnDimension('B')->setWidth(12); // Ancho de la columna B
//                $sheet1->getColumnDimension('C')->setWidth(12); // Ancho de la columna C
//                $sheet1->getColumnDimension('D')->setWidth(12); // Ancho de la columna D
//                $sheet1->getColumnDimension('E')->setWidth(12); // Ancho de la columna E
//                $sheet1->getColumnDimension('F')->setWidth(12); // Ancho de la columna F

//                $row++;
//                $totalGeneralCommprobantes += 0;
//            }

            // Contenido Final
//            $sheet1->setCellValue('A'.$row, 'TOTAL');
//            $sheet1->mergeCells('A'.$row.':C'.$row);
//            $sheet1->setCellValue('D'.$row, "");
//            $sheet1->setCellValue('E'.$row, "");
//            $sheet1->setCellValue('F'.$row, "");
//
//
//            $cellRange = 'A'.$row.':F'.$row;
//            $rowStyle = $sheet1->getStyle($cellRange);
//            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
//            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ececec'); // Fondo
//            $rowStyle->getFont()->setSize(10);
//            $rowStyle->getFont()->setBold(true);
//            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
//            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $desde = $this->desde;
            $hasta = $this->hasta;
            // Formatear el nombre del archivo Excel
            $nombre_excel = sprintf(
                "historial_de_programacion_%s_hasta_el_%s_.xlsx",
                date('d-m-Y', strtotime($desde)),
                $hasta
            );

//            $nombre_excel = "historial_de_programacion_" . date('d-m-Y',strtotime($desde)).' hasta el ' .$hasta.'_'.'.xlsx';
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
    public function cambiarEstadoDespachoFormulario(){
        try {

            if (!Gate::allows('cambiar_estado_despacho')) {
                session()->flash('error_delete', 'No tiene permisos para poder cambiar el estado del despacho.');
                return;
            }
            if (count($this->selectedItems) > 0) {
                // Validar que al menos un checkbox esté seleccionado
                $this->validate([
                    'selectedItems' => 'required|array|min:1',
                ], [
                    'selectedItems.required' => 'Debe seleccionar al menos una opción.',
                    'selectedItems.array'    => 'La selección debe ser válida.',
                    'selectedItems.min'      => 'Debe seleccionar al menos una opción.',
                ]);
                DB::beginTransaction();
                foreach ($this->selectedItems as $select) {
                    $updateDespacho = Despacho::find($select);
                    $updateDespacho->despacho_estado_aprobacion = 2;
                    if ($updateDespacho->save()) {
                        // Obtener los id_guia relacionados con el id_despacho
                        $despachoVentas = DB::table('despacho_ventas')
                            ->where('id_despacho', $select)
                            ->get();

                        // Array para evitar duplicados en historial_guias
                        $guiasProcesadas = [];

                        foreach ($despachoVentas as $despachoVenta) {
                            // Verificar si el id_guia ya fue procesado
                            if (!in_array($despachoVenta->id_guia, $guiasProcesadas)) {
                                // Actualizar el campo guia_estado_aprobacion en la tabla guias
                                DB::table('guias')
                                    ->where('id_guia', $despachoVenta->id_guia)
                                    ->update(['guia_estado_aprobacion' => 7]);

                                // Obtener el guia_nro_doc desde la tabla guias
                                $guia = DB::table('guias')
                                    ->where('id_guia', $despachoVenta->id_guia)
                                    ->first();

                                if ($guia) {
                                    // Insertar en historial_guias
                                    DB::table('historial_guias')->insert([
                                        'id_users' => Auth::id(),
                                        'id_guia' => $despachoVenta->id_guia,
                                        'guia_nro_doc' => $guia->guia_nro_doc,
                                        'historial_guia_estado_aprobacion' => 7, // Estado de aprobación
                                        'historial_guia_fecha_hora' => Carbon::now('America/Lima'), // Fecha y hora actual de Perú
                                        'historial_guia_estado' => 1, // Estado por defecto
                                        'created_at' => Carbon::now('America/Lima'),
                                        'updated_at' => Carbon::now('America/Lima'),
                                    ]);

                                    // Marcar el id_guia como procesado
                                    $guiasProcesadas[] = $despachoVenta->id_guia;
                                }
                            }
                        }
                        // Actualizar el estado en la tabla servicios_transporte
                        $serviciosTransporte = DB::table('despacho_ventas')
                            ->where('id_despacho', $select)
                            ->get();
                        foreach ($serviciosTransporte as $servicio) {

                            DB::table('servicios_transportes')
                                ->where('id_serv_transpt', $servicio->id_serv_transpt)
                                ->update(['serv_transpt_estado_aprobacion' => 4]);
                        }

                    } else {
                        DB::rollBack();
                        session()->flash('error_delete', 'No se pudo cambiar los estados de los despachos a "En Camino".');
                        return;
                    }
                }
                DB::commit();
                $this->selectedItems = [];
                $this->dispatch('hideModalDelete');
                session()->flash('success', 'Despachos en camino.');
            } else {
                $this->validate([
                    'id_despacho' => 'required|integer',
                ], [
                    'id_despacho.required' => 'El identificador es obligatorio.',
                    'id_despacho.integer' => 'El identificador debe ser un número entero.',
                ]);

                DB::beginTransaction();
                $updateDespacho = Despacho::find($this->id_despacho);
                $updateDespacho->despacho_estado_aprobacion = 2;
                if ($updateDespacho->save()) {
                    // Obtener los id_guia relacionados con el id_despacho
                    $despachoVentas = DB::table('despacho_ventas')
                        ->where('id_despacho', $this->id_despacho)
                        ->get();

                    // Array para evitar duplicados en historial_guias
                    $guiasProcesadas = [];

                    foreach ($despachoVentas as $despachoVenta) {
                        // Verificar si el id_guia ya fue procesado
                        if (!in_array($despachoVenta->id_guia, $guiasProcesadas)) {
                            // Actualizar el campo guia_estado_aprobacion en la tabla guias
                            DB::table('guias')
                                ->where('id_guia', $despachoVenta->id_guia)
                                ->update(['guia_estado_aprobacion' => 7]);

                            // Obtener el guia_nro_doc desde la tabla guias
                            $guia = DB::table('guias')
                                ->where('id_guia', $despachoVenta->id_guia)
                                ->first();

                            if ($guia) {
                                // Insertar en historial_guias
                                DB::table('historial_guias')->insert([
                                    'id_users' => Auth::id(),
                                    'id_guia' => $despachoVenta->id_guia,
                                    'guia_nro_doc' => $guia->guia_nro_doc,
                                    'historial_guia_estado_aprobacion' => 7, // Estado de aprobación
                                    'historial_guia_fecha_hora' => Carbon::now('America/Lima'), // Fecha y hora actual de Perú
                                    'historial_guia_estado' => 1, // Estado por defecto
                                    'created_at' => Carbon::now('America/Lima'),
                                    'updated_at' => Carbon::now('America/Lima'),
                                ]);

                                // Marcar el id_guia como procesado
                                $guiasProcesadas[] = $despachoVenta->id_guia;
                            }
                        }
                    }
                    // Actualizar el estado en la tabla servicios_transporte
                    $serviciosTransporte = DB::table('despacho_ventas')
                        ->where('id_despacho', $this->id_despacho)
                        ->get();
                    foreach ($serviciosTransporte as $servicio) {

                        DB::table('servicios_transportes')
                            ->where('id_serv_transpt', $servicio->id_serv_transpt)
                            ->update(['serv_transpt_estado_aprobacion' => 4]);
                    }

                    DB::commit();
                    $this->dispatch('hideModalDelete');
                    session()->flash('success', 'Despacho en camino.');
                } else {
                    DB::rollBack();
                    session()->flash('error_delete', 'No se pudo cambiar el estado del despacho a "En Camino".');
                    return;
                }
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al cambiar el estado del registro. Por favor, inténtelo nuevamente.');
        }
    }
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

            // 1. Actualizar estados de guías (comprobantes)
            foreach ($this->estadoComprobante as $key => $estado) {
                // Extraer el id_despacho_venta del key
                $parts = explode('_', $key);
                if ($parts[0] != $id_despacho) continue; // Solo procesar los del despacho actual

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

                // Actualizar estado en guías
                DB::table('guias')
                    ->where('id_guia', $despachoVenta->id_guia)
                    ->update(['guia_estado_aprobacion' => $es]);

                // Registrar en historial_guias
                $guia = DB::table('guias')
                    ->where('id_guia', $despachoVenta->id_guia)
                    ->first();

                if ($guia) {
                    DB::table('historial_guias')->insert([
                        'id_users' => Auth::id(),
                        'id_guia' => $despachoVenta->id_guia,
                        'guia_nro_doc' => $guia->guia_nro_doc,
                        'historial_guia_estado_aprobacion' => $es,
                        'historial_guia_fecha_hora' => Carbon::now('America/Lima'),
                        'historial_guia_estado' => 1,
                        'created_at' => Carbon::now('America/Lima'),
                        'updated_at' => Carbon::now('America/Lima'),
                    ]);
                }
            }

            // 2. Actualizar estados de servicios de transporte
            foreach ($this->estadoServicio as $key => $estado) {
                // Extraer el id_despacho_venta del key
                $parts = explode('_', $key);
                if ($parts[0] != $id_despacho) continue; // Solo procesar los del despacho actual

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
            }

            // Actualizar estado del despacho
            DB::table('despachos')
                ->where('id_despacho', $id_despacho)
                ->update(['despacho_estado_aprobacion' => 3]);

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

    public function generar_excel_servicio_transporte()
    {
        try {
            if (!Gate::allows('generar_excel_servicio_transporte')) {
                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
                return;
            }

            // Crear un nuevo archivo de Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Servicios Transporte');

            // FILA 1
            $row = 1;
            $sheet->setCellValue('A' . $row, 'HISTORIAL DE SERVICIO DE TRANSPORTE');
            $titleStyle = $sheet->getStyle('A' . $row);
            $titleStyle->getFont()->setSize(14); // Tamaño de fuente más grande
            $titleStyle->getFont()->setBold(true); // Texto en negrita
            $sheet->mergeCells('A' . $row . ':N' . $row); // Combinar celdas de A a Z
            $row++;
//          FILA 2
            $sheet->setCellValue('A' . $row, 'RESULTADO DE BUSQUEDA: Rango de fecha: ' . date("d-m-Y", strtotime($this->desde)) . ' al ' . date("d-m-Y", strtotime($this->hasta)));
            $subtitleStyle = $sheet->getStyle('A' . $row);
            $subtitleStyle->getFont()->setSize(12); // Tamaño de fuente
            $subtitleStyle->getFont()->setBold(true); // Texto en negrita
            $sheet->mergeCells('A' . $row . ':N' . $row); // Combinar celdas de A a Z
            $row++;
//          FILA 3
            $sheet->setCellValue('A'.$row, "");
            $sheet->mergeCells('A'.$row.':N'.$row);
            $row++;
//          FILA 4
            $sheet->setCellValue('A'.$row, 'SERVICIO DE TRANSPORTE');
            $titleStyle = $sheet->getStyle('A'.$row);
            $titleStyle->getFont()->setSize(8);
            $cellRange = 'A'.$row.':D'.$row;
            $sheet->mergeCells($cellRange);
            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE699');

            $sheet->setCellValue('E'.$row, 'FECHA PRESENTACIÓN');
            $titleStyle = $sheet->getStyle('E'.$row);
            $titleStyle->getFont()->setSize(8);
            $cellRange = 'E'.$row.':H'.$row;
            $sheet->mergeCells($cellRange);
            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFC000'); // Fondo

            $sheet->setCellValue('I'.$row, date('d/m/Y'));
            $titleStyle = $sheet->getStyle('I'.$row);
            $titleStyle->getFont()->setSize(8);
            $cellRange = 'I'.$row.':K'.$row;
            $sheet->mergeCells($cellRange);
            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFC000'); // Fondo

            $sheet->setCellValue('L'.$row, date("d-m-Y", strtotime($this->desde)) . ' al ' . date("d-m-Y", strtotime($this->hasta)));
            $titleStyle = $sheet->getStyle('L'.$row);
            $titleStyle->getFont()->setSize(8);
            $cellRange = 'L'.$row.':N'.$row;
            $sheet->mergeCells($cellRange);
            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00'); // Fondo

            $cellRange = 'A'.$row.':N'.$row;
            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $row++;
//          FILA 5
            $sheet->setCellValue('A'.$row, 'DATOS DEL SERVICIO TRANSPORTE');
            $titleStyle = $sheet->getStyle('A'.$row);
            $titleStyle->getFont()->setSize(8);
            $cellRange = 'A'.$row.':N'.$row;
            $sheet->mergeCells($cellRange);
            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('C4D79B'); // Fondo
            $cellRange = 'A'.$row.':N'.$row;

            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $row++;

//           FILA 6
            $sheet->setCellValue('E'.$row, 'REMITENTE');
            $titleStyle = $sheet->getStyle('E'.$row);
            $titleStyle->getFont()->setSize(8);
            $cellRange = 'E'.$row.':G'.$row;
            $sheet->mergeCells($cellRange);
            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('C4D79B'); // Fondo

            $sheet->setCellValue('H'.$row, 'DESTINATARIO');
            $titleStyle = $sheet->getStyle('H'.$row);
            $titleStyle->getFont()->setSize(8);
            $cellRange = 'H'.$row.':J'.$row;
            $sheet->mergeCells($cellRange);
            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('C4D79B'); // Fondo

            $cellRange = 'A'.$row.':N'.$row;
            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $cellRangeNoUsed1 = 'A' . $row . ':D' . $row; // Desde A hasta D
            $cellRangeNoUsed2 = 'K' . $row . ':N' . $row; // Desde K hasta P

            $styleNoUsed = $sheet->getStyle($cellRangeNoUsed1);
            $styleNoUsed->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE); // Sin bordes
            $styleNoUsed->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE); // Sin fondo

            $styleNoUsed = $sheet->getStyle($cellRangeNoUsed2);
            $styleNoUsed->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE); // Sin bordes
            $styleNoUsed->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
            $row++;
//            FILA 7
            $sheet->setCellValue('A' . $row, 'Código');
            $sheet->setCellValue('B' . $row, 'N° OS');
            $sheet->setCellValue('C' . $row, 'Motivo');
            $sheet->setCellValue('D' . $row, 'Detalle Motivo');
            $sheet->setCellValue('E' . $row, 'RUC');
            $sheet->setCellValue('F' . $row, 'Razón socail');
            $sheet->setCellValue('G' . $row, 'Dirección');
            $sheet->setCellValue('H' . $row, 'RUC');
            $sheet->setCellValue('I' . $row, 'Razón socail');
            $sheet->setCellValue('J' . $row, 'Dirección');
            $sheet->setCellValue('K' . $row, 'Peso');
            $sheet->setCellValue('L' . $row, 'Volumen');
            $sheet->setCellValue('M' . $row, 'Estado Aprobación');
            $sheet->setCellValue('N' . $row, 'Estado Entrega');

            // Estilo para los encabezados de la tabla
            $headerStyle = $sheet->getStyle('A' . $row . ':N' . $row);
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID); // Fondo sólido
            $headerStyle->getFill()->getStartColor()->setARGB('FFD3D3D3'); // Color gris claro
            $headerStyle->getFont()->setBold(true);

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(12);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(12);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(60);
            $sheet->getColumnDimension('H')->setWidth(6);
            $sheet->getColumnDimension('I')->setWidth(6);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(20);
            $sheet->getColumnDimension('N')->setWidth(15);

            $cellRange = 'A'.$row.':N'.$row;
            $rowStyle = $sheet->getStyle($cellRange);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);

            // Llenar la tabla con los datos
            $row++; // Comenzar en la cuarta fila
            foreach ($serviciosTransportes as $servicio) {
                $cellRange = 'A' . $row . ':N' . $row; // Definir el rango de la fila actual

                $sheet->setCellValue('A' . $row, $servicio->serv_transpt_codigo);
                $sheet->setCellValue('B' . $row, $servicio->serv_transpt_codigo_os);
                $sheet->setCellValue('C' . $row, $servicio->serv_transpt_motivo);
                $sheet->setCellValue('D' . $row, $servicio->serv_transpt_detalle_motivo);
                $sheet->setCellValue('E' . $row, $servicio->serv_transpt_remitente_ruc);
                $sheet->setCellValue('F' . $row, $servicio->serv_transpt_remitente_razon_social);
                $sheet->setCellValue('G' . $row, $servicio->serv_transpt_remitente_direccion);
                $sheet->setCellValue('H' . $row, $servicio->serv_transpt_destinatario_ruc);
                $sheet->setCellValue('I' . $row, $servicio->serv_transpt_destinatario_razon_social);
                $sheet->setCellValue('J' . $row, $servicio->serv_transpt_destinatario_direccion);
                $sheet->setCellValue('K' . $row, $this->general->formatoDecimal($servicio->serv_transpt_peso));
                $sheet->setCellValue('L' . $row, $this->general->formatoDecimal($servicio->serv_transpt_volumen));

                // Estado de aprobación
                $estadoAprobacion = '';
                if ($servicio->serv_transpt_estado_aprobacion == 1) {
                    $estadoAprobacion = 'APROBADO';
                } elseif ($servicio->serv_transpt_estado_aprobacion == 2) {
                    $estadoAprobacion = 'EN CAMINO';
                } elseif ($servicio->serv_transpt_estado_aprobacion == 3) {
                    $estadoAprobacion = 'CULMINADO';
                }
                $sheet->setCellValue('M' . $row, $estadoAprobacion);

                // Estado de entrega
                $estadoEntrega = '';
                if ($servicio->serv_transpt_entrega == 1) {
                    $estadoEntrega = 'ENTREGADO';
                } elseif ($servicio->serv_transpt_entrega == 2) {
                    $estadoEntrega = 'NO ENTREGADO';
                }
                $sheet->setCellValue('N' . $row, $estadoEntrega);

                // Aplicar bordes a la fila actual para que parezca una tabla
                $rowStyle = $sheet->getStyle($cellRange);
                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');

                $row++; // Pasar a la siguiente fila
            }
            // Ajustar el ancho de las columnas automáticamente
            foreach (range('A', 'N') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Guardar el archivo en un archivo temporal
            $fileName = 'servicios_transporte_' . now()->format('Ymd_His') . '.xlsx';
            $tempFilePath = sys_get_temp_dir() . '/' . $fileName;

            // Usar el writer de PhpSpreadsheet para guardar el archivo
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($tempFilePath);

            // Descargar el archivo
            return response()->download($tempFilePath, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrió un error al generar el reporte en Excel: ' . $e->getMessage());
            return;
        }
    }

}
