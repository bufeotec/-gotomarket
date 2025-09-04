<?php

namespace App\Livewire\Crm;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Campania;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Reportescampanias extends Component{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $campania;
    public function __construct(){
        $this->logs = new Logs();
        $this->campania = new Campania();
    }
    public $paginate_reporte = 10;
    public $id_campania = "";
    public $id_cliente = "";

    public function render(){
        $listar_campania = $this->campania->listar_campanias_ejecucion();

        // Clientes por campaña
        $resultados = $this->campania->obtener_clientes_por_campania_desde_puntos($this->id_campania, $this->paginate_reporte);

        if ($this->id_campania && $resultados->count()) {
            $items = $resultados->items();

            foreach ($items as $i => $r) {
                // SIEMPRE calcular Puntos Ganados Total (no depende de canjes)
                $puntosGanadosTotal = (float) DB::table('puntos as p')
                    ->join('puntos_detalles as pd', 'pd.id_punto', '=', 'p.id_punto')
                    ->where('p.id_campania', $this->id_campania)
                    ->where('p.id_cliente',  $r->id_cliente)
                    ->where('pd.punto_detalle_estado', 1)
                    ->sum('pd.punto_detalle_punto_ganado');

                // Defaults si no hay canjes
                $cantVendedoresConPremio = 0;
                $cantPremiosCanjeados    = 0;
                $puntosCanjeadosTotal    = 0.0;

                // Solo si existen vendedores y usuarios, busca canjes
                $idsVendedores = DB::table('vendedores_intranet')
                    ->where('id_cliente', $r->id_cliente)
                    ->pluck('id_vendedor_intranet');

                if ($idsVendedores->isNotEmpty()) {
                    $idsUsers = DB::table('users')
                        ->whereIn('id_vendedor_intranet', $idsVendedores)
                        ->whereNotNull('id_vendedor_intranet')
                        ->pluck('id_users');

                    if ($idsUsers->isNotEmpty()) {
                        // Canjes activos en la campaña
                        $canjes = DB::table('canjear_puntos')
                            ->whereIn('id_users', $idsUsers)
                            ->where('id_campania', $this->id_campania)
                            ->where('canjear_punto_estado', 1)
                            ->get(['id_canjear_punto', 'id_users']);

                        if ($canjes->isNotEmpty()) {
                            // Cant. Vendedores con Premio = vendedores únicos con al menos un canje
                            $idsUsersConCanje = $canjes->pluck('id_users')->unique();
                            $idsVendConCanje  = DB::table('users')
                                ->whereIn('id_users', $idsUsersConCanje)
                                ->whereNotNull('id_vendedor_intranet')
                                ->pluck('id_vendedor_intranet')
                                ->unique();
                            $cantVendedoresConPremio = $idsVendConCanje->count();

                            // Cantidades y puntos canjeados vigentes
                            $idsCanje = $canjes->pluck('id_canjear_punto');
                            $agg = DB::table('canjear_puntos_detalles')
                                ->whereIn('id_canjear_punto', $idsCanje)
                                ->where('canjear_punto_detalle_estado', 1)
                                ->selectRaw('COALESCE(SUM(canjear_punto_detalle_cantidad),0) AS cant,
                                     COALESCE(SUM(canjear_punto_detalle_total_puntos),0) AS total')
                                ->first();

                            $cantPremiosCanjeados = (int)   ($agg->cant  ?? 0);
                            $puntosCanjeadosTotal = (float) ($agg->total ?? 0);
                        }
                    }
                }

                // Inyectar en el item (siempre con valores, aunque sean 0)
                $items[$i] = (object) array_merge((array) $r, [
                    'cant_vendedores_con_premio' => $cantVendedoresConPremio,
                    'cant_premios_canjeados' => $cantPremiosCanjeados,
                    'puntos_ganados_total' => $puntosGanadosTotal,
                    'puntos_canjeados_total' => $puntosCanjeadosTotal,
                ]);
            }

            // IMPORTANTE: setCollection FUERA del foreach
            $resultados->setCollection(collect($items));
        }

        return view('livewire.crm.reportescampanias', compact('listar_campania', 'resultados'));
    }

    public function generar_excel_detalle_cliente($id_cliente){
        try {
            $id_clientes = $id_cliente;
            $reporte_cliente = $this->campania->reporte_por_cliente($this->id_campania, $id_clientes);

            if (!$reporte_cliente) {
                session()->flash('error', 'No se encontraron datos para la campaña seleccionada.');
                return;
            }

            // CAMBIO: Obtener vendedores que sumaron puntos en lugar de todos los vendedores del cliente
            $vendedores = $this->campania->obtener_vendedores_con_puntos($id_clientes, $this->id_campania);

            // Obtener premios de la campaña
            $premios = $this->campania->obtener_premios_campania($this->id_campania);

            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Detalle Cliente');

            $row = 1;

            // Título de la campaña
            $sheet1->setCellValue('A'.$row, strtoupper($reporte_cliente->campania_nombre ?: 'CAMPAÑA'));
            $total_columnas = 6 + $premios->count(); // 6 columnas fijas + premios
            $ultima_columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($total_columnas);
            $sheet1->mergeCells('A'.$row.':'.$ultima_columna.$row);
            $sheet1->getStyle('A'.$row)->getFont()->setBold(true);
            $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
            $row++; // Línea en blanco

            // Encabezados fijos
            $sheet1->setCellValue('A'.$row, 'ZONA');
            $sheet1->setCellValue('B'.$row, 'CÓDIGO CLIENTE');
            $sheet1->setCellValue('C'.$row, 'CLIENTE');
            $sheet1->setCellValue('D'.$row, 'VENDEDOR DE CLIENTE');
            $sheet1->setCellValue('E'.$row, 'PUNTOS GANADOS');
            $sheet1->setCellValue('F'.$row, 'PUNTOS CANJEADOS');

            // Agregar columnas de premios
            $columna_premio = 7; // Empezar después de la columna F
            foreach ($premios as $premio) {
                $columna_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna_premio);
                $texto_premio = $premio->premio_descripcion . "\n(" . $premio->campania_premio_puntaje . " pts)";
                $sheet1->setCellValue($columna_letra.$row, $texto_premio);
                $sheet1->getStyle($columna_letra.$row)->getAlignment()->setWrapText(true);
                $sheet1->getColumnDimension($columna_letra)->setWidth(20);
                $columna_premio++;
            }

            // Estilo para encabezados
            $rango_encabezados = 'A'.$row.':'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($total_columnas).$row;
            $sheet1->getStyle($rango_encabezados)->getFont()->setBold(true);
            $sheet1->getStyle($rango_encabezados)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('D9D9D9');
            $sheet1->getStyle($rango_encabezados)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Configurar ancho de columnas fijas
            $sheet1->getColumnDimension('A')->setWidth(15);
            $sheet1->getColumnDimension('B')->setWidth(20);
            $sheet1->getColumnDimension('C')->setWidth(50);
            $sheet1->getColumnDimension('D')->setWidth(30);
            $sheet1->getColumnDimension('E')->setWidth(22);
            $sheet1->getColumnDimension('F')->setWidth(22);

            $row++;

            // Guardar la fila de inicio para las combinaciones
            $fila_inicio = $row;

            // Si hay vendedores con puntos, mostrar una fila por cada vendedor
            if ($vendedores->count() > 0) {
                $primera_fila = true;

                foreach ($vendedores as $vendedor) {
                    // Solo llenar los datos del cliente en la primera fila
                    if ($primera_fila) {
                        $sheet1->setCellValue('A'.$row, $reporte_cliente->cliente_zona ?: '-');
                        $sheet1->setCellValue('B'.$row, $reporte_cliente->cliente_codigo_cliente ?: '-');
                        $sheet1->setCellValue('C'.$row, $reporte_cliente->cliente_nombre_cliente ?: '-');
                        $primera_fila = false;
                    }

                    // CAMBIO: Usar el nombre del vendedor desde la consulta con puntos
                    $sheet1->setCellValue('D'.$row, $vendedor->vendedor_nombre ?: '-');

                    // CAMBIO: PUNTOS GANADOS - usar la suma de puntos desde puntos_detalles
                    $sheet1->setCellValue('E'.$row, $vendedor->total_puntos_ganados ?: 0);

                    // PUNTOS CANJEADOS - mantener la lógica original
                    $puntos_canjeados = $this->campania->obtener_puntos_canjeados_vendedor($vendedor->vendedor_dni, $this->id_campania);
                    $sheet1->setCellValue('F'.$row, $puntos_canjeados);

                    // Cantidad de premios canjeados por cada premio
                    $columna_premio = 7;
                    foreach ($premios as $premio) {
                        $columna_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna_premio);
                        $cantidad_canjeada = $this->campania->obtener_premios_canjeados_vendedor($vendedor->vendedor_dni, $premio->id_premio, $this->id_campania);
                        $sheet1->setCellValue($columna_letra.$row, $cantidad_canjeada);
                        $sheet1->getStyle($columna_letra.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $columna_premio++;
                    }

                    $row++;
                }

                // Si hay más de un vendedor, combinar las celdas A, B y C
                if ($vendedores->count() > 1) {
                    $fila_fin = $row - 1;

                    // Combinar celdas para ZONA, CÓDIGO CLIENTE y CLIENTE
                    $sheet1->mergeCells('A'.$fila_inicio.':A'.$fila_fin);
                    $sheet1->mergeCells('B'.$fila_inicio.':B'.$fila_fin);
                    $sheet1->mergeCells('C'.$fila_inicio.':C'.$fila_fin);

                    // Centrar verticalmente el contenido de las celdas combinadas
                    $sheet1->getStyle('A'.$fila_inicio)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet1->getStyle('B'.$fila_inicio)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet1->getStyle('C'.$fila_inicio)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    // Centrar horizontalmente también
                    $sheet1->getStyle('A'.$fila_inicio)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet1->getStyle('B'.$fila_inicio)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet1->getStyle('C'.$fila_inicio)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                }

            } else {
                // Si no hay vendedores con puntos, mostrar una fila con los datos del cliente
                $sheet1->setCellValue('A'.$row, $reporte_cliente->cliente_zona ?: '-');
                $sheet1->setCellValue('B'.$row, $reporte_cliente->cliente_codigo_cliente ?: '-');
                $sheet1->setCellValue('C'.$row, $reporte_cliente->cliente_nombre_cliente ?: '-');
                $sheet1->setCellValue('D'.$row, '-');
                $sheet1->setCellValue('E'.$row, 0);
                $sheet1->setCellValue('F'.$row, 0);

                // Llenar ceros en las columnas de premios
                $columna_premio = 7;
                foreach ($premios as $premio) {
                    $columna_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna_premio);
                    $sheet1->setCellValue($columna_letra.$row, 0);
                    $columna_premio++;
                }
                $row++;
            }

            // Agregar fila de TOTALES al final
            $sheet1->setCellValue('A'.$row, '');
            $sheet1->setCellValue('B'.$row, '');
            $sheet1->setCellValue('C'.$row, '');
            $sheet1->setCellValue('D'.$row, 'TOTALES');
            $sheet1->getStyle('D'.$row)->getFont()->setBold(true);

            // CAMBIO: Calcular totales usando los datos de vendedores con puntos
            $total_puntos_ganados = $vendedores->sum('total_puntos_ganados');
            $total_puntos_canjeados = 0;
            $totales_premios = [];

            foreach ($vendedores as $vendedor) {
                $total_puntos_canjeados += $this->campania->obtener_puntos_canjeados_vendedor($vendedor->vendedor_dni, $this->id_campania);

                foreach ($premios as $premio) {
                    if (!isset($totales_premios[$premio->id_premio])) {
                        $totales_premios[$premio->id_premio] = 0;
                    }
                    $totales_premios[$premio->id_premio] += $this->campania->obtener_premios_canjeados_vendedor($vendedor->vendedor_dni, $premio->id_premio, $this->id_campania);
                }
            }

            $sheet1->setCellValue('E'.$row, $total_puntos_ganados);
            $sheet1->setCellValue('F'.$row, $total_puntos_canjeados);
            $sheet1->getStyle('E'.$row.':F'.$row)->getFont()->setBold(true);

            // Totales de premios
            $columna_premio = 7;
            foreach ($premios as $premio) {
                $columna_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna_premio);
                $sheet1->setCellValue($columna_letra.$row, $totales_premios[$premio->id_premio] ?? 0);
                $sheet1->getStyle($columna_letra.$row)->getFont()->setBold(true);
                $sheet1->getStyle($columna_letra.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $columna_premio++;
            }

            // Formatear el nombre del archivo Excel
            $nombre_cliente = str_replace(' ', '_', $reporte_cliente->cliente_nombre_cliente ?: 'cliente');
            $nombre_excel = sprintf("detalle_cliente_%s.xlsx", $nombre_cliente);

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
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
        }
    }

    public function generar_excel_detalle_ganador_cliente(){
        try {
            $resultado_detalle_cliente = $this->campania->obtener_detalle_cliente($this->id_campania);

            if ($resultado_detalle_cliente->isEmpty()) {
                session()->flash('error', 'No se encontraron datos para la campaña seleccionada.');
                return;
            }

            // Obtener información de la campaña
            $info_campania = $this->campania->obtener_info_campania($this->id_campania);

            // Obtener premios de la campaña
            $premios = $this->campania->obtener_premios_campania($this->id_campania);

            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Detalle General Campaña');

            $row = 1;

            // Título de la campaña
            $nombre_campania = $info_campania ? $info_campania->campania_nombre : 'CAMPAÑA';
            $sheet1->setCellValue('A'.$row, strtoupper($nombre_campania));
            $total_columnas = 6 + $premios->count(); // 6 columnas fijas + premios
            $ultima_columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($total_columnas);
            $sheet1->mergeCells('A'.$row.':'.$ultima_columna.$row);
            $sheet1->getStyle('A'.$row)->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
            $row++; // Línea en blanco

            // Encabezados fijos
            $sheet1->setCellValue('A'.$row, 'ZONA');
            $sheet1->setCellValue('B'.$row, 'CÓDIGO CLIENTE');
            $sheet1->setCellValue('C'.$row, 'CLIENTE');
            $sheet1->setCellValue('D'.$row, 'VENDEDOR DE CLIENTE');
            $sheet1->setCellValue('E'.$row, 'PUNTOS GANADOS');
            $sheet1->setCellValue('F'.$row, 'PUNTOS CANJEADOS');

            // Agregar columnas de premios
            $columna_premio = 7; // Empezar después de la columna F
            foreach ($premios as $premio) {
                $columna_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna_premio);
                $texto_premio = $premio->premio_descripcion . "\n(" . $premio->campania_premio_puntaje . " pts)";
                $sheet1->setCellValue($columna_letra.$row, $texto_premio);
                $sheet1->getStyle($columna_letra.$row)->getAlignment()->setWrapText(true);
                $sheet1->getColumnDimension($columna_letra)->setWidth(20);
                $columna_premio++;
            }

            // Estilo para encabezados
            $rango_encabezados = 'A'.$row.':'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($total_columnas).$row;
            $sheet1->getStyle($rango_encabezados)->getFont()->setBold(true);
            $sheet1->getStyle($rango_encabezados)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('D9D9D9');
            $sheet1->getStyle($rango_encabezados)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Configurar ancho de columnas fijas
            $sheet1->getColumnDimension('A')->setWidth(15);
            $sheet1->getColumnDimension('B')->setWidth(20);
            $sheet1->getColumnDimension('C')->setWidth(50);
            $sheet1->getColumnDimension('D')->setWidth(30);
            $sheet1->getColumnDimension('E')->setWidth(22);
            $sheet1->getColumnDimension('F')->setWidth(22);

            $row++;

            // Variables para totales generales
            $total_general_puntos_ganados = 0;
            $total_general_puntos_canjeados = 0;
            $totales_generales_premios = [];

            // Procesar cada cliente
            foreach ($resultado_detalle_cliente as $cliente) {
                // CAMBIO: Obtener vendedores con puntos del cliente actual
                $vendedores = $this->campania->obtener_vendedores_con_puntos($cliente->id_cliente, $this->id_campania);

                $primera_fila_cliente = true;
                $fila_inicio_cliente = $row;

                // Variables para totales del cliente
                $total_cliente_puntos_ganados = 0;
                $total_cliente_puntos_canjeados = 0;
                $totales_cliente_premios = [];

                if ($vendedores->count() > 0) {
                    foreach ($vendedores as $vendedor) {
                        // Solo mostrar datos del cliente en la primera fila
                        if ($primera_fila_cliente) {
                            $sheet1->setCellValue('A'.$row, $cliente->cliente_zona ?: '-');
                            $sheet1->setCellValue('B'.$row, $cliente->cliente_codigo_cliente ?: '-');
                            $sheet1->setCellValue('C'.$row, $cliente->cliente_nombre_cliente ?: '-');
                            $primera_fila_cliente = false;
                        }

                        // CAMBIO: Usar el nombre del vendedor desde la consulta con puntos
                        $sheet1->setCellValue('D'.$row, $vendedor->vendedor_nombre ?: '-');

                        // CAMBIO: PUNTOS GANADOS - usar la suma de puntos desde puntos_detalles
                        $puntos_ganados = $vendedor->total_puntos_ganados ?: 0;
                        $sheet1->setCellValue('E'.$row, $puntos_ganados);
                        $total_cliente_puntos_ganados += $puntos_ganados;

                        // CAMBIO: PUNTOS CANJEADOS - usar DNI del vendedor
                        $puntos_canjeados = $this->campania->obtener_puntos_canjeados_vendedor($vendedor->vendedor_dni, $this->id_campania);
                        $sheet1->setCellValue('F'.$row, $puntos_canjeados);
                        $total_cliente_puntos_canjeados += $puntos_canjeados;

                        // Cantidad de premios canjeados por cada premio
                        $columna_premio = 7;
                        foreach ($premios as $premio) {
                            $columna_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna_premio);
                            // CAMBIO: usar DNI del vendedor
                            $cantidad_canjeada = $this->campania->obtener_premios_canjeados_vendedor($vendedor->vendedor_dni, $premio->id_premio, $this->id_campania);
                            $sheet1->setCellValue($columna_letra.$row, $cantidad_canjeada);
                            $sheet1->getStyle($columna_letra.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                            // Acumular totales
                            if (!isset($totales_cliente_premios[$premio->id_premio])) {
                                $totales_cliente_premios[$premio->id_premio] = 0;
                            }
                            $totales_cliente_premios[$premio->id_premio] += $cantidad_canjeada;

                            $columna_premio++;
                        }

                        $row++;
                    }

                    // Combinar celdas para zona, código y nombre del cliente si hay múltiples vendedores
                    if ($vendedores->count() > 1) {
                        $fila_fin_cliente = $row - 1;
                        $sheet1->mergeCells('A'.$fila_inicio_cliente.':A'.$fila_fin_cliente);
                        $sheet1->mergeCells('B'.$fila_inicio_cliente.':B'.$fila_fin_cliente);
                        $sheet1->mergeCells('C'.$fila_inicio_cliente.':C'.$fila_fin_cliente);

                        // Centrar verticalmente el contenido combinado
                        $sheet1->getStyle('A'.$fila_inicio_cliente)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                        $sheet1->getStyle('B'.$fila_inicio_cliente)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                        $sheet1->getStyle('C'.$fila_inicio_cliente)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    }
                } else {
                    // Si no hay vendedores con puntos, mostrar una fila con los datos del cliente
                    $sheet1->setCellValue('A'.$row, $cliente->cliente_zona ?: '-');
                    $sheet1->setCellValue('B'.$row, $cliente->cliente_codigo_cliente ?: '-');
                    $sheet1->setCellValue('C'.$row, $cliente->cliente_nombre_cliente ?: '-');
                    $sheet1->setCellValue('D'.$row, '-');
                    $sheet1->setCellValue('E'.$row, 0);
                    $sheet1->setCellValue('F'.$row, 0);

                    // Llenar ceros en las columnas de premios
                    $columna_premio = 7;
                    foreach ($premios as $premio) {
                        $columna_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna_premio);
                        $sheet1->setCellValue($columna_letra.$row, 0);
                        $columna_premio++;
                    }
                    $row++;
                }

                // Solo mostrar totales del cliente si hay vendedores con puntos
                if ($vendedores->count() > 0) {
                    // Agregar fila de TOTALES del cliente
                    $sheet1->setCellValue('A'.$row, '');
                    $sheet1->setCellValue('B'.$row, '');
                    $sheet1->setCellValue('C'.$row, '');
                    $sheet1->setCellValue('D'.$row, 'TOTALES');
                    $sheet1->setCellValue('E'.$row, $total_cliente_puntos_ganados);
                    $sheet1->setCellValue('F'.$row, $total_cliente_puntos_canjeados);

                    // Estilo para totales del cliente
                    $sheet1->getStyle('D'.$row.':F'.$row)->getFont()->setBold(true);
                    $sheet1->getStyle('A'.$row.':'.$ultima_columna.$row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F0F0F0');

                    // Totales de premios del cliente
                    $columna_premio = 7;
                    foreach ($premios as $premio) {
                        $columna_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna_premio);
                        $sheet1->setCellValue($columna_letra.$row, $totales_cliente_premios[$premio->id_premio] ?? 0);
                        $sheet1->getStyle($columna_letra.$row)->getFont()->setBold(true);
                        $sheet1->getStyle($columna_letra.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $columna_premio++;
                    }

                    $row++;
                }

                // Acumular totales generales
                $total_general_puntos_ganados += $total_cliente_puntos_ganados;
                $total_general_puntos_canjeados += $total_cliente_puntos_canjeados;
                foreach ($totales_cliente_premios as $id_premio => $cantidad) {
                    if (!isset($totales_generales_premios[$id_premio])) {
                        $totales_generales_premios[$id_premio] = 0;
                    }
                    $totales_generales_premios[$id_premio] += $cantidad;
                }

                $row++; // Línea en blanco entre clientes
            }

            // Agregar fila de TOTALES GENERALES
            $sheet1->setCellValue('A'.$row, '');
            $sheet1->setCellValue('B'.$row, '');
            $sheet1->setCellValue('C'.$row, '');
            $sheet1->setCellValue('D'.$row, 'TOTALES GENERALES');
            $sheet1->setCellValue('E'.$row, $total_general_puntos_ganados);
            $sheet1->setCellValue('F'.$row, $total_general_puntos_canjeados);

            // Estilo para totales generales
            $sheet1->getStyle('D'.$row.':F'.$row)->getFont()->setBold(true)->setSize(12);
            $sheet1->getStyle('A'.$row.':'.$ultima_columna.$row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E0E0E0');

            // Totales generales de premios
            $columna_premio = 7;
            foreach ($premios as $premio) {
                $columna_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna_premio);
                $sheet1->setCellValue($columna_letra.$row, $totales_generales_premios[$premio->id_premio] ?? 0);
                $sheet1->getStyle($columna_letra.$row)->getFont()->setBold(true)->setSize(12);
                $sheet1->getStyle($columna_letra.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $columna_premio++;
            }

            // Formatear el nombre del archivo Excel
            $nombre_campania_archivo = str_replace(' ', '_', $nombre_campania);
            $nombre_excel = sprintf("detalle_general_campania_%s.xlsx", $nombre_campania_archivo);

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
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
        }
    }

    public function generar_excel_consolidado_premios(){
        try {
            $resultado_consolidado = $this->campania->obtener_consolidado_premios($this->id_campania);

            if (!$resultado_consolidado || $resultado_consolidado->premios->isEmpty()) {
                session()->flash('info', 'No hay premios reclamados para esta campaña.');
                return;
            }

            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Consolidado Premios');

            $row = 1;

            // Título de la campaña
            $sheet1->setCellValue('A'.$row, strtoupper($resultado_consolidado->campania_nombre ?: 'CAMPAÑA'));
            $sheet1->mergeCells('A'.$row.':B'.$row);
            $sheet1->getStyle('A'.$row)->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
            $row++; // Espacio

            // Encabezados de la tabla
            $sheet1->setCellValue('A'.$row, 'PREMIO');
            $sheet1->setCellValue('B'.$row, 'CANTIDAD');
            $sheet1->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
            $sheet1->getStyle('A'.$row.':B'.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('dae9f8');

            // Datos de los premios
            $row++;
            foreach ($resultado_consolidado->premios as $premio) {
                $sheet1->setCellValue('A'.$row, $premio->premio_descripcion . ' (' . $premio->campania_premio_puntaje . ' pts)');
                $sheet1->setCellValue('B'.$row, $premio->cantidad_reclamada);
                $row++;
            }

            // Ajustar dimensiones de columnas
            $sheet1->getColumnDimension('A')->setWidth(35);
            $sheet1->getColumnDimension('B')->setWidth(15);

            // Bordes para la tabla
            $lastRow = $row - 1;
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            $sheet1->getStyle('A'.($row- $resultado_consolidado->premios->count() - 1).':B'.$lastRow)->applyFromArray($styleArray);

            // Centrar contenido de la columna cantidad
            $sheet1->getStyle('B4:B'.$lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $nombre_excel = 'Consolidado_Premios_' . $resultado_consolidado->campania_nombre . '.xlsx';

            return response()->stream(
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

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
        }
    }
}
