<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GroupMessage;
use App\Models\User;

class GroupMessageDetail extends Model
{
    use HasFactory;

    public function message(){
        return $this->belongsTo(GroupMessage::class,'msg_id');
    }

    public function user_rec(){
        return $this->belongsTo(User::class,'receiver_id');
    }
}
