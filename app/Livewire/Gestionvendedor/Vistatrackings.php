<?php

namespace App\Livewire\Gestionvendedor;

use Illuminate\Support\Facades\Request;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;
use App\Models\DespachoVenta;
use App\Models\Facturaspreprogramacion;
use App\Models\General;
use App\Models\Historialdespachoventa;
use App\Models\Historialpreprogramacion;

class Vistatrackings extends Component
{
    private $logs;
    private $despachoventa;
    private $facpreprogramacion;
    private $general;
    private $despacho;
    private $historialpreprogramacion;
    private $historialdespachoventa;
    public function __construct(){
        $this->logs = new Logs();
        $this->despachoventa = new DespachoVenta();
        $this->facpreprogramacion = new Facturaspreprogramacion();
        $this->general = new General();
        $this->despacho = new Despacho();
        $this->historialpreprogramacion = new Historialpreprogramacion();
        $this->historialdespachoventa = new Historialdespachoventa();
    }
    public $id_fac_pre_prog = "";
    public $fac_pre_prog_cfnumdoc = "";

    public function mount($id, $numdoc){
        $this->id_fac_pre_prog = $id;
        $this->fac_pre_prog_cfnumdoc = $numdoc;
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
    public $mensajeEstadoFacturaEtapa2;
    public $etapaActual;
    public $codigoEncontrado = false;
    public $botonDeshabilitado = false;
    public $botonSiguienteVisible = true;
    public $botonAnteriorVisible;
    public $etapaMostrada;

    public function render(){
        return view('livewire.gestionvendedor.vistatrackings');
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
        $this->mensajeEtapa1 = null;
        $this->mensajeEtapa2 = null;
        $this->mensajeEtapa3 = null;
        $this->estadoMensaje = null;
        $this->mensajeEstadoEtapa2 = null;
        $this->mensajeEstadoEtapa3 = []; // Inicializar como array vacío
        $this->codigoEncontrado = false;
        $this->botonDeshabilitado = false;

        // Utilizar $this->fac_pre_prog_cfnumdoc en lugar de $this->search_compro
        $numdoc = $this->fac_pre_prog_cfnumdoc;

        // Buscar en la tabla facturas_pre_programaciones (ETAPA 1)
        $preProgramado = Facturaspreprogramacion::where('fac_pre_prog_cfnumdoc', $numdoc)->first();

        if ($preProgramado) {
            $this->codigoEncontrado = true;
            $this->etapaActual = 1; // Establece la etapa real
            $this->etapaMostrada = 1; // Se establece en etapa 1 por defecto
            $this->mensajeEtapa1 = $this->general->obtenerNombreFecha($preProgramado->fac_pre_prog_fecha, 'DateTime', 'DateTime') . ' | ' . "El comprobante está en pre-programación.";

            // Buscar el historial de cambios de estado en la tabla historial_pre_programacion
            $historial = Historialpreprogramacion::where('fac_pre_prog_cfnumdoc', $numdoc)
                ->orderBy('his_pre_progr_fecha_hora', 'desc')
                ->get();

            if ($historial->isNotEmpty()) {
                foreach ($historial as $registro) {
                    $fechaHora = $this->general->obtenerNombreFecha($registro->his_pre_progr_fecha_hora, 'DateTime', 'DateTime');
                    $estado = $registro->fac_pre_prog_estado_aprobacion;
                    $estadoGeneral = $registro->fac_pre_prog_estado; // Verificar el campo fac_pre_prog_estado

                    // Si el estado general es 0, mostrar mensaje de rechazo
                    if ($estadoGeneral == 0) {
                        $this->estadoMensaje[] = $fechaHora . ' | ' . "Comprobante rechazado.";
                    }

                    // Mostrar el estado de aprobación según el valor de fac_pre_prog_estado_aprobacion
                    switch ($estado) {
                        case 1:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Comprobante en revición.";
                            break;
                        case 2:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Enviado a despacho.";
                            break;
                        case 3:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Listo para despachar.";
                            break;
                        case 4:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Comprobante despachada.";
                            break;
                        case 5:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Aceptado por Créditos, pronto será enviado a despacho.";
                            break;
                        case 6:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Enviado a creditos y cobranzas.";
                            break;
                    }
                }
            }
        }

        // Buscar en la tabla despachos_ventas (ETAPA 2)
        $despachoVenta = DespachoVenta::where('despacho_venta_cfnumdoc', $numdoc)->first();

        if ($despachoVenta) {
            $this->codigoEncontrado = true;
            $this->etapaActual = 2; // Etapa 2
            $this->etapaMostrada = 2;
            $this->mensajeEtapa2 = $this->general->obtenerNombreFecha($despachoVenta->created_at, 'DateTime', 'DateTime') . ' | ' . "El comprobante ingresado fue programado para un despacho.";

            // Buscar el historial en la tabla historial_despachos_ventas
            $historialDespachoVenta = Historialdespachoventa::where('id_despacho', $despachoVenta->id_despacho)
                ->orderBy('his_desp_vent_fecha', 'desc')
                ->get();

            if ($historialDespachoVenta->isNotEmpty()) {
                // Array para evitar duplicados de estados
                $estadosMostrados = [];

                foreach ($historialDespachoVenta as $registro) {
                    $fechaHora = $this->general->obtenerNombreFecha($registro->his_desp_vent_fecha, 'DateTime', 'DateTime');
                    $estado = $registro->despacho_estado_aprobacion;

                    // Verificar si el estado ya fue mostrado
                    if (!in_array($estado, $estadosMostrados)) {
                        $estadosMostrados[] = $estado; // Marcar el estado como mostrado

                        switch ($estado) {
                            case 0:
                                $this->mensajeEstadoEtapa2[] = $fechaHora . ' | ' . "Estado: Pendiente por aprobar.";
                                break;
                            case 1:
                                $this->mensajeEstadoEtapa2[] = $fechaHora . ' | ' . "Estado: Programación aprobada.";
                                break;
                            case 2:
                                $this->mensajeEstadoEtapa2[] = $fechaHora . ' | ' . "Estado: Despacho en camino.";
                                break;
                        }
                    }
                }

                // Acceder al primer registro y verificar el estado de entrega
                $primerRegistro = $historialDespachoVenta->first();

                if ($primerRegistro && $primerRegistro->despacho_detalle_estado_entrega == 1) {
                    $this->mensajeEstadoFacturaEtapa2 = $this->general->obtenerNombreFecha($primerRegistro->his_desp_vent_fecha, 'DateTime', 'DateTime') . ' | ' . "Estado: Factura en tránsito.";
                } else {
                    $this->mensajeEstadoFacturaEtapa2 = null; // No mostrar mensaje si no cumple la condición
                }
            }
        }

        // Buscar en la tabla historial_despacho_ventas (ETAPA 3)
        $historialDespachoVentaEtapa3 = Historialdespachoventa::where('despacho_venta_cfnumdoc', $numdoc)
            ->whereNotNull('despacho_detalle_estado_entrega') // Solo registros con estado de entrega
            ->orderBy('his_desp_vent_fecha', 'desc')
            ->get();

        if ($historialDespachoVentaEtapa3->isNotEmpty()) {
            $this->codigoEncontrado = true;
            $this->etapaActual = 3;
            $this->etapaMostrada = 3;

            foreach ($historialDespachoVentaEtapa3 as $registro) {
                $fechaHora = $this->general->obtenerNombreFecha($registro->his_desp_vent_fecha, 'DateTime', 'DateTime');
                $estadoEntrega = $registro->despacho_detalle_estado_entrega;

                switch ($estadoEntrega) {
                    case 2:
                        $this->mensajeEstadoEtapa3[] = $fechaHora . ' | ' . "Estado: Comprobante entregado.";
                        break;
                    case 3:
                        $this->mensajeEstadoEtapa3[] = $fechaHora . ' | ' . "Estado: Comprobante no entregado.";
                        break;
                    case 4:
                        $this->mensajeEstadoEtapa3[] = $fechaHora . ' | ' . "Estado: Comprobante rechazado.";
                        break;
                }
            }

            // Mostrar mensaje de etapa 3 solo si el estado de entrega es 2, 3 o 4
            if (in_array($historialDespachoVentaEtapa3->first()->despacho_detalle_estado_entrega, [2, 3, 4])) {
                $this->mensajeEtapa3 = $this->general->obtenerNombreFecha($historialDespachoVentaEtapa3->first()->his_desp_vent_fecha, 'DateTime', 'DateTime') . ' | ' . "El despacho fue culminado.";
            } else {
                $this->mensajeEtapa3 = null; // No mostrar mensaje si no cumple la condición
            }
        }

        // Si no está en pre-programación pero sí en despacho, mostrar mensaje específico
        if (!$preProgramado && $despachoVenta) {
            $this->mensajeEtapa1 = "ESTE COMPROBANTE FUE PROGRAMADO PARA PROVINCIAL O MIXTO";
        }

        // Si el comprobante no se encuentra en ninguna tabla
        if (!$preProgramado && !$despachoVenta) {
            $this->mensajeEtapa3 = "El comprobante ingresado no fue programado para un despacho.";
        }

        // Si el comprobante solo está en etapa 1, se deshabilita el botón "Siguiente"
        if ($this->etapaActual == 1 && !$despachoVenta) {
            $this->botonDeshabilitado = true;
        }

        // Agregar mensaje de éxito o error
        if ($this->codigoEncontrado) {
            session()->flash('success', 'Comprobante identificado en el sistema.');
        } else {
            session()->flash('error', 'No hay registros para el comprobante ingresado.');
        }
    }
}
