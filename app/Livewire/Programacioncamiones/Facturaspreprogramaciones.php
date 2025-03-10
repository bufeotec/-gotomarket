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
    public $selectedGuias = [];
    public $filteredGuias = [];
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $importeTotalVenta = 0;
    public $id_tipo_servicios = "";
    public $searchFactura = "";
    public $desde;
    public $hasta;
    public $detalleFactura;
    public $estado_envio = "";
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');

        $this->selectedGuias = [];

        // Obtener facturas preprogramadas con estado 0
        $facturasPreProgramadas = DB::table('facturas_pre_programaciones')
            ->where('fac_pre_prog_estado', 0)
            ->get();

        // Formatear y agregar al array selectedGuias
        foreach ($facturasPreProgramadas as $factura) {
            $this->selectedGuias[] = [
//                'CFTD' => $factura->fac_pre_prog_cftd,
                'SERIE' => $factura->fac_pre_prog_cfnumser,
                'NÚMERO' => $factura->fac_pre_prog_cfnumdoc,
                'PESO' => $factura->fac_pre_prog_total_kg,
                'VOLUMEN' => $factura->fac_pre_prog_total_volumen,
                'NOMBRE CLIENTE' => $factura->fac_pre_prog_cnomcli,
                'CFIMPORTE' => $factura->fac_pre_prog_cfimporte,
                'RUC CLIENTE' => $factura->fac_pre_prog_cfcodcli,
                'guia' => $factura->fac_pre_prog_guia,
                'FECHA EMISIÓN' => $factura->fac_pre_prog_grefecemision,
                'DIRECCIÓN LLEGADA' => $factura->fac_pre_prog_direccion_llegada,
                'DEPARTAMENTO LLEGADA' => $factura->fac_pre_prog_departamento,
                'PROVINCIA LLEGADA' => $factura->fac_pre_prog_provincia,
                'DISTRITO LLEGADA' => $factura->fac_pre_prog_distrito,
            ];
        }
    }

    public function render(){
//        $fechadesde = "";
//        $fechahasta = "";
//        $documento_guia = $this->server->obtenerDocumentosRemision($fechadesde,$fechahasta);
//        $nc = $this->server->listar_notas_credito_ss();
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

//        $datosResult = $this->server->listar_comprobantes_listos_local($this->searchFactura, $this->desde, $this->hasta);
        $documento_guia = $this->server->obtenerDocumentosRemision($this->desde,$this->hasta);
//        dd($documento_guia);
        $this->filteredGuias = $documento_guia;
        if (!$documento_guia) {
            $this->filteredGuias = [];
        }
    }

    public function seleccionarFactura($SERIE, $NUMERO) {
        // Validar que la factura no exista en el array selectedGuias
        $comprobanteExiste = collect($this->selectedGuias)->first(function ($factura) use ($SERIE, $NUMERO) {
            return $factura['SERIE'] === $SERIE && $factura['NÚMERO'] === $NUMERO;
        });

        if ($comprobanteExiste) {
            // Mostrar un mensaje de error si la factura ya fue agregada
            session()->flash('error', 'Este comprobante ya fue agregado.');
            return;
        }

        // Buscar la factura en el array filteredGuias
        $factura = collect($this->filteredGuias)->first(function ($f) use ($SERIE, $NUMERO) {
            return $f->SERIE === $SERIE && $f->NÚMERO === $NUMERO;
        });

        // Validar que el peso y volumen sean mayores que 0
        if (($factura->PESO ?? 0) <= 0 || ($factura->VOLUMEN ?? 0) <= 0) {
            session()->flash('error', 'El peso o el volumen deben ser mayores a 0.');
            return;
        }

        // Agregar la factura seleccionada y actualizar el peso y volumen total
        $this->selectedFacturas[] = [
            'SERIE' => $SERIE,
            'NÚMERO' => $NUMERO,
            'PESO' => $factura->PESO,
            'VOLUMEN' => $factura->VOLUMEN,
            'NOMBRE CLIENTE' => $factura->{'NOMBRE CLIENTE'},
            'CFIMPORTE' => $factura->{'CFIMPORTE'},
            'RUC CLIENTE' => $factura->{'RUC CLIENTE'},
            'guia' => $factura->{'guia'},
            'FECHA EMISIÓN' => $factura->{'FECHA EMISIÓN'},
            'DIRECCIÓN LLEGADA' => $factura->{'DIRECCIÓN LLEGADA'},
            'DEPARTAMENTO LLEGADA' => $factura->{'DEPARTAMENTO LLEGADA'},
            'PROVINCIA LLEGADA' => $factura->{'PROVINCIA LLEGADA'},
            'DISTRITO LLEGADA' => $factura->{'DISTRITO LLEGADA'},
        ];

        // Actualizar los totales
        $this->pesoTotal += $factura->PESO;
        $this->volumenTotal += $factura->VOLUMEN;
        $this->importeTotalVenta += floatval($factura->{'CFIMPORTE'});

        // Eliminar la factura de la lista de facturas filtradas
        $this->filteredGuias = $this->filteredGuias->filter(function ($f) use ($NUMERO) {
            return $f->NÚMERO !== $NUMERO;
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
