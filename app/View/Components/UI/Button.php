<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public string $variant;
    public string $size;

    public function __construct(
        string $variant = 'primary',
        string $size = 'md'
    ) {
        $this->variant = $variant;
        $this->size = $size;
    }

    public function render(): View|Closure|string
    {
        return view('components.ui.button');
    }
}
