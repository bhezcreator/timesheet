@props([

    'name',

    'label'=>null,

    'value'=>null,

    'placeholder'=>'Sélectionner une heure',

    'min'=>null,

    'max'=>null,

    'disabled'=>false,

    'helper'=>null,

])


<div class="space-y-2">


@if($label)

<label

for="{{ $name }}"

class="
block
text-sm
font-semibold
text-gray-700
"

>

{{ $label }}

</label>

@endif



<div class="relative">


<input


{{ $attributes }}


type="time"


id="{{ $name }}"


name="{{ $name }}"


value="{{ old($name,$value) }}"


min="{{ $min }}"


max="{{ $max }}"


@if($disabled)

disabled

@endif



placeholder="{{ $placeholder }}"



class="

w-full

rounded-xl

border

border-gray-300

bg-white

px-4

py-3

pr-12

text-gray-700

shadow-sm

transition


focus:border-blue-500

focus:ring-4

focus:ring-blue-100


@error($name)

border-red-500

focus:border-red-500

focus:ring-red-100

@enderror


"



>



<div

class="

pointer-events-none

absolute

right-4

top-1/2

-translate-y-1/2

text-gray-400

"

>

<i class="las la-clock text-xl"></i>

</div>


</div>




@if($helper)

<p

class="
text-sm
text-gray-500
"

>

{{ $helper }}

</p>

@endif




@error($name)

<p

class="text-sm text-red-600"

>

{{ $message }}

</p>

@enderror



</div>
