<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\TestController;

Route::get('/home', function () {
    return view('welcome');
});
Route::get('create-cache', function(){
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    Artisan::call('optimize');   
    return response()->json(['status' => true,'msg' => 'cache made & optimized']);
});
Route::get('delete-cache', function(){
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize');   
    return response()->json(['status' => true,'msg' => 'cache cleared & optimized']);
});
Route::get('storage-link', function(){
    Artisan::call('storage:link'); 
    return response()->json(['status' => true,'msg' => 'storage linked']);
});
Route::get('/multiple-data',[App\Http\Controllers\Controller::class, 'multiple_data']);


Route::get('send_mail',[App\Http\Controllers\MailController::class, 'send_mail']);



//Test routes (will delete later)
Route::get('/groups-created',[TestController::class,'groups_created']);
Route::get('/groups-connected',[TestController::class,'groups_connected']);