<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Despacho;
use App\Models\DespachoVenta;
use App\Models\General;
use App\Models\Logs;
use App\Models\Programacion;
use App\Models\Transportista;
use App\Models\Historialdespachoventa;
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
    public $id_programacionRetorno = "";
    // Atributo público para almacenar los checkboxes seleccionados
    public $selectedItems = [];
    public $estadoComprobante = [];
    /* ---------------------------------------- */
    private $logs;
    private $programacion;
    private $despacho;
    private $general;
    private $historialdespachoventa;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->general = new General();
        $this->historialdespachoventa = new Historialdespachoventa();
    }
    public function mount()
    {
        $this->desde = Carbon::today()->toDateString(); // Fecha actual
        $this->hasta = Carbon::today()->addDays(6)->toDateString(); // Fecha 6 días después de la actual
    }

    public function render()
    {
        $resultado = $this->programacion->listar_programaciones_historial_programacion($this->desde,$this->hasta,$this->serie_correlativo,$this->estadoPro);
        foreach($resultado as $rehs){
            $rehs->despacho = DB::table('despachos as d')
                ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                ->join('tipo_servicios as ts','ts.id_tipo_servicios','=','d.id_tipo_servicios')
                ->where('d.id_programacion','=',$rehs->id_programacion)
                ->get();
            foreach ($rehs->despacho as $des){
                $totalVenta = 0;
                $des->comprobantes =  DB::table('despacho_ventas as dv')
                    ->where('dv.id_despacho','=',$des->id_despacho)
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
            $this->estadoComprobante = [];
            $this->listar_detalle_despacho = DB::table('despachos as d')
                ->join('users as u','u.id_users','=','d.id_users')
                ->where('d.id_despacho','=',$id)->first();
            if ($this->listar_detalle_despacho){
                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas')->where('id_despacho','=',$id)->get();
                foreach ($this->listar_detalle_despacho->comprobantes as $comp){
                    if (!in_array($comp->despacho_detalle_estado_entrega, [2, 3, 4])){
                        $this->estadoComprobante[$comp->id_despacho_venta] = 2;
                    }
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
                    ->where('d.despacho_estado_aprobacion','<>',4)
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
                                // Comprobantes locales
                                $datLocales =  DB::table('despacho_ventas as dv')
                                    ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                                    ->where('d.id_despacho', '=', $des->id_despacho)
                                    ->where('d.id_programacion', '=', $result->id_programacion)
//                                    ->where('d.id_tipo_servicios', '<>', 2) // Excluir provinciales
                                    ->whereNotExists(function ($query) use ($result) {
                                        $query->select(DB::raw(1))
                                            ->from('despacho_ventas as dv_provincial')
                                            ->join('despachos as d_provincial', 'd_provincial.id_despacho', '=', 'dv_provincial.id_despacho')
                                            ->where('d_provincial.id_programacion', '=', $result->id_programacion)
                                            ->where('d_provincial.id_tipo_servicios', '=', 2) // Solo considerar provinciales
                                            ->whereRaw('dv_provincial.despacho_venta_cftd = dv.despacho_venta_cftd') // Comparar ventas duplicadas
                                            ->whereRaw('dv_provincial.despacho_venta_cfnumser = dv.despacho_venta_cfnumser') // Comparar ventas duplicadas
                                            ->whereRaw('dv_provincial.despacho_venta_cfnumdoc = dv.despacho_venta_cfnumdoc'); // Comparar ventas duplicadas
                                    })
                                    ->get();

                                /* BUSCAR DESPACHOS PROVINCIALES DE LA MISMA PROGRAMACION */
                                // Obtener todos los despachos provinciales de la misma programación
                                $desProvinciPro = DB::table('despachos')
                                    ->where('id_programacion', '=', $result->id_programacion)
                                    ->where('id_tipo_servicios', '=', 2)
                                    ->pluck('id_despacho'); // Solo obtenemos los IDs de los despachos provinciales

                                // Obtener comprobantes provinciales si existen
                                $datProvinciales = collect(); // Inicializar como colección vacía

                                if ($desProvinciPro->isNotEmpty()) {
                                    $datProvinciales = DB::table('despacho_ventas')
                                        ->whereIn('id_despacho', $desProvinciPro) // Obtener comprobantes de todos los despachos provinciales
                                        ->get();
                                }

                                // Combinar comprobantes locales y provinciales
                                $datCombinado = $datLocales->merge($datProvinciales);
                                $des->comprobantes = $datCombinado;

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
                $cellRange = 'O'.$row.':W'.$row;
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
                $cellRange = 'X'.$row.':AC'.$row;
                $sheet1->mergeCells($cellRange);
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E2EFDA'); // Fondo

                $cellRange = 'A'.$row.':AC'.$row;
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
                $rowStyle->getFont()->setBold(true); // Hacer negritas

                $sheet1->setCellValue('O'.$row, 'N° OS');
                $sheet1->setCellValue('P'.$row, 'FLETE');
                $sheet1->mergeCells('P'.$row.':Q'.$row);
                $sheet1->setCellValue('R'.$row, 'OTROS');
                $sheet1->setCellValue('S'.$row, 'AYUDANTES');
                $sheet1->setCellValue('T'.$row, 'TRANSPORTISTA');
                $sheet1->setCellValue('U'.$row, 'FACTURA PROVEEDOR'); // Nueva columna
                $sheet1->setCellValue('V'.$row, 'FLETE TOTAL');
                $sheet1->setCellValue('W'.$row, '%');
                $cellRange = 'O'.$row.':W'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92CDDC'); // Fondo
                $rowStyle->getFont()->setBold(true); // Hacer negritas

                $sheet1->setCellValue('X'.$row, 'N° OS');
                $sheet1->setCellValue('Y'.$row, 'TRANSPORTISTA ');
                $sheet1->setCellValue('Z'.$row, 'DESTINO');
                $sheet1->setCellValue('AA'.$row, 'FACTURA');
                $sheet1->setCellValue('AB'.$row, 'FLETE TOTAL');
                $sheet1->setCellValue('AC'.$row, '%');
                $cellRange = 'X'.$row.':AC'.$row;
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
                $sheet1->getColumnDimension('S')->setWidth(15);
                $sheet1->getColumnDimension('T')->setWidth(25);
                $sheet1->getColumnDimension('U')->setWidth(25); // Ancho de la nueva columna
                $sheet1->getColumnDimension('V')->setWidth(18);
                $sheet1->getColumnDimension('W')->setWidth(6);
                $sheet1->getColumnDimension('X')->setWidth(12);
                $sheet1->getColumnDimension('Y')->setWidth(25);
                $sheet1->getColumnDimension('Z')->setWidth(18);
                $sheet1->getColumnDimension('AA')->setWidth(25);
                $sheet1->getColumnDimension('AB')->setWidth(18);
                $sheet1->getColumnDimension('AC')->setWidth(6);

                $cellRange = 'A'.$row.':AC'.$row;
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
                            $des->comprobantes = collect($des->comprobantes)->sortBy('despacho_venta_grefecemision')->values();
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
                                    ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
                                    ->where('dv.despacho_venta_cfnumser','=',$comproba->despacho_venta_cfnumser)
                                    ->where('dv.despacho_venta_cfnumdoc','=',$comproba->despacho_venta_cfnumdoc)
                                    ->where('dv.id_despacho','<>',$des->id_despacho)
                                    ->where('d.id_programacion','=',$resPro->id_programacion)
                                    ->where('d.id_tipo_servicios','=',2)
                                    ->first();

                                if ($validarMixto){
                                    $typeComprop = 3;
                                }else {
                                    $typeComprop = $des->id_tipo_servicios;
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
                                $sheet1->setCellValue('L'.$row, date('d/m/Y',strtotime($resPro->programacion_fecha)));

                                $loc = match ($typeComprop) {
                                    1 => 'LOCAL',
                                    2 => 'PROVINCIAL',
                                    3 => 'MIXTO',
                                    default => '',
                                };

                                $sheet1->setCellValue('M'.$row, $loc);
                                $cellRange = 'M'.$row.':M'.$row;
                                $rowStyle = $sheet1->getStyle($cellRange);
                                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                                $sheet1->setCellValue('N'.$row, $this->general->formatoDecimal($comproba->despacho_venta_total_kg));
                                if ($indexComprobante == 0 || $indexComprobante == 1){
                                    /* LOCAL */
                                    if ($des->id_tipo_servicios == 1){
                                        if ($indexComprobante == 0) {
                                            $informacionliquidacion = DB::table('despachos')
                                                ->where('id_despacho', '=', $des->id_despacho)
                                                ->orderBy('id_despacho', 'desc')->first();

                                            $sheet1->setCellValue('O'.$row, $des->despacho_numero_correlativo);

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
                                                $sheet1->setCellValue('P'.$rowO_W, $this->general->formatoDecimal($costoTarifa));
                                                $sheet1->mergeCells('P'.$rowO_W.':Q'.$rowO_W);
                                                $sheet1->setCellValue('R'.$rowO_W, $this->general->formatoDecimal($costoOtros));
                                                $sheet1->setCellValue('S'.$rowO_W, $this->general->formatoDecimal($costoMano));
                                                $sheet1->setCellValue('T'.$rowO_W, $des->transportista_nom_comercial);
                                                $sheet1->setCellValue('U'.$rowO_W, $fac_pro);
                                                $sheet1->setCellValue('V'.$rowO_W, $this->general->formatoDecimal($totalGeneralLocal));
                                                $sheet1->setCellValue('W'.$rowO_W, '');

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
                                                $sheet1->setCellValue('O'.$rowO_W, '');
                                                $sheet1->setCellValue('P'.$rowO_W, "");
                                                $sheet1->mergeCells('P'.$rowO_W.':Q'.$rowO_W);
                                                $sheet1->setCellValue('R'.$rowO_W, "");
                                                $sheet1->setCellValue('S'.$rowO_W, "");
                                                $sheet1->setCellValue('T'.$rowO_W, $vehiT);
                                                $sheet1->setCellValue('U'.$rowO_W, "");
                                                $sheet1->setCellValue('V'.$rowO_W, "");
                                                $sheet1->setCellValue('W'.$rowO_W, "");

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
                                            $sheet1->setCellValue('X'.$row, $des->despacho_numero_correlativo);
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
                                                if ($informacionliquidacion->id_distrito){
                                                    $disti = DB::table('distritos')->where('id_distrito','=',$informacionliquidacion->id_distrito)->first();
                                                    $destino.= "-".$disti->distrito_nombre;
                                                }

//                                                $sheet1->setCellValue('X'.$row, '');
                                                $sheet1->setCellValue('Y'.$row, $des->transportista_nom_comercial);
                                                $sheet1->setCellValue('Z'.$row, $destino);
                                                $sheet1->setCellValue('AA'.$row, $informacionliquidacion->despacho_numero_correlativo);
                                                $sheet1->setCellValue('AB'.$row, $this->general->formatoDecimal($totalGeneralLocalProvin));
                                                $sheet1->setCellValue('AC'.$row, '');

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

                                            $sheet1->setCellValue('X'.$row, $osMixtoProgramacion->despacho_numero_correlativo);
                                            $sheet1->setCellValue('Y'.$row, $osMixtoProgramacion->transportista_nom_comercial);
                                            $sheet1->setCellValue('Z'.$row, $destino);
                                            $sheet1->setCellValue('AA'.$row, $InformacionDespachoMixto->despacho_numero_correlativo);
                                            $sheet1->setCellValue('AB'.$row, $this->general->formatoDecimal($totalGeneralMixto));
                                            $poMixto = $totalImporteComprobanteDespachoPro != 0 ?  ($totalGeneralMixto / $totalImporteComprobanteDespachoPro) * 100 : 0;
                                            $sheet1->setCellValue('AC'.$row, $this->general->formatoDecimal($poMixto));
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

                                $cellRange = 'A'.$row.':AC'.$row;
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
                                $sheet1->mergeCells('P'.$row.':Q'.$row);

                                $row++;
//                                if ($comproba->despacho_detalle_estado_entrega == 2){
                                $importeTotalDespachos+=$comproba->despacho_venta_cfimporte;
                                $totalPesoDespachos+=$comproba->despacho_venta_total_kg;
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
                            if ($filaPorcentajeLocal) {
                                if ($importeTotalDespachos != 0) {
                                    $porcentaje = (($fleteFinalLocal / $importeTotalDespachos) * 100);
                                    $porcentaje = $this->general->formatoDecimal($porcentaje);
                                    $sheet1->setCellValue('W'.$filaPorcentajeLocal, $this->general->formatoDecimal($porcentaje));
                                } else {
                                    $sheet1->setCellValue('W'.$filaPorcentajeLocal, '0%');
                                }
                            }

                            if ($filaPorcentajeProvin) {
                                if ($importeTotalDespachos != 0) {
                                    $porcentaje = (($fleteFinalProvin / $importeTotalDespachos) * 100);
                                    $porcentaje = $this->general->formatoDecimal($porcentaje);
                                    $sheet1->setCellValue('AC'.$filaPorcentajeProvin, $this->general->formatoDecimal($porcentaje));
                                } else {
                                    $sheet1->setCellValue('AC'.$filaPorcentajeProvin, '0%');
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
                        $existeComprobanteCamino = DB::table('despacho_ventas')->where('id_despacho', $select)->get();
                        foreach ($existeComprobanteCamino as $e){
                            // Verificar si el estado no es 2, 3 ni 4
                            if (!in_array($e->despacho_detalle_estado_entrega, [2, 3, 4])) {
                                DB::table('despacho_ventas')->where('id_despacho_venta', $e->id_despacho_venta)->update(['despacho_detalle_estado_entrega' => 1]);
                            }
                        }

                        // Obtener los comprobantes actualizados para crear el historial
                        $comprobantes = DB::table('despacho_ventas')
                            ->where('id_despacho', $select)
                            ->get();
                        // Crear registro en historial para cada comprobante
                        foreach ($comprobantes as $comprobante) {
                            $historialDespacho = new Historialdespachoventa();
                            $historialDespacho->id_despacho = $select;
                            $historialDespacho->id_despacho_venta = $comprobante->id_despacho_venta;
                            $historialDespacho->despacho_venta_cfnumdoc = $comprobante->despacho_venta_cfnumdoc;
                            $historialDespacho->despacho_estado_aprobacion = 2;
                            $historialDespacho->despacho_detalle_estado_entrega = 1;
                            $historialDespacho->his_desp_vent_fecha = Carbon::now('America/Lima');

                            if (!$historialDespacho->save()) {
                                DB::rollBack();
                                session()->flash('error_delete', 'Error al guardar el historial del despacho.');
                                return;
                            }
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
                    $existeComprobanteCamino = DB::table('despacho_ventas')->where('id_despacho', $this->id_despacho)->get();
                    foreach ($existeComprobanteCamino as $e){
                        // Verificar si el estado no es 2, 3 ni 4
                        if (!in_array($e->despacho_detalle_estado_entrega, [2, 3, 4])) {
                            DB::table('despacho_ventas')->where('id_despacho_venta', $e->id_despacho_venta)->update(['despacho_detalle_estado_entrega' => 1]);
                        }
                    }
                    // Obtener los comprobantes actualizados para crear el historial
                    $comprobantes = DB::table('despacho_ventas')
                        ->where('id_despacho', $this->id_despacho)
                        ->get();

                    // Crear registro en historial para cada comprobante
                    foreach ($comprobantes as $comprobante) {
                        $historialDespacho = new Historialdespachoventa();
                        $historialDespacho->id_despacho = $this->id_despacho;
                        $historialDespacho->id_despacho_venta = $comprobante->id_despacho_venta;
                        $historialDespacho->despacho_venta_cfnumdoc = $comprobante->despacho_venta_cfnumdoc;
                        $historialDespacho->despacho_estado_aprobacion = 2;
                        $historialDespacho->his_desp_vent_fecha = Carbon::now('America/Lima');

                        if (!$historialDespacho->save()) {
                            DB::rollBack();
                            session()->flash('error_delete', 'Error al guardar el historial del despacho.');
                            return;
                        }
                    }
                    DB::commit();
                    $this->dispatch('hideModalDelete');
                    session()->flash('success', 'Despacho en camino.');
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
                $informacionDespachoVenta = DB::table('despacho_ventas as dv')
                    ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
                    ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
                    ->where('d.id_tipo_servicios','=',1)
                    ->where('dv.id_despacho_venta','=',$id_comprobante)
                    ->first();

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
                $es = (int)$estado;
                $comprobante->despacho_detalle_estado_entrega = $es;
                if ($comprobante->save()){

                    // **Nuevo código: Registrar en el historial**
                    $historial = new Historialdespachoventa();
                    $historial->id_despacho = $comprobante->id_despacho; // ID del despacho
                    $historial->id_despacho_venta = $comprobante->id_despacho_venta; // ID del comprobante
                    $historial->despacho_venta_cfnumdoc = $comprobante->despacho_venta_cfnumdoc; // Número de documento
                    $historial->despacho_detalle_estado_entrega = $es; // Nuevo estado
                    $historial->despacho_estado_aprobacion = 3; // Estado de aprobación (puedes cambiarlo si es necesario)
                    $historial->his_desp_vent_fecha = Carbon::now('America/Lima'); // Fecha actual
                    $historial->save(); // Guardar en el historial

                    if ($es == 3 && $informacionDespachoVenta){
                        $comprobanteProvincialProgramacion = DB::table('despacho_ventas as dv')
                            ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
                            ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
                            ->where('p.id_programacion','=',$informacionDespachoVenta->id_programacion)
                            ->where('d.id_tipo_servicios','=',2)
                            ->where('dv.despacho_venta_guia','=',$informacionDespachoVenta->despacho_venta_guia)
                            ->where('dv.despacho_venta_cfnumser','=',$informacionDespachoVenta->despacho_venta_cfnumser)
                            ->where('dv.despacho_venta_cfnumdoc','=',$informacionDespachoVenta->despacho_venta_cfnumdoc)
                            ->first();

                        if ($comprobanteProvincialProgramacion){
                            $comprobanteProvi = DespachoVenta::find($comprobanteProvincialProgramacion->id_despacho_venta);
                            if (!$comprobanteProvi) {
                                DB::rollBack();
                                session()->flash('errorComprobante', 'Comprobante no encontrado.');
                                return;
                            }
                            $ress = DB::table('despacho_ventas')->where('id_despacho_venta','=',$comprobanteProvincialProgramacion->id_despacho_venta)
                                ->update(['despacho_detalle_estado_entrega'=>3]);
                            if ($ress == 1){
                                // si el provincial no hay otros comprobantes poner como culminado.
                                $conteDe = DB::table('despacho_ventas')->where('id_despacho','=',$comprobanteProvincialProgramacion->id_despacho)->count();
                                $conteDeEstadoEntrega = DB::table('despacho_ventas')->where('id_despacho','=',$comprobanteProvincialProgramacion->id_despacho)
                                    ->where('despacho_detalle_estado_entrega','=',3)->count();
                                // si todos los despachos detalles ($conteDeEstadoEntrega) esta como no entregados cambiar el despacho como culminado
                                if ($conteDe == $conteDeEstadoEntrega){
                                    DB::table('despachos')->where('id_despacho','=',$comprobanteProvincialProgramacion->id_despacho)->update(['despacho_estado_aprobacion'=>3]);
                                }
                            }else{
                                DB::rollBack();
                                session()->flash('errorComprobante', 'Ocurrió un error al cambiar el estado.');
                                return;
                            }
                        }
                    }
                }else{
                    DB::rollBack();
                    session()->flash('errorComprobante', 'Ocurrió un error al cambiar el estado.');
                    return;
                }
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
