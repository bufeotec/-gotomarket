<?php

namespace App\Livewire\Registroflete;

use App\Models\General;
use App\Models\Logs;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Transportista;
use App\Models\Tarifario;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Fletes extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $transportistas;
    private $tarifario;
    public function __construct(){
        $this->logs = new Logs();
        $this->transportistas = new Transportista();
        $this->tarifario = new Tarifario();
    }
    public $search_transportistas;
    public $pagination_transportistas = 10;

    public function render(){
        $transportistas = $this->transportistas->listar_transportistas($this->search_transportistas,$this->pagination_transportistas);
        return view('livewire.registroflete.fletes', compact('transportistas'));
    }

    public function generar_excel_comparativo_provincia(){
        try {
            if (!Gate::allows('generar_excel_comparativo_provincia')) {
                session()->flash('error', 'No tiene permisos para descargar.');
                return;
            }

            $resultado_cp = $this->tarifario->obtener_comparativo_provincia();

            // Verificar si hay datos
            if (empty($resultado_cp) || count($resultado_cp) == 0) {
                session()->flash('error', 'No se encontraron datos para generar el reporte.');
                return;
            }

            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Comparativo Tarifas Provincial');

            $row = 1;

            // Título principal 'COMPARATIVO TARIFA PROVINCIA' desde la columna A hasta G
            $sheet1->mergeCells('A'.$row.':G'.$row);
            $sheet1->setCellValue('A'.$row, 'COMPARATIVO TARIFA PROVINCIA');

            // Estilo para el título
            $sheet1->getStyle('A'.$row)->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
            $row++;

            // Encabezados de las columnas
            $sheet1->setCellValue('A'.$row, 'N°');
            $sheet1->setCellValue('B'.$row, 'DEPARTAMENTO');
            $sheet1->setCellValue('C'.$row, 'PROVINCIA');
            $sheet1->setCellValue('D'.$row, 'TIPO DE SERVICIO');
            $sheet1->setCellValue('E'.$row, 'UNIDAD DE MEDIDA');
            $sheet1->setCellValue('F'.$row, 'TARIFA MINIMA');
            $sheet1->setCellValue('G'.$row, 'PROVEEDOR');

            // Estilo para encabezados
            $headerRange = 'A'.$row.':G'.$row;
            $sheet1->getStyle($headerRange)->getFont()->setBold(true);
            $sheet1->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE6E6E6');
            $sheet1->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Configurar ancho de columnas
            $sheet1->getColumnDimension('A')->setWidth(5);
            $sheet1->getColumnDimension('B')->setWidth(20);
            $sheet1->getColumnDimension('C')->setWidth(20);
            $sheet1->getColumnDimension('D')->setWidth(18);
            $sheet1->getColumnDimension('E')->setWidth(18);
            $sheet1->getColumnDimension('F')->setWidth(15);
            $sheet1->getColumnDimension('G')->setWidth(50);

            $row++; // Siguiente fila para datos

            // Llenar datos
            $contador = 1;
            foreach ($resultado_cp as $item) {
                $sheet1->setCellValue('A'.$row, $contador);
                $sheet1->setCellValue('B'.$row, $item->departamento_nombre ?? '');
                $sheet1->setCellValue('C'.$row, $item->provincia_nombre ?? '');
                $sheet1->setCellValue('D'.$row, $item->tipo_servicio_concepto ?? '');
                $sheet1->setCellValue('E'.$row, $item->medida_nombre ?? '');

                $medida_x = "";
                if ($item->id_medida == 9){
                    $medida_x = "cm³";
                } elseif ($item->id_medida == 23){
                    $medida_x = "kg";
                } else {
                    $medida_x = "";
                }
                $sheet1->setCellValue('F'.$row, 'S/ ' . number_format($item->tarifa_monto ?? 0, 2) . ' / ' . $medida_x);
                $sheet1->setCellValue('G'.$row, $item->transportista_razon_social ?? '');

                // Centrar el número y la tarifa
                $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet1->getStyle('F'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $contador++;
                $row++;
            }

            // Aplicar bordes a toda la tabla
            $lastRow = $row - 1;
            $dataRange = 'A3:G'.$lastRow;
            $sheet1->getStyle($dataRange)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Nombre del archivo
            $fecha_actual = date('Y-m-d_H-i-s');
            $nombre_excel = 'Comparativo_Tarifas_Provincia_'.$fecha_actual.'.xlsx';

            return response()->stream(
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

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
        }
    }

    public function generar_excel_comparativo_local(){
        try {
            if (!Gate::allows('generar_excel_comparativo_local')) {
                session()->flash('error', 'No tiene permisos para descargar.');
                return;
            }

            $resultado_cl = $this->tarifario->obtener_comparativo_local();

            // Verificar si hay datos
            if (empty($resultado_cl) || count($resultado_cl) == 0) {
                session()->flash('error', 'No se encontraron datos para generar el reporte.');
                return;
            }

            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Comparativo Tarifas Locales');

            $row = 1;

            // Título principal 'COMPARATIVO TARIFA LOCAL' desde la columna A hasta F
            $sheet1->mergeCells('A'.$row.':F'.$row);
            $sheet1->setCellValue('A'.$row, 'COMPARATIVO TARIFA LOCAL');

            // Estilo para el título
            $sheet1->getStyle('A'.$row)->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
            $row++;

            // Encabezados de las columnas
            $sheet1->setCellValue('A'.$row, 'N°');
            $sheet1->setCellValue('B'.$row, 'CAPACIDAD MAXIMA');
            $sheet1->setCellValue('C'.$row, 'TARIFA MINIMA');
            $sheet1->setCellValue('D'.$row, 'TIPO DE SERVICIO');
            $sheet1->setCellValue('E'.$row, 'TIPO DE VEHÍCULO');
            $sheet1->setCellValue('F'.$row, 'PROVEEDOR');

            // Estilo para encabezados
            $headerRange = 'A'.$row.':F'.$row;
            $sheet1->getStyle($headerRange)->getFont()->setBold(true);
            $sheet1->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE6E6E6');
            $sheet1->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Configurar ancho de columnas
            $sheet1->getColumnDimension('A')->setWidth(5);
            $sheet1->getColumnDimension('B')->setWidth(20);
            $sheet1->getColumnDimension('C')->setWidth(25);
            $sheet1->getColumnDimension('D')->setWidth(20);
            $sheet1->getColumnDimension('E')->setWidth(25);
            $sheet1->getColumnDimension('F')->setWidth(50);

            $row++; // Siguiente fila para datos

            // Llenar datos
            $contador = 1;
            foreach ($resultado_cl as $item) {
                $sheet1->setCellValue('A'.$row, $contador);

                // Formatear capacidad máxima (agregar unidad si es necesario)
                $capacidad = $item->tarifa_cap_max ?? 0;
                $sheet1->setCellValue('B'.$row, number_format($capacidad, 2) . ' Kg');

                $sheet1->setCellValue('C'.$row, 'S/. ' . number_format($item->tarifa_monto ?? 0, 2) . ' / VIAJE');
                $sheet1->setCellValue('D'.$row, $item->tipo_servicio_concepto ?? '');
                $sheet1->setCellValue('E'.$row, $item->tipo_vehiculo_concepto ?? '');
                $sheet1->setCellValue('F'.$row, $item->transportista_razon_social ?? '');

                // Centrar el número y alinear valores monetarios a la derecha
                $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet1->getStyle('B'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet1->getStyle('C'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $contador++;
                $row++;
            }

            // Aplicar bordes a toda la tabla
            $lastRow = $row - 1;
            $dataRange = 'A3:F'.$lastRow;
            $sheet1->getStyle($dataRange)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Nombre del archivo
            $fecha_actual = date('Y-m-d_H-i-s');
            $nombre_excel = 'Comparativo_Tarifas_Local_'.$fecha_actual.'.xlsx';

            return response()->stream(
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

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
        }
    }

    public function generar_excel_tiempo_transporte(){
        try {
            if (!Gate::allows('generar_excel_tiempo_transporte')) {
                session()->flash('error', 'No tiene permisos para descargar.');
                return;
            }

            $resultado_tt = $this->tarifario->obtener_tiempo_trasnsporte();

            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Tiempo de Transporte');

            $row = 1;

            // Título principal 'TIEMPO DE TRANSPORTE' desde la columna A hasta G
            $sheet1->mergeCells('A'.$row.':G'.$row);
            $sheet1->setCellValue('A'.$row, 'TIEMPO DE TRANSPORTE');

            // Estilo para el título
            $sheet1->getStyle('A'.$row)->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
            $row++;

            // Encabezados de las columnas
            $sheet1->setCellValue('A'.$row, 'N°');
            $sheet1->setCellValue('B'.$row, 'DEPARTAMENTO');
            $sheet1->setCellValue('C'.$row, 'PROVINCIA');
            $sheet1->setCellValue('D'.$row, 'TIEMPO DE TRANSPORTE MÍNIMO');
            $sheet1->setCellValue('E'.$row, 'TIEMPO DE TRANSPORTE MÁXIMO');
            $sheet1->setCellValue('F'.$row, 'PROVEEDOR DE TIEMPO MÍNIMO');
            $sheet1->setCellValue('G'.$row, 'PROVEEDOR DE TIEMPO MÁXIMO');

            // Estilo para encabezados
            $headerRange = 'A'.$row.':G'.$row;
            $sheet1->getStyle($headerRange)->getFont()->setBold(true);
            $sheet1->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE6E6E6');
            $sheet1->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Configurar ancho de columnas
            $sheet1->getColumnDimension('A')->setWidth(5);
            $sheet1->getColumnDimension('B')->setWidth(20);
            $sheet1->getColumnDimension('C')->setWidth(20);
            $sheet1->getColumnDimension('D')->setWidth(25);
            $sheet1->getColumnDimension('E')->setWidth(25);
            $sheet1->getColumnDimension('F')->setWidth(50);
            $sheet1->getColumnDimension('G')->setWidth(50);

            $row++;

            // Llenar datos
            $contador = 1;
            foreach ($resultado_tt as $datos) {
                $sheet1->setCellValue('A'.$row, $contador);
                $sheet1->setCellValue('B'.$row, $datos->departamento_nombre);
                $sheet1->setCellValue('C'.$row, $datos->provincia_nombre);
                $sheet1->setCellValue('D'.$row, $datos->tiempo_minimo);
                $sheet1->setCellValue('E'.$row, $datos->tiempo_maximo);
                $sheet1->setCellValue('F'.$row, $datos->proveedor_minimo);
                $sheet1->setCellValue('G'.$row, $datos->proveedor_maximo);

                // Centrar el número y los tiempos
                $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet1->getStyle('D'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet1->getStyle('E'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $row++;
                $contador++;
            }

            // Aplicar bordes a toda la tabla
            $dataRange = 'A3:G'.($row-1);
            $sheet1->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Nombre del archivo
            $fecha_actual = date('Y-m-d_H-i-s');
            $nombre_excel = 'Tiempo_Transporte_'.$fecha_actual.'.xlsx';

            return response()->stream(
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

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
        }
    }

}
