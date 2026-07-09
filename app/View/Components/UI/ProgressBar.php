<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProgressBar extends Component
{

    public int|float $value;

    public string $color;

    public string $size;


    public function __construct(
        int|float $value = 0,
        string $color = 'blue',
        string $size = 'md'
    ) {

        $this->value = min(max($value, 0), 100);

        $this->color = $color;

        $this->size = $size;
    }


    public function render(): View|Closure|string
    {
        return view('components.ui.progress-bar');
    }
}
