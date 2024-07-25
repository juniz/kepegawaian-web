<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RapatController;
use Livewire\Livewire;
use Livewire\Volt\Volt;

// Livewire::setScriptRoute(function ($handle) {
//     return Route::get('sdm/vendor/livewire/livewire.js', $handle);
// });

// Livewire::setUpdateRoute(function ($handle) {
//     // dd($handle);
//     return Route::post('sdm/vendor/livewire/update', $handle);
// });

Volt::route('/', 'login.index')->middleware('login');
Volt::route('/home', 'home.index')->middleware('ceklogin');
Volt::route('/izin', 'izin.index')->middleware('ceklogin');
Volt::route('/cuti', 'cuti.index')->middleware('ceklogin');
Volt::route('/jadwal', 'jadwal.index')->middleware('ceklogin');
Volt::route('/dashboard', 'dashboard.index')->middleware('ceklogin');
Volt::route('/dashboard/pegawai', 'dashboard.pegawai')->middleware('ceklogin');
Volt::route('/rapat', 'rapat.index');
Route::get('/rapat/print', [RapatController::class, 'index']);
Route::get('/logout', function () {
    session()->forget('user');
    return redirect('/');
})->middleware('ceklogin');
