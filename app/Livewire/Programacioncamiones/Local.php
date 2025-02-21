<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\General;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Logs;
use App\Models\Server;
use App\Models\Transportista;
use App\Models\Vehiculo;
use App\Models\Programacion;
use App\Models\Despacho;
use App\Models\DespachoVenta;
use App\Models\Facturaspreprogramacion;
use App\Models\Historialdespachoventa;
use App\Models\Historialpreprogramacion;


class Local extends Component
{
    private $logs;
    private $server;
    private $transportista;
    private $vehiculo;
    private $programacion;
    private $despacho;
    private $despachoventa;
    private $general;
    private $facpreprog;
    private $historialdespachoventa;
    private $historialpreprogramacion;
    public function __construct(){
        $this->logs = new Logs();
        $this->server = new Server();
        $this->transportista = new Transportista();
        $this->vehiculo = new Vehiculo();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->despachoventa = new DespachoVenta();
        $this->general = new General();
        $this->facpreprog = new Facturaspreprogramacion();
        $this->historialdespachoventa = new Historialdespachoventa();
        $this->historialpreprogramacion = new Historialpreprogramacion();
    }
    public $searchFactura = "";
    public $filteredFacturas = [];
    public $id_transportistas = "";
    public $vehiculosSugeridos = [];
    public $selectedVehiculo = "";
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $importeTotalVenta = 0;
    public $selectedFacturas = [];
    public $detalle_vehiculo = [];
    public $tarifaMontoSeleccionado = 0;
    public $montoOriginal = 0;
    public $programacion_fecha = '';
    public $despacho_ayudante = '';
    public $despacho_gasto_otros = '';
    public $id_tipo_servicios;
    public $despacho_peso;
    public $despacho_volumen;
    public $despacho_flete;
    public $id_tarifario;
    public $costoTotal = 0;
    public $id_tarifario_seleccionado = '';
    public $desde;
    public $hasta;
    public $despacho_monto_modificado = '';
    public $despacho_descripcion_modificado = '';
    public $importeTotal = 0;
    public $ratioCostoVenta = 0;
    public $ratioCostoPeso = 0;
    public $despacho_descripcion_otros = '';
    public $id_programacion_edit = '';
    public $id_despacho_edit = '';
    public $checkInput = '';
    public $facturas_pre_prog_estado_tres = [];
    public function mount($id = null){
        $this->id_transportistas = null;
        $this->selectedVehiculo = null;
        $this->programacion_fecha = now()->format('Y-m-d');
        $this->desde = null;
        $this->hasta = null;
        /**/
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
        if (count($this->vehiculosSugeridos) > 0){
            // Obtener los id_transportistas únicos de la colección
            $idsTransportistas = $this->vehiculosSugeridos->pluck('id_transportistas')->unique();
            // Consultar la base de datos para traer transportistas únicos
            $listar_transportistas = DB::table('transportistas')
                ->whereIn('id_transportistas', $idsTransportistas)
                ->get();
        }else{
            $listar_transportistas = $this->transportista->listar_transportista_sin_id();
        }
//        $listar_transportistas = $this->transportista->listar_transportista_sin_id();
        $listar_vehiculos = $this->vehiculo->obtener_vehiculos_con_tarifarios();
        $facturas_pre_prog_estado_dos = $this->facpreprog->listar_facturas_pre_programacion_estado_dos();
        $this->facturas_pre_prog_estado_tres = Facturaspreprogramacion::where('fac_pre_prog_estado_aprobacion', 3)->get();
        return view('livewire.programacioncamiones.local', compact('listar_transportistas', 'listar_vehiculos', 'facturas_pre_prog_estado_dos'));
    }

    public $id_fac_pre_prog = "";
    public function listar_informacion_programacion_edit(){
        $informacionPrograma = $this->programacion->informacion_id($this->id_programacion_edit);
        $informacionDespacho = $this->despacho->listar_despachos_por_programacion($this->id_programacion_edit);
        if ($informacionPrograma && $informacionDespacho){
            $this->id_transportistas = $informacionDespacho[0]->id_transportistas;
            $this->programacion_fecha = $informacionPrograma->programacion_fecha;
            $comprobantes = DB::table('despacho_ventas')->where('id_despacho','=',$informacionDespacho[0]->id_despacho)->get();
            foreach ($comprobantes as $c){
                $this->selectedFacturas[] = [
                    'CFTD' => $c->despacho_venta_cftd,
                    'CFNUMSER' => $c->despacho_venta_cfnumser,
                    'CFNUMDOC' => $c->despacho_venta_cfnumdoc,
                    'total_kg' => $c->despacho_venta_total_kg,
                    'total_volumen' => $c->despacho_venta_total_volumen,
                    'CNOMCLI' => $c->despacho_venta_cnomcli,
                    'CCODCLI' => $c->despacho_venta_cfcodcli,
                    'CFIMPORTE' => $c->despacho_venta_cfimporte,
                    'guia' => $c->despacho_venta_guia,
                    'GREFECEMISION' => $c->despacho_venta_grefecemision, // fecha de emision de la guía
                    'LLEGADADIRECCION' => $c->despacho_venta_direccion_llegada,// Dirección de destino
                    'LLEGADAUBIGEO' => null,// Código del ubigeo
                    'DEPARTAMENTO' => $c->despacho_venta_departamento,// Departamento
                    'PROVINCIA' => $c->despacho_venta_provincia,// Provincia
                    'DISTRITO' => $c->despacho_venta_distrito,// Distrito
                ];
                $this->pesoTotal += $c->despacho_venta_total_kg;
                $this->volumenTotal += $c->despacho_venta_total_volumen;
//                $importe = $this->general->formatoDecimal($c->despacho_venta_cfimporte);
//                $this->importeTotalVenta += floatval($importe);
                $importe = $c->despacho_venta_cfimporte;
                $importe = floatval($importe);
                $this->importeTotalVenta += $importe;
            }
            $this->tarifaMontoSeleccionado = $informacionDespacho[0]->despacho_monto_modificado;

            $this->despacho_descripcion_modificado = $informacionDespacho[0]->despacho_descripcion_modificado;
            $this->despacho_gasto_otros = $informacionDespacho[0]->despacho_gasto_otros;
            $this->despacho_descripcion_otros = $informacionDespacho[0]->despacho_descripcion_otros;
            $this->despacho_ayudante = $informacionDespacho[0]->despacho_ayudante;

            $this->montoOriginal = $informacionDespacho[0]->despacho_flete;
            $this->id_tarifario_seleccionado = $informacionDespacho[0]->id_tarifario;
            $this->selectedVehiculo = $informacionDespacho[0]->id_vehiculo;
            $this->checkInput = $informacionDespacho[0]->id_vehiculo.'-'.$informacionDespacho[0]->id_tarifario;
            $this->calcularCostoTotal();
            // Actualizar lista de vehículos sugeridos
            $this->listar_vehiculos_lo();
            $this->validarVehiculoSeleccionado();
        }
    }
//    #[On('buscarCom')]
    public function buscar_comprobantes(){
        // Verificar si ambas fechas están presentes
        if (!empty($this->desde) && !empty($this->hasta)) {
            // Obtener el año de las fechas 'desde' y 'hasta'
            $yearDesde = date('Y', strtotime($this->desde));
            $yearHasta = date('Y', strtotime($this->hasta));

            // Validar que los años sean 2025 o posteriores
            if ($yearDesde < 2024 || $yearHasta < 2024) {
                // Mostrar un mensaje de error si los años no son válidos
                session()->flash('error', 'Las fechas deben ser a partir de 2025.');
                return; // Salir del método si la validación falla
            }
        }

        $datosResult = $this->server->listar_comprobantes_listos_local($this->searchFactura, $this->desde, $this->hasta);
        $this->filteredFacturas = $datosResult;
        if (!$datosResult) {
            $this->filteredFacturas = [];
        }
    }
    /*  public function actualizarVehiculosSugeridos(){
        $this->tarifaMontoSeleccionado = null;
        $this->montoOriginal = null;
        $this->id_tarifario_seleccionado = null;
        $this->selectedVehiculo = null;
        $this->listar_vehiculos_lo();
    }*/

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

    public function seleccionarFactura($idFacPreProg){
        // Buscar la factura por su ID
        $factura = collect($this->facturas_pre_prog_estado_tres)->first(function ($f) use ($idFacPreProg) {
            return $f->id_fac_pre_prog === $idFacPreProg;
        });

        if (!$factura) {
            session()->flash('error', 'Factura no encontrada.');
            return;
        }

        // Validar que la factura no esté ya en el array selectedFacturas
        $comprobanteExiste = collect($this->selectedFacturas)->first(function ($facturaSeleccionada) use ($factura) {
            return $facturaSeleccionada['id_fac_pre_prog'] === $factura->id_fac_pre_prog;
        });

        if ($comprobanteExiste) {
            // Si la factura ya fue agregada, mostrar un mensaje de error
            session()->flash('error', 'Este comprobante ya fue agregado.');
            return;
        }

        // Verificar que el peso y volumen sean mayores a 0
        if ($factura->fac_pre_prog_total_kg <= 0 || $factura->fac_pre_prog_total_volumen <= 0) {
            session()->flash('error', 'El peso o el volumen deben ser mayores a 0.');
            return;
        }

        // Agregar la factura seleccionada al array
        $this->selectedFacturas[] = [
            'id_fac_pre_prog' => $factura->id_fac_pre_prog,
            'CFTD' => $factura->fac_pre_prog_cftd,
            'CFNUMSER' => $factura->fac_pre_prog_cfnumser,
            'CFNUMDOC' => $factura->fac_pre_prog_cfnumdoc,
            'total_kg' => $factura->fac_pre_prog_total_kg,
            'total_volumen' => $factura->fac_pre_prog_total_volumen,
            'CNOMCLI' => $factura->fac_pre_prog_cnomcli,
            'CCODCLI' => $factura->fac_pre_prog_cfcodcli,
            'CFIMPORTE' => $factura->fac_pre_prog_cfimporte,
            'guia' => $factura->fac_pre_prog_guia,
            'GREFECEMISION' => $factura->fac_pre_prog_grefecemision,
            'LLEGADADIRECCION' => $factura->fac_pre_prog_direccion_llegada,
            'DEPARTAMENTO' => $factura->fac_pre_prog_departamento,
            'PROVINCIA' => $factura->fac_pre_prog_provincia,
            'DISTRITO' => $factura->fac_pre_prog_distrito,
        ];

        // Actualizar los totales
        $this->pesoTotal += $factura->fac_pre_prog_total_kg;
        $this->volumenTotal += $factura->fac_pre_prog_total_volumen;

        $importes = $factura->fac_pre_prog_cfimporte;
        $importe = floatval($importes);
        $this->importeTotalVenta += $importe;

        // Actualizar lista de vehículos sugeridos
        $this->listar_vehiculos_lo();
        $this->validarVehiculoSeleccionado();
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
            $this->importeTotalVenta =  $this->importeTotalVenta - $factura['CFIMPORTE'];

            // Verifica si no quedan facturas seleccionadas
            if (empty($this->selectedFacturas)) {
                $this->pesoTotal = 0;
                $this->volumenTotal = 0;
            }

            $this->listar_vehiculos_lo();
            $this->validarVehiculoSeleccionado();
        }
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

    public function modal_por_vehiculo($id_ve){
        $this->detalle_vehiculo =  $this->vehiculo->listar_informacion_vehiculo($id_ve);
    }

    public function listar_vehiculos_lo(){
        $this->vehiculosSugeridos = $this->vehiculo->obtener_vehiculos_con_tarifarios_local($this->pesoTotal, $this->volumenTotal, 1, $this->id_transportistas);

        // Verificar si el vehículo previamente seleccionado sigue siendo válido
        $vehiculoValido = collect($this->vehiculosSugeridos)->first(function ($vehiculo) {
            return $vehiculo->id_vehiculo == $this->selectedVehiculo &&
                $vehiculo->id_tarifario == $this->id_tarifario_seleccionado;
        });

        if ($vehiculoValido) {
            // Mantener el vehículo seleccionado y el monto
            if (!$this->id_despacho_edit && !$this->id_programacion_edit){
                $this->tarifaMontoSeleccionado = $vehiculoValido->tarifa_monto;
                $this->selectedVehiculo = $vehiculoValido->id_vehiculo;
                $this->id_tarifario_seleccionado = $vehiculoValido->id_tarifario;
                $this->checkInput = $vehiculoValido->id_vehiculo.'-'.$vehiculoValido->id_tarifario;
                $this->calcularCostoTotal();
            }
        } else {
            // Limpiar selección si no es válida
            $this->tarifaMontoSeleccionado = null;
            $this->selectedVehiculo = null;
            $this->id_tarifario_seleccionado = null;
            $this->costoTotal = null;
            $this->checkInput = null;
        }
    }

    public function calcularCostoTotal(){
        $montoSeleccionado = floatval($this->tarifaMontoSeleccionado);
        $ayudante = floatval($this->despacho_ayudante);
        $otros = floatval($this->despacho_gasto_otros);

        $this->costoTotal = $montoSeleccionado + $ayudante + $otros;
    }

    public function guardarDespachos(){
        try {
            if (!Gate::allows('guardar_despacho_local')) {
                session()->flash('error', 'No tiene permisos para crear una programación local.');
                return;
            }
            $this->validate([
                'id_tipo_servicios' => 'nullable|integer',
                'id_transportistas' => 'required|integer',
                'selectedVehiculo' => 'required|integer',
                'selectedFacturas' => 'required|array|min:1',
                'despacho_peso' => 'nullable|numeric',
                'despacho_volumen' => 'nullable|numeric',
                'despacho_flete' => 'nullable|numeric',
                'despacho_ayudante' => 'nullable|regex:/^[0-9]+(\.[0-9]+)?$/',
                'despacho_gasto_otros' => 'nullable|regex:/^[0-9]+(\.[0-9]+)?$/',
                'despacho_descripcion_otros' => $this->despacho_gasto_otros > 0 ? 'required|string' : 'nullable|string',
                'despacho_descripcion_modificado' => $this->tarifaMontoSeleccionado !== $this->montoOriginal ? 'required|string' : 'nullable|string',
            ], [
                'selectedVehiculo.required' => 'Debes seleccionar un vehículo.',
                'selectedVehiculo.integer' => 'El vehículo debe ser un número entero.',

                'id_transportistas.required' => 'Debes seleccionar un transportista.',
                'id_transportistas.integer' => 'El transportista debe ser un número entero.',

                'selectedFacturas.required' => 'Debes seleccionar al menos un comprobante.',
                'selectedFacturas.array' => 'Los comprobantes deben ser un arreglo.',
                'selectedFacturas.min' => 'Debes seleccionar al menos un comprobante.',

                'despacho_ayudante.regex' => 'El ayudante debe ser un número válido.',
                'despacho_gasto_otros.regex' => 'El gasto en otros debe ser un número válido.',

                'despacho_descripcion_otros.required' => 'La descripción de gastos adicionales es requerida cuando se ingresa un monto.',
                'despacho_descripcion_otros.string' => 'La descripción debe ser una cadena de texto.',

                'despacho_descripcion_modificado.required' => 'La descripción por modificar el monto es obligatorio.',
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
                    ->where('d.despacho_estado_aprobacion','<>',4)
                    ->where('dv.despacho_venta_cftd', $factura['CFTD'])
                    ->where('dv.despacho_venta_cfnumser', $factura['CFNUMSER'])
                    ->where('dv.despacho_venta_cfnumdoc', $factura['CFNUMDOC'])
                    ->whereIn('dv.despacho_detalle_estado_entrega', [0,1,2])
                    ->orderBy('dv.id_despacho_venta', 'desc')
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
            $programacion->programacion_microtime = $microtimeCread;
            if (!$programacion->save()) {
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar la programación.');
                return;
            }
            $programacionCreada =  DB::table('programaciones')->where('programacion_microtime','=',$microtimeCread)->first();
            // Guardar el despacho
            if ($this->id_programacion_edit && $this->id_despacho_edit){
                // se va a eliminar los comprobantes del anterior registro
                $despacho = Despacho::find($this->id_despacho_edit);
            }else{
                $despacho = new Despacho();
                $despacho->id_users = Auth::id();
            }
            $despacho->id_programacion = $programacionCreada->id_programacion;
            $despacho->id_transportistas = $this->id_transportistas;
            $despacho->id_tipo_servicios = 1;
            $despacho->id_vehiculo = $this->selectedVehiculo;
            $despacho->id_tarifario = $this->id_tarifario_seleccionado;
            $despacho->despacho_peso = $this->pesoTotal;
            $despacho->despacho_volumen = $this->volumenTotal;
            $despacho->despacho_flete = $this->montoOriginal;
            $despacho->despacho_ayudante = $this->despacho_ayudante ?: null;
            $despacho->despacho_gasto_otros = $this->despacho_gasto_otros ?: null;
            $despacho->despacho_costo_total = $this->tarifaMontoSeleccionado +
                ($this->despacho_ayudante ?: 0) + ($this->despacho_gasto_otros ?: 0);
            $despacho->despacho_estado_aprobacion = 0;
            $despacho->despacho_descripcion_otros = $this->despacho_gasto_otros > 0 ? $this->despacho_descripcion_otros : null;
            $despacho->despacho_monto_modificado =  $this->tarifaMontoSeleccionado;
            $despacho->despacho_estado_modificado = $this->tarifaMontoSeleccionado != $this->montoOriginal ? 1 : 0;
            $despacho->despacho_descripcion_modificado = ($this->tarifaMontoSeleccionado != $this->montoOriginal) ? $this->despacho_descripcion_modificado : null;
            $despacho->despacho_estado = 1;
            $despacho->despacho_microtime = $microtimeCread;
            $existecap = DB::table('tarifarios')
                ->where('id_tarifario', $this->id_tarifario_seleccionado)
                ->select('tarifa_cap_min', 'tarifa_cap_max')
                ->first();
            $despacho->despacho_cap_min = $existecap->tarifa_cap_min;
            $despacho->despacho_cap_max = $existecap->tarifa_cap_max;
            if (!$despacho->save()) {
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar el despacho.');
                return;
            }
            $ultimoDespacho = DB::table('despachos')->where('despacho_microtime','=',$microtimeCread)->first();
            // Guardar en el historial de despachos
            $historialDespacho = new Historialdespachoventa();
            $historialDespacho->id_programacion = $programacionCreada->id_programacion;
            $historialDespacho->id_despacho = $ultimoDespacho->id_despacho;
            $historialDespacho->programacion_estado_aprobacion = 0;
            // Guardar el estado de aprobación de la programación en el historial
            $historialDespacho->despacho_estado_aprobacion = ($this->id_programacion_edit && $this->id_despacho_edit) ? 6 : 0;
            $historialDespacho->his_desp_vent_fecha = Carbon::now('America/Lima');
            if (!$historialDespacho->save()) {
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar el historial del despacho.');
                return;
            }

            // Guardar facturas seleccionadas en despacho_ventas
            foreach ($this->selectedFacturas as $factura) {
                $despachoVenta = new DespachoVenta();
                $despachoVenta->id_despacho = $ultimoDespacho->id_despacho;
                $despachoVenta->id_venta = null;
                $despachoVenta->despacho_venta_cftd = $factura['CFTD'];
                $despachoVenta->despacho_venta_cfnumser = $factura['CFNUMSER'];
                $despachoVenta->despacho_venta_cfnumdoc = $factura['CFNUMDOC'];
                $despachoVenta->despacho_venta_factura = $factura['CFNUMSER'] . '-' . $factura['CFNUMDOC'];
                $despachoVenta->despacho_venta_grefecemision = $factura['GREFECEMISION'];
                $despachoVenta->despacho_venta_cnomcli = $factura['CNOMCLI'];
                $despachoVenta->despacho_venta_cfcodcli = $factura['CCODCLI'];
                $despachoVenta->despacho_venta_guia = $factura['guia'];
                $despachoVenta->despacho_venta_cfimporte = $factura['CFIMPORTE'];
                $despachoVenta->despacho_venta_total_kg = $factura['total_kg'];
                $despachoVenta->despacho_venta_total_volumen = $factura['total_volumen'];
                $despachoVenta->despacho_venta_direccion_llegada = $factura['LLEGADADIRECCION'];
                $despachoVenta->despacho_venta_departamento = $factura['DEPARTAMENTO'];
                $despachoVenta->despacho_venta_provincia = $factura['PROVINCIA'];
                $despachoVenta->despacho_venta_distrito = $factura['DISTRITO'];
                $despachoVenta->despacho_detalle_estado = 1;
                $despachoVenta->despacho_detalle_microtime = microtime(true);
                $despachoVenta->despacho_detalle_estado_entrega = 0;
                if (!$despachoVenta->save()) {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar las facturas.');
                    return;
                }
            }

            $idsFacturas = array_column($this->selectedFacturas, 'id_fac_pre_prog');
            // Obtener los datos de las facturas antes de la actualización
            $facturas = DB::table('facturas_pre_programaciones')
                ->whereIn('id_fac_pre_prog', $idsFacturas)
                ->get();
            // Actualizar el estado de aprobación
            DB::table('facturas_pre_programaciones')
                ->whereIn('id_fac_pre_prog', $idsFacturas)
                ->update(['fac_pre_prog_estado_aprobacion' => 4]);
            // Registrar en historial_pre_programacion
            foreach ($facturas as $factura) {
                $historial = new Historialpreprogramacion();
                $historial->id_fac_pre_prog = $factura->id_fac_pre_prog;
                $historial->fac_pre_prog_cfnumdoc = $factura->fac_pre_prog_cfnumdoc;
                $historial->fac_pre_prog_estado_aprobacion = 4;
                $historial->fac_pre_prog_estado = 1;
                $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
                $historial->save();
            }

            // Solo actualizar facturas_mov si NO se está editando
            if (!$this->id_programacion_edit && !$this->id_despacho_edit) {
                $facturaMov = DB::table('facturas_mov')
                    ->where('id_fac_pre_prog', $this->selectedFacturas)
                    ->get();

                if ($facturaMov->isNotEmpty()) {
                    DB::table('facturas_mov')
                        ->where('id_fac_pre_prog', $this->selectedFacturas)
                        ->update([
                            'fac_acept_ges_fac' => Carbon::now('America/Lima'),
                            'fac_despacho' => Carbon::now('America/Lima'),
                        ]);
                }
            }
            DB::commit();

            if ($this->id_programacion_edit && $this->id_despacho_edit){
                return redirect()->route('Despachotransporte.aprobar_programacion_despacho')->with('success', '¡Registro actualizado correctamente!');
            }else{
                session()->flash('success', 'Registro guardado correctamente.');
                $this->reiniciar_campos();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error inesperado. Por favor, inténtelo nuevamente.');
        }
    }

    public function reiniciar_campos(){
        $this->searchFactura = "";
        $this->filteredFacturas = [];
        $this->id_transportistas = "";
        $this->vehiculosSugeridos = [];
        $this->selectedVehiculo = "";
        $this->pesoTotal = 0;
        $this->volumenTotal = 0;
        $this->selectedFacturas = [];
        $this->detalle_vehiculo = [];
        $this->tarifaMontoSeleccionado = 0;
        $this->id_tarifario_seleccionado = '';
        $this->despacho_ayudante = "";
        $this->despacho_gasto_otros = "";
        $this->id_tipo_servicios = null;
        $this->despacho_peso = null;
        $this->despacho_volumen = null;
        $this->despacho_flete = null;
        $this->id_tarifario = null;
        $this->costoTotal = 0;
        $this->montoOriginal = 0;
        $this->despacho_descripcion_otros = '';
        $this->despacho_descripcion_modificado = '';
        $this->programacion_fecha = now()->format('Y-m-d');
        $this->desde = date('Y-m-d', strtotime('-1 month'));
        $this->hasta = date('Y-m-d');
    }
}
