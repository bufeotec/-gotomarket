<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logs;

class LiquidacionfleteController extends Controller
{
    private $logs;
    public function __construct()
    {
        $this->logs = new Logs();
    }

    public function liquidacion_flete(){
        try {
            return view('liquidacion.liquidacion_flete');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function liquidaciones_pendientes(){
        try {
            return view('liquidacion.liquidaciones_pendientes');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function editar_liquidacion(){
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
    public function historial_liquidacion(){
        try {
            return view('liquidacion.historial_liquidacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
}
