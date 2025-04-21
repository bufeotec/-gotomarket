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

class Reporteindicadoresvalor extends Component
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

    public $totalValorTrans = 0;
    public $xdesde;
    public $xhasta;
    public $tipo_reporte = 'emision'; // Valor por defecto
    public $filteredData = [];
    public $summary = [];
    public $searchdatos = false;
    public $datosGraficoFleteTotal = [];
    public $datosGraficoFleteLimaProvincia = [];

    public $valoresPorzona = [];
    // Objetivos fijos como en la imagen
    public $objetivos = [
        'Total' => 3.90,
        'Local' => 1.90,
        'Provincia 1' => 5.50,
        'Provincia 2' => 9.50
    ];

    public $departamentos = [];
    public function mount(){
        $this->departamentos = $this->general->listar_departamento_zona();
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

    public function buscar_reporte_valor() {
        try {
            $this->searchdatos = true;

            $this->validate([
                'xdesde' => 'required|date',
                'xhasta' => 'required|date|after_or_equal:xdesde',
            ], [
                'xdesde.required' => 'La fecha de inicio es obligatoria.',
                'xdesde.date' => 'La fecha de inicio no es válida.',

                'xhasta.required' => 'La fecha de fin es obligatoria.',
                'xhasta.date' => 'La fecha de fin no es válida.',
                'xhasta.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            ]);
            if (empty($this->xdesde) || empty($this->xhasta)) {
                $this->filteredData = [];
                return;
            }
            $this->valoresPorzona = [];
            $local = $this->guia->listar_informacion_reporte_indicador_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,1);
            $provincia1 = $this->guia->listar_informacion_reporte_indicador_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,2);
            $provincia2 = $this->guia->listar_informacion_reporte_indicador_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,3);
            $this->totalValorTrans = $this->guia->listar_informacion_reporte_total_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,3);

            $this->valoresPorzona = array_merge($this->valoresPorzona, [$local, $provincia1, $provincia2]);


            $this->generalGraficoTotalMes();
            $this->generalGraficoFleteMes();

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return ;
        }
    }

    public function generalGraficoTotalMes(){
        try {
            $fechaDesde = Carbon::parse($this->xdesde);
            $fechaHasta = Carbon::parse($this->xhasta);
            $meses = [];
            $totalMes = [];
            $totalMesPorcen = [];
            while ($fechaDesde->lessThanOrEqualTo($fechaHasta)) {
                $meses[] = ucfirst($fechaDesde->locale('es')->isoFormat('MMMM')); // Nombre del mes en español y con mayúscula inicial
                $fechaAnhoMes = $fechaDesde->format('Y-m');
                $totalValorTrans = $this->guia->listar_informacion_reporte_total_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,1,1,$fechaAnhoMes);
                $totalLima = $this->guia->listar_informacion_reporte_indicador_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,1,2,$fechaAnhoMes);
                $totalProvincial = $this->guia->listar_informacion_reporte_indicador_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,2,2,$fechaAnhoMes);
                $por = $totalValorTrans != 0 ? (($totalLima+$totalProvincial) / $totalValorTrans) * 100 : 0;
                $totalMes[] = $totalValorTrans;
                $totalMesPorcen[] = $por;
                $fechaDesde->addMonth();
            }
            $this->dispatch('actualizarGraficoTotal', [
                $meses,
                $totalMes,
                $totalMesPorcen,
            ]);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
    }
    public function generalGraficoFleteMes(){
        try {
            $fechaDesde = Carbon::parse($this->xdesde);
            $fechaHasta = Carbon::parse($this->xhasta);
            $meses = [];
            $totalLima = [];
            $totalProvincial = [];
            $totalLimaPorce = [];
            $totalProvincialPorce = [];
            while ($fechaDesde->lessThanOrEqualTo($fechaHasta)) {
                $meses[] = ucfirst($fechaDesde->locale('es')->isoFormat('MMMM')); // Nombre del mes en español y con mayúscula inicial
                $fechaAnhoMes = $fechaDesde->format('Y-m');
                $totalLimaFlete = $this->guia->listar_informacion_reporte_indicador_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,1,2,$fechaAnhoMes,1);
                $totalLimaTrans = $this->guia->listar_informacion_reporte_indicador_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,1,2,$fechaAnhoMes,2);
                $totalProvincialFlete = $this->guia->listar_informacion_reporte_indicador_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,2,2,$fechaAnhoMes,1);
                $totalProvincialValor = $this->guia->listar_informacion_reporte_indicador_de_valor_transportado($this->tipo_reporte,$this->xdesde,$this->xhasta,$this->departamentos,2,2,$fechaAnhoMes,2);

                $totalLima[] = $totalLimaFlete;
                $totalProvincial[] =$totalProvincialFlete;
                $totalLimaPorce[] = $totalLimaTrans != 0 ? ($totalLimaFlete / $totalLimaTrans) * 100 : 0;
                $totalProvincialPorce[] = $totalProvincialValor != 0 ? ($totalProvincialFlete / $totalProvincialValor) * 100 : 0;

                $fechaDesde->addMonth();
            }
            $this->dispatch('actualizarGraficoFleteMes', [
                $meses,
                $totalLima,
                $totalProvincial,

                $totalLimaPorce,
                $totalProvincialPorce
            ]);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
    }

    public function exportarReporteValorExcel(){
        try {
            $this->validate([
                'xdesde' => 'required|date',
                'xhasta' => 'required|date|after_or_equal:xdesde',
            ], [
                'xdesde.required' => 'La fecha de inicio es obligatoria.',
                'xdesde.date' => 'La fecha de inicio no es válida.',

                'xhasta.required' => 'La fecha de fin es obligatoria.',
                'xhasta.date' => 'La fecha de fin no es válida.',
                'xhasta.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            ]);

            if (!Gate::allows('exportar_reporte_valor_excel')) {
                session()->flash('error', 'No tiene permisos para generar este reporte.');
                return;
            }

            $desde = $this->xdesde;
            $hasta = $this->xhasta;
            $tipo = $this->tipo_reporte;

            // Obtenemos los despachos con sus guías
            $despachos = DB::table('despachos as d')
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->where('d.despacho_estado_aprobacion', '!=', 4);

            if ($tipo == 1){
                // F. Emisión
                $despachos->whereDate('g.guia_fecha_emision', '>=', $desde)
                    ->whereDate('g.guia_fecha_emision', '<=', $hasta);

            }else{
                // F. Programación
                $despachos->whereDate('d.despacho_fecha_aprobacion', '>=', $desde)
                    ->whereDate('d.despacho_fecha_aprobacion', '<=', $hasta);
            }

            $despachos = $despachos->orderBy('d.despacho_numero_correlativo','asc')->get();

            if ($despachos->isEmpty()) {
                session()->flash('error', 'No hay datos para exportar en el rango de fechas seleccionado.');
                return;
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
                'Tipo de OS',
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
            foreach ($despachos as $desp) {
                /*  SE VA SABER SI ESA GUIA ES MIXTA O NO */
                $validarTipoOs = DB::table('programaciones as p')
                    ->join('despachos as d','d.id_programacion','=','p.id_programacion')
                    ->join('despacho_ventas as dv','dv.id_despacho','=','d.id_despacho')
                    ->join('guias as g','g.id_guia','=','dv.id_guia')
                    ->where('g.id_guia','=',$desp->id_guia)
                    ->where('d.id_despacho','<>',$desp->id_despacho)
                    ->where('d.id_programacion','=',$desp->id_programacion)
                    ->first();

                $tipoOs = "";
                if ($validarTipoOs){
                    $tipoOs = "MIXTO";
                }else{
                    if ($desp->id_tipo_servicios == 1){
                        $tipoOs = "LOCAL";
                    }elseif ($desp->id_tipo_servicios == 2){
                        $tipoOs = "PROVINCIAL";
                    }
                }
                $departemento = $this->departamentos;

                $sheet->setCellValue('A'.$row, date('d/m/Y', strtotime($desp->despacho_fecha_aprobacion)));
                $sheet->setCellValue('B'.$row, $desp->guia_fecha_emision ? date('d/m/Y', strtotime($desp->guia_fecha_emision)) : '');
                $sheet->setCellValue('C'.$row, $desp->despacho_numero_correlativo);
                $sheet->setCellValue('D'.$row, $desp->guia_importe_total ?? 0);
                $sheet->setCellValue('E'.$row, $desp->despacho_costo_total);
                $sheet->setCellValue('F'.$row, $tipoOs);

                // Estado de OS
                $estadoOS = match($desp->despacho_estado_aprobacion) {
                    0 => 'Pendiente',
                    1 => 'Aprobado',
                    2 => 'En camino',
                    3 => 'Culminado',
                    4 => 'Rechazado',
                    default => 'Desconocido'
                };
                $sheet->setCellValue('G'.$row, $estadoOS);


                $departamento = strtoupper(trim($desp->guia_departamento)); // Aseguramos formato

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

                $sheet->setCellValue('H'.$row, $desp->guia_departamento ?? 'S/N');
                $sheet->setCellValue('I'.$row, $desp->guia_provincia ?? 'S/N');
                $sheet->setCellValue('J'.$row, $zona ?? 'S/N');

                // Formato numérico para valores
                $sheet->getStyle('D'.$row.':E'.$row)->getNumberFormat()->setFormatCode('#,##0.00');

                $row++;
            }

            // ========== ANCHO DE COLUMNA ==========
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(14);
            $sheet->getColumnDimension('D')->setWidth(22);
            $sheet->getColumnDimension('E')->setWidth(23);
            $sheet->getColumnDimension('F')->setWidth(16);
            $sheet->getColumnDimension('G')->setWidth(14);
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        }catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el reporte: ' . $e->getMessage());
            return redirect()->back();
        }
    }

}
