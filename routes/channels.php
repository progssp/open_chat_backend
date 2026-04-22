<?php

use Illuminate\Support\Facades\Broadcast;
Broadcast::channel('user-{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('video_call', function () {
    return true;
});
