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
use App\Models\Notacreditodetalle;
use App\Models\Guia;

class Notascreditos extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $despachoventa;
    private $notacredito;
    private $server;
    private $notacreditodetalle;
    private $guia;
    public function __construct(){
        $this->logs = new Logs();
        $this->despachoventa = new DespachoVenta();
        $this->notacredito = new Notacredito();
        $this->server = new Server();
        $this->notacreditodetalle = new Notacreditodetalle();
        $this->guia = new Guia();
    }
    public $search_nota_credito;
    public $pagination_nota_credito = 10;
    public $id_not_cred = "";
    public $not_cred_motivo = "";
    public $not_cred_motivo_descripcion = "";
    public $nota_credito_estado = "";
    public $messageNotCret;
    public $desde;
    public $hasta;
    public $filteredGuias = [];
    public $detallesGuia = [];
    public $selectedGuias = [];
    public $nota_credito_detalle = [];
    public $seleccionarNCS = [];
    public $selectAll = false;
//
    public $select_varios = false;
    public $select_todas_nc = [];
//    PENDIENTES NC
    public $select_varias_guias = false;
    public $select_todas_guias = [];


    public function mount(){
        $this->desde = date('Y-01-01');
        $this->hasta = date('Y-m-d');

        $this->selectedGuias = [];
    }

    public function render(){
//        $listar_nota_credito = $this->notacredito->listar_nota_credito_activo($this->search_nota_credito, $this->pagination_nota_credito);
        $listar_nota_credito = $this->notacredito->listar_nota_credito_intranet();
        $listar_guias_pendientes_nc = $this->guia->litar_guias_pendientes_nc();
        return view('livewire.programacioncamiones.notascreditos', compact('listar_nota_credito', 'listar_guias_pendientes_nc'));
    }

    public function buscar_comprobantes(){

        if (!Gate::allows('buscar_nc_starsoft')) {
            session()->flash('error', 'No tiene permisos para buscar Notas de Crédito.');
            return;
        }

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
        $this->seleccionarNCS = [];
        $this->selectAll = false;
        $this->filteredGuias = $documento_guia;
        if (!$documento_guia) {
            $this->filteredGuias = [];
        }
    }

    public function seleccionar_una_nc_intranet(){
        // Obtener todos los NRO_DOC únicos disponibles
        $allAvailableNros = collect($this->filteredGuias)
            ->pluck('NRO_DOCUMENTO')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Verificar si están todos seleccionados
        $this->selectAll = count($this->seleccionarNCS) === count($allAvailableNros) &&
            count($this->seleccionarNCS) > 0;
    }

    public function seleccionar_todas_nc_intranet(){
        $this->selectAll = !$this->selectAll;

        if ($this->selectAll) {
            // Seleccionar todos los NRO_DOC únicos disponibles
            $this->seleccionarNCS = collect($this->filteredGuias)
                ->pluck('NRO_DOCUMENTO')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        } else {
            // Deseleccionar todos
            $this->seleccionarNCS = [];
        }
    }

    public function seleccionar_nota_credito($NRO_DOCUMENTO){

        if (!Gate::allows('seleccionar_nc_starsoft')) {
            session()->flash('error', 'No tiene permisos para seleccionar las Notas de Crédito.');
            return;
        }

        // Validar que la nota credito no exista en el array selectedGuias
        $comprobanteExiste = collect($this->selectedGuias)->first(function ($factura) use ($NRO_DOCUMENTO) {
            return $factura['NRO_DOCUMENTO'] === $NRO_DOCUMENTO;
        });

        if ($comprobanteExiste) {
            // Mostrar un mensaje de error si la nota credito ya fue agregada
            session()->flash('error', 'Esta guía ya fue agregada.');
            return;
        }

        // Buscar la guia en el array filteredGuias
        $factura = $this->filteredGuias->first(function ($f) use ($NRO_DOCUMENTO) {
            return $f->NRO_DOCUMENTO === $NRO_DOCUMENTO;
        });

        // Verificar si existe una factura en la tabla guias usando DB::table
        $existeEnGuias = DB::table('guias')
            ->where('guia_nro_doc_ref', $factura->NRO_DOCUMENTO_REF)
            ->exists();

        // Agregar la factura seleccionada
        $this->selectedGuias[] = [
            'NRO_DOCUMENTO' => $NRO_DOCUMENTO,
            'ALMACEN_DESTINO' => $factura->ALMACEN_DESTINO,
            'TIPO_DOCUMENTO' => $factura->TIPO_DOCUMENTO,
            'FECHA_EMISION' => $factura->FECHA_EMISION,
            'TIPO_MOVIMIENTO' => $factura->TIPO_MOVIMIENTO,
            'TIPO_DOCUMENTO_REF' => $factura->TIPO_DOCUMENTO_REF,
            'NRO_DOCUMENTO_REF' => $factura->NRO_DOCUMENTO_REF,
            'GLOSA' => $factura->GLOSA,
            'USUARIO' => $factura->USUARIO,
            'CODIGO_CLIENTE' => $factura->CODIGO_CLIENTE,
            'RUC_CLIENTE' => $factura->RUC_CLIENTE,
            'NOMBRE_CLIENTE' => $factura->NOMBRE_CLIENTE,
            'FORMA_DE_PAGO' => $factura->FORMA_DE_PAGO,
            'VENDEDOR' => $factura->VENDEDOR,
            'MONEDA' => $factura->MONEDA,
            'TIPO_DE_CAMBIO' => $factura->TIPO_DE_CAMBIO,
            'ESTADO' => $factura->ESTADO,
            'IMPORTE_TOTAL' => $factura->IMPORTE_TOTAL,
            'existe_en_guias' => $existeEnGuias,
        ];
    }

    public function eliminar_nota_credito_seleccionada($NRO_DOCUMENTO){

        if (!Gate::allows('eliminar_nc_seleccionada')) {
            session()->flash('error', 'No tiene permisos para eliminar la nota seleccionada.');
            return;
        }

        // Encuentra la nota credito en las seleccionadas
        $factura = collect($this->selectedGuias)->first(function ($f) use ($NRO_DOCUMENTO) {
            return $f['NRO_DOCUMENTO'] === $NRO_DOCUMENTO;
        });

        if ($factura) {
            // Elimina la nota credito de la lista seleccionada
            $this->selectedGuias = collect($this->selectedGuias)
                ->reject(function ($f) use ($NRO_DOCUMENTO) {
                    return $f['NRO_DOCUMENTO'] === $NRO_DOCUMENTO;
                })
                ->values()
                ->toArray();
        }
    }

    public function clear_form_nota_credito(){
        $this->id_not_cred = "";
        $this->not_cred_motivo = "";
        $this->not_cred_motivo_descripcion = "";
        $this->selectedGuias = [];
        $this->filteredGuias = [];
        $this->desde = date('Y-01-01');
        $this->hasta = date('Y-m-d');
    }

    public function modal_nota_credito_detalle($id_not_cred) {
        $this->nota_credito_detalle = $this->notacredito->listar_nota_credito_detalle($id_not_cred);
    }

    public function verDetallesGuia($id_guia) {
        // Busca los detalles en la tabla 'guias_detalles' usando el id_guia
        $this->detallesGuia = DB::table('guias_detalles')
            ->where('id_guia', '=', $id_guia)
            ->get()
            ->toArray();

        // Si no hay detalles, asigna un array vacío
        if (empty($this->detallesGuia)) {
            $this->detallesGuia = [];
        }
    }

    public function save_nota_credito() {
        try {
            if (!Gate::allows('guardar_nota_credito')) {
                session()->flash('error', 'No tiene permisos para guardar las notas de créditos.');
                return;
            }

            if (empty($this->seleccionarNCS)) {
                session()->flash('error', 'Debes seleccionar al menos una guía.');
                return;
            }

            DB::beginTransaction();

            // Filtrar las guías que han sido seleccionadas
            $guiasSeleccionadas = collect($this->filteredGuias)->whereIn('NRO_DOCUMENTO', $this->seleccionarNCS);

            foreach ($guiasSeleccionadas as $factura) {
                // Verificar si la factura ya existe en la tabla
                $facturaExistente = Notacredito::where('not_cred_nro_doc', $factura->NRO_DOCUMENTO)
                    ->first();

                if (!$facturaExistente) {
                    // Si no existe, crear un nuevo registro de cabecera
                    $nuevaFactura = new Notacredito();
                    $nuevaFactura->id_users = Auth::id();
                    $nuevaFactura->not_cred_almacen_destino = $factura->ALMACEN_DESTINO ?: null;
                    $nuevaFactura->not_cred_tipo_doc = $factura->TIPO_DOCUMENTO ?: null;
                    $nuevaFactura->not_cred_nro_doc = $factura->NRO_DOCUMENTO ?: null;
                    $nuevaFactura->not_cred_fecha_emision = $factura->FECHA_EMISION ?: null;
                    $nuevaFactura->not_cred_tipo_movimiento = $factura->TIPO_MOVIMIENTO ?: null;
                    $nuevaFactura->not_cred_tipo_doc_ref = $factura->TIPO_DOCUMENTO_REF ?: null;
                    $nuevaFactura->not_cred_nro_doc_ref = $factura->NRO_DOCUMENTO_REF ?: null;
                    $nuevaFactura->not_cred_glosa = $factura->GLOSA ?: null;
                    $nuevaFactura->not_cred_usuario = $factura->USUARIO ?: null;
                    $nuevaFactura->not_cred_codigo_cliente = $factura->CODIGO_CLIENTE ?: null;
                    $nuevaFactura->not_cred_ruc_cliente = $factura->RUC_CLIENTE ?: null;
                    $nuevaFactura->not_cred_nombre_cliente = $factura->NOMBRE_CLIENTE ?: null;
                    $nuevaFactura->not_cred_forma_pago = $factura->FORMA_DE_PAGO ?: null;
                    $nuevaFactura->not_cred_vendedor = $factura->VENDEDOR ?: null;
                    $nuevaFactura->not_cred_moneda = $factura->MONEDA ?: null;
                    $nuevaFactura->not_cred_tipo_cambio = $factura->TIPO_DE_CAMBIO ?: null;
                    $nuevaFactura->not_cred_estado = $factura->ESTADO ?: null;
                    $nuevaFactura->not_cred_importe_total = $factura->IMPORTE_TOTAL ?: null;
                    $nuevaFactura->not_cred_estado_aprobacion = 1;
                    $nuevaFactura->save();

                    // Obtener los detalles de la nota de crédito
                    $detalles = $this->server->listar_notas_credito_detalle_ss($factura->NRO_DOCUMENTO);

                    // Guardar los detalles en la tabla notas_creditos_detalles
                    foreach ($detalles as $detalle) {
                        $nuevoDetalle = new Notacreditodetalle();
                        $nuevoDetalle->id_users = Auth::id();
                        $nuevoDetalle->id_not_cred = $nuevaFactura->id_not_cred;
                        $nuevoDetalle->not_cred_nro_doc = $detalle->NRO_DOCUMENTO ?: null;
                        $nuevoDetalle->not_cred_det_almacen_entrada = $detalle->ALMACEN_ENTRADA ?: null;
                        $nuevoDetalle->not_cred_det_fecha_emision = $detalle->FECHA_EMISION ?: null;
                        $nuevoDetalle->not_cred_det_estado = $detalle->ESTADO ?: null;
                        $nuevoDetalle->not_cred_det_tipo_doc = $detalle->TIPO_DOCUMENTO ?: null;
                        $nuevoDetalle->not_cred_det_nro_doc = $detalle->NRO_DOCUMENTO ?: null;
                        $nuevoDetalle->not_cred_det_nro_linea = $detalle->NRO_LINEA ?: null;
                        $nuevoDetalle->not_cred_det_cod_producto = $detalle->COD_PRODUCTO ?: null;
                        $nuevoDetalle->not_cred_det_descripcion_procd = $detalle->DESCRIPCION_PRODUCTO ?: null;
                        $nuevoDetalle->not_cred_det_lote = $detalle->LOTE ?: null;
                        $nuevoDetalle->not_cred_det_unidad = $detalle->UNIDAD ?: null;
                        $nuevoDetalle->not_cred_det_cantidad = $detalle->CANTIDAD ?: null;
                        $nuevoDetalle->not_cred_det_precio_unit_final_inc_igv = $detalle->PRECIO_UNIT_FINAL_INC_IGV ?: null;
                        $nuevoDetalle->not_cred_det_texto = $detalle->TEXTO ?: null;
                        $nuevoDetalle->not_cred_det_igv_total = $detalle->IGV_TOTAL ?: null;
                        $nuevoDetalle->not_cred_det_importe_total_inc_igv = $detalle->IMPORTE_TOTAL_INC_IGV ?: null;
                        $nuevoDetalle->not_cred_det_moneda = $detalle->MONEDA ?: null;
                        $nuevoDetalle->not_cred_det_tipo_cambio = $detalle->TIPO_DE_CAMBIO ?: null;
                        $nuevoDetalle->not_cred_det_peso_gramos = $detalle->PESO_GRAMOS ?: null;
                        $nuevoDetalle->not_cred_det_volumen = $detalle->VOLUMEN_CM3 ?: null;
                        $nuevoDetalle->not_cred_det_peso_toal_gramos = $detalle->PESO_TOTAL_GRAMOS ?: null;
                        $nuevoDetalle->not_cred_det_volumen_total = $detalle->VOLUMEN_TOTAL_CM3 ?: null;
                        $nuevoDetalle->save();
                    }
                }
            }
            DB::commit();
            // Limpiar los resultados de la búsqueda y las guías seleccionadas
            $this->filteredGuias = [];
            $this->selectedGuias = [];
            $this->search_nota_credito = '';
            $this->seleccionarNCS = [];
            $this->selectAll = false;
            $this->desde = date('Y-01-01');
            $this->hasta = date('Y-m-d');
            // Cerrar el modal y mostrar mensaje de éxito
//            $this->dispatch('hideModal');
            session()->flash('success', 'Notas de Crédito guardadas correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al guardar las facturas: ' . $e->getMessage());
        }
    }
//    *****

    public function seleccionar_varias_nc_codigo(){
        $this->select_varios = !$this->select_varios;

        if ($this->select_varios) {
            $this->select_todas_nc = $this->notacredito->listar_nc_registradas()
                ->pluck('id_not_cred')
                ->toArray();
        } else {
            $this->select_todas_nc = [];
        }
    }

    public function cambiar_estado_codigo_motivo(){
        try {
            // Verifica permisos
            if (!Gate::allows('cambiar_estado_codigo_motivo')) {
                session()->flash('error', 'No tiene permisos para aceptar las guías.');
                return;
            }

            // Validar que al menos una guía esté seleccionada
            if (count($this->select_todas_nc) == 0) {
                session()->flash('error_codigo_nc', 'Debe seleccionar al menos una nota de crédito.');
                return;
            }

            // Validar campos obligatorios
            $this->validate([
                'not_cred_motivo' => 'required|integer|min:1',
                'not_cred_motivo_descripcion' => 'required|string|min:1|max:999'
            ], [
                'not_cred_motivo.required' => 'El código de motivo es obligatorio',
                'not_cred_motivo.integer' => 'El código de motivo debe ser numérico',
                'not_cred_motivo.min' => 'Seleccione un código de motivo válido',

                'not_cred_motivo_descripcion.required' => 'La descripción del motivo es obligatoria',
                'not_cred_motivo_descripcion.min' => 'La descripción debe tener al menos 1 caracteres',
                'not_cred_motivo_descripcion.max' => 'La descripción no debe exceder 999 caracteres'
            ]);

            DB::beginTransaction();

            $guiasActualizadas = 0;

            foreach ($this->select_todas_nc as $id_nc) {
                $nota_credito = Notacredito::find($id_nc);

                if ($nota_credito) {
                    // Actualizar estado de la nota de crédito
                    $nota_credito->not_cred_motivo = $this->not_cred_motivo;
                    $nota_credito->not_cred_motivo_descripcion = $this->not_cred_motivo_descripcion;
                    $nota_credito->not_cred_estado_aprobacion = 2;

                    if(!$nota_credito->save()){
                        DB::rollBack();
                        session()->flash('error', 'Error al actualizar el estado de la nota de crédito.');
                        return;
                    }

                    // Verificar si hay guías vinculadas
                    $guias = Guia::where('guia_nro_doc_ref', $nota_credito->not_cred_nro_doc_ref)->get();

                    foreach ($guias as $guia) {
                        $guia->guia_estado_aprobacion = 14;
                        if($guia->save()) {
                            $guiasActualizadas++;
                        }
                    }
                }
            }

            DB::commit();

            // Mensaje según si hubo guías actualizadas o no
            $mensaje = 'Estado de las notas de crédito se actualizaron correctamente.';
            if ($guiasActualizadas > 0) {
                $mensaje .= ' ' . $guiasActualizadas . ' guía(s) vinculada(s) se le cambiaron el estado.';
            }

            session()->flash('success', $mensaje);
            $this->dispatch('hideModalCodigo');
            // Limpiar selección después de guardar
            $this->select_todas_nc = [];
            $this->select_varios = false;
            $this->not_cred_motivo = "";
            $this->not_cred_motivo_descripcion = "";

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_codigo_nc', 'Ocurrió un error al actualizar las notas de crédito: ' . $e->getMessage());
        }
    }


//    *****

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

    public function cambio_estado($id_notCre){
        $id = base64_decode($id_notCre);
        if ($id) {
            $this->id_not_cred = $id;
            $this->messageNotCret = "¿Está seguro de aprobar esta nota de credito?";
        }
    }

    public function cambiar_estado_aprobacion(){
        try {

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

    // PENDIENTES DE NC
    public function seleccionar_varias_guias_anuladas(){
        $this->select_varias_guias = !$this->select_varias_guias;

        if ($this->select_varias_guias) {
            $this->select_todas_guias = $this->guia->litar_guias_pendientes_nc()
                ->pluck('id_guia')
                ->toArray();
        } else {
            $this->select_todas_guias = [];
        }
    }

    public function cambiar_guia_anuladas(){
        try {
            // Verifica permisos
            if (!Gate::allows('cambiar_guia_anuladas')) {
                session()->flash('error', 'No tiene permisos para aceptar las guías.');
                return;
            }

            // Validar que al menos una guía esté seleccionada
            if (count($this->select_todas_guias) == 0) {
                session()->flash('error_modal_anular_guia', 'Debe seleccionar al menos una nota de crédito.');
                return;
            }

            DB::beginTransaction();

            foreach ($this->select_todas_guias as $id_guia) {
                $nota_credito = Guia::find($id_guia);

                if ($nota_credito) {
                    $nota_credito->guia_estado_aprobacion = 14;

                    if(!$nota_credito->save()){
                        DB::rollBack();
                        session()->flash('error', 'Error al actualizar el estado de la guía.');
                        return;
                    }
                }
            }

            DB::commit();
            session()->flash('success', 'Guías anuladas correctamente.');
            $this->dispatch('hideModalAnularGuia');
            // Limpiar selección después de guardar
            $this->select_todas_guias = [];
            $this->select_varias_guias = false;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_codigo_nc', 'Ocurrió un error al actualizar las guías: ' . $e->getMessage());
        }
    }
}
