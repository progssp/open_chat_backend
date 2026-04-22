<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupDetail>
 */
class GroupDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'created_by' => 1,
            'name' => 'My group 1',
            'icon' => '/user_images/profile_pictures/picture.png',
            'tagline' => 'this is dummy tagline',
            'description' => 'this is dummy description'
        ];
    }
}


/*
query for getting all groups messages

select 
gd.id,gd.created_by,gd.name,gd.icon,gd.tagline,gd.description, 
g_msg.sender_id,g_msg.group_id,g_msg.message,g_msg.created_at, 
u.name, 
gm.group_id,gm.member_id,gm.deleted_at
from group_messages g_msg 
join (SELECT max(id) as "latest_id" FROM `group_messages` group by group_id order by id desc) l on g_msg.id = l.latest_id
join group_details gd on g_msg.group_id=gd.id 
join group_members gm on g_msg.group_id = gm.group_id
join users u on g_msg.sender_id = u.id 
where gm.member_id = 1 
group by g_msg.group_id 
order by g_msg.id desc;
*/