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
    public $reporteData = [];
    public $reporteValoresData = [];
    public $mostrarResultados = false;

    // Añadir propiedades para los datos de los gráficos
    public $datosMensualesGrafico = [];
    public $datosMensualesValorGrafico = [];

    public function mount(){
        $this->desde = date('Y-01-01');
        $this->hasta = date('Y-m-d');

        // Inicializar las estructuras de datos para evitar errores
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
        // Total de pedidos despachados (despachos con correlativo)
        $totalDespachados = DB::table('despachos as d')
            ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
            ->whereNotNull('d.despacho_numero_correlativo')
            ->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta])
            ->distinct('d.id_despacho')
            ->count('d.id_despacho');

        // Despachos con devolución
        $despachosConDevolucion = DB::table('despachos as d')
            ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
            ->join('notas_creditos as nc', 'nc.not_cred_nro_doc_ref', '=', 'g.guia_nro_doc_ref')
            ->whereNotNull('d.despacho_numero_correlativo')
            ->where('nc.not_cred_motivo', '1') // 1 = devolución
            ->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta])
            ->distinct('d.id_despacho')
            ->count('d.id_despacho');

        // Envíos sin devolución
        $enviosSinDevolucion = $totalDespachados - $despachosConDevolucion;

        // Indicador de efectividad
        $indicadorEfectividad = ($totalDespachados > 0)
            ? round(($enviosSinDevolucion / $totalDespachados) * 100, 2)
            : 0;

        // Cálculo de valores monetarios
        $montoTotalDespachados = DB::table('despachos as d')
            ->join('guias as g', function($join) {
                $join->on('g.id_guia', '=', DB::raw('(SELECT dv.id_guia FROM despacho_ventas dv WHERE dv.id_despacho = d.id_despacho LIMIT 1)'));
            })
            ->whereNotNull('d.despacho_numero_correlativo')
            ->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta])
            ->sum('d.despacho_costo_total');

        $montoConDevolucion = DB::table('despachos as d')
            ->join('guias as g', function($join) {
                $join->on('g.id_guia', '=', DB::raw('(SELECT dv.id_guia FROM despacho_ventas dv WHERE dv.id_despacho = d.id_despacho LIMIT 1)'));
            })
            ->join('notas_creditos as nc', 'nc.not_cred_nro_doc_ref', '=', 'g.guia_nro_doc_ref')
            ->whereNotNull('d.despacho_numero_correlativo')
            ->where('nc.not_cred_motivo', '1') // 1 = devolución
            ->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta])
            ->sum('d.despacho_costo_total');

        $montoSinDevolucion = $montoTotalDespachados - $montoConDevolucion;

        $indicadorEfectividadValor = ($montoTotalDespachados > 0)
            ? round(($montoSinDevolucion / $montoTotalDespachados) * 100, 2)
            : 0;

        $mesesEspanol = [
            1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR',
            5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC'
        ];

        $datosMensuales = DB::table('despachos as d')
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
            ->whereNotNull('d.despacho_numero_correlativo')
            ->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta])
            ->groupBy(DB::raw('YEAR(g.guia_fecha_emision)'), DB::raw('MONTH(g.guia_fecha_emision)'))
            ->orderBy(DB::raw('YEAR(g.guia_fecha_emision)'), 'asc')
            ->orderBy(DB::raw('MONTH(g.guia_fecha_emision)'), 'asc')
            ->get();

        // Procesar datos mensuales
        $meses = [];
        $totalDespachadosMensual = [];
        $enviosSinDevolucionMensual = [];
        $porcentajeEfectividadMensual = [];

        foreach ($datosMensuales as $dato) {
            $fecha = Carbon::createFromDate($dato->anio, $dato->mes, 1);
            $meses[] = $mesesEspanol[$dato->mes] . ' ' . $dato->anio;

            $totalDespachadosMensual[] = $dato->total_despachados;
            $sinDevolucion = $dato->total_despachados - $dato->despachos_con_devolucion;
            $enviosSinDevolucionMensual[] = $sinDevolucion;

            $porcentaje = ($dato->total_despachados > 0)
                ? round(($sinDevolucion / $dato->total_despachados) * 100, 2)
                : 0;
            $porcentajeEfectividadMensual[] = $porcentaje;
        }

        $datosMensualesValores = DB::table('despachos as d')
            ->select(
                DB::raw('MONTH(MIN(g.guia_fecha_emision)) as mes'),
                DB::raw('YEAR(MIN(g.guia_fecha_emision)) as anio'),
                DB::raw('d.id_despacho'), // Necesario para el GROUP BY
                DB::raw('MAX(d.despacho_costo_total) as monto_total'), // Usamos MAX ya que es el mismo valor para cada despacho
                DB::raw('MAX(CASE WHEN EXISTS (
            SELECT 1 FROM despacho_ventas dv
            JOIN guias g2 ON g2.id_guia = dv.id_guia
            JOIN notas_creditos nc ON nc.not_cred_nro_doc_ref = g2.guia_nro_doc_ref
            WHERE dv.id_despacho = d.id_despacho
            AND nc.not_cred_motivo = 1
        ) THEN d.despacho_costo_total ELSE 0 END) as monto_con_devolucion')
            )
            ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
            ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
            ->whereNotNull('d.despacho_numero_correlativo')
            ->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta])
            ->groupBy('d.id_despacho') // Agrupamos por despacho
            ->orderBy('anio', 'asc')
            ->orderBy('mes', 'asc')
            ->get();

        // Agrupamos por mes y año
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

        // Procesamos para el gráfico
        $mesesValores = [];
        $montoTotalMensual = [];
        $montoSinDevolucionMensual = [];
        $porcentajeEfectividadValorMensual = [];

        foreach ($datosAgrupadosPorMes as $mesAnio => $datos) {
            list($anio, $mes) = explode('-', $mesAnio);
            $fecha = Carbon::createFromDate($anio, $mes, 1);

            $mesesValores[] = $mesesEspanol[(int)$mes] . ' ' . $anio;
            $montoTotalMensual[] = $datos['monto_total'];

            $sinDevolucion = $datos['monto_total'] - $datos['monto_con_devolucion'];
            $montoSinDevolucionMensual[] = $sinDevolucion;

            $porcentaje = ($datos['monto_total'] > 0)
                ? round(($sinDevolucion / $datos['monto_total']) * 100, 2)
                : 0;
            $porcentajeEfectividadValorMensual[] = $porcentaje;
        }

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

        // Guardar los datos para los gráficos en propiedades de la clase
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
        $this->dispatch('actualizarGraficoDespachos', $this->datosMensualesGrafico[0] ?? $this->datosMensualesGrafico);
        $this->dispatch('actualizarGraficoValor', $this->datosMensualesValorGrafico[0] ?? $this->datosMensualesValorGrafico);
    }

    public function render()
    {
        $view = view('livewire.programacioncamiones.efectividadentregapedidos', [
            'reporte' => $this->reporteData,
            'reporteValores' => $this->reporteValoresData,
            'mostrarResultados' => $this->mostrarResultados
        ]);

        // Emitir eventos cuando se renderiza el componente si hay datos disponibles
        if ($this->mostrarResultados) {
            $this->dispatch('actualizarGraficoDespachos', $this->datosMensualesGrafico[0] ?? $this->datosMensualesGrafico);
            $this->dispatch('actualizarGraficoValor', $this->datosMensualesValorGrafico[0] ?? $this->datosMensualesValorGrafico);
        }

        return $view;
    }

    public function generar_excel_entrega_pedidos(){
        try {
            if (!Gate::allows('generar_excel_entrega_pedidos')) {
                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
                return;
            }

            // Primero obtener los despachos únicos
            $despachos = DB::table('despachos as d')
                ->whereNotNull('d.despacho_numero_correlativo')
                ->whereBetween('d.despacho_fecha_aprobacion', [$this->desde, $this->hasta])
                ->select('d.id_despacho')
                ->get()
                ->pluck('id_despacho');

            // Luego obtener los datos agrupados por despacho
            $datos = DB::table('despachos as d')
                ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                ->leftJoin('notas_creditos as nc', 'nc.not_cred_nro_doc_ref', '=', 'g.guia_nro_doc_ref')
                ->whereIn('d.id_despacho', $despachos)
                ->select(
                    'd.id_despacho',
                    'd.despacho_numero_correlativo',
                    'd.despacho_estado_aprobacion',
                    'd.despacho_fecha_aprobacion',
                    'd.despacho_costo_total',
                    DB::raw('MIN(g.guia_fecha_emision) as primera_fecha_emision'),
                    DB::raw('GROUP_CONCAT(DISTINCT g.guia_nro_doc SEPARATOR ", ") as guias'),
                    DB::raw('GROUP_CONCAT(DISTINCT g.guia_nombre_cliente SEPARATOR ", ") as clientes'),
                    DB::raw('GROUP_CONCAT(DISTINCT g.guia_nro_doc_ref SEPARATOR ", ") as facturas'),
                    DB::raw('GROUP_CONCAT(DISTINCT g.guia_departamento SEPARATOR ", ") as departamentos'),
                    DB::raw('GROUP_CONCAT(DISTINCT g.guia_provincia SEPARATOR ", ") as provincias'),
                    DB::raw('SUM(CASE WHEN nc.not_cred_motivo = 1 THEN 1 ELSE 0 END) as cantidad_nc')
                )
                ->groupBy('d.id_despacho', 'd.despacho_numero_correlativo', 'd.despacho_estado_aprobacion',
                    'd.despacho_fecha_aprobacion', 'd.despacho_costo_total')
                ->get();

            // Crear el archivo Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados directamente en celdas
            $sheet->setCellValue('A1', 'Fecha de Emisión de Guía');
            $sheet->setCellValue('B1', 'N° Guía(s)');
            $sheet->setCellValue('C1', 'Cliente(s)');
            $sheet->setCellValue('D1', 'N° Factura(s)/Boleta(s)');
            $sheet->setCellValue('E1', 'Valor Venta sin IGV');
            $sheet->setCellValue('F1', 'N° OS');
            $sheet->setCellValue('G1', 'Estado de OS');
            $sheet->setCellValue('H1', 'Fecha de Despacho');
            $sheet->setCellValue('I1', 'Fecha de Entrega');
            $sheet->setCellValue('J1', 'Cantidad NC - Motivo 1');
            $sheet->setCellValue('K1', 'Departamento');
            $sheet->setCellValue('L1', 'Provincia');
            $sheet->setCellValue('M1', 'Zona de despacho asignada');

            // Estilo para los encabezados
            $headerStyle = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE699']]
            ];
            $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

            // Llenar datos
            $row = 2;
            foreach ($datos as $d) {
                $sheet->setCellValue('A'.$row, $d->primera_fecha_emision);
                $sheet->setCellValue('B'.$row, $d->guias);
                $sheet->setCellValue('C'.$row, $d->clientes);
                $sheet->setCellValue('D'.$row, $d->facturas);
                $sheet->setCellValue('E'.$row, $d->despacho_costo_total);
                $sheet->setCellValue('F'.$row, $d->despacho_numero_correlativo);

                // Mapear estado de OS
                $estado = match($d->despacho_estado_aprobacion) {
                    0 => 'Pendiente',
                    1 => 'Aprobado',
                    2 => 'En camino',
                    3 => 'Culminado',
                    4 => 'Rechazado',
                    default => 'Desconocido'
                };
                $sheet->setCellValue('G'.$row, $estado);

                // Para departamento y provincia, tomamos el primero si hay múltiples
                $departamentos = explode(", ", $d->departamentos);
                $provincias = explode(", ", $d->provincias);

                $sheet->setCellValue('H'.$row, $d->despacho_fecha_aprobacion);
                $sheet->setCellValue('I'.$row, '');
                $sheet->setCellValue('J'.$row, $d->cantidad_nc);
                $sheet->setCellValue('K'.$row, $departamentos[0] ?? '');
                $sheet->setCellValue('L'.$row, $provincias[0] ?? '');
                $sheet->setCellValue('M'.$row, '');

                // Formato para valores numéricos
                $sheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('0');

                $row++;
            }

            // Autoajustar columnas
            foreach(range('A','M') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Formatear el nombre del archivo Excel
            $nombre_excel = sprintf(
                "reporte_por_despachos_%s_a_%s.xlsx",
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
