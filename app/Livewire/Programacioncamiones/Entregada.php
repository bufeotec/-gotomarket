<?php

namespace App\Livewire\Programacioncamiones;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Guia;
use App\Models\Logs;
use App\Models\Server;

class Entregada extends Component{
    private $guia;
    private $logs;
    private $server;
    public function __construct(){
        $this->guia = new Guia();
        $this->logs = new Logs();
        $this->server = new Server();
    }
    public $searchGuia = [];
    public $guias_estado_tres = [];
    public $selectedFacturas = [];
    public $importeTotalVenta = 0;
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $guia_fecha_despacho;
    public function mount(){
        $this->guia_fecha_despacho = now()->addDay()->format('Y-m-d');
    }

    public function render(){
        // Obtener las guías
        $guiasQuery = Guia::whereIn('guia_estado_aprobacion', [3, 11, 10]);

        // Filtrar por nombre del cliente si searchGuia tiene valor
        if (!empty($this->searchGuia)) {
            $guiasQuery->where(function($query) {
                $query->where('guia_nombre_cliente', 'like', '%' . $this->searchGuia . '%')
                    ->orWhere('guia_nro_doc', 'like', '%' . $this->searchGuia . '%')
                    ->orWhere('guia_nro_doc_ref', 'like', '%' . $this->searchGuia . '%');
            });
        }

        $guias = $guiasQuery->get();

        // Calcular el peso y volumen total para cada guía
        $this->guias_estado_tres = $guias->map(function ($guia) {
            $detalles = DB::table('guias_detalles')
                ->where('id_guia', $guia->id_guia)
                ->get();

            $pesoTotalGramos = $detalles->sum(function ($detalle) {
                return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
            });

            $pesoTotalKilos = $pesoTotalGramos / 1000;

            $volumenTotal = $detalles->sum(function ($detalle) {
                return $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
            });

            $guia->peso_total = $pesoTotalKilos;
            $guia->volumen_total = $volumenTotal;

            return $guia;
        });
        return view('livewire.programacioncamiones.entregada');
    }

    public function seleccionarFactura($id_guia){
        // Buscar la factura por su ID
        $factura = Guia::find($id_guia);

        if (!$factura) {
            session()->flash('error', 'Guía no encontrada.');
            return;
        }

        // Validar que la factura no esté ya en el array selectedFacturas
        $comprobanteExiste = collect($this->selectedFacturas)->first(function ($facturaSeleccionada) use ($factura) {
            return $facturaSeleccionada['id_guia'] === $factura->id_guia;
        });

        if ($comprobanteExiste) {
            session()->flash('error', 'Esta guía ya fue agregada.');
            return;
        }

        // Calcular el peso y volumen total para la guía seleccionada
        $detalles = DB::table('guias_detalles')
            ->where('id_guia', $factura->id_guia)
            ->get();

        // Calcular el peso total en kilogramos
        $pesoTotalGramos = $detalles->sum(function ($detalle) {
            return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
        });

        // Convertir el peso total a kilogramos
        $pesoTotalKilos = $pesoTotalGramos / 1000;

        $volumenTotal = $detalles->sum(function ($detalle) {
            return $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
        });

        // Validar que el peso y volumen sean mayores a 0
//        if ($pesoTotalKilos <= 0 || $volumenTotal <= 0) {
//            session()->flash('error', 'El peso o el volumen deben ser mayores a 0. Verifique los detalles de la guía.');
//            return;
//        }

        // Agregar la factura seleccionada al array
        $this->selectedFacturas[] = [
            'id_guia' => $factura->id_guia,
            'guia_almacen_origen' => $factura->guia_almacen_origen,
            'guia_tipo_doc' => $factura->guia_tipo_doc,
            'guia_nro_doc' => $factura->guia_nro_doc,
            'guia_fecha_emision' => $factura->guia_fecha_emision,
            'guia_tipo_movimiento' => $factura->guia_tipo_movimiento,
            'guia_tipo_doc_ref' => $factura->guia_tipo_doc_ref,
            'guia_nro_doc_ref' => $factura->guia_nro_doc_ref,
            'guia_glosa' => $factura->guia_glosa,
            'guia_fecha_proceso' => $factura->guia_fecha_proceso,
            'guia_hora_proceso' => $factura->guia_hora_proceso,
            'guia_usuario' => $factura->guia_usuario,
            'guia_cod_cliente' => $factura->guia_cod_cliente,
            'guia_ruc_cliente' => $factura->guia_ruc_cliente,
            'guia_nombre_cliente' => $factura->guia_nombre_cliente,
            'guia_forma_pago' => $factura->guia_forma_pago,
            'guia_vendedor' => $factura->guia_vendedor,
            'guia_moneda' => $factura->guia_moneda,
            'guia_tipo_cambio' => $factura->guia_tipo_cambio,
            'guia_estado' => $factura->guia_estado,
            'guia_direc_entrega' => $factura->guia_direc_entrega,
            'guia_nro_pedido' => $factura->guia_nro_pedido,
            'guia_importe_total' => $factura->guia_importe_total,
            'guia_importe_total_sin_igv' => $factura->guia_importe_total_sin_igv,
            'guia_departamento' => $factura->guia_departamento,
            'guia_provincia' => $factura->guia_provincia,
            'guia_destrito' => $factura->guia_destrito,
            'peso_total' => $pesoTotalKilos,
            'volumen_total' => $volumenTotal,
        ];

        // Actualizar los totales
        $this->pesoTotal += $pesoTotalKilos;
        $this->volumenTotal += $volumenTotal;

        $importes = $factura->guia_importe_total_sin_igv;
        $importe = floatval($importes);
        $this->importeTotalVenta += $importe;
    }

    public function eliminarFacturaSeleccionada($id_guia) {
        // Convertir id_guia a string para evitar problemas con bigint
        $id_guia = (string)$id_guia;

        // Encuentra la factura en las seleccionadas
        $factura = collect($this->selectedFacturas)->first(function ($f) use ($id_guia) {
            return (string)$f['id_guia'] === $id_guia; // Convertir a string para comparar
        });

        if ($factura) {
            // Elimina la factura de la lista seleccionada
            $this->selectedFacturas = collect($this->selectedFacturas)
                ->reject(function ($f) use ($id_guia) {
                    return (string)$f['id_guia'] === $id_guia; // Convertir a string para comparar
                }) ->values()
                ->toArray();

            $GuiaUpEstate = Guia::find($id_guia);
            $GuiaUpEstate->guia_estado_aprobacion = 11;
            $GuiaUpEstate->save();

            // Actualiza los totales
            $this->pesoTotal -= $factura['peso_total'];
            $this->volumenTotal -= $factura['volumen_total'];
            $this->importeTotalVenta -= floatval($factura['guia_importe_total_sin_igv']);

            // Verifica si no quedan facturas ni servicios de transporte seleccionados
            if (empty($this->selectedFacturas) && empty($this->selectedServTrns)) {
                $this->pesoTotal = 0;
                $this->volumenTotal = 0;
                $this->importeTotalVenta = 0;
            }

        } else {
            \Log::warning("No se encontró la guía con id_guia: $id_guia");
        }
    }

    public function guardar_despacho_entrega()
    {
        try {
            // Validar permisos
            if (!Gate::allows('guardar_despacho_entrega')) {
                session()->flash('error', 'No tiene permisos para guardar los registros.');
                return;
            }

            // Validar que se haya seleccionado al menos una guía
            if (empty($this->selectedFacturas) || count($this->selectedFacturas) == 0) {
                session()->flash('error', 'Debe seleccionar al menos una guía para guardar el despacho.');
                return;
            }

            // Validar datos de entrada
            $this->validate([
                'selectedFacturas' => 'required|array|min:1',
                'selectedFacturas.*.id_guia' => 'required|integer|exists:guias,id_guia',
                'guia_fecha_despacho' => 'required|date',
            ]);

            // Validación de rango de fechas (3 días antes y 3 días después)
            $fechaDespacho = Carbon::parse($this->guia_fecha_despacho);
            $fechaActual = Carbon::now('America/Lima')->startOfDay();

            // Calculamos los límites de fecha
            $fechaLimiteInferior = $fechaActual->copy()->subDays(3);
            $fechaLimiteSuperior = $fechaActual->copy()->addDays(3);

            // Verificamos si la fecha está fuera del rango permitido
            if ($fechaDespacho->lt($fechaLimiteInferior) || $fechaDespacho->gt($fechaLimiteSuperior)) {
                session()->flash('error', 'Fecha no válida. Solo se permiten fechas entre ' .
                    $fechaLimiteInferior->format('d-m-Y') . ' y ' .
                    $fechaLimiteSuperior->format('d-m-Y') . '.');
                return;
            }

            $contadorError = 0;
            DB::beginTransaction();

            // Validar duplicidad para las facturas seleccionadas
            foreach ($this->selectedFacturas as $factura) {
                $existe = DB::table('despacho_ventas as dv')
                    ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->where('d.despacho_estado_aprobacion', '<>', 4)
                    ->where('dv.id_guia', $factura['id_guia'])
                    ->whereIn('g.guia_estado_aprobacion', [7, 8])
                    ->orderBy('dv.id_despacho_venta', 'desc')
                    ->exists();

                if ($existe) {
                    $contadorError++;
                }
            }

            if ($contadorError > 0) {
                session()->flash('error', "Se encontraron {$contadorError} guía(s) duplicada(s). Por favor, verifique.");
                DB::rollBack();
                return;
            }

            // Obtener IDs de guías para actualización
            $idsGuias = array_column($this->selectedFacturas, 'id_guia');

            // Actualizar el estado de las guías a 8
            DB::table('guias')
                ->whereIn('id_guia', $idsGuias)
                ->update([
                    'guia_estado_aprobacion' => 8,
                    'guia_fecha_despacho' => $this->guia_fecha_despacho,
                    'updated_at' => now('America/Lima')
                ]);

            // Guardar en historial_guias
            $guias = DB::table('guias')
                ->whereIn('id_guia', $idsGuias)
                ->get()
                ->keyBy('id_guia');

            $historialData = [];
            foreach ($this->selectedFacturas as $factura) {
                if (isset($guias[$factura['id_guia']])) {
                    $guia = $guias[$factura['id_guia']];

                    $historialData[] = [
                        'id_users' => Auth::id(),
                        'id_guia' => $factura['id_guia'],
                        'guia_nro_doc' => $guia->guia_nro_doc,
                        'historial_guia_estado_aprobacion' => 8,
                        'historial_guia_fecha_hora' => $this->guia_fecha_despacho,
                        'historial_guia_estado' => 1,
                        'created_at' => Carbon::now('America/Lima'),
                        'updated_at' => Carbon::now('America/Lima'),
                    ];
                }
            }

            // Insertar en lote el historial
            DB::table('historial_guias')->insert($historialData);

            DB::commit();

            session()->flash('success', 'Registro guardado correctamente. ' . count($this->selectedFacturas) . ' guía(s) procesada(s).');
            $this->reiniciar_campos();
            $this->dispatch('hide_modal_confirmar_despacho');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            $this->setErrorBag($e->validator->errors());
            session()->flash('error', 'Error de validación: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error inesperado. Por favor, inténtelo nuevamente. ' . $e->getMessage());
        }
    }

    public function reiniciar_campos(){
        $this->guia_fecha_despacho = now()->addDay()->format('Y-m-d');
        $this->selectedFacturas = [];
        $this->pesoTotal = 0;
        $this->volumenTotal = 0;
    }
}
