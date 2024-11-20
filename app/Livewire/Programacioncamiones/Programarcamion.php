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
use App\Models\Programacion;
use App\Models\Despacho;
use App\Models\DespachoVenta;
use Illuminate\Support\Facades\DB;

class Programarcamion extends Component
{
    private $logs;
    private $transportista;
    private $tipovehiculo;
    private $tiposervicio;
    private $vehiculo;
    private $departamento;
    private $programacion;
    private $despacho;
    private $despachoventa;

    public function __construct(){
        $this->logs = new Logs();
        $this->transportista = new Transportista();
        $this->tipovehiculo = new TipoVehiculo();
        $this->tiposervicio = new TipoServicio();
        $this->vehiculo = new Vehiculo();
        $this->departamento = new Departamento();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->despachoventa = new DespachoVenta();
    }

    public $selected_transportista = null;
    public $search = "";
    public $despacho_fecha = "";
    public $despacho_mano_obra = "";
    public $despacho_otros = "";
    public $searchFactura = "";
    public $searchCliente = "";
    public $id_transportistas = "";
    public $id_programacion = "";
    public $id_tipo_servicios = "";
    public $id_tipo_vehiculo = "";
    public $id_vehiculo = "";
    public $id_departamento = "";
    public $id_provincia = "";
    public $id_distrito = "";
    public $selectedVehiculo = null;
    public $despacho_peso;
    public $despacho_volumen;
    public $despacho_flete;
    public $despacho_ayudante;
    public $despacho_gasto_otros;
    public $facturas = [
        ['id' => 9, 'serie' => 'FC-001', 'nombre' => 'Cliente A', 'peso' => 250, 'volumen' => 250000, 'tipo' => 1 ],
        ['id' => 10, 'serie' => 'FC-002', 'nombre' => 'Cliente B', 'peso' => 300, 'volumen' => 300000, 'tipo' => 1],
        ['id' => 11, 'serie' => 'FC-003', 'nombre' => 'Cliente C', 'peso' => 450, 'volumen' => 450000, 'tipo' => 1],
        ['id' => 12, 'serie' => 'FC-004', 'nombre' => 'Cliente D', 'peso' => 110, 'volumen' => 110000, 'tipo' => 1],
    ];

    public $clientes = [
        ['id' => 2, 'nombre' => 'Shambo', 'ruc' => '20305687411'],
        ['id' => 3, 'nombre' => 'Topitop', 'ruc' => '20457865499'],
        ['id' => 4, 'nombre' => 'Quispe', 'ruc' => '20025674522'],
    ];

    public $facturasCli = [
        ['id' => 1, 'serie' => 'FC-005', 'nombre' => 'Shambo', 'peso' => 250, 'volumen' => 250000, 'tipo' => 2],
        ['id' => 2, 'serie' => 'FC-006', 'nombre' => 'Shambo', 'peso' => 300, 'volumen' => 300000, 'tipo' => 2],
        ['id' => 3, 'serie' => 'FC-007', 'nombre' => 'Topitop', 'peso' => 450, 'volumen' => 450000, 'tipo' => 2],
        ['id' => 4, 'serie' => 'FC-008', 'nombre' => 'Topitop', 'peso' => 500, 'volumen' => 500000, 'tipo' => 2],
        ['id' => 5, 'serie' => 'FC-009', 'nombre' => 'Quispe', 'peso' => 290, 'volumen' => 290000, 'tipo' => 2],
        ['id' => 6, 'serie' => 'FC-010', 'nombre' => 'Quispe', 'peso' => 700, 'volumen' => 700000, 'tipo' => 2],
        ['id' => 7, 'serie' => 'FC-011', 'nombre' => 'Quispe', 'peso' => 50, 'volumen' => 50000, 'tipo' => 2],
        ['id' => 8, 'serie' => 'FC-012', 'nombre' => 'Quispe', 'peso' => 100, 'volumen' => 100000, 'tipo' => 2],
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
    public $tipoServicioSeleccionado = '';
    public $transportistasPorCliente = [];
    public $tarifaMontoSeleccionado = 0;
    public $searchFacturaCliente = '';
    public $filteredFacturasYClientes = [];
    public $selectedFacturasLocal = [];
    public $selectedFacturasProvincial = [];
    public $facturasPorCliente = [];
    public $vehiculosSugeridos = [];

    public function render(){
        $this->buscarFacturas();
        $this->buscarClientes();
        $listar_tipo_vehiculo = $this->tipovehiculo->listar_tipo_vehiculo();
        $listar_tipo_servicio = $this->tiposervicio->listar_tipo_servicios();
        $tipo_servicio_local_provincial = $this->tiposervicio->listar_tipo_servicio_local_provincial();
        $listar_departamento = $this->departamento->lista_departamento();
        $listar_vehiculos = $this->vehiculo->obtener_vehiculos_con_tarifarios();
        $this->compararPesoConVehiculos($listar_vehiculos);
        $listar_transportistas = $this->transportista->listar_transportista_sin_id();
        return view('livewire.programacioncamiones.programarcamion', compact('listar_tipo_vehiculo', 'listar_tipo_servicio', 'listar_departamento', 'listar_vehiculos', 'listar_transportistas', 'tipo_servicio_local_provincial'));
    }

    public function mount(){
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

    public function buscarFacturasYClientes() {
        $this->filteredFacturasYClientes = array_filter(array_merge($this->facturas, $this->facturasCli), function ($factura) {
            return (str_contains(strtolower($factura['serie']), strtolower($this->searchFacturaCliente)) ||
                    str_contains(strtolower($factura['nombre']), strtolower($this->searchFacturaCliente)) ||
                    str_contains((string) $factura['peso'], $this->searchFacturaCliente) ||
                    str_contains((string) $factura['volumen'], $this->searchFacturaCliente))
                && !in_array($factura, $this->selectedFacturas); // Excluir facturas seleccionadas
        });
    }

    public function seleccionarFacturaClienteJunto($facturaId) {
        $factura = collect(array_merge($this->facturas, $this->facturasCli))->firstWhere('id', $facturaId);

        if ($factura) {
            if ($this->tipoServicioSeleccionado == 1) { // Local
                $this->selectedFacturasLocal[] = $factura;
            } elseif ($this->tipoServicioSeleccionado == 2) { // Provincial
                // Agrupar facturas por nombre de cliente
                $cliente = $factura['nombre'];
                if (!isset($this->selectedFacturasProvincial[$cliente])) {
                    $this->selectedFacturasProvincial[$cliente] = [];
                }
                $this->selectedFacturasProvincial[$cliente][] = $factura;
            }

            // Actualiza los totales según el tipo de servicio
            if ($this->tipoServicioSeleccionado == 1 || $this->tipoServicioSeleccionado == 2) {
                $this->pesoTotal += $factura['peso'];
                $this->volumenTotal += $factura['volumen'];
            }

            // Elimina la factura seleccionada de la lista de búsqueda
            $this->filteredFacturasYClientes = array_filter($this->filteredFacturasYClientes, fn($f) => $f['id'] !== $facturaId);
        }
    }


    public function buscarClientes(){
        $this->filteredClientes = array_filter($this->clientes, function ($cliente) {
            return str_contains(strtolower($cliente['nombre']), strtolower($this->searchCliente)) ||
                str_contains(strtolower($cliente['ruc']), strtolower($this->searchCliente));
        });
    }

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

    public function seleccionarFacturaCliente($facturaId){
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

    public function buscarFacturas(){
        $this->filteredFacturas = array_filter($this->facturas, function ($factura) {
            return (str_contains(strtolower($factura['serie']), strtolower($this->searchFactura)) ||
                    str_contains(strtolower($factura['nombre']), strtolower($this->searchFactura)) ||
                    str_contains((string) $factura['peso'], $this->searchFactura) ||
                    str_contains((string) $factura['volumen'], $this->searchFactura))
                && !in_array($factura, $this->selectedFacturas);
        });
    }

    public function seleccionarFactura($facturaId){
        $factura = collect($this->facturas)->firstWhere('id', $facturaId);
        if ($factura) {
            // Agregar la factura seleccionada y actualizar el peso y volumen total
            $this->selectedFacturas[] = $factura;
            $this->pesoTotal += $factura['peso'];
            $this->volumenTotal += $factura['volumen'];

            // Eliminar la factura de la lista de facturas filtradas
            $this->filteredFacturas = array_filter($this->filteredFacturas, fn($f) => $f['id'] !== $facturaId);

            // Comparar el peso total con los vehículos nuevamente
            $this->compararPesoConVehiculos($this->vehiculo->listar_vehiculo());
        }
    }

    public function eliminarFacturaSeleccionada($facturaId) {
        // Encuentra la factura en las seleccionadas
        $factura = collect($this->selectedFacturas)->firstWhere('id', $facturaId);

        if ($factura) {
            // Elimina la factura de la lista seleccionada
            $this->selectedFacturas = array_filter($this->selectedFacturas, fn($f) => $f['id'] !== $facturaId);

            // Actualiza los totales
            $this->pesoTotal -= $factura['peso'];
            $this->volumenTotal -= $factura['volumen'];

            // Reintegrar la factura eliminada si coincide con la búsqueda actual
            if (str_contains(strtolower($factura['serie']), strtolower($this->searchFacturaCliente)) ||
                str_contains(strtolower($factura['nombre']), strtolower($this->searchFacturaCliente)) ||
                str_contains((string) $factura['peso'], $this->searchFacturaCliente) ||
                str_contains((string) $factura['volumen'], $this->searchFacturaCliente)) {
                $this->filteredFacturasYClientes[] = $factura;
            }

            // Recalcular las sugerencias de vehículos
            $this->compararPesoConVehiculos($this->vehiculo->listar_vehiculo());
        }
    }

    public function eliminarSeleccion($facturaId, $tipoServicio) {
        if ($tipoServicio == 'local') {
            // Encuentra la factura en selectedFacturasLocal
            $factura = collect($this->selectedFacturasLocal)->firstWhere('id', $facturaId);

            if ($factura) {
                // Elimina la factura de selectedFacturasLocal
                $this->selectedFacturasLocal = array_filter($this->selectedFacturasLocal, fn($f) => $f['id'] !== $facturaId);

                // Actualiza los totales
                $this->pesoTotal -= $factura['peso'];
                $this->volumenTotal -= $factura['volumen'];

                // Reintegra la factura eliminada si coincide con la búsqueda
                if (str_contains(strtolower($factura['serie']), strtolower($this->searchFacturaCliente)) ||
                    str_contains(strtolower($factura['nombre']), strtolower($this->searchFacturaCliente)) ||
                    str_contains((string) $factura['peso'], $this->searchFacturaCliente) ||
                    str_contains((string) $factura['volumen'], $this->searchFacturaCliente)) {
                    $this->filteredFacturasYClientes[] = $factura;
                }

                // Recalcular las sugerencias de vehículos
                $this->compararPesoConVehiculos($this->vehiculo->listar_vehiculo());
            }
        } elseif ($tipoServicio == 'provincial') {
            // Encuentra la factura en selectedFacturasProvincial
            foreach ($this->selectedFacturasProvincial as $cliente => $facturas) {
                $factura = collect($facturas)->firstWhere('id', $facturaId);

                if ($factura) {
                    // Elimina la factura de selectedFacturasProvincial
                    $this->selectedFacturasProvincial[$cliente] = array_filter($facturas, fn($f) => $f['id'] !== $facturaId);

                    // Si no hay más facturas para el cliente, elimina el cliente del array
                    if (empty($this->selectedFacturasProvincial[$cliente])) {
                        unset($this->selectedFacturasProvincial[$cliente]);
                    }

                    // Actualiza los totales
                    $this->pesoTotal -= $factura['peso'];
                    $this->volumenTotal -= $factura['volumen'];

                    // Reintegra la factura eliminada si coincide con la búsqueda
                    if (str_contains(strtolower($factura['serie']), strtolower($this->searchFacturaCliente)) ||
                        str_contains(strtolower($factura['nombre']), strtolower($this->searchFacturaCliente)) ||
                        str_contains((string) $factura['peso'], $this->searchFacturaCliente) ||
                        str_contains((string) $factura['volumen'], $this->searchFacturaCliente)) {
                        $this->filteredFacturasYClientes[] = $factura;
                    }

                    // Recalcular las sugerencias de vehículos
                    $this->compararPesoConVehiculos($this->vehiculo->listar_vehiculo());

                    break;
                }
            }
        }
    }



    public function actualizarVehiculosSugeridos(){
        // Verificar si se ha seleccionado un transportista
        if ($this->id_transportistas) {
            // Llamar el método para comparar el peso con los vehículos y filtrar los vehículos sugeridos
            $listar_vehiculos = $this->vehiculo->obtener_vehiculos_con_tarifarios();
            $this->compararPesoConVehiculos($listar_vehiculos);
        } else {
            // Si no se ha seleccionado un transportista, vaciar la lista de vehículos sugeridos
            $this->vehiculosSugeridos = [];
        }
    }

    public function compararPesoConVehiculos($listar_vehiculos){
        $this->vehiculosSugeridos = [];
        // Si no hay transportista seleccionado, no aplicar filtro
        if (empty($this->id_transportistas)) {
            $listar_vehiculos = $listar_vehiculos;
        } else {
            // Filtrar vehículos de acuerdo al transportista seleccionado
            $listar_vehiculos = $listar_vehiculos->filter(function ($vehiculo) {
                return $vehiculo->id_transportistas == $this->id_transportistas;
            });
        }
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
                if (!in_array($vehiculo->id_vehiculo, array_column($this->vehiculosSugeridos, 'id_vehiculo'))) {
                    $this->vehiculosSugeridos[] = $vehiculo;
                }
            }
        }
        // Ordenar los vehículos sugeridos según estado de aprobación y porcentaje de capacidad usada
        usort($this->vehiculosSugeridos, function ($a, $b) {
            return [$b->tarifa_estado_aprobacion, $b->vehiculo_capacidad_usada] <=> [$a->tarifa_estado_aprobacion, $a->vehiculo_capacidad_usada];
        });
    }

    public function seleccionarVehiculo($vehiculoId){
        $vehiculo = collect($this->vehiculosSugeridos)->firstWhere('id_vehiculo', $vehiculoId);
        if ($vehiculo) {
            // Actualiza el monto de la tarifa del vehículo seleccionado
            $this->tarifaMontoSeleccionado = $vehiculo->tarifa_monto;
        }
    }

    public function resetearCampoTipoServicio(){
        if ($this->id_tipo_servicios == 1) {
            $this->searchFactura = '';
            $this->filteredFacturas = [];
            $this->pesoTotal = 0;
            $this->volumenTotal = 0;
            $this->selectedFacturas = [];
            $this->id_transportistas = '';
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
            $this->id_transportistas = '';
        } elseif ($this->id_tipo_servicios == 3){
            $this->pesoTotal = 0;
            $this->volumenTotal = 0;
            $this->tipoServicioSeleccionado = '';
            $this->searchFacturaCliente = '';
            $this->filteredFacturasYClientes = [];
            $this->transportistasPorCliente = [];
            $this->selectedFacturasLocal = [];
            $this->selectedFacturasProvincial = [];
            $this->id_transportistas = '';
        }
        $this->vehiculosSugeridos = [];
        $this->despacho_fecha = now()->toDateString();
        $this->despacho_mano_obra = "";
        $this->despacho_otros = "";
        $this->selectedVehiculo = null;
        $this->tarifaMontoSeleccionado = 0;
    }

    public function deseleccionarFactura($facturaId){
        $this->selectedFacturas = array_filter($this->selectedFacturas, function ($factura) use ($facturaId) {
            if ($factura['id'] === $facturaId) {
                $this->pesoTotal -= $factura['peso'];
                $this->volumenTotal -= $factura['volumen'];
                return false;
            }
            return true;
        });
        // Reagregar la factura a las facturas filtradas
        $factura = collect($this->facturas)->firstWhere('id', $facturaId);
        if ($factura) {
            $this->filteredFacturas[] = $factura;
            $this->compararPesoConVehiculos($this->vehiculo->listar_vehiculo());
        }
    }

//    GUARDAR UN DESPACHO
    public function guardarDespachos(){
        try {
            $this->validate([
                'id_tipo_servicios' => 'nullable|integer',
                'id_transportistas' => 'required|integer',
                'selectedVehiculo' => 'required|integer',
                'id_departamento' => 'required_if:id_tipo_servicios,2|nullable|integer',
                'id_provincia' => 'required_if:id_tipo_servicios,2|nullable|integer',
                'id_distrito' => 'nullable|integer',
                'despacho_peso' => 'nullable|numeric',
                'despacho_volumen' => 'nullable|numeric',
                'despacho_flete' => 'nullable|numeric',
                'despacho_ayudante' => 'nullable|numeric',
                'despacho_gasto_otros' => 'nullable|numeric',
            ], [
                'selectedVehiculo.required' => 'Debes seleccionar un vehículo.',
                'selectedVehiculo.integer' => 'El vehículo debe ser un número entero.',

                'id_transportistas.required' => 'Debes seleccionar un transportista.',
                'id_transportistas.integer' => 'El transportista debe ser un número entero.',

                'id_departamento.required_if' => 'Debes seleccionar un departamento.',
                'id_departamento.integer' => 'El departamento debe ser un número entero.',

                'id_provincia.required_if' => 'Debes seleccionar una provincia.',
                'id_provincia.integer' => 'La provincia debe ser un número entero.',
            ]);
            DB::beginTransaction();
            // Guardar en la tabla Programaciones
            $programacion = new Programacion();
            $programacion->id_users = Auth::id();
            $programacion->programacion_fecha = $this->despacho_fecha;
            $programacion->programacion_estado = 1;
            $programacion->programacion_microtime = microtime(true);
            if (!$programacion->save()) {
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar la programación.');
                return;
            }
            // Guardar en la tabla Despachos según el tipo de servicio
            if ($this->id_tipo_servicios == 1) {
                // Tipo de servicio 1
                $despacho = new Despacho();
                $despacho->id_users = Auth::id();
                $despacho->id_programacion = $programacion->id_programacion;
                $despacho->id_transportistas = $this->id_transportistas;
                $despacho->id_tipo_servicios = $this->id_tipo_servicios;
                $despacho->id_vehiculo = $this->selectedVehiculo;
                $despacho->despacho_peso = $this->pesoTotal;
                $despacho->despacho_volumen = $this->volumenTotal;
                $despacho->despacho_flete = $this->tarifaMontoSeleccionado;
                $despacho->despacho_ayudante = $this->despacho_mano_obra ?: null;
                $despacho->despacho_gasto_otros = $this->despacho_otros ?: null;
                // Calcular despacho_costo_total
                $despacho_costo_total = $this->tarifaMontoSeleccionado;
                if (!empty($this->despacho_mano_obra)) {
                    $despacho_costo_total += $this->despacho_mano_obra;
                }
                if (!empty($this->despacho_otros)) {
                    $despacho_costo_total += $this->despacho_otros;
                }
                $despacho->despacho_costo_total = $despacho_costo_total;
                // Otros datos
                $despacho->despacho_estado = 1;
                $despacho->despacho_microtime = microtime(true);
                if (!$despacho->save()) {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar el registro.');
                }
                // Guardar facturas seleccionadas en despacho_ventas
                foreach ($this->selectedFacturas as $factura) {
                    $despachoVenta = new DespachoVenta();
                    $despachoVenta->id_despacho = $despacho->id_despacho;
                    $despachoVenta->id_venta = null;
                    $despachoVenta->despacho_venta_factura = $factura['serie'];
                    $despachoVenta->despacho_detalle_estado = 1;
                    $despachoVenta->despacho_detalle_microtime = microtime(true);
                    if (!$despachoVenta->save()) {
                        DB::rollBack();
                        session()->flash('error', 'Ocurrió un error al guardar el registro.');
                    }
                }
                DB::commit();
                session()->flash('success', 'Registro guardado correctamente.');
                $this->reiniciar_campos();
            } elseif ($this->id_tipo_servicios == 2) {
                // Tipo de servicio 2 (Agregar los campos id_departamento, id_provincia, id_distrito)
                $despacho = new Despacho();
                $despacho->id_users = Auth::id();
                $despacho->id_programacion = $programacion->id_programacion;
                $despacho->id_transportistas = $this->id_transportistas;
                $despacho->id_tipo_servicios = $this->id_tipo_servicios;
                $despacho->id_vehiculo = $this->selectedVehiculo;
                $despacho->id_departamento = $this->id_tipo_servicios == 2 ? $this->id_departamento : null;
                $despacho->id_provincia = $this->id_tipo_servicios == 2 ? $this->id_provincia : null;
                $despacho->id_distrito = $this->id_distrito ?: null;
                $despacho->despacho_peso = $this->pesoTotal;
                $despacho->despacho_volumen = $this->volumenTotal;
                $despacho->despacho_flete = $this->tarifaMontoSeleccionado * $this->pesoTotal;
                $despacho->despacho_ayudante = $this->despacho_mano_obra ?: null;
                $despacho->despacho_gasto_otros = $this->despacho_otros ?: null;
                // Calcular despacho_costo_total
                $despacho_costo_total = $despacho->despacho_flete;
                if (!empty($this->despacho_mano_obra)) {
                    $despacho_costo_total += $this->despacho_mano_obra;
                }
                if (!empty($this->despacho_otros)) {
                    $despacho_costo_total += $this->despacho_otros;
                }
                $despacho->despacho_costo_total = $despacho_costo_total;
                $despacho->despacho_estado = 1;
                $despacho->despacho_microtime = microtime(true);
                if (!$despacho->save()) {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar el registro.');
                }
                foreach ($this->selectedFacturas as $factura) {
                    $despachoVenta = new DespachoVenta();
                    $despachoVenta->id_despacho = $despacho->id_despacho;
                    $despachoVenta->id_venta = null;
                    $despachoVenta->despacho_venta_factura = $factura['serie'];
                    $despachoVenta->despacho_detalle_estado = 1;
                    $despachoVenta->despacho_detalle_microtime = microtime(true);
                    if (!$despachoVenta->save()) {
                        DB::rollBack();
                        session()->flash('error', 'Ocurrió un error al guardar el registro.');
                    }
                }
            } elseif ($this->id_tipo_servicios == 3) {
                // Verificar si hay al menos un comprobante provincial seleccionado
                $comprobantesProvinciales = $this->selectedFacturasProvincial;

                if (empty($comprobantesProvinciales)) {
                    session()->flash('error', 'Debe seleccionar al menos un comprobante provincial.');
                    return;
                }
                // Crear despachos para comprobantes provinciales agrupados por cliente
                $clientes = [];
                foreach ($comprobantesProvinciales as $cliente => $facturas) {
                    $clientes[$cliente] = $facturas;
                }
                foreach ($clientes as $cliente => $facturasCliente) {
                    // Verificar si el transportista fue seleccionado para este cliente
                    if (empty($this->transportistasPorCliente[$cliente])) {
                        session()->flash('error', "Debe seleccionar un transportista para el cliente: $cliente.");
                        return;
                    }
                    // Crear despacho para este cliente
                    $despacho = new Despacho();
                    $despacho->id_users = Auth::id();
                    $despacho->id_programacion = $programacion->id_programacion;
                    $despacho->id_transportistas = $this->transportistasPorCliente[$cliente]; // Transportista seleccionado
                    $despacho->id_tipo_servicios = $this->id_tipo_servicios;
                    $despacho->id_vehiculo = $this->selectedVehiculo;
                    $despacho->despacho_peso = array_sum(array_column($facturasCliente, 'peso'));
                    $despacho->despacho_volumen = array_sum(array_column($facturasCliente, 'volumen'));
                    $despacho->despacho_flete = $this->tarifaMontoSeleccionado;
                    $despacho->despacho_ayudante = $this->despacho_mano_obra ?: null;
                    $despacho->despacho_gasto_otros = $this->despacho_otros ?: null;
                    // Calcular costo total del despacho
                    $despacho->despacho_costo_total = $despacho->despacho_flete
                        + ($despacho->despacho_ayudante ?: 0)
                        + ($despacho->despacho_gasto_otros ?: 0);

                    $despacho->despacho_estado = 1;
                    $despacho->despacho_microtime = microtime(true);
                    if (!$despacho->save()) {
                        DB::rollBack();
                        session()->flash('error', "Error al guardar el despacho para el cliente: $cliente.");
                        return;
                    }
                    // Guardar las facturas asociadas a este despacho
                    foreach ($facturasCliente as $factura) {
                        $despachoVenta = new DespachoVenta();
                        $despachoVenta->id_despacho = $despacho->id_despacho;
                        $despachoVenta->id_venta = null; // Si aplica
                        $despachoVenta->despacho_venta_factura = $factura['serie'];
                        $despachoVenta->despacho_detalle_estado = 1;
                        $despachoVenta->despacho_detalle_microtime = microtime(true);

                        if (!$despachoVenta->save()) {
                            DB::rollBack();
                            session()->flash('error', "Error al guardar las facturas para el cliente: $cliente.");
                            return;
                        }
                    }
                }
                // Crear despachos para comprobantes locales (uno por cada comprobante)
                $comprobantesLocales = $this->selectedFacturasLocal;
                // Crear un solo despacho para todos los comprobantes locales
                $despacho = new Despacho();
                $despacho->id_users = Auth::id();
                $despacho->id_programacion = $programacion->id_programacion;
                $despacho->id_transportistas = $this->id_transportistas; // Transportista seleccionado
                $despacho->id_tipo_servicios = $this->id_tipo_servicios;
                $despacho->id_vehiculo = $this->selectedVehiculo;
                $despacho->despacho_peso = array_sum(array_column($comprobantesLocales, 'peso'));
                $despacho->despacho_volumen = array_sum(array_column($comprobantesLocales, 'volumen'));
                $despacho->despacho_flete = $this->tarifaMontoSeleccionado;
                $despacho->despacho_ayudante = $this->despacho_mano_obra ?: null;
                $despacho->despacho_gasto_otros = $this->despacho_otros ?: null;
                // Calcular costo total del despacho
                $despacho->despacho_costo_total = $despacho->despacho_flete
                    + ($despacho->despacho_ayudante ?: 0)
                    + ($despacho->despacho_gasto_otros ?: 0);
                $despacho->despacho_estado = 1;
                $despacho->despacho_microtime = microtime(true);
                if (!$despacho->save()) {
                    DB::rollBack();
                    session()->flash('error', "Error al guardar el despacho para los comprobantes locales.");
                    return;
                }
                // Guardar los comprobantes locales en despacho_ventas
                foreach ($comprobantesLocales as $factura) {
                    $despachoVenta = new DespachoVenta();
                    $despachoVenta->id_despacho = $despacho->id_despacho;
                    $despachoVenta->id_venta = null; // Si aplica
                    $despachoVenta->despacho_venta_factura = $factura['serie'];
                    $despachoVenta->despacho_detalle_estado = 1;
                    $despachoVenta->despacho_detalle_microtime = microtime(true);

                    if (!$despachoVenta->save()) {
                        DB::rollBack();
                        session()->flash('error', "Error al guardar la factura local: {$factura['serie']}.");
                        return;
                    }
                }
            }
            DB::commit();
            session()->flash('success', 'Registro guardado correctamente.');
            $this->reiniciar_campos();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error inesperado. Por favor, inténtelo nuevamente.');
        }
    }

    public function reiniciar_campos()
    {
        $this->id_tipo_servicios = null;
        $this->id_transportistas = null;
        $this->id_vehiculo = null;
        $this->id_departamento = null;
        $this->id_provincia = null;
        $this->id_distrito = null;
        $this->despacho_peso = null;
        $this->despacho_volumen = null;
        $this->despacho_flete = null;
        $this->despacho_ayudante = null;
        $this->despacho_gasto_otros = null;
        $this->selectedFacturas = [];
        $this->selectedVehiculo = null;
        $this->tarifaMontoSeleccionado = 0;
        $this->pesoTotal = 0;
        $this->volumenTotal = 0;
        $this->tipoServicioSeleccionado = '';
        $this->searchFacturaCliente = '';
        $this->filteredFacturasYClientes = [];
        $this->transportistasPorCliente = [];
        $this->selectedFacturasLocal = [];
        $this->selectedFacturasProvincial = [];
    }

}
