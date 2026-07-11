<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Permissions\Index as PermissionIndex;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/permissions', PermissionIndex::class)
            ->name('permissions.index');
    });

require __DIR__ . '/auth.php';
