<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    private $logs;
    public function __construct()
    {
        $this->logs = new Logs();
    }
    public function login(){
        try{
            if (auth()->check()) {
                return redirect()->route('intranet');
            }
            return view('auth/login');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
}
