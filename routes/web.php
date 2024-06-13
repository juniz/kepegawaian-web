<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Livewire\Volt\Volt;

if (env('APP_ENV') === 'production') {
    Livewire::setScriptRoute(function ($handle) {
        return Route::get('sdm/vendor/livewire/livewire.js', $handle);
    });

    Livewire::setUpdateRoute(function ($handle) {
        return Route::post('sdm/vendor/livewire/update', $handle);
    });
}

Volt::route('/', 'login.index')->middleware('login');
Volt::route('/home', 'home.index')->middleware('ceklogin');
Volt::route('/izin', 'izin.index')->middleware('ceklogin');
Volt::route('/cuti', 'cuti.index')->middleware('ceklogin');
Volt::route('/jadwal', 'jadwal.index')->middleware('ceklogin');
Route::get('/logout', function () {
    session()->forget('user');
    return redirect('/');
})->middleware('ceklogin');
