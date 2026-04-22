<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\GroupMessageDetail;
use App\Models\GroupMember;

class GroupMessage extends Model
{
    use HasFactory;

    public function user_sender(){
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function group_details(){
        return $this->belongsTo(GroupDetail::class,'group_id');
    }

    public function message_details(){
        return $this->hasMany(GroupMessageDetail::class,'msg_id');
    }

    public function member_details(){
        return $this->belongsTo(GroupMember::class,'sender_id','member_id');
    }
}
