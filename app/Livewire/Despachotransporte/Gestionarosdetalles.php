<?php

namespace App\Livewire\Despachotransporte;

use App\Livewire\Intranet\Navegation;
use App\Models\DespachoVenta;
use App\Models\Guia;
use Carbon\Carbon;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;
use App\Models\Vehiculo;
use App\Models\General;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Gestionarosdetalles extends Component{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    private $logs;
    private $despacho;
    private $vehiculo;
    private $general;
    public function __construct(){
        $this->logs = new Logs();
        $this->despacho = new Despacho();
        $this->vehiculo = new Vehiculo();
        $this->general = new General();
    }
    public $id_despacho = "";
    public $numero_os = "";
    public $editando = false;
    public $transportistas;
    public $transportista_seleccionado = "";
    public $transportista_actual = "";
    public $despacho_flete;
    public $despacho_gasto_otros;
    public $despacho_ayudante;
    public $despacho_descripcion_modificado;
    public $despacho_costo_total_sin_igv;
    public $despacho_costo_total_con_igv;
    public $igv = 0.18;
    public $id_tipo_servicios;
    public $despacho_fecha_entrega = "";
    public $programacion_fecha_edit = "";



    public $vehiculos = []; // Agregar esta propiedad
    public $despacho_info;

    public $guias_estados = [];
    public array $guias_fechas = [];
    public array $guias_cargos = [];
    public $despacho_estado_aprobacion_edit = "";
    public $fecha_entrega_espera = "";
    public $despacho_conformidad_factura = "";
    public $despacho_modo_pago_factura = "";
    public $despacho_referencia_acuerdo_comercial = "";
    public $despacho_garantias_servicio = "";
    public $tarifa_tiempo_transporte = "";



    public function mount($id_despacho){
        $this->despacho_fecha_entrega = now('America/Lima')->format('Y-m-d');
        $this->id_despacho = $id_despacho;
        $this->numero_os = DB::table('despachos')->where('id_despacho', '=', $id_despacho)->value('despacho_numero_correlativo');

        $this->programacion_fecha_edit = DB::table('programaciones as p')
            ->join('despachos as d', 'p.id_programacion', 'd.id_programacion')
            ->where('d.id_despacho', '=', $this->id_despacho)
            ->value('programacion_fecha');

        // Obtener la información completa del despacho
        $this->despacho_info = $this->despacho->listar_info_por_id($this->id_despacho);

        // Calcular la fecha de entrega esperada
        $this->calcular_fecha_entrega_esperada();

        // Asignar el transportista seleccionado ANTES de cargar la lista
        $this->transportista_seleccionado = $this->despacho_info->id_transportistas;

        // Cargar el estado actual del despacho
        $this->despacho_estado_aprobacion_edit = $this->despacho_info->despacho_estado_aprobacion;

        // Cargar los datos del transportista actual
        $this->transportista_actual = DB::table('transportistas')
            ->where('id_transportistas', $this->transportista_seleccionado)
            ->first();

        // Cargar el resto de datos
        $this->despacho_descripcion_modificado = $this->despacho_info->despacho_descripcion_modificado;
        $this->id_tipo_servicios = $this->despacho_info->id_tipo_servicios;
        $this->despacho_flete = (float)$this->despacho_info->despacho_flete;
        $this->despacho_gasto_otros = (float)$this->despacho_info->despacho_gasto_otros;
        $this->despacho_ayudante = (float)$this->despacho_info->despacho_ayudante;

        $this->despacho_conformidad_factura = $this->despacho_info->despacho_conformidad_factura;
        $this->despacho_modo_pago_factura = $this->despacho_info->despacho_modo_pago_factura;
        $this->despacho_referencia_acuerdo_comercial = $this->despacho_info->despacho_referencia_acuerdo_comercial;
        $this->despacho_garantias_servicio = $this->despacho_info->despacho_garantias_servicio;

        $this->cargarVehiculos();
        $this->calcularTotales();
    }

    public function calcular_fecha_entrega_esperada(){
        try {
            // Obtener la fecha de programación y el tiempo de transporte
            $datos_entrega = DB::table('programaciones as p')
                ->join('despachos as d', 'p.id_programacion', 'd.id_programacion')
                ->join('tarifarios as t', 'd.id_tarifario', 't.id_tarifario')
                ->where('d.id_despacho', $this->id_despacho)
                ->select('p.programacion_fecha', 't.tarifa_tiempo_transporte')
                ->first();

            if ($datos_entrega && $datos_entrega->programacion_fecha && $datos_entrega->tarifa_tiempo_transporte) {
                $this->tarifa_tiempo_transporte = $datos_entrega->tarifa_tiempo_transporte;

                // Convertir la fecha de programación a Carbon
                $fecha_inicio = \Carbon\Carbon::parse($datos_entrega->programacion_fecha);

                // Sumar los días del tiempo de transporte
                $fecha_entrega_esperada = $fecha_inicio->addDays($datos_entrega->tarifa_tiempo_transporte);

                // Asignar el resultado formateado
                $this->fecha_entrega_espera = $fecha_entrega_esperada->format('Y-m-d');
            } else {
                $this->fecha_entrega_espera = '-';
            }
        } catch (\Exception $e) {
            $this->fecha_entrega_espera = '-';
        }
    }


    public function actualizar_transportista(){
        $id = (string) $this->transportista_seleccionado;
        $this->transportista_actual = DB::table('transportistas')
            ->where('transportista_estado', 1)
            ->where('id_transportistas', $id)
            ->first();

        // Recargar vehículos cuando cambie el transportista
        $this->cargarVehiculos();
    }

    public function cargarVehiculos(){
        if (!$this->despacho_info || !$this->transportista_seleccionado) {
            $this->transportistas = [];
            return;
        }

        // Verificar el tipo de servicio
        if ($this->despacho_info->id_tipo_servicios == 1) { // Local
            $this->transportistas = $this->vehiculo->obtener_vehiculos_con_tarifarios_local($this->despacho_info->despacho_peso, null, 1, $this->transportista_seleccionado);
        } elseif ($this->despacho_info->id_tipo_servicios == 2) { // Provincial
            $this->transportistas = $this->vehiculo->obtener_vehiculos_con_tarifarios_provincial_os_detalle($this->despacho_info->despacho_peso, 2, null, $this->despacho_info->id_departamento ?? null, $this->despacho_info->id_provincia ?? null, $this->despacho_info->id_distrito ?? null);
        }
    }

    public function editar_gestionar_os(){
        if (!Gate::allows('editar_gestionar_os')) {
            session()->flash('error', 'No tiene permisos para editar esta OS.');
            return;
        }

        if (!$this->editando) {
            // Al activar el modo edición, recargar la información del despacho
            $this->despacho_info = $this->despacho->listar_info_por_id($this->id_despacho);
            $this->transportista_seleccionado = $this->despacho_info->id_transportistas;

            // Recargar transportistas disponibles
            $this->cargarVehiculos();
            // Actualizar el transportista actual
            $this->actualizar_transportista();
        }

        $this->editando = !$this->editando;
    }

    public function render(){
        $listar_info = $this->despacho->listar_info_por_id($this->id_despacho);

        // Resto de tu lógica para calcular guías, etc.
        if ($listar_info) {
            $totalVenta = 0;
            $guiasProcesadas = [];

            $guias = DB::table('despacho_ventas as dv')
                ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
                ->where('dv.id_despacho', '=', $this->id_despacho)
                ->select('dv.*', 'g.*')
                ->get();

            foreach ($guias as $guia) {
                if (!in_array($guia->id_guia, $guiasProcesadas)) {
                    // ← Inicializa la fecha de entrega por guía desde despacho_ventas
                    if (!isset($this->guias_fechas[$guia->id_guia])) {
                        // normaliza a 'Y-m-d' para el input type="date"
                        $this->guias_fechas[$guia->id_guia] = $guia->despacho_detalle_fecha_entrega
                            ? \Carbon\Carbon::parse($guia->despacho_detalle_fecha_entrega)->format('Y-m-d')
                            : null;
                    }

                    if (!isset($this->guias_cargos[$guia->id_guia])) {
                        $this->guias_cargos[$guia->id_guia] = $guia->despacho_detalle_documento ?? null;
                    }

                    $detalles = DB::table('guias_detalles')
                        ->where('id_guia', $guia->id_guia)
                        ->get();

                    $pesoTotalGramos = $detalles->sum(function ($detalle) {
                        return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                    });

                    if (!isset($this->guias_estados[$guia->id_guia])) {
                        $this->guias_estados[$guia->id_guia] = $guia->guia_estado_aprobacion ?? '';
                    }

                    $guia->pesoTotalKilos = $pesoTotalGramos / 1000;
                    $guia->volumenTotal = $detalles->sum(function ($detalle) {
                        return $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
                    });

                    $totalVenta += round(floatval($guia->guia_importe_total_sin_igv), 2);
                    $guiasProcesadas[] = $guia->id_guia;
                }

            }

            $guiasUnicas = $guias->whereIn('id_guia', $guiasProcesadas);
            $listar_info->guias = $guiasUnicas;
            $listar_info->totalVentaDespacho = $totalVenta;
        }

        return view('livewire.despachotransporte.gestionarosdetalles', compact('listar_info'));
    }

    public function validar_fecha(){
        try {
            // Validar que la fecha no esté vacía
            if (empty($this->despacho_fecha_entrega)) {
                session()->flash('error_fecha_entrega', 'La fecha de despacho es requerida.');
                return;
            }

            // Validación de rango de fechas (3 días antes y 3 días después)
            $fechaDespacho = Carbon::parse($this->despacho_fecha_entrega);
            $fechaActual = Carbon::now('America/Lima')->startOfDay();

            // Calculamos los límites de fecha
            $fechaLimiteInferior = $fechaActual->copy()->subDays(3);
            $fechaLimiteSuperior = $fechaActual->copy()->addDays(3);

            // Verificamos si la fecha está fuera del rango permitido
            if ($fechaDespacho->lt($fechaLimiteInferior) || $fechaDespacho->gt($fechaLimiteSuperior)) {
                session()->flash('error_fecha_entrega', 'Fecha no válida. Solo se permiten fechas entre ' .
                    $fechaLimiteInferior->format('d-m-Y') . ' y ' .
                    $fechaLimiteSuperior->format('d-m-Y') . '.');

                // Opcional: resetear la fecha a la fecha actual
//                $this->guia_fecha_despacho = $fechaActual->format('Y-m-d');
//                $this->actualizarFechaModal();
                return;
            }

            // Si la fecha es válida, limpiar cualquier mensaje de error previo
            session()->forget('error');

            // Actualizar la fecha para el modal

        } catch (\Exception $e) {
            session()->flash('error_fecha_entrega', 'Error al validar la fecha: ' . $e->getMessage());
            $this->despacho_fecha_entrega = Carbon::now('America/Lima')->format('Y-m-d');
        }
    }

    public function fecha_entrega(){
        try {
            if (!Gate::allows('fecha_entrega')) {
                session()->flash('error_fecha_entrega', 'No tiene permisos.');
                return;
            }

            $this->validate([
                'id_despacho' => 'required|integer',
            ], [
                'id_despacho.required' => 'El identificador es obligatorio.',
                'id_despacho.integer' => 'El identificador debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $despacho = Despacho::find($this->id_despacho);
            $despacho->despacho_fecha_entrega = $this->despacho_fecha_entrega;
            if ($despacho->save()) {

                // Actualiza en bloque todos los registros en despacho_ventas con ese id_despacho
                DespachoVenta::where('id_despacho', $this->id_despacho)
                    ->update([
                        'despacho_detalle_fecha_entrega' => $this->despacho_fecha_entrega,
                        'updated_at' => now('America/Lima'),
                    ]);

                DB::commit();
                $this->dispatch('hide_modal_fecha_entrega');
                session()->flash('success', 'La fecha de entrega ha sido programada correctamente.');
            } else {
                DB::rollBack();
                session()->flash('error_fecha_entrega', 'No se pudo programar la fecha de entrega.');
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_fecha_entrega', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
        }
    }

    public function calcularTotales(){
        // Asegúrate de que los valores sean numéricos
        $flete = is_numeric($this->despacho_flete) ? (float)$this->despacho_flete : 0;
        $otros = is_numeric($this->despacho_gasto_otros) ? (float)$this->despacho_gasto_otros : 0;
        $ayudante = ($this->id_tipo_servicios == 1 && is_numeric($this->despacho_ayudante))
            ? (float)$this->despacho_ayudante
            : 0;

        $this->despacho_costo_total_sin_igv = $flete + $otros + $ayudante;
        $this->despacho_costo_total_con_igv = $this->despacho_costo_total_sin_igv * (1 + $this->igv);
    }

    public function actualizar_despacho_os(){
        try {
            $this->validate([
                'despacho_flete' => 'required|numeric|min:0',
                'despacho_gasto_otros' => 'required|numeric|min:0',
                'despacho_ayudante' => 'nullable|numeric|min:0',
                'transportista_seleccionado' => 'required|exists:transportistas,id_transportistas',
                'despacho_estado_aprobacion_edit' => 'nullable|in:2,3,4',
                // validaciones opcionales para fechas/archivos por guía
                'guias_fechas' => 'sometimes|array',
                'guias_fechas.*' => 'nullable|date',
                'guias_cargos' => 'sometimes|array',
                'guias_cargos.*' => 'nullable',
            ]);

            if (!Gate::allows('actualizar_despacho_os')) {
                session()->flash('error', 'No tiene permisos para actualizar el despacho.');
                return;
            }

            DB::beginTransaction();

            // Actualizar los datos del menú
            $despacho = Despacho::findOrFail($this->id_despacho);

            // Guardar el estado actual antes de actualizar
            $estado_actual = $despacho->despacho_estado_aprobacion;
            // ACUERDOS COMERCIALES
            $despacho_conformidad_factura_actual = $despacho->despacho_conformidad_factura;
            $despacho_modo_pago_factura_actual = $despacho->despacho_modo_pago_factura;
            $despacho_referencia_acuerdo_comercial_actual = $despacho->despacho_referencia_acuerdo_comercial;
            $despacho_garantias_servicio_actual = $despacho->despacho_garantias_servicio;

            $despacho->id_transportistas = $this->transportista_seleccionado;
            $despacho->despacho_flete = $this->despacho_flete;
            $despacho->despacho_ayudante = $this->despacho_ayudante;
            $despacho->despacho_gasto_otros = $this->despacho_gasto_otros;
            $despacho->despacho_costo_total = $this->despacho_costo_total_sin_igv;
            // Actualizar el estado solo si ha cambiado y no está vacío
            if (!empty($this->despacho_estado_aprobacion_edit) &&
                $this->despacho_estado_aprobacion_edit != $estado_actual) {
                $despacho->despacho_estado_aprobacion = $this->despacho_estado_aprobacion_edit;
            }
            $despacho->despacho_descripcion_otros = $this->despacho_descripcion_modificado;

            // ACTUALIZAR ACUERDOS COMERCIALES
            if (!empty($this->despacho_conformidad_factura) &&
                $this->despacho_conformidad_factura != $despacho_conformidad_factura_actual) {
                $despacho->despacho_conformidad_factura = $this->despacho_conformidad_factura;
            }
            if (!empty($this->despacho_modo_pago_factura) &&
                $this->despacho_modo_pago_factura != $despacho_modo_pago_factura_actual) {
                $despacho->despacho_modo_pago_factura = $this->despacho_modo_pago_factura;
            }
            if (!empty($this->despacho_referencia_acuerdo_comercial) &&
                $this->despacho_referencia_acuerdo_comercial != $despacho_referencia_acuerdo_comercial_actual) {
                $despacho->despacho_referencia_acuerdo_comercial = $this->despacho_referencia_acuerdo_comercial;
            }
            if (!empty($this->despacho_garantias_servicio) &&
                $this->despacho_garantias_servicio != $despacho_garantias_servicio_actual) {
                $despacho->despacho_garantias_servicio = $this->despacho_garantias_servicio;
            }

            if (!$despacho->save()) {
                session()->flash('error', 'No se pudo actualizar el despacho.');
                return;
            }

            // Actualiza programacion_fecha
            if (!empty($this->programacion_fecha_edit)) {
                $idProgramacion = ($despacho->id_programacion);

                if ($idProgramacion > 0) {
                    DB::table('programaciones')
                        ->where('id_programacion', $idProgramacion)
                        ->update([
                            'programacion_fecha' => $this->programacion_fecha_edit,
                            'updated_at'         => now('America/Lima'),
                        ]);
                }
            }

            // ACTUALIZAR despacho_ventas (fecha y documento) por guía
            $rows = DB::table('despacho_ventas')
                ->where('id_despacho', $this->id_despacho)
                ->select('id_guia', 'despacho_detalle_fecha_entrega', 'despacho_detalle_documento')
                ->get();

            $dvModel = new DespachoVenta();

            foreach ($rows as $row) {
                $idGuia = $row->id_guia;

                $payload = ['updated_at' => now('America/Lima')];
                $tocar = false;

                // === FECHA: solo si viene y cambió ===
                $nuevaFecha = $this->guias_fechas[$idGuia] ?? null;
                $fechaActual = $row->despacho_detalle_fecha_entrega;

                if (!empty($nuevaFecha)) {
                    // Normaliza ambos a 'Y-m-d' para comparar
                    $fa = $fechaActual ? \Carbon\Carbon::parse($fechaActual)->format('Y-m-d') : null;
                    if ($fa !== $nuevaFecha) {
                        $payload['despacho_detalle_fecha_entrega'] = $nuevaFecha;
                        $tocar = true;
                    }
                }
                // si no vino fecha nueva, no toco la fecha

                // === DOCUMENTO: solo si se subió uno nuevo (UploadedFile) ===
                if (array_key_exists($idGuia, $this->guias_cargos)) {
                    $doc = $this->guias_cargos[$idGuia];

                    if ($doc instanceof UploadedFile) {
                        // Guardar SOLO si hay archivo nuevo
                        $rutaGuardada = $this->general->save_files($doc, 'gestionar_os/documentos');
                        $payload['despacho_detalle_documento'] = $rutaGuardada;
                        $tocar = true;

                        // opcional: refleja en memoria lo guardado para que la vista muestre el link correcto
                        $this->guias_cargos[$idGuia] = $rutaGuardada;
                    }
                    // Si es string (valor existente) o null: NO llamamos a save_files, no tocamos el campo
                }

                if ($tocar) {
                    $dvModel->newQuery()
                        ->where('id_despacho', $this->id_despacho)
                        ->where('id_guia', $idGuia)
                        ->update($payload);
                }
            }

            // Actualizar estados de las guías con validaciones y historial
            if (!empty($this->guias_estados)) {
                foreach ($this->guias_estados as $idGuia => $estado) {
                    if (!empty($estado)) {
                        // Validar que el estado sea uno de los permitidos: 7 (Transito), 3 (Por Programar), 8 (Entregado), 15 (Enviar a NC)
                        if (in_array($estado, [7, 3, 8, 15])) {
                            // Obtener la guía antes de actualizarla
                            $guia = DB::table('guias')->where('id_guia', $idGuia)->first();

                            if ($guia) {
                                $estado_actual = $guia->guia_estado_aprobacion;

                                // Actualizar el estado de la guía
                                DB::table('guias')
                                    ->where('id_guia', $idGuia)
                                    ->update([
                                        'guia_estado_aprobacion' => $estado,
                                        'updated_at' => now('America/Lima')
                                    ]);

                                // Lógica para manejar el historial según los estados
                                if ($estado == 3) { // Si cambia a Por Programar
                                    // Eliminar historial de estados posteriores (Transito, Entregado)
                                    DB::table('historial_guias')
                                        ->where('id_guia', $idGuia)
                                        ->whereIn('historial_guia_estado_aprobacion', [7, 8, 9])
                                        ->delete();
                                }
                                elseif ($estado == 7) { // Si cambia a Transito
                                    // Eliminar historial de estado Entregado
                                    DB::table('historial_guias')
                                        ->where('id_guia', $idGuia)
                                        ->where('historial_guia_estado_aprobacion', 8)
                                        ->delete();
                                }
                                elseif ($estado == 8) { // Si cambia a Entregado
                                    // Crear los registros intermedios faltantes
                                    $historial_existente = DB::table('historial_guias')
                                        ->where('id_guia', $idGuia)
                                        ->pluck('historial_guia_estado_aprobacion')
                                        ->toArray();

                                    $estados_faltantes = [];

                                    // Verificar qué estados intermedios faltan
                                    if (!in_array(3, $historial_existente)) $estados_faltantes[] = 3; // Por Programar
                                    if (!in_array(7, $historial_existente)) $estados_faltantes[] = 7; // Transito

                                    // Insertar los estados faltantes
                                    foreach ($estados_faltantes as $estado_faltante) {
                                        DB::table('historial_guias')->insert([
                                            'id_users' => Auth::id(),
                                            'id_guia' => $idGuia,
                                            'guia_nro_doc' => $guia->guia_nro_doc,
                                            'historial_guia_estado_aprobacion' => $estado_faltante,
                                            'historial_guia_fecha_hora' => Carbon::now('America/Lima'),
                                            'historial_guia_estado' => 1,
                                            'created_at' => Carbon::now('America/Lima'),
                                            'updated_at' => Carbon::now('America/Lima'),
                                        ]);
                                    }
                                }
                                elseif ($estado == 15) { // Si cambia a Enviar a NC
                                    // Para este estado, mantener el historial existente
                                    // No se elimina nada, solo se agrega el nuevo estado
                                }

                                // Insertar el nuevo estado en el historial (solo si no existe ya)
                                $existe_historial = DB::table('historial_guias')
                                    ->where('id_guia', $idGuia)
                                    ->where('historial_guia_estado_aprobacion', $estado)
                                    ->exists();

                                if (!$existe_historial) {
                                    DB::table('historial_guias')->insert([
                                        'id_users' => Auth::id(),
                                        'id_guia' => $idGuia,
                                        'guia_nro_doc' => $guia->guia_nro_doc,
                                        'historial_guia_estado_aprobacion' => $estado,
                                        'historial_guia_fecha_hora' => Carbon::now('America/Lima'),
                                        'historial_guia_estado' => 1,
                                        'created_at' => Carbon::now('America/Lima'),
                                        'updated_at' => Carbon::now('America/Lima'),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            session()->flash('success', 'Despacho actualizado correctamente.');
            $this->calcularTotales();
            $this->calcular_fecha_entrega_esperada();
            $this->editando = false;
            // recargar el transportista desde lo que quedó grabado
            $this->transportista_seleccionado = $despacho->id_transportistas;
            $this->transportista_actual = DB::table('transportistas')
                ->where('id_transportistas', $despacho->id_transportistas)
                ->first();

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }

    public function listar_guias_despachos($id){
        $this->id_despacho = base64_decode($id);
    }

    public function anular_os(){
        try {
            if (!Gate::allows('anular_os')) {
                session()->flash('error_delete', 'No tiene permisos para anular esta OS.');
                return;
            }

            $this->validate([
                'id_despacho' => 'required|integer',
            ], [
                'id_despacho.required' => 'El identificador es obligatorio.',
                'id_despacho.integer' => 'El identificador debe ser un número entero.',
            ]);

            DB::beginTransaction();

            // Obtener el despacho inicial
            $despacho = Despacho::find($this->id_despacho);

            // Verificar si es mixto (buscar despachos con misma programación)
            $despachosRelacionados = Despacho::where('id_programacion', $despacho->id_programacion)->get();

            $esMixto = $despachosRelacionados->count() > 1;
            $esLocal = $despacho->id_tipo_servicio == 1;
            $esProvincial = $despacho->id_tipo_servicio == 2;

            if ($esMixto) {
                // Procesar despacho mixto
                foreach ($despachosRelacionados as $desp) {
                    // Obtener las guías asociadas al despacho
                    $despachoVentas = DespachoVenta::where('id_despacho', $desp->id_despacho)->get();

                    foreach ($despachoVentas as $dv) {
                        $guia = Guia::find($dv->id_guia);
                        if ($guia) {
                            // Si es el despacho local del mixto o provincial del mixto
                            if ($desp->id_tipo_servicio == 1) { // Local en mixto
                                $desp->despacho_estado_aprobacion = 4;
                                $guia->guia_estado_aprobacion = 3;
                            } else { // Provincial en mixto
                                $desp->despacho_estado_aprobacion = 4;
                                $guia->guia_estado_aprobacion = 3;
                            }
                            $guia->save();
                        }
                    }
                    $desp->save();
                }
            } else {
                // Procesar despacho directo (local o provincial)
                $despachoVentas = DespachoVenta::where('id_despacho', $despacho->id_despacho)->get();

                foreach ($despachoVentas as $dv) {
                    $guia = Guia::find($dv->id_guia);
                    if ($guia) {
                        $despacho->despacho_estado_aprobacion = 4;
                        $guia->guia_estado_aprobacion = 3;
                        $guia->save();
                    }
                }
                $despacho->save();
            }

            DB::commit();
            $this->dispatch('hide_anular_os');
            session()->flash('success', 'OS anulada correctamente con todas sus guías.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al anular la OS: '.$e->getMessage());
        }
    }
}
