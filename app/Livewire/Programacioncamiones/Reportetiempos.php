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

class Reportetiempos extends Component
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
    public $mdesde;
    public $mhasta;
    public $filterByEmision = true;
    public $filteredData = [];
    public $localData = [];
    public $provincialData = [];
    public $searchdatos = false;
    public $searchExecuted = false;
    public $tiemposLima = [];
    public $tiemposProv = [];
    public $departamentos = [
        'CALLAO' => 'LIMA',
        'LIMA' => 'LIMA',
        'ANCASH' => 'PROVINCIA 1',
        'AYACUCHO' => 'PROVINCIA 1',
        'HUANCAVELICA' => 'PROVINCIA 1',
        'HUANUCO' => 'PROVINCIA 1',
        'JUNIN' => 'PROVINCIA 1',
        'LA LIBERTAD' => 'PROVINCIA 1',
        'LAMBAYEQUE' => 'PROVINCIA 1',
        'PASCO' => 'PROVINCIA 1',
        'ICA' => 'PROVINCIA 1',
        'AMAZONAS' => 'PROVINCIA 2',
        'APURIMAC' => 'PROVINCIA 2',
        'AREQUIPA' => 'PROVINCIA 2',
        'CAJAMARCA' => 'PROVINCIA 2',
        'CUSCO' => 'PROVINCIA 2',
        'LORETO' => 'PROVINCIA 2',
        'MADRE DE DIOS' => 'PROVINCIA 2',
        'MOQUEGUA' => 'PROVINCIA 2',
    ];
    public function render(){
        return view('livewire.programacioncamiones.reportetiempos');
    }
    public function setFilterByEmision($value)
    {
        $this->filterByEmision = $value;
        $this->desde = null;
        $this->hasta = null;
        $this->mdesde = null;
        $this->mhasta = null;
    }
    public function buscar_reporte_tiempo() {
        $this->searchExecuted = true;
        if (empty($this->desde) && empty($this->hasta) && empty($this->mdesde) && empty($this->mhasta)) {
            $this->localData = [];
            $this->provincialData = [];
            $this->filteredData = [];
            return;
        }

        $query = DB::table('guias as g')
            ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
            ->join('despachos as d', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('programaciones as p', 'd.id_programacion', '=', 'p.id_programacion')
            ->leftJoin('historial_guias as hg', function ($join) {
                $join->on('g.id_guia', '=', 'hg.id_guia')
                    ->where('hg.historial_guia_estado_aprobacion', '=', 5);
            })
            ->select(
                'g.guia_fecha_emision as fecha_emision',
                'g.guia_nro_doc as numero_guia',
                'g.guia_nombre_cliente as cliente',
                'p.programacion_fecha as fec_despacho',
                'd.despacho_estado_aprobacion as estado_os',
                'g.guia_departamento as departamento',
                'g.guia_provincia as provincia',
                'g.guia_direc_entrega as zona_despacho',
                DB::raw('CASE
                WHEN g.guia_departamento IN ("' . implode('", "', array_keys($this->departamentos)) . '")
                THEN "' . $this->departamentos['LIMA'] . '"
                ELSE "Provincia 2"
            END as departamento'),
                DB::raw('AVG(ABS(DATEDIFF(g.guia_fecha_emision, d.despacho_fecha_aprobacion))) as dias_entrega')
            )
            ->where('d.despacho_estado_aprobacion', [1, 2, 3]);

        // Filtros
        if ($this->filterByEmision) {
            if (!empty($this->desde)) {
                $query->whereDate('g.guia_fecha_emision', '>=', $this->desde);
            }
            if (!empty($this->hasta)) {
                $query->whereDate('g.guia_fecha_emision', '<=', $this->hasta);
            }
            if (!empty($this->mdesde) && !empty($this->mhasta)) {
                $query->whereBetween('g.guia_fecha_emision', [
                    date('Y-m-01', strtotime($this->mdesde)),
                    date('Y-m-t', strtotime($this->mhasta))
                ]);
            }
        } else {
            if (!empty($this->desde)) {
                $query->whereDate('p.programacion_fecha', '>=', $this->desde);
            }
            if (!empty($this->hasta)) {
                $query->whereDate('p.programacion_fecha', '<=', $this->hasta);
            }
            if (!empty($this->mdesde) && !empty($this->mhasta)) {
                $query->whereBetween('p.programacion_fecha', [
                    date('Y-m-01', strtotime($this->mdesde)),
                    date('Y-m-t', strtotime($this->mhasta))
                ]);
            }
        }
        $datos = $query->groupBy(
            'g.guia_fecha_emision',
            'g.guia_nro_doc',
            'g.guia_nombre_cliente',
            'p.programacion_fecha',
            'd.despacho_estado_aprobacion',
            'g.guia_departamento',
            'g.guia_provincia',
            'g.guia_direc_entrega'
        )->get();

        $this->filteredData = $datos;
    }
    public function exportarTiemposExcel()
    {
        try {
            // Verificar permisos
            if (!Gate::allows('exportar_tiempos_excel')) {
                session()->flash('error', 'No tiene permisos para generar este reporte.');
                return;
            }

            // Consulta principal con join a historial_guias
            $query = DB::table('guias as g')
                ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
                ->join('despachos as d', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('programaciones as p', 'd.id_programacion', '=', 'p.id_programacion')
                ->leftJoin('historial_guias as hg', function($join) {
                    $join->on('g.id_guia', '=', 'hg.id_guia')
                        ->where('hg.historial_guia_estado_aprobacion', '=', 5);
                })
                ->select(
                    'g.guia_fecha_emision as fecha_emision',
                    'g.guia_nro_doc as numero_guia',
                    'g.guia_nombre_cliente as cliente',
                    'p.programacion_fecha as fec_despacho',
                    'hg.historial_guia_fecha_hora as fecha_llegada',
                    DB::raw('DATEDIFF(COALESCE(hg.historial_guia_fecha_hora, NOW()), g.guia_fecha_emision) as dias_entrega'),
                    'd.despacho_estado_aprobacion as estado_os',
                    'g.guia_departamento as departamento',
                    'g.guia_provincia as provincia',
                    'g.guia_direc_entrega as zona_despacho'
                )
                ->whereIn('d.despacho_estado_aprobacion', [1, 2, 3])
                ->groupBy('g.id_guia');

            // Aplicar filtros
            if ($this->filterByEmision) {
                if (!empty($this->desde)) {
                    $query->whereDate('g.guia_fecha_emision', '>=', $this->desde);
                }
                if (!empty($this->hasta)) {
                    $query->whereDate('g.guia_fecha_emision', '<=', $this->hasta);
                }
                if (!empty($this->mdesde) && !empty($this->mhasta)) {
                    $query->whereBetween('g.guia_fecha_emision', [
                        date('Y-m-01', strtotime($this->mdesde)),
                        date('Y-m-t', strtotime($this->mhasta))
                    ]);
                }
            } else {
                if (!empty($this->desde)) {
                    $query->whereDate('p.programacion_fecha', '>=', $this->desde);
                }
                if (!empty($this->hasta)) {
                    $query->whereDate('p.programacion_fecha', '<=', $this->hasta);
                }
                if (!empty($this->mdesde) && !empty($this->mhasta)) {
                    $query->whereBetween('p.programacion_fecha', [
                        date('Y-m-01', strtotime($this->mdesde)),
                        date('Y-m-t', strtotime($this->mhasta))
                    ]);
                }
            }

            $filteredData = $query->get();

            if ($filteredData->isEmpty()) {
                session()->flash('error', 'No hay datos para exportar en el rango de fechas seleccionado.');
                return;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Reporte de Tiempos');

            // ========== CABECERA PRINCIPAL ==========
            $sheet->setCellValue('A1', 'REPORTE DE TIEMPOS DE ENTREGA');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('A8D08D');

            // ========== RANGO DE FECHAS ==========
            $rangoFechas = '';
            if (!empty($this->desde) && !empty($this->hasta)) {
                $rangoFechas = 'Del ' . date('d/m/Y', strtotime($this->desde)) . ' al ' . date('d/m/Y', strtotime($this->hasta));
            } elseif (!empty($this->mdesde) && !empty($this->mhasta)) {
                $rangoFechas = 'Del ' . date('m/Y', strtotime($this->mdesde)) . ' al ' . date('m/Y', strtotime($this->mhasta));
            }

            $sheet->setCellValue('A2', $rangoFechas);
            $sheet->mergeCells('A2:J2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

            // ========== ENCABEZADOS ==========
            $headers = [
                'Fecha Emisión',
                'N° Guía',
                'Cliente',
                'Fecha Despacho',
                'Fecha Llegada',
                'Días Entrega',
                'Estado OS',
                'Departamento',
                'Provincia',
                'Zona Despacho'
            ];

            $sheet->fromArray($headers, null, 'A3');
            $sheet->getStyle('A3:J3')->getFont()->setBold(true);
            $sheet->getStyle('A3:J3')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A3:J3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D9E1F2');

            // ========== LLENAR DATOS ==========
            $row = 4;
            foreach ($filteredData as $item) {
                // Determinar estado
                $estado = match($item->estado_os) {
                    1 => 'Pendiente', 2 => 'Aprobado', 3 => 'Liquidado', default => 'Desconocido'
                };

                $sheet->setCellValue('A'.$row, $item->fecha_emision ? date('d/m/Y', strtotime($item->fecha_emision)) : '');
                $sheet->setCellValue('B'.$row, $item->numero_guia);
                $sheet->setCellValue('C'.$row, $item->cliente);
                $sheet->setCellValue('D'.$row, $item->fec_despacho ? date('d/m/Y', strtotime($item->fec_despacho)) : '');
                $sheet->setCellValue('E'.$row, $item->fecha_llegada ? date('d/m/Y H:i', strtotime($item->fecha_llegada)) : 'En Camino');
                $sheet->setCellValue('F'.$row, $item->dias_entrega);
                $sheet->setCellValue('G'.$row, $estado);
                $sheet->setCellValue('H'.$row, $item->departamento ?? 'S/N');
                $sheet->setCellValue('I'.$row, $item->provincia ?? 'S/N');
                $sheet->setCellValue('J'.$row, $item->zona_despacho ?? 'S/N');

                $sheet->getStyle('A'.$row.':J'.$row)->getAlignment()->setHorizontal('center');
                $row++;
            }

            // ========== FORMATO NUMÉRICO ==========
            $sheet->getStyle('F4:F'.($row-1))->getNumberFormat()->setFormatCode('#,##0');

            // ========== ANCHO DE COLUMNA ==========
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(20); // Más ancho para fecha+hora
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
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
            $nombreArchivo = "reporte_tiempos_entrega_" . date('Ymd_His') . ".xlsx";

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
