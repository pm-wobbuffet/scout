<?php

use App\Models\Scout;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('scouts.{scout}.{password}', function (Scout $scout, $password) {
    return $scout->collaborator_password == $password;
});
