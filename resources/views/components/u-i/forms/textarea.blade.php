@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'helper' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'rows' => 5,
    'maxlength' => null,
])

<div
    x-data="{
        value: $el.querySelector('textarea')?.value ?? ''
    }"
    class="space-y-2"
>

    @if($label)

        <label
            for="{{ $name }}"
            class="block text-sm font-medium text-gray-700"
        >

            {{ $label }}

            @if($required)
                <span class="text-red-500">*</span>
            @endif

        </label>

    @endif


    <textarea

        {{ $attributes }}

        id="{{ $name }}"

        name="{{ $name }}"

        rows="{{ $rows }}"

        placeholder="{{ $placeholder }}"

        maxlength="{{ $maxlength }}"

        @disabled($disabled)

        @readonly($readonly)

        x-model="value"

        class="
            w-full
            rounded-xl
            border
            border-gray-300
            bg-white
            px-4
            py-3
            text-gray-700
            shadow-sm
            transition
            duration-200

            focus:border-blue-500
            focus:ring-4
            focus:ring-blue-100

            disabled:bg-gray-100
            disabled:cursor-not-allowed

            @error($name)
                border-red-500
                focus:border-red-500
                focus:ring-red-100
            @enderror
        "

    >{{ old($name, $slot) }}</textarea>



    <div class="flex justify-between">

        <div>

            @if($helper)

                <p class="text-sm text-gray-500">

                    {{ $helper }}

                </p>

            @endif

            @error($name)

                <p class="text-sm text-red-600">

                    {{ $message }}

                </p>

            @enderror

        </div>



        @if($maxlength)

            <span
                class="text-xs text-gray-400"
                x-text="value.length + ' / {{ $maxlength }}'"
            ></span>

        @endif

    </div>
</div>
