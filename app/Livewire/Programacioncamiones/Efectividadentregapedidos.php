<?php

namespace App\Livewire\Programacioncamiones;

use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Guia;
use Illuminate\Support\Facades\DB;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Efectividadentregapedidos extends Component
{
    use WithPagination, WithoutUrlPagination;

    private $logs;
    private $guia;

    public function __construct()
    {
        $this->logs = new Logs();
        $this->guia = new Guia();
    }

    // Propiedades públicas
    public $desde;
    public $hasta;
    public $tipo_reporte = '';
    public $searchExecuted = false;

    // Datos para reportes
    public $reporteData = [];
    public $reporteValoresData = [];

    // Datos para gráficos
    public $datosMensualesGrafico = [];
    public $datosMensualesValorGrafico = [];

    // Meses en español para gráficos
    public $mesesEspanol = [
        1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR',
        5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
        9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC'
    ];

    public function mount(){
        $this->desde = date('Y-01-01');
        $this->hasta = date('Y-m-d');
    }

    public function buscar_entrega_pedido(){
        try {
            $this->validate([
                'desde' => 'required|date',
                'hasta' => 'required|date|after_or_equal:desde',
            ], [
                'desde.required' => 'La fecha de inicio es obligatoria.',
                'hasta.required' => 'La fecha de fin es obligatoria.',
                'hasta.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            ]);

            $this->searchExecuted = true;
            // Obtener datos principales
            $this->obtenerDatosReporte();
            // Obtener datos para gráficos
            $this->obtenerDatosGraficos();

            // Disparar eventos para actualizar gráficos
            $this->dispatch('datosActualizados');
            $this->dispatch('actualizarGraficoDespachos', $this->datosMensualesGrafico);
            $this->dispatch('actualizarGraficoValor', $this->datosMensualesValorGrafico);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
        }
    }

    public function obtenerDatosReporte(){
        // Obtener datos de cantidad
        $datosCantidad = $this->guia->obtener_datos_efectividad_pedidos($this->tipo_reporte, $this->desde, $this->hasta, 'cantidad');

        // Obtener datos de valor
        $datosValor = $this->guia->obtener_datos_efectividad_pedidos($this->tipo_reporte, $this->desde, $this->hasta, 'valor');

        $this->reporteData = [
            'total_despachados' => $datosCantidad['total'] ?? 0,
            'despachos_con_devolucion' => $datosCantidad['con_devolucion'] ?? 0,
            'envios_sin_devolucion' => $datosCantidad['sin_devolucion'] ?? 0,
            'indicador_efectividad' => $datosCantidad['porcentaje_efectividad'] ?? 0
        ];

        $this->reporteValoresData = [
            'monto_total_despachados' => $datosValor['total'] ?? 0,
            'monto_con_devolucion' => $datosValor['con_devolucion'] ?? 0,
            'monto_sin_devolucion' => $datosValor['sin_devolucion'] ?? 0,
            'indicador_efectividad_valor' => $datosValor['porcentaje_efectividad'] ?? 0
        ];
    }

    public function obtenerDatosGraficos(){
        // Obtener datos mensuales para cantidad
        $datosMensuales = $this->guia->obtener_datos_mensuales_efectividad_pedidos($this->tipo_reporte, $this->desde, $this->hasta, 1);
        $this->procesarDatosGrafico($datosMensuales, 1);

        // Obtener datos mensuales para valor
        $datosMensualesValor = $this->guia->obtener_datos_mensuales_efectividad_pedidos($this->tipo_reporte, $this->desde, $this->hasta, 2);
        $this->procesarDatosGrafico($datosMensualesValor, 2);
    }

    public function procesarDatosGrafico($datos, $tipo){
        // Obtener el mes y año actual
        $mesActual = (int)date('n');
        $anioActual = date('Y');

        // Crear un array con todos los meses
        $todosMeses = [];
        for ($i = 1; $i <= $mesActual; $i++) {
            $todosMeses[$i] = [
                'mes' => $i,
                'anio' => $anioActual,
                'total' => 0,
                'con_devolucion' => 0
            ];
        }
        // Combinar con los datos reales
        foreach ($datos as $dato) {
            $todosMeses[$dato->mes] = [
                'mes' => $dato->mes,
                'anio' => $dato->anio,
                'total' => $dato->total,
                'con_devolucion' => $dato->con_devolucion
            ];
        }
        $meses = [];
        $totales = [];
        $sinDevolucion = [];
        $porcentajes = [];

        foreach ($todosMeses as $mesData) {
            $meses[] = $this->mesesEspanol[$mesData['mes']] . ' ' . $mesData['anio'];
            $totales[] = $mesData['total'];
            $sinDevolucion[] = $mesData['total'] - $mesData['con_devolucion'];

            $porcentaje = ($mesData['total'] > 0)
                ? round(($mesData['total'] - $mesData['con_devolucion']) / $mesData['total'] * 100, 2)
                : 0;
            $porcentajes[] = $porcentaje;
        }

        if ($tipo === 1) {
            $this->datosMensualesGrafico = [
                'meses' => $meses,
                'total_despachados' => $totales,
                'envios_sin_devolucion' => $sinDevolucion,
                'porcentaje_efectividad' => $porcentajes
            ];
        } else {
            $this->datosMensualesValorGrafico = [
                'meses' => $meses,
                'monto_total_despachados' => $totales,
                'monto_sin_devolucion' => $sinDevolucion,
                'porcentaje_efectividad_valor' => $porcentajes
            ];
        }
    }

    public function render(){
        return view('livewire.programacioncamiones.efectividadentregapedidos');
    }

    public function generar_excel_entrega_pedidos(){
        try {
            if (!Gate::allows('generar_excel_entrega_pedidos')) {
                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
                return;
            }

            // Consulta optimizada para evitar duplicados
            $query = DB::table('guias as g')
                ->join('despacho_ventas as dv', 'dv.id_guia', '=', 'g.id_guia')
                ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                ->leftJoin('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                ->leftJoin(DB::raw('(SELECT id_guia, MAX(historial_guia_fecha_hora) as fecha_entrega
                               FROM historial_guias
                               WHERE historial_guia_estado_aprobacion = 8
                               GROUP BY id_guia) as hg'),
                    'hg.id_guia', '=', 'g.id_guia')
                ->leftJoin(DB::raw('(SELECT not_cred_nro_doc_ref,
                               COUNT(id_not_cred) as count_nc,
                               SUM(not_cred_importe_total) as monto_nc
                               FROM notas_creditos
                               WHERE not_cred_motivo = 1
                               GROUP BY not_cred_nro_doc_ref) as nc'),
                    'nc.not_cred_nro_doc_ref', '=', 'g.guia_nro_doc_ref')
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
                    'hg.fecha_entrega',
                    'g.guia_departamento',
                    'g.guia_provincia',
                    'g.guia_direc_entrega',
                    DB::raw('COALESCE(nc.count_nc, 0) as tiene_nc'),
                    DB::raw('COALESCE(nc.monto_nc, 0) as monto_nc')
                )
                ->groupBy(
                    'g.id_guia',
                    'g.guia_fecha_emision',
                    'g.guia_nro_doc',
                    'g.guia_nombre_cliente',
                    'g.guia_nro_doc_ref',
                    'g.guia_importe_total',
                    'd.despacho_numero_correlativo',
                    'd.despacho_estado_aprobacion',
                    'p.programacion_fecha',
                    'hg.fecha_entrega',
                    'g.guia_departamento',
                    'g.guia_provincia',
                    'g.guia_direc_entrega',
                    'nc.count_nc',
                    'nc.monto_nc'
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
