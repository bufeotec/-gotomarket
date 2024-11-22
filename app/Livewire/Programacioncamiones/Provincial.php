<?php

namespace App\Livewire\Programacioncamiones;
use App\Models\Logs;
use App\Models\Server;
use App\Models\Transportista;
use App\Models\Departamento;
use App\Models\Vehiculo;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Provincial extends Component
{
    private $logs;
    private $server;
    private $transportista;
    private $departamento;
    private $vehiculo;
    public function __construct(){
        $this->logs = new Logs();
        $this->server = new Server();
        $this->transportista = new Transportista();
        $this->departamento = new Departamento();
        $this->vehiculo = new Vehiculo();
    }
    public $searchCliente = "";
    public $filteredClientes = [];
    public $selectedCliente = null;
    public $id_transportistas = "";
    public $provincias = [];
    public $distritos = [];
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $tarifaMontoSeleccionado = 0;
    public $vehiculosSugeridos = [];
    public $selectedVehiculo = "";
    public $id_departamento = "";
    public $id_provincia = "";
    public $id_distrito = "";
    public $selectedFacturas = [];
    public $comprobantes = [];
    public $select_nombre_cliente = null;
    public $searchComprobante = '';
    public $filteredComprobantes = [];
    public function mount(){
        $this->selectedCliente = null;
        $this->selectedVehiculo = null;
        $this->id_transportistas = null;
        $this->id_departamento = null;
        $this->id_provincia = null;
        $this->id_distrito = null;
    }
    public function render(){
        $listar_transportistas = $this->transportista->listar_transportista_sin_id();
        $listar_departamento = $this->departamento->lista_departamento();
        return view('livewire.programacioncamiones.provincial', compact('listar_transportistas', 'listar_departamento'));
    }

    public function listar_provincias(){
        $valor = $this->id_departamento;
        if ($valor) {
            $this->provincias = DB::table('provincias')->where('id_departamento', '=', $valor)->get();
        } else {
            $this->provincias = [];
            $this->id_provincia = '';
            $this->distritos = [];
            $this->id_distrito = '';
        }
    }

    public function listar_distritos(){
        $valor = $this->id_provincia;
        if ($valor) {
            $this->distritos = DB::table('distritos')->where('id_provincia', '=', $valor)->get();
        } else {
            $this->distritos = [];
            $this->id_distrito = '';
        }
    }

    public function buscar_cliente(){
        if ($this->searchCliente !== "") {
            $clientes = $this->server->listar_clientes($this->searchCliente);
            if ($clientes && count($clientes) > 0) {
                $this->filteredClientes = $clientes;
            } else {
                $this->filteredClientes = [];
            }
        } else {
            $this->filteredClientes = [];
        }
    }

    public function seleccionar_cliente($clienteId) {
        $cliente = collect($this->filteredClientes)->firstWhere('CCODCLI',(int) $clienteId);
        if ($cliente) {
            $this->selectedCliente = $cliente->CCODCLI;
            $this->select_nombre_cliente = $cliente->CNOMCLI;
            $this->searchCliente = "";
            $this->filteredClientes = [];
        } else {
            $this->resetear_cliente();
        }
    }
    public function limpiar_cliente($clienteId){
        $this->selectedCliente = '';
        $this->select_nombre_cliente = '';
        $this->searchCliente = "";
        $this->filteredClientes = [];
        $this->filteredComprobantes = [];
        $this->id_transportistas  = "";
        $this->id_departamento  = "";
        $this->id_provincia  = "";
        $this->id_distrito  = "";
        $this->pesoTotal  = "";
        $this->volumenTotal  = "";

    }

    public function buscar_comprobante() {
        if ($this->selectedCliente && $this->searchComprobante !== "") {
            $comprobantes = $this->server->listar_comprobantes_por_cliente($this->selectedCliente, $this->searchComprobante);
            $this->filteredComprobantes = $comprobantes;
        } else {
            $this->filteredComprobantes = [];
        }
    }

    public function resetear_cliente() {
        $this->selectedCliente = null;
        $this->select_nombre_cliente = null;
        $this->comprobantes = [];
        $this->searchCliente = "";
        $this->searchComprobante = "";
        $this->filteredComprobantes = [];
        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_provincial($this->pesoTotal,2,$this->id_transportistas, $this->id_departamento, $this->id_provincia, $this->id_distrito);
    }


    public function seleccionar_factura_cliente($CFTD, $CFNUMSER, $CFNUMDOC){
        // Validar que la factura no exista en el array selectedFacturas
        $comprobanteExiste = collect($this->selectedFacturas)->first(function ($factura) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
            return $factura['CFTD'] === $CFTD
                && $factura['CFNUMSER'] === $CFNUMSER
                && $factura['CFNUMDOC'] === $CFNUMDOC;
        });

        if ($comprobanteExiste) {
            // Mostrar un mensaje de error si la factura ya fue agregada
            session()->flash('error', 'Este comprobante ya fue agregado.');
            return;
        }

        // Buscar la factura en el array filteredFacturas
        $factura = $this->filteredComprobantes->first(function ($f) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
            return $f->CFTD === $CFTD
                && $f->CFNUMSER === $CFNUMSER
                && $f->CFNUMDOC === $CFNUMDOC;
        });

        if ($factura->total_kg <= 0 || $factura->total_volumen <= 0){
            session()->flash('error', 'El peso o el volumen deben ser mayores a 0.');
            return;
        }
        // Agregar la factura seleccionada y actualizar el peso y volumen total
        $this->selectedFacturas[] = [
            'CFTD' => $CFTD,
            'CFNUMSER' => $CFNUMSER,
            'CFNUMDOC' => $CFNUMDOC,
            'total_kg' => $factura->total_kg,
            'total_volumen' => $factura->total_volumen,
        ];
        $this->pesoTotal += $factura->total_kg;
        $this->volumenTotal += $factura->total_volumen;

        // Eliminar la factura de la lista de facturas filtradas
        $this->filteredComprobantes = $this->filteredComprobantes->filter(function ($f) use ($CFNUMDOC) {
            return $f->CFNUMDOC !== $CFNUMDOC;
        });
        // Actualizar lista de vehículos sugeridos
        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_provincial($this->pesoTotal,2,$this->id_transportistas,$this->id_departamento ,$this->id_provincia ,$this->id_distrito);
//        $this->buscar_comprobantes();
    }

    public function eliminarFacturaSeleccionada($CFTD, $CFNUMSER, $CFNUMDOC){
        // Encuentra la factura en las seleccionadas
        $factura = collect($this->selectedFacturas)->first(function ($f) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
            return $f['CFTD'] === $CFTD && $f['CFNUMSER'] === $CFNUMSER && $f['CFNUMDOC'] === $CFNUMDOC;
        });
        if ($factura) {
            // Elimina la factura de la lista seleccionada
            $this->selectedFacturas = collect($this->selectedFacturas)
                ->reject(function ($f) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
                    return $f['CFTD'] === $CFTD && $f['CFNUMSER'] === $CFNUMSER && $f['CFNUMDOC'] === $CFNUMDOC;
                })
                ->values()
                ->toArray();
            // Actualiza los totales
            $this->pesoTotal -= $factura['total_kg'];
            $this->volumenTotal -= $factura['total_volumen'];
//            // Añade la factura eliminada a las sugerencias si coincide con la búsqueda actual
//            if (
//                str_contains(strtolower($factura['CFTD']), strtolower($this->searchFactura)) ||
//                str_contains(strtolower($factura['CFNUMSER']), strtolower($this->searchFactura)) ||
//                str_contains(strtolower($factura['CNOMCLI']), strtolower($this->searchFactura)) ||
//                str_contains((string) $factura['total_kg'], $this->searchFactura) ||
//                str_contains((string) $factura['total_volumen'], $this->searchFactura)
//            ) {
//                $this->filteredFacturas[] = $factura;
//            }
            $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_provincial($this->pesoTotal,2,$this->id_transportistas,$this->id_departamento ,$this->id_provincia ,$this->id_distrito);
        }
    }

    public function modal_por_vehiculo($id_ve){
        $this->detalle_vehiculo =  $this->vehiculo->listar_informacion_vehiculo($id_ve);
    }

    public function actualizarVehiculosSugeridos(){
        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_provincial($this->pesoTotal,2,$this->id_transportistas,$this->id_departamento ,$this->id_provincia ,$this->id_distrito);
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
