<?php

namespace App\Livewire\Programacioncamiones;

use Livewire\Component;
use App\Models\Logs;
use App\Models\Server;
use App\Models\Transportista;
use App\Models\Vehiculo;


class Local extends Component
{
    private $logs;
    private $server;
    private $transportista;
    private $vehiculo;
    public function __construct(){
        $this->logs = new Logs();
        $this->server = new Server();
        $this->transportista = new Transportista();
        $this->vehiculo = new Vehiculo();
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
    public function mount(){
        $this->id_transportistas = null;
        $this->selectedVehiculo = null;
    }
    public $tarifaMontoSeleccionado = 0;

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
        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_new($this->pesoTotal, $this->volumenTotal,1,$this->id_transportistas);
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
        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_new($this->pesoTotal, $this->volumenTotal,1,$this->id_transportistas);
//        $this->buscar_comprobantes();
    }

    public function eliminarFacturaSeleccionada($CFTD, $CFNUMSER, $CFNUMDOC)
    {
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
            $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_new($this->pesoTotal, $this->volumenTotal,1,$this->id_transportistas);
        }
    }

    public function modal_por_vehiculo($id_ve){
        $this->detalle_vehiculo =  $this->vehiculo->listar_informacion_vehiculo($id_ve);
    }

//    public function compararPesoConVehiculos($listar_vehiculos)
//    {
//        $this->vehiculosSugeridos = [];
//
//        // Si no hay transportista seleccionado, no aplicar filtro
//        if (!empty($this->id_transportistas)) {
//            // Filtrar vehículos según el transportista seleccionado
//            $listar_vehiculos = $listar_vehiculos->filter(function ($vehiculo) {
//                return $vehiculo->id_transportistas == $this->id_transportistas;
//            });
//        }
//
//        foreach ($listar_vehiculos as $vehiculo) {
//            // Verificar que el peso del vehículo esté dentro del rango permitido
//            $pesoEnRango = $vehiculo->vehiculo_capacidad_peso >= $this->pesoTotal;
//
//            if (!empty($vehiculo->tarifa_cap_min) && !empty($vehiculo->tarifa_cap_max)) {
//                $pesoEnRango = $pesoEnRango &&
//                    $this->pesoTotal >= $vehiculo->tarifa_cap_min &&
//                    $this->pesoTotal <= $vehiculo->tarifa_cap_max;
//            }
//
//            if ($pesoEnRango) {
//                // Calcular el porcentaje de capacidad utilizada
//                $vehiculo->vehiculo_capacidad_usada = ($this->pesoTotal / $vehiculo->vehiculo_capacidad_peso) * 100;
//
//                // Evitar duplicados en la lista de sugerencias
//                if (!in_array($vehiculo->id_vehiculo, array_column($this->vehiculosSugeridos, 'id_vehiculo'))) {
//                    $this->vehiculosSugeridos[] = $vehiculo;
//                }
//            }
//        }
//
//        // Ordenar vehículos sugeridos por estado de aprobación y porcentaje de capacidad usada
//        usort($this->vehiculosSugeridos, function ($a, $b) {
//            return [$b->tarifa_estado_aprobacion, $b->vehiculo_capacidad_usada] <=> [$a->tarifa_estado_aprobacion, $a->vehiculo_capacidad_usada];
//        });
//    }




//    public function actualizarVehiculosSugeridos()
//    {
//        // Verificar si hay transportista seleccionado
//        if ($this->id_transportistas) {
//            // Obtener vehículos relacionados con sus tarifarios
//            $listar_vehiculos = $this->vehiculo->obtener_vehiculos_con_tarifarios();
//            $this->compararPesoConVehiculos($listar_vehiculos);
//        } else {
//            // Si no hay transportista seleccionado, limpiar sugerencias
//            $this->vehiculosSugeridos = [];
//        }
//    }







}
