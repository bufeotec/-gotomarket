<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use App\Models\Logs;
use App\Models\User;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Nuevoperfiles extends Component
{
    use WithPagination, WithoutUrlPagination;

    private $logs;
    private $user;
    public function __construct(){
        $this->logs = new Logs();
        $this->user = new User();
    }
    public $codigo_perfil = '';
    public $id_users = '';
    public $users_seleccionados = [];

    public function render(){
        // OBTENER ULTIMO ID DE LA TABLA ROLES
        $ultimoId = DB::table('roles')
            ->select('id')
            ->orderBy('id', 'desc')
            ->first();

        $nuevoNumero = $ultimoId ? ($ultimoId->id + 1) : 1;
        $this->codigo_perfil = 'PU' . $nuevoNumero;

        // OBTENER MENUS PRINCIPALES
        $menus_show = DB::table('menus')
            ->where('menu_show', '=', 1)
            ->get();

        // OBTENER SUBMENUS PARA CADA MENU
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
                    ->where('id_submenu', $submenu->id_submenu)
                    ->orderBy('descripcion')
                    ->get();
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
