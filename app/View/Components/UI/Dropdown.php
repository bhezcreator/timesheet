<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Dropdown extends Component
{
    public string $position;

    public function __construct(
        string $position = 'right'
    ) {

        $this->position = $position;
    }


    public function render(): View|Closure|string
    {
        return view('components.ui.dropdown');
    }
}
