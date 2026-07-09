<?php

namespace App\View\Components\UI\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Textarea extends Component
{
    public function __construct(
        public ?string $name = null,
        public ?string $label = null,
        public ?string $placeholder = null,
        public ?string $helper = null,
        public bool $required = false,
        public bool $disabled = false,
        public bool $readonly = false,
        public ?int $rows = 5,
        public ?int $maxlength = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.ui.forms.textarea');
    }
}
