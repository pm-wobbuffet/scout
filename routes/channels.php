<?php

use App\Models\Scout;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

// Broadcast::channel('Scout.{scout_id}.{collaborator_password}', function(User $user, int $scout_id, string $collab) {
//     $scout = Scout::where('id', $scout_id);
//     if($scout->collaborator_password === $collab) {
//         return true;
//     }
//     return false;
// });