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
//use App\Models\Guia;
use Carbon\Carbon;

class Facturaspreprogramaciones extends Component
{
    private $logs;
    private $tiposervicio;
    private $server;
    private $facpreprog;
//    private $guia;
    private $historialpreprogramacion;
    public function __construct(){
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
        $this->server = new Server();
//        $this->guia = new Guia();
        $this->facpreprog = new Facturaspreprogramacion();
        $this->historialpreprogramacion = new Historialpreprogramacion();
    }
    public $selectedGuias = [];
    public $filteredGuias = [];
    public $filtereddetGuias = [];
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
    }

    public function render(){
        $listar_tipo_servicios = $this->tiposervicio->listar_tipo_servicios();
        return view('livewire.programacioncamiones.facturaspreprogramaciones', compact('listar_tipo_servicios'));
    }
    public function buscar_comprobantes() {
        if (empty($this->desde) && empty($this->hasta) && empty($this->searchGuia)) {
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
        $this->filtereddetGuias = [];
        foreach ($this->filteredGuias as $guia) {
            $serie = isset($guia->serie) ? $guia->serie : null;
            $numero = isset($guia->numero) ? $guia->numero : null;

            if ($serie && $numero) {
                $detalles = $this->obtenerDetalleRemision($serie, $numero);
                $this->filtereddetGuias[$numero] = $detalles;
            }
        }
    }
    public function seleccionarGuia($NRO_DOC) {
        if (!is_array($this->selectedGuias)) {
            $this->selectedGuias = [];
        }

        $comprobanteExiste = collect($this->selectedGuias)->first(function ($guia) use ($NRO_DOC) {
            return isset($guia['nro_doc']) && $guia['nro_doc'] === $NRO_DOC;
        });

        if ($comprobanteExiste) {
            // Si ya existe, eliminar de la selección (deseleccionar)
            $this->selectedGuias = collect($this->selectedGuias)->reject(function ($guia) use ($NRO_DOC) {
                return isset($guia['nro_doc']) && $guia['nro_doc'] === $NRO_DOC;
            })->values()->toArray(); // Deselecciona el documento
        } else {
            // Si no existe, agregar a la selección
            $guia = collect($this->filteredGuias)->first(function ($guia_) use ($NRO_DOC) {
                return isset($guia_->NRO_DOC) && $guia_->NRO_DOC === $NRO_DOC;
            });

            if ($guia) {
                $this->selectedGuias[] = [
                    'almacen_origen' => $guia->ALMACEN_ORIGEN,
                    'estado' => $guia->ESTADO,
                    'tipo_doc' => $guia->TIPO_DOC,
                    'nro_doc' => $guia->NRO_DOC,
                    'fecha_emision' => $guia->FECHA_EMISION,
                    'tipo_movimiento' => $guia->TIPO_MOVIMIENTO,
                    'tipo_doc_ref' => $guia->TIPO_DOC_REF,
                    'nro_doc_ref' => $guia->NRO_DOC_REF,
                    'glosa' => $guia->GLOSA,
                    'fecha_de_proceso' => $guia->FECHA_DE_PROCESO,
                    'hora_de_proceso' => $guia->HORA_DE_PROCESO,
                    'usuario' => $guia->USUARIO,
                    'cod_cliente' => $guia->COD_CLIENTE,
                    'ruc_cliente' => $guia->RUC_CLIENTE,
                    'nombre_cliente' => $guia->NOMBRE_CLIENTE,
                    'forma_de_pago' => $guia->FORMA_DE_PAGO,
                    'vendedor' => $guia->VENDEDOR,
                    'moneda' => $guia->MONEDA,
                    'tipo_cambio' => $guia->TIPO_DE_CAMBIO,
                    'importe_total' => $guia->IMPORTE_TOTAL,
                    'direccion_entrega' => $guia->DIREC_ENTREGA,
                    'nro_pedido' => $guia->NRO_PEDIDO,
                    'departamento' => $guia->DEPARTAMENTO,
                    'provincia' => $guia->PROVINCIA,
                    'distrito' => $guia->DISTRITO,
                    // Puedes agregar otros campos si los necesitas
                ];
                // Elimina la guía de la lista de guías filtradas
                $this->filteredGuias = collect($this->filteredGuias)->reject(function ($guia_) use ($NRO_DOC) {
                    return isset($guia_->NRO_DOCUMENTO) && $guia_->NRO_DOCUMENTO === $NRO_DOC;
                })->values();
            }
        }
    }
    public function eliminarFacturaSeleccionada($NRO_DOC) {
        // Encuentra la guía en las seleccionadas
        $guia = collect($this->selectedGuias)->first(function ($f) use ($NRO_DOC) {
            return isset($f['nro_doc']) && $f['nro_doc'] === $NRO_DOC;
        });

        if ($guia) {
            // Elimina la guía de las seleccionadas
            $this->selectedGuias = collect($this->selectedGuias)
                ->reject(function ($f) use ($NRO_DOC) {
                    return isset($f['nro_doc']) && $f['nro_doc'] === $NRO_DOC;
                })
                ->values()
                ->toArray();
            $this->filteredGuias[] = (object) [
                'ALMACEN_ORIGEN' => $guia['almacen_origen'],
                'TIPO_DOC' => $guia['tipo_doc'],
                'NRO_DOC' => $guia['nro_doc'],
                'FECHA_EMISION' => $guia['fecha_emision'],
                'TIPO_MOVIMIENTO' => $guia['tipo_movimiento'],
                'TIPO_DOC_REF' => $guia['tipo_doc_ref'],
                'NRO_DOC_REF' => $guia['nro_doc_ref'],
                'GLOSA' => $guia['glosa'],
                'FECHA_DE_PROCESO' => $guia['fecha_de_proceso'],
                'HORA_DE_PROCESO' => $guia['hora_de_proceso'],
                'USUARIO' => $guia['usuario'],
                'COD_CLIENTE' => $guia['cod_cliente'],
                'RUC_CLIENTE' => $guia['ruc_cliente'],
                'NOMBRE_CLIENTE' => $guia['nombre_cliente'],
                'FORMA_DE_PAGO' => $guia['forma_de_pago'],
                'VENDEDOR' => $guia['vendedor'],
                'MONEDA' => $guia['moneda'],
                'TIPO_DE_CAMBIO' => $guia['tipo_cambio'],
                'ESTADO' => $guia['estado'],
                'DIREC_ENTREGA' => $guia['direccion_entrega'],
                'NRO_PEDIDO' => $guia['nro_pedido'],
                'DEPARTAMENTO' => $guia['departamento'],
                'PROVINCIA' => $guia['provincia'],
                'DISTRITO' => $guia['distrito'],
                'IMPORTE_TOTAL' => $guia['importe_total'],
            ];
        }
    }
//    public function guardarFacturas() {
//        try {
//            // Validar que haya facturas seleccionadas y un estado seleccionado
//            $this->validate([
//                'estado_envio' => 'required|integer',
//                'selectedFacturas' => 'required|array|min:1',
//            ], [
//                'estado_envio.required' => 'Debes seleccionar un estado.',
//                'estado_envio.integer' => 'El estado seleccionado no es válido.',
//                'selectedFacturas.required' => 'Debes seleccionar al menos una factura.',
//                'selectedFacturas.min' => 'Debes seleccionar al menos una factura.',
//            ]);
//
//            DB::beginTransaction();
//
//            foreach ($this->selectedFacturas as $factura) {
//                // Verificar si la factura ya existe en la tabla
//                $facturaExistente = Facturaspreprogramacion::where('fac_pre_prog_cftd', $factura['CFTD'])
//                    ->where('fac_pre_prog_cfnumser', $factura['CFNUMSER'])
//                    ->where('fac_pre_prog_cfnumdoc', $factura['CFNUMDOC'])
//                    ->first();
//
//                if ($facturaExistente) {
//                    // Si la factura existe, actualizar el estado
//                    $facturaExistente->fac_pre_prog_estado_aprobacion = $this->estado_envio;
//                    $facturaExistente->fac_pre_prog_estado = 1;
//                    $facturaExistente->fac_pre_prog_fecha = Carbon::now('America/Lima');
//                    $facturaExistente->save();
//
//                    // Guardar en la tabla historial_pre_programacion
//                    $historial = new Historialpreprogramacion();
//                    $historial->id_fac_pre_prog = $facturaExistente->id_fac_pre_prog;
//                    $historial->fac_pre_prog_cfnumdoc = $facturaExistente->fac_pre_prog_cfnumdoc;
//                    $historial->fac_pre_prog_estado_aprobacion = $facturaExistente->fac_pre_prog_estado_aprobacion;
//                    $historial->fac_pre_prog_estado = $facturaExistente->fac_pre_prog_estado;
//                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
//                    $historial->save();
//                } else {
//                    // Si no existe, crear un nuevo registro
//                    $nuevaFactura = new Facturaspreprogramacion();
//                    $nuevaFactura->id_users = Auth::id();
//                    $nuevaFactura->fac_pre_prog_cftd = $factura['CFTD'];
//                    $nuevaFactura->fac_pre_prog_cfnumser = $factura['CFNUMSER'];
//                    $nuevaFactura->fac_pre_prog_cfnumdoc = $factura['CFNUMDOC'];
//                    $nuevaFactura->fac_pre_prog_factura = $factura['CFNUMSER'] . '-' . $factura['CFNUMDOC'];
//                    $nuevaFactura->fac_pre_prog_grefecemision = $factura['GREFECEMISION'];
//                    $nuevaFactura->fac_pre_prog_cnomcli = $factura['CNOMCLI'];
//                    $nuevaFactura->fac_pre_prog_cfcodcli = $factura['CCODCLI'];
//                    $nuevaFactura->fac_pre_prog_guia = $factura['guia'];
//                    $nuevaFactura->fac_pre_prog_cfimporte = $factura['CFIMPORTE'];
//                    $nuevaFactura->fac_pre_prog_total_kg = $factura['total_kg'];
//                    $nuevaFactura->fac_pre_prog_total_volumen = $factura['total_volumen'];
//                    $nuevaFactura->fac_pre_prog_direccion_llegada = $factura['LLEGADADIRECCION'];
//                    $nuevaFactura->fac_pre_prog_departamento = $factura['DEPARTAMENTO'];
//                    $nuevaFactura->fac_pre_prog_provincia = $factura['PROVINCIA'];
//                    $nuevaFactura->fac_pre_prog_distrito = $factura['DISTRITO'];
//                    $nuevaFactura->fac_pre_prog_estado_aprobacion = $this->estado_envio;
//                    $nuevaFactura->fac_pre_prog_estado = 1;
//                    $nuevaFactura->fac_pre_prog_fecha = Carbon::now('America/Lima');
//                    $nuevaFactura->save();
//
//                    // Guardar en la tabla historial_pre_programacion
//                    $historial = new Historialpreprogramacion();
//                    $historial->id_fac_pre_prog = $nuevaFactura->id_fac_pre_prog;
//                    $historial->fac_pre_prog_cfnumdoc = $nuevaFactura->fac_pre_prog_cfnumdoc;
//                    $historial->fac_pre_prog_estado_aprobacion = $nuevaFactura->fac_pre_prog_estado_aprobacion;
//                    $historial->fac_pre_prog_estado = $nuevaFactura->fac_pre_prog_estado;
//                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
//                    $historial->save();
//
//                }    // Insertar en facturas_mov
//                DB::table('facturas_mov')->insert([
//                    'id_fac_pre_prog' => $historial->id_fac_pre_prog, // Usar el ID de la nueva factura creada
//                    'fac_envio_valpago' => Carbon::now('America/Lima'), // Establecer la fecha de envío
//                    'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
//                ]);
//            }
//            DB::commit();
//            $this->selectedGuias = [];
//            session()->flash('success', 'Guías enviadas correctamente.');
//        } catch (\Exception $e) {
//            DB::rollBack();
//            session()->flash('error', 'Ocurrió un error al guardar las facturas: ' . $e->getMessage());
//        }
//    }
//    public function guardarGuias() {
//        try {
//            // Validar que haya facturas seleccionadas y un estado seleccionado
//            $this->validate([
//                'estado_envio' => 'required|integer',
//                'selectedFacturas' => 'required|array|min:1',
//            ], [
//                'estado_envio.required' => 'Debes seleccionar un estado.',
//                'estado_envio.integer' => 'El estado seleccionado no es válido.',
//                'selectedFacturas.required' => 'Debes seleccionar al menos una factura.',
//                'selectedFacturas.min' => 'Debes seleccionar al menos una factura.',
//            ]);
//
//            DB::beginTransaction();
//
//            foreach ($this->selectedFacturas as $factura) {
//                // Verificar si la factura ya existe en la tabla
//                $facturaExistente = Facturaspreprogramacion::where('fac_pre_prog_cftd', $factura['CFTD'])
//                    ->where('fac_pre_prog_cfnumser', $factura['CFNUMSER'])
//                    ->where('fac_pre_prog_cfnumdoc', $factura['CFNUMDOC'])
//                    ->first();
//
//                if ($facturaExistente) {
//                    // Si la factura existe, actualizar el estado
//                    $facturaExistente->fac_pre_prog_estado_aprobacion = $this->estado_envio;
//                    $facturaExistente->fac_pre_prog_estado = 1;
//                    $facturaExistente->fac_pre_prog_fecha = Carbon::now('America/Lima');
//                    $facturaExistente->save();
//
//                    // Guardar en la tabla historial_pre_programacion
//                    $historial = new Historialpreprogramacion();
//                    $historial->id_fac_pre_prog = $facturaExistente->id_fac_pre_prog;
//                    $historial->fac_pre_prog_cfnumdoc = $facturaExistente->fac_pre_prog_cfnumdoc;
//                    $historial->fac_pre_prog_estado_aprobacion = $facturaExistente->fac_pre_prog_estado_aprobacion;
//                    $historial->fac_pre_prog_estado = $facturaExistente->fac_pre_prog_estado;
//                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
//                    $historial->save();
//                } else {
//                    // Si no existe, crear un nuevo registro
//                    $nuevaFactura = new Facturaspreprogramacion();
//                    $nuevaFactura->id_users = Auth::id();
//                    $nuevaFactura->fac_pre_prog_cftd = $factura['CFTD'];
//                    $nuevaFactura->fac_pre_prog_cfnumser = $factura['CFNUMSER'];
//                    $nuevaFactura->fac_pre_prog_cfnumdoc = $factura['CFNUMDOC'];
//                    $nuevaFactura->fac_pre_prog_factura = $factura['CFNUMSER'] . '-' . $factura['CFNUMDOC'];
//                    $nuevaFactura->fac_pre_prog_grefecemision = $factura['GREFECEMISION'];
//                    $nuevaFactura->fac_pre_prog_cnomcli = $factura['CNOMCLI'];
//                    $nuevaFactura->fac_pre_prog_cfcodcli = $factura['CCODCLI'];
//                    $nuevaFactura->fac_pre_prog_guia = $factura['guia'];
//                    $nuevaFactura->fac_pre_prog_cfimporte = $factura['CFIMPORTE'];
//                    $nuevaFactura->fac_pre_prog_total_kg = $factura['total_kg'];
//                    $nuevaFactura->fac_pre_prog_total_volumen = $factura['total_volumen'];
//                    $nuevaFactura->fac_pre_prog_direccion_llegada = $factura['LLEGADADIRECCION'];
//                    $nuevaFactura->fac_pre_prog_departamento = $factura['DEPARTAMENTO'];
//                    $nuevaFactura->fac_pre_prog_provincia = $factura['PROVINCIA'];
//                    $nuevaFactura->fac_pre_prog_distrito = $factura['DISTRITO'];
//                    $nuevaFactura->fac_pre_prog_estado_aprobacion = $this->estado_envio;
//                    $nuevaFactura->fac_pre_prog_estado = 1;
//                    $nuevaFactura->fac_pre_prog_fecha = Carbon::now('America/Lima');
//                    $nuevaFactura->save();
//
//                    // Guardar en la tabla historial_pre_programacion
//                    $historial = new Historialpreprogramacion();
//                    $historial->id_fac_pre_prog = $nuevaFactura->id_fac_pre_prog;
//                    $historial->fac_pre_prog_cfnumdoc = $nuevaFactura->fac_pre_prog_cfnumdoc;
//                    $historial->fac_pre_prog_estado_aprobacion = $nuevaFactura->fac_pre_prog_estado_aprobacion;
//                    $historial->fac_pre_prog_estado = $nuevaFactura->fac_pre_prog_estado;
//                    $historial->his_pre_progr_fecha_hora = Carbon::now('America/Lima');
//                    $historial->save();
//
//                }    // Insertar en facturas_mov
//                DB::table('facturas_mov')->insert([
//                    'id_fac_pre_prog' => $historial->id_fac_pre_prog, // Usar el ID de la nueva factura creada
//                    'fac_envio_valpago' => Carbon::now('America/Lima'), // Establecer la fecha de envío
//                    'id_users_responsable' => Auth::id(), // Asignar el ID del usuario responsable
//                ]);
//            }
//            DB::commit();
//            $this->selectedGuias = [];
//            session()->flash('success', 'Guías enviadas correctamente.');
//        } catch (\Exception $e) {
//            DB::rollBack();
//            session()->flash('error', 'Ocurrió un error al guardar las facturas: ' . $e->getMessage());
//        }
//    }

    public function listar_detallesf($nro_doc) {
        // Encuentra la guía en las seleccionadas
        $guia = collect($this->selectedGuias)->first(function ($f) use ($nro_doc) {
            return isset($f['nro_doc']) && $f['nro_doc'] === $nro_doc;
        });

        if ($guia) {
            $this->detalleFactura = $guia;
        } else {
            $this->detalleFactura = null;
        }
    }
    public function eliminarGuia($SERIE, $NUMERO)
    {
        $this->selectedGuias = array_filter($this->selectedGuias, function ($guia) use ($SERIE, $NUMERO) {
            return !($guia->SERIE === $SERIE && $guia->NUMERO === $NUMERO);
        });

        // Convertir el array filtrado en una colección de objetos nuevamente
        $this->selectedGuias = array_values(array_map(fn($guia) => (object) $guia, $this->selectedGuias));
    }
}
