<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Livewire\NfeEmissor;
use App\Livewire\NfeListagem;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});
Route::get('/', function () {
    return redirect()->route('nfe.listagem');
});

Route::get('/nfe/emissor', NfeEmissor::class)->name('nfe.emissor');
Route::get('/nfe/listagem', NfeListagem::class)->name('nfe.listagem');
require __DIR__.'/auth.php';
