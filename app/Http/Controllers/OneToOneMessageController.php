<?php

namespace App\Http\Controllers;
use App\Models\OneToOneMessage;
use App\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Broadcasting\PrivateChannel;
use App\Jobs\OneToOneSendMsg;
use App\Http\SendMessagesForSharedServer;
use App\Events\StartVideoCall;

class OneToOneMessageController extends Controller
{
    public static function get_one_to_one_messages_for_left_panel($user_id) : array{
        // $result = DB::select('call getOneToOneMessagesForLeftPanel('.$user_id.')');        
        // return $result;
        
        $final_arr = [];
        $data = OneToOneMessage::join(DB::raw('(select max(id) as "last_id" from one_to_one_messages otom where otom.sender_id='.$user_id.' or otom.receiver_id='.$user_id.' group by otom.secret_string) rset'),'one_to_one_messages.id','=','rset.last_id')
        ->select('id','sender_id','receiver_id','message_type','message','file_type','file_path','created_at')
        ->with(['user_sender'=>function($qry){
            $qry->select('id','user_first_name as sender_nm','icon as sender_icon');
        }])->with(['user_receiver'=>function($qry){
            $qry->select('id','user_first_name as receiver_nm','icon as receiver_icon');
        }])
        ->get();


        if($data->isNotEmpty()){            
            $final_obj = new \stdClass;
            foreach($data as $indi_msg){
                $final_obj->id = $indi_msg->id;
                $final_obj->sender_id = $indi_msg->sender_id;
                $final_obj->sender_nm = $indi_msg->user_sender->sender_nm;
                $final_obj->sender_icon = $indi_msg->user_sender->sender_icon;
                $final_obj->receiver_id = $indi_msg->receiver_id;
                $final_obj->receiver_nm = $indi_msg->user_receiver->receiver_nm;
                $final_obj->receiver_icon = $indi_msg->user_receiver->receiver_icon;
                $final_obj->message_type = $indi_msg->message_type;
                $final_obj->message = $indi_msg->message;
                $final_obj->file_type = $indi_msg->file_type;
                $final_obj->file_path = $indi_msg->file_path;
                $final_obj->created_at = $indi_msg->created_at;
                $final_arr[] = $final_obj;
                $final_obj = new \stdClass;
            }
        }
        return $final_arr;
    }

    public static function get_one_to_one_messages(Request $request){
        $sender_id = $request->sender_id;
        $receiver_id = $request->receiver_id;
        // $result = DB::select('call getOneToOneMessages('.$sender_id.','.$receiver_id.')');
        
        // return response()->json($result);
        $data = OneToOneMessage::select('id','sender_id','receiver_id','message','message_type','file_path','file_type','created_at')
        ->with(['user_sender' => function($qry){
            $qry->select('id','user_first_name as sender_nm','icon as sender_icon');
        }])
        ->with(['user_receiver' => function($qry){
            $qry->select('id','user_first_name as receiver_nm','icon as receiver_icon');
        }])
        ->where(function($qry) use($sender_id,$receiver_id){
            $qry->where('sender_id',$sender_id)->where('receiver_id',$receiver_id);
        })
        ->orWhere(function($qry) use($sender_id,$receiver_id){            
            $qry->where('sender_id',$receiver_id)->where('receiver_id',$sender_id);
        })
        ->orderBy('created_at','DESC')
        ->get();
        

        if($data->isNotEmpty()){
            
            $final_arr = [];
            
            $final_obj = new \stdClass;
            foreach($data as $indi_msg){
                $final_obj->id = $indi_msg->id;
                $final_obj->sender_id = $indi_msg->sender_id;
                $final_obj->sender_nm = $indi_msg->user_sender->sender_nm;
                $final_obj->sender_icon = $indi_msg->user_sender->sender_icon;
                $final_obj->receiver_id = $indi_msg->receiver_id;
                $final_obj->receiver_nm = $indi_msg->user_receiver->receiver_nm;
                $final_obj->receiver_icon = $indi_msg->user_receiver->receiver_icon;
                $final_obj->message_type = $indi_msg->message_type;
                $final_obj->message = $indi_msg->message;
                $final_obj->file_type = $indi_msg->file_type;
                $final_obj->file_path = $indi_msg->file_path;
                $final_obj->created_at = $indi_msg->created_at;
                $final_arr[] = $final_obj;
                $final_obj = new \stdClass;
            }
            
            
            return response()->json(['status'=>true,'messages'=>$final_arr]);
        }
        else{
            return response()->json(['status'=>false,'msg'=>'no message']);
        }
    }

    public function send_one_to_one_message(Request $request){
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors());
        }

        if((!isset($request->message)) && (!$request->hasFile("files_to_send"))){
            $ret_arr = ['status'=>false,'msg'=>'either send a message or a file'];
            return response()->json($ret_arr);
        }
        
        if((isset($request->message)) && ($request->hasFile("files_to_send"))){
            // return response()->json('has file and msg both');
            $files_to_send = $request->file('files_to_send');
            $icon_path = [];
            $file_type = [];
            if(count($files_to_send) > 0){
                foreach($files_to_send as $image_to_upload){
                    $tmp_icon_path = $image_to_upload->store('sent_files');
                    Log::debug($tmp_icon_path);
                    // $tmp_icon_path = substr($tmp_icon_path,strpos($tmp_icon_path,"/"),strlen($tmp_icon_path));
                    $tmp_icon_path = "/".$tmp_icon_path;
                    $icon_path[] = $tmp_icon_path;
                    $file_type[] = $image_to_upload->getClientOriginalExtension();
                }
                $data_for_queue = [
                    'image_path' => $icon_path,
                    'file_type' => $file_type,
                    'message' => $request->message,
                    'user' => $request->user(),
                    'receiver_id' => $request->receiver_id
                ];
                    
                //OneToOneSendMsg::dispatch($data_for_queue);
                $send_msg = new SendMessagesForSharedServer($data_for_queue);
                $send_msg->send_one_to_one_messsage();

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
                    'receiver_id' => $request->receiver_id
                ];
                //OneToOneSendMsg::dispatch($data_for_queue);
                
                $send_msg = new SendMessagesForSharedServer($data_for_queue);
                $send_msg->send_one_to_one_messsage();
                
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
                        \Log::debug($tmp_icon_path);
                        // $tmp_icon_path = substr($tmp_icon_path,strpos($tmp_icon_path,"/"),strlen($tmp_icon_path));
                        $tmp_icon_path = "/".$tmp_icon_path;
                        $icon_path[] = $tmp_icon_path;
                        $file_type[] = $image_to_upload->getClientOriginalExtension();
                    }
                    
                    $data_for_queue = [
                        'image_path' => $icon_path,
                        'file_type' => $file_type,
                        'message' => NULL,
                        'user' => $request->user(),
                        'receiver_id' => $request->receiver_id
                    ];
                    
                    //OneToOneSendMsg::dispatch($data_for_queue);
                    
                    $send_msg = new SendMessagesForSharedServer($data_for_queue);
                    $send_msg->send_one_to_one_messsage();

                    $ret_arr = ['status'=>true,'msg'=>count($request->file('files_to_send')) . ' uploaded'];
                    return response()->json($ret_arr);
                }
            }
        }
    }

    public static function get_last_one_to_one_messages($sender_id,$receiver_id){
        
        $data = OneToOneMessage::select('id','sender_id','receiver_id','message','message_type','file_path','file_type','created_at')
        ->with(['user_sender' => function($qry){
            $qry->select('id','user_first_name as sender_nm','icon as sender_icon');
        }])
        ->with(['user_receiver' => function($qry){
            $qry->select('id','user_first_name as receiver_nm','icon as receiver_icon');
        }])
        ->where(function($qry) use($sender_id,$receiver_id){
            $qry->where('sender_id',$sender_id)->where('receiver_id',$receiver_id);
        })
        ->orWhere(function($qry) use($sender_id,$receiver_id){            
            $qry->where('sender_id',$receiver_id)->where('receiver_id',$sender_id);
        })
        ->orderBy('created_at','DESC')->take(1)
        ->get();
        // return ($data);

        if($data->isNotEmpty()){
            
            $final_arr = [];
            
            $final_obj = new \stdClass;
            foreach($data as $indi_msg){
                $final_obj->id = $indi_msg->id;
                $final_obj->sender_id = $indi_msg->sender_id;
                $final_obj->sender_nm = $indi_msg->user_sender->sender_nm;
                $final_obj->sender_icon = $indi_msg->user_sender->sender_icon;
                $final_obj->receiver_id = $indi_msg->receiver_id;
                $final_obj->receiver_nm = $indi_msg->user_receiver->receiver_nm;
                $final_obj->receiver_icon = $indi_msg->user_receiver->receiver_icon;
                $final_obj->message_type = $indi_msg->message_type;
                $final_obj->message = $indi_msg->message;
                $final_obj->file_type = $indi_msg->file_type;
                $final_obj->file_path = $indi_msg->file_path;
                $final_obj->created_at = $indi_msg->created_at;
                $final_arr[] = $final_obj;
                $final_obj = new \stdClass;
            }
            
            
            return ($final_arr);
        }
        else{
            echo "not found";
        }
    }


    public function start_video_call(Request $request){

        $user = $request->user()->id;
        $rec_id = $request->rec_id;
        $stream = $request->stream;
        
            
        broadcast(new StartVideoCall($user, $rec_id,$stream));

        $ret_arr = ['status'=>true,'started'];
        return response()->json($ret_arr);
      
    }
}
