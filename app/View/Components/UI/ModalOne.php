<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;


class ModalOne extends Component
{

    public function __construct(
        public string $id,
        public string $title = '',
        public string $size = 'md'
    ) {}



    public function sizeClass()
    {

        return match ($this->size) {

            'sm' => 'max-w-md',

            'md' => 'max-w-lg',

            'lg' => 'max-w-2xl',

            'xl' => 'max-w-4xl',

            default => 'max-w-lg'
        };
    }



    public function render(): View|Closure|string
    {
        return view('components.ui.modal-one');
    }
}
