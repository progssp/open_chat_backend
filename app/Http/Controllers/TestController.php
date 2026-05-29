<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\GroupDetail;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Models\GroupMessageDetail;
use App\Models\OneToOneMessage;
use App\Http\Controllers\GroupMessageController;
use App\Http\Controllers\OneToOneMessageController;

class TestController extends Controller
{
    public function groups_created(){
        $data = GroupDetail::with('admin')->get();
        return json_encode($data);
    }

    public function groups_connected(){
        
        return asset('/storage/public/defaults/user_icons/logo192.png');
        $sender_id = 1;
        $receiver_id = 5;

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


    public function word_freq(Request $request){
        $validation = Validator::make($request->all(),[
            'top' => 'required|int',
            'exclude' => 'required'
        ]);
        if($validation->fails()){
            return response()->json(['status'=>false,'msg'=>$validation->errors()]);    
        }

        ini_set('memory_limit','-1');
        ini_set('file_uploads','On');
        ini_set('upload_max_filesize','200M');
        ini_set('post_max_size','200M');
        

        if($request->hasFile('txt_file')){
            $top = $request->input('top');
            $exclude = $request->input('exclude');
            $exclude = json_decode($exclude,true);
            // $path = "storage/test_dir/GUl3sZnTxUcEWmC7ENhCT8bvgo9v2yXfpZpXtnFW.txt";
            $path = "storage/test_dir/djpRHcMZbDZtiQtcl5usKGnLNedbTSVizRDi7fBc.txt";
            $path = $request->file('txt_file')->store('public/test_dir');
            $path = str_replace('public','storage',$path);
            // $f = fopen($path,"r");

            $file = file_get_contents($path);
            $file = strtolower($file);
            
            $words = str_word_count($file, 1);
            
            $diff = array_diff($words,$exclude);
            $wordCounts = array_count_values($diff);
            arsort($wordCounts);


            $values = array_values($wordCounts);
            
            $counter = -1;
            for($i=0;$i<count($values);$i++){
                // echo "comparing".$values[$i]."<=".intval($top) . "\n";
                if(intval($values[$i]) <= intval($top)){
                    break;
                }
                $counter++;
            }
            
            if($counter == -1){
                return [];
            }

            $i=0;
            

            $final_arr['data'] = [];
            $inner_obj = new \stdClass;
            foreach ($wordCounts as $word => $count) {
                $i++;
                if($i > $counter){
                    break;
                }
                $inner_obj->word = $word;
                $inner_obj->count = $count;

                $final_arr['data'][] = $inner_obj;
                $inner_obj = new \stdClass;
            }

            return $final_arr;
            
            
            
            return $wordCounts;

            // foreach ($wordCounts as $word => $count) {
            //     echo "$word: $count\n";
            // }
            
        }
        else if($request->input('text') != null){
            $text = $request->input('text');
            $text = strtolower($text);
            $top = $request->input('top');
            $exclude = $request->input('exclude');
            $exclude = json_decode($exclude,true);

            $words = str_word_count($text, 1);
            
            $diff = array_diff($words,$exclude);
            $wordCounts = array_count_values($diff);
            arsort($wordCounts);

            $values = array_values($wordCounts);
            
            $counter = -1;
            for($i=0;$i<count($values);$i++){
                if(intval($values[$i]) <= intval($top)){
                    break;
                }
                $counter++;
            }

            if($counter == -1){
                return [];
            }

            $i=0;
            

            $final_arr['data'] = [];
            $inner_obj = new \stdClass;
            foreach ($wordCounts as $word => $count) {
                $i++;
                if($i > $counter){
                    break;
                }
                $inner_obj->word = $word;
                $inner_obj->count = $count;

                $final_arr['data'][] = $inner_obj;
                $inner_obj = new \stdClass;
            }

            return $final_arr;
            
            

            
            
            // return $wordCounts;
            // return response()->json(['status'=>true,'text'=>$text,'top'=>$top,'exclude'=>$exclude]);
        }
        else{
            return response()->json(['status'=>false,'msg'=>'either upload text file or enter some text']);
        }
    }

    public function handle_webhook(Request $request){
        $signature = $request->header('X-Pusher-Signature');
        $body = $request->getContent();
        $hopedSignature = hash_hmac('sha256',$body,config('broadcasting.connections.pusher.secret'));

        if($signature !== $hopedSignature){
            return response()->json(['err' => 'invald sig'],400);
        }

        $payload = json_decode($body,true);
        foreach($payload['events'] as $events){
            Log::info($events);
        }
        
        return response()->json(['status' => 'OK'],200);
    }
}
