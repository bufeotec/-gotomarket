<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Logs;

class PedidoController extends Controller
{
    private $Logs;
    private $pedido;

    public function __construct(){
        $this->Logs = new Logs();
        $this->pedido = new Pedido();
    }
    public function pedidos(){
        try {
            return view('pedido.pedidos');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

}
