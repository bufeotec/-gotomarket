<?php

namespace App\Livewire\Configuracion;

use Illuminate\Support\Facades\Request;
use Livewire\Component;
use App\Models\Logs;
use App\Models\User;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class Nuevoperfiles extends Component
{
    use WithPagination, WithoutUrlPagination;

    private $logs;
    private $user;
    public $check = [];
    public function __construct(){
        $this->logs = new Logs();
        $this->user = new User();
    }
    public $codigo_perfil = '';
    public $urlActual;
    public $id_users = '';
    public $id_perfil;
    public $nombre_perfil;
    public $descripcion_perfil;
    public $users_seleccionados = [];
    public $permisosSeleccionados = [];
    public function mount($id_perfil=null)
    {
        $this->id_perfil = $id_perfil;
        $this->urlActual = explode('.', Request::route()->getName());
    }

    public function render(){

        if($this->id_perfil){
            $data_perfil = DB::table('roles')
                ->where('id', $this->id_perfil)
                ->first();

            $this->nombre_perfil = $data_perfil->name;
            $this->descripcion_perfil = $data_perfil->rol_descripcion;

            $ultimoId = DB::table('roles')
                ->where('id', $this->id_perfil)
                ->first();

            $nuevoNumero = $ultimoId ? ($ultimoId->id) : 1;
            $this->codigo_perfil = 'PU' . $nuevoNumero;

        }else{
            // OBTENER ULTIMO ID DE LA TABLA ROLES
            $ultimoId = DB::table('roles')
                ->select('id')
                ->orderBy('id', 'desc')
                ->first();
            $nuevoNumero = $ultimoId ? ($ultimoId->id + 1) : 1;
            $this->codigo_perfil = 'PU' . $nuevoNumero;
        }

        // OBTENER MENUS PRINCIPALES
        $menus_show = DB::table('menus')
            ->where('menu_show', '=', 1)
            ->get();

        // OBTENER SUBMENUS PARA CADA MENU
        $this->check = [];
        foreach ($menus_show as $menu) {
            $menu->submenus = DB::table('submenus')
                ->where('id_menu', $menu->id_menu)
                ->where('submenu_show', 1)
                ->where('submenu_status', 1)
                ->whereNotIn('submenu_name', ['Menús', 'Iconos', 'Empresas'])
                ->get();

            // Obtener permisos para cada submenú
            foreach ($menu->submenus as $submenu) {
                $submenu->permisos = DB::table('permissions')
//                    ->where('id_submenu', $submenu->id_submenu)
                        ->where('permissions_group',3)
                    ->where('permissions_group_id',$submenu->id_submenu)
                    ->orderBy('descripcion')
                    ->get();

                if($this->id_perfil){
                    foreach ($submenu->permisos as $li) {
                        $perMenu = DB::table('role_has_permissions')->where([['permission_id', '=', $li->id],
                            ['role_id', '=', $this->id_perfil]])->first();
                        if ($perMenu) {
                            $this->check[] = $li->id;
                            $this->permisosSeleccionados[$li->id] = true;
                        }
                    }
                }

            }
        }

        $listar_users = $this->user->listra_usuarios_activos();
        return view('livewire.configuracion.nuevoperfiles', compact('listar_users', 'menus_show'));
    }

    public function agregar_usuario(){
        if (empty($this->id_users)) {
            session()->flash('error_select_user', 'Debe seleccionar un usuario.');
            return;
        }

        $usuario = $this->user->find($this->id_users);

        // Verificar si el usuario ya fue agregado
        if (in_array($this->id_users, array_column($this->users_seleccionados, 'id_users'))) {
            session()->flash('error_select_user', 'Este usuario ya está seleccionado.');
        } else {
            $this->users_seleccionados[] = [
                'id_users' => $usuario->id_users,
                'name' => $usuario->name,
                'username' => $usuario->username
            ];
            $this->id_users = '';
        }
    }
    public function eliminar_usuario($index){
        if (isset($this->users_seleccionados[$index])) {
            unset($this->users_seleccionados[$index]);
            $this->users_seleccionados = array_values($this->users_seleccionados);
        }
    }
}
