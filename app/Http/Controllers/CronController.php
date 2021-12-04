<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Login;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\InstagramController;
use App\Models\Logs;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class CronController extends Controller
{

/*
public function getFeedTimeline($cookie){

$useragent = (new InstagramController)->uamulti();
// Media fetching feed
$req = (new InstagramController)->instagram(1, $useragent, 'feed/timeline/',$cookie);
$data = json_decode($req['1'],true);
 //die(json_encode($data));

 foreach($data['items'] as $key => $value) {
    $mediaid    = $value['id'];
    $shortcode = $value['code'];
    $liked = $value['has_liked'];

    // $mediaid    = $data['items'][0]['id'];
    // $shortcode = $data['items'][0]['code'];
    // $liked = $data['items'][0]['has_liked'];
    $datax = [
        "media_id" => $mediaid, "shortcode" => $shortcode, "liked" => $liked
    ];

return json_encode($datax);
}
}
*/

private function Like ($cookie,$mediaid,$username){
    $logs = new Logs();
    $useragent = (new InstagramController)->uamulti();
    $like = (new InstagramController)->instagram(1, $useragent, 'media/'.$mediaid.'/like/',$cookie,(new InstagramController)->hook('{"media_id":"'.$mediaid.'"}'));
    $json=json_decode($like[1]);
   // die(json_encode($json));
   if($json->status=='ok')
   {
    $logs->username = $username;
    $logs->status = "ok";
    $logs->media_id = $mediaid;
    $logs->save();

   }else{

    $logs->username = $username;
    $logs->status = "fail";
    $logs->media_id = $mediaid;
    $logs->save();
    //break;

   }

    return $logs;
}


public function run(){

    $useragent = (new InstagramController)->uamulti();
    //echo $useragent;

   $stm = DB::table('logins1')->pluck('cookie', 'username');
   //echo $stm;
   foreach ($stm as $name => $cookies) {
       $cookie = $cookies;
       $username = $name;


       $req = (new InstagramController)->instagram(1, $useragent, 'feed/timeline/',$cookie);
       $data = json_decode($req['1'],true);
       //die(json_encode($data));

    foreach($data['items'] as $key => $value) {
    $mediaid    = $value['id'];
    $shortcode = $value['code'];

    $likes = $this->Like($cookie,$mediaid,$username);
    $json = json_decode($likes);

    if($json->status == 'fail'){
        $stm = DB::table('logins1')->pluck('password', 'username');
        //$datax = json_decode($stm);
        //echo $stm[$username];
        $pwd = $stm[$username];
        $proxy = '';

        $request = (new InstagramController)->relogin($username,$pwd,$proxy);
        $json_data = json_decode($request);
        //die(json_encode($json));
        if($json_data->result == true){
        $cookies = $json_data->cookies;
        // $user = DB::table('logins1')->where('username', $username)->get();
        // $id = $user[0]->id;

        $UpdateDetails = Login::where('username', '=',  $username)->first();
        $UpdateDetails->cookie = $cookies;
        $UpdateDetails->save();

        echo json_encode(array("status" => $json_data->result , "message" => "Reloging Successfully"));
        break;
        }
    }

    echo json_encode($json);
    break;



     }
     }

}



}
