<?php

namespace app\controller;

use app\logic\Proxy;
use pms\Controller\WsBase;


/**
 * 需要转发的请求
 */
class Fault extends WsBase
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
        $data = $this->counnect->getData();
        \pms\output($data, 'Fault_proxy');
        //$this->proxy_send($data, $this->counnect->getFd());
    }

    /**
     * 代理发送
     * @param $data
     * @param $fd
     */
    public function proxy_send($data, $fd, $channel = false)
    {
        $server_name = $data['s'];
        $proxy = Proxy::getInstance($this->counnect->swoole_server, $server_name);
        if (is_string($proxy)) {
            # 出错了!
            $data = [
                'e' => 404,
                'm' => '服务异常-' . $server_name . ':' . $proxy,
                'st' => 'proxy@/index/index',
                'p' => $data['p'] ?? ''
            ];
            $this->counnect->swoole_server->send($fd, $this->encode($data));
            return false;
        }

        $re = $proxy->send($data, $fd);
        \pms\output($re, '消息发送结果');
        if ($re === false) {
            # 发送失败 写入队列
            $this->counnect->swoole_server->channel->push([
                'fd' => $this->counnect->getFd(),
                'd' => $data
            ]);
            swoole_timer_after(2000, [$this, 'pop_channel']);
        } else {
            $this->logger->info(json_encode($data));
            ## 发送成功
            if ($channel) {
                $this->pop_channel();
            }
        }
    }

    /**
     * 编码
     * @param array $data
     * @return string
     */
    private function encode(array $data): string
    {
        $msg_normal = \pms\Serialize::pack($data);
        $msg_length = pack("N", strlen($msg_normal)) . $msg_normal;
        return $msg_length;
    }


    /**
     * 消耗队列
     */
    public function pop_channel()
    {
        \pms\output('pop_channel', 'pop_channel');

        \pms\output($this->counnect->swoole_server->channel, 'pop_channel');
        $data = $this->counnect->swoole_server->channel->pop();
        if ($data == false) {
            return false;
        }
        \pms\output($data, 'pop_channel_1');
        $this->proxy_send($data['d'], $data['fd'], true);
    }

    /**
     * 不合法的处理器名字
     */
    public function action()
    {

    }


}