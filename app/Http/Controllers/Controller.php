<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use App\Models\OneToOneMessage;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function encrypt($str){
        
        // Store the cipher method
        $ciphering = config('app.ciphering');
        
        // Use OpenSSl Encryption method
        $iv_length = openssl_cipher_iv_length($ciphering);
        $options = config('app.options');
        
        // Non-NULL Initialization Vector for encryption
        $encryption_iv = config('app.encryption_iv');
        
        // Store the encryption key
        $encryption_key = config('app.encryption_key');
        
        // Use openssl_encrypt() function to encrypt the data
        $encryption = openssl_encrypt(
            $str, 
            $ciphering,
            $encryption_key, 
            $options, 
            $encryption_iv
        );
        
        // Display the encrypted string        
        return ($encryption);
    }

    public static function decrypt($str){
        $ciphering = config('app.ciphering');
        $options = config('app.options');
        // Non-NULL Initialization Vector for decryption
        $decryption_iv = config('app.encryption_iv');
        
        // Store the decryption key
        $decryption_key = config('app.encryption_key');
        
        // Use openssl_decrypt() function to decrypt the data
        $decryption = openssl_decrypt(
            $str, 
            $ciphering, 
            $decryption_key, 
            $options, 
            $decryption_iv
        );
        
        // Display the decrypted string
        return ($decryption);
    }

    public function multiple_data(){
        $data = User::select('user_first_name','id')->with(['on_to_one_message_sender'=>function($query){
            $query->select('sender_id');
        }])->get();
        $data2 = OneToOneMessage::select('message','sender_id')->with(['user_sender'=>function($query){
            $query->select('id','user_first_name');
        }])->get();
        // $data = DB::table('one_to_one_messages')
        // ->join('users','one_to_one_messages.sender_id','=','users.id')
        // ->select('one_to_one_messages.message','users.user_first_name as sender_name')->get();
        return json_encode($data2);
    }
}
