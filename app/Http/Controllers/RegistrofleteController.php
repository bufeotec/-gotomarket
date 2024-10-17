<?php

namespace App\Http\Controllers;

use App\Models\Transportista;
use Illuminate\Http\Request;
use App\Models\Logs;

class RegistrofleteController extends Controller
{
    private $logs;
    private $transportista;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->transportista = new Transportista();
    }
    public function fletes(){
        try {
            return view('registroflete.fletes');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function tarifario(){
        try {
            $id_transportista = base64_decode($_GET['data']);
            if ($id_transportista){
                $informacion_transportista = $this->transportista->listar_transportista_por_id($id_transportista);

                return view('registroflete.tarifario',compact('informacion_transportista'));
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
}
