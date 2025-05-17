<?php

use App\Models\Scout;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('scouts.{scout}.{collaborator_password}', function(Scout $scout, $password) {
    return false;
    return $scout->collaborator_password == $password;
});
