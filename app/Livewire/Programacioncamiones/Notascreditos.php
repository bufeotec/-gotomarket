<?php

namespace App\Livewire\Programacioncamiones;

use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Logs;
use App\Models\DespachoVenta;
use App\Models\Notacredito;
use App\Models\Server;
use App\Models\Notacreditoguia;

class Notascreditos extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $despachoventa;
    private $notacredito;
    private $server;
    private $notacreditoguia;
    public function __construct(){
        $this->logs = new Logs();
        $this->despachoventa = new DespachoVenta();
        $this->notacredito = new Notacredito();
        $this->server = new Server();
        $this->notacreditoguia = new Notacreditoguia();
    }
    public $search_nota_credito;
    public $pagination_nota_credito = 10;
    public $id_nota_credito = "";
    public $id_despacho_venta = "";
    public $not_cre_guia_motivo = "";
    public $not_cre_guia_motivo_descripcion = "";
    public $nota_credito_estado = "";
    public $messageNotCret;
    public $despacho_venta;
    public $search_factura = "";
    protected $listeners = ['refreshComponent' => 'render'];
    public $desde;
    public $hasta;
    public $filteredGuias = [];
    public $selectedGuias = [];
    public function mount(){
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');

        $this->selectedGuias = [];
    }

    public function render(){
        $fac_despacho = $this->despachoventa->listar_despacho_nota_credito($this->id_despacho_venta);
        $listar_nota_credito = $this->notacredito->listar_nota_credito_activo($this->search_nota_credito, $this->pagination_nota_credito);
        return view('livewire.programacioncamiones.notascreditos', compact('listar_nota_credito', 'fac_despacho'));
    }

    public function buscar_comprobantes(){
        // Verificar si no hay fechas ni búsqueda
        if (empty($this->desde) && empty($this->hasta) && empty($this->searchFactura)) {
            session()->flash('error-guia', 'Debe ingresar las fechas de búsqueda.');
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
                session()->flash('error-guia', 'Las fechas deben ser a partir de 2025.');
                return; // Salir del método si la validación falla
            }
        }

//        $datosResult = $this->server->listar_comprobantes_listos_local($this->searchFactura, $this->desde, $this->hasta);
        $documento_guia = $this->server->listar_notas_credito_ss($this->desde, $this->hasta);
        $this->filteredGuias = $documento_guia;
        if (!$documento_guia) {
            $this->filteredGuias = [];
        }
    }

    public function seleccionar_guia($TIPO_DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO){
        // Validar que la guia no exista en el array selectedGuias
        $comprobanteExiste = collect($this->selectedGuias)->first(function ($factura) use ($TIPO_DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO) {
            return $factura['TIPO_DOCUMENTO'] === $TIPO_DOCUMENTO
                && $factura['SERIE'] === $SERIE
                && $factura['NUMERO_DOCUMENTO'] === $NUMERO_DOCUMENTO;
        });

        if ($comprobanteExiste) {
            // Mostrar un mensaje de error si la guia ya fue agregada
            session()->flash('error', 'Esta guía ya fue agregado.');
            return;
        }

        // Buscar la guia en el array filteredGuias
        $factura = $this->filteredGuias->first(function ($f) use ($TIPO_DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO) {
            return $f->TIPO_DOCUMENTO === $TIPO_DOCUMENTO
                && $f->SERIE === $SERIE
                && $f->NUMERO_DOCUMENTO === $NUMERO_DOCUMENTO;
        });

        // Agregar la factura seleccionada y actualizar el peso y volumen total
        $this->selectedGuias[] = [
            'TIPO_DOCUMENTO' => $TIPO_DOCUMENTO,
            'SERIE' => $SERIE,
            'NUMERO_DOCUMENTO' => $NUMERO_DOCUMENTO,
            'CODIGO_ARTICULO' => $factura->CODIGO_ARTICULO,
            'DESCRIPCION_ARTICULO' => $factura->DESCRIPCION_ARTICULO,
            'LOTE' => $factura->LOTE,
            'CANTIDAD' => $factura->CANTIDAD,
            'PRECIO_VENTA' => $factura->PRECIO_VENTA,
            'VALOR_VENTA' => $factura->VALOR_VENTA,
            'CODIGO_CLIENTE' => $factura->CODIGO_CLIENTE,
            'NOMBRE_CLIENTE' => $factura->NOMBRE_CLIENTE,
            'TIPO_DOCUMENTO_FACTURA' => $factura->TIPO_DOCUMENTO_FACTURA,
            'SERIE_FACTURA' => $factura->SERIE_FACTURA,
            'NUMERO_DOCUMENTO_FACTURA' => $factura->NUMERO_DOCUMENTO_FACTURA,
            'ESTADO' => $factura->ESTADO,
            'TIPO_REFERENCIA' => $factura->TIPO_REFERENCIA,
            'SERIE_REFERENCIA' => $factura->SERIE_REFERENCIA,
            'DOCUMENTO_REFERENCIA' => $factura->DOCUMENTO_REFERENCIA,
        ];

        // Eliminar la guia de la lista de guias filtradas
//        $this->filteredGuias = $this->filteredGuias->filter(function ($f) use ($NUMERO_DOCUMENTO) {
//            return $f->NUMERO_DOCUMENTO !== $NUMERO_DOCUMENTO;
//        });
    }

    public function eliminar_guia_seleccionada($TIPO_DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO){
        // Encuentra la guia en las seleccionadas
        $factura = collect($this->selectedGuias)->first(function ($f) use ($TIPO_DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO) {
            return $f['TIPO_DOCUMENTO'] === $TIPO_DOCUMENTO && $f['SERIE'] === $SERIE && $f['NUMERO_DOCUMENTO'] === $NUMERO_DOCUMENTO;
        });

        if ($factura) {
            // Elimina la guia de la lista seleccionada
            $this->selectedGuias = collect($this->selectedGuias)
                ->reject(function ($f) use ($TIPO_DOCUMENTO, $SERIE, $NUMERO_DOCUMENTO) {
                    return $f['TIPO_DOCUMENTO'] === $TIPO_DOCUMENTO && $f['SERIE'] === $SERIE && $f['NUMERO_DOCUMENTO'] === $NUMERO_DOCUMENTO;
                })
                ->values()
                ->toArray();
        }
    }

    public function clear_form_nota_credito(){
        $this->id_nota_credito = "";
        $this->id_despacho_venta = "";
        $this->nota_credito_motivo = "";
        $this->nota_credito_motivo_descripcion = "";
    }

    public function edit_data($id){
        $notaCredEdit = Notacredito::find(base64_decode($id));
        if ($notaCredEdit) {
            $this->id_despacho_venta = $notaCredEdit->id_despacho_venta;
            $this->nota_credito_motivo = $notaCredEdit->nota_credito_motivo;
            $this->nota_credito_motivo_descripcion = $notaCredEdit->nota_credito_motivo_descripcion;
            $this->id_nota_credito = $notaCredEdit->id_nota_credito;

            $this->dispatch('updateSelect2', value: $notaCredEdit->id_despacho_venta);
        }
    }

//    public function saveNotaCredito() {
//        try {
//            $this->validate([
//                'nota_credito_motivo' => 'required|integer',
//                'nota_credito_motivo_descripcion' => 'required|string',
//                'id_despacho_venta' => 'required|integer',
//                'id_nota_credito' => 'nullable|integer',
//            ], [
//                'nota_credito_motivo.required' => 'Debes seleccionar un motivo.',
//                'nota_credito_motivo.integer' => 'El motivo debe ser un número entero.',
//
//                'nota_credito_motivo_descripcion.required' => 'La descripción es obligatoria.',
//                'nota_credito_motivo_descripcion.string' => 'La descripción debe ser una cadena de texto.',
//
//                'id_despacho_venta.required' => 'Debes seleccionar una factura.',
//                'id_despacho_venta.integer' => 'La factura debe ser un número entero.',
//
//                'id_nota_credito.integer' => 'El identificador debe ser un número entero.',
//            ]);
//
//            if (!$this->id_nota_credito) {
//                if (!Gate::allows('create_nota_credito')) {
//                    session()->flash('error', 'No tiene permisos para crear.');
//                    return;
//                }
//
//                $microtime = microtime(true);
//                DB::beginTransaction();
//                $notacredito_save = new Notacredito();
//                $notacredito_save->id_users = Auth::id();
//                $notacredito_save->id_despacho_venta = $this->id_despacho_venta;
//                $notacredito_save->nota_credito_motivo = $this->nota_credito_motivo;
//                $notacredito_save->nota_credito_motivo_descripcion = $this->nota_credito_motivo_descripcion;
//                $notacredito_save->nota_credito_estado = 1;
//                $notacredito_save->nota_credito_microtime = $microtime;
//                $notacredito_save->nota_credito_estado_aprobacion = 0;
//
//                if ($notacredito_save->save()) {
//                    // Actualizar el estado del despacho_venta
//                    DB::table('despacho_ventas')
//                        ->where('id_despacho_venta', $this->id_despacho_venta)
//                        ->update(['despacho_detalle_estado_entrega' => 5]);
//
//                    DB::commit();
//                    $this->dispatch('hideModal');
//                    session()->flash('success', 'Registro guardado correctamente.');
//                } else {
//                    DB::rollBack();
//                    session()->flash('error', 'Ocurrió un error al guardar.');
//                    return;
//                }
//            } else { // UPDATE
//                if (!Gate::allows('update_nota_credito')) {
//                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
//                    return;
//                }
//                DB::beginTransaction();
//                $notaCredito_update = Notacredito::findOrFail($this->id_nota_credito);
//
//                // Verificar si el estado de aprobación es 1 y si los campos relevantes han cambiado
//                if (
//                    $notaCredito_update->nota_credito_estado_aprobacion == 1 &&
//                    ($notaCredito_update->nota_credito_motivo != $this->nota_credito_motivo ||
//                        $notaCredito_update->nota_credito_motivo_descripcion != $this->nota_credito_motivo_descripcion)
//                ) {
//                    $notaCredito_update->nota_credito_estado_aprobacion = 0;
//                }
//
//                $notaCredito_update->id_despacho_venta = $this->id_despacho_venta;
//                $notaCredito_update->nota_credito_motivo = $this->nota_credito_motivo;
//                $notaCredito_update->nota_credito_motivo_descripcion = $this->nota_credito_motivo_descripcion;
//
//                if (!$notaCredito_update->save()) {
//                    DB::rollBack();
//                    session()->flash('error', 'No se pudo actualizar el registro.');
//                    return;
//                }
//
//                DB::commit();
//                $this->dispatch('hideModal');
//                session()->flash('success', 'Registro actualizado correctamente.');
//            }
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            $this->setErrorBag($e->validator->errors());
//        } catch (\Exception $e) {
//            DB::rollBack();
//            $this->logs->insertarLog($e);
//            session()->flash('error', 'Ocurrió un error al guardar el registro: ' . $e->getMessage());
//        }
//    }

    public function cambio_estado($id_notCre){
        $id = base64_decode($id_notCre);
        if ($id) {
            $this->id_nota_credito = $id;
            $this->messageNotCret = "¿Está seguro de aprobar esta nota de credito?";
        }
    }

    public function cambiar_estado_aprobacion(){
        try {
            if (!Gate::allows('cambiar_estado_aprobacion')) {
                session()->flash('error_pre_pro', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }
            $this->validate([
                'id_nota_credito' => 'required|integer',
            ], [
                'id_nota_credito.required' => 'El identificador es obligatorio.',
                'id_nota_credito.integer' => 'El identificador debe ser un número entero.',
            ]);
            DB::beginTransaction();
            $factura = Notacredito::find($this->id_nota_credito);

            if ($factura) {
                $factura->nota_credito_estado_aprobacion = 1;

                if ($factura->save()) {
                    DB::commit();
                    $this->dispatch('hideModalDelete');
                    session()->flash('success', 'Nota de credito aprobada.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo cambiar el estado de la factura.');
                }
            } else {
                DB::rollBack();
                session()->flash('error', 'La nota de credito no existe.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al aceptar la factura. Por favor, inténtelo nuevamente.');
        }
    }

    public function saveNotaCredito() {
        try {
            if (!Gate::allows('guardar_nota_credito')) {
                session()->flash('error', 'No tiene permisos para crear una programación local.');
                return;
            }
            // Validar que haya facturas seleccionadas y un estado seleccionado
            $this->validate([
                'selectedGuias' => 'required|array|min:1',
            ], [
                'selectedGuias.required' => 'Debes seleccionar al menos una factura.',
                'selectedGuias.min' => 'Debes seleccionar al menos una factura.',
            ]);

            DB::beginTransaction();

            foreach ($this->selectedGuias as $factura) {
                // Verificar si la factura ya existe en la tabla
                $facturaExistente = Notacreditoguia::where('not_cre_guia_tipo_doc', $factura['TIPO_DOCUMENTO'])
                    ->where('not_cre_guia_serie', $factura['SERIE'])
                    ->where('not_cre_guia_num_doc', $factura['NUMERO_DOCUMENTO'])
                    ->first();

                if (!$facturaExistente) { // Cambia esta condición
                    // Si no existe, crear un nuevo registro
                    $nuevaFactura = new Notacreditoguia();
                    $nuevaFactura->id_users = Auth::id();
                    $nuevaFactura->not_cre_guia_motivo = $this->not_cre_guia_motivo;
                    $nuevaFactura->not_cre_guia_motivo_descripcion = $this->not_cre_guia_motivo_descripcion;
                    $nuevaFactura->not_cre_guia_tipo_doc = $factura['TIPO_DOCUMENTO'] ?: null;
                    $nuevaFactura->not_cre_guia_serie = $factura['SERIE'] ?: null;
                    $nuevaFactura->not_cre_guia_num_doc = $factura['NUMERO_DOCUMENTO'] ?: null;
                    $nuevaFactura->not_cre_guia_codigo_articulo = $factura['CODIGO_ARTICULO'] ?: null;
                    $nuevaFactura->not_cre_guia_desc_articulo = $factura['DESCRIPCION_ARTICULO'] ?: null;
                    $nuevaFactura->not_cre_guia_lote = $factura['LOTE'] ?: null;
                    $nuevaFactura->not_cre_guia_cantidad = $factura['CANTIDAD'] ?: null;
                    $nuevaFactura->not_cre_guia_precio_venta = $factura['PRECIO_VENTA'] ?: null;
                    $nuevaFactura->not_cre_guia_valor_venta = $factura['VALOR_VENTA'] ?: null;
                    $nuevaFactura->not_cre_guia_codigo_cliente = $factura['CODIGO_CLIENTE'] ?: null;
                    $nuevaFactura->not_cre_guia_nombre_cliente = $factura['NOMBRE_CLIENTE'] ?: null;
                    $nuevaFactura->not_cre_guia_tipo_doc_factura = $factura['TIPO_DOCUMENTO_FACTURA'] ?: null;
                    $nuevaFactura->not_cre_guia_serie_factura = $factura['SERIE_FACTURA'] ?: null;
                    $nuevaFactura->not_cre_guia_num_doc_factura = $factura['NUMERO_DOCUMENTO_FACTURA'] ?: null;
                    $nuevaFactura->not_cre_guia_estado_guia = $factura['ESTADO'] ?: null;
                    $nuevaFactura->not_cre_guia_tipo_referencia = $factura['TIPO_REFERENCIA'] ?: null;
                    $nuevaFactura->not_cre_guia_serie_referencia = $factura['SERIE_REFERENCIA'] ?: null;
                    $nuevaFactura->not_cre_guia_doc_referencia = $factura['DOCUMENTO_REFERENCIA'] ?: null;
                    $nuevaFactura->save();
                }
            }
            DB::commit();
            // Limpiar las facturas seleccionadas y el estado
            $this->dispatch('hideModal');
            $this->selectedGuias = [];
            session()->flash('success', 'Facturas procesadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al guardar las facturas: ' . $e->getMessage());
        }
    }

}
