<?php

namespace App\View\Components\UI\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Radio extends Component
{

    public function __construct(

        public ?string $name = null,

        public ?string $label = null,

        public mixed $value = null,

        public mixed $checkedValue = null,

        public ?string $helper = null,

        public bool $disabled = false,

    ) {}


    public function render(): View|Closure|string
    {
        return view('components.ui.forms.radio');
    }
}
