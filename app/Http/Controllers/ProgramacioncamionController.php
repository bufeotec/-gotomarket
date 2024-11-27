<?php

namespace App\Http\Controllers;

use App\Models\Despacho;
use App\Models\DespachoVenta;
use App\Models\Programacion;
use Illuminate\Http\Request;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;

class ProgramacioncamionController extends Controller
{
    private $logs;
    private $programacion;
    private $despacho;
    private $despacho_venta;

    public function __construct()
    {
        $this->logs = new Logs();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->despacho_venta = new DespachoVenta();
    }

    public function programar_camion(){
        try {
            return view('programacion_camiones.programar_camion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function historial_programación(){
        try {
            return view('programacion_camiones.historial_programación');
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
}
