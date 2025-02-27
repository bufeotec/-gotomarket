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
        $this->facturas = [];

        // Utilizar $this->fac_pre_prog_cfnumdoc en lugar de $this->search_compro
        $numdoc = $this->fac_pre_prog_cfnumdoc;

        $preProg = Facturaspreprogramacion::where('fac_pre_prog_cfnumdoc', $numdoc)->first();

        if ($preProg) {
            $this->facturas = [$preProg->toArray()]; // Convertir en array dentro de otro array
        } else {
            $this->facturas = []; // Asegurar que facturas sea un array vacío
            session()->flash('error', 'No hay registros para el comprobante ingresado.');
        }

        // Buscar en la tabla facturas_pre_programaciones (ETAPA 1)
        $preProgramado = Facturaspreprogramacion::where('fac_pre_prog_cfnumdoc', $numdoc)->first();

        if ($preProgramado) {
            $this->codigoEncontrado = true;
            $this->etapaActual = 1; // Establece la etapa real
            $this->etapaMostrada = 1; // Se establece en etapa 1 por defecto

            // Buscar el historial de cambios de estado en la tabla historial_pre_programacion
            $historial = Historialpreprogramacion::where('fac_pre_prog_cfnumdoc', $numdoc)
                ->orderBy('his_pre_progr_fecha_hora', 'asc')
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
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Comprobante en creditos.";
                            $this->etapaActual = 1; // EN CREDITOS
                            break;
                        case 2:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Enviado a despachador.";
                            $this->etapaActual = 2; // POR PROGRAMAR
                            break;
                        case 3:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Listo para despachar.";
                            $this->etapaActual = 2; // POR PROGRAMAR
                            break;
                        case 4:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Comprobante despachado.";
                            $this->etapaActual = 3; // PROGRAMADO
                            break;
                        case 5:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Aceptado por Créditos.";
                            $this->etapaActual = 1; // EN CREDITOS
                            break;
                        case 6:
                            $this->estadoMensaje[] = $fechaHora . ' | ' . "Estado: Comprobante gestionado en facturación.";
                            $this->etapaActual = 3; // PROGRAMADO
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

            // Buscar el historial en la tabla historial_despachos_ventas
            $historialDespachoVenta = Historialdespachoventa::where('id_despacho', $despachoVenta->id_despacho)
                ->orderBy('his_desp_vent_fecha', 'asc')
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
                        }
                    }
                }
            }
        }

        // Buscar en la tabla historial_despacho_ventas (ETAPA 3)
        $historialDespachoVentaEtapa3 = Historialdespachoventa::where('despacho_venta_cfnumdoc', $numdoc)
            ->whereNotNull('despacho_detalle_estado_entrega') // Solo registros con estado de entrega
            ->orderBy('his_desp_vent_fecha', 'asc')
            ->get();

        if ($historialDespachoVentaEtapa3->isNotEmpty()) {
            $this->codigoEncontrado = true;
            $this->etapaActual = 4;
            $this->etapaMostrada = 4;

            // Variable auxiliar para almacenar "Factura en tránsito."
            $facturaEnTransito = null;
            $estadosEntregaMostrados = []; // Evitar duplicados

            foreach ($historialDespachoVentaEtapa3 as $registro) {
                $fechaHora = $this->general->obtenerNombreFecha($registro->his_desp_vent_fecha, 'DateTime', 'DateTime');
                $estadoEntrega = $registro->despacho_detalle_estado_entrega;

                // Si el estado es "Factura en tránsito.", lo guardamos en una variable temporal
                if ($estadoEntrega == 1) {
                    $facturaEnTransito = $fechaHora . ' | ' . "Estado: Factura en tránsito.";
                    continue;
                }

                // Construir clave única para evitar estados duplicados
                $claveUnica = $fechaHora . '|' . $estadoEntrega;

                // Solo agregar si no ha sido registrado antes
                if (!in_array($claveUnica, $estadosEntregaMostrados)) {
                    $estadosEntregaMostrados[] = $claveUnica; // Registrar el estado

                    switch ($estadoEntrega) {
                        case 2:
                            $this->mensajeEstadoEtapa3[] = $fechaHora . ' | ' . "Estado: Comprobante entregado.";
                            $this->etapaActual = 5;
                            break;
                        case 3:
                            $this->mensajeEstadoEtapa3[] = $fechaHora . ' | ' . "Estado: Comprobante no entregado.";
                            break;
                        case 4:
                            $this->mensajeEstadoEtapa3[] = $fechaHora . ' | ' . "Estado: Comprobante rechazado.";
                            break;
                    }
                }
            }

            // Agregar "Factura en tránsito." antes de los estados de entrega si existe
            if ($facturaEnTransito) {
                array_unshift($this->mensajeEstadoEtapa3, $facturaEnTransito);
            }
        }

//        FACTURAS RELACIONADAS
        if ($despachoVenta) {
            $facturasRelacionadas = DespachoVenta::where('id_despacho', $despachoVenta->id_despacho)
                ->where('despacho_venta_cfnumdoc', '!=', $numdoc) // Excluir la factura actual
                ->get(['despacho_venta_cfnumdoc', 'despacho_venta_guia', 'despacho_venta_cfimporte', 'despacho_venta_total_kg']);

            $this->facturasRelacionadas = $facturasRelacionadas->toArray(); // Almacenar en una propiedad para la vista
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

        // Unir todos los mensajes en un solo array
        $this->mensajesCompletos = array_merge(
            $this->estadoMensaje ?? [],
            $this->mensajeEstadoEtapa2 ?? [],
            $this->mensajeEstadoEtapa3 ?? []
        );
        // Agregar los mensajes individuales si existen
        if ($this->mensajeEtapa1) {
            $this->mensajesCompletos[] = $this->mensajeEtapa1;
        }
        if ($this->mensajeEstadoFacturaEtapa2) {
            $this->mensajesCompletos[] = $this->mensajeEstadoFacturaEtapa2;
        }
        if ($this->mensajeEtapa3) {
            $this->mensajesCompletos[] = $this->mensajeEtapa3;
        }
    }
}
