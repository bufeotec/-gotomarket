<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\Transportista;
use Illuminate\Http\Request;

class VendedorController extends Controller
{
    private $logs;
    private $transportista;
    public function __construct(){
        $this->logs = new Logs();
        $this->transportista = new Transportista();
    }

    public function vendedores(){
        try {
            return view('vendedor.vendedores');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
}
