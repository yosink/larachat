<?php

namespace App\Http\Controllers\Api;

use App\Models\History;
use App\Services\RedisService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SocketController extends Controller
{
    protected $service;

    public function __construct(RedisService $redisService)
    {
        $this->service = $redisService;
    }

    public function login(Request $request, $client_id)
    {
        $info = [
            'cmd' => $request->input('cmd'),
            'name' => $request->input('name'),
            'avatar' => $request->input('avatar')
        ];
        $this->service->login($client_id,$info);
        return response()->json(['status'=>0]);
    }

    public function getUser(Request $request)
    {
        $res = $this->service->getUser($request->input('uid'));
        return response()->json($res);
    }

    public function getUsers(Request $request)
    {
        $res = $this->service->getUsers(explode(',', $request->input('uids')));
        return response()->json($res);
    }

    public function getOnlineUsers()
    {
        $res = $this->service->getOnlineUsers();
        return response()->json($res);
    }

    public function getHistory()
    {
        $res = History::orderBy('addtime', 'desc')->paginate(10)->toArray();
        return response()->json($res);
    }

    public function addHistory($userid, $msg)
    {
        $user = $this->service->getUser($userid);
        History::create([
            'name' => $user->name,
            'avatar' => $user->avatar,
            'msg' => $msg,
            'addtime' => time()
        ]);
        return response()->json(['status'=> 0]);
    }
}
