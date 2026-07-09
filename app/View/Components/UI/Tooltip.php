<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tooltip extends Component
{

    public string $text;

    public string $position;


    public function __construct(
        string $text,
        string $position = 'top'
    ) {

        $this->text = $text;
        $this->position = $position;
    }


    public function render(): View|Closure|string
    {
        return view('components.ui.tooltip');
    }
}
