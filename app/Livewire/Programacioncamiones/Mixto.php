<?php

namespace App\Livewire\Programacioncamiones;
use App\Models\Guia;
use App\Models\Serviciotransporte;
use App\Models\Tarifario;
use Carbon\Carbon;
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

use Illuminate\Support\Facades\Gate;
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
    private $guia;
    private $serviciotransporte;

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
        $this->guia = new Guia();
        $this->serviciotransporte = new Serviciotransporte();
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
    public $montoOriginal = 0;
    /* ---------------------------------- */
    public $id_programacion_edit = '';
    public $id_despacho_edit = '';
    public $checkInput = '';
    public $guias_estado_tres = [];
    public $serv_transp = [];
    public $guiainfo = [];
    public $guia_detalle = [];
    public $searchGuia = [];
    public $selectedServTrns = [];
    /* ---------------------------------- */
    public function mount($id = null){
        $this->id_transportistas = null;
        $this->selectedVehiculo = null;
        $this->showBotonListo = null;
        $this->programacion_fecha = now()->format('Y-m-d');
        $this->desde = null;
        $this->hasta = null;
        if ($id){
            $this->id_programacion_edit = $id;
            $despachoEdit = DB::table('despachos')->where('id_programacion','=',$id)->where('id_tipo_servicios','=',1)->first();
            $despachoEditProvinci = DB::table('despachos')->where('id_programacion','=',$id)->where('id_tipo_servicios','=',2)->first();
            if ($despachoEdit && $despachoEditProvinci){
                $this->id_despacho_edit = $despachoEdit->id_despacho; // despacho local
                $this->listar_informacion_programacion_edit();
            }
        }
//        $this->dispatch('buscar_comprobantes');

//        $this->buscar_facturas_clientes();
    }

    public function render(){
        $tipo_servicio_local_provincial = $this->tiposervicio->listar_tipo_servicio_local_provincial();
//        $listar_transportistas = $this->transportista->listar_transportista_sin_id();
        $listar_departamento = $this->departamento->lista_departamento();
        if (count($this->vehiculosSugeridos) > 0){
            // Obtener los id_transportistas únicos de la colección
            $idsTransportistas = $this->vehiculosSugeridos->pluck('id_transportistas')->unique();
            // Consultar la base de datos para traer transportistas únicos
            $listar_transportistas = DB::table('transportistas')
                ->whereIn('id_transportistas', $idsTransportistas)
                ->get();
        }else{
            $listar_transportistas = [];
        }

        if (count($this->tarifariosSugeridos) > 0){
            // Obtener los id_transportistas únicos de la colección
            $idsTransportistas = $this->tarifariosSugeridos->pluck('id_transportistas')->unique();
            // Consultar la base de datos para traer transportistas únicos
            $listar_transportistasProvinciales = DB::table('transportistas')
                ->whereIn('id_transportistas', $idsTransportistas)
                ->get();
        }else{
            $listar_transportistasProvinciales = [];
        }

        // Obtener las guías con estado 3
        $guiasQuery = Guia::wherein('guia_estado_aprobacion', [3,11]);

        // Filtrar por nombre del cliente si searchGuia tiene valor
        if (!empty($this->searchGuia)) {
            $guiasQuery->where(function($query) {
                $query->where('guia_nombre_cliente', 'like', '%' . $this->searchGuia . '%')
                    ->orWhere('guia_nro_doc', 'like', '%' . $this->searchGuia . '%')
                    ->orWhere('guia_nro_doc_ref', 'like', '%' . $this->searchGuia . '%');
            });
        }

        $guias = $guiasQuery->get();

        // Calcular el peso y volumen total para cada guía
        $this->guias_estado_tres = $guias->map(function ($guia) {
            $detalles = DB::table('guias_detalles')
                ->where('id_guia', $guia->id_guia)
                ->get();

            $pesoTotalGramos = $detalles->sum(function ($detalle) {
                return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
            });

            $pesoTotalKilos = $pesoTotalGramos / 1000;

            $volumenTotal = $detalles->sum(function ($detalle) {
                return $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
            });

            $guia->peso_total = $pesoTotalKilos;
            $guia->volumen_total = $volumenTotal;

            return $guia;
        });

        $servTransp = Serviciotransporte::where('serv_transpt_estado_aprobacion', 0);

        $servicio = $servTransp->get();
        $this->serv_transp = $servicio;

        return view('livewire.programacioncamiones.mixto', compact('tipo_servicio_local_provincial', 'listar_transportistas', 'listar_transportistas', 'listar_departamento','listar_transportistasProvinciales'));
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
    public function listar_informacion_programacion_edit(){
        $informacionPrograma = $this->programacion->informacion_id($this->id_programacion_edit);
        $informacionDespachoLocal = DB::table('despachos')->where('id_despacho','=',$this->id_despacho_edit)->first();
        $despachosProvinciales = DB::table('despachos')->where('id_tipo_servicios','=',2)->where('id_programacion','=',$this->id_programacion_edit)->get();
        if ($informacionPrograma && $informacionDespachoLocal && $despachosProvinciales){
            $this->id_transportistas = $informacionDespachoLocal->id_transportistas;
            $this->programacion_fecha = $informacionPrograma->programacion_fecha;
            /*------------------------------*/

            /*------------------------------*/
            $comprobantes = DB::table('despacho_ventas')->where('id_despacho','=',$informacionDespachoLocal->id_despacho)->get();
            foreach ($comprobantes as $c){
                $validarcheckComprobante = DB::table('despacho_ventas as dv')
                    ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
                    ->where([
                        ['d.id_programacion','=',$this->id_programacion_edit],
                        ['d.id_tipo_servicios','=',2],
                        ['dv.id_guia','=',$c->id_guia],
                    ])->exists();
                // Obtener los datos de la guía desde la tabla guias
                $guia = DB::table('guias')
                    ->where('id_guia', '=', $c->id_guia)
                    ->first();

                if ($guia) {
                    // Obtener los detalles de la guía desde la tabla guias_detalles
                    $detallesGuia = DB::table('guias_detalles')
                        ->where('id_guia', '=', $c->id_guia)
                        ->get();

                    // Calcular el peso y volumen total de la guía
                    $pesoTotalGramos = $detallesGuia->sum(function ($detalle) {
                        return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                    });

                    $pesoTotalKilos = $pesoTotalGramos / 1000;

                    $volumenTotal = $detallesGuia->sum(function ($detalle) {
                        return $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
                    });
                    $guiasAgregadas = [];
                    $this->selectedFacturasLocal[] = [
                        'id_guia' => $c->id_guia,
                        'guia_almacen_origen' => $guia->guia_almacen_origen,
                        'guia_tipo_doc' => $guia->guia_tipo_doc,
                        'guia_nro_doc' => $guia->guia_nro_doc,
                        'peso_total' => $pesoTotalKilos,
                        'volumen_total' => $volumenTotal,
                        'guia_fecha_emision' => $guia->guia_fecha_emision,
                        'guia_tipo_movimiento' => $guia->guia_tipo_movimiento,
                        'guia_tipo_doc_ref' => $guia->guia_tipo_doc_ref,
                        'guia_nro_doc_ref' => $guia->guia_nro_doc_ref,
                        'guia_glosa' => $guia->guia_glosa,
                        'guia_fecha_proceso' => $guia->guia_fecha_proceso,
                        'guia_hora_proceso' => $guia->guia_hora_proceso,
                        'guia_usuario' => $guia->guia_usuario,
                        'guia_cod_cliente' => $guia->guia_cod_cliente,
                        'guia_ruc_cliente' => $guia->guia_ruc_cliente,
                        'guia_nombre_cliente' => $guia->guia_nombre_cliente,
                        'guia_forma_pago' => $guia->guia_forma_pago,
                        'guia_vendedor' => $guia->guia_vendedor,
                        'guia_moneda' => $guia->guia_moneda,
                        'guia_tipo_cambio' => $guia->guia_tipo_cambio,
                        'guia_estado' => $guia->guia_estado,
                        'guia_direc_entrega' => $guia->guia_direc_entrega,
                        'guia_nro_pedido' => $guia->guia_nro_pedido,
                        'isChecked' => $validarcheckComprobante,
                        'guia_importe_total' => $guia->guia_importe_total,
                        'guia_importe_total_sin_igv' => $guia->guia_importe_total_sin_igv,
                        'guia_departamento' => $guia->guia_departamento,
                        'guia_provincia' => $guia->guia_provincia,
                        'guia_destrito' => $guia->guia_destrito,
                    ];
                    // Marcar el id_guia como agregado
                    $guiasAgregadas[] = $c->id_guia;
                    $this->pesoTotal += $pesoTotalKilos;
                    $this->volumenTotal += $volumenTotal;
                    $importe = $guia->guia_importe_total_sin_igv;
                    $importe = floatval($importe);
                    $this->importeTotalVenta += $importe;
                }
            }
            // Agrupar los IDs de despachos provinciales para optimizar las consultas
            $idsDespachos = $despachosProvinciales->pluck('id_despacho');

            // Obtener todos los comprobantes relacionados en una sola consulta
            $comprobantesProvinciales = DB::table('despacho_ventas as dv')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->whereIn('dv.id_despacho', $idsDespachos)
                ->get()
                ->groupBy('id_despacho');

            foreach ($despachosProvinciales as $de){
                $comprobantes = $comprobantesProvinciales[$de->id_despacho] ?? collect();
                $comprobantesPro = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->where('dv.id_despacho','=',$de->id_despacho)
                    ->get();

                // Calcular el peso y volumen para cada guía específica

                $this->clientes_provinciales[] = [
                    'codigoCliente' =>  $comprobantesPro[0]->guia_ruc_cliente,
                    'nombreCliente' =>  $comprobantesPro[0]->guia_nombre_cliente,
                    'peso_total' => $de->despacho_peso,
                    'volumen_total' =>  $de->despacho_volumen,
                    'id_transportista' =>  $de->id_transportistas,
                    'id_tarifario' =>  $de->id_tarifario,
                    'montoOriginal' =>  $de->despacho_flete,
                    'montoSeleccionado' =>  $de->despacho_monto_modificado,
                    'montoSeleccionadoDescripcion' =>  $de->despacho_descripcion_modificado,
                    'otros' =>  $de->despacho_gasto_otros,
                    'otrosDescripcion' =>  $de->despacho_descripcion_otros,
                    'mano_obra' =>  0,
                    'departamento' =>  $de->id_departamento,
                    'provincia' =>  $de->id_provincia,
                    'distrito' =>  $de->id_distrito,
                    'listo' =>  true,
                    'ubiDepar' =>  $comprobantesPro[0]->guia_departamento,
                    'ubiPro' =>  $comprobantesPro[0]->guia_provincia,
                    'ubiDis' =>  $comprobantesPro[0]->guia_destrito,
                    'ubiDirc' =>  $comprobantesPro[0]->guia_direc_entrega,
                    'comprobantes' => $comprobantes->map(function ($factura) {
                        // Calcular peso y volumen específicos para esta guía
                        $detalles = DB::table('guias_detalles')
                            ->where('id_guia', $factura->id_guia)
                            ->get();

                        $pesoGramos = $detalles->sum(function ($detalle) {
                            return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                        });
                        $pesoKilos = $pesoGramos / 1000;

                        $volumen = $detalles->sum(function ($detalle) {
                            return $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
                        });

                        return [
                            'id_guia' => $factura->id_guia,
                            'peso_total' => $pesoKilos,
                            'volumen_total' => $volumen,
                            'guia_importe_total' => $factura->guia_importe_total,
                            'guia_importe_total_sin_igv' => $factura->guia_importe_total_sin_igv,
                            'guia_fecha_emision' => $factura->guia_fecha_emision,
                            'guia_nro_doc' => $factura->guia_nro_doc,
                            'guia_nro_doc_ref' => $factura->guia_nro_doc_ref,
                            'guia_nombre_cliente' => $factura->guia_nombre_cliente,
                            'guia_direc_entrega' => $factura->guia_direc_entrega,
                            'guia_departamento' => $factura->guia_departamento,
                            'guia_provincia' => $factura->guia_provincia,
                            'guia_destrito' => $factura->guia_destrito,
                        ];
                    })->toArray(),
                ];
            }
            $this->tarifaMontoSeleccionado = $informacionDespachoLocal->despacho_monto_modificado;

            $this->despacho_descripcion_modificado = $informacionDespachoLocal->despacho_descripcion_modificado;
            $this->despacho_gasto_otros = $informacionDespachoLocal->despacho_gasto_otros;
            $this->despacho_descripcion_otros = $informacionDespachoLocal->despacho_descripcion_otros;
            $this->despacho_ayudante = $informacionDespachoLocal->despacho_ayudante;

            $this->montoOriginal = $informacionDespachoLocal->despacho_flete;
            $this->id_tarifario_seleccionado = $informacionDespachoLocal->id_tarifario;
            $this->selectedVehiculo = $informacionDespachoLocal->id_vehiculo;
            $this->checkInput = $informacionDespachoLocal->id_vehiculo.'-'.$informacionDespachoLocal->id_tarifario;
            $this->calcularCostoTotal();
            // Actualizar lista de vehículos sugeridos
            $this->listar_vehiculos_lo();
            $this->validarVehiculoSeleccionado();
        }
    }
    public function abrirModalComprobantes($cliente,$index){ // en $cliente llega el código del cliente, DNI o RUC
        /* limpiamos primero el array */
        $this->clienteSeleccionado = "";
        $this->clienteindex = "";
        /* Añadimos valor */
        $this->clienteSeleccionado = $cliente;
        $this->clienteindex = $index;
        $datosCliente = $this->clientes_provinciales[$index] ?? null;
        $this->id_trans = null;
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
            $deparCl = DB::table('departamentos')->where('departamento_nombre','like',$datosCliente['ubiDepar'].'%')->first();
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
        $this->clientes_provinciales[$index]['peso_total'] = 0;
        $this->clientes_provinciales[$index]['volumen_total'] = 0;
        $this->clientes_provinciales[$index]['id_tarifario'] = null;
        foreach ($this->comprobantesSeleccionados as $com){
            $this->clientes_provinciales[$index]['peso_total'] += $com['peso_total'];
            $this->clientes_provinciales[$index]['volumen_total'] += $com['volumen_total'];
            $this->imporTotalPro += round($com['guia_importe_total_sin_igv'] ?? 0, 2);
            $this->toKg += $com['peso_total'];
            $this->toVol += $com['volumen_total'];
        }
        $this->listar_tarifarios_su($index);
        $this->activar_botonListo($index);
        $this->save_cliente_data($index);
    }
    public function listar_tarifarios_su($index){
        $datosCliente = $this->clientes_provinciales[$index] ?? null;
        if ($datosCliente){
            $this->tarifariosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_provincial($datosCliente['peso_total'],2,$datosCliente['id_transportista'],$datosCliente['departamento'] ,$datosCliente['provincia'] ,$datosCliente['distrito']);
            if (count($this->tarifariosSugeridos) <= 0){
                $this->limpiarParaPro($index);
            }
        }
    }
    public function limpiarParaPro($index){
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
        // Verificar si ambas fechas están presentes
        if (!empty($this->desde) && !empty($this->hasta)) {
            // Obtener el año de las fechas 'desde' y 'hasta'
            $yearDesde = date('Y', strtotime($this->desde));
            $yearHasta = date('Y', strtotime($this->hasta));

            // Validar que los años sean 2025 o posteriores
            if ($yearDesde < 2025 || $yearHasta < 2025) {
                // Mostrar un mensaje de error si los años no son válidos
                session()->flash('error', 'Las fechas deben ser a partir de 2025.');
                return; // Salir del método si la validación falla
            }
        }

        $comproba = $this->server->listar_comprobantes_listos_local($this->searchFacturaCliente, $this->desde, $this->hasta);
        $this->filteredFacturasYClientes = $comproba;
        if (!$comproba) {
            $this->filteredFacturasYClientes = [];
        }
    }

    public function seleccionarFactura($id_guia){
        // Buscar la factura por su ID
        $factura = Guia::find($id_guia);

        if (!$factura) {
            session()->flash('error', 'Guía no encontrada.');
            return;
        }

        // Validar que la factura no esté ya en el array selectedFacturas
        $comprobanteExiste = collect($this->selectedFacturasLocal)->first(function ($facturaSeleccionada) use ($factura) {
            return $facturaSeleccionada['id_guia'] === $factura->id_guia;
        });

        if ($comprobanteExiste) {
            session()->flash('error', 'Esta guía ya fue agregada.');
            return;
        }

        // Calcular el peso y volumen total para la guía seleccionada
        $detalles = DB::table('guias_detalles')
            ->where('id_guia', $factura->id_guia)
            ->get();

        // Calcular el peso total en kilogramos
        $pesoTotalGramos = $detalles->sum(function ($detalle) {
            return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
        });

        // Convertir el peso total a kilogramos
        $pesoTotalKilos = $pesoTotalGramos / 1000;

        $volumenTotal = $detalles->sum(function ($detalle) {
            return $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
        });

        // Validar que el peso y volumen sean mayores a 0
//        if ($pesoTotalKilos <= 0 || $volumenTotal <= 0) {
//            session()->flash('error', 'El peso o el volumen deben ser mayores a 0. Verifique los detalles de la guía.');
//            return;
//        }
        // Agregar la factura seleccionada y actualizar el peso y volumen total
        $this->selectedFacturasLocal[] = [
            'id_guia' => $factura->id_guia,
            'guia_almacen_origen' => $factura->guia_almacen_origen,
            'guia_tipo_doc' => $factura->guia_tipo_doc,
            'guia_nro_doc' => $factura->guia_nro_doc,
            'guia_fecha_emision' => $factura->guia_fecha_emision,
            'guia_tipo_movimiento' => $factura->guia_tipo_movimiento,
            'guia_tipo_doc_ref' => $factura->guia_tipo_doc_ref,
            'guia_nro_doc_ref' => $factura->guia_nro_doc_ref,
            'guia_glosa' => $factura->guia_glosa,
            'guia_fecha_proceso' => $factura->guia_fecha_proceso,
            'guia_hora_proceso' => $factura->guia_hora_proceso,
            'guia_usuario' => $factura->guia_usuario,
            'guia_cod_cliente' => $factura->guia_cod_cliente,
            'guia_ruc_cliente' => $factura->guia_ruc_cliente,
            'guia_nombre_cliente' => $factura->guia_nombre_cliente,
            'guia_forma_pago' => $factura->guia_forma_pago,
            'guia_vendedor' => $factura->guia_vendedor,
            'guia_moneda' => $factura->guia_moneda,
            'guia_tipo_cambio' => $factura->guia_tipo_cambio,
            'guia_estado' => $factura->guia_estado,
            'guia_direc_entrega' => $factura->guia_direc_entrega,
            'guia_nro_pedido' => $factura->guia_nro_pedido,
            'guia_importe_total' => $factura->guia_importe_total,
            'guia_importe_total_sin_igv' => $factura->guia_importe_total_sin_igv,
            'guia_departamento' => $factura->guia_departamento,
            'guia_provincia' => $factura->guia_provincia,
            'guia_destrito' => $factura->guia_destrito,
            'peso_total' => $pesoTotalKilos,
            'volumen_total' => $volumenTotal,
        ];
        $this->pesoTotal += $pesoTotalKilos;
        $this->volumenTotal += $volumenTotal;

        $importes = $factura->guia_importe_total_sin_igv;
        $importe = floatval($importes);
        $this->importeTotalVenta += $importe;

        // Actualizar lista de vehículos sugeridos
        $this->listar_vehiculos_lo();
        $this->validarVehiculoSeleccionado();
    }

    public function validarVehiculoSeleccionado(){
        if ($this->selectedVehiculo && $this->id_tarifario_seleccionado) {
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
    }

    public function actualizarFactura($id_guia, $isChecked){
        if ($isChecked) {
            $this->duplicar_comprobante($id_guia);
        } else {
            $this->eliminarFacturaProvincial($id_guia);
        }
    }

    public $clienteseleccionado = [];
    public function duplicar_comprobante($id_guia){
        $id_guia = (int)$id_guia;
        // Buscar la factura en la tabla Local
        $factura = collect($this->selectedFacturasLocal)->first(function ($f) use ($id_guia) {
            return $f['id_guia'] === $id_guia;
        });
        if (!$factura) {
            session()->flash('error', 'Comprobante no encontrado en la tabla Local.');
            return;
        }
        $codiCli = $factura['guia_ruc_cliente'];
        $nombCli = $factura['guia_nombre_cliente'];
        $DEPARTAMENTO = $factura['guia_departamento'];
        $PROVINCIA = $factura['guia_provincia'];
        $DISTRITO = $factura['guia_destrito'];
        $direccionLlegada = $factura['guia_direc_entrega'];

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
                return $comprobante['id_guia'] === $factura['id_guia'];
            });
            if ($existeComprobante) {
                session()->flash('error', 'El comprobante ya está duplicado para este cliente.');
                return;
            }
            // Agregar el comprobante al cliente existente
            foreach ($this->clientes_provinciales as &$cliente) {
                if ($cliente['codigoCliente'] == $codiCli && $cliente['nombreCliente'] == $nombCli && $cliente['ubiDepar'] == $DEPARTAMENTO && $cliente['ubiPro'] == $PROVINCIA && $cliente['ubiDis'] == $DISTRITO && $cliente['ubiDirc'] == $direccionLlegada) {
                    $cliente['comprobantes'][] = [
                        'id_guia' => $factura['id_guia'],
                        'peso_total' => $factura['peso_total'],
                        'volumen_total' => $factura['volumen_total'],
                        'guia_importe_total_sin_igv' => $factura['guia_importe_total_sin_igv'],
                        'guia_direc_entrega' => $factura['guia_direc_entrega'],
                        'guia_departamento' => $factura['guia_departamento'],
                        'guia_provincia' => $factura['guia_provincia'],
                        'guia_destrito' => $factura['guia_destrito'],
                        'guia_fecha_emision' => $factura['guia_fecha_emision'],
                        'guia_nro_doc' => $factura['guia_nro_doc'],
                        'guia_nro_doc_ref' => $factura['guia_nro_doc_ref'],
                        'guia_nombre_cliente' => $factura['guia_nombre_cliente'],
                    ];
                    break;
                }
            }
        }else{ // ingresar cliente nuevo
            $this->clientes_provinciales[] = [
                'codigoCliente' =>  $factura['guia_ruc_cliente'],
                'nombreCliente' =>  $factura['guia_nombre_cliente'],
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
                        'id_guia' => $factura['id_guia'],
                        'peso_total' => $factura['peso_total'],
                        'volumen_total' => $factura['volumen_total'],
                        'guia_importe_total_sin_igv' => $factura['guia_importe_total_sin_igv'],
                        'guia_fecha_emision' => $factura['guia_fecha_emision'],
                        'guia_nro_doc' => $factura['guia_nro_doc'],
                        'guia_nro_doc_ref' => $factura['guia_nro_doc_ref'],
                        'guia_nombre_cliente' => $factura['guia_nombre_cliente'],
                        'guia_direc_entrega' => $factura['guia_direc_entrega'],
                        'guia_departamento' => $factura['guia_departamento'],
                        'guia_provincia' => $factura['guia_provincia'],
                        'guia_destrito' => $factura['guia_destrito'],
                    ]
                ]
            ];
        }
    }
    public function eliminarFacturaProvincial($id_guia,$indexCliente = null){
        $id_guia = (int)$id_guia;
        foreach ($this->clientes_provinciales as $index => &$cliente) {
            // Filtrar comprobantes del cliente actual
            $cliente['comprobantes'] = collect($cliente['comprobantes'])
                ->filter(function ($comprobante) use ($id_guia) {
                    return !($comprobante['id_guia'] === $id_guia);
                })
                ->values()
                ->toArray();

            // Si el cliente no tiene comprobantes restantes, eliminarlo del array
            if (empty($cliente['comprobantes'])) {
                unset($this->clientes_provinciales[$index]);
            }else{
                if ($indexCliente == $index){
                    $cliente['listo'] = false;
                }
            }
        }

        // Actualizar el estado del checkbox en la tabla `selectedFacturasLocal`
        foreach ($this->selectedFacturasLocal as &$factura) {
            if ($factura['id_guia'] === $id_guia) {
                $factura['isChecked'] = false;
                break;
            }
        }

        // Reindexar el array `clientes_provinciales` después de eliminar elementos
        $this->clientes_provinciales = array_values($this->clientes_provinciales);
    }

    public function eliminarFacturaSeleccionada($id_guia){
        $id_guia = (string)$id_guia;
        $this->eliminarFacturaProvincial($id_guia);
        // Encuentra la factura en las seleccionadas
        $factura = collect($this->selectedFacturasLocal)->first(function ($f) use ($id_guia) {
            return (string)$f['id_guia'] === $id_guia; // Convertir a string para comparar
        });

        if ($factura) {
            // Elimina la factura de la lista seleccionada
            $this->selectedFacturasLocal = collect($this->selectedFacturasLocal)
                ->reject(function ($f) use ($id_guia) {
                    return (string)$f['id_guia'] === $id_guia; // Convertir a string para comparar
                })
                ->values()
                ->toArray();

            // Actualiza los totales
            $this->pesoTotal -= $factura['peso_total'];
            $this->volumenTotal -= $factura['volumen_total'];
            $this->importeTotalVenta -= floatval($factura['guia_importe_total_sin_igv']);

            // Verifica si no quedan facturas ni servicios de transporte seleccionados
            if (empty($this->selectedFacturasLocal) && empty($this->selectedServTrns)) {
                $this->pesoTotal = 0;
                $this->volumenTotal = 0;
                $this->importeTotalVenta = 0;
            }

            // Actualizar lista de vehículos sugeridos
            $this->listar_vehiculos_lo();
            $this->validarVehiculoSeleccionado();;
        } else {
            \Log::warning("No se encontró la guía con id_guia: $id_guia");
        }
//        foreach ($this->selectedFacturasLocal as $index => $factura) {
//            if ($factura['id_guia'] == $id_guia) {
//                $this->pesoTotal -= $factura['peso_total'];
//                $this->volumenTotal -= $factura['volumen_total'];
//                $this->importeTotalVenta =  $this->importeTotalVenta - $factura['guia_importe_total'];
//
//                unset($this->selectedFacturasLocal[$index]);
//                break;
//            }
//        }
//        // Verifica si no quedan facturas ni servicios de transporte seleccionados
//        if (empty($this->selectedFacturas) && empty($this->selectedServTrns)) {
//            $this->pesoTotal = 0;
//            $this->volumenTotal = 0;
//            $this->importeTotalVenta = 0;
//        }
//        // Reindexar el array `selectedFacturasLocal` después de eliminar elementos
//        $this->selectedFacturasLocal = array_values($this->selectedFacturasLocal);
//        $this->listar_vehiculos_lo();
//        $this->validarVehiculoSeleccionado();
    }

    public function listar_vehiculos_lo(){
        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_local($this->pesoTotal, $this->volumenTotal,1,$this->id_transportistas);
        // Verificar si el vehículo previamente seleccionado sigue siendo válido
        $vehiculoValido = collect($this->vehiculosSugeridos)->first(function ($vehiculo) {
            return $vehiculo->id_vehiculo == $this->selectedVehiculo &&
                $vehiculo->id_tarifario == $this->id_tarifario_seleccionado;
        });

        if ($vehiculoValido) {
            // Mantener el vehículo seleccionado y el monto
            $this->tarifaMontoSeleccionado = $vehiculoValido->tarifa_monto;
            $this->selectedVehiculo = $vehiculoValido->id_vehiculo;
            $this->id_tarifario_seleccionado = $vehiculoValido->id_tarifario;
            $this->calcularCostoTotal();
        } else {
            // Limpiar selección si no es válida
            $this->tarifaMontoSeleccionado = null;
            $this->selectedVehiculo = null;
            $this->id_tarifario_seleccionado = null;
            $this->costoTotal = null;
        }
    }

//    public function actualizarVehiculosSugeridos(){
//        $this->listar_vehiculos_lo();
//        $this->tarifaMontoSeleccionado = null;
//        $this->selectedVehiculo = null;
//        $this->id_tarifario_seleccionado = null;
//    }

    public function seleccionarVehiculo($vehiculoId,$id_tarifa){
        $vehiculo = collect($this->vehiculosSugeridos)->first(function ($vehiculo) use ($vehiculoId, $id_tarifa) {
            return $vehiculo->id_vehiculo == $vehiculoId && $vehiculo->id_tarifario == $id_tarifa;
        });
//        $vehiculo = collect($this->vehiculosSugeridos)->firstWhere('id_vehiculo', $vehiculoId);
        if ($vehiculo) {
            // Actualiza el monto de la tarifa del vehículo seleccionado
            $this->tarifaMontoSeleccionado = $vehiculo->tarifa_monto;
            $this->montoOriginal = $vehiculo->tarifa_monto;
            $this->id_tarifario_seleccionado = $id_tarifa;
            $this->selectedVehiculo = $vehiculoId;
            $this->checkInput = $vehiculoId.'-'.$id_tarifa;

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
            if (!Gate::allows('guardar_despacho_mixto')) {
                session()->flash('error', 'No tiene permisos para crear una programación mixta.');
                return;
            }
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
            if ($this->id_programacion_edit && $this->id_despacho_edit){
                // Se va a eliminar los comprobantes del anterior registro local
                DB::table('despacho_ventas')->where('id_despacho','=',$this->id_despacho_edit)->delete();
                /* vamos eliminar los despachos provinciales */
                // Obtener los despachos provinciales relacionados
                $despachosProvinciales = Despacho::where('id_tipo_servicios', 2)
                    ->where('id_programacion', $this->id_programacion_edit)
                    ->get();
                foreach ($despachosProvinciales as $despachoDelete) {
                    // Eliminar detalles de despacho y el registro principal
                    DespachoVenta::where('id_despacho', $despachoDelete->id_despacho)->delete();
                    $despachoDelete->delete();
                }
            }
            $contadorError = 0;
            $duplicados = 0;
            // Validar facturas locales
            foreach ($this->selectedFacturasLocal ?? [] as $comprobanteId => $factura) {
                if (!isset($factura['id_guia'])) {
                    session()->flash('error', 'Error en los datos de las facturas locales.');
                    DB::rollBack();
                    return;
                }
                $existe = DB::table('despacho_ventas as dv')
                    ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->where('d.despacho_estado_aprobacion', '<>', 4)
                    ->where('dv.id_guia', $factura['id_guia'])
                    ->whereIn('g.guia_estado_aprobacion', [7,8])
                    ->orderBy('dv.id_despacho_venta', 'desc')
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
            // Validar duplicidad para los servicios de transporte seleccionados (selectedServTrns)
            if (!empty($this->selectedServTrns)) { // Solo validar si selectedServTrns no está vacío
                foreach ($this->selectedServTrns as $servTrn) {
                    $existe = DB::table('despacho_ventas as dv')
                        ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                        ->join('servicios_transportes as st', 'dv.id_serv_transpt', '=', 'st.id_serv_transpt')
                        ->where('st.serv_transpt_estado_aprobacion', '<>', 3)
                        ->where('dv.id_serv_transpt', $servTrn['id_serv_transpt'])
                        ->whereIn('st.serv_transpt_estado_aprobacion', [0, 1, 2])
                        ->orderBy('dv.id_despacho_venta', 'desc')
                        ->exists();
                    if ($existe) {
                        $contadorError++;
                    }
                }
            }
            if ($contadorError > 0) {
                session()->flash('error', "Se encontraron guías o servicios de transporte duplicados. Por favor, verifica.");
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
            if ($this->id_programacion_edit && $this->id_despacho_edit){
                // se va a eliminar los comprobantes del anterior registro
                $programacion = Programacion::find($this->id_programacion_edit);
            }else{
                $programacion = new Programacion();
                $programacion->id_users = Auth::id();
            }
            $programacion->programacion_fecha = $this->programacion_fecha;
            $programacion->programacion_estado_aprobacion = 0;
            $programacion->programacion_estado = 1;
            $programacion->programacion_microtime = $microPro;
            if (!$programacion->save()) {
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar la programación.');
                return;
            }

            $prograCreado = DB::table('programaciones')->where('programacion_microtime','=',$microPro)->first();

            $comprobantesLocales = $this->selectedFacturasLocal;

            $costoOriginalFlete = DB::table('tarifarios')->where('id_tarifario','=',$this->id_tarifario_seleccionado)->first();
            $microLocal = microtime(true);
            if ($this->id_programacion_edit && $this->id_despacho_edit){
                // se va a eliminar los comprobantes del anterior registro
                $despachoLocal = Despacho::find($this->id_despacho_edit);
            }else{
                $despachoLocal = new Despacho();
                $despachoLocal->id_users = Auth::id();
            }
            $despachoLocal->id_programacion = $prograCreado->id_programacion;
            $despachoLocal->id_transportistas = $this->id_transportistas;
            $despachoLocal->id_tipo_servicios = 1; // Local
            $despachoLocal->id_vehiculo = $this->selectedVehiculo;
            $despachoLocal->id_tarifario = $this->id_tarifario_seleccionado;
            $despachoLocal->despacho_peso = array_sum(array_column($comprobantesLocales, 'peso_total'));
            $despachoLocal->despacho_volumen = array_sum(array_column($comprobantesLocales, 'volumen_total'));
            $despachoLocal->despacho_flete = $costoOriginalFlete->tarifa_monto;
            $despachoLocal->despacho_ayudante = ($this->despacho_ayudante ?: 0);
            $despachoLocal->despacho_gasto_otros =  ($this->despacho_gasto_otros ?: 0);
            $despachoLocal->despacho_costo_total = $this->tarifaMontoSeleccionado + ($this->despacho_ayudante ?: 0) + ($this->despacho_gasto_otros ?: 0);
            $despachoLocal->despacho_estado_aprobacion = 0;
            $despachoLocal->despacho_descripcion_otros = $this->despacho_descripcion_otros;
            $despachoLocal->despacho_monto_modificado = ($this->tarifaMontoSeleccionado ?: 0);
            $despachoLocal->despacho_estado_modificado = $costoOriginalFlete->tarifa_monto !=  $this->tarifaMontoSeleccionado ? 1 : 0;
            $despachoLocal->despacho_descripcion_modificado = $this->despacho_descripcion_modificado;

            $despachoLocal->despacho_estado = 1;
            $despachoLocal->despacho_microtime = $microLocal;

            $existecap = DB::table('tarifarios')
                ->where('id_tarifario', $this->id_tarifario_seleccionado)
                ->select('tarifa_cap_min', 'tarifa_cap_max')
                ->first();
            $despachoLocal->despacho_cap_min = $existecap->tarifa_cap_min;
            $despachoLocal->despacho_cap_max = $existecap->tarifa_cap_max;

            if (!$despachoLocal->save()) {
                DB::rollBack();
                session()->flash('error', 'Error al guardar el despacho local.');
                return;
            }

            $deslocal = DB::table('despachos')->where('despacho_microtime','=',$microLocal)->first();
            // Guardar las facturas asociadas al despacho local
            foreach ($comprobantesLocales as $factura) {
                $despachoVentaLocal = new DespachoVenta();
                $despachoVentaLocal->id_despacho = $deslocal->id_despacho;
                $despachoVentaLocal->id_guia = $factura['id_guia'];
                $despachoVentaLocal->despacho_detalle_estado = 1;
                $despachoVentaLocal->despacho_detalle_microtime = microtime(true);
                $despachoVentaLocal->despacho_detalle_estado_entrega = 0;

                if (!$despachoVentaLocal->save()) {
                    DB::rollBack();
                    session()->flash('error', 'Error al guardar las facturas locales.');
                    return;
                }
            }
            // Actualizar el estado de las guías a 4
            $idsGuias = array_column($comprobantesLocales, 'id_guia');
            DB::table('guias')
                ->whereIn('id_guia', $idsGuias)
                ->update(['guia_estado_aprobacion' => 4, 'updated_at' => now('America/Lima')]);
            // Guardar en historial_guias
            foreach ($comprobantesLocales as $factura) {
                // Obtener el número de documento de la guía
                $guia = DB::table('guias')
                    ->where('id_guia', $factura['id_guia'])
                    ->first();

                if ($guia) {
                    // Insertar en historial_guias
                    DB::table('historial_guias')->insert([
                        'id_users' => Auth::id(),
                        'id_guia' => $factura['id_guia'],
                        'guia_nro_doc' => $guia->guia_nro_doc,
                        'historial_guia_estado_aprobacion' => 4,
                        'historial_guia_fecha_hora' => Carbon::now('America/Lima'),
                        'historial_guia_estado' => 1,
                        'created_at' => Carbon::now('America/Lima'),
                        'updated_at' => Carbon::now('America/Lima'),
                    ]);
                }
            }
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
                $despacho->id_distrito = $cliente['distrito'] ? $cliente['distrito'] : null;
                $despacho->id_tarifario = $cliente['id_tarifario'];
                $despacho->despacho_peso = $cliente['peso_total'];
                $despacho->despacho_volumen = $cliente['volumen_total'];
                $despacho->despacho_flete = $cliente['montoOriginal'];
                $despacho->despacho_ayudante = ($cliente['mano_obra'] ?: 0);
                $despacho->despacho_gasto_otros = ($cliente['otros'] ?: 0);
                $despacho->despacho_costo_total = ($cliente['peso_total'] * $cliente['montoSeleccionado'] + ($cliente['otros'] ?: 0));
                $despacho->despacho_estado_aprobacion = 0;
                $despacho->despacho_descripcion_otros = $cliente['otrosDescripcion'];
                $despacho->despacho_monto_modificado = ($cliente['montoSeleccionado'] ?: 0);
                $despacho->despacho_estado_modificado = $cliente['montoOriginal'] != $cliente['montoSeleccionado'] ? 1 : 0;
                $despacho->despacho_descripcion_modificado = $cliente['montoSeleccionadoDescripcion'];
                $despacho->despacho_estado = 1;
                $despacho->despacho_microtime = $micro;

                $existecap = DB::table('tarifarios')
                    ->where('id_tarifario', $cliente['id_tarifario'])
                    ->select('tarifa_cap_min', 'tarifa_cap_max')
                    ->first();
                $despacho->despacho_cap_min = $existecap->tarifa_cap_min;
                $despacho->despacho_cap_max = $existecap->tarifa_cap_max;

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
                    $despachoVenta->id_guia = $comprobantesClien['id_guia'];
                    $despachoVenta->despacho_detalle_estado = 1;
                    $despachoVenta->despacho_detalle_microtime = microtime(true);
                    $despachoVenta->despacho_detalle_estado_entrega = 0;

                    if (!$despachoVenta->save()) {
                        DB::rollBack();
                        session()->flash('error', "Error al guardar las facturas para el cliente.");
                        return;
                    }
                }
                // Actualizar el estado de las guías a 4
                $idsGuias = array_column($cliente['comprobantes'], 'id_guia');
                DB::table('guias')
                    ->whereIn('id_guia', $idsGuias)
                    ->update(['guia_estado_aprobacion' => 4, 'updated_at' => now('America/Lima')]);
                // Guardar en historial_guias
                foreach ($cliente['comprobantes'] as $factura) {
                    // Obtener el número de documento de la guía
                    $guia = DB::table('guias')
                        ->where('id_guia', $factura['id_guia'])
                        ->first();

                    if ($guia) {
                        // Insertar en historial_guias
                        DB::table('historial_guias')->insert([
                            'id_users' => Auth::id(),
                            'id_guia' => $factura['id_guia'],
                            'guia_nro_doc' => $guia->guia_nro_doc,
                            'historial_guia_estado_aprobacion' => 4,
                            'historial_guia_fecha_hora' => Carbon::now('America/Lima'),
                            'historial_guia_estado' => 1,
                            'created_at' => Carbon::now('America/Lima'),
                            'updated_at' => Carbon::now('America/Lima'),
                        ]);
                    }
                }
            }

            DB::commit();
            if ($this->id_programacion_edit && $this->id_despacho_edit){
                return redirect()->route('Programacioncamion.programacion_pendientes')->with('success', '¡Registro actualizado correctamente!');
            }else{
                session()->flash('success', 'Registro guardado correctamente.');
                $this->reiniciar_campos();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }


    public function reiniciar_campos(){
        $this->searchFacturaCliente = '';
        $this->filteredFacturasYClientes = [];
        $this->detalle_vehiculo = [];
        $this->selectedFacturas = [];
        $this->pesoTotal = 0;
        $this->volumenTotal = 0;
        $this->importeTotalVenta = 0;
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
        $this->checkInput = null;
        $this->id_tarifario_seleccionado = '';
        $this->clientes_provinciales = [];
        $this->otros_gastos = "";
        $this->montoSelect = "";
    }


}
