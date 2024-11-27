<?php

namespace App\Livewire\Programacioncamiones;
use App\Models\Tarifario;
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
use App\Models\Departamento;

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
    private $departamento;
    private $tarifario;

    public function __construct()
    {
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
        $this->server = new Server();
        $this->transportista = new Transportista();
        $this->vehiculo = new Vehiculo();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->despachoventa = new DespachoVenta();
        $this->departamento = new Departamento();
        $this->tarifario = new Tarifario();
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
    public $id_transportistas = [];
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
    public $tarifariosSugeridos = [];
    public $provincias = [];
    public $distritos = [];
    public $id_provincia = '';
    public $id_distrito = '';
    public $id_departamento = "";
    public $showBotonListo = "";


    public function mount()
    {
        $this->id_transportistas = null;
        $this->selectedVehiculo = null;
        $this->showBotonListo = null;
        $this->programacion_fecha = now()->format('Y-m-d');
    }

    public function render()
    {
        $tipo_servicio_local_provincial = $this->tiposervicio->listar_tipo_servicio_local_provincial();
        $listar_transportistas = $this->transportista->listar_transportista_sin_id();
        $listar_departamento = $this->departamento->lista_departamento();
        return view('livewire.programacioncamiones.mixto', compact('tipo_servicio_local_provincial', 'listar_transportistas', 'listar_transportistas', 'listar_departamento'));
    }

//    PARA EL MODAL DE PROVINCIA
    public $clienteSeleccionado;
    public $clienteindex;
    public $datosSeleccionadosPorCliente = [];
    public $selectedTarifario = "";
    /* CÓDIGO EDER */
    public $clientes_provinciales = [];

    // VALORES PARA EL MODAL DE PROVINCIAL
    public $id_trans = "";
    public $id_tari = "";
    public $otros_gastos = "";
    public $mano_obra = "";
    public $depar = "";
    public $provin = "";
    public $distri = "";
    public $toKg = "";
    public $toVol = "";
    public $arrayDepartamentoPronvicial = [];
    public $arrayProvinciaPronvicial = [];
    public $arrayDistritoPronvicial = [];
    public $detalle_tarifario = "";

    public function abrirModalComprobantes($cliente,$index){ // en $cliente llega el código del cliente, DNI o RUC
        /* limpiamos primero el array */
        $this->clienteSeleccionado = "";
        $this->clienteindex = "";
        /* Añadimos valor */
        $this->clienteSeleccionado = $cliente;
        $this->clienteindex = $index;
        $datosCliente = $this->clientes_provinciales[$index] ?? null;
        $this->id_trans = $datosCliente['id_transportista'] ?? null;
        $this->id_tari =  null;
        $this->otros_gastos = $datosCliente['otros'] ?? null;
        $this->mano_obra = $datosCliente['mano_obra'] ?? null;
        $this->depar = $datosCliente['departamento'] ?? null;
        $this->provin = $datosCliente['provincia'] ?? null;
        $this->distri = $datosCliente['distrito'] ?? null;
        $this->toKg = 0;
        $this->toVol = 0;
        if (!$this->depar) {
            $this->arrayProvinciaPronvicial = [];
            $this->provin = null;
            $this->arrayDistritoPronvicial = [];
            $this->distri = null;
        } else {
            $this->listar_provincias();
            if (!$this->provin) {
                $this->arrayDistritoPronvicial = [];
                $this->distri = null;
            } else {
                $this->listar_distritos();
            }
        }
        // Cargar comprobantes seleccionados
        $this->comprobantesSeleccionados = $datosCliente['comprobantes'] ?? [];
        $this->clientes_provinciales[$index]['total_kg'] = 0;
        $this->clientes_provinciales[$index]['total_volumen'] = 0;
        $this->clientes_provinciales[$index]['id_tarifario'] = null;
        foreach ($this->comprobantesSeleccionados as $com){
            $this->clientes_provinciales[$index]['total_kg'] += $com['total_kg'];
            $this->clientes_provinciales[$index]['total_volumen'] += $com['total_volumen'];
            $this->toKg += $com['total_kg'];
            $this->toVol += $com['total_volumen'];
        }
        $this->listar_tarifarios_su($index);
        $this->activar_botonListo($index);
    }
    public function listar_tarifarios_su($index){
        $datosCliente = $this->clientes_provinciales[$index] ?? null;
        if ($datosCliente){
            $this->tarifariosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_provincial($datosCliente['total_kg'],2,$datosCliente['id_transportista'],$datosCliente['departamento'] ,$datosCliente['provincia'] ,$datosCliente['distrito']);
            if (count($this->tarifariosSugeridos) <= 0){
                $this->tarifaMontoSeleccionado = null;
                $this->selectedTarifario = null;
            }
        }
    }
    public function save_cliente_data($index){
        $this->clientes_provinciales[$index]['id_transportista'] = $this->id_trans;
        $this->clientes_provinciales[$index]['id_tarifario'] = $this->id_tari;
        $this->clientes_provinciales[$index]['otros'] = $this->otros_gastos;
        $this->clientes_provinciales[$index]['mano_obra'] = $this->mano_obra;
        $this->clientes_provinciales[$index]['departamento'] = $this->depar;
        $this->clientes_provinciales[$index]['provincia'] = $this->provin;
        $this->clientes_provinciales[$index]['distrito'] = $this->distri;
        $this->listar_tarifarios_su($index);
        $this->activar_botonListo($index);
    }
    function activar_botonListo($index){
        $informacionCliente = $this->clientes_provinciales[$index];
        if ($informacionCliente['id_transportista'] && $informacionCliente['id_tarifario'] && $informacionCliente['departamento'] && $informacionCliente['provincia']){
            $this->showBotonListo = true;
            $this->clientes_provinciales[$index]['listo'] = true;
        }else{
            $this->clientes_provinciales[$index]['listo'] = false;
            $this->showBotonListo = false;
        }
    }
    public function modal_detalle_tarifario($id){
        $this->detalle_tarifario =  $this->tarifario->listar_informacion_tarifa($id);
    }

    public function seleccionarTarifario($id){
        $tarifario = collect($this->tarifariosSugeridos)->first(function ($tarifario) use ($id){
            return $tarifario->id_tarifario == $id;
        });
        if ($tarifario) {
            // Actualiza el monto de la tarifa del vehículo seleccionado
            $this->tarifaMontoSeleccionado = $tarifario->tarifa_monto;
            $this->selectedTarifario = $id;
        }
    }

    public function guardarDatos(){
        if ($this->clienteSeleccionado) {
            $this->datosSeleccionadosPorCliente[$this->clienteSeleccionado] = [
                'id_transportista' => $this->id_transportistas,
                'despacho_gasto_otros' => $this->despacho_gasto_otros,
                'despacho_ayudante' => $this->despacho_ayudante,
                'id_departamento' => $this->id_departamento,
                'id_provincia' => $this->id_provincia,
                'id_distrito' => $this->id_distrito,
                'selectedTarifario' => $this->selectedTarifario,
                'tarifaMontoSeleccionado' => $this->tarifaMontoSeleccionado,
            ];
        }
        $this->dispatch('hideModal');
    }

    public function deparTari(){
        $this->id_tari = "";
        $this->save_cliente_data($this->clienteindex);
        $this->listar_provincias();
    }
    public function proviTari(){
        $this->id_tari = "";
        $this->save_cliente_data($this->clienteindex);
        $this->listar_distritos();
    }
    public function distriTari(){
        $this->id_tari = "";
        $this->save_cliente_data($this->clienteindex);

    }
    public function listar_provincias(){
        $valor = $this->depar;
        if ($valor) {
            $this->arrayProvinciaPronvicial = DB::table('provincias')->where('id_departamento', '=', $valor)->get();
        } else {
            $this->arrayProvinciaPronvicial = [];
            $this->provin = '';
            $this->arrayDistritoPronvicial = [];
            $this->distri = '';
        }
    }

    public function listar_distritos(){
        $valor = $this->provin;
        if ($valor) {
            $this->arrayDistritoPronvicial = DB::table('distritos')->where('id_provincia', '=', $valor)->get();
        } else {
            $this->arrayDistritoPronvicial = [];
            $this->distri = '';
        }
    }
//
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
            'CFTD' => $CFTD, // tipo de comprobantes
            'CFNUMSER' => $CFNUMSER, // serie del comprobante
            'CFNUMDOC' => $CFNUMDOC, // numero del comprobante
            'total_kg' => $factura->total_kg,
            'total_volumen' => $factura->total_volumen,
            'CNOMCLI' => $factura->CNOMCLI, // Nombre cliente
            'CCODCLI' => $factura->CCODCLI, // Código del cliente
            'CFIMPORTE' => $factura->CFIMPORTE, // importe
            'CFCODMON' => $factura->CFCODMON, // código de moneda
            'guia' => $factura->CFTEXGUIA, // guia
            'direccion_guia' => $factura->guia ? $factura->guia->LLEGADADIRECCION : '-', // dirección de guía.
            'fecha_guia' => $factura->guia ? $factura->guia->GREFECEMISION : '-', // fecha de la guía.
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

    public $clienteseleccionado = [];
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
        $codiCli = $factura['CCODCLI'];
        $nombCli = $factura['CNOMCLI'];

        $validarExisteCliente =  collect($this->clientes_provinciales)->first(function ($cliente) use ($codiCli,$nombCli) {
            return $cliente['codigoCliente'] === $codiCli
                && $cliente['nombreCliente'] === $nombCli;
        });
        if ($validarExisteCliente){ // si existe el cliente
            // Verificar si el comprobante ya existe en los comprobantes del cliente
            $existeComprobante = collect($validarExisteCliente['comprobantes'] ?? [])->contains(function ($comprobante) use ($factura) {
                return $comprobante['CFTD'] === $factura['CFTD'] &&
                    $comprobante['CFNUMSER'] === $factura['CFNUMSER'] &&
                    $comprobante['CFNUMDOC'] === $factura['CFNUMDOC'];
            });
            if ($existeComprobante) {
                session()->flash('error', 'El comprobante ya está duplicado para este cliente.');
                return;
            }
            // Agregar el comprobante al cliente existente
            foreach ($this->clientes_provinciales as &$cliente) {
                if ($cliente['codigoCliente'] == $codiCli && $cliente['nombreCliente'] == $nombCli) {
                    $cliente['comprobantes'][] = [
                        'CFTD' => $factura['CFTD'],
                        'CFNUMSER' => $factura['CFNUMSER'],
                        'CFNUMDOC' => $factura['CFNUMDOC'],
                        'total_kg' => $factura['total_kg'],
                        'total_volumen' => $factura['total_volumen'],
                        'CFIMPORTE' => $factura['CFIMPORTE'],
                        'CFCODMON' => $factura['CFCODMON'],
                        'guia' => $factura['guia'],
                        'direccion_guia' => $factura['direccion_guia'] , // dirección de guía.
                        'fecha_guia' => $factura['fecha_guia'] , // fecha de la guía.
                    ];
                    break;
                }
            }
        }else{ // ingresar cliente nuevo
            $this->clientes_provinciales[] = [
                'codigoCliente' =>  $factura['CCODCLI'],
                'nombreCliente' =>  $factura['CNOMCLI'],
                'total_kg' =>  0,
                'total_volumen' =>  0,
                'id_transportista' =>  null,
                'id_tarifario' =>  null,
                'otros' =>  null,
                'mano_obra' =>  null,
                'departamento' =>  null,
                'provincia' =>  null,
                'distrito' =>  null,
                'listo' =>  null,
                'comprobantes' => [
                    [
                        'CFTD' => $factura['CFTD'],
                        'CFNUMSER' => $factura['CFNUMSER'],
                        'CFNUMDOC' => $factura['CFNUMDOC'],
                        'total_kg' => $factura['total_kg'],
                        'total_volumen' => $factura['total_volumen'],
                        'CFIMPORTE' => $factura['CFIMPORTE'],
                        'CFCODMON' => $factura['CFCODMON'],
                        'guia' => $factura['guia'],
                        'direccion_guia' => $factura['direccion_guia'] , // dirección de guía.
                        'fecha_guia' => $factura['fecha_guia'] , // fecha de la guía.
                    ]
                ]
            ];
        }
    }
    public function eliminarFacturaProvincial($CFTD, $CFNUMSER, $CFNUMDOC)
    {
        foreach ($this->clientes_provinciales as $index => &$cliente) {
            // Filtrar comprobantes del cliente actual
            $cliente['comprobantes'] = collect($cliente['comprobantes'])
                ->filter(function ($comprobante) use ($CFTD, $CFNUMSER, $CFNUMDOC) {
                    return !($comprobante['CFTD'] === $CFTD &&
                        $comprobante['CFNUMSER'] === $CFNUMSER &&
                        $comprobante['CFNUMDOC'] === $CFNUMDOC);
                })
                ->values()
                ->toArray();

            // Si el cliente no tiene comprobantes restantes, eliminarlo del array
            if (empty($cliente['comprobantes'])) {
                unset($this->clientes_provinciales[$index]);
            }
        }

        // Actualizar el estado del checkbox en la tabla `selectedFacturasLocal`
        foreach ($this->selectedFacturasLocal as &$factura) {
            if ($factura['CFTD'] === $CFTD &&
                $factura['CFNUMSER'] === $CFNUMSER &&
                $factura['CFNUMDOC'] === $CFNUMDOC) {
                $factura['isChecked'] = false;
                break;
            }
        }

        // Reindexar el array `clientes_provinciales` después de eliminar elementos
        $this->clientes_provinciales = array_values($this->clientes_provinciales);
    }


    public function eliminarFacturaSeleccionada($CFTD, $CFNUMSER, $CFNUMDOC){
        $this->eliminarFacturaProvincial($CFTD,$CFNUMSER,$CFNUMDOC);

        foreach ($this->selectedFacturasLocal as $index => $factura) {
            if ($factura['CFTD'] == $CFTD && $factura['CFNUMSER'] == $CFNUMSER && $factura['CFNUMDOC'] == $CFNUMDOC) {
                $this->pesoTotal -= $factura['total_kg'];
                $this->volumenTotal -= $factura['total_volumen'];
                unset($this->selectedFacturasLocal[$index]);
                break;
            }
        }
        // Reindexar el array `selectedFacturasLocal` después de eliminar elementos
        $this->selectedFacturasLocal = array_values($this->selectedFacturasLocal);
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
