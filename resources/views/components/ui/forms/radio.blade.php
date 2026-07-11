@props([

    'name',

    'label'=>null,

    'value'=>null,

    'checkedValue'=>null,

    'helper'=>null,

    'disabled'=>false,

])


<label

class="

flex

items-start

gap-3

cursor-pointer

group

"


>


<input


type="radio"


name="{{ $name }}"


value="{{ $value }}"


@if($checkedValue == $value)

checked

@endif


@if($disabled)

disabled

@endif


{{ $attributes->merge([

'class'=>'

mt-1

h-5

w-5

border-gray-300

text-blue-600

focus:ring-4

focus:ring-blue-100

transition

'

]) }}



/>


<div class="flex-1">


@if($label)

<div

class="

text-sm

font-medium

text-gray-700

group-hover:text-blue-600

transition

"

>

{{ $label }}

</div>

@endif



@if($helper)

<p

class="

mt-1

text-sm

text-gray-500

"

>

{{ $helper }}

</p>

@endif



</div>


</label>



@error($name)

<p

class="text-sm text-red-600"

>

{{ $message }}

</p>

@enderror
