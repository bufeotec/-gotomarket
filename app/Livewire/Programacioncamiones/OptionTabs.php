<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Logs;
use Livewire\Component;

class OptionTabs extends Component
{
    /* --------------------------------------- */
    private $logs;
    /* --------------------------------------- */
    public $estadoTabs = 1;
    public function __construct()
    {
        $this->logs = new Logs();
    }

    public function render()
    {
        return view('livewire.programacioncamiones.option-tabs');
    }

}
