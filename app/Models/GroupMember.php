<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\GroupDetail;
use App\Models\GroupMessage;

class GroupMember extends Model
{
    use HasFactory;

    public function member_details(){
        return $this->belongsTo(User::class,'member_id');
    }

    public function group(){
        return $this->belongsTo(GroupDetail::class,'group_id');
    }

    // public function group_msg(){
    //     return $this->hasMany(GroupMessage::class,'group_id');
    // }
}
