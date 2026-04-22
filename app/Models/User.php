<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Contracts\OAuthenticatable;
use Illuminate\Http\Request;
use App\Models\OneToOneMessage;
use App\Models\GroupDetail;
use App\Models\GroupMessage;
use App\Models\GroupMessageDetail;

class User extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ["name", "email", "password"];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "email_verified_at" => "datetime",
    ];

    public function on_to_one_message_sender()
    {
        return $this->hasMany(OneToOneMessage::class, "sender_id");
    }

    public function on_to_one_message_rec()
    {
        return $this->hasMany(OneToOneMessage::class, "receiver_id");
    }

    public function groups_created()
    {
        return $this->hasMany(GroupDetail::class, "created_by");
    }

    public function groups_connected()
    {
        return $this->belongsToMany(
            GroupDetail::class,
            "group_members",
            "member_id",
            "group_id",
        );
    }

    public function group_messages()
    {
        return $this->hasMany(GroupMessage::class, "sender_id");
    }

    public function group_message_rec()
    {
        return $this->hasMany(GroupMessageDetail::class, "receiver_id");
    }
}
