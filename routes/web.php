<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Permissions\Index as PermissionIndex;
use App\Livewire\Roles\Index as RolesIndex;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Projects\Index as ProjectsIndex;

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
        Route::get('/roles', RolesIndex::class)
            ->name('roles.index');
        Route::get('/users', UsersIndex::class)
            ->name('users.index');
        Route::get('/projects', ProjectsIndex::class)
            ->name('projects.index');
    });

require __DIR__ . '/auth.php';
