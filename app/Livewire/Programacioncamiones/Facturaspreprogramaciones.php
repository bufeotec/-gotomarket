<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\TipoServicio;
use App\Models\Server;
use App\Models\Facturaspreprogramacion;
use App\Models\Historialpreprogramacion;
use Carbon\Carbon;

class Facturaspreprogramaciones extends Component
{
    private $logs;
    private $tiposervicio;
    private $server;
    private $facpreprog;
    private $historialpreprogramacion;
    public function __construct(){
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
        $this->server = new Server();
        $this->facpreprog = new Facturaspreprogramacion();
        $this->historialpreprogramacion = new Historialpreprogramacion();
    }
    public $selectedFacturas = [];
    public $filteredFacturas = [];
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $importeTotalVenta = 0;
    public $id_tipo_servicios = "";
    public $searchFactura = "";
    public $desde;
    public $hasta;
    public $estado_envio = "";
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');

        $this->selectedFacturas = [];

        // Obtener facturas preprogramadas con estado 0
        $facturasPreProgramadas = DB::table('facturas_pre_programaciones')
            ->where('fac_pre_prog_estado', 0)
            ->get();

        // Formatear y agregar al array selectedFacturas
        foreach ($facturasPreProgramadas as $factura) {
            $this->selectedFacturas[] = [
                'CFTD' => $factura->fac_pre_prog_cftd,
                'CFNUMSER' => $factura->fac_pre_prog_cfnumser,
                'CFNUMDOC' => $factura->fac_pre_prog_cfnumdoc,
                'total_kg' => $factura->fac_pre_prog_total_kg,
                'total_volumen' => $factura->fac_pre_prog_total_volumen,
                'CNOMCLI' => $factura->fac_pre_prog_cnomcli,
                'CFIMPORTE' => $factura->fac_pre_prog_cfimporte,
                'CCODCLI' => $factura->fac_pre_prog_cfcodcli,
                'guia' => $factura->fac_pre_prog_guia,
                'GREFECEMISION' => $factura->fac_pre_prog_grefecemision,
                'LLEGADADIRECCION' => $factura->fac_pre_prog_direccion_llegada,
                'DEPARTAMENTO' => $factura->fac_pre_prog_departamento,
                'PROVINCIA' => $factura->fac_pre_prog_provincia,
                'DISTRITO' => $factura->fac_pre_prog_distrito,
            ];
        }
    }

    public function render(){
        $fechadesde = '2023-01-01'; // Fecha de inicio (formato YYYY-MM-DD)
        $fechahasta = '2025-02-21'; // Fecha de fin (formato YYYY-MM-DD)
        $documento_guia = $this->server->obtenerDocumentosRemision($fechadesde,$fechahasta);
//
//        $serie = 'F001';
//        $numero = '0015272';
//        $detalle_guia = $this->server->obtenerDetalleRemision($serie,$numero);




        $listar_tipo_servicios = $this->tiposervicio->listar_tipo_servicios();
        return view('livewire.programacioncamiones.facturaspreprogramaciones', compact('listar_tipo_servicios'));
    }


    public function buscar_comprobantes(){
        // Verificar si no hay fechas ni búsqueda
        if (empty($this->desde) && empty($this->hasta) && empty($this->searchFactura)) {
            session()->flash('error', 'Debe ingresar al menos una fecha o un criterio de búsqueda.');
            return; // Salir del método
        }

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

        $datosResult = $this->server->listar_comprobantes_listos_local($this->searchFactura, $this->desde, $this->hasta);
        $this->filteredFacturas = $datosResult;
        if (!$datosResult) {
            $this->filteredFacturas = [];
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
            'CCODCLI' => $factura->CCODCLI,
            'guia' => $factura->CFTEXGUIA,
            'GREFECEMISION' => $factura->GREFECEMISION, // fecha de emision de la guía
            'LLEGADADIRECCION' => $factura->LLEGADADIRECCION,// Dirección de destino
            'LLEGADAUBIGEO' => $factura->LLEGADAUBIGEO,// Código del ubigeo
            'DEPARTAMENTO' => $factura->DEPARTAMENTO,// Departamento
            'PROVINCIA' => $factura->PROVINCIA,// Provincia
            'DISTRITO' => $factura->DISTRITO,// Distrito
        ];
        $this->pesoTotal += $factura->total_kg;
        $this->volumenTotal += $factura->total_volumen;
        $importe = $factura->CFIMPORTE;
        $importe = floatval($importe);
        $this->importeTotalVenta += $importe;

        // Eliminar la factura de la lista de facturas filtradas
        $this->filteredFacturas = $this->filteredFacturas->filter(function ($f) use ($CFNUMDOC) {
            return $f->CFNUMDOC !== $CFNUMDOC;
        });
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
        }
    }

    public function guardarFacturas() {
        try {
            // Validar que haya facturas seleccionadas y un estado seleccionado
            $this->validate([
                'estado_envio' => 'required|integer',
                'selectedFacturas' => 'required|array|min:1',
            ], [
                'estado_envio.required' => 'Debes seleccionar un estado.',
                'estado_envio.integer' => 'El estado seleccionado no es válido.',
                'selectedFacturas.required' => 'Debes seleccionar al menos una factura.',
                'selectedFacturas.min' => 'Debes seleccionar al menos una factura.',
            ]);

            DB::beginTransaction();

            foreach ($this->selectedFacturas as $factura) {
                // Verificar si la factura ya existe en la tabla
                $facturaExistente = Facturaspreprogramacion::where('fac_pre_prog_cftd', $factura['CFTD'])
                    ->where('fac_pre_prog_cfnumser', $factura['CFNUMSER'])
                    ->where('fac_pre_prog_cfnumdoc', $factura['CFNUMDOC'])
                    ->first();

                if ($facturaExistente) {
                    // Si la factura existe, actualizar el estado
                    $facturaExistente->fac_pre_prog_estado_aprobacion = $this->estado_envio;
                    $facturaExistente->fac_pre_prog_estado = 1;
                    $facturaExistente->fac_pre_prog_fecha = Carbon::now('America/Lima');
                    $facturaExistente->save();

                    // Guardar en la tabla historial_pre_programacion
                    $historial = new Historialpreprogramacion();
                    $historial->id_fac_pre_prog = $facturaExistente->id_fac_pre_prog;
                    $historial->fac_pre_prog_cfnumdoc = $facturaExistente->fac_pre_prog_cfnumdoc;
                    $historial->fac_pre_prog_estado_aprobacion = $facturaExistente->fac_pre_prog_estado_aprobacion;
                    $historial->fac_pre_prog_estado = $facturaExistente->fac_pre_prog_estado;
                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
                    $historial->save();
                } else {
                    // Si no existe, crear un nuevo registro
                    $nuevaFactura = new Facturaspreprogramacion();
                    $nuevaFactura->id_users = Auth::id();
                    $nuevaFactura->fac_pre_prog_cftd = $factura['CFTD'];
                    $nuevaFactura->fac_pre_prog_cfnumser = $factura['CFNUMSER'];
                    $nuevaFactura->fac_pre_prog_cfnumdoc = $factura['CFNUMDOC'];
                    $nuevaFactura->fac_pre_prog_factura = $factura['CFNUMSER'] . '-' . $factura['CFNUMDOC'];
                    $nuevaFactura->fac_pre_prog_grefecemision = $factura['GREFECEMISION'];
                    $nuevaFactura->fac_pre_prog_cnomcli = $factura['CNOMCLI'];
                    $nuevaFactura->fac_pre_prog_cfcodcli = $factura['CCODCLI'];
                    $nuevaFactura->fac_pre_prog_guia = $factura['guia'];
                    $nuevaFactura->fac_pre_prog_cfimporte = $factura['CFIMPORTE'];
                    $nuevaFactura->fac_pre_prog_total_kg = $factura['total_kg'];
                    $nuevaFactura->fac_pre_prog_total_volumen = $factura['total_volumen'];
                    $nuevaFactura->fac_pre_prog_direccion_llegada = $factura['LLEGADADIRECCION'];
                    $nuevaFactura->fac_pre_prog_departamento = $factura['DEPARTAMENTO'];
                    $nuevaFactura->fac_pre_prog_provincia = $factura['PROVINCIA'];
                    $nuevaFactura->fac_pre_prog_distrito = $factura['DISTRITO'];
                    $nuevaFactura->fac_pre_prog_estado_aprobacion = $this->estado_envio;
                    $nuevaFactura->fac_pre_prog_estado = 1;
                    $nuevaFactura->fac_pre_prog_fecha = Carbon::now('America/Lima');
                    $nuevaFactura->save();

                    // Guardar en la tabla historial_pre_programacion
                    $historial = new Historialpreprogramacion();
                    $historial->id_fac_pre_prog = $nuevaFactura->id_fac_pre_prog;
                    $historial->fac_pre_prog_cfnumdoc = $nuevaFactura->fac_pre_prog_cfnumdoc;
                    $historial->fac_pre_prog_estado_aprobacion = $nuevaFactura->fac_pre_prog_estado_aprobacion;
                    $historial->fac_pre_prog_estado = $nuevaFactura->fac_pre_prog_estado;
                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
                    $historial->save();

                }    // Insertar en facturas_mov
                DB::table('facturas_mov')->insert([
                    'id_fac_pre_prog' => $historial->id_fac_pre_prog, // Usar el ID de la nueva factura creada
                    'fac_envio_valpago' => Carbon::now('America/Lima'), // Establecer la fecha de envío
                    'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
                ]);
            }
            DB::commit();
            // Limpiar las facturas seleccionadas y el estado
            $this->selectedFacturas = [];
            $this->estado_envio = null;
            session()->flash('success', 'Facturas procesadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al guardar las facturas: ' . $e->getMessage());
        }
    }
    public $errorMessage;

    public function listar_detallesf($cftd, $cfnumser, $cfnumdoc) {
        try {
            $this->detalleFactura = Factura::where('fac_pre_prog_cftd', $cftd)
                ->where('fac_pre_prog_cfnumser', $cfnumser)
                ->where('fac_pre_prog_cfnumdoc', $cfnumdoc)
                ->first();

            if (!$this->detalleFactura) {
                $this->errorMessage = "No se encontró la factura con los parámetros especificados.";
            } else {
                $this->errorMessage = null; // Restablecer el mensaje si se encontró la factura
            }

        } catch (\Exception $e) {
            $this->errorMessage = "Ocurrió un error al intentar obtener la factura.";
            $this->logs->insertarLog($e);
        }
    }
}
