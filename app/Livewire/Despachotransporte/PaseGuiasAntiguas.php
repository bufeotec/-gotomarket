<?php

namespace App\Livewire\Despachotransporte;

use App\Models\Factura;
use App\Models\FacturaDetalle;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Server;
use App\Models\Guia;
use App\Models\Guiadetalle;
use App\Models\DespachoVenta;

class PaseGuiasAntiguas extends Component
{
    private $despachoventas;
    private $logs;
    private $server;
    public $guias_antiguas;
    public function __construct(){
        $this->logs = new Logs();
        $this->server = new Server();
        $this->despachoventas = new DespachoVenta();
        }
    public function render()
    {
        $Guias_factura = DB::table('guias')
            ->whereNotNull('guia_nro_doc')
            ->whereNotNull('guia_nro_doc_ref')
            ->get();

        foreach ($Guias_factura as $g_f){
            //Validar de que no se haya registrado ya
            $validar_existe_fac = DB::table('facturas')
//                ->where('factura_tipo_documento', $g_f->guia_tipo_doc_ref)
                ->where('factura_numero_documento', $g_f->guia_nro_doc_ref)
                ->first();
            if(!$validar_existe_fac){
                $Factura_mt = microtime(true);
                $Factura = new Factura();
                $Factura->id_users = $g_f->id_users;
                $Factura->factura_codigo_cliente = $g_f->guia_cod_cliente;
                $Factura->factura_moneda = $g_f->guia_moneda;
                $Factura->factura_tipo_documento = $g_f->guia_tipo_doc_ref;
                $Factura->factura_numero_documento = $g_f->guia_nro_doc_ref;
                $Factura->factura_fecha_emision = \Carbon\Carbon::parse($g_f->guia_fecha_emision)->format('Y-m-d');
                $Factura->factura_tipo_movimiento = $g_f->guia_tipo_movimiento;
                $Factura->factura_tipo_cambio = $g_f->guia_tipo_cambio;
                $Factura->factura_total_con_igv = $g_f->guia_importe_total;
                $Factura->factura_total_sin_igv = $g_f->guia_importe_total_sin_igv;
                $Factura->factura_tipo_pago = $g_f->guia_forma_pago;
                $Factura->factura_estado = 1;
                $Factura->factura_mt = $Factura_mt;
                if($Factura->save()){
                    $Factura_guardada = DB::table('facturas')
                        ->where('factura_mt', $Factura_mt)
                        ->first();

                    $Guias_factura_detalle = DB::table('guias_detalles')
                        ->where('id_guia', $g_f->id_guia)
                        ->get();

                    foreach ($Guias_factura_detalle as $gf_d){
                        $FacturaDetalle = new FacturaDetalle();
                        $FacturaDetalle->id_factura = $Factura_guardada->id_factura;
                        $FacturaDetalle->id_producto = null;
                        $FacturaDetalle->detalle_factura_descripcion = $gf_d->guia_det_descripcion_producto;
                        $FacturaDetalle->detalle_factura_codigo_producto = $gf_d->guia_det_cod_producto;
                        $FacturaDetalle->detalle_factura_lote = $gf_d->guia_det_lote;
                        $FacturaDetalle->detalle_factura_unidad = $gf_d->guia_det_unidad;
                        $FacturaDetalle->detalle_factura_cantidad = $gf_d->guia_det_cantidad;
                        $FacturaDetalle->detalle_factura_precio_unit_final_inc_igv = $gf_d->guia_det_precio_unit_final_inc_igv;
                        $FacturaDetalle->detalle_factura_precio_unit_antes_descuente_inc_igv = $gf_d->guia_det_precio_unit_antes_descuente_inc_igv;
                        $FacturaDetalle->detalle_factura_igv_total = $gf_d->guia_det_igv_total;
                        $FacturaDetalle->detalle_factura_importe_total_inc_igv = $gf_d->guia_det_importe_total_inc_igv;
                        $FacturaDetalle->detalle_factura_moneda = $gf_d->guia_det_moneda;
                        $FacturaDetalle->detalle_factura_tipo_cambio = $gf_d->guia_det_tipo_cambio;
                        $FacturaDetalle->detalle_factura_peso_gramo = $gf_d->guia_det_peso_gramo;
                        $FacturaDetalle->detalle_factura_volumen = $gf_d->guia_det_volumen;
                        $FacturaDetalle->detalle_factura_peso_total_gramo = $gf_d->guia_det_peso_total_gramo;
                        $FacturaDetalle->detalle_factura_volumen_total = $gf_d->guia_det_volumen_total;
                        $FacturaDetalle->save();
                    }
                }
            }
        }


        /*$codigo_vendedor = DB::table('vendedores')->get();

        foreach ($codigo_vendedor as $cv){
            $guias_con_vendedor_des = DB::table('guias')
                ->where('guia_vendedor',$cv->vendedor_des)
                ->where('guia_vendedor_codigo',null)
                ->get();
            if($guias_con_vendedor_des){
                foreach ($guias_con_vendedor_des as $gcv){
                    $guia = Guia::find($gcv->id_guia);
                    if($guia){
                        $guia->guia_vendedor_codigo = $cv->vendedor_codigo_vendedor_starsoft;
                        $guia->save();
                    }
                }
            }
        }*/

        /*        $guias_antiguas = $this->despachoventas->listar_guias_antiguas()->toArray();
        $guias_sin_duplicidad = [];

        foreach ($guias_antiguas as $item) {
            $guia = $item->despacho_venta_guia;
            $id = $item->id_despacho_venta;
            if (isset($guias_sin_duplicidad[$guia])) {
                if ($id > $guias_sin_duplicidad[$guia]->id_despacho_venta) {
                    $guias_sin_duplicidad[$guia] = (object)[
                        'id_despacho_venta' => $id,
                        'despacho_venta_guia' => $guia
                    ];
                }
            } else {
                $guias_sin_duplicidad[$guia] = (object)[
                    'id_despacho_venta' => $id,
                    'despacho_venta_guia' => $guia
                ];
            }
        }

        $guias_sin_duplicidad = array_values($guias_sin_duplicidad);
        $guias_sin_duplicidad = (object)$guias_sin_duplicidad;
        $contador_guias_ingresadas = 0;
        $contador_detalles_ingresados = 0;
        foreach ($guias_sin_duplicidad as $gsd){
            //Buscar si no existe esta guia en la tabla Guia
            $existe = $this->despachoventas->listar_guia_existente($gsd->despacho_venta_guia);
            if(!$existe){
                $buscar_datos_guia = $this->server->obtenerGuia_x_numdoc($gsd->despacho_venta_guia);
                if($buscar_datos_guia){
                    $Guia = new Guia();
                    $Guia->id_users = 4;
                    $Guia->guia_almacen_origen = $buscar_datos_guia['ALMACEN_ORIGEN'];
                    $Guia->guia_tipo_doc = $buscar_datos_guia['TIPO_DOC'];
                    $Guia->guia_nro_doc = $buscar_datos_guia['NRO_DOC'];
                    $Guia->guia_fecha_emision = $buscar_datos_guia['FECHA_EMISION'];
                    $Guia->guia_tipo_movimiento = $buscar_datos_guia['TIPO_MOVIMIENTO'];
                    $Guia->guia_tipo_doc_ref = $buscar_datos_guia['TIPO_DOC_REF'];
                    $Guia->guia_nro_doc_ref = $buscar_datos_guia['NRO_DOC_REF'];
                    $Guia->guia_glosa = $buscar_datos_guia['GLOSA'];
                    $Guia->guia_fecha_proceso = $buscar_datos_guia['FECHA_DE_PROCESO'];
                    $Guia->guia_hora_proceso = $buscar_datos_guia['HORA_DE_PROCESO'];
                    $Guia->guia_usuario = $buscar_datos_guia['USUARIO'];
                    $Guia->guia_cod_cliente = $buscar_datos_guia['COD_CLIENTE'];
                    $Guia->guia_ruc_cliente = $buscar_datos_guia['RUC_CLIENTE'];
                    $Guia->guia_nombre_cliente = $buscar_datos_guia['NOMBRE_CLIENTE'];
                    $Guia->guia_forma_pago = $buscar_datos_guia['FORMA_DE_PAGO'];
                    $Guia->guia_vendedor = $buscar_datos_guia['VENDEDOR'];
                    $Guia->guia_moneda = $buscar_datos_guia['MONEDA'];
                    $Guia->guia_tipo_cambio = $buscar_datos_guia['TIPO_DE_CAMBIO'];
                    $Guia->guia_estado = $buscar_datos_guia['ESTADO'];
                    $Guia->guia_direc_entrega = $buscar_datos_guia['DIREC_ENTREGA'];
                    $Guia->guia_nro_pedido = $buscar_datos_guia['NRO_PEDIDO'];
                    $Guia->guia_importe_total = $buscar_datos_guia['IMPORTE_TOTAL'];
                    $Guia->guia_departamento = $buscar_datos_guia['DEPARTAMENTO'];
                    $Guia->guia_provincia = $buscar_datos_guia['PROVINCIA'];
                    $Guia->guia_destrito = $buscar_datos_guia['DISTRITO'];
                    $Guia->guia_estado_aprobacion = 8;
                    $Guia->guia_estado_registro = 1;
                    $Guia->guia_fecha = now('America/Lima');
                    if($Guia->save()){
                        $buscar_datos_detalle_guia = $this->server->obtenerDetalleRemision($buscar_datos_guia['NRO_DOC']);
                        if($buscar_datos_detalle_guia){
                            $id_guia = $this->server->listar_guia_guardada($buscar_datos_guia['NRO_DOC'])->id_guia;
                            foreach ($buscar_datos_detalle_guia as $dg){
                                $GuiaDetalle = new Guiadetalle();
                                $GuiaDetalle->id_users = 4;
                                $GuiaDetalle->id_guia  = $id_guia;
                                $GuiaDetalle->guia_det_almacen_salida = $dg->ALMACEN_SALIDA ?: null;
                                $GuiaDetalle->guia_det_fecha_emision = $dg->FECHA_EMISION ?: null;
                                $GuiaDetalle->guia_det_estado = $dg->ESTADO ?: null;
                                $GuiaDetalle->guia_det_tipo_documento = $dg->TIPO_DOCUMENTO ?: null;
                                $GuiaDetalle->guia_det_nro_documento = $dg->NRO_DOCUMENTO ?: null;
                                $GuiaDetalle->guia_det_nro_linea = $dg->NRO_LINEA ?: null;
                                $GuiaDetalle->guia_det_cod_producto = $dg->COD_PRODUCTO ?: null;
                                $GuiaDetalle->guia_det_descripcion_producto = $dg->DESCRIPCION_PRODUCTO ?: null;
                                $GuiaDetalle->guia_det_lote = $dg->LOTE ?: null;
                                $GuiaDetalle->guia_det_unidad = $dg->UNIDAD ?: null;
                                $GuiaDetalle->guia_det_cantidad = $dg->CANTIDAD ?: null;
                                $GuiaDetalle->guia_det_precio_unit_final_inc_igv = $dg->PRECIO_UNIT_FINAL_INC_IGV ?: null;
                                $GuiaDetalle->guia_det_precio_unit_antes_descuente_inc_igv = $dg->PRECIO_UNIT_ANTES_DESCUENTO_INC_IGV ?: null;
                                $GuiaDetalle->guia_det_descuento_total_sin_igv = $dg->DESCUENTO_TOTAL_SIN_IGV ?: null;
                                $GuiaDetalle->guia_det_igv_total = $dg->IGV_TOTAL ?: null;
                                $GuiaDetalle->guia_det_importe_total_inc_igv = $dg->IMPORTE_TOTAL_INC_IGV ?: null;
                                $GuiaDetalle->guia_det_moneda = $dg->MONEDA ?: null;
                                $GuiaDetalle->guia_det_tipo_cambio = $dg->TIPO_CAMBIO ?: null;
                                $GuiaDetalle->guia_det_peso_gramo = $dg->PESO_GRAMOS ?: null;
                                $GuiaDetalle->guia_det_volumen = $dg->VOLUMEN_CM3 ?: null;
                                $GuiaDetalle->guia_det_peso_total_gramo = $dg->PESO_TOTAL_GRAMOS ?: null;
                                $GuiaDetalle->guia_det_volumen_total = $dg->VOLUMEN_TOTAL_CM3 ?: null;
                                if($GuiaDetalle->save()){
                                    $contador_detalles_ingresados++;
                                };
                            }
                        }
                        $contador_guias_ingresadas++;
                    }
                }
            }
        }
        $total = $contador_guias_ingresadas;
        $total_detalles = $contador_detalles_ingresados;
        $guias_antiguas = $this->despachoventas->actualizarGuiasAntiguas();*/

        //ALGORITMO PARA ACTUALIZAR DETALLE DE GUIA INDIVIDUALMENTE

        return view('livewire.despachotransporte.pase-guias-antiguas');
    }
}
