<?php

namespace App\Livewire\Crm;

use App\Models\Stock;
use App\Models\Stocklote;
use App\Models\Logs;
use App\Models\Server;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Stocks extends Component{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    private $logs;
    private $stock;
    private $server;
    private $stocklote;
    public function __construct(){
        $this->logs = new Logs();
        $this->stock = new Stock();
        $this->server = new Server();
        $this->stocklote = new Stocklote();
    }
    public $search_stock;
    public $paginationstock = 10;
    public $listar_stock = [];
    public $listar_stock_lote = [];
    public $obtener_stock_lote = [];
    public $codigo_unitario_actual = '';

    public function render(){
        $listar_stock_registrados = $this->stock->listar_stock_registrados($this->search_stock, $this->paginationstock);
        return view('livewire.crm.stocks', compact('listar_stock_registrados'));
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

            $contadorActualizados = 0;
            $contadorCreados = 0;
            $contadorIgnorados = 0;

            foreach ($this->listar_stock as $lc) {
                // Buscar si ya existe un vendedor con este código
                $stockExistente = Stock::where('stock_codigo_caja', $lc->CODIGO_CAJA)->first();

                if ($stockExistente) {
                    // Si el vendedor existe pero tiene estado 0, lo ignoramos
                    if ($stockExistente->stock_estado == 0) {
                        $contadorIgnorados++;
                        continue;
                    }
                    $microtime = microtime(true);

                    // Actualizar registro existente
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
