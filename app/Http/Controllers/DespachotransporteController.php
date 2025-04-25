<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Livewire\Gestiontransporte\Vehiculos;
use App\Models\Logs;
use App\Models\Transportista;
use App\Models\Programacion;
use App\Models\Despacho;
use App\Models\DespachoVenta;

class DespachotransporteController extends Controller
{
    private $logs;
    private $transportista;
    private $programacion;
    private $despacho;
    private $despachoventa;

    public function __construct(){
        $this->logs = new Logs();
        $this->transportista = new Transportista();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->despachoventa = new DespachoVenta();
    }

    public function registrar_transportista(){
        try {
            return view('gestiontransporte.transportistas');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function registrar_vehiculos(){
        try {
//            $id_transportistas = base64_decode($_GET['data']);
//            if ($id_transportistas){
//                $informacion_vehiculo = $this->transportista->listar_transportista_por_id($id_transportistas);
//
//                return view('gestiontransporte.vehiculos',compact('informacion_vehiculo'));
//            }
            return view('gestiontransporte.vehiculos');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function registrar_tarifas(){
        try {
            return view('registroflete.fletes');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function validar_tarifas(){
        try {
            return view('registroflete.validar_tarifa');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function validar_vehiculo(){
        try {
            return view('registroflete.tarifa_movil');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function guias_antiguas(){
        try {
            return view('registroflete.pase_guias_antiguas');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function programar_despachos(){
        try {
            return view('programacion_camiones.programar_camion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function registrar_servicio_transporte(){
        try {
            return view('despachotransporte.registrar_servicio_transporte');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function aprobar_programacion_despacho(){
        try {
            return view('programacion_camiones.programacion_pendientes');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function editar_programaciones(){
        try {
            $id_programacion = base64_decode($_GET['data']);
            if ($id_programacion){
                $programacion = $this->programacion->listar_informacion_x_id($id_programacion);
                $conteo = 0;
                $despacho = $this->despacho->listar_despachos_por_programacion($id_programacion);
                foreach ($despacho as $de){
                    $de->comprobantes = $this->despachoventa->listar_detalle_x_despacho($de->id_despacho);
                    $conteo+= $de->id_tipo_servicios;
                }
                // determinar que tipo de programacion es.

                return view('programacion_camiones.editar_programacion',compact('programacion','despacho','conteo','id_programacion'));
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function reporte_programacion_despacho(){
        try {
            return view('programacion_camiones.historial_programacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function liquidar_fletes(){
        try {
            return view('liquidacion.liquidacion_flete');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function aprobar_fletes(){
        try {
            return view('liquidacion.liquidaciones_pendientes');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function editar_liquidaciones(){
        try {
            $id_liquidacion = base64_decode($_GET['data']);
            if ($id_liquidacion){

                return view('liquidacion.editar_liquidacion',compact('id_liquidacion'));
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function reporte_flete_aprobados(){
        try {
            return view('liquidacion.historial_liquidacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function reporte_despacho_transporte(){
        try {
            return view('despachotransporte.reporte_control_documentario');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
}
