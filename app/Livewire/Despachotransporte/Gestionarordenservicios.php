<?php

namespace App\Livewire\Despachotransporte;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Logs;
use App\Models\TipoServicio;
use App\Models\Programacion;

class Gestionarordenservicios extends Component{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $tiposervicio;
    private $programacion;
    public function __construct(){
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
        $this->programacion = new Programacion();
    }
    public $select_tipo = "";
    // OS
    public $estado_os = "";
    public $codigo_os = "";
    public $id_tipo_servicios = "";
    public $fecha_desde_os = "";
    public $fecha_hasta_os = "";
    public $estados_os_seleccionados = [];
    // GUÍA - CLIENTE
    public $ruc_cliente = "";
    public $codigo_guia = "";
    public $estado_guia = "";
    public $fecha_desde_guia = "";
    public $fecha_hasta_guia = "";
    //
    public $resultados = [];
    public $programacionesContador = [];
    public $estados_seleccionados = [];
    public $estadosSeleccionados = [];
    public $estado_os_temp = "";
    public $estadoComprobante = [];
    public $currentDespachoId;
    public $estadoServicio = [];
    public $listar_detalle_despacho = [];
    public function mount(){
        $this->fecha_desde_os = Carbon::today()->toDateString();
        $this->fecha_hasta_os = Carbon::today()->toDateString();

        $this->fecha_desde_guia = Carbon::today()->toDateString();
        $this->fecha_hasta_guia = Carbon::today()->toDateString();
    }

    public function render(){
        $listar_tipo_servicio = $this->tiposervicio->listar_tipo_servicios_os();
        $conteoProgramacionesPend = DB::table('programaciones')->where('programacion_estado_aprobacion', '=', 0)->count();
        return view('livewire.despachotransporte.gestionarordenservicios', compact('listar_tipo_servicio', 'conteoProgramacionesPend'));
    }

    public function limpiar_tipo_select(){
        if ($this->select_tipo == 1){
            $this->estado_os = "";
            $this->estados_os_seleccionados = [];
            $this->codigo_os = "";
            $this->id_tipo_servicios = "";
            $this->fecha_desde_os = Carbon::today()->toDateString();
            $this->fecha_hasta_os = Carbon::today()->toDateString();
            $this->resultados = [];
        } elseif ($this->select_tipo == 2){
            $this->ruc_cliente = "";
            $this->codigo_guia = "";
            $this->estado_guia = "";
            $this->fecha_desde_guia = Carbon::today()->toDateString();
            $this->fecha_hasta_guia = Carbon::today()->toDateString();
            $this->resultados = [];
        }
    }

    public function agregarEstado() {
        if ($this->estado_os_temp !== '' && $this->estado_os_temp !== null) {
            // Verificar si el estado ya existe
            $existe = collect($this->estadosSeleccionados)->contains('valor', $this->estado_os_temp);

            if (!$existe) {
                $nombresEstados = [
                    '0' => 'Emitido',
                    '1' => 'Aprobado',
                    '2' => 'En Ejecución'
                ];

                $this->estadosSeleccionados[] = [
                    'valor' => $this->estado_os_temp,
                    'nombre' => $nombresEstados[$this->estado_os_temp] ?? 'Desconocido'
                ];

                $this->estado_os_temp = ""; // Limpiar el select
            }
        }
    }

    public function eliminarEstado($index) {
        if (isset($this->estadosSeleccionados[$index])) {
            unset($this->estadosSeleccionados[$index]);
            $this->estadosSeleccionados = array_values($this->estadosSeleccionados); // Reindexar
        }
    }

    public function buscar_orden_servicio(){
        try {
            if (!Gate::allows('buscar_orden_servicio')) {
                session()->flash('error', 'No tiene permisos para buscar una orden de servicio.');
                return;
            }

            $estadosBusqueda = collect($this->estadosSeleccionados)->pluck('valor')->toArray();

            $this->resultados = $this->programacion->listar_programaciones_realizadas_x_fechas_x_estado_new($this->fecha_desde_os, $this->fecha_hasta_os, $estadosBusqueda);

            $this->programacionesContador = [];

            foreach ($this->resultados as $key => $re) {
                if (!isset($this->programacionesContador[$re->id_programacion])) {
                    $this->programacionesContador[$re->id_programacion] = 0;
                }

                $queryDespacho = DB::table('despachos as d')
                    ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                    ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                    ->leftJoin('tarifarios as tar', 'tar.id_tarifario', '=', 'd.id_tarifario')
                    ->where('d.id_programacion', '=', $re->id_programacion);

                // Aplicar filtros adicionales
                if (count($estadosBusqueda) > 0) {
                    $queryDespacho->whereIn('d.despacho_estado_aprobacion', $estadosBusqueda);
                }

                if ($this->estado_os_temp !== '' && $this->estado_os_temp !== null) {
                    $queryDespacho->where('d.despacho_numero_correlativo', 'like', '%'.$this->estado_os_temp.'%');
                }

                if ($this->id_tipo_servicios !== '' && $this->id_tipo_servicios !== null) {
                    $queryDespacho->where('d.id_tipo_servicios', $this->id_tipo_servicios);
                }

                $re->despacho = $queryDespacho->get();

                $this->programacionesContador[$re->id_programacion] += count($re->despacho);

                // Si estamos filtrando y no hay despachos que cumplan, eliminamos la programación
                if ((count($estadosBusqueda) > 0 || $this->codigo_os !== '' || $this->id_tipo_servicios !== '')
                    && $re->despacho->isEmpty()) {
                    unset($this->resultados[$key]);
                    continue;
                }

                foreach ($re->despacho as $des) {
                    $totalVenta = 0;
                    $guiasProcesadas = [];

                    $des->comprobantes = DB::table('despacho_ventas as dv')
                        ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                        ->where('dv.id_despacho', '=', $des->id_despacho)
                        ->select('dv.*', 'g.guia_importe_total_sin_igv')
                        ->get();

                    foreach ($des->comprobantes as $com) {
                        if (!in_array($com->id_guia, $guiasProcesadas)) {
                            $precio = floatval($com->guia_importe_total_sin_igv);
                            $totalVenta += round($precio, 2);
                            $guiasProcesadas[] = $com->id_guia;
                        }
                    }
                    $des->totalVentaDespacho = $totalVenta;
                    $des->id_guia = $des->comprobantes[0]->id_guia ?? null;
                }
            }

            $this->resultados = array_values($this->resultados->toArray());

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al buscar órdenes de servicio: ' . $e->getMessage());
            $this->logs->insertarLog($e);
        }
    }

    public function buscar_guia_cliente(){
        try {
            if (!Gate::allows('buscar_guia_cliente')) {
                session()->flash('error', 'No tiene permisos para buscar guías de cliente.');
                return;
            }

            // Obtener programaciones en el rango de fechas especificado
            $this->resultados = $this->programacion->listar_programaciones_realizadas_x_fechas_guias($this->fecha_desde_guia, $this->fecha_hasta_guia);

            $this->programacionesContador = [];

            foreach ($this->resultados as $key => $re) {
                if (!isset($this->programacionesContador[$re->id_programacion])) {
                    $this->programacionesContador[$re->id_programacion] = 0;
                }

                // Consulta base para despachos
                $queryDespacho = DB::table('despachos as d')
                    ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                    ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                    ->leftJoin('tarifarios as tar', 'tar.id_tarifario', '=', 'd.id_tarifario')
                    ->join('despacho_ventas as dv', 'dv.id_despacho', '=', 'd.id_despacho')
                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                    ->where('d.id_programacion', '=', $re->id_programacion);

                // Aplicar filtros específicos para guías/clientes

                // Filtro por RUC/Cliente
                if ($this->ruc_cliente !== '' && $this->ruc_cliente !== null) {
                    $queryDespacho->where('g.guia_ruc_cliente', 'like', '%'.$this->ruc_cliente.'%');
                }

                // Filtro por código de guía
                if ($this->codigo_guia !== '' && $this->codigo_guia !== null) {
                    $queryDespacho->where('g.guia_nro_doc', 'like', '%'.$this->codigo_guia.'%');
                }

                // Filtro por estado de guía
                if ($this->estado_guia !== '' && $this->estado_guia !== null) {
                    $queryDespacho->where('g.guia_estado_aprobacion', $this->estado_guia);
                }

                // Filtro por rango de fechas de emisión de guía
                if ($this->fecha_desde_guia !== '' && $this->fecha_hasta_guia !== '') {
                    $queryDespacho->whereBetween('g.guia_fecha_emision', [$this->fecha_desde_guia, $this->fecha_hasta_guia]);
                }

                // Agrupar por despacho para evitar duplicados
                $queryDespacho->select(
                    'd.*',
                    't.transportista_nom_comercial',
                    'ts.tipo_servicio_concepto'
                )->distinct();

                $re->despacho = $queryDespacho->get();

                $this->programacionesContador[$re->id_programacion] += count($re->despacho);

                // Si estamos filtrando y no hay despachos que cumplan, eliminamos la programación
                if (($this->ruc_cliente !== '' || $this->codigo_guia !== '' || $this->estado_guia !== '')
                    && $re->despacho->isEmpty()) {
                    unset($this->resultados[$key]);
                    continue;
                }

                // Procesar cada despacho para calcular totales
                foreach ($re->despacho as $des) {
                    $totalVenta = 0;
                    $guiasProcesadas = [];

                    $des->comprobantes = DB::table('despacho_ventas as dv')
                        ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                        ->where('dv.id_despacho', '=', $des->id_despacho);

                    // Aplicar los mismos filtros a los comprobantes si es necesario
                    if ($this->ruc_cliente !== '' && $this->ruc_cliente !== null) {
                        $des->comprobantes->where('g.guia_ruc_cliente', 'like', '%'.$this->ruc_cliente.'%');
                    }

                    if ($this->codigo_guia !== '' && $this->codigo_guia !== null) {
                        $des->comprobantes->where('g.guia_nro_doc', 'like', '%'.$this->codigo_guia.'%');
                    }

                    if ($this->estado_guia !== '' && $this->estado_guia !== null) {
                        $des->comprobantes->where('g.guia_estado_aprobacion', $this->estado_guia);
                    }

                    $des->comprobantes = $des->comprobantes->select('dv.*', 'g.guia_importe_total_sin_igv')->get();

                    foreach ($des->comprobantes as $com) {
                        if (!in_array($com->id_guia, $guiasProcesadas)) {
                            $precio = floatval($com->guia_importe_total_sin_igv);
                            $totalVenta += round($precio, 2);
                            $guiasProcesadas[] = $com->id_guia;
                        }
                    }

                    $des->totalVentaDespacho = $totalVenta;
                    $des->id_guia = $des->comprobantes[0]->id_guia ?? null;
                }
            }

            $this->resultados = array_values($this->resultados->toArray());

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al buscar guías de cliente: ' . $e->getMessage());
            $this->logs->insertarLog($e);
        }
    }

    // DATELLES DEL DESPACHO
    public function listar_informacion_despacho($id_despacho) {
        try {
            // Limpiar estados anteriores
            $this->reset(['estadoComprobante', 'estadoServicio']);
            $this->currentDespachoId = $id_despacho;

            $this->listar_detalle_despacho = DB::table('despachos as d')
                ->join('users as u', 'u.id_users', '=', 'd.id_users')
                ->where('d.id_despacho', '=', $id_despacho)
                ->first();

            if ($this->listar_detalle_despacho) {
                // Obtener todos los despachos con el mismo id_programacion
                $despachosMismoProgramacion = DB::table('despachos')
                    ->where('id_programacion', $this->listar_detalle_despacho->id_programacion)
                    ->get();

                // Determinar si es mixto y provincial
                $this->listar_detalle_despacho->es_mixto = count($despachosMismoProgramacion) > 1;
                $this->listar_detalle_despacho->es_provincial = $this->listar_detalle_despacho->id_tipo_servicios == 2;
                $this->listar_detalle_despacho->es_mixto_provincial = $this->listar_detalle_despacho->es_mixto && $this->listar_detalle_despacho->es_provincial;

                // Resto del código existente...
                $comprobantes = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                    ->where('dv.id_despacho', '=', $id_despacho)
                    ->select('dv.*', 'g.*', 'dv.despacho_detalle_estado_entrega')
                    ->get();

                $this->listar_detalle_despacho->comprobantes = $comprobantes;

                foreach ($comprobantes as $comp) {
                    $key = $id_despacho.'_'.$comp->id_despacho_venta;

                    $estado = $comp->guia_estado_aprobacion;
                    if (isset($comp->despacho_detalle_estado_entrega)) {
                        if ($comp->despacho_detalle_estado_entrega == 0) {
                            $estado = $comp->guia_estado_aprobacion;
                        } elseif (in_array($comp->despacho_detalle_estado_entrega, [8, 11])) {
                            $estado = $comp->despacho_detalle_estado_entrega;
                        }
                    }

                    $this->estadoComprobante[$key] = in_array($estado, [8, 11, 12]) ? $estado : 8;
                }

                // Saber el estado de los servicios transporte
                $servicios = DB::table('despacho_ventas as dv')
                    ->join('servicios_transportes as st', 'st.id_serv_transpt', '=', 'dv.id_serv_transpt')
                    ->where('dv.id_despacho', '=', $id_despacho)
                    ->get();

                $this->listar_detalle_despacho->servicios_transportes = $servicios;

                foreach ($servicios as $serv) {
                    $key = $id_despacho.'_'.$serv->id_despacho_venta;
                    $this->estadoServicio[$key] = in_array($serv->serv_transpt_estado_aprobacion, [5, 6, 3])
                        ? $serv->serv_transpt_estado_aprobacion
                        : 5;
                }
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
        }
    }

    public function cambiarEstadoComprobante() {
        try {
            DB::beginTransaction();
            $id_despacho = $this->currentDespachoId;

            // Obtener información del despacho actual
            $despachoActual = DB::table('despachos')
                ->where('id_despacho', $id_despacho)
                ->first();

            if (!$despachoActual) {
                DB::rollBack();
                session()->flash('errorComprobante', 'Despacho no encontrado.');
                return;
            }

            // Verificar si es programación mixta
            $esProgramacionMixta = $this->esProgramacionMixta($despachoActual->id_programacion);
            $esDespachoLocal = ($despachoActual->id_tipo_servicios == 1);

            // Variables para control de guías en programaciones mixtas
            $guiasLocales = collect();
            $guiasProvinciales = collect();
            $estadoDespachoLocal = null;

            if ($esProgramacionMixta) {
                // Obtener despacho local de la programación
                $despachoLocal = DB::table('despachos')
                    ->where('id_programacion', $despachoActual->id_programacion)
                    ->where('id_tipo_servicios', 1)
                    ->first();

                if ($despachoLocal) {
                    $estadoDespachoLocal = $despachoLocal->despacho_estado_aprobacion;

                    // Obtener guías del despacho local
                    $guiasLocales = DB::table('despacho_ventas')
                        ->where('id_despacho', $despachoLocal->id_despacho)
                        ->pluck('id_guia');
                }

                // Obtener guías de despachos provinciales
                $despachosProvinciales = DB::table('despachos')
                    ->where('id_programacion', $despachoActual->id_programacion)
                    ->where('id_tipo_servicios', 2)
                    ->pluck('id_despacho');

                if ($despachosProvinciales->isNotEmpty()) {
                    $guiasProvinciales = DB::table('despacho_ventas')
                        ->whereIn('id_despacho', $despachosProvinciales)
                        ->pluck('id_guia');
                }
            }

            // Variables para estados
            $tieneGuias = false;
            $tieneGuiasEntregadas = false;
            $tieneGuiasNoEntregadas = false;
            $despachosProvincialesActualizar = [];

            // Procesar cada comprobante
            foreach ($this->estadoComprobante as $key => $estado) {
                $parts = explode('_', $key);
                if ($parts[0] != $id_despacho) continue;

                $id_despacho_venta = $parts[1];
                $es = (int)$estado;

                if (!in_array($es, [8, 11])) {
                    DB::rollBack();
                    session()->flash('errorComprobante', 'Estado inválido seleccionado para guía.');
                    return;
                }

                $despachoVenta = DB::table('despacho_ventas')
                    ->where('id_despacho_venta', $id_despacho_venta)
                    ->first();

                if (!$despachoVenta) {
                    DB::rollBack();
                    session()->flash('errorComprobante', 'Comprobante no encontrado.');
                    return;
                }

                // Lógica especial para programaciones mixtas
                if ($esProgramacionMixta && $esDespachoLocal) {
                    $es = 8; // Forzar estado 8 para despachos locales en mixtos

                    // Identificar despachos provinciales relacionados
                    $despachosProvinciales = DB::table('despachos')
                        ->where('id_programacion', $despachoActual->id_programacion)
                        ->where('id_tipo_servicios', 2) // Provincial
                        ->where('despacho_estado_aprobacion', '!=', 3) // No culminados
                        ->get();

                    foreach ($despachosProvinciales as $despachoProvincial) {
                        $despachosProvincialesActualizar[$despachoProvincial->id_despacho] = $despachoProvincial;
                    }
                }

                // Actualizar despacho_ventas
                DB::table('despacho_ventas')
                    ->where('id_despacho_venta', $id_despacho_venta)
                    ->update([
                        'despacho_detalle_estado_entrega' => $es,
                        'updated_at' => now('America/Lima')
                    ]);

                // Lógica para actualizar estado de guías en programaciones mixtas
                if ($esProgramacionMixta && $esDespachoLocal) {
                    // Verificar si la guía NO está duplicada en despachos provinciales
                    $esGuiaNoDuplicada = !$guiasProvinciales->contains($despachoVenta->id_guia);

                    if ($esGuiaNoDuplicada) {
                        // Actualizar solo las guías no duplicadas a estado 8
                        DB::table('guias')
                            ->where('id_guia', $despachoVenta->id_guia)
                            ->update([
                                'guia_estado_aprobacion' => 8,
                                'updated_at' => now('America/Lima')
                            ]);
                    }
                } elseif (!($esProgramacionMixta && $esDespachoLocal)) {
                    // Actualización normal para casos no mixtos
                    DB::table('guias')
                        ->where('id_guia', $despachoVenta->id_guia)
                        ->update([
                            'guia_estado_aprobacion' => $es,
                            'updated_at' => now('America/Lima')
                        ]);
                }

                // Lógica para registrar en historial según programación mixta
                $debeGuardarEnHistorial = true;

                if ($esProgramacionMixta) {
                    if ($estadoDespachoLocal != 3) {
                        // Si despacho local NO está en estado 3, solo guardar guías locales que NO se duplican
                        if ($esDespachoLocal) {
                            $debeGuardarEnHistorial = !$guiasProvinciales->contains($despachoVenta->id_guia);
                        } else {
                            $debeGuardarEnHistorial = false;
                        }
                    } else {
                        // Si despacho local está en estado 3, solo guardar guías que SÍ se duplican (provinciales)
                        if (!$esDespachoLocal) {
                            $debeGuardarEnHistorial = $guiasLocales->contains($despachoVenta->id_guia);
                        } else {
                            $debeGuardarEnHistorial = false;
                        }
                    }
                }

                // Registrar en historial solo si debe hacerlo
                if ($debeGuardarEnHistorial) {
                    DB::table('historial_guias')->insert([
                        'id_users' => Auth::id(),
                        'id_guia' => $despachoVenta->id_guia,
                        'guia_nro_doc' => DB::table('guias')->where('id_guia', $despachoVenta->id_guia)->value('guia_nro_doc'),
                        'historial_guia_estado_aprobacion' => $es,
                        'historial_guia_fecha_hora' => now('America/Lima'),
                        'historial_guia_estado' => 1,
                        'created_at' => now('America/Lima'),
                        'updated_at' => now('America/Lima')
                    ]);
                }

                // Evaluar estados
                $tieneGuias = true;
                if ($es == 8) $tieneGuiasEntregadas = true;
                if ($es == 11) $tieneGuiasNoEntregadas = true;
            }

            // Determinar estado final del despacho actual
            $estadoDespachoActual = 4; // Por defecto: Rechazado

            if ($tieneGuiasEntregadas) {
                $estadoDespachoActual = 3; // Culminado
            } elseif ($tieneGuiasNoEntregadas && !$tieneGuiasEntregadas) {
                $estadoDespachoActual = 4; // Rechazado
            }

            // Actualizar despacho actual
            DB::table('despachos')
                ->where('id_despacho', $id_despacho)
                ->update(['despacho_estado_aprobacion' => $estadoDespachoActual]);

            // Si es despacho local culminado en mixto, actualizar provinciales a "En tránsito" (2)
            if ($esProgramacionMixta && $esDespachoLocal && $estadoDespachoActual == 3) {
                foreach ($despachosProvincialesActualizar as $id_despacho_provincial => $despachoProvincial) {
                    DB::table('despachos')
                        ->where('id_despacho', $id_despacho_provincial)
                        ->update(['despacho_estado_aprobacion' => 2]); // En tránsito
                }
            }

            DB::commit();
            session()->flash('successComprobante', 'Estados actualizados correctamente.');
            $this->listar_informacion_despacho($id_despacho);
            $this->buscar_programacion();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('errorComprobante', 'Error: ' . $e->getMessage());
        }
    }

    public function esProgramacionMixta($idProgramacion) {
        // Contar cuántos despachos diferentes comparten la misma programación
        $countDespachos = DB::table('despachos')
            ->where('id_programacion', $idProgramacion)
            ->count();

        return ($countDespachos > 1);
    }

    public function verificarAprobacion($idDespacho){
        $liquidacionDetalle = DB::table('liquidacion_detalles')
            ->where('id_despacho', $idDespacho)
            ->first();

        if ($liquidacionDetalle) {
            $liquidacion = DB::table('liquidaciones')
                ->where('id_liquidacion', $liquidacionDetalle->id_liquidacion)
                ->first();

            if ($liquidacion && $liquidacion->liquidacion_estado_aprobacion == 1) {
                return true;
            }
        }

        return false;
    }
}
