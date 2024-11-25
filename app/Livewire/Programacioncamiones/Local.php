<?php

namespace App\Livewire\Programacioncamiones;

use Livewire\Component;
use App\Models\Logs;
use App\Models\Server;
use App\Models\Transportista;
use App\Models\Vehiculo;
use App\Models\Programacion;
use App\Models\Despacho;
use App\Models\DespachoVenta;


class Local extends Component
{
    private $logs;
    private $server;
    private $transportista;
    private $vehiculo;
    private $programacion;
    private $despacho;
    private $despachoventa;
    public function __construct(){
        $this->logs = new Logs();
        $this->server = new Server();
        $this->transportista = new Transportista();
        $this->vehiculo = new Vehiculo();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->despachoventa = new DespachoVenta();
    }
    public $searchFactura = "";
    public $filteredFacturas = [];
    public $id_transportistas = "";
    public $vehiculosSugeridos = [];
    public $selectedVehiculo = "";
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $selectedFacturas = [];
    public $detalle_vehiculo = [];
    public $tarifaMontoSeleccionado = 0;
    public $programacion_fecha = '';
    public $despacho_ayudante = '';
    public $despacho_gasto_otros = '';
    public function mount(){
        $this->id_transportistas = null;
        $this->selectedVehiculo = null;
        $this->programacion_fecha = now()->format('Y-m-d');
    }

    public function render(){
        $listar_transportistas = $this->transportista->listar_transportista_sin_id();
        $listar_vehiculos = $this->vehiculo->obtener_vehiculos_con_tarifarios();
        return view('livewire.programacioncamiones.local', compact('listar_transportistas', 'listar_vehiculos'));
    }

    public function buscar_comprobantes(){
        if ($this->searchFactura !== "") {
            $this->filteredFacturas = $this->server->listar_comprobantes_listos_local($this->searchFactura);
            if (!$this->filteredFacturas || count($this->filteredFacturas) == 0) {
                $this->filteredFacturas = [];
            }
        } else {
            $this->filteredFacturas = [];
        }
    }
    public function actualizarVehiculosSugeridos(){
        $this->listar_vehiculos_lo();
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

    public function seleccionarFactura($CFTD, $CFNUMSER, $CFNUMDOC){
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
        $factura = $this->filteredFacturas->first(function ($f) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
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
            'CNOMCLI' => $factura->CNOMCLI,
            'CFIMPORTE' => $factura->CFIMPORTE,
            'CFCODMON' => $factura->CFCODMON,
        ];
        $this->pesoTotal += $factura->total_kg;
        $this->volumenTotal += $factura->total_volumen;

        // Eliminar la factura de la lista de facturas filtradas
        $this->filteredFacturas = $this->filteredFacturas->filter(function ($f) use ($CFNUMDOC) {
            return $f->CFNUMDOC !== $CFNUMDOC;
        });
        // Actualizar lista de vehículos sugeridos
        $this->listar_vehiculos_lo();
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
            $this->listar_vehiculos_lo();
        }
    }

    public function modal_por_vehiculo($id_ve){
        $this->detalle_vehiculo =  $this->vehiculo->listar_informacion_vehiculo($id_ve);
    }

    public function listar_vehiculos_lo(){

        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_local($this->pesoTotal, $this->volumenTotal,1,$this->id_transportistas);
        if (count($this->vehiculosSugeridos) <= 0){
            $this->tarifaMontoSeleccionado = null;
            $this->selectedVehiculo = null;
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
                'despacho_ayudante' => 'nullable|numeric',
                'despacho_gasto_otros' => 'nullable|numeric',
            ], [
                'selectedVehiculo.required' => 'Debes seleccionar un vehículo.',
                'selectedVehiculo.integer' => 'El vehículo debe ser un número entero.',

                'id_transportistas.required' => 'Debes seleccionar un transportista.',
                'id_transportistas.integer' => 'El transportista debe ser un número entero.',
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
            // Tipo de servicio 1
            $despacho = new Despacho();
            $despacho->id_users = Auth::id();
            $despacho->id_programacion = $programacion->id_programacion;
            $despacho->id_transportistas = $this->id_transportistas;
            $despacho->id_tipo_servicios = 1;
            $despacho->id_vehiculo = $this->selectedVehiculo;
            $despacho->despacho_peso = $this->pesoTotal;
            $despacho->despacho_volumen = $this->volumenTotal;
            $despacho->despacho_flete = $this->tarifaMontoSeleccionado;
            $despacho->despacho_ayudante = $this->despacho_ayudante ?: null;
            $despacho->despacho_gasto_otros = $this->despacho_gasto_otros ?: null;
            // Calcular despacho_costo_total
            $despacho_costo_total = $this->tarifaMontoSeleccionado;
            if (!empty($this->despacho_ayudante)) {
                $despacho_costo_total += $this->despacho_ayudante;
            }
            if (!empty($this->despacho_gasto_otros)) {
                $despacho_costo_total += $this->despacho_gasto_otros;
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
                $despachoVenta->despacho_venta_cftd = $factura['CFTD'];
                $despachoVenta->despacho_venta_cfnumser = $factura['CFNUMSER'];
                $despachoVenta->despacho_venta_cfnumdoc = $factura['CFNUMDOC'];
                $despachoVenta->despacho_venta_factura = $factura['CFNUMSER'] . '-' . $factura['CFNUMDOC'];
                $despachoVenta->despacho_detalle_estado = 1;
                $despachoVenta->despacho_detalle_microtime = microtime(true);
                if (!$despachoVenta->save()) {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar el registro.');
                }
            }
            DB::commit();
            session()->flash('success', 'Registro guardado correctamente.');
//            $this->reiniciar_campos();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error inesperado. Por favor, inténtelo nuevamente.');
        }
    }



}
