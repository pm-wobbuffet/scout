<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api'])
    ->prefix('v1')
    ->namespace('\\App\\Http\\Controllers\\Api\\V1')
    ->name('api.v1.')
    ->group(function () {
        Route::get('/spawnpoints', 'SpawnPointsController@index');
        Route::get('/zones', 'ZoneController@index');
        Route::get('/expansions', 'ExpansionController@index');
        Route::match(['PUT', 'PATCH'], '/scout/{scout:slug}/bulkupdate', 'ScoutController@bulkUpdate');
        Route::match(['POST', 'PATCH'], '/scout/{scout:slug}/occupypoint', 'ScoutController@updateOccupiedPoint');
        Route::apiResource('scout', 'ScoutController');
    });

Route::middleware(['throttle:api'])
    ->prefix('v2')
    ->namespace('\\App\\Http\\Controllers\\Api\\V2')
    ->name('api.v2.')
    ->group(function () {
        Route::get('/expansions/{expansion}/zones', 'ExpansionController@zones')->name('expansions.zones');
        Route::resource('expansions', 'ExpansionController')->only(['index', 'show']);
        Route::get('/zones/{zone}/spawn_points', 'ZoneController@spawn_points');
        Route::resource('zones', 'ZoneController')->only(['index', 'show']);
        Route::resource('mobs', 'MobController')->only(['index', 'show']);
        Route::resource('scouts', 'ScoutController')->only(['show', 'store', 'update']);
        Route::resource('scouts.points', 'ScoutPointController');
    });
