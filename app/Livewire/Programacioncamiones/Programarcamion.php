<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Tarifario;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Transportista;
use App\Models\TipoVehiculo;
use App\Models\TipoServicio;
use App\Models\Vehiculo;
use App\Models\Departamento;
use App\Models\Despacho;
use Illuminate\Support\Facades\DB;

class Programarcamion extends Component
{
    private $logs;
    private $transportista;
    private $tipovehiculo;
    private $tiposervicio;
    private $vehiculo;
    private $departamento;
    private $despacho;

    public function __construct(){
        $this->logs = new Logs();
        $this->transportista = new Transportista();
        $this->tipovehiculo = new TipoVehiculo();
        $this->tiposervicio = new TipoServicio();
        $this->vehiculo = new Vehiculo();
        $this->departamento = new Departamento();
        $this->despacho = new Despacho();
    }

    public $selected_transportista = null;

    public $search = "";
    public $despacho_fecha = "";
    public $despacho_mano_obra = "";
    public $despacho_otros = "";
    public $searchFactura = "";
    public $searchCliente = "";
    public $id_transportistas = "";
    public $id_tipo_servicios = "";
    public $id_tipo_vehiculo = "";
    public $id_vehiculo = "";
    public $id_departamento = "";
    public $id_provincia = "";
    public $id_distrito = "";
    public $selectedVehiculo = null;
    public $despacho_tipo = "";
    public $despacho_peso_total = "";
    public $despacho_costo_total = "";
    public $despacho_estado = "";
    public $despacho_microtime = "";
    public $despacho_otro = "";
    public $facturas = [
        ['id' => 1, 'serie' => 'FC-001', 'nombre' => 'Cliente A', 'peso' => 250, 'volumen' => 250000 ],
        ['id' => 2, 'serie' => 'FC-002', 'nombre' => 'Cliente B', 'peso' => 300, 'volumen' => 300000],
        ['id' => 3, 'serie' => 'FC-003', 'nombre' => 'Cliente C', 'peso' => 450, 'volumen' => 450000],
        ['id' => 4, 'serie' => 'FC-004', 'nombre' => 'Cliente D', 'peso' => 110, 'volumen' => 110000],
    ];

    public $clientes = [
        ['id' => 2, 'nombre' => 'Shambo', 'ruc' => '20305687411'],
        ['id' => 3, 'nombre' => 'Topitop', 'ruc' => '20457865499'],
        ['id' => 4, 'nombre' => 'Quispe', 'ruc' => '20025674522'],
    ];

    public $facturasCli = [
        ['id' => 1, 'serie' => 'FC-001', 'nombre' => 'Shambo', 'peso' => 250, 'volumen' => 250000],
        ['id' => 2, 'serie' => 'FC-002', 'nombre' => 'Shambo', 'peso' => 300, 'volumen' => 300000],
        ['id' => 3, 'serie' => 'FC-003', 'nombre' => 'Topitop', 'peso' => 450, 'volumen' => 450000],
        ['id' => 4, 'serie' => 'FC-004', 'nombre' => 'Topitop', 'peso' => 500, 'volumen' => 500000],
        ['id' => 5, 'serie' => 'FC-005', 'nombre' => 'Quispe', 'peso' => 290, 'volumen' => 290000],
        ['id' => 6, 'serie' => 'FC-006', 'nombre' => 'Quispe', 'peso' => 700, 'volumen' => 700000],
    ];

    public $filteredFacturas = [];
    public $filteredClientes = [];
    public $selectedFacturas = [];
    public $selectedClientes = [];
    public $selectedCliente = null;
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $tarifa = null;
    public $tarifa_estado_aprobacion;
    public $selected_tipo_servicio = null;
    public $vehiculos = [];
    public $provincias = [];
    public $distritos = [];

    public function render(){
        $this->buscarFacturas();
        $this->buscarClientes();
        $listar_tipo_vehiculo = $this->tipovehiculo->listar_tipo_vehiculo();
        $listar_tipo_servicio = $this->tiposervicio->listar_tipo_servicios();
        $listar_departamento = $this->departamento->lista_departamento();
        $listar_vehiculos = $this->vehiculo->obtener_vehiculos_con_tarifarios();
        $this->compararPesoConVehiculos($listar_vehiculos);
        return view('livewire.programacioncamiones.programarcamion', compact('listar_tipo_vehiculo', 'listar_tipo_servicio', 'listar_departamento', 'listar_vehiculos'));
    }

    public function mount() {
        $this->despacho_fecha = now()->toDateString();
    }

    public function listar_provincias(){
        $valor = $this->id_departamento;
        if ($valor){
            $this->provincias = DB::table('provincias')->where('id_departamento', '=', $valor)->get();
        }
    }

    public function listar_distritos(){
        $valor = $this->id_provincia;
        if ($valor){
            $this->distritos=DB::table('distritos')->where('id_provincia', '=', $valor)->get();
        }
    }

    public function buscarClientes(){
        $this->filteredClientes = array_filter($this->clientes, function ($cliente) {
            return str_contains(strtolower($cliente['nombre']), strtolower($this->searchCliente)) ||
                str_contains(strtolower($cliente['ruc']), strtolower($this->searchCliente));
        });
    }

    public $facturasPorCliente = [];
    public function updateSearchCliente($id){
        $cliente = collect($this->clientes)->firstWhere('id', $id);
        // Actualiza solo los campos relacionados con el cliente seleccionado.
        if ($cliente) {
            $this->selectedCliente = $id;
            $this->searchCliente = $cliente['nombre'] . ' ' . $cliente['ruc'];
            $this->selectedClientes = [];
            // Actualizar `facturasPorCliente` sin afectar otros campos.
            $this->facturasPorCliente = collect($this->facturasCli)
                ->where('nombre', $cliente['nombre'])
                ->map(function ($facturasCli) {
                    return [
                        'id' => $facturasCli['id'],
                        'serie' => $facturasCli['serie'],
                        'nombre' => $facturasCli['nombre'],
                        'peso' => $facturasCli['peso'],
                        'volumen' => $facturasCli['volumen'],
                    ];
                })
                ->toArray();
        }
    }

    public function seleccionarFacturaCliente($facturaId) {
        $factura = collect($this->facturasPorCliente)->firstWhere('id', $facturaId);
        if ($factura) {
            // Agregar la factura seleccionada a la lista de facturas
            $this->selectedFacturas[] = $factura;
            $this->pesoTotal += $factura['peso'];
            $this->volumenTotal += $factura['volumen'];
            // Eliminar la factura de `facturasPorCliente`
            $this->facturasPorCliente = array_filter($this->facturasPorCliente, fn($f) => $f['id'] !== $facturaId);
            // Recalcula las sugerencias de vehículos según el peso total actualizado
            $this->compararPesoConVehiculos($this->vehiculo->obtener_vehiculos_con_tarifarios());
        }
    }

    public function buscarFacturas() {
        $this->filteredFacturas = array_filter($this->facturas, function ($factura) {
            return (str_contains(strtolower($factura['serie']), strtolower($this->searchFactura)) ||
                    str_contains(strtolower($factura['nombre']), strtolower($this->searchFactura)) ||
                    str_contains((string) $factura['peso'], $this->searchFactura) ||
                    str_contains((string) $factura['volumen'], $this->searchFactura))
                && !in_array($factura, $this->selectedFacturas);
        });
    }

    public function seleccionarFactura($facturaId) {
        $factura = collect($this->facturas)->firstWhere('id', $facturaId);
        if ($factura) {
            // Agregar la factura seleccionada y actualizar el peso y volumen total
            $this->selectedFacturas[] = $factura;
            $this->pesoTotal += $factura['peso'];
            $this->volumenTotal += $factura['volumen']; // Sumar volumen

            // Eliminar la factura de la lista de facturas filtradas
            $this->filteredFacturas = array_filter($this->filteredFacturas, fn($f) => $f['id'] !== $facturaId);

            // Comparar el peso total con los vehículos nuevamente
            $this->compararPesoConVehiculos($this->vehiculo->listar_vehiculo());
        }
    }

    public $vehiculosSugeridos = [];
    public function eliminarFacturaSeleccionada($facturaId) {
        // Encuentra la factura en las seleccionadas
        $factura = collect($this->selectedFacturas)->firstWhere('id', $facturaId);
        if ($factura) {
            // Elimina la factura de la lista de seleccionadas y actualiza el peso y volumen total
            $this->selectedFacturas = array_filter($this->selectedFacturas, fn($f) => $f['id'] !== $facturaId);
            $this->pesoTotal -= $factura['peso'];
            $this->volumenTotal -= $factura['volumen']; // Restar volumen

            // Restaurar la factura en `facturasPorCliente` si el cliente seleccionado coincide
            if ($factura['nombre'] === explode(' ', $this->searchCliente)[0]) {
                $this->facturasPorCliente[] = $factura;
            }

            // Recalcula las sugerencias de vehículos
            $this->compararPesoConVehiculos($this->vehiculo->listar_vehiculo());
            // Vuelve a ordenar `facturasPorCliente` si es necesario
            $this->facturasPorCliente = array_values($this->facturasPorCliente);
        }
    }

    // Comparar el peso total con la capacidad de los vehículos
    public function compararPesoConVehiculos($listar_vehiculos){
        $this->vehiculosSugeridos = [];
        foreach ($listar_vehiculos as $vehiculo) {
            // Verificar que el peso del vehículo esté dentro del rango permitido
            $pesoEnRango = $vehiculo->vehiculo_capacidad_peso >= $this->pesoTotal;
            if (!empty($vehiculo->tarifa_cap_min) && !empty($vehiculo->tarifa_cap_max)) {
                $pesoEnRango = $pesoEnRango &&
                    $this->pesoTotal >= $vehiculo->tarifa_cap_min &&
                    $this->pesoTotal <= $vehiculo->tarifa_cap_max;
            }
            // Verificación de la ubicación si el servicio es del tipo 2
            $ubicacionCoincide = true;
            if ($this->id_tipo_servicios == 2) {
                $ubicacionCoincide = (
                    $vehiculo->id_departamento == $this->id_departamento &&
                    $vehiculo->id_provincia == $this->id_provincia &&
                    ($vehiculo->id_distrito == $this->id_distrito || $this->id_distrito === null)
                );
            }
            if ($pesoEnRango && $ubicacionCoincide) {
                // Calculando el porcentaje de capacidad utilizada
                $vehiculo->vehiculo_capacidad_usada = ($this->pesoTotal / $vehiculo->vehiculo_capacidad_peso) * 100;
                $this->vehiculosSugeridos[] = $vehiculo;
            }
        }
        // Ordenar los vehículos sugeridos según estado de aprobación y porcentaje de capacidad usada
        usort($this->vehiculosSugeridos, function ($a, $b) {
            return [$b->tarifa_estado_aprobacion, $b->vehiculo_capacidad_usada] <=> [$a->tarifa_estado_aprobacion, $a->vehiculo_capacidad_usada];
        });
    }

    public function resetearCampoTipoServicio(){
        if ($this->id_tipo_servicios == 1) {
            $this->searchFactura = '';
            $this->filteredFacturas = [];
            $this->pesoTotal = 0;
            $this->volumenTotal = 0;
            $this->selectedFacturas = [];
        } elseif ($this->id_tipo_servicios == 2) {
            $this->searchCliente = '';
            $this->filteredClientes = [];
            $this->facturasPorCliente = [];
            $this->selectedCliente = null;
            $this->pesoTotal = 0;
            $this->volumenTotal = 0;
            $this->selectedFacturas = [];
            $this->id_departamento = "";
            $this->id_provincia = "";
            $this->id_distrito = "";
            $this->provincias = [];
            $this->distritos = [];
        }
        $this->vehiculosSugeridos = [];
        $this->despacho_fecha = now()->toDateString();
        $this->despacho_mano_obra = "";
        $this->despacho_otros = "";
        $this->selectedVehiculo = null;
    }
}
