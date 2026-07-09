<?php

namespace App\View\Components\UI\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Datepicker extends Component
{

    public function __construct(

        public ?string $name = null,

        public ?string $label = null,

        public ?string $value = null,

        public ?string $placeholder = 'Sélectionner une date',

        public ?string $min = null,

        public ?string $max = null,

        public bool $disabled = false,

        public ?string $helper = null,

    ) {}


    public function render(): View|Closure|string
    {
        return view('components.ui.forms.datepicker');
    }
}
