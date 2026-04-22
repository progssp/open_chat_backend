<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StartVideoCall implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $sender,$rec,$stream;
    public function __construct($sender,$rec,$stream)
    {
        $this->sender = $sender;
        $this->rec = $rec;
        $this->stream = $stream;
        Log::info($this->sender);
        Log::info($this->rec);
        Log::info($this->stream);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
                new PrivateChannel('user-'.$this->sender),
                new PrivateChannel('user-'.$this->rec)
            ];
    }
}
