<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\GestiontransporteController;
use App\Http\Controllers\VendedorController;
//use App\Http\Controllers\RegistrofleteController;
use App\Http\Controllers\TarifarioController;
use App\Http\Controllers\IntranetController;
use App\Http\Controllers\ProgramacioncamionController;
use App\Http\Controllers\LiquidacionfleteController;
use App\Http\Controllers\GestiondocumentariaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\TarifamovilController;
use App\Http\Controllers\GestionvendedorController;
//use App\Http\Controllers\TarifamovilController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\DespachotransporteController;


route::get('/phpinfo', function(){
    phpinfo();
});
// XDEBUG
/* ----------------------------- RUTAS CONTROLLER INTRANET ---------------------------------*/
route::get('/',[IntranetController::class ,'intranet'])->name('intranet')->middleware('verifyUserStatus')->middleware('auth');
//route::get('test',[IntranetController::class ,'test'])->name('test');
route::get('perfil',[IntranetController::class ,'perfil'])->name('intranet.perfil')->middleware('verifyUserStatus')->middleware('auth');
/* ----------------------------- FIN RUTAS CONTROLLER INTRANET ---------------------------------*/


/* ----------------------------- RUTAS DEL LOGIN ------------------------------*/
route::get('login',[LoginController::class ,'login'])->name('login');

/* ----------------------------- FIN RUTAS DEL LOGIN ------------------------------*/

/* ----------------------------- RUTAS DE CONFIGURACIÓN ---------------------------------*/
Route::prefix('configuracion')->middleware(['auth', 'canMenu:configuracion'])->group(function () {
    /* MENÚ */
    route::get('/menus',[ConfigurationController::class ,'menus'])->name('configuracion.menus')->middleware('verifyUserStatus')->middleware('can:menus');
    route::get('/submenu',[ConfigurationController::class ,'submenu'])->name('configuracion.submenu')->middleware('verifyUserStatus')->middleware('can:submenu');
    route::get('/usuarios',[ConfigurationController::class ,'usuarios'])->name('configuracion.usuarios')->middleware('verifyUserStatus')->middleware('can:usuarios');
    route::get('/roles',[ConfigurationController::class ,'roles'])->name('configuracion.roles')->middleware('verifyUserStatus')->middleware('can:roles');
    route::get('/iconos',[ConfigurationController::class ,'iconos'])->name('configuracion.iconos')->middleware('verifyUserStatus')->middleware('can:iconos');
    route::get('/empresas',[ConfigurationController::class ,'empresas'])->name('configuracion.empresas')->middleware('verifyUserStatus')->middleware('can:empresas');
    route::get('/nuevoperfil',[ConfigurationController::class ,'nuevoperfil'])->name('configuracion.nuevoperfil')->middleware('verifyUserStatus')->middleware('can:nuevoperfil');
    route::get('/vendedores',[ConfigurationController::class ,'vendedores'])->name('configuracion.vendedores')->middleware('verifyUserStatus')->middleware('can:vendedores');
    route::get('/crear_usuario',[ConfigurationController::class ,'crear_usuario'])->name('configuracion.crear_usuario')->middleware('verifyUserStatus')->middleware('can:crear_usuario');
});

/* ----------------------------- RUTAS FINALES DE CONFIGURACIÓN ---------------------------------*/

Route::prefix('Gestiontransporte')->middleware(['auth', 'canMenu:Gestiontransporte'])->group(function () {
    /* TRANSPORTISTAS */
    route::get('/transportistas',[GestiontransporteController::class ,'transportistas'])->name('Gestiontransporte.transportistas')->middleware('verifyUserStatus')->middleware('can:transportistas');
    route::get('/vehiculos',[GestiontransporteController::class ,'vehiculos'])->name('Gestiontransporte.vehiculos')->middleware('verifyUserStatus')->middleware('can:vehiculos');
});
Route::prefix('Tarifario')->middleware(['auth', 'canMenu:Tarifario'])->group(function () {
    /* FLETES - TARIFARIOS */
    route::get('/fletes',[TarifarioController::class ,'fletes'])->name('Tarifario.fletes')->middleware('verifyUserStatus')->middleware('can:fletes');
    route::get('/tarifas',[TarifarioController::class ,'tarifas'])->name('Tarifario.tarifas')->middleware('verifyUserStatus')->middleware('can:tarifas');
    route::get('/validar_tarifa',[TarifarioController::class ,'validar_tarifa'])->name('Tarifario.validar_tarifa')->middleware('verifyUserStatus')->middleware('can:validar_tarifa');
    route::get('/tarifa_movil',[TarifarioController::class ,'tarifa_movil'])->name('Tarifario.tarifa_movil')->middleware('verifyUserStatus')->middleware('can:tarifa_movil');
});
Route::prefix('Programacioncamion')->middleware(['auth', 'canMenu:Programacioncamion'])->group(function () {
    /* FLETES - TARIFARIOS */
    route::get('/reporte_liq_apro', [ProgramacioncamionController::class, 'reporte_liq_apro'])->name('Programacioncamion.reporte_liq_apro')->middleware('verifyUserStatus')->middleware('can:reporte_liq_apro');
    route::get('/programar_camion',[ProgramacioncamionController::class ,'programar_camion'])->name('Programacioncamion.programar_camion')->middleware('verifyUserStatus')->middleware('can:programar_camion');
    route::get('/programacion_pendientes',[ProgramacioncamionController::class ,'programacion_pendientes'])->name('Programacioncamion.programacion_pendientes')->middleware('verifyUserStatus')->middleware('can:programacion_pendientes');
    route::get('/historial_programacion',[ProgramacioncamionController::class ,'historial_programacion'])->name('Programacioncamion.historial_programacion')->middleware('verifyUserStatus')->middleware('can:historial_programacion');
    route::get('/detalle_programacion',[ProgramacioncamionController::class ,'detalle_programacion'])->name('Programacioncamion.detalle_programacion')->middleware('verifyUserStatus')->middleware('can:detalle_programacion');
    route::get('/editar_programacion',[ProgramacioncamionController::class ,'editar_programacion'])->name('Programacioncamion.editar_programacion')->middleware('verifyUserStatus')->middleware('can:editar_programacion');
    route::get('/facturas_pre_programacion',[ProgramacioncamionController::class ,'facturas_pre_programacion'])->name('Programacioncamion.facturas_pre_programacion')->middleware('verifyUserStatus')->middleware('can:facturas_pre_programacion');
    route::get('/credito_cobranza',[ProgramacioncamionController::class ,'credito_cobranza'])->name('Programacioncamion.credito_cobranza')->middleware('verifyUserStatus')->middleware('can:credito_cobranza');
    route::get('/facturas_aprobar',[ProgramacioncamionController::class ,'facturas_aprobar'])->name('Programacioncamion.facturas_aprobar')->middleware('verifyUserStatus')->middleware('can:facturas_aprobar');
    route::get('/gestion_factura_programacion',[ProgramacioncamionController::class ,'gestion_factura_programacion'])->name('Programacioncamion.gestion_factura_programacion')->middleware('verifyUserStatus')->middleware('can:gestion_factura_programacion');
    route::get('/notas_credito',[ProgramacioncamionController::class ,'notas_credito'])->name('Programacioncamion.notas_credito')->middleware('verifyUserStatus')->middleware('can:notas_credito');
//    route::get('/facturas_aprobar',[ProgramacioncamionController::class ,'facturas_aprobar'])->name('Programacioncamion.facturas_aprobar')->middleware('verifyUserStatus')->middleware('can:facturas_aprobar');
//    route::get('/gestion_factura_programacion',[ProgramacioncamionController::class ,'gestion_factura_programacion'])->name('Programacioncamion.gestion_factura_programacion')->middleware('verifyUserStatus')->middleware('can:gestion_factura_programacion');
    route::get('/registrar_guias_remision',[ProgramacioncamionController::class ,'registrar_guias_remision'])->name('Programacioncamion.registrar_guias_remision')->middleware('verifyUserStatus')->middleware('can:registrar_guias_remision');
    route::get('/reporte_ventas_indicadores',[ProgramacioncamionController::class ,'reporte_ventas_indicadores'])->name('Programacioncamion.reporte_ventas_indicadores')->middleware('verifyUserStatus')->middleware('can:reporte_ventas_indicadores');
    route::get('/reporte_estados_factura',[ProgramacioncamionController::class ,'reporte_estados_factura'])->name('Programacioncamion.reporte_estados_factura')->middleware('verifyUserStatus')->middleware('can:reporte_estados_factura');
    route::get('/reporte_control_documentario',[ProgramacioncamionController::class ,'reporte_control_documentario'])->name('Programacioncamion.reporte_control_documentario')->middleware('verifyUserStatus')->middleware('can:reporte_control_documentario');
    route::get('/facturacion',[ProgramacioncamionController::class ,'facturacion'])->name('Programacioncamion.facturacion')->middleware('verifyUserStatus')->middleware('can:facturacion');
    route::get('/validaredes',[ProgramacioncamionController::class ,'validaredes'])->name('Programacioncamion.validaredes')->middleware('verifyUserStatus')->middleware('can:validaredes');
    route::get('/vendedor',[ProgramacioncamionController::class ,'vendedor'])->name('Gestionvendedor.vendedor')->middleware('verifyUserStatus')->middleware('can:vendedor');
    route::get('/aprobar_camino',[ProgramacioncamionController::class ,'aprobar_camino'])->name('Gestionvendedor.aprobar_camino')->middleware('verifyUserStatus')->middleware('can:aprobar_camino');
    route::get('/aprobar_entregado',[ProgramacioncamionController::class ,'aprobar_entregado'])->name('Gestionvendedor.aprobar_entregado')->middleware('verifyUserStatus')->middleware('can:aprobar_entregado');
    route::get('/tracking',[ProgramacioncamionController::class ,'tracking'])->name('Programacioncamion.tracking')->middleware('verifyUserStatus')->middleware('can:tracking');
    route::get('/vistatracking', [ProgramacioncamionController::class, 'vistatracking'])->name('Programacioncamion.vistatracking')->middleware('verifyUserStatus')->middleware('can:vistatracking');
    route::get('/reporte_liq_apro', [ProgramacioncamionController::class, 'reporte_liq_apro'])->name('Programacioncamion.reporte_liq_apro')->middleware('verifyUserStatus')->middleware('can:reporte_liq_apro');
    route::get('/efectividad_entrega_pedidos', [ProgramacioncamionController::class, 'efectividad_entrega_pedidos'])->name('Programacioncamion.efectividad_entrega_pedidos')->middleware('verifyUserStatus')->middleware('can:efectividad_entrega_pedidos');
    route::get('/reporte_estado_documento', [ProgramacioncamionController::class, 'reporte_estado_documento'])->name('Programacioncamion.reporte_estado_documento')->middleware('verifyUserStatus')->middleware('can:reporte_estado_documento');
    route::get('/reporte_tiempos', [ProgramacioncamionController::class, 'reporte_tiempos'])->name('Programacioncamion.reporte_tiempos')->middleware('verifyUserStatus')->middleware('can:reporte_tiempos');
    route::get('/reporte_indicadores_valor', [ProgramacioncamionController::class, 'reporte_indicadores_valor'])->name('Programacioncamion.reporte_indicadores_valor')->middleware('verifyUserStatus')->middleware('can:reporte_indicadores_valor');
    route::get('/reporte_indicadores_peso', [ProgramacioncamionController::class, 'reporte_indicadores_peso'])->name('Programacioncamion.reporte_indicadores_peso')->middleware('verifyUserStatus')->middleware('can:reporte_indicadores_peso');
    route::get('/gestionar_nota_credito', [ProgramacioncamionController::class, 'gestionar_nota_credito'])->name('Programacioncamion.gestionar_nota_credito')->middleware('verifyUserStatus')->middleware('can:gestionar_nota_credito');
});
Route::prefix('Liquidacionflete')->middleware(['auth', 'canMenu:Liquidacionflete'])->group(function () {
    /* LIQUIDACION */
    route::get('/liquidacion_flete',[LiquidacionfleteController::class ,'liquidacion_flete'])->name('Liquidacionflete.liquidacion_flete')->middleware('verifyUserStatus')->middleware('can:liquidacion_flete');
    route::get('/editar_liquidacion',[LiquidacionfleteController::class ,'editar_liquidacion'])->name('Liquidacionflete.editar_liquidacion')->middleware('verifyUserStatus')->middleware('can:editar_liquidacion');
    route::get('/liquidaciones_pendientes',[LiquidacionfleteController::class ,'liquidaciones_pendientes'])->name('Liquidacionflete.liquidaciones_pendientes')->middleware('verifyUserStatus')->middleware('can:liquidaciones_pendientes');
    route::get('/historial_liquidacion',[LiquidacionfleteController::class ,'historial_liquidacion'])->name('Liquidacionflete.historial_liquidacion')->middleware('verifyUserStatus')->middleware('can:historial_liquidacion');
});
Route::prefix('Gestiondocumentaria')->middleware(['auth', 'canMenu:Gestiondocumentaria'])->group(function () {
    /* LIQUIDACION */
    route::get('/nota_credito',[GestiondocumentariaController::class ,'nota_credito'])->name('Gestiondocumentaria.nota_credito')->middleware('verifyUserStatus')->middleware('can:nota_credito');
   });
Route::prefix('Gestiondocumentaria')->middleware(['auth', 'canMenu:Gestiondocumentaria'])->group(function () {
    /* NOTACREDITO */
    route::get('/nota_credito',[GestiondocumentariaController::class ,'nota_credito'])->name('Gestiondocumentaria.nota_credito')->middleware('verifyUserStatus')->middleware('can:nota_credito');
    Route::get('/exportar_pdf', [GestiondocumentariaController::class, 'exportToPdf'])->name('exportar.pdf');
});
Route::prefix('Reporte')->middleware(['auth', 'canMenu:Reporte'])->group(function () {
    /* REPORTE */
    route::get('/ver_reporte',[ReporteController::class ,'ver_reporte'])->name('reporte.ver_reporte')->middleware('verifyUserStatus')->middleware('can:ver_reporte');
});
Route::prefix('Gestionvendedor')->middleware(['auth', 'canMenu:Gestionvendedor'])->group(function () {
    /* FLETES - TARIFARIOS */
    route::get('/vendedor',[GestionvendedorController::class ,'vendedor'])->name('Gestionvendedor.vendedor')->middleware('verifyUserStatus')->middleware('can:vendedor');
    route::get('/aprobar_camino',[GestionvendedorController::class ,'aprobar_camino'])->name('Gestionvendedor.aprobar_camino')->middleware('verifyUserStatus')->middleware('can:aprobar_camino');
    route::get('/aprobar_entregado',[GestionvendedorController::class ,'aprobar_entregado'])->name('Gestionvendedor.aprobar_entregado')->middleware('verifyUserStatus')->middleware('can:aprobar_entregado');
    route::get('/tracking',[GestionvendedorController::class ,'tracking'])->name('Gestionvendedor.tracking')->middleware('verifyUserStatus')->middleware('can:tracking');
});
Route::prefix('Despachotransporte')->middleware(['auth', 'canMenu:Despachotransporte'])->group(function () {
    /*  */
    route::get('/registrar_transportista',[DespachotransporteController::class ,'registrar_transportista'])->name('Despachotransporte.registrar_transportista')->middleware('verifyUserStatus')->middleware('can:registrar_transportista');
    route::get('/registrar_vehiculos',[DespachotransporteController::class ,'registrar_vehiculos'])->name('Despachotransporte.registrar_vehiculos')->middleware('verifyUserStatus')->middleware('can:registrar_vehiculos');
    route::get('/validar_vehiculo',[DespachotransporteController::class ,'validar_vehiculo'])->name('Despachotransporte.validar_vehiculo')->middleware('verifyUserStatus')->middleware('can:validar_vehiculo');
    route::get('/registrar_tarifas',[DespachotransporteController::class ,'registrar_tarifas'])->name('Despachotransporte.registrar_tarifas')->middleware('verifyUserStatus')->middleware('can:registrar_tarifas');
    route::get('/validar_tarifas',[DespachotransporteController::class ,'validar_tarifas'])->name('Despachotransporte.validar_tarifas')->middleware('verifyUserStatus')->middleware('can:validar_tarifas');
    route::get('/programar_despachos',[DespachotransporteController::class ,'programar_despachos'])->name('Despachotransporte.programar_despachos')->middleware('verifyUserStatus')->middleware('can:programar_despachos');
    route::get('/registrar_servicio_transporte',[DespachotransporteController::class ,'registrar_servicio_transporte'])->name('Despachotransporte.registrar_servicio_transporte')->middleware('verifyUserStatus')->middleware('can:registrar_servicio_transporte');
    route::get('/aprobar_programacion_despacho',[DespachotransporteController::class ,'aprobar_programacion_despacho'])->name('Despachotransporte.aprobar_programacion_despacho')->middleware('verifyUserStatus')->middleware('can:aprobar_programacion_despacho');
    route::get('/reporte_programacion_despacho',[DespachotransporteController::class ,'reporte_programacion_despacho'])->name('Despachotransporte.reporte_programacion_despacho')->middleware('verifyUserStatus')->middleware('can:reporte_programacion_despacho');
    route::get('/reporte_gestion_despacho',[DespachotransporteController::class ,'reporte_gestion_despacho'])->name('Despachotransporte.reporte_gestion_despacho')->middleware('verifyUserStatus')->middleware('can:reporte_gestion_despacho');
    route::get('/liquidar_fletes',[DespachotransporteController::class ,'liquidar_fletes'])->name('Despachotransporte.liquidar_fletes')->middleware('verifyUserStatus')->middleware('can:liquidar_fletes');
    route::get('/aprobar_fletes',[DespachotransporteController::class ,'aprobar_fletes'])->name('Despachotransporte.aprobar_fletes')->middleware('verifyUserStatus')->middleware('can:aprobar_fletes');
    route::get('/reporte_flete_aprobados',[DespachotransporteController::class ,'reporte_flete_aprobados'])->name('Despachotransporte.reporte_flete_aprobados')->middleware('verifyUserStatus')->middleware('can:reporte_flete_aprobados');

    route::get('/editar_liquidaciones',[DespachotransporteController::class ,'editar_liquidaciones'])->name('Despachotransporte.editar_liquidaciones')->middleware('verifyUserStatus')->middleware('can:editar_liquidaciones');
    route::get('/editar_programaciones',[DespachotransporteController::class ,'editar_programaciones'])->name('Despachotransporte.editar_programaciones')->middleware('verifyUserStatus')->middleware('can:editar_programaciones');
    route::get('/guias_antiguas',[DespachotransporteController::class ,'guias_antiguas'])->name('Despachotransporte.guias_antiguas')->middleware('verifyUserStatus')->middleware('can:guias_antiguas');
    route::get('/reporte_despacho_transporte',[DespachotransporteController::class ,'reporte_despacho_transporte'])->name('Despachotransporte.reporte_despacho_transporte')->middleware('verifyUserStatus')->middleware('can:reporte_despacho_transporte');
});
