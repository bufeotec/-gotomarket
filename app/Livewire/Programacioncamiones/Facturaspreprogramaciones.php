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
    public function seleccionarGuia($NRO_DOC) {
        // Asegúrate de que $selectedGuias sea un array
        if (!is_array($this->selectedGuias)) {
            $this->selectedGuias = [];
        }

        // Verifica si la guía ya está seleccionada
        $comprobanteExiste = collect($this->selectedGuias)->first(function ($guia) use ($NRO_DOC) {
            return isset($guia['NRO_DOC']) && $guia['NRO_DOC'] === $NRO_DOC;
        });

        if ($comprobanteExiste) {
            session()->flash('error', 'Esta guía ya fue seleccionada.');
            return;
        }

        // Busca la guía en $filteredGuias
        $guia = collect($this->filteredGuias)->first(function ($guia_) use ($NRO_DOC) {
            return $guia_->NRO_DOC === $NRO_DOC;
        });

        if ($guia) {
            // Agrega la guía a $selectedGuias
            $this->selectedGuias[] = [
                'NRO_DOC' => $guia->NRO_DOC,
                'PESO_G' => $guia->PESO_G,
                'VOLUMEN_TOTAL_CM3' => $guia->VOLUMEN_TOTAL_CM3,
                'NOMBRE_CLIENTE' => $guia->NOMBRE_CLIENTE,
                'IMPORTE_TOTAL' => $guia->IMPORTE_TOTAL,
                'RUC_CLIENTE' => $guia->RUC_CLIENTE,
                'FECHA_EMISION' => $guia->FECHA_EMISION,
                'DIRECCION_ENTREGA' => $guia->DIRECCION_ENTREGA,
                'DEPARTAMENTO' => $guia->DEPARTAMENTO,
                'PROVINCIA' => $guia->PROVINCIA,
                'DISTRITO' => $guia->DISTRITO,
            ];

            // Elimina la guía de $filteredGuias
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
    public function guardarGuias() {
        try {
            DB::beginTransaction();
            foreach ($this->selectedGuias as $g) {
                $existe_guia = $this->facpreprog->listar_guia_existente($g->SERIE,$g->NUMERO);
                if($existe_guia){
                    session()->flash('error', 'Esta guía ya existe en el intranet.');
                    return;
                }else{
                    $guia= new Facturaspreprogramacion();
                    $guia->guia_serie = $g->SERIE;
                    $guia->guia_numero = $g->NUMERO;
                    $guia->guia_peso = $g->PESO;
                    $guia->guia_volumen = $g->VOLUMEN;
                    $guia->guia_nombre_cliente = $g->NOMBRE_CLIENTE;
                    $guia->guia_importe = $g->IMPORTE;
                    $guia->guia_ruc_cliente = $g->RUC_CLIENTE;
                    $guia->guia_fecha_emision = $g->FECHA_EMISION;
                    $guia->guia_direccion_llegada = $g->DIRECCION_LLEGADA;
                    $guia->guia_departamento_llegada = $g->DEPARTAMENTO_LLEGADA;
                    $guia->guia_provincia_llegada = $g->PROVINCIA_LLEGADA;
                    $guia->guia_distrito_llegada = $g->DISTRITO_LLEGADA;
                    $guia->guia_estado = $this->estado_envio;
                    $guia->guia_fecha_creacion = now();
                    $guia->save();
                }
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
