<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Http\Request;

class GestionvendedorController extends Controller
{
    private $logs;
    public function __construct(){
        $this->logs = new Logs();
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
}
