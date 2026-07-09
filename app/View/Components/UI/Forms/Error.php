<?php

namespace App\View\Components\UI\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Error extends Component
{

    public function __construct(

        public string $name,

    ) {}


    public function render(): View|Closure|string
    {
        return view('components.ui.forms.error');
    }
}
