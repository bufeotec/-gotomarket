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
        $resultado = $this->programacion->listar_programaciones_realizadas_x_fechas_x_estado($this->desde, $this->hasta, 0);

        foreach ($resultado as $re) {
            $re->despacho = DB::table('despachos as d')
                ->join('transportistas as t', 't.id_transportistas', '=', 'd.id_transportistas')
                ->join('tipo_servicios as ts', 'ts.id_tipo_servicios', '=', 'd.id_tipo_servicios')
                ->leftJoin('tarifarios as tar', 'tar.id_tarifario', '=', 'd.id_tarifario')
                ->where('d.id_programacion', '=', $re->id_programacion)
                ->get();

            foreach ($re->despacho as $des) {
                $totalVenta = 0;
                $guiasProcesadas = []; // Array para rastrear los id_guia ya procesados

                $des->comprobantes = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                    ->where('dv.id_despacho', '=', $des->id_despacho)
                    ->select('dv.*', 'g.guia_importe_total')
                    ->get();

                foreach ($des->comprobantes as $com) {
                    // Verificar si el id_guia ya fue procesado
                    if (!in_array($com->id_guia, $guiasProcesadas)) {
                        $precio = floatval($com->guia_importe_total);  // Usar guia_importe_total
                        $totalVenta += round($precio, 2);
                        $guiasProcesadas[] = $com->id_guia; // Marcar el id_guia como procesado
                    }
                }
                $des->totalVentaDespacho = $totalVenta;

                // Agregar el id_guia al objeto $des (usamos el primer id_guia encontrado)
                if (count($des->comprobantes) > 0) {
                    $des->id_guia = $des->comprobantes[0]->id_guia;
                } else {
                    $des->id_guia = null; // O un valor por defecto si no hay comprobantes
                }
            }
        }

        $conteoProgramacionesPend = DB::table('programaciones')->where('programacion_estado_aprobacion', '=', 0)->count();

        return view('livewire.programacioncamiones.programaciones-pendientes', compact('resultado', 'conteoProgramacionesPend'));
    }

    public function listar_informacion_despacho($id) {
        try {
            // Obtener la información del despacho
            $this->listar_detalle_despacho = DB::table('despachos as d')
                ->join('users as u', 'u.id_users', '=', 'd.id_users')
                ->where('d.id_despacho', '=', $id)
                ->first();

            if ($this->listar_detalle_despacho) {
                // Obtener las guías únicas relacionadas con el despacho
                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                    ->where('dv.id_despacho', '=', $id)
                    ->select('g.*') // Selecciona solo las columnas de la guía
                    ->distinct()
                    ->get();

                // Obtener los servicios de transporte únicos relacionados con el despacho
                $this->listar_detalle_despacho->servicios_transportes = DB::table('despacho_ventas as dv')
                    ->join('servicios_transportes as st', 'st.id_serv_transpt', '=', 'dv.id_serv_transpt')
                    ->where('dv.id_despacho', '=', $id)
                    ->select('st.*') // Selecciona solo las columnas del servicio de transporte
                    ->distinct()
                    ->get();
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
        }
    }

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
    public function cambiarEstadoProgramacionFormulario() {
        try {
            if (!Gate::allows('aprobar_rechazar_programacion')) {
                session()->flash('error_delete', 'No tiene permisos para aprobar o rechazar esta programación.');
                return;
            }

            $this->validate([
                'id_progr' => 'required|integer',
                'estadoPro' => 'required|integer',
            ], [
                'id_progr.required' => 'El identificador es obligatorio.',
                'id_progr.integer' => 'El identificador debe ser un número entero.',

                'estadoPro.required' => 'El estado es obligatorio.',
                'estadoPro.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $correlaApro = null;

            if ($this->estadoPro == 1) { // APROBACIÓN
                /* Listar ultima programación aprobada */
                $correlaApro = $this->programacion->listar_ultima_aprobacion();
            }

            $programacionUpdate = Programacion::find($this->id_progr);
            $programacionUpdate->id_users_programacion = Auth::id();
            $programacionUpdate->programacion_fecha_aprobacion = date('Y-m-d');
            $programacionUpdate->programacion_estado_aprobacion = $this->estadoPro == 4 ? 2 : 1;

            if ($correlaApro) {
                $programacionUpdate->programacion_numero_correlativo = $correlaApro;
            }

            if ($programacionUpdate->save()) {
                // Listar despachos realizados
                $despachos = DB::table('despachos')->where('id_programacion', '=', $this->id_progr)->get();

                foreach ($despachos as $des) {
                    $updateDespacho = Despacho::find($des->id_despacho);
                    $updateDespacho->id_users_programacion = Auth::id();
                    $updateDespacho->despacho_estado_aprobacion = $this->estadoPro;

                    if ($this->estadoPro == 1) {
                        $correlaApro = $this->despacho->listar_ultima_aprobacion_despacho();
                        $updateDespacho->despacho_numero_correlativo = $correlaApro;
                    }

                    $updateDespacho->despacho_fecha_aprobacion = date('Y-m-d');

                    if (!$updateDespacho->save()) {
                        DB::rollBack();
                        session()->flash('error_delete', 'No se pudo aprobar los despachos relacionados a la programación.');
                        return;
                    }

                    // Guardar en historial_guias
                    $despachoVentas = DB::table('despacho_ventas')
                        ->where('id_despacho', $des->id_despacho)
                        ->get();

                    // Array para evitar duplicados en historial_guias
                    $guiasProcesadas = [];

                    foreach ($despachoVentas as $despachoVenta) {
                        // Verificar si el id_guia ya fue procesado
                        if (!in_array($despachoVenta->id_guia, $guiasProcesadas)) {
                            // Obtener el guia_nro_doc desde la tabla guias
                            $guia = DB::table('guias')
                                ->where('id_guia', $despachoVenta->id_guia)
                                ->first();

                            if ($guia) {
                                // Determinar el valor de historial_guia_estado_aprobacion
                                $historialEstadoAprobacion = ($this->estadoPro == 1) ? 9 : 10;

                                // Actualizar el estado en la tabla guias
                                DB::table('guias')
                                    ->where('id_guia', $despachoVenta->id_guia)
                                    ->update(['guia_estado_aprobacion' => $historialEstadoAprobacion]);

                                // Insertar en historial_guias
                                DB::table('historial_guias')->insert([
                                    'id_users' => Auth::id(),
                                    'id_guia' => $despachoVenta->id_guia,
                                    'guia_nro_doc' => $guia->guia_nro_doc,
                                    'historial_guia_estado_aprobacion' => $historialEstadoAprobacion,
                                    'historial_guia_fecha_hora' => Carbon::now('America/Lima'), // Fecha y hora actual de Perú
                                    'historial_guia_estado' => 1, // Estado por defecto
                                    'created_at' => Carbon::now('America/Lima'),
                                    'updated_at' => Carbon::now('America/Lima'),
                                ]);

                                // Marcar el id_guia como procesado
                                $guiasProcesadas[] = $despachoVenta->id_guia;
                            }
                        }
                    }
                    // Actualizar el estado en la tabla servicios_transporte
                    $serviciosTransporte = DB::table('despacho_ventas')
                        ->where('id_despacho', $des->id_despacho)
                        ->get();

                    foreach ($serviciosTransporte as $servicio) {
                        $nuevoEstado = ($this->estadoPro == 1) ? 2 : 3; // 2 para aprobado, 3 para rechazado

                        DB::table('servicios_transportes')
                            ->where('id_serv_transpt', $servicio->id_serv_transpt)
                            ->update(['serv_transpt_estado_aprobacion' => $nuevoEstado]);
                    }
                }

                DB::commit();
                $this->dispatch('hideModalDelete');

                if ($this->estadoPro == 1) {
                    session()->flash('success', 'Registro aprobado correctamente.');
                } else {
                    session()->flash('success', 'Registro rechazado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado de la programación.');
                return;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al cambiar el estado del registro. Por favor, inténtelo nuevamente.');
        }
    }

}
