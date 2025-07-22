<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\DB;
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
    public $programacion_fecha = '';
    public function mount(){
        $this->programacion_fecha = now()->format('Y-m-d');
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
}
