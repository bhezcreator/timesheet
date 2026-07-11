@props([

    'name',

    'label'=>null,

    'value'=>1,

    'checked'=>false,

    'disabled'=>false,

    'helper'=>null,

])


<div class="space-y-2">


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


type="checkbox"


name="{{ $name }}"


value="{{ $value }}"


@if($checked)

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

rounded

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

text-sm

text-gray-500

mt-1

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



</div>
