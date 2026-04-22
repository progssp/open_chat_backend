<?php
namespace App\Http;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\GroupMessage;
use App\Models\GroupMessageDetail;
use App\Models\GroupMember;
use App\Events\MessageSent;
use App\Models\OneToOneMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Broadcasting\PrivateChannel;
use App\Http\Controllers\OneToOneMessageController;
use App\Http\Controllers\GroupMessageController;

class SendMessagesForSharedServer {
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function send_group_message()
    {

        if((count($this->data['image_path'])!=0) && ($this->data['message']!=NULL)){     
            Log::info("User: " . $this->data['user']->id . " is uploading ".count($this->data['image_path'])." files with message: " . $this->data['message']);
            Log::info("file type: ".$this->data['file_type'][0]);
            // DB::beginTransaction();
            try{
                //saving message in group_message table
                $new_group_msg_id = GroupMessage::insertGetId([
                    'sender_id' => $this->data['user']->id,
                    'group_id' => $this->data['group_id'],
                    'message' => $this->data['message'],
                    'file_path' => json_encode($this->data['image_path']),
                    'file_type' => json_encode($this->data['file_type']),
                    'message_type' => 'group_file',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $rec_list = GroupMember::select('member_id')
                ->where('group_id',$this->data['group_id'])->orderBy('id')->get();
                
                foreach($rec_list as $indi_rec){
                    if($indi_rec->member_id != $this->data['user']->id){
                        $new_group_msg_detail = new GroupMessageDetail;
                        $new_group_msg_detail->msg_id = $new_group_msg_id;
                        $new_group_msg_detail->receiver_id = $indi_rec->member_id;
                        $new_group_msg_detail->created_at = date('Y-m-d H:i:s');
                        $new_group_msg_detail->save();
                    }
                }

                // getting last message to broadcast
                // $last_msg = DB::select('call getLastGroupMessage('.$this->data['group_id'].')');
                $last_msg = GroupMessageController::get_last_group_message($this->data['group_id']);
                $last_msg = $last_msg[0];

                //broadcast msg;
                $channel_arr = [];
                foreach($rec_list as $indi_rec){
                    $channel = new PrivateChannel('user-'.$indi_rec->member_id);
                    $channel_arr[] = $channel;
                }
            
                broadcast(new MessageSent($channel_arr, $last_msg));
            }
            catch(\Exception $e){
                // DB::rollback();
                // $ret_arr = ['status'=>false,'msg'=>'Error'];
                Log::error($e->getMessage());
                // return response()->json($ret_arr);
            }
        }
        else{
            if($this->data['message'] != NULL){
                Log::info("User: " . $this->data['user']->id . " is sending message: " . $this->data['message']);
                try{
                    //saving message in group_message table
                    $new_group_msg_id = GroupMessage::insertGetId([
                        'sender_id' => $this->data['user']->id,
                        'group_id' => $this->data['group_id'],
                        'message' => $this->data['message'],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
    
                    $rec_list = GroupMember::select('member_id')
                    ->where('group_id',$this->data['group_id'])->orderBy('id')->get();
                    
                    foreach($rec_list as $indi_rec){
                        if($indi_rec->member_id != $this->data['user']->id){
                            $new_group_msg_detail = new GroupMessageDetail;
                            $new_group_msg_detail->msg_id = $new_group_msg_id;
                            $new_group_msg_detail->receiver_id = $indi_rec->member_id;
                            $new_group_msg_detail->created_at = date('Y-m-d H:i:s');
                            $new_group_msg_detail->save();
                        }
                    }
    
                    // getting last message to broadcast
                    // $last_msg = DB::select('call getLastGroupMessage('.$this->data['group_id'].')');
                    $last_msg = GroupMessageController::get_last_group_message($this->data['group_id']);
                    $last_msg = $last_msg[0];
    
                    //broadcast msg;
                    $channel_arr = [];
                    foreach($rec_list as $indi_rec){
                        $channel = new PrivateChannel('user-'.$indi_rec->member_id);
                        $channel_arr[] = $channel;
                    }
                
                    broadcast(new MessageSent($channel_arr, $last_msg));
                }
                catch(\Exception $e){
                    // DB::rollback();
                    // $ret_arr = ['status'=>false,'msg'=>'Error'];
                    Log::error($e->getMessage());
                    // return response()->json($ret_arr);
                }
            }
            else if(count($this->data['image_path']) != 0){
                Log::info("User: " . $this->data['user']->id . " is uploading ".count($this->data['image_path'])." files");
                Log::info("file type: ".json_encode($this->data['file_type']));
                try{
                    //saving message in group_message table
                    $new_group_msg_id = GroupMessage::insertGetId([
                        'sender_id' => $this->data['user']->id,
                        'group_id' => $this->data['group_id'],
                        'file_path' => json_encode($this->data['image_path']),
                        'file_type' => json_encode($this->data['file_type']),
                        'message_type' => 'group_file',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
    
                    $rec_list = GroupMember::select('member_id')
                    ->where('group_id',$this->data['group_id'])->orderBy('id')->get();
                    
                    foreach($rec_list as $indi_rec){
                        if($indi_rec->member_id != $this->data['user']->id){
                            $new_group_msg_detail = new GroupMessageDetail;
                            $new_group_msg_detail->msg_id = $new_group_msg_id;
                            $new_group_msg_detail->receiver_id = $indi_rec->member_id;
                            $new_group_msg_detail->created_at = date('Y-m-d H:i:s');
                            $new_group_msg_detail->save();
                        }
                    }
    
                    // getting last message to broadcast
                    // $last_msg = DB::select('call getLastGroupMessage('.$this->data['group_id'].')');
                    $last_msg = GroupMessageController::get_last_group_message($this->data['group_id']);
                    $last_msg = $last_msg[0];
    
                    //broadcast msg;
                    $channel_arr = [];
                    foreach($rec_list as $indi_rec){
                        $channel = new PrivateChannel('user-'.$indi_rec->member_id);
                        $channel_arr[] = $channel;
                    }
                
                    broadcast(new MessageSent($channel_arr, $last_msg));
                }
                catch(\Exception $e){
                    // DB::rollback();
                    // $ret_arr = ['status'=>false,'msg'=>'Error'];
                    Log::error($e->getMessage());
                    // return response()->json($ret_arr);
                }
            }
        }
        
    }

    public function send_one_to_one_messsage()
    {
        $sender = $this->data['user']->id;
        $receiver = $this->data['receiver_id'];
        //getting secret string
        $secret_string = DB::select("select otom.* from one_to_one_messages otom where (otom.sender_id=".$sender." and otom.receiver_id=".$receiver.") or (otom.sender_id=".$receiver." and otom.receiver_id=".$sender.") order by otom.id desc limit 0,1;");
        if(empty($secret_string)){
            $receiver_nm = User::select('username')->where('id',$receiver)->first();
            $secret_string = $this->data['user']->username.''.$receiver_nm->username;
        }
        else{
            $secret_string = $secret_string[0]->secret_string;
        }

        if((count($this->data['image_path'])!=0) && ($this->data['message']!=NULL)){     
            Log::info("User: " . $this->data['user']->id . " is uploading ".count($this->data['image_path'])." files with message: " . $this->data['message']);
            Log::info("file type: ".$this->data['file_type'][0]);

            // DB::beginTransaction();
            try{
                // saving new message
                $new_group_msg = new OneToOneMessage;
                $new_group_msg->sender_id = $sender;
                $new_group_msg->receiver_id = $receiver;
                $new_group_msg->message = $this->data['message'];
                $new_group_msg->file_path = json_encode($this->data['image_path']);
                $new_group_msg->file_type = json_encode($this->data['file_type']);
                $new_group_msg->secret_string = $secret_string;
                $new_group_msg->created_at = date('Y-m-d H:i:s');
                $new_group_msg->save();

                // getting last message to broadcast
                // $last_msg = DB::select('call getLastOneToOneMessage('.$sender.','.$receiver.')');
                $last_msg = OneToOneMessageController::get_last_one_to_one_messages($sender,$receiver);
                Log::debug($last_msg);
                
                $last_msg = $last_msg[0];

                //broadcast msg;
                $channel_arr = [];
                $channel = new PrivateChannel('user-'.$last_msg->sender_id);
                $channel_arr[] = $channel;
                
                $channel = new PrivateChannel('user-'.$last_msg->receiver_id);
                $channel_arr[] = $channel;
                
                broadcast(new MessageSent($channel_arr,$last_msg));
            }
            catch(\Exception $e){
                // DB::rollback();
                // $ret_arr = ['status'=>false,'msg'=>'Error'];
                Log::error($e->getMessage());
                // return response()->json($ret_arr);
            }
        }
        else{
            if($this->data['message'] != NULL){
                Log::info("User: " . $this->data['user']->id . " is sending message: " . $this->data['message']);
                DB::beginTransaction();
                try{
                    // saving new message
                    $new_group_msg = new OneToOneMessage;
                    $new_group_msg->sender_id = $sender;
                    $new_group_msg->receiver_id = $receiver;
                    $new_group_msg->message = $this->data['message'];
                    $new_group_msg->secret_string = $secret_string;
                    $new_group_msg->created_at = date('Y-m-d H:i:s');
                    $new_group_msg->save();

                    // getting last message to broadcast
                    // $last_msg = DB::select('call getLastOneToOneMessage('.$sender.','.$receiver.')');
                    $last_msg = OneToOneMessageController::get_last_one_to_one_messages($sender,$receiver);
                    $last_msg = $last_msg[0];
                    

                    //broadcast msg;
                    $channel_arr = [];
                    $channel = new PrivateChannel('user-'.$last_msg->sender_id);
                    $channel_arr[] = $channel;
                    
                    $channel = new PrivateChannel('user-'.$last_msg->receiver_id);
                    $channel_arr[] = $channel;
                    
                    broadcast(new MessageSent($channel_arr,$last_msg));
                    DB::commit();
                }
                catch(\Exception $e){
                    DB::rollback();
                    $ret_arr = ['status'=>false,'msg'=>'Error'];
                    Log::error($e->getMessage());
                    return response()->json($ret_arr);
                }
            }
            else if(count($this->data['image_path'])!=0){
                Log::info("User: " . $this->data['user']->id . " is uploading ".count($this->data['image_path'])." files");
                Log::info("file type: ".json_encode($this->data['file_type']));
                // DB::beginTransaction();
                try{
                    // saving new message
                    $new_group_msg = new OneToOneMessage;
                    $new_group_msg->sender_id = $sender;
                    $new_group_msg->receiver_id = $receiver;
                    $new_group_msg->file_path = json_encode($this->data['image_path']);
                    $new_group_msg->file_type = json_encode($this->data['file_type']);
                    $new_group_msg->secret_string = $secret_string;
                    $new_group_msg->created_at = date('Y-m-d H:i:s');
                    $new_group_msg->save();

                    // getting last message to broadcast
                    // $last_msg = DB::select('call getLastOneToOneMessage('.$sender.','.$receiver.')');
                    $last_msg = OneToOneMessageController::get_last_one_to_one_messages($sender,$receiver);
                    $last_msg = $last_msg[0];

                    //broadcast msg;
                    $channel_arr = [];
                    $channel = new PrivateChannel('user-'.$last_msg->sender_id);
                    $channel_arr[] = $channel;
                    
                    $channel = new PrivateChannel('user-'.$last_msg->receiver_id);
                    $channel_arr[] = $channel;
                    
                    broadcast(new MessageSent($channel_arr,$last_msg));
                }
                catch(\Exception $e){
                    // DB::rollback();
                    // $ret_arr = ['status'=>false,'msg'=>'Error'];
                    Log::error($e->getMessage());
                    // return response()->json($ret_arr);
                }
            }
        }        
    }
}