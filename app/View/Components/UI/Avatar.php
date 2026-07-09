<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Avatar extends Component
{

    public ?string $src;

    public ?string $name;

    public string $size;

    public bool $status;


    public function __construct(
        ?string $src = null,
        ?string $name = null,
        string $size = 'md',
        bool $status = false
    ) {

        $this->src = $src;
        $this->name = $name;
        $this->size = $size;
        $this->status = $status;
    }


    public function render(): View|Closure|string
    {
        return view('components.ui.avatar');
    }
}
