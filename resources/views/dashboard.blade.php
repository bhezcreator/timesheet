<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <livewire:dashboard.personal />

    @php
        $user = auth()->user();
    @endphp

    @if($user->can('tableauAdmin'))
        <!-- Dashboard Administrateur -->
        <livewire:dashboard.admin />

    @elseif($user->can('validations.voir'))
        <!-- Dashboard Superviseur -->
        <livewire:dashboard.supervisor />
    @endif

</x-app-layout>
