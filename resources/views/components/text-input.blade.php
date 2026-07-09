@props(['disabled' => false])

<input
    @disabled($disabled)

    {{ $attributes->merge([
        'class' => '
            w-full
            px-4
            py-3
            rounded-xl
            border
            border-gray-200
            bg-white
            text-gray-700
            placeholder-gray-400
            shadow-sm

            focus:border-indigo-500
            focus:ring-4
            focus:ring-indigo-500/20
            focus:outline-none

            disabled:bg-gray-100
            disabled:cursor-not-allowed

            transition
            duration-200
        '
    ]) }}
>
