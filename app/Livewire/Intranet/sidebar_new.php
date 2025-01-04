<?php

namespace App\Livewire\Intranet;

use App\Models\Logs;
use App\Models\Menu;
use App\Models\Submenu;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Request;

class sidebar_new extends Component
{
    private $logs;
    private $menus;
    private $submenus;
    public $urlactual_sidebar;
    public $list_menus = array();
    public function __construct()
    {
        $this->logs = new Logs();
        $this->menus = new Menu();
        $this->submenus = new Submenu();
    }

    public function mount()
    {
        $this->urlactual_sidebar = explode('.', Request::route()->getName());

    }
    #[On('refresh_sidebar_menu')]
    public function listar_url_de_envio($datos)
    {
        $this->urlactual_sidebar = $datos;
        $this->reset('list_menus');
        $this->render();
    }

    public function render()
    {
        $this->list_menus = $this->menus->listar_menus_sidebar();
        if (count($this->list_menus) > 0) {
            foreach ($this->list_menus as $me) {
                $me->submenu = $this->submenus->listar_submenus_x_menu($me->id_menu);
            }
        }
        return view('livewire.intranet.sidebar_new');
    }
}
