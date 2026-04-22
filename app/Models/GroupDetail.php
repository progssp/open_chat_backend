<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\GroupMember;

class GroupDetail extends Model
{
    use HasFactory;

    public function admin(){
        return $this->belongsTo(User::class,'created_by');
    }

    public function members(){
        return $this->belongsToMany(User::class,'group_members','group_id','member_id');
    }

    public function messages(){
        return $this->hasMany(GroupMessage::class,'group_id');
    }
}
