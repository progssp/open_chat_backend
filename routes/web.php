<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

Route::get('/home', function () {
    return view('welcome');
});
Route::get('login2', function(){
    $ar = [2,4,76,89,43,65,90,65,23,1];
    $tmp = 0;
    echo "unsorted<br/>" . json_encode($ar) . "<br/>";
    for($i=0;$i<count($ar);$i++){
        for($j=0;$j<count($ar)-1;$j++){
            if($ar[$j+1] > $ar[$j]){
                $tmp = $ar[$j];
                $ar[$j] = $ar[$j+1];
                $ar[$j+1] = $tmp;
            }
        }
    }
    echo "sorted<br/>" . json_encode($ar) . "<br/>";
    // return response()->json("hell2o");
});
Route::get('/multiple-data',[App\Http\Controllers\Controller::class, 'multiple_data']);


Route::get('send_mail',[App\Http\Controllers\MailController::class, 'send_mail']);



//Test routes (will delete later)
Route::get('/groups-created',[TestController::class,'groups_created']);
Route::get('/groups-connected',[TestController::class,'groups_connected']);