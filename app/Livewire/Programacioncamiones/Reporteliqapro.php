<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Guia;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Log;

class Reporteliqapro extends Component
{
    use WithPagination, WithoutUrlPagination;

    private $logs;
    private $guia;

    public function __construct(){
        $this->logs = new Logs();
        $this->guia = new Guia();
    }

    public $desde;
    public $hasta;
    public $tipo_reporte = ''; // 'despacho' o 'programacion'
    public $filteredData = [];
    public $localData = [];
    public $provincialData = [];
    public $searchdatos = false;

    public function render(){
        return view('livewire.programacioncamiones.reporteliqapro');
    }

    public function buscar_datos() {
        $this->searchdatos = true;

        // Validación de campos vacíos
        if (empty($this->desde) || empty($this->hasta) || empty($this->tipo_reporte)) {
            $this->localData = [];
            $this->provincialData = [];
            $this->filteredData = [];
            return;
        }

        $query = DB::table('guias as g')
            ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
            ->join('despachos as d', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('transportistas as t', 'd.id_transportistas', '=', 't.id_transportistas')
            ->join('programaciones as p', 'd.id_programacion', '=', 'p.id_programacion')
            ->leftJoin('departamentos as dep', 'd.id_departamento', '=', 'dep.id_departamento')
            ->leftJoin('provincias as prov', 'd.id_provincia', '=', 'prov.id_provincia')
            ->leftJoin('distritos as dis', 'd.id_distrito', '=', 'dis.id_distrito')
            ->select(
                'p.programacion_fecha as fec_programacion',
                'd.despacho_fecha_aprobacion as fec_despacho',
                'dis.distrito_nombre as distrito',
                'prov.provincia_nombre as provincia',
                'dep.departamento_nombre as departamento',
                't.transportista_nom_comercial as proveedor',
                'd.id_tipo_servicios as tiposervicio',
                't.id_transportistas',
                'g.guia_nro_doc_ref as fact',
                'g.guia_importe_total as sin_igv',
                DB::raw('g.guia_importe_total * 1.18 as con_igv')
            )
            ->where('d.despacho_estado_aprobacion', [1, 2, 3]);

        // Aplicar filtro según el tipo seleccionado
        if ($this->tipo_reporte == 'programacion') {
            $query->whereDate('p.programacion_fecha', '>=', $this->desde)
                ->whereDate('p.programacion_fecha', '<=', $this->hasta);
        } else { // 'despacho' por defecto
            $query->whereDate('d.despacho_fecha_aprobacion', '>=', $this->desde)
                ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->hasta);
        }

        $datos = $query->get();

        if ($datos->isEmpty()) {
            $this->localData = collect();
            $this->provincialData = collect();
            $this->filteredData = collect();
            return;
        }

        $totalesProveedor = $datos->groupBy('id_transportistas')->map(function($group) {
            return $group->sum('con_igv');
        });

        $datosConTotales = $datos->map(function($item) use ($totalesProveedor) {
            $item->total_proveedor = $totalesProveedor[$item->id_transportistas] ?? 0;
            return $item;
        });

        $this->localData = $datosConTotales->where('tiposervicio', 1);
        $this->provincialData = $datosConTotales->where('tiposervicio', 2);
        $this->filteredData = $datosConTotales;
    }
    public function exportarDespachosExcel()
    {
        try {
            if (!Gate::allows('exportar_despachos_excel')) {
                session()->flash('error', 'No tiene permisos para generar este reporte.');
                return;
            }

            // Obtener datos filtrados por fechas (igual que en buscar_datos())
            $query = DB::table('guias as g')
                ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
                ->join('despachos as d', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('transportistas as t', 'd.id_transportistas', '=', 't.id_transportistas')
                ->join('programaciones as p', 'd.id_programacion', '=', 'p.id_programacion')
                ->leftJoin('departamentos as dep', 'd.id_departamento', '=', 'dep.id_departamento')
                ->leftJoin('provincias as prov', 'd.id_provincia', '=', 'prov.id_provincia')
                ->leftJoin('distritos as dis', 'd.id_distrito', '=', 'dis.id_distrito')
                ->select(
                    'p.programacion_fecha as fec_despacho',
                    'd.despacho_fecha_aprobacion as fec_aprob',
                    'dis.distrito_nombre as distrito',
                    'prov.provincia_nombre as provincia',
                    'dep.departamento_nombre as departamento',
                    't.transportista_nom_comercial as proveedor',
                    'd.id_tipo_servicios as tiposervicio', // Campo para determinar si es local o provincial
                    't.id_transportistas',
                    'g.guia_nro_doc_ref as fact',
                    'g.guia_importe_total as sin_igv',
                    DB::raw('g.guia_importe_total * 1.18 as con_igv')
                )
                ->where('d.despacho_estado_aprobacion', [1, 2, 3]);
                if ($this->filterByProgramacion) {
                    if (!empty($this->desde)) {
                        $query->whereDate('p.programacion_fecha', '>=', $this->desde);
                    }
                    if (!empty($this->hasta)) {
                        $query->whereDate('p.programacion_fecha', '<=', $this->hasta);
                    }
                } else {
                    if (!empty($this->ddesde)) {
                        $query->whereDate('d.despacho_fecha_aprobacion', '>=', $this->ddesde);
                    }
                    if (!empty($this->dhasta)) {
                        $query->whereDate('d.despacho_fecha_aprobacion', '<=', $this->dhasta);
                    }
                }

            $filteredData = $query->get();

            if ($filteredData->isEmpty()) {
                session()->flash('error', 'No hay datos para exportar en el rango de fechas seleccionado.');
                return;
            }

            // Separar datos por tipo de servicio (1=Local, 2=Provincial)
            $localData = $filteredData->where('tiposervicio', 1);
            $provincialData = $filteredData->where('tiposervicio', 2);

            // Calcular totales
            $totalLocalConIgv = $localData->sum('con_igv');
            $totalLocalSinIgv = $localData->sum('sin_igv');
            $totalProvincialConIgv = $provincialData->sum('con_igv');
            $totalProvincialSinIgv = $provincialData->sum('sin_igv');

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Despachos');

            // ========== CABECERA PRINCIPAL ==========
            $sheet->setCellValue('A1', 'REPORTE DE FLETE: LIQUIDACIONES APROBADAS');
            $sheet->mergeCells('A1:Q1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('A8D08D');

            // ========== TABLA LOCAL (LIMA) ==========
            $sheet->setCellValue('A3', 'DESPACHO LOCAL');
            $sheet->mergeCells('A3:H3');
            $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('A8D08D');

            // Encabezados tabla local
            $sheet->setCellValue('A4', 'FEC. DESPACHO');
            $sheet->setCellValue('B4', 'FEC. APROB');
            $sheet->setCellValue('C4', 'LOCAL');
            $sheet->setCellValue('D4', 'PROVEEDOR');
            $sheet->setCellValue('E4', 'FACT');
            $sheet->setCellValue('F4', 'SIN IGV');
            $sheet->setCellValue('G4', 'CON IGV');
            $sheet->setCellValue('H4', 'TOTAL PROVEEDOR');
            $sheet->getStyle('A4:H4')->getFont()->setBold(true);
            $sheet->getStyle('A4:H4')->getAlignment()->setHorizontal('center');

            // Llenar datos locales
            $row = 5;
            if ($localData->isNotEmpty()) {
                $groupedDataLocal = $localData->groupBy('proveedor');

                foreach ($groupedDataLocal as $proveedor => $guias) {
                    $totalProveedorConIgv = $guias->sum('con_igv');
                    $totalProveedorSinIgv = $guias->sum('sin_igv');
                    $firstRow = true;

                    foreach ($guias as $guia) {
                        $sheet->setCellValue('A'.$row, date('d/m/Y', strtotime($guia->fec_despacho)));
                        $sheet->setCellValue('B'.$row, date('d/m/Y', strtotime($guia->fec_aprob)));
                        $sheet->setCellValue('C'.$row, $guia->departamento ?? 'S/N');
                        $sheet->setCellValue('D'.$row, $proveedor);
                        $sheet->setCellValue('E'.$row, $guia->fact ?? '---');

                        // Formato numérico idéntico al de la vista
                        $sheet->setCellValue('F'.$row, 'S/ ' . number_format($guia->sin_igv, 2, '.', ','));
                        $sheet->setCellValue('G'.$row, 'S/ ' . number_format($guia->con_igv, 2, '.', ','));

                        if ($firstRow) {
                            $sheet->setCellValue('H'.$row, 'S/ ' . number_format($totalProveedorConIgv, 2, '.', ','));
                            $firstRow = false;
                        }

                        // Centrar contenido
                        $sheet->getStyle('A'.$row.':H'.$row)->getAlignment()->setHorizontal('center');
                        $row++;
                    }
                }

                // Total General Local (igual que en la vista)
                $sheet->setCellValue('A'.$row, 'TOTAL GENERAL');
                $sheet->mergeCells('A'.$row.':E'.$row);
                $sheet->setCellValue('F'.$row, 'S/ ' . number_format($totalLocalSinIgv, 2, '.', ','));
                $sheet->setCellValue('G'.$row, 'S/ ' . number_format($totalLocalConIgv, 2, '.', ','));
                $sheet->setCellValue('H'.$row, 'S/ ' . number_format($totalLocalConIgv, 2, '.', ','));

                // Estilo para el total general
                $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'D9E1F2']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => '000000']
                        ]
                    ]
                ]);
                $sheet->getStyle('A'.$row.':H'.$row)->getAlignment()->setHorizontal('center');
                $row++;
            } else {
                $sheet->setCellValue('A5', 'No hay datos de despachos locales (Lima)');
                $sheet->mergeCells('A5:H5');
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                $row = 6;
            }

            // Aplicar bordes a tabla local
            $sheet->getStyle('A3:H'.($row-1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ]);

            // ========== TABLA PROVINCIAL ==========
            $sheet->setCellValue('J3', 'DESPACHO A PROVINCIA');
            $sheet->mergeCells('J3:Q3');
            $sheet->getStyle('J3')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('J3')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('J3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('A8D08D');

            // Encabezados tabla provincial
            $sheet->setCellValue('J4', 'FEC. DESPACHO');
            $sheet->setCellValue('K4', 'FEC. APROB');
            $sheet->setCellValue('L4', 'DEPARTAMENTO - PROVINCIA');
            $sheet->setCellValue('M4', 'PROVEEDOR');
            $sheet->setCellValue('N4', 'FACT');
            $sheet->setCellValue('O4', 'SIN IGV');
            $sheet->setCellValue('P4', 'CON IGV');
            $sheet->setCellValue('Q4', 'TOTAL PROVEEDOR');
            $sheet->getStyle('J4:Q4')->getFont()->setBold(true);
            $sheet->getStyle('J4:Q4')->getAlignment()->setHorizontal('center');

            // Llenar datos provinciales
            $rowProvincial = 5;
            if ($provincialData->isNotEmpty()) {
                $groupedDataProvincial = $provincialData->groupBy('proveedor');

                foreach ($groupedDataProvincial as $proveedor => $guias) {
                    $totalProveedorConIgv = $guias->sum('con_igv');
                    $totalProveedorSinIgv = $guias->sum('sin_igv');
                    $firstRow = true;

                    foreach ($guias as $guia) {
                        $sheet->setCellValue('J'.$rowProvincial, date('d/m/Y', strtotime($guia->fec_despacho)));
                        $sheet->setCellValue('K'.$rowProvincial, date('d/m/Y', strtotime($guia->fec_aprob)));
                        $sheet->setCellValue('L'.$rowProvincial, ($guia->departamento ?? 'S/N').' - '.($guia->provincia ?? 'S/N'));
                        $sheet->setCellValue('M'.$rowProvincial, $proveedor);
                        $sheet->setCellValue('N'.$rowProvincial, $guia->fact ?? '---');

                        // Formato numérico idéntico al de la vista
                        $sheet->setCellValue('O'.$rowProvincial, 'S/ ' . number_format($guia->sin_igv, 2, '.', ','));
                        $sheet->setCellValue('P'.$rowProvincial, 'S/ ' . number_format($guia->con_igv, 2, '.', ','));

                        if ($firstRow) {
                            $sheet->setCellValue('Q'.$rowProvincial, 'S/ ' . number_format($totalProveedorConIgv, 2, '.', ','));
                            $firstRow = false;
                        }

                        // Centrar solo las columnas requeridas
//                        $sheet->getStyle('N'.$rowProvincial)->getAlignment()->setHorizontal('center');
                        $sheet->getStyle('J'.$rowProvincial.':Q'.$rowProvincial)->getAlignment()->setHorizontal('center');

                        $rowProvincial++;
                    }
                }

                // Total General Provincial (igual que en la vista)
                $sheet->setCellValue('J'.$rowProvincial, 'TOTAL GENERAL');
                $sheet->mergeCells('J'.$rowProvincial.':N'.$rowProvincial);
                $sheet->setCellValue('O'.$rowProvincial, 'S/ ' . number_format($totalProvincialSinIgv, 2, '.', ','));
                $sheet->setCellValue('P'.$rowProvincial, 'S/ ' . number_format($totalProvincialConIgv, 2, '.', ','));
                $sheet->setCellValue('Q'.$rowProvincial, 'S/ ' . number_format($totalProvincialConIgv, 2, '.', ','));

                // Estilo para el total general
                $sheet->getStyle('J'.$rowProvincial.':Q'.$rowProvincial)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'D9E1F2']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => '000000']
                        ]
                    ]
                ]);
                $sheet->getStyle('J'.$rowProvincial.':Q'.$rowProvincial)->getAlignment()->setHorizontal('center');
            } else {
                $sheet->setCellValue('J5', 'No hay datos de despachos provinciales');
                $sheet->mergeCells('J5:Q5');
                $sheet->getStyle('J5')->getAlignment()->setHorizontal('center');
            }

            // Aplicar bordes a tabla provincial
            $sheet->getStyle('J3:Q'.($rowProvincial))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ]);

            // Ajustar anchos de columnas
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(18);

            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(27);
            $sheet->getColumnDimension('M')->setWidth(25);
            $sheet->getColumnDimension('N')->setWidth(15);
            $sheet->getColumnDimension('O')->setWidth(12);
            $sheet->getColumnDimension('P')->setWidth(12);
            $sheet->getColumnDimension('Q')->setWidth(18);

            // Generar el archivo
            $nombre_excel = "reporte_liquidaciones_aprobadas_" . date('d-m-Y', strtotime($this->desde)) . '_al_' . date('d-m-Y', strtotime($this->hasta)) . '.xlsx';

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $temp_file = tempnam(sys_get_temp_dir(), $nombre_excel);
            $writer->save($temp_file);

            return response()->download($temp_file, $nombre_excel, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el reporte: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
