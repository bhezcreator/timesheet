@props([
    'columns' => $columns
])


<div
class="
bg-white
rounded-2xl
border
border-gray-100
shadow-sm
overflow-hidden
"
>


<div class="overflow-x-auto">


<table
class="
w-full
text-sm
text-left
"
>


<!-- Header -->

<thead
class="
bg-gray-50
border-b
"
>

<tr>


@foreach($columns as $column)

<th
class="
px-6
py-4
font-semibold
text-gray-600
uppercase
text-xs
tracking-wider
"
>

{{ $column }}

</th>


@endforeach


</tr>

</thead>



<!-- Body -->

<tbody
class="
divide-y
divide-gray-100
"
>


{{ $slot }}


</tbody>


</table>


</div>


</div>
