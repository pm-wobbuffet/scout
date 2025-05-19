<?php

namespace App\Listeners;

use App\Handlers\ScoutAssignMobHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Laravel\Reverb\Events\MessageReceived;

class ReverbMessageListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageReceived $event): void
    {
        /*
        $message = json_decode($event->message);

        if($message->event == 'ScoutAssignMob') {
            (new ScoutAssignMobHandler())->handle($message);
        }
            */
    }
}
