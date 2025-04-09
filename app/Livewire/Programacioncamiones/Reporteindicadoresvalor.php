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
    public $tipo_reporte = 'emision'; // Valor por defecto
    public $filteredData = [];
    public $summary = [];
    public $searchdatos = false;
    public $datosGraficoFleteTotal = [];
    public $datosGraficoFleteLimaProvincia = [];

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

        // Base query para despachos
        $despachosQuery = DB::table('despachos as d')
            ->select(
                'd.id_despacho',
                'd.despacho_costo_total as flete',
                'd.id_tipo_servicios as tipo_servicio',
                'dep.departamento_nombre as departamento'
            )
            ->leftJoin('departamentos as dep', 'd.id_departamento', '=', 'dep.id_departamento')
            ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
            ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
            ->where('d.despacho_estado_aprobacion', '!=', 4);

        // Base query para guías por despacho
        $guiasPorDespachoQuery = DB::table('despachos as d')
            ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
            ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
            ->select(
                'd.id_despacho',
                DB::raw('SUM(g.guia_importe_total) as valor_transportado')
            );

        // Aplicar filtro por tipo de fecha
        if ($this->tipo_reporte == 'despacho') {
            // Filtrar por fecha de despacho
            $despachosQuery->whereDate('d.despacho_fecha_aprobacion', '>=', $this->xdesde)
                ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->xhasta);

            $guiasPorDespachoQuery->whereDate('d.despacho_fecha_aprobacion', '>=', $this->xdesde)
                ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->xhasta);
        } else {
            // Filtrar por fecha de emisión (default)
            $despachosQuery->whereDate('g.guia_fecha_emision', '>=', $this->xdesde)
                ->whereDate('g.guia_fecha_emision', '<=', $this->xhasta);

            $guiasPorDespachoQuery->whereDate('g.guia_fecha_emision', '>=', $this->xdesde)
                ->whereDate('g.guia_fecha_emision', '<=', $this->xhasta);
        }

        // Ejecutar consultas
        $despachos = $despachosQuery
            ->groupBy('d.id_despacho', 'd.despacho_costo_total', 'd.id_tipo_servicios', 'dep.departamento_nombre')
            ->get();

        $guiasPorDespacho = $guiasPorDespachoQuery
            ->groupBy('d.id_despacho')
            ->get()
            ->keyBy('id_despacho');

        // Procesar datos como antes
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

        $fechaDesde = Carbon::parse($this->xdesde);
        $fechaHasta = Carbon::parse($this->xhasta);

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

        // Consulta para Flete Total - Corregida para evitar sumas duplicadas
        $fleteTotalQuery = DB::table(function($query) {
            $query->select(
                'despachos.id_despacho',
                'despachos.despacho_costo_total',
                DB::raw($this->tipo_reporte == 'despacho' ?
                    'despachos.despacho_fecha_aprobacion as fecha_referencia' :
                    'guias.guia_fecha_emision as fecha_referencia')
            )
                ->from('despachos')
                ->join('despacho_ventas', 'despachos.id_despacho', '=', 'despacho_ventas.id_despacho')
                ->join('guias', 'despacho_ventas.id_guia', '=', 'guias.id_guia')
                ->where('despachos.despacho_estado_aprobacion', '!=', 4);

            if ($this->tipo_reporte == 'despacho') {
                $query->whereDate('despachos.despacho_fecha_aprobacion', '>=', $this->xdesde)
                    ->whereDate('despachos.despacho_fecha_aprobacion', '<=', $this->xhasta);
            } else {
                $query->whereDate('guias.guia_fecha_emision', '>=', $this->xdesde)
                    ->whereDate('guias.guia_fecha_emision', '<=', $this->xhasta);
            }

            $query->groupBy('despachos.id_despacho');
        })->select(
            DB::raw('MONTH(fecha_referencia) as mes'),
            DB::raw('YEAR(fecha_referencia) as anio'),
            DB::raw('SUM(despacho_costo_total) as flete_total')
        )
            ->groupBy('mes', 'anio')
            ->get()
            ->keyBy(function($item) use ($mesesEspanol) {
                return $mesesEspanol[$item->mes] . '-' . substr($item->anio, 2, 2);
            });

        // Procesar datos para Flete Total
        $mesesFleteTotal = [];
        $fleteTotal = [];
        foreach ($mesesCompletos as $mes) {
            $mesesFleteTotal[] = $mes['key'];
            $fleteTotal[] = isset($fleteTotalQuery[$mes['key']]) ? (float)$fleteTotalQuery[$mes['key']]->flete_total : 0;
        }

        // Consulta para Flete Lima y Provincia - Corregida para evitar sumas duplicadas
        $fleteLimaProvinciaQuery = DB::table(function($query) {
            $query->select(
                'despachos.id_despacho',
                'despachos.despacho_costo_total',
                'despachos.id_tipo_servicios',
                'departamentos.departamento_nombre',
                DB::raw($this->tipo_reporte == 'despacho' ?
                    'despachos.despacho_fecha_aprobacion as fecha_referencia' :
                    'guias.guia_fecha_emision as fecha_referencia')
            )
                ->from('despachos')
                ->leftJoin('departamentos', 'despachos.id_departamento', '=', 'departamentos.id_departamento')
                ->join('despacho_ventas', 'despachos.id_despacho', '=', 'despacho_ventas.id_despacho')
                ->join('guias', 'despacho_ventas.id_guia', '=', 'guias.id_guia')
                ->where('despachos.despacho_estado_aprobacion', '!=', 4);

            if ($this->tipo_reporte == 'despacho') {
                $query->whereDate('despachos.despacho_fecha_aprobacion', '>=', $this->xdesde)
                    ->whereDate('despachos.despacho_fecha_aprobacion', '<=', $this->xhasta);
            } else {
                $query->whereDate('guias.guia_fecha_emision', '>=', $this->xdesde)
                    ->whereDate('guias.guia_fecha_emision', '<=', $this->xhasta);
            }

            $query->groupBy('despachos.id_despacho');
        })->select(
            DB::raw('MONTH(fecha_referencia) as mes'),
            DB::raw('YEAR(fecha_referencia) as anio'),
            DB::raw('SUM(CASE WHEN id_tipo_servicios = 1 OR (departamento_nombre IN ("LIMA", "CALLAO"))
                THEN despacho_costo_total ELSE 0 END) as flete_lima'),
            DB::raw('SUM(CASE WHEN departamento_nombre NOT IN ("LIMA", "CALLAO")
                THEN despacho_costo_total ELSE 0 END) as flete_provincia')
        )
            ->groupBy('mes', 'anio')
            ->get()
            ->keyBy(function($item) use ($mesesEspanol) {
                return $mesesEspanol[$item->mes] . '-' . substr($item->anio, 2, 2);
            });

        // Procesar datos para Flete Lima y Provincia
        $mesesFleteLimaProvincia = [];
        $fleteLima = [];
        $fleteProvincia = [];
        foreach ($mesesCompletos as $mes) {
            $mesesFleteLimaProvincia[] = $mes['key'];

            if (isset($fleteLimaProvinciaQuery[$mes['key']])) {
                $fleteLima[] = (float)$fleteLimaProvinciaQuery[$mes['key']]->flete_lima;
                $fleteProvincia[] = (float)$fleteLimaProvinciaQuery[$mes['key']]->flete_provincia;
            } else {
                $fleteLima[] = 0;
                $fleteProvincia[] = 0;
            }
        }

        // Asignar datos para los gráficos
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
