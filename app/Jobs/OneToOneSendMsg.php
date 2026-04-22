<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\OneToOneMessage;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Broadcasting\PrivateChannel;


class OneToOneSendMsg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
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
                $last_msg = DB::select('call getLastOneToOneMessage('.$sender.','.$receiver.')');
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
                // DB::beginTransaction();
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
                    $last_msg = DB::select('call getLastOneToOneMessage('.$sender.','.$receiver.')');
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
                    $last_msg = DB::select('call getLastOneToOneMessage('.$sender.','.$receiver.')');
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
