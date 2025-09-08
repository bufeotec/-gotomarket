<?php

namespace App\Livewire\Crm;

use App\Models\Stock;
use App\Models\Stocklote;
use App\Models\Logs;
use App\Models\Server;
use App\Models\Familia;
use App\Models\Marca;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Stocks extends Component{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    private $logs;
    private $stock;
    private $server;
    private $stocklote;
    private $familia;
    private $marca;
    public function __construct(){
        $this->logs = new Logs();
        $this->stock = new Stock();
        $this->server = new Server();
        $this->stocklote = new Stocklote();
        $this->familia = new Familia();
        $this->marca = new Marca();
    }
    public $search_stock;
    public $pagination_stock = 10;
    public $listar_stock = [];
    public $listar_stock_lote = [];
    public $obtener_stock_lote = [];
    public $codigo_unitario_actual = '';
    public $id_familia = '';
    public $id_marca = '';

    public function render(){
        $listar_stock_registrados = $this->stock->listar_stock_registrados($this->id_familia, $this->id_marca, $this->search_stock, $this->pagination_stock);
        $listar_familia = $this->familia->listar_familia_activos();
        $listar_marca = $this->marca->listar_marca_activos();
        return view('livewire.crm.stocks', compact('listar_stock_registrados', 'listar_familia', 'listar_marca'));
    }

    public function actualizar_stock(){
        try {

            if (!Gate::allows('actualizar_stock')) {
                session()->flash('error', 'No tiene permisos para actualizar los stock.');
                return;
            }

            DB::beginTransaction();

            $datosResult = $this->server->obtener_stock();
            $this->listar_stock = $datosResult;

            // Arrays temporales para guardar familias y marcas únicas
            $familias_temp = [];
            $marcas_temp = [];

            // Recopilar familias y marcas únicas
            foreach ($this->listar_stock as $lc) {
                if (!empty($lc->FAMILIA) && !in_array($lc->FAMILIA, $familias_temp)) {
                    $familias_temp[] = $lc->FAMILIA;
                }

                if (!empty($lc->MARCA) && !in_array($lc->MARCA, $marcas_temp)) {
                    $marcas_temp[] = $lc->MARCA;
                }
            }

            // Guardar familias que no existen
            foreach ($familias_temp as $familia_concepto) {
                $familiaExistente = Familia::where('familia_concepto', $familia_concepto)->first();

                if (!$familiaExistente) {
                    $microtime = microtime(true);
                    $familia = new Familia();
                    $familia->familia_concepto = $familia_concepto;
                    $familia->familia_microtime = $microtime;
                    $familia->familia_estado = 1;
                    $familia->save();
                }
            }

            // Guardar marcas que no existen
            foreach ($marcas_temp as $marca_concepto) {
                $marcaExistente = Marca::where('marca_concepto', $marca_concepto)->first();

                if (!$marcaExistente) {
                    $microtime = microtime(true);
                    $marca = new Marca();
                    $marca->marca_concepto = $marca_concepto;
                    $marca->marca_microtime = $microtime;
                    $marca->marca_estado = 1;
                    $marca->save();
                }
            }

            $contadorActualizados = 0;
            $contadorCreados = 0;
            $contadorIgnorados = 0;

            foreach ($this->listar_stock as $lc) {
                // Obtener IDs de familia y marca
                $id_familia = null;
                $id_marca = null;

                if (!empty($lc->FAMILIA)) {
                    $familia = Familia::where('familia_concepto', $lc->FAMILIA)->first();
                    $id_familia = $familia ? $familia->id_familia : null;
                }

                if (!empty($lc->MARCA)) {
                    $marca = Marca::where('marca_concepto', $lc->MARCA)->first();
                    $id_marca = $marca ? $marca->id_marca : null;
                }

                // Buscar si ya existe un stock con este código
                $stockExistente = Stock::where('stock_codigo_caja', $lc->CODIGO_CAJA)->first();

                if ($stockExistente) {
                    // Si el stock existe pero tiene estado 0, lo ignoramos
                    if ($stockExistente->stock_estado == 0) {
                        $contadorIgnorados++;
                        continue;
                    }
                    $microtime = microtime(true);

                    // Actualizar registro existente
                    $stockExistente->id_familia = $id_familia ?? null;
                    $stockExistente->id_marca = $id_marca ?? null;
                    $stockExistente->stock_control = $lc->CONTROL ?: null;
                    $stockExistente->stock_familia = $lc->FAMILIA ?: null;
                    $stockExistente->stock_linea = $lc->LINEA ?: null;
                    $stockExistente->stock_marca = $lc->MARCA ?: null;
                    $stockExistente->stock_codigo_caja = $lc->CODIGO_CAJA ?: null;
                    $stockExistente->stock_descripcion_producto = $lc->DESCRIPCION_PRODUCTO ?: null;
                    $stockExistente->stock_unidad = $lc->UNIDAD ?: null;
                    $stockExistente->stock_codigo_unitario = $lc->CODIGO_UNITARIO ?: null;
                    $stockExistente->stock_factor = $lc->FACTOR ?: null;
                    $stockExistente->stock_stock_caja = $lc->STOCK_CAJA ?: null;
                    $stockExistente->stock_stock_unitario = $lc->STOCK_UNITARIO ?: null;
                    $stockExistente->stock_microtime = $microtime;

                    $stockExistente->save();
                    $contadorActualizados++;
                } else {
                    // Crear nuevo registro
                    $microtime = microtime(true);
                    $stock = new Stock();
                    $stock->id_users = Auth::id();
                    $stock->id_familia = $id_familia ?? null;
                    $stock->id_marca = $id_marca ?? null;
                    $stock->stock_control = $lc->CONTROL ?: null;
                    $stock->stock_familia = $lc->FAMILIA ?: null;
                    $stock->stock_linea = $lc->LINEA ?: null;
                    $stock->stock_marca = $lc->MARCA ?: null;
                    $stock->stock_codigo_caja = $lc->CODIGO_CAJA ?: null;
                    $stock->stock_descripcion_producto = $lc->DESCRIPCION_PRODUCTO ?: null;
                    $stock->stock_unidad = $lc->UNIDAD ?: null;
                    $stock->stock_codigo_unitario = $lc->CODIGO_UNITARIO ?: null;
                    $stock->stock_factor = $lc->FACTOR ?: null;
                    $stock->stock_stock_caja = $lc->STOCK_CAJA ?: null;
                    $stock->stock_stock_unitario = $lc->STOCK_UNITARIO ?: null;
                    $stock->stock_microtime = $microtime;
                    $stock->stock_estado = 1;

                    $stock->save();
                    $contadorCreados++;
                }
            }

            DB::commit();
            // Mostrar mensaje con todos los contadores
            session()->flash('success', "Sincronización completada: {$contadorActualizados} registros actualizados, {$contadorCreados} nuevos registros creados.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al actualizar los stock: ' . $e->getMessage());
        }

        // Refrescar la vista
        $this->render();
    }

    public function generar_excel_stock(){
        try {
            if (!Gate::allows('generar_excel_stock')) {
                session()->flash('error', 'No tiene permisos para descargar.');
                return;
            }

            $resultados   = $this->stock->listar_stock_registrados_excel();
            $fecha_actual = Carbon::now('America/Lima');
            $fecha_cell   = $fecha_actual->format('Y-m-d');
            $fecha_file   = $fecha_actual->format('Ymd');

            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Stock');

            $row = 1;

            // Título
            $sheet1->setCellValue('A'.$row, mb_strtoupper("Resultado Stock {$fecha_cell}", 'UTF-8'));
            $sheet1->mergeCells("A{$row}:K{$row}");
            $sheet1->getStyle('A'.$row)->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Encabezados
            $row++;
            $row++;

            $sheet1->setCellValue('A'.$row, 'N°');
            $sheet1->setCellValue('B'.$row, 'FAMILIA');
            $sheet1->setCellValue('C'.$row, 'LÍNEA');
            $sheet1->setCellValue('D'.$row, 'MARCA');
            $sheet1->setCellValue('E'.$row, 'CÓDIGO');
            $sheet1->setCellValue('F'.$row, 'DESCRIPCIÓN');
            $sheet1->setCellValue('G'.$row, 'UNIDAD');
            $sheet1->setCellValue('H'.$row, 'CÓDIGO UNIDAD');
            $sheet1->setCellValue('I'.$row, 'FACTOR');
            $sheet1->setCellValue('J'.$row, 'STOCK CAJAS');
            $sheet1->setCellValue('K'.$row, 'STOCK UNIDADES');

            // Estilo simple encabezados - solo negrita
            $sheet1->getStyle("A{$row}:K{$row}")->getFont()->setBold(true);
            $sheet1->getStyle("A{$row}:K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Anchos
            $sheet1->getColumnDimension('A')->setWidth(5);
            $sheet1->getColumnDimension('B')->setWidth(20);
            $sheet1->getColumnDimension('C')->setWidth(20);
            $sheet1->getColumnDimension('D')->setWidth(14);
            $sheet1->getColumnDimension('E')->setWidth(23);
            $sheet1->getColumnDimension('F')->setWidth(60);
            $sheet1->getColumnDimension('G')->setWidth(12);
            $sheet1->getColumnDimension('H')->setWidth(23);
            $sheet1->getColumnDimension('I')->setWidth(12);
            $sheet1->getColumnDimension('J')->setWidth(16);
            $sheet1->getColumnDimension('K')->setWidth(18);

            // Datos
            $startDataRow = $row + 1;
            $row = $startDataRow;
            $contador = 1;

            foreach ($resultados as $it) {
                $sheet1->setCellValue('A'.$row, $contador);
                $sheet1->setCellValue('B'.$row, $it->stock_familia ?? '-');
                $sheet1->setCellValue('C'.$row, $it->stock_linea ?? '-');
                $sheet1->setCellValue('D'.$row, $it->stock_marca ?? '-');
                $sheet1->setCellValue('E'.$row, $it->stock_codigo_caja ?? '-');
                $sheet1->setCellValue('F'.$row, $it->stock_descripcion_producto ?? '-');
                $sheet1->setCellValue('G'.$row, $it->stock_unidad ?? '-');
                $sheet1->setCellValue('H'.$row, $it->stock_codigo_unitario ?? '-');

                // Campo FACTOR sin decimales (columna I)
                $factor_value = $it->stock_factor ?? null;
                if ($factor_value !== null && is_numeric($factor_value)) {
                    $sheet1->setCellValue('I'.$row, (int)$factor_value);
                } else {
                    $sheet1->setCellValue('I'.$row, '-');
                }

                // Campos con dos decimales (columnas J y K)
                $stock_caja_value = $it->stock_stock_caja ?? null;
                if ($stock_caja_value !== null && is_numeric($stock_caja_value)) {
                    $sheet1->setCellValue('J'.$row, (float)$stock_caja_value);
                } else {
                    $sheet1->setCellValue('J'.$row, '-');
                }

                $stock_unitario_value = $it->stock_stock_unitario ?? null;
                if ($stock_unitario_value !== null && is_numeric($stock_unitario_value)) {
                    $sheet1->setCellValue('K'.$row, (float)$stock_unitario_value);
                } else {
                    $sheet1->setCellValue('K'.$row, '-');
                }

                $contador++;
                $row++;
            }

            $lastRow = $row - 1;

            // Aplicar bordes simples a toda la tabla (encabezados + datos)
            if ($lastRow >= $startDataRow - 2) {
                $tableStyle = [
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                    ]
                ];

                // Aplicar bordes desde encabezados hasta últimos datos
                $sheet1->getStyle("A" . ($startDataRow - 2) . ":K{$lastRow}")->applyFromArray($tableStyle);
            }

            // Formatos numéricos
            if ($lastRow >= $startDataRow) {
                $sheet1->getStyle("J{$startDataRow}:K{$lastRow}")
                    ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            }

            // Nombre de archivo
            $nombre_excel = "RESULTADO_STOCK_{$fecha_file}.xlsx";

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
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
        }
    }

    public function obtener_detalle_stock_lote($codigo){
        if (!Gate::allows('obtener_stock_lote')) {
            session()->flash('error_modal', 'No tiene permisos para ver los detalles del lote.');
            return;
        }

        try {
            DB::beginTransaction();

            // Guardar el código actual para usarlo en la vista
            $this->codigo_unitario_actual = $codigo;

            // Buscar el id_stock en la tabla stocks usando el código unitario
            $stock = DB::table('stocks')
                ->where('stock_codigo_unitario', $codigo)
                ->where('stock_estado', 1)
                ->first();

            if (!$stock) {
                session()->flash('error_modal', 'No se encontró el stock.');
                DB::rollBack();
                return;
            }

            $id_stock = $stock->id_stock;

            $resultado_stock_lote = $this->server->obtener_detalle_stock_lote($codigo);
            $this->obtener_stock_lote = $resultado_stock_lote;

            $contadorActualizados = 0;
            $contadorCreados = 0;
            $contadorIgnorados = 0;

            // Verificar si hay datos para procesar
            if (!empty($this->obtener_stock_lote)) {
                foreach ($this->obtener_stock_lote as $lc) {
                    // Validar que los campos necesarios existan
                    if (empty($lc->CODIGO_UNITARIO) || empty($lc->LOTE)) {
                        $contadorIgnorados++;
                        continue;
                    }

                    // Buscar si ya existe un registro con este id_stock Y lote específico
                    $stockLoteExistente = Stocklote::where('stock_lote_codigo_unitario', $lc->CODIGO_UNITARIO)
                        ->where('stock_lote_lote', $lc->LOTE)
                        ->first();

                    if ($stockLoteExistente) {
                        // Si el registro existe pero tiene estado 0, lo ignoramos
                        if ($stockLoteExistente->stock_lote_estado == 0) {
                            $contadorIgnorados++;
                            continue;
                        }

                        $microtime = microtime(true);

                        // Actualizar registro existente
                        $stockLoteExistente->id_stock = $id_stock;
                        $stockLoteExistente->stock_lote_codigo_caja = $lc->CODIGO_CAJA ?? null;
                        $stockLoteExistente->stock_lote_descripcion_producto = $lc->DESCRIPCION_PRODUCTO ?? null;
                        $stockLoteExistente->stock_lote_codigo_unitario = $lc->CODIGO_UNITARIO ?? null;
                        $stockLoteExistente->stock_lote_lote = $lc->LOTE ?? null;
                        $stockLoteExistente->stock_lote_fecha_fabricacion = $lc->FECHA_FABRICACION ?? null;
                        $stockLoteExistente->stock_lote_fecha_vencimiento = $lc->FECHA_VENCIMIENTO ?? null;
                        $stockLoteExistente->stock_lote_stock_caja = $lc->STOCK_CAJA ?? null;
                        $stockLoteExistente->stock_lote_stock_unitario = $lc->STOCK_UNITARIO ?? null;
                        $stockLoteExistente->stock_lote_microtime = $microtime;

                        $stockLoteExistente->save();
                        $contadorActualizados++;
                    } else {
                        // Crear nuevo registro
                        $microtime = microtime(true);
                        $stockLote = new Stocklote();
                        $stockLote->id_users = Auth::id();
                        $stockLote->id_stock = $id_stock;
                        $stockLote->stock_lote_codigo_caja = $lc->CODIGO_CAJA ?? null;
                        $stockLote->stock_lote_descripcion_producto = $lc->DESCRIPCION_PRODUCTO ?? null;
                        $stockLote->stock_lote_codigo_unitario = $lc->CODIGO_UNITARIO ?? null;
                        $stockLote->stock_lote_lote = $lc->LOTE ?? null;
                        $stockLote->stock_lote_fecha_fabricacion = $lc->FECHA_FABRICACION ?? null;
                        $stockLote->stock_lote_fecha_vencimiento = $lc->FECHA_VENCIMIENTO ?? null;
                        $stockLote->stock_lote_stock_caja = $lc->STOCK_CAJA ?? null;
                        $stockLote->stock_lote_stock_unitario = $lc->STOCK_UNITARIO ?? null;
                        $stockLote->stock_lote_microtime = $microtime;
                        $stockLote->stock_lote_estado = 1;

                        $stockLote->save();
                        $contadorCreados++;
                    }
                }
            }

            DB::commit();

            // Después de actualizar/crear, obtener los datos para mostrar en el modal
            $this->listar_stock_lote = Stocklote::where('id_stock', $id_stock)
                ->where('stock_lote_estado', 1)
                ->orderBy('stock_lote_lote')
                ->get();

            // Mostrar mensaje de éxito
            session()->flash('success_modal', "Sincronización completada: {$contadorActualizados} registros actualizados, {$contadorCreados} nuevos registros creados.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'Ocurrió un error al actualizar los stock: ' . $e->getMessage());
        }
    }
}
