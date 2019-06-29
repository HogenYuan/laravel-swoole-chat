<?php

namespace App\Handlers;

use App\Models\ChatUser;
use Illuminate\Support\Facades\Cache;
use PhpParser\JsonDecoder;

class SwooleHandler
{
    private static $_instance;
    //对外提供获取唯一实例的方法
    public static function getInstance()
    {
        //检测类是否被实例化
        if (!(self::$_instance instanceof self)) {
            //清空缓存
            $redis = new \Redis();
            $redis->pconnect('127.0.0.1', 6379);
            $fds = $redis->GET('fds');
            $redis->SET('old fds', $fds);
            $redis->delete('fds');
            //懒得做数据表
            $redis->SET('server1', 0);
            $redis->SET('server2', 0);
            $redis->SET('server3', 0);
            $redis->delete('user1');
            $redis->delete('user2');
            $redis->delete('user3');
            self::$_instance = new SwooleHandler();
        }
        return self::$_instance;
    }


    public function onOpen($server, $request)
    { }

    // public function onHandshake($request, $response)
    // {
    //     info('swoole Handshake');
    // }

    //监听WebSocket连接关闭事件
    public function onClose($server, $fd)
    {
        $redis = new \Redis();
        $redis->pconnect('127.0.0.1', 6379);
        $fds = $redis->GET('fds');
        $fds = json_decode($fds, true);

        if (isset($fds[$fd])) {
            //获取sid
            $sid = $fds[$fd];
            unset($fds[$fd]);
            $new_fds = json_encode($fds);
            $redis->SET('fds', $new_fds);
            info("用户 {$fd} 离开了!");
            //修改用户列表和姓名
            $users = $redis->GET('user' . $sid);
            $users = json_decode($users, true);
            $nickname = $users[$fd];
            unset($users[$fd]);
            $new_users = json_encode($users);
            $redis->SET('user' . $sid, $new_users);
            //修改人数
            $count = $redis->decr('server' . $sid);
            //发消息
            $response = $this->response('close', [
                'count' => $count,
                'nickname' => $nickname,
                'fd' => $fd
            ]);
            foreach ($fds as $user_fd => $server_id) {
                if ($server_id == $sid) {
                    $server->push((int) $user_fd, $response);
                }
            }
        }
    }

    //监听WebSocket消息事件
    public function onMessage($server, $frame)
    {
        $data = $frame->data;
        $data = json_decode($data);
        $type = $data->type;
        $nickname = $data->nickname;
        $fd = $frame->fd;
        $sid = $data->sid;
        $redis = new \Redis();
        $redis->pconnect('127.0.0.1', 6379);
        //有用户接入
        if ($type == "connect") {
            //修改服务器人数
            $count = $redis->incr('server' . $sid);
            //保存用户fd
            $fds = $redis->GET('fds');
            if (!empty($fds)) {
                $fds = json_decode($fds, true);
            }
            $fds[$fd] = $sid;
            $new_fds = json_encode($fds);
            $redis->SET('fds', $new_fds);
            //新建链接
            $response = $this->response('connect', [
                'nickname' => $nickname,
                'fd' => $fd,
                'count' => $count
            ]);
            foreach ($fds as $user_fd => $server_id) {
                if ($server_id == $sid) {
                    $server->push((int) $user_fd, $response);
                }
            }
        } elseif ($type == "talk") {
            //获取fd用户
            $fds = $redis->GET('fds');
            $fds = json_decode($fds, true);
            //发送信息
            $response = $this->response('talk', [
                'nickname' => $nickname,
                'content' => $data->content,
                'fd' => $fd
            ]);
            foreach ($fds as $user_fd => $server_id) {
                info('test', [$sid, $server_id]);
                if ($server_id == $sid) {
                    $server->push((int) $user_fd, $response);
                }
            }
        } elseif ($type == "open") {
            //用户首次连入
            //获取用户列表
            // $redis->SET('nickname' . $fd, $nickname);
            $users = $redis->GET('user' . $sid);
            if (!empty($users)) {
                $users = json_decode($users, true);
            }
            $users[$fd] = $nickname;
            $new_users = json_encode($users);
            $redis->SET('user' . $sid, $new_users);
            //新建链接
            $response = $this->response('open', [
                'users' => $new_users
            ]);
            $server->push($fd, $response);
        }
    }

    // 接收http请求从post获取参数
    // 获取所有连接的客户端，验证uid给指定用户推送消息
    // token验证推送来源，避免恶意访问
    public function onRequest($request, $response)
    {
        info('swoole request', [$request, $response]);
    }

    function response($type, $data)
    {
        $data['type'] = $type;
        return json_encode($data);
    }
}
