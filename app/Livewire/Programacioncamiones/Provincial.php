<?php

namespace App\Livewire\Programacioncamiones;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Logs;
use App\Models\Server;
use App\Models\Tarifario;
use App\Models\Transportista;
use App\Models\Departamento;
use App\Models\Vehiculo;
use App\Models\Programacion;
use App\Models\Despacho;
use App\Models\DespachoVenta;
use Livewire\Component;

class Provincial extends Component
{
    private $logs;
    private $server;
    private $transportista;
    private $departamento;
    private $vehiculo;
    private $tarifario;
    private $programacion;
    private $despacho;
    private $despachoventa;
    public function __construct(){
        $this->logs = new Logs();
        $this->server = new Server();
        $this->transportista = new Transportista();
        $this->departamento = new Departamento();
        $this->vehiculo = new Vehiculo();
        $this->tarifario = new Tarifario();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->despachoventa = new DespachoVenta();
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
    public $tarifariosSugeridos = [];
    public $selectedTarifario = "";
    public $id_departamento = "";
    public $id_provincia = "";
    public $id_distrito = "";
    public $selectedFacturas = [];
    public $detalle_tarifario = [];
    public $comprobantes = [];
    public $select_nombre_cliente = null;
    public $searchComprobante = '';
    public $filteredComprobantes = [];
    public $programacion_fecha = '';
    public $despacho_ayudante = '';
    public $despacho_gasto_otros = '';
    public $id_tipo_servicios;
    public $despacho_peso;
    public $despacho_volumen;
    public $despacho_flete;
    public $id_tarifario;
    public $costoTotal = 0;
    public function mount(){
        $this->selectedCliente = null;
        $this->selectedTarifario = null;
        $this->id_transportistas = null;
        $this->id_departamento = null;
        $this->id_provincia = null;
        $this->id_distrito = null;
        $this->programacion_fecha = now()->format('Y-m-d');
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
        $this->tarifariosSugeridos = [];
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
        $this->listar_tarifarios_su();
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
            'guia' => $factura->CFTEXGUIA,
        ];
        $this->pesoTotal += $factura->total_kg;
        $this->volumenTotal += $factura->total_volumen;

        // Eliminar la factura de la lista de facturas filtradas
        $this->filteredComprobantes = $this->filteredComprobantes->filter(function ($f) use ($CFNUMDOC) {
            return $f->CFNUMDOC !== $CFNUMDOC;
        });
        // Actualizar lista de vehículos sugeridos
        $this->listar_tarifarios_su();
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
            $this->listar_tarifarios_su();
        }
    }

    public function modal_detalle_tarifario($id){
        $this->detalle_tarifario =  $this->tarifario->listar_informacion_tarifa($id);
    }
    public function deparTari(){
        $this->listar_tarifarios_su();
        $this->listar_provincias();
    }
    public function proviTari(){
        $this->listar_tarifarios_su();
        $this->listar_distritos();
    }
    public function distriTari(){
        $this->listar_tarifarios_su();
    }

    public function actualizarVehiculosSugeridos(){
        $this->listar_tarifarios_su();
        $this->tarifaMontoSeleccionado = null;
        $this->selectedTarifario = null;
    }

    public function seleccionarTarifario($id){
        $vehiculo = collect($this->tarifariosSugeridos)->first(function ($vehiculo) use ($id){
            return $vehiculo->id_tarifario == $id;
        });
        if ($vehiculo) {
            // Actualiza el monto de la tarifa del vehículo seleccionado
            $this->tarifaMontoSeleccionado = $vehiculo->tarifa_monto;
            $this->selectedTarifario = $id;
            $this->calcularCostoTotal();
        }
    }
    public function listar_tarifarios_su(){
        $this->tarifariosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_provincial($this->pesoTotal,2,$this->id_transportistas,$this->id_departamento ,$this->id_provincia ,$this->id_distrito);
        if (count($this->tarifariosSugeridos) <= 0){
            $this->tarifaMontoSeleccionado = null;
            $this->selectedTarifario = null;
        }
    }

    public function calcularCostoTotal(){
        $montoSeleccionado = floatval($this->tarifaMontoSeleccionado);
        $ayudante = floatval($this->despacho_ayudante);
        $otros = floatval($this->despacho_gasto_otros);
        $peso = floatval($this->pesoTotal);

        $this->costoTotal = ($montoSeleccionado * $peso) + $ayudante + $otros;
    }

    public function guardarDespachos(){
        try {
            $this->validate([
                'id_tipo_servicios' => 'nullable|integer',
                'id_transportistas' => 'required|integer',
                'selectedTarifario' => 'required|integer',
                'selectedFacturas' => 'required|array|min:1',
                'id_departamento' => 'required|integer',
                'id_provincia' => 'required|integer',
                'id_distrito' => 'nullable|integer',
                'despacho_peso' => 'nullable|numeric',
                'despacho_volumen' => 'nullable|numeric',
                'despacho_flete' => 'nullable|numeric',
                'despacho_ayudante' => 'nullable|regex:/^[0-9]+(\.[0-9]+)?$/',
                'despacho_gasto_otros' => 'nullable|regex:/^[0-9]+(\.[0-9]+)?$/',
            ], [
                'selectedTarifario.required' => 'Debes seleccionar una tarifa.',
                'selectedTarifario.integer' => 'La tarifa debe ser un número entero.',

                'id_transportistas.required' => 'Debes seleccionar un transportista.',
                'id_transportistas.integer' => 'El transportista debe ser un número entero.',

                'selectedFacturas.required' => 'Debes seleccionar al menos un comprobante.',
                'selectedFacturas.array' => 'Los comprobantes deben ser un arreglo.',
                'selectedFacturas.min' => 'Debes seleccionar al menos un comprobante.',

                'id_departamento.required' => 'Debes seleccionar un departamento.',
                'id_departamento.integer' => 'El departamento debe ser un número entero.',

                'id_provincia.required' => 'Debes seleccionar una provincia.',
                'id_provincia.integer' => 'La provincia debe ser un número entero.',

                'despacho_ayudante.regex' => 'El ayudante debe ser un número válido.',
                'despacho_gasto_otros.regex' => 'El gasto en otros debe ser un número válido.',
            ]);
            $contadorError = 0;
            DB::beginTransaction();
            // Validar duplicidad para las facturas seleccionadas
            foreach ($this->selectedFacturas as $factura) {
                $existe = DB::table('despacho_ventas')
                    ->where('despacho_venta_cftd', $factura['CFTD'])
                    ->where('despacho_venta_cfnumser', $factura['CFNUMSER'])
                    ->where('despacho_venta_cfnumdoc', $factura['CFNUMDOC'])
                    ->exists();
                if ($existe) {
                    $contadorError++;
                }
            }
            if ($contadorError > 0) {
                session()->flash('error', "Se encontraron comprobantes duplicadas. Por favor, verifica.");
                DB::rollBack();
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
            // Guardar el despacho
            $despacho = new Despacho();
            $despacho->id_users = Auth::id();
            $despacho->id_programacion = $programacion->id_programacion;
            $despacho->id_transportistas = $this->id_transportistas;
            $despacho->id_tipo_servicios = 2;
            $despacho->id_tarifario = $this->selectedTarifario;
            $despacho->id_departamento = $this->id_departamento;
            $despacho->id_provincia = $this->id_provincia;
            $despacho->id_distrito = $this->id_distrito ?: null;
            $despacho->despacho_peso = $this->pesoTotal;
            $despacho->despacho_volumen = $this->volumenTotal;
            $despacho->despacho_flete = $this->tarifaMontoSeleccionado * $this->pesoTotal;
            $despacho->despacho_ayudante = $this->despacho_ayudante ?: null;
            $despacho->despacho_gasto_otros = $this->despacho_gasto_otros ?: null;
            // Calcular despacho_costo_total
            $despacho_costo_total = $despacho->despacho_flete;
            if (!empty($this->despacho_ayudante)) {
                $despacho_costo_total += $this->despacho_ayudante;
            }
            if (!empty($this->despacho_gasto_otros)) {
                $despacho_costo_total += $this->despacho_gasto_otros;
            }
            $despacho->despacho_costo_total = $despacho_costo_total;
            $despacho->despacho_estado = 1;
            $despacho->despacho_microtime = microtime(true);
            if (!$despacho->save()) {
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar el registro.');
                return;
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

    public function reiniciar_campos()
    {
        $this->searchCliente = "";
        $this->filteredClientes = [];
        $this->selectedCliente = null;
        $this->id_transportistas = "";
        $this->provincias = [];
        $this->distritos = [];
        $this->pesoTotal = 0;
        $this->volumenTotal = 0;
        $this->tarifaMontoSeleccionado = 0;
        $this->tarifariosSugeridos = [];
        $this->selectedTarifario = "";
        $this->id_departamento = "";
        $this->id_provincia = "";
        $this->id_distrito = "";
        $this->selectedFacturas = [];
        $this->detalle_tarifario = [];
        $this->comprobantes = [];
        $this->select_nombre_cliente = null;
        $this->searchComprobante = '';
        $this->filteredComprobantes = [];
        $this->programacion_fecha = now()->format('Y-m-d');
        $this->despacho_ayudante = '';
        $this->despacho_gasto_otros = '';
        $this->id_tipo_servicios = null;
        $this->despacho_peso = null;
        $this->despacho_volumen = null;
        $this->despacho_flete = null;
        $this->id_tarifario = null;
        $this->costoTotal = 0;
    }


}
