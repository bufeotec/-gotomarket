<?php

namespace App\Livewire\Programacioncamiones;

use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\Logs;
use App\Models\DespachoVenta;
use App\Models\Departamento;
use App\Models\Refacturacion;

class Refacturaciones extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $despachoventas;
    private $departamentos;
    private $refacturacion;
    public function __construct(){
        $this->logs = new Logs();
        $this->despachoventas = new DespachoVenta();
        $this->departamentos = new Departamento();
        $this->refacturacion = new Refacturacion();
    }
    public $search_refactutacion;
    public $pagination_refactutacion = 10;
    public $id_refacturacion = "";
    public $id_despacho_venta = "";
    public $cftd = "";
    public $cfnumser = "";
    public $cfnumdoc = "";
    public $factura = "";
    public $grefecemision = "";
    public $cnomcli = "";
    public $cfcodcli = "";
    public $guia = "";
    public $cfimporte = "";
    public $total_kg = "";
    public $total_volumen = "";
    public $direccion_llegada = "";
    public $departamento = "";
    public $provincia = "";
    public $distrito = "";
    public $refacturacion_estado = "";

    public function render(){
        $refac = $this->despachoventas->listar_facturas_estado_cinco();
        $listar_departamento = $this->departamentos->lista_departamento();
        $listar_refactura = $this->refacturacion->listar_refacturacion($this->search_refactutacion, $this->pagination_refactutacion);
        return view('livewire.programacioncamiones.refacturaciones', compact('refac', 'listar_departamento', 'listar_refactura'));
    }

    public function clear_form_refacturacion(){
        $this->id_refacturacion = "";
        $this->id_despacho_venta = "";
        $this->cftd = "";
        $this->cfnumser = "";
        $this->cfnumdoc = "";
        $this->factura = "";
        $this->grefecemision = "";
        $this->cnomcli = "";
        $this->cfcodcli = "";
        $this->guia = "";
        $this->cfimporte = "";
        $this->total_kg = "";
        $this->total_volumen = "";
        $this->direccion_llegada = "";
        $this->departamento = "";
        $this->provincia = "";
        $this->distrito = "";
    }

    public function cargar_datos_factura(){
        if ($this->id_despacho_venta) {
            // Obtener los datos de la factura seleccionada
            $factura = DespachoVenta::find($this->id_despacho_venta);
            if ($factura) {
                $this->cftd = $factura->despacho_venta_cftd;
                $this->cfnumser = $factura->despacho_venta_cfnumser;
                $this->cfnumdoc = $factura->despacho_venta_cfnumdoc;
                $this->factura = $factura->despacho_venta_factura;
                $this->grefecemision = $factura->despacho_venta_grefecemision;
                $this->cnomcli = $factura->despacho_venta_cnomcli;
                $this->cfcodcli = $factura->despacho_venta_cfcodcli;
                $this->guia = $factura->despacho_venta_guia;
                $this->cfimporte = $factura->despacho_venta_cfimporte;
                $this->total_kg = $factura->despacho_venta_total_kg;
                $this->total_volumen = $factura->despacho_venta_total_volumen;
                $this->direccion_llegada = $factura->despacho_venta_direccion_llegada;
                $this->departamento = $factura->despacho_venta_departamento;
                $this->provincia = $factura->despacho_venta_provincia;
                $this->distrito = $factura->despacho_venta_distrito;

            }
        }
    }

    public function saveRefacturacion(){
        try {
            $this->validate([
                'id_despacho_venta' => 'required|integer',
                'cftd' => 'required|string',
                'cfnumser' => 'required|string',
                'cfnumdoc' => 'required|string',
                'factura' => 'required|string',
                'grefecemision' => 'required|date',
                'cnomcli' => 'required|string',
                'cfcodcli' => 'required|string',
                'guia' => 'required|string',
                'cfimporte' => 'required|string',
                'total_kg' => 'required|numeric',
                'total_volumen' => 'required|numeric',
                'direccion_llegada' => 'required|string',
                'departamento' => 'required|string',
                'provincia' => 'required|string',
                'distrito' => 'required|string',
                'refacturacion_estado' => 'nullable|integer',
                'id_refacturacion' => 'nullable|integer',
            ], [
                'id_despacho_venta.required' => 'Debes seleccionar un despacho de venta.',
                'id_despacho_venta.integer' => 'El despacho de venta debe ser un número entero.',

                'cftd.required' => 'El campo "cftd" es obligatorio.',
                'cftd.string' => 'El campo "cftd" debe ser una cadena de texto.',

                'cfnumser.required' => 'El campo "cfnumser" es obligatorio.',
                'cfnumser.string' => 'El campo "cfnumser" debe ser una cadena de texto.',

                'cfnumdoc.required' => 'El campo "cfnumdoc" es obligatorio.',
                'cfnumdoc.string' => 'El campo "cfnumdoc" debe ser una cadena de texto.',

                'factura.required' => 'El campo "factura" es obligatorio.',
                'factura.string' => 'El campo "factura" debe ser una cadena de texto.',

                'grefecemision.required' => 'La fecha de emisión es obligatoria.',
                'grefecemision.date' => 'La fecha de emisión debe tener un formato de fecha válido.',

                'cnomcli.required' => 'El nombre del cliente es obligatorio.',
                'cnomcli.string' => 'El nombre del cliente debe ser una cadena de texto.',

                'cfcodcli.required' => 'El código del cliente es obligatorio.',
                'cfcodcli.string' => 'El código del cliente debe ser una cadena de texto.',

                'guia.required' => 'El campo "guía" es obligatorio.',
                'guia.string' => 'El campo "guía" debe ser una cadena de texto.',

                'cfimporte.required' => 'El importe es obligatorio.',
                'cfimporte.string' => 'El importe debe ser una cadena de texto.',

                'total_kg.required' => 'El total de kilogramos es obligatorio.',
                'total_kg.numeric' => 'El total de kilogramos debe ser un número válido.',

                'total_volumen.required' => 'El total del volumen es obligatorio.',
                'total_volumen.numeric' => 'El total del volumen debe ser un número válido.',

                'direccion_llegada.required' => 'La dirección de llegada es obligatoria.',
                'direccion_llegada.string' => 'La dirección de llegada debe ser una cadena de texto.',

                'departamento.required' => 'El departamento es obligatorio.',
                'departamento.string' => 'El departamento debe ser una cadena de texto.',

                'provincia.required' => 'La provincia es obligatoria.',
                'provincia.string' => 'La provincia debe ser una cadena de texto.',

                'distrito.required' => 'El distrito es obligatorio.',
                'distrito.string' => 'El distrito debe ser una cadena de texto.',

                'refacturacion_estado.integer' => 'El estado de refacturación debe ser un número entero.',

                'id_refacturacion.integer' => 'El identificador de refacturación debe ser un número entero.',
            ]);

            if (!$this->id_refacturacion) { // INSERT
                if (!Gate::allows('create_refacturacion')) {
                    session()->flash('error', 'No tiene permisos para crear.');
                    return;
                }

                $microtime = microtime(true);
                DB::beginTransaction();
                $refac_save = new Refacturacion();
                $refac_save->id_users = Auth::id();
                $refac_save->id_despacho_venta = $this->id_despacho_venta;
                $refac_save->refacturacion_cftd = $this->cftd;
                $refac_save->refacturacion_cfnumser = $this->cfnumser;
                $refac_save->refacturacion_cfnumdoc = $this->cfnumdoc;
                $refac_save->refacturacion_factura = $this->factura;
                $refac_save->refacturacion_grefecemision = $this->grefecemision;
                $refac_save->refacturacion_cnomcli = $this->cnomcli;
                $refac_save->refacturacion_cfcodcli = $this->cfcodcli;
                $refac_save->refacturacion_guia = $this->guia;
                $refac_save->refacturacion_cfimporte = $this->cfimporte;
                $refac_save->refacturacion_total_kg = $this->total_kg;
                $refac_save->refacturacion_total_volumen = $this->total_volumen;
                $refac_save->refacturacion_direccion_llegada = $this->direccion_llegada;
                $refac_save->refacturacion_departamento = $this->departamento;
                $refac_save->refacturacion_provincia = $this->provincia;
                $refac_save->refacturacion_distrito = $this->distrito;
                $refac_save->refacturacion_estado = 1;
                $refac_save->refacturacion_microtime = $microtime;

                if ($refac_save->save()) {
                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Registro guardado correctamente.');

                } else {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar el registro.');
                    return;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }
}
