<?php

namespace App\Livewire\Despachotransporte;

use App\Models\DespachoVenta;
use App\Models\Guia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;

class Gestionarosdetalles extends Component
{
    private $logs;
    private $despacho;
    public function __construct(){
        $this->logs = new Logs();
        $this->despacho = new Despacho();
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

    public function mount($id_despacho){
        $this->id_despacho = $id_despacho;
        $this->numero_os = DB::table('despachos')->where('id_despacho', '=', $id_despacho)->value('despacho_numero_correlativo');

        // Obtener la información del despacho
        $despachoInfo = $this->despacho->listar_info_por_id($this->id_despacho);

        // Asignar el transportista seleccionado ANTES de cargar la lista
        $this->transportista_seleccionado = $despachoInfo->id_transportistas;

        // Cargar todos los transportistas activos
        $this->transportistas = DB::table('transportistas')
            ->where('transportista_estado', '=', 1)
            ->get();

        // Cargar los datos del transportista actual
        $this->transportista_actual = DB::table('transportistas')
            ->where('id_transportistas', $this->transportista_seleccionado)
            ->first();

        // Cargar el resto de datos
        $this->despacho_descripcion_modificado = $despachoInfo->despacho_descripcion_modificado;
        $this->id_tipo_servicios = $despachoInfo->id_tipo_servicios;
        $this->despacho_flete = (float)$despachoInfo->despacho_flete;
        $this->despacho_gasto_otros = (float)$despachoInfo->despacho_gasto_otros;
        $this->despacho_ayudante = (float)$despachoInfo->despacho_ayudante;

        $this->calcularTotales();
    }
    public function actualizar_transportista(){
        $id = (string) $this->transportista_seleccionado;
        $this->transportista_actual = DB::table('transportistas')
            ->where('transportista_estado', 1)
            ->where('id_transportistas', $id)
            ->first();
    }

    public function editar_gestionar_os(){
        if (!$this->editando) {
            // Al activar el modo edición, recargar el transportista actual del despacho
            $despachoInfo = $this->despacho->listar_info_por_id($this->id_despacho);
            $this->transportista_seleccionado = $despachoInfo->id_transportistas;

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
                    $detalles = DB::table('guias_detalles')
                        ->where('id_guia', $guia->id_guia)
                        ->get();

                    $pesoTotalGramos = $detalles->sum(function ($detalle) {
                        return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                    });

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
                'transportista_seleccionado' => 'required|exists:transportistas,id_transportistas'
            ]);

            if (!Gate::allows('actualizar_despacho_os')) {
                session()->flash('error', 'No tiene permisos para actualizar el despacho.');
                return;
            }

            DB::beginTransaction();

            // Actualizar los datos del menú
            $despacho = Despacho::findOrFail($this->id_despacho);
            $despacho->id_transportistas = $this->transportista_seleccionado;
            $despacho->despacho_flete = $this->despacho_flete;
            $despacho->despacho_ayudante = $this->despacho_ayudante;
            $despacho->despacho_gasto_otros = $this->despacho_gasto_otros;
            $despacho->despacho_costo_total = $this->despacho_costo_total_sin_igv;
            $despacho->despacho_descripcion_otros = $this->despacho_descripcion_modificado;

            if (!$despacho->save()) {
                session()->flash('error', 'No se pudo actualizar el despacho.');
                return;
            }

            DB::commit();
            session()->flash('success', 'Despacho actualizado correctamente.');
            $this->calcularTotales();
//            $this->despacho->listar_info_por_id($despacho->id_despacho);
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

    public function anular_os() {
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
