<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporte;
use App\Models\Logs;

class ReporteController extends Controller
{
    private $logs;
    private $reporte;

    public function __construct(){
        $this->logs = new Logs();
        $this->reporte = new Reporte();
    }

    public function ver_reporte(){
        try{
            return view('reporte.ver_reporte');
        }catch(Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al mostrar el contenido');
        }
    }

}
