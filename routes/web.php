<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Livewire\Volt\Volt;

Volt::route('/', 'login.index')->middleware('login');
Volt::route('/home', 'home.index')->middleware('ceklogin');
Volt::route('/izin', 'izin.index')->middleware('ceklogin');
Volt::route('/cuti', 'cuti.index')->middleware('ceklogin');
Route::get('/logout', function () {
    session()->forget('user');
    return redirect('/');
})->middleware('ceklogin');
