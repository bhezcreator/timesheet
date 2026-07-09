@props([
    'tabs' => $tabs,
    'active' => $active
])


<div
x-data="{
    activeTab: '{{ $active }}'
}"
>


<!-- Navigation -->

<div
class="
border-b
border-gray-200
"
>

<nav
class="
flex
gap-6
"
>


@foreach($tabs as $tab)


<button

type="button"

@click="activeTab='{{ $tab['key'] }}'"

class="

py-3

text-sm

font-medium

transition

border-b-2

"

:class="

activeTab === '{{ $tab['key'] }}'

?

'border-blue-600 text-blue-600'

:

'border-transparent text-gray-500 hover:text-gray-700'

"

>


@if(isset($tab['icon']))

<i class="{{ $tab['icon'] }} mr-1"></i>

@endif


{{ $tab['label'] }}


</button>


@endforeach


</nav>

</div>



<!-- Contenu -->

<div class="mt-6">


{{ $slot }}


</div>


</div>
