<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\GroupDetail;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Models\GroupMessageDetail;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Log;
use App\Events\MessageSent;
use DB;

class GroupDetailController extends Controller {
    
    public function create_group(Request $request){
        $icon_path = null;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'tagline' => 'required',
            'description' => 'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors());
        }
        
        DB::beginTransaction();
        try{
            if($request->hasFile('icon')){
                $icon_path = $request->file('icon')->store('group_icons');
            }                
            // $icon_path = substr($icon_path,strpos($icon_path,"/"),strlen($icon_path));
            $name = $request->name;
            $tagline = $request->tagline;
            $description = $request->description;

            $new_group = GroupDetail::insertGetId([
                'created_by' => $request->user()->id,
                'icon' => $icon_path ?? '/defaults/user_icons/default_profile_image.jpg',
                'name' => $name,
                'tagline' => $tagline,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            DB::commit();
            return response()->json(['status'=>true,'msg'=>"group created",'data'=>$new_group]);
            
        }
        catch(\Exception $e){
            DB::rollback();
            $ret_arr = ['status'=>false,'msg'=>'Error'];
            Log::debug('file: '.__FILE__.'\nline: '.__LINE__.',\nerr: '.$e.'\n\n');
            return response()->json($ret_arr);
        }
    }

    public function add_members_in_group(Request $request){
        $validator = Validator::make($request->all(), [
            'group_id' => 'required',
            'members_id' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors());
        }
        
        DB::beginTransaction();
        try{
            $members_arr = json_decode($request->members_id,true);

            //adding members in group        
            foreach($members_arr as $m_id){
                $new_member = GroupMember::insert([
                    'group_id' => $request->group_id,
                    'member_id' => $m_id,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            //sending info message of 'group create'
            $new_info = GroupMessage::insertGetId([
                'sender_id' => $request->user()->id,
                'group_id' => $request->group_id,
                'message' => 'New group has been created by '.$request->user()->user_first_name,
                'message_type' => 'group_info',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            //sending info message of 'group create' in group_messgae_detail
            foreach($members_arr as $m_id){
                if($m_id != $request->user()->id){
                    $new_info_gmd = GroupMessageDetail::insert([
                        'msg_id' => $new_info,
                        'receiver_id' => $m_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            // getting last message to broadcast
            // $last_msg = DB::select('call getLastGroupMessage('.$request->group_id.')');
            $last_msg = GroupMessageController::get_last_group_message($request->group_id);
            $last_msg = $last_msg[0];

            //broadcast msg;
            $channel_arr = [];
            foreach($members_arr as $indi_rec){
                $channel = new PrivateChannel('user-'.$indi_rec);
                $channel_arr[] = $channel;
            }
            
            broadcast(new MessageSent($channel_arr, $last_msg));
            DB::commit();


            $ret_arr = ['status'=>true,'msg'=>'members added'];
            return response()->json($ret_arr);
        }
        catch(\Exception $e){
            DB::rollback();
            $ret_arr = ['status'=>false,'msg'=>'Error'];
            Log::debug('file: '.__FILE__.'\nline: '.__LINE__.',\nerr: '.$e.'\n\n');
            return response()->json($ret_arr);
        }       
    }
}
