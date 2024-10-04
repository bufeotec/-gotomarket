<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ModalGeneral extends Component
{
    public $tama;
    public $id_modal;
    public $titleModal;
    public $modalContent;

    /**
     * Create a new component instance.
     */
    public function __construct($tama = '')
    {
        $this->tama = $tama;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.modal-general');
    }
}
