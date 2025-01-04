<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Despacho;
use App\Models\DespachoVenta;
use App\Models\General;
use App\Models\Logs;
use App\Models\Programacion;
use App\Models\Transportista;
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
    public $id_programacionRetorno = "";
    // Atributo público para almacenar los checkboxes seleccionados
    public $selectedItems = [];
    public $estadoComprobante = [];
    /* ---------------------------------------- */
    private $logs;
    private $programacion;
    private $despacho;
    private $general;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->general = new General();
    }
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
        $this->tipo_aprobacacion = null;
    }

    public function render()
    {
        $resultado = $this->programacion->listar_programaciones_historial_programacion($this->desde,$this->hasta,$this->serie_correlativo,$this->estadoPro);
        foreach($resultado as $rehs){
            $totalVenta = 0;
            $rehs->despacho = DB::table('despachos as d')
                ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                ->join('tipo_servicios as ts','ts.id_tipo_servicios','=','d.id_tipo_servicios')
                ->where('d.id_programacion','=',$rehs->id_programacion)
                ->get();
            foreach ($rehs->despacho as $des){
                $des->comprobantes =  DB::table('despacho_ventas as dv')
                    ->where('id_despacho','=',$des->id_despacho)
                    ->get();
                foreach ($des->comprobantes as $com){
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $totalVenta+= round($precio,2);
                }
                $des->totalVentaDespacho = $totalVenta;
            }
        }

        $roleId = auth()->user()->roles->first()->id ?? null;

        return view('livewire.programacioncamiones.historial-programacion',compact('resultado','roleId'));
    }

    public function listar_informacion_despacho($id){
        try {
            $this->listar_detalle_despacho = DB::table('despachos as d')
                ->join('users as u','u.id_users','=','d.id_users')
                ->where('d.id_despacho','=',$id)->first();
            if ($this->listar_detalle_despacho){
                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas')->where('id_despacho','=',$id)->get();
                foreach ($this->listar_detalle_despacho->comprobantes as $comp){
                    $this->estadoComprobante[$comp->id_despacho_venta] = 2;
                }
            }
        }catch (\Exception $e){
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
                    ->where('d.despacho_estado_aprobacion','=',3)
//                    ->limit(1) // solo se va a traer 1 por que primero es el primer despacho ya que  en local y provincial  es solo una OS y en mixto el primero es local y los demas provionciales pero en caso de mixto el
                    ->get();
                if (count($result->despacho) > 0){
                    $conteoDesp++;
                    foreach ($result->despacho as $indexComprobantes => $des){
                        if ($indexComprobantes == 0){

                            if ($des->id_tipo_servicios == 1){ // si es mixto va a entrar el primero que es local donde traigo todo
//                                $des->comprobantes =  DB::table('despacho_ventas as dv')
//                                ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
//                                ->where('d.id_despacho','=',$des->id_despacho)
//                                ->where('d.id_programacion','=',$result->id_programacion)
//                                ->where('d.id_tipo_servicios','<>',2)
//                                ->get();
                                $dat =  DB::table('despacho_ventas as dv')
                                    ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                                    ->where('d.id_despacho', '=', $des->id_despacho)
                                    ->where('d.id_programacion', '=', $result->id_programacion)
                                    ->where('d.id_tipo_servicios', '<>', 2) // Excluir provinciales
                                    ->whereNotExists(function ($query) use ($result) {
                                        $query->select(DB::raw(1))
                                            ->from('despacho_ventas as dv_provincial')
                                            ->join('despachos as d_provincial', 'd_provincial.id_despacho', '=', 'dv_provincial.id_despacho')
                                            ->where('d_provincial.id_programacion', '=', $result->id_programacion)
                                            ->where('d_provincial.id_tipo_servicios', '=', 2) // Solo considerar provinciales
                                            ->whereRaw('dv_provincial.id_venta = dv.id_venta'); // Comparar ventas duplicadas
                                    })
                                    ->get();
                                Log::info($dat);
                                $des->comprobantes = $dat;
                            }else{
                                $des->comprobantes =  DB::table('despacho_ventas as dv')
                                ->where('dv.id_despacho','=',$des->id_despacho)
                                ->get();
                            }

                            foreach ($des->comprobantes as $com){
                                $precio = floatval($com->despacho_venta_cfimporte);
                                $totalVenta+= round($precio,2);
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
                $sheet1->mergeCells('A'.$row.':Z'.$row);
                $row++;
                $sheet1->setCellValue('A'.$row, $mensaje);
                $titleStyle = $sheet1->getStyle('A'.$row);
                $titleStyle->getFont()->setSize(12);
                $titleStyle->getFont()->setBold(true);
                $sheet1->mergeCells('A'.$row.':Z'.$row);
                $row++;
                $sheet1->setCellValue('A'.$row, "");
                $sheet1->mergeCells('A'.$row.':Z'.$row);
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
                $cellRange = 'A'.$row.':N'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('C4D79B'); // Fondo

                $sheet1->setCellValue('O'.$row, 'TRANSPORTE LOCAL');
                $titleStyle = $sheet1->getStyle('O'.$row);
                $titleStyle->getFont()->setSize(8);
                $cellRange = 'O'.$row.':V'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92CDDC'); // Fondo

                $sheet1->setCellValue('W'.$row, '');
                $cellRange = 'W'.$row.':W'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('DDD9C4'); // Fondo
                $rowStyle->getFont()->setBold(true); // Hacer negritas

                $sheet1->setCellValue('X'.$row, 'TRANSPORTE PROVINCIA');
                $titleStyle = $sheet1->getStyle('X'.$row);
                $titleStyle->getFont()->setSize(8);
                $cellRange = 'X'.$row.':AB'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E2EFDA'); // Fondo

                $cellRange = 'A'.$row.':AB'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFont()->setSize(10);
                $rowStyle->getFont()->setBold(true);
                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $row++;

                $sheet1->setCellValue('A'.$row, 'T');
                $sheet1->setCellValue('B'.$row, 'CODF');
                $sheet1->setCellValue('C'.$row, 'DOCUMENTO');
                $sheet1->setCellValue('D'.$row, 'F . EMISION');
                $sheet1->setCellValue('E'.$row, 'N° PRO');
                $sheet1->setCellValue('F'.$row, 'N° RUC');
                $sheet1->setCellValue('G'.$row, 'CLIENTE');
                $sheet1->setCellValue('H'.$row, 'T1');
                $sheet1->setCellValue('I'.$row, 'T2');
                $sheet1->setCellValue('J'.$row, 'GUIA');
                $sheet1->setCellValue('K'.$row, 'IMPORTE');
                $sheet1->setCellValue('L'.$row, 'F. DESPACHO');
                $sheet1->setCellValue('M'.$row, 'TIPO SERVICIO');
                $sheet1->setCellValue('N'.$row, 'PESO');
                $cellRange = 'A'.$row.':N'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('C4D79B'); // Fondo
//            $rowStyle->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE); // color de texto
                $rowStyle->getFont()->setBold(true); // Hacer negritas

                $sheet1->setCellValue('O'.$row, 'N° OS');
                $sheet1->setCellValue('P'.$row, 'FLETE');
//            $sheet1->setCellValue('Q'.$row, 'GARAJE');
                $sheet1->mergeCells('P'.$row.':Q'.$row);
                $sheet1->setCellValue('R'.$row, 'OTROS');
                $sheet1->setCellValue('S'.$row, 'AYUDANTES');
                $sheet1->setCellValue('T'.$row, 'TRANSPORTISTA');
                $sheet1->setCellValue('U'.$row, 'FLETE TOTAL');
                $sheet1->setCellValue('V'.$row, '%');
                $cellRange = 'O'.$row.':V'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92CDDC'); // Fondo
                $rowStyle->getFont()->setBold(true); // Hacer negritas

                $sheet1->setCellValue('W'.$row, 'N° OS');
                $sheet1->setCellValue('X'.$row, 'TRANSPORTISTA ');
                $sheet1->setCellValue('Y'.$row, 'DESTINO');
                $sheet1->setCellValue('Z'.$row, 'FACTURA');
                $sheet1->setCellValue('AA'.$row, 'FLETE TOTAL');
                $sheet1->setCellValue('AB'.$row, '%');
                $cellRange = 'W'.$row.':AB'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('DDD9C4'); // Fondo
                $rowStyle->getFont()->setBold(true); // Hacer negritas

                $sheet1->getColumnDimension('A')->setWidth(6);
                $sheet1->getColumnDimension('B')->setWidth(12);
                $sheet1->getColumnDimension('C')->setWidth(15);
                $sheet1->getColumnDimension('D')->setWidth(15);
                $sheet1->getColumnDimension('E')->setWidth(12);
                $sheet1->getColumnDimension('F')->setWidth(12);
                $sheet1->getColumnDimension('G')->setWidth(60);
                $sheet1->getColumnDimension('H')->setWidth(6);
                $sheet1->getColumnDimension('I')->setWidth(6);
                $sheet1->getColumnDimension('J')->setWidth(15);
                $sheet1->getColumnDimension('K')->setWidth(15);
                $sheet1->getColumnDimension('L')->setWidth(15);
                $sheet1->getColumnDimension('M')->setWidth(15);
                $sheet1->getColumnDimension('N')->setWidth(15);
                $sheet1->getColumnDimension('O')->setWidth(12);
                $sheet1->getColumnDimension('P')->setWidth(15);
                $sheet1->getColumnDimension('R')->setWidth(12);
//            $sheet1->getColumnDimension('Q')->setWidth(15);
                $sheet1->getColumnDimension('S')->setWidth(15);
                $sheet1->getColumnDimension('T')->setWidth(25);
                $sheet1->getColumnDimension('U')->setWidth(18);
                $sheet1->getColumnDimension('V')->setWidth(6);
                $sheet1->getColumnDimension('W')->setWidth(12);
                $sheet1->getColumnDimension('X')->setWidth(25);
                $sheet1->getColumnDimension('Y')->setWidth(18);
                $sheet1->getColumnDimension('Z')->setWidth(25);
                $sheet1->getColumnDimension('AA')->setWidth(18);
                $sheet1->getColumnDimension('AB')->setWidth(6);

                $cellRange = 'A'.$row.':Z'.$row;
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
                            $filaPorcentajeLocal = null;
                            $fleteFinalLocal = null;

                            $fleteFinalProvin = null;
                            $filaPorcentajeProvin = null;

                            $totalPesoDespachos = 0;
                            $importeTotalDespachos = 0;
                            foreach ($des->comprobantes  as $indexComprobante => $comproba){
                                /**/
                                // buscar si es mixto o un servicio normal
                                $validarMixto = DB::table('despacho_ventas as dv')
                                    ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
                                    ->where('dv.despacho_venta_cfnumser','=',$comproba->despacho_venta_cfnumser)
                                    ->where('dv.despacho_venta_cfnumdoc','=',$comproba->despacho_venta_cfnumdoc)
                                    ->where('dv.id_despacho','<>',$des->id_despacho)
                                    ->where('d.id_programacion','=',$resPro->id_programacion)
                                    ->where('d.id_tipo_servicios','=',2)->first();

                                if ($validarMixto){
                                    $loc = 'MIXTO';
                                }else {
                                    $loc = $des->id_tipo_servicios == 1 ? 'LOCAL' : 'PROVINCIAL';
                                }

                                /**/
                                $guia = $comproba->despacho_venta_guia;
                                $parte1 = substr($guia, 0, 4); // T001
                                $parte2 = substr($guia, 4);    // 0013260

                                $sheet1->setCellValue('A'.$row, $comproba->despacho_venta_cftd);
                                $sheet1->setCellValue('B'.$row, $comproba->despacho_venta_cfnumser);
                                $sheet1->setCellValue('C'.$row, $comproba->despacho_venta_cfnumdoc);
                                $sheet1->setCellValue('D'.$row, date('d/m/Y', strtotime($comproba->despacho_venta_grefecemision)));
                                $sheet1->setCellValue('E'.$row, $resPro->programacion_numero_correlativo);
                                $sheet1->setCellValue('F'.$row, $comproba->despacho_venta_cfcodcli);
                                $sheet1->setCellValue('G'.$row, $comproba->despacho_venta_cnomcli);
                                $sheet1->setCellValue('H'.$row, 'GS');
                                $sheet1->setCellValue('I'.$row, $parte1);
                                $sheet1->setCellValue('J'.$row, $parte2);
                                $sheet1->setCellValue('K'.$row, $this->general->formatoDecimal($comproba->despacho_venta_cfimporte));
                                $sheet1->setCellValue('L'.$row, date('d/m/Y',strtotime($resPro->programacion_fecha_aprobacion)));

                                $sheet1->setCellValue('M'.$row, $loc);
                                $cellRange = 'M'.$row.':M'.$row;
                                $rowStyle = $sheet1->getStyle($cellRange);
                                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                                $sheet1->setCellValue('N'.$row, $this->general->formatoDecimal($comproba->despacho_venta_total_kg));
                                if ($indexComprobante == 0 || $indexComprobante == 1){
                                    /* LOCAL */
                                    if ($des->id_tipo_servicios == 1){
                                        if ($indexComprobante == 0){
                                            $informacionliquidacion = DB::table('liquidacion_detalles as ld')
                                                ->join('liquidaciones as l','l.id_liquidacion','=','ld.id_liquidacion')
                                                ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
                                                ->where('l.liquidacion_estado_aprobacion','=',1)
                                                ->where('ld.id_despacho','=',$des->id_despacho)
                                                ->orderBy('ld.id_liquidacion_detalle','desc')
                                                ->orderBy('l.id_liquidacion','desc')->first();

                                            $sheet1->setCellValue('O'.$row, $des->despacho_numero_correlativo);

                                            if ($informacionliquidacion){
                                                $gastos = DB::table('liquidacion_gastos')->where('id_liquidacion_detalle','=',$informacionliquidacion->id_liquidacion_detalle)->get();
                                                if ($gastos){
                                                    $costoTarifa = $gastos[0]->liquidacion_gasto_monto;
                                                    $costoMano = $gastos[1]->liquidacion_gasto_monto;
                                                    $costoOtros = $gastos[2]->liquidacion_gasto_monto;
                                                    $pesoFinalLiquidacion = $gastos[3]->liquidacion_gasto_monto;

                                                    $totalGeneralLocal = ($costoTarifa + $costoMano + $costoOtros);


                                                    $sheet1->setCellValue('P'.$row, $this->general->formatoDecimal($costoTarifa));
                                                    $sheet1->mergeCells('P'.$row.':Q'.$row);

                                                    $sheet1->setCellValue('R'.$row, $this->general->formatoDecimal($costoOtros));
//                                        $sheet1->setCellValue('Q'.$row, $costoOtros);

                                                    $sheet1->setCellValue('S'.$row, $this->general->formatoDecimal($costoMano));
                                                    $sheet1->setCellValue('T'.$row, $des->transportista_nom_comercial.' '.$informacionliquidacion->liquidacion_serie.'-'.$informacionliquidacion->liquidacion_correlativo);
//                                        $sheet1->setCellValue('S'.$row, $des->transportista_nom_comercial.' salgo de linea aca'.$vehiT);
                                                    $sheet1->setCellValue('U'.$row, $this->general->formatoDecimal($totalGeneralLocal));
                                                    $sheet1->setCellValue('V'.$row, '');

                                                    $fleteFinalLocal = $totalGeneralLocal;
                                                    $filaPorcentajeLocal = $row;

                                                    /* SI EN CASO LA PROGRAMACIÓN ES MIXTA LLENAR LAS OS DE PROVINCIAL */
                                                    $osMixtoProgramacion = DB::table('despachos as d')
                                                        ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                                                        ->where('d.id_programacion','=',$resPro->id_programacion)
                                                        ->where('d.id_tipo_servicios','=',2)->get();


//                                                    if (count($osMixtoProgramacion) > 0){
//                                                        $rowMixto = $row;
//                                                        foreach ($osMixtoProgramacion as $mixt){
//
//                                                            $totalImporteComprobanteDespachoPro = DB::table('despacho_ventas')
//                                                                ->where('id_despacho','=',$mixt->id_despacho)
//                                                                ->where('despacho_detalle_estado_entrega','=',2)
//                                                                ->sum('despacho_venta_cfimporte');
//
//                                                            $informacionliquidacion = DB::table('liquidacion_detalles as ld')
//                                                                ->join('liquidaciones as l','l.id_liquidacion','=','ld.id_liquidacion')
//                                                                ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
//                                                                ->where('l.liquidacion_estado_aprobacion','=',1)
//                                                                ->where('ld.id_despacho','=',$mixt->id_despacho)
//                                                                ->orderBy('ld.id_liquidacion_detalle','desc')
//                                                                ->orderBy('l.id_liquidacion','desc')->first();
//
//                                                            if ($informacionliquidacion){
//
//
//                                                                $gastos = DB::table('liquidacion_gastos')->where('id_liquidacion_detalle','=',$informacionliquidacion->id_liquidacion_detalle)->get();
//                                                                if ($gastos){
//
//
//                                                                    $costoTarifa = $gastos[0]->liquidacion_gasto_monto;
//                                                                    $costoMano = $gastos[1]->liquidacion_gasto_monto;
//                                                                    $costoOtros = $gastos[2]->liquidacion_gasto_monto;
//                                                                    $pesoFinalLiquidacion = $gastos[3]->liquidacion_gasto_monto;
//
//                                                                    $totalGeneralLocal = (($costoTarifa * $pesoFinalLiquidacion) + $costoMano + $costoOtros);
//
//                                                                    $destino = "";
//                                                                    if ($informacionliquidacion->id_departamento){
//                                                                        $dep = DB::table('departamentos')->where('id_departamento','=',$informacionliquidacion->id_departamento)->first();
//                                                                        $destino.= $dep->departamento_nombre;
//                                                                    }
//                                                                    if ($informacionliquidacion->id_provincia){
//                                                                        $provi = DB::table('provincias')->where('id_provincia','=',$informacionliquidacion->id_provincia)->first();
//                                                                        $destino.= "-".$provi->provincia_nombre;
//                                                                    }
//                                                                    if ($informacionliquidacion->id_distrito){
//                                                                        $disti = DB::table('distritos')->where('id_distrito','=',$informacionliquidacion->id_distrito)->first();
//                                                                        $destino.= "-".$disti->distrito_nombre;
//                                                                    }
//
//                                                                    $sheet1->setCellValue('W'.$rowMixto, $mixt->despacho_numero_correlativo);
//                                                                    $sheet1->setCellValue('X'.$rowMixto, $mixt->transportista_nom_comercial);
//                                                                    $sheet1->setCellValue('Y'.$rowMixto, $destino);
//                                                                    $sheet1->setCellValue('Z'.$rowMixto, $informacionliquidacion->liquidacion_serie.'-'.$informacionliquidacion->liquidacion_correlativo);
//                                                                    $sheet1->setCellValue('AA'.$rowMixto, $this->general->formatoDecimal($totalGeneralLocal));
//                                                                    $sheet1->setCellValue('AB'.$rowMixto, $this->general->formatoDecimal(($totalGeneralLocal / $totalImporteComprobanteDespachoPro) * 100));
//                                                                    if ($informacionliquidacion->liquidacion_detalle_comentarios){
//                                                                        $sheet1->setCellValue('AC'.$rowMixto, $informacionliquidacion->liquidacion_detalle_comentarios);
//                                                                        $sheet1->getColumnDimension('AC')->setWidth(15);
//                                                                        $cellRange = 'AC'.$rowMixto.':AC'.$rowMixto;
//                                                                        $sheet1->mergeCells($cellRange);
//                                                                        $rowStyle = $sheet1->getStyle($cellRange);
//                                                                        $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FABF8F'); // Fondo
//                                                                        $rowStyle->getFont()->setBold(true); // Hacer negritas
//
//                                                                    }
//
//                                                                    $rowMixto++;
//
//
//                                                                }
//                                                            }
//                                                        }
//                                                    }
                                                }
                                            }
                                        }else{
                                            /* ---------------------------------------------*/
                                            $vehiculo = DB::table('vehiculos  as v')
                                                ->join('tipo_vehiculos as tv','tv.id_tipo_vehiculo','=','v.id_tipo_vehiculo')
                                                ->where('v.id_vehiculo','=',$des->id_vehiculo)->first();
                                            $vehiT = "";
                                            if ($vehiculo){
                                                $vehiT = $vehiculo->tipo_vehiculo_concepto.': '.$vehiculo->vehiculo_capacidad_peso.'kg - '.$vehiculo->vehiculo_placa;
                                            }
                                            /* ---------------------------------------------*/
                                            $sheet1->setCellValue('O'.$row, '');
                                            $sheet1->setCellValue('P'.$row, "");
                                            $sheet1->mergeCells('P'.$row.':Q'.$row);
                                            $sheet1->setCellValue('R'.$row, "");
                                            $sheet1->setCellValue('S'.$row, "");
                                            $sheet1->setCellValue('T'.$row, $vehiT);
                                            $sheet1->setCellValue('U'.$row, "");
                                            $sheet1->setCellValue('V'.$row, '');
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
                                    }


                                    /* PROVINCIAL */
                                    if ($des->id_tipo_servicios == 2){
                                        if ($indexComprobante == 0){
                                            $informacionliquidacion = DB::table('liquidacion_detalles as ld')
                                                ->join('liquidaciones as l','l.id_liquidacion','=','ld.id_liquidacion')
                                                ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
                                                ->where('l.liquidacion_estado_aprobacion','=',1)
                                                ->where('ld.id_despacho','=',$des->id_despacho)
                                                ->orderBy('ld.id_liquidacion_detalle','desc')
                                                ->orderBy('l.id_liquidacion','desc')->first();
                                            $sheet1->setCellValue('W'.$row, $des->despacho_numero_correlativo);
                                            if ($informacionliquidacion){
                                                $gastos = DB::table('liquidacion_gastos')->where('id_liquidacion_detalle','=',$informacionliquidacion->id_liquidacion_detalle)->get();
                                                if ($gastos){
                                                    $costoTarifa = $gastos[0]->liquidacion_gasto_monto;
                                                    $costoMano = $gastos[1]->liquidacion_gasto_monto;
                                                    $costoOtros = $gastos[2]->liquidacion_gasto_monto;
                                                    $pesoFinalLiquidacion = $gastos[3]->liquidacion_gasto_monto;

                                                    $totalGeneralLocal = (($costoTarifa * $pesoFinalLiquidacion) + $costoMano + $costoOtros);

                                                    $destino = "";
                                                    if ($informacionliquidacion->id_departamento){
                                                        $dep = DB::table('departamentos')->where('id_departamento','=',$informacionliquidacion->id_departamento)->first();
                                                        $destino.= $dep->departamento_nombre;
                                                    }
                                                    if ($informacionliquidacion->id_provincia){
                                                        $provi = DB::table('provincias')->where('id_provincia','=',$informacionliquidacion->id_provincia)->first();
                                                        $destino.= "-".$provi->provincia_nombre;
                                                    }
                                                    if ($informacionliquidacion->id_distrito){
                                                        $disti = DB::table('distritos')->where('id_distrito','=',$informacionliquidacion->id_distrito)->first();
                                                        $destino.= "-".$disti->distrito_nombre;
                                                    }

                                                    $sheet1->setCellValue('X'.$row, $des->transportista_nom_comercial);
                                                    $sheet1->setCellValue('Y'.$row, $destino);
                                                    $sheet1->setCellValue('Z'.$row, $informacionliquidacion->liquidacion_serie.'-'.$informacionliquidacion->liquidacion_correlativo);
                                                    $sheet1->setCellValue('AA'.$row, $this->general->formatoDecimal($totalGeneralLocal));
                                                    $sheet1->setCellValue('AB'.$row, '');

                                                    $fleteFinalProvin = $totalGeneralLocal;
                                                    $filaPorcentajeProvin = $row;
                                                }
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
                                $cellRange = 'A'.$row.':AB'.$row;
                                $rowStyle = $sheet1->getStyle($cellRange);
                                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                                $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
                                $cellRange = 'E'.$row.':E'.$row;
                                $rowStyle = $sheet1->getStyle($cellRange);
//                        $rowStyle->getFont()->setSize(10);
                                $rowStyle->getFont()->setBold(true);

                                $cellRange = 'O'.$row.':Z'.$row;
                                $rowStyle = $sheet1->getStyle($cellRange);
//                        $rowStyle->getFont()->setBold(true);
                                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                $sheet1->mergeCells('P'.$row.':Q'.$row);

                                $row++;
                                $importeTotalDespachos+=$comproba->despacho_venta_cfimporte;
                                $totalPesoDespachos+=$comproba->despacho_venta_total_kg;
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
                            $sheet1->setCellValue('E'.$row, "");
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
                                $sheet1->setCellValue('J'.$row, "");
                            }
                            $sheet1->setCellValue('K'.$row, $this->general->formatoDecimal($importeTotalDespachos));
                            $sheet1->setCellValue('L'.$row, "");
                            $sheet1->setCellValue('M'.$row, "");
                            $sheet1->setCellValue('N'.$row, $this->general->formatoDecimal($totalPesoDespachos));
                            $cellRange = 'A'.$row.':N'.$row;
                            $rowStyle = $sheet1->getStyle($cellRange);
                            $rowStyle->getFont()->setSize(10);
                            $rowStyle->getFont()->setBold(true);
                            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
                            $row++;
                            /* ----------------------------------------------- */
                            if ($filaPorcentajeLocal){
                                $porcentaje = (($fleteFinalLocal / $importeTotalDespachos) * 100);
                                $porcentaje = $this->general->formatoDecimal($porcentaje);
                                $sheet1->setCellValue('V'.$filaPorcentajeLocal, $this->general->formatoDecimal($porcentaje).'%');
                            }
                            if ($filaPorcentajeProvin){
                                $porcentaje = (($fleteFinalProvin / $importeTotalDespachos) * 100);
                                $porcentaje = $this->general->formatoDecimal($porcentaje);
                                $sheet1->setCellValue('AB'.$filaPorcentajeProvin, $this->general->formatoDecimal($porcentaje).'%');
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
            if (count($this->selectedItems) > 0){
                // Validar que al menos un checkbox esté seleccionado
                $this->validate([
                    'selectedItems' => 'required|array|min:1',
                ], [
                    'selectedItems.required' => 'Debe seleccionar al menos una opción.',
                    'selectedItems.array'    => 'La selección debe ser válida.',
                    'selectedItems.min'      => 'Debe seleccionar al menos una opción.',
                ]);
                DB::beginTransaction();
                foreach ($this->selectedItems as $select){
                    $updateDespacho = Despacho::find($select);
                    $updateDespacho->despacho_estado_aprobacion = 2;
                    if ($updateDespacho->save()){
                        $existeComprobanteCamino = DB::table('despacho_ventas')->where('id_despacho', $select)->update(['despacho_detalle_estado_entrega'=>1]);
                        if (!$existeComprobanteCamino){
                            DB::rollBack();
                            session()->flash('error_delete', 'No se pudo cambiar los estados de los comprobantes a "En Camino".');
                            return;
                        }
                    }else{
                        DB::rollBack();
                        session()->flash('error_delete', 'No se pudo cambiar los estados de los despachos a "En Camino".');
                        return;
                    }
                }
                DB::commit();
                $this->selectedItems = [];
                $this->dispatch('hideModalDelete');
                session()->flash('success', 'Despachos en camino.');
            }else{
                $this->validate([
                    'id_despacho' => 'required|integer',
                ], [
                    'id_despacho.required' => 'El identificador es obligatorio.',
                    'id_despacho.integer' => 'El identificador debe ser un número entero.',
                ]);

                DB::beginTransaction();
                $updateDespacho = Despacho::find($this->id_despacho);
                $updateDespacho->despacho_estado_aprobacion = 2;
                if ($updateDespacho->save()){
                    $existeComprobanteCamino = DB::table('despacho_ventas')->where('id_despacho', $this->id_despacho)->update(['despacho_detalle_estado_entrega'=>1]);
                    if ($existeComprobanteCamino){
                        DB::commit();
                        $this->dispatch('hideModalDelete');
                        session()->flash('success', 'Despacho en camino.');
                    }
                }else{
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
    public function cambiarEstadoComprobante(){
        try {
            // $estado sebe contener el valor del select
            if (!Gate::allows('cambiar_estado_comprobante')) {
                session()->flash('errorComprobante', 'No tiene permisos para poder cambiar el estado del comprobante.');
                return;
            }

            DB::beginTransaction();
            foreach ($this->estadoComprobante as $id_comprobante => $estado){
                // Validar cada estado
                if (!in_array((int)$estado, [2, 3])) {
                    DB::rollBack();
                    session()->flash('errorComprobante', 'Estado inválido seleccionado.');
                    return;
                }
                $comprobante = DespachoVenta::find($id_comprobante);
                if (!$comprobante) {
                    DB::rollBack();
                    session()->flash('errorComprobante', 'Comprobante no encontrado.');
                    return;
                }
                // Actualizar el estado del comprobante
                $comprobante->despacho_detalle_estado_entrega = (int)$estado;
                $comprobante->save();
            }

            $id_despacho = $this->listar_detalle_despacho->id_despacho;
            Despacho::where('id_despacho', $id_despacho)->update(['despacho_estado_aprobacion' => 3]);

            DB::commit();
            session()->flash('success', 'Los estados fueron actualizados correctamente.');
            $this->listar_informacion_despacho($id_despacho);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('errorComprobante', 'Ocurrió un error al cambiar el estado del registro. Por favor, inténtelo nuevamente.');
        }
    }

}
