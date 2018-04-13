<?php

namespace app;

use Phalcon\Events\Event;

/**
 * 引导类,初始化
 * Class guidance
 * @property \app\table\server $server_table
 * @package app
 */
class Guidance extends \Phalcon\Di\Injectable
{

    /**
     * 构造函数
     * guidance constructor.
     */
    public function __construct()
    {

    }

    /**
     * 开始之前
     * @param Event $event
     * @param \pms\Server $pms_server
     * @param \Swoole\Server $server
     */
    public function beforeStart(Event $event, \pms\Server $pms_server, \Swoole\Server $server)
    {
        output('beforeStart  beforeStart', 'beforeStart');
        # 写入依赖注入

        $this->di->setShared('server_table', function () {
            return new \app\table\server();
        });
        $this->di->setShared('server_ping_table', function () {
            return new \app\table\serverPing();
        });

        $this->server_table;
    }

    /**
     * 启动事件
     * @param Event $event
     * @param \pms\Server $pms_server
     * @param \Swoole\Server $server
     */
    public function onWorkerStart(Event $event, \pms\Server $pms_server, \Swoole\Server $server)
    {
        output($server->taskworker, 'guidance');
        # 绑定一个权限验证
        $this->eventsManager->attach('Router:handleCall', $this);
        # 绑定一个准备判断和准备成功
        $this->eventsManager->attach('Server:readyJudge', $this);
        $this->eventsManager->attach('Server:readySucceed', $this);
    }

    /**
     * 准备判断
     */
    public function readyJudge(Event $event, \pms\Server $pms_server, $timeid)
    {
        output(boolval($this->config->database), '准备检查!');
        if ($this->config->database) {
            $this->dConfig->ready = true;
            output('初始化完成', 'init');
        }

    }

    /**
     * 准备完成
     */
    public function readySucceed(Event $event, \pms\Server $pms_server, \Swoole\Server $swoole_server)
    {
        #实例化更新服务列表
        $ser = new logic\UpdateServer($swoole_server);
        $ser->start();
    }

    /**
     * 路由事件
     * @param Event $event
     * @param \pms\Router $router
     * @param $data
     */
    public function handleCall(Event $event, \pms\Router $router, $data)
    {
        return true;
    }

}
