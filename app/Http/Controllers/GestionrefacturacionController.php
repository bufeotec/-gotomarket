<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logs;

class GestionrefacturacionController extends Controller
{
    private $logs;
    public function __construct(){
        $this->logs = new Logs();
    }
    public function refacturacion(){
        try {
            return view('gestionrefacturacion.refacturacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
}
