<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SampleMail;

class MailController extends Controller
{
    public function send_mail(){
        $data = (object)['from'=>'test user','msg'=>'this is sample mail message'];

        // $to_email = "shantanusingh@gmail.com";
        // $subject = "Simple Email Test via PHP";
        // $body = "Hi,nn This is test email send by PHP Script";
        // $headers = "From: sender\'s email";
        // try{
        //     (mail($to_email, $subject, $body, $headers));
        //     echo "sent";           
        // }
        // catch(Exception $e){
        //     echo $e->getMessage();
        // }
        
        
        foreach (['shantanusingh922@gmail.com', 'shantanusingh922@yahoo.com'] as $recipient) {
            Mail::to($recipient)->send(new SampleMail($data));
        }
        return "sent";

    }
}
