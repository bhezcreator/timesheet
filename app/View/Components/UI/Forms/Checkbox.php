<?php

namespace App\View\Components\UI\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Checkbox extends Component
{

    public function __construct(

        public ?string $name = null,

        public ?string $label = null,

        public mixed $value = 1,

        public bool $checked = false,

        public bool $disabled = false,

        public ?string $helper = null,

    ) {}



    public function render(): View|Closure|string
    {
        return view('components.ui.forms.checkbox');
    }
}
