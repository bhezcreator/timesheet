<?php

use App\Livewire\Permissions\Index as PermissionIndex;
use App\Livewire\Projects\AttributesProject;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\SubProjectManager;
use App\Livewire\Roles\Index as RolesIndex;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Users\Show as UsersShow;
use App\Livewire\Users\SettingsTabs;
use Illuminate\Support\Facades\Route;


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

        // Route pour la liste principale des projets (votre exemple)
        Route::get('/projects', ProjectsIndex::class)
            ->name('projects.index');

        // Route pour la gestion des sous-projets liés à un projet spécifique
        Route::get('/projects/{projectId}/sub-projects', SubProjectManager::class)
            ->name('projects.subprojects');

        Route::get('/users', UsersIndex::class)
            ->name('users.index');

        Route::get('/users/{userId}/show', UsersShow::class)
            ->name('users.show');

        Route::get('/users/{userId}/attributes-projects', AttributesProject::class)
            ->name('users.attributes_projects');

        Route::get('/settings', SettingsTabs::class)
            ->name('settings');
    });

require __DIR__ . '/auth.php';
