<?php

namespace App\View\Components\UI\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{

    public string $type;

    public ?string $label;

    public ?string $name;

    public ?string $placeholder;


    public function __construct(
        string $type = 'text',
        ?string $label = null,
        ?string $name = null,
        ?string $placeholder = null
    ) {

        $this->type = $type;
        $this->label = $label;
        $this->name = $name;
        $this->placeholder = $placeholder;
    }


    public function render(): View|Closure|string
    {
        return view('components.ui.forms.input');
    }
}
