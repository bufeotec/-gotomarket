<?php

namespace App\Livewire\Programacioncamiones;
use App\Models\Logs;
use App\Models\TipoServicio;
use App\Models\Server;
use App\Models\Transportista;
use App\Models\Vehiculo;

use Livewire\Component;

class Mixto extends Component
{
    private $logs;
    private $tiposervicio;
    private $server;
    private $transportista;
    private $vehiculo;
    public function __construct(){
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
        $this->server = new Server();
        $this->transportista = new Transportista();
        $this->vehiculo = new Vehiculo();
    }
    public $tipoServicioSeleccionado = '';
    public $searchFacturaCliente = '';
    public $filteredFacturasYClientes = [];
    public $selectedFacturas = [];
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $selectedFacturasLocal = [];
    public $selectedFacturasProvincial = [];
    public $transportistasPorCliente = [];
    public $tarifaMontoSeleccionado = 0;
    public $vehiculosSugeridos = [];
    public $selectedVehiculo = "";
    public $id_transportistas = "";

    public function render(){
        $tipo_servicio_local_provincial = $this->tiposervicio->listar_tipo_servicio_local_provincial();
        $listar_transportistas = $this->transportista->listar_transportista_sin_id();
        return view('livewire.programacioncamiones.mixto', compact('tipo_servicio_local_provincial', 'listar_transportistas', 'listar_transportistas'));
    }

    public function buscar_facturas_clientes(){
        if ($this->searchFacturaCliente !== "") {
            $this->filteredFacturasYClientes = $this->server->listar_comprobantes_listos_mixto($this->searchFacturaCliente);
            if (!$this->filteredFacturasYClientes || count($this->filteredFacturasYClientes) == 0) {
                $this->filteredFacturasYClientes = [];
            }
        } else {
            $this->filteredFacturasYClientes = [];
        }
    }

    public function seleccionarFactura($CFTD, $CFNUMSER, $CFNUMDOC)
    {
        $comprobanteId = "$CFTD-$CFNUMSER-$CFNUMDOC";

        // Busca la factura seleccionada en la lista filtrada
        $factura = collect($this->filteredFacturasYClientes)->first(function ($f) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
            return $f->CFTD === $CFTD && $f->CFNUMSER === $CFNUMSER && $f->CFNUMDOC === $CFNUMDOC;
        });

        if (!$factura) {
            return; // Si no se encuentra, no hace nada
        }

        // Sumar peso y volumen al seleccionar
        $this->pesoTotal += $factura->total_kg;
        $this->volumenTotal += $factura->total_volumen;

        // Agregar factura a la selección según el tipo de servicio
        if ($this->tipoServicioSeleccionado == 1) { // Local
            $this->selectedFacturasLocal[$comprobanteId] = [
                'CFTD' => $factura->CFTD,
                'CFNUMSER' => $factura->CFNUMSER,
                'CFNUMDOC' => $factura->CFNUMDOC,
                'CNOMCLI' => $factura->CNOMCLI,
                'total_kg' => $factura->total_kg,
                'total_volumen' => $factura->total_volumen,
            ];
        } elseif ($this->tipoServicioSeleccionado == 2) { // Provincial
            $cliente = $factura->CNOMCLI;

            if (!isset($this->selectedFacturasProvincial[$cliente])) {
                $this->selectedFacturasProvincial[$cliente] = [];
            }

            $this->selectedFacturasProvincial[$cliente][$comprobanteId] = [
                'CFTD' => $factura->CFTD,
                'CFNUMSER' => $factura->CFNUMSER,
                'CFNUMDOC' => $factura->CFNUMDOC,
                'CNOMCLI' => $factura->CNOMCLI,
                'total_kg' => $factura->total_kg,
                'total_volumen' => $factura->total_volumen,
            ];
        }

        // Eliminar factura de las facturas filtradas
        $this->filteredFacturasYClientes = $this->filteredFacturasYClientes->filter(function ($f) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
            return !($f->CFTD === $CFTD && $f->CFNUMSER === $CFNUMSER && $f->CFNUMDOC === $CFNUMDOC);
        });

        // Actualizar vehículos sugeridos
        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_mixto($this->pesoTotal, $this->volumenTotal, 1, $this->id_transportistas);
    }
    public function eliminarFacturaSeleccionada($CFTD, $CFNUMSER, $CFNUMDOC){
        $comprobanteId = "$CFTD-$CFNUMSER-$CFNUMDOC";

        // Eliminar factura de la selección según el tipo de servicio
        if (isset($this->selectedFacturasLocal[$comprobanteId])) {
            $factura = $this->selectedFacturasLocal[$comprobanteId];
            $this->pesoTotal -= $factura['total_kg'];
            $this->volumenTotal -= $factura['total_volumen'];
            unset($this->selectedFacturasLocal[$comprobanteId]);
        }

        // Eliminar de la sección Provincial
        foreach ($this->selectedFacturasProvincial as $cliente => $facturas) {
            if (isset($facturas[$comprobanteId])) {
                $factura = $facturas[$comprobanteId];
                $this->pesoTotal -= $factura['total_kg'];
                $this->volumenTotal -= $factura['total_volumen'];
                unset($this->selectedFacturasProvincial[$cliente][$comprobanteId]);

                // Eliminar cliente si no tiene más facturas seleccionadas
                if (empty($this->selectedFacturasProvincial[$cliente])) {
                    unset($this->selectedFacturasProvincial[$cliente]);
                }
                break;
            }
        }
    }

    public function actualizarVehiculosSugeridos(){
        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_local($this->pesoTotal, $this->volumenTotal,1,$this->id_transportistas);
        $this->tarifaMontoSeleccionado = null;
        $this->selectedVehiculo = null;
    }

    public function seleccionarVehiculo($vehiculoId){
        $vehiculo = collect($this->vehiculosSugeridos)->firstWhere('id_vehiculo', $vehiculoId);
        if ($vehiculo) {
            // Actualiza el monto de la tarifa del vehículo seleccionado
            $this->tarifaMontoSeleccionado = $vehiculo->tarifa_monto;
        }
    }



}
