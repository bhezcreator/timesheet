@props([

    'name'

])


@error($name)

<div

class="

mt-2

flex

items-center

gap-2

text-sm

text-red-600

"


>


<i

class="

las

la-exclamation-circle

text-lg

"

></i>


<span>

{{ $message }}

</span>


</div>


@enderror
