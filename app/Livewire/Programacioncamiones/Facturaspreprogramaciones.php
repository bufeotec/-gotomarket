<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\TipoServicio;
use App\Models\Server;
use App\Models\Facturaspreprogramacion;

class Facturaspreprogramaciones extends Component
{
    private $logs;
    private $tiposervicio;
    private $server;
    private $facpreprog;
    public function __construct(){
        $this->logs = new Logs();
        $this->tiposervicio = new TipoServicio();
        $this->server = new Server();
        $this->facpreprog = new Facturaspreprogramacion();
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
        $this->desde = null;
        $this->hasta = null;
    }

    public function render(){
        $listar_tipo_servicios = $this->tiposervicio->listar_tipo_servicios();
        return view('livewire.programacioncamiones.facturaspreprogramaciones', compact('listar_tipo_servicios'));
    }

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

    public function guardarFacturas(){
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

            $contadorError = 0;
            DB::beginTransaction();
            foreach ($this->selectedFacturas as $factura) {
                $existe = DB::table('facturas_pre_programaciones')
                    ->where('fac_pre_prog_cftd', $factura['CFTD'])
                    ->where('fac_pre_prog_cfnumser', $factura['CFNUMSER'])
                    ->where('fac_pre_prog_cfnumdoc', $factura['CFNUMDOC'])
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

            foreach ($this->selectedFacturas as $factura) {
                // Crear una nueva instancia del modelo y guardar los datos
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

                if (!$nuevaFactura->save()) {
                    session()->flash('Error al guardar una factura.');
                }
            }
            DB::commit();
            // Limpiar las facturas seleccionadas y el estado
            $this->selectedFacturas = [];
            $this->estado_envio = null;
            session()->flash('success', 'Facturas guardadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al guardar las facturas: ' . $e->getMessage());
        }
    }

}
