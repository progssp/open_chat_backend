<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GroupMessage;
use App\Models\GroupMessageDetail;
use App\Models\GroupMember;
use App\Events\MessageSent;
use App\Jobs\GroupSendMsg;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\OneToOneMessageController;
use Illuminate\Broadcasting\PrivateChannel;

use App\Http\SendMessagesForSharedServer;

class GroupMessageController extends Controller {
   
    public function generate_group_mess(){
        $res = GroupMessage::factory()->count(10)->create();
        return response()->json("hello");
    }

    public static function get_group_messages_for_left_panel(Request $request){
        $user_id = $request->user()->id;
        
        $final_arr = [];

        $data = GroupMessage::join(DB::raw('(select max(gm.id) as last_id from group_messages gm inner join group_message_details gmd where gm.sender_id='.$user_id.' or gmd.receiver_id='.$user_id.' group by gm.group_id) rset'),'group_messages.id','=','rset.last_id')
        ->select('id','sender_id','message_type','message','file_path','file_type','group_id','created_at','deleted_at as sender_deleted_at')
        ->with(['user_sender' => function($qry){
            $qry->select('id','user_first_name as sender_nm');
        }])
        ->with(['group_details' => function($qry){
            $qry->select('id','name as group_nm','icon','tagline','description');
        }])
        ->with(['member_details' => function($qry){
            $qry->select('member_id','deleted_at as member_removed_at','is_conv_deleted');
        }])
        ->with(['message_details' => function($qry){
            $qry->select('id','msg_id','deleted_at as receiver_deleted_at');
        }])->get();
        // echo json_encode($data);

        if($data->isNotEmpty()){
            
            $final_obj = new \stdClass;
            foreach($data as $indi_msg){
                $final_obj->id = $indi_msg->id;
                $final_obj->sender_id = $indi_msg->sender_id;
                $final_obj->sender_nm = $indi_msg->user_sender->sender_nm;
                $final_obj->message_type = $indi_msg->message_type;
                $final_obj->message = $indi_msg->message;
                $final_obj->file_type = $indi_msg->file_type;
                $final_obj->file_path = $indi_msg->file_path;
                $final_obj->group_id = $indi_msg->group_id;
                $final_obj->member_id = $indi_msg->member_details->member_id;
                $final_obj->member_removed_at = $indi_msg->member_details->member_removed_at;
                $final_obj->is_conv_deleted = $indi_msg->member_details->is_conv_deleted;
                $final_obj->receiver_deleted_at = $indi_msg->message_details[0]->receiver_deleted_at;
                $final_obj->group_nm = $indi_msg->group_details->group_nm;
                $final_obj->icon = $indi_msg->group_details->icon;
                $final_obj->tagline = $indi_msg->group_details->tagline;
                $final_obj->description = $indi_msg->group_details->description;
                $final_obj->created_at = $indi_msg->created_at;
                $final_obj->sender_deleted_at = $indi_msg->sender_deleted_at;
                $final_arr[] = $final_obj;
                $final_obj = new \stdClass;
            }
        }


        //getting otom messages
        $data_otom = OneToOneMessageController::get_one_to_one_messages_for_left_panel($user_id);
        for($i=0;$i<count($data_otom);$i++){
            $final_arr[] = $data_otom[$i];
        }

        //sorting array
        // for($i=0;$i<count($data_otom);$i++){}
        $tmp = 0;
        // echo "unsorted<br/>" . json_encode($ar) . "<br/>";
        for($i=0;$i<count($final_arr);$i++){
            for($j=0;$j<count($final_arr)-1;$j++){
                if($final_arr[$j+1]->created_at > $final_arr[$j]->created_at){
                    $tmp = $final_arr[$j];
                    $final_arr[$j] = $final_arr[$j+1];
                    $final_arr[$j+1] = $tmp;
                }
            }
        }
        // echo "sorted<br/>" . json_encode($ar) . "<br/>";

        return response()->json($final_arr);
    }

    public function get_group_messages(Request $request){
        $group_id = $request->group_id;
        $validated =Validator::make($request->all(), ['group_id'=>'required']);
        if($validated->fails()){
            return response()->json($validated->errors());
        }
        $data = GroupMessage::select('id','sender_id','message_type','message','file_path','file_type','group_id','created_at','deleted_at as sender_deleted_at')
        ->with(['user_sender' => function($qry){
            $qry->select('id','user_first_name as sender_nm');
        }])
        ->with(['group_details' => function($qry){
            $qry->select('id','name as group_nm','icon','tagline','description');
        }])
        ->where('group_id',1)
        ->orderBy('created_at','DESC')
        ->get();
        if($data->isNotEmpty()){
            
            $final_arr = [];
            
            $final_obj = new \stdClass;
            foreach($data as $indi_msg){
                $final_obj->id = $indi_msg->id;
                $final_obj->sender_id = $indi_msg->sender_id;
                $final_obj->sender_nm = $indi_msg->user_sender->sender_nm;
                $final_obj->message_type = $indi_msg->message_type;
                $final_obj->message = $indi_msg->message;
                $final_obj->file_type = $indi_msg->file_type;
                $final_obj->file_path = $indi_msg->file_path;
                $final_obj->group_nm = $indi_msg->group_details->group_nm;
                $final_obj->icon = $indi_msg->group_details->icon;
                $final_obj->tagline = $indi_msg->group_details->tagline;
                $final_obj->description = $indi_msg->group_details->description;
                $final_obj->created_at = $indi_msg->created_at;
                $final_arr[] = $final_obj;
                $final_obj = new \stdClass;
            }
            
            
            return response()->json($final_arr);
        }
        else{
            echo "not found";
        }        
    }

    public function send_group_message(Request $request){
        // return response()->json($request->all());
        $validator = Validator::make($request->all(), [
            'group_id' => 'required',
            // 'message' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors());
        }

        if((!isset($request->message)) && (!$request->hasFile("files_to_send"))){
            $ret_arr = ['status'=>false,'msg'=>'either send a message or a file'];
            return response()->json($ret_arr);
        }

        $checkUserAndGroup = GroupMember::where('group_id',$request->group_id)
        ->where('member_id',$request->user()->id)->first();
        if(empty($checkUserAndGroup)){
            $ret_arr = ['status'=>false,'msg'=>'user is not a member of group'];
            return response()->json($ret_arr);
        }

        $rec_list = GroupMember::select('member_id')
        ->where('group_id',$request->group_id)->orderBy('id')->get();

        if((isset($request->message)) && ($request->hasFile("files_to_send"))){
            // return response()->json('has file and msg both');
            $files_to_send = $request->file('files_to_send');
            $icon_path = [];
            $file_type = [];
            if(count($files_to_send) > 0){
                foreach($files_to_send as $image_to_upload){
                    $tmp_icon_path = $image_to_upload->store('sent_files');
                    $tmp_icon_path = "/".$tmp_icon_path;
                    $icon_path[] = $tmp_icon_path;
                    $file_type[] = $image_to_upload->getClientOriginalExtension();
                }
                $data_for_queue = [
                    'image_path' => $icon_path,
                    'file_type' => $file_type,
                    'message' => $request->message,
                    'user' => $request->user(),
                    'group_id' => $request->group_id
                ];
                    
                // GroupSendMsg::dispatch($data_for_queue);
                $send_msg = new SendMessagesForSharedServer($data_for_queue);
                $send_msg->send_group_message();
                

                $ret_arr = ['status'=>true,'msg'=>count($request->file('files_to_send')) . ' uploaded and msg sent'];
                return response()->json($ret_arr);
            }
        }
        else{
            if(isset($request->message)){
                // return response()->json('has msg only');
                $data_for_queue = [
                    'image_path' => [],
                    'message' => $request->message,
                    'user' => $request->user(),
                    'group_id' => $request->group_id
                ];
                // GroupSendMsg::dispatch($data_for_queue);
                
                $send_msg = new SendMessagesForSharedServer($data_for_queue);
                $send_msg->send_group_message();

                $ret_arr = ['status' => true,'msg' => 'msg sent'];
                return response()->json($ret_arr);
            }
            else if($request->hasFile("files_to_send")){
                // return response()->json('has file only');
                $files_to_send = $request->file('files_to_send');
                $icon_path = [];
                $file_type = [];
                if(count($files_to_send) > 0){
                    foreach($files_to_send as $image_to_upload){
                        $tmp_icon_path = $image_to_upload->store('sent_files');
                        $tmp_icon_path = "/".$tmp_icon_path;
                        $icon_path[] = $tmp_icon_path;
                        $file_type[] = $image_to_upload->getClientOriginalExtension();
                    }
                    
                    $data_for_queue = [
                        'image_path' => $icon_path,
                        'file_type' => $file_type,
                        'message' => NULL,
                        'user' => $request->user(),
                        'group_id' => $request->group_id
                    ];
                    
                    // GroupSendMsg::dispatch($data_for_queue);
                    
                    $send_msg = new SendMessagesForSharedServer($data_for_queue);
                    $send_msg->send_group_message();

                    $ret_arr = ['status'=>true,'msg'=>count($request->file('files_to_send')) . ' uploaded'];
                    return response()->json($ret_arr);
                }
            }
        }
    }

    public static function get_last_group_message($group_id){
       
        $data = GroupMessage::
        select('id','sender_id','message_type','message','file_path','file_type','group_id','created_at','deleted_at as sender_deleted_at')
        ->with(['user_sender' => function($qry){
            $qry->select('id','user_first_name as sender_nm');
        }])
        ->with(['group_details' => function($qry){
            $qry->select('id','name as group_nm','icon','tagline','description');
        }])
        ->with(['member_details' => function($qry){
            $qry->select('member_id','deleted_at as member_removed_at','is_conv_deleted');
        }])
        ->with(['message_details' => function($qry){
            $qry->select('id','msg_id','deleted_at as receiver_deleted_at');
        }])->orderBy('id','DESC')->take(1)->get();
        // echo json_encode($data);

        if($data->isNotEmpty()){
            
            $final_arr = [];
            
            $final_obj = new \stdClass;
            foreach($data as $indi_msg){
                $final_obj->id = $indi_msg->id;
                $final_obj->sender_id = $indi_msg->sender_id;
                $final_obj->sender_nm = $indi_msg->user_sender->sender_nm;                
                $final_obj->message_type = $indi_msg->message_type;
                $final_obj->message = $indi_msg->message;
                $final_obj->file_type = $indi_msg->file_type;
                $final_obj->file_path = $indi_msg->file_path;
                $final_obj->group_id = $indi_msg->group_id;
                $final_obj->group_nm = $indi_msg->group_details->group_nm;
                $final_obj->icon = $indi_msg->group_details->icon;
                $final_obj->tagline = $indi_msg->group_details->tagline;
                $final_obj->description = $indi_msg->group_details->description;
                $final_obj->created_at = $indi_msg->created_at;
                $final_obj->sender_deleted_at = $indi_msg->sender_deleted_at;
                $final_arr[] = $final_obj;
                $final_obj = new \stdClass;
            }
            
            
            return ($final_arr);
        }
        else{
            echo "not found";
        }
        
    }
}
