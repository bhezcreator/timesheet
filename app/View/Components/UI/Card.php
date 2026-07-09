<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Card extends Component
{
    public string $padding;


    public function __construct(
        string $padding = 'md'
    ) {

        $this->padding = $padding;
    }


    public function render(): View|Closure|string
    {
        return view('components.ui.card');
    }
}
