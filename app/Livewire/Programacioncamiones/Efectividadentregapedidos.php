<?php

namespace App\Livewire\Programacioncamiones;

use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Efectividadentregapedidos extends Component
{
    private $logs;
    public function __construct(){
        $this->logs = new Logs();
    }
    public $desde;
    public $hasta;
    public $tipo_reporte = ''; // Valor por defecto
    public $reporteData = [];
    public $reporteValoresData = [];
    public $mostrarResultados = false;
    public $datosMensualesGrafico = [];
    public $datosMensualesValorGrafico = [];

    public function mount(){
        $this->desde = date('Y-01-01');
        $this->hasta = date('Y-m-d');

        $this->datosMensualesGrafico = [
            'meses' => [],
            'total_despachados' => [],
            'envios_sin_devolucion' => [],
            'porcentaje_efectividad' => []
        ];

        $this->datosMensualesValorGrafico = [
            'meses' => [],
            'monto_total_despachados' => [],
            'monto_sin_devolucion' => [],
            'porcentaje_efectividad_valor' => []
        ];
    }

    public function buscar_entrega_pedido(){
        // Base query para despachos
        $despachosQuery = DB::table('despachos as d')
            ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
            ->whereNotNull('d.despacho_numero_correlativo');

        // Base query para valores monetarios
        $valoresQuery = DB::table('despachos as d')
            ->join('guias as g', function($join) {
                $join->on('g.id_guia', '=', DB::raw('(SELECT dv.id_guia FROM despacho_ventas dv WHERE dv.id_despacho = d.id_despacho LIMIT 1)'));
            })
            ->whereNotNull('d.despacho_numero_correlativo');

        // Aplicar filtro por tipo de fecha
        if ($this->tipo_reporte == '2') {
            // Filtrar por fecha de programación
            $despachosQuery->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                ->whereBetween('p.programacion_fecha', [$this->desde, $this->hasta]);

            $valoresQuery->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                ->whereBetween('p.programacion_fecha', [$this->desde, $this->hasta]);
        } else {
            // Filtrar por fecha de emisión (default)
            $despachosQuery->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta]);
            $valoresQuery->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta]);
        }

        // Total de pedidos despachados
        $totalDespachados = (clone $despachosQuery)
            ->distinct('d.id_despacho')
            ->count('d.id_despacho');

        // Despachos con devolución
        $despachosConDevolucion = (clone $despachosQuery)
            ->join('notas_creditos as nc', 'nc.not_cred_nro_doc_ref', '=', 'g.guia_nro_doc_ref')
            ->where('nc.not_cred_motivo', '1') // 1 = devolución
            ->distinct('d.id_despacho')
            ->count('d.id_despacho');

        $enviosSinDevolucion = $totalDespachados - $despachosConDevolucion;
        $indicadorEfectividad = ($totalDespachados > 0) ? round(($enviosSinDevolucion / $totalDespachados) * 100, 2) : 0;

        // Cálculo de valores monetarios - MODIFICADO para usar guia_importe_total
        $montoTotalDespachados = DB::table('despachos as d')
            ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
            ->whereNotNull('d.despacho_numero_correlativo')
            ->when($this->tipo_reporte == '2', function($query) {
                $query->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                    ->whereBetween('p.programacion_fecha', [$this->desde, $this->hasta]);
            }, function($query) {
                $query->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta]);
            })
            ->sum(DB::raw('g.guia_importe_total / 1.18'));

        $montoConDevolucion = DB::table('despachos as d')
            ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
            ->join('notas_creditos as nc', 'nc.not_cred_nro_doc_ref', '=', 'g.guia_nro_doc_ref')
            ->where('nc.not_cred_motivo', '1') // 1 = devolución
            ->whereNotNull('d.despacho_numero_correlativo')
            ->when($this->tipo_reporte == '2', function($query) {
                $query->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                    ->whereBetween('p.programacion_fecha', [$this->desde, $this->hasta]);
            }, function($query) {
                $query->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta]);
            })
            ->sum(DB::raw('g.guia_importe_total / 1.18'));

        $montoSinDevolucion = $montoTotalDespachados - $montoConDevolucion;
        $indicadorEfectividadValor = ($montoTotalDespachados > 0) ? round(($montoSinDevolucion / $montoTotalDespachados) * 100, 2) : 0;

        // Datos mensuales para gráficos
        $mesesEspanol = [
            1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR',
            5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC'
        ];

        $datosMensualesQuery = DB::table('despachos as d')
            ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
            ->select(
                DB::raw('MONTH(g.guia_fecha_emision) as mes'),
                DB::raw('YEAR(g.guia_fecha_emision) as anio'),
                DB::raw('COUNT(DISTINCT d.id_despacho) as total_despachados'),
                DB::raw('SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM notas_creditos nc
                    WHERE nc.not_cred_nro_doc_ref = g.guia_nro_doc_ref
                    AND nc.not_cred_motivo = 1
                ) THEN 1 ELSE 0 END) as despachos_con_devolucion')
            )
            ->whereNotNull('d.despacho_numero_correlativo');

        if ($this->tipo_reporte == '2') {
            $datosMensualesQuery->select(
                DB::raw('MONTH(p.programacion_fecha) as mes'),
                DB::raw('YEAR(p.programacion_fecha) as anio'),
                DB::raw('COUNT(DISTINCT d.id_despacho) as total_despachados'),
                DB::raw('SUM(CASE WHEN EXISTS (
            SELECT 1 FROM notas_creditos nc
            WHERE nc.not_cred_nro_doc_ref = g.guia_nro_doc_ref
            AND nc.not_cred_motivo = 1
        ) THEN 1 ELSE 0 END) as despachos_con_devolucion')
            )
                ->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                ->whereBetween('p.programacion_fecha', [$this->desde, $this->hasta])
                ->groupBy(DB::raw('YEAR(p.programacion_fecha)'), DB::raw('MONTH(p.programacion_fecha)'))
                ->orderBy(DB::raw('YEAR(p.programacion_fecha)'), 'asc')
                ->orderBy(DB::raw('MONTH(p.programacion_fecha)'), 'asc');
        } else {
            $datosMensualesQuery->select(
                DB::raw('MONTH(g.guia_fecha_emision) as mes'),
                DB::raw('YEAR(g.guia_fecha_emision) as anio'),
                DB::raw('COUNT(DISTINCT d.id_despacho) as total_despachados'),
                DB::raw('SUM(CASE WHEN EXISTS (
            SELECT 1 FROM notas_creditos nc
            WHERE nc.not_cred_nro_doc_ref = g.guia_nro_doc_ref
            AND nc.not_cred_motivo = 1
        ) THEN 1 ELSE 0 END) as despachos_con_devolucion')
            )
                ->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta])
                ->groupBy(DB::raw('YEAR(g.guia_fecha_emision)'), DB::raw('MONTH(g.guia_fecha_emision)'))
                ->orderBy(DB::raw('YEAR(g.guia_fecha_emision)'), 'asc')
                ->orderBy(DB::raw('MONTH(g.guia_fecha_emision)'), 'asc');
        }

        $datosMensuales = $datosMensualesQuery->get();

        // Procesar datos mensuales
        $meses = [];
        $totalDespachadosMensual = [];
        $enviosSinDevolucionMensual = [];
        $porcentajeEfectividadMensual = [];

        foreach ($datosMensuales as $dato) {
            $meses[] = $mesesEspanol[$dato->mes] . ' ' . $dato->anio;
            $totalDespachadosMensual[] = $dato->total_despachados;

            $sinDevolucion = $dato->total_despachados - $dato->despachos_con_devolucion;
            $enviosSinDevolucionMensual[] = $sinDevolucion;

            $porcentaje = ($dato->total_despachados > 0) ? round(($sinDevolucion / $dato->total_despachados) * 100, 2) : 0;
            $porcentajeEfectividadMensual[] = $porcentaje;
        }

        // Datos mensuales para valores
        $datosMensualesValoresQuery = DB::table('despachos as d')
            ->select(
                DB::raw('MONTH(MIN(g.guia_fecha_emision)) as mes'),
                DB::raw('YEAR(MIN(g.guia_fecha_emision)) as anio'),
                DB::raw('SUM(g.guia_importe_total / 1.18) as monto_total'),
                DB::raw('SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM notas_creditos nc
                    WHERE nc.not_cred_nro_doc_ref = g.guia_nro_doc_ref
                    AND nc.not_cred_motivo = 1
                ) THEN g.guia_importe_total / 1.18 ELSE 0 END) as monto_con_devolucion')
            )
            ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
            ->whereNotNull('d.despacho_numero_correlativo');

        if ($this->tipo_reporte == '2') {
            $datosMensualesValoresQuery->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                ->whereBetween('p.programacion_fecha', [$this->desde, $this->hasta]);
        } else {
            $datosMensualesValoresQuery->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta]);
        }

        $datosMensualesValores = $datosMensualesValoresQuery
            ->groupBy('d.id_despacho')
            ->orderBy('anio', 'asc')
            ->orderBy('mes', 'asc')
            ->get();

        // Agrupar por mes y año
        $datosAgrupadosPorMes = [];
        foreach ($datosMensualesValores as $dato) {
            $mesAnio = $dato->anio . '-' . str_pad($dato->mes, 2, '0', STR_PAD_LEFT);

            if (!isset($datosAgrupadosPorMes[$mesAnio])) {
                $datosAgrupadosPorMes[$mesAnio] = [
                    'monto_total' => 0,
                    'monto_con_devolucion' => 0
                ];
            }

            $datosAgrupadosPorMes[$mesAnio]['monto_total'] += $dato->monto_total;
            $datosAgrupadosPorMes[$mesAnio]['monto_con_devolucion'] += $dato->monto_con_devolucion;
        }

        // Procesar para el gráfico de valores
        $mesesValores = [];
        $montoTotalMensual = [];
        $montoSinDevolucionMensual = [];
        $porcentajeEfectividadValorMensual = [];

        foreach ($datosAgrupadosPorMes as $mesAnio => $datos) {
            list($anio, $mes) = explode('-', $mesAnio);
            $mesesValores[] = $mesesEspanol[(int)$mes] . ' ' . $anio;
            $montoTotalMensual[] = $datos['monto_total'];

            $sinDevolucion = $datos['monto_total'] - $datos['monto_con_devolucion'];
            $montoSinDevolucionMensual[] = $sinDevolucion;

            $porcentaje = ($datos['monto_total'] > 0) ? round(($sinDevolucion / $datos['monto_total']) * 100, 2) : 0;
            $porcentajeEfectividadValorMensual[] = $porcentaje;
        }

        // Asignar resultados
        $this->reporteData = [
            'total_despachados' => $totalDespachados,
            'despachos_con_devolucion' => $despachosConDevolucion,
            'envios_sin_devolucion' => $enviosSinDevolucion,
            'indicador_efectividad' => $indicadorEfectividad
        ];

        $this->reporteValoresData = [
            'monto_total_despachados' => $montoTotalDespachados,
            'monto_con_devolucion' => $montoConDevolucion,
            'monto_sin_devolucion' => $montoSinDevolucion,
            'indicador_efectividad_valor' => $indicadorEfectividadValor
        ];

        $this->datosMensualesGrafico = [
            'meses' => $meses,
            'total_despachados' => $totalDespachadosMensual,
            'envios_sin_devolucion' => $enviosSinDevolucionMensual,
            'porcentaje_efectividad' => $porcentajeEfectividadMensual
        ];

        $this->datosMensualesValorGrafico = [
            'meses' => $mesesValores,
            'monto_total_despachados' => $montoTotalMensual,
            'monto_sin_devolucion' => $montoSinDevolucionMensual,
            'porcentaje_efectividad_valor' => $porcentajeEfectividadValorMensual
        ];

        $this->mostrarResultados = true;

        $this->dispatch('datosActualizados');
        $this->dispatch('actualizarGraficoDespachos', $this->datosMensualesGrafico);
        $this->dispatch('actualizarGraficoValor', $this->datosMensualesValorGrafico);
    }

    public function render(){
        return view('livewire.programacioncamiones.efectividadentregapedidos', [
            'reporte' => $this->reporteData,
            'reporteValores' => $this->reporteValoresData,
            'mostrarResultados' => $this->mostrarResultados
        ]);
    }

    public function generar_excel_entrega_pedidos(){
        try {
            if (!Gate::allows('generar_excel_entrega_pedidos')) {
                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
                return;
            }

            // Consulta para obtener las guías con todos los datos requeridos
            $query = DB::table('guias as g')
                ->join('despacho_ventas as dv', 'dv.id_guia', '=', 'g.id_guia')
                ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                ->leftJoin('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                ->leftJoin('historial_guias as hg', function($join) {
                    $join->on('hg.id_guia', '=', 'g.id_guia')
                        ->where('hg.historial_guia_estado_aprobacion', '=', 8);
                })
                ->leftJoin('notas_creditos as nc', function($join) {
                    $join->on('nc.not_cred_nro_doc_ref', '=', 'g.guia_nro_doc_ref')
                        ->where('nc.not_cred_motivo', '=', 1);
                })
                ->whereNotNull('d.despacho_numero_correlativo')
                ->select(
                    'g.guia_fecha_emision',
                    'g.guia_nro_doc',
                    'g.guia_nombre_cliente',
                    'g.guia_nro_doc_ref',
                    'g.guia_importe_total',
                    'd.despacho_numero_correlativo',
                    'd.despacho_estado_aprobacion',
                    'p.programacion_fecha as fecha_despacho',
                    'hg.historial_guia_fecha_hora as fecha_entrega',
                    'g.guia_departamento',
                    'g.guia_provincia',
                    'g.guia_direc_entrega',
                    DB::raw('CASE WHEN nc.id_not_cred IS NOT NULL THEN 1 ELSE 0 END as tiene_nc'),
                    DB::raw('CASE WHEN nc.id_not_cred IS NOT NULL THEN g.guia_importe_total ELSE 0 END as monto_nc')
                );

            // Aplicar filtro por tipo de fecha
            if ($this->tipo_reporte == '2') {
                $query->whereBetween('p.programacion_fecha', [$this->desde, $this->hasta]);
            } else {
                $query->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta]);
            }

            $guias = $query->get();

            // Crear el archivo Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $headers = [
                'Fecha de Emisión de Guía',
                'N° Guía',
                'Cliente',
                'N° Factura / Boleta',
                'Valor Venta sin IGV',
                'N° OS',
                'Estado de OS',
                'Fecha de Despacho de Guía',
                'Fecha de entrega de Guía',
                'Cantidad NC con Motivo 1',
                'Monto NC - Motivo 1',
                'DEPARTAMENTO',
                'PROVINCIA',
                'ZONA DE DESPACHO ASIGNADA'
            ];

            // Escribir encabezados
            $sheet->fromArray($headers, null, 'A1');

            // Estilo para los encabezados
            $headerStyle = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE699']]
            ];
            $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

            // Llenar datos
            $row = 2;
            foreach ($guias as $guia) {
                $sheet->setCellValue('A'.$row, $guia->guia_fecha_emision);
                $sheet->setCellValue('B'.$row, $guia->guia_nro_doc);
                $sheet->setCellValue('C'.$row, $guia->guia_nombre_cliente);
                $sheet->setCellValue('D'.$row, $guia->guia_nro_doc_ref);

                // Valor sin IGV (dividir entre 1.18)
                $valorSinIgv = $guia->guia_importe_total / 1.18;
                $sheet->setCellValue('E'.$row, $valorSinIgv);

                $sheet->setCellValue('F'.$row, $guia->despacho_numero_correlativo);

                // Mapear estado de OS
                $estado = match($guia->despacho_estado_aprobacion) {
                    0 => 'Pendiente',
                    1 => 'Aprobado',
                    2 => 'En camino',
                    3 => 'Culminado',
                    4 => 'Rechazado',
                    default => 'Desconocido'
                };
                $sheet->setCellValue('G'.$row, $estado);

                $sheet->setCellValue('H'.$row, $guia->fecha_despacho);
                $sheet->setCellValue('I'.$row, $guia->fecha_entrega ? date('Y-m-d', strtotime($guia->fecha_entrega)) : '');
                $sheet->setCellValue('J'.$row, $guia->tiene_nc);
                $sheet->setCellValue('K'.$row, $guia->tiene_nc ? ($guia->monto_nc / 1.18) : 0); // Monto sin IGV
                $sheet->setCellValue('L'.$row, $guia->guia_departamento);
                $sheet->setCellValue('M'.$row, $guia->guia_provincia);
                $sheet->setCellValue('N'.$row, $guia->guia_direc_entrega);

                // Formato para valores numéricos
                $sheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('0');

                $row++;
            }

            // Autoajustar columnas
            foreach(range('A','N') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Formatear el nombre del archivo Excel
            $nombre_excel = sprintf(
                "reporte_por_guias_%s_a_%s.xlsx",
                date('d-m-Y', strtotime($this->desde)),
                date('d-m-Y', strtotime($this->hasta))
            );

            // Descargar el archivo
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
}
