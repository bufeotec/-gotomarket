<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{
    public $text;
    public $type;
    public $duration;
    /**
     * Create a new component instance.
     */
    public function __construct($text, $type = 'success',$duration = 5)
    {
        $this->text = $text;
        $this->type = $type;
        $this->duration = $duration; // Duración en segundos
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.alert');
    }
}
