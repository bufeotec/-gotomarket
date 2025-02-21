<?php

namespace App\Http\Controllers;

use App\Models\Despacho;
use App\Models\DespachoVenta;
use App\Models\Programacion;
use App\Models\Facturaspreprogramacion;
use Illuminate\Http\Request;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;

class ProgramacioncamionController extends Controller
{
    private $logs;
    private $programacion;
    private $despacho;
    private $despacho_venta;
    private $facturapreprogramacion;

    public function __construct()
    {
        $this->logs = new Logs();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->despacho_venta = new DespachoVenta();
        $this->facturapreprogramacion = new Facturaspreprogramacion();
    }

    public function facturas_pre_programacion(){
        try {
            return view('programacion_camiones.facturas_pre_programacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function credito_cobranza(){
        try {
            return view('programacion_camiones.credito_cobranza');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function gestion_factura_programacion(){
        try {
            return view('programacion_camiones.gestion_factura_programacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function programar_camion(){
        try {
            return view('programacion_camiones.programar_camion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function facturas_aprobar(){
        try {
            return view('programacion_camiones.facturas_aprobar');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function programacion_pendientes(){
        try {
            return view('programacion_camiones.programacion_pendientes');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function historial_programacion(){
        try {
            return view('programacion_camiones.historial_programacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function detalle_programacion(){
        try {
            $id_programacion = base64_decode($_GET['data']);
            if ($id_programacion){
                $programacion = $this->programacion->listar_informacion_x_id($id_programacion);
                $despacho = $this->despacho->listar_despachos_por_programacion($id_programacion);
                foreach ($despacho as $de){
                    $de->comprobantes = $this->despacho_venta->listar_detalle_x_despacho($de->id_despacho);
                }
                return view('programacion_camiones.detalle_programacion',compact('programacion','despacho'));
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function editar_programacion(){
        try {
            $id_programacion = base64_decode($_GET['data']);
            if ($id_programacion){
                $programacion = $this->programacion->listar_informacion_x_id($id_programacion);
                $conteo = 0;
                $despacho = $this->despacho->listar_despachos_por_programacion($id_programacion);
                foreach ($despacho as $de){
                    $de->comprobantes = $this->despacho_venta->listar_detalle_x_despacho($de->id_despacho);
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
    public function notas_credito(){
        try {
            return view('programacion_camiones.notas_credito');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
//    public function notas_credito(){
//        try {
//            return view('programacion_camiones.notas_credito');
//        }catch (\Exception $e){
//            $this->logs->insertarLog($e);
//            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
//        }
//    }
    public function refacturacion(){
        try {
            return view('programacion_camiones.refacturacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function facturacion(){
        try {
            return view('programacion_camiones.facturacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function validaredes(){
        try {
            return view('programacion_camiones.validaredes');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }


    public function vendedor(){
        try {
            $user = auth()->user();
            $id_transportistas = $user->id_transportistas;
            $id_users = $user->id_users; // Obtener el ID del usuario autenticado

            return view('gestionvendedor.vendedor', compact('id_transportistas', 'id_users'));
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function aprobar_camino(){
        try {
            return view('gestionvendedor.aprobar_camino');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function aprobar_entregado(){
        try {
            return view('gestionvendedor.aprobar_entregado');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function tracking(){
        try {
            return view('gestionvendedor.tracking');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function vistatracking(){
        try {
            $data = json_decode(base64_decode(request()->get('data')), true);

            if ($data && isset($data['id'])) {
                $id_fac = $data['id'];
                $num_doc = $data['numdoc'];

                $informacion_fac = $this->facturapreprogramacion->listar_fac_pre_prog_x_id($id_fac);

                return view('gestionvendedor.vistatracking', compact('informacion_fac', 'num_doc'));
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

}
