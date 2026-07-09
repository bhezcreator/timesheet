<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    public string $id;
    public string $size;

    public function __construct(
        string $id,
        string $size = 'md'
    ) {
        $this->id = $id;
        $this->size = $size;
    }


    public function render(): View|Closure|string
    {
        return view('components.ui.modal');
    }
}
