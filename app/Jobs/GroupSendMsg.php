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

use App\Models\GroupMessage;
use App\Models\GroupMessageDetail;
use App\Models\GroupMember;
use App\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Broadcasting\PrivateChannel;


class GroupSendMsg implements ShouldQueue
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
                $last_msg = DB::select('call getLastGroupMessage('.$this->data['group_id'].')');
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
                    $last_msg = DB::select('call getLastGroupMessage('.$this->data['group_id'].')');
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
            else if(count($this->data['image_path'])!=0){
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
                    $last_msg = DB::select('call getLastGroupMessage('.$this->data['group_id'].')');
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
        
        
        // DB::beginTransaction();
        // try{
        //     //saving message in group_message table
        //     $new_group_msg_id = GroupMessage::insertGetId([
        //         'sender_id' => $this->data['user']->id,
        //         'group_id' => $this->data['group_id'],
        //         'message' => $this->data['image_path'],
        //         'created_at' => date('Y-m-d H:i:s')
        //     ]);

        //     $rec_list = GroupMember::select('member_id')
        //     ->where('group_id',$this->data['group_id'])->orderBy('id')->get();
            
        //     foreach($rec_list as $indi_rec){
        //         if($indi_rec->member_id != $this->data['user']->id){
        //             $new_group_msg_detail = new GroupMessageDetail;
        //             $new_group_msg_detail->msg_id = $new_group_msg_id;
        //             $new_group_msg_detail->receiver_id = $indi_rec->member_id;
        //             $new_group_msg_detail->created_at = date('Y-m-d H:i:s');
        //             $new_group_msg_detail->save();
        //         }
        //     }

        //     // getting last message to broadcast
        //     $last_msg = DB::select('call getLastGroupMessage('.$this->data['group_id'].')');
        //     $last_msg = $last_msg[0];

        //     //broadcast msg;
        //     $channel_arr = [];
        //     foreach($rec_list as $indi_rec){
        //         $channel = new PrivateChannel('user-'.$indi_rec->member_id);
        //         $channel_arr[] = $channel;
        //     }
        
        //     broadcast(new MessageSent($channel_arr, $last_msg));
        // }
        // catch(\Exception $e){
        //     // DB::rollback();
        //     // $ret_arr = ['status'=>false,'msg'=>'Error'];
        //     Log::debug($e->getMessage());
        //     // return response()->json($ret_arr);
        // }
        
    }
}
