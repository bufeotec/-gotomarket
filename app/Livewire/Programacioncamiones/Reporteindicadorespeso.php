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
use Illuminate\Support\Facades\Log;

class Reporteindicadorespeso extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $guia;
    public function __construct(){
        $this->logs = new Logs();
        $this->guia = new Guia();
    }
    public $ydesde;
    public $yhasta;
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

    public function buscar_reporte_peso() {
        $this->searchdatos = true;

        if (empty($this->ydesde) || empty($this->yhasta)) {
            $this->filtrarData = [];
            return;
        }

        // 1. Obtenemos los despachos únicos con sus fletes
        $despachos = DB::table('despachos as d')
            ->select(
                'd.id_despacho',
                'd.despacho_costo_total as flete',
                'd.id_tipo_servicios as tipo_servicio',
                'dep.departamento_nombre as departamento'
            )
            ->leftJoin('departamentos as dep', 'd.id_departamento', '=', 'dep.id_departamento')
            ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
            ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
            ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->ydesde)
            ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->yhasta)
            ->groupBy('d.id_despacho', 'd.despacho_costo_total', 'd.id_tipo_servicios', 'dep.departamento_nombre')
            ->get();

        // 2. Obtenemos todas las guías y servicios de transporte por despacho
        $detallesPorDespacho = DB::table('despachos as d')
            ->leftJoin('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
            ->leftJoin('guias as g', 'dv.id_guia', '=', 'g.id_guia')
            ->leftJoin('servicios_transportes as st', 'dv.id_serv_transpt', '=', 'st.id_serv_transpt')
            ->select(
                'd.id_despacho',
                'g.id_guia',
                'st.serv_transpt_peso'
            )
            ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->ydesde)
            ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->yhasta)
            ->get()
            ->groupBy('id_despacho');

        // 3. Calculamos el peso para cada despacho
        $despachosConPeso = [];
        foreach ($despachos as $despacho) {
            $pesoTotalKilos = 0;

            // Verificar si hay registros para este despacho
            if (isset($detallesPorDespacho[$despacho->id_despacho])) {
                foreach ($detallesPorDespacho[$despacho->id_despacho] as $item) {
                    // Sumar peso de guías (si existe)
                    if ($item->id_guia) {
                        $detallesGuia = DB::table('guias_detalles')
                            ->where('id_guia', '=', $item->id_guia)
                            ->get();

                        $pesoTotalGramos = $detallesGuia->sum(function ($detalle) {
                            return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                        });
                        $pesoTotalKilos += $pesoTotalGramos / 1000;
                    }

                    // Sumar peso de servicio de transporte (si existe)
                    if ($item->serv_transpt_peso) {
                        $pesoTotalKilos += $item->serv_transpt_peso;
                    }
                }
            }

            $despachosConPeso[] = (object) [
                'id_despacho' => $despacho->id_despacho,
                'flete' => $despacho->flete,
                'peso' => $pesoTotalKilos,
                'tipo_servicio' => $despacho->tipo_servicio,
                'departamento' => $despacho->departamento
            ];
        }

        $this->filtrarData = $despachosConPeso;
        $this->calculateSummary();
        $this->obtenerDatosGraficos();
    }

    public function calculateSummary() {
        $summary = [
            'Total' => ['flete' => 0, 'peso' => 0, 'indicador' => 0, 'objetivo' => $this->objetivos['Total']],
            'Local' => ['flete' => 0, 'peso' => 0, 'indicador' => 0, 'objetivo' => $this->objetivos['Local']],
            'Provincia 1' => ['flete' => 0, 'peso' => 0, 'indicador' => 0, 'objetivo' => $this->objetivos['Provincia 1']],
            'Provincia 2' => ['flete' => 0, 'peso' => 0, 'indicador' => 0, 'objetivo' => $this->objetivos['Provincia 2']],
        ];

        foreach ($this->filtrarData as $resultado) {
            $departamento = strtoupper($resultado->departamento ?? '');

            // Determinar la zona
            $zona = 'Otra';
            if ($resultado->tipo_servicio == 1) {
                $zona = 'Local';
            } elseif (in_array($departamento, ['LIMA', 'CALLAO'])) {
                $zona = 'Local';
            } elseif (in_array($departamento, ['ANCASH', 'ICA', 'HUANCAVELICA', 'HUANUCO', 'LAMBAYEQUE',
                'LA LIBERTAD', 'JUNIN', 'PASCO', 'AYACUCHO'])) {
                $zona = 'Provincia 1';
            } elseif (in_array($departamento, ['APURIMAC', 'AMAZONAS', 'AREQUIPA', 'CAJAMARCA',
                'CUSCO', 'LORETO', 'MADRE DE DIOS', 'MOQUEGUA'])) {
                $zona = 'Provincia 2';
            }

            if (isset($summary[$zona])) {
                $summary[$zona]['flete'] += $resultado->flete;
                $summary[$zona]['peso'] += $resultado->peso;

                $summary['Total']['flete'] += $resultado->flete;
                $summary['Total']['peso'] += $resultado->peso;
            }
        }

        // Calcular indicador (Soles/Kg) para cada zona
        foreach ($summary as $zona => &$data) {
            $data['indicador'] = $data['peso'] > 0 ? round($data['flete'] / $data['peso'], 3) : 0;
        }

        $this->summary = $summary;
    }

    public function obtenerDatosGraficos() {
        $mesesEspanol = [
            1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR',
            5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC'
        ];

        // Inicializar arrays para evitar datos indefinidos
        $mesesPeso = $pesoLocal = $pesoProvincia1 = $pesoProvincia2 = [];
        $mesesFlete = $fleteLocal = $fleteProvincia = [];

        // Consulta optimizada para el gráfico de peso
        $datosPeso = DB::table('despachos as d')
            ->select(
                DB::raw('MONTH(d.despacho_fecha_aprobacion) as mes'),
                DB::raw('YEAR(d.despacho_fecha_aprobacion) as anio'),
                DB::raw('SUM(CASE WHEN d.id_tipo_servicios = 1 OR (dep.departamento_nombre IN ("LIMA", "CALLAO"))
                     THEN (
                         (SELECT COALESCE(SUM(gd.guia_det_peso_gramo * gd.guia_det_cantidad / 1000), 0)
                          FROM despacho_ventas dv
                          LEFT JOIN guias g ON dv.id_guia = g.id_guia
                          LEFT JOIN guias_detalles gd ON g.id_guia = gd.id_guia
                          WHERE dv.id_despacho = d.id_despacho) +
                         (SELECT COALESCE(SUM(st.serv_transpt_peso), 0)
                          FROM despacho_ventas dv
                          LEFT JOIN servicios_transportes st ON dv.id_serv_transpt = st.id_serv_transpt
                          WHERE dv.id_despacho = d.id_despacho AND dv.id_serv_transpt IS NOT NULL)
                     )
                     ELSE 0 END) as peso_local'),

                DB::raw('SUM(CASE WHEN dep.departamento_nombre IN ("ANCASH", "ICA", "HUANCAVELICA", "HUANUCO", "LAMBAYEQUE", "LA LIBERTAD", "JUNIN", "PASCO", "AYACUCHO")
                     THEN (
                         (SELECT COALESCE(SUM(gd.guia_det_peso_gramo * gd.guia_det_cantidad / 1000), 0)
                          FROM despacho_ventas dv
                          LEFT JOIN guias g ON dv.id_guia = g.id_guia
                          LEFT JOIN guias_detalles gd ON g.id_guia = gd.id_guia
                          WHERE dv.id_despacho = d.id_despacho) +
                         (SELECT COALESCE(SUM(st.serv_transpt_peso), 0)
                          FROM despacho_ventas dv
                          LEFT JOIN servicios_transportes st ON dv.id_serv_transpt = st.id_serv_transpt
                          WHERE dv.id_despacho = d.id_despacho AND dv.id_serv_transpt IS NOT NULL)
                     )
                     ELSE 0 END) as peso_provincia1'),

                DB::raw('SUM(CASE WHEN dep.departamento_nombre IN ("APURIMAC", "AMAZONAS", "AREQUIPA", "CAJAMARCA", "CUSCO", "LORETO", "MADRE DE DIOS", "MOQUEGUA")
                     THEN (
                         (SELECT COALESCE(SUM(gd.guia_det_peso_gramo * gd.guia_det_cantidad / 1000), 0)
                          FROM despacho_ventas dv
                          LEFT JOIN guias g ON dv.id_guia = g.id_guia
                          LEFT JOIN guias_detalles gd ON g.id_guia = gd.id_guia
                          WHERE dv.id_despacho = d.id_despacho) +
                         (SELECT COALESCE(SUM(st.serv_transpt_peso), 0)
                          FROM despacho_ventas dv
                          LEFT JOIN servicios_transportes st ON dv.id_serv_transpt = st.id_serv_transpt
                          WHERE dv.id_despacho = d.id_despacho AND dv.id_serv_transpt IS NOT NULL)
                     )
                     ELSE 0 END) as peso_provincia2')
            )
            ->leftJoin('departamentos as dep', 'd.id_departamento', '=', 'dep.id_departamento')
            ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->ydesde)
            ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->yhasta)
            ->groupBy(DB::raw('YEAR(d.despacho_fecha_aprobacion)'), DB::raw('MONTH(d.despacho_fecha_aprobacion)'))
            ->orderBy('anio', 'asc')
            ->orderBy('mes', 'asc')
            ->get();

        // Procesar datos para gráfico de peso
        foreach ($datosPeso as $dato) {
            $mesesPeso[] = $mesesEspanol[$dato->mes] . '-' . substr($dato->anio, 2, 2);
            $pesoLocal[] = (float)$dato->peso_local;
            $pesoProvincia1[] = (float)$dato->peso_provincia1;
            $pesoProvincia2[] = (float)$dato->peso_provincia2;
        }

        // Consulta optimizada para el gráfico de flete
        $datosFlete = DB::table('despachos as d')
            ->select(
                DB::raw('MONTH(d.despacho_fecha_aprobacion) as mes'),
                DB::raw('YEAR(d.despacho_fecha_aprobacion) as anio'),
                DB::raw('SUM(CASE WHEN d.id_tipo_servicios = 1 OR (dep.departamento_nombre IN ("LIMA", "CALLAO"))
                     THEN d.despacho_costo_total ELSE 0 END) as total_local'),
                DB::raw('SUM(CASE WHEN dep.departamento_nombre NOT IN ("LIMA", "CALLAO")
                     THEN d.despacho_costo_total ELSE 0 END) as total_provincia'),
                DB::raw('SUM(CASE WHEN d.id_tipo_servicios = 1 OR (dep.departamento_nombre IN ("LIMA", "CALLAO"))
                     THEN (SELECT COALESCE(SUM(gd.guia_det_peso_gramo * gd.guia_det_cantidad / 1000), 0)
                          FROM despacho_ventas dv
                          LEFT JOIN guias g ON dv.id_guia = g.id_guia
                          LEFT JOIN guias_detalles gd ON g.id_guia = gd.id_guia
                          WHERE dv.id_despacho = d.id_despacho)
                     ELSE 0 END) as peso_local'),
                DB::raw('SUM(CASE WHEN dep.departamento_nombre NOT IN ("LIMA", "CALLAO")
                     THEN (SELECT COALESCE(SUM(gd.guia_det_peso_gramo * gd.guia_det_cantidad / 1000), 0)
                          FROM despacho_ventas dv
                          LEFT JOIN guias g ON dv.id_guia = g.id_guia
                          LEFT JOIN guias_detalles gd ON g.id_guia = gd.id_guia
                          WHERE dv.id_despacho = d.id_despacho)
                     ELSE 0 END) as peso_provincia')
            )
            ->leftJoin('departamentos as dep', 'd.id_departamento', '=', 'dep.id_departamento')
            ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->ydesde)
            ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->yhasta)
            ->groupBy(DB::raw('YEAR(d.despacho_fecha_aprobacion)'), DB::raw('MONTH(d.despacho_fecha_aprobacion)'))
            ->orderBy('anio', 'asc')
            ->orderBy('mes', 'asc')
            ->get();

        // Procesar datos para gráfico de flete
        foreach ($datosFlete as $dato) {
            $mesesFlete[] = $mesesEspanol[$dato->mes] . '-' . substr($dato->anio, 2, 2);

            // Calcular flete local (evitando división por cero)
            $fleteLocal[] = $dato->peso_local != 0 ? round($dato->total_local / $dato->peso_local, 3) : 0;

            // Calcular flete provincia (evitando división por cero)
            $fleteProvincia[] = $dato->peso_provincia != 0 ? round($dato->total_provincia / $dato->peso_provincia, 3) : 0;
        }

        // Asignar datos con valores por defecto si están vacíos
        $this->datosGraficoPeso = [
            'meses' => !empty($mesesPeso) ? $mesesPeso : ['ENE-25'],
            'peso_local' => !empty($pesoLocal) ? $pesoLocal : [0],
            'peso_provincia1' => !empty($pesoProvincia1) ? $pesoProvincia1 : [0],
            'peso_provincia2' => !empty($pesoProvincia2) ? $pesoProvincia2 : [0]
        ];

        $this->datosGraficoFlete = [
            'meses' => !empty($mesesFlete) ? $mesesFlete : ['ENE-25'],
            'flete_local' => !empty($fleteLocal) ? $fleteLocal : [0],
            'flete_provincia' => !empty($fleteProvincia) ? $fleteProvincia : [0]
        ];

        // Emitir eventos para actualizar los gráficos
        $this->dispatch('actualizarGraficoPeso', $this->datosGraficoPeso);
        $this->dispatch('actualizarGraficoFlete', $this->datosGraficoFlete);
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

    public function exportarReportePesoExcel() {
        try {
            if (!Gate::allows('exportar_reporte_peso_excel')) {
                session()->flash('error', 'No tiene permisos para generar este reporte.');
                return;
            }

            if (empty($this->ydesde) || empty($this->yhasta)) {
                session()->flash('error', 'Debe especificar un rango de fechas para generar el reporte.');
                return;
            }

            // 1. Obtenemos los despachos únicos agrupados por id_despacho
            $despachos = DB::table('despachos as d')
                ->select(
                    'd.id_despacho',
                    'd.despacho_fecha_aprobacion',
                    'd.despacho_numero_correlativo',
                    'd.despacho_costo_total',
                    'd.id_tipo_servicios',
                    'd.despacho_estado_aprobacion',
                    DB::raw('MIN(g.guia_departamento) as guia_departamento'),
                    DB::raw('MIN(g.guia_provincia) as guia_provincia'),
                    DB::raw('MIN(g.guia_direc_entrega) as guia_direc_entrega')
                )
                ->join('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->ydesde)
                ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->yhasta)
                ->groupBy('d.id_despacho', 'd.despacho_fecha_aprobacion', 'd.despacho_numero_correlativo',
                    'd.despacho_costo_total', 'd.id_tipo_servicios', 'd.despacho_estado_aprobacion')
                ->get();

            if ($despachos->isEmpty()) {
                session()->flash('error', 'No hay datos para exportar en el rango de fechas seleccionado.');
                return;
            }

            // 2. Obtenemos todas las guías y servicios de transporte por despacho
            $detallesPorDespacho = DB::table('despachos as d')
                ->leftJoin('despacho_ventas as dv', 'd.id_despacho', '=', 'dv.id_despacho')
                ->leftJoin('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->leftJoin('servicios_transportes as st', 'dv.id_serv_transpt', '=', 'st.id_serv_transpt')
                ->select(
                    'd.id_despacho',
                    'g.id_guia',
                    'st.serv_transpt_peso'
                )
                ->whereDate('d.despacho_fecha_aprobacion', '>=', $this->ydesde)
                ->whereDate('d.despacho_fecha_aprobacion', '<=', $this->yhasta)
                ->get()
                ->groupBy('id_despacho');

            // 3. Preparamos los datos para el Excel
            $reporteData = [];
            foreach ($despachos as $despacho) {
                $pesoTotalKilos = 0;
                $serviciosTransporte = [];

                // Calcular peso total (guías + servicios de transporte)
                if (isset($detallesPorDespacho[$despacho->id_despacho])) {
                    foreach ($detallesPorDespacho[$despacho->id_despacho] as $item) {
                        // Sumar peso de guías
                        if ($item->id_guia) {
                            $detallesGuia = DB::table('guias_detalles')
                                ->where('id_guia', '=', $item->id_guia)
                                ->get();

                            $pesoTotalGramos = $detallesGuia->sum(function ($detalle) {
                                return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                            });
                            $pesoTotalKilos += $pesoTotalGramos / 1000;
                        }

                        // Sumar peso de servicios de transporte
                        if ($item->serv_transpt_peso) {
                            $pesoTotalKilos += $item->serv_transpt_peso;
                            $serviciosTransporte[] = $item->serv_transpt_peso;
                        }
                    }
                }

                // Determinar si es mixto (usando la primera guía como referencia)
                $esMixto = false;
                if (isset($detallesPorDespacho[$despacho->id_despacho])) {
                    $primeraGuia = $detallesPorDespacho[$despacho->id_despacho]->first(function ($item) {
                        return $item->id_guia !== null;
                    });

                    if ($primeraGuia) {
                        $validarMixto = DB::table('despacho_ventas as dv')
                            ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                            ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                            ->where('dv.id_guia', '=', $primeraGuia->id_guia)
                            ->where('dv.id_despacho', '<>', $despacho->id_despacho)
                            ->where('d.id_tipo_servicios', '=', 2)
                            ->first();

                        $esMixto = $validarMixto !== null;
                    }
                }

                $typeComprop = $esMixto ? 3 : $despacho->id_tipo_servicios;

                $tipoOS = match ($typeComprop) {
                    1 => 'LOCAL',
                    2 => 'PROVINCIAL',
                    3 => 'MIXTO',
                    default => '',
                };

                // Estado de OS
                $estadoOS = match($despacho->despacho_estado_aprobacion) {
                    0 => 'Pendiente',
                    1 => 'Aprobado',
                    2 => 'En camino',
                    3 => 'Culminado',
                    4 => 'Rechazado',
                    default => 'Desconocido'
                };

                $reporteData[] = [
                    'fecha_os' => $despacho->despacho_fecha_aprobacion,
                    'fecha_guia' => $despacho->despacho_fecha_aprobacion,
                    'numero_os' => $despacho->despacho_numero_correlativo,
                    'peso' => $pesoTotalKilos,
                    'flete' => $despacho->despacho_costo_total,
                    'tipo_os' => $tipoOS,
                    'estado_os' => $estadoOS,
                    'departamento' => $despacho->guia_departamento ?? 'S/N',
                    'provincia' => $despacho->guia_provincia ?? 'S/N',
                    'zona_despacho' => $despacho->guia_direc_entrega ?? 'S/N'
                ];
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
            $row = 4;
            foreach ($reporteData as $item) {
                $sheet->setCellValue('A'.$row, date('d/m/Y', strtotime($item['fecha_os'])));
                $sheet->setCellValue('B'.$row, date('d/m/Y', strtotime($item['fecha_guia'])));
                $sheet->setCellValue('C'.$row, $item['numero_os']);
                $sheet->setCellValue('D'.$row, $item['peso'] ?? 0);
                $sheet->setCellValue('E'.$row, $item['flete'] ?? 0);
                $sheet->setCellValue('F'.$row, $item['tipo_os']);
                $sheet->setCellValue('G'.$row, $item['estado_os']);
                $sheet->setCellValue('H'.$row, $item['departamento']);
                $sheet->setCellValue('I'.$row, $item['provincia']);
                $sheet->setCellValue('J'.$row, $item['zona_despacho']);

                $sheet->getStyle('A'.$row.':J'.$row)->getAlignment()->setHorizontal('center');
                $row++;
            }

            // ========== FORMATO NUMÉRICO ==========
            $sheet->getStyle('D4:D'.($row-1))->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E4:E'.($row-1))->getNumberFormat()->setFormatCode('#,##0.00');

            // ========== ANCHO DE COLUMNA ==========
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(30);
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

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el reporte: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
