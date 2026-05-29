<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('user-{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('video_call', function () {
    return true;
});

Broadcast::channel('group-{id}', function ($user, $id) {
    Log::debug("user: " . $user->id . "conn group id: " . $id);
});