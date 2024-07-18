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

        Route::apiResource('scout', 'ScoutController');
        Route::match(['PUT', 'PATCH'],'/bulkupdate/scout/{scout}', 'ScoutController@bulkUpdate');

    }
)->middleware(['throttle:api']);
