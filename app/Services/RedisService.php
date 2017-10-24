<?php

namespace App\Services;


use Illuminate\Support\Facades\Redis;

class RedisService
{
    const PREFIX = 'webim';
    public function login($client_id, $info)
    {
        Redis::set(self::PREFIX . ':client:' . $client_id, json_encode($info));
        Redis::sadd(self::PREFIX . ':online', $client_id);
    }

    public function logout($client_id)
    {
        Redis::del(self::PREFIX . ':client:'.$client_id);
        Redis::srem(self::PREFIX . ':online', $client_id);
    }

    public function getOnlineUsers()
    {
        return Redis::smembers(self::PREFIX . ':online');
    }

    public function getUsers(array $users)
    {
        $keys = [];
        foreach ($users as $v){
            $keys[] = self::PREFIX . ':client:'.$v;
        }
        $info_arr = Redis::mget($keys);
        $rets = [];
        foreach ($info_arr as $value){
            $rets[] = json_decode($value, true);
        }
        return $rets;
    }

    public function getUser($userid)
    {
        $res = Redis::get(self::PREFIX . ':client:'.$userid);
        return json_decode($res, true);
    }

    public function exists($userid)
    {
        return Redis::exists(self::PREFIX . ':client:'.$userid);
    }
}