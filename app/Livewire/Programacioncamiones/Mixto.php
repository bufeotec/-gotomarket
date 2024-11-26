<?php

namespace App\Livewire\Programacioncamiones;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Logs;
use App\Models\TipoServicio;
use App\Models\Server;
use App\Models\Transportista;
use App\Models\Vehiculo;
use App\Models\Programacion;
use App\Models\Despacho;
use App\Models\DespachoVenta;

use Livewire\Component;

class Mixto extends Component
{
    private $logs;
    private $tiposervicio;
    private $server;
    private $transportista;
    private $vehiculo;
    private $programacion;
    private $despacho;
    private $despachoventa;
    public function __construct(){
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
        $this->server = new Server();
        $this->transportista = new Transportista();
        $this->vehiculo = new Vehiculo();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->despachoventa = new DespachoVenta();
    }
    public $tipoServicioSeleccionado = 1;
    public $searchFacturaCliente = '';
    public $filteredFacturasYClientes = [];
    public $detalle_vehiculo = [];
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
    public $programacion_fecha = "";
    public $despacho_ayudante = '';
    public $despacho_gasto_otros = '';
    public $id_tipo_servicios;
    public $despacho_peso;
    public $despacho_volumen;
    public $despacho_flete;
    public $id_tarifario;
    public $id_tarifario_seleccionado = '';
    public $comprobantesSeleccionados = [];


    public function mount(){
        $this->id_transportistas = null;
        $this->selectedVehiculo = null;
        $this->programacion_fecha = now()->format('Y-m-d');
    }

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

    public function seleccionarFactura($CFTD, $CFNUMSER, $CFNUMDOC){
        // Validar que la factura no exista en el array selectedFacturas
        $comprobanteExiste = collect($this->selectedFacturasLocal)->first(function ($factura) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
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
        $factura = $this->filteredFacturasYClientes->first(function ($f) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
            return $f->CFTD === $CFTD
                && $f->CFNUMSER === $CFNUMSER
                && $f->CFNUMDOC === $CFNUMDOC;
        });

        if ($factura->total_kg <= 0 || $factura->total_volumen <= 0){
            session()->flash('error', 'El peso o el volumen deben ser mayores a 0.');
            return;
        }
        // Agregar la factura seleccionada y actualizar el peso y volumen total
        $this->selectedFacturasLocal[] = [
            'CFTD' => $CFTD,
            'CFNUMSER' => $CFNUMSER,
            'CFNUMDOC' => $CFNUMDOC,
            'total_kg' => $factura->total_kg,
            'total_volumen' => $factura->total_volumen,
            'CNOMCLI' => $factura->CNOMCLI,
            'CFIMPORTE' => $factura->CFIMPORTE,
            'CFCODMON' => $factura->CFCODMON,
            'guia' => $factura->CFTEXGUIA,
            'isChecked' => false,
        ];
        $this->pesoTotal += $factura->total_kg;
        $this->volumenTotal += $factura->total_volumen;

        // Eliminar la factura de la lista de facturas filtradas
        $this->filteredFacturasYClientes = $this->filteredFacturasYClientes->filter(function ($f) use ($CFNUMDOC) {
            return $f->CFNUMDOC !== $CFNUMDOC;
        });
        // Actualizar lista de vehículos sugeridos
        $this->listar_vehiculos_lo();
    }


    public function actualizarFactura($CFTD, $CFNUMSER, $CFNUMDOC, $isChecked){
        if ($isChecked) {
            $this->duplicar_comprobante($CFTD, $CFNUMSER, $CFNUMDOC);
        } else {
            $this->eliminarFacturaProvincial($CFTD, $CFNUMSER, $CFNUMDOC);
        }
    }

    public function duplicar_comprobante($CFTD, $CFNUMSER, $CFNUMDOC){
        // Buscar la factura en la tabla Local
        $factura = collect($this->selectedFacturasLocal)->first(function ($f) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
            return $f['CFTD'] === $CFTD &&
                $f['CFNUMSER'] === $CFNUMSER &&
                $f['CFNUMDOC'] === $CFNUMDOC;
        });
        if (!$factura) {
            session()->flash('error', 'Comprobante no encontrado en la tabla Local.');
            return;
        }
        // Verificar si ya existe en la tabla Provincial
        $existeEnProvincial = collect($this->selectedFacturasProvincial[$factura['CNOMCLI']] ?? [])->contains(function ($f) use ($factura) {
            return $f['CFTD'] === $factura['CFTD'] &&
                $f['CFNUMSER'] === $factura['CFNUMSER'] &&
                $f['CFNUMDOC'] === $factura['CFNUMDOC'];
        });
        if ($existeEnProvincial) {
            session()->flash('error', 'El comprobante ya está duplicado en la tabla Provincial.');
            return;
        }
        // Agregar la factura a la tabla Provincial agrupada por cliente
        $this->selectedFacturasProvincial[$factura['CNOMCLI']][] = [
            'CFTD' => $factura['CFTD'],
            'CFNUMSER' => $factura['CFNUMSER'],
            'CFNUMDOC' => $factura['CFNUMDOC'],
            'total_kg' => $factura['total_kg'],
            'total_volumen' => $factura['total_volumen'],
            'CNOMCLI' => $factura['CNOMCLI'],
            'CFIMPORTE' => $factura['CFIMPORTE'],
            'CFCODMON' => $factura['CFCODMON'],
            'guia' => $factura['guia'],
        ];
    }

    public function eliminarFacturaProvincial($CFTD, $CFNUMSER, $CFNUMDOC){
        foreach ($this->selectedFacturasProvincial as $cliente => $facturas) {
            $this->selectedFacturasProvincial[$cliente] = collect($facturas)
                ->filter(function ($factura) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
                    return !($factura['CFTD'] === $CFTD &&
                        $factura['CFNUMSER'] === $CFNUMSER &&
                        $factura['CFNUMDOC'] === $CFNUMDOC);
                })
                ->values()
                ->toArray();
            // Elimina el cliente si ya no tiene facturas
            if (empty($this->selectedFacturasProvincial[$cliente])) {
                unset($this->selectedFacturasProvincial[$cliente]);
            }
        }
        // Actualizar el estado del checkbox en la tabla Local
        foreach ($this->selectedFacturasLocal as &$factura) {
            if ($factura['CFTD'] === $CFTD &&
                $factura['CFNUMSER'] === $CFNUMSER &&
                $factura['CFNUMDOC'] === $CFNUMDOC) {
                $factura['isChecked'] = false;
                break;
            }
        }
    }

    public function eliminarFacturaSeleccionada($CFTD, $CFNUMSER, $CFNUMDOC){
        foreach ($this->selectedFacturasLocal as $index => $factura) {
            if ($factura['CFTD'] == $CFTD && $factura['CFNUMSER'] == $CFNUMSER && $factura['CFNUMDOC'] == $CFNUMDOC) {
                $this->pesoTotal -= $factura['total_kg'];
                $this->volumenTotal -= $factura['total_volumen'];
                unset($this->selectedFacturasLocal[$index]);
                break;
            }
        }
        $this->listar_vehiculos_lo();
    }

    public function listar_vehiculos_lo(){

        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_local($this->pesoTotal, $this->volumenTotal,1,$this->id_transportistas);
        if (count($this->vehiculosSugeridos) <= 0){
            $this->tarifaMontoSeleccionado = null;
            $this->selectedVehiculo = null;
            $this->id_tarifario_seleccionado = null;
        }
    }

    public function actualizarVehiculosSugeridos(){
        $this->listar_vehiculos_lo();
        $this->tarifaMontoSeleccionado = null;
        $this->selectedVehiculo = null;
        $this->id_tarifario_seleccionado = null;
    }

    public function seleccionarVehiculo($vehiculoId,$id_tarifa){
        $vehiculo = collect($this->vehiculosSugeridos)->first(function ($vehiculo) use ($vehiculoId, $id_tarifa) {
            return $vehiculo->id_vehiculo == $vehiculoId && $vehiculo->id_tarifario == $id_tarifa;
        });
//        $vehiculo = collect($this->vehiculosSugeridos)->firstWhere('id_vehiculo', $vehiculoId);
        if ($vehiculo) {
            // Actualiza el monto de la tarifa del vehículo seleccionado
            $this->tarifaMontoSeleccionado = $vehiculo->tarifa_monto;
            $this->id_tarifario_seleccionado = $id_tarifa;
            $this->selectedVehiculo = $vehiculoId;
        }
    }

    public function modal_por_vehiculo($id_ve){
        $this->detalle_vehiculo =  $this->vehiculo->listar_informacion_vehiculo($id_ve);
    }

    public function abrirModalComprobantes($cliente){
        if (isset($this->selectedFacturasProvincial[$cliente])) {
            $this->comprobantesSeleccionados = $this->selectedFacturasProvincial[$cliente];
        }
    }

    public function guardarDespachos(){
        try {
            $this->validate([
                'id_tipo_servicios' => 'nullable|integer',
                'id_transportistas' => 'required|integer',
                'selectedVehiculo' => 'required|integer',
                'despacho_peso' => 'nullable|numeric',
                'despacho_volumen' => 'nullable|numeric',
                'despacho_flete' => 'nullable|numeric',
                'despacho_ayudante' => 'nullable|regex:/^[0-9]+(\.[0-9]+)?$/',
                'despacho_gasto_otros' => 'nullable|regex:/^[0-9]+(\.[0-9]+)?$/',
            ], [
                'selectedVehiculo.required' => 'Debes seleccionar un vehículo.',
                'selectedVehiculo.integer' => 'El vehículo debe ser un número entero.',

                'id_transportistas.required' => 'Debes seleccionar un transportista.',
                'id_transportistas.integer' => 'El transportista debe ser un número entero.',

                'despacho_ayudante.regex' => 'El ayudante debe ser un número válido.',
                'despacho_gasto_otros.regex' => 'El gasto en otros debe ser un número válido.',
            ]);
            DB::beginTransaction();

            $duplicados = 0;

            // Validar facturas provinciales
            foreach ($this->selectedFacturasProvincial ?? [] as $cliente => $facturas) {
                foreach ($facturas as $factura) {
                    if (!isset($factura['CFTD'], $factura['CFNUMSER'], $factura['CFNUMDOC'])) {
                        session()->flash('error', 'Error en los datos de las facturas provinciales.');
                        DB::rollBack();
                        return;
                    }
                }
            }
            // Validar facturas locales
            foreach ($this->selectedFacturasLocal ?? [] as $comprobanteId => $factura) {
                if (!isset($factura['CFTD'], $factura['CFNUMSER'], $factura['CFNUMDOC'])) {
                    session()->flash('error', 'Error en los datos de las facturas locales.');
                    DB::rollBack();
                    return;
                }
                $existe = DB::table('despacho_ventas')
                    ->where('despacho_venta_cftd', $factura['CFTD'])
                    ->where('despacho_venta_cfnumser', $factura['CFNUMSER'])
                    ->where('despacho_venta_cfnumdoc', $factura['CFNUMDOC'])
                    ->exists();
                if ($existe) {
                    $duplicados++;
                }
            }
            if ($duplicados > 0) {
                session()->flash('error', "Se encontraron comprobantes duplicados.");
                DB::rollBack();
                return;
            }
            // Verificar si hay al menos un comprobante provincial seleccionado
            $comprobantesProvinciales = $this->selectedFacturasProvincial;

            if (empty($comprobantesProvinciales)) {
                session()->flash('error', 'Debe seleccionar al menos un comprobante provincial.');
                return;
            }
            // Guardar en la tabla Programaciones
            $programacion = new Programacion();
            $programacion->id_users = Auth::id();
            $programacion->programacion_fecha = $this->programacion_fecha;
            $programacion->programacion_estado = 1;
            $programacion->programacion_microtime = microtime(true);
            if (!$programacion->save()) {
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar la programación.');
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
                    session()->flash('error', "Debe seleccionar un transportista para el cliente.");
                    return;
                }
                // Crear despacho para este cliente
                $despacho = new Despacho();
                $despacho->id_users = Auth::id();
                $despacho->id_programacion = $programacion->id_programacion;
                $despacho->id_transportistas = $this->transportistasPorCliente[$cliente];
                $despacho->id_tipo_servicios = 2;
                $despacho->id_vehiculo = $this->selectedVehiculo;
                $despacho->id_tarifario = $this->id_tarifario_seleccionado;
                $despacho->despacho_peso = array_sum(array_column($facturasCliente, 'total_kg'));
                $despacho->despacho_volumen = array_sum(array_column($facturasCliente, 'total_volumen'));
                $despacho->despacho_flete = $this->tarifaMontoSeleccionado;
                $despacho->despacho_ayudante = $this->despacho_ayudante ?: null;
                $despacho->despacho_gasto_otros = $this->despacho_gasto_otros ?: null;
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
                    $despachoVenta->id_venta = null;
                    $despachoVenta->despacho_venta_cftd = $factura['CFTD'];
                    $despachoVenta->despacho_venta_cfnumser = $factura['CFNUMSER'];
                    $despachoVenta->despacho_venta_cfnumdoc = $factura['CFNUMDOC'];
                    $despachoVenta->despacho_venta_factura = $factura['CFNUMSER'] . '-' . $factura['CFNUMDOC'];
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
            $despacho->id_transportistas = $this->id_transportistas;
            $despacho->id_tipo_servicios = 1;
            $despacho->id_vehiculo = $this->selectedVehiculo;
            $despacho->id_tarifario = $this->id_tarifario_seleccionado;
            $despacho->despacho_peso = array_sum(array_column($comprobantesLocales, 'total_kg'));
            $despacho->despacho_volumen = array_sum(array_column($comprobantesLocales, 'total_volumen'));
            $despacho->despacho_flete = $this->tarifaMontoSeleccionado;
            $despacho->despacho_ayudante = $this->despacho_ayudante ?: null;
            $despacho->despacho_gasto_otros = $this->despacho_gasto_otros ?: null;
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
                $despachoVenta->despacho_venta_cftd = $factura['CFTD'];
                $despachoVenta->despacho_venta_cfnumser = $factura['CFNUMSER'];
                $despachoVenta->despacho_venta_cfnumdoc = $factura['CFNUMDOC'];
                $despachoVenta->despacho_venta_factura = $factura['CFNUMSER'] . '-' . $factura['CFNUMDOC'];
                $despachoVenta->despacho_detalle_estado = 1;
                $despachoVenta->despacho_detalle_microtime = microtime(true);

                if (!$despachoVenta->save()) {
                    DB::rollBack();
                    session()->flash('error', "Error al guardar la factura local: {$factura['serie']}.");
                    return;
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

    public function reiniciar_campos(){
        $this->searchFacturaCliente = '';
        $this->filteredFacturasYClientes = [];
        $this->detalle_vehiculo = [];
        $this->selectedFacturas = [];
        $this->pesoTotal = 0;
        $this->volumenTotal = 0;
        $this->selectedFacturasLocal = [];
        $this->selectedFacturasProvincial = [];
        $this->transportistasPorCliente = [];
        $this->tarifaMontoSeleccionado = 0;
        $this->vehiculosSugeridos = [];
        $this->selectedVehiculo = '';
        $this->id_transportistas = '';
        $this->programacion_fecha = now()->format('Y-m-d');
        $this->despacho_ayudante = '';
        $this->despacho_gasto_otros = '';
        $this->id_tipo_servicios = null;
        $this->despacho_peso = null;
        $this->despacho_volumen = null;
        $this->despacho_flete = null;
        $this->id_tarifario = null;
        $this->id_tarifario_seleccionado = '';
    }


}
