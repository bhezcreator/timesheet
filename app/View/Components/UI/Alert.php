<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{

    public string $type;

    public bool $dismissible;


    public function __construct(
        string $type = 'info',
        bool $dismissible = true
    ) {

        $this->type = $type;
        $this->dismissible = $dismissible;
    }


    public function render(): View|Closure|string
    {
        return view('components.ui.alert');
    }
}
