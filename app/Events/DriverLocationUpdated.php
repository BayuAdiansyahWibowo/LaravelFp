<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $driverId;
    public $latitude;
    public $longitude;

    /**
     * Create a new event instance.
     */
    public function __construct($driverId, $latitude, $longitude)
    {
        $this->driverId = $driverId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Channel where event will be broadcasted.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('driver-location');
    }

    /**
     * Event name on frontend (optional).
     */
    public function broadcastAs(): string
    {
        return 'driver.updated';
    }
}
