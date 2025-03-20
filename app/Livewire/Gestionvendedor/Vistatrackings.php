<?php

namespace App\Livewire\Gestionvendedor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;
use App\Models\DespachoVenta;
use App\Models\Facturaspreprogramacion;
use App\Models\General;
use App\Models\Historialdespachoventa;
use App\Models\Historialpreprogramacion;
use App\Models\Guia;
use App\Models\Historialguia;

class Vistatrackings extends Component
{
    private $logs;
    private $despachoventa;
    private $facpreprogramacion;
    private $general;
    private $despacho;
    private $historialpreprogramacion;
    private $historialdespachoventa;
    private $guia;
    private $historialguia;
    public function __construct(){
        $this->logs = new Logs();
        $this->despachoventa = new DespachoVenta();
        $this->facpreprogramacion = new Facturaspreprogramacion();
        $this->general = new General();
        $this->despacho = new Despacho();
        $this->historialpreprogramacion = new Historialpreprogramacion();
        $this->historialdespachoventa = new Historialdespachoventa();
        $this->guia = new Guia();
        $this->historialguia = new Historialguia();
    }
    public $id_guia = "";
    public $guia_nro_doc = "";

    public function mount($id, $numdoc){
        $this->id_guia = $id;
        $this->guia_nro_doc = $numdoc;
        $this->buscar();
    }
    public $search_compro;
    public $mensaje;
    public $estadoMensaje = [];
    public $mensajeEtapa1;
    public $mensajeEtapa2;
    public $mensajeEtapa3;
    public $mensajeEstadoEtapa1;
    public $mensajeEstadoEtapa2 = [];
    public $mensajeEstadoEtapa3 = [];
    public $mensajesCompletos = [];
    public $mensajeEstadoFacturaEtapa2;
    public $etapaActual;
    public $codigoEncontrado = false;
    public $botonDeshabilitado = false;
    public $botonSiguienteVisible = true;
    public $botonAnteriorVisible;
    public $etapaMostrada;
    public $facturas = [];
    public $facturasRelacionadas = [];
    public $guiainfo = [];
    public $guia_detalle = [];

    public function render(){
        return view('livewire.gestionvendedor.vistatrackings');
    }

    public function modal_guia_info($id_guia) {
        $this->guiainfo = $this->guia->listar_guia_x_id($id_guia);
    }

    public function listar_detalle_guia($id_guia) {
        $this->guia_detalle = $this->guia->listar_guia_detalle_x_id($id_guia);
    }

    public function cambiarEtapa($nuevaEtapa) {
        if ($this->codigoEncontrado && $nuevaEtapa >= 1 && $nuevaEtapa <= 3) {
            $this->etapaMostrada = $nuevaEtapa;
            $this->actualizarVisibilidadBotones();
        }
    }

    public function actualizarVisibilidadBotones() {
        // Control del botón siguiente
        $this->botonSiguienteVisible = false;

        if ($this->etapaMostrada == 1) {
            // Verifica si existe información en la etapa 2
            if (!empty($this->mensajeEtapa2) || !empty($this->mensajeEstadoEtapa2)) {
                $this->botonSiguienteVisible = true;
            }
        } elseif ($this->etapaMostrada == 2) {
            // Verifica si existe información en la etapa 3
            if (!empty($this->mensajeEtapa3) || !empty($this->mensajeEstadoEtapa3)) {
                $this->botonSiguienteVisible = true;
            }
        }

        // Control del botón anterior
        $this->botonAnteriorVisible = $this->etapaMostrada > 1;
    }

    public function buscar() {
        $numdoc = $this->guia_nro_doc; // Número de documento a buscar

        // Buscar la guía principal
        $preProg = Guia::where('guia_nro_doc', $numdoc)->first();

        if ($preProg) {
            // Obtener los detalles de la guía
            $detallesGuia = DB::table('guias_detalles')
                ->where('id_guia', $preProg->id_guia)
                ->get();

            // Calcular el peso y volumen total
            $pesoTotalGramos = 0; // Peso total en gramos
            $volumenTotal = 0;

            foreach ($detallesGuia as $detalle) {
                $pesoTotalGramos += $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                $volumenTotal += $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
            }

            // Convertir el peso total a kilogramos
            $pesoTotalKilogramos = $pesoTotalGramos / 1000;

            // Agregar los totales a la guía
            $preProg->peso_total_gramos = $pesoTotalGramos; // Peso en gramos (opcional)
            $preProg->peso_total_kilogramos = $pesoTotalKilogramos; // Peso en kilogramos
            $preProg->volumen_total = $volumenTotal;

            // Convertir en array dentro de otro array
            $this->facturas = [$preProg->toArray()];
        } else {
            $this->facturas = []; // Asegurar que facturas sea un array vacío
            session()->flash('error', 'No hay registros para el comprobante ingresado.');
        }

        // Buscar en la tabla historialguias
        $historialGuia = Historialguia::where('guia_nro_doc', $numdoc)
            ->orderBy('historial_guia_fecha_hora', 'asc')
            ->get();

        if ($historialGuia->isEmpty()) {
            session()->flash('error', 'No hay registros para el comprobante ingresado.');
            return;
        }

        // Inicializar variables
        $this->codigoEncontrado = true;
        $this->etapaActual = 0; // Iniciar en la etapa 0 (Fecha de Emisión)
        $this->mensajesCompletos = [];

        // Recorrer el historial de la guía
        foreach ($historialGuia as $registro) {
            $fechaHora = $this->general->obtenerNombreFecha($registro->historial_guia_fecha_hora, 'DateTime', 'DateTime');
            $estado = $registro->historial_guia_estado_aprobacion;

            switch ($estado) {
                case 1:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Guía en créditos.";
                    $this->etapaActual = 1; // EN CREDITOS
                    break;
                case 2:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Enviado a despachador.";
                    $this->etapaActual = 2; // POR PROGRAMAR
                    break;
                case 3:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Listo para despachar.";
                    $this->etapaActual = 2; // POR PROGRAMAR
                    break;
                case 4:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Guía despachado.";
                    $this->etapaActual = 3; // PROGRAMADO
                    break;
                case 5:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Aceptado por Créditos.";
                    $this->etapaActual = 1; // EN CREDITOS
                    break;
                case 6:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Guía gestionado en facturación.";
                    $this->etapaActual = 3; // PROGRAMADO
                    break;
                case 7:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Guía en tránsito.";
                    $this->etapaActual = 4; // EN RUTA
                    break;
                case 8:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Guía entregado.";
                    $this->etapaActual = 5; // COMPROBANTE ENTREGADO
                    break;
                case 9:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Despacho aprobado.";
                    $this->etapaActual = 3; // PROGRAMADO
                    break;
                case 10:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Despacho rechazado.";
                    $this->etapaActual = 2; // POR PROGRAMAR (se detiene aquí)
                    break;
                case 11:
                    $this->mensajesCompletos[] = $fechaHora . ' | ' . "Estado: Guía no entregado.";
                    $this->etapaActual = 5; // COMPROBANTE NO ENTREGADO
                    break;
            }

            // Si el estado es 10 (rechazado), se detiene el flujo
            if ($estado == 10) {
                break;
            }
        }

        // Obtener las guías relacionadas (mismo id_despacho)
        if ($preProg) {
            // Obtener el id_despacho de la guía actual
            $despachoVentaActual = DespachoVenta::where('id_guia', $preProg->id_guia)->first();

            if ($despachoVentaActual) {
                // Obtener todas las guías con el mismo id_despacho
                $despachosRelacionados = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->where('dv.id_despacho', $despachoVentaActual->id_despacho)
                    ->where('g.id_guia', '!=', $preProg->id_guia)
                    ->select('g.guia_nro_doc as guia_nro_doc',
                        'g.guia_nro_doc_ref as guia_nro_doc_ref',
                        'g.guia_importe_total as guia_importe_total',
                        'g.id_guia',
                    )
                    ->get();

                $this->facturasRelacionadas = $despachosRelacionados->toArray();
            } else {
                $this->facturasRelacionadas = [];
            }
        } else {
            $this->facturasRelacionadas = [];
        }

        // Mensaje de éxito
        session()->flash('success', 'Comprobante identificado en el sistema.');
    }
}
