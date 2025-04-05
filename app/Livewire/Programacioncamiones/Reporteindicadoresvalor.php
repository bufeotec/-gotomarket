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

class Reporteindicadoresvalor extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $guia;
    public function __construct(){
        $this->logs = new Logs();
        $this->guia = new Guia();
    }
    public $xdesde;
    public $xhasta;
    public $mxdesde;
    public $mxhasta;
    public $filteredData = [];
    public $summary = [];
    public $searchdatos = false;
    public $filterByEmision = true;

    public $departamentos = [
        'LIMA' => 'LIMA',
        'CALLAO' => 'LIMA',
        'ANCASH' => 'PROVINCIA 1',
        'ICA' => 'PROVINCIA 1',
        'HUANCAVELICA' => 'PROVINCIA 1',
        'HUANUCO' => 'PROVINCIA 1',
        'LAMBAYEQUE' => 'PROVINCIA 1',
        'LA LIBERTAD' => 'PROVINCIA 1',
        'JUNIN' => 'PROVINCIA 1',
        'PASCO' => 'PROVINCIA 1',
        'AYACUCHO' => 'PROVINCIA 1',
        'APURIMAC' => 'PROVINCIA 2',
        'AMAZONAS' => 'PROVINCIA 2',
        'AREQUIPA' => 'PROVINCIA 2',
        'CAJAMARCA' => 'PROVINCIA 2',
        'CUSCO' => 'PROVINCIA 2',
        'LORETO' => 'PROVINCIA 2',
        'MADRE DE DIOS' => 'PROVINCIA 2',
        'MOQUEGUA' => 'PROVINCIA 2',
    ];

    public function render(){
        return view('livewire.programacioncamiones.reporteindicadoresvalor');
    }
    public function setFilterByEmision($value)
    {
        $this->filterByEmision = $value;
        $this->xdesde = null;
        $this->xhasta = null;
        $this->mxdesde = null;
        $this->mxhasta = null;
    }
    public function buscar_reporte_valor() {
        $this->searchdatos = true;

        if (empty($this->xdesde) && empty($this->xhasta) && empty($this->mxdesde) && empty($this->mxhasta)) {
            $this->filteredData = [];
            return;
        }
        $query = DB::table('despachos as d')
            ->leftJoin('programaciones as p', 'd.id_programacion', '=', 'p.id_programacion')
            ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
            ->leftJoin('departamentos as dep', 'd.id_departamento', '=', 'dep.id_departamento')
            ->leftJoin('provincias as prov', 'd.id_provincia', '=', 'prov.id_provincia')
            ->select(
                'p.programacion_fecha as fec_despacho',
                'd.despacho_fecha_aprobacion as fecha_os',
                'd.created_at as fecha_ss',
                'd.despacho_numero_correlativo as numero_os',
                'd.despacho_flete as flete',
                'd.despacho_costo_total as valor_transportado',
                'd.id_tipo_servicios as tipo_servicio',
                'd.despacho_estado_aprobacion as estado_os',
                'prov.provincia_nombre as provincia',
                'dep.departamento_nombre as departamento',
                'dv.despacho_venta_direccion_llegada as zona_despacho'
            )
            ->where('d.despacho_estado_aprobacion', 3);

        if (!empty($this->xdesde)) {
            $query->whereDate('d.despacho_fecha_aprobacion', '>=', $this->xdesde);
        }
        if (!empty($this->xhasta)) {
            $query->whereDate('d.despacho_fecha_aprobacion', '<=', $this->xhasta);
        }

        // Filtros por mes
        if (!empty($this->mxdesde)) {
            $startMonth = date('Y-m-01', strtotime($this->mxdesde));
            $query->where('d.despacho_fecha_aprobacion', '>=', $startMonth);
        }
        if (!empty($this->mxhasta)) {
            $endMonth = date('Y-m-t', strtotime($this->mxhasta));
            $query->where('d.despacho_fecha_aprobacion', '<=', $endMonth);
        }

        $this->filteredData = $query->get();
        $this->calculateSummary();
    }
    private function calculateSummary() {
        $summary = [
            'Lima' => ['flete' => 0, 'valor' => 0, 'count' => 0, 'porcentaje' => 0],
            'Provincia 1' => ['flete' => 0, 'valor' => 0, 'count' => 0, 'porcentaje' => 0],
            'Provincia 2' => ['flete' => 0, 'valor' => 0, 'count' => 0, 'porcentaje' => 0],
            'Total' => ['flete' => 0, 'valor' => 0, 'count' => 0, 'porcentaje' => 0],
        ];

        foreach ($this->filteredData as $resultado) {
            $departamento = strtoupper(trim($resultado->departamento ?? ''));
            $zona = match(true) {
                in_array($departamento, ['LIMA', 'CALLAO']) => 'Lima',
                in_array($departamento, ['ANCASH', 'ICA', 'HUANCAVELICA', 'HUANUCO', 'LAMBAYEQUE', 'LA LIBERTAD', 'JUNIN', 'PASCO', 'AYACUCHO']) => 'Provincia 1',
                in_array($departamento, ['APURIMAC', 'AMAZONAS', 'AREQUIPA', 'CAJAMARCA', 'CUSCO', 'LORETO', 'MADRE DE DIOS', 'MOQUEGUA']) => 'Provincia 2',
                default => null
            };

            if ($zona) {
                $summary[$zona]['flete'] += $resultado->flete;
                $summary[$zona]['valor'] += $resultado->valor_transportado;
                $summary[$zona]['count']++;

                $summary['Total']['flete'] += $resultado->flete;
                $summary['Total']['valor'] += $resultado->valor_transportado;
                $summary['Total']['count']++;
            }
        }

        foreach ($summary as $zona => &$data) {
            if ($data['valor'] > 0) {
                $data['porcentaje'] = round(($data['flete'] / $data['valor']) * 100, 2);
            }
        }

        $this->summary = $summary;
    }
    public function exportarReporteValorExcel()
    {
        try {
            // Verificar permisos
            if (!Gate::allows('exportar_reporte_valor_excel')) {
                session()->flash('error', 'No tiene permisos para generar este reporte.');
                return;
            }

            $query = DB::table('despachos as d')
                ->leftJoin('programaciones as p', 'd.id_programacion', '=', 'p.id_programacion')
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->leftJoin('departamentos as dep', 'd.id_departamento', '=', 'dep.id_departamento')
                ->leftJoin('provincias as prov', 'd.id_provincia', '=', 'prov.id_provincia')
                ->select(
                    'p.programacion_fecha as fec_despacho',
                    'd.despacho_fecha_aprobacion as fecha_os',
                    'd.created_at as fecha_ss',
                    'd.despacho_numero_correlativo as numero_os',
                    'd.despacho_costo_total as valor_transportado',
                    'd.despacho_flete as flete',
                    'd.id_tipo_servicios as tipo_servicio',
                    'd.despacho_estado_aprobacion as estado_os',
                    'dep.departamento_nombre as departamento',
                    'prov.provincia_nombre as provincia',
                    'dv.despacho_venta_direccion_llegada as zona_despacho'
                )
                ->where('d.despacho_estado_aprobacion', 3);

            // Filtros por fechas
            if (!empty($this->ydesde) && !empty($this->yhasta)) {
                $query->whereDate('d.despacho_fecha_aprobacion', '>=', $this->ydesde)
                    ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->yhasta);
            } elseif (!empty($this->mydesde) && !empty($this->myhasta)) {
                $startMonth = date('Y-m-01', strtotime($this->mydesde));
                $endMonth = date('Y-m-t', strtotime($this->myhasta));
                $query->where('d.despacho_fecha_aprobacion', '>=', $startMonth)
                    ->where('d.despacho_fecha_aprobacion', '<=', $endMonth);
            }

            $filteredData = $query->get();

            if ($filteredData->isEmpty()) {
                session()->flash('error', 'No hay datos para exportar en el rango de fechas seleccionado.');
                return;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Reporte de Valor');

            // ========== CABECERA PRINCIPAL ==========
            $sheet->setCellValue('A1', 'REPORTE DE FLETE: INDICADORES DE VALOR TRANSPORTADO');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('A8D08D');

            // ========== RANGO DE FECHAS ==========
            $rangoFechas = '';
            if (!empty($this->ydesde) && !empty($this->yhasta)) {
                $rangoFechas = 'Del ' . date('d/m/Y', strtotime($this->ydesde)) . ' al ' . date('d/m/Y', strtotime($this->yhasta));
            } elseif (!empty($this->mydesde) && !empty($this->myhasta)) {
                $rangoFechas = 'Del ' . date('m/Y', strtotime($this->mydesde)) . ' al ' . date('m/Y', strtotime($this->myhasta));
            }
            $sheet->setCellValue('A2', $rangoFechas);
            $sheet->mergeCells('A2:J2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

            // ========== ENCABEZADOS ==========
            $headers = [
                'Fecha de OS',
                'Fecha de Guía y/o SS',
                'N° de OS',
                'Valor Transportado',
                'Flete / Monto de OS',
                'Tipo de OS',
                'Estado de OS',
                'Departamento',
                'Provincia',
                'Zona de Despacho Asignada'
            ];

            $sheet->fromArray($headers, null, 'A3');
            $sheet->getStyle('A3:J3')->getFont()->setBold(true);
            $sheet->getStyle('A3:J3')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A3:J3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D9E1F2');

            // ========== LLENAR DATOS ==========
            $row = 4;
            foreach ($filteredData as $item) {
                $sheet->setCellValue('A'.$row, $item->fecha_os ? date('d/m/Y', strtotime($item->fecha_os)) : '');
                $sheet->setCellValue('B'.$row, $item->fecha_ss ? date('d/m/Y', strtotime($item->fecha_ss)) : '');
                $sheet->setCellValue('C'.$row, $item->numero_os);
                $sheet->setCellValue('D'.$row, $item->valor_transportado ?? 0);
                $sheet->setCellValue('E'.$row, 'S/ ' . number_format($item->flete, 2, '.', ','));
                $sheet->setCellValue('F'.$row, match($item->tipo_servicio) {
                    1 => 'Local',
                    2 => 'Provincia',
                    3 => 'Mixto',
                    default => 'No especificado'
                });
                $sheet->setCellValue('G'.$row, match($item->estado_os) {
                    1 => 'Aprobado',
                    2 => 'En Camino',
                    3 => 'Liquidado',
                    default => 'Desconocido'
                });
                $sheet->setCellValue('H'.$row, $item->departamento ?? 'S/N');
                $sheet->setCellValue('I'.$row, $item->provincia ?? 'S/N');
                $sheet->setCellValue('J'.$row, $item->zona_despacho ?? 'S/N');

                $sheet->getStyle('A'.$row.':J'.$row)->getAlignment()->setHorizontal('center');
                $row++;
            }

            // ========== FORMATO NUMÉRICO ==========
            $sheet->getStyle('D4:D'.($row-1))->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E4:E'.($row-1))->getNumberFormat()->setFormatCode('"S/"#,##0.00');

            // ========== ANCHO DE COLUMNA (AUTO AJUSTE) ==========
            foreach (range('A', 'J') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // ========== BORDES ==========
            $sheet->getStyle('A3:J'.($row-1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ]);

            // ========== GENERAR ARCHIVO ==========
            $nombreArchivo = "reporte_valor_transportado_" . date('Ymd_His') . ".xlsx";

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $temp_file = tempnam(sys_get_temp_dir(), $nombreArchivo);
            $writer->save($temp_file);

            return response()->download($temp_file, $nombreArchivo, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el reporte: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
