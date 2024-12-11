<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IntranetController extends Controller
{
    private $logs;
    public function __construct()
    {
        $this->logs = new Logs();
    }

    public function test(){
        try{

            return view('emails.password_reset');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function intranet(){
        try{
//            $e = new Server();
//            $test = $e->prueba();
            return view('intranet/index');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function perfil(){
        try{
            $id = isset($_GET['data']) ? $_GET['data'] : null;
            if (!$id){
                /* Si en caso no se envía un parametro get sacar información por usuario autenticado */
                if (auth()->check()){
                    $auth = true;
                    $information =  DB::table('users')->where([['id_users','=',Auth::id()],['users_status','=',1]])->first();
                }else{
                    return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
                }
            }else{
                /* Buscar información por la variable get */
                $auth = false;
                $information =  DB::table('users')->where([['id_users','=',$id],['users_status','=',1]])->first();
            }
            if ($information){
                return view('intranet/perfil',compact('information','auth'));
            }

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

}
