<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group(['namespace' => '\\App\\Http\\Controllers'], function () {
    Route::get('/', 'MainController@index')->name('main');
    Route::post('/', 'MainController@store')->name('scout.store');
    Route::get('/scout/{scout:slug}/{password?}', 'MainController@view')->name('scout.view');
    Route::post('/scout/{scout:slug}/{password?}', 'MainController@update')->name('scout.update');
    Route::post('/scoutmeta/{scout:slug}/{password?}', 'MainController@updateMeta')->name('scout.updateMeta');
    Route::get('/scoutupdates/{scout:slug}/{password?}', 'MainController@getUpdates')->name('scout.updatelist');
    Route::post('/clone/{scout:slug}', 'MainController@clone')->name('scout.clone');
    Route::post('/finalize/{scout:slug}/{password?}', 'MainController@finalize')->name('scout.finalize');
    Route::post('/scoutimport/{scout:slug}/{password?}', 'MainController@import')->name('scout.import');

    Route::get('/custompoints', 'CustomPointsController@index')->name('custompoints');
    Route::get('/custompoints/generate/{expansion}', 'CustomPointsController@generate_custom')->name('custompoints.custom');
    Route::get('/custompoints/{zone}', 'CustomPointsController@get_custom')->name('custompoints.zone');
});

/*
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
*/

/*
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
*/
