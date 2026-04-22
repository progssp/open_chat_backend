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

class MessageSent implements ShouldBroadcastNow {
    
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $last_msg;
    private $channel_arr;

    public function __construct($channel_arr,$last_msg)
    {
        $this->channel_arr = $channel_arr;
        $this->last_msg = $last_msg;
        Log::info(json_encode($this->channel_arr));
        Log::info(json_encode($this->last_msg));

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this->channel_arr;
        // return [
        //     new PrivateChannel('user-'.$this->last_msg->sender_id),
        //     new PrivateChannel('user-'.$this->last_msg->receiver_id)
        // ];
    }
}
