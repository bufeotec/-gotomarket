<?php

namespace App\Livewire\Programacioncamiones;

use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Guia;
use App\Models\General;
use Illuminate\Support\Facades\DB;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class Efectividadentregapedidos extends Component
{
    use WithPagination, WithoutUrlPagination;

    private $logs;
    private $guia;
    private $general;
    public function __construct(){
        $this->logs = new Logs();
        $this->guia = new Guia();
        $this->general = new General();
    }
    public $desde;
    public $hasta;
    public $tipo_reporte = '';
    public $reporteData = [];

    public function mount(){
        $this->desde = date('Y-01-01');
        $this->hasta = date('Y-m-d');
    }

    public function render(){
        return view('livewire.programacioncamiones.efectividadentregapedidos');
    }

    public function buscar_entrega_pedido(){

        $this->validate([
            'tipo_reporte' => 'required|in:1,2',
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:xdesde',
        ], [
            'tipo_reporte.required' => 'El tipo de reporte es obligatorio.',
            'tipo_reporte.in' => 'El tipo de reporte seleccionado no es válido.',

            'desde.required' => 'La fecha de inicio es obligatoria.',
            'desde.date' => 'La fecha de inicio no es válida.',

            'hasta.required' => 'La fecha de fin es obligatoria.',
            'hasta.date' => 'La fecha de fin no es válida.',
            'hasta.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ]);

        $this->reporteData = $this->guia->obtener_datos_total_efectividad($this->tipo_reporte, $this->desde, $this->hasta);

        // Verificación más completa de la estructura de datos
        if (!empty($this->reporteData) &&
            isset($this->reporteData['grafico_meses']) &&
            isset($this->reporteData['grafico_despachos']) &&
            isset($this->reporteData['grafico_valores'])) {

            $this->dispatch('actualizarGraficosEfectividad',
                // Enviar directamente el objeto, no dentro de un array
                [
                    'meses' => $this->reporteData['grafico_meses'],
                    'pedidosEntregados' => $this->reporteData['grafico_despachos']['pedidos_entregados'] ?? [],
                    'entregadosSinDevolucion' => $this->reporteData['grafico_despachos']['entregados_sin_devolucion'] ?? [],
                    'efectividad' => $this->reporteData['grafico_despachos']['efectividad'] ?? [],
                    'solesEntregados' => $this->reporteData['grafico_valores']['soles_entregados'] ?? [],
                    'solesSinDevolucion' => $this->reporteData['grafico_valores']['soles_sin_devolucion'] ?? [],
                    'efectividadValor' => $this->reporteData['grafico_valores']['efectividad_valor'] ?? []
                ]
            );
        }
    }

    public function generar_excel_entrega_pedidos(){
        try {
            if (!Gate::allows('generar_excel_entrega_pedidos')) {
                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
                return;
            }

//            $despachos_g = DB::table('programaciones as p')
//                ->join('despachos as d', 'd.id_programacion', '=', 'p.id_programacion')
//                ->whereBetween('d.despacho_fecha_aprobacion', ['2025-01-01', '2027-08-31'])
//                ->where('d.id_tipo_servicios', 1)
//                ->where('d.despacho_estado_aprobacion', '<>', 4)
//                ->get();
//
//            // Crear archivo general
//            $spreadsheet = new Spreadsheet();
//            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
//
//            // HOJA 1: Resumen Local
//            $sheet1 = $spreadsheet->getActiveSheet();
//            $sheet1->setTitle('Flete Local');
//
//            // Encabezados
//            $headers = [
//                'N° OS Local',
//                'Fecha OS Local',
//                'Flete OS Local',
//                'Valor Transportado por OS Local',
//                'Peso Transportado por OS Local',
//            ];
//
//            $sheet1->fromArray($headers, null, 'A1');
//            $headerStyle = [
//                'font' => ['bold' => true],
//                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                'fill' => [
//                    'fillType' => Fill::FILL_SOLID,
//                    'startColor' => ['rgb' => 'FFE699']
//                ]
//            ];
//            $sheet1->getStyle('A1:E1')->applyFromArray($headerStyle);
//            $sheet1->getColumnDimension('A')->setWidth(20);
//            $sheet1->getColumnDimension('B')->setWidth(20);
//            $sheet1->getColumnDimension('C')->setWidth(18);
//            $sheet1->getColumnDimension('D')->setWidth(35);
//            $sheet1->getColumnDimension('E')->setWidth(30);
//
//            $row = 2;
//            foreach ($despachos_g as $g) {
//                $sheet1->setCellValue('A' . $row, $g->despacho_numero_correlativo);
//                $sheet1->setCellValue('B' . $row, date('d/m/Y', strtotime($g->programacion_fecha))); //programado
//
//                $liquidado = DB::table('liquidacion_gastos as lg')
//                    ->join('liquidacion_detalles as ld', 'ld.id_liquidacion_detalle', '=', 'lg.id_liquidacion_detalle')
//                    ->where('lg.liquidacion_gasto_concepto', '=', 'costo_flete')
//                    ->where('ld.id_despacho', '=', $g->id_despacho)
//                    ->first();
//
//                if ($liquidado) {
//                    $sheet1->setCellValue('C' . $row, $liquidado->liquidacion_gasto_monto);
//                } else {
//                    $sheet1->setCellValue('C' . $row, $g->despacho_costo_total);
//                }
//
//                $detalle = DB::table('despacho_ventas as dv')
//                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
//                    ->where('dv.id_despacho', '=', $g->id_despacho)
//                    ->get();
//
//                $sum = 0;
//                foreach ($detalle as $d) {
//                    if (in_array($d->guia_estado_aprobacion, [11, 12])) {
//                        $sum += 0;
//                    } else {
//                        $sum += $d->guia_importe_total_sin_igv;
//                    }
//                }
//
//                $sheet1->setCellValue('D' . $row, $sum);
//                $sheet1->setCellValue('E' . $row, $g->despacho_peso);
//                $row++;
//            }
//
//            // HOJA 2: Resumen Nacional
//            $sheet2 = new Worksheet($spreadsheet, 'Flete Provincia');
//            $spreadsheet->addSheet($sheet2);
//
//            /*$sheet2->setCellValue('A1', 'Este es un ejemplo de la hoja nacional');
//            $sheet2->getStyle('A1')->getFont()->setBold(true);
//            $sheet2->getColumnDimension('A')->setWidth(50);*/
//
//            // Encabezados
//            $headers2 = [
//                'N° OS Local',
//                'Fecha OS Local',
//                'Departamento de Entrega',
//                'Provincia de Entrega',
//                'Distrito de Entrega',
//                'Flete OS Provincia',
//                'Valor Transportado por OS Local',
//                'Peso Transportado por OS Local',
//            ];
//
//            $sheet2->fromArray($headers2, null, 'A1');
//            $headerStyle2 = [
//                'font' => ['bold' => true],
//                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                'fill' => [
//                    'fillType' => Fill::FILL_SOLID,
//                    'startColor' => ['rgb' => 'FFE699']
//                ]
//            ];
//            $sheet2->getStyle('A1:H1')->applyFromArray($headerStyle2);
//            $sheet2->getColumnDimension('A')->setWidth(20);
//            $sheet2->getColumnDimension('B')->setWidth(20);
//            $sheet2->getColumnDimension('C')->setWidth(30);
//            $sheet2->getColumnDimension('D')->setWidth(35);
//            $sheet2->getColumnDimension('E')->setWidth(30);
//            $sheet2->getColumnDimension('F')->setWidth(30);
//            $sheet2->getColumnDimension('G')->setWidth(30);
//            $sheet2->getColumnDimension('H')->setWidth(30);
//
//            $despachos_pro = DB::table('programaciones as p')
//                ->join('despachos as d', 'd.id_programacion', '=', 'p.id_programacion')
//
//                ->join('departamentos as depa', 'd.id_departamento', '=', 'depa.id_departamento')
//                ->join('provincias as pro', 'd.id_provincia', '=', 'pro.id_provincia')
//                ->join('distritos as di', 'd.id_distrito', '=', 'di.id_distrito')
//                ->where('d.id_tipo_servicios', 2)
//                ->where('d.despacho_estado_aprobacion', '<>', 4)
//                ->whereBetween('d.despacho_fecha_aprobacion', ['2025-01-01', '2027-08-31'])
//
//                ->get();
//
//            /*$despachos_pro = DB::table('despachos as d')
//                ->join('departamentos as depa', 'd.id_departamento', '=', 'depa.id_departamento')
//                ->join('provincias as pro', 'd.id_provincia', '=', 'pro.id_provincia')
//                ->join('distritos as di', 'd.id_distrito', '=', 'di.id_distrito')
//                ->whereBetween('d.despacho_fecha_aprobacion', ['2025-01-01', '2027-07-31'])
//                ->where('d.id_tipo_servicios', 2)
//                ->where('d.despacho_estado_aprobacion', '<>', 4)
//                ->get();*/
//
//            $row2 = 2;
//            foreach ($despachos_pro as $dp){
//                $sheet2->setCellValue('A' . $row2, $dp->despacho_numero_correlativo);
//                $sheet2->setCellValue('B' . $row2, date('d/m/Y', strtotime($dp->programacion_fecha)));
//                $sheet2->setCellValue('C' . $row2, $dp->departamento_nombre);
//                $sheet2->setCellValue('D' . $row2, $dp->provincia_nombre);
//                $sheet2->setCellValue('E' . $row2, $dp->distrito_nombre);
//                $sheet2->setCellValue('F' . $row2, $dp->despacho_costo_total);
//
//                $detalle = DB::table('despacho_ventas as dv')
//                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
//                    ->where('dv.id_despacho', '=', $dp->id_despacho)
//                    ->get();
//
//                $sum = 0;
//                foreach ($detalle as $d) {
//                    if (in_array($d->guia_estado_aprobacion, [11, 12])) {
//                        $sum += 0;
//                    } else {
//                        $sum += $d->guia_importe_total_sin_igv;
//                    }
//                }
//
//                $sheet2->setCellValue('G' . $row2, $sum);
//                $sheet2->setCellValue('H' . $row2, $dp->despacho_peso);
//                $row2++;
//            }
//
//            // OPCIONAL: Hoja activa al abrir
//            $spreadsheet->setActiveSheetIndexByName('Flete Local');
//
//            // Descargar archivo
//            $nombre_excel = "REPORTE AVANCE INTRANET.xlsx";
//            return response()->stream(
//                function () use ($writer) {
//                    $writer->save('php://output');
//                },
//                200,
//                [
//                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
//                    'Content-Disposition' => 'attachment; filename="' . $nombre_excel . '"',
//                ]
//            );


            // Construir la consulta base similar a la vista
            $query = DB::table('programaciones as p')
                ->join('despachos as d', 'd.id_programacion', '=', 'p.id_programacion')
                ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                ->leftJoin('notas_creditos as nc', function($join) {
                    $join->on('nc.not_cred_nro_doc_ref', '=', 'g.guia_nro_doc_ref')
                        ->where('nc.not_cred_motivo', '=', '1');
                })
                ->where('d.despacho_estado_aprobacion', '!=', 4)
                ->where('g.guia_estado_aprobacion', '=', 8)
                ->select(
                    'g.guia_fecha_emision',
                    'g.guia_nro_doc',
                    'g.guia_nombre_cliente',
                    'g.guia_nro_doc_ref',
                    'g.guia_importe_total',
                    'd.despacho_numero_correlativo',
                    'd.despacho_estado_aprobacion',
                    'p.programacion_fecha as fecha_despacho',
                    'g.updated_at as fecha_entrega',
                    DB::raw('IFNULL(nc.not_cred_nro_doc, "-") as nc_numero'),
                    DB::raw('IF(nc.not_cred_nro_doc IS NOT NULL, 1, 0) as tiene_nc'),
                    'g.guia_departamento',
                    'g.guia_provincia',
                    'g.guia_direc_entrega'
                );

            // Aplicar filtros según los parámetros del componente
            if ($this->tipo_reporte == 1) {
                $query->whereBetween('g.guia_fecha_emision', [$this->desde, $this->hasta]);
            } elseif ($this->tipo_reporte == 2) {
                $query->whereBetween('p.programacion_fecha', [$this->desde, $this->hasta]);
            }

            $guias = $query->get();

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
                $sheet->setCellValue('A'.$row, date('d/m/Y', strtotime($guia->guia_fecha_emision)));
                $sheet->setCellValue('B'.$row, $guia->guia_nro_doc);
                $sheet->setCellValue('C'.$row, $guia->guia_nombre_cliente);
                $sheet->setCellValue('D'.$row, $guia->guia_nro_doc_ref ?: '-');
                $sheet->setCellValue('E'.$row, $this->general->formatoDecimal(($guia->guia_importe_total)));
                $sheet->setCellValue('F'.$row, $guia->despacho_numero_correlativo);
                // estado de OS
                $estado = match((int)$guia->despacho_estado_aprobacion) {
                    0 => 'Pendiente',
                    1 => 'Aprobado',
                    2 => 'En camino',
                    3 => 'Culminado',
                    4 => 'Rechazado',
                    default => 'Desconocido'
                };
                $sheet->setCellValue('G'.$row, $estado);
                $sheet->setCellValue('H'.$row, date('d/m/Y', strtotime($guia->fecha_despacho)));
                $sheet->setCellValue('I'.$row, $guia->fecha_entrega ? date('d/m/Y', strtotime($guia->fecha_entrega)) : '-');
                $sheet->setCellValue('J'.$row, $guia->tiene_nc ? 1 : '-');
                $sheet->setCellValue('K'.$row, $guia->tiene_nc ? $this->general->formatoDecimal($guia->guia_importe_total) : '-');
                $sheet->setCellValue('L'.$row, $guia->guia_departamento);
                $sheet->setCellValue('M'.$row, $guia->guia_provincia);
                $sheet->setCellValue('N'.$row, $guia->guia_direc_entrega);

                // Center
                $sheet->getStyle('J'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            // Autoajustar columnas
            foreach(range('A','N') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Formatear el nombre del archivo Excel
            $nombre_excel = sprintf(
                "efectividad_despacho_%s_a_%s.xlsx",
                date('d-m-Y', strtotime($this->desde)),
                date('d-m-Y', strtotime($this->hasta))
            );

            // Descargar el archivo
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

            return response()->stream(
                function () use ($writer) {
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="' . $nombre_excel . '"',
                ]
            );

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }
}
