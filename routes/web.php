<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group(['namespace' => '\\App\\Http\\Controllers'], function () {
    Route::get('/', 'MainController@index')->name('main');
    Route::post('/', 'MainController@store')->name('scout.store');

    Route::get('/scout/{scout:slug}/{password?}', 'MainController@view')->name('scout.view');

    Route::post('/scoutpoint/{scout:slug}/{password?}', 'ScoutPointController@assignMob')->name('scout.assignmob');
    Route::post('/scoutpointclear/{scout:slug}/{password?}', 'ScoutPointController@clearPoint')->name('scout.clearpoint');
    Route::post('/scoutupdatemob/{scout:slug}/{password?}', 'ScoutPointController@updateMobStatus')->name('scout.updatemobstatus');
    Route::get('/scoutupdates/{scout:slug}/{password?}', 'MainController@getUpdates')->name('scout.updatelist');
    Route::post('/scoutoccupied/{scout:slug}/{password?}', 'MainController@updateOccupiedPoint')->name('scout.updateOccupiedPoint');
    Route::post('/scoutmeta/{scout:slug}/{password?}', 'MainController@updateMeta')->name('scout.updateMeta');
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