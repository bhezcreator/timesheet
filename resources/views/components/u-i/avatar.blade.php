@props([
    'src' => null,
    'name' => null,
    'size' => $size ?? 'md',
    'status' => $status ?? false,
])


@php

$sizes = [

    'sm' =>
        'w-8 h-8 text-xs',

    'md' =>
        'w-10 h-10 text-sm',

    'lg' =>
        'w-14 h-14 text-lg',

    'xl' =>
        'w-20 h-20 text-2xl',

];


$initials = collect(
    explode(' ', $name ?? 'User')
)
->map(fn($word)=>strtoupper($word[0]))
->take(2)
->implode('');


$colors = [

    'bg-blue-100 text-blue-700',

    'bg-green-100 text-green-700',

    'bg-purple-100 text-purple-700',

    'bg-yellow-100 text-yellow-700',

    'bg-red-100 text-red-700',

];


$color = $colors[
    crc32($name ?? 'user') % count($colors)
];


@endphp



<div
    class="
    relative
    inline-flex
    items-center
    justify-center
    rounded-full
    overflow-hidden
    font-semibold
    {{ $sizes[$size] }}
    "
>


@if($src)

<img
    src="{{ $src }}"
    alt="{{ $name }}"
    class="
    w-full
    h-full
    object-cover
    "
>

@else


<div
class="
w-full
h-full
flex
items-center
justify-center
{{ $color }}
"
>

{{ $initials }}

</div>


@endif



@if($status)

<span

class="
absolute
bottom-0
right-0
w-3
h-3
bg-green-500
border-2
border-white
rounded-full
"

></span>

@endif


</div>
