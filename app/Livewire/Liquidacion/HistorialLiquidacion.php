<?php

namespace App\Livewire\Liquidacion;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;
use App\Models\Liquidacion;
use App\Models\General;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
    public $search;
    public $estado_liquidacion = '';
    public $listar_detalle_liquidacion = [];
    public $guiasAsociadasDespachos = [];
    public $listar_detalle_despacho = [];
    public $id_liquidacion = '';
    public $liquidacion_ruta_comprobante = '';
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
    }
    public function render(){
        $resultado = $this->liquidacion->listar_liquidacion_aprobadas_new($this->search,$this->desde, $this->hasta);
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
        if (!Gate::allows('guardar_comprobante_liquidacion')) {
            session()->flash('error', 'No tiene permisos para guardar el comprobante relacionado con la liquidación.');
            return;
        }

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
//    public function listar_informacion_despacho($id){
//        try {
//            $this->listar_detalle_liquidacion = DB::table('liquidaciones')->where('id_liquidacion','=',$id)->first();
//            if ($this->listar_detalle_liquidacion){
//                $this->listar_detalle_liquidacion->detalles = DB::table('liquidacion_detalles as ld')
//                ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
//                ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
//                ->join('users as u','u.id_users','=','d.id_users')
//                ->where('ld.id_liquidacion','=',$id)->get();
//
//                foreach ($this->listar_detalle_liquidacion->detalles as $de){
//                    $de->comprobantes = DB::table('despacho_ventas')->where('id_despacho','=',$de->id_despacho)->get();
//
//                    $totalVenta = 0;
//                    $totalVentaRestar = 0;
//                    $totalPesoRestar = 0;
//                    foreach ($de->comprobantes as $com) {
//                        $precio = floatval($com->despacho_venta_cfimporte);
//                        $pesoMenos = $com->despacho_venta_total_kg;
//                        $totalVenta += $precio;
//                        if ($com->despacho_detalle_estado_entrega == 3){
//                            $totalVentaRestar += $precio;
//                            $totalPesoRestar += $pesoMenos;
//                        }
//                    }
//                    $de->totalVentaDespacho = $totalVenta;
//                    $de->totalVentaNoEntregado = $totalVentaRestar;
//                    $de->totalPesoNoEntregado = $totalPesoRestar;
//                }
//            }
//        }catch (\Exception $e){
//            $this->logs->insertarLog($e);
//        }
//    }
//    public function listar_informacion_despacho($id){
//        try {
//            $this->listar_detalle_despacho = DB::table('liquidacion_detalles as ld')
//                ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
//                ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
//                ->join('users as u','u.id_users','=','d.id_users')
//                ->where('d.id_despacho','=',$id)->first();
//            if ($this->listar_detalle_despacho){
//                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas')
//                    ->where('id_despacho','=',$id)->get();
//
//                $totalVenta = 0;
//                $totalVentaRestar = 0;
//                $totalPesoRestar = 0;
//                foreach ($this->listar_detalle_despacho->comprobantes as $com) {
//                    $precio = floatval($com->despacho_venta_cfimporte);
//                    $pesoMenos = $com->despacho_venta_total_kg;
//                    $totalVenta += $precio;
//                    if ($com->despacho_detalle_estado_entrega == 3){
//                        $totalVentaRestar += $precio;
//                        $totalPesoRestar += $pesoMenos;
//                    }
//                }
//                $this->listar_detalle_despacho->totalVentaDespacho = $totalVenta;
//                $this->listar_detalle_despacho->totalVentaNoEntregado = $totalVentaRestar;
//                $this->listar_detalle_despacho->totalPesoNoEntregado = $totalPesoRestar;
//
//            }
//        }catch (\Exception $e){
//            $this->logs->insertarLog($e);
//        }
//    }
    public function listar_informacion_despacho($id,$liquidacion){
        try {
            $this->listar_detalle_despacho = DB::table('liquidacion_detalles as ld')
                ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
                ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
                ->join('users as u','u.id_users','=','d.id_users')
                ->where('d.id_despacho','=',$id)
                ->where('ld.id_liquidacion','=',$liquidacion)
                ->first();
            if ($this->listar_detalle_despacho){
                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas')
                    ->where('id_despacho','=',$id)->get();

                $totalVenta = 0;
                $totalVentaRestar = 0;
                $totalPesoRestar = 0;
                foreach ($this->listar_detalle_despacho->comprobantes as $com) {
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $pesoMenos = $com->despacho_venta_total_kg;
                    $totalVenta += $precio;
                    if ($com->despacho_detalle_estado_entrega == 3){
                        $totalVentaRestar += $precio;
                        $totalPesoRestar += $pesoMenos;
                    }
                }
                $this->listar_detalle_despacho->totalVentaDespacho = $totalVenta;
                $this->listar_detalle_despacho->totalVentaNoEntregado = $totalVentaRestar;
                $this->listar_detalle_despacho->totalPesoNoEntregado = $totalPesoRestar;

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
    public function generar_excel_historial_liquidacion(){
        try {
            if (!Gate::allows('generar_excel_historial_liquidacion')) {
                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
                return;
            }
            $resultado = $this->liquidacion->listar_liquidacion_aprobadas_excel($this->search,$this->desde, $this->hasta);

            $spreadsheet = new Spreadsheet();
            $sheet1  = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('historial_liquidacion');


            $mensaje = "RESULTADO DE BÚSQUEDA: ";
            if ($this->search) {
                $mensaje .= "Criterio: \"" . $this->search . "\"";
            }
            if (isset($this->desde, $this->hasta)) {
                $mensaje .= " | Rango de fechas: " . date("d-m-Y", strtotime($this->desde)) . " al " . date("d-m-Y", strtotime($this->hasta));
            }

            // Configurar título
            $sheet1->setCellValue('A1', 'HISTORIAL DE APROBACIÓN DE LIQUIDACIONES');
            $titleStyle = $sheet1->getStyle('A1');
            $titleStyle->getFont()->setSize(16);
            $titleStyle->getFont()->setBold(true);
            $sheet1->mergeCells('A1:F1');
            $sheet1->setCellValue('A2', $mensaje);
            $titleStyle = $sheet1->getStyle('A2');
            $titleStyle->getFont()->setSize(12);
            $titleStyle->getFont()->setBold(true);
            $sheet1->mergeCells('A2:F2');
            $sheet1->setCellValue('A3', "");
            $sheet1->mergeCells('A3:F3');

            // Encabezados
            $row = 4;
            $sheet1->setCellValue('A'.$row, 'TRANSPORTE');
            $sheet1->setCellValue('B'.$row, 'SERVICIO');
            $sheet1->setCellValue('C'.$row, 'FACT');
            $sheet1->setCellValue('D'.$row, 'SIN IGV');
            $sheet1->setCellValue('E'.$row, 'CON IGV');
            $sheet1->setCellValue('F'.$row, 'TOTAL PROV');
            $sheet1->getColumnDimension('A')->setWidth(12); // Ancho de la columna A
            $sheet1->getColumnDimension('B')->setWidth(12); // Ancho de la columna B
            $sheet1->getColumnDimension('C')->setWidth(12); // Ancho de la columna C
            $sheet1->getColumnDimension('D')->setWidth(12); // Ancho de la columna D
            $sheet1->getColumnDimension('E')->setWidth(12); // Ancho de la columna E
            $sheet1->getColumnDimension('F')->setWidth(12); // Ancho de la columna F

            $cellRange = 'A'.$row.':F'.$row;
            $rowStyle = $sheet1->getStyle($cellRange);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getBorders()->getAllBorders()->getColor()->setARGB('000000');
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            // Alineación del encabezado
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $row++;

            /*---------------------------*/
            $totalGeneralCommprobantes = 0;

            $primeraFilaIngresadaTra = null;
            $ultimoTransportista = null;
            $sumaPorTransportista = 0;

            foreach ($resultado as $index => $re) {
                // Contenido sin formato decimal requerido
                $totalSinIGVExcel = $re->total_sin_igv;
                $totalConIVCExcel = $totalSinIGVExcel * 1.18;

                // Formatear valores para visualización
                $totalSinIGVExcelFormatted = $this->general->formatoDecimal($totalSinIGVExcel);
                $totalConIVCExcelFormatted = $this->general->formatoDecimal($totalConIVCExcel);

                // Manejo de filas combinadas por transportista
                if ($ultimoTransportista !== null && $ultimoTransportista !== $re->id_transportistas) {
                    // Si cambió el transportista, escribir el total acumulado para el anterior
                    $Filahasta = $row - 1; // Última fila del transportista anterior
                    $sheet1->mergeCells('F'.$primeraFilaIngresadaTra.':F'.$Filahasta);
                    $sheet1->setCellValue('F'.$primeraFilaIngresadaTra, $this->general->formatoDecimal($sumaPorTransportista)); // Total acumulado
                    $sumaPorTransportista = 0; // Reiniciar la suma para el nuevo transportista
                    $primeraFilaIngresadaTra = $row; // Nueva fila inicial
                }

                // Acumular el total para el transportista actual
                $sumaPorTransportista += $totalConIVCExcel;

                // Escribir los datos en la fila actual
                $sheet1->setCellValue('A'.$row, $re->transportista_nom_comercial);

                $sheet1->setCellValue('B'.$row, $re->servicios);

                $sheet1->setCellValue('C'.$row, $re->liquidacion_serie.'-'.$re->liquidacion_correlativo);

                $sheet1->setCellValue('D'.$row, $totalSinIGVExcelFormatted);

                $sheet1->setCellValue('E'.$row, $totalConIVCExcelFormatted);


                // Estilo general
                $cellRange = 'A'.$row.':F'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                // Alineación del encabezado
                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                // Actualizar el transportista actual
                if ($ultimoTransportista === null || $ultimoTransportista !== $re->id_transportistas) {
                    $ultimoTransportista = $re->id_transportistas;
                    $primeraFilaIngresadaTra = $row; // Primera fila del nuevo transportista
                }
                // Si es el último registro, cerrar el rango
                if ($index == count($resultado) - 1) {
                    $Filahasta = $row; // Última fila
                    $sheet1->mergeCells('F'.$primeraFilaIngresadaTra.':F'.$Filahasta);
                    $sheet1->setCellValue('F'.$primeraFilaIngresadaTra, $this->general->formatoDecimal($sumaPorTransportista)); // Total acumulado
                }

                $sheet1->getColumnDimension('A')->setWidth(12); // Ancho de la columna A
                $sheet1->getColumnDimension('B')->setWidth(12); // Ancho de la columna B
                $sheet1->getColumnDimension('C')->setWidth(12); // Ancho de la columna C
                $sheet1->getColumnDimension('D')->setWidth(12); // Ancho de la columna D
                $sheet1->getColumnDimension('E')->setWidth(12); // Ancho de la columna E
                $sheet1->getColumnDimension('F')->setWidth(12); // Ancho de la columna F

                $row++;
                $totalGeneralCommprobantes += $re->total_sin_igv;
            }

            // Contenido Final
            $sheet1->setCellValue('A'.$row, 'TOTAL');
            $sheet1->mergeCells('A'.$row.':C'.$row);
            $toSinIGV = $this->general->formatoDecimal($totalGeneralCommprobantes);
            $sheet1->setCellValue('D'.$row, $toSinIGV);
            $toConIGV = $this->general->formatoDecimal($totalGeneralCommprobantes * 1.18);
            $sheet1->setCellValue('E'.$row, $toConIGV);
            $sheet1->setCellValue('F'.$row, $toConIGV);


            $cellRange = 'A'.$row.':F'.$row;
            $rowStyle = $sheet1->getStyle($cellRange);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ececec'); // Fondo
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $nombre_excel = "historial_de_liquidación" . date('d-m-Y') . '.xlsx';
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
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
        }
    }
}
