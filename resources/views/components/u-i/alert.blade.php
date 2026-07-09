@props([
    'type' => $type ?? 'info',
    'dismissible' => $dismissible ?? true,
])


@php

$styles = [

    'success' => [
        'box' => 'bg-green-50 border-green-200 text-green-800',
        'icon' => 'las la-check-circle text-green-600',
    ],


    'error' => [
        'box' => 'bg-red-50 border-red-200 text-red-800',
        'icon' => 'las la-times-circle text-red-600',
    ],


    'warning' => [
        'box' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'icon' => 'las la-exclamation-triangle text-yellow-600',
    ],


    'info' => [
        'box' => 'bg-blue-50 border-blue-200 text-blue-800',
        'icon' => 'las la-info-circle text-blue-600',
    ],


];


@endphp



<div

    x-data="{show:true}"

    x-show="show"

    x-transition

    class="
        flex
        items-start
        gap-3
        p-4
        rounded-xl
        border
        {{ $styles[$type]['box'] }}

    "

>


    <!-- Icon -->
    <div class="pt-0.5">

        <i
            class="
            {{ $styles[$type]['icon'] }}
            text-xl
            "
        ></i>

    </div>



    <!-- Message -->

    <div class="flex-1 text-sm font-medium">

        {{ $slot }}

    </div>



    <!-- Close -->

    @if($dismissible)

    <button

        type="button"

        x-on:click="show=false"

        class="
        text-gray-400
        hover:text-gray-700
        transition
        "
    >

        <i class="las la-times"></i>

    </button>

    @endif



</div>
