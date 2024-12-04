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
use App\Models\General;

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
    private $general;

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
        $this->general = new General();
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
    public $desde;
    public $hasta;
    public $despacho_descripcion_otros = '';
    public $costoTotal = 0;
    public $importeTotalVenta = 0;
    public function mount()
    {
        $this->id_transportistas = null;
        $this->selectedVehiculo = null;
        $this->showBotonListo = null;
        $this->programacion_fecha = now()->format('Y-m-d');
        $this->desde = date('Y-m-d', strtotime('-1 month'));
        $this->hasta = date('Y-m-d');
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
    public $otros_gastos_descripcion_pro = "";
    public $montoSelect = "";
    public $montoSelectDescripcion = "";
    public $mano_obra = "";
    public $depar = "";
    public $provin = "";
    public $distri = "";
    public $toKg = "";
    public $imporTotalPro = "";
    public $despacho_descripcion_modificado = "";
    public $toVol = "";
    public $showCambiarPrecio = false;
    public $arrayDepartamentoPronvicial = [];
    public $arrayProvinciaPronvicial = [];
    public $arrayDistritoPronvicial = [];
    public $detalle_tarifario = [];
    public $opcionDetalle = false;
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
        $this->montoSelect =  null;
        $this->montoSelectDescripcion =  null;
        $this->showCambiarPrecio =  null;
        $this->opcionDetalle =  null;
        $this->detalle_tarifario =  [];
        $this->otros_gastos = $datosCliente['otros'] ?? null;
        $this->otros_gastos_descripcion_pro = $datosCliente['otrosDescripcion'] ?? null;
        $this->mano_obra = $datosCliente['mano_obra'] ?? null;
        /* -------------------------------- LIMPIAMOS EL UBIGEO ---------------------------------------*/
        $this->depar = null;
        $this->provin = null;
        $this->distri = null;
        $this->arrayProvinciaPronvicial = [];
        $this->arrayDistritoPronvicial = [];
        /* -------------------------------- LIMPIAMOS EL UBIGEO ---------------------------------------*/
        $this->toKg = 0;
        $this->imporTotalPro = 0;
        $this->toVol = 0;
        if ($datosCliente['ubiDepar']){
            $deparCl = DB::table('departamentos')->where('departamento_nombre','like','%'.$datosCliente['ubiDepar'].'%')->first();
            if ($deparCl){
                $this->depar = $deparCl->id_departamento;
                $this->clientes_provinciales[$index]['departamento'] = $deparCl->id_departamento;
                $this->listar_provincias();
                if ($datosCliente['ubiPro']){
                    $ta = trim($datosCliente['ubiPro']);
                    $provinCl = DB::table('provincias')
                        ->where('id_departamento','=',$deparCl->id_departamento)
                        ->where('provincia_nombre','like','%'.$ta.'%')->first();
                    if ($provinCl){
                        $this->provin = $provinCl->id_provincia;
                        $this->clientes_provinciales[$index]['provincia'] = $provinCl->id_provincia;
                        $this->listar_distritos();
                        if ($datosCliente['ubiDis']){
                            $ta2 = trim($datosCliente['ubiDis']);
                            $distriCl = DB::table('distritos')
                                ->where('id_provincia','=',$provinCl->id_provincia)
                                ->where('distrito_nombre','like','%'.$ta2.'%')->first();
                            if ($distriCl){
                                $this->distri = $distriCl->id_distrito;
                                $this->clientes_provinciales[$index]['distrito'] = $distriCl->id_distrito;
                            }
                        }
                    }
                }
            }
        }else{
            $this->depar = null;
            $this->clientes_provinciales[$index]['departamento'] = null;
            $this->clientes_provinciales[$index]['provincia'] = null;
            $this->clientes_provinciales[$index]['distrito'] = null;

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
        }

        // Cargar comprobantes seleccionados
        $this->comprobantesSeleccionados = $datosCliente['comprobantes'] ?? [];
        $this->clientes_provinciales[$index]['total_kg'] = 0;
        $this->clientes_provinciales[$index]['total_volumen'] = 0;
        $this->clientes_provinciales[$index]['id_tarifario'] = null;
        foreach ($this->comprobantesSeleccionados as $com){
            $this->clientes_provinciales[$index]['total_kg'] += $com['total_kg'];
            $this->clientes_provinciales[$index]['total_volumen'] += $com['total_volumen'];
            $this->imporTotalPro += round($com['CFIMPORTE'],2);
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
                $this->limpiarParaPro($index);
            }
        }
    }
    function limpiarParaPro($index){
        $this->clientes_provinciales[$index]['id_tarifario'] = '';
        $this->montoSelect = '';
        $this->selectedTarifario = '';
        $this->clientes_provinciales[$index]['montoSeleccionado'] = "";
        $this->clientes_provinciales[$index]['montoOriginal'] = "";
    }
    public function save_cliente_data($index){
        $this->clientes_provinciales[$index]['id_transportista'] = $this->id_trans;
        $this->clientes_provinciales[$index]['id_tarifario'] = $this->id_tari;
        $this->clientes_provinciales[$index]['otros'] = $this->otros_gastos;
        $this->clientes_provinciales[$index]['mano_obra'] = $this->mano_obra;
        $this->clientes_provinciales[$index]['departamento'] = $this->depar;
        $this->clientes_provinciales[$index]['provincia'] = $this->provin;
        $this->clientes_provinciales[$index]['distrito'] = $this->distri;
        if ($this->otros_gastos > 0){
            $this->clientes_provinciales[$index]['otrosDescripcion'] = $this->otros_gastos_descripcion_pro;
        }else{
            $this->clientes_provinciales[$index]['otrosDescripcion'] = "";
            $this->otros_gastos_descripcion_pro = '';
        }
        if ($this->id_tari){
            $pre = DB::table('tarifarios')->where('id_tarifario','=',$this->id_tari)->first();
            if ($pre){
                if (($this->clientes_provinciales[$index]['montoOriginal'] > 0 && $this->clientes_provinciales[$index]['montoSeleccionado'] > 0) && ($this->clientes_provinciales[$index]['montoOriginal'] != $this->montoSelect)){
                    $this->clientes_provinciales[$index]['montoSeleccionado'] = $this->montoSelect;
                }else{
                    $this->montoSelect = $pre->tarifa_monto;
                    $this->clientes_provinciales[$index]['montoSeleccionado'] = $pre->tarifa_monto;
                    $this->clientes_provinciales[$index]['montoOriginal'] = $pre->tarifa_monto;
                }
            }
        }else{
            $this->clientes_provinciales[$index]['montoSeleccionado'] = null;
            $this->clientes_provinciales[$index]['montoOriginal'] = null;
        }
        $this->clientes_provinciales[$index]['montoSeleccionadoDescripcion'] = $this->montoSelectDescripcion;
        $this->listar_tarifarios_su($index);
        $this->activar_botonListo($index);
    }
    public function modal_detalle_tarifario($id){
        if ($this->detalle_tarifario){
            if ($this->detalle_tarifario->id_tarifario == $id){
                $this->opcionDetalle = false;
                $this->detalle_tarifario = [];
            }else{
                $this->detalle_tarifario =  $this->tarifario->listar_informacion_tarifa($id);
            }
        }else{
            $this->opcionDetalle = true;
            $this->detalle_tarifario =  $this->tarifario->listar_informacion_tarifa($id);
        }

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
//        $this->id_tari = "";
        $this->save_cliente_data($this->clienteindex);
        $this->listar_provincias();
    }
    public function proviTari(){
//        $this->id_tari = "";
        $this->save_cliente_data($this->clienteindex);
        $this->listar_distritos();
    }
    public function distriTari(){
//        $this->id_tari = "";
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
            $this->filteredFacturasYClientes = $this->server->listar_comprobantes_listos_mixto($this->searchFacturaCliente, $this->desde, $this->hasta);
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
            'isChecked' => false,
            'GREFECEMISION' => $factura->GREFECEMISION, // fecha de emision de la guía
            'LLEGADADIRECCION' => $factura->LLEGADADIRECCION,// Dirección de destino
            'LLEGADAUBIGEO' => $factura->LLEGADAUBIGEO,// Código del ubigeo
            'DEPARTAMENTO' => $factura->DEPARTAMENTO,// Departamento
            'PROVINCIA' => $factura->PROVINCIA,// Provincia
            'DISTRITO' => $factura->DISTRITO,// Distrito
        ];
        $this->pesoTotal += $factura->total_kg;
        $this->volumenTotal += $factura->total_volumen;
        $importe = $this->general->formatoDecimal($factura->CFIMPORTE);
        $this->importeTotalVenta += floatval($importe);

        // Eliminar la factura de la lista de facturas filtradas
        $this->filteredFacturasYClientes = $this->filteredFacturasYClientes->filter(function ($f) use ($CFNUMDOC) {
            return $f->CFNUMDOC !== $CFNUMDOC;
        });
        // Actualizar lista de vehículos sugeridos
        $this->listar_vehiculos_lo();
        $vehiculoValido = collect($this->vehiculosSugeridos)->contains(function ($vehiculo) {
            return $vehiculo->id_vehiculo == $this->selectedVehiculo &&
                $vehiculo->id_tarifario == $this->id_tarifario_seleccionado;
        });

        if (!$vehiculoValido) {
            $this->tarifaMontoSeleccionado = null;
            $this->id_tarifario_seleccionado = null;
            $this->selectedVehiculo = null;
            $this->costoTotal = null;
        }
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
        $DEPARTAMENTO = $factura['DEPARTAMENTO'];
        $PROVINCIA = $factura['PROVINCIA'];
        $DISTRITO = $factura['DISTRITO'];
        $direccionLlegada = $factura['LLEGADADIRECCION'];

        $validarExisteCliente =  collect($this->clientes_provinciales)->first(function ($cliente) use ($codiCli,$nombCli,$DEPARTAMENTO,$PROVINCIA,$DISTRITO,$direccionLlegada) {
            return $cliente['codigoCliente'] === $codiCli
                && $cliente['nombreCliente'] === $nombCli
                && $cliente['ubiDepar'] == $DEPARTAMENTO
                && $cliente['ubiPro'] == $PROVINCIA
                && $cliente['ubiDis'] == $DISTRITO
                && $cliente['ubiDirc'] == $direccionLlegada;
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
                if ($cliente['codigoCliente'] == $codiCli && $cliente['nombreCliente'] == $nombCli && $cliente['ubiDepar'] == $DEPARTAMENTO && $cliente['ubiPro'] == $PROVINCIA && $cliente['ubiDis'] == $DISTRITO && $cliente['ubiDirc'] == $direccionLlegada) {
                    $cliente['comprobantes'][] = [
                        'CFTD' => $factura['CFTD'],
                        'CFNUMSER' => $factura['CFNUMSER'],
                        'CFNUMDOC' => $factura['CFNUMDOC'],
                        'total_kg' => $factura['total_kg'],
                        'total_volumen' => $factura['total_volumen'],
                        'CFIMPORTE' => $factura['CFIMPORTE'],
                        'CFCODMON' => $factura['CFCODMON'],
                        'guia' => $factura['guia'], // guia
                        'GREFECEMISION' => $factura['GREFECEMISION'], // fecha de emision de la guía
                        'LLEGADADIRECCION' => $factura['LLEGADADIRECCION'],// Dirección de destino
                        'LLEGADAUBIGEO' => $factura['LLEGADAUBIGEO'],// Código del ubigeo
                        'DEPARTAMENTO' => $factura['DEPARTAMENTO'],// Departamento
                        'PROVINCIA' => $factura['PROVINCIA'],// Provincia
                        'DISTRITO' => $factura['DISTRITO'],// Distrito
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
                'montoOriginal' =>  null, // guardar el monto original de la tarifa.
                'montoSeleccionado' =>  null, // guardar el monto que se puede modificar.
                'montoSeleccionadoDescripcion' =>  null, // descripción al modificar el precio.
                'otros' =>  null,
                'otrosDescripcion' =>  null, // descripción al añadir el precio en el campo otros.
                'mano_obra' =>  0,
                'departamento' =>  null,
                'provincia' =>  null,
                'distrito' =>  null,
                'listo' =>  null,
                'ubiDepar' =>  $DEPARTAMENTO,
                'ubiPro' =>  $PROVINCIA,
                'ubiDis' =>  $DISTRITO,
                'ubiDirc' =>  $direccionLlegada,
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
                        'GREFECEMISION' => $factura['GREFECEMISION'],
                        'LLEGADADIRECCION' => $factura['LLEGADADIRECCION'],
                        'LLEGADAUBIGEO' => $factura['LLEGADAUBIGEO'],
                        'DEPARTAMENTO' => $factura['DEPARTAMENTO'],
                        'PROVINCIA' => $factura['PROVINCIA'],
                        'DISTRITO' => $factura['DISTRITO'],
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
        $vehiculoValido = collect($this->vehiculosSugeridos)->contains(function ($vehiculo) {
            return $vehiculo->id_vehiculo == $this->selectedVehiculo &&
                $vehiculo->id_tarifario == $this->id_tarifario_seleccionado;
        });

        if (!$vehiculoValido) {
            $this->tarifaMontoSeleccionado = null;
            $this->id_tarifario_seleccionado = null;
            $this->selectedVehiculo = null;
            $this->costoTotal = null;
        }
    }

    public function listar_vehiculos_lo(){

        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_local($this->pesoTotal, $this->volumenTotal,1,$this->id_transportistas);
        if (count($this->vehiculosSugeridos) <= 0){
            $this->tarifaMontoSeleccionado = null;
            $this->selectedVehiculo = null;
            $this->id_tarifario_seleccionado = null;
            $this->costoTotal = null;
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
            $this->calcularCostoTotal();
        }
    }

    public function modal_por_vehiculo($id_ve){
        $this->detalle_vehiculo =  $this->vehiculo->listar_informacion_vehiculo($id_ve);
    }

    public function calcularCostoTotal(){
        $montoSeleccionado = floatval($this->tarifaMontoSeleccionado);
        $ayudante = floatval($this->despacho_ayudante);
        $otros = floatval($this->despacho_gasto_otros);

        $this->costoTotal = $montoSeleccionado + $ayudante + $otros;
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
            $comprobantesProvinciales = $this->clientes_provinciales;

            if (empty($comprobantesProvinciales)) {
                session()->flash('error', 'Debe seleccionar al menos un comprobante provincial.');
                return;
            }
            /* VALIDA QUE TODOS LOS PROVINCIALES ESTA CORRECTAMENTE INGRESADO LA INFORMACIÓN*/
            $clientes_listos = array_filter($this->clientes_provinciales, function ($cliente) {
                return $cliente['listo'] == true;
            });
            // Contamos los clientes filtrados
            $total_listos = count($clientes_listos);
            if(count($this->clientes_provinciales) != $total_listos){
                session()->flash('error', 'Falta ingresar la información de uno o más despachos a provincia.');
                return;
            }
            /* ------------------------------------------------------------------------ */
            $microPro = microtime(true);

            // Guardar en la tabla Programaciones
            $programacion = new Programacion();
            $programacion->id_users = Auth::id();
            $programacion->programacion_fecha = $this->programacion_fecha;
            $programacion->programacion_estado = 1;
            $programacion->programacion_microtime = $microPro;
            if (!$programacion->save()) {
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar la programación.');
                return;
            }

            $prograCreado = DB::table('programaciones')
                ->where('programacion_microtime','=',$microPro)->first();

            foreach ($comprobantesProvinciales as $cliente) {
                $micro = microtime(true);
                // Verificar si el transportista fue seleccionado para este cliente
                if (empty($cliente['id_transportista'])) {
                    DB::rollBack();
                    session()->flash('error', "Debe seleccionar un transportista para el cliente: ".$cliente['nombreCliente'].' '.$cliente['ubiDepar'].' - '.$cliente['ubiPro'].' - '.$cliente['ubiDis']);
                    return;
                }

                // Crear despacho para este cliente
                $despacho = new Despacho();
                $despacho->id_users = Auth::id();
                $despacho->id_programacion = $prograCreado->id_programacion;
                $despacho->id_transportistas = $cliente['id_transportista'];
                $despacho->id_tipo_servicios = 2;
                $despacho->id_departamento = $cliente['departamento'];
                $despacho->id_provincia = $cliente['provincia'];
                $despacho->id_distrito = $cliente['distrito'];
                $despacho->id_tarifario = $cliente['id_tarifario'];
                $despacho->despacho_peso = $cliente['total_kg'];
                $despacho->despacho_volumen = $cliente['total_volumen'];
                $despacho->despacho_flete = $cliente['montoOriginal'];
                $despacho->despacho_ayudante = ($cliente['mano_obra'] ?: 0);
                $despacho->despacho_gasto_otros = ($cliente['otros'] ?: 0);
                $despacho->despacho_costo_total = ($cliente['total_kg'] * $cliente['montoSeleccionado'] + ($cliente['otros'] ?: 0));
                $despacho->despacho_descripcion_otros = $cliente['otrosDescripcion'];
                $despacho->despacho_monto_modificado = ($cliente['montoSeleccionado'] ?: 0);
                $despacho->despacho_estado_modificado = $cliente['montoOriginal'] != $cliente['montoSeleccionado'] ? 1 : 0;
                $despacho->despacho_descripcion_modificado = $cliente['montoSeleccionadoDescripcion'];
                $despacho->despacho_estado = 1;
                $despacho->despacho_microtime = $micro;
                if (!$despacho->save()) {
                    DB::rollBack();
                    session()->flash('error', "Error al guardar el despacho para el cliente.");
                    return;
                }

                $despachoCreado = DB::table('despachos')
                    ->where('despacho_microtime','=',$micro)->first();
                // Guardar las facturas asociadas a este despacho
                foreach ($cliente['comprobantes'] as $comprobantesClien) {
                    $despachoVenta = new DespachoVenta();
                    $despachoVenta->id_despacho = $despachoCreado->id_despacho;
                    $despachoVenta->id_venta = null;
                    $despachoVenta->despacho_venta_cftd = $comprobantesClien['CFTD'];
                    $despachoVenta->despacho_venta_cfnumser = $comprobantesClien['CFNUMSER'];
                    $despachoVenta->despacho_venta_cfnumdoc = $comprobantesClien['CFNUMDOC'];
                    $despachoVenta->despacho_venta_factura = $comprobantesClien['CFNUMSER'] . '-' . $comprobantesClien['CFNUMDOC'];
                    $despachoVenta->despacho_detalle_estado = 1;
                    $despachoVenta->despacho_detalle_microtime = microtime(true);

                    if (!$despachoVenta->save()) {
                        DB::rollBack();
                        session()->flash('error', "Error al guardar las facturas para el cliente.");
                        return;
                    }
                }
            }

            // Crear despachos para comprobantes locales (uno por cada comprobante)
            $comprobantesLocales = $this->selectedFacturasLocal;

            $costoOriginalFlete = DB::table('tarifarios')
                ->where('id_tarifario','=',$this->id_tarifario_seleccionado)->first();
            // Crear un solo despacho para todos los comprobantes locales
            $microLocal = microtime(true);
            $despachoLocal = new Despacho();
            $despachoLocal->id_users = Auth::id();
            $despachoLocal->id_programacion = $prograCreado->id_programacion;
            $despachoLocal->id_transportistas = $this->id_transportistas;
            $despachoLocal->id_tipo_servicios = 1; // Local
            $despachoLocal->id_vehiculo = $this->selectedVehiculo;
            $despachoLocal->id_tarifario = $this->id_tarifario_seleccionado;
            $despachoLocal->despacho_peso = array_sum(array_column($comprobantesLocales, 'total_kg'));
            $despachoLocal->despacho_volumen = array_sum(array_column($comprobantesLocales, 'total_volumen'));
            $despachoLocal->despacho_flete = $costoOriginalFlete->tarifa_monto;
            $despachoLocal->despacho_ayudante = ($this->despacho_ayudante ?: 0);
            $despachoLocal->despacho_gasto_otros =  ($this->despacho_gasto_otros ?: 0);
            $despachoLocal->despacho_costo_total = $this->tarifaMontoSeleccionado + ($this->despacho_ayudante ?: 0) + ($this->despacho_gasto_otros ?: 0);

            $despachoLocal->despacho_descripcion_otros = $this->despacho_descripcion_otros;
            $despachoLocal->despacho_monto_modificado = ($this->tarifaMontoSeleccionado ?: 0);
            $despachoLocal->despacho_estado_modificado = $costoOriginalFlete->tarifa_monto !=  $this->tarifaMontoSeleccionado ? 1 : 0;
            $despachoLocal->despacho_descripcion_modificado = $this->despacho_descripcion_modificado;

            $despachoLocal->despacho_estado = 1;
            $despachoLocal->despacho_microtime = $microLocal;
            if (!$despachoLocal->save()) {
                DB::rollBack();
                session()->flash('error', 'Error al guardar el despacho local.');
                return;
            }

            $deslocal = DB::table('despachos')
                ->where('despacho_microtime','=',$microLocal)->first();
            // Guardar las facturas asociadas al despacho local
            foreach ($comprobantesLocales as $factura) {
                $despachoVentaLocal = new DespachoVenta();
                $despachoVentaLocal->id_despacho = $deslocal->id_despacho;
                $despachoVentaLocal->id_venta = null;
                $despachoVentaLocal->despacho_venta_cftd = $factura['CFTD'];
                $despachoVentaLocal->despacho_venta_cfnumser = $factura['CFNUMSER'];
                $despachoVentaLocal->despacho_venta_cfnumdoc = $factura['CFNUMDOC'];
                $despachoVentaLocal->despacho_venta_factura = $factura['CFNUMSER'] . '-' . $factura['CFNUMDOC'];
                $despachoVentaLocal->despacho_detalle_estado = 1;
                $despachoVentaLocal->despacho_detalle_microtime = microtime(true);

                if (!$despachoVentaLocal->save()) {
                    DB::rollBack();
                    session()->flash('error', 'Error al guardar las facturas locales.');
                    return;
                }
            }

            DB::commit();
            session()->flash('success', 'Despachos guardados correctamente.');
            $this->reiniciar_campos();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar la programación.');
            return;
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
        $this->clientes_provinciales = [];
    }


}
