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
use Illuminate\Support\Facades\Log;

class Reporteindicadorespeso extends Component
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
    public $ydesde;
    public $yhasta;
    public $tipo_reporte = "";
    public $totalPesoTrans = 0;
    public $filtrarData = [];
    public $summary = [];
    public $searchdatos = false;

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

    // Objetivos fijos como se muestran en la imagen
    public $objetivos = [
        'Total' => 0.35,
        'Local' => 0.15,
        'Provincia 1' => 0.55,
        'Provincia 2' => 0.85
    ];

    public $datosGraficoPeso = [];
    public $datosGraficoFlete = [];

    public $departamentos = [];
    public function mount(){
        $this->departamentos = $this->general->listar_departamento_zona();
    }
    public function render() {
        if ($this->searchdatos) {
            // Verificar que los datos existan antes de enviarlos
            if (!empty($this->datosGraficoPeso)) {
                $this->dispatch('actualizarGraficoPeso', $this->datosGraficoPeso);
            } else {
                $this->dispatch('actualizarGraficoPeso', [
                    'meses' => ['ENE-25'],
                    'peso_local' => [0],
                    'peso_provincia1' => [0],
                    'peso_provincia2' => [0]
                ]);
            }

            if (!empty($this->datosGraficoFlete)) {
                $this->dispatch('actualizarGraficoFlete', $this->datosGraficoFlete);
            } else {
                $this->dispatch('actualizarGraficoFlete', [
                    'meses' => ['ENE-25'],
                    'flete_local' => [0],
                    'flete_provincia' => [0]
                ]);
            }
        }

        return view('livewire.programacioncamiones.reporteindicadorespeso');
    }

    public function buscar_reporte_peso() {
        try {
            $this->searchdatos = true;

            $this->validate([
                'ydesde' => 'required|date',
                'yhasta' => 'required|date|after_or_equal:ydesde',
            ], [
                'ydesde.required' => 'La fecha de inicio es obligatoria.',
                'ydesde.date' => 'La fecha de inicio no es válida.',

                'yhasta.required' => 'La fecha de fin es obligatoria.',
                'yhasta.date' => 'La fecha de fin no es válida.',
                'yhasta.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            ]);

            $this->filtrarData = [];

            // 1. Obtenemos los despachos únicos con sus fletes
            $local = $this->guia->listar_informacion_reporte_indicador_de_peso($this->tipo_reporte, $this->ydesde, $this->yhasta, $this->departamentos, 1);
            $provincia1 = $this->guia->listar_informacion_reporte_indicador_de_peso($this->tipo_reporte, $this->ydesde, $this->yhasta, $this->departamentos, 2);
            $provincia2 = $this->guia->listar_informacion_reporte_indicador_de_peso($this->tipo_reporte, $this->ydesde, $this->yhasta, $this->departamentos, 3);

            $this->totalPesoTrans = $this->guia->listar_informacion_reporte_total_de_peso_transportado($this->tipo_reporte,$this->ydesde,$this->yhasta,$this->departamentos,3);

            $this->filtrarData = array_merge($this->filtrarData, [$local, $provincia1, $provincia2]);

            $this->generarGraficoReportePeso(1);
            $this->generarGraficoReportePeso(2);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return ;
        }
    }
    public function generarGraficoReportePeso($ty){
        try {
            $fechaDesde = Carbon::parse($this->ydesde);
            $fechaHasta = Carbon::parse($this->yhasta);
            $meses = [];

            $lima = [];
            $provinciaOne = [];
            $provinciaTwoe = [];

                while ($fechaDesde->lessThanOrEqualTo($fechaHasta)) {
                $meses[] = ucfirst($fechaDesde->locale('es')->isoFormat('MMMM')); // Nombre del mes en español y con mayúscula inicial
                $fechaAnhoMes = $fechaDesde->format('Y-m');

                $lima[] = $this->guia->listar_informacion_reporte_indicador_de_peso($this->tipo_reporte, $this->ydesde, $this->yhasta, $this->departamentos, 1,$ty,$fechaAnhoMes);
                $provinciaOne[] = $this->guia->listar_informacion_reporte_indicador_de_peso($this->tipo_reporte, $this->ydesde, $this->yhasta, $this->departamentos, 2,$ty,$fechaAnhoMes);
                if ($ty == 1){
                    $provinciaTwoe[] = $this->guia->listar_informacion_reporte_indicador_de_peso($this->tipo_reporte, $this->ydesde, $this->yhasta, $this->departamentos, 3,$ty,$fechaAnhoMes);
                }

                $fechaDesde->addMonth();
            }

            if ($ty == 1){
                $this->dispatch('generarGraficoPesoToneladas', [
                    $meses,
                    $lima,
                    $provinciaOne,
                    $provinciaTwoe
                ]);
            }else{
                $this->dispatch('generarGraficoPesoKilos', [
                    $meses,
                    $lima,
                    $provinciaOne,
                    $provinciaTwoe
                ]);
            }


        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
    }
    public function exportarReportePesoExcel() {
        try {

            if (!Gate::allows('exportar_reporte_peso_excel')) {
                session()->flash('error', 'No tiene permisos para generar este reporte.');
                return;
            }

            $this->buscar_reporte_peso();
            $this->validate([
                'ydesde' => 'required|date',
                'yhasta' => 'required|date|after_or_equal:ydesde',
            ], [
                'ydesde.required' => 'La fecha de inicio es obligatoria.',
                'ydesde.date' => 'La fecha de inicio no es válida.',

                'yhasta.required' => 'La fecha de fin es obligatoria.',
                'yhasta.date' => 'La fecha de fin no es válida.',
                'yhasta.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            ]);

            $desde = $this->ydesde;
            $hasta = $this->yhasta;
            $tipo = $this->tipo_reporte;

            $resultDetalles = DB::table('despachos as d')
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->leftJoin('servicios_transportes as st', 'dv.id_serv_transpt', '=', 'st.id_serv_transpt')
                ->where('d.despacho_estado_aprobacion', '=', 3)
                ->where('d.despacho_liquidado', '=', 1)
            ;

            if ($tipo == 1){
                // F. Emisión
                $resultDetalles->whereDate('g.guia_fecha_emision', '>=', $desde)
                    ->whereDate('g.guia_fecha_emision', '<=', $hasta);

            }else{
                // F. Programación
                $resultDetalles->whereDate('d.despacho_fecha_aprobacion', '>=', $desde)
                    ->whereDate('d.despacho_fecha_aprobacion', '<=', $hasta);
            }

            $resultDetallesExcel = $resultDetalles->orderBy('d.despacho_numero_correlativo','asc')->get();


            foreach ($resultDetallesExcel as $deta){
                $pesoTotalKilos = 0;

                $detallesGuia = DB::table('guias_detalles')
                    ->where('id_guia', '=', $deta->id_guia)
                    ->get();
                $pesoTotalGramos = $detallesGuia->sum(function ($detalle) {
                    return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                });
                $pesoTotalKilos += $pesoTotalGramos / 1000;
                if ($deta->serv_transpt_peso) {
                    $pesoTotalKilos += $deta->serv_transpt_peso;
                }
                $deta->peso = $pesoTotalKilos;

                $subTotal = $this->general->sacarMontoLiquidacion($deta->id_despacho);

                $deta->despacho_costo_totalLi = $subTotal;
            }


            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Detalle de Despachos');

            // ========== CABECERA PRINCIPAL ==========
            $sheet->setCellValue('A1', 'REPORTE FLETE: INDICADOR DE PESO DESPACHADO');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('A8D08D');

            // ========== RANGO DE FECHAS ==========
            $rangoFechas = 'Del ' . date('d/m/Y', strtotime($this->ydesde)) . ' al ' . date('d/m/Y', strtotime($this->yhasta));
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
            $departemento = $this->departamentos;
            $row = 4;
            foreach ($resultDetallesExcel as $item) {
                $validarTipoOs = DB::table('programaciones as p')
                    ->join('despachos as d','d.id_programacion','=','p.id_programacion')
                    ->join('despacho_ventas as dv','dv.id_despacho','=','d.id_despacho')
                    ->join('guias as g','g.id_guia','=','dv.id_guia')
                    ->where('g.id_guia','=',$item->id_guia)
                    ->where('d.id_despacho','<>',$item->id_despacho)
                    ->where('d.id_programacion','=',$item->id_programacion)
                    ->first();

                $tipoOs = "";
                if ($validarTipoOs){
                    $tipoOs = "MIXTO";
                }else{
                    if ($item->id_tipo_servicios == 1){
                        $tipoOs = "LOCAL";
                    }elseif ($item->id_tipo_servicios == 2){
                        $tipoOs = "PROVINCIAL";
                    }
                }
                $estadoOS = match($item->despacho_estado_aprobacion) {
                    0 => 'Pendiente',
                    1 => 'Aprobado',
                    2 => 'En camino',
                    3 => 'Culminado',
                    4 => 'Rechazado',
                    default => 'Desconocido'
                };

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

                $sheet->setCellValue('A'.$row, date('d/m/Y', strtotime($item->despacho_fecha_aprobacion)));
                $sheet->setCellValue('B'.$row, date('d/m/Y', strtotime($item->guia_fecha_emision)));
                $sheet->setCellValue('C'.$row, $item->despacho_numero_correlativo);
                $sheet->setCellValue('D'.$row, $item->peso ?? 0);
                $sheet->setCellValue('E'.$row, $item->despacho_costo_totalLi ?? 0);
                $sheet->setCellValue('F'.$row, $tipoOs);
                $sheet->setCellValue('G'.$row, $estadoOS);
                $sheet->setCellValue('H'.$row, $item->guia_departamento ?? 'S/N');
                $sheet->setCellValue('I'.$row, $item->guia_provincia ?? 'S/N');
                $sheet->setCellValue('J'.$row, $zona);

                $sheet->getStyle('A'.$row.':J'.$row)->getAlignment()->setHorizontal('center');
                $row++;
            }

            // ========== FORMATO NUMÉRICO ==========
            $sheet->getStyle('D4:D'.($row-1))->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E4:E'.($row-1))->getNumberFormat()->setFormatCode('#,##0.00');

            // ========== ANCHO DE COLUMNA ==========
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(22);
            $sheet->getColumnDimension('F')->setWidth(35);
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
            $nombreArchivo = "reporte_peso_flete_" . date('Ymd_His') . ".xlsx";
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $temp_file = tempnam(sys_get_temp_dir(), $nombreArchivo);
            $writer->save($temp_file);

            return response()->download($temp_file, $nombreArchivo, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el reporte: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
