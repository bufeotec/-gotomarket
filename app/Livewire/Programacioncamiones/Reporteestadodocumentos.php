<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use App\Models\General;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Reporteestadodocumentos extends Component
{
    private $logs;
    public $guia_estado_aprobacion;
    public $tipo_reporte;
    public $desde;
    public $hasta;
    public $resultados = [];
    public $mostrarFechas = false;

    // Definimos los umbrales de alerta según la segunda imagen
    public $alertas = [
        1 => 3,    // Créditos
        3 => 3,    // Pendiente de Programación
        4 => 3,    // Programado
        7 => 7     // En camino
    ];

    public function __construct(){
        $this->logs = new Logs();
    }

    public function mount(){
        $this->desde = date('Y-m-d', strtotime('-1 month'));
        $this->hasta = date('Y-m-d');
    }

    public function updatedTipoReporte($value){
        $this->mostrarFechas = $value == '2';
    }

    public function buscar_estado_documento(){
        $this->resultados = [];

        if($this->tipo_reporte == '1') {
            $this->resultados = $this->obtenerDocumentosExcedidos();
        } elseif($this->tipo_reporte == '2') {
            $this->resultados = $this->obtenerHistorial();
        }
    }

    public function obtenerDocumentosExcedidos(){
        $hoy = Carbon::now('America/Lima');
        $resultados = [];

        // Si se seleccionó un estado específico
        if($this->guia_estado_aprobacion) {
            $estado = $this->guia_estado_aprobacion;
            $diasAlerta = $this->alertas[$estado] ?? 0;

            $query = DB::table('guias')
                ->where('guia_estado_aprobacion', $estado)
                ->whereRaw("DATEDIFF(?, updated_at) > ?", [$hoy, $diasAlerta]);

            $cantidad = $query->count();

            if($cantidad > 0) {
                $resultados[] = [
                    'zona' => $this->obtenerNombreEstado($estado),
                    'promedio' => number_format($query->avg(DB::raw("DATEDIFF('$hoy', updated_at)")), 2),
                    'cantidad' => $cantidad,
                    'estado_id' => $estado,
                    'guias' => $query->get()->toArray()
                ];
            }
        } else {
            // Si no se seleccionó estado, mostrar todos
            foreach($this->alertas as $estado => $diasAlerta) {
                $query = DB::table('guias')
                    ->where('guia_estado_aprobacion', $estado)
                    ->whereRaw("DATEDIFF(?, updated_at) > ?", [$hoy, $diasAlerta]);

                $cantidad = $query->count();

                if($cantidad > 0) {
                    $resultados[] = [
                        'zona' => $this->obtenerNombreEstado($estado),
                        'promedio' => number_format($query->avg(DB::raw("DATEDIFF('$hoy', updated_at)")), 2),
                        'cantidad' => $cantidad,
                        'estado_id' => $estado,
                        'guias' => $query->get()->toArray()
                    ];
                }
            }
        }

        return $resultados;
    }

    public function obtenerHistorial(){
        $hoy = Carbon::now('America/Lima');
        $resultados = [];

        // Validar fechas
        if(empty($this->desde)) {
            session()->flash('error', 'Debe seleccionar una fecha de inicio');
            return [];
        }

        if(empty($this->hasta)) {
            session()->flash('error', 'Debe seleccionar una fecha de fin');
            return [];
        }

        // Si se seleccionó un estado específico
        if($this->guia_estado_aprobacion) {
            $estado = $this->guia_estado_aprobacion;
            $diasAlerta = $this->alertas[$estado] ?? 0;

            $query = DB::table('guias')
                ->where('guia_estado_aprobacion', $estado)
                ->whereBetween('guia_fecha_emision', [$this->desde, $this->hasta])
                ->whereRaw("DATEDIFF(?, updated_at) > ?", [$hoy, $diasAlerta]);

            $cantidad = $query->count();

            if($cantidad > 0) {
                $resultados[] = [
                    'zona' => $this->obtenerNombreEstado($estado),
                    'promedio' => number_format($query->avg(DB::raw("DATEDIFF('$hoy', updated_at)")), 2),
                    'cantidad' => $cantidad,
                    'estado_id' => $estado,
                    'guias' => $query->get()->toArray()
                ];
            }
        } else {
            // Si no se seleccionó estado, mostrar todos
            foreach($this->alertas as $estado => $diasAlerta) {
                $query = DB::table('guias')
                    ->where('guia_estado_aprobacion', $estado)
                    ->whereBetween('guia_fecha_emision', [$this->desde, $this->hasta])
                    ->whereRaw("DATEDIFF(?, updated_at) > ?", [$hoy, $diasAlerta]);

                $cantidad = $query->count();

                if($cantidad > 0) {
                    $resultados[] = [
                        'zona' => $this->obtenerNombreEstado($estado),
                        'promedio' => number_format($query->avg(DB::raw("DATEDIFF('$hoy', updated_at)")), 2),
                        'cantidad' => $cantidad,
                        'estado_id' => $estado,
                        'guias' => $query->get()->toArray()
                    ];
                }
            }
        }

        return $resultados;
    }

    public function obtenerNombreEstado($estadoId){
        $estados = [
            1 => 'En Créditos',
            3 => 'Pend. Programación',
            4 => 'Programado',
            7 => 'En Camino'
        ];

        return $estados[$estadoId] ?? 'Desconocido';
    }

    public function render(){
        return view('livewire.programacioncamiones.reporteestadodocumentos', [
            'mostrarFechas' => $this->tipo_reporte == '2',
            'resultados' => $this->resultados
        ]);
    }

    public function generar_excel_estado_documentos(){
        try {
            if (!Gate::allows('generar_excel_estado_documentos')) {
                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
                return;
            }

            $hoy = Carbon::now('America/Lima');
            $guias = [];

            // Obtener las guías según el tipo de reporte
            if($this->tipo_reporte == '1') { // Consulta
                if($this->guia_estado_aprobacion) {
                    $estado = $this->guia_estado_aprobacion;
                    $diasAlerta = $this->alertas[$estado] ?? 0;

                    $guias = DB::table('guias')
                        ->where('guia_estado_aprobacion', $estado)
                        ->whereRaw("DATEDIFF(?, updated_at) > ?", [$hoy, $diasAlerta])
                        ->get();
                } else {
                    foreach($this->alertas as $estado => $diasAlerta) {
                        $guiasEstado = DB::table('guias')
                            ->where('guia_estado_aprobacion', $estado)
                            ->whereRaw("DATEDIFF(?, updated_at) > ?", [$hoy, $diasAlerta])
                            ->get()
                            ->toArray();

                        $guias = array_merge($guias, $guiasEstado);
                    }
                }
            } elseif($this->tipo_reporte == '2') { // Historial
                if(empty($this->desde) || empty($this->hasta)) {
                    session()->flash('error', 'Debe seleccionar ambas fechas para el historial');
                    return;
                }

                if($this->guia_estado_aprobacion) {
                    $estado = $this->guia_estado_aprobacion;
                    $diasAlerta = $this->alertas[$estado] ?? 0;

                    $guias = DB::table('guias')
                        ->where('guia_estado_aprobacion', $estado)
                        ->whereBetween('guia_fecha_emision', [$this->desde, $this->hasta])
                        ->whereRaw("DATEDIFF(?, updated_at) > ?", [$hoy, $diasAlerta])
                        ->get();
                } else {
                    foreach($this->alertas as $estado => $diasAlerta) {
                        $guiasEstado = DB::table('guias')
                            ->where('guia_estado_aprobacion', $estado)
                            ->whereBetween('guia_fecha_emision', [$this->desde, $this->hasta])
                            ->whereRaw("DATEDIFF(?, updated_at) > ?", [$hoy, $diasAlerta])
                            ->get()
                            ->toArray();

                        $guias = array_merge($guias, $guiasEstado);
                    }
                }
            }

            if(empty($guias)) {
                session()->flash('error', 'No se encontraron guías para exportar');
                return;
            }

            // Crear el archivo Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $sheet->setCellValue('A1', 'Fecha de Emisión de Guía');
            $sheet->setCellValue('B1', 'N° Guía');
            $sheet->setCellValue('C1', 'Cliente');
            $sheet->setCellValue('D1', 'N° Factura / Boleta');
            $sheet->setCellValue('E1', 'Valor Venta sin IGV');
            $sheet->setCellValue('F1', 'Estado actual');
            $sheet->setCellValue('G1', 'Días en estado "En credito"');
            $sheet->setCellValue('H1', 'Días en estado "Pendiente Programación"');
            $sheet->setCellValue('I1', 'Días en estado "Programado"');
            $sheet->setCellValue('J1', 'Días en estado "En Camino"');

            // Estilo para los encabezados
            $headerStyle = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE699']]
            ];
            $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

            // Llenar datos
            $row = 2;
            foreach($guias as $guia) {
                $diasEnEstado = abs((int)$hoy->diffInDays(Carbon::parse($guia->updated_at)));
//                number_format($query->avg(DB::raw("DATEDIFF('$hoy', updated_at)")), 2),

                // Mapear estado actual
                $estadoActual = match($guia->guia_estado_aprobacion) {
                    0 => 'Guía anulada',
                    1 => 'En Créditos',
                    2 => 'Despachador',
                    3 => 'Pendiente Programación',
                    4 => 'Programado',
                    5 => 'Aceptado por créditos',
                    6 => 'Estado de facturación',
                    7 => 'En Camino',
                    8 => 'Guía entregado',
                    9 => 'Programación/despacho aprobado',
                    10 => 'Programación/despacho rechazada',
                    11 => 'Guía no entregada',
                    12 => 'Guía rechazada',
                    default => 'Desconocido'
                };

                // Calcular días para cada estado relevante
                $diasCreditos = $guia->guia_estado_aprobacion == 1 ? $diasEnEstado.' días' : '---';
                $diasPendiente = $guia->guia_estado_aprobacion == 3 ? $diasEnEstado.' días' : '---';
                $diasProgramado = $guia->guia_estado_aprobacion == 4 ? $diasEnEstado.' días' : '---';
                $diasCamino = $guia->guia_estado_aprobacion == 7 ? $diasEnEstado.' días' : '---';

                $sheet->setCellValue('A'.$row, $guia->guia_fecha_emision);
                $sheet->setCellValue('B'.$row, $guia->guia_nro_doc);
                $sheet->setCellValue('C'.$row, $guia->guia_nombre_cliente);
                $sheet->setCellValue('D'.$row, $guia->guia_nro_doc_ref);
                $sheet->setCellValue('E'.$row, $guia->guia_importe_total);
                $sheet->setCellValue('F'.$row, $estadoActual);
                $sheet->setCellValue('G'.$row, $diasCreditos);
                $sheet->setCellValue('H'.$row, $diasPendiente);
                $sheet->setCellValue('I'.$row, $diasProgramado);
                $sheet->setCellValue('J'.$row, $diasCamino);

                // Formato para valores numéricos
                $sheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode('#,##0.00');

                $row++;
            }

            // Autoajustar columnas
            foreach(range('A','J') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Formatear el nombre del archivo Excel
            $tipo = $this->tipo_reporte == '1' ? 'consulta' : 'historial';
            $nombre_excel = "reporte_guias_{$tipo}_".date('d-m-Y').".xlsx";

            // Descargar el archivo
            return response()->stream(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename='.$nombre_excel,
                ]
            );

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
        }
    }
}
