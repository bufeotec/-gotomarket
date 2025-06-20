<?php

namespace App\Livewire\Configuracion;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\User;
use App\Models\Vendedor;

class Crearusuarios extends Component
{
    private $logs;
    private $user;
    private $vendedor;
    public function __construct(){
        $this->logs = new Logs();
        $this->user = new User();
        $this->vendedor = new Vendedor();
    }

    public function mount(){
        $this->ruta_img_default = "assets/images/faces/1.jpg";
        $this->dispatch('updateUserImagePreview', ['image' => asset($this->ruta_img_default)]);
    }
    public $id_users;
    public $name;
    public $last_name;
    public $email;
    public $users_cargo;
    public $username;
    public $profile_picture;
    public $password;
    public $id_rol;
    public $ruta_img_default = "";
    public $id_vendedor= '';
    public $vendedor_seleccionados = [];

    public function render(){
        $listar_vendedores = $this->vendedor->listra_vendedores_activos();
        return view('livewire.configuracion.crearusuarios', compact('listar_vendedores'));
    }

    public function agregar_vendedor(){
        if (empty($this->id_vendedor)) {
            session()->flash('error_select_vendedor', 'Debe seleccionar un vendedor.');
            return;
        }

        $vendedor = $this->vendedor->find($this->id_vendedor);

        // Verificar si el usuario ya fue agregado
        if (in_array($this->id_vendedor, array_column($this->vendedor_seleccionados, 'id_vendedor'))) {
            session()->flash('error_select_vendedor', 'Este vendedor ya está seleccionado.');
        } else {
            $this->vendedor_seleccionados[] = [
                'id_vendedor' => $vendedor->id_vendedor,
                'vendedor_codigo_intranet' => $vendedor->vendedor_codigo_intranet,
                'vendedor_codigo_vendedor_starsoft' => $vendedor->vendedor_codigo_vendedor_starsoft,
                'vendedor_des' => $vendedor->vendedor_des
            ];
            $this->id_vendedor = '';
        }
    }

    public function eliminar_vendedor($index){
        if (isset($this->vendedor_seleccionados[$index])) {
            unset($this->vendedor_seleccionados[$index]);
            $this->vendedor_seleccionados = array_values($this->vendedor_seleccionados);
        }
    }
}
