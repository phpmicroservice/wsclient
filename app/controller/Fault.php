<?php

namespace app\controller;

use app\logic\Proxy;
use app\logic\Server;

/**
 * 需要转发的请求
 */
class Fault extends \pms\Controller
{

    public function initialize()
    {

    }

    /**
     * 不合法的控制器名字
     * @param $data
     */
    public function proxy()
    {
        $data = $this->connect->request;
        output('262626', 'Fault_proxy');
        $this->proxy_send($data, $this->connect->getFd());
    }

    /**
     * 代理发送
     * @param $data
     * @param $fd
     */
    public function proxy_send($data, $fd, $channel = false)
    {
        $server_name = $data['s'];
        $proxy = Proxy::getInstance($this->connect->swoole_server, $server_name);
        if (is_string($proxy)) {
            # 出错了!
            $data = [
                'e' => 404,
                'm' => '服务异常-' . $server_name . ':' . $proxy,
                'st' => 'proxy@/index/index',
                'p' => $data['p'] ?? ''
            ];
            $this->connect->swoole_server->send($fd, \swoole_serialize::pack($data) . PACKAGE_EOF);
            return false;
        }

        $re = $proxy->send($data, $fd);
        output($re, '消息发送结果');
        if ($re === false) {
            # 发送失败 写入队列
            $this->connect->swoole_server->channel->push([
                'fd' => $this->connect->getFd(),
                'd' => $data
            ]);
            swoole_timer_after(2000, [$this, pop_channel]);
        } else {
            $this->logger->info(json_encode($data));
            ## 发送成功
            if ($channel) {
                $this->pop_channel();
            }
        }
    }

    /**
     * 消耗队列
     */
    public function pop_channel()
    {
        output('pop_channel', 'pop_channel');

        output($this->connect->swoole_server->channel, 'pop_channel');
        $data = $this->connect->swoole_server->channel->pop();
        if ($data == false) {
            return false;
        }
        output($data, 'pop_channel_1');
        $this->proxy_send($data['d'], $data['fd'], true);
    }

    /**
     * 不合法的处理器名字
     */
    public function action()
    {

    }


}