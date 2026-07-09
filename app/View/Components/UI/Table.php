<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{

    public array $columns;


    public function __construct(
        array $columns = []
    ) {

        $this->columns = $columns;
    }


    public function render(): View|Closure|string
    {
        return view('components.ui.table');
    }
}
