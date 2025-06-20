<?php

namespace App\Listeners;

use App\Events\verfiyEmailAddressCode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class verfiyEmailAddressCodeFired
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
    public function handle(verfiyEmailAddressCode $event): void
    {
        dd($event);
        //
    }
}
