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
    public function seleccionarGuia($SERIE, $NUMERO) {
        if (!is_array($this->selectedGuias)) {
            $this->selectedGuias = [];
        }

        $comprobanteExiste = collect($this->selectedGuias)->first(function ($guia) use ($SERIE, $NUMERO) {
            return isset($guia->SERIE, $guia->NUMERO) && $guia->SERIE === $SERIE && $guia->NUMERO === $NUMERO;
        });

        if ($comprobanteExiste) {
            session()->flash('error', 'Esta guía ya fue seleccionada.');
            return;
        }

        $guia = collect($this->filteredGuias)->first(function ($guia_) use ($SERIE, $NUMERO) {
            return $guia_->SERIE === $SERIE && $guia_->NUMERO === $NUMERO;
        });

        $this->selectedGuias[] = (object) [
            'SERIE' => $SERIE,
            'NUMERO' => $NUMERO,
            'PESO' => $guia->PESO,
            'VOLUMEN' => $guia->VOLUMEN,
            'NOMBRE_CLIENTE' => $guia->{'NOMBRE CLIENTE'},
            'IMPORTE' => 0,
            'RUC_CLIENTE' => $guia->{'RUC CLIENTE'},
            'FECHA_EMISION' => $guia->{'FECHA EMISION'},
            'DIRECCION_LLEGADA' => $guia->{'DIRECCION LLEGADA'},
            'DEPARTAMENTO_LLEGADA' => $guia->{'DEPARTAMENTO LLEGADA'},
            'PROVINCIA_LLEGADA' => $guia->{'PROVINCIA LLEGADA'},
            'DISTRITO_LLEGADA' => $guia->{'DISTRITO LLEGADA'},
        ];


        $this->filteredGuias = collect($this->filteredGuias)->reject(function ($guia_) use ($SERIE, $NUMERO) {
            return $guia_->SERIE === $SERIE && $guia_->NUMERO === $NUMERO;
        })->values();
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
