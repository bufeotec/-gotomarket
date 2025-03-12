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
    public $estado_envio = 1;
    public $errorMessage;
    public function mount(){
        $this->desde = date('Y-01-01');
        $this->hasta = date('Y-m-d');
        $this->selectedGuias = [];

        // Obtener facturas preprogramadas con estado 0
//        $facturasPreProgramadas = DB::table('facturas_pre_programaciones')
//            ->where('fac_pre_prog_estado', 0)
//            ->get();
//
//        // Formatear y agregar al array selectedGuias
//        foreach ($facturasPreProgramadas as $factura) {
//            $this->selectedGuias[] = [
////                'CFTD' => $factura->fac_pre_prog_cftd,
//                'SERIE' => $factura->fac_pre_prog_cfnumser,
//                'NÚMERO' => $factura->fac_pre_prog_cfnumdoc,
//                'PESO' => $factura->fac_pre_prog_total_kg,
//                'VOLUMEN' => $factura->fac_pre_prog_total_volumen,
//                'NOMBRE CLIENTE' => $factura->fac_pre_prog_cnomcli,
//                'CFIMPORTE' => $factura->fac_pre_prog_cfimporte,
//                'RUC CLIENTE' => $factura->fac_pre_prog_cfcodcli,
//                'guia' => $factura->fac_pre_prog_guia,
//                'FECHA EMISIÓN' => $factura->fac_pre_prog_grefecemision,
//                'DIRECCIÓN LLEGADA' => $factura->fac_pre_prog_direccion_llegada,
//                'DEPARTAMENTO LLEGADA' => $factura->fac_pre_prog_departamento,
//                'PROVINCIA LLEGADA' => $factura->fac_pre_prog_provincia,
//                'DISTRITO LLEGADA' => $factura->fac_pre_prog_distrito,
//            ];
//        }
    }

    public function render(){
        $listar_tipo_servicios = $this->tiposervicio->listar_tipo_servicios();
        return view('livewire.programacioncamiones.facturaspreprogramaciones', compact('listar_tipo_servicios'));
    }

    public function buscar_comprobantes(){
        if (empty($this->desde) && empty($this->hasta) && empty($this->searchFactura)) {
            session()->flash('error', 'Debe ingresar al menos una fecha o un criterio de búsqueda.');
            return;
        }

        if (!empty($this->desde) && !empty($this->hasta)) {
            $yearDesde = date('Y', strtotime($this->desde));
            $yearHasta = date('Y', strtotime($this->hasta));
            if ($yearDesde < 2025 || $yearHasta < 2025) {
                session()->flash('error', 'Las fechas deben ser a partir de 2025.');
                return;
            }
        }

        $this->filteredGuias = $this->server->obtenerDocumentosRemision($this->desde, $this->hasta) ?? [];
    }
    public function seleccionarGuia($NRO_DOC) {
        if (!is_array($this->selectedGuias)) {
            $this->selectedGuias = [];
        }

        $comprobanteExiste = collect($this->selectedGuias)->first(function ($guia) use ($NRO_DOC) {
            return isset($guia['NRO_DOC']) && $guia['NRO_DOC'] === $NRO_DOC;
        });

        if ($comprobanteExiste) {
            session()->flash('error', 'Esta guía ya fue seleccionada.');
            return;
        }

        $guia = collect($this->filteredGuias)->first(function ($guia_) use ($NRO_DOC) {
            return $guia_->NRO_DOC === $NRO_DOC;
        });

        if ($guia) {
            $this->selectedGuias[] = [
                'almacen_origen' => $guia->ALMACEN_ORIGEN,
                'tipo_doc' => $guia->TIPO_DOC,
                'nro_doc' => $guia->NRO_DOC,
                'fecha_emision' => $guia->FECHA_EMISION,
                'tipo_movimiento' => $guia->TIPO_MOVIMIENTO,
                'tipo_doc_ref' => $guia->TIPO_DOC_REF,
                'nro_doc_ref' => $guia->NRO_DOC_REF,
                'glosa' => $guia->GLOSA,
                'fecha_proceso' => $guia->FECHA_PROCESO,
                'hora_proceso' => $guia->HORA_PROCESO,
                'usuario' => $guia->USUARIO,
                'cod_cliente' => $guia->COD_CLIENTE,
                'ruc_cliente' => $guia->RUC_CLIENTE,
                'nombre_cliente' => $guia->NOMBRE_CLIENTE,
                'forma_pago' => $guia->FORMA_PAGO,
                'vendedor' => $guia->VENDEDOR,
                'moneda' => $guia->MONEDA,
                'tipo_cambio' => $guia->TIPO_CAMBIO,
                'estado' => $guia->ESTADO,
                'direccion_entrega' => $guia->DIRECCION_ENTREGA,
                'nro_pedido' => $guia->NRO_PEDIDO,
                'importe_total' => $guia->IMPORTE_TOTAL,
                'departamento' => $guia->DEPARTAMENTO,
                'provincia' => $guia->PROVINCIA,
                'distrito' => $guia->DISTRITO,
                'peso' => $guia->PESO_G,
                'volumen' => $guia->VOLUMEN_CM3,
                'peso_total' => $guia->PESO_TOTAL_G,
                'volumen_total' => $guia->VOLUMEN_TOTAL_CM3,
                'codigo' => $guia->CODIGO,
                'descripcion' => $guia->DESCRIPCION,
                'cantidad' => $guia->CANTIDAD,
                'unidad' => $guia->UNIDAD,
            ];

            $this->filteredGuias = collect($this->filteredGuias)->reject(function ($guia_) use ($NRO_DOC) {
                return $guia_->NRO_DOC === $NRO_DOC;
            })->values();
        }
    }
    public function eliminarFacturaSeleccionada($NRO_DOC) {
        // Encuentra la guía en las seleccionadas
        $guia = collect($this->selectedGuias)->first(function ($f) use ($NRO_DOC) {
            return $f['NRO_DOC'] === $NRO_DOC;
        });

        if ($guia) {
            $this->selectedGuias = collect($this->selectedGuias)
                ->reject(function ($f) use ($NRO_DOC) {
                    return $f['NRO_DOC'] === $NRO_DOC;
                })
                ->values()
                ->toArray();

            $this->filteredGuias[] = (object) [
                'NRO_DOC' => $guia['NRO_DOC'],
                'PESO_G' => $guia['PESO_G'],
                'VOLUMEN_TOTAL_CM3' => $guia['VOLUMEN_TOTAL_CM3'],
                'NOMBRE_CLIENTE' => $guia['NOMBRE_CLIENTE'],
                'IMPORTE_TOTAL' => $guia['IMPORTE_TOTAL'],
                'RUC_CLIENTE' => $guia['RUC_CLIENTE'],
                'FECHA_EMISION' => $guia['FECHA_EMISION'],
                'DIRECCION_ENTREGA' => $guia['DIRECCION_ENTREGA'],
                'DEPARTAMENTO' => $guia['DEPARTAMENTO'],
                'PROVINCIA' => $guia['PROVINCIA'],
                'DISTRITO' => $guia['DISTRITO'],
            ];
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
            $this->selectedGuias = [];
            session()->flash('success', 'Guías enviadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al guardar las facturas: ' . $e->getMessage());
        }
    }
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
    public function eliminarGuia($SERIE, $NUMERO)
    {
        // Filtrar y eliminar la guía seleccionada
        $this->selectedGuias = array_filter($this->selectedGuias, function ($guia) use ($SERIE, $NUMERO) {
            return !($guia->SERIE === $SERIE && $guia->NUMERO === $NUMERO);
        });

        // Convertir el array filtrado en una colección de objetos nuevamente
        $this->selectedGuias = array_values(array_map(fn($guia) => (object) $guia, $this->selectedGuias));
    }
}
