<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group([
    'prefix' => 'v1',
    'namespace' => 'App\\Http\\Controllers\\Api\\V1',

    ],
    function() {
        Route::get('/spawnpoints', 'SpawnPointsController@index');

        Route::get('/zones', 'ZoneController@index');

        Route::get('/expansions', 'ExpansionController@index');

        Route::match(['PUT', 'PATCH'],'/scout/{scout}/bulkupdate', 'ScoutController@bulkUpdate');
        Route::apiResource('scout', 'ScoutController');

    }
)->middleware(['throttle:api']);
