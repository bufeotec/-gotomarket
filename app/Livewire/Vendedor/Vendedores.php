<?php

namespace App\Livewire\Vendedor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Vendedor;
use App\Models\Server;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpParser\Node\Expr\New_;
use Carbon\Carbon;

class Vendedores extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $vendedor;
    private $server;
    public function __construct(){
        $this->logs = new Logs();
        $this->vendedor = new Vendedor();
        $this->server = new Server();
    }
    public $listar_vendedores = [];
    public $paginacion_vendedores = 10;
    public $id_vendedor = "";
    public $vendedor_estado = "";
    public $messageDeshabilitarVendedor;
    public $messageEliminarVendedor;
    public $vendedor_codigo_intranet = "";

    public function render(){
        $vendedores = $this->vendedor->listar_vendedores_activos($this->paginacion_vendedores);
        $ultimaActualizacion = Vendedor::where('vendedor_estado', '!=', 0)
            ->orderBy('id_vendedor', 'desc')
            ->value('vendedor_fecha_ultima_actualizacion');

        return view('livewire.vendedor.vendedores', compact('vendedores', 'ultimaActualizacion'));
    }

    public function actualizar_vendedores(){
        try {
            DB::beginTransaction();

            $datosResult = $this->server->obtenervendedores();
            $this->listar_vendedores = $datosResult;

            $contadorActualizados = 0;
            $contadorCreados = 0;
            $contadorIgnorados = 0;

            foreach ($this->listar_vendedores as $lv) {
                // Buscar si ya existe un vendedor con este código
                $vendedorExistente = Vendedor::where('vendedor_codigo_vendedor_starsoft', $lv->COD_VEN)->first();

                if ($vendedorExistente) {
                    // Si el vendedor existe pero tiene estado 0, lo ignoramos
                    if ($vendedorExistente->vendedor_estado == 0) {
                        $contadorIgnorados++;
                        continue;
                    }
                    $microtime = microtime(true);

                    // Actualizar registro existente
                    $vendedorExistente->vendedor_des = $lv->DES_VEN ?: null;
                    $vendedorExistente->vendedor_ruta = $lv->COD_RUTA ?: null;
                    $vendedorExistente->vendedor_codigo_zona = $lv->COD_ZONA ?: null;
                    $vendedorExistente->vendedor_fecha_ultima_actualizacion = Carbon::now('America/Lima');
                    $vendedorExistente->vendedor_direccion = $lv->DIR_VEN ?: null;
                    $vendedorExistente->vendedor_telefono = $lv->TEL_VEN ?: null;
                    $vendedorExistente->vendedor_email = $lv->EMA_VEN ?: null;
                    $vendedorExistente->vendedor_ruc = $lv->RUC_VEN ?: null;
                    $vendedorExistente->vendedor_cod_terri = $lv->COD_TERR ?: null;
                    $vendedorExistente->vendedor_cod_seg = $lv->COD_SEG ?: null;
                    $vendedorExistente->vendedor_cod_ubi = $lv->COD_UBI ?: null;
                    $vendedorExistente->vendedor_usuario = $lv->USUARIO ?: null;
                    $vendedorExistente->vendedor_num_doc = $lv->NUM_DOC ?: null;
                    $vendedorExistente->vendedor_por_comision = $lv->POR_COMISION ?: null;
                    $vendedorExistente->vendedor_flg_ecommerce = $lv->FLG_ECOMMERCE ?: null;
                    $vendedorExistente->vendedor_mt = $microtime;

                    $vendedorExistente->save();
                    $contadorActualizados++;
                } else {
                    // Crear nuevo registro
                    $microtime = microtime(true);
                    $vendedor = new Vendedor();
                    $vendedor->vendedor_codigo_intranet = null;
                    $vendedor->vendedor_codigo_vendedor_starsoft = $lv->COD_VEN ?: null;
                    $vendedor->vendedor_des = $lv->DES_VEN ?: null;
                    $vendedor->vendedor_nombre = null;
                    $vendedor->vendedor_seg_terri = null;
                    $vendedor->vendedor_ruta = $lv->COD_RUTA ?: null;
                    $vendedor->vendedor_codigo_zona = $lv->COD_ZONA ?: null;
                    $vendedor->vendedor_nombre_zona = null;
                    $vendedor->vendedor_fecha_ultima_actualizacion = Carbon::now('America/Lima');
                    $vendedor->vendedor_direccion = $lv->DIR_VEN ?: null;
                    $vendedor->vendedor_telefono = $lv->TEL_VEN ?: null;
                    $vendedor->vendedor_email = $lv->EMA_VEN ?: null;
                    $vendedor->vendedor_ruc = $lv->RUC_VEN ?: null;
                    $vendedor->vendedor_cod_terri = $lv->COD_TERR ?: null;
                    $vendedor->vendedor_cod_seg = $lv->COD_SEG ?: null;
                    $vendedor->vendedor_cod_ubi = $lv->COD_UBI ?: null;
                    $vendedor->vendedor_cod_fecr = null;
                    $vendedor->vendedor_usuario = $lv->USUARIO ?: null;
                    $vendedor->vendedor_num_doc = $lv->NUM_DOC ?: null;
                    $vendedor->vendedor_por_comision = $lv->POR_COMISION ?: null;
                    $vendedor->vendedor_flg_ecommerce = $lv->FLG_ECOMMERCE ?: null;
                    $vendedor->vendedor_estado = 1;
                    $vendedor->vendedor_mt = $microtime;

                    $vendedor->save();
                    $contadorCreados++;
                }
            }

            DB::commit();
            // Mostrar mensaje con todos los contadores
            session()->flash('success', "Sincronización completada: {$contadorActualizados} registros actualizados, {$contadorCreados} nuevos registros creados.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al actualizar los vendedores: ' . $e->getMessage());
        }

        // Refrescar la vista
        $this->render();
    }

    public function btn_deshabilitar_vendedor($id_v,$estado){
        $id = base64_decode($id_v);
        $status = $estado;
        if ($id){
            $this->id_vendedor = $id;
            $this->vendedor_estado = $status;
            if ($status == 2){
                $this->messageDeshabilitarVendedor = "¿Está seguro que desea deshabilitar este registro?";
            }else{
                $this->messageDeshabilitarVendedor = "¿Está seguro que desea habilitar este registro?";
            }
        }
    }

    public function deshabilitar_vendedor(){
        try {
            if (!Gate::allows('deshabilitar_vendedor')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }

            $this->validate([
                'id_vendedor' => 'required|integer',
                'vendedor_estado' => 'required|integer',
            ], [
                'id_vendedor.required' => 'El identificador es obligatorio.',
                'id_vendedor.integer' => 'El identificador debe ser un número entero.',

                'vendedor_estado.required' => 'El estado es obligatorio.',
                'vendedor_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $deshabilitar_vendedor = Vendedor::find($this->id_vendedor);
            $deshabilitar_vendedor->vendedor_estado = $this->vendedor_estado;
            if ($deshabilitar_vendedor->save()) {
                DB::commit();
                $this->dispatch('hideModalDeshabilitarVendedor');
                if ($this->vendedor_estado == 2){
                    session()->flash('success', 'Registro deshabilitado correctamente.');
                }else{
                    session()->flash('success', 'Registro habilitado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_deshabilitar_vendedor', 'No se pudo cambiar el estado del registro.');
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }

    public function btn_eliminar_vendedor($id_v){
        $id = base64_decode($id_v);
        if ($id){
            $this->id_vendedor = $id;
            $this->messageEliminarVendedor = "¿Está seguro que desea eliminar este registro?";
        }
    }

    public function eliminar_vendedor(){
        try {
            if (!Gate::allows('eliminar_vendedor')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }

            $this->validate([
                'id_vendedor' => 'required|integer',
            ], [
                'id_vendedor.required' => 'El identificador es obligatorio.',
                'id_vendedor.integer' => 'El identificador debe ser un número entero.',
            ]);

            DB::beginTransaction();

            $vendedor = Vendedor::find($this->id_vendedor);

            if (!$vendedor) {
                session()->flash('error_eliminar_vendedor', 'Vendedor no encontrado.');
                return;
            }

            $vendedor->vendedor_estado = 0;
            $vendedor->vendedor_codigo_intranet = null;

            if ($vendedor->save()) {
                DB::commit();
                $this->dispatch('hideModalEliminarVendedor');
                session()->flash('success', 'Registro eliminado correctamente.');
            } else {
                DB::rollBack();
                session()->flash('error_eliminar_vendedor', 'No se pudo eliminar el registro.');
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al eliminar el registro: ' . $e->getMessage());
        }
    }

    public function btn_codigo_intranet($id_v){
        $id = base64_decode($id_v);
        if ($id){
            $this->id_vendedor = $id;
            $this->vendedor_codigo_intranet = "";
        }
    }

    public function guardar_codigo_intranet(){
        try {
            if (!Gate::allows('guardar_codigo_intranet')) {
                session()->flash('error_codigo_intranet', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }

            $this->validate([
                'id_vendedor' => 'required|integer',
                'vendedor_codigo_intranet' => 'required|string|max:50',
            ], [
                'id_vendedor.required' => 'El identificador es obligatorio.',
                'id_vendedor.integer' => 'El identificador debe ser un número entero.',

                'vendedor_codigo_intranet.required' => 'El código correlativo es obligatorio.',
            ]);
            DB::beginTransaction();

            $vendedor = Vendedor::find($this->id_vendedor);

            if (!$vendedor) {
                session()->flash('error_codigo_intranet', 'Vendedor no encontrado.');
                return;
            }

            $correlativo = trim($this->vendedor_codigo_intranet);
            $codigo = 'VEN' . $correlativo;
            $validar_cod = DB::table('vendedores')->where('vendedor_codigo_intranet', '=', $codigo)->exists();
            if (!$validar_cod) {
                $vendedor->vendedor_codigo_intranet = $codigo;
                if ($vendedor->save()) {
                    DB::commit();
                    $this->dispatch('hideModalCodigoIntranet');
                    session()->flash('success', 'Corrido creado correctamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error_codigo_intranet', 'No se pudo eliminar el registro.');
                    return;
                }
            } else {
                session()->flash('error_codigo_intranet', 'El código ya esta registrado.');
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_codigo_intranet', 'Ocurrió un error al eliminar el registro: ' . $e->getMessage());
        }
    }

}
