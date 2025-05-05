<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group(['namespace' => '\\App\\Http\\Controllers'], function () {
    Route::get('/', 'MainController@index')->name('main');
    Route::post('/', 'MainController@store')->name('scout.store');
});

/*
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

*/