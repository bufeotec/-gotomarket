<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\Menu;
use Illuminate\Http\Request;

class GestiontransporteController extends Controller
{
    private $logs;

    public function __construct()
    {
        $this->logs = new Logs();
    }

    public function transportistas(){
        try {


            return view('gestiontransporte.transportistas');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
}
