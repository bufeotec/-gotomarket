<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Guia;
use App\Models\Logs;
use Carbon\Carbon;
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
    public $filteredData = [];
    public $summary = [];
    public $searchdatos = false;

    // Objetivos fijos como en la imagen
    public $objetivos = [
        'Total' => 3.90,
        'Local' => 1.90,
        'Provincia 1' => 5.50,
        'Provincia 2' => 9.50
    ];

    public $departamentos = [
        'LIMA' => 'LOCAL',
        'CALLAO' => 'LOCAL',
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

    public function buscar_reporte_valor() {
        $this->searchdatos = true;

        if (empty($this->xdesde) || empty($this->xhasta)) {
            $this->filteredData = [];
            return;
        }

        // 1. Obtenemos los despachos únicos con guías (incluyendo la relación con guías)
        $despachos = DB::table('despachos as d')
            ->select(
                'd.id_despacho',
                'd.despacho_costo_total as flete',
                'd.id_tipo_servicios as tipo_servicio',
                'dep.departamento_nombre as departamento'
            )
            ->leftJoin('departamentos as dep', 'd.id_departamento', '=', 'dep.id_departamento')
            ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
            ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia') // Aquí está el cambio clave
            ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->xdesde)
            ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->xhasta)
            ->groupBy('d.id_despacho', 'd.despacho_costo_total', 'd.id_tipo_servicios', 'dep.departamento_nombre')
            ->get();

        // 2. Obtenemos la suma de guías por despacho (igual que antes)
        $guiasPorDespacho = DB::table('despachos as d')
            ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
            ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
            ->select(
                'd.id_despacho',
                DB::raw('SUM(g.guia_importe_total) as valor_transportado')
            )
            ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->xdesde)
            ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->xhasta)
            ->groupBy('d.id_despacho')
            ->get()
            ->keyBy('id_despacho');

        // 3. Combinamos los datos y determinamos la zona
        $this->filteredData = $despachos->map(function($despacho) use ($guiasPorDespacho) {
            $departamento = strtoupper($despacho->departamento ?? '');

            $zona = 'Otra';
            if ($despacho->tipo_servicio == 1) {
                $zona = 'Local';
            } elseif (in_array($departamento, ['LIMA', 'CALLAO'])) {
                $zona = 'Local';
            } elseif (in_array($departamento, ['ANCASH', 'ICA', 'HUANCAVELICA', 'HUANUCO', 'LAMBAYEQUE', 'LA LIBERTAD', 'JUNIN', 'PASCO', 'AYACUCHO'])) {
                $zona = 'Provincia 1';
            } elseif (in_array($departamento, ['APURIMAC', 'AMAZONAS', 'AREQUIPA', 'CAJAMARCA', 'CUSCO', 'LORETO', 'MADRE DE DIOS', 'MOQUEGUA'])) {
                $zona = 'Provincia 2';
            }

            return (object) [
                'flete' => $despacho->flete,
                'valor_transportado' => $guiasPorDespacho[$despacho->id_despacho]->valor_transportado ?? 0,
                'zona' => $zona
            ];
        });

        $this->calculateSummary();
        $this->obtenerDatosGraficos();
    }

    public function calculateSummary() {
        $summary = [
            'Total' => ['flete' => 0, 'valor' => 0, 'porcentaje' => 0],
            'Local' => ['flete' => 0, 'valor' => 0, 'porcentaje' => 0],
            'Provincia 1' => ['flete' => 0, 'valor' => 0, 'porcentaje' => 0],
            'Provincia 2' => ['flete' => 0, 'valor' => 0, 'porcentaje' => 0],
        ];

        foreach ($this->filteredData as $resultado) {
            $zona = $resultado->zona;

            if (isset($summary[$zona])) {
                $summary[$zona]['flete'] += $resultado->flete;
                $summary[$zona]['valor'] += $resultado->valor_transportado;

                $summary['Total']['flete'] += $resultado->flete;
                $summary['Total']['valor'] += $resultado->valor_transportado;
            }
        }

        // Calculamos porcentajes solo si hay valor transportado
        foreach ($summary as $zona => &$data) {
            if ($data['valor'] > 0) {
                $data['porcentaje'] = round(($data['flete'] / $data['valor']) * 100, 2);
            } else {
                $data['porcentaje'] = 0;
            }
        }

        $this->summary = $summary;
    }

    public function obtenerDatosGraficos() {
        $mesesEspanol = [
            1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR',
            5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC'
        ];

        // Primero obtenemos el rango de meses completo
        $fechaDesde = Carbon::parse($this->xdesde);
        $fechaHasta = Carbon::parse($this->xhasta);

        // Creamos un array con todos los meses en el rango
        $mesesCompletos = [];
        $current = $fechaDesde->copy();
        while ($current <= $fechaHasta) {
            $mesesCompletos[] = [
                'mes' => $current->month,
                'anio' => $current->year,
                'key' => $mesesEspanol[$current->month] . '-' . substr($current->year, 2, 2)
            ];
            $current->addMonth();
        }

        // Consulta para Flete Total (despachos únicos con guías)
        $despachosFleteTotal = DB::table('despachos as d')
            ->select(
                DB::raw('MONTH(d.despacho_fecha_aprobacion) as mes'),
                DB::raw('YEAR(d.despacho_fecha_aprobacion) as anio'),
                DB::raw('SUM(d.despacho_costo_total) as flete_total')
            )
            ->whereIn('d.id_despacho', function($query) {
                $query->select('dv.id_despacho')
                    ->from('despacho_ventas as dv')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->whereDate('dv.created_at', '>=', $this->xdesde)
                    ->whereDate('dv.created_at', '<=', $this->xhasta);
            })
            ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->xdesde)
            ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->xhasta)
            ->groupBy(DB::raw('YEAR(d.despacho_fecha_aprobacion)'), DB::raw('MONTH(d.despacho_fecha_aprobacion)'))
            ->get()
            ->keyBy(function($item) use ($mesesEspanol) {
                return $mesesEspanol[$item->mes] . '-' . substr($item->anio, 2, 2);
            });

        // Procesar datos para Flete Total (asegurando todos los meses)
        $mesesFleteTotal = [];
        $fleteTotal = [];
        foreach ($mesesCompletos as $mes) {
            $mesesFleteTotal[] = $mes['key'];
            $fleteTotal[] = isset($despachosFleteTotal[$mes['key']]) ? (float)$despachosFleteTotal[$mes['key']]->flete_total : 0;
        }

        // Consulta para Flete Lima y Provincia (despachos únicos con guías)
        $despachosFleteLimaProvincia = DB::table('despachos as d')
            ->select(
                DB::raw('MONTH(d.despacho_fecha_aprobacion) as mes'),
                DB::raw('YEAR(d.despacho_fecha_aprobacion) as anio'),
                DB::raw('SUM(CASE WHEN d.id_tipo_servicios = 1 OR (dep.departamento_nombre IN ("LIMA", "CALLAO"))
                THEN d.despacho_costo_total ELSE 0 END) as flete_lima'),
                DB::raw('SUM(CASE WHEN dep.departamento_nombre NOT IN ("LIMA", "CALLAO")
                THEN d.despacho_costo_total ELSE 0 END) as flete_provincia')
            )
            ->leftJoin('departamentos as dep', 'd.id_departamento', '=', 'dep.id_departamento')
            ->whereIn('d.id_despacho', function($query) {
                $query->select('dv.id_despacho')
                    ->from('despacho_ventas as dv')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->whereDate('dv.created_at', '>=', $this->xdesde)
                    ->whereDate('dv.created_at', '<=', $this->xhasta);
            })
            ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->xdesde)
            ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->xhasta)
            ->groupBy(DB::raw('YEAR(d.despacho_fecha_aprobacion)'), DB::raw('MONTH(d.despacho_fecha_aprobacion)'))
            ->get()
            ->keyBy(function($item) use ($mesesEspanol) {
                return $mesesEspanol[$item->mes] . '-' . substr($item->anio, 2, 2);
            });

        // Procesar datos para Flete Lima y Provincia (asegurando todos los meses)
        $mesesFleteLimaProvincia = [];
        $fleteLima = [];
        $fleteProvincia = [];
        foreach ($mesesCompletos as $mes) {
            $mesesFleteLimaProvincia[] = $mes['key'];

            if (isset($despachosFleteLimaProvincia[$mes['key']])) {
                $fleteLima[] = (float)$despachosFleteLimaProvincia[$mes['key']]->flete_lima;
                $fleteProvincia[] = (float)$despachosFleteLimaProvincia[$mes['key']]->flete_provincia;
            } else {
                $fleteLima[] = 0;
                $fleteProvincia[] = 0;
            }
        }

        // Asignar datos con valores por defecto si están vacíos
        $this->datosGraficoFleteTotal = [
            'meses' => $mesesFleteTotal,
            'flete_total' => $fleteTotal
        ];

        $this->datosGraficoFleteLimaProvincia = [
            'meses' => $mesesFleteLimaProvincia,
            'flete_lima' => $fleteLima,
            'flete_provincia' => $fleteProvincia
        ];

        // Emitir eventos para actualizar los gráficos
        $this->dispatch('actualizarGraficoFleteTotal', $this->datosGraficoFleteTotal);
        $this->dispatch('actualizarGraficoFleteLimaProvincia', $this->datosGraficoFleteLimaProvincia);
    }

    public function render(){
        if ($this->searchdatos) {
            // Verificar que los datos existan antes de enviarlos
            if (!empty($this->datosGraficoFleteTotal)) {
                $this->dispatch('actualizarGraficoFleteTotal', $this->datosGraficoFleteTotal);
            } else {
                $this->dispatch('actualizarGraficoFleteTotal', [
                    'meses' => ['ENE-25'],
                    'flete_total' => [0]
                ]);
            }

            if (!empty($this->datosGraficoFleteLimaProvincia)) {
                $this->dispatch('actualizarGraficoFleteLimaProvincia', $this->datosGraficoFleteLimaProvincia);
            } else {
                $this->dispatch('actualizarGraficoFleteLimaProvincia', [
                    'meses' => ['ENE-25'],
                    'flete_lima' => [0],
                    'flete_provincia' => [0]
                ]);
            }
        }

        return view('livewire.programacioncamiones.reporteindicadoresvalor');
    }

    public function exportarReporteValorExcel(){
        try {
            if (!Gate::allows('exportar_reporte_valor_excel')) {
                session()->flash('error', 'No tiene permisos para generar este reporte.');
                return;
            }

            if (empty($this->xdesde) || empty($this->xhasta)) {
                session()->flash('error', 'Debe especificar un rango de fechas para generar el reporte.');
                return;
            }

            // Obtenemos los despachos con sus guías
            $despachos = DB::table('despachos as d')
                ->select(
                    'd.id_despacho',
                    'd.despacho_fecha_aprobacion',
                    'd.despacho_numero_correlativo',
                    'd.despacho_costo_total',
                    'd.id_tipo_servicios',
                    'd.despacho_estado_aprobacion',
                    'g.guia_departamento',
                    'g.guia_provincia',
                    'g.guia_direc_entrega',
                    'g.id_guia',
                    DB::raw('MIN(g.guia_fecha_emision) as fecha_guia_ss'),
                    DB::raw('SUM(g.guia_importe_total) as valor_transportado'),
                    DB::raw('CASE
                    WHEN d.id_tipo_servicios = 1 THEN "Local"
                    WHEN d.id_tipo_servicios = 2 AND g.guia_departamento IN ("LIMA", "CALLAO") THEN "Local"
                    WHEN d.id_tipo_servicios = 2 AND g.guia_departamento IN ("ANCASH", "ICA", "HUANCAVELICA", "HUANUCO", "LAMBAYEQUE", "LA LIBERTAD", "JUNIN", "PASCO", "AYACUCHO") THEN "Provincia 1"
                    WHEN d.id_tipo_servicios = 2 AND g.guia_departamento IN ("APURIMAC", "AMAZONAS", "AREQUIPA", "CAJAMARCA", "CUSCO", "LORETO", "MADRE DE DIOS", "MOQUEGUA") THEN "Provincia 2"
                    ELSE "Otra"
                END as zona')
                )
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->xdesde)
                ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->xhasta)
                ->groupBy(
                    'd.id_despacho',
                    'd.despacho_fecha_aprobacion',
                    'd.despacho_numero_correlativo',
                    'd.despacho_costo_total',
                    'd.id_tipo_servicios',
                    'd.despacho_estado_aprobacion',
                    'g.guia_departamento',
                    'g.guia_provincia',
                    'g.guia_direc_entrega',
                    'g.id_guia'
                )
                ->get();

            if ($despachos->isEmpty()) {
                session()->flash('error', 'No hay datos para exportar en el rango de fechas seleccionado.');
                return;
            }

            // Procesar los despachos para determinar si son mixtos
            $processedDespachos = [];
            $uniqueDespachos = [];

            foreach ($despachos as $despacho) {
                // Verificar si ya procesamos este despacho
                if (isset($uniqueDespachos[$despacho->id_despacho])) {
                    continue;
                }

                // Verificar si es mixto
                $validarMixto = DB::table('despacho_ventas as dv')
                    ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                    ->join('guias_detalles as gd', 'gd.id_guia', '=', 'g.id_guia')
                    ->where('dv.id_guia', '=', $despacho->id_guia)
                    ->where('dv.id_despacho', '<>', $despacho->id_despacho)
                    ->where('d.id_programacion', '=', $despacho->id_programacion ?? null)
                    ->where('d.id_tipo_servicios', '=', 2)
                    ->first();

                $typeComprop = $validarMixto ? 3 : $despacho->id_tipo_servicios;

                $tipoOS = match ($typeComprop) {
                    1 => 'LOCAL',
                    2 => 'PROVINCIAL',
                    3 => 'MIXTO',
                    default => '',
                };

                // Solo agregar despachos únicos (máximo 4)
                if (!isset($uniqueDespachos[$despacho->id_despacho]) && count($uniqueDespachos) < 4) {
                    $processedDespachos[] = (object) [
                        'despacho_fecha_aprobacion' => $despacho->despacho_fecha_aprobacion,
                        'fecha_guia_ss' => $despacho->fecha_guia_ss,
                        'despacho_numero_correlativo' => $despacho->despacho_numero_correlativo,
                        'valor_transportado' => $despacho->valor_transportado,
                        'despacho_costo_total' => $despacho->despacho_costo_total,
                        'tipo_os' => $tipoOS,
                        'despacho_estado_aprobacion' => $despacho->despacho_estado_aprobacion,
                        'guia_departamento' => $despacho->guia_departamento,
                        'guia_provincia' => $despacho->guia_provincia,
                        'guia_direc_entrega' => $despacho->guia_direc_entrega,
                        'zona' => $despacho->zona
                    ];

                    $uniqueDespachos[$despacho->id_despacho] = true;
                }
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Detalle de Despachos');

            // ========== CABECERA PRINCIPAL ==========
            $sheet->setCellValue('A1', 'DETALLE DE DESPACHOS CON GUÍAS');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('A8D08D');

            // ========== RANGO DE FECHAS ==========
            $rangoFechas = 'Del ' . date('d/m/Y', strtotime($this->xdesde)) . ' al ' . date('d/m/Y', strtotime($this->xhasta));
            $sheet->setCellValue('A2', $rangoFechas);
            $sheet->mergeCells('A2:J2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

            // ========== ENCABEZADOS ==========
            $headers = [
                'Fecha de OS',
                'Fecha de Guía y/o SS',
                'N° de OS',
                'Valor Transportado (S/)',
                'Flete / Monto de OS (S/)',
                'Tipo de OS(Local, Mixto o Provincia)',
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
            foreach ($processedDespachos as $despacho) {
                $sheet->setCellValue('A'.$row, date('d/m/Y', strtotime($despacho->despacho_fecha_aprobacion)));
                $sheet->setCellValue('B'.$row, $despacho->fecha_guia_ss ? date('d/m/Y', strtotime($despacho->fecha_guia_ss)) : '');
                $sheet->setCellValue('C'.$row, $despacho->despacho_numero_correlativo);
                $sheet->setCellValue('D'.$row, $despacho->valor_transportado ?? 0);
                $sheet->setCellValue('E'.$row, $despacho->despacho_costo_total);
                $sheet->setCellValue('F'.$row, $despacho->tipo_os);

                // Estado de OS
                $estadoOS = match($despacho->despacho_estado_aprobacion) {
                    0 => 'Pendiente',
                    1 => 'Aprobado',
                    2 => 'En camino',
                    3 => 'Culminado',
                    4 => 'Rechazado',
                    default => 'Desconocido'
                };
                $sheet->setCellValue('G'.$row, $estadoOS);

                $sheet->setCellValue('H'.$row, $despacho->guia_departamento ?? 'S/N');
                $sheet->setCellValue('I'.$row, $despacho->guia_provincia ?? 'S/N');
                $sheet->setCellValue('J'.$row, $despacho->guia_direc_entrega ?? 'S/N');

                // Formato numérico para valores
                $sheet->getStyle('D'.$row.':E'.$row)->getNumberFormat()->setFormatCode('#,##0.00');

                $row++;
            }

            // ========== ANCHO DE COLUMNA ==========
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(35);

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
            $nombreArchivo = "indicador_valor_transportado_" . date('Ymd_His') . ".xlsx";
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
