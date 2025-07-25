<?php

namespace App\Livewire\Programacioncamiones;
use App\Models\Guia;
use App\Models\Serviciotransporte;
use Carbon\Carbon;
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
use App\Models\General;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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
    private $general;
    private $guia;
    private $serviciotransporte;
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
        $this->general = new General();
        $this->guia = new Guia();
        $this->serviciotransporte = new Serviciotransporte();
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
    public $despacho_descripcion_otros = '';
    public $desde;
    public $hasta;
    public $montoOriginal = 0;
    public $importeTotalVenta = 0;
    public $despacho_descripcion_modificado = '';
    public $id_programacion_edit = '';
    public $id_despacho_edit = '';
    public $guias_estado_tres = [];
    public $searchGuia = [];
    public $serv_transp = [];
    public $selectedServTrns = [];
    public $guiainfo = [];
    public $guia_detalle = [];
    public $checkInput = '';
    public function mount($id = null){
        $this->selectedCliente = null;
        $this->selectedTarifario = null;
        $this->id_transportistas = null;
        $this->id_departamento = null;
        $this->id_provincia = null;
        $this->id_distrito = null;
        $this->programacion_fecha = now()->format('Y-m-d');
        $this->desde = null;
        $this->hasta = null;
        if ($this->selectedCliente) {
            $this->buscar_comprobante();
        }
        if ($id){
            $this->id_programacion_edit = $id;
            $despachoEdit = DB::table('despachos')->where('id_programacion','=',$id)->first();
            if ($despachoEdit){
                $this->id_despacho_edit = $despachoEdit->id_despacho;
                $this->listar_informacion_programacion_edit();
            }
        }
    }
    public function render(){
        // listar transportistas que coinciden con las tarifas
        if (count($this->tarifariosSugeridos) > 0){
            // Obtener los id_transportistas únicos de la colección
            $idsTransportistas = $this->tarifariosSugeridos->pluck('id_transportistas')->unique();
            // Consultar la base de datos para traer transportistas únicos
            $listar_transportistas = DB::table('transportistas')
                ->whereIn('id_transportistas', $idsTransportistas)
                ->get();
        }else{
            $listar_transportistas = $this->transportista->listar_transportista_sin_id();
        }
        $listar_departamento = $this->departamento->lista_departamento();

        // Obtener las guías con estado 3
        $guiasQuery = Guia::wherein('guia_estado_aprobacion', [3, 11, 10])
            ->where('guia_departamento','!=', 'LIMA')
            ->where('guia_departamento','!=', 'CALLAO');

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

        $servTransp = Serviciotransporte::where('serv_transpt_estado_aprobacion', 0)
            ->where('id_departamento', '!=', 15);
        $servicio = $servTransp->get();
        $this->serv_transp = $servicio;

        return view('livewire.programacioncamiones.provincial', compact('listar_transportistas', 'listar_departamento'));
    }
    public function listar_informacion_programacion_edit(){
        $informacionPrograma = $this->programacion->informacion_id($this->id_programacion_edit);
        $informacionDespacho = $this->despacho->listar_despachos_por_programacion($this->id_programacion_edit);
        if ($informacionPrograma && $informacionDespacho){
            $this->id_transportistas = $informacionDespacho[0]->id_transportistas;
            $this->programacion_fecha = $informacionPrograma->programacion_fecha;
            $comprobantes = DB::table('despacho_ventas')
                ->where('id_despacho','=',$informacionDespacho[0]->id_despacho)
                ->get();
            // Array temporal para evitar duplicados
            $guiasAgregadas = [];

            foreach ($comprobantes as $c) {
                // Verificar si el id_guia ya fue agregado
                if (in_array($c->id_guia, $guiasAgregadas)) {
                    continue; // Saltar si ya existe
                }

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

                    // Agregar la guía a selectedFacturas
                    $this->selectedFacturas[] = [
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
                        'guia_importe_total' => $guia->guia_importe_total,
                        'guia_importe_total_sin_igv' => $guia->guia_importe_total_sin_igv,
                        'guia_departamento' => $guia->guia_departamento,
                        'guia_provincia' => $guia->guia_provincia,
                        'guia_destrito' => $guia->guia_destrito,
                    ];

                    // Marcar el id_guia como agregado
                    $guiasAgregadas[] = $c->id_guia;

                    // Sumar al peso y volumen total
                    $this->pesoTotal += $pesoTotalKilos;
                    $this->volumenTotal += $volumenTotal;

                    // Sumar al importe total
                    $importe = floatval($guia->guia_importe_total_sin_igv);
                    $this->importeTotalVenta += $importe;
                }
            }

            // Obtener los servicios de transporte asociados al despacho
            $serviciosTransporte = DB::table('despacho_ventas')
                ->where('id_despacho', '=', $informacionDespacho[0]->id_despacho)
                ->get();

            foreach ($serviciosTransporte as $servicio) {
                // Obtener los datos del servicio de transporte desde la tabla servicios_transporte
                $servTrn = DB::table('servicios_transportes')
                    ->where('id_serv_transpt', '=', $servicio->id_serv_transpt)
                    ->first();

                if ($servTrn) {
                    // Agregar el servicio de transporte a selectedServTrns
                    $this->selectedServTrns[] = [
                        'id_serv_transpt' => $servTrn->id_serv_transpt,
                        'serv_transpt_motivo' => $servTrn->serv_transpt_motivo,
                        'serv_transpt_detalle_motivo' => $servTrn->serv_transpt_detalle_motivo,
                        'serv_transpt_remitente_ruc' => $servTrn->serv_transpt_remitente_ruc,
                        'serv_transpt_remitente_razon_social' => $servTrn->serv_transpt_remitente_razon_social,
                        'serv_transpt_remitente_direccion' => $servTrn->serv_transpt_remitente_direccion,
                        'serv_transpt_destinatario_ruc' => $servTrn->serv_transpt_destinatario_ruc,
                        'serv_transpt_destinatario_razon_social' => $servTrn->serv_transpt_destinatario_razon_social,
                        'serv_transpt_destinatario_direccion' => $servTrn->serv_transpt_destinatario_direccion,
                        'serv_transpt_codigo' => $servTrn->serv_transpt_codigo,
                        'peso_total_st' => $servTrn->serv_transpt_peso,
                        'volumen_total_st' => $servTrn->serv_transpt_volumen,
                    ];

                    // Sumar al peso y volumen total
                    $this->pesoTotal += $servTrn->serv_transpt_peso;
                    $this->volumenTotal += $servTrn->serv_transpt_volumen;
                }
            }

            $montoModifi = floatval($informacionDespacho[0]->despacho_monto_modificado);
            if ($montoModifi){
                $this->tarifaMontoSeleccionado = $montoModifi;
            }
            $this->montoOriginal = floatval($informacionDespacho[0]->despacho_flete);
            $this->despacho_descripcion_modificado = $informacionDespacho[0]->despacho_descripcion_modificado;
            $this->despacho_gasto_otros = $informacionDespacho[0]->despacho_gasto_otros;
            $this->despacho_descripcion_otros = $informacionDespacho[0]->despacho_descripcion_otros;
            $this->despacho_ayudante = $informacionDespacho[0]->despacho_ayudante;
            if ($informacionDespacho[0]->id_departamento){
                $this->id_departamento = $informacionDespacho[0]->id_departamento;
                $this->listar_provincias();
                if ($informacionDespacho[0]->id_provincia){
                    $this->id_provincia = $informacionDespacho[0]->id_provincia;
                    $this->listar_distritos();
                    if ($informacionDespacho[0]->id_distrito){
                        $this->id_distrito = $informacionDespacho[0]->id_distrito;
                    }
                }
            }
            $id_despacho = $informacionDespacho[0]->id_tarifario;
            $this->selectedTarifario = $id_despacho;

            $this->calcularCostoTotal();
            // Actualizar lista de vehículos sugeridos
            $this->listar_tarifarios_su();
            $this->validarTarifaSeleccionada();
            $this->buscar_comprobante();
        }
    }
    public function listar_provincias(){
        $valor = $this->id_departamento;
        if ($valor) {
            $this->provincias = DB::table('provincias')->where('id_departamento', '=', $valor)->get();
        } else {
            $this->provincias = [];
            $this->id_provincia = "";
            $this->distritos = [];
            $this->id_distrito = "";
        }
    }

    public function listar_distritos(){
        $valor = $this->id_provincia;
        if ($valor) {
            $this->distritos = DB::table('distritos')->where('id_provincia', '=', $valor)->get();
        } else {
            $this->distritos = [];
            $this->id_distrito = "";
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
        $cliente = collect($this->filteredClientes)->firstWhere('CCODCLI', (int) $clienteId);
        if ($cliente) {
            $this->selectedCliente = $cliente->CCODCLI;
            $this->select_nombre_cliente = $cliente->CNOMCLI;
            $this->searchCliente = "";
            $this->searchComprobante = "";
            $this->filteredClientes = [];
//            $this->buscar_comprobante();
        } else {
            $this->resetear_cliente();
        }
    }
    public function limpiar_cliente($clienteId){
        $this->selectedCliente = '';
        $this->select_nombre_cliente = '';
        $this->searchCliente = '';
        $this->searchComprobante = '';
        $this->filteredClientes = [];
        $this->filteredComprobantes = [];
        $this->id_transportistas  = "";
        $this->id_departamento  = "";
        $this->id_provincia  = "";
        $this->id_distrito  = "";
        $this->pesoTotal  = 0;
        $this->volumenTotal  = 0;
        $this->tarifariosSugeridos = [];
        $this->selectedFacturas = [];
        $this->desde = null;
        $this->hasta = null;
    }
    public function buscar_comprobante() {
        if ($this->selectedCliente) {
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

            $comprobantes = $this->server->listar_comprobantes_por_cliente($this->selectedCliente, $this->searchComprobante, $this->desde, $this->hasta);
            // Si no hay comprobantes, asignar un array vacío
            if (!$comprobantes) {
                $this->filteredComprobantes = [];
            } else {
                $this->filteredComprobantes =  $comprobantes;
            }
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

    public function seleccionar_factura_cliente($id_guia){
        // Buscar la factura por su ID
        $factura = Guia::find($id_guia);

        if (!$factura) {
            session()->flash('error', 'Guía no encontrada.');
            return;
        }

        // Validar que la factura no esté ya en el array selectedFacturas
        $comprobanteExiste = collect($this->selectedFacturas)->first(function ($facturaSeleccionada) use ($factura) {
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
        if ($pesoTotalKilos <= 0 || $volumenTotal <= 0) {
            session()->flash('error', 'El peso o el volumen deben ser mayores a 0. Verifique los detalles de la guía.');
            return;
        }

        if (count($this->selectedFacturas) > 0){
            // Obtiene el primer comprobante seleccionado
            $primerComprobante = $this->selectedFacturas[0];

            $esUbigeo = $primerComprobante['guia_departamento'] == $factura->guia_departamento && $primerComprobante['guia_provincia'] == $factura->guia_provincia && $primerComprobante['guia_destrito'] == $factura->guia_destrito;
            if ($esUbigeo){
                if (
                    $factura->guia_departamento != $primerComprobante['guia_departamento'] ||
                    $factura->guia_provincia != $primerComprobante['guia_provincia'] ||
                    $factura->guia_destrito != $primerComprobante['guia_destrito']
                ) {
                    $tex = $primerComprobante['guia_departamento'].' - '.$primerComprobante['guia_provincia'].' - '.$primerComprobante['guia_destrito'];
                    session()->flash('error', "No puedes agregar un comprobante con un ubigeo diferente a $tex.");
                    return;
                }
            }else{
                if (
                    $primerComprobante['guia_departamento'] !== $factura->guia_departamento ||
                    $primerComprobante['guia_provincia'] !== $factura->guia_provincia ||
                    $primerComprobante['guia_destrito'] !== $factura->guia_destrito
                ) {
                    session()->flash('error', 'No puedes agregar un comprobante con un ubigeo diferente al del primer comprobante seleccionado.');
                    return;
                }
            }
        }

        // Agregar la factura seleccionada al array
        $this->selectedFacturas[] = [
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

        // Actualizar los totales
        $this->pesoTotal += $pesoTotalKilos;
        $this->volumenTotal += $volumenTotal;

        $importes = $factura->guia_importe_total_sin_igv;
        $importe = floatval($importes);
        $this->importeTotalVenta += $importe;

        // Configurar ubigeo si es la primera guía
        if (count($this->selectedFacturas) == 1) {
            $this->configurarUbigeoDesdeGuia($factura);
        }
        // Actualizar lista de vehículos sugeridos
        $this->listar_tarifarios_su();
        $this->validarTarifaSeleccionada();
    }
    // Nuevo método para configurar ubigeo
    public function configurarUbigeoDesdeGuia($guia) {
        // Normalizar nombres (trim + mayúsculas)
        $departamento = strtoupper(trim($guia->guia_departamento));
        $provincia = strtoupper(trim($guia->guia_provincia));
        $distrito = strtoupper(trim($guia->guia_destrito));

        // Buscar departamento (comparación exacta)
        $departamentoDB = DB::table('departamentos')
            ->whereRaw('UPPER(TRIM(departamento_nombre)) = ?', [$departamento])
            ->first();

        if ($departamentoDB) {
            $this->id_departamento = $departamentoDB->id_departamento;
            $this->listar_provincias();

            // Buscar provincia (exacta y dentro del departamento)
            $provinciaDB = DB::table('provincias')
                ->where('id_departamento', $this->id_departamento)
                ->whereRaw('UPPER(TRIM(provincia_nombre)) = ?', [$provincia])
                ->first();

            if ($provinciaDB) {
                $this->id_provincia = $provinciaDB->id_provincia;
                $this->listar_distritos();

                // Buscar distrito (exacto y dentro de la provincia)
                $distritoDB = DB::table('distritos')
                    ->where('id_provincia', $this->id_provincia)
                    ->whereRaw('UPPER(TRIM(distrito_nombre)) = ?', [$distrito])
                    ->first();

                if ($distritoDB) {
                    $this->id_distrito = $distritoDB->id_distrito;
                } else {
                    $this->id_distrito = null;
                    session()->flash('warning', 'Distrito no encontrado en la base de datos.');
                }
            } else {
                $this->id_provincia = null;
                $this->id_distrito = null;
                session()->flash('warning', 'Provincia no encontrada en el departamento seleccionado.');
            }
        } else {
            $this->id_departamento = null;
            $this->id_provincia = null;
            $this->id_distrito = null;
            session()->flash('warning', 'Departamento no encontrado en la base de datos.');
        }
    }

    public function eliminarFacturaSeleccionada($id_guia) {
        // Convertir id_guia a string para evitar problemas con bigint
        $id_guia = (string)$id_guia;

        // Encuentra la factura en las seleccionadas
        $factura = collect($this->selectedFacturas)->first(function ($f) use ($id_guia) {
            return (string)$f['id_guia'] === $id_guia; // Convertir a string para comparar
        });

        if ($factura) {
            // Elimina la factura de la lista seleccionada
            $this->selectedFacturas = collect($this->selectedFacturas)
                ->reject(function ($f) use ($id_guia) {
                    return (string)$f['id_guia'] === $id_guia; // Convertir a string para comparar
                })
                ->values()
                ->toArray();

            $GuiaUpEstate = Guia::find($id_guia);
            $GuiaUpEstate->guia_estado_aprobacion = 11;
            $GuiaUpEstate->save();

            // Actualiza los totales
            $this->pesoTotal -= $factura['peso_total'];
            $this->volumenTotal -= $factura['volumen_total'];
            $this->importeTotalVenta -= floatval($factura['guia_importe_total_sin_igv']);

            // Verifica si no quedan facturas ni servicios de transporte seleccionados
            if (empty($this->selectedFacturas) && empty($this->selectedServTrns)) {
                $this->pesoTotal = 0;
                $this->volumenTotal = 0;
                $this->importeTotalVenta = 0;

                // Limpiar los campos de ubicación
                $this->id_departamento = "";
                $this->id_provincia = "";
                $this->id_distrito = "";
                $this->id_transportistas = "";

                // Limpiar las listas de provincias y distritos
                $this->provincias = [];
                $this->distritos = [];
            }

            // Actualizar lista de vehículos sugeridos
            $this->listar_tarifarios_su();
            $this->validarTarifaSeleccionada();
        } else {
            \Log::warning("No se encontró la guía con id_guia: $id_guia");
        }
    }

    public function seleccionarServTrns($id_ser_t){
        // Buscar la factura por su ID
        $serv_trn = Serviciotransporte::find($id_ser_t);

        if (!$serv_trn) {
            session()->flash('error', 'Servicio transporte no encontrada.');
            return;
        }

        // Validar que la factura no esté ya en el array selectedFacturas
        $comprobanteExiste = collect($this->selectedServTrns)->first(function ($facturaSeleccionada) use ($serv_trn) {
            return $facturaSeleccionada['id_serv_transpt'] === $serv_trn->id_serv_transpt;
        });

        if ($comprobanteExiste) {
            session()->flash('error', 'Esta guía ya fue agregada.');
            return;
        }

        // Validar que el peso y volumen sean mayores a 0
        if ($serv_trn->serv_transpt_peso <= 0 || $serv_trn->serv_transpt_volumen <= 0) {
            session()->flash('error', 'El peso o el volumen deben ser mayores a 0. Verifique los detalles de la guía.');
            return;
        }

        // Obtener ubigeo de referencia (de guías o servicios ya seleccionados)
        $ubigeoReferencia = $this->obtenerUbigeoReferencia();

        // Si ya hay elementos seleccionados, validar consistencia de ubigeo
        if ($ubigeoReferencia) {
            $departamentoCoincide = $serv_trn->id_departamento == $ubigeoReferencia['id_departamento'];
            $provinciaCoincide = $serv_trn->id_provincia == $ubigeoReferencia['id_provincia'];
            $distritoCoincide = $serv_trn->id_distrito == $ubigeoReferencia['id_distrito'];

            if (!$departamentoCoincide || !$provinciaCoincide || !$distritoCoincide) {
                $textoUbigeo = $this->obtenerTextoUbigeo($ubigeoReferencia);
                session()->flash('error', "El servicio de transporte debe tener el mismo ubigeo ($textoUbigeo) que los documentos ya seleccionados.");
                return;
            }
        }

        // Agregar la factura seleccionada al array
        $this->selectedServTrns[] = [
            'id_serv_transpt' => $serv_trn->id_serv_transpt,
            'serv_transpt_motivo' => $serv_trn->serv_transpt_motivo,
            'serv_transpt_detalle_motivo' => $serv_trn->serv_transpt_detalle_motivo,
            'serv_transpt_remitente_ruc' => $serv_trn->serv_transpt_remitente_ruc,
            'serv_transpt_remitente_razon_social' => $serv_trn->serv_transpt_remitente_razon_social,
            'serv_transpt_remitente_direccion' => $serv_trn->serv_transpt_remitente_direccion,
            'serv_transpt_destinatario_ruc' => $serv_trn->serv_transpt_destinatario_ruc,
            'serv_transpt_destinatario_razon_social' => $serv_trn->serv_transpt_destinatario_razon_social,
            'serv_transpt_destinatario_direccion' => $serv_trn->serv_transpt_destinatario_direccion,
            'serv_transpt_codigo' => $serv_trn->serv_transpt_codigo,
            'id_departamento' => $serv_trn->id_departamento,
            'id_provincia' => $serv_trn->id_provincia,
            'id_distrito' => $serv_trn->id_distrito,
            'peso_total_st' => $serv_trn->serv_transpt_peso,
            'volumen_total_st' => $serv_trn->serv_transpt_volumen,
        ];

        // Actualizar los totales
        $this->pesoTotal += $serv_trn->serv_transpt_peso;
        $this->volumenTotal += $serv_trn->serv_transpt_volumen;

        // Si es el primer elemento, configurar ubigeo
        if (count($this->selectedServTrns) == 1 && empty($this->selectedFacturas)) {
            $this->configurarUbigeoDesdeServicio($serv_trn);
        }

        // Actualizar lista de vehículos sugeridos
        $this->listar_tarifarios_su();
        $this->validarTarifaSeleccionada();
    }
    // Nuevo método para obtener ubigeo de referencia
    public function obtenerUbigeoReferencia() {
        if (!empty($this->selectedFacturas)) {
            $primerFactura = $this->selectedFacturas[0];
            return [
                'id_departamento' => $this->id_departamento,
                'id_provincia' => $this->id_provincia,
                'id_distrito' => $this->id_distrito
            ];
        } elseif (!empty($this->selectedServTrns)) {
            $primerServicio = $this->selectedServTrns[0];
            return [
                'id_departamento' => $primerServicio['id_departamento'],
                'id_provincia' => $primerServicio['id_provincia'],
                'id_distrito' => $primerServicio['id_distrito']
            ];
        }
        return null;
    }
    // Nuevo método para obtener texto descriptivo del ubigeo
    public function obtenerTextoUbigeo($ubigeo) {
        $departamento = DB::table('departamentos')
            ->where('id_departamento', $ubigeo['id_departamento'])
            ->value('departamento_nombre');

        $provincia = DB::table('provincias')
            ->where('id_provincia', $ubigeo['id_provincia'])
            ->value('provincia_nombre');

        $distrito = DB::table('distritos')
            ->where('id_distrito', $ubigeo['id_distrito'])
            ->value('distrito_nombre');

        return "$departamento - $provincia - $distrito";
    }
    // Nuevo método para configurar ubigeo desde servicio
    public function configurarUbigeoDesdeServicio($servicio) {
        $this->id_departamento = $servicio->id_departamento;
        $this->id_provincia = $servicio->id_provincia;
        $this->id_distrito = $servicio->id_distrito;

        // Actualizar listas desplegables
        $this->listar_provincias();
        $this->listar_distritos();
    }

    public function eliminarSerTrnSeleccionada($id_ser_t) {
        // Convertir id_ser_t a string para evitar problemas con bigint
        $id_ser_t = (string)$id_ser_t;

        // Encuentra el servicio de transporte en las seleccionadas
        $servTrn = collect($this->selectedServTrns)->first(function ($f) use ($id_ser_t) {
            return (string)$f['id_serv_transpt'] === $id_ser_t;
        });

        if ($servTrn) {
            // Elimina el servicio de transporte de la lista seleccionada
            $this->selectedServTrns = collect($this->selectedServTrns)
                ->reject(function ($f) use ($id_ser_t) {
                    return (string)$f['id_serv_transpt'] === $id_ser_t;
                })
                ->values()
                ->toArray();

            // Actualiza los totales
            $this->pesoTotal -= $servTrn['peso_total_st'];
            $this->volumenTotal -= $servTrn['volumen_total_st'];

            // Verifica si no quedan servicios de transporte ni facturas seleccionados
            if (empty($this->selectedServTrns) && empty($this->selectedFacturas)) {
                $this->pesoTotal = 0;
                $this->volumenTotal = 0;
            }

            // Actualizar lista de vehículos sugeridos
            $this->listar_tarifarios_su();
            $this->validarTarifaSeleccionada();
        } else {
            \Log::warning("No se encontró el servicio transporte con id_serv_transpt: $id_ser_t");
        }
    }

    public function validarTarifaSeleccionada() {
        if ($this->selectedTarifario) {
            $tarifaValida = collect($this->tarifariosSugeridos)->first(function ($tarifa) {
                return $tarifa->id_tarifario == $this->selectedTarifario;
            });

            if (!$tarifaValida) {
                $this->selectedTarifario = null;
                $this->tarifaMontoSeleccionado = null;
                $this->costoTotal = null;
            }
        }
    }

    public function modal_detalle_tarifario($id){
        $this->detalle_tarifario =  $this->tarifario->listar_informacion_tarifa($id);
    }
    public function deparTari(){
        $this->id_provincia = "";
        $this->id_distrito = "";
        $this->provincias = [];
        $this->distritos = [];
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

//    public function actualizarVehiculosSugeridos(){
//        $this->listar_tarifarios_su();
//        $this->tarifaMontoSeleccionado = null;
//        $this->selectedTarifario = null;
//    }

    public function seleccionarTarifario($id){
        $vehiculo = collect($this->tarifariosSugeridos)->first(function ($vehiculo) use ($id){
            return $vehiculo->id_tarifario == $id;
        });
        if ($vehiculo) {
            // Actualiza el monto de la tarifa del vehículo seleccionado
            $this->tarifaMontoSeleccionado = $vehiculo->tarifa_monto;
            $this->montoOriginal = $vehiculo->tarifa_monto;
            $this->selectedTarifario = $id;
            $this->despacho_descripcion_modificado = "";
            $this->calcularCostoTotal();
        }
    }

    public function listar_tarifarios_su(){
        $this->tarifariosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_provincial($this->pesoTotal,2,$this->id_transportistas,$this->id_departamento ,$this->id_provincia ,$this->id_distrito);

        // Verificar si el tarifario previamente seleccionado sigue siendo válido
        $tarifaValidar = collect($this->tarifariosSugeridos)->first(function ($tarifa) {
            return $tarifa->id_tarifario == $this->selectedTarifario;
        });
        if ($tarifaValidar){
            if (!$this->id_despacho_edit && !$this->id_programacion_edit){
                $this->tarifaMontoSeleccionado = $tarifaValidar->tarifa_monto;
                $this->selectedTarifario = $tarifaValidar->id_tarifario;
                $this->calcularCostoTotal();
            }
        } else {
            // Limpiar valores relacionados
            $this->tarifaMontoSeleccionado = null;
            $this->selectedTarifario = null;
            $this->costoTotal = null;
        }
    }

    public function calcularCostoTotal(){
        $montoSeleccionado = floatval($this->tarifaMontoSeleccionado);
        $otros = floatval($this->despacho_gasto_otros);

        $this->costoTotal = ($montoSeleccionado * $this->pesoTotal) + $otros;
    }

    public function modal_guia_info($id_guia) {
        $this->guiainfo = $this->guia->listar_guia_x_id($id_guia);
    }
    public function listar_detalle_guia($id_guia) {
        $this->guia_detalle = $this->guia->listar_guia_detalle_x_id($id_guia);
    }

    public function guardarDespachos(){
        try {
            if (!Gate::allows('guardar_despacho_provincial')) {
                session()->flash('error', 'No tiene permisos para crear una programación provincial.');
                return;
            }
            $this->validate([
                'id_tipo_servicios' => 'nullable|integer',
                'id_transportistas' => 'required|integer',
                'selectedTarifario' => 'required|integer',
                'selectedFacturas' => 'nullable|array',
                'selectedServTrns' => 'nullable|array',
                'id_departamento' => 'required|integer',
                'id_provincia' => 'required|integer',
                'id_distrito' => 'nullable|integer',
                'despacho_peso' => 'nullable|numeric',
                'despacho_volumen' => 'nullable|numeric',
                'despacho_flete' => 'nullable|numeric',
                'despacho_ayudante' => 'nullable|regex:/^[0-9]+(\.[0-9]+)?$/',
                'despacho_gasto_otros' => 'nullable|regex:/^[0-9]+(\.[0-9]+)?$/',
                'despacho_descripcion_otros' => $this->despacho_gasto_otros > 0 ? 'required|string' : 'nullable|string',
//                'despacho_descripcion_modificado' => $this->tarifaMontoSeleccionado != $this->montoOriginal ? 'required|string' : 'nullable|string',
                ], [
                'selectedTarifario.required' => 'Debes seleccionar una tarifa.',
                'selectedTarifario.integer' => 'La tarifa debe ser un número entero.',

                'id_transportistas.required' => 'Debes seleccionar un transportista.',
                'id_transportistas.integer' => 'El transportista debe ser un número entero.',

                'id_departamento.required' => 'Debes seleccionar un departamento.',
                'id_departamento.integer' => 'El departamento debe ser un número entero.',

                'id_provincia.required' => 'Debes seleccionar una provincia.',
                'id_provincia.integer' => 'La provincia debe ser un número entero.',

                'despacho_ayudante.regex' => 'El ayudante debe ser un número válido.',
                'despacho_gasto_otros.regex' => 'El gasto en otros debe ser un número válido.',

                'despacho_descripcion_otros.required' => 'La descripción de gastos adicionales es requerida cuando se ingresa un monto.',
                'despacho_descripcion_otros.string' => 'La descripción debe ser una cadena de texto.',

//                'despacho_descripcion_modificado.required' => 'La descripción por modificar el monto es obligatorio.',
            ]);
            $contadorError = 0;
            DB::beginTransaction();
            if ($this->id_programacion_edit && $this->id_despacho_edit){
                // se va a eliminar los comprobantes del anterior registro
                DB::table('despacho_ventas')->where('id_despacho','=',$this->id_despacho_edit)->delete();
            }
            $microtimeCread = microtime(true);
            // Validar duplicidad para las facturas seleccionadas
            foreach ($this->selectedFacturas as $factura) {
                $existe = DB::table('despacho_ventas as dv')
                    ->join('despachos as d','d.id_despacho','=','dv.id_despacho')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->where('d.despacho_estado_aprobacion', '<>', 4)
                    ->where('dv.id_guia', $factura['id_guia'])
                    ->whereIn('g.guia_estado_aprobacion', [7, 8])
                    ->orderBy('dv.id_despacho_venta', 'desc')
                    ->exists();
                if ($existe) {
                    $contadorError++;
                }
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
                session()->flash('error', "Se encontraron comprobantes duplicadas. Por favor, verifica.");
                DB::rollBack();
                return;
            }

            // VALIDAR COMENTARIO
            if ($this->tarifaMontoSeleccionado != $this->montoOriginal && empty($this->despacho_descripcion_modificado)) {
                session()->flash('error', "La descripción por modificar el monto es obligatorio cuando se cambia el valor original.");
                DB::rollBack();
                return;
            }

            if ($this->id_programacion_edit && $this->id_despacho_edit){
                // actualizar en la tabla Programaciones
                $programacion = Programacion::find($this->id_programacion_edit);
            }else{
                // Guardar en la tabla Programaciones
                $programacion = new Programacion();
                $programacion->id_users = Auth::id();
            }
            $programacion->programacion_fecha = $this->programacion_fecha;
            $programacion->programacion_estado_aprobacion = 0;
            $programacion->programacion_estado = 1;
            $programacion->programacion_microtime = $microtimeCread;
            if (!$programacion->save()) {
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar la programación.');
                return;
            }
            $programacionCreada =  DB::table('programaciones')->where('programacion_microtime','=',$microtimeCread)->first();

            if ($this->id_programacion_edit && $this->id_despacho_edit){
                // actulizar el despacho
                $despacho = Despacho::find($this->id_despacho_edit);
            }else{
                // Guardar el despacho
                $despacho = new Despacho();
                $despacho->id_users = Auth::id();
            }
            $despacho->id_programacion = $programacionCreada->id_programacion;
            $despacho->id_transportistas = $this->id_transportistas;
            $despacho->id_tipo_servicios = 2;
            $despacho->id_tarifario = $this->selectedTarifario;
            $despacho->id_departamento = $this->id_departamento;
            $despacho->id_provincia = $this->id_provincia;
            $despacho->id_distrito = $this->id_distrito ?: null;
            $despacho->despacho_peso = $this->pesoTotal;
            $despacho->despacho_volumen = $this->volumenTotal;
            $despacho->despacho_flete = $this->montoOriginal;
            $despacho->despacho_gasto_otros = $this->despacho_gasto_otros ?: null;
            // Calcular despacho_costo_total
            $despacho_costo_total = $this->tarifaMontoSeleccionado * $this->pesoTotal;
            if (!empty($this->despacho_gasto_otros)) {
                $despacho_costo_total += $this->despacho_gasto_otros;
            }
            $despacho->despacho_costo_total = $despacho_costo_total;
            $despacho->despacho_estado_aprobacion = 0;
            $despacho->despacho_descripcion_otros = $this->despacho_gasto_otros > 0 ? $this->despacho_descripcion_otros : null;
            $despacho->despacho_monto_modificado = $this->tarifaMontoSeleccionado ?: null;
            $despacho->despacho_estado_modificado = $this->tarifaMontoSeleccionado != $this->montoOriginal ? 1 : 0;
            $despacho->despacho_descripcion_modificado = ($this->tarifaMontoSeleccionado != $this->montoOriginal) ? $this->despacho_descripcion_modificado : null;            $despacho->despacho_estado = 1;
            $despacho->despacho_microtime = $microtimeCread;

            $existecap = DB::table('tarifarios')
                ->where('id_tarifario', $this->selectedTarifario)
                ->select('tarifa_cap_min', 'tarifa_cap_max')
                ->first();
            $despacho->despacho_cap_min = $existecap->tarifa_cap_min;
            $despacho->despacho_cap_max = $existecap->tarifa_cap_max;

            if (!$despacho->save()) {
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar el registro.');
                return;
            }
            $ultimoDespacho = DB::table('despachos')->where('despacho_microtime','=',$microtimeCread)->first();

            // Guardar facturas seleccionadas en despacho_ventas
            foreach ($this->selectedFacturas as $factura) {
                // Crear un nuevo registro en despacho_ventas
                $despachoVenta = new DespachoVenta();
                $despachoVenta->id_despacho = $ultimoDespacho->id_despacho;
                $despachoVenta->id_guia = $factura['id_guia']; // Guardar solo id_guia
                $despachoVenta->despacho_detalle_estado = 1;
                $despachoVenta->despacho_detalle_microtime = microtime(true);
                $despachoVenta->despacho_detalle_estado_entrega = 0;

                if (!$despachoVenta->save()) {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar las facturas.');
                    return;
                }
            }
            // Guardar servicios de transporte seleccionados en despacho_ventas (si existen)
            if (!empty($this->selectedServTrns)) {
                foreach ($this->selectedServTrns as $servTrn) {
                    // Crear un nuevo registro en despacho_ventas sin id_guia
                    $despachoServicio = new DespachoVenta();
                    $despachoServicio->id_despacho = $ultimoDespacho->id_despacho;
                    $despachoServicio->id_serv_transpt = $servTrn['id_serv_transpt']; // Guardar id_serv_transpt
                    $despachoServicio->despacho_detalle_estado = 1;
                    $despachoServicio->despacho_detalle_microtime = microtime(true);
                    $despachoServicio->despacho_detalle_estado_entrega = 0;

                    if (!$despachoServicio->save()) {
                        DB::rollBack();
                        session()->flash('error', 'Ocurrió un error al guardar los servicios de transporte.');
                        return;
                    }
                }
            }
            // Actualizar el estado de las guías a 4
            if (!empty($this->selectedFacturas)) {
                $idsGuias = array_column($this->selectedFacturas, 'id_guia');
                DB::table('guias')
                    ->whereIn('id_guia', $idsGuias)
                    ->update(['guia_estado_aprobacion' => 4, 'updated_at' => now('America/Lima')]);
                // Guardar en historial_guias
                foreach ($this->selectedFacturas as $factura) {
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
                            'historial_guia_fecha_hora' => $programacion->programacion_fecha,
                            'historial_guia_estado' => 1,
                            'created_at' => Carbon::now('America/Lima'),
                            'updated_at' => Carbon::now('America/Lima'),
                        ]);
                    }
                }
            }

            // Actualizar el estado de los servicios transporte a 1 (solo si hay servicios)
            if (!empty($this->selectedServTrns)) {
                $idsSerTr = array_column($this->selectedServTrns, 'id_serv_transpt');
                DB::table('servicios_transportes')
                    ->whereIn('id_serv_transpt', $idsSerTr)
                    ->update(['serv_transpt_estado_aprobacion' => 1]);
            }

            DB::commit();
            if ($this->id_programacion_edit && $this->id_despacho_edit){
                return redirect()->route('Programacioncamion.programacion_pendientes')->with('success', '¡Registro actualizado correctamente!');
            }else{
                session()->flash('success', 'Registro guardado correctamente.');
                $this->reiniciar_campos();
            }
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
        $this->importeTotalVenta = 0;
        $this->tarifaMontoSeleccionado = 0;
        $this->tarifariosSugeridos = [];
        $this->selectedTarifario = "";
        $this->id_departamento = "";
        $this->id_provincia = "";
        $this->id_distrito = "";
        $this->selectedFacturas = [];
        $this->selectedServTrns = [];
        $this->detalle_tarifario = [];
        $this->comprobantes = [];
        $this->select_nombre_cliente = null;
        $this->searchComprobante = '';
        $this->filteredComprobantes = [];
        $this->programacion_fecha = now()->format('Y-m-d');
        $this->desde = date('Y-m-d', strtotime('-1 month'));
        $this->hasta = date('Y-m-d');
        $this->despacho_ayudante = '';
        $this->despacho_gasto_otros = '';
        $this->id_tipo_servicios = null;
        $this->despacho_peso = null;
        $this->despacho_volumen = null;
        $this->despacho_flete = null;
        $this->id_tarifario = null;
        $this->despacho_descripcion_otros = '';
        $this->despacho_descripcion_modificado = '';
        $this->costoTotal = 0;
        $this->montoOriginal = 0;
    }


}
