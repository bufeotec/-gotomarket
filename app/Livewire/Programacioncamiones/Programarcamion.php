<?php

namespace App\Livewire\Programacioncamiones;

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
    public $searchFactura = "";
    public $searchCliente = "";
    public $id_transportistas = "";
    public $id_tipo_servicios = "";
    public $id_tipo_vehiculo = "";
    public $id_vehiculo = "";
    public $id_departamento = "";
    public $id_provincia = "";
    public $id_distrito = "";
    public $despacho_tipo = "";
    public $despacho_peso_total = "";
    public $despacho_costo_total = "";
    public $despacho_estado = "";
    public $despacho_microtime = "";
    public $despacho_mano_obra = "";
    public $despacho_otro = "";
    public $facturas = [
        ['id' => 1, 'serie' => 'FC-001', 'nombre' => 'Cliente A', 'peso' => 250],
        ['id' => 2, 'serie' => 'FC-002', 'nombre' => 'Cliente B', 'peso' => 300],
        ['id' => 3, 'serie' => 'FC-003', 'nombre' => 'Cliente C', 'peso' => 450],
    ];

    public $clientes = [
        ['id' => 2, 'nombre' => 'Shambo', 'ruc' => '20305687411'],
        ['id' => 3, 'nombre' => 'Topitop', 'ruc' => '20457865499'],
        ['id' => 4, 'nombre' => 'Quispe', 'ruc' => '20025674522'],
    ];

    public $facturasCli = [
        ['id' => 1, 'serie' => 'FC-001', 'nombre' => 'Shambo', 'peso' => 250],
        ['id' => 2, 'serie' => 'FC-002', 'nombre' => 'Shambo', 'peso' => 300],
        ['id' => 3, 'serie' => 'FC-003', 'nombre' => 'Topitop', 'peso' => 450],
        ['id' => 4, 'serie' => 'FC-004', 'nombre' => 'Topitop', 'peso' => 500],
        ['id' => 5, 'serie' => 'FC-005', 'nombre' => 'Quispe', 'peso' => 290],
        ['id' => 6, 'serie' => 'FC-006', 'nombre' => 'Quispe', 'peso' => 700],
    ];

    public $filteredFacturas = [];
    public $filteredClientes = [];
    public $selectedFacturas = [];
    public $selectedClientes = [];
    public $selectedCliente = null;
    public $totalPeso = 0;
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
        $listar_transportistas = $this->listarTransportistasProgramarCamion($this->search);
        $listar_tipo_servicio = $this->tiposervicio->listar_tipo_servicios();
        $listar_departamento = $this->departamento->lista_departamento();
        return view('livewire.programacioncamiones.programarcamion', compact('listar_transportistas', 'listar_tipo_vehiculo', 'listar_tipo_servicio', 'listar_departamento'));
    }

    public function actualizarSeleccion()
    {
        $this->calcularTarifa();
        $this->listar_provincias();
    }
    public function actualizarSeleccionDistritos()
    {
        $this->calcularTarifa();
        $this->listar_distritos();
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
            $this->totalPeso = 0;
            // Actualizar `facturasPorCliente` sin afectar otros campos.
            $this->facturasPorCliente = collect($this->facturasCli)
                ->where('nombre', $cliente['nombre'])
                ->map(function ($factura) {
                    return [
                        'id' => $factura['id'],
                        'serie' => $factura['serie'],
                        'nombre' => $factura['nombre'],
                        'peso' => $factura['peso'],
                    ];
                })
                ->toArray();
        }
    }

    public function buscarFacturas(){
        $this->filteredFacturas = array_filter($this->facturas, function ($factura) {
            return str_contains(strtolower($factura['serie']), strtolower($this->searchFactura)) ||
                str_contains(strtolower($factura['nombre']), strtolower($this->searchFactura)) ||
                str_contains((string) $factura['peso'], $this->searchFactura);
        });
    }

    public function listarTransportistasProgramarCamion($search = ''){
        try {
            $query = DB::table('transportistas')
                ->where('transportista_estado', '=', 1);

            if (!empty($search)) {
                $query->where('transportista_nom_comercial', 'like', '%' . $search . '%');
            }

            return $query->orderBy('id_transportistas', 'desc')->take(10)->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
    }

    public function selectTransportista($id){
        $this->selected_transportista = $id;
        $this->id_transportistas = $id;
        $this->id_tipo_servicios = "";
        $this->id_tipo_vehiculo = "";
        $this->selectedFacturas = [];
        $this->totalPeso = 0;
    }

    public function calculateTotalPeso(){
        $this->totalPeso = collect($this->selectedFacturas)
            ->map(fn($facturaId) => collect($this->facturas)->firstWhere('id', $facturaId)['peso'] ?? 0)
            ->sum();
        $this->calcularTarifa();

    }

    public function calculateTotalPesoCliente(){
        $this->totalPeso = collect($this->selectedClientes)
            ->map(fn($facturaId) => collect($this->facturasCli)->firstWhere('id', $facturaId)['peso'] ?? 0)
            ->sum();
        $this->calcularTarifa();

    }

    public function calcularTarifa() {
        $this->tarifa = null;
        $this->tarifa_estado_aprobacion = null;

        try {
            if ($this->id_tipo_servicios == 2) {
                $tarifarioQuery = DB::table('tarifarios')
                    ->where('id_transportistas', $this->id_transportistas)
                    ->where('id_tipo_servicio', $this->id_tipo_servicios)
                    ->where('id_departamento', $this->id_departamento)
                    ->where('id_provincia', $this->id_provincia)
                    ->where('tarifa_cap_min', '<=', $this->totalPeso)
                    ->where('tarifa_cap_max', '>=', $this->totalPeso)
                    ->where('tarifa_estado', 1);

                // Verifica que id_distrito tenga un valor específico antes de incluirlo
                if (!is_null($this->id_distrito) && $this->id_distrito != '') {
                    $tarifarioQuery->where('id_distrito', $this->id_distrito);
                }

                $tarifario = $tarifarioQuery->first();
            } else {
                $tarifario = DB::table('tarifarios')
                    ->where('id_transportistas', $this->id_transportistas)
                    ->where('id_tipo_servicio', $this->id_tipo_servicios)
                    ->where('id_tipo_vehiculo', $this->id_tipo_vehiculo)
                    ->where('tarifa_cap_min', '<=', $this->totalPeso)
                    ->where('tarifa_cap_max', '>=', $this->totalPeso)
                    ->where('tarifa_estado', 1)
                    ->first();
            }

            if ($tarifario) {
                if ($tarifario->tarifa_estado_aprobacion == 1) {
                    $this->tarifa = $tarifario->tarifa_monto;
                }
                $this->tarifa_estado_aprobacion = $tarifario->tarifa_estado_aprobacion;
            } else {
                $this->tarifa = null;
                $this->tarifa_estado_aprobacion = null;
                session()->flash('error', 'No se encontró un tarifario que coincida con los criterios especificados.');
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
        }
    }



    public function resetearCampoTipoServicio(){
        // Si el tipo de servicio es "local"
        if ($this->id_tipo_servicios == 1) {
            $this->id_tipo_vehiculo = "";
            $this->selectedFacturas = [];
            $this->totalPeso = 0;
            $this->tarifa = null;
            $this->vehiculos = [];
            $this->despacho_mano_obra = "";
            $this->despacho_otro = "";
        }
        // Si el tipo de servicio es "provincial"
        elseif ($this->id_tipo_servicios == 2) {
            // Reiniciar campos específicos del servicio "provincial"
            $this->searchCliente = "";
            $this->id_departamento = "";
            $this->id_provincia = "";
            $this->id_distrito = "";
            $this->despacho_mano_obra = "";
            $this->despacho_otro = "";
            $this->selectedCliente = null;
            $this->facturasPorCliente = [];
            $this->selectedClientes = [];
            $this->totalPeso = 0;
            $this->tarifa = null;
        }
    }

    public function listar_vehiculos(){
        $valor = $this->id_tipo_vehiculo;
        $tr = $this->id_transportistas;
        if ($valor){
            $this->vehiculos=DB::table('vehiculos')
                ->where('id_tipo_vehiculo', '=', $valor)
                ->where('id_transportistas', '=', $tr)
                ->get();
        }
    }

    public function resetearCampos(){
        // Resetear los campos relevantes después de guardar el despacho
        $this->id_transportistas = null;  // Resetear transportista seleccionado
        $this->selected_transportista = null;
        $this->id_tipo_servicios = null;  // Resetear tipo de servicio
        $this->id_tipo_vehiculo = null;  // Resetear tipo de vehículo
        $this->id_vehiculo = null;       // Resetear vehículo
        $this->selectedFacturas = [];    // Limpiar facturas seleccionadas
        $this->totalPeso = 0;            // Resetear peso total
        $this->tarifa = null;            // Resetear tarifa
        $this->tarifa_estado_aprobacion = null; // Resetear estado de aprobación de tarifa
        $this->vehiculos = [];           // Limpiar lista de vehículos
        $this->selectedClientes = [];    // Limpiar clientes seleccionados
        $this->selectedCliente = null;   // Limpiar cliente seleccionado
        $this->searchCliente = "";       // Limpiar búsqueda de clientes
        $this->id_departamento = "";     // Limpiar departamento seleccionado
        $this->id_provincia = "";        // Limpiar provincia seleccionada
        $this->id_distrito = "";         // Limpiar distrito seleccionado
        $this->search = "";              // Limpiar búsqueda de transportistas
        $this->listarTransportistasProgramarCamion();  // Actualizar lista de transportistas
    }

    public function saveDespacho(){
        try {
            // Validación de los campos
            $this->validate([
                'id_transportistas' => 'nullable|integer',
                'id_vehiculo' => 'nullable|integer',
                'id_departamento' => 'nullable|integer',
                'id_provincia' => 'nullable|integer',
                'id_distrito' => 'nullable|integer',
                'despacho_tipo' => 'nullable|integer',
                'despacho_peso_total' => 'nullable|numeric',
                'despacho_costo_total' => 'nullable|numeric',
                'despacho_mano_obra' => 'nullable|string',
                'despacho_otro' => 'nullable|string',
                'despacho_estado' => 'nullable|integer',
            ]);

            // Comenzamos la transacción
            DB::beginTransaction();

            $despacho = new Despacho();
            $despacho->id_users = Auth::id();
            $despacho->id_transportistas = $this->id_transportistas;
            $despacho->id_vehiculo = !empty($this->id_vehiculo) ? $this->id_vehiculo : null;
            $despacho->id_departamento = !empty($this->id_departamento) ? $this->id_departamento : null;
            $despacho->id_provincia = !empty($this->id_provincia) ? $this->id_provincia : null;
            $despacho->id_distrito = !empty($this->id_distrito) ? $this->id_distrito : null;
            $despacho->despacho_fecha = now();
            $despacho->despacho_tipo = $this->id_tipo_servicios;
            $despacho->despacho_peso_total = $this->totalPeso;
            $despacho->despacho_costo_total = $this->tarifa;
            $despacho->despacho_mano_obra = !empty($this->despacho_mano_obra) ? $this->despacho_mano_obra : null;
            $despacho->despacho_otro = !empty($this->despacho_otro) ? $this->despacho_otro : null;
            $despacho->despacho_estado = 1;
            $despacho->despacho_microtime = microtime(true);

            // Guardamos el despacho
            if ($despacho->save()) {
                DB::commit();  // Confirmamos la transacción
                session()->flash('success', 'Despacho guardado correctamente.');
                $this->resetearCampos();
            } else {
                DB::rollBack();  // Revertimos la transacción si hay un error
                session()->flash('error', 'Ocurrió un error al guardar el despacho.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al guardar el despacho. Por favor, inténtelo nuevamente.');
            $this->logs->insertarLog($e);
        }
    }
}
