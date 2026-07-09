<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tabs extends Component
{

    public array $tabs;


    public string $active;


    public function __construct(
        array $tabs = [],
        string $active = ''
    ) {

        $this->tabs = $tabs;

        $this->active = $active ?: ($tabs[0]['key'] ?? '');
    }


    public function render(): View|Closure|string
    {
        return view('components.ui.tabs');
    }
}
