<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Despacho;
use App\Models\General;
use App\Models\Logs;
use App\Models\Programacion;
use App\Models\Transportista;
use App\Models\Historialguia;
use App\Models\Historialdespachoventa;
use App\Models\Serviciotransporte;
use App\Models\Guia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class ProgramacionesPendientes extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $desde;
    public $hasta;
    public $listar_detalle_despacho = [];
    public $id_progr = "";
    public $estadoPro = "";
    public $id_serv_transpt = "";
    public $serv_transpt_estado_aprobacion = "";
    public $guias_info = [];
    public $guia_detalle = [];
    public $estado_programacion = "";
    public $resultados = [];
    public $selectedDespachos = [];
    public $actionType = '';
    public $selectedItems = [];
    public $programacionesContador = [];
    /* ---------------------------------------- */
    private $logs;
    private $programacion;
    private $despacho;
    private $general;
    private $historialguia;
    private $serviciotransporte;
    private $guia;
    private $historialdespachoventa;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->general = new General();
        $this->historialguia = new Historialguia();
        $this->serviciotransporte = new Serviciotransporte();
        $this->guia = new Guia();
        $this->historialdespachoventa = new Historialdespachoventa();
    }
    public function mount()
    {
        $this->desde = Carbon::today()->toDateString(); // Fecha actual
        $this->hasta = Carbon::tomorrow()->toDateString(); // Un día después de la fecha actual
    }

    public function render(){
//        $resultado = $this->programacion->listar_programaciones_realizadas_x_fechas_x_estado($this->desde, $this->hasta, 0);
//
//        foreach ($resultado as $re) {
//            $re->despacho = DB::table('despachos as d')
//                ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
//                ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
//                ->leftJoin('tarifarios as tar', 'tar.id_tarifario', '=', 'd.id_tarifario')
//                ->where('d.id_programacion', '=', $re->id_programacion)
//                ->get();
//
//            foreach ($re->despacho as $des) {
//                $totalVenta = 0;
//                $guiasProcesadas = []; // Array para rastrear los id_guia ya procesados
//
//                $des->comprobantes = DB::table('despacho_ventas as dv')
//                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
//                    ->where('dv.id_despacho', '=', $des->id_despacho)
//                    ->select('dv.*', 'g.guia_importe_total_sin_igv')
//                    ->get();
//
//                foreach ($des->comprobantes as $com) {
//                    // Verificar si el id_guia ya fue procesado
//                    if (!in_array($com->id_guia, $guiasProcesadas)) {
//                        $precio = floatval($com->guia_importe_total_sin_igv);
//                        $totalVenta += round($precio, 2);
//                        $guiasProcesadas[] = $com->id_guia; // Marcar el id_guia como procesado
//                    }
//                }
//                $des->totalVentaDespacho = $totalVenta;
//
//                // Agregar el id_guia al objeto $des (usamos el primer id_guia encontrado)
//                if (count($des->comprobantes) > 0) {
//                    $des->id_guia = $des->comprobantes[0]->id_guia;
//                } else {
//                    $des->id_guia = null; // O un valor por defecto si no hay comprobantes
//                }
//            }
//        }

        $conteoProgramacionesPend = DB::table('programaciones')->where('programacion_estado_aprobacion', '=', 0)->count();

        return view('livewire.programacioncamiones.programaciones-pendientes', compact('conteoProgramacionesPend'));
    }

    public function buscar_programacion() {
        $this->selectedDespachos = [];

        $this->resultados = $this->programacion->listar_programaciones_realizadas_x_fechas_x_estado($this->desde, $this->hasta, $this->estado_programacion);
        $this->programacionesContador = [];

        // Procesa los resultados
        foreach ($this->resultados as $key => $re) {

            if (!isset($this->programacionesContador[$re->id_programacion])) {
                $this->programacionesContador[$re->id_programacion] = 0;
            }

            $queryDespacho = DB::table('despachos as d')
                ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                ->leftJoin('tarifarios as tar', 'tar.id_tarifario', '=', 'd.id_tarifario')
                ->where('d.id_programacion', '=', $re->id_programacion);

            // Solo aplicar filtro si se seleccionó un estado específico
            if ($this->estado_programacion !== '' && $this->estado_programacion !== null) {
                // Si el estado seleccionado es "Tránsito" (2), filtramos por despacho_estado_aprobacion
                if ($this->estado_programacion == 2) {
                    $queryDespacho->where('d.despacho_estado_aprobacion', '=', 2);
                }
                // Para otros estados (0: Emitido, 1: Aprobado), no filtramos aquí (ya se filtró en la consulta principal)
            }

            $re->despacho = $queryDespacho->get();

            // Incrementar el contador para esta programación
            $this->programacionesContador[$re->id_programacion] += count($re->despacho);

            // Si estamos filtrando por un estado específico y no hay despachos que cumplan, eliminamos la programación
            if ($this->estado_programacion !== '' && $this->estado_programacion !== null) {
                // Para Tránsito, verificamos que haya despachos en ese estado
                if ($this->estado_programacion == 2 && $re->despacho->isEmpty()) {
                    unset($this->resultados[$key]);
                    continue;
                }
                // Para Emitido (0) y Aprobado (1), ya fueron filtrados en la consulta principal
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

                if (count($des->comprobantes) > 0) {
                    $des->id_guia = $des->comprobantes[0]->id_guia;
                } else {
                    $des->id_guia = null;
                }
            }
        }

        // Reindexar el array después de posibles eliminaciones
        $this->resultados = array_values($this->resultados->toArray());
    }

//    public function listar_informacion_despacho($id) {
//        try {
//            // Obtener la información del despacho
//            $this->listar_detalle_despacho = DB::table('despachos as d')
//                ->join('users as u', 'u.id_users', '=', 'd.id_users')
//                ->where('d.id_despacho', '=', $id)
//                ->first();
//
//            if ($this->listar_detalle_despacho) {
//                // Obtener las guías únicas relacionadas con el despacho
//                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas as dv')
//                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
//                    ->where('dv.id_despacho', '=', $id)
//                    ->select('g.*') // Selecciona solo las columnas de la guía
//                    ->distinct()
//                    ->get();
//
//                // Obtener los servicios de transporte únicos relacionados con el despacho
//                $this->listar_detalle_despacho->servicios_transportes = DB::table('despacho_ventas as dv')
//                    ->join('servicios_transportes as st', 'st.id_serv_transpt', '=', 'dv.id_serv_transpt')
//                    ->where('dv.id_despacho', '=', $id)
//                    ->select('st.*') // Selecciona solo las columnas del servicio de transporte
//                    ->distinct()
//                    ->get();
//            }
//        } catch (\Exception $e) {
//            $this->logs->insertarLog($e);
//        }
//    }

    public function listar_detalle_guia($id_despacho) {
        // Obtener los id_guia desde despacho_ventas usando el id_despacho
        $id_guias = DB::table('despacho_ventas')
            ->where('id_despacho', $id_despacho)
            ->pluck('id_guia')
            ->toArray();

        // Obtener los detalles de las guías desde la tabla guias_detalles
        $this->guia_detalle = DB::table('guias_detalles')
            ->whereIn('id_guia', $id_guias)
            ->get();
    }

    public function cambiarEstadoProgramacion($id,$estado){ //  $estado = 1 aprobar , 2 desaprobar
        if ($id){
            $this->id_progr = $id;
            $this->estadoPro = $estado;
        }
    }

    public function prepareAction($actionType){
        $this->actionType = $actionType;
        $this->resetValidation();
    }

    public function cambiarEstadoProgramacionFormulario() {
        try {
            $this->validate([
                'selectedDespachos' => 'required|array|min:1',
                'selectedDespachos.*' => 'integer|exists:despachos,id_despacho',
                'actionType' => 'required|in:1,4'
            ]);

            DB::beginTransaction();

            // Verificar permisos
            $permisoRequerido = $this->actionType == 1 ? 'aprobar_programacion' : 'rechazar_programacion';
            if (!Gate::allows($permisoRequerido)) {
                session()->flash('error', 'No tiene permisos para esta acción.');
                return;
            }

            // Obtener todos los despachos seleccionados con sus programaciones
            $despachosSeleccionados = Despacho::whereIn('id_despacho', $this->selectedDespachos)
                ->select('id_despacho', 'id_programacion', 'despacho_estado_aprobacion')
                ->get();

            // 1. Validar que todos los despachos seleccionados estén en estado 0 (Pendiente)
            $despachosInvalidos = $despachosSeleccionados->where('despacho_estado_aprobacion', '!=', 0);
            if ($despachosInvalidos->isNotEmpty()) {
                session()->flash('error', 'Algunos despachos seleccionados no están en estado Pendiente.');
                return;
            }

            // 2. Validar programaciones mixtas (que tengan más de un despacho)
            $programaciones = $despachosSeleccionados->groupBy('id_programacion');

            foreach ($programaciones as $id_programacion => $despachos) {
                // Obtener el total de despachos para esta programación
                $totalDespachosProgramacion = Despacho::where('id_programacion', $id_programacion)
                    ->where('despacho_estado_aprobacion', 0) // Solo contar los pendientes
                    ->count();

                // Si es programación mixta (más de un despacho) y no seleccionó todos
                if ($totalDespachosProgramacion > 1 && $despachos->count() < $totalDespachosProgramacion) {
                    $programacion = DB::table('programaciones')
                        ->where('id_programacion', $id_programacion)
                        ->first();

                    $fechaProgramacion = $programacion ? date('d/m/Y', strtotime($programacion->programacion_fecha)) : 'N/A';

                    session()->flash('error', "La programación del $fechaProgramacion tiene despachos sin seleccionar. Debe seleccionar todos los despachos de esta programación.");
                    return;
                }
            }

            // Procesar cada despacho seleccionado
            foreach ($this->selectedDespachos as $id_despacho) {
                // Obtener el despacho sin relaciones
                $despacho = Despacho::findOrFail($id_despacho);

                // Verificación adicional
                if ($despacho->despacho_estado_aprobacion != 0) {
                    continue;
                }

                // 1. Actualizar despacho
                $despacho->id_users_programacion = Auth::id();
                $despacho->despacho_estado_aprobacion = $this->actionType;
                $despacho->despacho_fecha_aprobacion = now();

                if ($this->actionType == 1) { // Solo para aprobación
                    $correlativo = $this->despacho->listar_ultima_aprobacion_despacho();
                    $despacho->despacho_numero_correlativo = $correlativo;

                    // Obtener la programación asociada al despacho
                    $programacion = DB::table('programaciones')
                        ->where('id_programacion', $despacho->id_programacion)
                        ->first();

                    // Actualizar programación si no está aprobada
                    if ($programacion && $programacion->programacion_estado_aprobacion != 1) {
                        $correlaProg = $this->programacion->listar_ultima_aprobacion();
                        DB::table('programaciones')
                            ->where('id_programacion', $despacho->id_programacion)
                            ->update([
                                'id_users_programacion' => Auth::id(),
                                'programacion_fecha_aprobacion' => now(),
                                'programacion_estado_aprobacion' => 1,
                                'programacion_numero_correlativo' => $correlaProg
                            ]);
                    }
                }
                $despacho->save();

                // 2. Actualizar guías relacionadas
                $guias = DB::table('despacho_ventas')
                    ->where('id_despacho', $id_despacho)
                    ->pluck('id_guia')
                    ->unique();

                $estadoGuia = $this->actionType == 1 ? 9 : 10; // 9=Aprobado, 10=Rechazado

                foreach ($guias as $id_guia) {
                    DB::table('guias')
                        ->where('id_guia', $id_guia)
                        ->update(['guia_estado_aprobacion' => $estadoGuia]);

                    $guia = DB::table('guias')->where('id_guia', $id_guia)->first();

                    DB::table('historial_guias')->insert([
                        'id_users' => Auth::id(),
                        'id_guia' => $id_guia,
                        'guia_nro_doc' => $guia->guia_nro_doc,
                        'historial_guia_estado_aprobacion' => $estadoGuia,
                        'historial_guia_fecha_hora' => now('America/Lima'),
                        'historial_guia_descripcion' => null,
                        'historial_guia_estado' => 1,
                        'created_at' => Carbon::now('America/Lima'),
                        'updated_at' => Carbon::now('America/Lima'),
                    ]);
                }

                // 3. Actualizar servicios de transporte
                $estadoServicio = $this->actionType == 1 ? 2 : 3; // 2=Aprobado, 3=Rechazado
                DB::table('servicios_transportes')
                    ->whereIn('id_serv_transpt', function($query) use ($id_despacho) {
                        $query->select('id_serv_transpt')
                            ->from('despacho_ventas')
                            ->where('id_despacho', $id_despacho);
                    })
                    ->update(['serv_transpt_estado_aprobacion' => $estadoServicio]);
            }

            DB::commit();

            $this->dispatch('hideModalDelete');
            $this->selectedDespachos = [];
            $this->buscar_programacion();
            $message = $this->actionType == 1 ?
                'Despachos aprobados correctamente.' :
                'Despachos rechazados correctamente.';

            session()->flash('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    // PARA EL CAMBIO DE ESTADO 'EN CAMINO' O EN 'TRANSITO' COMO CHUCHA LO QUIERAS LLAMARLE

    // Función para preparar la acción "En Camino"

    public function confirmar_encamino(){
        // Validación inicial
        if (empty($this->selectedDespachos)) {
            session()->flash('error', 'Debe seleccionar al menos un despacho.');
            return;
        }
    }
    public function cambiarEstadoEnCamino(){
        try {
            $this->validate([
                'selectedDespachos' => 'required|array|min:1',
                'selectedDespachos.*' => 'integer|exists:despachos,id_despacho',
            ]);

            // 1. Validar que todos estén en estado Aprobado (1)
            $despachosInvalidos = Despacho::whereIn('id_despacho', $this->selectedDespachos)
                ->where('despacho_estado_aprobacion', '!=', 1)
                ->pluck('id_despacho')
                ->toArray();

            if (!empty($despachosInvalidos)) {
                session()->flash('error', 'Algunos despachos seleccionados no están en estado Aprobado.');
                return;
            }

//            // 2. Validar programaciones mixtas (que tengan más de un despacho)
//            $despachosSeleccionados = Despacho::whereIn('id_despacho', $this->selectedDespachos)
//                ->select('id_despacho', 'id_programacion')
//                ->get();

            // Agrupar por programación
//            $programaciones = $despachosSeleccionados->groupBy('id_programacion');
//
//            foreach ($programaciones as $id_programacion => $despachos) {
//                // Obtener el total de despachos aprobados para esta programación
//                $totalDespachosProgramacion = Despacho::where('id_programacion', $id_programacion)
//                    ->where('despacho_estado_aprobacion', 1) // Solo contar los aprobados
//                    ->count();
//
//                // Si es programación mixta (más de un despacho) y no seleccionó todos
//                if ($totalDespachosProgramacion > 1 && $despachos->count() < $totalDespachosProgramacion) {
//                    $programacion = DB::table('programaciones')
//                        ->where('id_programacion', $id_programacion)
//                        ->first();
//
//                    $fechaProgramacion = $programacion ? date('d/m/Y', strtotime($programacion->programacion_fecha)) : 'N/A';
//
//                    session()->flash('error', "La programación del $fechaProgramacion tiene despachos sin seleccionar. Debe seleccionar todos los despachos de esta programación.");
//                    return;
//                }
//            }

            DB::beginTransaction();

            // Procesar todos los despachos seleccionados
            $despachos = Despacho::whereIn('id_despacho', $this->selectedDespachos)->get();

            foreach ($despachos as $despacho) {
                // Actualizar estado del despacho
                $despacho->despacho_estado_aprobacion = 2;
                $despacho->save();

                // Obtener guías relacionadas
                $guias = DB::table('despacho_ventas')
                    ->where('id_despacho', $despacho->id_despacho)
                    ->join('guias', 'guias.id_guia', '=', 'despacho_ventas.id_guia')
                    ->select('guias.id_guia', 'guias.guia_nro_doc')
                    ->get();

                // Actualizar estado de las guías (7 = En Camino)
                DB::table('guias')
                    ->whereIn('id_guia', $guias->pluck('id_guia'))
                    ->update(['guia_estado_aprobacion' => 7]);

                // Insertar en historial de guías
                $historialGuias = $guias->map(function ($guia) {
                    return [
                        'id_users' => Auth::id(),
                        'id_guia' => $guia->id_guia,
                        'guia_nro_doc' => $guia->guia_nro_doc,
                        'historial_guia_estado_aprobacion' => 7,
                        'historial_guia_fecha_hora' => now('America/Lima'),
                        'historial_guia_descripcion' => null,
                        'historial_guia_estado' => 1,
                        'created_at' => Carbon::now('America/Lima'),
                        'updated_at' => Carbon::now('America/Lima'),
                    ];
                })->toArray();

                DB::table('historial_guias')->insert($historialGuias);

                // Actualizar servicios de transporte (4 = En Camino)
                DB::table('servicios_transportes')
                    ->whereIn('id_serv_transpt', function($query) use ($despacho) {
                        $query->select('id_serv_transpt')
                            ->from('despacho_ventas')
                            ->where('id_despacho', $despacho->id_despacho);
                    })
                    ->update(['serv_transpt_estado_aprobacion' => 4]);
            }

            DB::commit();

            session()->flash('success', 'Despachos marcados como "En Camino" correctamente.');
            $this->dispatch('hideModalEnCamino');
            $this->selectedDespachos = [];
            $this->buscar_programacion();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al cambiar estado: ' . $e->getMessage());
            $this->logs->insertarLog($e);
        }
    }

    // DATELLES DEL DESPACHO

    public $estadoComprobante = [];
    public $currentDespachoId;
    public $estadoServicio = [];
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

    public function cambiarEstadoComprobante(){
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
            $esProgramacionMixta = DB::table('despachos')
                    ->where('id_programacion', $despachoActual->id_programacion)
                    ->count() > 1;

            $esDespachoLocal = ($despachoActual->id_tipo_servicios == 1);

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

                // Actualizar guías (excepto para despachos locales en mixtos)
                if (!($esProgramacionMixta && $esDespachoLocal)) {
                    DB::table('guias')
                        ->where('id_guia', $despachoVenta->id_guia)
                        ->update([
                            'guia_estado_aprobacion' => $es,
                            'updated_at' => now('America/Lima')
                        ]);
                }

                // Registrar en historial
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
