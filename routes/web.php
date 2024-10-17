<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\GestiontransporteController;
use App\Http\Controllers\RegistrofleteController;
use App\Http\Controllers\IntranetController;

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
Route::prefix('configuracion')->middleware('auth')->group(function () {
    /* MENÚ */
    route::get('/menus',[ConfigurationController::class ,'menus'])->name('configuracion.menus')->middleware('verifyUserStatus')->middleware('can:menus');
    route::get('/submenu',[ConfigurationController::class ,'submenu'])->name('configuracion.submenu')->middleware('verifyUserStatus')->middleware('can:submenu');
    route::get('/usuarios',[ConfigurationController::class ,'usuarios'])->name('configuracion.usuarios')->middleware('verifyUserStatus')->middleware('can:usuarios');
    route::get('/roles',[ConfigurationController::class ,'roles'])->name('configuracion.roles')->middleware('verifyUserStatus')->middleware('can:roles');
    route::get('/iconos',[ConfigurationController::class ,'iconos'])->name('configuracion.iconos')->middleware('verifyUserStatus')->middleware('can:iconos');
    route::get('/empresas',[ConfigurationController::class ,'empresas'])->name('configuracion.empresas')->middleware('verifyUserStatus')->middleware('can:empresas');
});

/* ----------------------------- RUTAS FINALES DE CONFIGURACIÓN ---------------------------------*/

Route::prefix('Gestiontransporte')->middleware('auth')->group(function () {
    /* TRANSPORTISTAS */
    route::get('/transportistas',[GestiontransporteController::class ,'transportistas'])->name('Gestiontransporte.transportistas')->middleware('verifyUserStatus')->middleware('can:transportistas');
    route::get('/vehiculos',[GestiontransporteController::class ,'vehiculos'])->name('Gestiontransporte.vehiculos')->middleware('verifyUserStatus')->middleware('can:vehiculos');
});

Route::prefix('Registroflete')->middleware('auth')->group(function () {
    /* FLETES */
    route::get('/fletes',[RegistrofleteController::class ,'fletes'])->name('Registroflete.fletes')->middleware('verifyUserStatus')->middleware('can:fletes');
    route::get('/tarifario',[RegistrofleteController::class ,'tarifario'])->name('Registroflete.tarifario')->middleware('verifyUserStatus')->middleware('can:tarifario');
});
