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
