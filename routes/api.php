<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupMessageController;
use App\Http\Controllers\GroupDetailController;
use App\Http\Controllers\OneToOneMessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TestController;


Route::get("/",function(){
    return "api route workinq";
});

Route::post('/word-freq', [TestController::class,'word_freq']);

Route::post('/webhook', [TestController::class,'handle_webhook']);

//routes for development
Route::post('/generate-group-mess', [GroupMessageController::class,'generate_group_mess']);



Route::prefix('user')->group(function(){
    
    Route::get('login', [UserController::class, 'login_view'])->name('login');
    Route::post('login', [UserController::class, 'login']);

    Route::post('register', [UserController::class, 'register']);
   
    Route::post('/check-username-availability', [UserController::class,'check_username_availability']);



    Route::middleware('auth:api')->group(function(){
        
        Route::post('/check-auth',[UserController::class,'check_auth']);

        Route::post('/edit-profile',[UserController::class,'edit_profile']);

        Route::post('/create-group',[GroupDetailController::class,'create_group']);
        Route::post('/add-members-in-group',[GroupDetailController::class,'add_members_in_group']);

        Route::post('/get-messages-for-left-panel',[GroupMessageController::class,'get_group_messages_for_left_panel']);

        Route::post('/send-group-message',[GroupMessageController::class,'send_group_message']);
        
        Route::post('/send-one-to-one-message',[OneToOneMessageController::class,'send_one_to_one_message']);

        Route::post('/get-one-to-one-messages',[OneToOneMessageController::class,'get_one_to_one_messages']);
    
        Route::post('/get-group-messages',[GroupMessageController::class,'get_group_messages']);
        Route::post('/search-users',[UserController::class,'search_users']);

        
        Route::post('/start-video-call',[OneToOneMessageController::class,'start_video_call']);
        
        Route::post('logout', [UserController::class, 'logout']);
    });

});

