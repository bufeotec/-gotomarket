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

class Reporteindicadorespeso extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $guia;
    public function __construct(){
        $this->logs = new Logs();
        $this->guia = new Guia();
    }
    public $ydesde;
    public $yhasta;
    public $mydesde;
    public $myhasta;
    public $filtrarData = [];
    public $summary = [];
    public $searchdatos = false;
    public $filterByEmision = true;

    public $depa = [
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
        return view('livewire.programacioncamiones.reporteindicadorespeso');
    }
    public function setFilterByEmision($value)
    {
        $this->filterByEmision = $value;
        $this->ydesde = null;
        $this->yhasta = null;
        $this->mydesde = null;
        $this->myhasta = null;
    }
    public function buscar_reporte_peso() {
        $this->searchdatos = true;
        if ((empty($this->ydesde) && empty($this->yhasta)) &&
            (empty($this->mydesde) && empty($this->myhasta))) {
            $this->filtrarData = [];
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
                'd.despacho_peso as peso',
                'd.id_tipo_servicios as tipo_servicio',
                'd.despacho_estado_aprobacion as estado_os',
                'dep.departamento_nombre as departamento',
                'prov.provincia_nombre as provincia',
                'dv.despacho_venta_direccion_llegada as zona_despacho'
            )
            ->where('d.despacho_estado_aprobacion', 3);

        if (!empty($this->ydesde) && !empty($this->yhasta)) {
            $query->whereBetween('d.despacho_fecha_aprobacion', [
                $this->ydesde,
                $this->yhasta
            ]);
        }
        elseif (!empty($this->mydesde) && !empty($this->myhasta)) {
            $startMonth = date('Y-m-01', strtotime($this->mydesde));
            $endMonth = date('Y-m-t', strtotime($this->myhasta));
            $query->whereBetween('d.despacho_fecha_aprobacion', [
                $startMonth,
                $endMonth
            ]);
        }

        $this->filtrarData = $query->get();
        $this->calculateSummary();
    }
    private function calculateSummary() {
        $summary = [
            'Lima' => ['flete' => 0, 'peso' => 0, 'count' => 0, 'indicador' => 0],
            'Provincia 1' => ['flete' => 0, 'peso' => 0, 'count' => 0, 'indicador' => 0],
            'Provincia 2' => ['flete' => 0, 'peso' => 0, 'count' => 0, 'indicador' => 0],
            'Total' => ['flete' => 0, 'peso' => 0, 'count' => 0, 'indicador' => 0],
        ];

        foreach ($this->filtrarData as $resultado) {
            $departamento = strtoupper(trim($resultado->departamento ?? ''));
            $zona = match(true) {
                in_array($departamento, ['LIMA', 'CALLAO']) => 'Lima',
                in_array($departamento, ['ANCASH', 'ICA', 'HUANCAVELICA', 'HUANUCO', 'LAMBAYEQUE',
                    'LA LIBERTAD', 'JUNIN', 'PASCO', 'AYACUCHO']) => 'Provincia 1',
                in_array($departamento, ['APURIMAC', 'AMAZONAS', 'AREQUIPA', 'CAJAMARCA',
                    'CUSCO', 'LORETO', 'MADRE DE DIOS', 'MOQUEGUA']) => 'Provincia 2',
                default => null
            };

            if ($zona) {
                $summary[$zona]['flete'] += $resultado->flete;
                $summary[$zona]['peso'] += $resultado->peso;
                $summary[$zona]['count']++;

                $summary['Total']['flete'] += $resultado->flete;
                $summary['Total']['peso'] += $resultado->peso;
                $summary['Total']['count']++;
            }
        }

        // Calcular indicador (Soles/Kg)
        foreach ($summary as $zona => &$data) {
            $data['indicador'] = $data['peso'] > 0 ? round($data['flete'] / $data['peso'], 2) : 0;
        }

        $this->summary = $summary;
    }
    public function exportarReportePesoExcel()
    {
        try {
            // Verificar permisos
            if (!Gate::allows('exportar_reporte_peso_excel')) {
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
                    'd.despacho_flete as flete',
                    'd.despacho_peso as peso',
                    'd.id_tipo_servicios as tipo_servicio',
                    'd.despacho_estado_aprobacion as estado_os',
                    'dep.departamento_nombre as departamento',
                    'prov.provincia_nombre as provincia',
                    'dv.despacho_venta_direccion_llegada as zona_despacho'
                )
                ->where('d.despacho_estado_aprobacion', 3);

            // Filtros por fecha
            if (!empty($this->ydesde) && !empty($this->yhasta)) {
                $query->whereBetween('d.despacho_fecha_aprobacion', [
                    $this->ydesde,
                    $this->yhasta
                ]);
            } elseif (!empty($this->mydesde) && !empty($this->myhasta)) {
                $startMonth = date('Y-m-01', strtotime($this->mydesde));
                $endMonth = date('Y-m-t', strtotime($this->myhasta));
                $query->whereBetween('d.despacho_fecha_aprobacion', [
                    $startMonth,
                    $endMonth
                ]);
            }

            $filteredData = $query->get();

            if ($filteredData->isEmpty()) {
                session()->flash('error', 'No hay datos para exportar en el rango de fechas seleccionado.');
                return;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Reporte de Peso');

            // ========== CABECERA PRINCIPAL ==========
            $sheet->setCellValue('A1', 'REPORTE FLETE: INDICADOR DE PESO DESPACHADO');
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
                'Peso Despachado - Kg',
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
                // Determinar la fecha más antigua
                $fechas = [];
                if ($item->fecha_os) $fechas[] = strtotime($item->fecha_os);
                if ($item->fecha_ss) $fechas[] = strtotime($item->fecha_ss);
                $fechaAntigua = !empty($fechas) ? date('d/m/Y', min($fechas)) : '';

                // Determinar tipo de servicio
                $tipoServicio = match($item->tipo_servicio) {
                    1 => 'Local', 2 => 'Provincia', 3 => 'Mixto', default => 'No especificado'
                };

                // Determinar estado
                $estado = match($item->estado_os) {
                    1 => 'Pendiente', 2 => 'Aprobado', 3 => 'Liquidado', default => 'Desconocido'
                };

                $sheet->setCellValue('A'.$row, $item->fecha_os ? date('d/m/Y', strtotime($item->fecha_os)) : '');
                $sheet->setCellValue('B'.$row, $fechaAntigua);
                $sheet->setCellValue('C'.$row, $item->numero_os);
                $sheet->setCellValue('D'.$row, $item->peso ?? 0);
                $sheet->setCellValue('E'.$row, 'S/ ' . number_format($item->flete, 2, '.', ','));
                $sheet->setCellValue('F'.$row, $tipoServicio);
                $sheet->setCellValue('G'.$row, $estado);
                $sheet->setCellValue('H'.$row, $item->departamento ?? 'S/N');
                $sheet->setCellValue('I'.$row, $item->provincia ?? 'S/N');
                $sheet->setCellValue('J'.$row, $item->zona_despacho ?? 'S/N');

                $sheet->getStyle('A'.$row.':J'.$row)->getAlignment()->setHorizontal('center');
                $row++;
            }

            // ========== FORMATO NUMÉRICO ==========
            $sheet->getStyle('D4:D'.($row-1))->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E4:E'.($row-1))->getNumberFormat()->setFormatCode('"S/"#,##0.00');

            // ========== ANCHO DE COLUMNA ==========
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(18);
            $sheet->getColumnDimension('I')->setWidth(18);
            $sheet->getColumnDimension('J')->setAutoSize(true);

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
            $nombreArchivo = "reporte_peso_flete_" . date('Ymd_His') . ".xlsx";

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
