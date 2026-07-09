<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => '
        inline-flex
        items-center
        justify-center
        gap-2
        px-6
        py-3
        rounded-xl
        bg-gradient-to-r
        from-indigo-600
        to-purple-600
        border
        border-transparent
        font-semibold
        text-sm
        text-white
        shadow-lg
        shadow-indigo-500/30
        hover:from-indigo-700
        hover:to-purple-700
        hover:shadow-xl
        focus:outline-none
        focus:ring-2
        focus:ring-indigo-500
        focus:ring-offset-2
        active:scale-95
        transition
        duration-200
        cursor-pointer
    '
]) }}>

    {{ $slot }}

</button>
