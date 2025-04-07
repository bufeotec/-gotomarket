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
    public $tipo_reporte = '';
    public $filteredData = [];
    public $searchExecuted = false;

    // Objetivos por zona
    public $objetivos = [
        'LOCAL' => 3,
        'PROVINCIA 1' => 6,
        'PROVINCIA 2' => 8
    ];

    // Clasificación de departamentos
    public $departamentos = [
        'CALLAO' => 'LOCAL',
        'LIMA' => 'LOCAL',
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

    public function buscar_reporte_tiempo(){
        $this->searchExecuted = true;

        if (empty($this->desde) || empty($this->hasta)) {
            $this->filteredData = [];
            return;
        }

        $query = DB::table('guias as g')
            ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
            ->join('despachos as d', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('programaciones as p', 'd.id_programacion', '=', 'p.id_programacion')
            ->select(
                'g.guia_fecha_emision as fecha_emision',
                'g.updated_at as fecha_entrega',
                'p.programacion_fecha as fec_programacion',
                'd.id_tipo_servicios as tipo_servicio',
                'g.guia_departamento as departamento',
                DB::raw('CASE
                WHEN d.id_tipo_servicios = 1 THEN "LOCAL"
                WHEN d.id_tipo_servicios = 2 AND g.guia_departamento IN ("' . implode('", "', array_keys($this->departamentos)) . '")
                THEN "' . $this->departamentos['LIMA'] . '"
                ELSE "PROVINCIA 2"
            END as zona'),
                DB::raw('DATEDIFF(g.updated_at, ' . ($this->tipo_reporte === 'emision' ? 'g.guia_fecha_emision' : 'p.programacion_fecha') . ') as dias_entrega'),
                DB::raw('CASE
                WHEN d.id_tipo_servicios = 1 AND DATEDIFF(g.updated_at, ' . ($this->tipo_reporte === 'emision' ? 'g.guia_fecha_emision' : 'p.programacion_fecha') . ') <= 3 THEN 1
                WHEN d.id_tipo_servicios = 2 AND g.guia_departamento IN ("' . implode('", "', array_filter(array_keys($this->departamentos), function($d) { return $this->departamentos[$d] === "PROVINCIA 1"; })) . '")
                AND DATEDIFF(g.updated_at, ' . ($this->tipo_reporte === 'emision' ? 'g.guia_fecha_emision' : 'p.programacion_fecha') . ') <= 6 THEN 1
                WHEN d.id_tipo_servicios = 2 AND g.guia_departamento IN ("' . implode('", "', array_filter(array_keys($this->departamentos), function($d) { return $this->departamentos[$d] === "PROVINCIA 2"; })) . '")
                AND DATEDIFF(g.updated_at, ' . ($this->tipo_reporte === 'emision' ? 'g.guia_fecha_emision' : 'p.programacion_fecha') . ') <= 8 THEN 1
                ELSE 0
            END as cumple_objetivo')
            )
            ->where('g.guia_estado_aprobacion', 8);

        // Filtros por fecha
        if ($this->tipo_reporte === 'emision') {
            $query->whereDate('g.guia_fecha_emision', '>=', $this->desde)
                ->whereDate('g.guia_fecha_emision', '<=', $this->hasta);
        } elseif ($this->tipo_reporte === 'programacion') {
            $query->whereDate('p.programacion_fecha', '>=', $this->desde)
                ->whereDate('p.programacion_fecha', '<=', $this->hasta);
        }

        $this->filteredData = $query->get();
        // Preparar datos para el gráfico si hay resultados
        $this->prepararDatosParaGrafico();
    }

    public function prepararDatosParaGrafico()
    {
        $mesesEspanol = [
            1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR',
            5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC'
        ];

        // 1. Procesamos los datos ya obtenidos en $this->filteredData
        $datosPorMes = [];
        $datosIndividualesPorZona = [
            'LOCAL' => [],
            'PROVINCIA 1' => [],
            'PROVINCIA 2' => []
        ];

        foreach ($this->filteredData as $item) {
            $fecha = $this->tipo_reporte === 'emision'
                ? $item->fecha_emision
                : $item->fec_programacion;

            $mesNumero = date('n', strtotime($fecha));
            $anio = date('y', strtotime($fecha));
            $mesKey = $mesesEspanol[$mesNumero] . '-' . $anio;
            $zona = $item->zona;

            // Almacenar datos individuales por zona
            if (!isset($datosIndividualesPorZona[$zona])) {
                $datosIndividualesPorZona[$zona] = [];
            }

            // Guardar el dato individual con información completa de la guía
            $datosIndividualesPorZona[$zona][] = [
                'fecha_emision' => $item->fecha_emision,
                'fecha_entrega' => $item->fecha_entrega,
                'fecha_programacion' => $item->fec_programacion,
                'dias_entrega' => $item->dias_entrega,
                'departamento' => $item->departamento,
                'tipo_servicio' => $item->tipo_servicio,
                'cumple_objetivo' => $item->cumple_objetivo,
                'mes' => $mesKey
            ];

            // Continuar con el procesamiento por mes como antes
            if (!isset($datosPorMes[$mesKey])) {
                $datosPorMes[$mesKey] = [
                    'mes' => $mesesEspanol[$mesNumero],
                    'LOCAL' => ['suma' => 0, 'count' => 0],
                    'PROVINCIA 1' => ['suma' => 0, 'count' => 0],
                    'PROVINCIA 2' => ['suma' => 0, 'count' => 0],
                ];
            }

            $datosPorMes[$mesKey][$zona]['suma'] += $item->dias_entrega;
            $datosPorMes[$mesKey][$zona]['count']++;
        }

        // 2. Si no hay datos, mostrar el mes actual con ceros
        if (empty($datosPorMes)) {
            $mesActualNumero = date('n');
            $anioActual = date('y');
            $mesKey = $mesesEspanol[$mesActualNumero] . '-' . $anioActual;
            $datosPorMes[$mesKey] = [
                'mes' => $mesesEspanol[$mesActualNumero],
                'LOCAL' => ['suma' => 0, 'count' => 0],
                'PROVINCIA 1' => ['suma' => 0, 'count' => 0],
                'PROVINCIA 2' => ['suma' => 0, 'count' => 0],
            ];
        }

        // 3. Ordenar los meses cronológicamente
        uksort($datosPorMes, function($a, $b) use ($mesesEspanol) {
            $mesA = substr($a, 0, 3);
            $mesB = substr($b, 0, 3);
            $mesNumA = array_search($mesA, $mesesEspanol);
            $mesNumB = array_search($mesB, $mesesEspanol);
            $anioA = substr($a, 4, 2);
            $anioB = substr($b, 4, 2);

            return strcmp("20{$anioA}-{$mesNumA}", "20{$anioB}-{$mesNumB}");
        });

        // 4. Preparar datos para el gráfico
        $meses = [];
        $tiempoLima = [];
        $tiempoProvincia = [];

        foreach ($datosPorMes as $mesKey => $data) {
            $meses[] = $data['mes'];

            // Tiempo Lima (Local)
            $countLocal = $data['LOCAL']['count'];
            $tiempoLima[] = $countLocal > 0
                ? round($data['LOCAL']['suma'] / $countLocal, 2) // 2 decimales como en la tabla
                : 0;

            // Tiempo Provincia (combinando Prov 1 y 2)
            $sumaProvincia = $data['PROVINCIA 1']['suma'] + $data['PROVINCIA 2']['suma'];
            $countProvincia = $data['PROVINCIA 1']['count'] + $data['PROVINCIA 2']['count'];
            $tiempoProvincia[] = $countProvincia > 0
                ? round($sumaProvincia / $countProvincia, 2) // 2 decimales como en la tabla
                : 0;
        }

        // Almacenar los datos individuales en una propiedad para que estén disponibles
        $this->datosIndividualesGuias = $datosIndividualesPorZona;

        // 5. Calcular métricas adicionales por zona
        $metricasPorZona = [];
        foreach ($datosIndividualesPorZona as $zona => $guias) {
            if (count($guias) > 0) {
                // Calcular el cumplimiento de objetivo por zona
                $totalGuias = count($guias);
                $guiasCumplenObjetivo = array_filter($guias, function ($guia) {
                    return $guia['cumple_objetivo'] == 1;
                });
                $porcentajeCumplimiento = $totalGuias > 0
                    ? round((count($guiasCumplenObjetivo) / $totalGuias) * 100, 2)
                    : 0;

                // Calcular tiempo promedio de entrega
                $tiempoTotal = array_sum(array_column($guias, 'dias_entrega'));
                $tiempoPromedio = $totalGuias > 0
                    ? round($tiempoTotal / $totalGuias, 2)
                    : 0;

                $metricasPorZona[$zona] = [
                    'total_guias' => $totalGuias,
                    'guias_cumplen_objetivo' => count($guiasCumplenObjetivo),
                    'porcentaje_cumplimiento' => $porcentajeCumplimiento,
                    'tiempo_promedio' => $tiempoPromedio
                ];
            } else {
                $metricasPorZona[$zona] = [
                    'total_guias' => 0,
                    'guias_cumplen_objetivo' => 0,
                    'porcentaje_cumplimiento' => 0,
                    'tiempo_promedio' => 0
                ];
            }
        }

        // Almacenar las métricas calculadas
        $this->metricasPorZona = $metricasPorZona;

        // 6. Enviar datos al frontend
        $this->dispatch('actualizarGraficoTiempoEntrega', [
            'meses' => $meses,
            'tiempo_lima' => $tiempoLima,
            'tiempo_provincia' => $tiempoProvincia,
            'metricas_por_zona' => $metricasPorZona
        ]);
    }

    public function render(){
        return view('livewire.programacioncamiones.reportetiempos');
    }

    public function exportarTiemposExcel(){
        try {
            if (!Gate::allows('exportar_tiempos_excel')) {
                session()->flash('error', 'No tiene permisos para generar este reporte.');
                return;
            }

            // Verificar parámetros requeridos
            if (empty($this->desde) || empty($this->hasta) || empty($this->tipo_reporte)) {
                session()->flash('error', 'Faltan parámetros para generar el reporte.');
                return;
            }

            $query = DB::table('guias as g')
                ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
                ->join('despachos as d', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('programaciones as p', 'd.id_programacion', '=', 'p.id_programacion')
                ->select(
                    'g.guia_fecha_emision as fecha_emision',
                    'g.guia_nro_doc as numero_guia',
                    'g.guia_nombre_cliente as cliente',
                    'p.programacion_fecha as fecha_despacho',
                    'g.updated_at as fecha_entrega',
                    DB::raw('DATEDIFF(g.updated_at, ' . ($this->tipo_reporte === 'emision' ? 'g.guia_fecha_emision' : 'p.programacion_fecha') . ') as dias_entrega'),
                    'g.guia_estado_aprobacion as estado',
                    'g.guia_departamento as departamento',
                    'g.guia_provincia as provincia',
                    'g.guia_direc_entrega as zona_despacho',
                    DB::raw('CASE
                    WHEN d.id_tipo_servicios = 1 THEN "LOCAL"
                    WHEN d.id_tipo_servicios = 2 AND g.guia_departamento IN ("' . implode('", "', array_keys($this->departamentos)) . '")
                    THEN "' . $this->departamentos['LIMA'] . '"
                    ELSE "PROVINCIA 2"
                END as zona'),
                    // Agregamos esta condición para filtrar solo las que cumplen
                    DB::raw('CASE
                    WHEN d.id_tipo_servicios = 1 AND DATEDIFF(g.updated_at, ' . ($this->tipo_reporte === 'emision' ? 'g.guia_fecha_emision' : 'p.programacion_fecha') . ') <= 3 THEN 1
                    WHEN d.id_tipo_servicios = 2 AND g.guia_departamento IN ("' . implode('", "', array_filter(array_keys($this->departamentos), function($d) { return $this->departamentos[$d] === "PROVINCIA 1"; })) . '")
                    AND DATEDIFF(g.updated_at, ' . ($this->tipo_reporte === 'emision' ? 'g.guia_fecha_emision' : 'p.programacion_fecha') . ') <= 6 THEN 1
                    WHEN d.id_tipo_servicios = 2 AND g.guia_departamento IN ("' . implode('", "', array_filter(array_keys($this->departamentos), function($d) { return $this->departamentos[$d] === "PROVINCIA 2"; })) . '")
                    AND DATEDIFF(g.updated_at, ' . ($this->tipo_reporte === 'emision' ? 'g.guia_fecha_emision' : 'p.programacion_fecha') . ') <= 8 THEN 1
                    ELSE 0
                END as cumple_objetivo')
                )
                ->where('g.guia_estado_aprobacion', 8)
                // Filtramos solo las guías que cumplen con el objetivo
                ->having('cumple_objetivo', '=', 1);

            // Filtros por fecha
            if ($this->tipo_reporte === 'emision') {
                $query->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta]);
            } else {
                $query->whereBetween('p.programacion_fecha', [$this->desde, $this->hasta]);
            }

            $filteredData = $query->get();

            if ($filteredData->isEmpty()) {
                session()->flash('error', 'No hay guías que cumplan con los objetivos de tiempo en el rango de fechas seleccionado.');
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
            $rangoFechas = 'Del ' . date('d/m/Y', strtotime($this->desde)) . ' al ' . date('d/m/Y', strtotime($this->hasta));
            $sheet->setCellValue('A2', $rangoFechas);
            $sheet->mergeCells('A2:J2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

            // ========== ENCABEZADOS ==========
            $headers = [
                'Fecha de Emisión de Guía',
                'N° Guía',
                'Cliente',
                'Fecha de Despacho de Guía',
                'Fecha de entrega de Guía',
                'DÍAS DE ENTREGA',
                'ESTADO DE OS',
                'DEPARTAMENTO',
                'PROVINCIA',
                'ZONA DE DESPACHO ASIGNADA'
            ];

            $sheet->fromArray($headers, null, 'A3');
            $sheet->getStyle('A3:J3')->getFont()->setBold(true);
            $sheet->getStyle('A3:J3')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A3:J3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D9E1F2');

            // ========== LLENAR DATOS ==========
            $row = 4;
            foreach ($filteredData as $item) {
                $sheet->setCellValue('A'.$row, $item->fecha_emision ? date('d/m/Y', strtotime($item->fecha_emision)) : '');
                $sheet->setCellValue('B'.$row, $item->numero_guia);
                $sheet->setCellValue('C'.$row, $item->cliente);
                $sheet->setCellValue('D'.$row, $item->fecha_despacho ? date('d/m/Y', strtotime($item->fecha_despacho)) : '');
                $sheet->setCellValue('E'.$row, $item->fecha_entrega ? date('d/m/Y', strtotime($item->fecha_entrega)) : '');
                $sheet->setCellValue('F'.$row, round($item->dias_entrega) . ' días');
                $sheet->setCellValue('G'.$row, 'ENTREGADO');
                $sheet->setCellValue('H'.$row, $item->departamento ?? 'S/N');
                $sheet->setCellValue('I'.$row, $item->provincia ?? 'S/N');
                $sheet->setCellValue('J'.$row, $item->zona_despacho ?? 'S/N');

                $sheet->getStyle('A'.$row.':J'.$row)->getAlignment()->setHorizontal('center');
                $row++;
            }

            // ========== ANCHO DE COLUMNA ==========
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
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

    public function getEstadoDespacho($estado){
        return match($estado) {
            1 => 'Pendiente',
            2 => 'Aprobado',
            3 => 'Liquidado',
            default => 'Desconocido'
        };
    }
}
