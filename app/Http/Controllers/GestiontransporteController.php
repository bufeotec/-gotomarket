<?php

namespace App\Http\Controllers;

use App\Livewire\Gestiontransporte\Vehiculos;
use App\Models\Logs;
use App\Models\Transportista;
use Illuminate\Http\Request;

class GestiontransporteController extends Controller
{
    private $logs;
    private $transportista;

    public function __construct()
    {
        $this->logs = new Logs();
        $this->transportista = new Transportista();
    }

    public function transportistas(){
        try {
            return view('gestiontransporte.transportistas');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function vehiculos(){
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
}
