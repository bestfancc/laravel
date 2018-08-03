<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class SiteController extends Controller
{

    public function Index()
    {
        echo 'apache重启中...';exit;
//        $this->phpinfo();exit;  
        $memcache = new \Memcached();
        $result = $memcache->addServer('127.0.0.1',11211);
//        $result = $memcache->connect();
//        var_dump($result);exit;
        $memcache->set('ssdf','sdfsssssss');
        $value = $memcache->get('ssdf');
        var_dump($value);exit;
//        $redis = new \Redis();
//        $redis->connect('fancc.top', 6379);
//        $authResult = $redis->auth('zhangxiujuan.10');
//        var_dump($authResult);exit;
//        $redis->set('aaa','sdfeesdfsf');
//        $name = $redis->get('aaa');
//        var_dump($name);
//        exit;
//        $this->phpinfo();exit;
//        session_start();
//        if (!isset($_SESSION['TEST'])) {
//            $_SESSION['TEST'] = time();
//        }
//
//        $_SESSION['TEST3'] = time();
//
//        print $_SESSION['TEST'];
//        print "<br><br>";
//        print $_SESSION['TEST3'];
//        print "<br><br>";
//        print session_id();

    }
    //即时聊天
    public function chat()
    {
        if(!Auth::check()) {
            return redirect()->route('login');
        }
        $userId = Auth::id();
        $user = DB::table('users')->where('id', '=', $userId)->first();
        @$redis = new \Redis();
        @$redis->connect('127.0.0.1', 6379);
        @$redis->set('chat-user-info-'.$userId,json_encode($user));
        $data = [
          'user' => $user,
        ];
        return view('/site/chat',$data);
    }
    public function phpinfo()
    {
	    phpinfo();
    }	
}
