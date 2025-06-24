<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class ConfigurationController extends Controller
{
    private $logs;
    private $menu;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->menu = new Menu();
    }

    public function menus(){
        try {


            return view('configuration.menu');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function submenu(){
        try {
            $id_menu = base64_decode($_GET['data']);
            if ($id_menu){
                $informacion_menu = $this->menu->listar_menu_x_id($id_menu);

                return view('configuration.submenu',compact('informacion_menu'));
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function usuarios(){
        try {


            return view('configuration.usuarios');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function roles(){
        try {


            return view('configuration.roles');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function iconos(){
        try {


            return view('configuration.icono');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function empresas(){
        try {


            return view('configuration.empresas');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function vendedores(){
        try {
            return view('vendedor.vendedores');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function nuevoperfil(){
        try {
            $id_perfil = isset($_GET['id']) ? base64_decode($_GET['id']) : null;
            // Verificar si el perfil existe si se proporciona un ID
            if($id_perfil) {
                $perfil = Role::find($id_perfil);
                if(!$perfil) {
                    return redirect()->back()->with('error', 'El perfil solicitado no existe.');
                }
            }
            return view('configuration.nuevoperfil', compact('id_perfil'));
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function crear_usuario(){
        try {
            $id_users = isset($_GET['id_users']) ? base64_decode($_GET['id_users']) : null;
            // Verificar si el perfil existe si se proporciona un ID
            if($id_users) {
                $users = User::find($id_users);
                if(!$users) {
                    return redirect()->back()->with('error', 'El usuario solicitado no existe.');
                }
            }
            return view('configuration.crear_usuario',compact('id_users'));
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

}
