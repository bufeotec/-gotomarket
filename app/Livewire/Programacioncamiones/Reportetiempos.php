<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\General;
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

class Reportetiempos extends Component
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
    public $filteredData = [];
    public $searchExecuted = false;

    /* ---------------------------------------- */
    public $meses_grafico = [];
    public $tiempoLima = [];
    public $tiempoProvincia = [];

    /* ---------------------------------------- */
    // Objetivos por zona
    public $objetivos = [
        'LOCAL' => 3,
        'PROVINCIA 1' => 6,
        'PROVINCIA 2' => 8
    ];

    // Clasificación de departamentos
    public $departamentos = [];

    public function mount(){
        $this->desde = date('Y-01-01');
        $this->hasta = date('Y-m-d');
        $this->departamentos = $this->general->listar_departamento_zona();
    }
    public function render(){
        return view('livewire.programacioncamiones.reportetiempos');
    }
    public function buscar_reporte_tiempo(){
        try {

            $this->validate([
                'tipo_reporte' => 'required|in:1,2',
                'desde' => 'required|date',
                'hasta' => 'required|date|after_or_equal:desde',
            ], [
                'tipo_reporte.required' => 'El tipo de reporte es obligatorio.',
                'tipo_reporte.in' => 'El tipo de reporte seleccionado no es válido.',

                'desde.required' => 'La fecha de inicio es obligatoria.',
                'desde.date' => 'La fecha de inicio no es válida.',

                'hasta.required' => 'La fecha de fin es obligatoria.',
                'hasta.date' => 'La fecha de fin no es válida.',
                'hasta.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            ]);

            $this->searchExecuted = true;

            if (empty($this->desde) || empty($this->hasta)) {
                $this->filteredData = [];
                return;
            }

            $resultLocal =  $this->guia->listar_informacion_reporte_tiempos_atencion_pedido($this->tipo_reporte,$this->desde,$this->hasta,$this->departamentos,1);
            $resultProvincia1 =  $this->guia->listar_informacion_reporte_tiempos_atencion_pedido($this->tipo_reporte,$this->desde,$this->hasta,$this->departamentos,2);
            $resultProvincia2 =  $this->guia->listar_informacion_reporte_tiempos_atencion_pedido($this->tipo_reporte,$this->desde,$this->hasta,$this->departamentos,3);

            $this->filteredData = [
                number_format(round($resultLocal,2), 2, '.', ' '),
                number_format(round($resultProvincia1,2), 2, '.', ' '),
                number_format(round($resultProvincia2,2), 2, '.', ' '),
            ];

            $this->implementar_datos_graficos();

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return ;
        }
    }
    public function implementar_datos_graficos(){
        try {

            $fechaDesde = Carbon::parse($this->desde);
            $fechaHasta = Carbon::parse($this->hasta);

            $meses = [];
            $tiempoLima = [];
            $tiempoProvincia = [];

            while ($fechaDesde->lessThanOrEqualTo($fechaHasta)) {
                $meses[] = ucfirst($fechaDesde->locale('es')->isoFormat('MMMM')); // Nombre del mes en español y con mayúscula inicial
                $fechaAnhoMes = $fechaDesde->format('Y-m');
                $tiempoLima[] =  $this->guia->listar_informacion_reporte_tiempos_atencion_pedido($this->tipo_reporte,$this->desde,$this->hasta,$this->departamentos,1,1,$fechaAnhoMes);
                $tiempoProvincia[] =  $this->guia->listar_informacion_reporte_tiempos_atencion_pedido($this->tipo_reporte,$this->desde,$this->hasta,$this->departamentos,2,1,$fechaAnhoMes);
                $fechaDesde->addMonth();
            }


            $this->dispatch('actualizarGraficoTiempoEntrega', [
                $meses,
                $tiempoLima,
                $tiempoProvincia
            ]);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return ;
        }
    }

    public function exportarTiemposExcel(){
        try {
            $this->implementar_datos_graficos();
            if (!Gate::allows('exportar_tiempos_excel')) {
                session()->flash('error', 'No tiene permisos para generar este reporte.');
                return;
            }

            // Verificar parámetros requeridos
            if (empty($this->desde) || empty($this->hasta) || empty($this->tipo_reporte)) {
                session()->flash('error', 'Faltan parámetros para generar el reporte.');
                return;
            }
            $desde = $this->desde;
            $hasta = $this->hasta;
            $tipo = $this->tipo_reporte;

            $queryReporteTiemposAtencion = DB::table('guias as g')
                ->select('g.*','dv.*','d.*','p.*','g.updated_at as fecha_entrega')
                ->join('despacho_ventas as dv', 'g.id_guia', '=', 'dv.id_guia')
                ->join('despachos as d', 'dv.id_despacho', '=', 'd.id_despacho')
                ->join('programaciones as p', 'd.id_programacion', '=', 'p.id_programacion')
                ->where('g.guia_estado_aprobacion', 8);

            if ($tipo == 1){
                // F. Emisión
                $queryReporteTiemposAtencion->whereDate('g.guia_fecha_emision', '>=', $desde)->whereDate('g.guia_fecha_emision', '<=', $hasta);
            }else{
                // F. Programación
                $queryReporteTiemposAtencion->whereDate('p.programacion_fecha', '>=', $desde)->whereDate('p.programacion_fecha', '<=', $hasta);
            }

            $resultConsulta = $queryReporteTiemposAtencion->get();


            if ($resultConsulta->isEmpty()) {
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
            foreach ($resultConsulta as $item) {
                $departemento = $this->departamentos;

                $departamento = strtoupper(trim($item->guia_departamento)); // Aseguramos formato

                $zonaEncontrada = null;
                foreach ($departemento as $indice => $zona) {
                    if (in_array($departamento, $zona)) {
                        $zonaEncontrada = $indice;
                        break;
                    }
                }
                if ($zonaEncontrada === 0) {
                    $zona = "LOCAL";
                } elseif ($zonaEncontrada === 1) {
                    $zona = "PROVINCIA 1";
                } elseif ($zonaEncontrada === 2) {
                    $zona = "PROVINCIA 2";
                } else {
                    $zona = "";
                }

                $fechaInicio = $item->guia_fecha_emision;

                $fechaFin = $item->fecha_entrega;
                // Asegúrate de que ambas fechas estén en formato DateTime
                $inicio = new \DateTime($fechaInicio);
                $fin = new \DateTime($fechaFin);
                // Calculamos diferencia en días
                $diferencia = $inicio->diff($fin)->days;

                $sheet->setCellValue('A'.$row, date('d-m-Y', strtotime($item->guia_fecha_emision)) ? date('d-m-Y', strtotime($item->guia_fecha_emision)) : '');
                $sheet->setCellValue('B'.$row, $item->guia_nro_doc);
                $sheet->setCellValue('C'.$row, $item->guia_nombre_cliente.'|'.$item->guia_ruc_cliente);
                $sheet->setCellValue('D'.$row, date('d/m/Y', strtotime($item->programacion_fecha)) ? date('d/m/Y', strtotime($item->programacion_fecha)) : '');
                $sheet->setCellValue('E'.$row, date('d/m/Y', strtotime($item->fecha_entrega)) ? date('d/m/Y', strtotime($item->fecha_entrega)) : '');
                $sheet->setCellValue('F'.$row, round($diferencia));
                $sheet->setCellValue('G'.$row, 'ENTREGADO');
                $sheet->setCellValue('H'.$row, $item->guia_departamento ?? 'S/N');
                $sheet->setCellValue('I'.$row, $item->guia_provincia ?? 'S/N');
                $sheet->setCellValue('J'.$row, $zona ?? 'S/N');

                $sheet->getStyle('A'.$row.':J'.$row)->getAlignment()->setHorizontal('center');
                $row++;
            }

            // ========== ANCHO DE COLUMNA ==========
            $sheet->getColumnDimension('A')->setWidth(28);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(35);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(20);
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
            $nombreArchivo = "tiempos_entrega_" . date('Ymd_His') . ".xlsx";
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
